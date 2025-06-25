<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tagihan;
use App\Models\Mikrotik;
use App\Models\Pelanggan;
use App\Models\PaketPppoe;
use App\Models\DataInvoice;
use Illuminate\Http\Request;
use App\Models\BillingSeting;
use App\Models\UnpaidInvoice;
use Illuminate\Support\Carbon;
use App\Helpers\WhatsappHelper;
use App\Exports\PelangganExport;
use App\Imports\PelangganImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class BillingController extends Controller
{
    public function unpaid()
    {
        $currentMonth = now()->month;  // Mendapatkan bulan sekarang
        $currentYear = now()->year;    // Mendapatkan tahun sekarang

        $unpaidInvoices = Tagihan::with(['pelanggan.mikrotik', 'pelanggan.paket'])
            ->whereIn('status', ['Belum Lunas', 'Tertunggak'])
            ->whereMonth('tanggal_jatuh_tempo', $currentMonth)  // Filter berdasarkan bulan sekarang
            ->whereYear('tanggal_jatuh_tempo', $currentYear)    // Filter berdasarkan tahun sekarang
            ->latest('tanggal_jatuh_tempo')
            ->get();

        return view('ROLE.MEMBER.BILLING.unpaid', compact('unpaidInvoices'));
    }




    public function bayar($invoiceId)
    {
        // Retrieve the invoice
        $invoice = UnpaidInvoice::findOrFail($invoiceId);
        $invoice->sudah_dibayar = true;
        $invoice->save();

        // Retrieve the customer with related data
        $pelanggan = Pelanggan::with('paket', 'mikrotik')->find($invoice->pelanggan_id);

        // Check for other unpaid invoices (tunggakan)
        $tunggakan = UnpaidInvoice::where('pelanggan_id', $pelanggan->id)
            ->where('status_pembayaran', unpaid)
            ->orderBy('jatuh_tempo', 'asc')
            ->get();

        // Retrieve the latest invoice
        $latestInvoice = UnpaidInvoice::where('pelanggan_id', $pelanggan->id)
            ->orderBy('jatuh_tempo', 'desc')
            ->first();

        if ($latestInvoice && $latestInvoice->id == $invoice->id) {
            // If the latest invoice was paid

            // Update the customer's isolation status
            $pelanggan->isolated = false;
            $pelanggan->notified = false;
            $pelanggan->notified_at = null;

            // Calculate the next payment due date
            $tanggalPemasangan = Carbon::parse($pelanggan->tanggal_pemasangan);
            $sekarang = Carbon::now();

            if ($sekarang->day >= $tanggalPemasangan->day) {
                $jatuhTempoBaru = $tanggalPemasangan->copy()->addMonthsNoOverflow($sekarang->diffInMonths($tanggalPemasangan) + 1);
            } else {
                $jatuhTempoBaru = $tanggalPemasangan->copy()->addMonthsNoOverflow($sekarang->diffInMonths($tanggalPemasangan));
            }

            $pelanggan->pembayaran_selanjutnya = $jatuhTempoBaru->setDay($tanggalPemasangan->day);

            // Now update the MikroTik profile based on the customer's package
            $this->updateMikrotikProfile($pelanggan);
        } else {
            // If there are still unpaid invoices, keep the customer isolated
            $pelanggan->isolated = true;
            $pelanggan->notified = false;
            $pelanggan->notified_at = null;

            if ($tunggakan->count() > 0) {
                $pelanggan->pembayaran_selanjutnya = $tunggakan->first()->jatuh_tempo;
            }
        }

        // Save the updated customer data
        $pelanggan->save();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Tagihan berhasil dibayar.');
    }

    private function updateMikrotikProfile($pelanggan)
    {
        // Retrieve the MikroTik router data
        $router = Mikrotik::where('router_id', $pelanggan->router_id)->first();

        if (!$router) {
            \Log::error('Router tidak ditemukan untuk pelanggan ID: ' . $pelanggan->id);
            return;
        }

        try {
            // Connect to the MikroTik API
            $client = new \RouterOS\Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $router->port_api,
                'user' => $router->username,
                'pass' => $router->password,
            ]);

            // Retrieve PPPoE secrets (users)
            $querySecret = new \RouterOS\Query('/ppp/secret/print');
            $secretUsers = $client->query($querySecret)->read();

            // Find the matching PPPoE user
            foreach ($secretUsers as $user) {
                if ($user['name'] === trim($pelanggan->akun_pppoe)) {
                    // Update profile based on the customer's package
                    $updateProfileQuery = new \RouterOS\Query('/ppp/secret/set');
                    $updateProfileQuery->equal('.id', $user['.id']);
                    $updateProfileQuery->equal('profile', $pelanggan->profile_paket);

                    $client->query($updateProfileQuery)->read();

                    \Log::info("✅ Pelanggan ID: {$pelanggan->id} profile PPPoE diubah ke {$pelanggan->profile_paket}.");

                    // Check for and disconnect any active connection
                    $queryActive = new \RouterOS\Query('/ppp/active/print');
                    $activeUsers = $client->query($queryActive)->read();

                    foreach ($activeUsers as $active) {
                        if ($active['name'] === trim($pelanggan->akun_pppoe)) {
                            // Disconnect the active user to apply the new profile
                            $removeActive = new \RouterOS\Query('/ppp/active/remove');
                            $removeActive->equal('.id', $active['.id']);
                            $client->query($removeActive)->read();

                            \Log::info("⚡ Koneksi aktif pelanggan ID: {$pelanggan->id} telah diputus, profile baru akan diterapkan.");
                        }
                    }

                    return;
                }
            }

            \Log::warning("⚠️ Akun PPPoE '{$pelanggan->akun_pppoe}' tidak ditemukan di router {$router->ip_address}.");
        } catch (\Exception $e) {
            \Log::error("❌ Gagal update profile pelanggan ID {$pelanggan->id}: " . $e->getMessage());
        }
    }

    public function paid()
    {
        $paidInvoices = Tagihan::with(['pelanggan.mikrotik', 'pelanggan.paket'])
            ->where('status', 'Lunas')
            ->orderByDesc('tanggal_pembayaran')
            ->get();

        return view('ROLE.MEMBER.BILLING.paid', compact('paidInvoices'));
    }


    public function riwayatTagihan()
    {
        $currentMonth = now()->month;  // Mendapatkan bulan sekarang
        $currentYear = now()->year;    // Mendapatkan tahun sekarang

        // Mengambil data tagihan yang sudah dibayar (Lunas) dan yang belum dibayar (Belum Lunas/Tertunggak)
        $invoices = Tagihan::with(['pelanggan.mikrotik', 'pelanggan.paket'])
            ->whereIn('status', ['Lunas', 'Belum Lunas', 'Tertunggak'])
            ->where(function ($query) use ($currentMonth, $currentYear) {
                // Filter berdasarkan bulan dan tahun saat ini untuk tagihan yang belum dibayar
                $query->whereMonth('tanggal_jatuh_tempo', $currentMonth)
                    ->whereYear('tanggal_jatuh_tempo', $currentYear);
            })
            ->orWhere(function ($query) {
                // Untuk tagihan yang sudah dibayar, tidak perlu filter bulan/tahun
                $query->where('status', 'Lunas');
            })
            ->orderByDesc('tanggal_pembayaran')  // Urutkan berdasarkan tanggal pembayaran jika ada
            ->orderByDesc('tanggal_jatuh_tempo') // Atau urutkan berdasarkan tanggal jatuh tempo jika pembayaran belum dilakukan
            ->get();

        return view('ROLE.MEMBER.BILLING.riwayat', compact('invoices'));
    }


    public function bil_pelanggan()
    {
        $user = auth()->user();

        // Ambil semua MikroTik yang dimiliki user ini
        $mikrotikIds = $user->mikrotik()->pluck('id');

        // Ambil pelanggan berdasarkan `mikrotik_id`
        $pelanggan = Pelanggan::whereIn('mikrotik_id', $mikrotikIds)
            ->orderBy('id', 'desc')
            ->get();

        // Ambil paket PPPoE berdasarkan `mikrotik_id`
        $paketpppoe = PaketPppoe::whereIn('mikrotik_id', $mikrotikIds)->get();

        return view('ROLE.MEMBER.BILLING.bill_pelanggan', compact('pelanggan', 'paketpppoe'));
    }

    public function hapusData($id)
    {
        $data = Pelanggan::find($id);
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $mikrotik_id = $data->mikrotik_id;
        $mikrotik = Mikrotik::find($mikrotik_id);

        if (!$mikrotik) {
            return response()->json(['error' => 'MikroTik tidak ditemukan'], 404);
        }

        try {
            // Koneksi ke MikroTik
            $client = new \RouterOS\Client([
                'host' => 'id-1.aqtnetwork.my.id',  // Sesuaikan dengan model Anda
                'user' => $mikrotik->username,
                'pass' => $mikrotik->password,
                'port' => (int) $mikrotik->port_api,
            ]);

            $akun_pppoe = $data->akun_pppoe;

            // **Ambil daftar PPP Secret berdasarkan name**
            $querySecret = (new \RouterOS\Query('/ppp/secret/print'))
                ->where('name', $akun_pppoe);
            $secrets = $client->query($querySecret)->read();

            \Log::info('Data PPP Secret: ' . json_encode($secrets));

            if (empty($secrets)) {
                return response()->json(['error' => 'PPP Secret tidak ditemukan untuk akun: ' . $akun_pppoe], 404);
            }

            // **Ambil daftar PPP Active berdasarkan name**
            $queryActive = (new \RouterOS\Query('/ppp/active/print'))
                ->where('name', $akun_pppoe);
            $actives = $client->query($queryActive)->read();

            \Log::info('Data PPP Active: ' . json_encode($actives));

            // **Hapus PPP SECRET berdasarkan .id**
            foreach ($secrets as $secret) {
                $client
                    ->query((new \RouterOS\Query('/ppp/secret/remove'))
                        ->equal('.id', $secret['.id']))
                    ->read();
            }

            // **Hapus PPP ACTIVE berdasarkan name**
            foreach ($actives as $active) {
                $client
                    ->query((new \RouterOS\Query('/ppp/active/remove'))
                        ->equal('.id', $active['.id']))
                    ->read();
            }

            // Hapus data pelanggan dari database
            $data->delete();

            return response()->json(['success' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
        }
    }


    public function updatePelanggan(Request $request)
{
   
    $pelanggan = Pelanggan::find($request->id);

    $pelanggan->update([
        'nama_pelanggan'  => $request->nama_pelanggan,
        'nomor_telepon'   => $request->nomor_telepon,
        'paketpppoe_id'           => $request->paket,
    ]);

    return redirect()->back()->with('success', 'Data pelanggan berhasil diperbarui.');
}

    public function kirimwa(Request $request)
    {
        $user = auth()->user();

        // Ambil session_id berdasarkan role user
        if ($user->role === 'member') {
            $session_id = $user->unique_member;
        } else {
            // Cek apakah ada anak user yang memiliki session_id
            $child = User::where('parent_id', $user->id)->first();
            $session_id = $child?->unique_member; // null safe jika child tidak ada
        }

        // Cek apakah session_id ditemukan
        if (!$session_id) {
            return response()->json(['success' => false, 'message' => 'Session ID tidak ditemukan.']);
        }

        // Ambil nomor tujuan dan pesan
        $nomor = $request->nomor;
        $pesan = $request->pesan;

        try {
            // Kirim request POST ke server WhatsApp API dengan Content-Type: application/json
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('http://whatsapp.aqtnetwork.my.id:3000/api/send', [
                        'session_id' => $session_id,
                        'number' => $nomor,
                        'message' => $pesan,
                    ]);

            // Log response dari server
            \Log::info('Response dari server WhatsApp:', [
                'session_id' => $session_id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Cek jika request berhasil
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan berhasil dikirim',
                    'data' => $response->body(),
                ]);
            } else {
                // Jika request gagal
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim pesan',
                    'error' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Menangani error jika terjadi masalah saat koneksi ke server WhatsApp
            \Log::error('Error saat menghubungi server WhatsApp:', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan koneksi ke server WhatsApp',
                'error' => $e->getMessage(),
            ], 500);
        }
    }





    public function bcwa()
    {
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new PelangganImport, $request->file('file'));

        return redirect()->back()->with('success', 'Data berhasil diimport!');
    }

    public function exportExcel()
    {
        return Excel::download(new PelangganExport, 'data_pelanggan.xlsx');
    }
    public function billingSetting()
    {
        return view('ROLE.MEMBER.BILLING.billing_setting');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prorata_enable' => 'nullable|boolean',
            'generate_invoice_mode' => 'required|in:tanggal_pembayaran,dimajukan',
            'dimajukan_hari' => 'nullable|integer|min:0',
            'default_jatuh_tempo_hari' => 'required|integer|min:1',
        ]);

        $validated['prorata_enable'] = $request->has('prorata_enable');

        BillingSeting::updateOrCreate(['id' => 1], $validated);

        return back()->with('success', 'Pengaturan billing berhasil disimpan.');
    }
    public function showDetail($id)
    {
        $invoice = Tagihan::with(['pelanggan.mikrotik'])
            ->where('invoice_id', $id)
            ->firstOrFail();

        return view('ROLE.MEMBER.BILLING._detail', compact('invoice'));
    }
    public function showUnpaidDetail($id)
    {
        $invoice = Tagihan::with(['pelanggan.mikrotik'])
            ->where('invoice_id', $id)
            ->firstOrFail();
        return view('ROLE.MEMBER.BILLING._detail', compact('invoice'));
    }
    public function cetakInvoice($id){
        $invoice = Tagihan::with(['pelanggan.mikrotik', 'pelanggan.paket'])
            ->where('invoice_id', $id)
            ->firstOrFail();
        //dd($invoice);
        // Cek apakah invoice sudah lunas
        if ($invoice->status !== 'Lunas') {
            return redirect()->back()->with('error', 'Invoice belum lunas, tidak dapat dicetak.');
        }

        return view('ROLE.MEMBER.BILLING.cetak_invoice', compact('invoice'));
    }
    public function cariInvoice(Request $request)
    {

        $invoice = Tagihan::with('pelanggan.paket')
            ->where('invoice_id', $request->invoice_id)
            ->first();

        if ($invoice) {
            return view('ROLE.MEMBER.BILLING.cari_invoice', compact('invoice'));
        } else {
            return response()->json(['success' => false, 'message' => 'Invoice tidak ditemukan']);
        }
    }
    public function success()
    {
        return view('ROLE.MEMBER.BILLING.success');
    }
    public function confirmBayar(Request $request)
    {

        \Log::info($request);
        try {


            // Ambil data tagihan berdasarkan ID
            $tagihan = Tagihan::with(['pelanggan'])->findOrFail($request->id);
            $data_invoice = DataInvoice::where('tagihan_id', $tagihan->id)->first();

            // Update status tagihan
            $tagihan->update([
                'status' => 'Lunas',
                'metode' => $request->metode,
                'penagih' => auth()->user()->name,
                'tanggal_dibayar' => Carbon::now(),
            ]);
            $data_invoice->update(
                [
                    'status' => 'Lunas',
                ]
            );

            // Siapkan data WA
            $data = [
                'full_name' => $tagihan->pelanggan->nama_pelanggan ?? 'Pelanggan',
                'no_invoice' => $tagihan->invoice_id,
                'total' => number_format($tagihan->jumlah_tagihan, 0, ',', '.'),
                'invoice_date' => Carbon::now()->format('d-m-Y'),
                'footer' => 'Hubungi CS jika ada pertanyaan.',
            ];

            // Kirim pesan WA jika ada pelanggan
            if ($tagihan->pelanggan) {
                WhatsappHelper::sendWaTemplate(
                    $tagihan->pelanggan->nomor_telepon,
                    'Payment Paid',
                    $data,
                    $tagihan->pelanggan->user_id ?? null,
                    $data_invoice->unique_member
                );
            }

            return response()->json(['message' => 'Pembayaran berhasil dikonfirmasi.']);
        } catch (\Exception $e) {
            \Log::error('❌ Konfirmasi bayar gagal: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat memproses pembayaran.'], 500);
        }
    }




    public function kirimWhatsapp(Request $request)
    {
        // Ambil data invoice berdasarkan tagihan_id dari request
        $datainvoice = DataInvoice::where('tagihan_id', $request->id)->first();

        if (!$datainvoice) {
            return response()->json(['message' => 'Data invoice tidak ditemukan.'], 404);
        }

        // Ambil data tagihan lengkap
        $invoice = Tagihan::with('pelanggan.paket')->find($datainvoice->tagihan_id);

        if (!$invoice || !$invoice->pelanggan) {
            return response()->json(['message' => 'Tagihan atau pelanggan tidak valid.'], 404);
        }

        $pelanggan = $invoice->pelanggan;

        // Siapkan data untuk dikirim ke template WhatsApp
        $data = [
            'full_name' => $pelanggan->nama_pelanggan ?? 'Pelanggan',
            'uid' => $pelanggan->pelanggan_id ?? '-',
            'pppoe_user' => $pelanggan->akun_pppoe ?? '-',
            'pppoe_pass' => $pelanggan->password_pppoe ?? '-',
            'pppoe_profile' => $pelanggan->paket_pppoe ?? '-',
            'no_invoice' => $invoice->invoice_id ?? '-',
            'invoice_date' => Carbon::parse($invoice->tanggal_generate)->format('d-m-Y'),
            'amount' => number_format($invoice->jumlah_tagihan, 0, ',', '.'),
            'ppn' => number_format($invoice->ppn ?? 0, 0, ',', '.'),
            'discount' => number_format($invoice->diskon ?? 0, 0, ',', '.'),
            'total' => number_format(
                ($invoice->jumlah_tagihan ?? 0) + ($invoice->ppn ?? 0) - ($invoice->diskon ?? 0),
                0,
                ',',
                '.'
            ),
            'period' => Carbon::parse($invoice->tanggal_generate)->isoFormat('MMMM Y'),
            'due_date' => Carbon::parse($pelanggan->tanggal_jatuh_tempo)->format('d-m-Y'),
            'payment_gateway' => route('invoice.cari', ['invoice_id' => $invoice->invoice_id ?? '-']),
            'payment_mutasi' => $pelanggan->metode_pembayaran ?? '-',
            'footer' => 'Terima kasih telah menggunakan layanan kami.',
        ];

        // Kirim pesan WhatsApp via helper
        WhatsappHelper::sendWaTemplate(
            $pelanggan->nomor_telepon,      // Nomor tujuan
            'Invoice Reminder',               // Nama template WA
            $data,                          // Data yang digunakan dalam template
            $pelanggan->user_id ?? null,    // Optional user_id
            $datainvoice->unique_member       // Optional session_id
        );

        return response()->json(['message' => 'Pesan WhatsApp berhasil dikirim.']);
    }
    public function updateTagihan(Request $request)
{
    $request->validate([
        'id'      => 'required|exists:tagihan,id',
        'nominal'=> 'required|numeric|min:0',
        'diskon' => 'nullable|numeric|min:0|max:100',
        'ppn'    => 'nullable|numeric|min:0|max:100',
    ]);

    $tagihan = Tagihan::findOrFail($request->id);
    
$datainvoices = DataInvoice::where('tagihan_id', $tagihan->id)->first();
    $nominal        = $request->nominal;
    $diskon_percent = $request->diskon ?? $tagihan->pelanggan->diskon;
    $ppn_percent    = $request->ppn ?? $tagihan->pelanggan->ppn;

    // Hitung nominal diskon & ppn dalam rupiah
    $diskon_rupiah = $nominal * ($diskon_percent / 100);
    $ppn_rupiah    = $nominal * ($ppn_percent / 100);

    $jumlah_tagihan = max(($nominal - $diskon_rupiah) + $ppn_rupiah, 0);

    // Simpan ke tabel tagihan
   
    $tagihan->jumlah_tagihan  = $jumlah_tagihan;
    $tagihan->save();

    // Update juga ke data_invoice jika ditemukan
    if ($datainvoices) {
        $datainvoices->nominal = $jumlah_tagihan;
        $datainvoices->save();
    }

    return response()->json([
        'message' => 'Tagihan dan invoice berhasil diperbarui.',
        'data' => [
            'nominal'         => $nominal,
            'diskon_percent'  => $diskon_percent,
            'ppn_percent'     => $ppn_percent,
            'diskon_rupiah'   => round($diskon_rupiah),
            'ppn_rupiah'      => round($ppn_rupiah),
            'jumlah_tagihan'  => round($jumlah_tagihan),
        ]
    ]);
}



}

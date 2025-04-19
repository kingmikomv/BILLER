<?php

namespace App\Http\Controllers;

use App\Exports\PelangganExport;
use App\Imports\PelangganImport;
use App\Models\Mikrotik;
use App\Models\PaketPppoe;
use App\Models\Pelanggan;
use App\Models\UnpaidInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class BillingController extends Controller
{
    public function unpaid()
    {
        // Ambil jatuh tempo paling baru untuk setiap pelanggan yang belum bayar
        $latestInvoices = UnpaidInvoice::where('status_pembayaran', unpaid)
            ->selectRaw('MAX(jatuh_tempo) as jatuh_tempo, pelanggan_id')
            ->groupBy('pelanggan_id');

        // Gabungkan untuk ambil data lengkap dari tagihan tersebut
        $unpaidInvoices = UnpaidInvoice::with('pelanggan', 'pelanggan.mikrotik', 'pelanggan.paket')
            ->joinSub($latestInvoices, 'latest', function ($join) {
                $join
                    ->on('unpaid_invoices.pelanggan_id', '=', 'latest.pelanggan_id')
                    ->on('unpaid_invoices.jatuh_tempo', '=', 'latest.jatuh_tempo');
            })
            ->orderBy('unpaid_invoices.jatuh_tempo', 'asc')
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
    
    public function paid() {}
    public function riwayat() {}

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

    public function kirimwa(Request $request)
    {
        $nomor = $request->nomor;
        $pesan = $request->pesan;
        $token = 'g3ZXCoCHeR1y75j4xJoz';

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Token API tidak ditemukan.'], 400);
        }

        $response = Http::withHeaders([
            'Authorization' => $token
        ])->post('https://api.fonnte.com/send', [
            'target' => $nomor,
            'message' => $pesan,
            'countryCode' => '62',  // Kode Negara (62 untuk Indonesia)
        ]);

        if ($response->successful()) {
            return response()->json(['success' => true, 'message' => 'Pesan berhasil dikirim.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim pesan.']);
        }
    }

    public function bcwa() {}

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
}

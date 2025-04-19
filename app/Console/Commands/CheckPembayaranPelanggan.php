<?php

namespace App\Console\Commands;

use App\Models\Mikrotik;
use App\Models\Pelanggan;
use App\Models\UnpaidInvoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RouterOS\Client;
use RouterOS\Query;

class CheckPembayaranPelanggan extends Command
{
    protected $signature = 'pelanggan:check-pembayaran';
    protected $description = 'Cek pelanggan yang melewati batas tanggal pembayaran dan kirim notifikasi WhatsApp';
    protected $token = 'g3ZXCoCHeR1y75j4xJoz';

    public function handle()
    {
        $today = Carbon::now()->toDateString();
        $batasIsolasi = Carbon::now()->subDays(1)->toDateString();

        \Log::info("⏰ Tanggal hari ini: {$today}");

        // Langkah 1: Buat Invoice Baru Saat Jatuh Tempo
        $pelangganHariIni = Pelanggan::whereDate('pembayaran_selanjutnya', $today)->get();

        foreach ($pelangganHariIni as $pelanggan) {
            $sudahAda = UnpaidInvoice::where('pelanggan_id', $pelanggan->id)
                ->where('jatuh_tempo', $pelanggan->pembayaran_selanjutnya)
                ->where('status_pembayaran', unpaid)
                ->exists();

            if (!$sudahAda) {
                $invoiceId = $this->generateInvoiceId($pelanggan->id);
                $jatuhTempo = $pelanggan->pembayaran_selanjutnya;

                UnpaidInvoice::create([
                    'invoice_id' => $invoiceId,
                    'pelanggan_id' => $pelanggan->id,
                    'jatuh_tempo' => $jatuhTempo,
                    'bulan' => Carbon::parse($jatuhTempo)->locale('id')->translatedFormat('F'),
                    'tahun' => Carbon::parse($jatuhTempo)->format('Y'),
                    'sudah_dibayar' => false,
                ]);

                \Log::info("🧾 Invoice baru dicatat: {$invoiceId} untuk pelanggan ID: {$pelanggan->id}");
            }

            $pesan = $this->generateNotificationMessage($pelanggan);
            $this->sendWhatsAppNotification($pelanggan->nomor_telepon, $pesan);
        }

        // Langkah 2: Buat Invoice Baru Jika Bulan Berganti & Tagihan Lama Belum Lunas
        $pelangganUnpaid = Pelanggan::all();

        foreach ($pelangganUnpaid as $pelanggan) {
            $latestInvoice = UnpaidInvoice::where('pelanggan_id', $pelanggan->id)
                ->orderByDesc('jatuh_tempo')
                ->first();

            if ($latestInvoice && !$latestInvoice->sudah_dibayar) {
                $jatuhTempoBulan = Carbon::parse($latestInvoice->jatuh_tempo)->format('Y-m');
                $currentBulan = Carbon::now()->format('Y-m');

                if ($jatuhTempoBulan !== $currentBulan) {
                    $invoiceId = $this->generateInvoiceId($pelanggan->id);
                    $jatuhTempo = Carbon::now()->startOfMonth()->toDateString();

                    UnpaidInvoice::create([
                        'invoice_id' => $invoiceId,
                        'pelanggan_id' => $pelanggan->id,
                        'jatuh_tempo' => $jatuhTempo,
                        'bulan' => Carbon::parse($jatuhTempo)->locale('id')->translatedFormat('F'),
                        'tahun' => Carbon::parse($jatuhTempo)->format('Y'),
                        'sudah_dibayar' => false,
                    ]);

                    \Log::info("💡 Pelanggan ID {$pelanggan->id} masih nunggak, invoice bulan baru dibuat: {$invoiceId}");
                }
            }
        }

        // Langkah 3: Isolasi Pelanggan Yang Telat Lebih Dari 7 Hari
        $pelangganIsolasi = Pelanggan::whereDate('pembayaran_selanjutnya', '<=', $batasIsolasi)->get();

        foreach ($pelangganIsolasi as $pelanggan) {
            $sudahAda = UnpaidInvoice::where('pelanggan_id', $pelanggan->id)
                ->where('jatuh_tempo', $pelanggan->pembayaran_selanjutnya)
                ->where('status_pembayaran', unpaid)
                ->exists();

            if (!$sudahAda) {
                $invoiceId = $this->generateInvoiceId($pelanggan->id);
                $jatuhTempo = $pelanggan->pembayaran_selanjutnya;

                UnpaidInvoice::create([
                    'invoice_id' => $invoiceId,
                    'pelanggan_id' => $pelanggan->id,
                    'jatuh_tempo' => $jatuhTempo,
                    'bulan' => Carbon::parse($jatuhTempo)->locale('id')->translatedFormat('F'),
                    'tahun' => Carbon::parse($jatuhTempo)->format('Y'),
                    'sudah_dibayar' => false,
                ]);

                \Log::info("🧾 Invoice isolasi dicatat: {$invoiceId} untuk pelanggan ID: {$pelanggan->id}");
            }

            \Log::info("🚫 Mengisolasi pelanggan: {$pelanggan->id} - {$pelanggan->akun_pppoe}");

            $pesanIsolasi = $this->generateIsolationMessage($pelanggan);
            $this->sendWhatsAppNotification($pelanggan->nomor_telepon, $pesanIsolasi);
            $this->isolirPelanggan($pelanggan);
            \Log::info("🔒 Pelanggan ID {$pelanggan->id} berhasil diisolir.");
        }

        $this->info('✅ Proses pengecekan selesai. Invoice, notifikasi, dan isolasi diproses.');
    }

    private function generateInvoiceId($pelangganId)
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $random = strtoupper(Str::random(4));
        return $prefix . str_pad($pelangganId, 4, '0', STR_PAD_LEFT) . '-' . $random;
    }

    private function generateNotificationMessage($pelanggan)
    {
        $jumlahTagihan = optional($pelanggan->paket)->harga_paket ?? 0;
        $invoiceId = \App\Models\UnpaidInvoice::where('pelanggan_id', $pelanggan->id)
            ->where('status_pembayaran', unpaid)
            ->latest()
            ->value('invoice_id');  // hanya ambil invoice_id
        return "📢 *Pemberitahuan Tagihan Internet*\n\n"
            . "Yth. *{$pelanggan->nama_pelanggan}*,\n\n"
            . "Kami informasikan bahwa tagihan layanan internet Anda telah terbit dengan rincian sebagai berikut:\n\n"
            . "🔹 *Invoice:* {$invoiceId}\n"
            . "🔹 *ID Pelanggan:* {$pelanggan->pelanggan_id}\n"
            . '🔹 *Jumlah Tagihan:* Rp' . number_format($jumlahTagihan, 0, ',', '.') . "\n"
            . '🔹 *Jatuh Tempo:* ' . Carbon::parse($pelanggan->pembayaran_selanjutnya)->format('d M Y') . "\n\n"
            . "Mohon untuk segera melakukan pembayaran sebelum jatuh tempo guna menghindari gangguan layanan.\n\n"
            . "✅ *Bayar Sekarang:* \n\n"
            . "Jika Anda sudah melakukan pembayaran atau memerlukan bantuan, silakan hubungi kami.\n\n"
            . "📞 *Layanan Pelanggan:* 08XXXXXXXXXX\n\n"
            . 'Terima kasih atas kepercayaan Anda menggunakan layanan kami.';
    }

    private function generateIsolationMessage($pelanggan)
    {
        $jumlahTagihan = optional($pelanggan->paket)->harga_paket ?? 0;
        $invoiceId = \App\Models\UnpaidInvoice::where('pelanggan_id', $pelanggan->id)
            ->where('status_pembayaran', unpaid)
            ->latest()
            ->value('invoice_id');  // hanya ambil invoice_id
        return "⚠️ *Pemberitahuan Pembatasan Layanan*\n\n"
            . "Yth. *{$pelanggan->nama_pelanggan}*,\n\n"
            . "Kami informasikan bahwa layanan internet Anda sementara *dibatasi* karena belum diterimanya pembayaran tagihan dengan rincian berikut:\n\n"
            . "🔹 *Invoice:* {$invoiceId}\n"
            . "🔹 *ID Pelanggan:* {$pelanggan->pelanggan_id}\n"
            . '🔹 *Jumlah Tagihan:* Rp' . number_format($jumlahTagihan, 0, ',', '.') . "\n"
            . '🔹 *Jatuh Tempo:* ' . Carbon::parse($pelanggan->pembayaran_selanjutnya)->format('d M Y') . "\n\n"
            . "Untuk mengaktifkan kembali layanan, silakan segera lakukan pembayaran melalui tautan berikut:\n\n"
            . "✅ *Bayar Sekarang:* {$pelanggan->link_pembayaran}\n\n"
            . "Jika Anda sudah melakukan pembayaran atau membutuhkan bantuan, silakan hubungi layanan pelanggan kami.\n\n"
            . "📞 *Layanan Pelanggan:* 08XXXXXXXXXX\n\n"
            . 'Kami menghargai perhatian dan kerja sama Anda.';
    }

    private function sendWhatsAppNotification($nomor, $pesan)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $nomor,
                'message' => $pesan,
            ]);

            \Log::info("📨 Notifikasi terkirim ke {$nomor}: " . $response->body());
        } catch (\Exception $e) {
            \Log::error("❌ Gagal kirim notifikasi ke {$nomor}: " . $e->getMessage());
        }
    }

    private function isolirPelanggan($pelanggan)
    {
        //dd($pelanggan);
        $router = Mikrotik::where('router_id', $pelanggan->router_id)->first();

        if (!$router) {
            \Log::error('Router tidak ditemukan untuk pelanggan ID: ' . $pelanggan->id);
            return;
        }

        try {
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id:' . $router->port_api,
                'user' => $router->username,
                'pass' => $router->password,
            ]);

            $querySecret = new Query('/ppp/secret/print');
            $secretUsers = $client->query($querySecret)->read();

            foreach ($secretUsers as $user) {
                if ($user['name'] === trim($pelanggan->akun_pppoe)) {
                    // 1️⃣ Ubah profile jadi isolir-biller
                    $updateProfileQuery = new Query('/ppp/secret/set');
                    $updateProfileQuery->equal('.id', $user['.id']);
                    $updateProfileQuery->equal('profile', 'isolir-biller');
                    $client->query($updateProfileQuery)->read();

                    \Log::info('✅ Pelanggan ID: ' . $pelanggan->id . ' profile PPPoE diubah ke isolir-biller.');

                    // 2️⃣ Cek active connection
                    $queryActive = new Query('/ppp/active/print');
                    $activeUsers = $client->query($queryActive)->read();

                    foreach ($activeUsers as $active) {
                        if ($active['name'] === trim($pelanggan->akun_pppoe)) {
                            // 3️⃣ Hapus koneksi aktif
                            $removeActive = new Query('/ppp/active/remove');
                            $removeActive->equal('.id', $active['.id']);
                            $client->query($removeActive)->read();

                            \Log::info('⚡ Koneksi aktif pelanggan ID: ' . $pelanggan->id . ' (username: ' . $pelanggan->akun_pppoe . ') telah diputus.');
                        }
                    }

                    return;
                }
            }

            \Log::warning("⚠️ Akun PPPoE '{$pelanggan->akun_pppoe}' tidak ditemukan di router {$router->ip_address}.");
        } catch (\Exception $e) {
            \Log::error("❌ Gagal mengisolir pelanggan ID {$pelanggan->id}: " . $e->getMessage());
        }
    }
}

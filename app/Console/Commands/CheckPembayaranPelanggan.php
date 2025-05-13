<?php

namespace App\Console\Commands;

use App\Models\Mikrotik;
use App\Models\Pelanggan;  // Pastikan model Pelanggan di-import
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use RouterOS\Client;
use RouterOS\Query;

class CheckPembayaranPelanggan extends Command
{
    /**
     * Nama dan signature dari command.
     *
     * @var string
     */
    protected $signature = 'pelanggan:check-pembayaran';

    /**
     * Deskripsi command.
     *
     * @var string
     */
    protected $description = 'Cek pelanggan yang melewati batas tanggal pembayaran dan kirim notifikasi WhatsApp';

    /**
     * Token API Fonnte.
     *
     * @var string
     */
    protected $token = 'g3ZXCoCHeR1y75j4xJoz';

    /**
     * Eksekusi command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now();
        $minus7days = $today->copy()->addDays(7);
        $batasIsolasi = $today->copy()->subDays(1);

        \Log::info("Tanggal sekarang: {$today->toDateString()}");
        \Log::info("Batas pembayaran dalam 7 hari: {$minus7days->toDateString()}");

        // ========== Reset Status untuk Pelanggan dengan Tanggal Pembayaran Hari Ini ==========
        Pelanggan::whereDate('pembayaran_selanjutnya', $today)
            ->update([
                'status_pembayaran' => 'Belum Dibayar',
                'notified' => false,
                'isolated' => false
            ]);

        \Log::info('Reset status pembayaran untuk pelanggan dengan tanggal pembayaran hari ini.');

        // ========== Notifikasi Pembayaran ==========

        $pelangganHarusBayar = Pelanggan::where('pembayaran_selanjutnya', '<=', $minus7days)
            ->where('status_pembayaran', 'Belum Dibayar')
            ->where(function ($query) use ($today) {
                $query
                    ->whereNull('notified_at')
                    ->orWhereDate('notified_at', '<>', $today);  // pastikan bukan hari ini
            })
            ->get();

        foreach ($pelangganHarusBayar as $pelanggan) {
            // 1. Kirim Notifikasi WA
            \Log::info('Mengirim notifikasi pembayaran ke: ' . $pelanggan->id . ' - ' . $pelanggan->nomor_telepon);

            $pesan = $this->generateNotificationMessage($pelanggan);
            $this->sendWhatsAppNotification($pelanggan->nomor_telepon, $pesan);

            // 2. Update status notified
            $pelanggan->update([
                'notified' => true,
                'notified_at' => now(),
            ]);

            // 3. Masukkan ke tabel unpaid_invoices jika belum ada
            $sudahAda = \App\Models\UnpaidInvoice::where('pelanggan_id', $pelanggan->id)
                ->where('sudah_dibayar', false)
                ->exists();

            if (!$sudahAda) {
                // Generate invoice ID
                $prefix = 'INV-' . now()->format('Ymd') . '-';
                $lastId = \App\Models\UnpaidInvoice::whereDate('created_at', now())->count() + 1;
                $invoiceId = $prefix . str_pad($lastId, 4, '0', STR_PAD_LEFT);

                \App\Models\UnpaidInvoice::create([
                    'invoice_id' => $invoiceId,
                    'pelanggan_id' => $pelanggan->id,
                    'jatuh_tempo' => $pelanggan->pembayaran_selanjutnya ?? now(),
                    'sudah_dibayar' => false
                ]);

                \Log::info("✅ Invoice belum bayar dicatat: $invoiceId untuk pelanggan ID: " . $pelanggan->id);
            }
        }

        // ========== Logika Isolasi ==========
        $pelangganIsolasi = Pelanggan::where('pembayaran_selanjutnya', '<=', $batasIsolasi)
            ->where('status_pembayaran', 'Belum Dibayar')
            ->where('isolated', false)  // Hanya pelanggan yang belum diisolasi
            ->get();

        foreach ($pelangganIsolasi as $pelanggan) {
            \Log::info('Mengisolasi pelanggan: ' . $pelanggan->id . ' - ' . $pelanggan->akun_pppoe);

            $pesanIsolasi = $this->generateIsolationMessage($pelanggan);
            $this->sendWhatsAppNotification($pelanggan->nomor_telepon, $pesanIsolasi);

            $this->isolirPelanggan($pelanggan);

            $pelanggan->update(['isolated' => true]);  // Tandai pelanggan sudah diisolasi
        }

        $this->info('Pengecekan pembayaran selesai. Notifikasi dan isolasi telah diproses jika diperlukan.');
    }

    /** Membuat pesan notifikasi pembayaran. */

    /**
     * Membuat pesan notifikasi pembayaran.
     */
    private function generateNotificationMessage($pelanggan)
    {
        $jumlahTagihan = optional($pelanggan->paket)->harga_paket ?? 0;
    
        // Ambil invoice yang belum dibayar dari tabel UnpaidInvoice
        $invoice = \App\Models\UnpaidInvoice::where('pelanggan_id', $pelanggan->id)
            ->where('sudah_dibayar', false)
            ->latest()
            ->first();
    
        $invoiceId = $invoice ? $invoice->invoice_id : 'INV-UNKNOWN';
    
        return "📢 *Pemberitahuan Tagihan Internet*\n\n"
            . "Yth. *{$pelanggan->nama_pelanggan}*,\n\n"
            . "Kami informasikan bahwa tagihan layanan internet Anda telah terbit dengan rincian sebagai berikut:\n\n"
            . "🔹 *Invoice ID:* {$invoiceId}\n"
            . "🔹 *ID Pelanggan:* {$pelanggan->pelanggan_id}\n"
            . "🔹 *Jumlah Tagihan:* Rp" . number_format($jumlahTagihan, 0, ',', '.') . "\n"
            . "🔹 *Jatuh Tempo:* " . Carbon::parse($pelanggan->pembayaran_selanjutnya)->format('d M Y') . "\n\n"
            . "Mohon untuk segera melakukan pembayaran sebelum jatuh tempo guna menghindari gangguan layanan.\n\n"
            . "✅ *Bayar Sekarang:* \n\n"
            . "Jika Anda sudah melakukan pembayaran atau memerlukan bantuan, silakan hubungi kami.\n\n"
            . "📞 *Layanan Pelanggan:* 08XXXXXXXXXX\n\n"
            . "Terima kasih atas kepercayaan Anda menggunakan layanan kami.";
    }
    

    private function generateIsolationMessage($pelanggan)
    {
        $jumlahTagihan = optional($pelanggan->paket)->harga_paket ?? 0;

        return "⚠️ *Pemberitahuan Pembatasan Layanan*\n\n"
            . "Yth. *{$pelanggan->nama_pelanggan}*,\n\n"
            . "Kami informasikan bahwa layanan internet Anda sementara *dibatasi* karena belum diterimanya pembayaran tagihan dengan rincian berikut:\n\n"
            . "🔹 *ID Pelanggan:* {$pelanggan->pelanggan_id}\n"
            . '🔹 *Jumlah Tagihan:* Rp' . number_format($jumlahTagihan, 0, ',', '.') . "\n"
            . '🔹 *Jatuh Tempo:* ' . Carbon::parse($pelanggan->pembayaran_selanjutnya)->format('d M Y') . "\n\n"
            . "Untuk mengaktifkan kembali layanan, silakan segera lakukan pembayaran melalui tautan berikut:\n\n"
            . "✅ *Bayar Sekarang:* {$pelanggan->link_pembayaran}\n\n"
            . "Jika Anda sudah melakukan pembayaran atau membutuhkan bantuan, silakan hubungi layanan pelanggan kami.\n\n"
            . "📞 *Layanan Pelanggan:* 08XXXXXXXXXX\n\n"
            . "Kami menghargai perhatian dan kerja sama Anda.\n\n";
    }

    private function isolirPelanggan($pelanggan)
    {
        $router = Mikrotik::where('router_id', $pelanggan->router_id)->first();

        if (!$router) {
            \Log::error('Router tidak ditemukan untuk pelanggan ID: ' . $pelanggan->id);
            return;
        }

        try {
            $client = new Client([
                'host' => 'id-1.aqtnetwork.my.id',
                'user' => $router->username,
                'pass' => $router->password,
                'port' => (int) $router->port_api,
            ]);

            $querySecret = new Query('/ppp/secret/print');
            $secretUsers = $client->query($querySecret)->read();

            $updated = false;
            foreach ($secretUsers as $user) {
                if ($user['name'] === trim($pelanggan->akun_pppoe)) {
                    $updateProfileQuery = new Query('/ppp/secret/set');
                    $updateProfileQuery->equal('.id', $user['.id']);
                    $updateProfileQuery->equal('profile', 'isolir-biller');
                    $client->query($updateProfileQuery)->read();
                    \Log::info('Profil PPPoE berhasil diubah: ' . $pelanggan->akun_pppoe);
                    $updated = true;
                    break;
                }
            }

            if (!$updated) {
                \Log::error('Akun PPPoE tidak ditemukan di /ppp/secret: ' . $pelanggan->akun_pppoe);
            }

            $queryActive = new Query('/ppp/active/print');
            $pppActiveConnections = $client->query($queryActive)->read();

            foreach ($pppActiveConnections as $connection) {
                if ($connection['name'] === trim($pelanggan->akun_pppoe)) {
                    $removeActiveQuery = new Query('/ppp/active/remove');
                    $removeActiveQuery->equal('.id', $connection['.id']);
                    $client->query($removeActiveQuery)->read();
                    \Log::info('Koneksi PPPoE berhasil dihapus: ' . $pelanggan->akun_pppoe);
                    break;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error saat mengisolasi pelanggan ID: ' . $pelanggan->id . ' - ' . $e->getMessage());
        }
    }

    protected function sendWhatsAppNotification($nomor, $pesan)
    {
        $nomor = str_replace('+62', '', $nomor);
        $nomor = ltrim($nomor, '0');

        $this->info("Mengirim WhatsApp ke {$nomor} dengan pesan: {$pesan}");

        $response = Http::withHeaders([
            'Authorization' => $this->token
        ])->post('https://api.fonnte.com/send', [
            'target' => $nomor,
            'message' => $pesan,
            'countryCode' => '62',
        ]);

        if ($response->successful()) {
            $this->info("Notifikasi WhatsApp berhasil dikirim ke {$nomor}.");
        } else {
            $this->error("Gagal mengirim notifikasi ke {$nomor}. Error: " . $response->body());
        }
    }
}

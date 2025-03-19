<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pelanggan; // Pastikan model Pelanggan di-import
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

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
         $today = Carbon::now(); // Tanggal saat ini
         $minus7days = $today->copy()->addDays(7); // Batas 7 hari ke depan untuk isolasi
     
         \Log::info("Tanggal sekarang: {$today->toDateString()}");
         \Log::info("Batas pembayaran dalam 7 hari: {$minus7days->toDateString()}");
     
         // Cek pelanggan yang harus membayar dalam 7 hari sebelum jatuh tempo
         $pelangganHarusBayar = Pelanggan::where('pembayaran_selanjutnya', '<=', $minus7days)
             ->where('status_pembayaran', 'Belum Dibayar')
             ->get();
     
         if ($pelangganHarusBayar->isEmpty()) {
             \Log::info("Tidak ada pelanggan yang harus dibayar dalam 7 hari.");
         }
     
         foreach ($pelangganHarusBayar as $pelanggan) {
             \Log::info("Pelanggan yang akan menerima notifikasi: " . $pelanggan->id . " - " . $pelanggan->nomor_telepon);
     
             // Update status menjadi 'Belum Dibayar' dan set notified
             $pelanggan->update([
                 'notified' => true,
                 'status_pembayaran' => 'Belum Dibayar'
             ]);
     
             // Kirim pesan WhatsApp
             $this->sendWhatsAppNotification($pelanggan->nomor_telepon, 'pembayaran_selanjutnya');
         }
     
         $this->info('Pengecekan pembayaran selesai. Notifikasi dikirim jika diperlukan.');
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
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    protected $table = 'pelanggan';
    protected $guarded = [];

    public function paket()
    {
        return $this->belongsTo(PaketPppoe::class, 'kode_paket', 'kode_paket');
    }

/**
     * Update status pembayaran setelah pembayaran berhasil.
     */
    public function updateTanggalPembayaran()
    {
        $this->update([
            'pembayaran_selanjutnya' => now(),
            'pembayaran_yang_akan_datang' => now()->addMonth(),
            'status_pembayaran' => 'Sudah Dibayar',
            'notified' => false, // Reset notifikasi untuk bulan berikutnya
        ]);

        // Kirim konfirmasi pembayaran berhasil
        $this->sendPaymentConfirmation();
    }

    /**
     * Kirim notifikasi WhatsApp konfirmasi pembayaran sukses
     */
    public function sendPaymentConfirmation()
    {
        $nomor = str_replace('+62', '', $this->nomor_telepon);
        $nomor = ltrim($nomor, '0');

        $pesan = "Terima kasih! Pembayaran Anda telah berhasil. Layanan Anda tetap aktif. Sampai jumpa di bulan berikutnya!";

        Http::withHeaders([ 
            'Authorization' => 'g3ZXCoCHeR1y75j4xJoz'
        ])->post('https://api.fonnte.com/send', [
            'target' => $nomor,
            'message' => $pesan,
            'countryCode' => '62',
        ]);
    }
}

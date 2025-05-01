<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappHelper
{
    protected static $token = 'g3ZXCoCHeR1y75j4xJoz'; // Ganti dengan token Fonnte kamu

    public static function sendWa($nomor, $pesan)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => self::$token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $nomor,
                'message' => $pesan,
                'countryCode' => '62', // opsional, bisa dihilangkan kalau nomor sudah format 62xxx
            ]);

            Log::info("📨 Notifikasi terkirim ke {$nomor}: " . $response->body());
        } catch (\Exception $e) {
            Log::error("❌ Gagal kirim notifikasi ke {$nomor}: " . $e->getMessage());
        }
    }
}

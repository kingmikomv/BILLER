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
           $response = Http::post('http://localhost:3000/api/send', [
                'session_id' => 'main-session',
                'number' => $nomor,
                'message' => $pesan,
            ]);

            Log::info("📨 Notifikasi terkirim ke {$nomor}: " . $response);
        } catch (\Exception $e) {
            Log::error("❌ Gagal kirim notifikasi ke {$nomor}: " . $e->getMessage());
        }
    }
}

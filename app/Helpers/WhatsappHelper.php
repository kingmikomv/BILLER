<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\MessageTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsappHelper
{
    /**
     * Kirim WA menggunakan template dari database
     * 
     * @param string $nomor
     * @param string $templateName
     * @param array $data
     * @param int|null $userId
     * @param string|null $sessionId
     * 
     * @return void
     */
    public static function sendWaTemplate($nomor, $templateName, array $data = [], $userId = null, $sessionId)
    {
        try {
            // Ambil template berdasarkan user_id dan name
            $template = MessageTemplate::where('name', $templateName);

            $cariUnique = User::where('unique_member', $sessionId)->first();
            if ($cariUnique) {
                $template->where('user_id', $cariUnique->id);
            }

            $template = $template->first();
            Log::info($sessionId);

            if (!$template) {
                Log::warning("❗ Template '{$templateName}' tidak ditemukan.");
                return;
            }

            // Replace placeholder {{key}} dengan value dari $data
            $pesan = $template->content;
            foreach ($data as $key => $value) {
                $pesan = str_replace('[' . $key . ']', $value, $pesan);
            }

            Log::info($data);

            // Kirim WA
            $response = Http::post('http://localhost:3000/api/send', [
                'session_id' => $sessionId,
                'number' => $nomor,
                'message' => $pesan,
            ]);
Log::info('SEND WA PARAMETER', [
    'session_id' => $sessionId,
    'number' => $nomor,
    'message' => $pesan
]);
            if ($response->successful()) {
                Log::info("📨 Pesan template '{$templateName}' terkirim ke {$nomor}");
            } else {
                Log::warning("⚠️ Gagal kirim ke {$nomor}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("❌ Gagal kirim pesan '{$templateName}' ke {$nomor}: " . $e->getMessage());
        }
    }
}

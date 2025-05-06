<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Tagihan;
use Carbon\Carbon;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Ambil JSON mentah dari body request
        $raw = file_get_contents('php://input');
        Log::info('Webhook RAW', ['raw' => $raw]);

        $payload = json_decode($raw, true) ?? [];
        Log::info('Webhook PARSED', $payload);

        $orderId = $payload['order_id'] ?? null;
        $status = $payload['transaction_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;
        $paymentChannel = $payload['va_numbers'][0]['bank'] ?? ($payload['permata_va_number'] ?? ($payload['payment_code'] ?? null));
        $settlementTime = $payload['settlement_time'] ?? null;

        if (!$orderId || !$status) {
            Log::warning('Webhook tidak memiliki order_id atau status');
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $tagihan = Tagihan::where('invoice_id', $orderId)->first();

        if (!$tagihan) {
            Log::warning("Tagihan dengan order_id {$orderId} tidak ditemukan.");
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        switch ($status) {
            case 'settlement':
                $tagihan->update([
                    'status' => 'Lunas',
                    'payment_method' => $paymentType,
                    'payment_channel' => $paymentChannel,
                    'midtrans_paid_at' => $settlementTime ?? Carbon::now(),
                    'midtrans_transaction_status' => $status,
                ]);
                Log::info("Tagihan {$orderId} berhasil dibayar.");
                break;

            case 'pending':
                $tagihan->update([
                    'midtrans_transaction_status' => $status,
                ]);
                Log::info("Tagihan {$orderId} dalam status pending.");
                break;

            case 'expire':
            case 'cancel':
            case 'deny':
                $tagihan->update([
                    'midtrans_transaction_status' => $status,
                ]);
                Log::info("Tagihan {$orderId} gagal dengan status {$status}.");
                break;

            default:
                Log::info("Status transaksi tidak dikenal: {$status}");
                break;
        }

        return response()->json(['message' => 'Webhook processed'], 200);
    }
}

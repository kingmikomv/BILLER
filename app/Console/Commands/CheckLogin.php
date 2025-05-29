<?php

namespace App\Console;

use Carbon\Carbon;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckLogin
{
    public static function handle()
    {
        Log::info('[CheckLogin] Process started at ' . now());

        try {
            $now = Carbon::now();

            // Ambil username yang sudah login
            $loggedInUsers = DB::connection('freeradius')
                ->table('radacct')
                ->whereNotNull('acctstarttime')
                ->pluck('username')
                ->unique();

            Log::info('[CheckLogin] Logged-in users:', $loggedInUsers->toArray());

            // Ambil voucher yang cocok dan belum digunakan
            $vouchers = Voucher::whereIn('username', $loggedInUsers)
                ->where('status', '!=', 'used')
                ->get();

            Log::info('[CheckLogin] Matching vouchers:', $vouchers->pluck('username')->toArray());

            foreach ($vouchers as $voucher) {
                $duration = DB::connection('freeradius')
                    ->table('radreply')
                    ->where('username', $voucher->username)
                    ->where('attribute', 'Session-Timeout')
                    ->value('value');

                if (!$duration || !is_numeric($duration)) {
                    continue;
                }

                $voucher->update([
                    'status'     => 'used',
                    'login_at'   => $now,
                    'expired_at' => $now->copy()->addSeconds($duration),
                    'delete_at'  => $now->copy()->addDays(7),
                ]);

                Log::info("[CheckLogin] Updated voucher: {$voucher->username}");
            }

            // Tambahan: update voucher yang sudah expired
            $expiredVouchers = Voucher::where('status', 'used')
                ->whereNotNull('expired_at')
                ->where('expired_at', '<', $now)
                ->get();

            foreach ($expiredVouchers as $expired) {
                $expired->update([
                    'status' => 'expired',
                ]);

                Log::info("[CheckLogin] Expired voucher: {$expired->username}");
            }

            Log::info('[CheckLogin] Process finished successfully.');
        } catch (\Exception $e) {
            Log::error('[CheckLogin] Error: ' . $e->getMessage());
        }
    }
}

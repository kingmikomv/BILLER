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

            /**
             * 1. Ambil user yang sedang login dari radacct (acctstarttime != null dan acctstoptime == null)
             */
            $loggedInUsers = DB::connection('freeradius')
                ->table('radacct')
                ->whereNotNull('acctstarttime')
                ->whereNull('acctstoptime')
                ->pluck('username')
                ->unique();

            Log::info('[CheckLogin] Logged-in users:', $loggedInUsers->toArray());

            /**
             * 2. Update voucher yang baru login (status belum 'used')
             */
            $vouchers = Voucher::whereIn('username', $loggedInUsers)
                ->where('status', '!=', 'used')
                ->get();

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
                    'status' => 'used',
                    'login_at' => $now,
                    'expired_at' => $now->copy()->addSeconds($duration),
                    'delete_at' => $now->copy()->addDays(7),
                ]);

                Log::info("[CheckLogin] Voucher marked as 'used': {$voucher->username}");
            }

            /**
             * 3. Update voucher yang sudah expired
             */
            $expiredVouchers = Voucher::where('status', 'used')
                ->whereNotNull('expired_at')
                ->where('expired_at', '<', $now)
                ->get();

            foreach ($expiredVouchers as $expired) {
                $expired->update([
                    'status' => 'expired',
                ]);

                Log::info("[CheckLogin] Voucher expired: {$expired->username}");

                // Optional: Hapus user di Mikrotik via API
                // if (Mikrotik::isConnected()) {
                //     Mikrotik::removePPPSecret($expired->username);
                //     Log::info("[CheckLogin] Removed expired user from MikroTik: {$expired->username}");
                // }
            }

            /**
             * 4. Deteksi user yang sudah logout berdasarkan acctstoptime yang baru masuk (10 menit terakhir)
             */
            $loggedOutUsers = DB::connection('freeradius')
                ->table('radacct')
                ->select('username', 'acctstoptime')
                ->whereNotNull('acctstoptime')
                ->where('acctstoptime', '>=', $now->copy()->subMinutes(10)) // ambil data logout 10 menit terakhir
                ->get();

            foreach ($loggedOutUsers as $user) {
                $updated = Voucher::where('username', $user->username)
                    ->where('status', 'used')
                    ->update([
                        'status' => 'logout',
                    ]);

                if ($updated) {
                    Log::info("[CheckLogin] User logged out: {$user->username} at {$user->acctstoptime}");
                }
            }

            Log::info('[CheckLogin] Process finished successfully.');
        } catch (\Exception $e) {
            Log::error('[CheckLogin] Error: ' . $e->getMessage());
        }
    }

}

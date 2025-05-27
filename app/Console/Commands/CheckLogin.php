<?php

namespace App\Console;

use Illuminate\Support\Facades\DB;
use App\Models\Voucher;

class CheckLogin
{
    public static function handle()
    {
        // Ambil username yang sudah login dari radacct
        $loggedInUsers = DB::connection('freeradius')
            ->table('radacct')
            ->whereNotNull('acctstarttime')
            ->pluck('username')
            ->unique();

        // Update status voucher ke 'used' jika belum
        $updated = Voucher::whereIn('username', $loggedInUsers)
            ->where('status', '!=', 'used')
            ->update(['status' => 'used']);

        if ($updated > 0) {
            \Log::info("[$updated] voucher updated to 'used'.");
        }
    }
}

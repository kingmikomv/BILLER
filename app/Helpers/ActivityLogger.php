<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Menyimpan log aktivitas ke database
     *
     * @param string $activity  Aktivitas yang dilakukan user
     * @param string|null $target  Target aktivitas (misalnya pelanggan, transaksi, dll.)
     */
    public static function log($activity, $target = null)
    {
        if (Auth::check()) {
            ActivityLog::create([
                'user_id'  => Auth::id(),
                'role'     => Auth::user()->role, // Pastikan kolom role ada di tabel users
                'activity' => $activity,
                'target'   => $target,
            ]);
        }
    }
}

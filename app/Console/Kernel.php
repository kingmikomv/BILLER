<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\CheckLogin;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule generate invoice harian
        $schedule->command('generate:invoice')->dailyAt('00:00');

        // Kirim invoice via WA, 30 menit setelah generate
        $schedule->command('kirim:invoice-wa')->dailyAt('01:48');

        // Jalankan pengecekan voucher login setiap menit
        $schedule->call(function () {
        try {
            CheckLogin::handle();
            \Log::info('CheckLogin::handle() sukses jalan.');
        } catch (\Throwable $e) {
            \Log::error('Error di CheckLogin: ' . $e->getMessage());
        }
    })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

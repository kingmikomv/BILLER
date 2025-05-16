<?php

namespace App\Console;

use Carbon\Carbon;
use Xendit\Xendit;
use Xendit\Invoice;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use Illuminate\Support\Str;
use App\Models\BillingSeting;
use App\Helpers\WhatsappHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
  
    
     protected function schedule(Schedule $schedule){
    $schedule->command('generate:invoice')->dailyAt('01:48');
        $schedule->command('kirim:invoice-wa')->dailyAt('01:48'); // Kirim 30 menit setelah generate

     }
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

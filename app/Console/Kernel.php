<?php

namespace App\Console;

use App\Models\BillingSeting;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        // Ambil semua pelanggan
        $pelanggans = Pelanggan::all();
        $prota_envable = BillingSeting::first(); // Ambil setting prorata dari database
        foreach ($pelanggans as $pelanggan) {
            $bulanIni = now(); // Bulan ini
            $bulanDepan = now()->addMonth(); // Bulan depan

            // Cek apakah pelanggan prabayar atau pascabayar
            $statusPembayaran = $pelanggan->metode_pembayaran;
            Log::info('Status Pembayaran: ' . $statusPembayaran);
            
            // Menentukan jumlah hari untuk perhitungan prorata
            $jumlahHariBulanIni = $bulanIni->daysInMonth;
            $hariIni = $bulanIni->day;
            $prorata = 0;

            // Cek jika status pembayaran prabayar
            if ($statusPembayaran === 'Prabayar') {
                // Jika pelanggan prabayar, tagihan untuk bulan depan
                $jumlahHariDalamBulanDepan = $bulanDepan->daysInMonth;

                // Menghitung prorata untuk bulan depan
                if ($pelanggan->tanggal_mulai) {
                    // Jika pelanggan mulai pada pertengahan bulan, hitung prorata
                    $hariMulai = $pelanggan->tanggal_mulai->day;
                    $prorata = $pelanggan->paket->harga_paket / $jumlahHariBulanIni * ($jumlahHariBulanIni - $hariMulai + 1);
                }

                // Tagihan bulan depan untuk pelanggan prabayar
                Tagihan::firstOrCreate(
                    [
                        'pelanggan_id' => $pelanggan->id,
                        'tanggal_jatuh_tempo' => $bulanDepan->endOfMonth(),
                    ],
                    [
                        'prorata' => $prota_envable->prorata_enable, // Ambil status prorata dari setting
                        'tanggal_generate' => now(),
                        'jumlah_tagihan' => $prorata ? $prorata : $pelanggan->paket->harga_paket, // Gunakan prorata jika ada
                        'status' => 'Belum Lunas',
                        'tanggal_pembayaran' => now(), // Tentukan tanggal pembayaran
                    ]
                );
            } elseif ($statusPembayaran === 'Pascabayar') {
                // Untuk pelanggan pascabayar, tagihan untuk bulan ini
                // Menghitung prorata jika pelanggan menggunakan layanan setengah bulan
                if ($pelanggan->tanggal_mulai) {
                    // Prorata berdasarkan tanggal mulai pelanggan
                    $prorata = $pelanggan->paket->harga_paket / $jumlahHariBulanIni * $hariIni;
                }

                // Tagihan bulan ini untuk pelanggan pascabayar
                Tagihan::firstOrCreate(
                    [
                        'pelanggan_id' => $pelanggan->id,
                        'tanggal_jatuh_tempo' => $bulanIni->endOfMonth(),
                    ],
                    [
                        'prorata' => $prota_envable->prorata_enable, // Ambil status prorata dari setting

                        'tanggal_generate' => now(),
                        'jumlah_tagihan' => $prorata ? $prorata : $pelanggan->paket->harga_paket, // Gunakan prorata jika ada
                        'status' => 'Belum Lunas',
                        'tanggal_pembayaran' => now(), // Tentukan tanggal pembayaran
                    ]
                );
            }
        }
    })->monthlyOn(1, '00:00'); // Jadwal tugas dijalankan setiap tanggal 1 jam 00:00
}



    

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

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
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
{
    // 1. Schedule Generate Tagihan Bulanan (Normal)
    $schedule->call(function () {
        $pelanggans = Pelanggan::all();
        $billingSetting = BillingSeting::first();
        $sekarang = now();

        foreach ($pelanggans as $pelanggan) {
            if (!$pelanggan->tanggal_daftar) {
                continue;
            }

            $tanggalGenerate = $sekarang->copy()->setDay(
                min(Carbon::parse($pelanggan->tanggal_daftar)->day, $sekarang->copy()->endOfMonth()->day)
            );

            if ($billingSetting->generate_invoice_mode == 'dimajukan' && $billingSetting->dimajukan_hari) {
                $tanggalGenerate = $tanggalGenerate->subDays($billingSetting->dimajukan_hari);
            }

            $bulanDaftar = Carbon::parse($pelanggan->tanggal_daftar)->format('Y-m');
            $bulanSekarang = $sekarang->format('Y-m');

            if ($bulanDaftar == $bulanSekarang) {
                continue;
            }

            if ($pelanggan->metode_pembayaran == 'prabayar') {
                $tagihanBelumLunas = Tagihan::where('pelanggan_id', $pelanggan->id)
                    ->where('status', 'Belum Lunas')
                    ->exists();

                if ($tagihanBelumLunas) {
                    continue;
                }
            }

            $tagihan = Tagihan::firstOrCreate(
                [
                    'pelanggan_id' => $pelanggan->id,
                    'tanggal_jatuh_tempo' => $tanggalGenerate->copy()->addDays($billingSetting->default_jatuh_tempo_hari),
                ],
                [
                    'invoice_id' => 'INV-' . date('Ymd') . strtoupper(Str::random(6)),
                    'prorata' => 0,
                    'tanggal_generate' => $tanggalGenerate,
                    'jumlah_tagihan' => $pelanggan->paket->harga_paket,
                    'status' => 'Belum Lunas',
                    'tanggal_pembayaran' => $tanggalGenerate,
                ]
            );

            // ✅ Tambahan: Buat invoice di Xendit
            if (empty($tagihan->link_pembayaran)) {
                try {
                    Xendit::setApiKey(config('services.xendit.api_key'));

                    $params = [
                        'external_id' => $tagihan->invoice_id,
                        'payer_email' => $pelanggan->email ?? 'test@example.com',
                        'description' => 'Pembayaran tagihan pelanggan #' . $pelanggan->id,
                        'amount' => $tagihan->jumlah_tagihan,
                        'invoice_duration' => 86400,
                        'customer' => [
                            'given_names' => $pelanggan->nama ?? 'Customer',
                            'email' => $pelanggan->email ?? 'test@example.com',
                        ],
                    ];

                    $invoice = Invoice::create($params);

                    $tagihan->update([
                        'xendit_invoice_id' => $invoice['id'],
                        'link_pembayaran' => $invoice['invoice_url'],
                        'xendit_status' => $invoice['status'],
                    ]);
                } catch (\Exception $e) {
                    Log::error('Gagal generate invoice Xendit: ' . $e->getMessage());
                }
            }
        }
    })->monthlyOn(1, '00:00');

    // 2. Schedule Kirim Pesan WhatsApp
    $schedule->call(function () {
        $tagihans = Tagihan::whereDate('tanggal_generate', now()->toDateString())
            ->where('status', 'Belum Lunas')
            ->get();

        Log::info('Jumlah tagihan untuk dikirim WA hari ini: ' . $tagihans->count());

        foreach ($tagihans as $tagihan) {
            $pelanggan = $tagihan->pelanggan;
            if ($pelanggan && $pelanggan->nomor_telepon) {
                $invoice = $tagihan->invoice_id ?? 'N/A';
                $jumlah = number_format($tagihan->jumlah_tagihan, 0, ',', '.');
                $jatuhTempo = Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d-m-Y');
                $linkPembayaran = $tagihan->link_pembayaran ?? '';

                $pesan = "Yth. {$pelanggan->nama},\n\n"
                    . "Kami informasikan bahwa tagihan layanan internet Anda telah diterbitkan dengan rincian sebagai berikut:\n\n"
                    . "*Nomor Invoice:* {$invoice}\n"
                    . "*Jumlah Tagihan:* Rp {$jumlah}\n"
                    . "*Jatuh Tempo:* {$jatuhTempo}\n\n"
                    . (!empty($linkPembayaran) ? "Untuk melakukan pembayaran, silakan klik link berikut:\n{$linkPembayaran}\n\n" : "")
                    . "Mohon segera melakukan pembayaran sebelum jatuh tempo untuk menghindari gangguan layanan.\n\n"
                    . "Terima kasih atas kepercayaan Anda.\n\n"
                    . "Salam,\n"
                    . "Tim Layanan Pelanggan";

                $nomorTelepon = ltrim($pelanggan->nomor_telepon, '0');
                $nomorTelepon = '62' . $nomorTelepon;
                WhatsappHelper::sendWa($nomorTelepon, $pesan);

                Log::info("Pesan WA dikirim ke: {$pelanggan->nomor_telepon}");
            }
        }
    })->dailyAt('08:00');

    // 3. Schedule Isolir Pelanggan
    $schedule->call(function () {
        $tagihans = Tagihan::where('status', 'Belum Lunas')
            ->whereDate('tanggal_jatuh_tempo', '<', now()->toDateString())
            ->get();

        foreach ($tagihans as $tagihan) {
            $pelanggan = $tagihan->pelanggan;
            if ($pelanggan) {
                $pelanggan->status_internet = 'Isolir';
                $pelanggan->save();

                Log::info("Pelanggan {$pelanggan->nama} diisolir karena lewat jatuh tempo.");

                if ($pelanggan->nomor_telepon) {
                    $invoice = $tagihan->invoice_id ?? 'N/A';
                    $jumlah = number_format($tagihan->jumlah_tagihan, 0, ',', '.');
                    $jatuhTempo = Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d-m-Y');

                    $pesan = "Yth. {$pelanggan->nama},\n\n"
                        . "Kami informasikan bahwa layanan internet Anda *sementara dinonaktifkan* karena keterlambatan pembayaran tagihan berikut:\n\n"
                        . "*Nomor Invoice:* {$invoice}\n"
                        . "*Jumlah Tagihan:* Rp {$jumlah}\n"
                        . "*Jatuh Tempo:* {$jatuhTempo}\n\n"
                        . "Segera lakukan pembayaran agar layanan dapat diaktifkan kembali.\n\n"
                        . "Jika sudah melakukan pembayaran, abaikan pesan ini.\n\n"
                        . "Terima kasih atas perhatian Anda.\n\n"
                        . "Salam,\n"
                        . "Tim Layanan Pelanggan";

                    $nomorTelepon = ltrim($pelanggan->nomor_telepon, '0');
                    $nomorTelepon = '62' . $nomorTelepon;
                    WhatsappHelper::sendWa($nomorTelepon, $pesan);
                }
            }
        }
    })->dailyAt('09:00');
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

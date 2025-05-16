<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\DataInvoice;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Str;

class GenerateInvoiceFromTagihan extends Command
{
    protected $signature = 'generate:invoice';
    protected $description = 'Generate invoice dari table tagihan berdasarkan tanggal_generate hari ini';

    public function handle()
    {
        $today = Carbon::today();

        // Ambil semua tagihan dengan tanggal_generate = hari ini
        $tagihans = Tagihan::whereDate('tanggal_generate', $today)->get();

        if ($tagihans->isEmpty()) {
            $this->info('✅ Tidak ada tagihan untuk hari ini.');
            return;
        }

        foreach ($tagihans as $tagihan) {
            $pelanggan = Pelanggan::find($tagihan->pelanggan_id);

            if (!$pelanggan) {
                Log::warning("❌ Pelanggan ID {$tagihan->pelanggan_id} tidak ditemukan.");
                continue;
            }
$user = optional($pelanggan->mikrotik)->user;

if (!$user || $user->role !== 'member') {
    Log::warning("❌ User tidak ditemukan atau bukan member untuk pelanggan ID {$pelanggan->id}.");
    continue;
}

            // ✅ Simpan ke data_invoices (untuk WA Scheduler) meskipun belum lunas
            DataInvoice::updateOrCreate(
                ['unique_member' => $user->unique_member],
    [
        'pelanggan_id'   => $pelanggan->id,
        'tagihan_id'     => $tagihan->id,
        'tanggal_generate' => $tagihan->tanggal_generate,
        'nomor_telepon'  => $pelanggan->nomor_telepon,
        'nominal'        => $tagihan->jumlah_tagihan,
        'status'         => $tagihan->status,
        'status_wa'      => 'pending',
    ]
            );

            // ✅ Buat tagihan bulan depan jika tagihan saat ini sudah Lunas dan Prabayar
            if ($pelanggan->metode_pembayaran === 'Prabayar' && $tagihan->status === 'Lunas') {
                $bulanDepan = $today->copy()->addMonth()->startOfMonth();

                $existingNextMonthTagihan = Tagihan::where('pelanggan_id', $pelanggan->id)
                    ->whereMonth('tanggal_generate', $bulanDepan->month)
                    ->whereYear('tanggal_generate', $bulanDepan->year)
                    ->first();

                if (!$existingNextMonthTagihan) {
                    $invoiceId = 'INV-' . date('Ymd') . strtoupper(Str::random(6));

                    $newTagihan = Tagihan::create([
                        'invoice_id' => $invoiceId,
                        'pelanggan_id' => $pelanggan->id,
                        'tanggal_generate' => $bulanDepan,
                        'tanggal_jatuh_tempo' => $bulanDepan->copy()->addMonth(),
                        'jumlah_tagihan' => $tagihan->jumlah_tagihan,
                        'status' => 'Lunas',
                    ]);

                    DataInvoice::updateOrCreate(
                         ['unique_member' => $user->unique_member],
    [
        'pelanggan_id'   => $pelanggan->id,
        'tagihan_id'     => $tagihan->id,
        'tanggal_generate' => $tagihan->tanggal_generate,
        'nomor_telepon'  => $pelanggan->nomor_telepon,
        'nominal'        => $tagihan->jumlah_tagihan,
        'status'         => $tagihan->status,
        'status_wa'      => 'pending',
    ]
                    );

                    Log::info("✅ Tagihan bulan depan dibuat untuk pelanggan ID {$pelanggan->id}.");
                } else {
                    Log::info("✅ Pelanggan ID {$pelanggan->id} sudah memiliki tagihan bulan depan.");
                }
            }

            // ✅ Buat link pembayaran Midtrans jika belum lunas
            if (
                $tagihan->status === 'Belum Lunas' &&
                (empty($tagihan->link_pembayaran) || $tagihan->midtrans_transaction_status === 'expire')
            ) {
                try {
                    Config::$serverKey = config('services.midtrans.server_key');
                    Config::$isProduction = config('services.midtrans.is_production');
                    Config::$isSanitized = true;
                    Config::$is3ds = true;

                    $params = [
                        'transaction_details' => [
                            'order_id' => $tagihan->invoice_id,
                            'gross_amount' => $tagihan->jumlah_tagihan,
                        ],
                        'customer_details' => [
                            'first_name' => $pelanggan->nama,
                            'phone' => $pelanggan->nomor_telepon,
                        ],
                        'callbacks' => [
                            'finish' => config('app.url'). '/api/invoice/success',
                        ],
                    ];

                    $transaction = Snap::createTransaction($params);

                    $tagihan->update([
                        'midtrans_order_id' => $tagihan->invoice_id,
                        'link_pembayaran' => $transaction->redirect_url,
                        'payment_method' => null,
                        'payment_channel' => null,
                        'midtrans_paid_at' => null,
                        'midtrans_transaction_status' => 'pending',
                    ]);

                    Log::info("✅ Link Midtrans berhasil dibuat untuk invoice {$tagihan->invoice_id}");
                } catch (\Exception $e) {
                    Log::error("❌ Gagal membuat Midtrans invoice untuk {$tagihan->invoice_id}: " . $e->getMessage());
                }
            }
        }

        $this->info('✅ Berhasil memproses ' . $tagihans->count() . ' tagihan dan update ke data_invoices.');
    }
}

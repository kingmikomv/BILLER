<?php
namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\DataInvoice;
use App\Helpers\WhatsappHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class KirimInvoiceWA extends Command
{
    protected $signature = 'kirim:invoice-wa';
    protected $description = 'Kirim invoice dari database ke pelanggan via WhatsApp';

   public function handle()
{
    $invoices = DataInvoice::with('pelanggan') // pastikan eager loading relasi
        ->where('status_wa', 'pending')
        ->whereDate('tanggal_generate', Carbon::today())
        ->get();

    if ($invoices->isEmpty()) {
        Log::info('📭 Tidak ada invoice yang perlu dikirim hari ini.');
        $this->info('Tidak ada invoice yang perlu dikirim hari ini.');
        return;
    }

    foreach ($invoices as $invoice) {
        // Buat data untuk template
       $data = [
    'full_name'        => $invoice->pelanggan->nama_pelanggan ?? 'Pelanggan',
    'uid'              => $invoice->pelanggan->pelanggan_id ?? '-',
    'pppoe_user'       => $invoice->pelanggan->akun_pppoe ?? '-',
    'pppoe_pass'       => $invoice->pelanggan->password_pppoe ?? '-',
    'pppoe_profile'    => $invoice->pelanggan->paket_pppoe ?? '-',
    'no_invoice'       => $invoice->tagihan->invoice_id ?? '-',
    'invoice_date'     => Carbon::parse($invoice->tanggal_generate)->format('d-m-Y'),
    'amount'           => number_format($invoice->jumlah_tagihan, 0, ',', '.'),
    
    // Jika kamu pakai perhitungan PPN, diskon, dan total
    'ppn'              => number_format($invoice->ppn ?? 0, 0, ',', '.'), // pastikan kolomnya ada
    'discount'         => number_format($invoice->diskon ?? 0, 0, ',', '.'),
    'total'            => number_format(($invoice->nominal ?? 0) + ($invoice->ppn ?? 0) - ($invoice->diskon ?? 0), 0, ',', '.'),

    'period'           => Carbon::parse($invoice->tanggal_generate)->isoFormat('MMMM Y'), // e.g. Mei 2025
    'due_date'         => Carbon::parse($invoice->pelanggan->tanggal_jatuh_tempo)->format('d-m-Y'),
'payment_gateway' => route('invoice.cari', ['invoice_id' => $invoice->tagihan->invoice_id ?? '-']),
    'payment_mutasi'   => $pelanggan->metode_pembayaran ?? '-',
    'footer'           => 'Terima kasih telah menggunakan layanan kami.',
];


        // Kirim pesan WA
        WhatsappHelper::sendWaTemplate(
    $invoice->nomor_telepon,
    'Invoice Terbit',
    $data,
    $invoice->pelanggan->user_id ?? null,  // user_id di posisi ke-4
    $invoice->unique_member                // session_id di posisi ke-5
);


        // Log detail pengiriman
        Log::info("✅ Invoice dikirim ke {$invoice->nomor_telepon} dengan data: ", $data);

        // Update status
        $invoice->update([
            'status_wa' => 'sent'
        ]);
    }

    Log::info('📤 Semua invoice berhasil dikirim via WhatsApp.');
    $this->info('Pengiriman invoice via WA selesai.');
}
}

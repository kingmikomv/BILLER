<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique(); // Tambahkan ini

            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade');

            $table->date('tanggal_generate');
            $table->date('tanggal_jatuh_tempo');
            $table->date('tanggal_pembayaran')->nullable();

            $table->integer('jumlah_tagihan');
            $table->boolean('prorata')->default(false);
            $table->enum('status', ['Lunas', 'Belum Lunas', 'Tertunggak'])->default('Belum Lunas');

            // Kolom untuk Midtrans
            $table->string('midtrans_order_id')->nullable();           // Sama dengan invoice_id
            $table->string('link_pembayaran')->nullable();             // Snap URL
            $table->string('payment_method')->nullable();              // Credit Card / bank_transfer / dll
            $table->string('payment_channel')->nullable();             // Mandiri / BRI / Gopay dll
            $table->timestamp('midtrans_paid_at')->nullable();         // Waktu dibayar
            $table->string('midtrans_transaction_status')->nullable(); // settlement / pending / expire / cancel


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};

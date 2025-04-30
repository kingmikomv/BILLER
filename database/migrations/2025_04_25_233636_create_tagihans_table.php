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
        

            $table->string('xendit_invoice_id')->nullable(); // ID dari Xendit
            $table->string('link_pembayaran')->nullable();      // URL invoice
            $table->string('payment_method')->nullable();         // VA / QR / dll
            $table->string('payment_channel')->nullable();         // BRI / BCA / dll
            $table->timestamp('xendit_paid_at')->nullable();   // Waktu dibayar
            $table->string('xendit_status')->nullable();            // PAID / EXPIRED / PENDING
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

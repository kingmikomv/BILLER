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
        Schema::create('billing_seting', function (Blueprint $table) {
            $table->id();
        
            // Default penerbitan invoice (prorata)
            $table->boolean('prorata_enable')->default(true); // aktif/tidaknya sistem prorata
        
            // Generate invoice
            $table->enum('generate_invoice_mode', ['tanggal_pembayaran', 'dimajukan'])->default('tanggal_pembayaran');
            $table->integer('dimajukan_hari')->nullable(); // contoh: majukan 5 hari sebelum tanggal pembayaran
        
            // Jatuh tempo (misal: 7 hari setelah invoice dibuat)
            $table->integer('default_jatuh_tempo_hari')->default(7); // bisa diubah lewat setting
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_seting');
    }
};

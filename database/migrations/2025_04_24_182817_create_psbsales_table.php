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
        Schema::create('psbsales', function (Blueprint $table) {
            $table->id();
            $table->string('parent_id')->nullable();
            $table->string('sales')->nullable();
            $table->string('nama_psb')->nullable();
            $table->longText('alamat_psb')->nullable();
            $table->longText('foto_lokasi_psb')->nullable();
            $table->string('paket_psb')->nullable();
            $table->date('tanggal_ingin_pasang')->nullable();
            $table->string('telepon')->nullable();
            $table->longText('alasan')->nullable();
            $table->string('status')->nullable();
            $table->string('status_pemasangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psbsales');
    }
};

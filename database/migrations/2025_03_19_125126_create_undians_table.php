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
        Schema::create('undian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mikrotik_id')->constrained('mikrotik')->onDelete('cascade'); // Relasi ke tabel mikrotik
            $table->string('kode_undian')->unique(); // Kode unik undian
            $table->string('nama_undian'); // Nama event undian
            $table->string('foto_undian')->nullable(); // Foto undian (path)
            $table->dateTime('tanggal_kocok')->nullable(); // Tanggal undian dikocok
            $table->string('pemenang')->nullable(); // Nama pemenang
            $table->string('foto_pemenang')->nullable(); // Foto pemenang (path)
            $table->timestamps(); // created_at & updated_at
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('undian');
    }
};

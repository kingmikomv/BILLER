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
        Schema::create('tiketpsb', function (Blueprint $table) {
            $table->id();
            $table->string('no_tiket')->unique();
            $table->string('status_tiket');
            $table->string('serialnumber')->nullable();
            $table->string('parent_id')->nullable();

            // Relasi ke tabel pelanggan
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade');
            
            // Relasi ke tabel mikrotik/router
            $table->foreignId('mikrotik_id')->constrained('mikrotik')->onDelete('cascade');
            $table->string('router_username');
            
            // Relasi ke tabel paketpppoe
            $table->foreignId('paket_id')->constrained('paketpppoe')->onDelete('cascade');
            
            $table->string('akun_pppoe')->unique();
            $table->string('password_pppoe');
            $table->longText('alamat');
            $table->string('nomor_telepon');
            
            $table->date('tanggal_daftar');
            $table->date('pembayaran_selanjutnya')->nullable();
            $table->date('pembayaran_yang_akan_datang')->nullable();
            $table->date('tanggal_ingin_pasang')->nullable();
            $table->date('tanggal_terpasang')->nullable();
            
            // Informasi tambahan
            $table->string('nama_ssid')->nullable();
            $table->string('password_ssid')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('odp')->nullable();
            $table->string('olt')->nullable();
            
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiketpsb');
    }
};

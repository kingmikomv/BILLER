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
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('pelanggan_id')->unique();
            $table->string('router_id');
            $table->string('unique_id');
            $table->string('router_username');
            $table->string('kode_paket');
            $table->string('profile_paket');
            $table->string('nama_pelanggan');
            $table->string('akun_pppoe')->unique();
            $table->string('password_pppoe');
            $table->longText('alamat');
            $table->string('nomor_telepon');
            $table->date('tanggal_daftar'); 
            $table->date('pembayaran_selanjutnya')->nullable(); 
            $table->date('pembayaran_yang_akan_datang')->nullable(); 
            $table->date('tanggal_ingin_pasang')->nullable(); 
            $table->date('tanggal_terpasang')->nullable(); 

            $table->string('nama_ssid')->nullable(); // Kolom nama SSID (opsional)
            $table->string('password_ssid')->nullable(); // Kolom password SSID (opsional)
            $table->string('mac_address')->nullable(); // Kolom MAC Address (opsional)
            $table->string('serialnumber')->nullable(); // Kolom status pasang (opsional)
            $table->string('odp')->nullable(); // Kolom ODP (opsional)
            $table->string('olt')->nullable(); // Kolom OLT (opsional)
            $table->string('no_tiket')->nullable(); // Kolom nomor tiket (opsional)
            $table->string('status_terpasang')->nullable();
            $table->string('dipasang_oleh')->nullable();
            

            // Tambahkan kolom untuk metode pembayaran (prabayar atau pascabayar)
            $table->enum('metode_pembayaran', ['Prabayar', 'Pascabayar'])->default('Pascabayar');
        
            // Status pembayaran hanya untuk pascabayar, prabayar langsung lunas
            $table->enum('status_pembayaran', ['Sudah Dibayar', 'Belum Dibayar'])->default('Sudah Dibayar'); 
            

            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};

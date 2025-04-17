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
            
            // Relasi ke tabel mikrotik
            $table->foreignId('mikrotik_id')->constrained('mikrotik')->onDelete('cascade');
            $table->string('router_id');
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
            $table->date('tanggal_ingin_pasang')->nullable(); 
            $table->date('tanggal_terpasang')->nullable(); 
            $table->boolean('notified')->default(false);
            $table->date('notified_at')->nullable();
            $table->boolean('isolated')->default(false);
            
            // Informasi tambahan pelanggan
            $table->string('nama_ssid')->nullable();
            $table->string('password_ssid')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('serialnumber')->nullable();
            $table->string('odp')->nullable();
            $table->string('olt')->nullable();
            $table->string('no_tiket')->nullable();
            $table->string('status_terpasang')->nullable();
            $table->string('dipasang_oleh')->nullable();
        
            // Metode dan status pembayaran
            $table->enum('metode_pembayaran', ['Prabayar', 'Pascabayar'])->default('Pascabayar');
        
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

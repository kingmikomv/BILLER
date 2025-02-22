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
            $table->string('unique_id');
            $table->string('status_tiket');
            $table->string('serial_number')->nullable();
            $table->string('nama_pelanggan');
            $table->string('pelanggan_id')->unique();
            $table->string('router_id');
            $table->string('router_username');
            $table->string('kode_paket');
            $table->string('profile_paket');
            $table->string('akun_pppoe')->unique();
            $table->string('password_pppoe');
            $table->longText('alamat');
            $table->string('nomor_telepon');
            $table->date('tanggal_daftar');
            $table->date('pembayaran_selanjutnya')->nullable();
            $table->date('pembayaran_yang_akan_datang')->nullable();
            $table->date('tanggal_ingin_pasang')->nullable();
            $table->string('nama_ssid')->nullable(); // Kolom nama SSID (opsional)
            $table->string('password_ssid')->nullable(); // Kolom password SSID (opsional)
            $table->string('mac_address')->nullable(); // Kolom MAC Address (opsional)
            $table->string('odp')->nullable(); // Kolom ODP (opsional)
            $table->string('olt')->nullable(); // Kolom OLT (opsional)

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

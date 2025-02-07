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
        Schema::create('paketpppoe', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id');
            $table->string('router_id');
            $table->string('kode_paket');
            $table->string('site');
            $table->string('username'); // dari mikrotik
            $table->string('profile');
            $table->string('harga_paket');
            $table->string('nama_paket');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paketpppoe');
    }
};

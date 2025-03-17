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

            $table->foreignId('mikrotik_id')->constrained('mikrotik')->onDelete('cascade'); 
            $table->string('kode_paket');
            $table->string('site');
            
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

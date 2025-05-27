<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('hotspot_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // relasi ke users
            $table->string('name');                   // Nama Profile
            $table->integer('price');                 // Harga
            $table->integer('reseller_price')->nullable(); // Harga Reseller
            $table->integer('rate_up');               // Upload Mbps
            $table->integer('rate_down');             // Download Mbps
            $table->integer('uptime')->nullable();    // dalam jam
            $table->integer('validity')->nullable();  // dalam hari
            $table->string('groupname')->unique();    // untuk radgroupreply
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hotspot_profiles');
    }
};

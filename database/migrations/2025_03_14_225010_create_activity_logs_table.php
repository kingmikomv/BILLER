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
    Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id'); // ID user yang melakukan aksi
        $table->string('role'); // Role user (Teknisi, CS, Penagih, dll.)
        $table->string('activity'); // Aktivitas yang dilakukan
        $table->string('target')->nullable(); // Target aksi (misal, nama pelanggan atau transaksi)
        $table->timestamp('created_at')->useCurrent(); // Waktu aksi terjadi
        $table->timestamp('updated_at')->useCurrent();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

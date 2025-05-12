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
       Schema::create('data_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('unique_member')->index();
            $table->unsignedBigInteger('pelanggan_id');
            $table->unsignedBigInteger('tagihan_id'); // ✅ foreign key ke tabel tagihan

            $table->string('nomor_telepon')->nullable();
            $table->date('tanggal_generate');
            $table->decimal('nominal', 12, 2);
            $table->string('status');
            $table->string('status_wa')->default('pending');
            $table->timestamps();

            $table->foreign('pelanggan_id')->references('id')->on('pelanggan')->onDelete('cascade');
            $table->foreign('tagihan_id')->references('id')->on('tagihan')->onDelete('cascade'); // ✅ relasi tagihan
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_invoices');
    }
};

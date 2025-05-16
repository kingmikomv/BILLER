<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ResetTagihanTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('tagihan');

        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique();

            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade');

            $table->date('tanggal_generate');
            $table->date('tanggal_jatuh_tempo');
            $table->date('tanggal_pembayaran')->nullable();
            $table->date('tanggal_dibayar')->nullable();

            $table->integer('jumlah_tagihan');
            $table->boolean('prorata')->default(false);
            $table->enum('status', ['Lunas', 'Belum Lunas', 'Tertunggak'])->default('Belum Lunas');

            $table->string('midtrans_order_id')->nullable();
            $table->string('link_pembayaran')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_channel')->nullable();
            $table->timestamp('midtrans_paid_at')->nullable();
       $table->string('midtrans_transaction_status')->nullable(); // settlement / pending / expire / cancel
            $table->string('metode')->nullable();
            $table->string('penagih')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tagihan');
    }
}

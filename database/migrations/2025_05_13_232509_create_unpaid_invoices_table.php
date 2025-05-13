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
        Schema::create('unpaid_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique();
            $table->date('jatuh_tempo')->nullable();
            $table->boolean('sudah_dibayar')->default(false);
            $table->timestamps();

            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('unpaid_invoices');
    }
};

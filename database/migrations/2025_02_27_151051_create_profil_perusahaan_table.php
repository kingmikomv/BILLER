<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('profil_perusahaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke users
            $table->string('nama_perusahaan');
            $table->string('brand')->nullable();
            $table->string('nomor_izin_isp')->nullable();
            $table->string('nomor_izin_jartaplok')->nullable();
            $table->string('npwp')->nullable();
            $table->text('alamat')->nullable();
            $table->string('website')->nullable();
            $table->string('email_perusahaan')->unique();
            $table->string('nomor_telepon')->nullable();
            $table->string('nomor_whatsapp')->nullable();
            $table->string('nama_owner')->nullable();
            $table->string('nama_finance')->nullable();
            $table->string('nomor_telepon_finance')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('profil_perusahaan');
    }
};

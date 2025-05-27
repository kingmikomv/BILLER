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
        Schema::connection('freeradius')->table('nas', function (Blueprint $table) {
            // Tambahkan kolom user_id (tanpa foreign key constraint)
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            // Tidak bisa menggunakan ->foreign() karena 'users' ada di DB lain
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('freeradius')->table('nas', function (Blueprint $table) {
            // $table->dropForeign(['user_id']); // hanya jika FK digunakan
            $table->dropColumn('user_id');
        });
    }
};

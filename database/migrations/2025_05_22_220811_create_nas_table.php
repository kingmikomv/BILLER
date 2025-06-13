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
      Schema::connection('freeradius')->create('nas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nasname'); // IP
            $table->string('shortname')->nullable();
            $table->string('type')->default('other');
            $table->string('ports')->nullable();
            $table->string('secret')->default('secret');
            $table->string('server')->nullable();
            $table->string('community')->nullable();
            $table->string('description')->nullable();

            // Tambahan Laravel: relasi user (tanpa FK constraint karena beda DB)
$table->unsignedBigInteger('user_id')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('freeradius')->create('nas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nasname'); // IP NAS
            $table->string('shortname')->nullable();
            $table->string('type')->default('other');
            $table->string('ports')->nullable();
            $table->string('secret')->default('secret');
            $table->string('server')->nullable();
            $table->string('community')->nullable();
            $table->string('description')->nullable();

            // relasi member Laravel
            $table->unsignedBigInteger('user_id')->nullable(); // tanpa foreign key
        });
    }

    public function down(): void
    {
        Schema::connection('freeradius')->dropIfExists('nas');
    }
};

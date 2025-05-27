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
        Schema::connection('freeradius')->create('radcheck', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->string('attribute')->default('Cleartext-Password');
            $table->string('op')->default(':=');
            $table->string('value');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::connection('freeradius')->dropIfExists('radcheck');
    }
};

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
        Schema::create('mikrotik', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id');
            $table->string('router_id');
            $table->string('site');
            $table->string('port_api')->nullable();
            $table->string('port_winbox')->nullable();
            $table->string('port_olt')->nullable();
            $table->string('port_remoteweb')->nullable();

            //group untuk login api
            $table->string('username');
            $table->string('password');

            //vpn
            $table->string('vpn_name');
            $table->string('vpn_username');
            $table->string('vpn_password');
            $table->string('local_ip');
            $table->string('remote_ip');


            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mikrotik');
    }
};

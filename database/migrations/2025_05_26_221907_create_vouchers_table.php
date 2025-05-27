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
       Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->constrained()->onDelete('cascade');  // user pembuat voucher
            $table->foreignId('hotspot_profile_id')->constrained('hotspot_profiles')->onDelete('cascade'); // profile yg dipakai
            
            $table->string('username')->unique();
            $table->string('password');
            
            $table->enum('user_model', ['username_equals_password', 'username_plus_password']);
            $table->enum('char_type', ['uppercase', 'lowercase', 'numbers', 'uppercase_numbers']);
            
            $table->string('prefix')->nullable();

            $table->enum('status', ['active', 'used', 'expired'])->default('active');
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_otp_temp', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->comment('Full phone with country code e.g. +919876543210');
            $table->string('email', 255)->nullable()->comment('Email for email-type OTPs');
            $table->string('otp', 10)->comment('The OTP value (4-digit for phone, 6-digit for email)');
            $table->enum('type', ['phone', 'email'])->comment('phone = Step 1 OTP, email = Step 4/login OTP');
            $table->enum('user_cat', ['customer', 'driver'])->default('customer');
            $table->boolean('verified')->default(false)->comment('0 = pending, 1 = used/verified');
            $table->dateTime('expires_at')->comment('OTP expiry time (10 minutes from creation)');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['phone', 'type'], 'idx_phone_type');
            $table->index('expires_at', 'idx_expires');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_otp_temp');
    }
};

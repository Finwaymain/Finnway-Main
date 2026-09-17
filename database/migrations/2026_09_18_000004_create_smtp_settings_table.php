<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('smtp_settings')) {
            Schema::create('smtp_settings', function (Blueprint $table) {
                $table->id();
                $table->string('mail_mailer', 50)->default('smtp');
                $table->string('mail_host', 191)->nullable();
                $table->integer('mail_port')->default(465);
                $table->string('mail_username', 191)->nullable();
                $table->string('mail_password', 191)->nullable();
                $table->string('mail_encryption', 20)->nullable()->default('ssl');
                $table->string('mail_from_address', 191)->nullable();
                $table->string('mail_from_name', 191)->nullable()->default('Fiinway Desk');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_tested_at')->nullable();
                $table->string('last_test_status', 50)->nullable();
                $table->text('last_test_message')->nullable();
                $table->timestamps();
            });

            // Seed default configuration (Hostinger / Fiinway setup)
            DB::table('smtp_settings')->insert([
                'mail_mailer'       => 'smtp',
                'mail_host'         => 'smtp.hostinger.com',
                'mail_port'         => 465,
                'mail_username'     => 'git@openscore.msmeloan.sbs',
                'mail_password'     => 'Hostinger@2026Secure!',
                'mail_encryption'   => 'ssl',
                'mail_from_address' => 'git@openscore.msmeloan.sbs',
                'mail_from_name'    => 'Fiinway Desk',
                'is_active'         => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};

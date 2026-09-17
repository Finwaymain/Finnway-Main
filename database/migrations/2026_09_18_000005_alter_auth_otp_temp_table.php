<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auth_otp_temp')) {
            // Using DB::statement to modify enum columns to VARCHAR for universal compatibility
            DB::statement("ALTER TABLE auth_otp_temp MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'email'");
            DB::statement("ALTER TABLE auth_otp_temp MODIFY COLUMN `user_cat` VARCHAR(50) NOT NULL DEFAULT 'customer'");
            DB::statement("ALTER TABLE auth_otp_temp MODIFY COLUMN `phone` VARCHAR(30) NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('auth_otp_temp')) {
            DB::statement("ALTER TABLE auth_otp_temp MODIFY COLUMN `type` ENUM('phone','email') NOT NULL DEFAULT 'email'");
            DB::statement("ALTER TABLE auth_otp_temp MODIFY COLUMN `user_cat` ENUM('customer','driver') NOT NULL DEFAULT 'customer'");
        }
    }
};

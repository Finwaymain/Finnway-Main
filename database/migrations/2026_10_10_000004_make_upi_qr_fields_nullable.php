<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('upi_qr_transactions')) {
            try {
                DB::statement("ALTER TABLE `upi_qr_transactions` MODIFY `user_id` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE `upi_qr_transactions` MODIFY `ac_no` VARCHAR(50) NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE `upi_qr_transactions` MODIFY `status` VARCHAR(30) DEFAULT 'unassigned'");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE `upi_qr_transactions` MODIFY `wallet_credited` TINYINT(1) DEFAULT 0");
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        // Safe no-op
    }
};

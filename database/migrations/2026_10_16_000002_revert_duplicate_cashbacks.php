<?php

use Illuminate\Database\Migrations\Migration;
use App\Services\CashbackReconciliationService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            CashbackReconciliationService::revertDuplicateDisbursements();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Migration revert_duplicate_cashbacks notice: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};

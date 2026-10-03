<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\VendorTeamService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            VendorTeamService::reconcileAllDuplicateFreelancers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Migration reconcile_duplicate_freelancers notice: " . $e->getMessage());
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

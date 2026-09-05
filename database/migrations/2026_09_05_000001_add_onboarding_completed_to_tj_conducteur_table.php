<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tj_conducteur') && !Schema::hasColumn('tj_conducteur', 'onboarding_completed')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                $table->string('onboarding_completed', 10)->default('no')->after('is_verified');
            });

            // Backfill existing drivers who have completed onboarding
            $driversWithCategories = DB::table('tj_conducteur_categories')
                ->distinct()
                ->pluck('driver_id')
                ->toArray();

            if (!empty($driversWithCategories)) {
                $verifiedDriverIds = DB::table('tj_conducteur')
                    ->whereIn('id', $driversWithCategories)
                    ->where(function ($q) {
                        $q->where('is_verified', 1)
                          ->orWhere('statut', 'yes');
                    })
                    ->pluck('id')
                    ->toArray();

                if (!empty($verifiedDriverIds)) {
                    DB::table('tj_conducteur')
                        ->whereIn('id', $verifiedDriverIds)
                        ->update(['onboarding_completed' => 'yes']);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tj_conducteur') && Schema::hasColumn('tj_conducteur', 'onboarding_completed')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                $table->dropColumn('onboarding_completed');
            });
        }
    }
};

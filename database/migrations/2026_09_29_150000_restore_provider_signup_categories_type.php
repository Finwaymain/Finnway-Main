<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tj_categorie_user') && Schema::hasColumn('tj_categorie_user', 'type')) {
            // Restore provider signup categories (IDs < 13207) to type = null
            // so they are properly returned by OnboardingController::init
            // while consumer catalog services (IDs >= 13207) retain type = 'consumer_service'.
            DB::table('tj_categorie_user')
                ->where('id', '<', 13207)
                ->update(['type' => null]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tj_categorie_user') && Schema::hasColumn('tj_categorie_user', 'type')) {
            DB::table('tj_categorie_user')
                ->where('id', '<', 13207)
                ->update(['type' => 'consumer_service']);
        }
    }
};

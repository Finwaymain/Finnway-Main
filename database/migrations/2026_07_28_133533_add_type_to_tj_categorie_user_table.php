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
        Schema::table('tj_categorie_user', function (Blueprint $table) {
            // Disambiguates trees sharing this table: existing rows (null) are the
            // provider/driver signup categories from UserCategorySeeder; rows tagged
            // 'consumer_service' are the user-app "All Services" catalog.
            $table->string('type')->nullable()->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tj_categorie_user', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

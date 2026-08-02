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
        Schema::table('tj_conducteur_categories', function (Blueprint $table) {
            $table->string('statut', 10)->default('yes')->after('subcategory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tj_conducteur_categories', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};

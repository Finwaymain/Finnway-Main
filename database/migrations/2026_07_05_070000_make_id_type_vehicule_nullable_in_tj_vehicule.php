<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Makes id_type_vehicule nullable so non-transport drivers can be onboarded
     * without crashing on the NOT NULL constraint.
     */
    public function up(): void
    {
        // Use raw ALTER TABLE to handle the case where the table was created
        // outside of Laravel migrations (no Blueprint definition exists for it)
        DB::statement('ALTER TABLE tj_vehicule MODIFY COLUMN id_type_vehicule INT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only safe to reverse if no NULLs exist; skip if there are
        $hasNulls = DB::table('tj_vehicule')->whereNull('id_type_vehicule')->exists();
        if (!$hasNulls) {
            DB::statement('ALTER TABLE tj_vehicule MODIFY COLUMN id_type_vehicule INT NOT NULL');
        }
    }
};

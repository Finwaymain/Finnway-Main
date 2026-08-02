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
        Schema::table('tj_type_vehicule', function (Blueprint $table) {
            $table->double('base_price', 10, 2)->default(0.00)->nullable()->after('prix');
            $table->double('per_km_price', 10, 2)->default(0.00)->nullable()->after('base_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tj_type_vehicule', function (Blueprint $table) {
            $table->dropColumn(['base_price', 'per_km_price']);
        });
    }
};

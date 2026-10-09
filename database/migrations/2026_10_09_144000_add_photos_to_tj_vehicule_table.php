<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tj_vehicule')) {
            Schema::table('tj_vehicule', function (Blueprint $table) {
                if (!Schema::hasColumn('tj_vehicule', 'front_photo')) {
                    $table->string('front_photo', 255)->nullable();
                }
                if (!Schema::hasColumn('tj_vehicule', 'back_photo')) {
                    $table->string('back_photo', 255)->nullable();
                }
                if (!Schema::hasColumn('tj_vehicule', 'side_photo')) {
                    $table->string('side_photo', 255)->nullable();
                }
                if (!Schema::hasColumn('tj_vehicule', 'numberplate_photo')) {
                    $table->string('numberplate_photo', 255)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tj_vehicule')) {
            Schema::table('tj_vehicule', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('tj_vehicule', 'front_photo')) $cols[] = 'front_photo';
                if (Schema::hasColumn('tj_vehicule', 'back_photo')) $cols[] = 'back_photo';
                if (Schema::hasColumn('tj_vehicule', 'side_photo')) $cols[] = 'side_photo';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};

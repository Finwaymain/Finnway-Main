<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tj_conducteur') && !Schema::hasColumn('tj_conducteur', 'aadhar_number')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                $table->string('aadhar_number', 20)->nullable()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tj_conducteur') && Schema::hasColumn('tj_conducteur', 'aadhar_number')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                $table->dropColumn('aadhar_number');
            });
        }
    }
};

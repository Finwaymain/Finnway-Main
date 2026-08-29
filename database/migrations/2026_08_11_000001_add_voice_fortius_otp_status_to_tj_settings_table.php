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
        if (Schema::hasTable('tj_settings') && !Schema::hasColumn('tj_settings', 'voice_fortius_otp_status')) {
            Schema::table('tj_settings', function (Blueprint $table) {
                $table->string('voice_fortius_otp_status')->default('0')->nullable();
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
        if (Schema::hasTable('tj_settings') && Schema::hasColumn('tj_settings', 'voice_fortius_otp_status')) {
            Schema::table('tj_settings', function (Blueprint $table) {
                $table->dropColumn('voice_fortius_otp_status');
            });
        }
    }
};

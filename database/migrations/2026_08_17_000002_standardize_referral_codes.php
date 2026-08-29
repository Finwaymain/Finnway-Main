<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tj_conducteur')) {
            if (!Schema::hasColumn('tj_conducteur', 'referral_code')) {
                Schema::table('tj_conducteur', function (Blueprint $table) {
                    $table->string('referral_code', 50)->nullable()->after('phone');
                });
            }
            DB::statement("UPDATE tj_conducteur SET referral_code = CONCAT('FIINB', LPAD(id, 5, '0'))");
        }

        if (Schema::hasTable('tj_user_app')) {
            if (!Schema::hasColumn('tj_user_app', 'referral_code')) {
                Schema::table('tj_user_app', function (Blueprint $table) {
                    $table->string('referral_code', 50)->nullable()->after('phone');
                });
            }
            DB::statement("UPDATE tj_user_app SET referral_code = CONCAT('FIINU', LPAD(id, 5, '0'))");
        }

        if (Schema::hasTable('referral')) {
            DB::statement("
                UPDATE referral r
                JOIN tj_conducteur c ON r.user_id = c.id
                SET r.referral_code = CONCAT('FIINB', LPAD(c.id, 5, '0'))
            ");

            DB::statement("
                UPDATE referral r
                JOIN tj_user_app u ON r.user_id = u.id
                SET r.referral_code = CONCAT('FIINU', LPAD(u.id, 5, '0'))
            ");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};

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
        // Fix any user containing subadmin in email or created via sub-admin flow
        DB::table('users')
            ->where('email', 'LIKE', '%subadmin%')
            ->where('email', '!=', 'admin@cabme.com')
            ->where('email', '!=', 'admin@fooddelivery.com')
            ->update([
                'role' => 'sub_admin',
                'is_active' => true,
            ]);

        // Ensure main admin emails are super_admin
        DB::table('users')
            ->whereIn('email', ['admin@cabme.com', 'admin@fooddelivery.com'])
            ->update([
                'role' => 'super_admin',
                'is_active' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No-op
    }
};

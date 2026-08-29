<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Ensure admin@cabme.com user exists and is Super Admin
        $cabmeAdmin = User::where('email', 'admin@cabme.com')->first();
        if ($cabmeAdmin) {
            $cabmeAdmin->password = Hash::make('12345678');
            $cabmeAdmin->role = 'super_admin';
            $cabmeAdmin->is_active = true;
            $cabmeAdmin->save();
        } else {
            User::create([
                'name' => 'Main Super Admin',
                'email' => 'admin@cabme.com',
                'password' => Hash::make('12345678'),
                'role' => 'super_admin',
                'is_active' => true,
            ]);
        }

        // 2. Also ensure admin@fooddelivery.com exists as Super Admin
        $foodAdmin = User::where('email', 'admin@fooddelivery.com')->first();
        if ($foodAdmin) {
            $foodAdmin->role = 'super_admin';
            $foodAdmin->is_active = true;
            $foodAdmin->save();
        }
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

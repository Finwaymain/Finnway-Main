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
        try {
            // Fix AUTO_INCREMENT on users table
            $maxId = (int)DB::table('users')->max('id');
            $nextId = max($maxId + 1, 1);
            DB::statement("ALTER TABLE users MODIFY id INT AUTO_INCREMENT;");
            DB::statement("ALTER TABLE users AUTO_INCREMENT = {$nextId};");
        } catch (\Throwable $e) {
            // Log or ignore if MySQL version syntax differs
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

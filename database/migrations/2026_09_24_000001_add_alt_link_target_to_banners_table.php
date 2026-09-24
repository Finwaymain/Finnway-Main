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
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'alt')) {
                $table->string('alt')->nullable()->after('title');
            }
            if (!Schema::hasColumn('banners', 'link')) {
                $table->text('link')->nullable()->after('image');
            }
            if (!Schema::hasColumn('banners', 'target_app')) {
                $table->enum('target_app', ['user', 'driver', 'both'])->default('both')->after('link');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'target_app')) {
                $table->dropColumn('target_app');
            }
            if (Schema::hasColumn('banners', 'link')) {
                $table->dropColumn('link');
            }
            if (Schema::hasColumn('banners', 'alt')) {
                $table->dropColumn('alt');
            }
        });
    }
};

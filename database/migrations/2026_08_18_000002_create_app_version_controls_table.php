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
        if (!Schema::hasTable('app_version_controls')) {
            Schema::create('app_version_controls', function (Blueprint $table) {
                $table->id();
                $table->string('app_type', 30)->unique(); // 'customer' or 'business'
                $table->string('app_name', 150);
                $table->string('latest_version', 30)->default('1.0.0');
                $table->string('minimum_version', 30)->default('1.0.0');
                $table->boolean('force_update')->default(false);
                $table->text('playstore_url');
                $table->text('appstore_url')->nullable();
                $table->string('title', 200)->default('New Update Available!');
                $table->text('message')->nullable();
                $table->boolean('is_maintenance')->default(false);
                $table->text('maintenance_message')->nullable();
                $table->timestamps();
            });

            // Seed default version records for Customer App and Driver App
            DB::table('app_version_controls')->insert([
                [
                    'app_type' => 'customer',
                    'app_name' => 'Fiinway User App',
                    'latest_version' => '1.0.17',
                    'minimum_version' => '1.0.0',
                    'force_update' => false,
                    'playstore_url' => 'https://play.google.com/store/apps/details?id=com.fiinway',
                    'appstore_url' => 'https://apps.apple.com/app/id000000000',
                    'title' => 'New Version Available!',
                    'message' => 'A new version of the Fiinway User App is available with performance improvements and new services. Please update from Google Play Store to enjoy the best experience.',
                    'is_maintenance' => false,
                    'maintenance_message' => 'Fiinway services are currently undergoing scheduled maintenance. We will be back online shortly!',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'app_type' => 'business',
                    'app_name' => 'Fiinway Driver & Partner App',
                    'latest_version' => '1.0.17',
                    'minimum_version' => '1.0.0',
                    'force_update' => false,
                    'playstore_url' => 'https://play.google.com/store/apps/details?id=com.fiinway.driver',
                    'appstore_url' => 'https://apps.apple.com/app/id000000000',
                    'title' => 'Important Driver Partner Update!',
                    'message' => 'A critical update is available for Fiinway Driver Partners with updated ride dispatch, payout enhancements, and bug fixes. Please update now to continue accepting rides.',
                    'is_maintenance' => false,
                    'maintenance_message' => 'Partner services are undergoing scheduled maintenance. We will be back online shortly!',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_version_controls');
    }
};

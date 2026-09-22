<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tj_user_app')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                if (! Schema::hasColumn('tj_user_app', 'latitude')) {
                    $table->decimal('latitude', 11, 8)->nullable()->after('amount');
                }
                if (! Schema::hasColumn('tj_user_app', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                }
                if (! Schema::hasColumn('tj_user_app', 'city')) {
                    $table->string('city', 100)->nullable()->after('longitude');
                }
                if (! Schema::hasColumn('tj_user_app', 'address')) {
                    $table->text('address')->nullable()->after('city');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tj_user_app')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                if (Schema::hasColumn('tj_user_app', 'address')) {
                    $table->dropColumn('address');
                }
                if (Schema::hasColumn('tj_user_app', 'city')) {
                    $table->dropColumn('city');
                }
                if (Schema::hasColumn('tj_user_app', 'longitude')) {
                    $table->dropColumn('longitude');
                }
                if (Schema::hasColumn('tj_user_app', 'latitude')) {
                    $table->dropColumn('latitude');
                }
            });
        }
    }
};

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
        if (Schema::hasTable('tj_settings')) {
            Schema::table('tj_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('tj_settings', 'business_whatsapp_number')) {
                    $table->string('business_whatsapp_number', 30)->nullable()->default('9429693669');
                }
                if (!Schema::hasColumn('tj_settings', 'business_call_number')) {
                    $table->string('business_call_number', 30)->nullable()->default('9429693669');
                }
                if (!Schema::hasColumn('tj_settings', 'customer_whatsapp_number')) {
                    $table->string('customer_whatsapp_number', 30)->nullable()->default('9429693669');
                }
                if (!Schema::hasColumn('tj_settings', 'customer_call_number')) {
                    $table->string('customer_call_number', 30)->nullable()->default('9429693669');
                }
            });

            // Populate default numbers for any existing row in tj_settings if empty
            DB::table('tj_settings')->whereNull('business_whatsapp_number')->orWhere('business_whatsapp_number', '')->update([
                'business_whatsapp_number' => '9429693669',
                'business_call_number' => '9429693669',
                'customer_whatsapp_number' => '9429693669',
                'customer_call_number' => '9429693669',
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
        if (Schema::hasTable('tj_settings')) {
            Schema::table('tj_settings', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('tj_settings', 'business_whatsapp_number')) $columns[] = 'business_whatsapp_number';
                if (Schema::hasColumn('tj_settings', 'business_call_number')) $columns[] = 'business_call_number';
                if (Schema::hasColumn('tj_settings', 'customer_whatsapp_number')) $columns[] = 'customer_whatsapp_number';
                if (Schema::hasColumn('tj_settings', 'customer_call_number')) $columns[] = 'customer_call_number';
                
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};

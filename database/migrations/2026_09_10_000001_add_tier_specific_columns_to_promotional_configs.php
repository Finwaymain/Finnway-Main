<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promotional_configs')) {
            Schema::table('promotional_configs', function (Blueprint $table) {
                // Tier 1 (With Referral Code)
                if (!Schema::hasColumn('promotional_configs', 'discount_per_service_with_code')) {
                    $table->decimal('discount_per_service_with_code', 10, 2)->default(50.00)->after('bonus_with_code');
                }
                if (!Schema::hasColumn('promotional_configs', 'expiry_days_with_code')) {
                    $table->integer('expiry_days_with_code')->default(30)->after('uses_with_code');
                }
                if (!Schema::hasColumn('promotional_configs', 'custom_expiry_date_with_code')) {
                    $table->date('custom_expiry_date_with_code')->nullable()->after('expiry_days_with_code');
                }
                if (!Schema::hasColumn('promotional_configs', 'min_bill_with_code')) {
                    $table->decimal('min_bill_with_code', 10, 2)->default(50.00)->after('custom_expiry_date_with_code');
                }
                if (!Schema::hasColumn('promotional_configs', 'max_bill_with_code')) {
                    $table->decimal('max_bill_with_code', 10, 2)->default(100000.00)->after('min_bill_with_code');
                }
                if (!Schema::hasColumn('promotional_configs', 'applicable_roles_with_code')) {
                    $table->string('applicable_roles_with_code', 50)->default('all')->after('max_bill_with_code');
                }

                // Tier 2 (Without Referral Code - Direct Join)
                if (!Schema::hasColumn('promotional_configs', 'discount_per_service_without_code')) {
                    $table->decimal('discount_per_service_without_code', 10, 2)->default(50.00)->after('bonus_without_code');
                }
                if (!Schema::hasColumn('promotional_configs', 'expiry_days_without_code')) {
                    $table->integer('expiry_days_without_code')->default(30)->after('uses_without_code');
                }
                if (!Schema::hasColumn('promotional_configs', 'custom_expiry_date_without_code')) {
                    $table->date('custom_expiry_date_without_code')->nullable()->after('expiry_days_without_code');
                }
                if (!Schema::hasColumn('promotional_configs', 'min_bill_without_code')) {
                    $table->decimal('min_bill_without_code', 10, 2)->default(50.00)->after('custom_expiry_date_without_code');
                }
                if (!Schema::hasColumn('promotional_configs', 'max_bill_without_code')) {
                    $table->decimal('max_bill_without_code', 10, 2)->default(100000.00)->after('min_bill_without_code');
                }
                if (!Schema::hasColumn('promotional_configs', 'applicable_roles_without_code')) {
                    $table->string('applicable_roles_without_code', 50)->default('all')->after('max_bill_without_code');
                }
            });

            // Populate existing row with fallback values
            $config = DB::table('promotional_configs')->first();
            if ($config) {
                DB::table('promotional_configs')->where('id', $config->id)->update([
                    'discount_per_service_with_code'    => $config->discount_per_service ?? 50.00,
                    'expiry_days_with_code'             => $config->expiry_days ?? 30,
                    'custom_expiry_date_with_code'      => $config->custom_expiry_date ?? null,
                    'min_bill_with_code'                => $config->min_bill_amount ?? 50.00,
                    'max_bill_with_code'                => $config->max_bill_amount ?? 100000.00,
                    'applicable_roles_with_code'        => $config->applicable_roles ?? 'all',

                    'discount_per_service_without_code' => $config->discount_per_service ?? 50.00,
                    'expiry_days_without_code'          => $config->expiry_days ?? 30,
                    'custom_expiry_date_without_code'   => $config->custom_expiry_date ?? null,
                    'min_bill_without_code'             => $config->min_bill_amount ?? 50.00,
                    'max_bill_without_code'             => $config->max_bill_amount ?? 100000.00,
                    'applicable_roles_without_code'     => $config->applicable_roles ?? 'all',
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('promotional_configs')) {
            Schema::table('promotional_configs', function (Blueprint $table) {
                $table->dropColumn([
                    'discount_per_service_with_code',
                    'expiry_days_with_code',
                    'custom_expiry_date_with_code',
                    'min_bill_with_code',
                    'max_bill_with_code',
                    'applicable_roles_with_code',
                    'discount_per_service_without_code',
                    'expiry_days_without_code',
                    'custom_expiry_date_without_code',
                    'min_bill_without_code',
                    'max_bill_without_code',
                    'applicable_roles_without_code',
                ]);
            });
        }
    }
};

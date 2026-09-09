<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Promotional System Master Configuration
        if (!Schema::hasTable('promotional_configs')) {
            Schema::create('promotional_configs', function (Blueprint $table) {
                $table->id();
                $table->decimal('bonus_with_code', 10, 2)->default(300.00);
                $table->decimal('bonus_without_code', 10, 2)->default(150.00);
                $table->decimal('discount_per_service', 10, 2)->default(50.00);
                $table->integer('uses_with_code')->default(6);
                $table->integer('uses_without_code')->default(3);
                $table->integer('expiry_days')->default(30);
                $table->date('custom_expiry_date')->nullable();
                $table->decimal('min_bill_amount', 10, 2)->default(50.00);
                $table->decimal('max_bill_amount', 10, 2)->default(100000.00);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->string('applicable_roles', 50)->default('all'); // all, customer, driver
                $table->timestamps();
            });

            // Seed initial default configuration
            DB::table('promotional_configs')->insert([
                'bonus_with_code'      => 300.00,
                'bonus_without_code'   => 150.00,
                'discount_per_service' => 50.00,
                'uses_with_code'       => 6,
                'uses_without_code'    => 3,
                'expiry_days'          => 30,
                'min_bill_amount'      => 50.00,
                'max_bill_amount'      => 100000.00,
                'status'               => 'active',
                'applicable_roles'     => 'all',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // 2. User Promotional Wallets (Separated from withdrawable cash)
        if (!Schema::hasTable('user_promotions')) {
            Schema::create('user_promotions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('user_type', 20)->default('customer'); // customer, driver
                $table->decimal('initial_bonus', 10, 2)->default(0.00);
                $table->decimal('remaining_bonus', 10, 2)->default(0.00);
                $table->decimal('discount_per_service', 10, 2)->default(50.00);
                $table->integer('total_uses')->default(0);
                $table->integer('uses_remaining')->default(0);
                $table->boolean('joined_with_code')->default(false);
                $table->string('referral_code_used', 50)->nullable();
                $table->dateTime('expiry_date')->nullable();
                $table->enum('status', ['active', 'exhausted', 'expired'])->default('active');
                $table->timestamps();

                $table->index(['user_id', 'user_type']);
                $table->index('status');
            });
        }

        // 3. User Promotional Usage Ledger
        if (!Schema::hasTable('user_promotion_logs')) {
            Schema::create('user_promotion_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_promotion_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->string('user_type', 20)->default('customer');
                $table->string('service_type', 30); // cab, home_service
                $table->string('booking_id', 50);
                $table->decimal('service_price', 10, 2);
                $table->decimal('promotional_amount', 10, 2)->default(0.00); // +50
                $table->decimal('booking_total', 10, 2); // 150
                $table->decimal('discount_applied', 10, 2)->default(0.00); // -50
                $table->decimal('final_payable', 10, 2); // 100
                $table->timestamp('created_at')->useCurrent();

                $table->index(['user_id', 'user_type']);
                $table->index('booking_id');
            });
        }

        // 4. Add promotional columns to tj_requete (Cabs) if not present
        if (Schema::hasTable('tj_requete')) {
            Schema::table('tj_requete', function (Blueprint $table) {
                if (!Schema::hasColumn('tj_requete', 'promotional_amount')) {
                    $table->decimal('promotional_amount', 10, 2)->default(0.00)->after('montant');
                }
                if (!Schema::hasColumn('tj_requete', 'promotional_discount')) {
                    $table->decimal('promotional_discount', 10, 2)->default(0.00)->after('promotional_amount');
                }
                if (!Schema::hasColumn('tj_requete', 'is_promotional_applied')) {
                    $table->boolean('is_promotional_applied')->default(false)->after('promotional_discount');
                }
            });
        }

        // 5. Add promotional columns to service_requests (Home Services) if not present
        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('service_requests', 'promotional_amount')) {
                    $table->decimal('promotional_amount', 10, 2)->default(0.00)->after('amount');
                }
                if (!Schema::hasColumn('service_requests', 'promotional_discount')) {
                    $table->decimal('promotional_discount', 10, 2)->default(0.00)->after('promotional_amount');
                }
                if (!Schema::hasColumn('service_requests', 'is_promotional_applied')) {
                    $table->boolean('is_promotional_applied')->default(false)->after('promotional_discount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_promotion_logs');
        Schema::dropIfExists('user_promotions');
        Schema::dropIfExists('promotional_configs');

        if (Schema::hasTable('tj_requete')) {
            Schema::table('tj_requete', function (Blueprint $table) {
                if (Schema::hasColumn('tj_requete', 'is_promotional_applied')) {
                    $table->dropColumn(['promotional_amount', 'promotional_discount', 'is_promotional_applied']);
                }
            });
        }

        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                if (Schema::hasColumn('service_requests', 'is_promotional_applied')) {
                    $table->dropColumn(['promotional_amount', 'promotional_discount', 'is_promotional_applied']);
                }
            });
        }
    }
};

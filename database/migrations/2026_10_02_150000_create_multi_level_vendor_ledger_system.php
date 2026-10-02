<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Add hierarchy and rate control columns to marketing_vendors
        if (Schema::hasTable('marketing_vendors')) {
            Schema::table('marketing_vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('marketing_vendors', 'parent_vendor_id')) {
                    $table->unsignedBigInteger('parent_vendor_id')->nullable()->after('user_type');
                    $table->index('parent_vendor_id');
                }
                if (!Schema::hasColumn('marketing_vendors', 'head_vendor_id')) {
                    $table->unsignedBigInteger('head_vendor_id')->nullable()->after('parent_vendor_id');
                    $table->index('head_vendor_id');
                }
                if (!Schema::hasColumn('marketing_vendors', 'designation')) {
                    $table->string('designation', 100)->nullable()->after('team_type');
                }
                if (!Schema::hasColumn('marketing_vendors', 'hierarchy_level')) {
                    $table->unsignedTinyInteger('hierarchy_level')->default(0)->after('designation');
                }
                if (!Schema::hasColumn('marketing_vendors', 'is_rate_visible')) {
                    $table->boolean('is_rate_visible')->default(true)->after('rate_per_business');
                }
                if (!Schema::hasColumn('marketing_vendors', 'approved_by_vendor_id')) {
                    $table->unsignedBigInteger('approved_by_vendor_id')->nullable()->after('approved_by');
                }
            });
        }

        // 2. Create marketing_payment_ledgers table for multi-level lineage & transaction auditing
        if (Schema::hasTable('marketing_payment_ledgers')) {
            Schema::dropIfExists('marketing_payment_ledgers');
        }

        Schema::create('marketing_payment_ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acquisition_id');
            $table->unsignedBigInteger('head_vendor_id');
            $table->unsignedBigInteger('parent_vendor_id')->nullable();
            $table->unsignedBigInteger('vendor_id'); // Beneficiary vendor of this ledger entry
            $table->unsignedBigInteger('team_member_id')->nullable(); // Freelancer / Agent
            $table->unsignedBigInteger('acquired_user_id');
            $table->string('acquired_user_type', 20); // 'customer' or 'business'
            $table->string('service_category', 50)->default('General'); // 'Cab', 'Delivery', 'Home Service', etc.
            $table->decimal('rate_applied', 10, 2)->default(0.00);
            $table->decimal('earned_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('pending_amount', 10, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->string('payout_reference', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('acquisition_id', 'mkt_ledg_acq_idx');
            $table->index('head_vendor_id', 'mkt_ledg_head_idx');
            $table->index('parent_vendor_id', 'mkt_ledg_parent_idx');
            $table->index('vendor_id', 'mkt_ledg_vnd_idx');
            $table->index('team_member_id', 'mkt_ledg_tm_idx');
            $table->index(['acquired_user_id', 'acquired_user_type'], 'mkt_ledg_user_idx');
            $table->index('payment_status', 'mkt_ledg_status_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('marketing_payment_ledgers');

        if (Schema::hasTable('marketing_vendors')) {
            Schema::table('marketing_vendors', function (Blueprint $table) {
                $columns = ['parent_vendor_id', 'head_vendor_id', 'designation', 'hierarchy_level', 'is_rate_visible', 'approved_by_vendor_id'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('marketing_vendors', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

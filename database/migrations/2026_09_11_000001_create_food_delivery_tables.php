<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // seed defaults

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('food_restaurant_types')) {
            Schema::create('food_restaurant_types', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique(); // cloud_kitchen | actual_restaurant
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->decimal('onboarding_fee', 12, 2)->default(0);
                $table->string('approval_mode')->default('manual'); // auto | manual
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_owners')) {
            Schema::create('food_owners', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('phone', 20)->unique();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('otp', 10)->nullable();
                $table->timestamp('otp_expires_at')->nullable();
                $table->string('access_token', 64)->nullable()->index();
                $table->string('fcm_token')->nullable();
                $table->string('status')->default('active'); // active | blocked
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_restaurants')) {
            Schema::create('food_restaurants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('owner_id')->index();
                $table->unsignedBigInteger('type_id')->nullable()->index();
                $table->string('business_type')->nullable(); // cloud_kitchen | actual_restaurant
                $table->string('category')->nullable();
                $table->string('sub_category')->nullable();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('logo')->nullable();
                $table->string('cover_image')->nullable();
                $table->text('description')->nullable();
                $table->string('owner_name')->nullable();
                $table->string('owner_phone', 20)->nullable();
                $table->string('owner_email')->nullable();
                $table->string('address')->nullable();
                $table->string('landmark')->nullable();
                $table->string('area')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('pincode', 20)->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->time('opening_time')->nullable();
                $table->time('closing_time')->nullable();
                $table->string('working_days')->nullable(); // json csv
                $table->integer('avg_prep_minutes')->default(20);
                $table->decimal('delivery_radius_km', 8, 2)->default(5);
                $table->decimal('min_order_amount', 12, 2)->default(0);
                $table->decimal('max_order_amount', 12, 2)->nullable();
                $table->boolean('delivery_available')->default(true);
                $table->boolean('takeaway_available')->default(false);
                $table->boolean('dine_in_available')->default(false);
                $table->string('fssai_number')->nullable();
                $table->string('gst_number')->nullable();
                $table->string('pan_number')->nullable();
                $table->string('id_proof')->nullable();
                $table->string('business_proof')->nullable();
                $table->string('fssai_doc')->nullable();
                $table->string('gst_doc')->nullable();
                $table->string('cancelled_cheque')->nullable();
                $table->string('bank_account_name')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_ifsc')->nullable();
                $table->string('bank_branch')->nullable();
                $table->string('upi_id')->nullable();
                $table->string('onboarding_status')->default('pending_registration');
                // pending_registration|payment_pending|payment_success|pending_approval|active|rejected|suspended|temporarily_closed|permanently_closed
                $table->string('operational_status')->default('closed'); // open|busy|closed|temporarily_closed
                $table->text('rejection_reason')->nullable();
                $table->decimal('onboarding_fee_paid', 12, 2)->default(0);
                $table->string('onboarding_payment_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->decimal('rating_avg', 3, 2)->default(0);
                $table->integer('rating_count')->default(0);
                $table->boolean('auto_accept')->default(false);
                $table->boolean('is_premium')->default(false);
                $table->timestamp('premium_expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_onboarding_payments')) {
            Schema::create('food_onboarding_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->unsignedBigInteger('owner_id')->index();
                $table->decimal('amount', 12, 2);
                $table->string('currency', 10)->default('INR');
                $table->string('payment_method')->nullable();
                $table->string('gateway')->nullable();
                $table->string('gateway_order_id')->nullable();
                $table->string('gateway_payment_id')->nullable();
                $table->string('status')->default('pending'); // pending|processing|paid|failed|refunded
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_categories')) {
            Schema::create('food_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->string('name');
                $table->string('image')->nullable();
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_products')) {
            Schema::create('food_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->string('name');
                $table->string('image')->nullable();
                $table->text('description')->nullable();
                $table->string('food_type')->default('veg'); // veg|non_veg|egg
                $table->decimal('restaurant_price', 12, 2);
                $table->decimal('discount_price', 12, 2)->nullable();
                $table->integer('prep_minutes')->nullable();
                $table->integer('available_qty')->nullable();
                $table->string('availability')->default('available'); // available|out_of_stock|temporarily_unavailable
                $table->text('ingredients')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_product_addons')) {
            Schema::create('food_product_addons', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('name');
                $table->decimal('price', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_product_variants')) {
            Schema::create('food_product_variants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('name');
                $table->decimal('price', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_markup_rules')) {
            Schema::create('food_markup_rules', function (Blueprint $table) {
                $table->id();
                $table->string('scope')->default('global'); // global|restaurant|category|product
                $table->unsignedBigInteger('restaurant_id')->nullable()->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('rule_type')->default('percentage'); // percentage|flat
                $table->decimal('rule_value', 12, 2)->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_commission_rules')) {
            Schema::create('food_commission_rules', function (Blueprint $table) {
                $table->id();
                $table->string('scope')->default('global'); // global|restaurant_type|restaurant|category|product
                $table->unsignedBigInteger('restaurant_type_id')->nullable()->index();
                $table->unsignedBigInteger('restaurant_id')->nullable()->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('rule_type')->default('percentage'); // percentage|flat
                $table->decimal('rule_value', 12, 2)->default(10);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_charge_rules')) {
            Schema::create('food_charge_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->string('charge_type')->default('flat'); // percentage|flat|per_km|slab|dynamic
                $table->decimal('charge_value', 12, 2)->default(0);
                $table->decimal('min_amount', 12, 2)->nullable();
                $table->decimal('max_amount', 12, 2)->nullable();
                $table->decimal('order_min', 12, 2)->nullable();
                $table->decimal('order_max', 12, 2)->nullable();
                $table->json('slab_json')->nullable();
                $table->time('time_from')->nullable();
                $table->time('time_to')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_delivery_charge_rules')) {
            Schema::create('food_delivery_charge_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Default Delivery');
                $table->decimal('base_charge', 12, 2)->default(30);
                $table->decimal('free_radius_km', 8, 2)->default(0);
                $table->decimal('base_radius_km', 8, 2)->default(3);
                $table->decimal('per_km_charge', 12, 2)->default(10);
                $table->decimal('max_distance_km', 8, 2)->default(30);
                $table->boolean('allow_above_max')->default(false);
                $table->decimal('above_max_per_km', 12, 2)->nullable();
                $table->json('distance_slabs')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_premium_deals')) {
            Schema::create('food_premium_deals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->string('deal_type')->default('commission_off'); // commission_off|percent_discount|fixed_commission|markup_off
                $table->decimal('deal_value', 12, 2)->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_offers')) {
            Schema::create('food_offers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->string('name');
                $table->string('discount_type')->default('percentage'); // percentage|flat|bogo|combo
                $table->decimal('discount_value', 12, 2)->default(0);
                $table->decimal('min_order', 12, 2)->nullable();
                $table->decimal('max_discount', 12, 2)->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->string('status')->default('draft'); // draft|pending_approval|active|expired|paused
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_orders')) {
            Schema::create('food_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->string('customer_name')->nullable();
                $table->string('customer_phone', 20)->nullable();
                $table->text('delivery_address')->nullable();
                $table->string('delivery_landmark')->nullable();
                $table->decimal('delivery_lat', 10, 7)->nullable();
                $table->decimal('delivery_lng', 10, 7)->nullable();
                $table->decimal('distance_km', 8, 2)->nullable();
                $table->text('special_instructions')->nullable();
                $table->decimal('food_amount', 12, 2)->default(0); // restaurant original
                $table->decimal('markup_amount', 12, 2)->default(0);
                $table->decimal('food_subtotal', 12, 2)->default(0); // customer food
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('platform_charges', 12, 2)->default(0);
                $table->decimal('other_charges', 12, 2)->default(0);
                $table->decimal('delivery_charge', 12, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('customer_payable', 12, 2)->default(0);
                $table->decimal('commission_amount', 12, 2)->default(0);
                $table->decimal('restaurant_net_amount', 12, 2)->default(0);
                $table->decimal('company_due_amount', 12, 2)->default(0);
                $table->decimal('penalty_amount', 12, 2)->default(0);
                $table->decimal('refund_amount', 12, 2)->default(0);
                $table->string('payment_method')->nullable(); // wallet|upi|cod
                $table->string('payment_status')->default('pending');
                // pending|paid|cash_pending|cash_collected|failed|refunded
                $table->string('order_status')->default('pending');
                // pending|restaurant_accepted|preparing|ready_for_pickup|rider_assigned|rider_at_restaurant|food_picked_up|out_for_delivery|rider_at_location|delivered|completed|rejected|cancelled|disputed
                $table->string('settlement_status')->default('pending'); // pending|processing|settled|held
                $table->integer('prep_minutes')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('ready_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->string('reject_reason')->nullable();
                $table->unsignedBigInteger('rider_id')->nullable()->index();
                $table->string('rider_name')->nullable();
                $table->string('rider_phone')->nullable();
                $table->string('rider_status')->nullable();
                $table->string('pickup_otp', 10)->nullable();
                $table->string('delivery_otp', 10)->nullable();
                $table->unsignedBigInteger('offer_id')->nullable();
                $table->json('charges_breakdown')->nullable();
                $table->json('meta')->nullable();
                $table->boolean('is_test')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_order_items')) {
            Schema::create('food_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('product_name');
                $table->integer('quantity')->default(1);
                $table->decimal('restaurant_unit_price', 12, 2)->default(0);
                $table->decimal('markup_unit', 12, 2)->default(0);
                $table->decimal('customer_unit_price', 12, 2)->default(0);
                $table->decimal('addons_total', 12, 2)->default(0);
                $table->decimal('line_total', 12, 2)->default(0);
                $table->string('variant_name')->nullable();
                $table->json('addons_json')->nullable();
                $table->text('instructions')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_settlements')) {
            Schema::create('food_settlements', function (Blueprint $table) {
                $table->id();
                $table->string('settlement_number')->unique();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->date('period_from')->nullable();
                $table->date('period_to')->nullable();
                $table->integer('orders_count')->default(0);
                $table->decimal('gross_sales', 12, 2)->default(0);
                $table->decimal('commission', 12, 2)->default(0);
                $table->decimal('other_charges', 12, 2)->default(0);
                $table->decimal('refunds', 12, 2)->default(0);
                $table->decimal('penalties', 12, 2)->default(0);
                $table->decimal('adjustments', 12, 2)->default(0);
                $table->decimal('net_amount', 12, 2)->default(0);
                $table->string('status')->default('pending'); // pending|processing|approved|paid|failed
                $table->string('transaction_ref')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_due_payments')) {
            Schema::create('food_due_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->nullable()->index();
                $table->unsignedBigInteger('rider_id')->nullable()->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('party_type'); // restaurant|rider
                $table->string('due_type')->default('commission'); // commission|cod|penalty|other
                $table->decimal('amount', 12, 2);
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->string('status')->default('pending'); // pending|partial|paid
                $table->string('payment_ref')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_transactions')) {
            Schema::create('food_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('txn_number')->unique();
                $table->unsignedBigInteger('restaurant_id')->nullable()->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('txn_type'); // onboarding|order_payment|due_payment|settlement|refund|penalty
                $table->decimal('amount', 12, 2);
                $table->string('payment_method')->nullable();
                $table->string('status')->default('pending');
                $table->string('gateway_ref')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_reviews')) {
            Schema::create('food_reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('restaurant_id')->index();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('rider_id')->nullable();
                $table->integer('restaurant_rating')->nullable();
                $table->integer('rider_rating')->nullable();
                $table->text('restaurant_review')->nullable();
                $table->text('rider_review')->nullable();
                $table->json('restaurant_tags')->nullable();
                $table->json('rider_tags')->nullable();
                $table->text('restaurant_reply')->nullable();
                $table->timestamp('replied_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_disputes')) {
            Schema::create('food_disputes', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number')->unique();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->unsignedBigInteger('restaurant_id')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('rider_id')->nullable();
                $table->string('raised_by'); // customer|restaurant|rider|admin
                $table->string('issue_type');
                $table->text('description')->nullable();
                $table->string('priority')->default('medium'); // low|medium|high|critical
                $table->string('status')->default('open'); // open|under_review|in_progress|resolved|closed
                $table->string('proof_image')->nullable();
                $table->text('resolution')->nullable();
                $table->decimal('refund_amount', 12, 2)->nullable();
                $table->decimal('penalty_amount', 12, 2)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_support_tickets')) {
            Schema::create('food_support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number')->unique();
                $table->unsignedBigInteger('restaurant_id')->nullable()->index();
                $table->unsignedBigInteger('owner_id')->nullable()->index();
                $table->string('category')->nullable();
                $table->string('subject');
                $table->text('message');
                $table->string('priority')->default('medium');
                $table->string('status')->default('open');
                $table->string('attachment')->nullable();
                $table->text('admin_reply')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_notifications')) {
            Schema::create('food_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('restaurant_id')->nullable()->index();
                $table->unsignedBigInteger('owner_id')->nullable()->index();
                $table->string('title');
                $table->text('message')->nullable();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('ref_id')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_settings')) {
            Schema::create('food_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Seed default types + rules
        if (Schema::hasTable('food_restaurant_types') && DB::table('food_restaurant_types')->count() === 0) {
            DB::table('food_restaurant_types')->insert([
                [
                    'code' => 'cloud_kitchen',
                    'name' => 'Cloud Kitchen',
                    'description' => 'Home / cloud kitchen — online orders only',
                    'is_active' => true,
                    'onboarding_fee' => 499,
                    'approval_mode' => 'manual',
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'actual_restaurant',
                    'name' => 'Actual Restaurant',
                    'description' => 'Physical restaurant — dine-in / takeaway / delivery',
                    'is_active' => true,
                    'onboarding_fee' => 999,
                    'approval_mode' => 'manual',
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if (Schema::hasTable('food_commission_rules') && DB::table('food_commission_rules')->count() === 0) {
            DB::table('food_commission_rules')->insert([
                'scope' => 'global',
                'rule_type' => 'percentage',
                'rule_value' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('food_markup_rules') && DB::table('food_markup_rules')->count() === 0) {
            DB::table('food_markup_rules')->insert([
                'scope' => 'global',
                'rule_type' => 'percentage',
                'rule_value' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('food_delivery_charge_rules') && DB::table('food_delivery_charge_rules')->count() === 0) {
            DB::table('food_delivery_charge_rules')->insert([
                'name' => 'Default Delivery',
                'base_charge' => 30,
                'base_radius_km' => 3,
                'per_km_charge' => 10,
                'max_distance_km' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('food_settings') && DB::table('food_settings')->count() === 0) {
            $defaults = [
                'nearby_restaurant_radius_km' => '15',
                'nearby_food_pickup_radius_km' => '2',
                'max_extra_delivery_distance_km' => '2',
                'max_extra_delivery_time_min' => '15',
                'max_food_orders_per_rider' => '2',
                'max_parcel_orders_per_rider' => '2',
                'max_total_active_orders_per_rider' => '3',
                'order_accept_escalation_min' => '5',
                'prep_delay_admin_alert_min' => '10',
            ];
            foreach ($defaults as $k => $v) {
                DB::table('food_settings')->insert([
                    'key' => $k,
                    'value' => $v,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'food_settings',
            'food_notifications',
            'food_support_tickets',
            'food_disputes',
            'food_reviews',
            'food_transactions',
            'food_due_payments',
            'food_settlements',
            'food_order_items',
            'food_orders',
            'food_offers',
            'food_premium_deals',
            'food_delivery_charge_rules',
            'food_charge_rules',
            'food_commission_rules',
            'food_markup_rules',
            'food_product_variants',
            'food_product_addons',
            'food_products',
            'food_categories',
            'food_onboarding_payments',
            'food_restaurants',
            'food_owners',
            'food_restaurant_types',
        ];
        foreach ($tables as $t) {
            Schema::dropIfExists($t);
        }
    }
};

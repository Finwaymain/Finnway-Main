<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Subscription Plans (Driver / Business Partner)
        if (Schema::hasTable('subscription_plans')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('subscription_plans', 'tier_level')) {
                    $table->integer('tier_level')->default(1)->after('id');
                }
                if (!Schema::hasColumn('subscription_plans', 'commission_rate')) {
                    $table->decimal('commission_rate', 5, 2)->default(10.00)->after('price');
                }
                if (!Schema::hasColumn('subscription_plans', 'benefits_list')) {
                    $table->json('benefits_list')->nullable()->after('commission_rate');
                }
                if (!Schema::hasColumn('subscription_plans', 'badge')) {
                    $table->string('badge', 50)->nullable()->after('benefits_list');
                }
            });

            // The 26 official benefits for Business Partner / Driver Subscription
            $driver26Benefits = [
                "Instant Payout / Daily Withdrawal",
                "Zero Commission on Rides / Orders",
                "Priority Booking Dispatch",
                "Premium Customer Support",
                "Dedicated Relationship Manager",
                "Free Marketing & Profile Promotion",
                "Verified Partner Badge",
                "Access to High-Value Bookings",
                "Advanced Analytics & Earnings Report",
                "Custom Service Area Selection",
                "Fuel / Vehicle Maintenance Discounts",
                "Free Health & Accidental Insurance Cover",
                "Priority Customer Care (No Waiting)",
                "Free Replacement of Damaged QR / Standee",
                "Multi-City Booking Access",
                "Festival Bonus & Incentive Eligibility",
                "Customer Review Removal Request (Unfair reviews)",
                "Free Uniform / Merchandising Top-Up",
                "Direct Customer Chat Feature",
                "Flexible Working Hours Toggle",
                "Peak Hour Surcharge Earnings (100% to partner)",
                "Weekly Training & Skill Upgradation",
                "Referral Bonus Booster (2x Earnings)",
                "Zero Cancellation Penalty (up to 3/month)",
                "Tax & GST Invoicing Assistance",
                "VIP Partner Club Membership"
            ];

            // Seed/Update Professional Plan (₹2,500/yr)
            $proPlan = DB::table('subscription_plans')->where('name', 'LIKE', '%Professional%')->first();
            if ($proPlan) {
                DB::table('subscription_plans')->where('id', $proPlan->id)->update([
                    'tier_level' => 2,
                    'price' => '2500.00',
                    'commission_rate' => 0.00,
                    'expiryDay' => '365',
                    'badge' => 'Most Popular',
                    'plan_tier' => 'professional',
                    'plan_points' => json_encode($driver26Benefits),
                    'benefits_list' => json_encode($driver26Benefits),
                    'isEnable' => 'true',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('subscription_plans')->insert([
                    'tier_level' => 2,
                    'name' => 'Professional Plan',
                    'price' => '2500.00',
                    'commission_rate' => 0.00,
                    'bookingLimit' => '999999',
                    'expiryDay' => '365',
                    'description' => 'Zero commission on all rides & orders. Keep 100% of your earnings with all 26 VIP partner benefits.',
                    'image' => '',
                    'type' => 'paid',
                    'plan_tier' => 'professional',
                    'isEnable' => 'true',
                    'place' => '1',
                    'badge' => 'Most Popular',
                    'plan_points' => json_encode($driver26Benefits),
                    'benefits_list' => json_encode($driver26Benefits),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Consumer Premium Plans
        if (Schema::hasTable('consumer_premium_plans')) {
            Schema::table('consumer_premium_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('consumer_premium_plans', 'tier_level')) {
                    $table->integer('tier_level')->default(1)->after('id');
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'badge')) {
                    $table->string('badge', 50)->nullable()->after('name');
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'chargeable_items')) {
                    $table->json('chargeable_items')->nullable()->after('description');
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'benefits_list')) {
                    $table->json('benefits_list')->nullable()->after('chargeable_items');
                }
            });

            // 10 Chargeable Items under Basic (Free) Plan
            $chargeableItems = [
                ['name' => 'Platform Fee', 'charge' => '₹5 - ₹15 per booking', 'status' => 'Paid'],
                ['name' => 'Surge / Peak Hour Pricing', 'charge' => 'Applicable', 'status' => 'Paid'],
                ['name' => 'Delivery / Shipping Fee', 'charge' => 'Full standard charge', 'status' => 'Paid'],
                ['name' => 'Cancellation Charges', 'charge' => 'Standard cancellation fee', 'status' => 'Paid'],
                ['name' => 'Night Surcharge', 'charge' => 'Applicable on night bookings', 'status' => 'Paid'],
                ['name' => 'Priority Dispatch Fee', 'charge' => 'Extra for urgent bookings', 'status' => 'Paid'],
                ['name' => 'Customer Support', 'charge' => 'Standard queue (Waiting time)', 'status' => 'Standard'],
                ['name' => 'Cashback & Offers', 'charge' => 'Basic public offers only', 'status' => 'Limited'],
                ['name' => 'Free Ride Cancellation Window', 'charge' => 'Only 2 minutes', 'status' => 'Limited'],
                ['name' => 'Payment Convenience Fee', 'charge' => 'Applicable on certain modes', 'status' => 'Paid'],
            ];

            // Consumer Unlocked Member Perks
            $consumerUnlockedBenefits = [
                "Zero Platform Fee on all bookings",
                "Zero Surge Pricing (No peak-hour hikes)",
                "Free Delivery on Parcel & Food (up to 5 km)",
                "Free Cancellation (up to 3 per month)",
                "Priority Booking - Nearest driver/partner assigned first",
                "24/7 Dedicated VIP Support (No waiting)",
                "Exclusive Member Discounts & Higher Cashback (Up to 20%)",
                "Free Ride Upgrades (Subject to availability)",
                "Extended Free Waiting Time (up to 10 mins)",
                "Family Sharing (Share benefits with 1 member)"
            ];

            $consumerPlans = [
                [
                    'tier_level' => 1,
                    'name' => 'Basic Plan',
                    'price' => 0.00,
                    'validity_days' => 365,
                    'description' => 'Pay-as-you-go basic access with standard platform fees and public offers.',
                    'badge' => 'Current Plan',
                    'chargeable_items' => json_encode($chargeableItems),
                    'benefits_list' => json_encode(['Basic ride & delivery access', 'Public offers only']),
                    'status' => 'active',
                    'display_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tier_level' => 2,
                    'name' => 'Standard Plan',
                    'price' => 500.00,
                    'validity_days' => 30,
                    'description' => 'Zero platform fees, zero surge charges, and standard member discounts.',
                    'badge' => 'Most Popular',
                    'chargeable_items' => json_encode($chargeableItems),
                    'benefits_list' => json_encode(array_slice($consumerUnlockedBenefits, 0, 5)),
                    'status' => 'active',
                    'display_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tier_level' => 3,
                    'name' => 'Executive Plan',
                    'price' => 700.00,
                    'validity_days' => 30,
                    'description' => 'Priority dispatch, zero surge, free food delivery & 10% instant cashback.',
                    'badge' => 'Recommended',
                    'chargeable_items' => json_encode($chargeableItems),
                    'benefits_list' => json_encode(array_slice($consumerUnlockedBenefits, 0, 7)),
                    'status' => 'active',
                    'display_order' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tier_level' => 4,
                    'name' => 'VIP Plan',
                    'price' => 900.00,
                    'validity_days' => 30,
                    'description' => 'All-inclusive VIP membership with maximum cashback, zero platform fees & family sharing.',
                    'badge' => 'VIP Tier',
                    'chargeable_items' => json_encode($chargeableItems),
                    'benefits_list' => json_encode($consumerUnlockedBenefits),
                    'status' => 'active',
                    'display_order' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tier_level' => 5,
                    'name' => 'Premium Annual Plan',
                    'price' => 1100.00,
                    'validity_days' => 365,
                    'description' => 'Annual mega savings plan: full year of VIP perks, free shipping, and maximum rewards.',
                    'badge' => 'Best Value',
                    'chargeable_items' => json_encode($chargeableItems),
                    'benefits_list' => json_encode($consumerUnlockedBenefits),
                    'status' => 'active',
                    'display_order' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($consumerPlans as $cp) {
                $existing = DB::table('consumer_premium_plans')->where('name', $cp['name'])->first();
                if ($existing) {
                    DB::table('consumer_premium_plans')->where('id', $existing->id)->update($cp);
                } else {
                    DB::table('consumer_premium_plans')->insert($cp);
                }
            }
        }

        // 3. Add email verification fields to tj_conducteur and tj_user_app
        if (Schema::hasTable('tj_conducteur') && !Schema::hasColumn('tj_conducteur', 'email_verified_at')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }
        if (Schema::hasTable('tj_user_app') && !Schema::hasColumn('tj_user_app', 'email_verified_at')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscription_plans')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->dropColumn(['tier_level', 'commission_rate', 'benefits_list', 'badge']);
            });
        }
        if (Schema::hasTable('consumer_premium_plans')) {
            Schema::table('consumer_premium_plans', function (Blueprint $table) {
                $table->dropColumn(['tier_level', 'badge', 'chargeable_items', 'benefits_list']);
            });
        }
        if (Schema::hasTable('tj_conducteur') && Schema::hasColumn('tj_conducteur', 'email_verified_at')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                $table->dropColumn('email_verified_at');
            });
        }
        if (Schema::hasTable('tj_user_app') && Schema::hasColumn('tj_user_app', 'email_verified_at')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                $table->dropColumn('email_verified_at');
            });
        }
    }
};

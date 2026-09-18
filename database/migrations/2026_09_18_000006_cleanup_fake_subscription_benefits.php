<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Clean fake 26 benefits from subscription_plans table
        if (Schema::hasTable('subscription_plans')) {
            $plans = DB::table('subscription_plans')->get();
            foreach ($plans as $plan) {
                $points = is_array($plan->plan_points) ? $plan->plan_points : (json_decode($plan->plan_points ?? '[]', true) ?: []);
                $benefits = is_array($plan->benefits_list) ? $plan->benefits_list : (json_decode($plan->benefits_list ?? '[]', true) ?: []);

                $hasFakeInPoints = is_array($points) && count($points) >= 20 && in_array('Instant Payout / Daily Withdrawal', $points);
                $hasFakeInBenefits = is_array($benefits) && count($benefits) >= 20 && in_array('Instant Payout / Daily Withdrawal', $benefits);

                $update = [];
                if ($hasFakeInPoints) {
                    $update['plan_points'] = json_encode(['Access to all features in the driver app']);
                }
                if ($hasFakeInBenefits) {
                    $update['benefits_list'] = null;
                }

                if (!empty($update)) {
                    DB::table('subscription_plans')->where('id', $plan->id)->update($update);
                }
            }
        }

        // 2. Clean fake 26 benefits from tj_conducteur.subscription_plan column
        if (Schema::hasTable('tj_conducteur') && Schema::hasColumn('tj_conducteur', 'subscription_plan')) {
            $drivers = DB::table('tj_conducteur')->whereNotNull('subscription_plan')->get();
            foreach ($drivers as $driver) {
                $subPlan = is_array($driver->subscription_plan) ? $driver->subscription_plan : json_decode($driver->subscription_plan ?? '', true);
                if (is_array($subPlan)) {
                    $pts = $subPlan['plan_points'] ?? $subPlan['benefits_list'] ?? [];
                    if (is_array($pts) && count($pts) >= 20 && in_array('Instant Payout / Daily Withdrawal', $pts)) {
                        $actualPlan = !empty($driver->subscriptionPlanId) ? DB::table('subscription_plans')->where('id', $driver->subscriptionPlanId)->first() : null;
                        $realPoints = $actualPlan ? (json_decode($actualPlan->plan_points ?? '[]', true) ?: ['Access to all features in the driver app']) : [];
                        $subPlan['plan_points'] = $realPoints;
                        $subPlan['benefits_list'] = $realPoints;
                        DB::table('tj_conducteur')->where('id', $driver->id)->update([
                            'subscription_plan' => json_encode($subPlan)
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
    }
};

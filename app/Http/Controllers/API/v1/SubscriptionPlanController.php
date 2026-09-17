<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\Controller;

use App\Models\SubscriptionPlan;

use App\Models\SubscriptionHistory;

use App\Models\Driver;
use App\Models\DriverTransaction;
use App\Models\ConsumerPremiumPlan;
use App\Models\UserApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Carbon\Carbon;

class SubscriptionPlanController extends Controller

{



    public function __construct()

    {

    }

    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function getPlanList(Request $request)
    {
        $output = [];
        $subscriptionPlan = SubscriptionPlan::where('isEnable', '=', 'true')->orderBy('tier_level', 'asc')->get();

        $default26Benefits = [
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

        if (count($subscriptionPlan) > 0) {
            foreach ($subscriptionPlan as $row) {
                $row->id = (string)$row->id;
                $row->tier_level = intval($row->tier_level ?? 1);
                $row->commission_rate = floatval($row->commission_rate ?? ($row->tier_level === 1 ? 10.00 : 0.00));
                $row->badge = $row->badge ?? ($row->tier_level >= 2 ? 'Most Popular' : '');

                if ($row->image != '') {
                    if (file_exists(public_path('assets/images/subscription' . '/' . $row->image))) {
                        $row->image = asset('assets/images/subscription') . '/' . $row->image;
                    } else {
                        $row->image = asset('assets/images/placeholder_image.jpg');
                    }
                }

                $planPoints = is_array($row->plan_points) ? $row->plan_points : (json_decode($row->plan_points ?? '[]', true) ?: []);
                if (empty($planPoints)) {
                    $planPoints = $default26Benefits;
                }
                if (Schema::hasColumn('subscription_plans', 'cashback_on_purchase') && floatval($row->cashback_on_purchase ?? 0) > 0) {
                    $planPoints[] = "₹{$row->cashback_on_purchase} instant cashback on plan purchase";
                }
                $row->plan_points = $planPoints;
                $row->benefits_list = is_array($row->benefits_list) ? $row->benefits_list : (json_decode($row->benefits_list ?? '[]', true) ?: $default26Benefits);

                $output[] = $row;
            }

            return response()->json([
                'success' => 'success',
                'error' => null,
                'message' => 'Subscription plans fetched successfully',
                'data' => $output,
                'commission_loss_calculator' => [
                    'standard_commission_pct' => 10,
                    'example_monthly_earnings' => 50000,
                    'example_monthly_loss' => 5000,
                    'example_yearly_loss' => 60000,
                    'cta_text' => 'Switch to a Subscription Plan & Save Up to ₹60,000/Year!',
                    'all_26_locked_benefits' => $default26Benefits,
                ],
            ]);
        } else {
            return response()->json([
                'success' => 'Failed',
                'error' => 'No Data Found',
                'message' => null,
            ]);
        }
    }

    public function getConsumerPlans(Request $request)
    {
        $output = [];
        $consumerPlans = ConsumerPremiumPlan::where('status', 'active')->orderBy('display_order')->get();

        $defaultChargeableItems = [
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

        $defaultUnlockedBenefits = [
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

        if (count($consumerPlans) > 0) {
            foreach ($consumerPlans as $row) {
                $item = new \stdClass();
                $item->id = (string)$row->id;
                $item->tier_level = intval($row->tier_level ?? 1);
                $item->name = (string)$row->name;
                $item->price = (string)$row->price;
                $item->badge = $row->badge ?? (floatval($row->price) == 500 ? 'Most Popular' : (floatval($row->price) >= 1100 ? 'Best Value' : ''));
                $item->expiryDay = (string)$row->validity_days;
                $item->description = $row->description ?? '';
                $item->type = floatval($row->price) > 0 ? 'paid' : 'free';
                $item->isEnable = 'true';
                $item->place = (string)($row->display_order ?? '1');
                $item->image = asset('assets/images/placeholder_image.jpg');
                $item->cashback_on_purchase = (string)($row->cashback_on_purchase ?? '0');
                
                $chargeables = is_array($row->chargeable_items) ? $row->chargeable_items : (json_decode($row->chargeable_items ?? '[]', true) ?: $defaultChargeableItems);
                $item->chargeable_items = $chargeables;

                $benefits = is_array($row->benefits_list) ? $row->benefits_list : (json_decode($row->benefits_list ?? '[]', true) ?: $defaultUnlockedBenefits);
                $item->benefits_list = $benefits;

                // Build plan points from consumer plan features
                $planPoints = [];
                if ($row->discount_cab > 0) $planPoints[] = "{$row->discount_cab}% discount on Cab rides";
                if ($row->discount_bike > 0) $planPoints[] = "{$row->discount_bike}% discount on Bike rides";
                if ($row->sender_cashback_value > 0) $planPoints[] = "{$row->sender_cashback_value}% cashback on sending money";
                if ($row->receiver_cashback_value > 0) $planPoints[] = "{$row->receiver_cashback_value}% cashback on receiving money";
                if (Schema::hasColumn('consumer_premium_plans', 'cashback_on_purchase') && floatval($row->cashback_on_purchase ?? 0) > 0) {
                    $planPoints[] = "₹{$row->cashback_on_purchase} instant cashback on plan purchase";
                }
                if ($row->free_shipping) $planPoints[] = "Free shipping on marketplace orders";
                if ($row->loan_personal) $planPoints[] = "Personal loan access";
                if ($row->loan_business) $planPoints[] = "Business loan access";
                if ($row->loan_virtual) $planPoints[] = "Virtual credit limit: ₹{$row->virtual_credit_limit}";
                
                if (empty($planPoints)) {
                    $planPoints = $benefits;
                }
                
                $item->plan_points = $planPoints;
                $output[] = $item;
            }
        } else {
            // Fallback to active subscription plans if consumer plans are not configured
            $subPlans = SubscriptionPlan::where('isEnable', '=', 'true')->get();
            foreach ($subPlans as $row) {
                $item = new \stdClass();
                $item->id = (string)$row->id;
                $item->tier_level = intval($row->tier_level ?? 1);
                $item->name = (string)$row->name;
                $item->price = (string)$row->price;
                $item->badge = $row->badge ?? '';
                $item->expiryDay = (string)$row->expiryDay;
                $item->description = $row->description ?? '';
                $item->type = (string)$row->type;
                $item->isEnable = (string)$row->isEnable;
                $item->place = (string)$row->place;
                $item->image = asset('assets/images/placeholder_image.jpg');
                $item->cashback_on_purchase = (string)($row->cashback_on_purchase ?? '0');
                $planPoints = is_array($row->plan_points) ? $row->plan_points : (json_decode($row->plan_points ?? '[]', true) ?: $defaultUnlockedBenefits);
                $item->plan_points = $planPoints;
                $item->chargeable_items = $defaultChargeableItems;
                $item->benefits_list = $defaultUnlockedBenefits;
                $output[] = $item;
            }
        }

        if (!empty($output)) {
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Consumer plans fetched successfully';
            $response['chargeable_items_warning'] = [
                'banner' => 'You are currently paying extra fees on every booking!',
                'savings_callout' => 'Consumers on Standard Plan save an average of ₹850/month!',
                'items' => $defaultChargeableItems,
            ];
            $response['data'] = $output;
        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'No Data Found';
            $response['message'] = null;
        }

        return response()->json($response);
    }

    public function setConsumerSubscription(Request $request){
        try {
            $planId = $request->get('planId');
            $userId = $request->get('userId');
            $paymentType = strtolower((string) $request->get('paymentType', ''));

            if (empty($planId) || empty($userId)) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'planId and userId are required',
                    'message' => 'Invalid request',
                ], 422);
            }

            $planData = ConsumerPremiumPlan::where('id', $planId)->first();
            if (!$planData) {
                // Fallback to SubscriptionPlan table if not in ConsumerPremiumPlan
                $subPlan = SubscriptionPlan::where('id', $planId)->first();
                if ($subPlan) {
                    $planData = new ConsumerPremiumPlan();
                    $planData->id = $subPlan->id;
                    $planData->name = $subPlan->name;
                    $planData->price = $subPlan->price;
                    $planData->validity_days = intval($subPlan->expiryDay > 0 ? $subPlan->expiryDay : 365);
                    $planData->description = $subPlan->description;
                    $planData->cashback_on_purchase = $subPlan->cashback_on_purchase ?? 0;
                }
            }

            $user = UserApp::where('id', $userId)->first();

            if (!$planData) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Consumer plan not found',
                    'message' => 'Invalid plan ID',
                ], 404);
            }

            if (!$user) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'User not found',
                    'message' => 'Invalid user ID',
                ], 404);
            }

            // Enforce No-Downgrade rule
            if (!empty($user->consumer_plan_id)) {
                $currentPlan = ConsumerPremiumPlan::where('id', $user->consumer_plan_id)->first();
                if ($currentPlan && isset($currentPlan->tier_level)) {
                    $newTier = intval($planData->tier_level ?? 1);
                    $currTier = intval($currentPlan->tier_level ?? 1);
                    if ($newTier < $currTier) {
                        return response()->json([
                            'success' => 'Failed',
                            'error' => 'Downgrade is not permitted. You can only upgrade to a higher-tier plan.',
                            'message' => 'Downgrade not allowed',
                        ], 422);
                    }
                }
            }

            // Verify MPIN when paying with wallet
            if ($paymentType === 'wallet') {
                $mpin = trim((string) $request->get('mpin'));
                if (empty($mpin)) {
                    return response()->json([
                        'success' => 'Failed',
                        'error' => 'MPIN is required for wallet payment',
                        'message' => 'Please enter your MPIN to authorize this transaction',
                    ], 400);
                }

                $hashedMpin = md5($mpin);
                $dbMpin = $user->m_pin ?? null;
                $dbMdp = $user->mdp ?? null;
                $mpinValid = ($dbMdp === $hashedMpin) || ($dbMdp === $mpin) || (!empty($dbMpin) && ($dbMpin === $mpin || $dbMpin === $hashedMpin));
                if (!$mpinValid) {
                    return response()->json([
                        'success' => 'Failed',
                        'error' => 'Incorrect MPIN',
                        'message' => 'The MPIN you entered is incorrect. Please try again.',
                    ], 400);
                }

                if (floatval($user->amount) < floatval($planData->price)) {
                    return response()->json([
                        'success' => 'Failed',
                        'error' => 'Insufficient wallet balance',
                        'message' => "You don't have sufficient balance to purchase this plan",
                    ]);
                }
            }

            DB::beginTransaction();

            if ($paymentType === 'wallet') {
                $newWalletBalance = floatval($user->amount) - floatval($planData->price);
                UserApp::where('id', $userId)->update(['amount' => $newWalletBalance]);
                $this->recordPlanWalletDebit($user, floatval($planData->price), $planData->name, (int) $planData->id, 'customer');
            }

            $expiryDate = Carbon::now()->addDays((int) ($planData->validity_days ?? 365));
            $this->updateConsumerPlanOnUser((int) $userId, $planData, $expiryDate);

            $cashbackAmount = Schema::hasColumn('consumer_premium_plans', 'cashback_on_purchase')
                ? floatval($planData->cashback_on_purchase ?? 0)
                : 0.0;
            $this->applyPlanPurchaseCashback($user, $cashbackAmount, $planData->name, (int) $planData->id, 'customer');

            // Trigger referral reward for referrer
            try {
                \App\Services\ReferralRewardService::processReward((int)$userId, 'consumer_subscription', floatval($planData->price ?? 0), 'Consumer Plan Purchase');
            } catch (\Throwable $th) {
                \Log::error("Referral reward error for consumer subscription: " . $th->getMessage());
            }

            DB::commit();

            $user = UserApp::where('id', $userId)->first();

            // Send Confirmation Email with invoice & unlocked benefits
            try {
                $userEmail = $user->email ?? '';
                if (!empty($userEmail) && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                    $userName = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? ''));
                    $benefits = json_decode($planData->benefits_list ?? '[]', true) ?: (is_array($planData->plan_points) ? $planData->plan_points : (json_decode($planData->plan_points ?? '[]', true) ?: []));
                    \App\Services\PlanEmailService::sendPlanActivationEmail([
                        'email' => $userEmail,
                        'user_name' => !empty($userName) ? $userName : 'Valued Customer',
                        'user_id' => $user->id,
                        'user_type' => 'customer',
                        'plan_name' => $planData->name,
                        'amount' => $planData->price,
                        'validity' => ($planData->validity_days ?? '30') . ' Days',
                        'expiry_date' => $expiryDate ? $expiryDate->format('d M Y') : 'Active',
                        'txn_id' => 'FWC-' . strtoupper(uniqid()),
                        'benefits' => $benefits,
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error("Failed to send consumer plan activation email: " . $e->getMessage());
            }

            return response()->json([
                'success' => 'success',
                'error' => null,
                'message' => 'Consumer subscription added successfully',
                'cashback_credited' => $cashbackAmount,
                'data' => [
                    'consumer_plan_id' => Schema::hasColumn('tj_user_app', 'consumer_plan_id')
                        ? (string) ($user->consumer_plan_id ?? $planData->id)
                        : (string) $planData->id,
                    'consumer_plan_expiry_date' => $expiryDate->toDateTimeString(),
                    'consumer_plan' => $planData->toArray(),
                    'amount' => (string) ($user->amount ?? '0'),
                ],
            ]);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            \Log::error('setConsumerSubscription failed', [
                'planId' => $request->get('planId'),
                'userId' => $request->get('userId'),
                'paymentType' => $request->get('paymentType'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => 'Failed',
                'error' => 'Unable to activate subscription',
                'message' => config('app.debug') ? $e->getMessage() : 'Server error while activating plan. Please contact support.',
            ], 500);
        }
    }

    private function updateConsumerPlanOnUser(int $userId, ConsumerPremiumPlan $planData, Carbon $expiryDate): void
    {
        $updateData = [];

        if (Schema::hasColumn('tj_user_app', 'consumer_plan_id')) {
            $updateData['consumer_plan_id'] = $planData->id;
        }
        if (Schema::hasColumn('tj_user_app', 'consumer_plan_expiry_date')) {
            $updateData['consumer_plan_expiry_date'] = $expiryDate;
        }
        if (Schema::hasColumn('tj_user_app', 'consumer_plan')) {
            $updateData['consumer_plan'] = json_encode($planData->toArray());
        }

        if (! empty($updateData)) {
            UserApp::where('id', $userId)->update($updateData);
        }
    }

    public function setData(Request $request){
        $planId = $request->get('planId');
        $driverId = $request->get('driverId');
        $paymentType = $request->get('paymentType');
        $subscriptionData=SubscriptionPlan::where('id', $planId)->first();
        $driver = Driver::where('id', $driverId)->first();
        
        if (!$subscriptionData) {
            $response['success'] = 'Failed';
            $response['error'] = 'Subscription plan not found';
            $response['message'] = 'Invalid plan ID';
            return response()->json($response);
        }

        if (!$driver) {
            $response['success'] = 'Failed';
            $response['error'] = 'Driver not found';
            $response['message'] = 'Invalid driver ID';
            return response()->json($response);
        }

        // Enforce No-Downgrade rule for Driver
        if (!empty($driver->subscriptionPlanId)) {
            $currentPlan = SubscriptionPlan::where('id', $driver->subscriptionPlanId)->first();
            if ($currentPlan && isset($currentPlan->tier_level)) {
                $newTier = intval($subscriptionData->tier_level ?? 1);
                $currTier = intval($currentPlan->tier_level ?? 1);
                if ($newTier < $currTier) {
                    return response()->json([
                        'success' => 'Failed',
                        'error' => 'Downgrade is not permitted. You can only upgrade to a higher-tier plan.',
                        'message' => 'Downgrade not allowed',
                    ], 422);
                }
            }
        }

        if(strtolower($paymentType)=='wallet'){
            $mpin = trim((string) $request->get('mpin'));
            if (empty($mpin)) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'MPIN is required for wallet payment',
                    'message' => 'Please enter your MPIN to authorize this transaction',
                ]);
            }

            $hashedMpin = md5($mpin);
            $dbMpin = $driver->m_pin ?? null;
            $dbMdp = $driver->mdp ?? null;
            $mpinValid = ($dbMdp === $hashedMpin) || ($dbMdp === $mpin) || (!empty($dbMpin) && ($dbMpin === $mpin || $dbMpin === $hashedMpin));
            if (!$mpinValid) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Incorrect MPIN',
                    'message' => 'The MPIN you entered is incorrect. Please try again.',
                ]);
            }

            if(floatval($driver->amount)<floatval($subscriptionData->price)){
                $response['success'] = 'Failed';
                $response['error'] = 'Insufficient wallet balance';
                $response['message'] = "You don't have sufficient balance to purchase this plan";
                return response()->json($response);
            }

            $newWalletBalance = floatval($driver->amount) - floatval($subscriptionData->price);
            Driver::where('id', $driverId)->update(['amount'=>$newWalletBalance]);
            $this->recordPlanWalletDebit($driver, floatval($subscriptionData->price), $subscriptionData->name, (int) $subscriptionData->id, 'driver');
        }
        
        $subscriptionPlanId = $subscriptionData->id;
        $subscriptionTotalOrders = $subscriptionData->bookingLimit;
        $expiryDay = $subscriptionData->expiryDay;
        $expiryDate = intval($expiryDay) !== -1 ? Carbon::now()->addDays($expiryDay) : null;
        
        Driver::where('id', $driverId)->update([
            'subscriptionPlanId'=>$subscriptionPlanId,
            'subscriptionExpiryDate'=> $expiryDate,
            'subscriptionTotalOrders'=> $subscriptionTotalOrders,
            'subscription_plan'=> $subscriptionData
        ]);
        
        $subscriptionHistory =  SubscriptionHistory::create([
            'subscription_plan' => $subscriptionData,
            'expiry_date'=> $expiryDate,
            'payment_type'=>$paymentType,
            'user_id'=>$driverId,
            'subscriptionPlanId'=> $subscriptionPlanId,
        ]);
        if (!$subscriptionHistory) {
            $response['success'] = 'Failed';
            $response['error'] = 'Failed to create subscription history';
            $response['message'] = 'Database error';
            return response()->json($response);             
        }

        $cashbackAmount = floatval($subscriptionData->cashback_on_purchase ?? 0);
        $this->applyPlanPurchaseCashback($driver, $cashbackAmount, $subscriptionData->name, (int) $subscriptionData->id, 'driver');

        // Trigger referral reward for referrer
        try {
            \App\Services\ReferralRewardService::processReward((int)$driverId, 'business_subscription', floatval($subscriptionData->price ?? 0), 'Driver Plan Purchase');
        } catch (\Throwable $th) {
            \Log::error("Referral reward error for driver subscription: " . $th->getMessage());
        }

        // Send Confirmation Email with invoice & all 26 unlocked benefits
        try {
            $driverEmail = $driver->email ?? '';
            if (!empty($driverEmail) && filter_var($driverEmail, FILTER_VALIDATE_EMAIL)) {
                $driverName = trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? ''));
                $benefits = json_decode($subscriptionData->benefits_list ?? '[]', true) ?: (is_array($subscriptionData->plan_points) ? $subscriptionData->plan_points : (json_decode($subscriptionData->plan_points ?? '[]', true) ?: []));
                \App\Services\PlanEmailService::sendPlanActivationEmail([
                    'email' => $driverEmail,
                    'user_name' => !empty($driverName) ? $driverName : 'Partner',
                    'user_id' => $driver->id,
                    'user_type' => 'partner',
                    'plan_name' => $subscriptionData->name,
                    'amount' => $subscriptionData->price,
                    'validity' => ($subscriptionData->expiryDay ?? '365') . ' Days',
                    'expiry_date' => $expiryDate ? $expiryDate->format('d M Y') : 'Lifetime',
                    'txn_id' => 'FWP-' . strtoupper(uniqid()),
                    'benefits' => $benefits,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error("Failed to send driver plan activation email: " . $e->getMessage());
        }

        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Subscription added successfully';
        $response['cashback_credited'] = $cashbackAmount;
        return response()->json($response);
    }

    public function getSubscriptionHistory(Request $request){
        $driverId = $request->get('driverId');
        $historyData=SubscriptionHistory::where('user_id',$driverId)->orderBy('created_at','desc')->get();
        $output = [];
        if (count($historyData) > 0) {

            foreach ($historyData as $row) {
                $data = $row->toArray();
                $data['id'] = (string) $row->id;
                $subscription_plan = $row->subscription_plan;
                if (!empty($subscription_plan['image'])) {
                    $imagePath = public_path('assets/images/subscription/' . $subscription_plan['image']);

                    if (file_exists($imagePath)) {
                        $subscription_plan['image'] = asset('assets/images/subscription/' . $subscription_plan['image']);
                    } else {
                        $subscription_plan['image'] = asset('assets/images/placeholder_image.jpg');
                    }
                }
                $data['subscription_plan'] = $subscription_plan;
                $data['created_at'] = $row->created_at->format('Y-m-d H:i:s');

                $output[] = $data;
            }
            if (!empty($output)) {

                $response['success'] = 'success';

                $response['error'] = null;

                $response['message'] = 'Subscription plans fetched successfully';

                $response['data'] = $output;
            } else {

                $response['success'] = 'Failed';

                $response['error'] = 'Error while fetch data';
            }
        } else {

            $response['success'] = 'Failed';

            $response['error'] = 'No Data Found';

            $response['message'] = null;
        }



        return response()->json($response);
    }

    private function recordPlanWalletDebit($entity, float $amount, string $planName, int $planId, string $userType): void
    {
        if ($amount <= 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $txnId = (string) time();
        $description = "Purchased {$planName} plan";

        if ($userType === 'driver') {
            $payload = [
                'amount'         => $amount,
                'payment_method' => 'Wallet',
                'id_conducteur'  => $entity->id,
                'creer'          => $now,
                'modifier'       => $now,
            ];
            if (Schema::hasColumn('tj_conducteur_transaction', 'deduction_type')) {
                $payload['deduction_type'] = '0';
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'payment_status')) {
                $payload['payment_status'] = 'success';
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'type')) {
                $payload['type'] = 'debit';
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'description')) {
                $payload['description'] = $description;
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'txn_id')) {
                $payload['txn_id'] = $txnId;
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'planId')) {
                $payload['planId'] = (string) $planId;
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'date')) {
                $payload['date'] = date('Y-m-d');
            }
            DB::table('tj_conducteur_transaction')->insert($payload);
        } else {
            $payload = [
                'amount'         => $amount,
                'payment_method' => 'Wallet',
                'id_user_app'    => $entity->id,
                'creer'          => $now,
                'modifier'       => $now,
            ];
            if (Schema::hasColumn('tj_transaction', 'deduction_type')) {
                $payload['deduction_type'] = '0';
            }
            if (Schema::hasColumn('tj_transaction', 'payment_status')) {
                $payload['payment_status'] = 'success';
            }
            if (Schema::hasColumn('tj_transaction', 'type')) {
                $payload['type'] = 'debit';
            }
            if (Schema::hasColumn('tj_transaction', 'description')) {
                $payload['description'] = $description;
            }
            if (Schema::hasColumn('tj_transaction', 'txn_id')) {
                $payload['txn_id'] = $txnId;
            }
            if (Schema::hasColumn('tj_transaction', 'user_type')) {
                $payload['user_type'] = 'customer';
            }
            if (Schema::hasColumn('tj_transaction', 'date')) {
                $payload['date'] = date('Y-m-d');
            }
            DB::table('tj_transaction')->insert($payload);
        }
    }

    private function applyPlanPurchaseCashback($entity, float $cashbackAmount, string $planName, int $planId, string $userType): void
    {
        if ($cashbackAmount <= 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $txnId = (string) (time() + 1);
        $description = "Cashback on purchasing {$planName} plan";

        if ($userType === 'driver') {
            DB::table('tj_conducteur')->where('id', $entity->id)->increment('amount', $cashbackAmount);
            $payload = [
                'amount'         => $cashbackAmount,
                'payment_method' => 'Wallet',
                'id_conducteur'  => $entity->id,
                'creer'          => $now,
                'modifier'       => $now,
            ];
            if (Schema::hasColumn('tj_conducteur_transaction', 'deduction_type')) {
                $payload['deduction_type'] = '1';
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'payment_status')) {
                $payload['payment_status'] = 'success';
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'type')) {
                $payload['type'] = 'credit';
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'description')) {
                $payload['description'] = $description;
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'txn_id')) {
                $payload['txn_id'] = $txnId;
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'planId')) {
                $payload['planId'] = (string) $planId;
            }
            if (Schema::hasColumn('tj_conducteur_transaction', 'date')) {
                $payload['date'] = date('Y-m-d');
            }
            DB::table('tj_conducteur_transaction')->insert($payload);
        } else {
            DB::table('tj_user_app')->where('id', $entity->id)->increment('amount', $cashbackAmount);
            $payload = [
                'amount'         => $cashbackAmount,
                'payment_method' => 'Wallet',
                'id_user_app'    => $entity->id,
                'creer'          => $now,
                'modifier'       => $now,
            ];
            if (Schema::hasColumn('tj_transaction', 'deduction_type')) {
                $payload['deduction_type'] = '1';
            }
            if (Schema::hasColumn('tj_transaction', 'payment_status')) {
                $payload['payment_status'] = 'success';
            }
            if (Schema::hasColumn('tj_transaction', 'type')) {
                $payload['type'] = 'credit';
            }
            if (Schema::hasColumn('tj_transaction', 'description')) {
                $payload['description'] = $description;
            }
            if (Schema::hasColumn('tj_transaction', 'txn_id')) {
                $payload['txn_id'] = $txnId;
            }
            if (Schema::hasColumn('tj_transaction', 'user_type')) {
                $payload['user_type'] = 'customer';
            }
            if (Schema::hasColumn('tj_transaction', 'date')) {
                $payload['date'] = date('Y-m-d');
            }
            DB::table('tj_transaction')->insert($payload);
        }
    }

    /**
     * Send OTP for Email Verification before Plan Activation
     * Endpoint: POST /api/v1/plan/send-email-otp
     */
    public function sendPlanEmailOtp(Request $request)
    {
        $email = strtolower(trim($request->input('email', '')));
        $userId = $request->input('user_id', $request->input('userId'));
        $userType = strtolower($request->input('user_type', $request->input('user_cat', 'driver')));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Please enter a valid email address.'
            ], 422);
        }

        $name = 'Member';
        if ($userType === 'driver') {
            $driver = Driver::find($userId);
            if ($driver) $name = trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? ''));
        } else {
            $user = UserApp::find($userId);
            if ($user) $name = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? ''));
        }

        $otp = strval(random_int(100000, 999999));

        DB::table('auth_otp_temp')
            ->where('email', $email)
            ->whereIn('type', ['plan_email_otp', 'email', ''])
            ->delete();

        DB::table('auth_otp_temp')->insert([
            'phone'      => (string)($userId ?? ''),
            'email'      => $email,
            'otp'        => $otp,
            'type'       => 'plan_email_otp',
            'user_cat'   => $userType,
            'verified'   => 0,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sent = \App\Services\PlanEmailService::sendPlanOtpEmail($email, $otp, $name, $userType);
        if (!$sent) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Failed to send OTP email. Please verify your SMTP settings in Admin Panel.'
            ], 500);
        }

        return response()->json([
            'success' => 'success',
            'message' => 'A 6-digit OTP has been sent to ' . $email . '. Valid for 15 minutes.',
            'email' => $email,
        ]);
    }

    /**
     * Verify OTP and link verified email to profile
     * Endpoint: POST /api/v1/plan/verify-email-otp
     */
    public function verifyPlanEmailOtp(Request $request)
    {
        $email = strtolower(trim($request->input('email', '')));
        $otp = trim($request->input('otp', ''));
        $userId = $request->input('user_id', $request->input('userId'));
        $userType = strtolower($request->input('user_type', $request->input('user_cat', 'driver')));

        if (empty($email) || empty($otp)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Email and OTP are required.'
            ], 422);
        }

        $cleanOtp = preg_replace('/\D/', '', $otp);

        $record = DB::table('auth_otp_temp')
            ->where('email', $email)
            ->whereIn('type', ['plan_email_otp', 'email', ''])
            ->where('verified', 0)
            ->where('expires_at', '>', now()->subMinutes(2))
            ->orderBy('id', 'desc')
            ->first();

        if (!$record || trim((string)$record->otp) !== $cleanOtp) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Invalid or expired OTP. Please enter the correct 6-digit code.'
            ], 422);
        }

        DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);

        if ($userType === 'driver') {
            Driver::where('id', $userId)->update([
                'email' => $email,
                'email_verified_at' => now(),
            ]);
        } else {
            UserApp::where('id', $userId)->update([
                'email' => $email,
                'email_verified_at' => now(),
            ]);
        }

        return response()->json([
            'success' => 'success',
            'message' => 'Email verified successfully!',
            'email' => $email,
        ]);
    }

}

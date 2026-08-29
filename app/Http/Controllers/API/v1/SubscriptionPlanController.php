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

        $subscriptionPlan = SubscriptionPlan::where('isEnable', '=', 'true')->get();



        if (count($subscriptionPlan) > 0) {

            foreach ($subscriptionPlan as $row) {

                $row->id = (string)$row->id;

                if ($row->image != '') {

                    if (file_exists(public_path('assets/images/subscription' . '/' . $row->image))) {

                        $row->image = asset('assets/images/subscription') . '/' . $row->image;
                    } else {

                        $row->image = asset('assets/images/placeholder_image.jpg');
                    }
                }

                $planPoints = is_array($row->plan_points) ? $row->plan_points : (json_decode($row->plan_points ?? '[]', true) ?: []);
                if (Schema::hasColumn('subscription_plans', 'cashback_on_purchase') && floatval($row->cashback_on_purchase ?? 0) > 0) {
                    $planPoints[] = "₹{$row->cashback_on_purchase} instant cashback on plan purchase";
                }
                $row->plan_points = $planPoints;

                $output[] = $row;
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

    public function getConsumerPlans(Request $request)
    {
        $output = [];
        $consumerPlans = ConsumerPremiumPlan::where('status', 'active')->orderBy('display_order')->get();

        if (count($consumerPlans) > 0) {
            foreach ($consumerPlans as $row) {
                $item = new \stdClass();
                $item->id = (string)$row->id;
                $item->name = (string)$row->name;
                $item->price = (string)$row->price;
                $item->expiryDay = (string)$row->validity_days;
                $item->description = $row->description ?? '';
                $item->type = floatval($row->price) > 0 ? 'paid' : 'free';
                $item->isEnable = 'true';
                $item->place = (string)($row->display_order ?? '1');
                $item->image = asset('assets/images/placeholder_image.jpg');
                $item->cashback_on_purchase = (string)($row->cashback_on_purchase ?? '0');
                
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
                    $planPoints[] = "Premium member benefits";
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
                $item->name = (string)$row->name;
                $item->price = (string)$row->price;
                $item->expiryDay = (string)$row->expiryDay;
                $item->description = $row->description ?? '';
                $item->type = (string)$row->type;
                $item->isEnable = (string)$row->isEnable;
                $item->place = (string)$row->place;
                $item->image = asset('assets/images/placeholder_image.jpg');
                $item->cashback_on_purchase = (string)($row->cashback_on_purchase ?? '0');
                $planPoints = is_array($row->plan_points) ? $row->plan_points : (json_decode($row->plan_points ?? '[]', true) ?: []);
                $item->plan_points = $planPoints;
                $output[] = $item;
            }
        }

        if (!empty($output)) {
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Consumer plans fetched successfully';
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

}

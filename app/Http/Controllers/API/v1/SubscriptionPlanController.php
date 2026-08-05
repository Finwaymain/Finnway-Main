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
                $row->id = (string)$row->id;
                $row->expiryDay = (string)$row->validity_days;
                $row->price = (string)$row->price;
                $row->description = $row->description ?? '';
                
                // Build plan points from consumer plan features
                $planPoints = [];
                if ($row->discount_cab > 0) $planPoints[] = "{$row->discount_cab}% discount on Cab rides";
                if ($row->discount_bike > 0) $planPoints[] = "{$row->discount_bike}% discount on Bike rides";
                if ($row->sender_cashback_value > 0) $planPoints[] = "{$row->sender_cashback_value}% cashback on sending money";
                if ($row->receiver_cashback_value > 0) $planPoints[] = "{$row->receiver_cashback_value}% cashback on receiving money";
                if ($row->free_shipping) $planPoints[] = "Free shipping on marketplace orders";
                if ($row->loan_personal) $planPoints[] = "Personal loan access";
                if ($row->loan_business) $planPoints[] = "Business loan access";
                if ($row->loan_virtual) $planPoints[] = "Virtual credit limit: ₹{$row->virtual_credit_limit}";
                
                if (empty($planPoints)) {
                    $planPoints[] = "Premium member benefits";
                }
                
                $row->plan_points = $planPoints;
                $output[] = $row;
            }

            if (!empty($output)) {
                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'Consumer plans fetched successfully';
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

    public function setConsumerSubscription(Request $request){
        $planId = $request->get('planId');
        $userId = $request->get('userId');
        $paymentType = $request->get('paymentType');
        
        $planData = ConsumerPremiumPlan::where('id', $planId)->first();
        $user = UserApp::where('id', $userId)->first();
        
        if (!$planData) {
            $response['success'] = 'Failed';
            $response['error'] = 'Consumer plan not found';
            $response['message'] = 'Invalid plan ID';
            return response()->json($response);
        }
        
        if(strtolower($paymentType)=='wallet'){
            if(floatval($user->amount) < floatval($planData->price)){
                $response['success'] = 'Failed';
                $response['error'] = 'Insufficient wallet balance';
                $response['message'] = "You don't have sufficient balance to purchase this plan";
                return response()->json($response);
            }else{
                $newWalletBalance = floatval($user->amount) - floatval($planData->price);
                UserApp::where('id', $userId)->update(['amount'=>$newWalletBalance]);
                $date = date('Y-m-d H:i:s');

                DB::table('tj_transaction')->insert([
                    'amount' => $planData->price,
                    'payment_method' => 'Wallet',
                    'id_user' => $userId,
                    'creer' => $date,
                    'planId'=> $planData->id
                ]);
            }
        }
        
        $expiryDate = Carbon::now()->addDays($planData->validity_days);
        
        $updateResult = UserApp::where('id', $userId)->update([
            'consumer_plan_id'=>$planData->id,
            'consumer_plan_expiry_date'=> $expiryDate,
            'consumer_plan'=> json_encode($planData->toArray())
        ]);
        
        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Consumer subscription added successfully';
        return response()->json($response);
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
        if(strtolower($paymentType)=='wallet'){
            if(floatval($driver->amount)<floatval($subscriptionData->price)){
                $response['success'] = 'Failed';
                $response['error'] = 'Insufficient wallet balance';
                $response['message'] = "You don't have sufficient balance to purchase this plan";
                return response()->json($response);
            }else{
                $newWalletBalance = floatval($driver->amount) - floatval($subscriptionData->price);
                Driver::where('id', $driverId)->update(['amount'=>$newWalletBalance]);
                $date = date('Y-m-d H:i:s');

                DB::table('tj_conducteur_transaction')->insert([
                    'amount' => $subscriptionData->price,
                    'payment_method' => 'Wallet',
                    'id_conducteur' => $driverId,
                    'creer' => $date,
                    'planId'=> $subscriptionData->id
                ]);
            }
        }
        
        $subscriptionPlanId = $subscriptionData->id;
        $subscriptionTotalOrders = $subscriptionData->bookingLimit;
        $expiryDay = $subscriptionData->expiryDay;
        $expiryDate = intval($expiryDay) !== -1 ? Carbon::now()->addDays($expiryDay) : null;
        
        $updateResult = Driver::where('id', $driverId)->update([
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
        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Subscription added successfully';
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

}

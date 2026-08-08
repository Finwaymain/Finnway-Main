<?php



namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;

use App\Models\Currency;
use App\Models\Driver;
use App\Models\SubscriptionHistory;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use File;
use Image;

class SubscriptionPlanController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $query = SubscriptionPlan::withCount('subscribers');

        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');

            if ($request->selected_search == 'name') {
                $query->where('name', 'LIKE', "%{$search}%");
            } elseif ($request->selected_search == 'price') {
                if (stripos('free', $search) !== false) {
                    $query->where('price', '0');
                }else{
                    $query->where('price', 'LIKE', "%{$search}%");
                } 
                
            }
        }
        $subscriptionPlans = $query->paginate(10);

        $currency = Currency::where('statut', 'yes')->first();
        $overviewPlans = SubscriptionPlan::where('subscription_plans.id', '!=', 1)
            ->leftJoin('subscription_history', 'subscription_history.subscriptionPlanId', '=', 'subscription_plans.id')
            ->select(
                'subscription_plans.*',
                DB::raw('SUM(JSON_UNQUOTE(JSON_EXTRACT(subscription_history.subscription_plan, "$.price"))) as total_earning')
            )
            ->groupBy('subscription_plans.id')
            ->get();

        return view("subscription_plans.index", compact('subscriptionPlans', 'currency', 'overviewPlans'));
    }



    public function create()
    {
        $categories = collect([]);
        if (Schema::hasTable('tj_categorie_user')) {
            $categories = DB::table('tj_categorie_user')->get();
        } elseif (Schema::hasTable('tj_services')) {
            $categories = DB::table('tj_services')->get();
        } elseif (Schema::hasTable('tj_category')) {
            $categories = DB::table('tj_category')->get();
        }
        return view("subscription_plans.create", compact('categories'));
    }

    public function store(Request $request)
    {
        $enabledPlans = SubscriptionPlan::where('isEnable', 'true')->where('id', '!=', 1)->count();



        $request->merge([
            'plan_points' => array_filter($request->plan_points) // Removes empty values
        ]);

        $validator = Validator::make($request->all(), $rules = [

            'planName' => 'required',
            'planPrice' => ['required_if:planType,paid',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('planType') === 'paid' && $value <= 0) {
                        $fail(__('lang.plan_price_in_positive_no'));
                    }
                }],
            'plan_validity' => [
                'required_if:plan_validity_days,limited',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('plan_validity_days') === 'limited') {
                        if ($value == 0) {
                            $fail(__('lang.expiry_day_zero'));
                        } elseif ($value < 0 && $value != -1) {
                            $fail(__('lang.expiry_day_in_positive_no'));
                        }
                    }
                }
            ],
            'description' => 'required',
            'order' => 'required',
            'image' => 'required|mimes:jpeg,jpg,png,webp,gif',
            'booking_limit' => ['required_if:set_booking_limit,limited',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('set_booking_limit') === 'limited' && $value <= 0) {
                        $fail(__('lang.booking_limit_in_positive_no'));

                    }
                }],
            'plan_points' => 'required|array|min:1',
            'plan_points.*' => 'required|string|min:1',

        ], $messages = [

            'planName.required' => __("lang.enter_plan_name"),
            'planPrice.required' => __("lang.enter_plan_price"),
            'plan_validity.required' => __("lang.please_enter_expiry"),
            'description.required' => __("lang.enter_description"),
            'order.required' => __("lang.enter_display_order"),
            'image.required' => __("lang.upload_plan_image"),
            'set_booking_limit.required' => __("lang.enter_booking_limit"),
            'plan_points.required' => __("lang.enter_plan_points")

        ]);
        if ($enabledPlans == 0 && !$request->status) {
            $validator->after(function ($validator) {
                $validator->errors()->add('status', __('lang.atleast_one_subscription_plan_should_be_active'));
            });
        }


        if ($validator->fails()) {

            return back()

                ->withErrors($validator)->with(['message' => $messages])

                ->withInput();
        }

        $data = $request->all();
        $filename = null;
        if ($request->hasfile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'subscription_plan_' . time() . '.' . $extension;
            $dir = public_path('assets/images/subscription/');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file->move($dir, $filename);
        }


        SubscriptionPlan::create([
            'name'         => $data['planName'],
            'type'         => $data['planType'],
            'price'        => $data['planType'] == 'free' ? '0' : $data['planPrice'],
            'expiryDay'    => $data['plan_validity_days'] == 'limited' ? $data['plan_validity'] : '-1',
            'description'  => $data['description'],
            'place'        => $data['order'],
            'isEnable'     => ($request->has('status')) ? 'true' : 'false',
            'image'        => $filename,
            'plan_points'  => $data['plan_points'],
            'bookingLimit' => $data['set_booking_limit'] == 'limited' ? $data['booking_limit'] : '-1',
            // Benefit config
            'plan_tier'                  => $data['plan_tier'] ?? 'basic',
            'sender_cashback_type'       => $data['sender_cashback_type'] ?? 'percentage',
            'sender_cashback_value'      => floatval($data['sender_cashback_value'] ?? 0),
            'receiver_cashback_type'     => $data['receiver_cashback_type'] ?? 'percentage',
            'receiver_cashback_value'    => floatval($data['receiver_cashback_value'] ?? 0),
            'cashback_on_purchase'       => floatval($data['cashback_on_purchase'] ?? 0),
            'discount_home_service'      => floatval($data['discount_home_service'] ?? 0),
            'discount_travel'            => floatval($data['discount_travel'] ?? 0),
            'discount_hotel'             => floatval($data['discount_hotel'] ?? 0),
            'discount_food'              => floatval($data['discount_food'] ?? 0),
            'discount_medical'           => floatval($data['discount_medical'] ?? 0),
            'discount_marketplace'       => floatval($data['discount_marketplace'] ?? 0),
            'discount_transaction'       => floatval($data['discount_transaction'] ?? 0),
            'min_amount_hotel'          => floatval($data['min_amount_hotel'] ?? 0),
            'min_amount_home_service'   => floatval($data['min_amount_home_service'] ?? 0),
            'min_amount_shopping'       => floatval($data['min_amount_shopping'] ?? 0),
            'min_amount_food'           => floatval($data['min_amount_food'] ?? 0),
            'min_amount_travel'         => floatval($data['min_amount_travel'] ?? 0),
            'min_amount_medical'        => floatval($data['min_amount_medical'] ?? 0),
            'min_amount_cab'            => floatval($data['min_amount_cab'] ?? 0),
            'discount_delivery_food'         => floatval($data['discount_delivery_food'] ?? 0),
            'discount_delivery_shopping'     => floatval($data['discount_delivery_shopping'] ?? 0),
            'discount_delivery_home_service' => floatval($data['discount_delivery_home_service'] ?? 0),
            'discount_delivery_medical'      => floatval($data['discount_delivery_medical'] ?? 0),
            'discount_delivery_parcel'       => floatval($data['discount_delivery_parcel'] ?? 0),
            'shopping_discount'          => floatval($data['shopping_discount'] ?? 0),
            'free_ride_limit'            => intval($data['free_ride_limit'] ?? 0),
            'free_ride_reset'            => $data['free_ride_reset'] ?? 'monthly',
            'wallet_increment_value'     => floatval($data['wallet_increment_value'] ?? 0),
            'wallet_increment_period'    => $data['wallet_increment_period'] ?? 'daily',
            'wallet_decrement_value'     => floatval($data['wallet_decrement_value'] ?? 0),
            'wallet_decrement_period'    => $data['wallet_decrement_period'] ?? 'daily',
            'referral_bonus_type'        => $data['referral_bonus_type'] ?? 'flat',
            'referral_bonus_value'       => floatval($data['referral_bonus_value'] ?? 0),
            'loan_enabled'               => $request->has('loan_enabled'),
            'loan_max_amount'            => floatval($data['loan_max_amount'] ?? 0),
            'interest_free_loan_enabled' => $request->has('interest_free_loan_enabled'),
            'interest_free_loan_limit'   => floatval($data['interest_free_loan_limit'] ?? 0),
        ]);

        return redirect('subscription-plans');
    }

    public function edit($id)
    {
        $subscriptionPlan = SubscriptionPlan::find($id);
        $categories = collect([]);
        if (Schema::hasTable('tj_categorie_user')) {
            $categories = DB::table('tj_categorie_user')->get();
        } elseif (Schema::hasTable('tj_services')) {
            $categories = DB::table('tj_services')->get();
        } elseif (Schema::hasTable('tj_category')) {
            $categories = DB::table('tj_category')->get();
        }
        return view("subscription_plans.edit", compact('subscriptionPlan', 'categories'));
    }



    public function update(Request $request, $id)
    {

        $enabledPlans = SubscriptionPlan::where('isEnable', 'true')->where('id', '!=', 1)->get();
        $enabledPlansCount = $enabledPlans->count();
        if ($enabledPlansCount == 1) {
            $enablePlanId = $enabledPlans->first()->id;
        }
        $request->merge([
            'plan_points' => array_filter($request->plan_points) // Removes empty values
        ]);

        $validator = Validator::make($request->all(), $rules = [

            'planName' => 'required',
            'planPrice' => [
                'required_if:planType,paid',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('planType') === 'paid' && $value <= 0) {
                        $fail(__('lang.plan_price_in_positive_no'));
                    }
                }
            ],
            'plan_validity' => [
                'required_if:plan_validity_days,limited',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('plan_validity_days') === 'limited') {
                        if ($value == 0) {
                            $fail(__('lang.expiry_day_zero'));
                        } elseif ($value < 0 && $value != -1) {
                            $fail(__('lang.expiry_day_in_positive_no'));
                        }
                    }
                }
            ],
            'description' => 'required',
            'order' => 'required',
            'image' => 'image|mimes:jpeg,jpg,png',
            'booking_limit' => [
                'required_if:set_booking_limit,limited',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('set_booking_limit') === 'limited' && $value <= 0) {
                        $fail(__('lang.booking_limit_in_positive_no'));
                        
                    }
                }
            ],
            'plan_points' => 'required|array|min:1',
            'plan_points.*' => 'required|string|min:1',

        ], $messages = [

            'planName.required' => __("lang.enter_plan_name"),
            'planPrice.required' => __("lang.enter_plan_price"),
            'plan_validity.required' => __("lang.please_enter_expiry"),
            'description.required' => __("lang.enter_description"),
            'order.required' => __("lang.enter_display_order"),
            'image.required' => __("lang.upload_plan_image"),
            'set_booking_limit.required' => __("lang.enter_booking_limit"),
            'plan_points.required' => __("lang.enter_plan_points")

        ]);

        if ($enabledPlansCount == 1 && $enablePlanId == $id && !$request->status) {
            $validator->after(function ($validator) {
                $validator->errors()->add('status', __('lang.atleast_one_subscription_plan_should_be_active'));
            });
        }
        if ($id != 1 && intVal($request->order) == 0) {
            $validator->after(function ($validator) {
                $validator->errors()->add('order', __('lang.commision_plan_will_be_always_at_first'));
            });
        }

        if ($validator->fails()) {

            return back()

                ->withErrors($validator)->with(['message' => $messages])

                ->withInput();
        }


        $plan = SubscriptionPlan::find($id);
        $filename = $plan->image;
        if ($request->hasfile('image')) {
            $destination = public_path('assets/images/subscription/' . $plan->image);
            if (File::exists($destination)) {
                File::delete($destination);
            }
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'subscription_plan_' . $id . '.' . $extension;
            $dir = public_path('assets/images/subscription/');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file->move($dir, $filename);
        }
        $data = $request->all();

        SubscriptionPlan::where('id', $id)->update([
            'name'         => $data['planName'],
            'type'         => $data['planType'],
            'price'        => $data['planType'] == 'free' ? '0' : $data['planPrice'],
            'expiryDay'    => $data['plan_validity_days'] == 'limited' ? $data['plan_validity'] : '-1',
            'description'  => $data['description'],
            'place'        => $data['order'],
            'isEnable'     => ($request->has('status')) ? 'true' : 'false',
            'image'        => $filename,
            'plan_points'  => $data['plan_points'],
            'bookingLimit' => $data['set_booking_limit'] == 'limited' ? $data['booking_limit'] : '-1',
            // Benefit config
            'plan_tier'                  => $data['plan_tier'] ?? 'basic',
            'sender_cashback_type'       => $data['sender_cashback_type'] ?? 'percentage',
            'sender_cashback_value'      => floatval($data['sender_cashback_value'] ?? 0),
            'receiver_cashback_type'     => $data['receiver_cashback_type'] ?? 'percentage',
            'receiver_cashback_value'    => floatval($data['receiver_cashback_value'] ?? 0),
            'cashback_on_purchase'       => floatval($data['cashback_on_purchase'] ?? 0),
            'discount_home_service'      => floatval($data['discount_home_service'] ?? 0),
            'discount_travel'            => floatval($data['discount_travel'] ?? 0),
            'discount_hotel'             => floatval($data['discount_hotel'] ?? 0),
            'discount_food'              => floatval($data['discount_food'] ?? 0),
            'discount_medical'           => floatval($data['discount_medical'] ?? 0),
            'discount_marketplace'       => floatval($data['discount_marketplace'] ?? 0),
            'discount_transaction'       => floatval($data['discount_transaction'] ?? 0),
            'min_amount_hotel'          => floatval($data['min_amount_hotel'] ?? 0),
            'min_amount_home_service'   => floatval($data['min_amount_home_service'] ?? 0),
            'min_amount_shopping'       => floatval($data['min_amount_shopping'] ?? 0),
            'min_amount_food'           => floatval($data['min_amount_food'] ?? 0),
            'min_amount_travel'         => floatval($data['min_amount_travel'] ?? 0),
            'min_amount_medical'        => floatval($data['min_amount_medical'] ?? 0),
            'min_amount_cab'            => floatval($data['min_amount_cab'] ?? 0),
            'discount_delivery_food'         => floatval($data['discount_delivery_food'] ?? 0),
            'discount_delivery_shopping'     => floatval($data['discount_delivery_shopping'] ?? 0),
            'discount_delivery_home_service' => floatval($data['discount_delivery_home_service'] ?? 0),
            'discount_delivery_medical'      => floatval($data['discount_delivery_medical'] ?? 0),
            'discount_delivery_parcel'       => floatval($data['discount_delivery_parcel'] ?? 0),
            'shopping_discount'          => floatval($data['shopping_discount'] ?? 0),
            'free_ride_limit'            => intval($data['free_ride_limit'] ?? 0),
            'free_ride_reset'            => $data['free_ride_reset'] ?? 'monthly',
            'wallet_increment_value'     => floatval($data['wallet_increment_value'] ?? 0),
            'wallet_increment_period'    => $data['wallet_increment_period'] ?? 'daily',
            'wallet_decrement_value'     => floatval($data['wallet_decrement_value'] ?? 0),
            'wallet_decrement_period'    => $data['wallet_decrement_period'] ?? 'daily',
            'referral_bonus_type'        => $data['referral_bonus_type'] ?? 'flat',
            'referral_bonus_value'       => floatval($data['referral_bonus_value'] ?? 0),
            'loan_enabled'               => $request->has('loan_enabled'),
            'loan_max_amount'            => floatval($data['loan_max_amount'] ?? 0),
            'interest_free_loan_enabled' => $request->has('interest_free_loan_enabled'),
            'interest_free_loan_limit'   => floatval($data['interest_free_loan_limit'] ?? 0),
        ]);
        return redirect('subscription-plans');
    }

    public function delete($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $plan = SubscriptionPlan::find($id[$i]);
                    $plan->delete();
                }
            } else {
                $plan = SubscriptionPlan::find($id);
                $plan->delete();
            }
        }
        return redirect()->back();
    }


    public function toggalSwitch(Request $request)
    {
        $enabledPlans = SubscriptionPlan::where('isEnable', 'true')->where('id', '!=', 1)->get();
        $enabledPlansCount = $enabledPlans->count();
        if ($enabledPlansCount == 1) {
            $enablePlanId = $enabledPlans->first()->id;
        }

        $ischeck = $request->input('ischeck');
        $id = $request->input('id');

        $subscriptionPlan = SubscriptionPlan::find($id);
        if ($ischeck == "true") {
            $subscriptionPlan->isEnable = 'true';
            $subscriptionPlan->save();
            return response()->json(['success' => true, 'message' => 'Subscription plan disabled successfully']);
        } else {
            if ($enabledPlansCount == 1 && $enablePlanId == $id) {
                return response()->json(['success' => false, 'message' => __('lang.atleast_one_subscription_plan_should_be_active')], 400);
            } else {
                $subscriptionPlan->isEnable = 'false';
                $subscriptionPlan->save();
                return response()->json(['success' => true, 'message' => 'Subscription plan disabled successfully']);
            }
        }
    }
    public function currentSubscriberList($id,Request $request)
    {
        $subscriptionPlan = SubscriptionPlan::where('id', $id)->first();
        $query = Driver::where('subscriptionPlanId', $id)->select('nom', 'prenom', 'subscriptionExpiryDate', 'subscriptionTotalOrders', 'subscription_plan');
        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            if ($request->selected_search == 'driver') {
                $query->where('tj_conducteur.prenom', 'LIKE', '%' . $search . '%')
                    ->orWhere(DB::raw('CONCAT(tj_conducteur.nom, " ",tj_conducteur.prenom)'), 'LIKE', '%' . $search . '%');
            } elseif ($request->selected_search == 'planName') {   
                    $query->where('subscription_plan->name', 'LIKE', "%{$search}%");
            } elseif ($request->selected_search == 'planType') {
                $query->where('subscription_plan->type', 'LIKE', "%{$search}%");
            }
        }
        
        $currentSubscribers=$query->paginate(10);
        return view("subscription_plans.current_subscriber", compact('subscriptionPlan', 'currentSubscribers'));
    }
    public function SubscriptionHistory(Request $request)
    {
        $query = SubscriptionHistory::join('tj_conducteur', 'tj_conducteur.id', '=', 'subscription_history.user_id')
            ->leftjoin('subscription_plans', 'subscription_plans.id','=', 'subscription_history.subscriptionPlanId')
            ->select('tj_conducteur.nom', 'tj_conducteur.prenom', 'subscription_history.*');
        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            if ($request->selected_search == 'name') {
                $query->where('subscription_plans.name', 'LIKE', '%'.$search.'%');
            } elseif ($request->selected_search == 'driver') {
                $query->where('tj_conducteur.prenom', 'LIKE', '%' . $search . '%')
                      ->orWhere(DB::raw('CONCAT(tj_conducteur.nom, " ",tj_conducteur.prenom)'), 'LIKE', '%' . $search . '%');
                        
            } 
        }

        $history= $query->orderBy('created_at','desc')->paginate(10);
        return view("subscription_plans.history", compact('history'));
    }

    public function deleteHistory($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $plan = SubscriptionHistory::find($id[$i]);
                    $plan->delete();
                }
            } else {
                $plan = SubscriptionHistory::find($id);
                $plan->delete();
            }
        }
        return redirect()->back();
    }
}

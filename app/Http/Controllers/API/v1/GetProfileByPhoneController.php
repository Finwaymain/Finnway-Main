<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\Controller;

use App\Models\Driver;

use App\Models\UserApp;

use Illuminate\Http\Request;

use DB;



class GetProfileByPhoneController extends Controller

{



   public function __construct()

   {

      $this->limit=20;

   }

  

   public function adduseraccess($user_id, $user_type)

   {

       $user = DB::table('users_access')->where('user_id', $user_id)->where('user_type', $user_type)->first();

       if ($user && !empty($user->accesstoken)) {

           $token = $user->accesstoken;

       } else {

           $token = $this->getUniqAccessToken();

           DB::table('users_access')->insert(['user_id' => $user_id, 'accesstoken' => $token, 'user_type' => $user_type]);

       }

       return $token;

   }



    public function getUniqAccessToken()

    {

        $accessget = 0;

        $accessToken = '';

        while ($accessget == 0) {

            $accessToken = md5(uniqid(mt_rand(), true));

            $user = DB::table('users_access')->where('accesstoken', $accessToken)->first();

            if (!$user) {

                $accessget = 1;

            }

        }

        return $accessToken;

    }



    public function getData(Request $request)

    {

        $date_heure = date('Y-m-d H:i:s');

        $phone = $request->get('phone');

        $user_cat = $request->get('user_cat');

        $accesstoken = $request->header('accesstoken');

        $email = $request->get('email');

        $login_type = $request->get('login_type');

        //for customer

        if($user_cat == 'customer'){

            if($login_type=='phoneNumber' || $login_type == 'phoneOtp' || $login_type == 'phone'){

                $checkuser = UserApp::where('phone', $phone)->first();

            }else{

                $checkuser = UserApp::where('email', $email)->first();

            }

            

            if (!empty($checkuser)) {

                if ($login_type == 'phoneNumber' || $login_type == 'phoneOtp' || $login_type == 'phone') {

                    $checkaccount = UserApp::where('phone', $phone)->where('statut', 'yes')->first();

                }else{

                    $checkaccount = UserApp::where('email', $email)->where('statut', 'yes')->first();

                }

                if($checkaccount){

                    

                    $row = $checkuser->toArray();

                

                    $id = $row['id'];

                        

                    $accesstoken = $accesstoken ? $accesstoken : $this->adduseraccess($row['id'], 'customer');



                    unset($row['mdp']);

                    

                    $row['user_cat'] = "user_app";

                    

                    $row['online'] = "";

                    

                    $get_currency = DB::table('tj_currency')->select('*')->where('statut','=','yes')->get();

                    foreach ($get_currency as $row_currency){

                        $row['currency'] = $row_currency->symbole;

                    }



                    $get_country = DB::table('tj_country')->select('*')->where('statut','=','yes')->get();

                    foreach ($get_country as $row_country){

                        $row['country'] = $row_country->code;

                    }

                    

                    $get_admin_commission = DB::table('tj_commission')->select('*')->where('statut', '=', 'yes')->get();

                    foreach ($get_admin_commission as $row_commission) {

                        $row['admin_commission'] = $row_commission->value;

                    }

                    

                    $row['photo']='';

                    $row['photo_nic']='';



                    if(!empty($row)){

                        

                        if($row['photo_path'] != ''){

                            if(file_exists(public_path('assets/images/users'.'/'.$row['photo_path'] )))

                            {

                                $image_user = asset('assets/images/users').'/'. $row['photo_path'];

                            }

                            else

                            {

                                $image_user =asset('assets/images/placeholder_image.jpg');

        

                            }

                            $row['photo_path'] = $image_user;

                        }

                        if($row['photo_nic_path'] != ''){

                            if(file_exists(public_path('assets/images/users'.'/'.$row['photo_nic_path'] )))

                            {

                                $image = asset('assets/images/users').'/'. $row['photo_nic_path'];

                            }

                            else

                            {

                                $image =asset('assets/images/placeholder_image.jpg');

        

                            }

                            $row['photo_nic_path'] = $image;

                        }

                        $row['id']=(string)$id;
                        $row['photo'] = '';
                        $row['accesstoken'] = $accesstoken;
                        $row['referral_code'] = \App\Services\ReferralCodeService::getOrCreateReferralCode((int)$id, 'customer');
                        if (empty($row['ac_no']) || strlen(trim((string)$row['ac_no'])) != 12) {
                            $row['ac_no'] = \App\Services\PocketNumberService::getOrCreatePocketNumber((int)$id, 'customer');
                        }

                        $response['success']= 'success';

                        $response['error']=null;

                        $response['message']= 'successfully';

                        $response['data'] = $row;

                        

                    }else{

                        $response['success']= 'Failed';

                        $response['error']='Failed to fetch data';

                    }

                        

                }else{

                    $response['success'] = 'Failed';

                    $response['error'] = 'Your account is not activated, please contact to administartor';

                }

                

            }else {

                $response['success']= 'Failed';

                $response['error'] = 'User not found';

            }

        

        //for driver

        

        }elseif($user_cat == 'driver'){



            if ($login_type == 'phoneNumber' || $login_type == 'phoneOtp' || $login_type == 'phone') {

                $checkuser = Driver::where('phone', $phone)->first();

            } else {

                $checkuser = Driver::where('email', $email)->first();

            }

            if (!empty($checkuser)) {



                if ($login_type == 'phoneNumber' || $login_type == 'phoneOtp' || $login_type == 'phone') {

                    $checkaccount = Driver::where('phone', $phone)->first();

                }else{

                    $checkaccount = Driver::where('email', $email)->first();

                }        

                if (!empty($checkaccount)){

                    

                    $row = $checkuser->toArray();

                        

                    $accesstoken = $accesstoken ? $accesstoken : $this->adduseraccess($row['id'], 'driver');



                    unset($row['mdp']);

                    $row['user_cat'] = "driver";

                    $id_user = $row['id'];

            

                    $get_currency = DB::table('tj_currency')->select('*')->where('statut','=','yes')->get();

                    foreach ($get_currency as $row_currency){

                        $row['currency'] = $row_currency->symbole;

                    }



                    $get_country = DB::table('tj_country')->select('*')->where('statut','=','yes')->get();

                    foreach ($get_country as $row_country){

                        $row['country'] = $row_country->code;

                    }

                    

                    $get_vehicle = DB::table('tj_vehicule')->select('*')->where('statut','=','yes')->where('id_conducteur','=',$id_user)->get();

                    foreach ($get_vehicle as $row_vehicle){

                        $row['brand'] = $row_vehicle->brand;

                        $row['model'] = $row_vehicle->model;

                        $row['color'] = $row_vehicle->color;

                        $row['numberplate'] = $row_vehicle->numberplate;

                    }

                    

                    if(!empty($row)){



                        $row['photo']='';



                        if($row['photo_path'] != ''){

                            if(file_exists(public_path('assets/images/driver'.'/'.$row['photo_path'] )))

                            {

                                $image_user = asset('assets/images/driver').'/'. $row['photo_path'];

                            }

                            else

                            {

                                $image_user =asset('assets/images/placeholder_image.jpg');



                            }

                            $row['photo_path'] = $image_user;

                        }
                        if ($row['subscription_plan'] != null && $row['subscription_plan'] != '') {
                            if (is_string($row['subscription_plan'])) {
                                $row['subscription_plan'] = json_decode($row['subscription_plan'], true);
                            }
                            if (is_array($row['subscription_plan'])) {
                                // Resync with active plan from subscription_plans table if available
                                if (!empty($row['subscriptionPlanId'])) {
                                    $livePlan = DB::table('subscription_plans')->where('id', $row['subscriptionPlanId'])->first();
                                    if ($livePlan) {
                                        $livePoints = is_array($livePlan->plan_points) ? $livePlan->plan_points : (json_decode($livePlan->plan_points ?? '[]', true) ?: []);
                                        $row['subscription_plan']['plan_points'] = $livePoints;
                                        $row['subscription_plan']['benefits_list'] = $livePoints;
                                        if (!empty($livePlan->name)) {
                                            $row['subscription_plan']['name'] = $livePlan->name;
                                        }
                                    }
                                }

                                // Purge legacy 26 fake bulk items if present
                                $pts = $row['subscription_plan']['plan_points'] ?? $row['subscription_plan']['benefits_list'] ?? [];
                                if (is_array($pts) && count($pts) >= 20 && in_array('Instant Payout / Daily Withdrawal', $pts)) {
                                    $row['subscription_plan']['plan_points'] = [];
                                    $row['subscription_plan']['benefits_list'] = [];
                                }

                                if (!empty($row['subscription_plan']['image'])) {
                                    if (file_exists(public_path('assets/images/subscription' . '/' . $row['subscription_plan']['image']))) {
                                        $subscriptionPlanImg = asset('assets/images/subscription') . '/' . $row['subscription_plan']['image'];
                                    } else {
                                        $subscriptionPlanImg = asset('assets/images/placeholder_image.jpg');
                                    }
                                    $row['subscription_plan']['image'] = $subscriptionPlanImg;
                                }
                            }
                        }


                        $row['photo_licence'] = '';

                        $row['photo_nic'] = '';

                        $row['photo_car_service_book'] = '';

                        $row['photo_road_worthy'] = '';

                        if($row['photo_nic_path'] != ''){

                            if(file_exists(public_path('assets/images/driver'.'/'.$row['photo_nic_path'] )))

                            {

                                $image = asset('assets/images/driver').'/'. $row['photo_nic_path'];

                            }

                            else

                            {

                                $image =asset('assets/images/placeholder_image.jpg');



                            }

                            $row['photo_nic_path'] = $image;

                        }



                        if($row['photo_licence_path'] != ''){

                            if(file_exists(public_path('assets/images/driver'.'/'.$row['photo_licence_path'] )))

                            {

                                $image_licence = asset('assets/images/driver').'/'. $row['photo_licence_path'];

                            }

                            else

                            {

                                $image_licence =asset('assets/images/placeholder_image.jpg');



                            }

                            $row['photo_licence_path'] = $image_licence;

                        }

                        if($row['photo_car_service_book_path'] != ''){

                            if(file_exists(public_path('assets/images/driver'.'/'.$row['photo_car_service_book_path'] )))

                            {

                                $image_car = asset('assets/images/driver').'/'. $row['photo_car_service_book_path'];

                            }

                            else

                            {

                                $image_car =asset('assets/images/placeholder_image.jpg');



                            }

                            $row['photo_car_service_book_path'] = $image_car;

                        }



                        if($row['photo_road_worthy_path'] != ''){

                            if(file_exists(public_path('assets/images/driver'.'/'.$row['photo_road_worthy_path'] )))

                            {

                                $image_road = asset('assets/images/driver').'/'. $row['photo_road_worthy_path'];

                            }

                            else

                            {

                                $image_road =asset('assets/images/placeholder_image.jpg');



                            }

                            $row['photo_road_worthy_path'] = $image_road;

                        }



                        $isOnboarded = \App\Services\DriverProfileService::isOnboardingCompleted($id_user);
                        $row['onboarding_completed'] = $isOnboarded ? 'yes' : 'no';

                        if (!$isOnboarded) {
                            $row['is_verified'] = 'no';
                            $row['statut'] = 'no';
                            $row['statut_vehicule'] = 'no';
                            $row['is_home_service_provider'] = false;
                            $row['is_transport_category'] = false;
                            $row['is_delivery_partner'] = false;
                            $row['is_bike_rider'] = false;
                            $row['primary_console'] = 'taxi';
                            $row['selected_categories'] = [];
                            DB::table('tj_conducteur')->where('id', $id_user)->update([
                                'is_verified' => 0,
                                'statut' => 'no',
                                'statut_vehicule' => 'no',
                                'onboarding_completed' => 'no',
                            ]);
                        } else {
                            $row['selected_categories'] = DB::table('tj_conducteur_categories')
                                ->where('driver_id', $id_user)
                                ->get()
                                ->map(fn($item) => (string)($item->subcategory_id ?? $item->category_id))
                                ->toArray();

                            // Drivers whose selected categories are vehicle-based
                            // (cab, delivery, parcel, etc.) use the native app shell.
                            // Only pure home-service categories get the web dashboard.
                            $allCategoriesById = DB::table('tj_categorie_user')
                                ->select('id', 'parent_id', 'libelle')
                                ->get()
                                ->keyBy('id');

                            // Helper function to resolve root category
                            $getRootCategory = function($catId) use ($allCategoriesById) {
                                $current = $allCategoriesById->get((int)$catId);
                                $depth = 0;
                                while ($current && $current->parent_id && $depth < 8) {
                                    $parent = $allCategoriesById->get($current->parent_id);
                                    if (!$parent) break;
                                    $current = $parent;
                                    $depth++;
                                }
                                return $current;
                            };

                            $isTransportCategory = false;
                            $isDeliveryCategory = false;
                            $isHomeServiceProvider = false;
                            $isBikeRider = false;

                            $homeServiceProfessions = [
                                'electrician', 'plumber', 'cleaner', 'carpenter', 'painter',
                                'pest control', 'ac repair', 'appliance repair', 'home tutor',
                                'maid', 'cook', 'babysitter', 'physiotherapist', 'nurse',
                            ];

                            // Check driver's primary category from tj_conducteur if present
                            $primaryCatId = !empty($row['category_id']) ? (int)$row['category_id'] : null;

                            // Check if driver has a vehicle registered in tj_vehicule
                            $hasVehicle = !empty($row['numberplate']) || DB::table('tj_vehicule')->where('id_conducteur', $id_user)->where('statut', 'yes')->exists();

                            foreach ($row['selected_categories'] as $catId) {
                                $root = $getRootCategory($catId);
                                $rootLabel = strtolower(trim(preg_replace('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $root ? $root->libelle : '')));

                                $cur = $allCategoriesById->get((int)$catId);
                                $curLabel = strtolower(trim(preg_replace('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $cur ? $cur->libelle : '')));

                                if (str_contains($rootLabel, 'transport') || str_contains($curLabel, 'cab') || str_contains($curLabel, 'taxi') || str_contains($curLabel, 'auto driver') || str_contains($curLabel, 'e-rickshaw')) {
                                    $isTransportCategory = true;
                                } elseif (str_contains($rootLabel, 'delivery') || str_contains($curLabel, 'delivery & logistics') || str_contains($curLabel, 'parcel delivery') || str_contains($curLabel, 'food delivery') || str_contains($curLabel, 'logistics partner')) {
                                    $isDeliveryCategory = true;
                                }

                                if (str_contains($curLabel, 'bike rider') || str_contains($curLabel, 'motorcycle')) {
                                    $isBikeRider = true;
                                }

                                if (str_contains($rootLabel, 'home services') || str_contains($curLabel, 'home services')) {
                                    $isHomeServiceProvider = true;
                                }
                                foreach ($homeServiceProfessions as $profession) {
                                    if ($curLabel === $profession || str_contains($curLabel, $profession)) {
                                        $isHomeServiceProvider = true;
                                        break;
                                    }
                                }
                            }

                            // If driver has a primary category id, its root determines primary role
                            if ($primaryCatId) {
                                $primaryRoot = $getRootCategory($primaryCatId);
                                $primaryRootLabel = strtolower(trim(preg_replace('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $primaryRoot ? $primaryRoot->libelle : '')));
                                if (str_contains($primaryRootLabel, 'transport')) {
                                    $isTransportCategory = true;
                                    $isDeliveryCategory = false;
                                } elseif (str_contains($primaryRootLabel, 'delivery')) {
                                    $isDeliveryCategory = true;
                                }
                            }

                            // If driver has a transport vehicle, transport takes priority over secondary delivery add-ons
                            if ($hasVehicle && $isTransportCategory) {
                                $isDeliveryCategory = false;
                            }

                            // Determine primary console and flags:
                            if ($isTransportCategory) {
                                // Transport & Mobility driver: Cab, Taxi, Auto, Bike Taxi
                                $row['is_transport_category'] = true;
                                $row['is_delivery_partner'] = false;
                                $row['is_home_service_provider'] = false;
                                $row['primary_console'] = 'taxi';
                            } elseif ($isDeliveryCategory || $isBikeRider) {
                                // Delivery & Logistics driver: Delivery Partner, Parcel Delivery, Food Delivery
                                $row['is_transport_category'] = false;
                                $row['is_delivery_partner'] = true;
                                $row['is_home_service_provider'] = false;
                                $row['primary_console'] = 'delivery';
                            } else {
                                // Home Services or Marketplace driver
                                $row['is_transport_category'] = false;
                                $row['is_delivery_partner'] = false;
                                $row['is_home_service_provider'] = true;
                                $row['primary_console'] = 'home_service';
                            }

                            $row['is_bike_rider'] = $isBikeRider;

                            if ($row['is_home_service_provider']) {
                                $row['is_verified'] = 'yes';
                                $row['statut'] = 'yes';
                                $row['statut_vehicule'] = 'yes';
                                DB::table('tj_conducteur')->where('id', $id_user)->update([
                                    'is_verified' => 1,
                                    'statut' => 'yes',
                                    'statut_vehicule' => 'yes',
                                ]);
                            } else {
                                $dbVerified = DB::table('tj_conducteur')->where('id', $id_user)->value('is_verified');
                                $row['is_verified'] = ($dbVerified == 1) ? 'yes' : 'no';
                            }
                        }

                        $row['id']=(string)$id_user;
                        $row['accesstoken'] = $accesstoken;
                        $row['referral_code'] = \App\Services\ReferralCodeService::getOrCreateReferralCode((int)$id_user, 'driver');

                        $rideEarnings = DB::table('tj_requete')->where('id_conducteur', $id_user)->where('statut', 'completed')->sum('montant');
                        $parcelEarnings = 0;
                        if (\Illuminate\Support\Facades\Schema::hasTable('parcel_orders')) {
                            $parcelEarnings = DB::table('parcel_orders')->where('id_conducteur', $id_user)->where('status', 'completed')->sum('amount');
                        }
                        $serviceEarnings = 0;
                        if (\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
                            $serviceEarnings = DB::table('service_requests')->where('driver_id', $id_user)->whereIn('status', ['Completed', 'completed'])->sum('amount');
                        }
                        $calcEarn = round(floatval($rideEarnings) + floatval($parcelEarnings) + floatval($serviceEarnings), 2);
                        $row['earn_amount'] = (string) $calcEarn;

                        // Driver wallet balance should strictly reflect actual withdrawable/debt balance in tj_conducteur.amount
                        $row['amount'] = (string) number_format(floatval($row['amount'] ?? 0), 2, '.', '');
                        if (empty($row['ac_no']) || strlen(trim((string)$row['ac_no'])) != 12) {
                            $row['ac_no'] = \App\Services\PocketNumberService::getOrCreatePocketNumber((int)$id_user, 'driver');
                        }

                        $response['success']= 'success';
                        $response['error']=null;
                        $response['message']= 'successfully';
                        $response['data'] = $row;

                    } else {

                        $response['success']= 'Failed';

                        $response['error']='Failed to fetch data';

                    }

                    

                }else{

                    $response['success'] = 'Failed';

                    $response['error'] = 'Your account is not activated, please contact to administartor';

                }

                

            }else{

                $response['success']= 'Failed';

                $response['error']='Driver Not Found';

            }

        }

        else{

            $response['success']= 'Failed';

            $response['error']='Not Found';

        }



        return response()->json($response);

    }

}


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
                        if ($row['subscription_plan']!=null && $row['subscription_plan']!='' && $row['subscription_plan']['image'] != '') {

                            if (file_exists(public_path('assets/images/subscription' . '/' . $row['subscription_plan']['image']))) {

                                $subscriptionPlanImg = asset('assets/images/subscription') . '/' . $row['subscription_plan']['image'];
                            } else {

                                $subscriptionPlanImg = asset('assets/images/placeholder_image.jpg');
                            }

                            $row['subscription_plan']['image'] = $subscriptionPlanImg;
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



                        //set flag for verified

                        if($row['is_verified'] == 1){

                            $row['is_verified'] = 'yes';

                        }else{

                            $row['is_verified'] = 'no';

                        }

                        $row['selected_categories'] = DB::table('tj_conducteur_categories')
                            ->where('driver_id', $id_user)
                            ->get()
                            ->map(fn($item) => (string)($item->subcategory_id ?? $item->category_id))
                            ->toArray();

                        $row['onboarding_completed'] = DB::table('tj_conducteur_categories')
                            ->where('driver_id', $id_user)
                            ->exists() ? 'yes' : 'no';

                        // Drivers whose selected categories are vehicle-based
                        // (cab, delivery, parcel, etc.) use the native app shell.
                        // Only pure home-service categories get the web dashboard.
                        $allCategoriesById = DB::table('tj_categorie_user')
                            ->select('id', 'parent_id', 'libelle')
                            ->get()
                            ->keyBy('id');

                        $nativeDashboardRoots = [
                            'Transport & Mobility',
                            'Delivery & Logistics',
                        ];

                        $isTransportCategory = false;
                        $isHomeServiceProvider = false;
                        $homeServiceProfessions = [
                            'electrician', 'plumber', 'cleaner', 'carpenter', 'painter',
                            'pest control', 'ac repair', 'appliance repair', 'home tutor',
                            'maid', 'cook', 'babysitter', 'physiotherapist', 'nurse',
                        ];

                        foreach ($row['selected_categories'] as $catId) {
                            $current = $allCategoriesById->get((int) $catId);
                            $depth = 0;
                            while ($current && $depth < 8) {
                                $normalized = preg_replace(
                                    '/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u',
                                    '',
                                    $current->libelle ?? ''
                                );
                                $normalized = trim($normalized);
                                $normalizedLower = strtolower($normalized);

                                if (str_contains($normalizedLower, 'home services')) {
                                    $isHomeServiceProvider = true;
                                }
                                foreach ($homeServiceProfessions as $profession) {
                                    if ($normalizedLower === $profession || str_contains($normalizedLower, $profession)) {
                                        $isHomeServiceProvider = true;
                                        break;
                                    }
                                }

                                foreach ($nativeDashboardRoots as $root) {
                                    if ($normalized === $root || str_contains($normalized, $root)) {
                                        $isTransportCategory = true;
                                        break 2;
                                    }
                                }
                                $current = $current->parent_id ? $allCategoriesById->get($current->parent_id) : null;
                                $depth++;
                            }
                        }

                        if (!$isTransportCategory && ($row['parcel_delivery'] ?? '') === 'yes') {
                            $isTransportCategory = true;
                        }

                        if ($row['onboarding_completed'] === 'yes' && !$isTransportCategory) {
                            $isHomeServiceProvider = true;
                        }

                        if ($isHomeServiceProvider) {
                            $isTransportCategory = false;
                            $row['is_verified'] = 'yes';
                            $row['statut'] = 'yes';
                            $row['statut_vehicule'] = 'yes';
                            DB::table('tj_conducteur')->where('id', $id_user)->update([
                                'is_verified' => 1,
                                'statut' => 'yes',
                                'statut_vehicule' => 'yes',
                            ]);
                        }

                        $row['is_transport_category'] = $isTransportCategory;
                        $row['is_home_service_provider'] = $isHomeServiceProvider;

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


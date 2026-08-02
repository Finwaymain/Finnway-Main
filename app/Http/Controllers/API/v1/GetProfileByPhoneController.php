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

                    $checkaccount = Driver::where('phone', $phone)->where('statut', 'yes')->first();

                }else{

                    $checkaccount = Driver::where('email', $email)->where('statut', 'yes')->first();

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

                        // Drivers whose selected categories never touch the
                        // "Transport & Mobility" tree (i.e. only home-service /
                        // non-vehicle categories) get sent to the web-based
                        // dashboard instead of the native taxi-style home screen.
                        $allCategoriesById = DB::table('tj_categorie_user')
                            ->select('id', 'parent_id', 'libelle')
                            ->get()
                            ->keyBy('id');

                        $isTransportCategory = false;
                        foreach ($row['selected_categories'] as $catId) {
                            $current = $allCategoriesById->get((int) $catId);
                            $depth = 0;
                            while ($current && $depth < 5) {
                                if (trim($current->libelle) === '🚕 Transport & Mobility') {
                                    $isTransportCategory = true;
                                    break 2;
                                }
                                $current = $current->parent_id ? $allCategoriesById->get($current->parent_id) : null;
                                $depth++;
                            }
                        }
                        $row['is_transport_category'] = $isTransportCategory;

                        $row['id']=(string)$id_user;

                        $row['accesstoken'] = $accesstoken;

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


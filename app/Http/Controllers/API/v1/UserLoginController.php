<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriversDocuments;
use App\Models\UserApp;
use Illuminate\Http\Request;
use DB;

class UserLoginController extends Controller
{

    public function __construct()
    {
        $this->limit = 20;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function login(Request $request)
    {

        $date_heure = date('Y-m-d H:i:s');
        $id_user = "";
        $mdp = md5($request->get('mdp'));
        $email = $request->get('email');
        $mdp = str_replace("'", "\'", $mdp);
        $email = str_replace("'", "\'", $email);
        $user_cat = $request->get('user_cat');
        $accesstoken = $request->header('accesstoken');
        
        $response = array();

        if (!empty($request->get('mdp') && $request->get('email'))) {
			//for customer
            if($user_cat == 'customer') {
                $checkuser = UserApp::where('email', $email)->first();
				if (!empty($checkuser)) {
                	
                    $checkaccount = UserApp::where('email', $email)->where('statut', 'yes')->first();
                    if (!empty($checkaccount)) {
                        	
                        $row = $checkuser->toArray();
                        if($row['mdp'] == $mdp){
                            $response['success'] = 'Success';
                            $response['error'] = null;
                            $response['message'] = 'Login Sucessfully';
                            $accesstoken = $accesstoken ? $accesstoken : $this->adduseraccess($row['id'], 'customer');
                            unset($row['mdp']);
							
                            $row['user_cat'] = "user_app";
                            $row['online'] = "";
                            $id_user = $row['id'];
                            
                            $get_country = DB::table('tj_country')->select('*')->where('statut', '=', 'yes')->get();
                            foreach ($get_country as $row_country) {
                                $row['country'] = $row_country->code;
                            }

                            $get_admin_commission = DB::table('tj_commission')->select('*')->where('statut', '=', 'yes')->get();
                            foreach ($get_admin_commission as $row_commission) {
                                $row['admin_commission'] = $row_commission->value;
                                $row['commision_type'] = $row_commission->type;

                            }

                            if (!empty($row)) {
                                if($row['photo_path'] != '') {
                                    if (file_exists(public_path('assets/images/users' . '/' . $row['photo_path']))) {
                                        $image_user = asset('assets/images/users') . '/' . $row['photo_path'];
                                    } else {
                                        $image_user = asset('assets/images/placeholder_image.jpg');
                                    }
                                    $row['photo_path'] = $image_user;
                                }
                                $row['accesstoken'] = $accesstoken;
                                $response['data'] = $row;
								
                            } else {
                                $response['success'] = 'Failed';
                                $response['error'] = 'Incorrect Password or Email';
                            }
                        } else {
                            $response['success'] = 'Failed';
                            $response['error'] = 'Incorrect Password';
                        }
                    } else {
                        $response['success'] = 'Failed';
                        $response['error'] = 'Your account is not activated, please contact to administartor';
                    }
                } else {
                    $response['success'] = 'Failed';
                    $response['error'] = 'User not found';
                }
            
			//for driver
			
			} elseif ($user_cat == 'driver') {
                
                $checkuser = Driver::where('email', $email)->first();
                
                if (!empty($checkuser)) {
                	
                    $checkaccount = Driver::where('email', $email)->first();
                    
                    if (!empty($checkaccount)){
                    	
                        $row = $checkuser->toArray();

                        if($row['mdp'] == $mdp) {
                            	
                            $response['success'] = 'success';
                            $response['error'] = null;
                            $response['message'] = 'Login Sucessfully';
                            $accesstoken = $accesstoken ? $accesstoken : $this->adduseraccess($row['id'], 'driver');
							
                            unset($row['mdp']);
							
                            $row['user_cat'] = "driver";
                            $id_user = $row['id'];

                            $get_currency = DB::table('tj_currency')->select('*')->where('statut', '=', 'yes')->get();
							
                            if($row['is_verified'] == 1){
                                $row['is_verified'] = 'yes';
                            }else{
                                $row['is_verified'] = 'no';
                            }

                            $get_country = DB::table('tj_country')->select('*')->where('statut', '=', 'yes')->get();
                            
                            foreach ($get_country as $row_country) {
                                $row['country'] = $row_country->code;
                            }

                            $get_admin_commission = DB::table('tj_commission')->select('*')->where('statut', '=', 'yes')->get();
                            
                            foreach ($get_admin_commission as $row_commission) {
                                $row['admin_commission'] = $row_commission->value;
                                $row['commision_type'] = $row_commission->type;

                            }

                  
	                    	$get_vehicle = DB::table('tj_vehicule')->select('*')->where('statut', '=', 'yes')
                            ->where('id_conducteur', '=', $id_user)->get();
	
	                    	foreach ($get_vehicle as $row_vehicle) {
	                        	$row['brand'] = $row_vehicle->brand;
	                        	$row['model'] = $row_vehicle->model;
	                        	$row['color'] = $row_vehicle->color;
	                        	$row['numberplate'] = $row_vehicle->numberplate;
	                    	}

                            $row['selected_categories'] = DB::table('tj_conducteur_categories')
                                ->where('driver_id', $id_user)
                                ->get()
                                ->map(fn($item) => (string)($item->subcategory_id ?? $item->category_id))
                                ->toArray();

                            $row['onboarding_completed'] = DB::table('tj_conducteur_categories')
                                ->where('driver_id', $id_user)
                                ->exists() ? 'yes' : 'no';

	                     	if(!empty($row)){
		                        $row['photo'] = '';
		                        if ($row['photo_path'] != '') {
		                            if (file_exists(public_path('assets/images/driver' . '/' . $row['photo_path']))) {
		                                $image_user = asset('assets/images/driver') . '/' . $row['photo_path'];
		                            } else {
		                                $image_user = asset('assets/images/placeholder_image.jpg');
		                             }
		                             $row['photo_path'] = $image_user;
		                        }
		                         $row['accesstoken'] = $accesstoken;

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

		                         $response['data'] = $row;
	                     	} else {
	                        	 $response['success'] = 'Failed';
	                         	$response['error'] = 'Incorrect Password or Email';
	                     	}
                   
                		} else {
                        	$response['success'] = 'Failed';
                        	$response['error'] = 'Incorrect Password';
	                    }
                    } else {
                        $response['success'] = 'Failed';
                        $response['error'] = 'Your account is not activated, please contact to administartor';
                    }
                } else {
                    $response['success'] = 'Failed';
                    $response['error'] = 'Driver Not Found';
                }
            } else {
                $response['success'] = 'Failed';
                $response['error'] = 'Not Found';
            }
        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'some fields are missing';
        }
        
        return response()->json($response);
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

    public function testkey()
    {

        return "Success";
    }

}

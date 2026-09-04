<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\Controller;

use App\Models\UserApp;

use App\Models\Driver;

use App\Models\Currency;

use App\Models\Country;

use App\Models\Referral;

use Illuminate\Http\Request;

use DB;
use App\Services\PhoneService;



class UserController extends Controller

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

    public function index()

    {

        $users = UserApp::all();

        return response()->json($users);

    }



    // public function register(Request $request)
    // {

    //     $prenom = $request->get('firstname');

    //     $prenom = str_replace("'", "\'", $prenom);

    //     $nom = $request->get('lastname');

    //     $nom = str_replace("'", "\'", $nom);

    //     $phone = $request->get('phone');

    //     $email = $request->get('email');

    //     $mdp = $request->get('password');

    //     $mdp = str_replace("'", "\'", $mdp);

    //     $login_type = $request->get('login_type');

    //     $tonotify = $request->get('tonotify');

    //     $account_type = $request->get('account_type');

    //     $referral_code = $request->get('referral_code');

    //     $mdp = md5($mdp);

    //     $date_heure = date('Y-m-d H:i:s');



    //     if ($login_type == "phoneNumber" && empty($mdp)) {

    //         $response['success'] = 'Failed';

    //         $response['error'] = 'Password is required.';

    //         return response()->json($response);

    //     }

    //     if ($account_type == "customer") {



    //         $chkephone = UserApp::where('phone', $phone)->first();

    //         $chkemail = UserApp::where('email', $email)->first();



    //         if (! empty($chkephone) or ! empty($chkemail)) {

    //             if (! empty($chkephone)) {

    //                 $response['success'] = 'Failed';

    //                 $response['error'] = 'Phone number already exist...';

    //                 return response()->json($response);

    //             }



    //             if (! empty($chkemail)) {

    //                 $response['success'] = 'Failed';

    //                 $response['error'] = 'Email already exist...';

    //                 return response()->json($response);

    //             }

    //         } else {

    //             $gender = $request->get('gender');

    //             $age = $request->get('age');



    //             $insertdata = DB::insert("insert into tj_user_app(prenom,nom,phone,mdp,statut,login_type,tonotify,creer,statut_nic,email,age,gender)

    //                 values('" . $prenom . "','" . $nom . "','" . $phone . "','" . $mdp . "','yes','" . $login_type . "','" . $tonotify . "','" . $date_heure . "','no','" . $email . "','" . $age . "','" . $gender . "')");



    //             $id = DB::getPdo()->lastInsertId();



    //             $referralBy = '';

    //             if ($referral_code != '') {

    //                 $query = Referral::Where('referral_code', $referral_code)->first();

    //                 if (! empty($query)) {

    //                     $referralBy = $query->user_id;

    //                 }



    //             }

    //             $uniqid = uniqid();

    //             $rand_start = rand(1, 5);

    //             $userReferralCode = substr($uniqid, $rand_start, 5);

    //             Referral::insert([

    //                 'user_id' => $id,

    //                 'referral_by_id' => $referralBy ? $referralBy : null,

    //                 'referral_code' => $userReferralCode,

    //                 'code_used' => 'false',

    //                 'creer' => $date_heure

    //             ]);



    //             if ($id > 0) {

    //                 $response['success'] = 'success';

    //                 $response['error'] = null;

    //                 $response['message'] = 'User Registered successfully';



    //                 $get_user = UserApp::where('id', $id)->first();

    //                 $row = $get_user->toArray();

    //                 unset($row['mdp']);

    //                 $row['user_cat'] = "user_app";

    //                 $row['accesstoken'] = $this->adduseraccess($row['id'], 'customer');



    //                 $get_currency = Currency::where('statut', 'yes')->first();

    //                 $row_currency = $get_currency->toArray();

    //                 $row['currency'] = $row_currency['symbole'];

    //                 $row['decimal_digit'] = $row_currency['decimal_digit'];



    //                 $row['country'] = '';

    //                 $get_country = Country::where('statut', 'yes')->first();

    //                 if (! empty($get_country)) {

    //                     $row_country = $get_country->toArray();

    //                     $row['country'] = $row_country['code'];



    //                 }



    //                 $row['country'] = $row_country['code'];

    //                 $get_admin_commission = DB::table('tj_commission')->select('*')->where('statut', '=', 'yes')->get();

    //                 foreach ($get_admin_commission as $row_commission) {

    //                     $row['admin_commission'] = $row_commission->value;

    //                 }

    //                 $row['referral_code'] = $userReferralCode;

    //                 $row['referral_by'] = $referralBy ? $referralBy : null;

    //                 $row['id'] = (string) $id;

    //                 $response['data'] = $row;

    //                 return response()->json($response);

    //             } else {

    //                 $response['success'] = 'Failed';

    //                 $response['error'] = 'Id Not Found';

    //                 return response()->json($response);

    //             }

    //         }

    //     } elseif ($account_type == "driver") {



    //         $chkephone = Driver::where('phone', $phone)->first();

    //         $chkemail = Driver::where('email', $email)->first();



    //         if (! empty($chkephone) or ! empty($chkemail)) {



    //             if (! empty($chkephone)) {



    //                 $response['success'] = 'Failed';

    //                 $response['error'] = 'Phone number already exist...';

    //                 return response()->json($response);



    //             }



    //             if (! empty($chkemail)) {



    //                 $response['success'] = 'Failed';

    //                 $response['error'] = 'Email already exist...';

    //                 return response()->json($response);

    //             }



    //         } else {



    //             $insertdata = DB::insert("insert into tj_conducteur(online,prenom,nom,phone,mdp,statut,login_type,tonotify,creer,updated_at,status_car_image,statut_vehicule,email,address,amount,parcel_delivery,driver_on_ride)

    //             values('no','" . $prenom . "','" . $nom . "','" . $phone . "','" . $mdp . "','no','" . $login_type . "','" . $tonotify . "','" . $date_heure . "','" . $date_heure . "','no','no','" . $email . "','','0','yes','no')");

    //             $id = DB::getPdo()->lastInsertId();



    //             if ($id > 0) {

    //                 $response['success'] = 'success';

    //                 $response['error'] = null;

    //                 $response['message'] = 'Driver Registered Success';



    //                 $get_user = Driver::where('id', $id)->first();

    //                 $row = $get_user->toArray();

    //                 unset($row['mdp']);



    //                 $row['accesstoken'] = $this->adduseraccess($row['id'], 'driver');

    //                 $row['user_cat'] = "driver";



    //                 $get_currency = Currency::where('statut', 'yes')->first();

    //                 $row_currency = $get_currency->toArray();

    //                 $row['currency'] = $row_currency['symbole'];



    //                 $row['country'] = '';

    //                 $get_country = Country::where('statut', 'yes')->first();

    //                 if (! empty($get_country)) {

    //                     $row_country = $get_country->toArray();

    //                     $row['country'] = $row_country['code'];



    //                 }



    //                 $get_admin_commission = DB::table('tj_commission')->select('*')->where('statut', '=', 'yes')->get();

    //                 foreach ($get_admin_commission as $row_commission) {

    //                     $row['admin_commission'] = $row_commission->value;

    //                 }

    //                 $row['id'] = (string) $id;

    //                 $response['data'] = $row;

    //                 return response()->json($response);

    //             } else {

    //                 $response['success'] = 'Failed';

    //                 $response['error'] = 'Id Not Found';

    //                 return response()->json($response);



    //             }

    //             $emailsubject = '';

    //             $emailmessage = '';

    //             $emailtemplate = DB::table('email_template')->select('*')->where('type', 'new_registration')->first();

    //             if (! empty($emailtemplate)) {

    //                 $emailsubject = $emailtemplate->subject;

    //                 $emailmessage = $emailtemplate->message;

    //                 $send_to_admin = $emailtemplate->send_to_admin;

    //             }



    //             $email = DB::table('tj_settings')->select('contact_us_email')->value('contact_us_email');

    //             $email = $email ? $email : 'none@none.com';

    //             $to = '';

    //             if ($send_to_admin == "true") {

    //                 $to = $email;

    //             }



    //             $app_name = env('APP_NAME', 'Cabme');

    //             $date = date('d F Y');

    //             $emailmessage = str_replace("{AppName}", $app_name, $emailmessage);

    //             $emailmessage = str_replace("{UserName}", $row['nom'] . " " . $row['prenom'], $emailmessage);

    //             $emailmessage = str_replace("{UserEmail}", $row['email'], $emailmessage);

    //             $emailmessage = str_replace("{UserPhone}", $row['phone'], $emailmessage);

    //             $emailmessage = str_replace('{UserId}', $row['id'], $emailmessage);

    //             $emailmessage = str_replace('{Date}', $date, $emailmessage);



    //             // Always set content-type when sending HTML email

    //             $headers = "MIME-Version: 1.0" . "\r\n";

    //             $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    //             $headers .= 'From: ' . $app_name . '<' . $email . '>' . "\r\n";

    //             mail($to, $emailsubject, $emailmessage, $headers);



    //         }

    //     } else {

    //         $response['success'] = 'Failed';

    //         $response['error'] = 'Not Found';

    //         return response()->json($response);

    //     }



    // }

    // new register code with common_user_base table 2025-12-09
   public function register(Request $request)
{
    $prenom = $request->get('firstname');
    $prenom = str_replace("'", "\'", $prenom);
    $nom = $request->get('lastname');
    $nom = str_replace("'", "\'", $nom);
    $phone = PhoneService::normalize($request->get('phone'));
    $email = $request->get('email');
    $mdp = $request->get('password');
    $mdp = str_replace("'", "\'", $mdp);
    $login_type = $request->get('login_type');
    $tonotify = $request->get('tonotify');
    $account_type = $request->get('account_type');
    $referral_code = $request->get('referral_code');
    $mdp = md5($mdp);
    $date_heure = date('Y-m-d H:i:s');

    if ($login_type == "phoneNumber" && empty($mdp)) {
        return response()->json(['success' => 'Failed', 'error' => 'Password is required.']);
    }

    // ====================== CUSTOMER ======================
    if ($account_type == "customer") {

        $chkephone = PhoneService::customerExists($phone);
        $chkemail = UserApp::where('email', $email)->first();

        if ($chkephone || !empty($chkemail)) {
            return response()->json([
                'success' => 'Failed',
                'error' => $chkephone ? 'Phone number already exist...' : 'Email already exist...'
            ]);
        }

        $gender = $request->get('gender');
        $age = $request->get('age');

        // ✅ INSERT CUSTOMER WITHOUT ac_no (TEMP)
        DB::insert("insert into tj_user_app(prenom,nom,phone,mdp,statut,login_type,tonotify,creer,statut_nic,email,age,gender)
        values('$prenom','$nom','$phone','$mdp','yes','$login_type','$tonotify','$date_heure','no','$email','$age','$gender')");

        $id = DB::getPdo()->lastInsertId();

        // ✅ GENERATE CUSTOMER ac_no (7080)
        // $random_number = rand(10, 99);
        // $datetime_part = date('His');
        // $ac_no = substr("7080" . $id . $random_number . $datetime_part, 0, 12);
        
        // ✅ GENERATE GLOBALLY UNIQUE CUSTOMER ac_no (7080 + unified sequence)
        $ac_no = \App\Services\PocketNumberService::generateForUser((int)$id, 'customer');

        // ✅ Generate and sync unique referral code (FIINC...)
        $userRefCode = \App\Services\ReferralCodeService::getOrCreateReferralCode($id, 'customer');
        if (!empty($referral_code)) {
            $referrer = \App\Services\ReferralCodeService::resolveReferrer($referral_code);
            if ($referrer && (int)$referrer['user_id'] != $id) {
                DB::table('referral')->where('user_id', $id)->where('user_type', 'customer')->update([
                    'referral_by_id'   => (int)$referrer['user_id'],
                    'referral_by_type' => $referrer['user_type'] ?? 'customer',
                    'referral_by_code' => $referral_code,
                    'code_used'        => 'true',
                ]);
            }
        }

        if ($id > 0) {
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'User Registered successfully';

            $get_user = UserApp::where('id', $id)->first();
            $row = $get_user->toArray();
            unset($row['mdp']);

            $row['user_cat'] = "user_app";
            $row['accesstoken'] = $this->adduseraccess($row['id'], 'customer');
            $row['referral_code'] = $userRefCode;
            $row['id'] = (string)$id;

            $response['data'] = $row;
            return response()->json($response);
        }

    // ====================== DRIVER ======================
    } elseif ($account_type == "driver") {

        $chkephone = PhoneService::driverExists($phone);
        $chkemail = Driver::where('email', $email)->first();

        if ($chkephone || !empty($chkemail)) {
            return response()->json([
                'success' => 'Failed',
                'error' => $chkephone ? 'Phone number already exist...' : 'Email already exist...'
            ]);
        }

        // ✅ INSERT DRIVER WITHOUT ac_no (TEMP)
        $category_id = $request->get('category_id');
        $category_val = $category_id ? intval($category_id) : 'NULL';
        DB::insert("insert into tj_conducteur(online,prenom,nom,phone,mdp,statut,login_type,tonotify,creer,updated_at,status_car_image,statut_vehicule,email,address,amount,parcel_delivery,driver_on_ride,category_id,is_verified)
        values('no','$prenom','$nom','$phone','$mdp','no','$login_type','$tonotify','$date_heure','$date_heure','no','no','$email','','0','no','no',$category_val,0)");

        $id = DB::getPdo()->lastInsertId();

        // GENERATE GLOBALLY UNIQUE DRIVER ac_no (7060 + unified sequence)
        $ac_no = \App\Services\PocketNumberService::generateForUser((int)$id, 'driver');

        // Assign default free subscription plan
        $freePlan = DB::table('subscription_plans')->where('type', 'free')->first();
        if ($freePlan) {
            DB::table('tj_conducteur')->where('id', $id)->update([
                'subscriptionPlanId' => $freePlan->id,
                'subscriptionTotalOrders' => $freePlan->bookingLimit,
                'subscription_plan' => json_encode($freePlan),
            ]);
        }

        // ✅ Generate and sync unique referral code (FIINB...)
        $driverRefCode = \App\Services\ReferralCodeService::getOrCreateReferralCode($id, 'driver');
        if (!empty($referral_code)) {
            $referrer = \App\Services\ReferralCodeService::resolveReferrer($referral_code);
            if ($referrer && (int)$referrer['user_id'] != $id) {
                DB::table('referral')->where('user_id', $id)->where('user_type', 'driver')->update([
                    'referral_by_id'   => (int)$referrer['user_id'],
                    'referral_by_type' => $referrer['user_type'] ?? 'driver',
                    'referral_by_code' => $referral_code,
                    'code_used'        => 'true',
                ]);
            }
        }

        if ($id > 0) {

            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Driver Registered Success';

            $get_user = Driver::where('id', $id)->first();
            $row = $get_user->toArray();
            unset($row['mdp']);

            $row['accesstoken'] = $this->adduseraccess($row['id'], 'driver');
            $row['user_cat'] = "driver";
            $row['referral_code'] = $driverRefCode;
            $row['id'] = (string)$id;
            $row['onboarding_completed'] = 'no';
            $row['is_home_service_provider'] = false;
            $row['is_transport_category'] = false;
            $row['is_verified'] = 'no';
            $row['statut'] = 'no';
            $row['selected_categories'] = [];

            $response['data'] = $row;
            return response()->json($response);
        }

    } else {
        return response()->json(['success' => 'Failed', 'error' => 'Not Found']);
    }
}




    public static function url()

    {

        $actual_link = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $site_url = preg_replace('/^www\./', '', parse_url($actual_link, PHP_URL_HOST));

        if (($_SERVER['HTTPS'] && $_SERVER['HTTPS'] === 'on')) {

            return "https://" . $site_url;

        } else {

            return "http://" . $site_url;

        }

    }



    public function adduseraccess($user_id, $user_type)

    {

        $user = DB::table('users_access')->where('user_id', $user_id)->where('user_type', $user_type)->first();

        if ($user && ! empty($user->accesstoken)) {

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

            if (! $user) {

                $accessget = 1;

            }

        }

        return $accessToken;

    }

}


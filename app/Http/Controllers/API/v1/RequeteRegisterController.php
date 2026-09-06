<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\API\v1\GcmController;

use App\Http\Controllers\Controller;

use App\Models\Requests;
use App\Models\Settings;
use App\Models\Commission;
use App\Models\Driver;
use DB;
use Carbon\Carbon;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\File;

use App\Helpers\Helper;



class RequeteRegisterController extends Controller

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



    public function register(Request $request)

    {

      try {

        $user_id = $request->get('user_id');

        $ride_type = $request->get('ride_type');

        $user_detail = json_encode($request->get('user_detail'));

        $lat1 = $request->get('lat1');

        $lng1 = $request->get('lng1');

        $lat2 = $request->get('lat2');

        $lng2 = $request->get('lng2');

        $cout = $request->get('cout');

        $duree = $request->get('duree');

        $distance = $request->get('distance');

        $distance_unit = $request->get('distance_unit');

        $age_children1 = $request->get('age_children1');

        $age_children2 = $request->get('age_children2');

        $age_children3 = $request->get('age_children3');

        $trip_objective = $request->get('trip_objective');

        $trip_category = $request->get('trip_category');



        $id_conducteur = $request->get('id_conducteur');
        $id_type_vehicule = $request->get('id_type_vehicule');

        $id_payment = $request->get('id_payment');

        $depart_name = $request->get('depart_name');

        $destination_name = $request->get('destination_name');

        $image = $request->file('image');

        $place = $request->get('place');

        $place = str_replace("'", "\'", $place);

        $number_poeple = $request->get('number_poeple');

        $number_poeple = str_replace("'", "\'", $number_poeple);

        $statut_round = $request->get('statut_round');

        $stops=json_encode($request->get('stops'));

        if (!empty($request->get('date_retour')))

            $date_retour = $request->get('date_retour');

        else

            $date_retour = date('Y-m-d');

        if (!empty($request->get('heure_retour')))

            $heure_retour = $request->get('heure_retour');

        else

            $heure_retour = date('H:i:s');

        $date_heure = date('Y-m-d H:i:s');



        if (!empty($image)) {

            $file = $request->file('image');

            try {
                $filename = Helper::uploadToImageKit($file, '/rides/receipts');
            } catch (\Exception $e) {
                \Log::warning('ImageKit upload for ride receipt failed, falling back to local: ' . $e->getMessage());
                $extenstion = $file->getClientOriginalExtension();
                $filename = 'requete_images_' . time() . '.' . $extenstion;
                Helper::compressFile($file->getPathName(), public_path('images/recu_trajet_course').'/'.$filename, 8);
            }

        } else {

            $filename = '';

        }

        if (!empty($id_payment)) {

            if ($ride_type != "driver") {

                // ─── STALE RIDE AUTO-CLEANUP ────────────────────────────────────
                // Rides that are stuck in 'new' with no driver assigned for > 30 minutes
                // (caused by prior notification failures or app crashes) would permanently
                // block new bookings. Auto-cancel them before the new booking check.
                DB::table('tj_requete')
                    ->where('id_user_app', $user_id)
                    ->whereIn('statut', ['new', 'driver_rejected'])
                    ->where('id_conducteur', 0)
                    ->where('creer', '<', date('Y-m-d H:i:s', strtotime('-30 minutes')))
                    ->update(['statut' => 'canceled', 'modifier' => date('Y-m-d H:i:s')]);
                // ────────────────────────────────────────────────────────────────

                $activeRide=Requests::where('id_user_app', $user_id)->whereIn('statut', ['new', 'on ride', 'confirmed'])->count();
                if($activeRide>0){
                    $response['success'] = 'Failed';
                    $response['error'] = 'You are already on an active ride. Please complete or cancel your current ride before creating new one. ';
                    return response()->json($response);
                }
                $date_heure = date('Y-m-d H:i:s');

                if (empty($id_conducteur) || $id_conducteur == 0) {
                    $settings = DB::table('tj_settings')->select('driver_radios', 'minimum_deposit_amount')->first();
                    $radius = $settings->driver_radios ?? 10;
                    $minimum_wallet_balance = $settings->minimum_deposit_amount ?? 0;

                        $libelle = DB::table('tj_type_vehicule')->where('id', $id_type_vehicule)->value('libelle');
                        $typeIds = $libelle ? DB::table('tj_type_vehicule')->where('libelle', '=', $libelle)->pluck('id')->toArray() : [$id_type_vehicule];
                        $closestDriver = DB::table("tj_conducteur")
                            ->join('tj_vehicule', 'tj_vehicule.id_conducteur', '=', 'tj_conducteur.id')
                            ->select(
                                "tj_conducteur.id",
                                "tj_conducteur.fcm_id",
                                // Use 6371 (km) — driver_radios is stored in km
                                DB::raw("6371 * acos(GREATEST(-1, LEAST(1,
                                    cos(radians(" . floatval($lat1) . "))
                                    * cos(radians(tj_conducteur.latitude))
                                    * cos(radians(tj_conducteur.longitude) - radians(" . floatval($lng1) . "))
                                    + sin(radians(" . floatval($lat1) . "))
                                    * sin(radians(tj_conducteur.latitude))))) AS distance")
                            )
                            ->having('distance', '<=', $radius)
                            ->where('tj_conducteur.statut', 'yes')
                            ->where('tj_conducteur.online', '!=', 'no')
                            ->where('tj_conducteur.is_verified', '=', '1')
                            ->where(function($q) {
                                $q->whereNull('tj_conducteur.driver_on_ride')
                                  ->orWhere('tj_conducteur.driver_on_ride', '!=', 'yes');
                            })
                            // ->where('tj_conducteur.amount', '>=', $minimum_wallet_balance)
                            ->whereIn('tj_vehicule.id_type_vehicule', $typeIds)
                            ->orderBy('distance', 'asc')
                            ->first();

                    if ($closestDriver) {
                        $id_conducteur = $closestDriver->id;
                    } else {
                        $id_conducteur = 0;
                    }
                }

                $id = DB::table('tj_requete')->insertGetId([
                    'date_retour' => $date_retour,
                    'statut_round' => $statut_round ?? 'no',
                    'heure_retour' => $heure_retour,
                    'number_poeple' => $number_poeple ?? '1',
                    'place' => $place ?? '',
                    'id_payment_method' => $id_payment ?? 1,
                    'trajet' => $filename ?? '',
                    'depart_name' => $depart_name ?? '',
                    'destination_name' => $destination_name ?? '',
                    'id_conducteur' => $id_conducteur ?? 0,
                    'id_type_vehicule' => $id_type_vehicule ?? 0,
                    'id_user_app' => $user_id,
                    'latitude_depart' => $lat1 ?? '0',
                    'longitude_depart' => $lng1 ?? '0',
                    'latitude_arrivee' => $lat2 ?? '0',
                    'longitude_arrivee' => $lng2 ?? '0',
                    'statut' => ($id_conducteur > 0) ? 'new' : 'driver_rejected',
                    'creer' => $date_heure,
                    'distance' => $distance ?? '0',
                    'distance_unit' => $distance_unit ?? 'KM',
                    'montant' => $cout ?? '0',
                    'duree' => !empty($duree) ? $duree : '0',
                    'trip_objective' => $trip_objective ?? '',
                    'age_children1' => $age_children1 ?? '',
                    'age_children2' => $age_children2 ?? '',
                    'age_children3' => $age_children3 ?? '',
                    'feel_safe' => 0,
                    'tip_amount' => 0,
                    'statut_paiement' => '',
                    'modifier' => $date_heure,
                    'statut_course' => '',
                    'id_conducteur_accepter' => 0,
                    'trip_category' => '',
                    'feel_safe_driver' => 0,
                    'stops' => $stops ?? '[]'
                ]);

                if ($id > 0) {

                    $get_user = Requests::where('id', $id)->first();

                    $rowData = $get_user->toArray();

                }

                $title = str_replace("'", "\'", "New ride");

                $msg = str_replace("'", "\'", "You have just received a request from a client");

                $tab = explode("\\", $msg);
                $msg_ = "";
                for ($i = 0; $i < count($tab); $i++) {
                    $msg_ = $msg_ . "" . $tab[$i];
                }

                $message = array(
                    "body" => $msg_,
                    "title" => $title,
                    "sound" => "ride_request_sound",
                    "tag" => "ridenewrider",
                    "statut" => ($id_conducteur > 0) ? 'new' : 'driver_rejected'
                );

                if ($id > 0 && isset($rowData)) {
                    $rideArray = $rowData;
                    $rideArray['id'] = (string)$rideArray['id'];
                    $message = array_merge($rideArray, $message);
                }

                // ─── NOTIFICATIONS (isolated try-catch) ─────────────────────────
                // Notification failures (e.g. charset issues, FCM quota) must NEVER
                // affect the ride booking success response. The ride is already created.
                try {
                    if ($id_conducteur > 0) {
                        $fcm_token = DB::table('tj_conducteur')->where('fcm_id','!=','')->where('id','=',$id_conducteur)->value('fcm_id');
                        if (!empty($fcm_token)) {
                            GcmController::sendNotification($fcm_token, $message);
                        }

                        // Insert notification record for Driver
                        if (\Illuminate\Support\Facades\Schema::hasTable('tj_notification')) {
                            DB::table('tj_notification')->insert([
                                'titre' => $title,
                                'message' => $msg_,
                                'statut' => 'yes',
                                'creer' => $date_heure,
                                'modifier' => $date_heure,
                                'to_id' => $id_conducteur,
                                'from_id' => $user_id,
                                'type' => 'ridenewrider'
                            ]);
                        }

                        // Also notify the Customer that ride request is sent
                        $user_fcm = DB::table('tj_user_app')->where('fcm_id','!=','')->where('id','=',$user_id)->value('fcm_id');
                        if (!empty($user_fcm)) {
                            $userPayload = [
                                'title' => 'Ride Requested',
                                'body' => "Ride #{$id} requested. Searching for your captain...",
                                'sound' => 'mySound',
                                'tag' => 'ridebooked',
                                'statut' => 'new',
                                'ride_id' => (string) $id,
                            ];
                            if (isset($rowData)) {
                                $userPayload = array_merge($rowData, $userPayload);
                                $userPayload['id'] = (string) $userPayload['id'];
                            }
                            GcmController::sendNotification($user_fcm, $userPayload);

                            if (\Illuminate\Support\Facades\Schema::hasTable('tj_notification')) {
                                DB::table('tj_notification')->insert([
                                    'titre' => 'Ride Requested',
                                    'message' => "Ride #{$id} requested. Searching for your captain...",
                                    'statut' => 'yes',
                                    'creer' => $date_heure,
                                    'modifier' => $date_heure,
                                    'to_id' => $user_id,
                                    'from_id' => $id_conducteur,
                                    'type' => 'ridebooked'
                                ]);
                            }
                        }
                    } else {
                        $fcm_token = DB::table('tj_user_app')->where('fcm_id','!=','')->where('id','=',$user_id)->value('fcm_id');
                        if (!empty($fcm_token)) {
                            $userMsg = array(
                                "body" => "No drivers available in your area.",
                                "title" => "No Drivers Found",
                                "sound" => "mySound",
                                "tag" => "riderejected",
                                "statut" => "driver_rejected"
                            );
                            if ($id > 0 && isset($rowData)) {
                                $rideArray = $rowData;
                                $rideArray['id'] = (string)$rideArray['id'];
                                $userMsg = array_merge($rideArray, $userMsg);
                            }
                            GcmController::sendNotification($fcm_token, $userMsg);
                        }
                    }
                } catch (\Exception $notifEx) {
                    // Log but do NOT fail the booking response — the ride was created successfully.
                    \Log::warning('RequeteRegister notification error (ride created ok, id=' . $id . '): ' . $notifEx->getMessage());
                }
                // ────────────────────────────────────────────────────────────────



            } else {


                $setting = Settings::first();
                $subscriptionModel = $setting->subscription_model;
                $commissionData = Commission::first();
                $commissionModel = $commissionData->statut;
                $driverData = Driver::where('id', $id_conducteur)->first();
                if($subscriptionModel=='true' || $commissionModel=='yes'){
                    if(!empty($driverData->subscriptionTotalOrders)){
                        if(!empty($driverData->subscriptionExpiryDate)){
                            $expiryDate = Carbon::parse($driverData->subscriptionExpiryDate);
                            $currentDate = Carbon::now();
                            if ($expiryDate->lessThan($currentDate)) {
                                $response['success'] = 'Failed';
                                $response['error'] = 'Your have reached the maximum booking limit for the current plan,upgrade the subscription to continue.';
                                return response()->json($response);
                            }
                        }
                        if(intval($driverData->subscriptionTotalOrders)!='-1'){
                            if(intval($driverData->subscriptionTotalOrders)<=0){
                                $response['success'] = 'Failed';
                                $response['error'] = 'Your have reached the maximum booking limit for the current plan,upgrade the subscription to continue.';
                                return response()->json($response);
                            }
                        }
                    }else{
                        $response['success'] = 'Failed';
                        $response['error'] = 'Your have reached the maximum booking limit for the current plan,upgrade the subscription to continue.';
                        return response()->json($response);
                    }
                }
                $date_heure = date('Y-m-d H:i:s');



                $insertdata = DB::insert("insert into tj_requete(date_retour,statut_round,heure_retour,

            number_poeple,place,id_payment_method,trajet,depart_name,

            destination_name,id_conducteur,id_user_app,latitude_depart,longitude_depart,latitude_arrivee,

            longitude_arrivee,statut,creer,distance,distance_unit,montant,duree,trip_objective,age_children1,

            age_children2,age_children3,feel_safe,tip_amount,statut_paiement,

            modifier,statut_course,id_conducteur_accepter,trip_category,feel_safe_driver,stops,ride_type,user_info)

            values('" . $date_retour . "','" . $statut_round . "','" . $heure_retour . "','" . $number_poeple . "','" . $place . "','" . $id_payment . "','" . $filename . "','" . $depart_name . "','" . $destination_name . "'

            ,'" . $id_conducteur . "','" . $user_id . "','" . $lat1 . "','" . $lng1 . "','" . $lat2 . "','" . $lng2 . "',

            'confirmed','" . $date_heure . "','" . $distance . "', '" . $distance_unit . "', '" . $cout . "','" . $duree . "',

            '" . $trip_objective . "','" . $age_children1 . "','" . $age_children2 . "',

            '" . $age_children3 . "',0,0,'','" . $date_heure . "','',0,'',0,'" . $stops . "','" . $ride_type . "','" . $user_detail . "')");





                $id = DB::getPdo()->lastInsertId();

                if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
                    if ($driverData->subscriptionTotalOrders != null && $driverData->subscriptionTotalOrders != '' && $driverData->subscriptionTotalOrders != '-1') {
                        $remaningRides = intval($driverData->subscriptionTotalOrders) - 1;
                        Driver::where('id', $id_conducteur)->update(['subscriptionTotalOrders' => $remaningRides]);
                    }
                }

            }

            if ($id > 0) {
                

                $get_user = Requests::where('id', $id)->first();

                $row = $get_user->toArray();

                

                $row['stops']=json_decode($row['stops'],true);

                $row['user_info'] = json_decode($row['user_info'], true);

                if ($row['trajet'] != '') {

                    if (file_exists(public_path('images/recu_trajet_course/' . '/' . $row['trajet']))) {

                        $image_user = asset('images/recu_trajet_course/') . '/' . $row['trajet'];

                    } else {

                        $image_user = asset('assets/images/placeholder_image.jpg');



                    }

                    $row['trajet'] = $image_user;

                }



                $output[] = $row;

                $response['success'] = 'success';

                $response['error'] = null;

                $response['message'] = 'Successfully created';

                $response['data'] = $row;
                $response['data_list'] = $output;
                return response()->json($response);

            } else {

                $response['success'] = 'Failed';

                $response['error'] = 'Failed';
                return response()->json($response);
            }

        } else {

            $response['success'] = 'Failed';

            $response['error'] = 'some field required';
            return response()->json($response);
        }

      } catch (\Exception $e) {
          \Log::error('RequeteRegister error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
          return response()->json([
              'success' => 'Failed',
              'error' => 'Booking failed: ' . $e->getMessage()
          ], 200);
      }

    }





}


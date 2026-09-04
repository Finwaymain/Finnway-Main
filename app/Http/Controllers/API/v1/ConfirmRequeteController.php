<?php



namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;

use App\Models\Requests;
use App\Models\Settings;
use App\Models\Commission;
use App\Models\Driver;

use App\Models\Notification;

use Illuminate\Http\Request;

use App\Http\Controllers\API\v1\GcmController;

use DB;

class ConfirmRequeteController extends Controller

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

    public function confirmRequest(Request $request)

    {

        $months = array("January" => 'Jan', "February" => 'Feb', "March" => 'Mar', "April" => 'Apr', "May" => 'May', "June" => 'Jun', "July" => 'Jul', "August" => 'Aug', "September" => 'Sep', "October" => 'Oct', "November" => 'Nov', "December" => 'Dec');



        $id_requete = $request->get('id_ride');

        $id_user = $request->get('id_user');

        $driver_name = $request->get('driver_name');

        $from_id = $request->get('from_id');



        $lat_conducteur = $request->get('lat_conducteur');

        $lng_conducteur = $request->get('lng_conducteur');

        $lat_client = $request->get('lat_client');

        $lng_client = $request->get('lng_client');



        $lat_conducteur = str_replace(".", " ", $lat_conducteur);

        $lng_conducteur = str_replace(".", " ", $lng_conducteur);

        $lat_client = str_replace(".", " ", $lat_client);

        $lng_client = str_replace(".", " ", $lng_client);
        
        $setting = Settings::first();
        $subscriptionModel = $setting->subscription_model;
        $commissionData = Commission::first();
        $commissionModel = $commissionData->statut;
        $rideInfo=Requests::where('id', $id_requete)->first();
        $driverData = Driver::where('id', $from_id)->first();
        if (!empty($id_requete) && !empty($id_user) && !empty($from_id)) {
            if ($driverData) {
                $driver_name = $driverData->prenom . ' ' . $driverData->nom;
            }
            if ($driverData && ($subscriptionModel == 'true' || $commissionModel == 'yes') && $driverData->subscriptionTotalOrders != '' && $driverData->subscriptionTotalOrders != null && intval($driverData->subscriptionTotalOrders) != '-1') {
                if(intval($driverData->subscriptionTotalOrders)<=0){
                    $response['success'] = 'Failed';
                    $response['error'] = 'Your have reached the maximum booking limit for the current plan,upgrade the subscription to continue accepting new bookings.';
                    return response()->json($response);
                }
               
            }

            // Blocking Rule: If driver has outstanding cash collection due debt (negative balance), block accepting new booking
            if ($driverData && floatval($driverData->amount ?? 0) < 0) {
                $dueDebt = number_format(abs(floatval($driverData->amount)), 2);
                $response['success'] = 'Failed';
                $response['error'] = 'You have an outstanding cash collection due of ₹' . $dueDebt . '. Please clear your pending dues to continue accepting new bookings.';
                return response()->json($response);
            }
            $updatedata =  DB::update('update tj_requete set statut = ?, id_conducteur = ? where id = ? AND statut = ?', ['confirmed', $from_id, $id_requete, 'new']);



            if (!empty($updatedata)) {

                $otp = random_int(100000, 999999);
                $user =  Requests::where('id', $id_requete)->first();
               
                if ($driverData && ($subscriptionModel == 'true'|| $commissionModel == 'yes')) {

                    if ($driverData->subscriptionTotalOrders != null && $driverData->subscriptionTotalOrders != '' && $driverData->subscriptionTotalOrders != '-1') {
                        $remaningRides = intval($driverData->subscriptionTotalOrders) - 1;
                        Driver::where('id', $from_id)->update(['subscriptionTotalOrders' => $remaningRides]);
                    }
                }

                if ($user) {

                    $user->otp = $otp;

                    $user->otp_created = now();
                }

                $user->save();

                $sql = Requests::where('id', $id_requete)->first();

                $row = $sql->toArray();

                $row['id'] = (string)$row['id'];

                $row['creer'] = date("d", strtotime($row['creer'])) . " " . $months[date("F", strtotime($row['creer']))] . ", " . date("Y", strtotime($row['creer']));

                $row['date_retour'] = date("d", strtotime($row['date_retour'])) . " " . $months[date("F", strtotime($row['date_retour']))] . ", " . date("Y", strtotime($row['date_retour']));



                if ($row['trajet'] != '') {

                    if (file_exists(public_path('images/recu_trajet_course' . '/' . $row['trajet']))) {

                        $image_user = asset('images/recu_trajet_course') . '/' . $row['trajet'];
                    } else {

                        $image_user = asset('assets/images/placeholder_image.jpg');
                    }

                    $row['trajet'] = $image_user;
                }

                $tmsg = '';

                $terrormsg = '';



                $title = str_replace("'", "\'", "Confirmation of your ride");

                $msg = str_replace("'", "\'", $driver_name . " is Confirmed your ride.");



                $tab[] = array();

                $tab = explode("\\", $msg);

                $msg_ = "";

                for ($i = 0; $i < count($tab); $i++) {

                    $msg_ = $msg_ . "" . $tab[$i];
                }

                $driver = DB::table('tj_conducteur')->where('id', $row['id_conducteur'])->first();
                if ($driver) {
                    $row['prenomConducteur'] = $driver->prenom;
                    $row['nomConducteur'] = $driver->nom;
                    $row['photo_path'] = $driver->photo_path;
                    if ($row['photo_path'] != '') {
                        if (file_exists(public_path('assets/images/driver' . '/' . $row['photo_path']))) {
                            $row['photo_path'] = asset('assets/images/driver') . '/' . $row['photo_path'];
                        } else {
                            $row['photo_path'] = asset('assets/images/placeholder_image.jpg');
                        }
                    } else {
                        $row['photo_path'] = asset('assets/images/placeholder_image.jpg');
                    }
                }
                
                $sql_nb_avis = DB::table('tj_note')
                    ->select(DB::raw("COUNT(id) as nb_avis"), DB::raw("SUM(niveau) as somme"))
                    ->where('id_conducteur', '=', $row['id_conducteur'])
                    ->get();

                if (!empty($sql_nb_avis)) {
                    foreach ($sql_nb_avis as $row_nb_avis) {
                        $somme = $row_nb_avis->somme;
                    }
                    $nb_avis = $row_nb_avis->nb_avis;
                    if ($nb_avis != "0") {
                        $moyenne = $somme / $nb_avis;
                    } else {
                        $moyenne = 0;
                    }
                } else {
                    $somme = "0";
                    $nb_avis = "0";
                    $moyenne = 0;
                }
                $row['moyenne'] = $moyenne;
                $row['statut'] = 'confirmed';

                $message = array_merge($row, array("body" => $msg_, "title" => $title, "sound" => "default", "tag" => "rideconfirmed", "statut" => "confirmed"));

                $fcm_token = DB::table('tj_user_app')->where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');

                if (!empty($fcm_token)) {

                    GcmController::sendNotification($fcm_token, $message);



                    $date_heure = date('Y-m-d H:i:s');

                    $to_id = $request->get('id_user');



                    $insertdata = DB::insert("insert into tj_notification(titre,message,statut,creer,modifier,to_id,from_id,type)

            values('" . $title . "','" . $msg . "','yes','" . $date_heure . "','" . $date_heure . "','" . $to_id . "','" . $from_id . "','rideconfirmed')");

                    $sql_notification = Notification::orderby('id', 'desc')->first();

                    $data = $sql_notification->toArray();

                    $row['titre'] = $data['titre'];

                    $row['message'] = $data['message'];

                    $row['statut_notification'] = $data['statut'];

                    $row['to_id'] = $data['to_id'];

                    $row['from_id'] = $data['from_id'];

                    $row['type'] = $data['type'];
                }



                $row['tax'] = json_decode($row['tax'], true);

                $row['stops'] = json_decode($row['stops'], true);

                $row['user_info'] = json_decode($row['user_info'], true);



                // Terminate ringing alarm/notifications on other drivers' devices
                try {
                    $recentNotifTokens = DB::table('tj_notification')
                        ->join('tj_conducteur', 'tj_conducteur.id', '=', 'tj_notification.to_id')
                        ->where('tj_notification.type', 'ridenewrider')
                        ->where('tj_notification.to_id', '!=', $from_id)
                        ->where('tj_notification.creer', '>=', date('Y-m-d H:i:s', strtotime('-10 minutes')))
                        ->whereNotNull('tj_conducteur.fcm_id')
                        ->where('tj_conducteur.fcm_id', '!=', '')
                        ->pluck('tj_conducteur.fcm_id')
                        ->unique();

                    if ($recentNotifTokens->isNotEmpty()) {
                        $cancelPayload = [
                            'title' => 'Ride Taken',
                            'body' => 'This ride has been accepted by another driver.',
                            'tag' => 'booking_taken',
                            'statut' => 'taken',
                            'id_ride' => (string) $id_requete,
                        ];
                        foreach ($recentNotifTokens as $cToken) {
                            GcmController::sendNotification($cToken, $cancelPayload);
                        }
                    }
                } catch (\Throwable $cancelEx) {
                    \Log::warning('Failed sending ride cancellation push: ' . $cancelEx->getMessage());
                }

                $response['success'] = 'success';

                $response['error'] = null;

                $response['message'] = 'status successfully updated';

                $response['data'] = $row;
                return response()->json($response);
            } else {
                $response['success'] = 'Failed';
                $checkRide = Requests::where('id', $id_requete)->first();
                if ($checkRide && $checkRide->statut == 'confirmed') {
                    $response['error'] = 'This ride has already been accepted by another driver.';
                } else {
                    $response['error'] = 'Failed to update data';
                }
                return response()->json($response);
            }
       
        } else {

            $response['success'] = 'Failed';

            $response['error'] = 'some field are missing';
            return response()->json($response);
        }

        
    }
}

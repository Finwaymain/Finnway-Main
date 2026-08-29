<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\API\v1\GcmController;

use App\Http\Controllers\Controller;

use App\Models\Notification;

use App\Models\Requests;
use App\Models\Commission;
use App\Models\Driver;
use App\Models\Settings;
use DB;

use Illuminate\Http\Request;



class SetRejectedRequeteController extends Controller

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

    public function rejectedRequest(Request $request)

    {



        $id_requete = $request->get('id_ride');

        $id_user = $request->get('id_user');

        $driver_name = $request->get('name');

        $from_id = $request->get('from_id');

        $reason = $request->get('reason');

        $user_cat = $request->get('user_cat');

        $settings = Settings::first();
        $subscriptionModel = $settings ? $settings->subscription_model : null;
        $commissionData = Commission::first();
        $commissionModel = $commissionData ? $commissionData->statut : null;
        $sql = Requests::where('id', $id_requete)->first();

        if (empty($sql)) {
            return response()->json([
                'success' => 'success',
                'message' => 'Request already completed or processed',
                'data' => [],
            ]);
        }

        $drivertoReject = $sql->id_conducteur;
        $rideStatus = $sql->statut;

        if (empty($from_id)) {
            $from_id = $drivertoReject ?: $request->header('id_user');
        }

        if (empty($driver_name) && !empty($from_id)) {
            $conducteur = DB::table('tj_conducteur')->where('id', $from_id)->first();
            $driver_name = $conducteur ? trim(($conducteur->prenom ?? '') . ' ' . ($conducteur->nom ?? '')) : 'Driver';
        }
        if (empty($driver_name)) {
            $driver_name = 'Driver';
        }

        if (empty($id_user)) {
            $id_user = $sql->id_user_app;
        }

        if (!empty($id_requete)) {
            $rejectDriverIds = $sql->rejected_driver_id;

            $rejDriverIds = array();

            if ($rejectDriverIds != null) {

                $rejDriverIds = json_decode($rejectDriverIds, true);
            }



            $row_sql = $sql->toArray();

            if ($row_sql['trajet'] != '') {

                if (file_exists(public_path('images/recu_trajet_course' . '/' . $row_sql['trajet']))) {

                    $image_user = asset('images/recu_trajet_course') . '/' . $row_sql['trajet'];
                } else {

                    $image_user = asset('assets/images/placeholder_image.jpg');
                }

                $row_sql['trajet'] = $image_user;
            }



            if ($user_cat == 'driver') {

                $tmsg = '';

                $terrormsg = '';



                $title = str_replace("'", "\'", "Rejection of your ride");

                $msg = str_replace("'", "\'", $driver_name . " is cancelled your ride.");

                $reasons = str_replace("'", "\'", "$reason");



                $tab[] = array();

                $tab = explode("\\", $msg);

                $msg_ = "";

                for ($i = 0; $i < count($tab); $i++) {

                    $msg_ = $msg_ . "" . $tab[$i];
                }



                $row_sql['statut'] = 'driver_rejected';
                $message = array_merge($row_sql, array("body" => $msg_, "reasons" => $reasons, "title" => $title, "sound" => "mySound", "tag" => "riderejected", "statut" => "driver_rejected"));

                $fcm_token = DB::table('tj_user_app')->where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');

                if (!empty($fcm_token)) {

                    GcmController::sendNotification($fcm_token, $message);
                }



                $lat = $row_sql['latitude_depart'];

                $long = $row_sql['longitude_depart'];

                $vehicleType = DB::table('tj_vehicule')->select('id_type_vehicule')->where('id_conducteur', $from_id)->first();
                $id_type_vehicule = $vehicleType ? $vehicleType->id_type_vehicule : ($sql->id_type_vehicule ?? 0);

                $settings = DB::table('tj_settings')->select('driver_radios', 'minimum_deposit_amount')->first();

                $radius = $settings->driver_radios ?? 10;

                $minimum_wallet_balance = $settings->minimum_deposit_amount ?? 0;

                if (!in_array($from_id, $rejDriverIds)) {
                    array_push($rejDriverIds, $from_id);
                }
                $updateRejDriverArr = json_encode($rejDriverIds);
                
                // Update rejected list and temporarily reset driver to new
                DB::update('update tj_requete set rejected_driver_id = ?, statut = ? where id = ?', [$updateRejDriverArr, 'new', $id_requete]);
                
                // Perform automated rotation to assign next driver and notify them
                Requests::rotateRequestIfNeeded($id_requete, true);
                
                $sql_update = Requests::where('id', '=', $id_requete)->first();
                $row = $sql_update->toArray();
                $row['id'] = (string)$row['id'];
            } elseif ($user_cat == 'user_app') {



                $updatedata = DB::update('update tj_requete set statut = ? where id = ?', ['rejected', $id_requete]);

                $sql_update = Requests::where('id', '=', $id_requete)->first();

                $row = $sql_update->toArray();

                $row['id'] = (string)$row['id'];

                $tmsg = '';

                $terrormsg = '';



                $title = str_replace("'", "\'", "Cancellation of  ride");

                $msg = str_replace("'", "\'", $driver_name . " canceled the ride");

                $reasons = str_replace("'", "\'", "$reason");



                $tab[] = array();

                $tab = explode("\\", $msg);

                $msg_ = "";

                for ($i = 0; $i < count($tab); $i++) {

                    $msg_ = $msg_ . "" . $tab[$i];
                }



                $row['statut'] = 'rejected';
                $message = array_merge($row, array("body" => $msg_, "reasons" => $reasons, "title" => $title, "sound" => "mySound", "tag" => "riderejected", "statut" => "rejected"));

                $fcm_token = DB::table('tj_conducteur')->where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');

                if (!empty($fcm_token)) {

                    GcmController::sendNotification($fcm_token, $message);
                }
            }



            if (!empty($fcm_token)) {



                $date_heure = date('Y-m-d H:i:s');

                $from_id = $request->get('from_id');

                $to_id = $request->get('id_user');



                $insertdata = DB::insert("insert into tj_notification(titre,message,statut,creer,modifier,to_id,from_id,type)

                values('" . $title . "','" . $msg . "','yes','" . $date_heure . "','" . $date_heure . "','" . $to_id . "','" . $from_id . "','riderejected')");

                $sql_notification = Notification::orderby('id', 'desc')->first();

                $data = $sql_notification->toArray();

                $row['titre'] = $data['titre'];

                $row['message'] = $data['message'];

                $row['reason'] = $reason;

                $row['statut_notification'] = $data['statut'];

                $row['to_id'] = $data['to_id'];

                $row['from_id'] = $data['from_id'];

                $row['type'] = $data['type'];
            }
            if($rideStatus=='confirmed'){
                if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
                    $rejectedDriverData = Driver::where('id', $drivertoReject)->first();

                    if ($rejectedDriverData->subscriptionTotalOrders != '' && $rejectedDriverData->subscriptionTotalOrders != null && intval($rejectedDriverData->subscriptionTotalOrders != '-1')) {
                        $subscriptionTotalOrders = intval($rejectedDriverData->subscriptionTotalOrders) + 1;
                        Driver::where('id', $drivertoReject)->update(['subscriptionTotalOrders' => $subscriptionTotalOrders]);
                    }
                }
            }
            
            $response['success'] = 'success';

            $response['error'] = null;

            $response['message'] = 'status successfully updated';

            $response['data'] = $row;
        } else {

            $response['success'] = 'Failed';

            $response['error'] = 'some fields are missing';
        }

        return response()->json($response);
    }
}

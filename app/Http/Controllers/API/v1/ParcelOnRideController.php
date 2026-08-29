<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\API\v1\GcmController;

use App\Http\Controllers\Controller;

use App\Models\Driver;

use App\Models\Notification;

use App\Models\ParcelOrder;

use DB;

use Illuminate\Http\Request;



class ParcelOnRideController extends Controller

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





    public function onrideRequest(Request $request)

    {

        $id_parcel = $request->get('id_parcel');

        $id_user = $request->get('id_user');

        $driver_name = $request->get('driver_name');

        $driver_id = $request->get('driver_id');
        $otp = $request->get('otp');
        $sql = ParcelOrder::where('id', $id_parcel)->first();
        
        $settings = DB::table('tj_settings')->select('show_ride_otp')->first();
        if ($settings && $settings->show_ride_otp == 'yes') {
            if ($sql->otp != null && $sql->otp != $otp) {
                $response['success'] = 'failed';
                $response['error'] = 'Invalid OTP. Please enter the correct OTP to start the delivery.';
                return response()->json($response);
            }
        }

        $driverId = $sql->id_conducteur;
        $driverDetail = Driver::where('id', $driverId)->first();
        if ($driverDetail->driver_on_ride == 'no') {
            $updatedata = ParcelOrder::where('id', $id_parcel)->update(['status' => 'onride']);


            $updateDriver = Driver::where('id', $driverId)->update(['driver_on_ride' => 'yes']);

            if (! empty($updatedata)) {





                $row = $sql->toArray();



                if ($row['parcel_image'] != '') {

                    if (file_exists(public_path('images/parcel_order' . '/' . $row['parcel_image']))) {

                        $image_user = asset('images/parcel_order') . '/' . $row['parcel_image'];
                    } else {

                        $image_user = asset('assets/images/placeholder_image.jpg');
                    }

                    $row['parcel_image'] = $image_user;
                }

                $months = array("January" => 'Jan', "February" => 'Feb', "March" => 'Mar', "April" => 'Apr', "May" => 'May', "June" => 'Jun', "July" => 'Jul', "August" => 'Aug', "September" => 'Sep', "October" => 'Oct', "November" => 'Nov', "December" => 'Dec');

                $row['created_at'] = date("d", strtotime($row['created_at'])) . " " . $months[date("F", strtotime($row['created_at']))] . ", " . date("Y", strtotime($row['created_at']));



                $driver = Driver::where('id', $row['id_conducteur'])->first();

                $row['prenomConducteur'] = $driver->prenom;

                $row['nomConducteur'] = $driver->nom;

                $row['photo_path'] = $driver->photo_path;

                if ($row['photo_path'] != '') {

                    if (file_exists(public_path('assets/images/driver' . '/' . $row['photo_path']))) {

                        $image_user = asset('assets/images/driver') . '/' . $row['photo_path'];
                    } else {

                        $image_user = asset('assets/images/placeholder_image.jpg');
                    }
                } else {

                    $image_user = asset('assets/images/placeholder_image.jpg');
                }



                $row['photo_path'] = $image_user;



                $sql_nb_avis = DB::table('tj_note')

                    ->select(DB::raw("COUNT(id) as nb_avis"), DB::raw("SUM(niveau) as somme"))

                    ->where('id_conducteur', '=', $row['id_conducteur'])

                    ->get();



                if (! empty($sql_nb_avis)) {

                    foreach ($sql_nb_avis as $row_nb_avis)

                        $somme = $row_nb_avis->somme;

                    $nb_avis = $row_nb_avis->nb_avis;

                    if ($nb_avis != "0")

                        $moyenne = $somme / $nb_avis;
                    else

                        $moyenne = 0;
                } else {

                    $somme = "0";

                    $nb_avis = "0";

                    $moyenne = 0;
                }

                $row['moyenne'] = $moyenne;

                $title = str_replace("'", "\'", "Delivering of your Parcel");

                $msg = str_replace("'", "\'", $driver_name . " is started to deliver your parcel.");



                $tab[] = array();

                $tab = explode("\\", $msg);

                $msg_ = "";

                for ($i = 0; $i < count($tab); $i++) {

                    $msg_ = $msg_ . "" . $tab[$i];
                }



                $message = array("body" => $msg_, "title" => $title, "sound" => "mySound", "tag" => "rideonride");

                $fcm_token = DB::table('tj_user_app')->where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');

                if (! empty($fcm_token)) {

                    GcmController::sendNotification($fcm_token, $message);



                    $date_heure = date('Y-m-d H:i:s');

                    $driver_id = $request->get('driver_id');

                    $to_id = $request->get('id_user');

                    $insertdata = DB::insert("insert into tj_notification(titre,message,statut,creer,modifier,to_id,from_id,type)

            values('" . $title . "','" . $msg . "','yes','$date_heure','$date_heure','" . $to_id . "','" . $driver_id . "','rideonride')");



                    $sql_notification = Notification::where('to_id', $to_id)->first();

                    $data = $sql_notification->toArray();

                    $row['titre'] = $data['titre'];

                    $row['message'] = $data['message'];

                    $row['statut_notification'] = $data['statut'];

                    $row['to_id'] = $data['to_id'];

                    $row['driver_id'] = $data['from_id'];

                    $row['type'] = $data['type'];
                }



                $row['id'] = (string) $row['id'];

                $row['tax'] = json_decode($row['tax'], true);



                $response['success'] = 'success';

                $response['error'] = null;

                $response['message'] = 'status update successfully';

                $response['data'] = $row;
            } else {

                $response['success'] = 'failed';

                $response['error'] = 'failed to update';
            }
        } else {
            $response['success'] = 'failed';

            $response['error'] = 'You are already on an active ride. Please complete your current ride before starting a new one.';
        }



        return response()->json($response);
    }
}

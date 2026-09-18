<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\API\v1\GcmController;

use App\Http\Controllers\Controller;

use App\Models\Driver;

use App\Models\Notification;

use App\Models\ParcelOrder;

use DB;

use Illuminate\Http\Request;



class ParcelCompleteController extends Controller

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

    public function completeRequest(Request $request)

    {

        $months = array("January" => 'Jan', "February" => 'Feb', "March" => 'Mar', "April" => 'Apr', "May" => 'May', "June" => 'Jun', "July" => 'Jul', "August" => 'Aug', "September" => 'Sep', "October" => 'Oct', "November" => 'Nov', "December" => 'Dec');



        $id_parcel = $request->get('id_parcel');

        $id_user = $request->get('id_user');

        $driver_name = $request->get('driver_name');

        $from_id = $request->get('from_id');

        $date_heure = date('Y-m-d 00:00:00');

        if (!empty($id_parcel) && !empty($driver_name) && !empty($id_user) && !empty($from_id)) {

            $sql = ParcelOrder::where('id', $id_parcel)->first();
            if (!$sql) {
                $response['success'] = 'Failed';
                $response['error'] = 'Parcel order not found';
                return response()->json($response);
            }

            $updatedata = ParcelOrder::where('id', $id_parcel)->update(['status' => 'completed']);
            $driverId = $sql->id_conducteur ?: $from_id;
            $updateDriver = Driver::where('id', $driverId)->update(['driver_on_ride' => 'no']);

            if (!empty($updatedata)) {

                $sql = ParcelOrder::where('id', $id_parcel)->first();
                $row = $sql->toArray();

                $row['id'] = (string)$row['id'];



                $row['created_at'] = date("d", strtotime($row['created_at'])) . " " . $months[date("F", strtotime($row['created_at']))] . ", " . date("Y", strtotime($row['created_at']));



                if (!empty($row['parcel_image'])) {

                    if (file_exists(public_path('images/parcel_order' . '/' . $row['parcel_image']))) {

                        $image_user = asset('images/parcel_order') . '/' . $row['parcel_image'];

                    } else {

                        $image_user = asset('assets/images/placeholder_image.jpg');

                    }

                    $row['parcel_image'] = $image_user;

                } else {
                    $row['parcel_image'] = asset('assets/images/placeholder_image.jpg');
                }



                $driver = Driver::where('id', $driverId)->first();

                $row['prenomConducteur'] = $driver ? $driver->prenom : '';

                $row['nomConducteur'] = $driver ? $driver->nom : '';

                $row['photo_path'] = $driver ? $driver->photo_path : '';

                if (!empty($row['photo_path'])) {

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



                if (!empty($sql_nb_avis)) {

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



                $title = str_replace("'", "\'", "Parcel delivered");

                $msg = str_replace("'", "\'", $driver_name . " is delivered your parcel.");



                $tab[] = array();

                $tab = explode("\\", $msg);

                $msg_ = "";

                for ($i = 0; $i < count($tab); $i++) {

                    $msg_ = $msg_ . "" . $tab[$i];

                }



                $message = array("body" => $msg_, "title" => $title, "sound" => "mySound", "tag" => "ridecompleted");

                $fcm_token = DB::table('tj_user_app')->where('fcm_id','!=','')->where('id','=',$id_user)->value('fcm_id');

                if (!empty($fcm_token)) {

                    GcmController::sendNotification($fcm_token, $message);

                    

                    $date_heure = date('Y-m-d H:i:s');

                    $to_id = $request->get('id_user');

                    $insertdata = DB::insert("insert into tj_notification(titre,message,statut,creer,modifier,to_id,from_id,type)

            values('" . $title . "','" . $msg . "','yes','" . $date_heure . "','" . $date_heure . "','" . $to_id . "','" . $from_id . "','ridecompleted')");

                    $sql_notification = Notification::orderby('modifier', 'desc')->first();

                    $data = $sql_notification->toArray();

                    $row['titre'] = $data['titre'];

                    $row['message'] = $data['message'];

                    $row['statut_notification'] = $data['statut'];

                    $row['to_id'] = $data['to_id'];

                    $row['from_id'] = $data['from_id'];

                    $row['type'] = $data['type'];

                }



                $row['tax'] = json_decode($row['tax'], true);



                $response['success'] = 'success';

                $response['error'] = null;

                $response['message'] = 'status successfully updated';

                $response['data'] = $row;



            } else {

                $response['success'] = 'Failed';

                $response['error'] = 'Failed to update data';

            }

        } else {

            $response['success'] = 'Failed';

            $response['error'] = 'some fields are missing';

        }

        return response()->json($response);

    }





}


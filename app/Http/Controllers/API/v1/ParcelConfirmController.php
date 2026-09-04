<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\API\v1\GcmController;

use App\Http\Controllers\Controller;

use App\Models\Notification;

use App\Models\ParcelOrder;

use App\Models\Driver;
use App\Models\Settings;
use App\Models\Commission;
use DB;

use Illuminate\Http\Request;



class ParcelConfirmController extends Controller

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



        $id_parcel = $request->get('id_parcel');

        $id_user = $request->get('id_user');

        $driver_name = $request->get('driver_name');

        $driver_id = $request->get('driver_id');

        $setting = Settings::first();
        $subscriptionModel = $setting->subscription_model;
        $commissionData = Commission::first();
        $commissionModel = $commissionData->statut;
        $driverData = Driver::where('id', $driver_id)->first();

        if (! empty($id_parcel) && ! empty($id_user) && ! empty($driver_name) && ! empty($driver_id)) {

            if (($subscriptionModel == 'true' || $commissionModel == 'yes') && $driverData->subscriptionTotalOrders != '' && $driverData->subscriptionTotalOrders != null && intval($driverData->subscriptionTotalOrders) != '-1') {
                if (intval($driverData->subscriptionTotalOrders) <= 0) {
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

            $updatedata = ParcelOrder::where('id', $id_parcel)->update(['status' => 'confirmed', 'id_conducteur' => $driver_id]);



            if (! empty($updatedata)) {

                $otp = random_int(100000, 999999);

                $parcelOrder = ParcelOrder::where('id', $id_parcel)->first();
                $driverId = $parcelOrder->id_conducteur;

                if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
                    $driverData = Driver::where('id', $driverId)->first();
                    if ($driverData->subscriptionTotalOrders != null && $driverData->subscriptionTotalOrders != '' && $driverData->subscriptionTotalOrders != '-1') {
                        $remaningRides = intval($driverData->subscriptionTotalOrders) - 1;
                        Driver::where('id', $driverId)->update(['subscriptionTotalOrders' => $remaningRides]);
                    }
                }
                if ($parcelOrder) {

                    $parcelOrder->otp = $otp;
                }

                $parcelOrder->save();

                $sql = ParcelOrder::where('id', $id_parcel)->first();

                $row = $sql->toArray();

                $row['id'] = (string) $row['id'];

                if ($row['parcel_image'] != '') {

                    $parcelImage = json_decode($row['parcel_image'], true);

                    $image_user = [];

                    foreach ($parcelImage as $value) {

                        if (file_exists(public_path('images/parcel_order/' . '/' . $value))) {

                            $image = asset('images/parcel_order/') . '/' . $value;
                        }

                        array_push($image_user, $image);
                    }

                    if (! empty($image_user)) {

                        $row['parcel_image'] = $image_user;
                    } else {

                        $image_user = asset('assets/images/placeholder_image.jpg');
                    }
                }

                $title = str_replace("'", "\'", "Confirmation of your parcel order");

                $msg = str_replace("'", "\'", $driver_name . " is Confirmed your parcel order.");



                $tab[] = array();

                $tab = explode("\\", $msg);

                $msg_ = "";

                for ($i = 0; $i < count($tab); $i++) {

                    $msg_ = $msg_ . "" . $tab[$i];
                }



                $message = array("body" => $msg_, "title" => $title, "sound" => 'mySound', "tag" => "parcelconfirmed");

                $fcm_token = DB::table('tj_user_app')->where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');

                if (! empty($fcm_token)) {

                    GcmController::sendNotification($fcm_token, $message);



                    $date_heure = date('Y-m-d H:i:s');

                    $to_id = $request->get('id_user');



                    $insertdata = DB::insert("insert into tj_notification(titre,message,statut,creer,modifier,to_id,from_id,type)

            values('" . $title . "','" . $msg . "','yes','" . $date_heure . "','" . $date_heure . "','" . $to_id . "','" . $driver_id . "','rideconfirmed')");

                    $sql_notification = Notification::orderby('id', 'desc')->first();

                    $data = $sql_notification->toArray();

                    $row['titre'] = $data['titre'];

                    $row['message'] = $data['message'];

                    $row['statut_notification'] = $data['statut'];

                    $row['to_id'] = $data['to_id'];

                    $row['from_id'] = $data['from_id'];

                    $row['type'] = $data['type'];



                    $driver_data = Driver::where('id', $driver_id)->first();

                    $driver = $driver_data->toArray();

                    $row['driver_id'] = (string) $driver['id'];

                    $row['driver_name'] = (string) $driver_name;

                    $row['driver_phone'] = (string) $driver['phone'];
                }



                // Terminate ringing alarm/notifications on other drivers' devices
                try {
                    $otherParcelTokens = DB::table('tj_conducteur')
                        ->where('id', '!=', $driver_id)
                        ->where('statut', 'yes')
                        ->where('online', '!=', 'no')
                        ->whereNotNull('fcm_id')
                        ->where('fcm_id', '!=', '')
                        ->where(function ($query) {
                            $query->where('tj_conducteur.parcel_delivery', '=', 'yes');
                        })
                        ->pluck('fcm_id');

                    if ($otherParcelTokens->isNotEmpty()) {
                        $cancelPayload = [
                            'title' => 'Parcel Taken',
                            'body' => 'This parcel request has been accepted by another driver.',
                            'tag' => 'booking_taken',
                            'statut' => 'taken',
                            'id_parcel' => (string) $id_parcel,
                            'booking_id' => (string) $id_parcel,
                        ];
                        foreach ($otherParcelTokens as $pToken) {
                            GcmController::sendNotification($pToken, $cancelPayload);
                        }
                    }
                } catch (\Throwable $cancelEx) {
                    \Log::warning('Failed sending parcel cancellation push: ' . $cancelEx->getMessage());
                }

                $response['success'] = 'success';

                $response['error'] = null;

                $response['message'] = 'status successfully updated';

                $response['data'] = $row;

                return response()->json($response);
            } else {

                $response['success'] = 'Failed';

                $response['error'] = 'Failed to update data';
                return response()->json($response);
            }
        } else {

            $response['success'] = 'Failed';

            $response['error'] = 'some field are missing';
            return response()->json($response);
        }
    }
}

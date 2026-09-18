<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\ParcelOrder;
use Illuminate\Http\Request;
use DB;

class ParcelCanceledController extends Controller
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

    public function cancelRequest(Request $request)
    {
        $id_requete = $request->get('parcel_id');
        $reason = $request->get('reason');

        $sql = ParcelOrder::where('id', $id_requete)->first();
        if (!$sql) {
            $response['success'] = 'failed';
            $response['error'] = 'Parcel order not found';
            return response()->json($response);
        }

        $oldStatus = $sql->status;
        $driverId = $sql->id_conducteur;

        $updatedata = DB::update('update parcel_orders set status = ?,reason = ? where id = ?', ['canceled', $reason, $id_requete]);

        if (!empty($updatedata)) {
            if (($oldStatus == 'confirmed' || $oldStatus == 'onride') && !empty($driverId)) {
                DB::table('tj_conducteur')->where('id', $driverId)->update(['driver_on_ride' => 'no']);

                $setting = DB::table('tj_settings')->first();
                $subscriptionModel = $setting->subscription_model ?? 'false';
                $commissionData = DB::table('tj_commission')->first();
                $commissionModel = $commissionData->statut ?? 'no';

                if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
                    $driverData = DB::table('tj_conducteur')->where('id', $driverId)->first();
                    if ($driverData && $driverData->subscriptionTotalOrders !== null && $driverData->subscriptionTotalOrders !== '' && intval($driverData->subscriptionTotalOrders) != -1) {
                        $remaningRides = intval($driverData->subscriptionTotalOrders) + 1;
                        DB::table('tj_conducteur')->where('id', $driverId)->update(['subscriptionTotalOrders' => $remaningRides]);
                    }
                }
            }

            $sql = ParcelOrder::where('id', $id_requete)->first();
            $row = $sql->toArray();
            $row['id'] = (string) $row['id'];
            $row['tax'] = is_string($row['tax']) ? json_decode($row['tax'], true) : $row['tax'];

            $image_user = [];
            if (!empty($row['parcel_image'])) {
                $parcelImage = is_string($row['parcel_image']) ? json_decode($row['parcel_image'], true) : $row['parcel_image'];
                if (is_array($parcelImage)) {
                    foreach ($parcelImage as $value) {
                        if (file_exists(public_path('images/parcel_order/' . '/' . $value))) {
                            $image_user[] = asset('images/parcel_order/') . '/' . $value;
                        }
                    }
                }
            }
            $row['parcel_image'] = !empty($image_user) ? $image_user : [asset('assets/images/placeholder_image.jpg')];

            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'successfully';
            $response['data'] = $row;
        } else {
            $response['success'] = 'failed';
            $response['error'] = 'failed to update';
        }
        return response()->json($response);
    }





}

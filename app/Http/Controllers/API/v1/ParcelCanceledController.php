<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\v1\GcmController;
use App\Models\ParcelOrder;
use App\Models\Driver;
use App\Models\Settings;
use App\Models\Commission;
use Illuminate\Http\Request;
use DB;

class ParcelCanceledController extends Controller
{
    public function __construct()
    {
        $this->limit = 20;
    }

    /**
     * Cancel parcel request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelRequest(Request $request)
    {
        $id_requete = $request->get('parcel_id') ?: $request->get('id_parcel');
        $reason = $request->get('reason') ?: 'Cancelled by customer';

        if (empty($id_requete)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Parcel ID is required',
            ]);
        }

        $sql = ParcelOrder::where('id', $id_requete)->first();
        if (!$sql) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Parcel order not found',
            ]);
        }

        $oldStatus = $sql->status;
        $driverId = (int)($sql->id_conducteur ?? 0);
        $userId = (int)($sql->id_user_app ?? 0);

        // 1. Update parcel order status to canceled
        $sql->status = 'canceled';
        $sql->reason = $reason;
        $sql->save();

        // 2. Refund wallet if payment was already made via wallet
        $isWallet = false;
        if (!empty($sql->id_payment_method)) {
            $payMethod = DB::table('tj_payment_method')->where('id', $sql->id_payment_method)->first();
            if ($payMethod && (stripos($payMethod->libelle, 'wallet') !== false || stripos($payMethod->slug ?? '', 'wallet') !== false || $sql->id_payment_method == 5)) {
                $isWallet = true;
            }
        }

        if (($sql->payment_status == 'yes' || $sql->payment_status == 'success') && $isWallet && (float)$sql->amount > 0 && !empty($userId)) {
            $alreadyRefunded = DB::table('tj_transaction')
                ->where('id_user_app', $userId)
                ->where('note', 'like', "%Parcel #{$id_requete}%cancelled%")
                ->exists();

            if (!$alreadyRefunded) {
                DB::table('tj_user_app')->where('id', $userId)->increment('amount', (float)$sql->amount);

                $date_heure = date('Y-m-d H:i:s');
                DB::table('tj_transaction')->insert([
                    'amount' => $sql->amount,
                    'note' => "Parcel #{$id_requete} cancelled - Refund to wallet",
                    'id_user_app' => $userId,
                    'creer' => $date_heure,
                    'modifier' => $date_heure,
                    'deduct_type' => 'credit',
                    'payment_type' => 'wallet',
                ]);

                $sql->payment_status = 'refunded';
                $sql->save();
            }
        }

        // 3. Free up assigned driver & restore subscription
        if (!empty($driverId) && $driverId > 0) {
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

            // Send FCM cancellation notification to assigned driver
            $fcm_token = DB::table('tj_conducteur')->where('id', $driverId)->where('fcm_id', '!=', '')->value('fcm_id');
            if (!empty($fcm_token)) {
                $cancelMsg = [
                    'body' => "Parcel booking #{$id_requete} has been cancelled by customer.",
                    'title' => 'Parcel Cancelled',
                    'sound' => 'mySound',
                    'tag' => 'booking_cancelled',
                    'statut' => 'cancelled',
                    'booking_id' => (string)$id_requete,
                    'id_ride' => (string)$id_requete,
                    'id' => (string)$id_requete,
                    'order_type' => 'parcel',
                ];
                GcmController::sendNotification($fcm_token, $cancelMsg);
            }
        } else {
            // Pre-assignment cancellation: broadcast cancellation to nearby drivers who may have received the ping
            try {
                $lat = $sql->lat_source;
                $lng = $sql->lng_source;
                if (!empty($lat) && !empty($lng)) {
                    $settings = DB::table('tj_settings')->select('driver_radios')->first();
                    $radius = $settings ? ($settings->driver_radios ?? 50) : 50;

                    $nearbyDrivers = DB::table('tj_conducteur')
                        ->select('id', 'fcm_id', DB::raw("6371 * acos(cos(radians(" . (float)$lat . "))
                            * cos(radians(latitude))
                            * cos(radians(longitude) - radians(" . (float)$lng . "))
                            + sin(radians(" . (float)$lat . "))
                            * sin(radians(latitude))) AS distance"))
                        ->having('distance', '<=', $radius)
                        ->where('statut', 'yes')
                        ->where('fcm_id', '!=', '')
                        ->get();

                    $broadcastCancel = [
                        'body' => "Parcel booking #{$id_requete} has been cancelled.",
                        'title' => 'Parcel Cancelled',
                        'sound' => 'mySound',
                        'tag' => 'booking_cancelled',
                        'statut' => 'cancelled',
                        'booking_id' => (string)$id_requete,
                        'id_ride' => (string)$id_requete,
                        'id' => (string)$id_requete,
                        'order_type' => 'parcel',
                    ];

                    foreach ($nearbyDrivers as $nDriver) {
                        if (!empty($nDriver->fcm_id)) {
                            GcmController::sendNotification($nDriver->fcm_id, $broadcastCancel);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("ParcelCanceledController broadcast cancel failed: " . $e->getMessage());
            }
        }

        // 4. Format response data
        $sql_refreshed = ParcelOrder::where('id', $id_requete)->first();
        $row = $sql_refreshed ? $sql_refreshed->toArray() : $sql->toArray();
        $row['id'] = (string) $row['id'];
        $row['tax'] = is_string($row['tax'] ?? null) ? json_decode($row['tax'], true) : ($row['tax'] ?? []);

        $image_user = [];
        if (!empty($row['parcel_image'])) {
            $parcelImage = is_string($row['parcel_image']) ? json_decode($row['parcel_image'], true) : $row['parcel_image'];
            if (is_array($parcelImage)) {
                foreach ($parcelImage as $value) {
                    if (!empty($value) && file_exists(public_path('images/parcel_order/' . $value))) {
                        $image_user[] = asset('images/parcel_order/' . $value);
                    }
                }
            }
        }
        $row['parcel_image'] = !empty($image_user) ? $image_user : [asset('assets/images/placeholder_image.jpg')];

        return response()->json([
            'success' => 'success',
            'error' => null,
            'message' => 'Parcel cancelled successfully',
            'data' => $row,
        ]);
    }
}

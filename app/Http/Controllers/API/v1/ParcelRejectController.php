<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\GcmController;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ParcelOrder;
use App\Models\Commission;
use App\Models\Driver;
use App\Models\Settings;
use DB;
use Illuminate\Http\Request;

class ParcelRejectController extends Controller
{
    public function __construct()
    {
        $this->limit = 20;
    }

    /**
     * Reject or cancel parcel request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectRequest(Request $request)
    {
        $id_parcel = $request->get('id_parcel') ?: $request->get('parcel_id');
        $id_user = $request->get('id_user') ?: $request->get('user_id');
        $driver_name = $request->get('name') ?: $request->get('driver_name');
        $from_id = $request->get('from_id');
        $reason = $request->get('reason') ?: 'Rejected';
        $user_cat = strtolower(trim((string)$request->get('user_cat', 'user_app')));

        if (empty($id_parcel)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Parcel ID is required',
            ]);
        }

        $sql = ParcelOrder::where('id', $id_parcel)->first();
        if (!$sql) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Parcel order not found',
            ]);
        }

        $rideStatus = $sql->status;
        $drivertoReject = (int)($sql->id_conducteur ?? 0);
        $userId = (int)($sql->id_user_app ?? 0);

        $settings = Settings::first();
        $subscriptionModel = $settings ? $settings->subscription_model : null;
        $commissionData = Commission::first();
        $commissionModel = $commissionData ? $commissionData->statut : null;

        $rejectDriverIds = $sql->rejected_driver_id;
        $rejDriverIds = [];
        if ($rejectDriverIds != null) {
            $rejDriverIds = json_decode($rejectDriverIds, true) ?? [];
        }

        $title = '';
        $msg = '';
        $fcm_token = null;

        if ($user_cat === 'driver') {
            // DRIVER REJECTING THE PARCEL
            $driver_name = $driver_name ?: 'Driver';
            $title = "Rejection of your Parcel";
            $msg = $driver_name . " has rejected your parcel.";

            // Free up driver
            $fromDriverId = !empty($from_id) ? (int)$from_id : $drivertoReject;
            if ($fromDriverId > 0) {
                Driver::where('id', $fromDriverId)->update(['driver_on_ride' => 'no']);
                if (!in_array($fromDriverId, $rejDriverIds)) {
                    $rejDriverIds[] = $fromDriverId;
                }
            }

            $updateRejDriverArr = json_encode($rejDriverIds);
            DB::update('update parcel_orders set status = ?, rejected_driver_id = ?, id_conducteur = 0 where id = ?', ['driver_rejected', $updateRejDriverArr, $id_parcel]);

            // Notify user app
            $targetUserId = !empty($id_user) && (int)$id_user > 0 ? (int)$id_user : $userId;
            $fcm_token = DB::table('tj_user_app')->where('fcm_id', '!=', '')->where('id', '=', $targetUserId)->value('fcm_id');
            if (!empty($fcm_token)) {
                $message = [
                    'body' => $msg,
                    'reasons' => $reason,
                    'title' => $title,
                    'sound' => 'mySound',
                    'tag' => 'riderejected',
                    'statut' => 'driver_rejected',
                    'order_type' => 'parcel',
                    'id_parcel' => (string)$id_parcel,
                ];
                GcmController::sendNotification($fcm_token, $message);
            }

            // Restore subscription for driver if confirmed
            if ($rideStatus == 'confirmed' || $rideStatus == 'onride') {
                if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
                    $rejectedDriverData = Driver::where('id', $fromDriverId)->first();
                    if ($rejectedDriverData && $rejectedDriverData->subscriptionTotalOrders != '' && $rejectedDriverData->subscriptionTotalOrders != null && intval($rejectedDriverData->subscriptionTotalOrders) != -1) {
                        $subscriptionTotalOrders = intval($rejectedDriverData->subscriptionTotalOrders) + 1;
                        Driver::where('id', $fromDriverId)->update(['subscriptionTotalOrders' => $subscriptionTotalOrders]);
                    }
                }
            }
        } else {
            // USER CANCELLING THE PARCEL (user_app or customer)
            $title = "Cancellation of parcel delivery";
            $msg = "Customer has cancelled the parcel delivery";

            DB::update('update parcel_orders set status = ?, reason = ? where id = ?', ['canceled', $reason, $id_parcel]);

            // Refund wallet if paid via wallet
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
                    ->where('note', 'like', "%Parcel #{$id_parcel}%cancelled%")
                    ->exists();

                if (!$alreadyRefunded) {
                    DB::table('tj_user_app')->where('id', $userId)->increment('amount', (float)$sql->amount);

                    $date_heure = date('Y-m-d H:i:s');
                    DB::table('tj_transaction')->insert([
                        'amount' => $sql->amount,
                        'note' => "Parcel #{$id_parcel} cancelled - Refund to wallet",
                        'id_user_app' => $userId,
                        'creer' => $date_heure,
                        'modifier' => $date_heure,
                        'deduct_type' => 'credit',
                        'payment_type' => 'wallet',
                    ]);

                    DB::table('parcel_orders')->where('id', $id_parcel)->update(['payment_status' => 'refunded']);
                }
            }

            // Free up assigned driver & notify them
            $targetDriverId = $drivertoReject > 0 ? $drivertoReject : (int)($id_user ?? 0);
            if ($targetDriverId > 0) {
                Driver::where('id', $targetDriverId)->update(['driver_on_ride' => 'no']);

                if ($rideStatus == 'confirmed' || $rideStatus == 'onride') {
                    if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
                        $rejectedDriverData = Driver::where('id', $targetDriverId)->first();
                        if ($rejectedDriverData && $rejectedDriverData->subscriptionTotalOrders != '' && $rejectedDriverData->subscriptionTotalOrders != null && intval($rejectedDriverData->subscriptionTotalOrders) != -1) {
                            $subscriptionTotalOrders = intval($rejectedDriverData->subscriptionTotalOrders) + 1;
                            Driver::where('id', $targetDriverId)->update(['subscriptionTotalOrders' => $subscriptionTotalOrders]);
                        }
                    }
                }

                $fcm_token = DB::table('tj_conducteur')->where('fcm_id', '!=', '')->where('id', '=', $targetDriverId)->value('fcm_id');
                if (!empty($fcm_token)) {
                    $message = [
                        'body' => $msg,
                        'reasons' => $reason,
                        'title' => $title,
                        'sound' => 'mySound',
                        'tag' => 'booking_cancelled',
                        'statut' => 'cancelled',
                        'booking_id' => (string)$id_parcel,
                        'id_ride' => (string)$id_parcel,
                        'id' => (string)$id_parcel,
                        'order_type' => 'parcel',
                    ];
                    GcmController::sendNotification($fcm_token, $message);
                }
            }
        }

        // Notification record in DB if notification sent
        if (!empty($fcm_token)) {
            $date_heure = date('Y-m-d H:i:s');
            $notifTo = ($user_cat === 'driver') ? $userId : ($targetDriverId ?? 0);
            $notifFrom = ($user_cat === 'driver') ? ($from_id ?? 0) : $userId;

            DB::table('tj_notification')->insert([
                'titre' => $title,
                'message' => $msg,
                'statut' => 'yes',
                'creer' => $date_heure,
                'modifier' => $date_heure,
                'to_id' => $notifTo ?? 0,
                'from_id' => $notifFrom ?? 0,
                'type' => 'riderejected',
            ]);
        }

        // Format updated row
        $sql_update = ParcelOrder::where('id', '=', $id_parcel)->first();
        $row = $sql_update ? $sql_update->toArray() : $sql->toArray();
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
            'message' => 'status successfully updated',
            'data' => $row,
        ]);
    }
}

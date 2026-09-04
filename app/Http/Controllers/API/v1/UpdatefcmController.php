<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use App\Services\PhoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpdatefcmController extends Controller
{
    public function __construct()
    {
        $this->limit = 20;
    }

    /**
     * Update FCM Token for Customer or Driver.
     * Ensures strict role separation: driver tokens are ONLY stored in tj_conducteur,
     * and customer tokens are ONLY stored in tj_user_app.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatefcm(Request $request)
    {
        $user_id   = $request->get('user_id') ?: $request->get('id_user') ?: $request->get('id_driver') ?: $request->get('driver_id');
        $fcm_id    = trim((string)$request->get('fcm_id'));
        $device_id = $request->get('device_id') ?? '';
        $user_cat  = strtolower(trim((string)$request->get('user_cat')));
        $phone     = $request->get('phone') ?: $request->get('mobile');
        $date_heure = date('Y-m-d H:i:s');

        if (empty($fcm_id)) {
            return response()->json([
                'success' => 'Failed',
                'error'   => 'fcm_id is required',
            ]);
        }

        // Determine if target is driver or customer
        $isDriver = ($user_cat === 'driver');

        if ($isDriver) {
            // ── DRIVER FLOW ──────────────────────────────────────────────────────────
            $driver = null;
            if (!empty($user_id) && (int)$user_id > 0) {
                $driver = Driver::where('id', (int)$user_id)->first();
            }
            if (!$driver && !empty($phone)) {
                $driver = PhoneService::findDriver($phone);
            }

            if (!$driver) {
                return response()->json([
                    'success' => 'Failed',
                    'error'   => 'Driver account not found',
                ]);
            }

            $driverId = (int)$driver->id;

            // Clear this device's token from any OTHER drivers to prevent multi-account collisions
            DB::table('tj_conducteur')
                ->where('fcm_id', $fcm_id)
                ->where('id', '!=', $driverId)
                ->update(['fcm_id' => '', 'modifier' => $date_heure]);

            // Update driver token strictly in tj_conducteur
            DB::table('tj_conducteur')
                ->where('id', $driverId)
                ->update([
                    'fcm_id'    => $fcm_id,
                    'device_id' => $device_id,
                    'modifier'  => $date_heure,
                ]);

            $driver = Driver::where('id', $driverId)->first();
            $row = $driver->toArray();
            $row['id'] = (string)$row['id'];
            $row['photo'] = '';
            $row['photo_licence'] = '';
            $row['photo_nic'] = '';
            $row['photo_car_service_book'] = '';
            $row['photo_road_worthy'] = '';

            $row['photo_path'] = (!empty($row['photo_path']) && file_exists(public_path('assets/images/driver/' . $row['photo_path'])))
                ? asset('assets/images/driver/' . $row['photo_path'])
                : asset('assets/images/placeholder_image.jpg');

            $row['photo_nic_path'] = (!empty($row['photo_nic_path']) && file_exists(public_path('assets/images/driver/' . $row['photo_nic_path'])))
                ? asset('assets/images/driver/' . $row['photo_nic_path'])
                : asset('assets/images/placeholder_image.jpg');

            $row['photo_licence_path'] = (!empty($row['photo_licence_path']) && file_exists(public_path('assets/images/driver/' . $row['photo_licence_path'])))
                ? asset('assets/images/driver/' . $row['photo_licence_path'])
                : asset('assets/images/placeholder_image.jpg');

            $row['photo_car_service_book_path'] = (!empty($row['photo_car_service_book_path']) && file_exists(public_path('assets/images/driver/' . $row['photo_car_service_book_path'])))
                ? asset('assets/images/driver/' . $row['photo_car_service_book_path'])
                : asset('assets/images/placeholder_image.jpg');

            $row['photo_road_worthy_path'] = (!empty($row['photo_road_worthy_path']) && file_exists(public_path('assets/images/driver/' . $row['photo_road_worthy_path'])))
                ? asset('assets/images/driver/' . $row['photo_road_worthy_path'])
                : asset('assets/images/placeholder_image.jpg');

            return response()->json([
                'success' => 'success',
                'error'   => null,
                'message' => 'successful',
                'data'    => $row,
            ]);

        } else {
            // ── CUSTOMER FLOW ────────────────────────────────────────────────────────
            $customer = null;
            if (!empty($user_id) && (int)$user_id > 0) {
                $customer = UserApp::where('id', (int)$user_id)->first();
            }
            if (!$customer && !empty($phone)) {
                $customer = PhoneService::findCustomer($phone);
            }

            if (!$customer) {
                return response()->json([
                    'success' => 'Failed',
                    'error'   => 'Customer account not found',
                ]);
            }

            $customerId = (int)$customer->id;

            // Clear this device's token from any OTHER customer records to prevent multi-account collisions
            DB::table('tj_user_app')
                ->where('fcm_id', $fcm_id)
                ->where('id', '!=', $customerId)
                ->update(['fcm_id' => '', 'modifier' => $date_heure]);

            // Update customer token strictly in tj_user_app
            DB::table('tj_user_app')
                ->where('id', $customerId)
                ->update([
                    'fcm_id'    => $fcm_id,
                    'device_id' => $device_id,
                    'modifier'  => $date_heure,
                ]);

            $customer = UserApp::where('id', $customerId)->first();
            $row = $customer->toArray();
            $row['id'] = (string)$row['id'];
            $row['photo'] = '';
            $row['photo_nic'] = '';

            $row['photo_path'] = (!empty($row['photo_path']) && file_exists(public_path('assets/images/users/' . $row['photo_path'])))
                ? asset('assets/images/users/' . $row['photo_path'])
                : asset('assets/images/placeholder_image.jpg');

            $row['photo_nic_path'] = (!empty($row['photo_nic_path']) && file_exists(public_path('assets/images/users/' . $row['photo_nic_path'])))
                ? asset('assets/images/users/' . $row['photo_nic_path'])
                : asset('assets/images/placeholder_image.jpg');

            return response()->json([
                'success' => 'success',
                'error'   => null,
                'message' => 'successful',
                'data'    => $row,
            ]);
        }
    }
}

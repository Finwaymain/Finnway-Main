<?php

namespace App\Http\Controllers\API\v1;

use App\Models\ParcelOrder;
use App\Http\Controllers\Controller;
use App\Models\Requests;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Helpers\Helper;

class ParcelRegisterController extends Controller
{

    public function __construct()
    {
        $this->limit = 20;
    }

    public function register(Request $request)
    {
        $months = array("January" => 'Jan', "February" => 'Fev', "March" => 'Mar', "April" => 'Avr', "May" => 'Mai', "June" => 'Jun', "July" => 'Jul', "August" => 'Aou', "September" => 'Sep', "October" => 'Oct', "November" => 'Nov', "December" => 'Dec');

        $user_id = $request->get('user_id');
        $lat1 = $request->get('lat1');
        $lng1 = $request->get('lng1');
        $lat2 = $request->get('lat2');
        $lng2 = $request->get('lng2');
        $sourceCity = $request->get('source_city');
        $destinationCity = $request->get('destination_city');
        $distance = $request->get('distance');
        $distance_unit = $request->get('distance_unit');
        $duration = $request->get('duration');
        $id_payment = $request->get('id_payment') ?? '';
        $source_adrs = $request->get('source_adrs');
        $destination_adrs = $request->get('destination_adrs');
        $sender_name = $request->get('sender_name');
        $receiver_name = $request->get('receiver_name');
        $sender_phone = $request->get('sender_phone');
        $receiver_phone = $request->get('receiver_phone');
        $note = $request->get('note');
        $parcel_weight = $request->get('parcel_weight');
        $parcel_dimension = $request->get('parcel_dimension');
        $image = $request->file('parcel_image');
        $parcel_type = $request->get('parcel_type');
        $filenames = [];
        $filename = '';
        if ($request->hasfile('parcel_image')) {
            for ($i = 0; $i < sizeof($image); $i++) {
                try {
                    $url = $this->uploadToImageKit($image[$i], '/parcel_order');
                    array_push($filenames, $url);
                } catch (\Exception $e) {
                    \Log::warning('ImageKit upload for parcel failed, falling back to local: ' . $e->getMessage());
                    $extenstion = $image[$i]->getClientOriginalExtension();
                    $time = time() . '_' . $i . '.' . $extenstion;
                    $filename = 'parcel_' . $time;
                    $compressedImage = Helper::compressFile($image[$i]->getPathName(), public_path('images/parcel_order') . '/' . $filename, 8);
                    array_push($filenames, $filename);
                }
            }
            $filename = json_encode($filenames);
        }
        $parcel_date = $request->get('parcel_date');
        $parcel_time = $request->get('parcel_time');
        $receive_date = $request->get('receive_date');
        $receive_time = $request->get('receive_time');

        $amount = $request->get('amount');
        $created_at = date('Y-m-d H:i:s');
        $otp = rand(1000, 9999);
        ParcelOrder::create([
            'otp' => $otp,
            'id_user_app' => $user_id,
            'source' => $source_adrs,
            'destination' => $destination_adrs,
            'lat_source' => $lat1,
            'lng_source' => $lng1,
            'lat_destination' => $lat2,
            'lng_destination' => $lng2,
            'source_city' => $sourceCity,
            'destination_city' => $destinationCity,
            'sender_name' => $sender_name,
            'sender_phone' => $sender_phone,
            'receiver_name' => $receiver_name,
            'receiver_phone' => $receiver_phone,
            'parcel_weight' => $parcel_weight,
            'parcel_dimension' => $parcel_dimension,
            'parcel_type' => $parcel_type,
            'parcel_image' => $filename,
            'note' => $note,
            'parcel_date' => $parcel_date,
            'parcel_time' => $parcel_time,
            'receive_date' => $receive_date,
            'receive_time' => $receive_time,
            'status' => 'new',
            'payment_status' => 'no',
            'id_payment_method' => $id_payment,
            'distance' => $distance,
            'distance_unit' => $distance_unit,
            'amount' => $amount,
            'duration' => $duration
        ]);

        $id = DB::getPdo()->lastInsertId();
        if ($id > 0) {
            $get_user = ParcelOrder::leftJoin('tj_payment_method', 'tj_payment_method.id', '=', 'parcel_orders.id_payment_method')
                ->leftJoin('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
                ->select('parcel_orders.*', 'tj_payment_method.libelle as payment_method', 'parcel_category.title as parcel_type')
                ->where('parcel_orders.id', $id)->first();

            if (!$get_user) {
                // Fallback: fetch without joins
                $get_user = ParcelOrder::where('id', $id)->first();
            }

            $row = $get_user->toArray();
            $row['id'] = (string) $row['id'];
            $row['created_at'] = date("d", strtotime($row['created_at'])) . " " . $months[date("F", strtotime($row['created_at']))] . ". " . date("Y", strtotime($row['created_at']));
            $row['updated_at'] = date("d", strtotime($row['updated_at'])) . " " . $months[date("F", strtotime($row['updated_at']))] . ". " . date("Y", strtotime($row['updated_at']));

            if ($row['parcel_image'] != '') {
                $parcelImage = json_decode($row['parcel_image'], true);
                $image_user = [];
                foreach ($parcelImage as $value) {
                    if (file_exists(public_path('images/parcel_order/' . '/' . $value))) {
                        $image = asset('images/parcel_order/') . '/' . $value;
                    }
                    array_push($image_user, $image);
                }
                if (!empty($image_user)) {
                    $row['parcel_image'] = $image_user;
                } else {
                    $image_user = asset('assets/images/placeholder_image.jpg');
                }
            }

            // Find nearby parcel drivers and notify them
            $settings = DB::table('tj_settings')->select('driver_radios')->first();
            $radius = $settings->driver_radios ?? 10;

            $drivers = DB::table("tj_conducteur")
                ->leftJoin('tj_conducteur_categories', 'tj_conducteur.id', '=', 'tj_conducteur_categories.driver_id')
                ->leftJoin('tj_categorie_user', 'tj_conducteur_categories.subcategory_id', '=', 'tj_categorie_user.id')
                ->select(
                    "tj_conducteur.id",
                    "tj_conducteur.fcm_id",
                    DB::raw("6371 * acos(cos(radians(" . floatval($lat1) . "))
                            * cos(radians(tj_conducteur.latitude))
                            * cos(radians(tj_conducteur.longitude) - radians(" . floatval($lng1) . "))
                            + sin(radians(" . floatval($lat1) . "))
                            * sin(radians(tj_conducteur.latitude))) AS distance")
                )
                ->having('distance', '<=', $radius)
                ->where('tj_conducteur.statut', 'yes')
                ->where('tj_conducteur.online', '!=', 'no')
                ->where('tj_conducteur.is_verified', '=', '1')
                ->where(function ($query) {
                    $query->where('tj_categorie_user.libelle', '=', 'Parcel Delivery')
                        ->orWhere('tj_conducteur.parcel_delivery', '=', 'yes');
                })
                ->distinct()
                ->get();

            if ($drivers->isNotEmpty()) {
                $fcmMsg = array(
                    "body" => "You have just received a request for a parcel delivery",
                    "title" => "New Parcel Request",
                    "sound" => "ride_request_sound",
                    "tag" => "parcelnew",
                    "statut" => "new"
                );

                $notificationPayload = array_merge($row, $fcmMsg);
                if (isset($notificationPayload['parcel_image']) && is_array($notificationPayload['parcel_image'])) {
                    $notificationPayload['parcel_image'] = json_encode($notificationPayload['parcel_image']);
                }

                foreach ($drivers as $driver) {
                    if (!empty($driver->fcm_id)) {
                        \App\Http\Controllers\API\v1\GcmController::sendNotification($driver->fcm_id, $notificationPayload);
                    }
                }
            }

            $output[] = $row;
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Successfully created';
            $response['data'] = $output;
        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'Failed';
        }

        return response()->json($response);
    }

    private function uploadToImageKit($file, $folder = '/parcel_order')
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'parcel_' . time() . '_' . uniqid() . '.' . $extension;

        $privateKey = config('imagekit.private_key');

        if (empty($privateKey)) {
            throw new \Exception('IMAGEKIT_PRIVATE_KEY is not configured on the server.');
        }

        $url = "https://upload.imagekit.io/api/v1/files/upload";

        $postData = [
            'file' => new \CURLFile($file->getRealPath(), $file->getMimeType(), $filename),
            'fileName' => $filename,
            'folder' => $folder,
            'useUniqueFileName' => 'true'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ":");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            \Log::error('ImageKit cURL error: ' . $curlError);
            throw new \Exception('Upload connection failed: ' . $curlError);
        }

        if ($statusCode === 200) {
            $json = json_decode($response, true);
            if (isset($json['url'])) {
                return $json['url'];
            }
            throw new \Exception('ImageKit returned 200 but no URL in response.');
        }

        \Log::error("ImageKit upload failed [{$statusCode}]: {$response}");
        throw new \Exception("ImageKit error ({$statusCode})");
    }
}

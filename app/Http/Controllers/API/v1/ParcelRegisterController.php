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

        $user_id = $request->get('user_id') ?: $request->get('id_user_app');
        $lat1 = $request->get('lat1') ?: $request->get('lat_source');
        $lng1 = $request->get('lng1') ?: $request->get('lng_source');
        $lat2 = $request->get('lat2') ?: $request->get('lat_destination');
        $lng2 = $request->get('lng2') ?: $request->get('lng_destination');
        $sourceCity = $request->get('source_city');
        $destinationCity = $request->get('destination_city');
        $distance = $request->get('distance');
        $distance_unit = $request->get('distance_unit');
        $duration = $request->get('duration');
        $id_payment = $request->get('id_payment') ?? '';
        $source_adrs = $request->get('source_adrs') ?: $request->get('source');
        $destination_adrs = $request->get('destination_adrs') ?: $request->get('destination');
        $sender_name = $request->get('sender_name');
        $receiver_name = $request->get('receiver_name');
        $sender_phone = $request->get('sender_phone');
        $receiver_phone = $request->get('receiver_phone');
        $note = $request->get('note');
        $parcel_weight = $request->get('parcel_weight');
        $parcel_dimension = $request->get('parcel_dimension');
        $parcel_type = $request->get('parcel_type') ?: ($request->get('parcel_category') ?: '1');
        $image = $request->file('parcel_image');
        $filenames = [];
        $filename = json_encode([]);
        $amount = number_format((float)($request->get('amount') ?? 0), 2, '.', '');
        $distance = number_format((float)($request->get('distance') ?? 0), 2, '.', '');

        if ($request->hasfile('parcel_image')) {
            $images = is_array($image) ? $image : [$image];
            $destDir = public_path('images/parcel_order');
            if (!File::isDirectory($destDir)) {
                @File::makeDirectory($destDir, 0777, true, true);
            }
            foreach ($images as $idx => $imgFile) {
                if (!$imgFile) continue;
                try {
                    $url = $this->uploadToImageKit($imgFile, '/parcel_order');
                    array_push($filenames, $url);
                } catch (\Throwable $e) {
                    \Log::warning('ImageKit upload for parcel failed, falling back to local: ' . $e->getMessage());
                    try {
                        $extension = method_exists($imgFile, 'getClientOriginalExtension') ? ($imgFile->getClientOriginalExtension() ?: 'jpg') : 'jpg';
                        $imgFileName = 'parcel_' . time() . '_' . $idx . '.' . $extension;
                        $destPath = $destDir . '/' . $imgFileName;
                        Helper::compressFile($imgFile->getPathName(), $destPath, 8);
                        array_push($filenames, $imgFileName);
                    } catch (\Throwable $localEx) {
                        \Log::error('Local image save for parcel failed: ' . $localEx->getMessage());
                    }
                }
            }
            $filename = json_encode($filenames);
        }
        $parcel_date = $request->get('parcel_date');
        $parcel_time = $request->get('parcel_time');
        $receive_date = $request->get('receive_date');
        $receive_time = $request->get('receive_time');

        $created_at = date('Y-m-d H:i:s');
        $otp = (string) random_int(100000, 999999);
        $id = 0;
        try {
            $newOrder = ParcelOrder::create([
                'otp' => $otp,
                'id_user_app' => $user_id,
                'source' => $source_adrs ?: '',
                'destination' => $destination_adrs ?: '',
                'lat_source' => $lat1 ?: '',
                'lng_source' => $lng1 ?: '',
                'lat_destination' => $lat2 ?: '',
                'lng_destination' => $lng2 ?: '',
                'source_city' => $sourceCity ?? '',
                'destination_city' => $destinationCity ?? '',
                'sender_name' => $sender_name ?? '',
                'sender_phone' => $sender_phone ?? '',
                'receiver_name' => $receiver_name ?? '',
                'receiver_phone' => $receiver_phone ?? '',
                'parcel_weight' => $parcel_weight ?? '',
                'parcel_dimension' => $parcel_dimension ?? '',
                'parcel_type' => $parcel_type,
                'parcel_image' => (!empty($filename) && $filename !== '""') ? $filename : json_encode([]),
                'note' => $note ?? '',
                'parcel_date' => $parcel_date ?: date('Y-m-d'),
                'parcel_time' => $parcel_time ?: date('H:i:s'),
                'receive_date' => $receive_date ?: date('Y-m-d'),
                'receive_time' => $receive_time ?: date('H:i:s'),
                'status' => 'new',
                'reason' => '',
                'payment_status' => 'no',
                'id_payment_method' => $id_payment ?: '0',
                'distance' => $distance ?? '0',
                'distance_unit' => $distance_unit ?? 'KM',
                'amount' => $amount ?? '0',
                'duration' => $duration ?? ''
            ]);
            $id = $newOrder ? $newOrder->id : DB::getPdo()->lastInsertId();
        } catch (\Throwable $dbEx) {
            \Log::error('ParcelOrder::create failed: ' . $dbEx->getMessage());
            return response()->json([
                'success' => 'Failed',
                'error' => 'Database error: ' . $dbEx->getMessage(),
                'message' => 'Failed to create parcel order'
            ], 200);
        }
        if ($id > 0) {
            try {
                // BUG FIX: alias 'parcel_type' conflicted with parcel_orders.parcel_type column.
                // Renamed to 'parcel_category_title' to avoid ambiguity in toArray().
                $get_user = ParcelOrder::leftJoin('tj_payment_method', 'tj_payment_method.id', '=', 'parcel_orders.id_payment_method')
                    ->leftJoin('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
                    ->select(
                        'parcel_orders.*',
                        DB::raw("COALESCE(tj_payment_method.libelle, 'Pending') as payment_method"),
                        DB::raw("parcel_category.title as parcel_category_title")
                    )
                    ->where('parcel_orders.id', $id)->first();
            } catch (\Exception $e) {
                \Log::warning('ParcelRegister join query failed, falling back: ' . $e->getMessage());
                $get_user = null;
            }

            if (!$get_user) {
                // Fallback: fetch without joins
                $get_user = ParcelOrder::where('id', $id)->first();
            }

            $row = $get_user ? $get_user->toArray() : [];
            $row['id'] = (string)$id;
            $row['user_name'] = $sender_name;
            $row['id_user_app'] = (string)$user_id;

            // BUG FIX: null-safe date formatting — PHP 8 throws TypeError on strtotime(null)
            $createdAt = !empty($row['created_at']) ? $row['created_at'] : date('Y-m-d H:i:s');
            $updatedAt = !empty($row['updated_at']) ? $row['updated_at'] : date('Y-m-d H:i:s');
            $row['created_at'] = date("d", strtotime($createdAt)) . " " . ($months[date("F", strtotime($createdAt))] ?? date("M", strtotime($createdAt))) . ". " . date("Y", strtotime($createdAt));
            $row['updated_at'] = date("d", strtotime($updatedAt)) . " " . ($months[date("F", strtotime($updatedAt))] ?? date("M", strtotime($updatedAt))) . ". " . date("Y", strtotime($updatedAt));

            // BUG FIX: $image was used before assignment (if file_exists was false, push was undefined)
            if (!empty($row['parcel_image']) && $row['parcel_image'] !== '[]') {
                $parcelImage = json_decode($row['parcel_image'], true) ?? [];
                $image_user = [];
                foreach ($parcelImage as $value) {
                    $resolvedImage = null;
                    if (file_exists(public_path('images/parcel_order/' . $value))) {
                        $resolvedImage = asset('images/parcel_order/') . '/' . $value;
                    } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                        // ImageKit / CDN URL — use as-is
                        $resolvedImage = $value;
                    }
                    if ($resolvedImage) {
                        $image_user[] = $resolvedImage;
                    }
                }
                if (!empty($image_user)) {
                    $row['parcel_image'] = $image_user;
                }
            }

            // Find nearby parcel drivers and notify them
            try {
                $settings = DB::table('tj_settings')->select('driver_radios')->first();
                $radius = floatval($settings->driver_radios ?? 15);
                if ($radius <= 0) $radius = 15;

                // ── Bike-first logic ─────────────────────────────────────────
                // If parcel is light (≤ 5 kg) AND small (≤ 3 ft dimension),
                // prefer bike/motorcycle drivers. Fall back to all types if none found nearby.
                $parcelWeight    = floatval($parcel_weight ?? 999);
                $parcelDimension = floatval($parcel_dimension ?? 999);
                $isBikeEligible  = ($parcelWeight <= 5 && $parcelDimension <= 3);

                // Shared haversine distance expression
                $distanceExpr = DB::raw("6371 * acos(
                    LEAST(1, GREATEST(-1,
                        cos(radians(" . floatval($lat1) . "))
                        * cos(radians(COALESCE(tj_conducteur.latitude, 0)))
                        * cos(radians(COALESCE(tj_conducteur.longitude, 0)) - radians(" . floatval($lng1) . "))
                        + sin(radians(" . floatval($lat1) . "))
                        * sin(radians(COALESCE(tj_conducteur.latitude, 0)))
                    )
                ) AS distance");

                // Base driver query builder (shared filters)
                $baseQuery = function () use ($distanceExpr, $radius) {
                    return DB::table("tj_conducteur")
                        ->leftJoin('tj_conducteur_categories', 'tj_conducteur.id', '=', 'tj_conducteur_categories.driver_id')
                        ->leftJoin('tj_categorie_user', 'tj_conducteur_categories.subcategory_id', '=', 'tj_categorie_user.id')
                        ->select("tj_conducteur.id", "tj_conducteur.fcm_id", "tj_conducteur.vehicle_type", $distanceExpr)
                        ->whereNotNull('tj_conducteur.latitude')
                        ->whereNotNull('tj_conducteur.longitude')
                        ->where('tj_conducteur.latitude', '!=', '')
                        ->where('tj_conducteur.longitude', '!=', '')
                        ->having('distance', '<=', $radius)
                        ->where('tj_conducteur.statut', 'yes')
                        ->where('tj_conducteur.online', '!=', 'no')
                        ->where(function ($q) {
                            $q->whereIn('tj_conducteur.is_verified', ['1', 1, 'yes'])
                              ->orWhere('tj_conducteur.statut', 'yes');
                        })
                        ->distinct();
                };

                $drivers = collect();

                if ($isBikeEligible) {
                    // First: try bike/motorcycle drivers only
                    $drivers = $baseQuery()
                        ->where(function ($q) {
                            $q->whereIn(DB::raw('LOWER(tj_conducteur.vehicle_type)'), ['bike', 'motorcycle', 'two wheeler', 'scooter', 'moped'])
                              ->orWhere('tj_conducteur.parcel_delivery', '=', 'yes');
                        })
                        ->get();

                    if ($drivers->isEmpty()) {
                        // Fall back to all parcel-capable drivers
                        \Log::info('ParcelRegister: No bike drivers found near by, falling back to all drivers for parcel #' . $id);
                    }
                }

                if ($drivers->isEmpty()) {
                    // All parcel-capable drivers (or non-bike-eligible parcel)
                    $drivers = $baseQuery()
                        ->where(function ($query) {
                            $query->whereIn('tj_categorie_user.libelle', [
                                'Parcel Delivery', 'Food Delivery', 'Pickup & Drop (Personal runner)',
                                'Logistics Partner', 'Bike Rider', 'Pickup'
                            ])
                            ->orWhereIn('tj_conducteur_categories.category_id', [12880, 12888])
                            ->orWhere('tj_conducteur.parcel_delivery', '=', 'yes');
                        })
                        ->get();
                }

                if ($drivers->isNotEmpty()) {
                    $notifTag = $isBikeEligible ? 'parcelbike' : 'parcelnew';
                    $fcmMsg = [
                        "body"             => "New Parcel: {$source_adrs} to {$destination_adrs} (₹{$amount})",
                        "title"            => "New Parcel Request",
                        "sound"            => "ride_request_sound",
                        "tag"              => $notifTag,
                        "statut"           => "new",
                        "order_type"       => "parcel",
                        "depart_name"      => $source_adrs,
                        "destination_name" => $destination_adrs,
                        "montant"          => (string)$amount,
                        "preferred_vehicle" => $isBikeEligible ? 'bike' : 'any',
                    ];

                    $notificationPayload = array_merge($row, $fcmMsg);
                    if (isset($notificationPayload['parcel_image']) && is_array($notificationPayload['parcel_image'])) {
                        $notificationPayload['parcel_image'] = json_encode($notificationPayload['parcel_image']);
                    }

                    foreach ($drivers as $driver) {
                        if (!empty($driver->fcm_id)) {
                            try {
                                \App\Http\Controllers\API\v1\GcmController::sendNotification($driver->fcm_id, $notificationPayload);
                            } catch (\Exception $notifEx) {
                                \Log::warning('Parcel FCM notification failed for driver ' . $driver->id . ': ' . $notifEx->getMessage());
                            }
                        }
                    }
                }
            } catch (\Exception $driverEx) {
                // Driver search/notify failed — log it but still return success to the user
                \Log::error('ParcelRegister driver search failed: ' . $driverEx->getMessage());
            }

            // ── Wallet: deduct balance at booking time if wallet payment ─────
            // The payment screen calls this endpoint; for wallet method, deduct immediately
            // so the user doesn't need a separate API call.
            try {
                $walletPaymentMethod = DB::table('tj_payment_method')
                    ->where(DB::raw('LOWER(libelle)'), 'like', '%wallet%')
                    ->first();

                $isWalletPayment = $walletPaymentMethod && ((string)$id_payment === (string)$walletPaymentMethod->id);

                if ($isWalletPayment && !empty($user_id)) {
                    $userRow = DB::table('tj_user_app')->select('amount')->where('id', $user_id)->first();
                    $currentBalance = floatval($userRow->amount ?? 0);
                    $deductAmount   = floatval($amount ?? 0);

                    if ($currentBalance >= $deductAmount && $deductAmount > 0) {
                        $newBalance = $currentBalance - $deductAmount;
                        DB::table('tj_user_app')->where('id', $user_id)->update(['amount' => $newBalance]);
                        DB::table('parcel_orders')->where('id', $id)->update(['payment_status' => 'yes']);

                        // Log transaction
                        DB::table('tj_transaction')->insert([
                            'amount'          => $deductAmount,
                            'deduction_type'  => 0,
                            'ride_id'         => $id,
                            'payment_method'  => 'wallet',
                            'payment_status'  => 'success',
                            'id_user_app'     => $user_id,
                            'creer'           => date('Y-m-d H:i:s'),
                            'modifier'        => date('Y-m-d H:i:s'),
                        ]);

                        $row['payment_status'] = 'yes';
                    } else if ($deductAmount > 0) {
                        // Insufficient balance — booking was created but wallet not deducted
                        \Log::warning("ParcelRegister: Wallet balance {$currentBalance} insufficient for {$deductAmount}, parcel #{$id}");
                        $row['wallet_error'] = 'Insufficient wallet balance';
                    }
                }
            } catch (\Exception $walletEx) {
                \Log::error('ParcelRegister wallet deduction failed: ' . $walletEx->getMessage());
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

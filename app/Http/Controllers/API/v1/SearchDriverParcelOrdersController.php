<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\ParcelOrder;
use Illuminate\Http\Request;
use DB;

class SearchDriverParcelOrdersController extends Controller
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


    public function getData(Request $request)
    {
        $months = array("January" => 'Jan', "February" => 'Feb', "March" => 'Mar', "April" => 'Apr', "May" => 'May', "June" => 'Jun', "July" => 'Jul', "August" => 'Aug', "September" => 'Sep', "October" => 'Oct', "November" => 'Nov', "December" => 'Dec');

        $source_lat = $request->get('source_lat') ?: $request->get('lat');
        $source_lng = $request->get('source_lng') ?: $request->get('lng');
        $destination_lat = $request->get('destination_lat');
        $destination_lng = $request->get('destination_lng');
        $date = $request->get('date');
        $source_city = $request->get('source_city');
        $driver_id = $request->get('driver_id') ?: ($request->get('id_driver') ?: $request->get('id_conducteur'));

        $driver = null;
        if (!empty($driver_id) && (int)$driver_id > 0) {
            $driver = Driver::where('id', (int)$driver_id)->first();
        }

        // Fallback to driver's stored GPS coordinates if request params are missing or 0
        if ((empty($source_lat) || empty($source_lng) || (float)$source_lat == 0) && !empty($driver)) {
            $source_lat = $driver->latitude ?: null;
            $source_lng = $driver->longitude ?: null;
        }

        $hasGps = (!empty($source_lat) && !empty($source_lng) && (float)$source_lat != 0);

        // Helper to build base query for unassigned new parcels
        $buildBaseQuery = function () use ($source_lat, $source_lng, $hasGps) {
            $query = ParcelOrder::leftJoin('tj_payment_method', 'tj_payment_method.id', '=', 'parcel_orders.id_payment_method')
                ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'parcel_orders.id_user_app')
                ->leftJoin('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
                ->select(
                    'parcel_orders.*',
                    DB::raw("COALESCE(tj_payment_method.libelle, 'Pending') as payment_method"),
                    DB::raw("parcel_category.title as parcel_category_title"),
                    'tj_user_app.nom',
                    'tj_user_app.prenom',
                    'tj_user_app.phone as user_phone',
                    'tj_user_app.photo_path as user_photo'
                )
                ->where('parcel_orders.status', '=', 'new');

            if ($hasGps) {
                $haversine = "(6371 * acos(LEAST(1, GREATEST(-1, cos(radians(" . floatval($source_lat) . ")) * cos(radians(parcel_orders.lat_source)) * cos(radians(parcel_orders.lng_source) - radians(" . floatval($source_lng) . ")) + sin(radians(" . floatval($source_lat) . ")) * sin(radians(parcel_orders.lat_source))))))";
                $query->selectRaw("{$haversine} AS distance");
            }

            return $query;
        };

        $ParcelOrder = collect();

        if ($hasGps) {
            $driverRadius = floatval(DB::table('tj_settings')->value('driver_radios') ?? 15);
            if ($driverRadius <= 0) $driverRadius = 15;

            $haversine = "(6371 * acos(LEAST(1, GREATEST(-1, cos(radians(" . floatval($source_lat) . ")) * cos(radians(parcel_orders.lat_source)) * cos(radians(parcel_orders.lng_source) - radians(" . floatval($source_lng) . ")) + sin(radians(" . floatval($source_lat) . ")) * sin(radians(parcel_orders.lat_source))))))";

            // Tier 1: Search within default radius (15km)
            $tier1 = $buildBaseQuery();
            if (!empty($date)) $tier1->where('parcel_date', '=', $date);
            $ParcelOrder = $tier1->whereRaw("{$haversine} <= ?", [$driverRadius])
                ->orderByRaw("{$haversine} ASC")
                ->get();

            // Tier 2: If none within 15km, search within 50km
            if ($ParcelOrder->isEmpty()) {
                $tier2 = $buildBaseQuery();
                if (!empty($date)) $tier2->where('parcel_date', '=', $date);
                $ParcelOrder = $tier2->whereRaw("{$haversine} <= 50")
                    ->orderByRaw("{$haversine} ASC")
                    ->get();
            }

            // Tier 3: If none within 50km, search within 150km
            if ($ParcelOrder->isEmpty()) {
                $tier3 = $buildBaseQuery();
                if (!empty($date)) $tier3->where('parcel_date', '=', $date);
                $ParcelOrder = $tier3->whereRaw("{$haversine} <= 150")
                    ->orderByRaw("{$haversine} ASC")
                    ->get();
            }
        }

        // Tier 4: Fallback to city match if provided and still empty
        if ($ParcelOrder->isEmpty() && !empty($source_city)) {
            $tier4 = $buildBaseQuery();
            if (!empty($date)) $tier4->where('parcel_date', '=', $date);
            $ParcelOrder = $tier4->where(function ($q) use ($source_city) {
                $q->where('source_city', 'like', '%' . $source_city . '%')
                  ->orWhere('destination_city', 'like', '%' . $source_city . '%');
            })->get();
        }

        // Tier 5: Final fallback - return any recent unassigned new parcel order
        if ($ParcelOrder->isEmpty()) {
            $tier5 = $buildBaseQuery();
            if (!empty($date)) $tier5->where('parcel_date', '=', $date);
            $ParcelOrder = $tier5->orderBy('parcel_orders.id', 'DESC')
                ->limit(20)
                ->get();
        }

        $output = [];
        if (!$ParcelOrder->isEmpty()) {
            foreach ($ParcelOrder as $row) {
                $row->id = (string)$row->id;
                $row->user_name = trim((string)$row->prenom . " " . (string)$row->nom);
                $row->distance = isset($row->distance) ? (string)round(floatval($row->distance), 2) : "0";
                $row->amount = isset($row->amount) ? (string)$row->amount : "0";
                $row->otp = isset($row->otp) ? (string)$row->otp : "";

                if (!empty($row->parcel_image)) {
                    $parcelImage = is_string($row->parcel_image) ? json_decode($row->parcel_image, true) : $row->parcel_image;
                    $image_user = [];
                    if (is_array($parcelImage)) {
                        foreach ($parcelImage as $value) {
                            if (empty($value)) continue;
                            if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                                $image_user[] = $value;
                            } elseif (file_exists(public_path('images/parcel_order/' . $value))) {
                                $image_user[] = asset('images/parcel_order/' . $value);
                            } else {
                                $image_user[] = asset('assets/images/placeholder_image.jpg');
                            }
                        }
                    }
                    $row->parcel_image = !empty($image_user) ? $image_user : [asset('assets/images/placeholder_image.jpg')];
                } else {
                    $row->parcel_image = [asset('assets/images/placeholder_image.jpg')];
                }

                if (!empty($row->user_photo)) {
                    if (str_starts_with($row->user_photo, 'http://') || str_starts_with($row->user_photo, 'https://')) {
                        // already absolute url
                    } elseif (file_exists(public_path('assets/images/users/' . $row->user_photo))) {
                        $row->user_photo = asset('assets/images/users/' . $row->user_photo);
                    } else {
                        $row->user_photo = asset('assets/images/placeholder_image.jpg');
                    }
                } else {
                    $row->user_photo = asset('assets/images/placeholder_image.jpg');
                }

                $createdAt = !empty($row->created_at) ? $row->created_at : date('Y-m-d H:i:s');
                $row->created_at = date("d", strtotime($createdAt)) . " " . ($months[date("F", strtotime($createdAt))] ?? date("M", strtotime($createdAt))) . ". " . date("Y", strtotime($createdAt));

                $output[] = $row;
            }

            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Parcel Order fetch successfully';
            $response['data'] = $output;
        } else {
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'No new parcel requests available right now';
            $response['data'] = [];
        }

        return response()->json($response);
    }
}

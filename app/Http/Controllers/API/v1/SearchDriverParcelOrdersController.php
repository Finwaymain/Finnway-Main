<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\ParcelOrder;
use App\Models\Zone;
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
        $driver_id = $request->get('driver_id') ?: $request->get('id_driver');
        $driver = Driver::where('id', $driver_id)
            ->where(function($q) {
                $q->whereIn('is_verified', ['1', 1, 'yes'])
                  ->orWhere('statut', 'yes');
            })
            ->first();

        if(empty($driver)){

            $response['success'] = 'Failed';
            $response['error'] = 'Your document is not verified. Contact to admin for approval';
            $response['message'] = null;

        }else{

            //start - chek driver zone before assign the list of parcel
            if(!empty($driver->zone_id)){
                $driver_zone_ids = explode(',',$driver->zone_id);
                if(count($driver_zone_ids) > 0){
                    if(Zone::whereIn('id',$driver_zone_ids)->where('status','yes')->exists()){
                        $in_zone = "no";
                        $zones= Zone::whereIn('id',$driver_zone_ids)->where('status','yes')->get();
                        foreach($zones as $zone){
                            if(!empty($zone->area)){
                                try {
                                    $zone_area_json = $zone->area->toJson();
                                    $zone_area_array = json_decode($zone_area_json, true);
                                    $vertices_x = $vertices_y = [];
                                    if(isset($zone_area_array['coordinates'])){
                                        foreach($zone_area_array['coordinates'] as $key => $data){
                                            foreach($data as $k=>$v){
                                                $vertices_x[] = (float) $v[0]; // Longitude = X
                                                $vertices_y[] = (float) $v[1]; // Latitude = Y
                                            }
                                        }
                                        $points_polygon = count($vertices_x)-1; 
                                        if($points_polygon >= 3 && $this->is_in_polygon($points_polygon, $vertices_x, $vertices_y, (float)$source_lng, (float)$source_lat)){
                                            $in_zone = "yes";
                                            break; 
                                        }
                                    }
                                } catch (\Throwable $e) {}
                            }
                        }
                        if($in_zone == "no"){
                            \Log::info("SearchDriverParcelOrders: Driver $driver_id outside zone polygon, falling back to GPS radius.");
                        }
                    }
				}
            }
            //end - chek driver zone before assign the list of parcel
            
            $output = [];

            if ((!empty($date)) || (!empty($source_lat) && !empty($source_lng)) || (!empty($destination_lat) && !empty($destination_lng))) {

                $ParcelOrder = ParcelOrder::leftJoin('tj_payment_method', 'tj_payment_method.id', '=', 'parcel_orders.id_payment_method')
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
                    );

                if (!empty($date)) {
                    $ParcelOrder = $ParcelOrder->where('parcel_date', '=', $date);
                }

                if (!empty($source_lat) && !empty($source_lng)) {
                    $driverRadius = floatval(DB::table('tj_settings')->value('driver_radios') ?? 15);
                    if ($driverRadius <= 0) {
                        $driverRadius = 15;
                    }
                    $haversine = "(6371 * acos(cos(radians(" . floatval($source_lat) . ")) * cos(radians(parcel_orders.lat_source)) * cos(radians(parcel_orders.lng_source) - radians(" . floatval($source_lng) . ")) + sin(radians(" . floatval($source_lat) . ")) * sin(radians(parcel_orders.lat_source))))";
                    $ParcelOrder = $ParcelOrder->selectRaw("{$haversine} AS distance")
                        ->whereRaw("{$haversine} <= ?", [$driverRadius]);
                }

                if (!empty($destination_lat) && !empty($destination_lng)) {
                    $destHaversine = "(6371 * acos(cos(radians(" . floatval($destination_lat) . ")) * cos(radians(parcel_orders.lat_destination)) * cos(radians(parcel_orders.lng_destination) - radians(" . floatval($destination_lng) . ")) + sin(radians(" . floatval($destination_lat) . ")) * sin(radians(parcel_orders.lat_destination))))";
                    $ParcelOrder = $ParcelOrder->whereRaw("{$destHaversine} <= 25");
                }

                $ParcelOrder = $ParcelOrder->where('parcel_orders.status', '=', 'new')->get();

                if ($ParcelOrder->isEmpty() && !empty($source_city)) {
                    $ParcelOrder = ParcelOrder::leftJoin('tj_payment_method', 'tj_payment_method.id', '=', 'parcel_orders.id_payment_method')
                        ->leftJoin('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
                        ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'parcel_orders.id_user_app')
                        ->select(
                            'parcel_orders.*',
                            DB::raw("COALESCE(tj_payment_method.libelle, 'Pending') as payment_method"),
                            DB::raw("parcel_category.title as parcel_category_title"),
                            'tj_user_app.nom',
                            'tj_user_app.prenom',
                            'tj_user_app.phone as user_phone',
                            'tj_user_app.photo_path as user_photo'
                        )
                        ->where('source_city', 'like', '%' . $source_city . '%')
                        ->where('parcel_orders.status', '=', 'new')
                        ->get();
                }

                if (!$ParcelOrder->isEmpty()) {

                    foreach ($ParcelOrder as $row) {

                        $row->id= (string)$row->id;
                        
                        $row->user_name = (string)$row->prenom . " " . $row->nom;
                        
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

                        $row->created_at = date("d", strtotime($row->created_at)) . " " . $months[date("F", strtotime($row->created_at))] . ". " . date("Y", strtotime($row->created_at));

                        $output[] = $row;
                            
                    }

                    if (!empty($output)) {
                        $response['success'] = 'success';
                        $response['error'] = null;
                        $response['message'] = 'Parcel Order fetch successfully';
                        $response['data'] = $output;
                    } else {
                        $response['success'] = 'Failed';
                        $response['error'] = 'No Data Found';
                    }

                } else {
                    $response['success'] = 'Failed';
                    $response['error'] = 'No Data Found';
                    $response['message'] = null;

                }
            } else {
                $response['success'] = 'Failed';
                $response['error'] = 'some field required';

            }
        }

        return response()->json($response);
    }

    public function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y){
		$i = $j = $c = $point = 0;
		for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
			$point = $i;
			if( $point == $points_polygon )
				$point = 0;
			if ( (($vertices_y[$point]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) && ($longitude_x < ($vertices_x[$j] - $vertices_x[$point]) * ($latitude_y - $vertices_y[$point]) / ($vertices_y[$j] - $vertices_y[$point]) + $vertices_x[$point]) ) )
				$c = !$c;
		}
		return $c;
	}
}

<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use DB;
use App\Helpers\Helper;

class UserRoadWorthyDocController extends Controller
{

   public function __construct()
   {
      $this->limit=20;
   }
  /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */

  public function updateRoadWorthy(Request $request)
  {

        $id_user = $request->get('id_driver');
        $image= $request->file('image');
        $date_heure = date('Y-m-d H:i:s');

        if(empty($image))
        {
            $response['success']= 'Failed';
            $response['error']= 'Image Not Found';
        } else
        {

            $image = '';
            $file = $request->file('image');
            try {
                $filename = Helper::uploadToImageKit($file, '/driver/roadworthy');
            } catch (\Exception $e) {
                \Log::warning('ImageKit upload for driver road-worthy doc failed, falling back to local: ' . $e->getMessage());
                $extenstion = $file->getClientOriginalExtension();
                $filename = 'Driver_Road_worthy'.time().'.'.$extenstion;
                Helper::compressFile($file->getPathName(), public_path('assets/images/driver').'/'.$filename, 8);
            }


        $updatedata = DB::update('update tj_conducteur set photo_road_worthy = ?,photo_road_worthy_path = ?,modifier = ? where id = ?',[$image,$filename,$date_heure,$id_user]);

        if($updatedata > 0){

            $get_user = Driver::where('id',$id_user)->first();
            $row = $get_user->toArray();

            $row['id']=(string)$row['id'];
            $row['photo'] = '';
            $row['photo_nic'] = '';
            $row['photo_car_service_book'] = '';
            $row['photo_licence'] = '';
            $row['photo_road_worthy'] = '';
            $row['photo_path'] = Helper::resolveImagePath($row['photo_path']);
            $row['photo_nic_path'] = Helper::resolveImagePath($row['photo_nic_path']);
            $row['photo_car_service_book_path'] = Helper::resolveImagePath($row['photo_car_service_book_path']);
            $row['photo_licence_path'] = Helper::resolveImagePath($row['photo_licence_path']);
            $row['photo_road_worthy_path'] = Helper::resolveImagePath($row['photo_road_worthy_path']);
            $response['success']= 'Success';
            $response['error']= null;
            $response['message']= 'Document Updated';
            $response['data'] = $row;
        } else {
            $response['success']= 'Failed';
            $response['error']= 'Document Not Updated';
        }
      }

    return response()->json($response);
  }

}

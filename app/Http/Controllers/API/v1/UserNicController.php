<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use Illuminate\Http\Request;
use DB;
use App\Helpers\Helper;

class UserNicController extends Controller
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

  public function updateUserNic(Request $request)
  {


        $user_cat =  $request->get('user_cat');
        $image_name = $request->get('image_name');
        $image= $request->file('image');
        $date_heure = date('Y-m-d H:i:s');
        $path = 'assets/images/users';
        if($user_cat == "user_app"){

        	$id_user = $request->get('id_user');
        	if(empty($image)){
            	$response['success']= 'Failed';
            	$response['error']= 'Image Not Found';
        	} else{
		        $file = $request->file('image');
		        try {
		            $filename = Helper::uploadToImageKit($file, '/users/nic');
		        } catch (\Exception $e) {
		            \Log::warning('ImageKit upload for user NIC failed, falling back to local: ' . $e->getMessage());
		            $extenstion = $file->getClientOriginalExtension();
		            $filename = 'User_nic'.time().'.'.$extenstion;
		            Helper::compressFile($file->getPathName(), public_path('assets/images/users').'/'.$filename, 8);
		        }
		        $image = '';
		        $updatedata = DB::update('update tj_user_app set photo_nic = ?,photo_nic_path = ?,modifier = ? where id = ?',[$image,$filename,$date_heure,$id_user]);
		        if(!empty($updatedata)){
		            $updatestatus = DB::update('update tj_user_app set statut_nic = ? where id = ?',['uploaded',$id_user]);

		            $get_user = UserApp::
		            select('*')
		            ->where('id',$id_user)
		            ->orderby('modifier', 'desc')
		            ->get();
		            foreach($get_user as $row){
		                $image_user = $row->photo_path;
		                $image_path = Helper::resolveImagePath($row->photo_nic_path, 'assets/images/users');
		                $row->photo_path = $image_path;
		                $row->photo_nic_path = $image_path;
		                if($image_user != ''){
		                    $row->photo_nic_path = Helper::resolveImagePath($image_user, 'assets/images/users');
		                }

		                $response['success']= 'success';
		                $response['error']= null;
		                $response['message']= 'Photo Nic successfully updated';
		                $response['data'] = $row;
		            }
		    } else{
		        $response['success']= 'Failed';
		        $response['error']= 'Image Not Updated';
		    }
			}

        }elseif($user_cat == "driver"){

            $id_user = $request->get('id_user');
            if(empty($image)){
                $response['success']= 'Failed';
                $response['error']= 'Image Not Found';
            }else{

	            $image ='';
	            $file = $request->file('image');
	            try {
	                $filename = Helper::uploadToImageKit($file, '/driver/nic');
	            } catch (\Exception $e) {
	                \Log::warning('ImageKit upload for driver NIC failed, falling back to local: ' . $e->getMessage());
	                $extenstion = $file->getClientOriginalExtension();
	                $filename = 'driver_nic'.time().'.'.$extenstion;
	                Helper::compressFile($file->getPathName(), public_path('assets/images/driver').'/'.$filename, 8);
	            }

	            $updatedata = DB::update('update tj_conducteur set photo_nic = ?,photo_nic_path = ?,modifier = ? where id = ?',[$image,$filename,$date_heure,$id_user]);

	            if($updatedata > 0){

	                $get_user = Driver::
	                select('*')
	                ->where('id',$id_user)
	                ->orderby('modifier', 'desc')
	                ->get();
	                foreach($get_user as $row){
	                    $row->photo_path = Helper::resolveImagePath($row->photo_path, 'assets/images/driver');

	                        $row->photo = '';

	                        $row->photo_licence = '';
	                        $row->photo_nic = '';
	                        $row->photo_car_service_book = '';
	                        $row->photo_road_worthy = '';
	                        $row->photo_nic_path = Helper::resolveImagePath($row->photo_nic_path, 'assets/images/driver');
	                        $row->photo_licence_path = Helper::resolveImagePath($row->photo_licence_path, 'assets/images/driver');
	                        $row->photo_car_service_book_path = Helper::resolveImagePath($row->photo_car_service_book_path, 'assets/images/driver');
	                        $row->photo_road_worthy_path = Helper::resolveImagePath($row->photo_road_worthy_path, 'assets/images/driver');
	                $response['success']= 'success';
	                $response['error']= null;
	                $response['message']= 'status nic successfully updated';
	                $response['data'] = $row;
			 }
        }else{
        	$response['success']= 'Failed';
        	$response['error']= 'Image Not Updated';
        }
		}
    }else{
        $response['success']= 'Failed';
        $response['error']= 'Not Found';
    }

    return response()->json($response);
  }

}

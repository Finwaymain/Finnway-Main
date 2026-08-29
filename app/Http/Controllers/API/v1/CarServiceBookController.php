<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\VehicleServiceBook;
use Illuminate\Http\Request;
use DB;
use App\Helpers\Helper;
class CarServiceBookController extends Controller
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
  
  public function register(Request $request)
  {
         
        $id_user = $request->get('id_driver');
        $km = $request->get('km_driven');
        $image_name = $request->file('image');
        $date_heure = date('Y-m-d H:i:s');
        
       if(!empty($image_name) && $id_user && $km){
        $image = '';
        $file = $request->file('image');
        try {
            $filename = Helper::uploadToImageKit($file, '/vehicle/carservice');
        } catch (\Exception $e) {
            \Log::warning('ImageKit upload for car service book failed, falling back to local: ' . $e->getMessage());
            $extenstion = $file->getClientOriginalExtension();
            $filename = 'car_service_book_'.time().'.'.$extenstion;
            $file->move(public_path('assets/images/vehicule'), $filename);
        }

        if($image_name){
           
            $insertdata = DB::insert("insert into tj_vehicule_service_book(id_conducteur,km,photo_car_service_book,photo_car_service_book_path,file_name,creer,modifier)
            values('".$id_user."','".$km."','".$image."','".$filename."','".$filename."','".$date_heure."','".$date_heure."')");
           
            $get_user = VehicleServiceBook::where('id_conducteur',$id_user)->first();
            $row = $get_user->toArray();
            
            $row['photo_car_service_book_path'] = Helper::resolveImagePath($row['photo_car_service_book_path'], 'assets/images/vehicule');
        
            if ($insertdata > 0) {
                $response['success']= 'Success';
                $response['error']= null;
                $response['message']='Car Service Added Successfully';
                $response['data'] = $row;
            } else {
                $response['success']= 'Failed';
                $response['error']= 'Failed to Add data';
            }
           
        }
        
        else{
            $response['success']= 'Failed';
            $response['error']= 'image not uploaded';
        }

       }  else{
        $response['success']= 'Failed';
        $response['error']= 'Fill All Fields';
    }

    return response()->json($response);
  }

}

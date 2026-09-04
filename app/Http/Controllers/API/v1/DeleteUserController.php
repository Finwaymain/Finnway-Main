<?php

namespace App\Http\Controllers\API\v1;
use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Requests;
use App\Models\FavoriteRide;
use App\Models\VehicleLocation;
use App\Models\Message;
use App\Models\Note;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use DB;

class DeleteUserController extends Controller
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

  public function deleteuser(Request $request)
  {
    $id = $request->get('user_id') ?: $request->get('id') ?: $request->get('driver_id');
    $user_cat = $request->get('user_cat');
    $phone = $request->get('phone');

    if (!empty($id) || !empty($phone)) {
        $success = \App\Services\UserPurgeService::purgeUser($id, $user_cat, $phone);
        if (!empty($phone)) {
            \App\Services\UserPurgeService::purgeByPhone($phone, $user_cat);
        }

        if ($success) {
            $response['status'] = 200;
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'User Deleted Successfully';
        } else {
            $response['status'] = 404;
            $response['success'] = 'Failed';
            $response['error'] = 'User not found or failed to delete';
        }
    } else {
        $response['status'] = 400;
        $response['success'] = 'Failed';
        $response['error'] = 'Id Required';
    }
    return response()->json($response);
  }

}

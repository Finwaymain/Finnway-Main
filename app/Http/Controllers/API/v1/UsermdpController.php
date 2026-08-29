<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use Illuminate\Http\Request;
use DB;
class UsermdpController extends Controller
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


  public function UpdateUsermdp(Request $request)
  {
        $user_cat = strtolower(trim((string)$request->get('user_cat', '')));
        $raw_anc = trim((string)$request->get('anc_mdp', ''));
        $raw_new = trim((string)$request->get('new_mdp', ''));
        $anc_mdp = md5($raw_anc);
        $new_mdp = md5($raw_new);
        $date_heure = date('Y-m-d H:i:s');

        $id_user = $request->get('id_user') ?? $request->get('user_id');
        $id_driver = $request->get('id_driver') ?? $request->get('driver_id');

        $isDriver = ($user_cat === 'driver' || $user_cat === 'conducteur' || !empty($id_driver) && empty($id_user));

        if (!$isDriver) {
            $get_user = UserApp::where('id', $id_user)->first();
            if (!$get_user) {
                $response['success'] = 'Failed';
                $response['error'] = 'User Not Found';
            } else {
                $oldpass = $get_user->toArray();
                $matched = ($oldpass['mdp'] == $anc_mdp || (!empty($oldpass['m_pin']) && $oldpass['m_pin'] == $raw_anc));

                if ($matched) {
                    DB::table('tj_user_app')->where('id', $id_user)->update([
                        'mdp' => $new_mdp,
                        'm_pin' => $raw_new,
                        'modifier' => $date_heure
                    ]);

                    if (\Illuminate\Support\Facades\Schema::hasTable('common_user_base')) {
                        DB::table('common_user_base')->where('user_id', $id_user)->where('user_type', 'customer')->update([
                            'm_pin' => $raw_new,
                            'modifier' => $date_heure
                        ]);
                    }

                    $row = DB::table('tj_user_app')->where('id', $id_user)->first();
                    if (!empty($row)) {
                        $row->id = (string)$row->id;
                        $response['success'] = 'success';
                        $response['res'] = 'success';
                        $response['msg'] = 'MPIN updated successfully';
                        $response['message'] = 'MPIN updated successfully';
                        $response['error'] = null;
                        $response['data'] = $row;
                    } else {
                        $response['success'] = 'Failed';
                        $response['res'] = 'error';
                        $response['error'] = 'Failed to Update Password';
                    }
                } else {
                    $response['success'] = 'Failed';
                    $response['res'] = 'error';
                    $response['error'] = 'Incorrect Current Password / MPIN';
                }
            }
        } else {
            $driverIdToUse = $id_driver ?: $id_user;
            $get_user = Driver::where('id', $driverIdToUse)->first();
            if (!$get_user) {
                $response['success'] = 'Failed';
                $response['res'] = 'error';
                $response['error'] = 'Driver Not Found';
            } else {
                $oldpass = $get_user->toArray();
                $matched = ($oldpass['mdp'] == $anc_mdp || (!empty($oldpass['m_pin']) && $oldpass['m_pin'] == $raw_anc));

                if ($matched) {
                    DB::table('tj_conducteur')->where('id', $driverIdToUse)->update([
                        'mdp' => $new_mdp,
                        'm_pin' => $raw_new,
                        'modifier' => $date_heure
                    ]);

                    if (\Illuminate\Support\Facades\Schema::hasTable('common_user_base')) {
                        DB::table('common_user_base')->where('user_id', $driverIdToUse)->where('user_type', 'driver')->update([
                            'm_pin' => $raw_new,
                            'modifier' => $date_heure
                        ]);
                    }

                    $row = DB::table('tj_conducteur')->where('id', $driverIdToUse)->first();
                    if (!empty($row)) {
                        $row->id = (string)$row->id;
                        $response['success'] = 'success';
                        $response['res'] = 'success';
                        $response['msg'] = 'MPIN updated successfully';
                        $response['message'] = 'MPIN updated successfully';
                        $response['error'] = null;
                        $response['data'] = $row;
                    } else {
                        $response['success'] = 'Failed';
                        $response['res'] = 'error';
                        $response['error'] = 'Failed to Update Password';
                    }
                } else {
                    $response['success'] = 'Failed';
                    $response['res'] = 'error';
                    $response['error'] = 'Incorrect Current Password / MPIN';
                }
            }
        }

        return response()->json($response);
  }

}

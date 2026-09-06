<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use Illuminate\Http\Request;
use DB;

class WalletController extends Controller
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
  
  public function getData(Request $request)
  {
    $id_user = $request->get('id_user') ?? $request->get('user_id') ?? $request->get('driver_id');
    $cat_user = strtolower($request->get('user_cat') ?? $request->get('user_type') ?? '');
    if (empty($cat_user)) {
        if ($request->has('driver_id') || $request->has('id_conducteur')) {
            $cat_user = 'driver';
        } else {
            $cat_user = 'user_app';
        }
    }
    if(!empty($id_user)){
    if($cat_user == "user_app" || $cat_user == "user" || $cat_user == "customer"){
        $sql = DB::table('tj_user_app')
        ->select('id', 'ac_no', 'amount', 'earn_amount')
        ->where('id','=',$id_user)
        ->get();

        if ($sql->count() > 0) {
            $row = $sql->first();
            $earningWalletSum = 0;
            if (!empty($row->ac_no) && \Illuminate\Support\Facades\Schema::hasTable('tbl_earning')) {
                $earningWalletSum = DB::table('tbl_earning')
                    ->where('ac_no', $row->ac_no)
                    ->where(function ($q) {
                        $q->where('created_at', 'credit')
                          ->orWhere('created_at', 'LIKE', '%credit%')
                          ->orWhere('earn_wallet', '>', 0);
                    })
                    ->sum('earn_wallet');
            }
            $finalEarn = max(floatval($row->earn_amount ?? 0), floatval($earningWalletSum));
            $row->earn_amount = strval(number_format($finalEarn, 2, '.', ''));
        }
    
    }elseif($cat_user == "driver"){
        $sql = DB::table('tj_conducteur')
        ->select('id', 'ac_no', 'amount', 'earn_amount')
        ->where('id','=',$id_user)
        ->get();

        if($sql->count() > 0){
            $row = $sql->first();
            $rideEarnings = DB::table('tj_requete')
                ->where('id_conducteur', $id_user)
                ->where('statut', 'completed')
                ->sum('montant');
            $parcelEarnings = DB::table('parcel_orders')
                ->where('id_conducteur', $id_user)
                ->where('status', 'completed')
                ->sum('amount');
            $serviceEarnings = 0;
            if (\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
                $serviceEarnings = DB::table('service_requests')
                    ->where('driver_id', $id_user)
                    ->whereIn('status', ['Completed', 'completed'])
                    ->sum('amount');
            }
            $earningWalletSum = 0;
            if (!empty($row->ac_no) && \Illuminate\Support\Facades\Schema::hasTable('tbl_earning')) {
                $earningWalletSum = DB::table('tbl_earning')
                    ->where('ac_no', $row->ac_no)
                    ->where(function ($q) {
                        $q->where('created_at', 'credit')
                          ->orWhere('created_at', 'LIKE', '%credit%')
                          ->orWhere('earn_wallet', '>', 0);
                    })
                    ->sum('earn_wallet');
            }
            $calcEarn = round(floatval($rideEarnings) + floatval($parcelEarnings) + floatval($serviceEarnings), 2);
            $storedEarn = floatval($row->earn_amount ?? 0);
            $row->earn_amount = strval(number_format(max($storedEarn, $calcEarn), 2, '.', ''));
            // Driver wallet balance should strictly reflect actual withdrawable/debt balance in tj_conducteur.amount
            $row->amount = strval(number_format(floatval($row->amount ?? 0), 2, '.', ''));
            $response['success']= 'success';
            $response['error']= null;
            $response['message'] = 'Successfully';
            $response['data'] = $row;
            return response()->json($response);
        }
    }
    else{
        $response['success']= 'Failed';
        $response['error']= 'Not Found';
        return response()->json($response);
    }

    if($sql->count() > 0){
        $row = $sql->first();
        $response['success']= 'success';
        $response['error']= null;
        $response['message'] = 'Successfully';
        $response['data'] = $row;
    }else{
        $response['success']= 'Failed';
        $response['error']= 'Failed to Fetch data';
    }
    }else{
        $response['success']= 'Failed';
        $response['error']= 'some field are missing';
    }
        return response()->json($response);

    }
        

  
}

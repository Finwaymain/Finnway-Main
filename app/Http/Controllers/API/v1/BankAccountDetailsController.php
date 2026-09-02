<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use DB;
class BankAccountDetailsController extends Controller
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
    public function index()
    {

      $users = Driver::all();
      $users = Driver::paginate($this->limit);
      return response()->json($users);
    }

  	public function getData(Request $request)
  	{

        $user_id   = $request->get('user_id') ?: $request->get('driver_id') ?: $request->get('id_user') ?: $request->get('id_conducteur');
        $user_type = strtolower($request->get('user_type') ?: ($request->has('user_id') && !$request->has('driver_id') ? 'customer' : 'driver'));

	    if(!empty($user_id)){
            $table = ($user_type === 'customer') ? 'tj_user_app' : 'tj_conducteur';
            $selectCols = ['bank_name','branch_name','holder_name','account_no','ifsc_code'];
            if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'other_info')) {
                $selectCols[] = 'other_info';
            }

		    $row = DB::table($table)
	    	->select($selectCols)
	    	->where('id',$user_id)
	    	->first();

            if ($row) {
                $row->bank_name   = $row->bank_name ?? '';
                $row->branch_name = $row->branch_name ?? '';
                $row->holder_name = $row->holder_name ?? '';
                $row->account_no  = $row->account_no ?? '';
                $row->other_info  = $row->other_info ?? '';
                $row->ifsc_code   = $row->ifsc_code ?? '';

                if($row->bank_name=='' && $row->branch_name=='' && $row->holder_name=='' && $row->account_no=='' && $row->other_info=='' && $row->ifsc_code==''){
                    $response['success']= 'Failed';
                    $response['error']= 'Failed to fetch bank details';
                }
                else{
                    $response['success']= 'success';
                    $response['error']= null;
                    $response['message']= 'Bank details fetch successfully';
                    $response['data'] = $row;
                }
            } else {
                $response['success']= 'Failed';
                $response['error']= 'Bank details not found';
            }
	    }else{
	    	$response['success']= 'Failed';
		    $response['error']= 'Driver Id or User Id required';
	    }

	    return response()->json($response);
	}

	 public function register(Request $request)
	 {
        $user_id = $request->get('driver_id') ?: $request->get('user_id') ?: $request->get('id_user_app');
        $user_type = $request->get('user_type', 'driver');
        $bank_name = trim((string)$request->get('bank_name'));
        $branch_name = trim((string)$request->get('branch_name'));
        $holder_name = trim((string)$request->get('holder_name'));
        $account_no = trim((string)$request->get('account_no'));
        $other_info = trim((string)$request->get('information'));
        $ifsc_code = strtoupper(trim((string)$request->get('ifsc_code') ?: $request->get('other_info')));
        $date_heure = date('Y-m-d H:i:s');

        if (!empty($user_id)) {
            $table = ($user_type === 'customer' || $request->has('user_id') || $request->has('id_user_app')) ? 'tj_user_app' : 'tj_conducteur';
            $entity = DB::table($table)->where('id', $user_id)->first();

            if (!$entity && $table === 'tj_conducteur') {
                $entity = DB::table('tj_user_app')->where('id', $user_id)->first();
                if ($entity) {
                    $table = 'tj_user_app';
                    $user_type = 'customer';
                }
            }

            if ($entity) {
                $phone = $entity->phone ?? null;

                // Validate bank name, account number, ifsc code and cross-application uniqueness
                $valRes = \App\Helpers\BankValidationHelper::validateBankDetails(
                    $bank_name,
                    $account_no,
                    $ifsc_code,
                    $phone,
                    $user_id,
                    $user_type
                );

                if (!$valRes['valid']) {
                    return response()->json([
                        'success' => 'Failed',
                        'error'   => $valRes['error']
                    ]);
                }

                $updateData = [
                    'bank_name'   => $bank_name,
                    'branch_name' => $branch_name,
                    'holder_name' => $holder_name,
                    'account_no'  => $account_no,
                    'ifsc_code'   => $ifsc_code,
                    'modifier'    => $date_heure,
                ];
                if (\Schema::hasColumn($table, 'other_info')) {
                    $updateData['other_info'] = $other_info ?: $ifsc_code;
                }

                $updatedata = DB::table($table)->where('id', $user_id)->update($updateData);

                $row = DB::table($table)->where('id', $user_id)->first();

                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'Bank details added successfully';
                $response['data'] = $row;
            } else {
                $response['success'] = 'Failed';
                $response['error'] = 'Account Not Found';
            }
        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'User ID required';
        }

    	return response()->json($response);
  	}
}

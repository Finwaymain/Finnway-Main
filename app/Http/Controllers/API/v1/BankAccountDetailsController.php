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
        $user_type = strtolower($request->get('user_type') ?: $request->get('user_cat') ?: ($request->has('driver_id') && !$request->has('user_id') ? 'driver' : 'customer'));
        $phone     = $request->get('phone') ?: $request->get('mobile');

        $table = ($user_type === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        $row = null;

        $selectCols = ['bank_name', 'branch_name', 'holder_name', 'account_no', 'ifsc_code'];
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'other_info')) {
            $selectCols[] = 'other_info';
        }

        // Primary: Match by unique 10-digit phone number
        if (!empty($phone)) {
            $cleanPhone = substr(preg_replace('/\D/', '', (string)$phone), -10);
            if (!empty($cleanPhone)) {
                $row = DB::table($table)
                    ->select($selectCols)
                    ->where('phone', 'like', "%{$cleanPhone}%")
                    ->first();
            }
        }

        // Secondary: Match strictly in the designated table by ID
        if (!$row && !empty($user_id)) {
            $row = DB::table($table)
                ->select($selectCols)
                ->where('id', $user_id)
                ->first();
        }

        if ($row) {
            $row->bank_name   = $row->bank_name ?? '';
            $row->branch_name = $row->branch_name ?? '';
            $row->holder_name = $row->holder_name ?? '';
            $row->account_no  = $row->account_no ?? '';
            $row->other_info  = $row->other_info ?? '';
            $row->ifsc_code   = $row->ifsc_code ?? '';

            if ($row->bank_name === '' && $row->branch_name === '' && $row->holder_name === '' && $row->account_no === '' && $row->ifsc_code === '') {
                return response()->json([
                    'success' => 'Failed',
                    'error'   => 'Bank details not found'
                ]);
            }

            return response()->json([
                'success' => 'success',
                'error'   => null,
                'message' => 'Bank details fetch successfully',
                'data'    => $row
            ]);
        }

        return response()->json([
            'success' => 'Failed',
            'error'   => 'User or driver bank details not found'
        ]);
  	}

	 public function register(Request $request)
	 {
        $user_id     = $request->get('user_id') ?: $request->get('driver_id') ?: $request->get('id_user_app') ?: $request->get('id_conducteur');
        $user_type   = strtolower($request->get('user_type') ?: $request->get('user_cat') ?: ($request->has('driver_id') && !$request->has('user_id') ? 'driver' : 'customer'));
        $phone       = $request->get('phone') ?: $request->get('mobile');
        $bank_name   = trim((string)$request->get('bank_name'));
        $branch_name = trim((string)$request->get('branch_name'));
        $holder_name = trim((string)$request->get('holder_name'));
        $account_no  = trim((string)$request->get('account_no'));
        $other_info  = trim((string)$request->get('information'));
        $ifsc_code   = strtoupper(trim((string)$request->get('ifsc_code') ?: $request->get('other_info')));
        $date_heure  = date('Y-m-d H:i:s');

        $table = ($user_type === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        $entity = null;

        // Primary: Match by unique 10-digit phone number
        if (!empty($phone)) {
            $cleanPhone = substr(preg_replace('/\D/', '', (string)$phone), -10);
            if (!empty($cleanPhone)) {
                $entity = DB::table($table)->where('phone', 'like', "%{$cleanPhone}%")->first();
            }
        }

        // Secondary: Match strictly in designated table by ID
        if (!$entity && !empty($user_id)) {
            $entity = DB::table($table)->where('id', $user_id)->first();
        }

        if ($entity) {
            $userPhone = $entity->phone ?? $phone;
            $actualId  = $entity->id;

            // Validate bank name, account number, ifsc code and cross-application uniqueness
            $valRes = \App\Helpers\BankValidationHelper::validateBankDetails(
                $bank_name,
                $account_no,
                $ifsc_code,
                $userPhone,
                $actualId,
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

            DB::table($table)->where('id', $actualId)->update($updateData);
            $row = DB::table($table)->where('id', $actualId)->first();

            return response()->json([
                'success' => 'success',
                'error'   => null,
                'message' => 'Bank details added successfully',
                'data'    => $row
            ]);
        }

        return response()->json([
            'success' => 'Failed',
            'error'   => 'Account Not Found'
        ]);
	 }
}

<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\Controller;

use App\Models\UserApp;

use App\Models\Driver;

use App\Models\Currency;

use App\Models\Country;

use App\Models\Settings;

use Illuminate\Http\Request;

use DB;

class DriverWithdrawalsController extends Controller

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



    $users = UserApp::all();

    $users = UserApp::paginate($this->limit);

    return response()->json($users);

  }



  public function Withdrawals(Request $request)
  {
        $user_id = $request->get('user_id') ?: $request->get('driver_id') ?: $request->get('id_user') ?: $request->get('id_conducteur');
        $user_type = strtolower($request->get('user_type') ?? '');
        $amount = floatval($request->get('amount'));
        $note = $request->get('note');
        $date_heure = date('Y-m-d H:i:s');

        $setting = Settings::first();
        $minWithdrawAmount = floatval($setting->minimum_withdrawal_amount ?? 0);

        if (empty($user_id) || $amount <= 0) {
            return response()->json([
                'success' => 'Failed',
                'error'   => 'Please enter a valid withdrawal amount.'
            ]);
        }

        $isDriver = ($user_type === 'driver' || Driver::where('id', $user_id)->exists());
        $chkid = $isDriver ? Driver::where('id', $user_id)->first() : UserApp::where('id', $user_id)->first();

        if (!$chkid) {
            return response()->json([
                'success' => 'Failed',
                'error'   => ($isDriver ? 'Driver' : 'User') . ' not found.'
            ]);
        }

        $userAmount = floatval($chkid->amount ?? 0);
        $reqAmount = floatval($amount);

        if ($reqAmount > $userAmount) {
            return response()->json([
                'success' => 'Failed',
                'error'   => 'Withdrawal amount (₹' . number_format($reqAmount, 2) . ') exceeds your available balance of ₹' . number_format($userAmount, 2) . '.'
            ]);
        }

        if ($minWithdrawAmount > 0 && $userAmount < $minWithdrawAmount) {
            return response()->json([
                'success' => 'Failed',
                'error'   => 'Minimum wallet balance required for withdrawal is ₹' . number_format($minWithdrawAmount, 2) . '.'
            ]);
        }

        $id = DB::table('withdrawals')->insertGetId([
            'id_conducteur' => $user_id,
            'amount'        => $reqAmount,
            'note'          => $note ?? 'Payout Request',
            'statut'        => 'pending',
            'creer'         => $date_heure,
            'modifier'      => $date_heure,
        ]);

        if ($id > 0) {
            $response['success'] = 'success';
            $response['error']   = null;
            $response['message'] = 'Amount withdrawal request submitted successfully';
            $response['data']    = [
                'widrawals_statut' => 'pending',
                'widrawals_amount' => $reqAmount,
            ];
            return response()->json($response);
        }

        return response()->json([
            'success' => 'Failed',
            'error'   => 'Failed to save withdrawal request.'
        ]);
  }



}

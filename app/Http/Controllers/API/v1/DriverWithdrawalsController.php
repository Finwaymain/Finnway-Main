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

        $isDriver = ($user_type === 'driver');
        if (!$request->has('user_type') && ($request->has('driver_id') || $request->has('id_conducteur'))) {
            $isDriver = true;
        }

        $chkid = $isDriver ? Driver::where('id', $user_id)->first() : UserApp::where('id', $user_id)->first();

        if (!$chkid) {
            return response()->json([
                'success' => 'Failed',
                'error'   => ($isDriver ? 'Driver' : 'User') . ' not found.'
            ]);
        }

        $userAmount = floatval($chkid->amount ?? 0);
        $userEarn = floatval($chkid->earn_amount ?? 0);
        $withdrawableAmount = min($userAmount, $userEarn);
        $reqAmount = floatval($amount);

        if ($reqAmount > $withdrawableAmount) {
            $topupAmount = max(0, $userAmount - $withdrawableAmount);
            $msg = 'Withdrawal amount (₹' . number_format($reqAmount, 2) . ') exceeds your withdrawable earnings balance of ₹' . number_format($withdrawableAmount, 2) . '.';
            if ($topupAmount > 0) {
                $msg .= ' Self top-up funds (₹' . number_format($topupAmount, 2) . ') cannot be withdrawn via payout and can only be used for platform services.';
            }
            return response()->json([
                'success' => 'Failed',
                'error'   => $msg
            ]);
        }

        if ($minWithdrawAmount > 0 && $userAmount < $minWithdrawAmount) {
            return response()->json([
                'success' => 'Failed',
                'error'   => 'Minimum wallet balance required for withdrawal is ₹' . number_format($minWithdrawAmount, 2) . '.'
            ]);
        }

        $roleTag = $isDriver ? '[Driver]' : '[User]';
        $finalNote = $roleTag . ' ' . ($note ?? 'Payout Request');

        // 1. Deduct wallet balance immediately upon payout request
        $newAmount = max(0, $userAmount - $reqAmount);
        $newEarn = max(0, $userEarn - $reqAmount);

        if ($isDriver) {
            DB::table('tj_conducteur')->where('id', $user_id)->update([
                'amount'      => $newAmount,
                'earn_amount' => $newEarn,
                'modifier'    => $date_heure,
            ]);
        } else {
            DB::table('tj_user_app')->where('id', $user_id)->update([
                'amount'      => $newAmount,
                'earn_amount' => $newEarn,
                'modifier'    => $date_heure,
            ]);
        }

        // 2. Insert into withdrawals table
        $id = DB::table('withdrawals')->insertGetId([
            'id_conducteur' => $user_id,
            'amount'        => $reqAmount,
            'note'          => $finalNote,
            'statut'        => 'pending',
            'creer'         => $date_heure,
            'modifier'      => $date_heure,
        ]);

        // 3. Record debit transaction in wallet ledger immediately
        $txnId = str_pad((string)$id, 7, '0', STR_PAD_LEFT);
        if ($isDriver) {
            DB::table('tj_conducteur_transaction')->insert([
                'id_conducteur'   => $user_id,
                'ac_no'           => $chkid->ac_no ?? $chkid->phone,
                'txn_id'          => $txnId,
                'withdraw_status' => 'pending',
                'payment_status'  => 'pending',
                'payment_method'  => 'Bank Withdrawal',
                'description'     => 'Bank Withdrawal Request',
                'note'            => 'Payout Request #' . $txnId . ' to Linked Bank Account',
                'amount'          => $reqAmount,
                'type'            => 'debit',
                'deduction_type'  => 0,
                'date'            => date('Y-m-d'),
                'creer'           => $date_heure,
                'modifier'        => $date_heure,
            ]);
        } else {
            DB::table('tj_transaction')->insert([
                'id_user_app'     => $user_id,
                'ac_no'           => $chkid->ac_no ?? $chkid->phone,
                'txn_id'          => $txnId,
                'withdraw_status' => 'pending',
                'payment_status'  => 'pending',
                'payment_method'  => 'Bank Withdrawal',
                'description'     => 'Bank Withdrawal Request #' . $txnId,
                'amount'          => $reqAmount,
                'type'            => 'debit',
                'deduction_type'  => 0,
                'date'            => date('Y-m-d'),
                'creer'           => $date_heure,
                'modifier'        => $date_heure,
            ]);
        }

        if ($id > 0) {
            $response['success'] = 'success';
            $response['error']   = null;
            $response['message'] = 'Amount withdrawal request submitted successfully';
            $response['data']    = [
                'widrawals_statut' => 'pending',
                'widrawals_amount' => $reqAmount,
                'new_balance'      => $newAmount,
                'new_earnings'     => $newEarn,
            ];
            return response()->json($response);
        }

        return response()->json([
            'success' => 'Failed',
            'error'   => 'Failed to save withdrawal request.'
        ]);
  }



}

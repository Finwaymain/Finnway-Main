<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use Illuminate\Http\Request;
use DB;
class AddAmountController extends Controller
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
    $id_user = $request->get('id_user') ?? $request->get('user_id') ?? $request->get('driver_id');
    $cat_user = strtolower(trim((string)$request->get('cat_user', 'user_app')));
    $amount_init = floatval($request->get('amount'));
    $paymethod = $request->get('paymethod', 'Online');
    $transaction = $request->get('transaction_id', 'TXN_' . time());
    $payStatus = 'success';
    $date_heure = date('Y-m-d H:i:s');

    if ($amount_init < 10) {
        return response()->json([
            'success' => 'Failed',
            'error'   => 'Minimum top-up amount is ₹10.',
            'message' => 'Minimum top-up amount is ₹10.',
        ], 400);
    }

    if ($amount_init > 50000) {
        return response()->json([
            'success' => 'Failed',
            'error'   => 'Maximum top-up amount per transaction is ₹50,000.',
            'message' => 'Maximum top-up amount per transaction is ₹50,000.',
        ], 400);
    }

    $isDriver = ($cat_user === 'driver' || $cat_user === 'conducteur' || $request->has('driver_id'));

    if (!$isDriver) {

        $sql = DB::table('tj_user_app')
        ->where('id','=',$id_user)
        ->first();
        $row = $sql;
        if ($sql) {
          $amount_ = floatval($sql->amount ?? 0);
          $topup_ = floatval($sql->topup_balance ?? 0);
          $amount = $amount_ + $amount_init;
          $newTopup = $topup_ + $amount_init;

          DB::table('tj_user_app')->where('id', $id_user)->update([
              'amount'        => $amount,
              'topup_balance' => $newTopup,
              'modifier'      => $date_heure,
          ]);

          $insTx = [
              'amount'          => $amount_init,
              'deduction_type'  => 1,
              'payment_method'  => $paymethod,
              'payment_status'  => $payStatus,
              'id_user_app'     => $id_user,
              'creer'           => $date_heure,
              'modifier'        => $date_heure,
          ];
          if (\Illuminate\Support\Facades\Schema::hasColumn('tj_transaction', 'wallet_bucket')) {
              $insTx['wallet_bucket'] = 'topup';
          }
          if (\Illuminate\Support\Facades\Schema::hasColumn('tj_transaction', 'description')) {
              $insTx['description'] = 'Wallet Top-Up (Non-withdrawable)';
          }
          DB::table('tj_transaction')->insert($insTx);
        }
        $sql_notification = UserApp::where('id',$id_user)->first();
        $data = $sql_notification ? $sql_notification->toArray() : [];
        if (!empty($data)) {
            $row->amount = $data['amount'];
            $email = $data['email'] ?? null;
        } else {
            $email = null;
        }
        if(!empty($email)){

        $emailsubject = '';
        $emailmessage = '';
        $emailtemplate = DB::table('email_template')->select('*')->where('type', 'wallet_topup')->first();
        if (!empty($emailtemplate)) {
          $emailsubject = $emailtemplate->subject;
          $emailmessage = $emailtemplate->message;
        }
        $currencyData = DB::table('tj_currency')->select('*')->where('statut', 'yes')->first();
        if ($currencyData->symbol_at_right == "true") {
          $amount_init = number_format($amount_init, $currencyData->decimal_digit) . $currencyData->symbole;
          $newBalance = number_format($data['amount'], $currencyData->decimal_digit) . $currencyData->symbole;
        } else {
          $amount_init = $currencyData->symbole . number_format($amount_init, $currencyData->decimal_digit);
          $newBalance = $currencyData->symbole . number_format($data['amount'], $currencyData->decimal_digit);

        }

        $contact_us_email = DB::table('tj_settings')->select('contact_us_email')->value('contact_us_email');
        $contact_us_email = $contact_us_email ? $contact_us_email : 'none@none.com';


        $app_name = env('APP_NAME', 'Cabme');

        $to = $email;
        $date = date('d F Y', strtotime($date_heure));


        $emailmessage = str_replace("{AppName}", $app_name, $emailmessage);
        $emailmessage = str_replace("{UserName}", $data['nom'] . " " . $data['prenom'], $emailmessage);
        $emailmessage = str_replace("{Amount}", $amount_init, $emailmessage);
        $emailmessage = str_replace("{PaymentMethod}", $paymethod, $emailmessage);
        $emailmessage = str_replace('{TransactionId}', $transaction, $emailmessage);
        $emailmessage = str_replace('{Balance}', $newBalance, $emailmessage);
        $emailmessage = str_replace('{Date}', $date, $emailmessage);

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $app_name . '<' . $contact_us_email . '>' . "\r\n";
        try {
            @mail($to, $emailsubject, $emailmessage, $headers);
        } catch (\Throwable $e) {}

      }

        if(!empty($row)){
          $response['success'] = 'success';
          $response['error'] = null;
          $response['message'] = 'successfully';
          $response['data'] = $row;

        }else{
          $response['success'] = 'Failed';
          $response['error'] = 'Failed';

        }

    }
    elseif($cat_user == "driver"){
        $row = DB::table('tj_conducteur')
        ->where('id','=',DB::raw($id_user))
        ->first();

        if ($row) {
          $amount_ = floatval($row->amount ?? 0);
          $topup_ = floatval($row->topup_balance ?? 0);
          $amount = $amount_ + $amount_init;
          $newTopup = $topup_ + $amount_init;

          DB::table('tj_conducteur')->where('id', $id_user)->update([
              'amount'        => $amount,
              'topup_balance' => $newTopup,
              'modifier'      => $date_heure,
          ]);

          $insDTx = [
              'amount'         => $amount_init,
              'payment_method' => $paymethod,
              'id_conducteur'  => $id_user,
              'creer'          => $date_heure,
              'modifier'       => $date_heure,
          ];
          if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'wallet_bucket')) {
              $insDTx['wallet_bucket'] = 'topup';
          }
          if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'note')) {
              $insDTx['note'] = 'Wallet Top-Up (Non-withdrawable)';
          }
          if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'description')) {
              $insDTx['description'] = 'Wallet Top-Up (Non-withdrawable)';
          }
          DB::table('tj_conducteur_transaction')->insert($insDTx);
        }
        
        $sql_notification = Driver::where('id',$id_user)->first();
        $data = $sql_notification->toArray();
        $row->amount = $data['amount'];
        $email = $data['email'];

      if (!empty($email)) {

        $emailsubject = '';
        $emailmessage = '';
        $emailtemplate = DB::table('email_template')->select('*')->where('type', 'wallet_topup')->first();
        if (!empty($emailtemplate)) {
          $emailsubject = $emailtemplate->subject;
          $emailmessage = $emailtemplate->message;
          $send_to_admin = $emailtemplate->send_to_admin;
        }
        $currencyData = DB::table('tj_currency')->select('*')->where('statut', 'yes')->first();
        if ($currencyData->symbol_at_right == "true") {
          $amount_init = number_format($amount_init, $currencyData->decimal_digit) . $currencyData->symbole;
          $newBalance = number_format($data['amount'], $currencyData->decimal_digit) . $currencyData->symbole;
        } else {
          $amount_init = $currencyData->symbole . number_format($amount_init, $currencyData->decimal_digit);
          $newBalance = $currencyData->symbole . number_format($data['amount'], $currencyData->decimal_digit);

        }

        $contact_us_email = DB::table('tj_settings')->select('contact_us_email')->value('contact_us_email');
        $contact_us_email = $contact_us_email ? $contact_us_email : 'none@none.com';


        $app_name = env('APP_NAME', 'Cabme');
        
        if($send_to_admin=="true"){
          $to = $email.",".$contact_us_email;

        }else{
          $to = $email;

        }
        $date = date('d F Y', strtotime($date_heure));


        $emailmessage = str_replace("{AppName}", $app_name, $emailmessage);
        $emailmessage = str_replace("{UserName}", $data['nom'] . " " . $data['prenom'], $emailmessage);
        $emailmessage = str_replace("{Amount}", $amount_init, $emailmessage);
        $emailmessage = str_replace("{PaymentMethod}", $paymethod, $emailmessage);
        $emailmessage = str_replace('{TransactionId}', $transaction, $emailmessage);
        $emailmessage = str_replace('{Balance}', $newBalance, $emailmessage);
        $emailmessage = str_replace('{Date}', $date, $emailmessage);

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $app_name . '<' . $contact_us_email . '>' . "\r\n";
        try {
            @mail($to, $emailsubject, $emailmessage, $headers);
        } catch (\Throwable $e) {}

      }

      if(!empty($row)){
          $response['success'] = 'success';
          $response['error'] = null;
          $response['message'] = 'successfully';
          $response['data'] = $row;

        }else
        {
          $response['success'] = 'Failed';
          $response['error'] = 'Failed';

        }
    }
    else{
      $response['success'] = 'Failed';
      $response['error'] = 'User category is incorrect';

    }
    return response()->json($response);
  }

}

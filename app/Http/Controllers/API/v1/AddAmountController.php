<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Http\Controllers\API\v1\GcmController;
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
        ->select('amount')
        ->where('id','=',$id_user)
        ->get();
        foreach($sql as $row){
          $amount_ = $row->amount;
          $amount = $amount_+$amount_init;

        $updatedata = DB::update('update tj_user_app set amount = ?,modifier = ? where id = ?',[$amount,$date_heure,$id_user]);

        $query = DB::table('tj_transaction')->insert([
            'amount'         => (string)$amount_init,
            'deduction_type' => 1,
            'type'           => 'credit',
            'payment_method' => $paymethod,
            'payment_status' => $payStatus,
            'id_user_app'    => $id_user,
            'description'    => 'Wallet Top-Up',
            'creer'          => $date_heure,
            'modifier'       => $date_heure,
        ]);
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


        $app_name = 'Fiinway';

        $to = $email;
        $date = date('d F Y', strtotime($date_heure));

        $emailsubject = !empty($emailsubject) ? str_replace("{AppName}", $app_name, $emailsubject) : "Wallet Topup Confirmation - {$app_name}";
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
        try {
            @mail($to, $emailsubject, $emailmessage, $headers);
        } catch (\Throwable $e) {}

      }

      // Send Push Notification & Notification Record to Customer
      $user_fcm = $sql_notification->fcm_id ?? '';
      if (!empty($user_fcm)) {
          $notifPayload = [
              'title' => 'Fiinway',
              'body'  => "Your Fiinway account has been credited with {$amount_init}. Updated balance: {$newBalance}.",
              'sound' => 'default',
              'tag'   => 'wallet_topup',
              'type'  => 'wallet',
          ];
          try {
              GcmController::sendNotification($user_fcm, $notifPayload);
          } catch (\Throwable $e) {}

          if (\Illuminate\Support\Facades\Schema::hasTable('tj_notification')) {
              DB::table('tj_notification')->insert([
                  'titre'    => 'Fiinway',
                  'message'  => "Your Fiinway account has been credited with {$amount_init}. Updated balance: {$newBalance}.",
                  'statut'   => 'yes',
                  'creer'    => $date_heure,
                  'modifier' => $date_heure,
                  'to_id'    => $id_user,
                  'from_id'  => 0,
                  'type'     => 'wallet_topup',
              ]);
          }
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
        ->select('amount')
        ->where('id','=',DB::raw($id_user))
        ->first();


          $amount_ = $row->amount;
          $amount = $amount_+$amount_init;

          $updatedata = DB::update('update tj_conducteur set amount = ? where id = ?',[$amount,$id_user]);
          $amount=$amount_init;

          DB::table('tj_conducteur_transaction')->insert([
              'amount'         => (string)$amount_init,
              'deduction_type' => 1,
              'type'           => 'credit',
              'payment_method' => $paymethod,
              'id_conducteur'  => $id_user,
              'description'    => 'Wallet Top-Up',
              'note'           => 'Wallet Top-Up via ' . $paymethod,
              'creer'          => $date_heure,
              'modifier'       => $date_heure,
          ]);
        
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


        $app_name = 'Fiinway';
        
        if($send_to_admin=="true"){
          $to = $email.",".$contact_us_email;

        }else{
          $to = $email;

        }
        $date = date('d F Y', strtotime($date_heure));

        $emailsubject = !empty($emailsubject) ? str_replace("{AppName}", $app_name, $emailsubject) : "Wallet Topup Confirmation - {$app_name}";
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
        try {
            @mail($to, $emailsubject, $emailmessage, $headers);
        } catch (\Throwable $e) {}

      }

      // Send Push Notification & Notification Record to Driver
      $driver_fcm = $sql_notification->fcm_id ?? '';
      if (!empty($driver_fcm)) {
          $notifPayload = [
              'title' => 'Fiinway',
              'body'  => "Your Fiinway account has been credited with {$amount_init}. Updated balance: {$newBalance}.",
              'sound' => 'default',
              'tag'   => 'wallet_topup',
              'type'  => 'wallet',
          ];
          try {
              GcmController::sendNotification($driver_fcm, $notifPayload);
          } catch (\Throwable $e) {}

          if (\Illuminate\Support\Facades\Schema::hasTable('tj_notification')) {
              DB::table('tj_notification')->insert([
                  'titre'    => 'Fiinway',
                  'message'  => "Your Fiinway account has been credited with {$amount_init}. Updated balance: {$newBalance}.",
                  'statut'   => 'yes',
                  'creer'    => $date_heure,
                  'modifier' => $date_heure,
                  'to_id'    => $id_user,
                  'from_id'  => 0,
                  'type'     => 'wallet_topup',
              ]);
          }
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

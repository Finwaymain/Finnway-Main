<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\Controller;

use App\Models\Requests;

use App\Models\Commission;
use App\Models\Driver;

use App\Models\Tax;

use Illuminate\Http\Request;

use DB;



class PaymentByCashController extends Controller

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





  public function UpdatePayment(Request $request)

  {



        $currencyData = DB::table('tj_currency')->select('*')->where('statut', 'yes')->first();

        $currency = $currencyData ? ($currencyData->symbole ?? '') : '';



        $id_requete = $request->get('id_ride');
        $id_user = $request->get('id_driver');
        $id_user_app = $request->get('id_user_app');
        $amount_new = floatval($request->get('amount'));
        $paymethod = $request->get('paymethod') ?: 'Cash';
        $date_heure = date('Y-m-d H:i:s');
        $discount = floatval($request->get('discount'));
        $tax = $request->get('tax');
        $tax_json = json_encode($tax);
        $tip = floatval($request->get('tip'));
        $transaction_id = $request->get('transaction_id');
        $payment_status = $request->get('payment_status') ?: 'success';

        // Resolve missing IDs and amount from tj_requete
        $rideRecord = null;
        if (!empty($id_requete)) {
            $rideRecord = DB::table('tj_requete')->where('id', $id_requete)->first();
            if ($rideRecord) {
                if (empty($id_user)) $id_user = $rideRecord->id_conducteur;
                if (empty($id_user_app)) $id_user_app = $rideRecord->id_user_app;
                if ($amount_new <= 0) $amount_new = floatval($rideRecord->montant);
            }
        }

        $baseFare = max(0, floatval($amount_new) - floatval($discount));

        // 1. Resolve Admin Commission directly from tj_commission active setting on base fare
        $commission_amount = 0;
        $admin_commisions = Commission::where('statut', 'yes')->first();
        if (!$admin_commisions) {
            $admin_commisions = Commission::first();
        }

        if (!empty($admin_commisions)) {
            $commType = strtolower(trim((string) ($admin_commisions->type ?? 'percentage')));
            $commVal = floatval($admin_commisions->value ?? 0);
            if ($commType == 'percentage' || $commType == 'percent') {
                $commission_amount = round(($commVal * floatval($baseFare)) / 100, 2);
            } else {
                $commission_amount = round($commVal, 2);
            }
        }

        // 2. Resolve GST / Active Taxes from tj_tax filtered by payment method (Home Service reference standard)
        $taxDetails = [];
        $totalTaxAmount = 0.0;
        $taxHtml = '';
        $m = strtolower(trim((string) $paymethod));

        if (\Illuminate\Support\Facades\Schema::hasTable('tj_tax')) {
            $dbTaxes = DB::table('tj_tax')->where('statut', 'yes')->get();
            foreach ($dbTaxes as $t) {
                $methods = !empty($t->applicable_on) ? explode(',', strtolower($t->applicable_on)) : ['cash', 'upi', 'wallet', 'online'];
                $applies = in_array($m, $methods, true) ||
                           in_array('all', $methods, true) ||
                           ($m === 'upi' && in_array('online', $methods, true)) ||
                           ($m === 'online' && in_array('upi', $methods, true)) ||
                           (str_contains($m, 'cash') && in_array('cash', $methods, true)) ||
                           (str_contains($m, 'wallet') && in_array('wallet', $methods, true));
                if ($applies) {
                    $val = floatval($t->value ?? 0);
                    $tAmt = (strtolower((string) $t->type) === 'percentage') ? round(($baseFare * $val) / 100, 2) : round($val, 2);
                    $totalTaxAmount += $tAmt;
                    $taxDetails[] = [
                        'libelle' => $t->libelle,
                        'value'   => (string) $t->value,
                        'type'    => $t->type,
                        'amount'  => $tAmt,
                    ];
                    $taxHtml .= "<p><b>" . $t->libelle . " (" . $t->value . ($t->type == 'Percentage' ? '%' : '') . "): </b>" . $currency . number_format($tAmt, 2) . "</p>";
                }
            }
        }

        if (empty($taxHtml)) {
            $taxHtml = "0";
        }
        $tax_json = json_encode($taxDetails);

        $totalamount = round($baseFare + $totalTaxAmount + $tip, 2);

        // Driver share on non-cash rides: Fare - Commission + Tip (Taxes belong to platform/govt, NEVER credited to driver!)
        $driverShare = max(0, round(($baseFare + $tip) - $commission_amount, 2));

        // 3. User Wallet Deduction ONLY if paid via Wallet (NEVER for Cash!)
        if (strtolower($paymethod) == 'wallet' && !empty($id_user_app)) {
            $userRow = DB::table('tj_user_app')->where('id', $id_user_app)->first();
            if ($userRow && $userRow->amount !== null) {
                $userBal = floatval($userRow->amount);
                $newUserBal = max(0, round($userBal - $totalamount, 2));
                DB::table('tj_user_app')->where('id', $id_user_app)->update(['amount' => $newUserBal, 'modifier' => $date_heure]);
                
                $userTxData = [
                    'amount' => '-' . $totalamount,
                    'payment_method' => 'Wallet',
                    'payment_status' => 'success',
                    'ride_id' => (string) $id_requete,
                    'id_user_app' => $id_user_app,
                    'creer' => $date_heure,
                    'modifier' => $date_heure,
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('tj_transaction', 'deduction_type')) {
                    $userTxData['deduction_type'] = 'Cab Ride';
                }
                DB::table('tj_transaction')->insert($userTxData);
            }
        } elseif (strtolower($paymethod) == 'cash' && !empty($id_user_app)) {
            // User paid physical cash to driver in hand: record Cash transaction for history
            $userTxData = [
                'amount' => $totalamount,
                'payment_method' => 'Cash',
                'payment_status' => 'success',
                'ride_id' => (string) $id_requete,
                'id_user_app' => $id_user_app,
                'creer' => $date_heure,
                'modifier' => $date_heure,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('tj_transaction', 'deduction_type')) {
                $userTxData['deduction_type'] = 'Cab Ride';
            }
            DB::table('tj_transaction')->insert($userTxData);
        }

        // 4. Driver Wallet Accounting (Home Service standard)
        $sql_driver = DB::table('tj_conducteur')
            ->select('amount', 'earn_amount')
            ->where('id', '=', $id_user)
            ->first();

        $walletAmount = $sql_driver && $sql_driver->amount !== null && $sql_driver->amount !== '' ? floatval($sql_driver->amount) : 0;
        $earnAmount = $sql_driver && !empty($sql_driver->earn_amount) ? floatval($sql_driver->earn_amount) : 0;

        if (strtolower($paymethod) == 'cash') {
            // Cash collected by driver in hand: platform commission + GST/Taxes debited from driver wallet.
            // Driver wallet balance CAN go negative (debt/due to platform).
            $totalPlatformDeduction = round(floatval($commission_amount) + floatval($totalTaxAmount), 2);
            $newWalletAmount = round($walletAmount - $totalPlatformDeduction, 2);
        } else {
            // Online / UPI / Wallet: net earnings (base - commission + tip) credited to driver wallet
            $newWalletAmount = round($walletAmount + $driverShare, 2);
        }

        $driverUpdateData = ['amount' => strval(number_format($newWalletAmount, 2, '.', ''))];
        if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur', 'earn_amount')) {
            $driverUpdateData['earn_amount'] = strval(number_format($earnAmount + $baseFare, 2, '.', ''));
        }
        DB::table('tj_conducteur')->where('id', '=', $id_user)->update($driverUpdateData);

        $date = date('Y-m-d H:i:s');

        // Driver Transaction Ledger (Transparent 3-row logging matching Home Service)
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur_transaction')) {
            if (strtolower($paymethod) == 'cash') {
                // 1. Gross Cash Earning Entry
                $grossTxData = [
                    'id_conducteur'  => $id_user,
                    'amount'         => (string) $totalamount,
                    'payment_method' => 'Cash',
                    'id_ride'        => (string) $id_requete,
                    'creer'          => $date,
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'deduction_type')) {
                    $grossTxData['deduction_type'] = 'Cab Ride';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'note')) {
                    $grossTxData['note'] = 'Received cash payment (including ' . $currency . $totalTaxAmount . ' taxes) for Ride #' . $id_requete;
                }
                DB::table('tj_conducteur_transaction')->insert($grossTxData);

                // 2. Admin Commission Deduction
                if (!empty($commission_amount)) {
                    $commTxData = [
                        'id_conducteur'  => $id_user,
                        'amount'         => "-" . $commission_amount,
                        'payment_method' => 'Commission',
                        'id_ride'        => (string) $id_requete,
                        'creer'          => $date,
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'deduction_type')) {
                        $commTxData['deduction_type'] = 'Commission';
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'note')) {
                        $commTxData['note'] = 'Admin Commission for Ride #' . $id_requete;
                    }
                    DB::table('tj_conducteur_transaction')->insert($commTxData);
                }

                // 3. Tax / GST Deduction
                if (!empty($totalTaxAmount)) {
                    $taxTxData = [
                        'id_conducteur'  => $id_user,
                        'amount'         => "-" . $totalTaxAmount,
                        'payment_method' => 'Tax Deduction',
                        'id_ride'        => (string) $id_requete,
                        'creer'          => $date,
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'deduction_type')) {
                        $taxTxData['deduction_type'] = 'Tax';
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'note')) {
                        $taxTxData['note'] = 'GST / Platform Taxes collected in cash for Ride #' . $id_requete;
                    }
                    DB::table('tj_conducteur_transaction')->insert($taxTxData);
                }
            } else {
                // Online / Wallet / UPI: Net earnings credited, commission deducted
                $onlineTxData = [
                    'id_conducteur'  => $id_user,
                    'amount'         => (string) round($baseFare + $tip, 2),
                    'payment_method' => $paymethod,
                    'id_ride'        => (string) $id_requete,
                    'creer'          => $date,
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'deduction_type')) {
                    $onlineTxData['deduction_type'] = 'Cab Ride';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'note')) {
                    $onlineTxData['note'] = 'Received payment via ' . $paymethod . ' for Ride #' . $id_requete;
                }
                DB::table('tj_conducteur_transaction')->insert($onlineTxData);

                if (!empty($commission_amount)) {
                    $commTxData = [
                        'id_conducteur'  => $id_user,
                        'amount'         => "-" . $commission_amount,
                        'payment_method' => 'Commission',
                        'id_ride'        => (string) $id_requete,
                        'creer'          => $date,
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'deduction_type')) {
                        $commTxData['deduction_type'] = 'Commission';
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'note')) {
                        $commTxData['note'] = 'Admin Commission for Ride #' . $id_requete;
                    }
                    DB::table('tj_conducteur_transaction')->insert($commTxData);
                }
            }
        }

        $row_payment_method = DB::table('tj_payment_method')->select('id')->whereRaw('LOWER(libelle) = ?', [strtolower($paymethod)])->first();
        if ($row_payment_method) {
            $id_payment = $row_payment_method->id;
        } else {
            $default_pm = DB::table('tj_payment_method')->select('id')->first();
            $id_payment = $default_pm ? $default_pm->id : 1;
        }

     if(!empty($id_requete)){



        $updateFields = [
            'statut_paiement' => 'yes',
            'id_payment_method' => $id_payment,
            'tip_amount' => $tip,
            'tax' => $tax_json,
            'discount' => $discount,
            'transaction_id' => $transaction_id,
            'admin_commission' => $commission_amount,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('tj_requete', 'tax_amount')) {
            $updateFields['tax_amount'] = $totalTaxAmount;
        }
        $updatedata = DB::table('tj_requete')->where('id', $id_requete)->update($updateFields);



         if ($updatedata > 0) {

            $sql = Requests::where('id',$id_requete)->first();

        $row = $sql->toArray();

        $row['id']=(string)$row['id'];

        $row['tax'] = json_decode($row['tax'], true);

        if($row['trajet'] != ''){

            if(file_exists(public_path('images/recu_trajet_course'.'/'.$row['trajet'] )))

            {

                $image_user = asset('images/recu_trajet_course').'/'. $row['trajet'];

            }

            else

            {

                $image_user =asset('assets/images/placeholder_image.jpg');



            }

            $row['trajet'] = $image_user;

        }





                // Get user info

            $query = DB::table('tj_requete')

            ->crossJoin('tj_user_app')

            ->select('tj_user_app.fcm_id', 'tj_user_app.id', 'tj_user_app.nom', 'tj_user_app.prenom', 'tj_user_app.email')

            ->where('tj_requete.id_user_app','=',DB::raw('tj_user_app.id'))

            ->where('tj_requete.id','=',$id_requete)

            ->get();



            // Get Ride Info

            $query_ride =  DB::table('tj_requete')

                        ->select('distance', 'distance_unit','duree', 'montant', 'creer', 'trajet','discount','tax','tip_amount')

                        ->where('id','=',$id_requete)

                        ->get();

            foreach($query_ride as $ride){

                $distance = $ride->distance;

				$distance_unit = $ride->distance_unit;

				$duree = $ride->duree;

                $date_heure = $ride->creer;

                $img_name = $ride->trajet;

            }



			$total = !empty($totalamount) ? $totalamount : 0;

			$subtotal = !empty($amount_new) ? number_format($amount_new,2): 0;

			$discount = !empty($discount) ? number_format($discount,2): 0;

			$tax = number_format($totalTaxAmount,2);

			$tip_amount = !empty($tip) ? number_format($tip,2):0;

			$total = number_format($total,2);

            if($currencyData->symbol_at_right=="true"){

                    $total = $total."".$currency;

                    $subtotal = $subtotal . "" . $currency;

                    $discount = $discount . "" . $currency;

                    $tip_amount = $tip_amount . "" . $currency;

                    $tax = $tax . "" . $currency;



                }else{

                    $total = $currency."".$total ;

                    $subtotal = $currency . "" . $subtotal;

                    $discount = $currency . "" . $discount;

                    $tip_amount = $currency . "" . $tip_amount;

                    $tax = $currency . "" . $tax;



                }

            $tokens = array();

            $nom = "";

            $prenom = "";

            $email = "";

            if (!empty($query)) {

                foreach($query as $user){



                    if (!empty($user->fcm_id)) {

                        $tokens[] = $user->fcm_id;

                        $nom = $user->nom;

                        $prenom = $user->prenom;

                        $email = $user->email;

                    }

                }

            }



            if($email != ""){

                    $emailsubject = '';

                    $emailmessage = '';

                    $emailtemplate = DB::table('email_template')->select('*')->where('type', 'payment_receipt')->first();

                    if(!empty($emailtemplate)){

                        $emailsubject = $emailtemplate->subject;

                        $emailmessage = $emailtemplate->message;

                        $send_to_admin = $emailtemplate->send_to_admin;

                    }

            	$contact_us_email = DB::table('tj_settings')->select('contact_us_email')->value('contact_us_email');

				$contact_us_email = $contact_us_email?$contact_us_email:'none@none.com';





            	$app_name = env('APP_NAME','Cabme');

                if($send_to_admin=="true"){

                        $to = $email . "," . $contact_us_email;

                }else{

                        $to = $email;

                    }

                $date=date('d F Y',strtotime($date_heure));



                $emailsubject=str_replace("{AppName}", $app_name, $emailsubject);



                $emailmessage=str_replace("{AppName}", $app_name, $emailmessage);

                $emailmessage=str_replace("{UserName}", $prenom . " " . $nom, $emailmessage);

                $emailmessage=str_replace("{Distance}", $distance . " " . $distance_unit, $emailmessage);

                $emailmessage=str_replace("{Duree}", $duree, $emailmessage);

                $emailmessage=str_replace('{Subtotal}', $subtotal, $emailmessage);

                $emailmessage=str_replace('{Discount}', $discount, $emailmessage);

                $emailmessage=str_replace('{Tax}', $taxHtml, $emailmessage);

                $emailmessage=str_replace('{Tip}', $tip_amount, $emailmessage);

                $emailmessage=str_replace('{Total}', $total, $emailmessage);

                $emailmessage=str_replace('{Date}', $date, $emailmessage);

                

                // Always set content-type when sending HTML email

                $headers = "MIME-Version: 1.0" . "\r\n";

				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

				$headers .= 'From: '.$app_name.'<'.$contact_us_email.'>' . "\r\n";

                mail($to,$emailsubject,$emailmessage,$headers);

            }



            $response['success'] = 'success';

            $response['error'] = null;

            $response['message'] = 'status successfully updated';

            $response['data'] = $row;

        }else {

            $response['success'] = 'Failed';

            $response['error'] = 'Failed to update data';

        }

    }else {

        $response['success'] = 'Failed';

        $response['error'] = 'ID is requiered';

    }

   return response()->json($response);

  }

}


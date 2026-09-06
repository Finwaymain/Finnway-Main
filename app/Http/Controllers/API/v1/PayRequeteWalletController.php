<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\Controller;

use App\Http\Controllers\API\v1\GcmController;

use App\Models\Requests;

use App\Models\UserApp;

use App\Models\Driver;

use App\Models\Commission;

use App\Models\Tax;

use App\Models\PaymentMethod;

use Illuminate\Http\Request;

use DB;

class PayRequeteWalletController extends Controller

{



    public function UpdatePayRequeteWallet(Request $request)

    {

        $currencyData = DB::table('tj_currency')->select('*')->where('statut', 'yes')->first();

        $currency = $currencyData->symbole ? $currencyData->symbole : '$';



        $id_requete = $request->get('id_ride');

        $id_user = $request->get('id_driver');

        $id_user_app = $request->get('id_user_app');

        $amount_new = floatval($request->get('amount'));

        $paymethod = $request->get('paymethod');

        $date_heure = date('Y-m-d H:i:s');

        $discount = floatval($request->get('discount'));

        $tip = floatval($request->get('tip'));

        $transaction_id = $request->get('transaction_id');

        $payment_status = $request->get('payment_status');

        $tax = $request->get('tax');

        $tax_json = json_encode($tax);



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

        // 2. Resolve GST / Active Taxes from tj_tax for 'wallet' (Home Service reference standard)
        $taxDetails = [];
        $totalTaxAmount = 0.0;
        $taxHtml = '';

        if (\Illuminate\Support\Facades\Schema::hasTable('tj_tax')) {
            $dbTaxes = DB::table('tj_tax')->where('statut', 'yes')->get();
            foreach ($dbTaxes as $t) {
                $methods = !empty($t->applicable_on) ? explode(',', strtolower($t->applicable_on)) : ['cash', 'upi', 'wallet', 'online'];
                $applies = in_array('wallet', $methods, true) || in_array('all', $methods, true);
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

        $totalUserAmount = round($baseFare + $totalTaxAmount + $tip, 2);
        $driverBaseAmount = round($baseFare + $tip, 2);
        $totalDriverAmount = max(0, round($driverBaseAmount - $commission_amount, 2));

        // 3. User Wallet Deduction
        $row_amount = DB::table('tj_user_app')->select('amount')->where('id', '=', $id_user_app)->first();
        $userWallet = 0;
        if (!empty($row_amount)) {
            if ($row_amount->amount != '' && $row_amount->amount != null) {
                $userWallet = floatval($row_amount->amount);
            }
            $userWallet = max(0, round($userWallet - $totalUserAmount, 2));
            DB::table('tj_user_app')->where('id', $id_user_app)->update(['amount' => $userWallet, 'modifier' => $date_heure]);
        }

        $userTxData = [
            'amount' => '-' . $totalUserAmount,
            'payment_method' => 'Wallet',
            'payment_status' => $payment_status ?: 'success',
            'ride_id' => (string) $id_requete,
            'id_user_app' => $id_user_app,
            'creer' => $date_heure,
            'modifier' => $date_heure,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('tj_transaction', 'deduction_type')) {
            $userTxData['deduction_type'] = 'Cab Ride';
        }
        DB::table('tj_transaction')->insert($userTxData);

        // 4. Driver Wallet Credit (Net earnings: Base - Commission + Tip; Taxes kept by platform)
        $row_driver = DB::table('tj_conducteur')->select('amount', 'earn_amount')->where('id', $id_user)->first();
        $driverWallet = 0;
        $earnAmount = 0;
        if (!empty($row_driver)) {
            if ($row_driver->amount != '' && $row_driver->amount != null) {
                $driverWallet = floatval($row_driver->amount);
            }
            if (!empty($row_driver->earn_amount)) {
                $earnAmount = floatval($row_driver->earn_amount);
            }
            $driverWallet = round($driverWallet + $totalDriverAmount, 2);
            $driverUpdateData = ['amount' => $driverWallet];
            if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur', 'earn_amount')) {
                $driverUpdateData['earn_amount'] = strval(number_format($earnAmount + $baseFare, 2, '.', ''));
            }
            DB::table('tj_conducteur')->where('id', $id_user)->update($driverUpdateData);
        }

        $date = date('Y-m-d H:i:s');
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur_transaction')) {
            // Gross Fare Earning
            $driverTxData = [
                'amount'         => (string) $driverBaseAmount,
                'payment_method' => 'Wallet',
                'id_conducteur'  => $id_user,
                'id_ride'        => (string) $id_requete,
                'creer'          => $date,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'deduction_type')) {
                $driverTxData['deduction_type'] = 'Cab Ride';
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur_transaction', 'note')) {
                $driverTxData['note'] = 'Received payment via Wallet for Ride #' . $id_requete;
            }
            DB::table('tj_conducteur_transaction')->insert($driverTxData);

            // Commission Deduction
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

        $row_payment_method = DB::table('tj_payment_method')->select('id')->whereRaw('LOWER(libelle) = ?', [strtolower($paymethod ?: 'wallet')])->first();
        if ($row_payment_method) {
            $id_payment = $row_payment_method->id;
        } else {
            $default_pm = DB::table('tj_payment_method')->select('id')->first();
            $id_payment = $default_pm ? $default_pm->id : 1;
        }

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



            $sql = Requests::where('id', $id_requete)->first();

            $row = $sql->toarray();

            $row['id'] = (string)$row['id'];

            $row['tax'] = json_decode($row['tax'], true);

            $sql_user = UserApp::where('id', $id_user_app)->first();

            $row_user = $sql_user->toarray();

            $row_user['id'] = (string)$row_user['id'];



            $sql_driver = Driver::where('id', $id_user)->first();

            $row_driver = $sql_driver->toarray();

            $row_driver['id'] = (string)$row_driver['id'];



            $sql_payment = PaymentMethod::where('id', $id_payment)->first();

            $row_payment = $sql_payment->toarray();

            $row_payment['id'] = (string)$row_payment['id'];



            $row['payment_method'] = $row_payment['libelle'];

            $row['amount'] = $row_user['amount'];

            $row['amount_driver'] = $row_driver['amount'];

            $row['tax'] = $row['tax'];

            $row['discount'] = $row['discount'];



            $response['success'] = 'Success';

            $response['error'] = null;

            $response['data'] = $row;



            $tmsg = '';

            $terrormsg = '';



            $title = str_replace("'", "\'", "Payment of the ride");

            $msg = str_replace("'", "\'", "Your customer has just paid for his ride");



            $tab[] = array();

            $tab = explode("\\", $msg);

            $msg_ = "";

            for ($i = 0; $i < count($tab); $i++) {

                $msg_ = $msg_ . "" . $tab[$i];
            }



            $message = array("body" => $msg_, "title" => $title, "sound" => "mySound", "tag" => "ridecompleted");

            $fcm_token = DB::table('tj_conducteur')->where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');

            if (!empty($fcm_token)) {

                GcmController::sendNotification($fcm_token, $message);
            }



            $currencyData = DB::table('tj_currency')->select('*')->where('statut', 'yes')->first();

            $currency = $currencyData->symbole ? $currencyData->symbole : '$';



            // Get user info

            $query = DB::table('tj_requete')

                ->crossJoin('tj_user_app')

                ->select('tj_user_app.fcm_id', 'tj_user_app.id', 'tj_user_app.nom', 'tj_user_app.prenom', 'tj_user_app.email')

                ->where('tj_requete.id_user_app', '=', DB::raw('tj_user_app.id'))

                ->where('tj_requete.id', '=', $id_requete)

                ->get();



            // Get Ride Info

            $ride = DB::table('tj_requete')->select('distance', 'distance_unit', 'duree', 'montant', 'creer', 'trajet', 'discount', 'tax', 'tip_amount')->where('id', '=', $id_requete)->first();



            $distance = $ride->distance;

            $distance_unit = $ride->distance_unit;

            $duree = $ride->duree;

            $date_heure = $ride->creer;

            $img_name = $ride->trajet;



            $total = !empty($totalAmount) ? $totalAmount : 0;

            $subtotal = !empty($amount_new) ? number_format($amount_new, 2) : 0;

            $discount = !empty($discount) ? number_format($discount, 2) : 0;

            $tax = number_format($totalTaxAmount, 2);

            $tip_amount = !empty($tip) ? number_format($tip, 2) : 0;

            $total = number_format($total, 2);

            if ($currencyData->symbol_at_right == "true") {

                $total = $total . "" . $currency;

                $subtotal = $subtotal . "" . $currency;

                $discount = $discount . "" . $currency;

                $tip_amount = $tip_amount . "" . $currency;

                $tax = $tax . "" . $currency;
            } else {

                $total = $currency . "" . $total;

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

                foreach ($query as $user) {

                    if (!empty($user->fcm_id)) {

                        $tokens[] = $user->fcm_id;

                        $nom = $user->nom;

                        $prenom = $user->prenom;

                        $email = $user->email;
                    }
                }
            }



            if ($email != "") {

                $emailsubject = '';

                $emailmessage = '';

                $emailtemplate = DB::table('email_template')->select('*')->where('type', 'payment_receipt')->first();

                if (!empty($emailtemplate)) {

                    $emailsubject = $emailtemplate->subject;

                    $emailmessage = $emailtemplate->message;

                    $send_to_admin = $emailtemplate->send_to_admin;
                }



                $contact_us_email = DB::table('tj_settings')->select('contact_us_email')->value('contact_us_email');

                $contact_us_email = $contact_us_email ? $contact_us_email : 'none@none.com';





                $app_name = env('APP_NAME', 'Cabme');



                if ($send_to_admin == "true") {

                    $to = $email . "," . $contact_us_email;
                } else {

                    $to = $email;
                }



                $emailsubject = str_replace("{AppName}", $app_name, $emailsubject);



                $emailmessage = str_replace("{AppName}", $app_name, $emailmessage);

                $emailmessage = str_replace("{UserName}", $prenom . " " . $nom, $emailmessage);

                $emailmessage = str_replace("{Distance}", $distance . " " . $distance_unit, $emailmessage);

                $emailmessage = str_replace("{Duree}", $duree, $emailmessage);

                $emailmessage = str_replace('{Subtotal}', $subtotal, $emailmessage);

                $emailmessage = str_replace('{Discount}', $discount, $emailmessage);

                $emailmessage = str_replace('{Tax}', $taxHtml, $emailmessage);

                $emailmessage = str_replace('{Tip}', $tip_amount, $emailmessage);

                $emailmessage = str_replace('{Total}', $total, $emailmessage);

                $emailmessage = str_replace('{Date}', $date, $emailmessage);



                // Always set content-type when sending HTML email

                $headers = "MIME-Version: 1.0" . "\r\n";

                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                $headers .= 'From: ' . $app_name . '<' . $contact_us_email . '>' . "\r\n";

                mail($to, $emailsubject, $emailmessage, $headers);
            }
        } else {

            $response['success'] = 'Failed';

            $response['error'] = 'Failed';
        }



        return response()->json($response);
    }
}

<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\Controller;

use App\Models\Commission;
use App\Models\Driver;
use App\Models\ParcelOrder;

use DB;

use Illuminate\Http\Request;



class PaymentByCashParcelController extends Controller

{



    public function __construct()

    {

        $this->limit = 20;

    }



    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */





    public function UpdatePayment(Request $request)

    {



        $currencyData = DB::table('tj_currency')->select('*')->where('statut', 'yes')->first();

        $currency = $currencyData->symbole ? $currencyData->symbole : '$';



        $id_requete = $request->get('id_parcel');

        $id_user = $request->get('id_driver');

        $amount_new = floatval($request->get('amount'));

        $paymethod = $request->get('paymethod');

        $date_heure = date('Y-m-d H:i:s');

        $discount = floatval($request->get('discount'));

        $tax = $request->get('tax');

        $tax_json = json_encode($tax);

        $tip = floatval($request->get('tip'));

        $transaction_id = $request->get('transaction_id');

        $totalamount = floatval($amount_new);



        if (!empty($discount)) {
            $totalamount = floatval($totalamount) - floatval($discount);
        }

        // 1. Resolve Admin Commission directly from tj_commission active setting
        $commission_amount = 0;
        $admin_commisions = Commission::where('statut', 'yes')->first();
        if (!$admin_commisions) {
            $admin_commisions = Commission::first();
        }

        if (!empty($admin_commisions)) {
            $commType = strtolower(trim((string) ($admin_commisions->type ?? 'percentage')));
            $commVal = floatval($admin_commisions->value ?? 0);
            if ($commType == 'percentage' || $commType == 'percent') {
                $commission_amount = round(($commVal * floatval($totalamount)) / 100, 2);
            } else {
                $commission_amount = round($commVal, 2);
            }
        }

        // 2. Resolve GST / Active Taxes from tj_tax if not passed from client
        if ((empty($tax) || !is_array($tax) || count($tax) === 0) && \Illuminate\Support\Facades\Schema::hasTable('tj_tax')) {
            $dbTaxes = DB::table('tj_tax')->where('statut', 'yes')->get();
            $tax = [];
            foreach ($dbTaxes as $t) {
                $methods = !empty($t->applicable_on) ? explode(',', strtolower($t->applicable_on)) : ['cash', 'upi', 'wallet', 'online'];
                if (in_array(strtolower($paymethod), $methods) || in_array('all', $methods)) {
                    $tax[] = [
                        'libelle' => $t->libelle,
                        'value' => (string) $t->value,
                        'type' => $t->type,
                    ];
                }
            }
        }

        $tax_json = json_encode($tax);
        $totalTaxAmount = 0;
        $taxHtml = '';

        if (!empty($tax)) {
            for ($i = 0; $i < sizeof($tax); $i++) {
                $data = $tax[$i];
                if (strtolower($data['type']) == "percentage") {
                    $taxValue = (floatval($data['value']) * $totalamount) / 100;
                    $taxlabel = $data['libelle'];
                    $value = $data['value'] . "%";
                } else {
                    $taxValue = floatval($data['value']);
                    $taxlabel = $data['libelle'];
                    $decDigit = $currencyData ? intval($currencyData->decimal_digit ?? 2) : 2;
                    $value = $currency . "" . number_format(floatval($data['value']), $decDigit);
                }

                $decDigit = $currencyData ? intval($currencyData->decimal_digit ?? 2) : 2;
                $totalTaxAmount += floatval(number_format($taxValue, $decDigit, '.', ''));
                $taxValueAmount = $currency . "" . number_format($taxValue, $decDigit);
                $taxHtml .= "<p><b>" . $taxlabel . "(" . $value . "): </b>" . $taxValueAmount . "</p>";
            }
        }

        if ($taxHtml == '') {
            $taxHtml = "0";
        }

        if (!empty($totalTaxAmount)) {
            $totalamount = $totalamount + $totalTaxAmount;
        }

        if (!empty($tip)) {
            $totalamount = $totalamount + $tip;
        }

        $totalDriverAmount = max(0, floatval($totalamount) - floatval($commission_amount));

        // 3. User Wallet Deduction if paid via Wallet
        $id_user_app = $request->get('id_user_app');
        if (strtolower($paymethod) == 'wallet' && !empty($id_user_app)) {
            $userRow = DB::table('tj_user_app')->where('id', $id_user_app)->first();
            if ($userRow && $userRow->amount !== null) {
                $userBal = floatval($userRow->amount);
                $newUserBal = max(0, $userBal - $totalamount);
                DB::table('tj_user_app')->where('id', $id_user_app)->update(['amount' => $newUserBal, 'modifier' => $date_heure]);
                DB::table('tj_transaction')->insert([
                    'amount' => '-' . $totalamount,
                    'deduction_type' => 1,
                    'payment_method' => 'Wallet',
                    'id_user_app' => $id_user_app,
                    'creer' => $date_heure,
                    'modifier' => $date_heure
                ]);
            }
        }

        // 4. Driver Wallet Accounting
        $sql_driver = DB::table('tj_conducteur')
            ->select('amount')
            ->where('id', '=', $id_user)
            ->first();

        $walletAmount = $sql_driver && $sql_driver->amount !== null ? floatval($sql_driver->amount) : 0;

        if (strtolower($paymethod) == 'cash') {
            // Cash collected by driver in hand: platform commission + GST/Taxes debited from driver wallet
            $totalPlatformDeduction = floatval($commission_amount) + floatval($totalTaxAmount);
            $newWalletAmount = $walletAmount - $totalPlatformDeduction;
        } else {
            // Online / UPI / Wallet: net earnings credited to driver wallet
            $newWalletAmount = $walletAmount + $totalDriverAmount;
        }

        DB::table('tj_conducteur')->where('id', '=', $id_user)->update(['amount' => $newWalletAmount]);

        $date = date('Y-m-d H:i:s');

        if (!empty($commission_amount)) {
            DB::table('tj_conducteur_transaction')->insert([
                'id_conducteur' => $id_user,
                'amount' => "-" . $commission_amount,
                'payment_method' => 'Commission',
                'id_ride' => $id_requete,
                'creer' => $date
            ]);
        }

        if (strtolower($paymethod) == 'cash' && !empty($totalTaxAmount)) {
            DB::table('tj_conducteur_transaction')->insert([
                'id_conducteur' => $id_user,
                'amount' => "-" . $totalTaxAmount,
                'payment_method' => 'Tax/GST',
                'id_ride' => $id_requete,
                'creer' => $date
            ]);
        }

        $driverBaseAmount = floatval($amount_new) - floatval($discount) + floatval($tip);
        if (!empty($driverBaseAmount)) {
            DB::table('tj_conducteur_transaction')->insert([
                'id_conducteur' => $id_user,
                'amount' => $driverBaseAmount,
                'payment_method' => $paymethod,
                'id_ride' => $id_requete,
                'creer' => $date
            ]);
        }


        $row_payment_method = DB::table('tj_payment_method')->select('id')->where('libelle', $paymethod)->first();

        if ($row_payment_method) {

            $id_payment = $row_payment_method->id;

        } else {

            $response['success'] = 'Failed';

            $response['error'] = 'Payment method not found';

            return response()->json($response);

        }

        if (!empty($id_requete)) {



            $updatedata = DB::update('update parcel_orders set payment_status = ?,id_payment_method = ?,tip = ?,tax = ?,discount = ?,admin_commission = ? where id = ?', ['yes', $id_payment, $tip, $tax_json, $discount, $commission_amount, $id_requete]);



            if ($updatedata > 0) {

                $sql = ParcelOrder::where('id', $id_requete)->first();

                $row = $sql->toArray();

                $row['id'] = (string)$row['id'];

                $row['tax'] = json_decode($row['tax'], true);



                $image_parcel = [];

                if ($row['parcel_image'] != '') {

                    $parcelImage = json_decode($row['parcel_image'], true);



                    foreach ($parcelImage as $value) {

                        if (file_exists(public_path('images/parcel_order/' . '/' . $value))) {

                            $image = asset('images/parcel_order/') . '/' . $value;

                        }

                        array_push($image_parcel, $image);

                    }

                    if (!empty($image_parcel)) {

                        $row['parcel_image'] = $image_parcel;

                    } else {

                        $row['parcel_image'] = asset('assets/images/placeholder_image.jpg');

                    }

                }





                // Get user info

                $query = DB::table('parcel_orders')

                    ->crossJoin('tj_user_app')

                    ->select('tj_user_app.fcm_id', 'tj_user_app.id', 'tj_user_app.nom', 'tj_user_app.prenom', 'tj_user_app.email')

                    ->where('parcel_orders.id_user_app', '=', DB::raw('tj_user_app.id'))

                    ->where('parcel_orders.id', '=', $id_requete)

                    ->get();



                // Get Ride Info

                $query_ride = DB::table('parcel_orders')

                    ->select('distance', 'distance_unit')

                    ->where('id', '=', $id_requete)

                    ->first();

                $distance = $query_ride->distance;

                $distance_unit = $query_ride->distance_unit;



                $total = !empty($totalamount) ? $totalamount : 0;

                $subtotal = !empty($amount_new) ? number_format($amount_new, 2) : 0;

                $discount = !empty($discount) ? number_format($discount, 2) : 0;

                $tax = number_format($totalTaxAmount, 2);

                $tip = !empty($tip) ? number_format($tip, 2) : 0;

                $total = number_format($total, 2);

                if ($currencyData->symbol_at_right == "true") {

                    $total = $total . "" . $currency;

                    $subtotal = $subtotal . "" . $currency;

                    $discount = $discount . "" . $currency;

                    $tip = $tip . "" . $currency;

                    $tax = $tax . "" . $currency;



                } else {

                    $total = $currency . "" . $total;

                    $subtotal = $currency . "" . $subtotal;

                    $discount = $currency . "" . $discount;

                    $tip = $currency . "" . $tip;

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

                    $date = date('d F Y', strtotime($date_heure));



                    $duree = ($row['duration']) ? $row['duration'] : "";

                    $emailsubject = str_replace("{AppName}", $app_name, $emailsubject);



                    $emailmessage = str_replace("{AppName}", $app_name, $emailmessage);

                    $emailmessage = str_replace("{UserName}", $prenom . " " . $nom, $emailmessage);

                    $emailmessage = str_replace("{Distance}", $distance . " " . $distance_unit, $emailmessage);

                    $emailmessage = str_replace("{Duree}", $duree, $emailmessage);

                    $emailmessage = str_replace('{Subtotal}', $subtotal, $emailmessage);

                    $emailmessage = str_replace('{Discount}', $discount, $emailmessage);

                    $emailmessage = str_replace('{Tax}', $taxHtml, $emailmessage);

                    $emailmessage = str_replace('{Tip}', $tip, $emailmessage);

                    $emailmessage = str_replace('{Total}', $total, $emailmessage);

                    $emailmessage = str_replace('{Date}', $date, $emailmessage);

                    

                    // Always set content-type when sending HTML email

                    $headers = "MIME-Version: 1.0" . "\r\n";

                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                    $headers .= 'From: ' . $app_name . '<' . $contact_us_email . '>' . "\r\n";

                    mail($to, $emailsubject, $emailmessage, $headers);

                }



                $response['success'] = 'success';

                $response['error'] = null;

                $response['message'] = 'status successfully updated';

                $response['data'] = $row;

            } else {

                $response['success'] = 'Failed';

                $response['error'] = 'Failed to update data';

            }

        } else {

            $response['success'] = 'Failed';

            $response['error'] = 'ID is requiered';

        }

        return response()->json($response);

    }

}


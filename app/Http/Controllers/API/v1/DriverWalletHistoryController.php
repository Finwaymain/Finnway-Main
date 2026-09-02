<?php



namespace App\Http\Controllers\API\v1;



use App\Http\Controllers\Controller;

use App\Models\Requests;

use App\Models\UserApp;

use App\Models\Note;

use Illuminate\Http\Request;

use DB;

class DriverWalletHistoryController extends Controller

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

    $months = array ("January"=>'Jan',"February"=>'Feb',"March"=>'Mar',"April"=>'Apr',"May"=>'May',"June"=>'Jun',"July"=>'Jul',"August"=>'Aug',"September"=>'Sep',"October"=>'Oct',"November"=>'Nov',"December"=>'Dec');

    $daily_ride= [];

    $monthly_ride=[];

    $yearly_ride=[];

    $weekly_ride=[];

    $id_diver =$request->get('id_diver');

    $date_start = date('Y-m-d 00:00:00');

    $date_end = date('Y-m-d 23:59:59');

    $date_before_week = date('Y-m-d 00:00:00', strtotime('-7 days'));

    $month = date('m');

    $year = date('Y');

    $output=[];

    $wallet=[];

    if(!empty($id_diver)){



    $rideEarnings = DB::table('tj_requete')
        ->where('id_conducteur', $id_diver)
        ->where('statut', 'completed')
        ->sum('montant');

    $parcelEarnings = DB::table('parcel_orders')
        ->where('id_conducteur', $id_diver)
        ->where('status', 'completed')
        ->sum('amount');

    $serviceEarnings = 0;
    if (\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
        $serviceEarnings = DB::table('service_requests')
            ->where('driver_id', $id_diver)
            ->whereIn('status', ['Completed', 'completed'])
            ->sum('amount');
    }

    $total_earning = strval(round(floatval($rideEarnings) + floatval($parcelEarnings) + floatval($serviceEarnings), 2));

    $sql = DB::table('tj_requete')
    ->leftJoin('tj_conducteur_transaction', function($join) {
        $join->on('tj_conducteur_transaction.id_ride', '=', 'tj_requete.id')
             ->where('tj_conducteur_transaction.payment_method', '!=', 'Commission');
    })
    ->leftJoin('tj_payment_method', 'tj_requete.id_payment_method', '=', 'tj_payment_method.id')
    ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'tj_requete.id_user_app')
    ->leftJoin('tj_conducteur', 'tj_conducteur.id', '=', 'tj_requete.id_conducteur')
    ->select(       'tj_requete.id',
                    'tj_requete.id_user_app',
                    'tj_requete.distance_unit',
                    'tj_requete.depart_name',
                    'tj_requete.destination_name',
                    'tj_requete.otp',
                    'tj_requete.latitude_depart',
                    'tj_requete.longitude_depart',
                    'tj_requete.latitude_arrivee',
                    'tj_requete.longitude_arrivee',
                    'tj_requete.number_poeple',
                    'tj_requete.place',
                    'tj_requete.statut',
                    'tj_requete.id_conducteur',
                    'tj_requete.creer',
                    'tj_requete.trajet',
                    'tj_requete.feel_safe_driver',
                    'tj_user_app.nom',
                    'tj_user_app.prenom',
                    'tj_user_app.id as existing_user_id',
                    'tj_requete.distance',
                    'tj_requete.ride_type',
                    'tj_user_app.phone',
                    'tj_user_app.photo_path',
                    'tj_conducteur.nom as nomConducteur',
                    'tj_conducteur.prenom as prenomConducteur',
                    'tj_conducteur.phone as driverPhone',
                    'tj_requete.date_retour',
                    'tj_requete.heure_retour',
                    'tj_requete.statut_round',
                    'tj_requete.montant',
                    'tj_requete.duree',
                    'tj_user_app.id as userId',
                    'tj_requete.statut_paiement',
                    'tj_payment_method.libelle as payment',
                    'tj_payment_method.image as payment_image',
                    'tj_requete.trip_objective',
                    'tj_requete.age_children1',
                    'tj_requete.age_children2',
                    'tj_requete.age_children3',
                    'tj_requete.stops',
                    'tj_requete.tax',
                    'tj_requete.tip_amount',
                    'tj_requete.discount',
                    'tj_requete.admin_commission',
                    'tj_requete.user_info',
                    DB::raw('COALESCE(tj_conducteur_transaction.amount, tj_requete.montant) as amount'))
    ->where('tj_requete.statut', '=', 'completed')
    ->where('tj_requete.id_conducteur', '=', $id_diver)
    ->orderBy('tj_requete.creer', 'desc')
    ->get();



    foreach($sql as $row)

    {

        $row->userId = (string)$row->userId;

        $row->discount = $row->discount;

        $row->tip_amount = $row->tip_amount;

        $row->tax = json_decode($row->tax,true);

        $row->montant = $row->montant;

        $row->amount = (string)$row->amount;

        $row->destination_name = $row->destination_name;

        $row->depart_name = $row->depart_name;

        $row->user_info = json_decode($row->user_info, true);

        $row->stops = json_decode($row->stops, true);

        if($row->ride_type==null || $row->ride_type==""){

                    $row->ride_type = "normal";

        }

        $row->raw_date = $row->creer;
        $row->creer = date("d", strtotime($row->creer))." ".$months[date("F", strtotime($row->creer))].", ".date("Y", strtotime($row->creer));

        $row->date_retour = date("d", strtotime($row->date_retour))." ".$months[date("F", strtotime($row->date_retour))].", ".date("Y", strtotime($row->date_retour));



        $row->id=(string)$row->id;

        // Nb confirmed

                if ($row->photo_path != '') {

                    if (file_exists(public_path('assets/images/users' . '/' . $row->photo_path))) {

                        $image_user = asset('assets/images/users') . '/' . $row->photo_path;

                    } else {

                        $image_user = asset('assets/images/placeholder_image.jpg');

                    }

                    $row->photo_path = $image_user;

                }

                $moyenne_driver = 0;



                if(!empty($row->existing_user_id)){

                    $sql_nb_avis_driver = DB::table('tj_user_note')

                        ->select(DB::raw("COUNT(id) as nb_avis_driver"), DB::raw("SUM(niveau_driver) as somme_driver"))

                        ->where('id_user_app', '=', $row->existing_user_id)

                        ->get();

                    if (!empty($sql_nb_avis_driver)) {

                        foreach ($sql_nb_avis_driver as $row_nb_avis_driver) {
                            $somme_driver = $row_nb_avis_driver->somme_driver;
                            $nb_avis_driver = $row_nb_avis_driver->nb_avis_driver;
                            if ($nb_avis_driver != 0) {
                                $moyenne_driver = $somme_driver / $nb_avis_driver;
                            }
                        }
                    }

                }

                $sql_nb_avis = DB::table('tj_note')

                    ->select(DB::raw("COUNT(id) as nb_avis"), DB::raw("SUM(niveau) as somme"))

                    ->where('id_conducteur', '=', $row->id_conducteur)

                    ->get();



                $moyenne = 0;
                if (!empty($sql_nb_avis)) {

                    foreach ($sql_nb_avis as $row_nb_avis) {
                        $somme = $row_nb_avis->somme;
                        $nb_avis = $row_nb_avis->nb_avis;
                        if ($nb_avis != 0) {
                            $moyenne = $somme / $nb_avis;
                        }
                    }

                }

                $row->moyenne_driver = (string)$moyenne_driver;

                $row->moyenne = (string)$moyenne;

                $row->order_type = 'ride';

                $row->existing_user_id = (string)$row->existing_user_id;

                $output[] = $row;



    



    }

    $parcelOrder = DB::table('parcel_orders')
    ->leftJoin('tj_conducteur_transaction', function($join) {
        $join->on('tj_conducteur_transaction.id_parcel', '=', 'parcel_orders.id')
             ->where('tj_conducteur_transaction.payment_method', '!=', 'Commission');
    })
    ->leftJoin('tj_payment_method', 'parcel_orders.id_payment_method', '=', 'tj_payment_method.id')
    ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'parcel_orders.id_user_app')
    ->leftJoin('tj_conducteur', 'tj_conducteur.id', '=', 'parcel_orders.id_conducteur')
    ->select(       'parcel_orders.*',
                    'tj_user_app.nom',
                    'tj_user_app.prenom',
                    'tj_user_app.phone',
                    'tj_user_app.photo_path',
                    'tj_conducteur.nom as nomConducteur',
                    'tj_conducteur.prenom as prenomConducteur',
                    'tj_conducteur.phone as driverPhone',
                    'tj_user_app.id as userId',
                    'tj_payment_method.libelle as payment',
                    'tj_payment_method.image as payment_image',
                    DB::raw('COALESCE(tj_conducteur_transaction.amount, parcel_orders.amount) as transactionAmount'))
    ->where('parcel_orders.status', '=', 'completed')
    ->where('parcel_orders.id_conducteur', '=', $id_diver)
    ->orderBy('parcel_orders.created_at', 'desc')
    ->get();



    if(!empty($parcelOrder)){

        foreach($parcelOrder as $po){

        $po->id=(string)$po->id;

        $po->userId = (string)$po->userId;

        $po->discount = $po->discount;

        $po->tip = $po->tip;

        $po->tax = json_decode($po->tax,true);

        $po->amount = $po->amount;

        $po->transactionAmount = (string)$po->transactionAmount;

        $po->destination = $po->destination;

        $po->source = $po->source;

        $po->order_type = 'parcel';

        $po->raw_date = $po->created_at;
        $po->created_at = date("d", strtotime($po->created_at))." ".$months[date("F", strtotime($po->created_at))].", ".date("Y", strtotime($po->created_at));

        $po->creer = $po->created_at;
        // Nb confirmed

                if ($po->photo_path != '') {

                    if (file_exists(public_path('assets/images/users' . '/' . $po->photo_path))) {

                        $image_user = asset('assets/images/users') . '/' . $po->photo_path;

                    } else {

                        $image_user = asset('assets/images/placeholder_image.jpg');

                    }

                    $po->photo_path = $image_user;

                }

                $output[] = $po;

        }

    }

    // ── 3. Completed Home Services (Service Requests) ───────────────────────
    if (\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
        $serviceBookings = DB::table('service_requests')
            ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'service_requests.user_id')
            ->leftJoin('tj_conducteur', 'tj_conducteur.id', '=', 'service_requests.driver_id')
            ->select(
                'service_requests.*',
                'tj_user_app.nom',
                'tj_user_app.prenom',
                'tj_user_app.phone',
                'tj_user_app.photo_path',
                'tj_conducteur.nom as nomConducteur',
                'tj_conducteur.prenom as prenomConducteur',
                'tj_conducteur.phone as driverPhone',
                'tj_user_app.id as userId'
            )
            ->where('service_requests.driver_id', '=', $id_diver)
            ->whereIn('service_requests.status', ['Completed', 'completed'])
            ->orderBy('service_requests.updated_at', 'desc')
            ->get();

        foreach ($serviceBookings as $sb) {
            $serviceEarnAmount = (string) round(floatval($sb->amount ?: 0), 2);
            $sbItem = new \stdClass();
            $sbItem->id                 = 'SR-' . $sb->id;
            $sbItem->service_request_id = (string) $sb->id;
            $sbItem->userId             = (string) ($sb->userId ?? '');
            $sbItem->nom                = (string) ($sb->nom ?? 'Customer');
            $sbItem->prenom             = (string) ($sb->prenom ?? '');
            $sbItem->phone              = (string) ($sb->phone ?? '');
            $sbItem->driverPhone        = (string) ($sb->driverPhone ?? '');
            $sbItem->nomConducteur      = (string) ($sb->nomConducteur ?? '');
            $sbItem->prenomConducteur   = (string) ($sb->prenomConducteur ?? '');
            $sbItem->amount             = $serviceEarnAmount;
            $sbItem->montant            = $serviceEarnAmount;
            $sbItem->transactionAmount   = $serviceEarnAmount;
            $sbItem->depart_name        = (string) ($sb->service_name ?? 'Home Service');
            $sbItem->destination_name   = (string) ($sb->service_address ?? 'Customer Location');
            $sbItem->statut             = 'completed';
            $sbItem->statut_paiement    = (string) ($sb->payment_status ?? 'paid');
            $sbItem->payment            = !empty($sb->payment_method) ? strtoupper($sb->payment_method) : ($sb->payment_status === 'paid_cash' ? 'Cash' : 'Online');
            $sbItem->order_type         = 'service';
            $sbItem->libelle            = (string) ($sb->service_name ?? 'Home Service');

            $dateCol = $sb->updated_at ?? $sb->created_at ?? date('Y-m-d H:i:s');
            $sbItem->raw_date           = $dateCol;
            $sbItem->creer              = date("d", strtotime($dateCol)) . " " . ($months[date("F", strtotime($dateCol))] ?? date("M", strtotime($dateCol))) . ", " . date("Y", strtotime($dateCol));
            $sbItem->created_at         = $sbItem->creer;

            if (!empty($sb->photo_path)) {
                $sbItem->photo_path = file_exists(public_path('assets/images/users/' . $sb->photo_path))
                    ? asset('assets/images/users/' . $sb->photo_path)
                    : asset('assets/images/placeholder_image.jpg');
            } else {
                $sbItem->photo_path = asset('assets/images/placeholder_image.jpg');
            }

            $output[] = $sbItem;
        }
    }

    $allDriverTxns = DB::table('tj_conducteur_transaction')
        ->where('id_conducteur', '=', $id_diver)
        ->orderBy('creer', 'desc')
        ->get();

    $existingTxnIds = array_map(function($item) {
        return (string) ($item->id ?? '');
    }, $output);

    foreach ($allDriverTxns as $wt) {
        $txnIdStr = (string) $wt->id;
        if (in_array($txnIdStr, $existingTxnIds, true)) {
            continue;
        }

        $wt->id                  = $txnIdStr;
        $wt->amount              = (string) $wt->amount;
        $wt->id_payment_method   = "";
        $wt->libelle             = (string) ($wt->payment_method ?? 'Wallet Transaction');
        $rawCreer                = $wt->creer ?? null;
        $wt->raw_date            = $rawCreer;
        $wt->creer               = !empty($rawCreer) ? date("d", strtotime($rawCreer)) . " " . ($months[date("F", strtotime($rawCreer))] ?? date("M", strtotime($rawCreer))) . ", " . date("Y", strtotime($rawCreer)) : "";
        $wt->user_name           = "";
        $wt->user_photo          = "";
        $wt->user_photo_path     = "";
        $wt->destination_name    = "";
        $wt->depart_name         = "";
        $wt->id_user_app         = "";
        $wt->admin_commission    = "";
        $wt->discount            = "";
        $wt->tip_amount          = "";
        $wt->tax                 = "";
        $wt->montant             = $wt->amount;

        $note = (string) ($wt->note ?? '');
        $desc = (string) ($wt->description ?? '');
        $paymentMethod = (string) ($wt->payment_method ?? '');
        $dedType = (string) ($wt->deduction_type ?? '');

        if ($paymentMethod === 'Commission' || stripos($note, 'commission') !== false || stripos($desc, 'commission') !== false) {
            $wt->order_type       = 'commission';
            $wt->depart_name      = 'Admin Commission Deduction';
            $wt->destination_name = 'Admin Panel';
            $wt->libelle          = 'Admin Commission';
        } elseif (stripos($desc, 'marketplace') !== false || stripos($note, 'marketplace') !== false || stripos($paymentMethod, 'marketplace') !== false) {
            $wt->order_type       = 'marketplace';
            $wt->depart_name      = 'Marketplace Sale Earnings';
            $wt->destination_name = 'Wallet';
            $wt->libelle          = 'Marketplace Sale';
        } elseif (stripos($desc, 'withdraw') !== false || stripos($note, 'withdraw') !== false || stripos($desc, 'payout') !== false) {
            $wt->order_type       = 'withdraw';
            $wt->depart_name      = 'Bank Withdrawal';
            $wt->destination_name = 'Linked Bank Account';
            $wt->libelle          = 'Bank Withdrawal';
        } elseif ($dedType === '1' || stripos($desc, 'top-up') !== false || stripos($desc, 'topup') !== false || stripos($note, 'topup') !== false || stripos($note, 'top-up') !== false || stripos($desc, 'recharge') !== false) {
            $wt->order_type       = 'topup';
            $wt->depart_name      = 'Wallet Top-Up';
            $wt->destination_name = 'Wallet';
            $wt->libelle          = 'Wallet Top-Up';
        } elseif ($paymentMethod === 'Tax/GST' || stripos($note, 'tax') !== false || stripos($paymentMethod, 'tax') !== false || stripos($paymentMethod, 'gst') !== false) {
            $wt->order_type       = 'tax';
            $wt->depart_name      = 'Tax & Charges Deduction';
            $wt->destination_name = 'Taxes & Fees';
            $wt->libelle          = 'GST & Taxes';
        } elseif (!empty($wt->id_ride) && DB::table('service_requests')->where('id', $wt->id_ride)->exists()) {
            $svc                  = DB::table('service_requests')->where('id', $wt->id_ride)->first();
            $wt->order_type       = 'service';
            $wt->depart_name      = !empty($svc->service_name) ? $svc->service_name : 'Home Service';
            $wt->destination_name = $svc->service_address ?? 'Home Service Customer';
            $wt->libelle          = 'Home Service';
            if (!empty($svc->user_id)) {
                $usr = DB::table('tj_user_app')->where('id', $svc->user_id)->first();
                if ($usr) {
                    $wt->user_name = trim(($usr->nom ?? '') . ' ' . ($usr->prenom ?? ''));
                }
            }
        } else {
            $wt->order_type       = 'wallet';
            $wt->depart_name      = $wt->libelle;
            $wt->destination_name = 'Wallet';
        }

        $output[] = $wt;
    }

    usort($output, function($a, $b) {
        $timeA = isset($a->raw_date) ? strtotime($a->raw_date) : 0;
        $timeB = isset($b->raw_date) ? strtotime($b->raw_date) : 0;
        return $timeB - $timeA;
    });

    if(!empty($output)){

        $response['success'] = 'success';

        $response['error'] = null;

        $response['message'] = 'Successfully';

        $response['data'] = $output;

        $response['total_earnings'] = $total_earning;





    }else{
        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'No Data Found';
        $response['data'] = [];
        $response['total_earnings'] = $total_earning;
    }

}else{

    $response['success'] = 'Failed';

    $response['error'] = 'Id is required';



}

        return response()->json($response);



    }







}


<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Requests;
use App\Models\UserApp;
use App\Models\Vehicle;
use App\Models\ParcelOrder;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $date_start = date('Y-m-d 00:00:00');
        $date_end = date('Y-m-d 23:59:59');

        $currency = Currency::where('statut', 'yes')->first();

        $total_users = UserApp::count();
        $total_drivers = Driver::leftJoin('tj_vehicule', 'tj_vehicule.id_conducteur', '=', 'tj_conducteur.id')
        ->leftJoin('tj_type_vehicule', 'tj_type_vehicule.id', '=', 'tj_vehicule.id_type_vehicule')->count();

        $today_users = UserApp::whereBetween('creer', [$date_start, $date_end])->count('id');
        $today_drivers = Driver::join('tj_vehicule', 'tj_vehicule.id_conducteur', '=', 'tj_conducteur.id')
        ->join('tj_type_vehicule', 'tj_type_vehicule.id', '=', 'tj_vehicule.id_type_vehicule')
        ->whereBetween('tj_conducteur.creer', [$date_start, $date_end])->count();
        
        $new_rides = Requests::where('statut', 'new')->count('id');
        $on_rides = Requests::where('statut', 'on ride')->count('id');

        $confirmed_rides = Requests::leftjoin('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
        ->join('tj_conducteur', 'tj_requete.id_conducteur', '=', 'tj_conducteur.id')
        ->join('tj_payment_method', 'tj_requete.id_payment_method', '=', 'tj_payment_method.id')
        ->where('tj_requete.statut', 'confirmed')
        ->where('tj_requete.deleted_at', '=', NULL)
        ->count('tj_requete.id');

        $confirmed_parcel_rides = ParcelOrder::leftjoin('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')
        ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')
        ->join('tj_payment_method', 'parcel_orders.id_payment_method', '=', 'tj_payment_method.id')
        ->where('parcel_orders.status', 'confirmed')
        ->count('parcel_orders.id');


        $today_confirmed_rides = Requests::leftjoin('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
        ->join('tj_conducteur', 'tj_requete.id_conducteur', '=', 'tj_conducteur.id')
        ->join('tj_payment_method', 'tj_requete.id_payment_method', '=', 'tj_payment_method.id')
        ->where('tj_requete.deleted_at', '=', NULL)
        ->where('tj_requete.statut', 'confirmed')->whereBetween('tj_requete.creer', [$date_start, $date_end])->count('tj_requete.id');

        $today_parcel_confirmed_rides = ParcelOrder::leftjoin('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')
        ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')
        ->join('tj_payment_method', 'parcel_orders.id_payment_method', '=', 'tj_payment_method.id')
        ->where('parcel_orders.status', 'confirmed')->whereBetween('parcel_orders.created_at', [$date_start, $date_end])->count('parcel_orders.id');


        $completed_rides = Requests::leftjoin('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
        ->join('tj_conducteur', 'tj_requete.id_conducteur', '=', 'tj_conducteur.id')
        ->join('tj_payment_method', 'tj_requete.id_payment_method', '=', 'tj_payment_method.id')
        ->where('tj_requete.deleted_at', '=', NULL)
        ->where('tj_requete.statut', 'completed')->count('tj_requete.id');

        $completed_parcel_rides = ParcelOrder::leftjoin('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')
        ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')
        ->join('tj_payment_method', 'parcel_orders.id_payment_method', '=', 'tj_payment_method.id')
        ->where('parcel_orders.status', 'completed')->count('parcel_orders.id');


        $today_completed_rides = Requests::leftjoin('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
        ->join('tj_conducteur', 'tj_requete.id_conducteur', '=', 'tj_conducteur.id')
        ->join('tj_payment_method', 'tj_requete.id_payment_method', '=', 'tj_payment_method.id')
        ->where('tj_requete.deleted_at', '=', NULL)
        ->where('tj_requete.statut', 'completed')->whereBetween('tj_requete.creer', [$date_start, $date_end])->count('tj_requete.id');

        $today_parcel_completed_rides = ParcelOrder::leftjoin('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')
        ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')
        ->join('tj_payment_method', 'parcel_orders.id_payment_method', '=', 'tj_payment_method.id')
        ->where('parcel_orders.status', 'completed')->whereBetween('parcel_orders.created_at', [$date_start, $date_end])->count('parcel_orders.id');

        $canceled_rides = Requests::leftjoin('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
          ->join('tj_conducteur', 'tj_requete.id_conducteur', '=', 'tj_conducteur.id')
          ->join('tj_payment_method', 'tj_requete.id_payment_method', '=', 'tj_payment_method.id')
          ->where('tj_requete.statut', 'canceled')
          ->orwhere('tj_requete.statut', 'rejected')
          ->where('tj_requete.deleted_at', '=', NULL)->count('tj_requete.id');

        $canceled_parcel_rides = ParcelOrder::leftjoin('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')
          ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')
          ->join('tj_payment_method', 'parcel_orders.id_payment_method', '=', 'tj_payment_method.id')
          ->where('parcel_orders.status', 'canceled')
          ->orwhere('parcel_orders.status', 'rejected')
          ->count('parcel_orders.id');


        $today_canceled_rides = Requests::leftjoin('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
          ->join('tj_conducteur', 'tj_requete.id_conducteur', '=', 'tj_conducteur.id')
          ->join('tj_payment_method', 'tj_requete.id_payment_method', '=', 'tj_payment_method.id')
          ->where('tj_requete.deleted_at', '=', NULL)
          ->whereIn('tj_requete.statut', ['canceled','rejected'])->whereBetween('tj_requete.creer', [$date_start, $date_end])->count('tj_requete.id');

          $today_parcel_canceled_rides = ParcelOrder::leftjoin('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')
          ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')
          ->join('tj_payment_method', 'parcel_orders.id_payment_method', '=', 'tj_payment_method.id')
          ->whereIn('parcel_orders.status', ['canceled','rejected'])->whereBetween('parcel_orders.created_at', [$date_start, $date_end])->count('parcel_orders.id');

        
        $total_admin_commission = Requests::where('statut', 'completed')->sum('admin_commission');
        $total_parcel_admin_commission = ParcelOrder::where('status', 'completed')->sum('admin_commission');

        $today_admin_commission = Requests::where('statut', 'completed')->whereBetween('creer', [$date_start, $date_end])->sum('admin_commission');
        $today_parcel_admin_commission = ParcelOrder::where('status', 'completed')->whereBetween('created_at', [$date_start, $date_end])->sum('admin_commission');

        $saletoday = Requests::where('statut', 'completed')->whereBetween('creer', [$date_start, $date_end])->count('id');
        $commitionfortoday = Commission::where('statut', 'yes')->whereBetween('creer', [$date_start, $date_end])->sum('value');

        $day = date('w');
        $week_start = date('Y-m-d', strtotime('-' . $day . ' days'));
        $week_end = date('Y-m-d', strtotime('+' . (6 - $day) . ' days'));
        $week_start = date('Y-m-d', strtotime($week_start . ' +1 day'));
        $week_end = date('Y-m-d', strtotime($week_end . ' +1 day'));
        $commitionforweek = Commission::where('statut', 'yes')->whereBetween('creer', [$week_start, $week_end])->sum('value');

        $date_heure = date('Y-m-d');
        $date_start = date("Y-m-d", strtotime(date('Y-m-1')));
        $date_end = date("Y-m-t", strtotime($date_heure));
        $commitionformonth = Commission::where('statut', 'yes')->whereBetween('creer', [$date_start, $date_end])->sum('value');

        $drivers = Driver::where('statut', '=', 'no')->get();
        $active_drivers = Driver::where('statut', '=', 'yes')->inRandomOrder()->limit(10)->get();

        $latest_rides = Requests::
        leftjoin('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
            ->join('tj_conducteur', 'tj_requete.id_conducteur', '=', 'tj_conducteur.id')
            ->join('tj_payment_method', 'tj_requete.id_payment_method', '=', 'tj_payment_method.id')
            ->select('tj_requete.id', 'tj_requete.statut', 'tj_requete.statut_paiement', 'tj_requete.depart_name', 'tj_requete.destination_name', 'tj_requete.distance', 'tj_requete.montant', 'tj_requete.creer', 'tj_conducteur.id as driver_id', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.id as user_id', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'tj_payment_method.libelle', 'tj_payment_method.image')
            ->where('tj_requete.statut', 'completed')->inRandomOrder()->limit(10)->get();

        $total_earnings = $this->getTotalEarnings();
        $today_earnings = $this->getTotalEarnings('today');
        $total_parcel_earnings = $this->getParcelTotalEarnings();
        $today_parcel_earnings = $this->getParcelTotalEarnings('today');
        $admin_commision = $currency->symbole . '0';
        
        $vehicles = Vehicle::leftjoin('tj_type_vehicule', 'tj_type_vehicule.id', '=', 'tj_vehicule.id_type_vehicule')->where('statut', 'yes')->groupBy('brand')->inRandomOrder()->limit(10)->get();

        return view('home')->with("total_users", $total_users)
            ->with("total_drivers", $total_drivers)
            ->with("today_users", $today_users)
            ->with("today_drivers", $today_drivers)
            ->with("vehicles", $vehicles)
            ->with("new_rides", $new_rides)
            ->with("on_rides", $on_rides)
            ->with("confirmed_rides", $confirmed_rides)
            ->with("confirmed_parcel_rides", $confirmed_parcel_rides)
            ->with("today_confirmed_rides", $today_confirmed_rides)
            ->with("today_parcel_confirmed_rides", $today_parcel_confirmed_rides)
            ->with("completed_rides", $completed_rides)
            ->with("completed_parcel_rides", $completed_parcel_rides)
            ->with("todays_completed_ride", $today_completed_rides)
            ->with("today_parcel_completed_rides", $today_parcel_completed_rides)
            ->with("canceled_rides", $canceled_rides)
            ->with("canceled_parcel_rides", $canceled_parcel_rides)
            ->with("today_canceled_rides", $today_canceled_rides)
            ->with("today_parcel_canceled_rides", $today_parcel_canceled_rides)
            ->with("saletoday", $saletoday)
            ->with("commitionfortoday", $commitionfortoday)
            ->with("commitionforweek", $commitionforweek)
            ->with("commitionformonth", $commitionformonth)
            ->with("currency", $currency)
            ->with("drivers", $drivers)
            ->with("active_drivers", $active_drivers)
            ->with("total_earnings", $total_earnings)
            ->with("total_parcel_earnings", $total_parcel_earnings)
            ->with("today_earnings", $today_earnings)
            ->with("today_parcel_earnings", $today_parcel_earnings)
            ->with("latest_rides", $latest_rides)
            ->with("admin_commision", $admin_commision)
            ->with("today_admin_commission", $today_admin_commission)
            ->with("today_parcel_admin_commission", $today_parcel_admin_commission)
            ->with('total_admin_commission', $total_admin_commission)
            ->with('total_parcel_admin_commission',$total_parcel_admin_commission);
            
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function welcome()
    {
        return view('welcome');
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function users()
    {
        return view('users');
    }

    public function updateDriverStatus(Request $request, $id)
    {
        $driver = Driver::find($id);
        if ($driver) {
            $driver->statut = 'yes';
        }
        $driver->save();
        return redirect()->back();
    }

    public function getTotalEarnings($type = null)
    {
        $date_start = date('Y-m-d 00:00:00');
        $date_end = date('Y-m-d 23:59:59');
        $trip = Requests::where('statut', 'completed');
        if ($type == "today") {
            $trip->whereBetween('creer', [$date_start, $date_end]);
        }
        return floatval($trip->sum('montant'));
    }

    public function getParcelTotalEarnings($type = null)
    {
        $date_start = date('Y-m-d 00:00:00');
        $date_end = date('Y-m-d 23:59:59');
        $trip = ParcelOrder::where('status', 'completed');
        if ($type == "today") {
            $trip->whereBetween('created_at', [$date_start, $date_end]);
        }
        return floatval($trip->sum('amount'));
    }


    public function getSalesOverview()
    {
        $v01 = 0;
        $v02 = 0;
        $v03 = 0;
        $v04 = 0;
        $v05 = 0;
        $v06 = 0;
        $v07 = 0;
        $v08 = 0;
        $v09 = 0;
        $v10 = 0;
        $v11 = 0;
        $v12 = 0;
        $currentYear = date('Y');
        $currentMonth = date('m');


        $order = Requests::where('statut', 'completed')->get();

        foreach ($order as $key => $value) {
            $price = 0;
            $orderMonth = date('m', strtotime($value->creer));
            $orderYear = date('Y', strtotime($value->creer));
            $price = intval($value->montant);
            $price = $price - intval($value->discount);
            $price = $price + intval($value->tax);
            if ($currentYear == $orderYear) {
                switch ($orderMonth) {
                    case "01":
                        $v01 = intval($v01) + $price;
                        break;
                    case "02":
                        $v02 = intval($v02) + $price;
                        break;
                    case "03":
                        $v03 = intval($v03) + $price;
                        break;
                    case "04":
                        $v04 = intval($v04) + $price;
                        break;
                    case "05":
                        $v05 = intval($v05) + $price;
                        break;
                    case "06":
                        $v06 = intval($v06) + $price;
                        break;
                    case "07":
                        $v07 = intval($v07) + $price;
                        break;
                    case "08":
                        $v08 = intval($v08) + $price;
                        break;
                    case "09":
                        $v09 = intval($v09) + $price;
                        break;
                    case "10":
                        $v10 = intval($v10) + $price;
                        break;
                    case "11":
                        $v11 = intval($v11) + $price;
                        break;
                    default :
                        $v12 = intval($v12) + $price;
                        break;
                }
            }

        }

        $data['v1'] = $v01;
        $data['v2'] = $v02;
        $data['v2'] = $v03;
        $data['v4'] = $v04;
        $data['v5'] = $v05;
        $data['v6'] = $v06;
        $data['v7'] = $v07;
        $data['v8'] = $v08;
        $data['v9'] = $v09;
        $data['v10'] = $v10;
        $data['v11'] = $v11;
        $data['v12'] = $v12;
        echo json_encode($data);

    }
    
}

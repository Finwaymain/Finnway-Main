<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Requests;
use App\Models\UserApp;
use App\Models\Vehicle;
use App\Models\ParcelOrder;
use App\Services\FinancialReportService;
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
    public function index(Request $request)
    {
        $period = $request->get('period', 'this_month');
        [$startDate, $endDate, $periodKey] = FinancialReportService::parseDateRange($period);

        $currency = Currency::where('statut', 'yes')->first();

        $total_users = UserApp::count();
        $total_drivers = Driver::count();

        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        $today_users = UserApp::whereBetween('creer', [$today_start, $today_end])->count('id');
        $today_drivers = Driver::whereBetween('creer', [$today_start, $today_end])->count();
        
        $new_rides = Requests::where('statut', 'new')->count('id');
        $on_rides = Requests::where('statut', 'on ride')->count('id');

        $confirmed_rides = Requests::whereNull('deleted_at')->where('statut', 'confirmed')->count('id');
        $confirmed_parcel_rides = ParcelOrder::where('status', 'confirmed')->count('id');

        $today_confirmed_rides = Requests::whereNull('deleted_at')->where('statut', 'confirmed')->whereBetween('creer', [$today_start, $today_end])->count('id');
        $today_parcel_confirmed_rides = ParcelOrder::where('status', 'confirmed')->whereBetween('created_at', [$today_start, $today_end])->count('id');

        $completed_rides = Requests::whereNull('deleted_at')->where('statut', 'completed')->count('id');
        $completed_parcel_rides = ParcelOrder::where('status', 'completed')->count('id');

        $today_completed_rides = Requests::whereNull('deleted_at')->where('statut', 'completed')->whereBetween('creer', [$today_start, $today_end])->count('id');
        $today_parcel_completed_rides = ParcelOrder::where('status', 'completed')->whereBetween('created_at', [$today_start, $today_end])->count('id');

        $canceled_rides = Requests::whereNull('deleted_at')->whereIn('statut', ['canceled', 'rejected'])->count('id');
        $canceled_parcel_rides = ParcelOrder::whereIn('status', ['canceled', 'rejected'])->count('id');

        $today_canceled_rides = Requests::whereNull('deleted_at')->whereIn('statut', ['canceled', 'rejected'])->whereBetween('creer', [$today_start, $today_end])->count('id');
        $today_parcel_canceled_rides = ParcelOrder::whereIn('status', ['canceled', 'rejected'])->whereBetween('created_at', [$today_start, $today_end])->count('id');

        // Dynamic Financial Statistics from Shared Engine for selected period
        $financialStats = FinancialReportService::computeStats($startDate, $endDate);

        // Previous period stats for true Period-over-Period Growth Badges
        $prevRange = match($periodKey) {
            'today' => [date('Y-m-d 00:00:00', strtotime('-1 day')), date('Y-m-d 23:59:59', strtotime('-1 day'))],
            'this_week' => [date('Y-m-d 00:00:00', strtotime('-1 week start of week')), date('Y-m-d 23:59:59', strtotime('-1 week end of week'))],
            'this_year' => [date('Y-01-01 00:00:00', strtotime('-1 year')), date('Y-12-31 23:59:59', strtotime('-1 year'))],
            default => [date('Y-m-01 00:00:00', strtotime('first day of last month')), date('Y-m-t 23:59:59', strtotime('last day of last month'))],
        };
        $prevStats = FinancialReportService::computeStats($prevRange[0], $prevRange[1]);
        $prevUsers = UserApp::whereBetween('creer', [$prevRange[0], $prevRange[1]])->count('id');
        $prevDrivers = Driver::whereBetween('creer', [$prevRange[0], $prevRange[1]])->count();
        $currUsers = UserApp::whereBetween('creer', [$startDate, $endDate])->count('id');
        $currDrivers = Driver::whereBetween('creer', [$startDate, $endDate])->count();

        $calcGrowth = function($current, $prev) {
            if ($prev > 0) {
                $growth = round((($current - $prev) / $prev) * 100, 1);
                return ($growth >= 0 ? '+' : '') . $growth . '%';
            }
            if ($current > 0) {
                return '+100%';
            }
            return '+0.0%';
        };

        $revGrowth = $calcGrowth($financialStats['grossRevenue'], $prevStats['grossRevenue']);
        $profitGrowth = $calcGrowth($financialStats['netRevenue'], $prevStats['netRevenue']);
        $userGrowth = $calcGrowth($currUsers, $prevUsers);
        $driverGrowth = $calcGrowth($currDrivers, $prevDrivers);
        $txnsGrowth = $calcGrowth($financialStats['totalTransactions'], $prevStats['totalTransactions']);

        $total_earnings = $financialStats['grossRevenue'];
        $total_admin_commission = $financialStats['netRevenue'];
        $adminNetProfit = $financialStats['netProfitPnl'];
        $total_transactions = $financialStats['totalTransactions'];

        $periodLabel = match($periodKey) {
            'today' => 'Today (' . date('d M Y') . ')',
            'this_week' => 'This Week (' . date('d M', strtotime('monday this week')) . ' - ' . date('d M Y', strtotime('sunday this week')) . ')',
            'this_month' => date('01 M Y') . ' - ' . date('t M Y'),
            'this_year' => 'Year ' . date('Y'),
            'all' => 'All Time',
            default => date('01 M Y') . ' - ' . date('t M Y'),
        };

        $drivers = Driver::where('statut', '=', 'no')->get();
        $active_drivers = Driver::where('statut', '=', 'yes')->inRandomOrder()->limit(10)->get();

        $latest_rides = Requests::
        leftjoin('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
            ->join('tj_conducteur', 'tj_requete.id_conducteur', '=', 'tj_conducteur.id')
            ->join('tj_payment_method', 'tj_requete.id_payment_method', '=', 'tj_payment_method.id')
            ->select('tj_requete.id', 'tj_requete.statut', 'tj_requete.statut_paiement', 'tj_requete.depart_name', 'tj_requete.destination_name', 'tj_requete.distance', 'tj_requete.montant', 'tj_requete.creer', 'tj_conducteur.id as driver_id', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.id as user_id', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'tj_payment_method.libelle', 'tj_payment_method.image')
            ->where('tj_requete.statut', 'completed')->orderBy('tj_requete.creer', 'desc')->limit(10)->get();

        $currency_symbol = $currency->symbole ?? '₹';
        $admin_commision = $currency_symbol . number_format($financialStats['netRevenue'], 2);
        
        $vehicles = Vehicle::leftjoin('tj_type_vehicule', 'tj_type_vehicule.id', '=', 'tj_vehicule.id_type_vehicule')->where('statut', 'yes')->groupBy('brand')->inRandomOrder()->limit(10)->get();

        return view('home', compact(
            'total_users', 'total_drivers', 'today_users', 'today_drivers', 'vehicles',
            'new_rides', 'on_rides', 'confirmed_rides', 'confirmed_parcel_rides',
            'today_confirmed_rides', 'today_parcel_confirmed_rides',
            'completed_rides', 'completed_parcel_rides', 'today_completed_rides', 'today_parcel_completed_rides',
            'canceled_rides', 'canceled_parcel_rides', 'today_canceled_rides', 'today_parcel_canceled_rides',
            'currency', 'currency_symbol', 'drivers', 'active_drivers', 'total_earnings', 'adminNetProfit',
            'latest_rides', 'admin_commision', 'total_admin_commission',
            'financialStats', 'periodKey', 'periodLabel',
            'revGrowth', 'profitGrowth', 'userGrowth', 'driverGrowth', 'txnsGrowth', 'total_transactions',
            'startDate', 'endDate'
        ));
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

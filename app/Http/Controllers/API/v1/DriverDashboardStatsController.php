<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use DB;
use Illuminate\Http\Request;

class DriverDashboardStatsController extends Controller
{
    public function __construct()
    {
        $this->limit = 20;
    }

    public function stats(Request $request)
    {
        $driverId = $request->get('driver_id');

        if (empty($driverId)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Driver Id Not Found',
            ]);
        }

        $today = date('Y-m-d');

        $todayRides = DB::table('tj_requete')
            ->where('id_conducteur', $driverId)
            ->where('statut', 'completed')
            ->whereDate('creer', $today)
            ->sum('montant');

        $todayParcels = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('parcel_orders')) {
            $todayParcels = DB::table('parcel_orders')
                ->where('id_conducteur', $driverId)
                ->where('status', 'completed')
                ->whereDate('created_at', $today)
                ->sum('amount');
        }

        $todayServices = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
            $todayServices = DB::table('service_requests')
                ->where('driver_id', $driverId)
                ->whereIn('status', ['Completed', 'completed'])
                ->where(function($q) use ($today) {
                    $q->whereDate('updated_at', $today)->orWhereDate('created_at', $today);
                })
                ->sum('amount');
        }

        $todayEarnings = round(floatval($todayRides) + floatval($todayParcels) + floatval($todayServices), 2);

        $todayRideCount = DB::table('tj_requete')
            ->where('id_conducteur', $driverId)
            ->where('statut', 'completed')
            ->whereDate('creer', $today)
            ->count();

        $todayServiceCount = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
            $todayServiceCount = DB::table('service_requests')
                ->where('driver_id', $driverId)
                ->whereIn('status', ['Completed', 'completed'])
                ->where(function($q) use ($today) {
                    $q->whereDate('updated_at', $today)->orWhereDate('created_at', $today);
                })
                ->count();
        }

        $todayBookings = $todayRideCount + $todayServiceCount;

        $ratingRow = DB::table('tj_note')
            ->where('id_conducteur', $driverId)
            ->selectRaw('AVG(niveau) as avg_rating, COUNT(*) as total')
            ->first();

        $walletBalance = DB::table('tj_conducteur')->where('id', $driverId)->value('amount');

        $driverRow = DB::table('tj_conducteur')->where('id', $driverId)->first();
        $photo = '';
        if ($driverRow && !empty($driverRow->photo_path)) {
            $photo = filter_var($driverRow->photo_path, FILTER_VALIDATE_URL)
                ? $driverRow->photo_path
                : (file_exists(public_path('assets/images/driver/' . $driverRow->photo_path))
                    ? asset('assets/images/driver/' . $driverRow->photo_path)
                    : asset('assets/images/placeholder_image.jpg'));
        }

        $pendingRideRequests = DB::table('tj_requete')
            ->where('id_conducteur', $driverId)
            ->where('statut', 'new')
            ->count();

        $pendingServiceRequests = ServiceRequestAPIController::countPendingServiceRequestsForDriver((int) $driverId);
        $pendingRequests = $pendingRideRequests + $pendingServiceRequests;

        $activeRide = DB::table('tj_requete')
            ->where('id_conducteur', $driverId)
            ->whereIn('statut', ['confirmed', 'on ride'])
            ->orderByDesc('id')
            ->first();

        $activeService = null;
        if ($activeRide) {
            $activeService = [
                'id' => (string) $activeRide->id,
                'statut' => $activeRide->statut,
                'depart_name' => $activeRide->depart_name,
                'destination_name' => $activeRide->destination_name,
                'montant' => $activeRide->montant,
                'creer' => $activeRide->creer,
            ];
        }

        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'successfully';
        $response['data'] = [
            'name' => trim(($driverRow->nom ?? '') . ' ' . ($driverRow->prenom ?? '')) ?: 'Driver',
            'photo' => $photo,
            'online' => $driverRow->online ?? 'no',
            'today_earnings' => (string) ($todayEarnings ?? 0),
            'today_bookings' => (string) $todayBookings,
            'wallet_balance' => (string) ($walletBalance ?? 0),
            'rating' => number_format((float) ($ratingRow->avg_rating ?? 0), 1),
            'rating_count' => (int) ($ratingRow->total ?? 0),
            'pending_requests' => (string) $pendingRequests,
            'pending_service_requests' => (string) $pendingServiceRequests,
            'pending_ride_requests' => (string) $pendingRideRequests,
            'active_service' => $activeService,
        ];

        return response()->json($response);
    }
}

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

        $todayEarnings = DB::table('tj_conducteur_transaction')
            ->where('id_conducteur', $driverId)
            ->where('type', 'credit')
            ->where('date', $today)
            ->sum('amount');

        $todayBookings = DB::table('tj_requete')
            ->where('id_conducteur', $driverId)
            ->where('statut', 'completed')
            ->whereDate('creer', $today)
            ->count();

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

        $pendingRequests = DB::table('tj_requete')
            ->where('id_conducteur', $driverId)
            ->where('statut', 'new')
            ->count();

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
            'active_service' => $activeService,
        ];

        return response()->json($response);
    }
}

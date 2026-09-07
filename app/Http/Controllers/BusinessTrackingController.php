<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class BusinessTrackingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = VehicleType::select('id', 'libelle')->orderBy('libelle', 'asc')->get();
        $counts = $this->calculateCounts();
        $center = $this->getDefaultMapCenter();

        return view('business_tracking.index', compact('categories', 'counts', 'center'));
    }

    public function getLiveData(Request $request)
    {
        $statusFilter = strtolower(trim((string) $request->get('status', 'all')));
        $categoryFilter = trim((string) $request->get('category', ''));
        $search = trim((string) $request->get('search', ''));

        // Query active rides to identify drivers currently on trip
        $activeRideDriverIds = DB::table('tj_requete')
            ->whereIn('statut', ['confirmed', 'onride'])
            ->whereNotNull('id_conducteur')
            ->where('id_conducteur', '!=', 0)
            ->pluck('id_conducteur')
            ->map(fn($id) => (string) $id)
            ->toArray();

        // Query active service requests if table exists
        $activeServiceDriverIds = [];
        if (Schema::hasTable('service_requests')) {
            $activeServiceDriverIds = DB::table('service_requests')
                ->whereIn(DB::raw('LOWER(TRIM(status))'), ['assigned', 'accepted', 'in_progress', 'on_the_way'])
                ->whereNotNull('driver_id')
                ->where('driver_id', '!=', 0)
                ->pluck('driver_id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        }

        $allBusyDriverIds = array_unique(array_merge($activeRideDriverIds, $activeServiceDriverIds));

        // Base query
        $query = Driver::leftJoin('tj_vehicule', 'tj_vehicule.id_conducteur', '=', 'tj_conducteur.id')
            ->leftJoin('tj_type_vehicule', 'tj_type_vehicule.id', '=', 'tj_vehicule.id_type_vehicule')
            ->select(
                'tj_conducteur.id',
                'tj_conducteur.nom',
                'tj_conducteur.prenom',
                'tj_conducteur.phone',
                'tj_conducteur.email',
                'tj_conducteur.photo_path',
                'tj_conducteur.statut',
                'tj_conducteur.online',
                'tj_conducteur.driver_on_ride',
                'tj_conducteur.latitude',
                'tj_conducteur.longitude',
                'tj_conducteur.modifier',
                'tj_conducteur.creer',
                'tj_type_vehicule.id as vehicle_type_id',
                'tj_type_vehicule.libelle as vehicle_type',
                'tj_type_vehicule.image as vehicle_image'
            );

        $allDrivers = $query->get();

        // Calculate counts across all drivers
        $totalCount = $allDrivers->count();
        $onlineCount = 0;
        $onTripCount = 0;
        $offlineCount = 0;
        $locatedCount = 0;

        foreach ($allDrivers as $d) {
            $driverIdStr = (string) $d->id;
            $isOnTrip = ($d->driver_on_ride === 'yes') || in_array($driverIdStr, $allBusyDriverIds, true);
            $isOnline = ($d->online === 'yes') && ($d->statut === 'yes');

            if ($isOnTrip) {
                $onTripCount++;
            } elseif ($isOnline) {
                $onlineCount++;
            } else {
                $offlineCount++;
            }

            $lat = floatval($d->latitude ?? 0);
            $lng = floatval($d->longitude ?? 0);
            if (!empty($lat) && !empty($lng) && abs($lat) > 0.001 && abs($lng) > 0.001) {
                $locatedCount++;
            }
        }

        // Filter drivers according to request params
        $filteredDrivers = $allDrivers->filter(function ($d) use ($statusFilter, $categoryFilter, $search, $allBusyDriverIds) {
            $driverIdStr = (string) $d->id;
            $isOnTrip = ($d->driver_on_ride === 'yes') || in_array($driverIdStr, $allBusyDriverIds, true);
            $isOnline = ($d->online === 'yes') && ($d->statut === 'yes');

            // Status filter
            if ($statusFilter === 'online' && (!$isOnline || $isOnTrip)) {
                return false;
            }
            if ($statusFilter === 'on_trip' && !$isOnTrip) {
                return false;
            }
            if ($statusFilter === 'offline' && ($isOnline || $isOnTrip)) {
                return false;
            }

            // Category filter
            if (!empty($categoryFilter) && $categoryFilter !== 'all') {
                if ((string) $d->vehicle_type_id !== $categoryFilter && strtolower((string) $d->vehicle_type) !== strtolower($categoryFilter)) {
                    return false;
                }
            }

            // Search keyword filter
            if (!empty($search)) {
                $name = strtolower(trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')));
                $phone = strtolower(trim((string) ($d->phone ?? '')));
                $vType = strtolower(trim((string) ($d->vehicle_type ?? '')));
                $id = (string) $d->id;
                $searchLower = strtolower($search);

                if (
                    strpos($name, $searchLower) === false &&
                    strpos($phone, $searchLower) === false &&
                    strpos($vType, $searchLower) === false &&
                    strpos($id, $searchLower) === false
                ) {
                    return false;
                }
            }

            return true;
        });

        // Format for map and list
        $result = [];
        foreach ($filteredDrivers as $d) {
            $driverIdStr = (string) $d->id;
            $isOnTrip = ($d->driver_on_ride === 'yes') || in_array($driverIdStr, $allBusyDriverIds, true);
            $isOnline = ($d->online === 'yes') && ($d->statut === 'yes');

            $status = 'offline';
            $statusLabel = 'Offline';
            if ($isOnTrip) {
                $status = 'on_trip';
                $statusLabel = 'On Trip / Busy';
            } elseif ($isOnline) {
                $status = 'online';
                $statusLabel = 'Online & Available';
            } elseif ($d->statut === 'no') {
                $status = 'inactive';
                $statusLabel = 'Account Inactive';
            }

            $lat = floatval($d->latitude ?? 0);
            $lng = floatval($d->longitude ?? 0);
            $hasCoords = (!empty($lat) && !empty($lng) && abs($lat) > 0.001 && abs($lng) > 0.001);

            // Avatar resolution
            $avatar = asset('images/user.png');
            if (!empty($d->photo_path)) {
                if (file_exists(public_path('assets/images/driver/' . $d->photo_path))) {
                    $avatar = asset('assets/images/driver/' . $d->photo_path);
                } elseif (filter_var($d->photo_path, FILTER_VALIDATE_URL)) {
                    $avatar = $d->photo_path;
                }
            }

            $lastUpdate = 'Recently';
            if (!empty($d->modifier) && $d->modifier !== '0000-00-00 00:00:00') {
                $lastUpdate = Carbon::parse($d->modifier)->diffForHumans();
            } elseif (!empty($d->creer)) {
                $lastUpdate = Carbon::parse($d->creer)->diffForHumans();
            }

            $result[] = [
                'id' => (string) $d->id,
                'name' => trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: ('Partner #' . $d->id),
                'phone' => $d->phone ?? 'N/A',
                'email' => $d->email ?? '',
                'avatar' => $avatar,
                'vehicle_type' => $d->vehicle_type ?: 'Partner',
                'latitude' => $lat,
                'longitude' => $lng,
                'has_coords' => $hasCoords,
                'status' => $status,
                'status_label' => $statusLabel,
                'last_updated' => $lastUpdate,
                'edit_url' => url('/drivers/edit/' . $d->id),
            ];
        }

        return response()->json([
            'success' => true,
            'counts' => [
                'total' => $totalCount,
                'online' => $onlineCount,
                'on_trip' => $onTripCount,
                'offline' => $offlineCount,
                'located' => $locatedCount,
            ],
            'drivers' => array_values($result),
        ]);
    }

    private function calculateCounts(): array
    {
        $total = DB::table('tj_conducteur')->count();
        $online = DB::table('tj_conducteur')->where('online', 'yes')->where('statut', 'yes')->count();
        $onRide = DB::table('tj_conducteur')->where('driver_on_ride', 'yes')->count();

        // Also check ongoing rides
        $activeRideCount = DB::table('tj_requete')
            ->whereIn('statut', ['confirmed', 'onride'])
            ->whereNotNull('id_conducteur')
            ->where('id_conducteur', '!=', 0)
            ->distinct('id_conducteur')
            ->count('id_conducteur');

        $onTrip = max($onRide, $activeRideCount);
        $offline = max(0, $total - $online - $onTrip);

        $located = DB::table('tj_conducteur')
            ->whereNotNull('latitude')
            ->where('latitude', '!=', '')
            ->where('latitude', '!=', '0')
            ->whereNotNull('longitude')
            ->where('longitude', '!=', '')
            ->where('longitude', '!=', '0')
            ->count();

        return [
            'total' => $total,
            'online' => $online,
            'on_trip' => $onTrip,
            'offline' => $offline,
            'located' => $located,
        ];
    }

    private function getDefaultMapCenter(): array
    {
        $first = DB::table('tj_conducteur')
            ->whereNotNull('latitude')
            ->where('latitude', '!=', '')
            ->where('latitude', '!=', '0')
            ->select('latitude', 'longitude')
            ->first();

        if ($first && floatval($first->latitude) != 0 && floatval($first->longitude) != 0) {
            return [
                'lat' => floatval($first->latitude),
                'lng' => floatval($first->longitude),
            ];
        }

        return [
            'lat' => 20.5937,
            'lng' => 78.9629,
        ];
    }
}

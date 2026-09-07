<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ServiceRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $thresholdMinutes = 15; // Search threshold limit window
        $thresholdTime = Carbon::now()->subMinutes($thresholdMinutes);
        $escalationTime = Carbon::now()->subMinutes(2); // 2-minute escalation threshold

        // Automatically cancel pending requests older than 15 minutes where no provider accepted
        ServiceRequest::where('status', 'pending')
            ->whereNull('driver_id')
            ->where('created_at', '<', $thresholdTime)
            ->update(['status' => 'cancelled']);

        $query = ServiceRequest::with(['user', 'provider'])->orderBy('created_at', 'desc');

        if ($status === 'escalated') {
            // New Tab: Unassigned requests created >= 2 minutes ago OR cancelled without a driver
            $query->whereNull('driver_id')
                  ->where(function($q) use ($escalationTime) {
                      $q->where(function($p) use ($escalationTime) {
                          $p->where('status', 'pending')
                            ->where('created_at', '<=', $escalationTime);
                      })->orWhere('status', 'cancelled');
                  });
        } elseif ($status === 'timed_out' || $status === 'cancelled') {
            $query->where('status', 'cancelled');
        } elseif ($status === 'pending') {
            // Active searching within 2 minutes
            $query->where('status', 'pending')
                  ->where('created_at', '>=', $escalationTime);
        } elseif ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('service_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('nom', 'LIKE', "%{$search}%")
                                ->orWhere('prenom', 'LIKE', "%{$search}%")
                                ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        $requests = $query->paginate(15)->appends($request->all());

        $totalBookings = ServiceRequest::count();
        $autoAccepted = ServiceRequest::whereIn('status', ['accepted', 'confirmed', 'in_progress', 'completed'])->count();
        $pendingMatch = ServiceRequest::where('status', 'pending')->whereNull('driver_id')->where('created_at', '>=', $escalationTime)->count();
        
        // Count of requests escalated (> 2 min without provider or cancelled without provider)
        $escalatedCount = ServiceRequest::whereNull('driver_id')
            ->where(function($q) use ($escalationTime) {
                $q->where(function($p) use ($escalationTime) {
                    $p->where('status', 'pending')
                      ->where('created_at', '<=', $escalationTime);
                })->orWhere('status', 'cancelled');
            })->count();

        $timedOutCount = ServiceRequest::where('status', 'cancelled')->whereNull('driver_id')->count();
        $completed = ServiceRequest::where('status', 'completed')->count();

        return view('service_requests.index', compact(
            'requests', 
            'status', 
            'search', 
            'totalBookings', 
            'autoAccepted', 
            'pendingMatch', 
            'escalatedCount', 
            'timedOutCount', 
            'completed', 
            'thresholdMinutes'
        ));
    }

    public function show($id)
    {
        $booking = ServiceRequest::with(['user', 'provider'])->find($id);
        if (!$booking) {
            return redirect()->route('service_requests')->with('error', 'Booking request not found.');
        }

        return view('service_requests.show', compact('booking'));
    }

    public function retrySearch($id)
    {
        $booking = ServiceRequest::find($id);
        if ($booking) {
            $booking->status = 'pending';
            $booking->driver_id = null;
            $booking->created_at = Carbon::now();
            $booking->save();
            return redirect()->back()->with('success', 'Search restarted with a fresh provider search window.');
        }
        return redirect()->back()->with('error', 'Service request not found.');
    }

    public function cancelRequest($id)
    {
        $booking = ServiceRequest::find($id);
        if ($booking) {
            $booking->status = 'cancelled';
            $booking->save();
            return redirect()->back()->with('success', 'Service request marked as cancelled.');
        }
        return redirect()->back()->with('error', 'Service request not found.');
    }

    /**
     * Fetch business users related to the specific service within 0-30km radius
     */
    public function getNearbyProviders(Request $request, $id)
    {
        try {
            $booking = ServiceRequest::with('user')->find($id);
            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Service request not found.'], 404);
            }

            $serviceName = (string) ($booking->service_name ?? '');
            $bookingLat = (float) ($booking->lat ?? 0);
            $bookingLng = (float) ($booking->lng ?? 0);

            // Fetch all active providers / business users
            $drivers = DB::table('tj_conducteur')
                ->where(function($q) {
                    $q->where('statut', 'yes')
                      ->orWhereNull('statut');
                })
                ->whereNull('deleted_at')
                ->select(
                    'id', 'nom', 'prenom', 'business_name', 'phone', 'alternate_phone',
                    'latitude', 'longitude', 'online', 'statut', 'address', 'category_id', 'photo', 'photo_path'
                )
                ->get();

            $matchingProviders30km = [];
            $otherMatchingProviders = [];

            foreach ($drivers as $drv) {
                // Check if provider is related to this service
                $matchingInfo = $this->checkDriverMatchesService((int) $drv->id, $drv, $serviceName);
                if (!$matchingInfo['matches']) {
                    continue; // ONLY business users related to that service!
                }

                $drvLat = (float) ($drv->latitude ?? 0);
                $drvLng = (float) ($drv->longitude ?? 0);
                $hasCoords = ($drvLat != 0.0 && $drvLng != 0.0 && $bookingLat != 0.0 && $bookingLng != 0.0);

                $distanceKm = null;
                if ($hasCoords) {
                    $distanceKm = $this->calculateHaversineDistance($bookingLat, $bookingLng, $drvLat, $drvLng);
                }

                $providerItem = [
                    'id' => $drv->id,
                    'name' => trim("{$drv->prenom} {$drv->nom}"),
                    'business_name' => $drv->business_name ?: '',
                    'phone' => $drv->phone ?: 'N/A',
                    'alternate_phone' => $drv->alternate_phone ?: '',
                    'online' => (strtolower(trim((string)$drv->online)) === 'yes'),
                    'distance_km' => $distanceKm !== null ? round($distanceKm, 1) : null,
                    'distance_label' => $distanceKm !== null ? (round($distanceKm, 1) . ' km away') : 'GPS unavailable',
                    'profession' => $matchingInfo['profession_label'],
                    'matched_category' => $matchingInfo['matched_term'],
                    'address' => $drv->address ?: '',
                ];

                if ($distanceKm !== null && $distanceKm <= 30.0) {
                    $matchingProviders30km[] = $providerItem;
                } else {
                    $otherMatchingProviders[] = $providerItem;
                }
            }

            // Sort 0-30km providers: nearest first, online first
            usort($matchingProviders30km, function($a, $b) {
                if ($a['online'] !== $b['online']) {
                    return $b['online'] ? 1 : -1;
                }
                return ($a['distance_km'] <=> $b['distance_km']);
            });

            // Sort other matching providers: online first
            usort($otherMatchingProviders, function($a, $b) {
                if ($a['online'] !== $b['online']) {
                    return $b['online'] ? 1 : -1;
                }
                return strcmp($a['name'], $b['name']);
            });

            $minsAgo = Carbon::parse($booking->created_at)->diffInMinutes(now());

            return response()->json([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'service_name' => $booking->service_name,
                    'customer_name' => $booking->user ? trim("{$booking->user->prenom} {$booking->user->nom}") : 'Customer',
                    'customer_phone' => $booking->user->phone ?? 'N/A',
                    'service_address' => $booking->service_address ?: ($booking->city ?: 'N/A'),
                    'lat' => $booking->lat,
                    'lng' => $booking->lng,
                    'elapsed_minutes' => $minsAgo,
                    'status' => $booking->status,
                ],
                'service_name' => $serviceName,
                'providers_within_30km' => $matchingProviders30km,
                'other_matching_providers' => $otherMatchingProviders,
                'total_matching' => count($matchingProviders30km) + count($otherMatchingProviders),
            ]);
        } catch (\Throwable $e) {
            Log::error('getNearbyProviders error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch providers: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check if a driver/business user relates to the requested service
     */
    private function checkDriverMatchesService(int $driverId, $drv, string $serviceName): array
    {
        $normalizedService = strtolower(trim($serviceName));
        if ($normalizedService === '') {
            return ['matches' => true, 'profession_label' => 'Service Partner', 'matched_term' => ''];
        }

        $serviceKeywords = $this->extractServiceKeywords($normalizedService);
        $driverCategories = [];

        // 1. Check mapped categories in tj_conducteur_categories
        if (Schema::hasTable('tj_conducteur_categories')) {
            $mapped = DB::table('tj_conducteur_categories as cc')
                ->join('tj_categorie_user as cu', 'cu.id', '=', 'cc.category_id')
                ->where('cc.driver_id', $driverId)
                ->pluck('cu.libelle')
                ->toArray();
            $driverCategories = array_merge($driverCategories, $mapped);
        }

        // 2. Check skills in driver_service_skills
        if (Schema::hasTable('driver_service_skills')) {
            $skills = DB::table('driver_service_skills as ds')
                ->join('tj_categorie_user as cu', 'cu.id', '=', 'ds.skill_id')
                ->where('ds.driver_id', $driverId)
                ->pluck('cu.libelle')
                ->toArray();
            $driverCategories = array_merge($driverCategories, $skills);
        }

        // 3. Check primary category_id on driver record
        if (!empty($drv->category_id)) {
            $catName = DB::table('tj_categorie_user')->where('id', $drv->category_id)->value('libelle');
            if ($catName) {
                $driverCategories[] = $catName;
            }
        }

        // 4. Check business name
        if (!empty($drv->business_name)) {
            $driverCategories[] = $drv->business_name;
        }

        $driverCategories = array_values(array_unique(array_filter($driverCategories)));
        $professionLabel = !empty($driverCategories) ? implode(', ', array_slice($driverCategories, 0, 3)) : 'Service Partner';

        // Check for match
        foreach ($driverCategories as $cat) {
            $catLower = strtolower(trim(preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $cat)));
            
            // Direct substring matches
            if (str_contains($normalizedService, $catLower) || str_contains($catLower, $normalizedService)) {
                return ['matches' => true, 'profession_label' => $professionLabel, 'matched_term' => $cat];
            }

            // Keyword alias matches
            foreach ($serviceKeywords as $kw) {
                if ($kw !== '' && (str_contains($catLower, $kw) || str_contains($kw, $catLower))) {
                    return ['matches' => true, 'profession_label' => $professionLabel, 'matched_term' => $cat];
                }
            }
        }

        return ['matches' => false, 'profession_label' => $professionLabel, 'matched_term' => ''];
    }

    /**
     * Extract related domain keywords for home service categories
     */
    private function extractServiceKeywords(string $service): array
    {
        $keywords = [$service];
        $words = preg_split('/[\s,\-_]+/', $service);
        foreach ($words as $w) {
            if (strlen($w) >= 3) {
                $keywords[] = $w;
            }
        }

        $synonyms = [
            'ac' => ['ac', 'air conditioner', 'air conditioning', 'cooling', 'hvac', 'appliance', 'repair'],
            'electrician' => ['electric', 'electrician', 'wiring', 'switch', 'light', 'fan', 'geyser', 'inverter'],
            'plumber' => ['plumb', 'plumber', 'pipe', 'tap', 'leak', 'drain', 'water', 'toilet', 'bathroom'],
            'clean' => ['clean', 'cleaner', 'cleaning', 'deep clean', 'sofa', 'carpet', 'maid', 'housekeeping'],
            'carpenter' => ['carpent', 'carpenter', 'wood', 'furniture', 'door', 'window', 'table'],
            'paint' => ['paint', 'painter', 'painting', 'wall', 'whitewash'],
            'appliance' => ['appliance', 'repair', 'refrigerator', 'fridge', 'washing machine', 'microwave', 'tv'],
            'pest' => ['pest', 'pest control', 'termite', 'cockroach', 'mosquito', 'rodent'],
            'tutor' => ['tutor', 'tuition', 'teacher', 'education'],
            'salon' => ['salon', 'beauty', 'beautician', 'haircut', 'makeup', 'spa'],
        ];

        foreach ($synonyms as $groupKey => $groupWords) {
            if (str_contains($service, $groupKey)) {
                $keywords = array_merge($keywords, $groupWords);
            }
        }

        return array_values(array_unique($keywords));
    }

    /**
     * Haversine formula distance calculation in Kilometers
     */
    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Admin assigns a business user/provider to a service request
     */
    public function assignProvider(Request $request, $id)
    {
        $driverId = $request->input('driver_id');
        if (empty($driverId)) {
            return response()->json(['success' => false, 'message' => 'Please select a business partner to assign.'], 422);
        }

        $booking = ServiceRequest::find($id);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Service request not found.'], 404);
        }

        if (in_array(strtolower($booking->status), ['completed', 'cancelled', 'rejected']) && !empty($booking->driver_id)) {
            return response()->json(['success' => false, 'message' => 'Cannot assign provider to a ' . $booking->status . ' request.'], 422);
        }

        $driver = DB::table('tj_conducteur')->where('id', $driverId)->first();
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Selected service provider was not found in system.'], 404);
        }

        // Assign provider & update status to Confirmed
        $booking->driver_id = $driverId;
        $booking->status = 'Confirmed';

        // Ensure 4-digit start OTP exists
        if (empty($booking->otp)) {
            $booking->otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        }

        $booking->save();

        $driverFullName = trim("{$driver->prenom} {$driver->nom}");

        // Step 1: Push Notification to Customer
        try {
            $customerToken = DB::table('tj_user_app')->where('id', $booking->user_id)->value('fcm_id');
            $customerTitle = "Expert Assigned: {$driverFullName}";
            $customerBody = "{$driverFullName} has been assigned to your {$booking->service_name} request. Start OTP: {$booking->otp}";
            
            $customerPayload = [
                'title' => $customerTitle,
                'body' => $customerBody,
                'tag' => 'homeservicenotif',
                'type' => 'homeservice',
                'booking_id' => (string) $booking->id,
                'status' => 'Confirmed',
                'driver_id' => (string) $driverId,
            ];

            if (!empty($customerToken)) {
                \App\Http\Controllers\API\v1\GcmController::sendNotification($customerToken, $customerPayload);
            }

            if (Schema::hasTable('tj_notification')) {
                DB::table('tj_notification')->insert([
                    'titre' => $customerTitle,
                    'message' => $customerBody,
                    'type' => 'homeservice',
                    'to_id' => $booking->user_id,
                    'from_id' => $driverId,
                    'creer' => date('Y-m-d H:i:s'),
                    'modifier' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $custEx) {
            Log::error('Assign push error (customer): ' . $custEx->getMessage());
        }

        // Step 2: Push Notification to Business User / Driver
        try {
            $driverToken = $driver->fcm_id ?? null;
            $driverTitle = "New Service Assigned: {$booking->service_name}";
            $addressSnippet = $booking->service_address ?: ($booking->city ?: 'Customer Location');
            $driverBody = "Admin has assigned booking #{$booking->id} to you at {$addressSnippet}. Start OTP: {$booking->otp}. Tap to view details.";

            $driverPayload = [
                'title' => $driverTitle,
                'body' => $driverBody,
                'tag' => 'homeservicerequest',
                'type' => 'homeservice',
                'booking_id' => (string) $booking->id,
                'statut' => 'confirmed',
                'status' => 'Confirmed',
                'sound' => 'ride_request_sound',
                'channel_id' => 'ride_requests',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ];

            if (!empty($driverToken)) {
                \App\Http\Controllers\API\v1\GcmController::sendNotification($driverToken, $driverPayload);
            }

            if (Schema::hasTable('tj_notification')) {
                DB::table('tj_notification')->insert([
                    'titre' => $driverTitle,
                    'message' => $driverBody,
                    'type' => 'homeservice',
                    'to_id' => $driverId,
                    'from_id' => 0,
                    'creer' => date('Y-m-d H:i:s'),
                    'modifier' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $drvEx) {
            Log::error('Assign push error (driver): ' . $drvEx->getMessage());
        }

        // Step 3: Dismiss alerts on other providers
        try {
            $otherTokens = DB::table('tj_notification')
                ->join('tj_conducteur', 'tj_conducteur.id', '=', 'tj_notification.to_id')
                ->where('tj_notification.type', 'homeservice')
                ->where('tj_notification.message', 'like', "%#{$booking->id}%")
                ->where('tj_notification.to_id', '!=', $driverId)
                ->whereNotNull('tj_conducteur.fcm_id')
                ->where('tj_conducteur.fcm_id', '!=', '')
                ->pluck('tj_conducteur.fcm_id')
                ->unique();

            foreach ($otherTokens as $otherTok) {
                \App\Http\Controllers\API\v1\GcmController::sendNotification($otherTok, [
                    'title' => 'Service Request Assigned',
                    'body' => "Booking #{$booking->id} has been assigned by admin.",
                    'tag' => 'booking_taken',
                    'statut' => 'taken',
                    'booking_id' => (string) $booking->id,
                ]);
            }
        } catch (\Throwable $cancelEx) {
            Log::error('Cancel other alerts error: ' . $cancelEx->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully assigned {$driverFullName} to booking #{$booking->id}! Customer and partner notified.",
            'booking_id' => $booking->id,
            'driver_name' => $driverFullName,
            'status' => $booking->status,
        ]);
    }

    /**
     * Real-time polling check for unassigned 2-minute escalations
     */
    public function escalationCheck()
    {
        $escalationTime = Carbon::now()->subMinutes(2);
        $escalatedCount = ServiceRequest::whereNull('driver_id')
            ->where(function($q) use ($escalationTime) {
                $q->where(function($p) use ($escalationTime) {
                    $p->where('status', 'pending')
                      ->where('created_at', '<=', $escalationTime);
                })->orWhere('status', 'cancelled');
            })->count();

        return response()->json([
            'success' => true,
            'escalated_count' => $escalatedCount,
            'has_escalations' => ($escalatedCount > 0),
        ]);
    }
}

<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\UserApp;

class ServiceRequestAPIController extends Controller
{
    public function bookService(Request $request)
    {
        $user_id = $request->header('id_user') ?? $request->input('user_id');
        
        $mediaUrls = [];

        if ($request->has('media') && is_array($request->input('media'))) {
            $privateKey = config('imagekit.private_key');
            if (empty($privateKey)) {
                return response()->json([
                    'success' => 'failed',
                    'message' => 'IMAGEKIT_PRIVATE_KEY is not configured.'
                ], 500);
            }

            foreach ($request->input('media') as $mediaItem) {
                if (isset($mediaItem['base64'])) {
                    $base64 = $mediaItem['base64'];
                    
                    // Extract base64 content
                    // e.g. "data:image/png;base64,iVBORw..."
                    if (strpos($base64, 'data:') === 0) {
                        $parts = explode(',', $base64);
                        if (count($parts) == 2) {
                            $base64 = $parts[1];
                        }
                    }

                    $filename = 'service_' . time() . '_' . uniqid();

                    $url = "https://upload.imagekit.io/api/v1/files/upload";

                    $postData = [
                        'file' => $base64,
                        'fileName' => $filename,
                        'folder' => '/fiinway_service_requests',
                        'useUniqueFileName' => 'true'
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                    curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ':');

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        // skip on error
                    } else {
                        $response = json_decode($result, true);
                        if (isset($response['url'])) {
                            $mediaUrls[] = $response['url'];
                        }
                    }
                    curl_close($ch);
                }
            }
        }

        $serviceRequest = ServiceRequest::create([
            'user_id' => $user_id,
            'driver_id' => $request->input('driver_id'),
            'service_name' => $request->input('service_name'),
            'address_type' => $request->input('address_type'),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'preferred_date' => $request->input('date'),
            'preferred_time' => $request->input('time'),
            'description' => $request->input('description'),
            'status' => 'Pending',
            'media' => json_encode($mediaUrls)
        ]);

        return response()->json([
            'success' => 'success',
            'message' => 'Service request booked successfully.',
            'data' => $serviceRequest
        ]);
    }
    
    public function getHistory(Request $request)
    {
        $user_id = $request->header('id_user') ?? $request->input('user_id');
        
        if (!$user_id) {
            return response()->json(['success' => 'error', 'message' => 'User ID is required']);
        }
        
        $requests = ServiceRequest::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => 'success',
            'data' => $requests
        ]);
    }
    
    /**
     * Returns Home Services catalog for user-app "More" / All Services.
     * Top-level rows are type=consumer_service only (never provider signup categories).
     */
    public function getServiceCategories(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            return $this->searchServiceCategories($search);
        }

        $parentId = $request->input('parent_id');

        $query = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
            ->where('statut', true)
            ->where('type', 'consumer_service');

        $query = $parentId ? $query->where('parent_id', $parentId) : $query->whereNull('parent_id');

        $rows = $query->select('id', 'libelle', 'image')->orderBy('id')->get();

        $data = $rows->map(function ($row) {
            $hasChildren = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
                ->where('parent_id', $row->id)
                ->where('statut', true)
                ->where('type', 'consumer_service')
                ->exists();

            return [
                'id' => $row->id,
                'libelle' => $row->libelle,
                'image' => $row->image,
                'has_children' => $hasChildren,
            ];
        });

        return response()->json([
            'success' => 'success',
            'data' => $data,
        ]);
    }

    private function searchServiceCategories(string $search)
    {
        $rows = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
            ->where('statut', true)
            ->where('type', 'consumer_service')
            ->where('libelle', 'like', '%' . $search . '%')
            ->select('id', 'libelle', 'image', 'parent_id')
            ->orderBy('libelle')
            ->limit(100)
            ->get();

        $allNodes = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
            ->where('type', 'consumer_service')
            ->select('id', 'libelle', 'parent_id')
            ->get()
            ->keyBy('id');

        $data = $rows->map(function ($row) use ($allNodes) {
            $breadcrumb = [];
            $currentId = $row->parent_id;
            $depth = 0;

            while ($currentId && $depth < 12) {
                $parent = $allNodes->get($currentId);
                if (!$parent) {
                    break;
                }
                array_unshift($breadcrumb, $parent->libelle);
                $currentId = $parent->parent_id;
                $depth++;
            }

            $hasChildren = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
                ->where('parent_id', $row->id)
                ->where('statut', true)
                ->where('type', 'consumer_service')
                ->exists();

            return [
                'id' => $row->id,
                'libelle' => $row->libelle,
                'image' => $row->image,
                'has_children' => $hasChildren,
                'breadcrumb' => $breadcrumb,
                'parent_id' => $row->parent_id,
            ];
        });

        return response()->json([
            'success' => 'success',
            'data' => $data,
        ]);
    }

    public function getHomeServices(Request $request)
    {
        $parent = \Illuminate\Support\Facades\DB::table('tj_categorie_user')->where('libelle', '🧹 Home Services')->first();
        if (!$parent) {
            return response()->json(['success' => 'success', 'data' => []]);
        }

        $subCategories = \Illuminate\Support\Facades\DB::table('tj_categorie_user')->where('parent_id', $parent->id)->get();
        
        $categories = [];
        $icons = [
            'Cleaner' => '🧹',
            'Electrician' => '⚡',
            'Plumber' => '🚰',
            'Carpenter' => '🔨',
            'Painter' => '🎨',
            'Pest Control' => '🐜'
        ];

        foreach ($subCategories as $sub) {
            $services = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
                ->where('parent_id', $sub->id)
                ->where('statut', true)
                ->select('libelle', 'image')
                ->get()
                ->toArray();
                
            if (count($services) > 0) {
                $categories[] = [
                    'title' => $sub->libelle,
                    'icon' => $icons[$sub->libelle] ?? '🔧',
                    'services' => $services
                ];
            }
        }
        
        return response()->json([
            'success' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Unified driver bookings feed for My Booking console.
     * Includes rides, service requests, and parcels.
     * Query: id_driver (required), status=incoming|active|history|all
     */
    public function getDriverBookings(Request $request)
    {
        $driverId = $request->input('id_driver') ?? $request->header('id_user');
        $statusFilter = strtolower(trim((string) $request->input('status', 'all')));

        if (empty($driverId)) {
            return response()->json(['success' => 'error', 'message' => 'Driver ID is required', 'data' => []]);
        }

        $bookings = [];

        // 1) Rides
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_requete')) {
            $rides = \Illuminate\Support\Facades\DB::table('tj_requete')
                ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'tj_requete.id_user_app')
                ->where('tj_requete.id_conducteur', $driverId)
                ->select(
                    'tj_requete.id',
                    'tj_requete.depart_name',
                    'tj_requete.destination_name',
                    'tj_requete.statut',
                    'tj_requete.montant',
                    'tj_requete.creer',
                    'tj_requete.modifier',
                    'tj_user_app.prenom',
                    'tj_user_app.nom',
                    'tj_user_app.phone'
                )
                ->orderByDesc('tj_requete.id')
                ->limit(100)
                ->get();

            foreach ($rides as $ride) {
                $status = strtolower(trim((string) ($ride->statut ?? '')));
                $group = $this->bookingStatusGroup($status);
                $customer = trim(($ride->prenom ?? '') . ' ' . ($ride->nom ?? ''));
                $bookings[] = [
                    'id' => (string) $ride->id,
                    'type' => 'ride',
                    'title' => 'Ride Booking',
                    'subtitle' => trim(($ride->depart_name ?? 'Pickup') . ' → ' . ($ride->destination_name ?? 'Drop')),
                    'status' => $ride->statut ?? 'new',
                    'status_group' => $group,
                    'customer_name' => $customer !== '' ? $customer : 'Customer',
                    'customer_phone' => $ride->phone ?? '',
                    'amount' => (float) ($ride->montant ?? 0),
                    'date' => $ride->creer ?? $ride->modifier ?? now()->toDateTimeString(),
                    'pickup' => $ride->depart_name ?? '',
                    'drop' => $ride->destination_name ?? '',
                ];
            }
        }

        // 2) Service / home bookings
        if (\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
            $services = \Illuminate\Support\Facades\DB::table('service_requests')
                ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'service_requests.user_id')
                ->where(function ($q) use ($driverId) {
                    $q->where('service_requests.driver_id', $driverId)
                        ->orWhereNull('service_requests.driver_id');
                })
                ->select(
                    'service_requests.*',
                    'tj_user_app.prenom',
                    'tj_user_app.nom',
                    'tj_user_app.phone'
                )
                ->orderByDesc('service_requests.id')
                ->limit(100)
                ->get();

            foreach ($services as $svc) {
                // Unassigned pending requests are incoming for all drivers;
                // assigned ones only show for that driver.
                $isAssigned = !empty($svc->driver_id);
                if ($isAssigned && (string) $svc->driver_id !== (string) $driverId) {
                    continue;
                }

                $status = strtolower(trim((string) ($svc->status ?? 'pending')));
                $group = $this->bookingStatusGroup($status);
                if (!$isAssigned && $group !== 'incoming') {
                    continue;
                }

                $customer = trim(($svc->prenom ?? '') . ' ' . ($svc->nom ?? ''));
                $bookings[] = [
                    'id' => (string) $svc->id,
                    'type' => 'service',
                    'title' => $svc->service_name ?? 'Service Booking',
                    'subtitle' => $svc->description ?? ($svc->address_type ?? 'Home service request'),
                    'status' => $svc->status ?? 'Pending',
                    'status_group' => $group,
                    'customer_name' => $customer !== '' ? $customer : 'Customer',
                    'customer_phone' => $svc->phone ?? '',
                    'amount' => (float) ($svc->amount ?? 0),
                    'date' => $svc->preferred_date
                        ? trim(($svc->preferred_date ?? '') . ' ' . ($svc->preferred_time ?? ''))
                        : ($svc->created_at ?? now()->toDateTimeString()),
                    'pickup' => $svc->address_type ?? '',
                    'drop' => '',
                    'lat' => $svc->lat ?? null,
                    'lng' => $svc->lng ?? null,
                ];
            }
        }

        // 3) Parcel bookings
        if (\Illuminate\Support\Facades\Schema::hasTable('parcel_orders')) {
            $parcelQuery = \Illuminate\Support\Facades\DB::table('parcel_orders')
                ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'parcel_orders.id_user_app')
                ->where('parcel_orders.id_conducteur', $driverId)
                ->select('parcel_orders.*', 'tj_user_app.prenom', 'tj_user_app.nom', 'tj_user_app.phone')
                ->orderByDesc('parcel_orders.id')
                ->limit(100)
                ->get();

            foreach ($parcelQuery as $parcel) {
                $status = strtolower(trim((string) ($parcel->statut ?? '')));
                $group = $this->bookingStatusGroup($status);
                $customer = trim(($parcel->prenom ?? '') . ' ' . ($parcel->nom ?? ''));
                if ($customer === '') {
                    $customer = $parcel->sender_name ?? $parcel->receiver_name ?? 'Customer';
                }
                $pickup = $parcel->source ?? $parcel->sender_address ?? $parcel->depart_name ?? '';
                $drop = $parcel->destination ?? $parcel->receiver_address ?? $parcel->destination_name ?? '';
                $bookings[] = [
                    'id' => (string) $parcel->id,
                    'type' => 'parcel',
                    'title' => 'Parcel Booking',
                    'subtitle' => trim(($pickup !== '' ? $pickup : 'Pickup') . ' → ' . ($drop !== '' ? $drop : 'Drop')),
                    'status' => $parcel->statut ?? 'new',
                    'status_group' => $group,
                    'customer_name' => $customer,
                    'customer_phone' => $parcel->phone ?? '',
                    'amount' => (float) ($parcel->amount ?? $parcel->montant ?? 0),
                    'date' => $parcel->creer ?? $parcel->created_at ?? now()->toDateTimeString(),
                    'pickup' => $pickup,
                    'drop' => $drop,
                ];
            }
        }

        usort($bookings, function ($a, $b) {
            return strtotime($b['date'] ?? 0) <=> strtotime($a['date'] ?? 0);
        });

        $counts = ['incoming' => 0, 'active' => 0, 'history' => 0];
        foreach ($bookings as $item) {
            $group = $item['status_group'] ?? 'incoming';
            if (isset($counts[$group])) {
                $counts[$group]++;
            }
        }

        if (in_array($statusFilter, ['incoming', 'active', 'history'], true)) {
            $bookings = array_values(array_filter($bookings, function ($item) use ($statusFilter) {
                return ($item['status_group'] ?? '') === $statusFilter;
            }));
        }

        return response()->json([
            'success' => 'success',
            'counts' => $counts,
            'data' => array_values($bookings),
        ]);
    }

    public function updateServiceBookingStatus(Request $request)
    {
        $driverId = $request->input('id_driver') ?? $request->header('id_user');
        $bookingId = $request->input('booking_id');
        $status = $request->input('status');

        if (empty($driverId) || empty($bookingId) || empty($status)) {
            return response()->json(['success' => 'error', 'message' => 'booking_id, id_driver and status are required']);
        }

        $key = strtolower(str_replace(' ', '_', (string) $status));
        $map = [
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'canceled' => 'Cancelled',
        ];
        if (!isset($map[$key])) {
            return response()->json(['success' => 'error', 'message' => 'Invalid status']);
        }
        $normalized = $map[$key];

        $booking = ServiceRequest::find($bookingId);
        if (!$booking) {
            return response()->json(['success' => 'error', 'message' => 'Booking not found']);
        }

        if (!empty($booking->driver_id) && (string) $booking->driver_id !== (string) $driverId) {
            return response()->json(['success' => 'error', 'message' => 'This booking is assigned to another provider']);
        }

        $booking->driver_id = $driverId;
        $booking->status = $normalized;
        $booking->save();

        return response()->json([
            'success' => 'success',
            'message' => 'Booking status updated',
            'data' => $booking,
        ]);
    }

    private function bookingStatusGroup(string $status): string
    {
        $status = strtolower(trim($status));
        $incoming = ['new', 'pending', 'open', 'requested'];
        $active = ['confirmed', 'accepted', 'on ride', 'onride', 'on_ride', 'in progress', 'in_progress', 'started'];
        $history = ['completed', 'rejected', 'reject', 'cancelled', 'canceled', 'failed'];

        if (in_array($status, $incoming, true)) return 'incoming';
        if (in_array($status, $active, true)) return 'active';
        if (in_array($status, $history, true)) return 'history';
        return 'incoming';
    }
}

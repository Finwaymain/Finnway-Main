<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodDuePayment;
use App\Models\Food\FoodNotification;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodOrderItem;
use App\Models\Food\FoodProduct;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodTransaction;
use App\Services\Food\FoodPricingEngine;
use App\Http\Controllers\API\v1\GcmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RestaurantOrderController extends Controller
{
    protected function restaurant(Request $request): ?FoodRestaurant
    {
        $owner = $request->attributes->get('food_owner');
        if (!$owner) {
            $restaurantId = $request->get('restaurant_id') ?: $request->attributes->get('restaurant_id');
            return $restaurantId ? FoodRestaurant::find($restaurantId) : null;
        }
        return FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first();
    }

    public function dashboard(Request $request)
    {
        $restaurant = $this->restaurant($request);
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $today = now()->toDateString();
        $base = FoodOrder::where('restaurant_id', $restaurant->id)->whereDate('created_at', $today);
        $totalOrders = (clone $base)->count();
        $pending = (clone $base)->where('order_status', 'pending')->count();
        $preparing = (clone $base)->whereIn('order_status', ['restaurant_accepted', 'preparing'])->count();
        $ready = (clone $base)->where('order_status', 'ready_for_pickup')->count();
        $outForDelivery = (clone $base)->whereIn('order_status', ['rider_assigned', 'rider_at_restaurant', 'food_picked_up', 'out_for_delivery', 'rider_at_location'])->count();
        $delivered = (clone $base)->whereIn('order_status', ['delivered', 'completed'])->count();
        $cancelled = (clone $base)->whereIn('order_status', ['rejected', 'cancelled'])->count();
        $todaySales = (float) (clone $base)->whereIn('order_status', ['delivered', 'completed'])->sum('food_amount');
        $todayCommission = (float) (clone $base)->whereIn('order_status', ['delivered', 'completed'])->sum('commission_amount');
        $todayNet = (float) (clone $base)->whereIn('order_status', ['delivered', 'completed'])->sum('restaurant_net_amount');
        $pendingDue = (float) FoodDuePayment::where('restaurant_id', $restaurant->id)->where('status', '!=', 'paid')->sum(DB::raw('amount - paid_amount'));

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $restaurant,
                'today_orders' => $totalOrders,
                'pending' => $pending,
                'preparing' => $preparing,
                'ready' => $ready,
                'out_for_delivery' => $outForDelivery,
                'delivered' => $delivered,
                'cancelled' => $cancelled,
                'active_orders' => $pending + $preparing + $ready + $outForDelivery,
                'today_sales' => round($todaySales, 2),
                'today_commission' => round($todayCommission, 2),
                'today_net' => round($todayNet, 2),
                'pending_due' => round($pendingDue, 2),
            ],
        ]);
    }

    public function incoming(Request $request)
    {
        $restaurant = $this->restaurant($request);
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $orders = FoodOrder::with('items')
            ->where('restaurant_id', $restaurant->id)
            ->where('order_status', 'pending')
            ->orderByDesc('id')
            ->get();
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function list(Request $request)
    {
        $restaurant = $this->restaurant($request);
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $q = FoodOrder::with('items')->where('restaurant_id', $restaurant->id);
        if ($request->filled('status')) {
            $statuses = explode(',', $request->get('status'));
            $q->whereIn('order_status', $statuses);
        }
        if ($request->get('active') == '1') {
            $q->whereIn('order_status', [
                'pending', 'restaurant_accepted', 'preparing', 'ready_for_pickup',
                'rider_assigned', 'rider_at_restaurant', 'food_picked_up', 'out_for_delivery', 'rider_at_location',
            ]);
        }
        if ($request->filled('search')) {
            $s = trim($request->get('search'));
            $q->where(function ($sub) use ($s) {
                $sub->where('order_number', 'like', "%{$s}%")
                    ->orWhere('customer_name', 'like', "%{$s}%")
                    ->orWhere('customer_phone', 'like', "%{$s}%");
            });
        }
        $perPage = (int) $request->get('per_page', 25);
        if ($request->get('paginate') === '0' || $request->get('all') === '1') {
            $orders = $q->orderByDesc('id')->get();
        } else {
            $orders = $q->orderByDesc('id')->paginate($perPage);
        }
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function show(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $order = FoodOrder::with('items')->where('restaurant_id', $restaurant->id)->where('id', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        return response()->json(['success' => true, 'data' => $order]);
    }

    public function accept(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $order = FoodOrder::where('restaurant_id', $restaurant->id)->where('id', $id)->where('order_status', 'pending')->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Pending order not found.']);
        }
        $order->order_status = 'restaurant_accepted';
        $order->prep_minutes = (int) $request->get('prep_minutes', $restaurant->avg_prep_minutes ?? 20);
        $order->accepted_at = now();
        $order->save();
        $this->notify($restaurant, 'Order Accepted', 'Order #' . $order->order_number . ' accepted.', 'order', $order->id);

        // Immediately dispatch notification to nearby and eligible delivery drivers
        $this->dispatchOrderToRiders($order, $restaurant);

        return response()->json(['success' => true, 'data' => $order->load('items')]);
    }

    public function reject(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $order = FoodOrder::where('restaurant_id', $restaurant->id)->where('id', $id)->where('order_status', 'pending')->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Pending order not found.']);
        }
        $order->order_status = 'rejected';
        $order->reject_reason = $request->get('reason', 'Restaurant rejected');
        $order->rejected_at = now();
        if (in_array($order->payment_status, ['paid'], true)) {
            $order->payment_status = 'refunded';
            $order->refund_amount = $order->customer_payable;
        }
        $order->save();
        return response()->json(['success' => true, 'data' => $order]);
    }

    public function updateStatus(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $order = FoodOrder::where('restaurant_id', $restaurant->id)->where('id', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        $status = $request->get('order_status', $request->get('status'));
        $allowed = [
            'restaurant_accepted' => ['preparing'],
            'preparing' => ['ready_for_pickup'],
            'ready_for_pickup' => ['ready_for_pickup'],
        ];
        $current = $order->order_status;
        if ($status === 'preparing' && in_array($current, ['pending', 'restaurant_accepted', 'preparing'], true)) {
            $order->order_status = 'preparing';
            // If rider not yet assigned, re-alert riders that order is preparing
            if (empty($order->rider_id)) {
                $this->dispatchOrderToRiders($order, $restaurant);
            }
        } elseif ($status === 'ready_for_pickup' && in_array($current, ['pending', 'restaurant_accepted', 'preparing', 'ready_for_pickup'], true)) {
            $order->order_status = 'ready_for_pickup';
            $order->ready_at = now();
            $order->pickup_otp = $order->pickup_otp ?: (string) random_int(1000, 9999);

            // Notify riders that order is ready for pickup
            if (empty($order->rider_id)) {
                $this->dispatchOrderToRiders($order, $restaurant);
            }
        } else {
            return response()->json(['success' => false, 'error' => 'Invalid status transition.']);
        }
        $order->save();
        return response()->json(['success' => true, 'data' => $order->load('items')]);
    }

    /**
     * Dispatches FCM push notification to nearby and city food delivery riders.
     */
    public function dispatchOrderToRiders(FoodOrder $order, ?FoodRestaurant $restaurant = null): void
    {
        try {
            if (!$restaurant) {
                $restaurant = FoodRestaurant::find($order->restaurant_id);
            }
            if (!$restaurant) return;

            $rLat = floatval($restaurant->latitude ?? 0);
            $rLng = floatval($restaurant->longitude ?? 0);
            $settings = DB::table('tj_settings')->select('driver_radios')->first();
            $radius = floatval($settings->driver_radios ?? 20);
            if ($radius <= 0) $radius = 20;

            $foodCategoryIds = [12889, 12888, 12882, 12890]; // Food Delivery, Delivery & Logistics, Bike Rider, Parcel Delivery

            // Query active online drivers with FCM token
            $query = DB::table('tj_conducteur')
                ->where('tj_conducteur.statut', 'yes')
                ->where('tj_conducteur.online', '!=', 'no')
                ->whereNotNull('tj_conducteur.fcm_id')
                ->where('tj_conducteur.fcm_id', '!=', '');

            // Filter by food delivery eligibility
            $query->where(function ($q) use ($foodCategoryIds) {
                $q->whereIn('tj_conducteur.category_id', $foodCategoryIds)
                    ->orWhere('tj_conducteur.parcel_delivery', 'yes')
                    ->orWhereExists(function ($sub) use ($foodCategoryIds) {
                        $sub->select(DB::raw(1))
                            ->from('tj_conducteur_categories')
                            ->leftJoin('tj_categorie_user as cu_cat', 'tj_conducteur_categories.category_id', '=', 'cu_cat.id')
                            ->leftJoin('tj_categorie_user as cu_sub', 'tj_conducteur_categories.subcategory_id', '=', 'cu_sub.id')
                            ->whereColumn('tj_conducteur_categories.driver_id', 'tj_conducteur.id')
                            ->where(function ($cq) use ($foodCategoryIds) {
                                $cq->whereIn('tj_conducteur_categories.category_id', $foodCategoryIds)
                                    ->orWhereIn('tj_conducteur_categories.subcategory_id', $foodCategoryIds)
                                    ->orWhere('cu_cat.libelle', 'like', '%food%')
                                    ->orWhere('cu_cat.libelle', 'like', '%delivery%')
                                    ->orWhere('cu_cat.libelle', 'like', '%bike%')
                                    ->orWhere('cu_sub.libelle', 'like', '%food%')
                                    ->orWhere('cu_sub.libelle', 'like', '%delivery%')
                                    ->orWhere('cu_sub.libelle', 'like', '%bike%');
                            });
                    })
                    // If driver has no categories assigned yet, include them
                    ->orWhereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('tj_conducteur_categories')
                            ->whereColumn('tj_conducteur_categories.driver_id', 'tj_conducteur.id');
                    });
            });

            // Distance filtering
            if ($rLat != 0.0 && $rLng != 0.0) {
                $query->where(function ($dq) use ($rLat, $rLng, $radius) {
                    $dq->whereRaw("(
                        6371 * acos(
                            LEAST(1.0, GREATEST(-1.0,
                                cos(radians(" . $rLat . "))
                                * cos(radians(tj_conducteur.latitude))
                                * cos(radians(tj_conducteur.longitude) - radians(" . $rLng . "))
                                + sin(radians(" . $rLat . "))
                                * sin(radians(tj_conducteur.latitude))
                            ))
                        ) <= " . $radius . "
                    )")
                    ->orWhereNull('tj_conducteur.latitude')
                    ->orWhere('tj_conducteur.latitude', '=', '')
                    ->orWhere('tj_conducteur.latitude', '=', '0');
                });
            }

            $drivers = $query->select('tj_conducteur.id', 'tj_conducteur.fcm_id')->distinct()->limit(50)->get();

            Log::info("Dispatched food order #{$order->order_number} to " . count($drivers) . " drivers.");

            $fcmPayload = [
                'title' => '🔔 New Food Delivery Order!',
                'body' => "Order #{$order->order_number} at {$restaurant->name} (Earn ₹{$order->delivery_charge})",
                'sound' => 'ride_request_sound',
                'tag' => 'food_delivery',
                'type' => 'food_delivery',
                'order_type' => 'food',
                'statut' => (string) $order->order_status,
                'order_id' => (string) $order->id,
                'order_number' => (string) $order->order_number,
                'restaurant_name' => (string) $restaurant->name,
                'restaurant_address' => (string) ($restaurant->address ?? ''),
                'restaurant_lat' => (string) ($restaurant->latitude ?? ''),
                'restaurant_lng' => (string) ($restaurant->longitude ?? ''),
                'delivery_address' => (string) ($order->delivery_address ?? ''),
                'delivery_lat' => (string) ($order->delivery_lat ?? ''),
                'delivery_lng' => (string) ($order->delivery_lng ?? ''),
                'montant' => (string) ($order->delivery_charge ?? '0'),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ];

            foreach ($drivers as $driver) {
                if (!empty($driver->fcm_id)) {
                    GcmController::sendNotification($driver->fcm_id, $fcmPayload);
                }
            }
        } catch (\Throwable $e) {
            Log::error("Food dispatch push notification error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function confirmHandover(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $order = FoodOrder::where('restaurant_id', $restaurant->id)->where('id', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        $otp = (string) $request->get('pickup_otp');
        if ($order->pickup_otp && $otp && $otp !== $order->pickup_otp) {
            return response()->json(['success' => false, 'error' => 'Invalid pickup OTP.']);
        }
        $order->order_status = 'food_picked_up';
        $order->picked_up_at = now();
        $order->rider_status = 'picked_up';
        $order->save();
        return response()->json(['success' => true, 'data' => $order]);
    }

    protected function notify(FoodRestaurant $restaurant, string $title, string $message, string $type, $refId): void
    {
        FoodNotification::create([
            'restaurant_id' => $restaurant->id,
            'owner_id' => $restaurant->owner_id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'ref_id' => $refId,
            'is_read' => false,
        ]);
    }
}

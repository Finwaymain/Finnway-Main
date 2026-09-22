<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Food\FoodDuePayment;
use App\Models\Food\FoodNotification;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodTransaction;
use App\Models\UserApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiderFoodController extends Controller
{
    /**
     * Strictly verifies whether the driver is qualified for Food Delivery.
     * Drivers with Food Delivery, Delivery & Logistics, Bike Rider, or Parcel Delivery categories,
     * or active delivery drivers without assigned subcategories are qualified.
     */
    private function isFoodDeliveryRider($riderId): bool
    {
        if (!$riderId) return false;

        $driver = DB::table('tj_conducteur')->where('id', $riderId)->first();
        if (!$driver) return false;

        // Must be an active driver
        if ($driver->statut !== 'yes') return false;

        $foodCategoryIds = [12889, 12888, 12882, 12890]; // Food Delivery, Delivery & Logistics, Bike Rider, Parcel Delivery

        // 1. Direct category on tj_conducteur
        if (in_array((int)$driver->category_id, $foodCategoryIds, true)) {
            return true;
        }

        // 2. Parcel/delivery enabled flag
        if (strtolower((string)$driver->parcel_delivery) === 'yes') {
            return true;
        }

        // 3. Category mapping table check (checking category_id and subcategory_id)
        $hasCat = DB::table('tj_conducteur_categories')
            ->leftJoin('tj_categorie_user as cu_cat', 'tj_conducteur_categories.category_id', '=', 'cu_cat.id')
            ->leftJoin('tj_categorie_user as cu_sub', 'tj_conducteur_categories.subcategory_id', '=', 'cu_sub.id')
            ->where('tj_conducteur_categories.driver_id', $riderId)
            ->where(function ($query) use ($foodCategoryIds) {
                $query->whereIn('tj_conducteur_categories.category_id', $foodCategoryIds)
                    ->orWhereIn('tj_conducteur_categories.subcategory_id', $foodCategoryIds)
                    ->orWhere('cu_cat.libelle', 'like', '%food%')
                    ->orWhere('cu_cat.libelle', 'like', '%delivery%')
                    ->orWhere('cu_cat.libelle', 'like', '%bike%')
                    ->orWhere('cu_sub.libelle', 'like', '%food%')
                    ->orWhere('cu_sub.libelle', 'like', '%delivery%')
                    ->orWhere('cu_sub.libelle', 'like', '%bike%');
            })
            ->exists();

        if ($hasCat) return true;

        // If driver has no categories assigned yet, allow verified active drivers
        $totalCats = DB::table('tj_conducteur_categories')->where('driver_id', $riderId)->count();
        if ($totalCats === 0) {
            return true;
        }

        return false;
    }

    /**
     * Sends push notification to customer regarding order progress.
     */
    private function notifyCustomer(FoodOrder $order, string $title, string $body, string $status)
    {
        try {
            $user = null;
            if ($order->customer_id) {
                $user = UserApp::find($order->customer_id);
            }
            if (!$user && $order->customer_phone) {
                $cleanPhone = substr(preg_replace('/[^0-9]/', '', (string)$order->customer_phone), -10);
                if ($cleanPhone) {
                    $user = UserApp::where('phone', 'like', "%{$cleanPhone}%")->first();
                }
            }
            if ($user && !empty($user->fcm_id)) {
                $payload = [
                    'title' => $title,
                    'body' => $body,
                    'tag' => 'food_order_status',
                    'order_type' => 'food',
                    'order_id' => (string) $order->id,
                    'order_number' => (string) $order->order_number,
                    'order_status' => (string) $status,
                    'sound' => 'default',
                ];
                \App\Http\Controllers\API\v1\GcmController::sendNotification($user->fcm_id, $payload);
            }
        } catch (\Throwable $e) {
            Log::error("Food customer push notification error: " . $e->getMessage());
        }
    }

    public function incoming(Request $request)
    {
        $riderId = $request->get('rider_id');
        // Filter out non-food delivery drivers
        if ($riderId && !$this->isFoodDeliveryRider($riderId)) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Only Food Delivery partners are eligible for food deliveries.'
            ]);
        }

        $lat = (float) $request->get('latitude');
        $lng = (float) $request->get('longitude');
        $radius = (float) $request->get('radius_km', 15);
        if ($radius <= 0) $radius = 15;

        $orders = FoodOrder::with('restaurant', 'items')
            ->whereIn('order_status', ['restaurant_accepted', 'preparing', 'ready_for_pickup'])
            ->whereNull('rider_id')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->filter(function ($order) use ($lat, $lng, $radius) {
                if (!$lat || !$lng || !$order->restaurant || !$order->restaurant->latitude) {
                    return true;
                }
                $d = \App\Services\Food\FoodPricingEngine::haversineKm(
                    $lat, $lng,
                    (float) $order->restaurant->latitude,
                    (float) $order->restaurant->longitude
                );
                $order->pickup_distance_km = round($d, 2);
                return $d <= $radius;
            })
            ->values();

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function accept(Request $request, $id)
    {
        $riderId = $request->get('rider_id');
        if (!$riderId) {
            return response()->json(['success' => false, 'error' => 'Rider ID is required.']);
        }

        // Strictly verify Food Delivery qualification
        if (!$this->isFoodDeliveryRider($riderId)) {
            return response()->json([
                'success' => false,
                'error' => 'Access Denied: Only registered Food Delivery partners can accept food delivery orders.'
            ]);
        }

        $order = FoodOrder::where('id', $id)
            ->whereIn('order_status', ['restaurant_accepted', 'preparing', 'ready_for_pickup'])
            ->whereNull('rider_id')
            ->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order is no longer available or already accepted.']);
        }

        $active = FoodOrder::where('rider_id', $riderId)
            ->whereIn('order_status', ['rider_assigned', 'rider_at_restaurant', 'food_picked_up', 'out_for_delivery', 'rider_at_location'])
            ->count();
        $max = (int) \App\Models\Food\FoodSetting::getValue('max_food_orders_per_rider', 2);
        if ($active >= $max) {
            return response()->json(['success' => false, 'error' => 'Active food order limit reached.']);
        }

        $driver = Driver::find($riderId);
        $riderName = $request->get('rider_name') ?: ($driver ? trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? '')) : 'Delivery Partner');
        $riderPhone = $request->get('rider_phone') ?: ($driver ? $driver->phone : '');

        $order->rider_id = $riderId;
        $order->rider_name = $riderName;
        $order->rider_phone = $riderPhone;
        $order->rider_status = 'assigned';
        $order->order_status = 'rider_assigned';
        $order->pickup_otp = $order->pickup_otp ?: (string) random_int(1000, 9999);
        $order->save();

        // Update driver state
        if ($driver) {
            $driver->driver_on_ride = 'yes';
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $driver->latitude = (string)$request->get('latitude');
                $driver->longitude = (string)$request->get('longitude');
            }
            $driver->save();
        }

        // Notify Restaurant
        FoodNotification::create([
            'restaurant_id' => $order->restaurant_id,
            'title' => 'Rider Assigned',
            'message' => "Rider {$riderName} assigned for order #{$order->order_number}.",
            'type' => 'order',
            'ref_id' => $order->id,
            'is_read' => false,
        ]);

        // Notify Customer
        $this->notifyCustomer(
            $order,
            'Rider Assigned',
            "Rider {$riderName} is heading to pick up your order #{$order->order_number}.",
            'rider_assigned'
        );

        return response()->json(['success' => true, 'data' => $order->load('items', 'restaurant')]);
    }

    public function active(Request $request)
    {
        $riderId = $request->get('rider_id');
        if (!$riderId) {
            return response()->json(['success' => false, 'error' => 'rider_id required.']);
        }

        $orders = FoodOrder::with('items', 'restaurant')
            ->where('rider_id', $riderId)
            ->whereIn('order_status', [
                'rider_assigned', 'rider_at_restaurant', 'food_picked_up', 'out_for_delivery', 'rider_at_location',
            ])
            ->orderBy('id')
            ->get();

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function updateStatus(Request $request, $id)
    {
        $riderId = $request->get('rider_id');
        $order = FoodOrder::where('id', $id)->where('rider_id', $riderId)->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Active order not found for this rider.']);
        }

        // Optionally update rider live coordinates
        if ($request->filled('latitude') && $request->filled('longitude') && $riderId) {
            Driver::where('id', $riderId)->update([
                'latitude' => (string)$request->get('latitude'),
                'longitude' => (string)$request->get('longitude'),
            ]);
        }

        $status = $request->get('status');
        $map = [
            'arrived_restaurant' => 'rider_at_restaurant',
            'picked_up' => 'food_picked_up',
            'out_for_delivery' => 'out_for_delivery',
            'arrived_customer' => 'rider_at_location',
            'delivered' => 'delivered',
        ];

        if (!isset($map[$status])) {
            return response()->json(['success' => false, 'error' => 'Invalid status.']);
        }

        // Handshake 1: Pickup from restaurant with Pickup OTP
        if ($status === 'picked_up') {
            $otp = (string) $request->get('pickup_otp');
            if ($order->pickup_otp && $otp && $otp !== (string)$order->pickup_otp) {
                return response()->json(['success' => false, 'error' => 'Invalid pickup OTP. Please verify with restaurant.']);
            }
            $order->picked_up_at = now();
            $this->notifyCustomer(
                $order,
                'Order Picked Up!',
                "Your order #{$order->order_number} is picked up and on the way!",
                'food_picked_up'
            );
        }

        // Milestone: Rider arrived at restaurant
        if ($status === 'arrived_restaurant') {
            FoodNotification::create([
                'restaurant_id' => $order->restaurant_id,
                'title' => 'Rider Arrived',
                'message' => "Rider {$order->rider_name} arrived at restaurant for order #{$order->order_number}.",
                'type' => 'order',
                'ref_id' => $order->id,
                'is_read' => false,
            ]);
        }

        // Milestone: Rider arrived at customer doorstep
        if ($status === 'arrived_customer') {
            $this->notifyCustomer(
                $order,
                'Rider at Doorstep!',
                "Rider {$order->rider_name} has arrived with order #{$order->order_number}! Share OTP {$order->delivery_otp} to complete handover.",
                'rider_at_location'
            );
        }

        // Handshake 2: Customer Handover with Delivery OTP
        if ($status === 'delivered') {
            $otp = (string) $request->get('delivery_otp');
            if ($order->delivery_otp && $otp && $otp !== (string)$order->delivery_otp) {
                return response()->json(['success' => false, 'error' => 'Invalid delivery OTP. Please verify with customer.']);
            }

            if ($order->payment_method === 'cod') {
                $order->payment_status = 'cash_collected';
                FoodDuePayment::create([
                    'rider_id' => $order->rider_id,
                    'order_id' => $order->id,
                    'party_type' => 'rider',
                    'due_type' => 'cod',
                    'amount' => $order->customer_payable,
                    'paid_amount' => 0,
                    'status' => 'pending',
                ]);
            } else {
                $order->payment_status = 'paid';
            }

            $order->delivered_at = now();
            $order->order_status = 'delivered';
            $order->rider_status = 'delivered';
            $order->save();

            // Check if rider has any remaining active rides/deliveries
            $remaining = FoodOrder::where('rider_id', $riderId)
                ->whereIn('order_status', ['rider_assigned', 'rider_at_restaurant', 'food_picked_up', 'out_for_delivery', 'rider_at_location'])
                ->count();
            if ($remaining === 0) {
                Driver::where('id', $riderId)->update(['driver_on_ride' => 'no']);
            }

            // Notify Customer
            $this->notifyCustomer(
                $order,
                'Order Delivered!',
                "Your order #{$order->order_number} has been delivered. Enjoy your meal!",
                'delivered'
            );

            return response()->json(['success' => true, 'data' => $order]);
        }

        $order->order_status = $map[$status];
        $order->rider_status = $status;
        $order->save();

        return response()->json(['success' => true, 'data' => $order]);
    }

    /**
     * Periodic GPS ping from rider app during active delivery.
     */
    public function updateLocation(Request $request)
    {
        $riderId = $request->get('rider_id');
        $lat = $request->get('latitude');
        $lng = $request->get('longitude');

        if ($riderId && $lat && $lng) {
            Driver::where('id', $riderId)->update([
                'latitude' => (string)$lat,
                'longitude' => (string)$lng,
            ]);
            return response()->json(['success' => true, 'message' => 'Location updated.']);
        }

        return response()->json(['success' => false, 'error' => 'rider_id, latitude, and longitude are required.']);
    }

    public function dues(Request $request)
    {
        $dues = FoodDuePayment::where('rider_id', $request->get('rider_id'))
            ->where('party_type', 'rider')
            ->orderByDesc('id')
            ->get();
        $pending = $dues->where('status', '!=', 'paid')->sum(fn ($d) => $d->amount - $d->paid_amount);
        return response()->json(['success' => true, 'data' => ['pending_due' => $pending, 'items' => $dues]]);
    }

    public function payDue(Request $request)
    {
        $due = FoodDuePayment::where('rider_id', $request->get('rider_id'))
            ->where('id', $request->get('due_id'))
            ->first();
        if (!$due) {
            return response()->json(['success' => false, 'error' => 'Due not found.']);
        }
        $amount = (float) $request->get('amount', $due->amount - $due->paid_amount);
        $due->paid_amount = min($due->amount, $due->paid_amount + $amount);
        $due->status = $due->paid_amount >= $due->amount ? 'paid' : 'partial';
        $due->payment_ref = $request->get('payment_ref', 'RIDER_DUE_' . time());
        if ($due->status === 'paid') {
            $due->paid_at = now();
        }
        $due->save();
        FoodTransaction::create([
            'txn_number' => 'TXN' . time() . 'R' . $due->rider_id,
            'order_id' => $due->order_id,
            'txn_type' => 'due_payment',
            'amount' => $amount,
            'payment_method' => $request->get('payment_method', 'upi'),
            'status' => 'paid',
            'gateway_ref' => $due->payment_ref,
            'notes' => 'Rider company due payment',
        ]);
        return response()->json(['success' => true, 'data' => $due]);
    }
}

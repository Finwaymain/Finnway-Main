<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodCategory;
use App\Models\Food\FoodDuePayment;
use App\Models\Food\FoodNotification;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodOrderItem;
use App\Models\Food\FoodProduct;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodReview;
use App\Models\Food\FoodSetting;
use App\Models\Food\FoodTransaction;
use App\Models\UserApp;
use App\Services\Food\FoodPricingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerFoodController extends Controller
{
    public function nearby(Request $request)
    {
        $lat = (float) $request->get('latitude', $request->get('lat'));
        $lng = (float) $request->get('longitude', $request->get('lng'));
        $radius = (float) $request->get('radius', FoodSetting::getValue('nearby_restaurant_radius_km', 25));
        if ($radius <= 0) {
            $radius = 25.0;
        }

        $userId = $request->get('user_id');
        $phone = $request->get('phone');
        $userType = $request->get('user_type', 'customer');
        $city = $request->get('city');
        $address = $request->get('address');

        // 1. If coordinates provided, persist them in user table (or driver table)
        if ($lat && $lng) {
            $this->saveUserLocation($userId, $phone, $userType, $lat, $lng, $city, $address);
        } elseif ($userId || $phone) {
            // 2. If coordinates not provided in request, look up user's saved location
            $saved = $this->getUserLocation($userId, $phone, $userType);
            if ($saved && !empty($saved['latitude']) && !empty($saved['longitude'])) {
                $lat = (float) $saved['latitude'];
                $lng = (float) $saved['longitude'];
                if (!$city && !empty($saved['city'])) {
                    $city = $saved['city'];
                }
            }
        }

        // 3. Query open restaurants and calculate distance from user's lat & long
        $restaurants = FoodRestaurant::query()
            ->where('operational_status', 'open')
            ->where('delivery_available', true)
            ->get()
            ->map(function ($r) use ($lat, $lng) {
                $r->distance_km = ($lat && $lng && $r->latitude && $r->longitude)
                    ? round(FoodPricingEngine::haversineKm($lat, $lng, (float) $r->latitude, (float) $r->longitude), 2)
                    : null;
                $r->logo_url = $r->logo ? (str_starts_with($r->logo, 'http') ? $r->logo : asset('storage/' . ltrim($r->logo, '/'))) : null;
                $r->cover_url = $r->cover_image ? (str_starts_with($r->cover_image, 'http') ? $r->cover_image : asset('storage/' . ltrim($r->cover_image, '/'))) : null;
                return $r;
            })
            ->filter(function ($r) use ($radius) {
                if ($r->distance_km === null) {
                    return true;
                }
                $allowedRadius = max($radius, (float) ($r->delivery_radius_km ?: 25));
                return $r->distance_km <= $allowedRadius;
            })
            ->sort(function ($a, $b) {
                if ($a->distance_km === null && $b->distance_km === null) return 0;
                if ($a->distance_km === null) return 1;
                if ($b->distance_km === null) return -1;
                return $a->distance_km <=> $b->distance_km;
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $restaurants,
            'radius_km' => $radius,
            'user_lat' => $lat ?: null,
            'user_lng' => $lng ?: null,
            'city' => $city ?: null,
        ]);
    }

    public function saveUserLocation($userId, $phone, $userType, float $lat, float $lng, ?string $city = null, ?string $address = null): bool
    {
        if (!$lat || !$lng) {
            return false;
        }

        $isDriver = ($userType === 'driver');
        $updated = false;

        // If driver, update tj_conducteur
        if ($isDriver && ($userId || $phone)) {
            $q = DB::table('tj_conducteur');
            if ($userId) {
                $q->where('id', $userId);
            } elseif ($phone) {
                $cleanPhone = preg_replace('/\D/', '', $phone);
                $q->where(function ($sq) use ($phone, $cleanPhone) {
                    $sq->where('phone', $phone)
                       ->orWhere('phone', 'LIKE', '%' . substr($cleanPhone, -10));
                });
            }
            $updateData = ['latitude' => $lat, 'longitude' => $lng, 'modifier' => now()];
            $updated = (bool) $q->update($updateData);
        }

        // Update tj_user_app
        if (!$updated && ($userId || $phone)) {
            $q = DB::table('tj_user_app');
            if ($userId) {
                $q->where('id', $userId);
            } elseif ($phone) {
                $cleanPhone = preg_replace('/\D/', '', $phone);
                $q->where(function ($sq) use ($phone, $cleanPhone) {
                    $sq->where('phone', $phone)
                       ->orWhere('phone', 'LIKE', '%' . substr($cleanPhone, -10));
                });
            }
            $updateData = [
                'latitude' => $lat,
                'longitude' => $lng,
                'modifier' => now(),
            ];
            if ($city) {
                $updateData['city'] = $city;
            }
            if ($address) {
                $updateData['address'] = $address;
            }
            $updated = (bool) $q->update($updateData);
        }

        return $updated;
    }

    public function getUserLocation($userId, $phone, $userType = 'customer'): ?array
    {
        if ($userType === 'driver' && ($userId || $phone)) {
            $q = DB::table('tj_conducteur');
            if ($userId) $q->where('id', $userId);
            elseif ($phone) $q->where('phone', 'LIKE', '%' . substr(preg_replace('/\D/', '', $phone), -10));
            $driver = $q->first(['latitude', 'longitude']);
            if ($driver && $driver->latitude && $driver->longitude) {
                return [
                    'latitude' => (float) $driver->latitude,
                    'longitude' => (float) $driver->longitude,
                    'city' => null,
                    'address' => null,
                ];
            }
        }

        if ($userId || $phone) {
            $q = DB::table('tj_user_app');
            if ($userId) $q->where('id', $userId);
            elseif ($phone) $q->where('phone', 'LIKE', '%' . substr(preg_replace('/\D/', '', $phone), -10));
            $user = $q->first(['latitude', 'longitude', 'city', 'address']);
            if ($user && $user->latitude && $user->longitude) {
                return [
                    'latitude' => (float) $user->latitude,
                    'longitude' => (float) $user->longitude,
                    'city' => $user->city ?? null,
                    'address' => $user->address ?? null,
                ];
            }
        }

        return null;
    }

    public function updateLocation(Request $request)
    {
        $lat = (float) $request->get('latitude', $request->get('lat'));
        $lng = (float) $request->get('longitude', $request->get('lng'));
        $userId = $request->get('user_id');
        $phone = $request->get('phone');
        $userType = $request->get('user_type', 'customer');
        $city = $request->get('city');
        $address = $request->get('address');

        if (!$lat || !$lng) {
            return response()->json(['success' => false, 'error' => 'Latitude and longitude are required.'], 422);
        }

        if (!$userId && !$phone) {
            return response()->json(['success' => false, 'error' => 'user_id or phone is required.'], 422);
        }

        $saved = $this->saveUserLocation($userId, $phone, $userType, $lat, $lng, $city, $address);

        return response()->json([
            'success' => true,
            'message' => 'User location saved successfully.',
            'data' => [
                'latitude' => $lat,
                'longitude' => $lng,
                'city' => $city,
                'address' => $address,
                'persisted' => $saved,
            ],
        ]);
    }

    public function restaurantMenu(Request $request, $id)
    {
        $restaurant = FoodRestaurant::where('id', $id)->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $restaurant->logo_url = $restaurant->logo ? (str_starts_with($restaurant->logo, 'http') ? $restaurant->logo : asset('storage/' . ltrim($restaurant->logo, '/'))) : null;
        $restaurant->cover_url = $restaurant->cover_image ? (str_starts_with($restaurant->cover_image, 'http') ? $restaurant->cover_image : asset('storage/' . ltrim($restaurant->cover_image, '/'))) : null;

        $engine = new FoodPricingEngine();
        $categories = FoodCategory::where('restaurant_id', $restaurant->id)->where('is_active', true)->orderBy('sort_order')->get();
        $products = FoodProduct::with(['addons', 'variants'])
            ->where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->where('availability', 'available')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($p) use ($engine, $restaurant) {
                $price = $engine->customerUnitPrice($p, $restaurant);
                $p->customer_price = $price['customer_price'];
                $p->markup_amount = $price['markup'];
                $p->mrp = $price['mrp'];
                $p->image_url = $p->image_url;
                return $p;
            });
        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $restaurant,
                'categories' => $categories,
                'products' => $products,
            ],
        ]);
    }

    public function placeOrder(Request $request)
    {
        $restaurant = FoodRestaurant::where('id', $request->get('restaurant_id'))
            ->where('operational_status', 'open')
            ->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not available.']);
        }

        $items = $request->get('items', []);
        if (!is_array($items) || count($items) === 0) {
            return response()->json(['success' => false, 'error' => 'Cart is empty.']);
        }

        $engine = new FoodPricingEngine();
        $foodAmount = 0.0;
        $markupAmount = 0.0;
        $foodSubtotal = 0.0;
        $lineRows = [];

        foreach ($items as $item) {
            $product = FoodProduct::where('restaurant_id', $restaurant->id)
                ->where('id', $item['product_id'] ?? 0)
                ->where('is_active', true)
                ->first();
            if (!$product) {
                return response()->json(['success' => false, 'error' => 'Invalid product in cart.']);
            }
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = $engine->customerUnitPrice($product, $restaurant);
            $addonsTotal = 0.0;
            $addonsJson = $item['addons'] ?? [];
            if (is_array($addonsJson)) {
                foreach ($addonsJson as $ad) {
                    $addonsTotal += (float) ($ad['price'] ?? 0);
                }
            }
            $lineFood = $price['base_price'] * $qty;
            $lineMarkup = $price['markup'] * $qty;
            $lineCustomer = ($price['customer_price'] + $addonsTotal) * $qty;
            $foodAmount += $lineFood;
            $markupAmount += $lineMarkup;
            $foodSubtotal += $lineCustomer;
            $lineRows[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $qty,
                'restaurant_unit_price' => $price['base_price'],
                'markup_unit' => $price['markup'],
                'customer_unit_price' => $price['customer_price'],
                'addons_total' => $addonsTotal * $qty,
                'line_total' => $lineCustomer,
                'variant_name' => $item['variant_name'] ?? null,
                'addons_json' => $addonsJson,
                'instructions' => $item['instructions'] ?? null,
            ];
        }

        $lat = (float) $request->get('delivery_lat');
        $lng = (float) $request->get('delivery_lng');
        $distance = ($lat && $lng && $restaurant->latitude && $restaurant->longitude)
            ? FoodPricingEngine::haversineKm($lat, $lng, (float) $restaurant->latitude, (float) $restaurant->longitude)
            : (float) $request->get('distance_km', 0);
        $delivery = $engine->calculateDeliveryCharge($distance);
        if (!$delivery['available']) {
            return response()->json(['success' => false, 'error' => 'Delivery not available for this distance.']);
        }

        $platform = $engine->calculatePlatformCharges($foodSubtotal);
        $discount = (float) $request->get('discount_amount', 0);

        // Home Service Standard Promo Bonus discount
        $applyPromo = filter_var($request->get('apply_promotional', false), FILTER_VALIDATE_BOOLEAN);
        $promoDiscount = 0.0;
        if ($applyPromo) {
            $promoDiscount = min(50.0, round($foodSubtotal * 0.20, 2)); // 20% discount up to ₹50
        }
        $discount = max($discount, $promoDiscount);

        $paymentMethod = strtolower($request->get('payment_method', 'wallet'));

        // COD is explicitly disabled - only Wallet or UPI / Online allowed
        if ($paymentMethod === 'cod' || str_contains($paymentMethod, 'cash')) {
            return response()->json([
                'success' => false,
                'error' => 'Cash on Delivery is not available. Please pay via Fiinway Wallet or UPI / Online Payment.',
            ], 422);
        }

        // Calculate dynamic taxes applied by admin from tj_tax
        $taxAmount = 0.0;
        $taxesBreakdown = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_tax')) {
            $activeTaxes = DB::table('tj_tax')->where('statut', 'yes')->get();
            $normMethod = ($paymentMethod === 'wallet') ? 'wallet' : 'upi';
            foreach ($activeTaxes as $t) {
                $applicable = strtolower($t->applicable_on ?? '');
                $methods = array_map('trim', explode(',', $applicable));
                $isApplicable = empty($applicable)
                    || in_array($normMethod, $methods)
                    || in_array('all', $methods)
                    || in_array('online', $methods);

                if ($isApplicable) {
                    $val = floatval($t->value ?? 0);
                    $isPercent = (strtolower($t->type ?? '') === 'percentage' || str_contains(strtolower($t->type ?? ''), 'percent'));
                    $amt = $isPercent ? round(($foodSubtotal * $val) / 100, 2) : round($val, 2);
                    $taxAmount += $amt;
                    $taxesBreakdown[] = [
                        'id' => $t->id,
                        'name' => $t->libelle,
                        'type' => $t->type,
                        'rate' => $val,
                        'amount' => $amt,
                        'label' => $isPercent ? "{$t->libelle} ({$val}%)" : "{$t->libelle} (₹{$val})",
                    ];
                }
            }
        }

        $deliveryCharge = $delivery['amount'];
        $customerPayable = max(0, round($foodSubtotal + $platform['total'] + $deliveryCharge + $taxAmount - $discount, 2));
        $commission = $engine->resolveCommission($restaurant, $foodAmount);
        $restaurantNet = round($foodAmount - $commission['amount'], 2);
        $companyDue = round($commission['amount'] + $markupAmount + $platform['total'] + $taxAmount, 2);

        // Customer or Driver resolution for wallet balance & MPIN
        $userId = $request->get('customer_id') ?: ($request->get('user_id') ?: $request->get('driver_id'));
        $phone = $request->get('customer_phone') ?: $request->get('phone');
        $userType = $request->get('user_type', 'customer');
        $isDriver = ($userType === 'driver' || $request->has('driver_id'));
        $user = null;

        if ($isDriver) {
            if ($userId) {
                $user = \App\Models\Driver::find($userId);
            }
            if (!$user && $phone) {
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
                $last10 = substr($cleanPhone, -10);
                if ($last10) {
                    $user = \App\Models\Driver::where('phone', 'like', "%{$last10}%")->first();
                }
            }
        } else {
            if ($userId) {
                $user = UserApp::find($userId);
            }
            if (!$user && $phone) {
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
                $last10 = substr($cleanPhone, -10);
                if ($last10) {
                    $user = UserApp::where('phone', 'like', "%{$last10}%")->first();
                }
            }
            // Fallback: If not found in UserApp, check Driver
            if (!$user) {
                if ($userId) {
                    $user = \App\Models\Driver::find($userId);
                    if ($user) $isDriver = true;
                }
                if (!$user && $phone) {
                    $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
                    $last10 = substr($cleanPhone, -10);
                    if ($last10) {
                        $user = \App\Models\Driver::where('phone', 'like', "%{$last10}%")->first();
                        if ($user) $isDriver = true;
                    }
                }
            }
        }

        // Home Service Wallet Payment Flow Verification
        if ($paymentMethod === 'wallet') {
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Please log in to pay with your Fiinway Wallet.',
                ], 422);
            }

            $walletBalance = floatval($user->amount ?? 0);
            if ($walletBalance < $customerPayable) {
                return response()->json([
                    'success' => false,
                    'error' => 'Insufficient wallet balance. Required: ₹' . number_format($customerPayable, 2) . ', Available: ₹' . number_format($walletBalance, 2),
                    'insufficient_balance' => true,
                    'available_balance' => $walletBalance,
                ], 422);
            }

            $mPin = $request->input('m_pin') ?? $request->input('mpin');
            if (empty($mPin)) {
                return response()->json([
                    'success' => false,
                    'require_mpin' => true,
                    'error' => 'Wallet M-PIN is required to authorize payment.',
                ], 422);
            }

            $userMPin = (string)($user->m_pin ?? '');
            $userMdp  = (string)($user->mdp ?? '');
            $enteredMPin = (string)$mPin;
            $isMPinValid = (!empty($userMPin) && $userMPin === $enteredMPin) || 
                           (!empty($userMdp) && $userMdp === md5($enteredMPin));

            if (empty($userMPin) && empty($userMdp)) {
                $user->m_pin = $enteredMPin;
                $user->mdp = md5($enteredMPin);
                $user->save();
                $isMPinValid = true;
            }

            if (!$isMPinValid) {
                return response()->json([
                    'success' => false,
                    'require_mpin' => true,
                    'error' => 'Invalid Wallet M-PIN. Please enter your correct 4-digit M-PIN.',
                ], 422);
            }
        }

        $paymentStatus = ($paymentMethod === 'cod') ? 'cash_pending' : 'paid';

        $order = null;
        DB::transaction(function () use (
            &$order, $restaurant, $request, $foodAmount, $markupAmount, $foodSubtotal, $discount,
            $platform, $deliveryCharge, $customerPayable, $commission, $restaurantNet, $companyDue,
            $paymentMethod, $paymentStatus, $distance, $lineRows, $user, $isDriver, $taxAmount, $taxesBreakdown
        ) {
            $order = FoodOrder::create([
                'order_number' => 'FIIN-FOOD-' . time() . random_int(10, 99),
                'restaurant_id' => $restaurant->id,
                'customer_id' => $user ? $user->id : $request->get('customer_id'),
                'customer_name' => $request->get('customer_name') ?: ($user ? trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')) : 'Customer'),
                'customer_phone' => $request->get('customer_phone') ?: ($user ? $user->phone : ''),
                'delivery_address' => $request->get('delivery_address'),
                'delivery_landmark' => $request->get('delivery_landmark'),
                'delivery_lat' => $request->get('delivery_lat'),
                'delivery_lng' => $request->get('delivery_lng'),
                'distance_km' => $distance,
                'special_instructions' => $request->get('special_instructions'),
                'food_amount' => $foodAmount,
                'markup_amount' => $markupAmount,
                'food_subtotal' => $foodSubtotal,
                'discount_amount' => $discount,
                'platform_charges' => $platform['total'],
                'other_charges' => 0,
                'delivery_charge' => $deliveryCharge,
                'tax_amount' => $taxAmount,
                'customer_payable' => $customerPayable,
                'commission_amount' => $commission['amount'],
                'restaurant_net_amount' => $restaurantNet,
                'company_due_amount' => $companyDue,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'order_status' => 'pending',
                'settlement_status' => 'pending',
                'delivery_otp' => (string) random_int(1000, 9999),
                'charges_breakdown' => $taxesBreakdown,
                'is_test' => (bool) $request->get('is_test', false),
            ]);

            // Persist customer's latest delivery coordinates and address in user table
            $delLat = (float) $request->get('delivery_lat');
            $delLng = (float) $request->get('delivery_lng');
            $delAddr = (string) $request->get('delivery_address', '');
            if ($delLat && $delLng && $user) {
                $this->saveUserLocation($user->id, $user->phone, $isDriver ? 'driver' : 'customer', $delLat, $delLng, null, $delAddr);
            }

            foreach ($lineRows as $row) {
                FoodOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'quantity' => $row['quantity'],
                    'restaurant_unit_price' => $row['restaurant_unit_price'],
                    'markup_unit' => $row['markup_unit'],
                    'customer_unit_price' => $row['customer_unit_price'],
                    'addons_total' => $row['addons_total'],
                    'line_total' => $row['line_total'],
                    'variant_name' => $row['variant_name'],
                    'addons_json' => $row['addons_json'],
                    'instructions' => $row['instructions'],
                ]);
            }

            FoodDuePayment::create([
                'order_id' => $order->id,
                'party_type' => 'restaurant',
                'restaurant_id' => $restaurant->id,
                'due_type' => 'commission',
                'amount' => $commission['amount'],
                'paid_amount' => 0,
                'status' => 'pending',
            ]);

            if ($paymentMethod === 'wallet' && $user && $customerPayable > 0) {
                $user->amount = max(0, round(floatval($user->amount ?? 0) - $customerPayable, 2));
                $user->save();

                DB::table('tj_transaction')->insert([
                    'id_user_app'     => $user->id,
                    'user_type'       => $isDriver ? 'driver' : 'customer',
                    'amount'          => '-' . $customerPayable,
                    'type'            => 'debit',
                    'deduction_type'  => 0,
                    'payment_method'  => 'Fiinway Wallet',
                    'payment_status'  => 'success',
                    'description'     => 'Food Order payment #' . $order->order_number,
                    'txn_id'          => 'TXN' . time() . $order->id,
                    'date'            => date('Y-m-d'),
                    'creer'           => date('Y-m-d H:i:s'),
                    'modifier'        => date('Y-m-d H:i:s'),
                ]);
            }

            if ($paymentMethod !== 'cod') {
                FoodTransaction::create([
                    'txn_number' => 'TXN' . time() . $order->id,
                    'restaurant_id' => $restaurant->id,
                    'order_id' => $order->id,
                    'txn_type' => 'order_payment',
                    'amount' => $customerPayable,
                    'payment_method' => $paymentMethod,
                    'status' => 'paid',
                    'gateway_ref' => $request->get('payment_ref'),
                    'notes' => 'Customer food order payment',
                ]);
            }

            FoodNotification::create([
                'restaurant_id' => $restaurant->id,
                'owner_id' => $restaurant->owner_id,
                'title' => 'New Food Order',
                'message' => 'Order #' . $order->order_number . ' received. Amount ₹' . $order->customer_payable,
                'type' => 'order',
                'ref_id' => $order->id,
                'is_read' => false,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully.',
            'data' => $order->load('items'),
        ]);
    }

    public function getTaxes(Request $request)
    {
        $paymentMethod = strtolower($request->get('payment_method', 'wallet'));
        $normMethod = ($paymentMethod === 'wallet') ? 'wallet' : 'upi';

        $taxes = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_tax')) {
            $activeTaxes = DB::table('tj_tax')->where('statut', 'yes')->get();
            foreach ($activeTaxes as $t) {
                $applicable = strtolower($t->applicable_on ?? '');
                $methods = array_map('trim', explode(',', $applicable));
                $isApplicable = empty($applicable)
                    || in_array($normMethod, $methods)
                    || in_array('all', $methods)
                    || in_array('online', $methods);

                if ($isApplicable) {
                    $val = floatval($t->value ?? 0);
                    $isPercent = (strtolower($t->type ?? '') === 'percentage' || str_contains(strtolower($t->type ?? ''), 'percent'));
                    $taxes[] = [
                        'id' => $t->id,
                        'name' => $t->libelle,
                        'value' => $val,
                        'type' => $isPercent ? 'percentage' : 'flat',
                        'label' => $isPercent ? "{$t->libelle} ({$val}%)" : "{$t->libelle} (₹{$val})",
                        'applicable_on' => $t->applicable_on ?? 'wallet,upi,online',
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $taxes,
        ]);
    }

    public function getWallet(Request $request)
    {
        $userType = $request->get('user_type', 'customer');
        $userId = $request->get('user_id') ?: ($request->get('id_user') ?: $request->get('driver_id'));
        $phone = $request->get('customer_phone') ?: $request->get('phone');
        $isDriver = ($userType === 'driver' || $request->has('driver_id'));
        $user = null;

        if ($isDriver) {
            if ($userId) {
                $user = \App\Models\Driver::find($userId);
            }
            if (!$user && $phone) {
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
                $last10 = substr($cleanPhone, -10);
                if ($last10) {
                    $user = \App\Models\Driver::where('phone', 'like', "%{$last10}%")->first();
                }
            }
        } else {
            if ($userId) {
                $user = UserApp::find($userId);
            }
            if (!$user && $phone) {
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
                $last10 = substr($cleanPhone, -10);
                if ($last10) {
                    $user = UserApp::where('phone', 'like', "%{$last10}%")->first();
                }
            }
            // Fallback: If not found in UserApp, check Driver
            if (!$user) {
                if ($userId) {
                    $user = \App\Models\Driver::find($userId);
                    if ($user) $isDriver = true;
                }
                if (!$user && $phone) {
                    $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
                    $last10 = substr($cleanPhone, -10);
                    if ($last10) {
                        $user = \App\Models\Driver::where('phone', 'like', "%{$last10}%")->first();
                        if ($user) $isDriver = true;
                    }
                }
            }
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'User not found.',
                'data' => [
                    'wallet_balance' => 0.0,
                    'has_mpin' => false,
                    'user_type' => $userType,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'name' => trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')),
                'phone' => $user->phone,
                'wallet_balance' => floatval($user->amount ?? 0),
                'has_mpin' => !empty($user->m_pin) || !empty($user->mdp),
                'user_type' => $isDriver ? 'driver' : 'customer',
            ]
        ]);
    }

    public function track(Request $request, $id)
    {
        $order = FoodOrder::with('items', 'restaurant')->where('id', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        if ($request->filled('customer_id') && (int) $order->customer_id !== (int) $request->get('customer_id')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.']);
        }

        $riderData = null;
        if ($order->rider_id) {
            $driver = \App\Models\Driver::find($order->rider_id);
            if ($driver) {
                $riderData = [
                    'id' => (int) $driver->id,
                    'name' => trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? '')),
                    'phone' => $driver->phone,
                    'photo' => $driver->photo_path,
                    'latitude' => $driver->latitude ? (float) $driver->latitude : null,
                    'longitude' => $driver->longitude ? (float) $driver->longitude : null,
                    'online' => $driver->online,
                    'vehicle_number' => $driver->statut_vehicule,
                ];
            }
        }
        $order->rider = $riderData;

        return response()->json(['success' => true, 'data' => $order]);
    }

    public function myOrders(Request $request)
    {
        $q = FoodOrder::with('items', 'restaurant')->orderByDesc('id');
        if ($request->filled('customer_id')) {
            $q->where('customer_id', $request->get('customer_id'));
        } elseif ($request->filled('customer_phone')) {
            $q->where('customer_phone', $request->get('customer_phone'));
        } else {
            return response()->json(['success' => false, 'error' => 'customer_id or customer_phone required.']);
        }
        if ($request->filled('status')) {
            $q->where('order_status', $request->get('status'));
        }
        return response()->json(['success' => true, 'data' => $q->paginate(20)]);
    }

    public function rate(Request $request, $id)
    {
        $order = FoodOrder::where('id', $id)->whereIn('order_status', ['delivered', 'completed'])->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Delivered order not found.']);
        }
        $review = FoodReview::updateOrCreate(
            ['order_id' => $order->id],
            [
                'restaurant_id' => $order->restaurant_id,
                'customer_id' => $order->customer_id,
                'rider_id' => $order->rider_id,
                'restaurant_rating' => $request->get('restaurant_rating'),
                'rider_rating' => $request->get('rider_rating'),
                'restaurant_review' => $request->get('restaurant_review'),
                'rider_review' => $request->get('rider_review'),
                'restaurant_tags' => $request->get('restaurant_tags'),
                'rider_tags' => $request->get('rider_tags'),
            ]
        );
        $avg = FoodReview::where('restaurant_id', $order->restaurant_id)->whereNotNull('restaurant_rating')->avg('restaurant_rating');
        $count = FoodReview::where('restaurant_id', $order->restaurant_id)->whereNotNull('restaurant_rating')->count();
        FoodRestaurant::where('id', $order->restaurant_id)->update([
            'rating_avg' => round((float) $avg, 2),
            'rating_count' => $count,
        ]);
        return response()->json(['success' => true, 'data' => $review]);
    }

    public function reorder(Request $request, $id)
    {
        $old = FoodOrder::with('items')->where('id', $id)->first();
        if (!$old) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        $items = [];
        $unavailable = [];
        foreach ($old->items as $item) {
            $product = FoodProduct::where('id', $item->product_id)
                ->where('is_active', true)
                ->where('availability', 'available')
                ->first();
            if (!$product) {
                $unavailable[] = $item->product_name;
                continue;
            }
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $item->quantity,
                'addons' => $item->addons_json,
                'variant_name' => $item->variant_name,
            ];
        }
        return response()->json([
            'success' => true,
            'data' => [
                'restaurant_id' => $old->restaurant_id,
                'items' => $items,
                'unavailable' => $unavailable,
            ],
        ]);
    }
}

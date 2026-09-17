<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\DriverKit;
use App\Models\DriverKitOrder;
use App\Models\DriverCategory;
use App\Models\UserCategory;
use Illuminate\Support\Str;

class DriverKitApiController extends Controller
{
    /**
     * Get Driver Partner Kit Status and Configuration
     * Endpoint: GET /api/v1/driver/kit-status?driver_id=X
     */
    public function getKitStatus(Request $request)
    {
        $driverId = $request->query('driver_id');
        if (empty($driverId)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'driver_id parameter is required',
            ], 400);
        }

        $cleanId = preg_replace('/[^0-9]/', '', (string)$driverId);
        $driver = Driver::where('id', $driverId)
            ->when(!empty($cleanId), function($q) use ($cleanId) {
                $q->orWhere('id', $cleanId)->orWhere('phone', 'LIKE', '%' . $cleanId);
            })
            ->orWhere('phone', $driverId)
            ->first();

        if (!$driver) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Driver not found',
            ], 404);
        }

        // 1. Check if Driver has completed category onboarding (check driver categories, skills, vehicles, and profile)
        $hasCatTable = \Illuminate\Support\Facades\Schema::hasTable('tj_conducteur_categories')
            && \Illuminate\Support\Facades\DB::table('tj_conducteur_categories')->where('driver_id', $driver->id)->exists();

        $hasSkillTable = \Illuminate\Support\Facades\Schema::hasTable('driver_service_skills')
            && \Illuminate\Support\Facades\DB::table('driver_service_skills')->where('driver_id', $driver->id)->exists();

        $hasVehTable = \Illuminate\Support\Facades\Schema::hasTable('tj_vehicule')
            && \Illuminate\Support\Facades\DB::table('tj_vehicule')->where('id_conducteur', $driver->id)->exists();

        $hasCategories = $hasCatTable || $hasSkillTable || $hasVehTable || !empty($driver->category_id) || ($driver->onboarding_completed ?? '') === 'yes' || !empty($driver->user_cat);

        // 2. Check if Driver is Verified by Admin
        $isVerified = ($driver->statut === 'yes' || (isset($driver->is_verified) && $driver->is_verified == 1));

        // 3. Determine Driver Category Code ('bike', 'auto', 'car', 'home_service', 'all')
        $categoryCode = $this->resolveDriverCategoryCode($driver) ?? 'home_service';

        // 3. Check if Driver has already purchased the Welcome Kit (by driver_id OR by phone number verification!)
        $driverPhone = trim($driver->phone ?? '');
        $cleanPhone = preg_replace('/[^0-9]/', '', $driverPhone);
        $shortPhone = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

        $paidOrder = DriverKitOrder::where(function($q) use ($driverId, $driverPhone, $shortPhone) {
            $q->where('driver_id', $driverId);
            if (!empty($driverPhone)) {
                $q->orWhere('receiver_phone', $driverPhone);
                if (!empty($shortPhone)) {
                    $q->orWhere('receiver_phone', 'LIKE', '%' . $shortPhone . '%');
                }
            }
        })
        ->where('payment_status', 'paid')
        ->orderBy('id', 'desc')
        ->first();

        $hasPurchased = !empty($paidOrder);

        // 4. Find matching Category Kit
        $kit = DriverKit::where('category_code', $categoryCode)->where('is_active', true)->first();
        if (!$kit) {
            // Fallback to 'all' or first active kit
            $kit = DriverKit::where('category_code', 'all')->where('is_active', true)->first()
                ?? DriverKit::where('is_active', true)->first();
        }

        if (!$kit) {
            // Default fallback object if no kits seeded
            $kit = (object)[
                'id' => 1,
                'category_code' => $categoryCode,
                'title' => 'Partner Starter Kit',
                'description' => 'Official Fiinway apparel, ID badge, and safety gear package for verified service partners.',
                'price' => 499.00,
                'image' => '',
                'items_included' => ['Fiinway Branded T-Shirt', 'Partner ID Card', 'Safety Gear'],
                'is_compulsory' => false,
                'checkout_url' => '/onboarding/kit-purchase',
            ];
        }

        // 5. Calculate popup rules
        // Should show popup only if:
        //  - Driver is verified
        //  - Driver has NOT already purchased
        $shouldShowPopup = ($isVerified && !$hasPurchased);

        // Compulsory is defined per-category in the kit record
        $isCompulsory = (bool)($kit->is_compulsory ?? false);

        // Build WebView URL with pre-filled query parameters
        $token = $request->query('accesstoken', $driver->accesstoken ?? '');
        $driverName = trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? ''));

        $baseUrl = 'https://api.fiinway.com';
        $checkoutPath = $kit->checkout_url ?? '/onboarding/kit-purchase';
        $normalizedPath = str_starts_with($checkoutPath, '/') ? $checkoutPath : '/' . $checkoutPath;

        $queryParams = http_build_query([
            'driver_id' => $driver->id,
            'accesstoken' => $token,
            'kit_id' => $kit->id,
            'category' => $categoryCode,
            'name' => $driverName,
            'phone' => $driverPhone,
        ]);

        $fullWebviewUrl = rtrim($baseUrl, '/') . $normalizedPath . '?' . $queryParams;

        $sizes = is_array($kit->sizes) ? $kit->sizes : (json_decode($kit->sizes ?? '[]', true) ?: ['S', 'M', 'L', 'XL', 'XXL']);

        return response()->json([
            'success' => 'success',
            'data' => [
                'driver_id' => (int)$driver->id,
                'driver_name' => $driverName,
                'category_code' => $categoryCode,
                'category_label' => $this->getCategoryLabel($categoryCode),
                'is_verified' => (bool)$isVerified,
                'has_purchased' => (bool)$hasPurchased,
                'should_show_popup' => (bool)$shouldShowPopup,
                'is_compulsory' => (bool)$isCompulsory,
                'booking_required' => (bool)($kit->booking_required ?? true),
                'kit' => [
                    'id' => $kit->id,
                    'sku' => $kit->sku ?? ('FW-KIT-' . strtoupper($categoryCode)),
                    'category_code' => $kit->category_code,
                    'title' => $kit->title,
                    'description' => $kit->description,
                    'price' => (float)$kit->price,
                    'price_formatted' => '₹' . number_format($kit->price, 0),
                    'mrp' => (float)($kit->mrp ?? ($kit->price * 1.5)),
                    'mrp_formatted' => '₹' . number_format($kit->mrp ?? ($kit->price * 1.5), 0),
                    'cashback_amount' => (float)($kit->cashback_amount ?? 0),
                    'cashback_formatted' => '₹' . number_format($kit->cashback_amount ?? 0, 0),
                    'stock_quantity' => (int)($kit->stock_quantity ?? 500),
                    'image' => $kit->image ? (str_starts_with($kit->image, 'http') ? $kit->image : url($kit->image)) : '',
                    'items_included' => is_array($kit->items_included) ? $kit->items_included : (json_decode($kit->items_included ?? '[]', true) ?: []),
                    'sizes' => $sizes,
                    'is_compulsory' => (bool)$kit->is_compulsory,
                    'booking_required' => (bool)($kit->booking_required ?? true),
                    'webview_url' => $fullWebviewUrl,
                ],
                'order' => $paidOrder ? [
                    'id' => $paidOrder->id,
                    'order_number' => $paidOrder->order_number,
                    'amount' => (float)$paidOrder->amount,
                    'selected_size' => $paidOrder->selected_size ?? $paidOrder->tshirt_size ?? 'L',
                    'delivery_status' => $paidOrder->delivery_status,
                    'tracking_code' => $paidOrder->tracking_code ?? $paidOrder->tracking_number ?? ('FWP' . rand(1000000000, 9999999999)),
                    'courier_partner' => $paidOrder->courier_partner ?? 'Blue Dart Express',
                    'expected_delivery_date' => $paidOrder->expected_delivery_date ?? ('Expected by ' . date('d M, 6:00 PM', strtotime('+3 days'))),
                    'purchased_at' => $paidOrder->purchased_at,
                ] : null,
            ]
        ]);
    }

    /**
     * Get Kit Catalog (All Category Kits + Winter Collection)
     * Endpoint: GET /api/v1/driver/kit-catalog
     */
    public function getKitCatalog(Request $request)
    {
        $category = $request->query('category');
        $query = DriverKit::where('is_active', true)->orderBy('display_order', 'asc');

        if (!empty($category)) {
            $query->where(function($q) use ($category) {
                $q->where('category_code', $category)->orWhere('category_code', 'all');
            });
        }

        $kits = $query->get()->map(function($k) {
            return [
                'id' => $k->id,
                'sku' => $k->sku,
                'category_code' => $k->category_code,
                'title' => $k->title,
                'description' => $k->description,
                'price' => (float)$k->price,
                'price_formatted' => '₹' . number_format($k->price, 0),
                'mrp' => (float)($k->mrp ?? ($k->price * 1.5)),
                'mrp_formatted' => '₹' . number_format($k->mrp ?? ($k->price * 1.5), 0),
                'cashback_amount' => (float)($k->cashback_amount ?? 0),
                'cashback_formatted' => '₹' . number_format($k->cashback_amount ?? 0, 0),
                'is_compulsory' => (bool)$k->is_compulsory,
                'booking_required' => (bool)($k->booking_required ?? true),
                'stock_quantity' => (int)($k->stock_quantity ?? 500),
                'image' => $k->image ? (str_starts_with($k->image, 'http') ? $k->image : url($k->image)) : '',
                'items_included' => is_array($k->items_included) ? $k->items_included : (json_decode($k->items_included ?? '[]', true) ?: []),
                'sizes' => is_array($k->sizes) ? $k->sizes : (json_decode($k->sizes ?? '[]', true) ?: ['S', 'M', 'L', 'XL', 'XXL']),
            ];
        });

        return response()->json([
            'success' => 'success',
            'data' => $kits,
        ]);
    }

    /**
     * Get Detailed Parcel Tracking for a Kit Order
     * Endpoint: GET /api/v1/driver/kit-order/track
     */
    public function getOrderTracking(Request $request)
    {
        $orderId = $request->query('order_id');
        $orderNumber = $request->query('order_number');
        $driverId = $request->query('driver_id');

        $query = DriverKitOrder::query();
        if (!empty($orderId)) {
            $query->where('id', $orderId);
        } elseif (!empty($orderNumber)) {
            $query->where('order_number', $orderNumber);
        } elseif (!empty($driverId)) {
            $query->where('driver_id', $driverId)->orderBy('id', 'desc');
        } else {
            return response()->json(['success' => 'Failed', 'error' => 'order_id or driver_id is required'], 400);
        }

        $order = $query->first();
        if (!$order) {
            return response()->json(['success' => 'Failed', 'error' => 'Order not found'], 404);
        }

        // Build default timeline if not present
        $timeline = is_array($order->status_timeline) ? $order->status_timeline : (json_decode($order->status_timeline ?? '[]', true) ?: []);
        if (empty($timeline)) {
            $orderDate = $order->purchased_at ? $order->purchased_at->format('d M Y, h:i A') : date('d M Y, h:i A');
            $timeline = [
                [
                    'status' => 'booked',
                    'title' => 'Booking Confirmed',
                    'date' => $orderDate,
                    'description' => 'Your partner marketing kit has been booked successfully.',
                    'is_completed' => true,
                    'is_current' => false,
                ],
                [
                    'status' => 'picked_up',
                    'title' => 'Picked Up',
                    'date' => date('d M Y, h:i A', strtotime($order->purchased_at ?? now() . ' + 4 hours')),
                    'description' => 'Picked up from Fiinway Central Warehouse.',
                    'is_completed' => in_array($order->delivery_status, ['picked_up', 'in_transit', 'out_for_delivery', 'delivered']),
                    'is_current' => $order->delivery_status === 'picked_up',
                ],
                [
                    'status' => 'in_transit',
                    'title' => 'In Transit',
                    'date' => date('d M Y, h:i A', strtotime($order->purchased_at ?? now() . ' + 1 day')),
                    'description' => 'Your parcel is in transit to destination sorting facility.',
                    'is_completed' => in_array($order->delivery_status, ['in_transit', 'out_for_delivery', 'delivered']),
                    'is_current' => $order->delivery_status === 'in_transit',
                ],
                [
                    'status' => 'out_for_delivery',
                    'title' => 'Out for Delivery',
                    'date' => date('d M Y, h:i A', strtotime($order->purchased_at ?? now() . ' + 2 days')),
                    'description' => 'Your parcel is with our delivery partner and will be delivered today.',
                    'is_completed' => in_array($order->delivery_status, ['out_for_delivery', 'delivered']),
                    'is_current' => $order->delivery_status === 'out_for_delivery',
                ],
                [
                    'status' => 'delivered',
                    'title' => 'Delivered',
                    'date' => $order->delivery_status === 'delivered' ? date('d M Y, h:i A') : 'Pending',
                    'description' => $order->delivery_status === 'delivered' ? 'Successfully delivered to partner address.' : 'Will be updated after successful delivery.',
                    'is_completed' => $order->delivery_status === 'delivered',
                    'is_current' => $order->delivery_status === 'delivered',
                ],
            ];
        }

        $trackingCode = $order->tracking_code ?? $order->tracking_number ?? ('FWP' . rand(1000000000, 9999999999));
        $courierName = $order->courier_partner ?? 'Blue Dart Express';

        return response()->json([
            'success' => 'success',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'kit_title' => $order->kit_title,
                'amount' => (float)$order->amount,
                'amount_formatted' => '₹' . number_format($order->amount, 0),
                'selected_size' => $order->selected_size ?? $order->tshirt_size ?? 'L',
                'delivery_status' => $order->delivery_status,
                'delivery_status_label' => ucfirst(str_replace('_', ' ', $order->delivery_status)),
                'tracking_code' => $trackingCode,
                'tracking_url' => $order->tracking_url ?? ("https://www.bluedart.com/tracking?track=" . $trackingCode),
                'courier_partner' => $courierName,
                'courier_partner_tagline' => 'Delivery with Trust',
                'courier_partner_rating' => '4.6 (10,245 reviews)',
                'expected_delivery' => $order->expected_delivery_date ?? ('Today, ' . date('d M') . ' by 6:00 PM'),
                'parcel_details' => [
                    'item' => $order->kit_title,
                    'weight' => '1.2 kg',
                    'type' => 'Standard Express Delivery',
                    'ref_no' => 'FW-' . date('Ymd') . '-' . $order->id,
                    'note' => 'Handle with care - Official Partner Kit',
                ],
                'delivery_executive' => [
                    'name' => $order->delivery_partner_name ?? 'Ravi Kumar',
                    'role' => 'Delivery Executive',
                    'phone' => $order->delivery_partner_phone ?? '+91 98765 43210',
                    'vehicle_no' => $order->delivery_partner_vehicle ?? 'DL 1L AB 1234',
                    'partner_id' => $order->delivery_partner_id ?? 'BD567890',
                ],
                'timeline' => $timeline,
                'purchased_at' => $order->purchased_at,
            ]
        ]);
    }

    /**
     * Record Driver Kit Purchase
     * Endpoint: POST /api/v1/driver/kit-purchase/record
     */
    public function recordPurchase(Request $request)
    {
        $driverId = $request->input('driver_id') ?? $request->input('id_driver');
        if (empty($driverId)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'driver_id parameter is required',
            ], 400);
        }

        $driver = Driver::where('id', $driverId)->orWhere('phone', $driverId)->first();
        if (!$driver) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Driver not found',
            ], 404);
        }

        $kit = DriverKit::find($request->kit_id);
        $kitTitle = $kit ? $kit->title : 'Official Partner Kit';
        $amount = (float)($request->amount ?? ($kit ? $kit->price : 499.00));
        $categoryCode = $kit ? $kit->category_code : $this->resolveDriverCategoryCode($driver);
        $paymentMethod = strtolower($request->payment_method ?? 'online');

        // If paying via Wallet Balance, verify MPIN and debit balance
        if ($paymentMethod === 'wallet') {
            $mpin = trim($request->mpin ?? '');
            if (empty($mpin)) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'M-PIN is required for wallet payment',
                ], 422);
            }

            $hashedMpin = md5($mpin);
            $mpinValid = ($driver->mdp === $hashedMpin)
                || (!empty($driver->m_pin) && $driver->m_pin === $mpin);

            if (!$mpinValid) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Incorrect M-PIN. Please try again.',
                ], 422);
            }

            $currentBalance = (float)($driver->amount ?? 0);
            if ($currentBalance < $amount) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Insufficient wallet balance to purchase kit.',
                ], 422);
            }

            // Debit Driver Wallet
            $driver->decrement('amount', $amount);

            // Record transaction
            \Illuminate\Support\Facades\DB::table('tj_conducteur_transaction')->insert([
                'id_conducteur' => $driver->id,
                'amount' => $amount,
                'payment_method' => 'wallet',
                'type' => 'debit',
                'description' => "Partner Starter Kit Purchase ({$kitTitle})",
                'creer' => date('Y-m-d H:i:s'),
                'modifier' => date('Y-m-d H:i:s'),
            ]);
        }

        $receiverPhone = $request->receiver_phone ?? ($driver->phone ?? '');
        $orderNumber = 'KIT-' . date('Ymd') . '-' . rand(1000, 9999);
        $trackingCode = 'FWP' . rand(1000000000, 9999999999);
        $transactionId = $request->transaction_id ?? ('TXN-' . Str::upper(Str::random(10)));
        $selectedSize = $request->selected_size ?? $request->tshirt_size ?? 'L';

        $order = DriverKitOrder::create([
            'driver_id' => $driver->id,
            'kit_id' => $kit ? $kit->id : null,
            'order_number' => $orderNumber,
            'category_code' => $categoryCode,
            'kit_title' => $kitTitle,
            'amount' => $amount,
            'tshirt_size' => $selectedSize,
            'selected_size' => $selectedSize,
            'receiver_name' => $request->receiver_name ?? trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? '')),
            'receiver_phone' => $receiverPhone,
            'shipping_address' => $request->shipping_address ?? ($driver->address ?? 'Registered Partner Address'),
            'pincode' => $request->pincode ?? '',
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentMethod === 'deduct_earnings' ? 'pending' : 'paid',
            'delivery_status' => 'booked',
            'tracking_code' => $trackingCode,
            'tracking_number' => $trackingCode,
            'courier_partner' => 'Blue Dart Express',
            'expected_delivery_date' => date('d M, 6:00 PM', strtotime('+3 days')),
            'transaction_id' => $transactionId,
            'purchased_at' => now(),
        ]);

        return response()->json([
            'success' => 'success',
            'message' => 'Partner kit purchase recorded successfully',
            'data' => $order,
        ]);
    }

    /**
     * Resolve Driver's primary category code ('bike', 'auto', 'car', 'home_service', 'all')
     */
    private function resolveDriverCategoryCode(Driver $driver): string
    {
        // 1. Direct check on Driver's primary selected category ID ($driver->category_id)
        if (!empty($driver->category_id)) {
            $cat = \Illuminate\Support\Facades\DB::table('tj_categorie_user')->where('id', $driver->category_id)->first();
            if ($cat) {
                if (in_array(strtolower($cat->type ?? ''), ['service', 'home_service', 'consumer_service'])) {
                    return 'home_service';
                }
                $name = strtolower(trim($cat->libelle ?? ''));
                $code = $this->matchCategoryKeyword($name);
                if ($code) return $code;
            }
        }

        // 2. Check specific subcategories in `tj_conducteur_categories` (checking subcategory_id FIRST)
        $subCatIds = \Illuminate\Support\Facades\DB::table('tj_conducteur_categories')
            ->where('driver_id', $driver->id)
            ->whereNotNull('subcategory_id')
            ->pluck('subcategory_id')
            ->toArray();
        if (!empty($subCatIds)) {
            $subCats = \Illuminate\Support\Facades\DB::table('tj_categorie_user')->whereIn('id', $subCatIds)->get();
            foreach ($subCats as $subCat) {
                if (in_array(strtolower($subCat->type ?? ''), ['service', 'home_service', 'consumer_service'])) {
                    return 'home_service';
                }
                $code = $this->matchCategoryKeyword(strtolower($subCat->libelle ?? ''));
                if ($code) return $code;
            }
        }

        // 3. Check Driver's registered vehicle in `tj_vehicule` -> `tj_type_vehicule`
        $vehicle = \Illuminate\Support\Facades\DB::table('tj_vehicule')
            ->join('tj_type_vehicule', 'tj_vehicule.id_type_vehicule', '=', 'tj_type_vehicule.id')
            ->where('tj_vehicule.id_conducteur', $driver->id)
            ->select('tj_type_vehicule.libelle')
            ->first();
        if ($vehicle && !empty($vehicle->libelle)) {
            $code = $this->matchCategoryKeyword(strtolower($vehicle->libelle));
            if ($code) return $code;
        }

        // 4. Check category_id entries in `tj_conducteur_categories`
        $catIds = \Illuminate\Support\Facades\DB::table('tj_conducteur_categories')
            ->where('driver_id', $driver->id)
            ->pluck('category_id')
            ->toArray();
        if (!empty($catIds)) {
            $cats = \Illuminate\Support\Facades\DB::table('tj_categorie_user')->whereIn('id', $catIds)->get();
            foreach ($cats as $cat) {
                if (in_array(strtolower($cat->type ?? ''), ['service', 'home_service', 'consumer_service'])) {
                    return 'home_service';
                }
                $code = $this->matchCategoryKeyword(strtolower($cat->libelle ?? ''));
                if ($code) return $code;
            }
        }

        // 5. Check vehicle model / make strings if available
        $model = strtolower($driver->model ?? '');
        if (!empty($model)) {
            $code = $this->matchCategoryKeyword($model);
            if ($code) return $code;
        }

        // Default to home_service if no vehicle, otherwise all
        return 'home_service';
    }

    private function matchCategoryKeyword(string $text): ?string
    {
        if (empty($text)) return null;

        // 1. Home Service / Specialist keywords (Highest Priority for service pros)
        if (
            str_contains($text, 'electric') ||
            str_contains($text, 'plumb') ||
            str_contains($text, 'clean') ||
            str_contains($text, 'repair') ||
            str_contains($text, 'appliance') ||
            str_contains($text, 'ac ') ||
            str_contains($text, 'ac_') ||
            str_contains($text, 'air condition') ||
            str_contains($text, 'carpenter') ||
            str_contains($text, 'painter') ||
            str_contains($text, 'paint') ||
            str_contains($text, 'salon') ||
            str_contains($text, 'beauty') ||
            str_contains($text, 'pest') ||
            str_contains($text, 'service') ||
            str_contains($text, 'technician') ||
            str_contains($text, 'mechanic') ||
            str_contains($text, 'handyman') ||
            str_contains($text, 'installation') ||
            str_contains($text, 'maintenance') ||
            str_contains($text, 'home')
        ) {
            return 'home_service';
        }

        // 2. Bike / Two Wheeler keywords
        if (
            str_contains($text, 'bike') ||
            str_contains($text, 'moto') ||
            str_contains($text, 'two wheeler') ||
            str_contains($text, '2-wheeler') ||
            str_contains($text, '2 wheeler') ||
            str_contains($text, 'scooter') ||
            str_contains($text, 'rider') ||
            str_contains($text, 'parcel delivery') ||
            str_contains($text, 'food delivery')
        ) {
            return 'bike';
        }

        // 3. Auto Rickshaw keywords (Must be specific to avoid matching "automobile" or transmission "auto")
        if (
            str_contains($text, 'rickshaw') ||
            str_contains($text, 'auto rickshaw') ||
            str_contains($text, 'auto driver') ||
            str_contains($text, 'e-rickshaw') ||
            str_contains($text, 'tuk-tuk') ||
            str_contains($text, 'tuk tuk') ||
            str_contains($text, '3-wheeler') ||
            str_contains($text, 'three wheeler') ||
            preg_match('/\b(auto)\b/i', $text)
        ) {
            return 'auto';
        }

        // 4. Car / Cab / Taxi / 4-Wheeler keywords
        if (
            str_contains($text, 'cab') ||
            str_contains($text, 'taxi') ||
            str_contains($text, 'car') ||
            str_contains($text, 'sedan') ||
            str_contains($text, 'suv') ||
            str_contains($text, 'four wheeler') ||
            str_contains($text, '4-wheeler') ||
            str_contains($text, 'hatchback') ||
            str_contains($text, 'driver') ||
            str_contains($text, 'truck') ||
            str_contains($text, 'packers')
        ) {
            return 'car';
        }

        return null;
    }

    private function getCategoryLabel(string $code): string
    {
        return match ($code) {
            'bike' => 'Two-Wheeler / Bike Taxi',
            'auto' => 'Auto Rickshaw',
            'car' => 'Cab / Four-Wheeler',
            'home_service' => 'Home Service Specialist',
            default => 'Home Service Specialist',
        };
    }
}

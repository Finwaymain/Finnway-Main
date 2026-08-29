<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\DriverKit;
use App\Models\DriverKitOrder;
use App\Helpers\RazorpayConfig;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DriverKitWebController extends Controller
{
    /**
     * Mobile-friendly Web Checkout page for Driver Kit
     * URL: /onboarding/kit-purchase
     */
    public function showCheckout(Request $request)
    {
        $driverId = $request->query('driver_id') ?? $request->query('id_driver');
        $phoneParam = $request->query('phone');
        $kitId = $request->query('kit_id');

        $driver = null;
        if ($driverId) {
            $driver = Driver::where('id', $driverId)->orWhere('phone', $driverId)->first();
        }
        if (!$driver && $phoneParam) {
            $driver = Driver::where('phone', $phoneParam)->first();
        }

        // Determine Category Code from Driver profile or query
        $category = 'home_service';
        if ($driver) {
            $category = $this->resolveDriverCategoryCode($driver);
        } else if ($request->filled('category')) {
            $category = $request->query('category');
        }

        $kit = null;
        if ($kitId) {
            $kit = DriverKit::find($kitId);
        }
        if (!$kit && $category) {
            $kit = DriverKit::where('category_code', $category)->where('is_active', true)->first();
        }
        if (!$kit) {
            $kit = DriverKit::where('category_code', 'all')->where('is_active', true)->first()
                ?? DriverKit::where('is_active', true)->first();
        }

        // Check if already ordered by driver ID OR phone number
        $existingOrder = null;
        if ($driver) {
            $driverPhone = trim($driver->phone ?? '');
            $cleanPhone = preg_replace('/[^0-9]/', '', $driverPhone);
            $shortPhone = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            $existingOrder = DriverKitOrder::where(function($q) use ($driver, $driverPhone, $shortPhone) {
                $q->where('driver_id', $driver->id);
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
        }

        // All active kits for interactive dropdown selection
        $allKits = DriverKit::where('is_active', true)->orderBy('price', 'desc')->get();

        // Resolve Razorpay Key
        $razorpayKey = RazorpayConfig::resolve()['key'] ?? '';
        $walletBalance = (float)($driver->amount ?? 0);

        return view('driver_kits.web_checkout', compact('driver', 'kit', 'allKits', 'category', 'existingOrder', 'razorpayKey', 'walletBalance'));
    }

    /**
     * Process checkout submission
     */
    public function submitCheckout(Request $request)
    {
        $request->validate([
            'driver_id' => 'required',
            'kit_id' => 'required',
            'tshirt_size' => 'required|string',
            'receiver_name' => 'required|string|max:150',
            'receiver_phone' => 'required|string|max:30',
            'shipping_address' => 'required|string',
        ]);

        $driver = Driver::where('id', $request->driver_id)->orWhere('phone', $request->driver_id)->firstOrFail();
        $kit = DriverKit::findOrFail($request->kit_id);

        $paymentMethod = strtolower($request->payment_method ?? 'online');
        $amount = (float)$kit->price;

        // Handle Wallet Payment
        if ($paymentMethod === 'wallet') {
            $mpin = trim($request->mpin ?? '');
            if (empty($mpin)) {
                return back()->with('error', 'Please enter your M-PIN to pay via Wallet.');
            }

            $hashedMpin = md5($mpin);
            $mpinValid = ($driver->mdp === $hashedMpin) || (!empty($driver->m_pin) && $driver->m_pin === $mpin);

            if (!$mpinValid) {
                return back()->with('error', 'Incorrect M-PIN. Please try again.');
            }

            $currBal = (float)($driver->amount ?? 0);
            if ($currBal < $amount) {
                return back()->with('error', 'Insufficient Wallet Balance (Available: ₹' . number_format($currBal, 2) . '). Please use UPI or Razorpay.');
            }

            // Deduct from Driver Wallet
            $driver->decrement('amount', $amount);

            // Record transaction in tj_conducteur_transaction
            DB::table('tj_conducteur_transaction')->insert([
                'id_conducteur' => $driver->id,
                'amount' => $amount,
                'payment_method' => 'wallet',
                'type' => 'debit',
                'description' => "Partner Welcome Kit Purchase ({$kit->title})",
                'creer' => date('Y-m-d H:i:s'),
                'modifier' => date('Y-m-d H:i:s'),
            ]);
        }

        $orderNumber = 'KIT-' . date('Ymd') . '-' . rand(1000, 9999);
        $transactionId = $request->transaction_id ?? ('TXN-' . Str::upper(Str::random(12)));

        $order = DriverKitOrder::create([
            'driver_id' => $driver->id,
            'kit_id' => $kit->id,
            'order_number' => $orderNumber,
            'category_code' => $kit->category_code,
            'kit_title' => $kit->title,
            'amount' => $amount,
            'tshirt_size' => $request->tshirt_size,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'shipping_address' => $request->shipping_address,
            'payment_method' => $paymentMethod,
            'payment_status' => 'paid',
            'delivery_status' => 'processing',
            'transaction_id' => $transactionId,
            'purchased_at' => now(),
        ]);

        return view('driver_kits.web_success', compact('order', 'kit', 'driver'));
    }

    /**
     * Resolve Driver's primary category code ('bike', 'auto', 'car', 'home_service', 'all')
     */
    private function resolveDriverCategoryCode(Driver $driver): string
    {
        if (!empty($driver->category_id)) {
            $cat = DB::table('tj_categorie_user')->where('id', $driver->category_id)->first();
            if ($cat) {
                if (in_array(strtolower($cat->type ?? ''), ['service', 'home_service', 'consumer_service'])) {
                    return 'home_service';
                }
                $name = strtolower(trim($cat->libelle ?? ''));
                $code = $this->matchCategoryKeyword($name);
                if ($code) return $code;
            }
        }

        $subCatIds = DB::table('tj_conducteur_categories')
            ->where('driver_id', $driver->id)
            ->whereNotNull('subcategory_id')
            ->pluck('subcategory_id')
            ->toArray();
        if (!empty($subCatIds)) {
            $subCats = DB::table('tj_categorie_user')->whereIn('id', $subCatIds)->get();
            foreach ($subCats as $subCat) {
                if (in_array(strtolower($subCat->type ?? ''), ['service', 'home_service', 'consumer_service'])) {
                    return 'home_service';
                }
                $code = $this->matchCategoryKeyword(strtolower($subCat->libelle ?? ''));
                if ($code) return $code;
            }
        }

        $vehicle = DB::table('tj_vehicule')
            ->join('tj_type_vehicule', 'tj_vehicule.id_type_vehicule', '=', 'tj_type_vehicule.id')
            ->where('tj_vehicule.id_conducteur', $driver->id)
            ->select('tj_type_vehicule.libelle')
            ->first();
        if ($vehicle && !empty($vehicle->libelle)) {
            $code = $this->matchCategoryKeyword(strtolower($vehicle->libelle));
            if ($code) return $code;
        }

        $catIds = DB::table('tj_conducteur_categories')
            ->where('driver_id', $driver->id)
            ->pluck('category_id')
            ->toArray();
        if (!empty($catIds)) {
            $cats = DB::table('tj_categorie_user')->whereIn('id', $catIds)->get();
            foreach ($cats as $cat) {
                if (in_array(strtolower($cat->type ?? ''), ['service', 'home_service', 'consumer_service'])) {
                    return 'home_service';
                }
                $code = $this->matchCategoryKeyword(strtolower($cat->libelle ?? ''));
                if ($code) return $code;
            }
        }

        $model = strtolower($driver->model ?? '');
        if (!empty($model)) {
            $code = $this->matchCategoryKeyword($model);
            if ($code) return $code;
        }

        return 'home_service';
    }

    private function matchCategoryKeyword(string $text): ?string
    {
        if (empty($text)) return null;

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
}

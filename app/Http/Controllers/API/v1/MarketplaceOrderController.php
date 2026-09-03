<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceOrderItem;
use App\Models\MarketplaceProduct;
use App\Models\UserApp;
use App\Http\Controllers\API\v1\GcmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class MarketplaceOrderController extends Controller
{
    private function getAuthenticatedUserId(Request $request)
    {
        $keys = ['user_id', 'user-id', 'id_user', 'id-user', 'driver_id', 'driver-id', 'id_conducteur'];
        foreach ($keys as $key) {
            $val = $request->json($key) ?? $request->input($key) ?? $request->query($key) ?? $request->header($key);
            if (!empty($val)) {
                return is_array($val) ? $val[0] : $val;
            }
        }

        $serverKeys = ['HTTP_USER_ID', 'HTTP_USER_ID', 'HTTP_ID_USER', 'HTTP_DRIVER_ID', 'HTTP_ID_CONDUCTEUR', 'HTTP_ACCESSTOKEN'];
        foreach ($serverKeys as $sKey) {
            if (!empty($_SERVER[$sKey])) {
                return $_SERVER[$sKey];
            }
        }

        $accessToken = $request->header('accesstoken') ?? $request->query('accesstoken') ?? $request->input('accesstoken') ?? $request->json('accesstoken');
        if (!empty($accessToken)) {
            $userAccess = DB::table('users_access')->where('accesstoken', $accessToken)->first();
            if ($userAccess && !empty($userAccess->user_id)) {
                return $userAccess->user_id;
            }
        }

        return null;
    }

    /**
     * Resolve the authenticated seller identity: [user_id, user_type, phone, last10]
     * Ensures strict uniqueness by phone number across all user and driver roles.
     */
    private function getAuthenticatedSeller(Request $request): array
    {
        $userId = null;
        $userType = $request->input('user_type') ?? $request->header('user-type') ?? $request->header('usertype');
        $phone = $request->input('phone') ?? $request->input('seller_phone') ?? $request->query('phone') ?? $request->header('phone') ?? $request->header('seller-phone');

        // 1. Resolve via access token
        $accessToken = $request->header('accesstoken') ?? $request->query('accesstoken') ?? $request->input('accesstoken');
        if (!empty($accessToken)) {
            $userAccess = DB::table('users_access')->where('accesstoken', $accessToken)->first();
            if ($userAccess && !empty($userAccess->user_id)) {
                $userId = $userAccess->user_id;
                $userType = ($userAccess->user_type === 'driver') ? 'driver' : 'customer';
            }
        }

        // 2. Fallback to explicit ID parameters if not resolved from token
        if (!$userId) {
            $keys = ['driver_id', 'id_conducteur', 'user_id', 'id_user'];
            foreach ($keys as $key) {
                $val = $request->input($key) ?? $request->query($key) ?? $request->header($key);
                if (!empty($val)) {
                    $userId = $val;
                    if (str_contains($key, 'driver') || str_contains($key, 'conducteur')) {
                        $userType = 'driver';
                    }
                    break;
                }
            }
        }

        if (!$userType) {
            $userType = ($request->has('driver_id') || $request->has('id_conducteur') || $request->input('user_type') === 'driver') ? 'driver' : 'customer';
        }

        // 3. Resolve phone from database if not directly supplied
        if (empty($phone) && $userId) {
            if ($userType === 'driver') {
                $sellerRecord = DB::table('tj_conducteur')->where('id', $userId)->first();
                $phone = $sellerRecord->phone ?? null;
            } else {
                $sellerRecord = DB::table('tj_user_app')->where('id', $userId)->first();
                $phone = $sellerRecord->phone ?? null;
            }
        }

        // 4. If phone is provided but userId is not, resolve user from phone
        if (!empty($phone) && !$userId) {
            $digits = preg_replace('/[^0-9]/', '', (string)$phone);
            $last10 = substr($digits, -10);
            if ($userType === 'driver') {
                $sellerRecord = DB::table('tj_conducteur')->where('phone', 'like', "%$last10%")->first();
                if ($sellerRecord) {
                    $userId = $sellerRecord->id;
                }
            } else {
                $sellerRecord = DB::table('tj_user_app')->where('phone', 'like', "%$last10%")->first();
                if ($sellerRecord) {
                    $userId = $sellerRecord->id;
                }
            }
        }

        $last10 = !empty($phone) ? substr(preg_replace('/[^0-9]/', '', (string)$phone), -10) : '';
        $normPhone = !empty($last10) ? '+91' . $last10 : ($phone ?? '');

        return [
            'user_id'   => $userId,
            'user_type' => $userType ?: 'customer',
            'phone'     => $normPhone,
            'last10'    => $last10,
        ];
    }

    /**
     * Record wallet transaction history for customer or driver.
     */
    private function recordWalletTransaction($userId, $userType, $amount, $type, $description, $txnId)
    {
        $dateOnly = date('Y-m-d');
        $dateTime = date('Y-m-d H:i:s');
        $deductionType = ($type === 'credit') ? 1 : 0;

        if ($userType === 'driver') {
            DB::table('tj_conducteur_transaction')->insert([
                'id_conducteur'   => $userId,
                'amount'          => (string)$amount,
                'type'            => $type,
                'deduction_type'  => $deductionType,
                'payment_method'  => 'Fiinway Wallet',
                'payment_status'  => 'success',
                'description'     => $description,
                'txn_id'          => $txnId,
                'date'            => $dateOnly,
                'creer'           => $dateTime,
                'modifier'        => $dateTime,
            ]);
        } else {
            DB::table('tj_transaction')->insert([
                'id_user_app'     => $userId,
                'user_type'       => 'customer',
                'amount'          => (string)$amount,
                'type'            => $type,
                'deduction_type'  => $deductionType,
                'payment_method'  => 'Fiinway Wallet',
                'payment_status'  => 'success',
                'description'     => $description,
                'txn_id'          => $txnId,
                'date'            => $dateOnly,
                'creer'           => $dateTime,
                'modifier'        => $dateTime,
            ]);
        }
    }

    /**
     * Calculate all active taxes applicable for the order based on payment method.
     */
    public function calculateMarketplaceTaxes(float $subtotal, string $paymentMethod = 'wallet'): array
    {
        $activeTaxes = DB::table('tj_tax')->where('statut', 'yes')->get();
        $taxBreakdown = [];
        $totalTaxAmount = 0;
        $taxLabels = [];
        $totalRate = 0;

        $normMethod = strtolower(trim($paymentMethod));
        if (str_contains($normMethod, 'wallet')) $normMethod = 'wallet';
        elseif (str_contains($normMethod, 'razorpay') || str_contains($normMethod, 'online') || str_contains($normMethod, 'card')) $normMethod = 'online';
        elseif (str_contains($normMethod, 'upi') || str_contains($normMethod, 'gpay') || str_contains($normMethod, 'phonepe')) $normMethod = 'upi';
        elseif (str_contains($normMethod, 'cash') || str_contains($normMethod, 'cod')) $normMethod = 'cash';

        foreach ($activeTaxes as $tax) {
            $applicable = strtolower($tax->applicable_on ?? '');
            $methods = array_map('trim', explode(',', $applicable));

            $isApplicable = empty($applicable) 
                || in_array($normMethod, $methods) 
                || in_array('all', $methods)
                || ($normMethod === 'online' && (in_array('online', $methods) || in_array('upi', $methods) || in_array('card', $methods)))
                || ($normMethod === 'upi' && (in_array('upi', $methods) || in_array('online', $methods)))
                || ($normMethod === 'wallet' && in_array('wallet', $methods))
                || ($normMethod === 'cash' && in_array('cash', $methods));

            if ($isApplicable) {
                $val = floatval($tax->value ?? 0);
                $isPercent = (strtolower($tax->type ?? '') === 'percentage' || str_contains(strtolower($tax->type ?? ''), 'percent'));

                if ($isPercent) {
                    $amt = round(($subtotal * $val) / 100, 2);
                    $label = "{$tax->libelle} ({$val}%)";
                    $totalRate += $val;
                } else {
                    $amt = round($val, 2);
                    $label = "{$tax->libelle} (₹{$val})";
                }

                $taxBreakdown[] = [
                    'id'         => $tax->id,
                    'name'       => $tax->libelle,
                    'type'       => $tax->type,
                    'rate'       => $val,
                    'rate_label' => $isPercent ? "{$val}%" : "₹{$val}",
                    'amount'     => $amt,
                    'label'      => $label,
                ];

                $totalTaxAmount += $amt;
                $taxLabels[] = $label;
            }
        }

        return [
            'total_tax_amount' => round($totalTaxAmount, 2),
            'taxes'            => $taxBreakdown,
            'tax_name'         => !empty($taxLabels) ? implode(', ', $taxLabels) : 'GST',
            'tax_rate'         => $totalRate,
        ];
    }

    /**
     * Get checkout summary breakdown with active taxes and commission calculations.
     */
    public function checkoutSummary(Request $request)
    {
        $items = $request->input('items', []);
        $subtotal = 0;

        foreach ($items as $item) {
            $p = MarketplaceProduct::find($item['product_id'] ?? 0);
            if ($p) {
                $qty = intval($item['quantity'] ?? 1);
                $subtotal += floatval($p->price) * $qty;
            }
        }

        if ($subtotal <= 0 && $request->has('price')) {
            $subtotal = floatval($request->input('price')) * intval($request->input('quantity', 1));
        }

        $deliveryCharge = floatval($request->input('delivery_charge', 0));
        $paymentMethod = strtolower($request->input('payment_method', 'wallet'));

        // 1. Calculate All Active Applicable Taxes from tj_tax
        $taxCalc = $this->calculateMarketplaceTaxes($subtotal, $paymentMethod);
        $totalTaxAmount = $taxCalc['total_tax_amount'];
        $totalPayable = round($subtotal + $deliveryCharge + $totalTaxAmount, 2);

        // 2. Fetch active Commission Settings
        $commSetting = \App\Models\MarketplaceCommissionSetting::getActiveSetting();
        $commType = $commSetting->commission_type ?? 'percentage';
        $commRate = floatval($commSetting->commission_value ?? 5);
        $adminCommissionAmount = 0;

        if ($commType === 'percentage') {
            $adminCommissionAmount = round(($subtotal * $commRate) / 100, 2);
        } else {
            $adminCommissionAmount = round($commRate, 2);
        }
        $sellerPayoutAmount = max(0, round($subtotal - $adminCommissionAmount, 2));

        return response()->json([
            'success' => 'Success',
            'data' => [
                'subtotal'                 => $subtotal,
                'delivery_charge'          => $deliveryCharge,
                'taxes'                    => $taxCalc['taxes'],
                'total_tax_amount'         => $totalTaxAmount,
                'tax'                      => [
                    'name'   => $taxCalc['tax_name'],
                    'rate'   => $taxCalc['tax_rate'],
                    'amount' => $totalTaxAmount,
                ],
                'total_payable'            => $totalPayable,
                'admin_commission'         => [
                    'type'   => $commType,
                    'rate'   => $commRate,
                    'amount' => $adminCommissionAmount,
                ],
                'estimated_seller_payout'  => $sellerPayoutAmount,
            ]
        ]);
    }

    /**
     * Place a new order with Tax Calculation and Escrow Settlement Hold.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_address' => 'required|string',
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error' => $validator->errors()->first()
            ], 420);
        }

        // 1. Identify Buyer accurately (Driver vs UserApp)
        $userType = $request->input('user_type') ?? $request->query('user_type') ?? $request->header('user_type');
        $driverId = $request->input('driver_id') ?? $request->query('driver_id') ?? $request->header('driver_id');
        $userIdInput = $request->input('user_id') ?? $request->query('user_id') ?? $request->header('user_id');
        $phoneInput = $request->input('phone') ?? $request->query('phone') ?? $request->header('phone');
        $accessToken = $request->header('accesstoken') ?? $request->query('accesstoken') ?? $request->input('accesstoken');

        $buyer = null;
        $buyerType = 'customer';

        if ($accessToken) {
            $userAccess = DB::table('users_access')->where('accesstoken', $accessToken)->first();
            if ($userAccess && !empty($userAccess->user_id)) {
                $uType = ($userAccess->user_type === 'driver') ? 'driver' : 'user';
                if ($uType === 'driver') {
                    $buyer = \App\Models\Driver::find($userAccess->user_id);
                    if ($buyer) $buyerType = 'driver';
                } else {
                    $buyer = UserApp::find($userAccess->user_id);
                    if ($buyer) $buyerType = 'customer';
                }
            }
        }

        if (!$buyer && (!empty($driverId) || $userType === 'driver')) {
            $id = $driverId ?: $userIdInput;
            if ($id) {
                $buyer = \App\Models\Driver::find($id);
                if ($buyer) $buyerType = 'driver';
            }
        }

        if (!$buyer && !empty($userIdInput)) {
            if ($userType === 'driver') {
                $buyer = \App\Models\Driver::find($userIdInput);
                if ($buyer) $buyerType = 'driver';
            }
            if (!$buyer) {
                $buyer = UserApp::find($userIdInput);
                if ($buyer) $buyerType = 'customer';
            }
            if (!$buyer) {
                $buyer = \App\Models\Driver::find($userIdInput);
                if ($buyer) $buyerType = 'driver';
            }
        }

        if (!$buyer && !empty($phoneInput)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneInput);
            if (strlen($cleanPhone) >= 10) {
                $last10 = substr($cleanPhone, -10);
                $buyer = UserApp::where('phone', 'like', "%{$last10}%")->first();
                if ($buyer) {
                    $buyerType = 'customer';
                } else {
                    $buyer = \App\Models\Driver::where('phone', 'like', "%{$last10}%")->first();
                    if ($buyer) $buyerType = 'driver';
                }
            }
        }

        if (!$buyer) {
            $buyer = UserApp::first();
        }

        $userId = $buyer->id;

        $itemsData = $request->input('items');
        $subtotal = 0;
        $validatedItems = [];
        $firstSellerId = null;

        // Pre-validate products, stock, and seller
        foreach ($itemsData as $item) {
            $product = MarketplaceProduct::find($item['product_id']);
            if (!$product) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Product not found: ID ' . $item['product_id']
                ], 404);
            }

            if (in_array(strtolower($product->status ?? ''), ['sold', 'rejected', 'inactive', 'deleted', 'disabled'])) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Product is not available for purchase: ' . $product->title
                ], 422);
            }

            if (strval($product->user_id) === strval($userId) && $buyerType !== 'driver') {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'You cannot purchase your own product: ' . $product->title
                ], 422);
            }

            if ($product->stock_quantity < $item['quantity']) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Insufficient stock for product: ' . $product->title . ' (Available: ' . $product->stock_quantity . ')'
                ], 422);
            }

            $itemPrice = floatval($product->price);
            $subtotal += $itemPrice * $item['quantity'];
            if (!$firstSellerId) {
                $firstSellerId = $product->user_id;
            }

            $validatedItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $itemPrice
            ];
        }

        $deliveryCharge = floatval($request->input('delivery_charge', 0));
        $paymentMethod = strtolower($request->input('payment_method', 'wallet'));

        // 1. Calculate All Active Applicable Taxes from tj_tax
        $taxCalc = $this->calculateMarketplaceTaxes($subtotal, $paymentMethod);
        $totalTaxAmount = $taxCalc['total_tax_amount'];
        $taxName = $taxCalc['tax_name'];
        $taxRate = $taxCalc['tax_rate'];
        $totalPayable = round($subtotal + $deliveryCharge + $totalTaxAmount, 2);

        // 2. Calculate Admin Commission & Seller Net Payout
        $commSetting = \App\Models\MarketplaceCommissionSetting::getActiveSetting();
        $commType = $commSetting->commission_type ?? 'percentage';
        $commRate = floatval($commSetting->commission_value ?? 5);
        $adminCommissionAmount = 0;

        if ($commType === 'percentage') {
            $adminCommissionAmount = round(($subtotal * $commRate) / 100, 2);
        } else {
            $adminCommissionAmount = round($commRate, 2);
        }
        $sellerPayoutAmount = max(0, round($subtotal - $adminCommissionAmount, 2));

        $txnId = $request->input('txn_id', 'TXN_' . time() . '_' . rand(1000, 9999));
        $buyerBalance = floatval($buyer->amount ?? 0);

        // Check transaction sum fallback if balance is 0
        if ($buyerBalance == 0) {
            $uType = ($buyerType === 'driver') ? 'driver' : 'customer';
            $txnSum = DB::table('tj_transaction')
                ->where('id_user_app', $buyer->id)
                ->where(function($q) use ($uType) {
                    $q->where('user_type', $uType)
                      ->orWhereNull('user_type')
                      ->orWhere('user_type', '');
                })
                ->sum('amount');
            if ($txnSum > 0) {
                $buyerBalance = floatval($txnSum);
                $buyer->amount = $buyerBalance;
                $buyer->save();
            }
        }

        // Check wallet balance & M-PIN ONLY if payment method is wallet
        if ($paymentMethod === 'wallet') {
            if ($buyerBalance < $totalPayable) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Insufficient wallet balance. Required: ₹' . number_format($totalPayable, 2) . ', Available: ₹' . number_format($buyerBalance, 2)
                ], 422);
            }

            $mPin = $request->input('m_pin') ?? $request->input('mpin');
            if (empty($mPin)) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Wallet M-PIN is required to authorize payment.',
                    'require_mpin' => true
                ], 422);
            }

            $userMPin = (string)($buyer->m_pin ?? '');
            $userMdp  = (string)($buyer->mdp ?? '');
            $enteredMPin = (string)$mPin;

            $isMPinValid = (!empty($userMPin) && $userMPin === $enteredMPin) || 
                           (!empty($userMdp) && $userMdp === md5($enteredMPin));

            if (empty($userMPin) && empty($userMdp)) {
                // Initialize user M-PIN if not set yet
                $buyer->m_pin = $enteredMPin;
                $buyer->mdp = md5($enteredMPin);
                $buyer->save();
                $isMPinValid = true;
            }

            if (!$isMPinValid) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Invalid Wallet M-PIN. Please enter your correct M-PIN.',
                    'require_mpin' => true
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // 1. Deduct buyer wallet if wallet payment (Includes Tax & Delivery)
            if ($paymentMethod === 'wallet') {
                $buyer->amount = max(0, $buyerBalance - $totalPayable);
                $buyer->save();
            }

            $firstSellerType = $validatedItems[0]['product']->user_type ?? 'customer';

            // Resolve seller phone number
            $firstSeller = ($firstSellerType === 'driver') ? \App\Models\Driver::find($firstSellerId) : UserApp::find($firstSellerId);
            if (!$firstSeller) {
                $firstSeller = UserApp::find($firstSellerId) ?? \App\Models\Driver::find($firstSellerId);
            }
            $sellerPhone = $firstSeller->phone ?? '';

            $buyerPhone = $request->input('phone') ?? ($buyer->phone ?? '');

            // 2. Create order with full metadata, taxes, unique Purchasing ID & Order Number
            $order = MarketplaceOrder::create([
                'user_id'                 => $userId,
                'buyer_type'              => $buyerType,
                'seller_id'               => $firstSellerId,
                'seller_type'             => $firstSellerType,
                'buyer_phone'             => $buyerPhone,
                'seller_phone'            => $sellerPhone,
                'total_amount'            => $totalPayable,
                'subtotal'                => $subtotal,
                'delivery_charge'         => $deliveryCharge,
                'tax_name'                => $taxName,
                'tax_rate'                => $taxRate,
                'tax_amount'              => $totalTaxAmount,
                'payment_method'          => $paymentMethod === 'wallet' ? 'Fiinway Wallet' : ucfirst($paymentMethod),
                'payment_status'          => 'success',
                'txn_id'                  => $txnId,
                'delivery_address'        => $request->input('delivery_address'),
                'phone'                   => $buyerPhone,
                'contact_name'            => $request->input('contact_name', trim(($buyer->prenom ?? '') . ' ' . ($buyer->nom ?? ''))),
                'city'                    => $request->input('city', ''),
                'pincode'                 => $request->input('pincode', ''),
                'status'                  => 'pending',
                'delivery_days'           => 3,
                'status_notes'            => 'New Order placed. Awaiting seller confirmation.',
                'admin_commission_type'   => $commType,
                'admin_commission_rate'   => $commRate,
                'admin_commission_amount' => $adminCommissionAmount,
                'seller_payout_amount'    => $sellerPayoutAmount,
                'payout_status'           => 'pending', // ESCROW: Seller wallet will be automatically credited upon delivery!
            ]);

            // Assign standard unique Order Number & Purchase Tracking ID
            $uniqueOrderNum = 'FW-ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $uniquePurchaseId = 'FWMP-' . date('Ymd') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);
            $order->order_number = $uniqueOrderNum;
            $order->purchase_id  = $uniquePurchaseId;
            $order->save();

            // 3. Process items & reduce stock
            $date = date('Y-m-d H:i:s');
            $sellersToNotify = [];

            foreach ($validatedItems as $vItem) {
                $product = $vItem['product'];
                $qty = $vItem['quantity'];
                $price = $vItem['price'];

                // Create order item
                MarketplaceOrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'price'      => $price,
                ]);

                // Update product stock
                $product->stock_quantity = max(0, $product->stock_quantity - $qty);
                if ($product->stock_quantity <= 0) {
                    $product->status = 'sold';
                }
                $product->save();

                // Seller record to notify (resolving accurately by phone & user record)
                $sellerType = $product->user_type ?? 'customer';
                $seller = ($sellerType === 'driver') ? \App\Models\Driver::find($product->user_id) : UserApp::find($product->user_id);
                if (!$seller) {
                    $seller = UserApp::find($product->user_id) ?? \App\Models\Driver::find($product->user_id);
                }

                if ($seller) {
                    $sellersToNotify[$seller->id][] = [
                        'seller' => $seller,
                        'product_title' => $product->title,
                        'qty' => $qty
                    ];
                }
            }

            // 4. Transaction record for buyer (Deduction)
            if ($paymentMethod === 'wallet') {
                $buyerType = ($buyer instanceof \App\Models\Driver) ? 'driver' : 'customer';
                $productSummary = implode(', ', array_map(fn($v) => $v['product']->title, $validatedItems));
                $taxNote = $totalTaxAmount > 0 ? " (Incl. {$taxName} ₹{$totalTaxAmount})" : "";
                $this->recordWalletTransaction(
                    $userId,
                    $buyerType,
                    $totalPayable,
                    'debit',
                    "Marketplace Purchase: Purchased '{$productSummary}'{$taxNote} - {$order->order_number}",
                    $txnId
                );
            }

            DB::commit();

            // Trigger dynamic referral cashback reward
            try {
                $buyerType = ($buyer instanceof \App\Models\Driver) ? 'driver' : 'customer';
                \App\Services\ReferralRewardService::processReward(
                    $userId,
                    $buyerType,
                    'marketplace_order',
                    $totalPayable,
                    "Marketplace Order {$order->order_number}"
                );
            } catch (\Exception $ex) {
                \Illuminate\Support\Facades\Log::error("Marketplace referral reward error: " . $ex->getMessage());
            }

            // 5. Send Notifications to Sellers (Targeted via phone & user model)
            foreach ($sellersToNotify as $sellerId => $notifs) {
                $seller = $notifs[0]['seller'];
                $productTitles = [];
                foreach ($notifs as $n) {
                    $productTitles[] = "'{$n['product_title']}' (x{$n['qty']})";
                }
                $productsStr = implode(', ', $productTitles);
                $buyerDisplayName = trim(($buyer->prenom ?? '') . ' ' . ($buyer->nom ?? '')) ?: 'Customer';
                $buyerContact = $order->buyer_phone ?: ($buyer->phone ?? 'N/A');
                $notifTitle = "🔔 New Order {$order->order_number}";
                $notifBody = "🔔 New Order: {$productsStr} for ₹" . number_format($subtotal, 2) . " from {$buyerDisplayName} (Phone: {$buyerContact}). Please confirm or reject.";

                // Save to database notifications
                DB::table('tj_notification')->insert([
                    'to_id' => $seller->id,
                    'from_id' => $userId,
                    'titre' => $notifTitle,
                    'message' => $notifBody,
                    'statut' => 'unread',
                    'type' => 'marketplace',
                    'creer' => $date,
                    'modifier' => $date,
                ]);

                // Send push notification
                if (!empty($seller->fcm_id)) {
                    try {
                        $fcmMessage = [
                            'title' => $notifTitle,
                            'body' => $notifBody,
                            'tag' => 'marketplace_order_new',
                            'order_id' => (string)$order->id,
                            'order_number' => (string)$order->order_number,
                            'purchase_id' => (string)$order->purchase_id,
                            'status' => 'pending',
                        ];
                        GcmController::sendNotification($seller->fcm_id, $fcmMessage);
                    } catch (\Exception $e) {
                        // ignore GCM errors
                    }
                }
            }

            return response()->json([
                'success' => 'Success',
                'message' => 'Order placed successfully',
                'data' => MarketplaceOrder::with(['items.product.images', 'buyer'])->find($order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => 'Failed',
                'error' => 'Failed to process order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buyer's order history.
     */
    public function buyerOrders(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request)
               ?? $request->input('user_id')
               ?? $request->query('user_id')
               ?? $request->input('driver_id')
               ?? $request->query('driver_id')
               ?? 1;

        $orders = MarketplaceOrder::where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('user_id', (string)$userId)
              ->orWhere('user_id', (int)$userId);
        })
        ->with(['items.product.images', 'items.product.seller'])
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($order) {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $sUser = $item->product->seller ?? UserApp::find($item->product->user_id) ?? \App\Models\Driver::find($item->product->user_id);
                    $sellerName = 'Seller';
                    $sellerPhone = '';
                    if ($sUser) {
                        $sellerName = trim(($sUser->prenom ?? '') . ' ' . ($sUser->nom ?? ''));
                        if (empty($sellerName)) {
                            $sellerName = $sUser->name ?? $sUser->phone ?? 'Seller';
                        }
                        $sellerPhone = $sUser->phone ?? $sUser->mobile ?? '';
                    }
                    $item->product->seller_info = [
                        'id' => $item->product->user_id,
                        'name' => $sellerName,
                        'phone' => $sellerPhone,
                    ];
                }
            }
            return $order;
        });

        return response()->json([
            'success' => 'Success',
            'data' => $orders
        ]);
    }

    /**
     * Seller's order management history.
     * Strictly filters orders by the seller's unique phone number / verified seller identity.
     */
    public function sellerOrders(Request $request)
    {
        $seller = $this->getAuthenticatedSeller($request);
        if (empty($seller['phone']) && empty($seller['user_id'])) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        // Get orders matching this seller's phone or containing their products
        $orders = MarketplaceOrder::where(function($q) use ($seller) {
            if (!empty($seller['last10'])) {
                $q->where('seller_phone', 'like', '%' . $seller['last10'] . '%');
            }
            if (!empty($seller['user_id'])) {
                if (!empty($seller['last10'])) {
                    $q->orWhere(function($q2) use ($seller) {
                        $q2->where('seller_id', $seller['user_id'])
                           ->where('seller_type', $seller['user_type']);
                    });
                } else {
                    $q->where('seller_id', $seller['user_id'])
                      ->where('seller_type', $seller['user_type']);
                }
            }
        })
        ->orWhereHas('items.product', function ($query) use ($seller) {
            if (!empty($seller['last10'])) {
                $query->where('seller_phone', 'like', '%' . $seller['last10'] . '%');
            }
            if (!empty($seller['user_id'])) {
                if (!empty($seller['last10'])) {
                    $query->orWhere(function($q2) use ($seller) {
                        $q2->where('user_id', $seller['user_id'])
                           ->where('user_type', $seller['user_type']);
                    });
                } else {
                    $query->where('user_id', $seller['user_id'])
                          ->where('user_type', $seller['user_type']);
                }
            }
        })
        ->with(['items.product.images', 'buyer'])
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($order) use ($seller) {
            // Filter items so the seller only sees their own products in the order listing
            $order->items = $order->items->filter(function ($item) use ($seller) {
                if (!$item->product) return false;
                if (!empty($seller['last10']) && !empty($item->product->seller_phone)) {
                    if (str_contains($item->product->seller_phone, $seller['last10'])) return true;
                }
                if (!empty($seller['user_id'])) {
                    return (strval($item->product->user_id) === strval($seller['user_id'])) &&
                           ($item->product->user_type === $seller['user_type']);
                }
                return false;
            })->values();
            return $order;
        });

        return response()->json([
            'success' => 'Success',
            'data' => $orders
        ]);
    }

    /**
     * Order Details.
     */
    public function show(Request $request, $id)
    {
        $seller = $this->getAuthenticatedSeller($request);
        $userId = $seller['user_id'];

        $order = MarketplaceOrder::with(['items.product.images', 'buyer', 'items.product.seller'])->find($id);

        if (!$order) {
            return response()->json(['success' => 'Failed', 'error' => 'Order not found'], 404);
        }

        // Verify if user is either the buyer or the seller
        $isBuyer = !empty($userId) && (strval($order->user_id) === strval($userId));
        $isSeller = false;

        if (!empty($seller['last10']) && !empty($order->seller_phone) && str_contains($order->seller_phone, $seller['last10'])) {
            $isSeller = true;
        }

        if (!$isSeller && !empty($userId)) {
            $isSeller = $order->items()->whereHas('product', function ($query) use ($seller) {
                $query->where('user_id', $seller['user_id'])
                      ->where('user_type', $seller['user_type']);
            })->exists();
        }

        if (!$isBuyer && !$isSeller) {
            return response()->json(['success' => 'Failed', 'error' => 'Forbidden'], 403);
        }

        return response()->json([
            'success' => 'Success',
            'data' => $order
        ]);
    }

    /**
     * Update order status (Seller action).
     */
    public function updateStatus(Request $request, $id)
    {
        $seller = $this->getAuthenticatedSeller($request);
        $userId = $seller['user_id'];

        $order = MarketplaceOrder::find($id);
        if (!$order) {
            return response()->json(['success' => 'Failed', 'error' => 'Order not found'], 404);
        }

        // Check if user is seller of products in this order OR buyer of the order
        $isBuyer = !empty($userId) && (strval($order->user_id) === strval($userId));
        $isSeller = false;

        if (!empty($seller['last10']) && !empty($order->seller_phone) && str_contains($order->seller_phone, $seller['last10'])) {
            $isSeller = true;
        }

        if (!$isSeller) {
            foreach ($order->items as $item) {
                if ($item->product) {
                    if (!empty($seller['last10']) && !empty($item->product->seller_phone) && str_contains($item->product->seller_phone, $seller['last10'])) {
                        $isSeller = true;
                        break;
                    }
                    if (!empty($userId) && (strval($item->product->user_id) === strval($userId)) && ($item->product->user_type === $seller['user_type'])) {
                        $isSeller = true;
                        break;
                    }
                }
            }
        }

        if (!$isSeller && !$isBuyer) {
            return response()->json(['success' => 'Failed', 'error' => 'Forbidden. Only the seller or buyer can update order status.'], 403);
        }

        $inputData = array_merge($request->all(), $request->json()->all());

        $validator = Validator::make($inputData, [
            'status' => 'required|string|in:pending,confirmed,packed,processing,dispatched,shipped,out_for_delivery,delivered,rejected,cancelled',
            'delivery_days' => 'nullable|integer|min:0',
            'status_notes' => 'nullable|string|max:255',
            'courier_name' => 'nullable|string|max:255',
            'tracking_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error' => $validator->errors()->first()
            ], 420);
        }

        $status = $inputData['status'] ?? $request->input('status');
        $currentStatus = strtolower($order->status ?? 'pending');

        // Requirement 5: Terminal states cannot be modified or reopened
        if (in_array($currentStatus, ['delivered', 'completed'])) {
            return response()->json([
                'success' => 'Failed',
                'error' => "Order #{$order->order_number} has already been delivered and completed. Status cannot be modified."
            ], 422);
        }

        if (in_array($currentStatus, ['rejected', 'cancelled'])) {
            return response()->json([
                'success' => 'Failed',
                'error' => "Order #{$order->order_number} has already been declined and closed. Status cannot be modified."
            ], 422);
        }

        // Requirement 3: Enforce forward-only sequential progression
        $allowedTransitions = [
            'pending'          => ['confirmed', 'rejected', 'cancelled'],
            'placed'           => ['confirmed', 'rejected', 'cancelled'],
            'confirmed'        => ['packed', 'shipped', 'rejected', 'cancelled'],
            'packed'           => ['shipped', 'out_for_delivery'],
            'processing'       => ['packed', 'shipped', 'out_for_delivery'],
            'shipped'          => ['out_for_delivery'],
            'dispatched'       => ['out_for_delivery'],
            'out_for_delivery' => ['delivered'],
        ];

        if (isset($allowedTransitions[$currentStatus]) && !in_array($status, $allowedTransitions[$currentStatus])) {
            return response()->json([
                'success' => 'Failed',
                'error' => "Invalid order transition from '{$currentStatus}' to '{$status}'. Orders must progress sequentially forward."
            ], 422);
        }

        $order->status = $status;

        if (in_array($status, ['rejected', 'cancelled'])) {
            // Requirement 1: Clear shipping partner and delivery days when rejected
            $order->courier_name = null;
            $order->tracking_id = null;
            $order->delivery_days = null;
            $order->payout_status = 'cancelled';
        } else {
            if (isset($inputData['delivery_days']) && Schema::hasColumn('marketplace_orders', 'delivery_days')) {
                $order->delivery_days = $inputData['delivery_days'];
            }
            if (isset($inputData['courier_name']) && Schema::hasColumn('marketplace_orders', 'courier_name')) {
                $order->courier_name = $inputData['courier_name'];
            }
            if (isset($inputData['tracking_id']) && Schema::hasColumn('marketplace_orders', 'tracking_id')) {
                $order->tracking_id = $inputData['tracking_id'];
            }
        }

        // Context-specific status notes
        if (!empty($inputData['status_notes'])) {
            $order->status_notes = $inputData['status_notes'];
        } elseif ($status === 'confirmed') {
            $order->status_notes = 'Order confirmed by seller. Packing in progress.';
        } elseif ($status === 'packed' || $status === 'processing') {
            $order->status_notes = 'Order packed and ready for shipping.';
        } elseif ($status === 'shipped' || $status === 'dispatched') {
            $cName = $order->courier_name ?: 'Courier Partner';
            $tId = $order->tracking_id ? " (Tracking ID: {$order->tracking_id})" : '';
            $order->status_notes = "Order dispatched via {$cName}{$tId}.";
        } elseif ($status === 'out_for_delivery') {
            $order->status_notes = 'Order is out for delivery with partner.';
        } elseif ($status === 'delivered') {
            $order->status_notes = 'Order delivered successfully. Payout will be verified and released by Company Admin.';
        } elseif ($status === 'rejected') {
            $order->status_notes = 'Order declined by seller.';
        } elseif ($status === 'cancelled') {
            $order->status_notes = 'Order cancelled.';
        }

        // Resolve buyer model for notifications & refund if needed
        $buyerPhone = $order->buyer_phone ?: $order->phone;
        $buyer = null;
        if (!empty($buyerPhone)) {
            $clean = preg_replace('/[^0-9]/', '', $buyerPhone);
            if (strlen($clean) >= 10) {
                $last10 = substr($clean, -10);
                if ($order->buyer_type === 'driver') {
                    $buyer = \App\Models\Driver::where('phone', 'like', "%{$last10}%")->first() ?? UserApp::where('phone', 'like', "%{$last10}%")->first();
                } else {
                    $buyer = UserApp::where('phone', 'like', "%{$last10}%")->first() ?? \App\Models\Driver::where('phone', 'like', "%{$last10}%")->first();
                }
            }
        }
        if (!$buyer) {
            $buyer = ($order->buyer_type === 'driver') ? \App\Models\Driver::find($order->user_id) : UserApp::find($order->user_id);
            if (!$buyer) {
                $buyer = UserApp::find($order->user_id) ?? \App\Models\Driver::find($order->user_id);
            }
        }

        // 1. If REJECTED / CANCELLED: Refund buyer wallet & restore stock
        if (in_array($status, ['rejected', 'cancelled'])) {
            // Restore inventory stock
            foreach ($order->items as $it) {
                if ($it->product) {
                    $it->product->stock_quantity = intval($it->product->stock_quantity) + intval($it->quantity);
                    if ($it->product->status === 'sold') {
                        $it->product->status = 'active';
                    }
                    $it->product->save();
                }
            }

            // Wallet refund if paid
            $isPaidOnlineOrWallet = in_array(strtolower($order->payment_status ?? ''), ['success', 'paid']) 
                && !str_contains(strtolower($order->payment_method ?? ''), 'cash');

            if ($buyer && $isPaidOnlineOrWallet && floatval($order->total_amount) > 0) {
                $refundAmt = floatval($order->total_amount);
                $buyer->amount = floatval($buyer->amount ?? 0) + $refundAmt;
                $buyer->save();

                $bBuyerType = ($buyer instanceof \App\Models\Driver) ? 'driver' : 'customer';
                $this->recordWalletTransaction(
                    $buyer->id,
                    $bBuyerType,
                    $refundAmt,
                    'credit',
                    "Refund: Order #{$order->order_number} declined/cancelled by seller",
                    'REFUND_' . time() . '_' . rand(1000, 9999)
                );
                $order->payment_status = 'refunded';
            }
        }

        // 2. Requirement 4: If DELIVERED, do NOT release payout automatically! Leave in escrow for Admin release.
        if ($status === 'delivered') {
            $subtotal = floatval($order->subtotal ?: $order->total_amount);
            $payoutAmount = floatval($order->seller_payout_amount);

            if ($payoutAmount <= 0) {
                $commSetting = \App\Models\MarketplaceCommissionSetting::getActiveSetting();
                $commVal = floatval($commSetting->commission_value ?? 10);
                $commAmount = round(($subtotal * $commVal) / 100, 2);
                $payoutAmount = max(0, $subtotal - $commAmount);
                $order->admin_commission_rate = $commVal;
                $order->admin_commission_amount = $commAmount;
                $order->seller_payout_amount = $payoutAmount;
            }

            if ($order->payout_status !== 'released') {
                $order->payout_status = 'pending';
            }

            // Look up seller user record for delivery notification
            $sellerModel = null;
            if (!empty($order->seller_phone)) {
                $cleanPhone = substr(preg_replace('/\D/', '', (string)$order->seller_phone), -10);
                if (!empty($cleanPhone)) {
                    $sellerModel = ($order->seller_type === 'driver')
                        ? \App\Models\Driver::where('phone', 'like', "%{$cleanPhone}%")->first()
                        : UserApp::where('phone', 'like', "%{$cleanPhone}%")->first();
                }
            }
            if (!$sellerModel && $order->seller_id) {
                $sellerModel = ($order->seller_type === 'driver') ? \App\Models\Driver::find($order->seller_id) : UserApp::find($order->seller_id);
            }
            if (!$sellerModel && $order->seller_id) {
                $sellerModel = UserApp::find($order->seller_id) ?? \App\Models\Driver::find($order->seller_id);
            }

            if ($sellerModel && $payoutAmount > 0) {
                // Notify seller that order was delivered and payout is pending Admin release
                $sellerNotifMsg = "Order {$order->order_number} marked as Delivered! Net payout of ₹" . number_format($payoutAmount, 2) . " is held in escrow and will be released by Company Admin to your wallet.";
                DB::table('tj_notification')->insert([
                    'to_id'    => $sellerModel->id,
                    'from_id'  => $order->user_id,
                    'titre'    => "Delivered: Order {$order->order_number}",
                    'message'  => $sellerNotifMsg,
                    'statut'   => 'unread',
                    'type'     => 'marketplace',
                    'creer'    => date('Y-m-d H:i:s'),
                    'modifier' => date('Y-m-d H:i:s'),
                ]);

                if (!empty($sellerModel->fcm_id)) {
                    try {
                        GcmController::sendNotification($sellerModel->fcm_id, [
                            'title'        => "Delivered: Order {$order->order_number}",
                            'body'         => $sellerNotifMsg,
                            'tag'          => 'marketplace_delivery',
                            'order_id'     => (string)$order->id,
                            'order_number' => (string)$order->order_number,
                        ]);
                    } catch (\Exception $ex) {}
                }
            }
        }

        $order->save();

        // Get product titles for clear notification
        $productTitles = [];
        foreach ($order->items as $it) {
            if ($it->product) {
                $productTitles[] = "'{$it->product->title}'";
            }
        }
        $productsStr = !empty($productTitles) ? implode(', ', $productTitles) : 'your item';

        $statusTitles = [
            'pending'          => 'Pending Confirmation',
            'placed'           => 'Placed',
            'confirmed'        => 'Confirmed by Seller',
            'packed'           => 'Packed',
            'processing'       => 'In Processing',
            'dispatched'       => 'Dispatched',
            'shipped'          => 'Shipped',
            'out_for_delivery' => 'Out for Delivery',
            'delivered'        => 'Delivered',
            'completed'        => 'Completed',
            'rejected'         => 'Declined by Seller',
            'cancelled'        => 'Cancelled',
        ];

        $statusLabel = $statusTitles[$status] ?? ucfirst(str_replace('_', ' ', $status));
        $date = date('Y-m-d H:i:s');

        $shippingInfo = '';
        if (!in_array($status, ['rejected', 'cancelled'])) {
            if (!empty($order->courier_name)) {
                $shippingInfo .= " Courier: {$order->courier_name}.";
            }
            if (!empty($order->tracking_id)) {
                $shippingInfo .= " Tracking ID: {$order->tracking_id}.";
            }
            if (!empty($order->delivery_days)) {
                $shippingInfo .= " Estimated delivery: {$order->delivery_days} days.";
            }
            if (!empty($order->status_notes)) {
                $shippingInfo .= " Note: {$order->status_notes}";
            }
        }

        $orderDisplayNum = $order->order_number ?: ('FW-ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT));
        $purchaseDisplayId = $order->purchase_id ?: ('FWMP-' . date('Ymd') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));

        if (in_array($status, ['rejected', 'cancelled'])) {
            $notificationMessage = "Your order {$orderDisplayNum} for {$productsStr} has been declined by the seller. Full refund has been credited to your account.";
            $pushBody = "Your order {$orderDisplayNum} was declined by the seller. Full refund has been credited.";
        } elseif ($status === 'delivered') {
            $notificationMessage = "Great news! Your order {$orderDisplayNum} for {$productsStr} has been delivered successfully.";
            $pushBody = "Great news! Your order {$orderDisplayNum} has been delivered successfully.";
        } else {
            $notificationMessage = "Your order {$orderDisplayNum} (Purchase ID: {$purchaseDisplayId}) for {$productsStr} is now {$statusLabel}.{$shippingInfo}";
            $pushBody = "Your order for {$productsStr} is now {$statusLabel}!{$shippingInfo}";
        }

        if ($buyer) {
            // Save in-app notification to tj_notification table
            DB::table('tj_notification')->insert([
                'to_id'    => $buyer->id,
                'from_id'  => $userId,
                'titre'    => "Order {$orderDisplayNum}: {$statusLabel}",
                'message'  => $notificationMessage,
                'statut'   => 'unread',
                'type'     => 'marketplace',
                'creer'    => $date,
                'modifier' => $date,
            ]);

            // Send Real-Time Firebase Push Notification
            if (!empty($buyer->fcm_id)) {
                try {
                    $fcmMessage = [
                        'title'        => "Order {$orderDisplayNum}: {$statusLabel}",
                        'body'         => $pushBody,
                        'tag'          => 'marketplace_order_status',
                        'status'       => $status,
                        'order_id'     => (string)$order->id,
                        'order_number' => (string)$orderDisplayNum,
                        'purchase_id'  => (string)$purchaseDisplayId,
                        'phone'        => (string)($order->buyer_phone ?: $order->phone),
                        'courier_name' => (string)($order->courier_name ?? ''),
                        'tracking_id'  => (string)($order->tracking_id ?? ''),
                    ];
                    GcmController::sendNotification($buyer->fcm_id, $fcmMessage);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('FCM Buyer Notification error: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'success' => 'Success',
            'message' => "Order status updated to '{$statusLabel}' and buyer has been notified.",
            'data'    => MarketplaceOrder::with(['items.product.images', 'buyer'])->find($order->id)
        ]);
    }

    /**
     * Track Order by Order Number, Purchasing ID, or Order ID.
     */
    public function trackOrder(Request $request, $identifier)
    {
        $order = MarketplaceOrder::where('order_number', $identifier)
            ->orWhere('purchase_id', $identifier)
            ->orWhere('id', $identifier)
            ->with(['items.product.images', 'buyer'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Order not found for tracking identifier: ' . $identifier
            ], 404);
        }

        return response()->json([
            'success' => 'Success',
            'data' => [
                'order_number'     => $order->order_number,
                'purchase_id'      => $order->purchase_id,
                'order_id'         => $order->id,
                'status'           => $order->status,
                'status_label'     => ucfirst(str_replace('_', ' ', $order->status)),
                'status_notes'     => $order->status_notes,
                'courier_name'     => $order->courier_name,
                'tracking_id'      => $order->tracking_id,
                'delivery_days'    => $order->delivery_days,
                'delivery_address' => $order->delivery_address,
                'buyer_phone'      => $order->buyer_phone ?: $order->phone,
                'contact_name'     => $order->contact_name,
                'total_amount'     => $order->total_amount,
                'created_at'       => $order->created_at,
                'items'            => $order->items,
            ]
        ]);
    }
}

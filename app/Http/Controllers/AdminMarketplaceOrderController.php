<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceOrder;
use App\Models\MarketplaceOrderItem;
use App\Models\MarketplaceProduct;
use App\Models\UserApp;
use App\Models\Driver;
use App\Http\Controllers\API\v1\GcmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminMarketplaceOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display all Marketplace Orders with stage filters and full details.
     */
    public function index(Request $request)
    {
        $stage = $request->input('stage', 'all');
        $payoutFilter = $request->input('payout_status', 'all');
        $search = $request->input('search');

        $query = MarketplaceOrder::with(['items.product.images', 'buyer'])
            ->orderBy('id', 'desc');

        // Filter by Product Stage
        if ($stage !== 'all' && !empty($stage)) {
            $query->where('status', $stage);
        }

        // Filter by Payout Status
        if ($payoutFilter !== 'all' && !empty($payoutFilter)) {
            $query->where('payout_status', $payoutFilter);
        }

        // Search query
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('txn_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('tracking_id', 'like', "%{$search}%")
                  ->orWhere('courier_name', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function ($bq) use ($search) {
                      $bq->where('nom', 'like', "%{$search}%")
                         ->orWhere('prenom', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items.product', function ($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15)->appends($request->all());

        $commSetting = \App\Models\MarketplaceCommissionSetting::getActiveSetting();
        $defaultCommRate = floatval($commSetting->commission_value ?? 5);

        // Attach resolved seller info and compute commission for each order
        foreach ($orders as $order) {
            $firstItem = $order->items->first();
            $sellerId = $order->seller_id ?? ($firstItem && $firstItem->product ? $firstItem->product->user_id : null);
            $seller = null;
            if ($sellerId) {
                $seller = UserApp::find($sellerId) ?? Driver::find($sellerId);
            }
            $order->resolved_seller = $seller;

            // Auto-compute commission & payout if 0 for legacy orders
            $sub = floatval($order->subtotal ?: $order->total_amount);
            if ($order->seller_payout_amount <= 0 && $sub > 0) {
                $cRate = $order->admin_commission_rate > 0 ? $order->admin_commission_rate : $defaultCommRate;
                $cAmount = round(($sub * $cRate) / 100, 2);
                $order->admin_commission_rate = $cRate;
                $order->admin_commission_amount = $cAmount;
                $order->seller_payout_amount = max(0, round($sub - $cAmount, 2));
            }
        }

        // Summary Statistics for Stage Filter Badges
        $counts = [
            'all'              => MarketplaceOrder::count(),
            'placed'           => MarketplaceOrder::where('status', 'placed')->count(),
            'processing'       => MarketplaceOrder::where('status', 'processing')->count(),
            'dispatched'       => MarketplaceOrder::where('status', 'dispatched')->count(),
            'shipped'          => MarketplaceOrder::where('status', 'shipped')->count(),
            'out_for_delivery' => MarketplaceOrder::where('status', 'out_for_delivery')->count(),
            'delivered'        => MarketplaceOrder::where('status', 'delivered')->count(),
            'completed'        => MarketplaceOrder::where('status', 'completed')->count(),
            'cancelled'        => MarketplaceOrder::where('status', 'cancelled')->count(),
            'pending_payout'   => MarketplaceOrder::where('payout_status', 'pending')->count(),
            'released_payout'  => MarketplaceOrder::where('payout_status', 'released')->count(),
        ];

        $totalSalesAmount = MarketplaceOrder::sum('total_amount');
        $totalCommissionEarned = MarketplaceOrder::where('payout_status', 'released')->sum('admin_commission_amount');
        $pendingCommissionHold = MarketplaceOrder::where('payout_status', 'pending')->sum('admin_commission_amount');

        return view('marketplace.orders', compact(
            'orders',
            'stage',
            'payoutFilter',
            'search',
            'counts',
            'totalSalesAmount',
            'totalCommissionEarned',
            'pendingCommissionHold'
        ));
    }

    /**
     * Confirm order & release payout to seller's wallet after deducting admin commission.
     */
    public function releasePayout(Request $request, $id)
    {
        $order = MarketplaceOrder::with(['items.product'])->findOrFail($id);

        if ($order->payout_status === 'released') {
            return redirect()->back()->with('error', 'Payout for this order has already been released to the seller.');
        }

        $subtotal = floatval($order->subtotal ?: $order->total_amount);
        $firstItem = $order->items->first();
        $sellerId = $order->seller_id ?? ($firstItem && $firstItem->product ? $firstItem->product->user_id : null);

        if (!$sellerId) {
            return redirect()->back()->with('error', 'Unable to determine seller for this order.');
        }

        $sellerType = $order->seller_type ?? ($firstItem && $firstItem->product && !empty($firstItem->product->driver_id) ? 'driver' : 'customer');
        $seller = null;
        if (!empty($order->seller_phone)) {
            $cleanPhone = substr(preg_replace('/\D/', '', (string)$order->seller_phone), -10);
            if (!empty($cleanPhone)) {
                $seller = ($sellerType === 'driver')
                    ? Driver::where('phone', 'like', "%{$cleanPhone}%")->first()
                    : UserApp::where('phone', 'like', "%{$cleanPhone}%")->first();
            }
        }
        if (!$seller && $sellerId) {
            $seller = ($sellerType === 'driver') ? Driver::find($sellerId) : UserApp::find($sellerId);
        }
        if (!$seller && $sellerId) {
            $seller = UserApp::find($sellerId) ?? Driver::find($sellerId);
        }

        if (!$seller) {
            return redirect()->back()->with('error', "Seller user record (ID: {$sellerId}) not found.");
        }

        // Calculate commission & payout if not stored
        $commissionAmount = floatval($order->admin_commission_amount);
        $payoutAmount = floatval($order->seller_payout_amount);

        if ($payoutAmount <= 0) {
            $commSetting = \App\Models\MarketplaceCommissionSetting::getActiveSetting();
            $commType = $commSetting->commission_type ?? 'percentage';
            $commVal = floatval($commSetting->commission_value ?? 5);

            if ($commType === 'percentage') {
                $commissionAmount = round(($subtotal * $commVal) / 100, 2);
            } else {
                $commissionAmount = round($commVal, 2);
            }
            $payoutAmount = max(0, $subtotal - $commissionAmount);

            $order->admin_commission_type = $commType;
            $order->admin_commission_rate = $commVal;
            $order->admin_commission_amount = $commissionAmount;
            $order->seller_payout_amount = $payoutAmount;
        }

        DB::beginTransaction();
        try {
            // 1. Credit seller wallet with net payout (EARNINGS: Withdrawable Balance)
            $sellerBalance = floatval($seller->amount ?? 0);
            $sellerWithdrawable = floatval($seller->withdrawable_balance ?? 0);
            $sellerEarn = floatval($seller->earn_amount ?? 0);

            $seller->amount = $sellerBalance + $payoutAmount;
            $seller->withdrawable_balance = $sellerWithdrawable + $payoutAmount;
            $seller->earn_amount = $sellerEarn + $payoutAmount;
            $seller->save();

            $txnId = 'PAYOUT_' . time() . '_' . rand(1000, 9999);
            $dateOnly = date('Y-m-d');
            $dateTime = date('Y-m-d H:i:s');
            $sellerType = ($seller instanceof Driver) ? 'driver' : 'customer';

            // 2. Record Transaction 1: Marketplace Sale Earning Credit
            if ($sellerType === 'driver') {
                $driverTx = [
                    'id_conducteur'   => $seller->id,
                    'amount'          => (string)$subtotal,
                    'type'            => 'credit',
                    'deduction_type'  => 1,
                    'payment_method'  => 'Marketplace Escrow',
                    'payment_status'  => 'success',
                    'description'     => "Marketplace Sale Earnings (Gross): Order #{$order->id}",
                    'txn_id'          => $txnId . '_SALE',
                    'date'            => $dateOnly,
                    'creer'           => $dateTime,
                    'modifier'        => $dateTime,
                ];
                if (Schema::hasColumn('tj_conducteur_transaction', 'wallet_bucket')) {
                    $driverTx['wallet_bucket'] = 'earning';
                }
                DB::table('tj_conducteur_transaction')->insert($driverTx);

                // Record Transaction 2: Marketplace Admin Commission Deduction
                if ($commissionAmount > 0) {
                    $driverCommTx = [
                        'id_conducteur'   => $seller->id,
                        'amount'          => (string)$commissionAmount,
                        'type'            => 'debit',
                        'deduction_type'  => 0,
                        'payment_method'  => 'Platform Fee',
                        'payment_status'  => 'success',
                        'description'     => "Admin Commission Deduction ({$order->admin_commission_rate}%): Order #{$order->id}",
                        'txn_id'          => $txnId . '_COMM',
                        'date'            => $dateOnly,
                        'creer'           => $dateTime,
                        'modifier'        => $dateTime,
                    ];
                    if (Schema::hasColumn('tj_conducteur_transaction', 'wallet_bucket')) {
                        $driverCommTx['wallet_bucket'] = 'spend';
                    }
                    DB::table('tj_conducteur_transaction')->insert($driverCommTx);
                }
            } else {
                $userTx = [
                    'id_user_app'     => $seller->id,
                    'user_type'       => 'customer',
                    'amount'          => (string)$subtotal,
                    'type'            => 'credit',
                    'deduction_type'  => 1,
                    'payment_method'  => 'Marketplace Escrow',
                    'payment_status'  => 'success',
                    'description'     => "Marketplace Sale Earnings (Gross): Order #{$order->id}",
                    'txn_id'          => $txnId . '_SALE',
                    'date'            => $dateOnly,
                    'creer'           => $dateTime,
                    'modifier'        => $dateTime,
                ];
                if (Schema::hasColumn('tj_transaction', 'wallet_bucket')) {
                    $userTx['wallet_bucket'] = 'earning';
                }
                DB::table('tj_transaction')->insert($userTx);

                // Record Transaction 2: Marketplace Admin Commission Deduction
                if ($commissionAmount > 0) {
                    $userCommTx = [
                        'id_user_app'     => $seller->id,
                        'user_type'       => 'customer',
                        'amount'          => (string)$commissionAmount,
                        'type'            => 'debit',
                        'deduction_type'  => 0,
                        'payment_method'  => 'Platform Fee',
                        'payment_status'  => 'success',
                        'description'     => "Admin Commission Deduction ({$order->admin_commission_rate}%): Order #{$order->id}",
                        'txn_id'          => $txnId . '_COMM',
                        'date'            => $dateOnly,
                        'creer'           => $dateTime,
                        'modifier'        => $dateTime,
                    ];
                    if (Schema::hasColumn('tj_transaction', 'wallet_bucket')) {
                        $userCommTx['wallet_bucket'] = 'spend';
                    }
                    DB::table('tj_transaction')->insert($userCommTx);
                }
            }

            // 3. Mark order as Payout Released & Confirmed
            $order->payout_status = 'released';
            $order->payout_released_at = $dateTime;
            $order->payout_released_by = Auth::id() ?: 1;
            $order->save();

            // 4. Send In-App & Push Notification to Seller
            DB::table('tj_notification')->insert([
                'to_id'    => $seller->id,
                'from_id'  => 1,
                'titre'    => 'Marketplace Payout Confirmed',
                'message'  => "Admin confirmed Order #{$order->id}. Net payout of ₹" . number_format($payoutAmount, 2) . " has been credited to your wallet (Commission: ₹" . number_format($commissionAmount, 2) . ").",
                'statut'   => 'unread',
                'type'     => 'marketplace',
                'creer'    => $dateTime,
                'modifier' => $dateTime,
            ]);

            if (!empty($seller->fcm_id)) {
                try {
                    $fcmMessage = [
                        'title' => 'Payout Credited to Wallet!',
                        'body' => "₹" . number_format($payoutAmount, 2) . " for Order #{$order->id} has been credited to your wallet.",
                        'tag' => 'marketplace_payout',
                        'order_id' => (string)$order->id,
                    ];
                    GcmController::sendNotification($seller->fcm_id, $fcmMessage);
                } catch (\Exception $e) {
                    // Ignore GCM error
                }
            }

            DB::commit();

            return redirect()->back()->with('success', "Order #{$order->id} payout of ₹" . number_format($payoutAmount, 2) . " successfully confirmed and credited to seller's wallet! (Admin Commission: ₹" . number_format($commissionAmount, 2) . ")");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Marketplace Payout Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to release payout: ' . $e->getMessage());
        }
    }
}

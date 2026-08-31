<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceCommissionSetting;
use App\Models\MarketplaceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketplaceCommissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $setting = MarketplaceCommissionSetting::getActiveSetting();

        // Calculate marketplace financial overview metrics
        $totalOrdersCount = MarketplaceOrder::count();
        $totalMarketplaceSales = MarketplaceOrder::sum('subtotal');
        $totalCommissionEarned = MarketplaceOrder::where('payout_status', 'released')->sum('admin_commission_amount');
        $pendingCommissionHold = MarketplaceOrder::where('payout_status', 'pending')->sum('admin_commission_amount');
        $totalSellerPayoutsSettled = MarketplaceOrder::where('payout_status', 'released')->sum('seller_payout_amount');
        $pendingSellerPayouts = MarketplaceOrder::where('payout_status', 'pending')->sum('seller_payout_amount');

        return view('marketplace.commission', compact(
            'setting',
            'totalOrdersCount',
            'totalMarketplaceSales',
            'totalCommissionEarned',
            'pendingCommissionHold',
            'totalSellerPayoutsSettled',
            'pendingSellerPayouts'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'commission_type'  => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'description'      => 'nullable|string|max:255',
        ]);

        $setting = MarketplaceCommissionSetting::first();
        if (!$setting) {
            $setting = new MarketplaceCommissionSetting();
        }

        $setting->commission_type = $request->input('commission_type');
        $setting->commission_value = $request->input('commission_value');
        $setting->min_order_amount = $request->input('min_order_amount', 0);
        $setting->is_active = $request->has('is_active') ? true : false;
        $setting->description = $request->input('description');
        $setting->save();

        return redirect()->back()->with('success', 'Marketplace Commission Settings updated successfully!');
    }
}

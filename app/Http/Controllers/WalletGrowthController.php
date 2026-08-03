<?php

namespace App\Http\Controllers;

use App\Models\ApiKeySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class WalletGrowthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $growthEnabled = ApiKeySetting::where('key_name', 'wallet_growth_enabled')->value('key_value') ?? 'true';
        $growthRate    = ApiKeySetting::where('key_name', 'wallet_growth_rate')->value('key_value') ?? '0.10';
        $growthMode    = ApiKeySetting::where('key_name', 'wallet_growth_mode')->value('key_value') ?? 'percentage';
        $frequency     = ApiKeySetting::where('key_name', 'wallet_growth_freq')->value('key_value') ?? 'daily';

        return view('wallet.growth', compact('growthEnabled', 'growthRate', 'growthMode', 'frequency'));
    }

    public function update(Request $request)
    {
        ApiKeySetting::updateOrCreate(
            ['key_name' => 'wallet_growth_enabled'],
            ['group' => 'wallet', 'provider' => 'growth', 'key_value' => $request->has('enabled') ? 'true' : 'false', 'is_active' => true]
        );

        ApiKeySetting::updateOrCreate(
            ['key_name' => 'wallet_growth_rate'],
            ['group' => 'wallet', 'provider' => 'growth', 'key_value' => $request->rate ?? '0.10', 'is_active' => true]
        );

        ApiKeySetting::updateOrCreate(
            ['key_name' => 'wallet_growth_mode'],
            ['group' => 'wallet', 'provider' => 'growth', 'key_value' => $request->mode ?? 'percentage', 'is_active' => true]
        );

        ApiKeySetting::updateOrCreate(
            ['key_name' => 'wallet_growth_freq'],
            ['group' => 'wallet', 'provider' => 'growth', 'key_value' => $request->frequency ?? 'daily', 'is_active' => true]
        );

        return redirect()->back()->with('success', 'Wallet Growth settings updated successfully.');
    }

    public function runManualGrowth()
    {
        Artisan::call('wallet:growth');
        return redirect()->back()->with('success', 'Wallet Growth calculation executed successfully!');
    }
}

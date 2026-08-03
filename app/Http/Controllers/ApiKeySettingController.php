<?php

namespace App\Http\Controllers;

use App\Models\ApiKeySetting;
use Illuminate\Http\Request;

class ApiKeySettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $apiKeys = ApiKeySetting::all()->groupBy('group');
        return view('administration_tools.api_keys.index', compact('apiKeys'));
    }

    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'provider'   => 'required|string',
            'group'      => 'required|string',
            'key_name'   => 'required|string',
            'key_value'  => 'nullable|string',
            'secret_value' => 'nullable|string',
        ]);

        ApiKeySetting::updateOrCreate(
            ['provider' => $request->provider, 'key_name' => $request->key_name],
            [
                'group'        => $request->group,
                'key_value'    => $request->key_value,
                'secret_value' => $request->secret_value,
                'is_active'    => $request->has('is_active'),
                'is_sandbox'   => $request->has('is_sandbox'),
            ]
        );

        return redirect()->back()->with('success', 'API Key updated successfully.');
    }

    public function toggleStatus(Request $request)
    {
        $setting = ApiKeySetting::findOrFail($request->id);
        $setting->is_active = ($request->ischeck === 'true');
        $setting->save();

        return response()->json(['success' => true]);
    }
}

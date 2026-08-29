<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppVersionControl;

class AppVersionControlController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display version settings for Customer and Driver apps
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'customer');
        if (!in_array($tab, ['customer', 'business'])) {
            $tab = 'customer';
        }

        $customerConfig = AppVersionControl::firstOrCreate(
            ['app_type' => 'customer'],
            [
                'app_name' => 'Fiinway User App',
                'latest_version' => '1.0.17',
                'minimum_version' => '1.0.0',
                'force_update' => false,
                'playstore_url' => 'https://play.google.com/store/apps/details?id=com.fiinway',
                'title' => 'New Version Available!',
                'message' => 'A new version of the Fiinway User App is available with performance improvements and new services. Please update from Google Play Store to enjoy the best experience.',
            ]
        );

        $businessConfig = AppVersionControl::firstOrCreate(
            ['app_type' => 'business'],
            [
                'app_name' => 'Fiinway Driver & Partner App',
                'latest_version' => '1.0.17',
                'minimum_version' => '1.0.0',
                'force_update' => false,
                'playstore_url' => 'https://play.google.com/store/apps/details?id=com.fiinway.driver',
                'title' => 'Important Driver Partner Update!',
                'message' => 'A critical update is available for Fiinway Driver Partners with updated ride dispatch, payout enhancements, and bug fixes. Please update now to continue accepting rides.',
            ]
        );

        return view('app_version_control.index', compact('tab', 'customerConfig', 'businessConfig'));
    }

    /**
     * Update version configuration
     */
    public function update(Request $request, $id)
    {
        $config = AppVersionControl::findOrFail($id);

        $request->validate([
            'app_name' => 'required|string|max:150',
            'latest_version' => 'required|string|max:30',
            'minimum_version' => 'required|string|max:30',
            'playstore_url' => 'required|url',
            'appstore_url' => 'nullable|url',
            'title' => 'required|string|max:200',
            'message' => 'nullable|string',
            'maintenance_message' => 'nullable|string',
        ]);

        $config->update([
            'app_name' => $request->app_name,
            'latest_version' => trim($request->latest_version),
            'minimum_version' => trim($request->minimum_version),
            'force_update' => $request->has('force_update') ? true : false,
            'playstore_url' => trim($request->playstore_url),
            'appstore_url' => trim($request->appstore_url),
            'title' => $request->title,
            'message' => $request->message,
            'is_maintenance' => $request->has('is_maintenance') ? true : false,
            'maintenance_message' => $request->maintenance_message,
        ]);

        return redirect()->route('app-version-control.index', ['tab' => $config->app_type])
            ->with('success', "{$config->app_name} version settings updated successfully.");
    }

    /**
     * Toggle Force Update Switch via AJAX
     */
    public function toggleForce(Request $request, $id)
    {
        $config = AppVersionControl::findOrFail($id);
        $config->force_update = !$config->force_update;
        $config->save();

        return response()->json([
            'success' => true,
            'force_update' => $config->force_update,
        ]);
    }

    /**
     * Toggle Maintenance Switch via AJAX
     */
    public function toggleMaintenance(Request $request, $id)
    {
        $config = AppVersionControl::findOrFail($id);
        $config->is_maintenance = !$config->is_maintenance;
        $config->save();

        return response()->json([
            'success' => true,
            'is_maintenance' => $config->is_maintenance,
        ]);
    }
}

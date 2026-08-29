<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppVersionControl;

class AppVersionApiController extends Controller
{
    /**
     * Check if app update is required or optional
     * Endpoint: GET /api/v1/app-version/check
     */
    public function checkVersion(Request $request)
    {
        $appType = $request->query('app_type', 'customer');
        if (!in_array($appType, ['customer', 'business'])) {
            $appType = 'customer';
        }

        $installedVersion = trim($request->query('version', '1.0.0'));
        $platform = strtolower($request->query('platform', 'android'));

        // Normalize version string (e.g. '1.0.17+17' -> '1.0.17')
        if (str_contains($installedVersion, '+')) {
            $installedVersion = explode('+', $installedVersion)[0];
        }

        $config = AppVersionControl::where('app_type', $appType)->first();

        if (!$config) {
            return response()->json([
                'success' => 'success',
                'data' => [
                    'force_update' => false,
                    'optional_update' => false,
                    'is_maintenance' => false,
                    'installed_version' => $installedVersion,
                    'latest_version' => $installedVersion,
                    'minimum_version' => $installedVersion,
                    'store_url' => '',
                ]
            ]);
        }

        $storeUrl = ($platform === 'ios' && !empty($config->appstore_url))
            ? $config->appstore_url
            : $config->playstore_url;

        // 1. Check Maintenance Mode
        if ($config->is_maintenance) {
            return response()->json([
                'success' => 'success',
                'data' => [
                    'is_maintenance' => true,
                    'maintenance_message' => $config->maintenance_message ?? 'We are undergoing scheduled maintenance. Please check back shortly.',
                    'force_update' => false,
                    'optional_update' => false,
                    'store_url' => $storeUrl,
                ]
            ]);
        }

        // 2. Compare installed version with minimum supported version
        // version_compare returns -1 if installed < target, 0 if equal, 1 if installed > target
        $minVersion = $config->minimum_version ?: '1.0.0';
        $latestVersion = $config->latest_version ?: '1.0.0';

        $isBelowMinimum = version_compare($installedVersion, $minVersion, '<');
        $isBelowLatest = version_compare($installedVersion, $latestVersion, '<');

        $forceUpdate = $config->force_update || $isBelowMinimum;
        $optionalUpdate = !$forceUpdate && $isBelowLatest;

        return response()->json([
            'success' => 'success',
            'data' => [
                'is_maintenance' => false,
                'force_update' => (bool)$forceUpdate,
                'optional_update' => (bool)$optionalUpdate,
                'installed_version' => $installedVersion,
                'latest_version' => $latestVersion,
                'minimum_version' => $minVersion,
                'title' => $config->title ?? 'New Update Available!',
                'message' => $config->message ?? 'A new version of the app is available on the Play Store. Please update now to continue.',
                'store_url' => $storeUrl,
                'app_name' => $config->app_name,
            ]
        ]);
    }
}

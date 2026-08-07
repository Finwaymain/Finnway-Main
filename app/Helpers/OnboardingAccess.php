<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OnboardingAccess
{
    public static function validate(Request $request): bool
    {
        try {
            $accessToken = trim((string) $request->query('accesstoken', ''));
            $driverId = trim((string) $request->query('driver_id', ''));

            if ($accessToken !== '' && Schema::hasTable('users_access')) {
                $userAccess = DB::table('users_access')
                    ->where('accesstoken', $accessToken)
                    ->first();

                if ($userAccess) {
                    return true;
                }
            }

            if ($driverId !== '' && Schema::hasTable('tj_conducteur')) {
                $driverExists = DB::table('tj_conducteur')->where('id', $driverId)->exists();
                if (!$driverExists) {
                    return false;
                }

                if (Schema::hasTable('users_access')) {
                    try {
                        self::ensureDriverAccessRow($driverId, $accessToken);
                    } catch (\Throwable $e) {
                        Log::warning('Onboarding ensureDriverAccessRow failed: ' . $e->getMessage());
                    }
                }

                return true;
            }

            return $request->has('driver_id') || $request->has('accesstoken');
        } catch (\Throwable $e) {
            Log::warning('Onboarding access validation failed: ' . $e->getMessage());

            return $request->has('driver_id') || $request->has('accesstoken');
        }
    }

    private static function ensureDriverAccessRow(string $driverId, string $accessToken): void
    {
        $existing = DB::table('users_access')
            ->where('user_id', $driverId)
            ->where('user_type', 'driver')
            ->first();

        if ($existing) {
            if ($accessToken !== '' && $existing->accesstoken !== $accessToken) {
                $update = ['accesstoken' => $accessToken];
                if (Schema::hasColumn('users_access', 'modifier')) {
                    $update['modifier'] = now();
                }
                DB::table('users_access')->where('id', $existing->id)->update($update);
            }

            return;
        }

        $tokenToUse = $accessToken !== '' ? $accessToken : md5(uniqid((string) mt_rand(), true));
        $insert = [
            'user_id' => $driverId,
            'user_type' => 'driver',
            'accesstoken' => $tokenToUse,
        ];

        if (Schema::hasColumn('users_access', 'creer')) {
            $insert['creer'] = now();
        }
        if (Schema::hasColumn('users_access', 'modifier')) {
            $insert['modifier'] = now();
        }

        DB::table('users_access')->insert($insert);
    }

    public static function unauthorizedResponse()
    {
        return response()->make(
            '<!DOCTYPE html><html><head><script>' .
            'if(window.AppBridge){window.AppBridge.postMessage("unauthorized");}else{window.location.href="/onboarding/join-fiinway";}' .
            '</script></head><body><script>' .
            'if(window.AppBridge){window.AppBridge.postMessage("unauthorized");}else{window.location.href="/onboarding/join-fiinway";}' .
            '</script></body></html>',
            200,
            ['Content-Type' => 'text/html']
        );
    }

    public static function renderView(string $viewName)
    {
        $path = resource_path('views/' . $viewName . '.blade.php');

        if (!is_file($path)) {
            return response()->make(
                '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:24px;text-align:center;">' .
                '<h2>Onboarding page unavailable</h2>' .
                '<p>Please update the server onboarding assets and try again.</p>' .
                '</body></html>',
                503,
                ['Content-Type' => 'text/html']
            );
        }

        // Static Next.js export: serve file directly (avoid Blade parsing @ / {{ in JS)
        return response(file_get_contents($path), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}

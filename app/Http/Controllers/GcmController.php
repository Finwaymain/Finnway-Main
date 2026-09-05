<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Google\Client as Google_Client;

class GcmController extends Controller
{
    public static function sendNotification($token, $messages, $topic = '')
    {
        if (empty($token) && empty($topic)) {
            return response()->json([
                'success' => false,
                'message' => 'Token or topic is required.',
            ]);
        }

        $title = is_array($messages) ? ($messages['title'] ?? 'Fiinway') : 'Fiinway';
        $body = is_array($messages) ? ($messages['body'] ?? '') : (string)$messages;

        $fcmData = [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        if (is_array($messages)) {
            foreach ($messages as $key => $value) {
                if ($key !== 'title' && $key !== 'body') {
                    if (is_array($value) || is_object($value)) {
                        $fcmData[$key] = json_encode($value);
                    } else {
                        $fcmData[$key] = (string)$value;
                    }
                }
            }
        }

        $isRideRequest = ($fcmData['statut'] ?? '') === 'new' || 
                         ($fcmData['tag'] ?? '') === 'ridenewrider' || 
                         ($fcmData['tag'] ?? '') === 'parcelnew';

        $isHomeServiceAlert = ($fcmData['type'] ?? '') === 'homeservice' || 
                              ($fcmData['tag'] ?? '') === 'homeservicerequest' || 
                              ($fcmData['tag'] ?? '') === 'homeservicenotif' || 
                              !empty($fcmData['booking_id']);

        $isIncomingAlert = $isRideRequest || $isHomeServiceAlert;
        $channelId = $isIncomingAlert ? 'ride_requests' : 'high_importance_channel';
        $soundName = $isIncomingAlert ? 'ride_request_sound' : 'default';

        // 1. Try Firebase HTTP v1 API (credentials.json file or .env config)
        $credentials = null;
        if (Storage::disk('local')->has('firebase/credentials.json') || file_exists(storage_path('app/firebase/credentials.json'))) {
            $credentials = json_decode(file_get_contents(storage_path('app/firebase/credentials.json')), true);
        } elseif (env('FIREBASE_CREDENTIALS_BASE64')) {
            $credentials = json_decode(base64_decode(env('FIREBASE_CREDENTIALS_BASE64')), true);
        } elseif (env('FIREBASE_CREDENTIALS_JSON')) {
            $credentials = json_decode(env('FIREBASE_CREDENTIALS_JSON'), true);
        }

        if (!empty($credentials) && is_array($credentials)) {
            try {
                // Cache Google OAuth token for 55 minutes (3300s) to eliminate 1-2s latency per notification
                $cacheKey = 'fcm_v1_token_' . md5(json_encode($credentials));
                $access_token = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3300, function () use ($credentials) {
                    $guzzleClient = new \GuzzleHttp\Client(['verify' => false]);
                    $client = new Google_Client();
                    $client->setHttpClient($guzzleClient);
                    $client->setAuthConfig($credentials);
                    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                    $client->fetchAccessTokenWithAssertion($guzzleClient);
                    $client_token = $client->getAccessToken();
                    return $client_token['access_token'] ?? null;
                });

                if (!empty($access_token)) {
                    $projectId = $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID', 'fiinway-app');
                    $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';

                    // Ensure title and body are available inside data payload for background handler
                    $fcmData['title'] = (string)$title;
                    $fcmData['body'] = (string)$body;
                    $fcmData['is_incoming_alert'] = $isIncomingAlert ? '1' : '0';

                    $messageBlock = [
                        'data' => $fcmData,
                        'android' => [
                            'priority' => 'HIGH',
                            'ttl' => '60s',
                        ],
                        'apns' => [
                            'headers' => [
                                'apns-priority' => '10',
                                'apns-push-type' => 'alert',
                            ],
                            'payload' => [
                                'aps' => [
                                    'alert' => [
                                        'title' => $title,
                                        'body' => $body,
                                    ],
                                    'sound' => $isIncomingAlert ? 'ride_request_sound.caf' : 'default',
                                    'badge' => 1,
                                    'content-available' => 1,
                                ],
                            ],
                        ],
                    ];

                    // For standard notifications (marketing, chat, receipts), attach top-level notification
                    // For incoming ride/service alerts, omit top-level notification for Android so Google Play
                    // does not swallow the push into system tray, ensuring background Dart handler fires immediately!
                    if (!$isIncomingAlert) {
                        $messageBlock['notification'] = [
                            'title' => $title,
                            'body' => $body,
                        ];
                        $messageBlock['android']['notification'] = [
                            'sound' => $soundName,
                            'channel_id' => $channelId,
                            'notification_priority' => 'PRIORITY_HIGH',
                            'default_sound' => true,
                            'default_vibrate_timings' => true,
                        ];
                    }

                    if (!empty($topic) && empty($token)) {
                        $messageBlock['topic'] = $topic;
                    } else {
                        $messageBlock['token'] = $token;
                    }

                    $payload = ['message' => $messageBlock];

                    $headers = [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $access_token,
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                    $result = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // If token was rejected (401), clear cache so next call refreshes
                    if ($httpCode === 401) {
                        \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    }

                    if ($httpCode >= 200 && $httpCode < 300) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Notification sent via HTTP v1.',
                            'result' => json_decode($result, true),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("FCM v1 send error in root GcmController: " . $e->getMessage());
            }
        }

        // 2. Fallback to Legacy FCM API if HTTP v1 credentials not present or failed
        $serverKey = env('FCM_SERVER_KEY')
            ?: config('app.firebase.server_key')
            ?: (\Illuminate\Support\Facades\Schema::hasColumn('tj_settings', 'fcm_key') ? DB::table('tj_settings')->value('fcm_key') : null)
            ?: 'AAAA-dummy-fallback-key';

        $fcmData['title'] = (string)$title;
        $fcmData['body'] = (string)$body;
        $fcmData['is_incoming_alert'] = $isIncomingAlert ? '1' : '0';

        $legacyPayload = [
            'to' => !empty($token) ? $token : '/topics/' . $topic,
            'priority' => 'high',
            'time_to_live' => 60,
            'data' => $fcmData,
        ];

        if (!$isIncomingAlert) {
            $legacyPayload['notification'] = [
                'title' => $title,
                'body' => $body,
                'sound' => $soundName,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'channel_id' => $channelId,
            ];
        }

        $headers = [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($legacyPayload));

        $result = curl_exec($ch);
        curl_close($ch);

        return response()->json([
            'success' => true,
            'message' => 'Notification sent via Legacy FCM.',
            'result' => json_decode($result, true),
        ]);
    }
}
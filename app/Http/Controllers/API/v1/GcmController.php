<?php

namespace App\Http\Controllers\API\v1;

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
                $client = new Google_Client();
                $client->setAuthConfig($credentials);
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                $client->refreshTokenWithAssertion();
                $client_token = $client->getAccessToken();
                $access_token = $client_token['access_token'] ?? null;

                if (!empty($access_token)) {
                    $projectId = $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID', 'fiinway-app');
                    $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';

                    $payload = [
                        'message' => [
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'data' => $fcmData,
                            'android' => [
                                'priority' => 'HIGH',
                                'ttl' => '45s',
                                'notification' => [
                                    'sound' => $soundName,
                                    'channel_id' => $channelId,
                                    'notification_priority' => 'PRIORITY_MAX',
                                    'default_sound' => !$isIncomingAlert,
                                    'default_vibrate_timings' => true,
                                ],
                            ],
                            'apns' => [
                                'payload' => [
                                    'aps' => [
                                        'sound' => $isIncomingAlert ? 'ride_request_sound.caf' : 'default',
                                        'badge' => 1,
                                        'content-available' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ];

                    if (!empty($topic) && empty($token)) {
                        $payload['message']['topic'] = $topic;
                    } else {
                        $payload['message']['token'] = $token;
                    }

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
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                    $result = curl_exec($ch);
                    curl_close($ch);

                    return response()->json([
                        'success' => true,
                        'message' => 'Notification sent via HTTP v1.',
                        'result' => json_decode($result, true),
                    ]);
                }
            } catch (\Exception $e) {}
        }

        // 2. Fallback to Legacy FCM API if HTTP v1 credentials not present or failed
        $serverKey = env('FCM_SERVER_KEY')
            ?: config('app.firebase.server_key')
            ?: (\Illuminate\Support\Facades\Schema::hasColumn('tj_settings', 'fcm_key') ? DB::table('tj_settings')->value('fcm_key') : null)
            ?: 'AAAA-dummy-fallback-key';

        $legacyPayload = [
            'to' => !empty($token) ? $token : '/topics/' . $topic,
            'priority' => 'high',
            'time_to_live' => 45,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => $soundName,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'channel_id' => $channelId,
            ],
            'data' => $fcmData,
        ];

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

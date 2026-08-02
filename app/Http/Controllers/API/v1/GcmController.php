<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Google\Client as Google_Client;

class GcmController extends Controller
{

    public static function sendNotification($token, $messages)
    {

        if (Storage::disk('local')->has('firebase/credentials.json')) {

            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $client_token = $client->getAccessToken();
            $access_token = $client_token['access_token'];

            if (!empty($access_token) && !empty($token)) {

                $credentialsPath = storage_path('app/firebase/credentials.json');
                $credentials = json_decode(file_get_contents($credentialsPath), true);
                $projectId = $credentials['project_id'] ?? config('app.firebase.project_id');
                $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';

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

                $data = [
                    'message' => [
                        'notification' => [
                            'title' => is_array($messages) ? $messages['title'] : 'Notification',
                            'body' => is_array($messages) ? $messages['body'] : $messages,
                        ],
                        'data' => $fcmData,
                        'token' => $token,
                        'android' => [
                            'priority' => 'high',
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => [
                                    'content-available' => 1,
                                ]
                            ]
                        ]
                    ],
                ];

                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                $result = curl_exec($ch);
                if ($result === FALSE) {
                    die('FCM Send Error: ' . curl_error($ch));
                }
                curl_close($ch);
                $result = json_decode($result);

                $response = array();
                $response['success'] = true;
                $response['message'] = 'Notification successfully sent.';
                $response['result'] = $result;
            } else {
                $response = array();
                $response['success'] = false;
                $response['message'] = 'Missing acccess token or fcm token to send notification.';
            }
        } else {
            $response = array();
            $response['success'] = false;
            $response['message'] = 'Firebase credentials file not found.';
        }

        return response()->json($response);
    }
}

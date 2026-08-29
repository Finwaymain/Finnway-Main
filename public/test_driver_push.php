<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\GcmController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

header('Content-Type: application/json');

$driverId = $_GET['driver_id'] ?? $_GET['id'] ?? null;
$phone = $_GET['phone'] ?? null;

$query = DB::table('tj_conducteur');
if (!empty($driverId)) {
    $query->where('id', $driverId);
} elseif (!empty($phone)) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    $query->where('phone', 'like', "%{$cleanPhone}%");
} else {
    $query->where('fcm_id', '!=', '')->whereNotNull('fcm_id');
}

$drivers = $query->select('id', 'nom', 'prenom', 'phone', 'fcm_id', 'statut')->limit(10)->get();

if ($drivers->isEmpty()) {
    $drivers = DB::table('tj_conducteur')
        ->where('fcm_id', '!=', '')
        ->whereNotNull('fcm_id')
        ->select('id', 'nom', 'prenom', 'phone', 'fcm_id', 'statut')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get();
}

if ($drivers->isEmpty()) {
    $allDrivers = DB::table('tj_conducteur')->select('id', 'nom', 'prenom', 'phone', 'fcm_id', 'statut')->orderBy('id', 'desc')->limit(5)->get();
    echo json_encode([
        'success' => false,
        'message' => 'No drivers with active FCM tokens found. Showing latest 5 drivers.',
        'latest_drivers' => $allDrivers
    ], JSON_PRETTY_PRINT);
    exit;
}

$results = [];
foreach ($drivers as $driver) {
    $fcmToken = trim((string)$driver->fcm_id);
    
    if (empty($fcmToken)) {
        $results[] = [
            'driver_id' => $driver->id,
            'name' => trim("{$driver->prenom} {$driver->nom}"),
            'phone' => $driver->phone,
            'fcm_token_status' => 'EMPTY - Driver app has not uploaded FCM token to server yet',
            'push_result' => null
        ];
        continue;
    }

    $message = [
        'title' => 'Test Driver Notification 🚖',
        'body' => "Test push to driver #{$driver->id} (" . trim("{$driver->prenom} {$driver->nom}") . ")",
        'tag' => 'ridenewrider',
        'statut' => 'new',
        'id' => '99999',
        'depart_name' => 'Server Test Pickup',
        'destination_name' => 'Server Test Dropoff',
        'montant' => '100',
        'distance' => '5.0',
        'distance_unit' => 'KM'
    ];

    $response = GcmController::sendNotification($fcmToken, $message);
    $resData = is_object($response) && method_exists($response, 'getData') ? $response->getData(true) : $response;

    $isUnregistered = str_contains(json_encode($resData), 'UNREGISTERED') || str_contains(json_encode($resData), 'NotRegistered');

    $results[] = [
        'driver_id' => $driver->id,
        'name' => trim("{$driver->prenom} {$driver->nom}"),
        'phone' => $driver->phone,
        'fcm_token' => substr($fcmToken, 0, 20) . '...' . substr($fcmToken, -10),
        'fcm_token_length' => strlen($fcmToken),
        'token_status' => $isUnregistered ? 'EXPIRED/STALE TOKEN (App uninstalled or new token generated on phone)' : 'ACTIVE',
        'action_needed' => $isUnregistered ? 'Open Driver App on phone to register new fresh FCM token' : 'Token active',
        'push_result' => $resData
    ];
}

// Also test topic broadcast to 'cabme_driver'
$topicMessage = [
    'title' => 'Test Topic Broadcast 🚖',
    'body' => 'Broadcasting test request to cabme_driver topic',
    'tag' => 'ridenewrider',
    'statut' => 'new',
    'id' => '99999'
];

$topicResponse = GcmController::sendNotification('', $topicMessage, 'cabme_driver');
$topicResData = is_object($topicResponse) && method_exists($topicResponse, 'getData') ? $topicResponse->getData(true) : $topicResponse;

echo json_encode([
    'success' => true,
    'drivers_tested' => $results,
    'topic_broadcast_result' => $topicResData
], JSON_PRETTY_PRINT);

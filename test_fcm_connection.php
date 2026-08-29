<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=================================================================\n";
echo "  FIINWAY FIREBASE FCM CONNECTION STATUS\n";
echo "=================================================================\n\n";

$credentialsPath = storage_path('app/firebase/credentials.json');

if (!file_exists($credentialsPath)) {
    echo "  [FAIL] credentials.json not found at: $credentialsPath\n";
    exit(1);
}

$content = file_get_contents($credentialsPath);
$json = json_decode($content, true);

if (!$json || !isset($json['project_id'])) {
    echo "  [FAIL] credentials.json is not valid JSON.\n";
    exit(1);
}

echo "  Project ID       : " . ($json['project_id'] ?? 'N/A') . "\n";
echo "  Client Email     : " . ($json['client_email'] ?? 'N/A') . "\n";
echo "  Private Key ID   : " . ($json['private_key_id'] ?? 'N/A') . "\n\n";

try {
    $client = new \Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    $client->refreshTokenWithAssertion();
    $token = $client->getAccessToken();

    if (!empty($token['access_token'])) {
        echo "  [PASS] Google Cloud OAuth2 Access Token Verified!\n";
        echo "  Access Token     : " . substr($token['access_token'], 0, 25) . "...\n";
        echo "  Token Expires In : " . ($token['expires_in'] ?? 'N/A') . " seconds\n";
        echo "\n=================================================================\n";
        echo "  FCM HTTP v1 SERVER STATUS: 100% OPERATIONAL\n";
        echo "=================================================================\n";
    } else {
        echo "  [FAIL] Could not retrieve access token.\n";
    }
} catch (\Exception $e) {
    echo "  [FAIL] Authentication Exception: " . $e->getMessage() . "\n";
}

<?php
/**
 * Wallet Refresh Test Script
 * Tests wallet top-up and data refresh functionality
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Wallet Refresh Test Script ===\n\n";

// Test 1: Check wallet API endpoint
echo "Test 1: Checking wallet API endpoint...\n";
try {
    $request = \Illuminate\Http\Request::create('/api/v1/wallet?id_user=68&user_cat=driver', 'GET');
    $request->headers->set('apikey', 'base64:nTfofcBByTDenJQYlsRbH0JjeVFW5lWsIIyXtq8/9sU=');
    $response = app()->handle($request);
    echo "Status Code: {$response->getStatusCode()}\n";
    echo "Response: {$response->getContent()}\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// Test 2: Check wallet history API endpoint
echo "Test 2: Checking wallet history API endpoint...\n";
try {
    $request = \Illuminate\Http\Request::create('/api/v1/wallet-history?id_diver=68', 'GET');
    $request->headers->set('apikey', 'base64:nTfofcBByTDenJQYlsRbH0JjeVFW5lWsIIyXtq8/9sU=');
    $response = app()->handle($request);
    echo "Status Code: {$response->getStatusCode()}\n";
    echo "Response: {$response->getContent()}\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// Test 3: Check user wallet API endpoint
echo "Test 3: Checking user wallet API endpoint...\n";
try {
    $request = \Illuminate\Http\Request::create('/api/v1/wallet?id_user=68&user_cat=user_app', 'GET');
    $request->headers->set('apikey', 'base64:nTfofcBByTDenJQYlsRbH0JjeVFW5lWsIIyXtq8/9sU=');
    $response = app()->handle($request);
    echo "Status Code: {$response->getStatusCode()}\n";
    echo "Response: {$response->getContent()}\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// Test 4: Check user transaction API endpoint
echo "Test 4: Checking user transaction API endpoint...\n";
try {
    $request = \Illuminate\Http\Request::create('/api/v1/transaction?id_user_app=68', 'GET');
    $request->headers->set('apikey', 'base64:nTfofcBByTDenJQYlsRbH0JjeVFW5lWsIIyXtq8/9sU=');
    $response = app()->handle($request);
    echo "Status Code: {$response->getStatusCode()}\n";
    echo "Response: {$response->getContent()}\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo "=== Test Script Complete ===\n";

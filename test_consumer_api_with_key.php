<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate API request with API key header
$request = \Illuminate\Http\Request::create('/api/v1/get-consumer-plans', 'GET');
$request->headers->set('apikey', 'base64:nTfofcBByTDenJQYlsRbH0JjeVFW5lWsIIyXtq8/9sU=');

$response = app()->handle($request);
echo $response->getContent();

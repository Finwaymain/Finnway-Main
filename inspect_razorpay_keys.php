<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== CHECKING RAZORPAY / PAYMENT KEYS IN DB ===\n\n";

if (Schema::hasTable('api_key_settings')) {
    $keys = DB::table('api_key_settings')->get();
    echo "api_key_settings: " . json_encode($keys) . "\n\n";
}

if (Schema::hasTable('tj_settings')) {
    $settings = DB::table('tj_settings')->first();
    echo "tj_settings sample: " . json_encode($settings) . "\n\n";
}

if (Schema::hasTable('tj_payment_method')) {
    $pm = DB::table('tj_payment_method')->get();
    echo "tj_payment_method: " . json_encode($pm) . "\n\n";
}

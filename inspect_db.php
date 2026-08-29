<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== DB SCHEMA INSPECTION ===\n";

if (Schema::hasTable('referral')) {
    echo "referral columns: " . implode(', ', Schema::getColumnListing('referral')) . "\n";
    $sample = DB::table('referral')->first();
    echo "referral sample: " . json_encode($sample) . "\n\n";
}

if (Schema::hasTable('tj_transaction')) {
    echo "tj_transaction columns: " . implode(', ', Schema::getColumnListing('tj_transaction')) . "\n";
    $sample = DB::table('tj_transaction')->where('payment_method', 'Referral Reward')->first();
    echo "tj_transaction referral sample: " . json_encode($sample) . "\n\n";
}

if (Schema::hasTable('tj_user_app')) {
    echo "tj_user_app columns: " . implode(', ', array_slice(Schema::getColumnListing('tj_user_app'), 0, 15)) . "\n\n";
}

if (Schema::hasTable('tj_conducteur')) {
    echo "tj_conducteur columns: " . implode(', ', array_slice(Schema::getColumnListing('tj_conducteur'), 0, 15)) . "\n\n";
}

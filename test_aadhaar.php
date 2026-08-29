<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$phone = '9669454554';
$cleanPhone = preg_replace('/[^0-9]/', '', $phone);
$last10 = substr($cleanPhone, -10);

echo "Testing phone lookup for last10: " . $last10 . "\n";

$user = DB::table('tj_user_app')->where('phone', 'like', "%{$last10}")->first();
if ($user) {
    echo "Found user in tj_user_app:\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . ($user->prenom ?? '') . " " . ($user->nom ?? '') . "\n";
    echo "Phone: " . ($user->phone ?? '') . "\n";
    echo "Aadhar Number: '" . ($user->aadhar_number ?? '') . "'\n";
    echo "Photo NIC: '" . ($user->photo_nic ?? '') . "'\n";
    echo "Photo NIC Path: '" . ($user->photo_nic_path ?? '') . "'\n";
    echo "Statut NIC: '" . ($user->statut_nic ?? '') . "'\n";
    echo "KYC Status: '" . ($user->kyc_status ?? '') . "'\n";
} else {
    echo "User NOT found in tj_user_app by phone {$last10}\n";
}

$driver = DB::table('tj_conducteur')->where('phone', 'like', "%{$last10}")->first();
if ($driver) {
    echo "Found driver in tj_conducteur:\n";
    echo "ID: " . $driver->id . "\n";
    echo "Name: " . ($driver->prenom ?? '') . " " . ($driver->nom ?? '') . "\n";
    echo "Phone: " . ($driver->phone ?? '') . "\n";
    echo "Aadhar Number: '" . ($driver->aadhar_number ?? '') . "'\n";
} else {
    echo "Driver NOT found in tj_conducteur by phone {$last10}\n";
}

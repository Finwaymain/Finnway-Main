<?php
try {
    $seeder = new Database\Seeders\VehicleDataSeeder();
    $seeder->run();
    echo "Success\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

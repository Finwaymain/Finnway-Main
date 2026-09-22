<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\API\v1\Food\CustomerFoodController;
use App\Models\Food\FoodRestaurant;
use App\Models\UserApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=======================================================\n";
echo ">>> STARTING USER LOCATION & NEAREST RESTAURANTS TEST <<<\n";
echo "=======================================================\n\n";

function assertTest($condition, $stepName) {
    if ($condition) {
        echo "[PASS] $stepName\n";
    } else {
        echo "[FAIL] $stepName\n";
        exit(1);
    }
}

// ----------------------------------------------------------------------
// Step 1: Verify Database Schema
// ----------------------------------------------------------------------
echo "STEP 1: Check Database Columns on tj_user_app\n";
assertTest(Schema::hasColumn('tj_user_app', 'latitude'), "tj_user_app has 'latitude' column");
assertTest(Schema::hasColumn('tj_user_app', 'longitude'), "tj_user_app has 'longitude' column");
assertTest(Schema::hasColumn('tj_user_app', 'city'), "tj_user_app has 'city' column");
assertTest(Schema::hasColumn('tj_user_app', 'address'), "tj_user_app has 'address' column");
echo "\n";

// ----------------------------------------------------------------------
// Step 2: Ensure Test Customer 'test1' Exists
// ----------------------------------------------------------------------
echo "STEP 2: Setup Test Customer 'test1'\n";
$testUser = UserApp::find('998812');
if (!$testUser) {
    $testUser = new UserApp();
    $testUser->id = '998812';
    $testUser->prenom = 'Test1';
    $testUser->nom = 'Customer';
    $testUser->phone = '9999900001';
    $testUser->amount = 5000;
    $testUser->save();
}
// Clear old coordinates for clean test
DB::table('tj_user_app')->where('id', '998812')->update([
    'latitude' => null,
    'longitude' => null,
    'city' => null,
    'address' => null,
]);
assertTest(true, "Customer 'test1' initialized (ID: 998812, Phone: 9999900001)");
echo "\n";

// ----------------------------------------------------------------------
// Step 3: Seed Sample Test Restaurants in Ujjain
// ----------------------------------------------------------------------
echo "STEP 3: Seed Known Distance Restaurants Around Ujjain (Center: 23.1765, 75.7885)\n";
// Restaurant 1: Very close (~1.2 km)
$restA = FoodRestaurant::updateOrCreate(
    ['id' => 901],
    [
        'name' => 'Mahakal Bhojnalaya (Close)',
        'owner_phone' => '9999911001',
        'address' => 'Mahakal Marg, Ujjain',
        'city' => 'Ujjain',
        'latitude' => 23.1810,
        'longitude' => 75.7780,
        'delivery_radius_km' => 25,
        'operational_status' => 'open',
        'delivery_available' => true,
    ]
);

// Restaurant 2: Medium distance (~2.1 km)
$restB = FoodRestaurant::updateOrCreate(
    ['id' => 902],
    [
        'name' => 'Freeganj Flavors (Medium)',
        'owner_phone' => '9999911002',
        'address' => 'Freeganj Circle, Ujjain',
        'city' => 'Ujjain',
        'latitude' => 23.1670,
        'longitude' => 75.7950,
        'delivery_radius_km' => 25,
        'operational_status' => 'open',
        'delivery_available' => true,
    ]
);

// Restaurant 3: Far away (~21.0 km)
$restC = FoodRestaurant::updateOrCreate(
    ['id' => 903],
    [
        'name' => 'Highway Dhaba (Far)',
        'owner_phone' => '9999911003',
        'address' => 'Dewas-Ujjain Highway',
        'city' => 'Ujjain',
        'latitude' => 23.0500,
        'longitude' => 75.9500,
        'delivery_radius_km' => 25,
        'operational_status' => 'open',
        'delivery_available' => true,
    ]
);

assertTest($restA && $restB && $restC, "Seeded 3 test restaurants at known distances from user");
echo "\n";

// ----------------------------------------------------------------------
// Step 4: Test Location Persistence via updateLocation / saveUserLocation
// ----------------------------------------------------------------------
echo "STEP 4: Test Location Persistence in tj_user_app Table\n";
$controller = new CustomerFoodController();
$targetLat = 23.1765;
$targetLng = 75.7885;
$targetCity = 'Ujjain';
$targetAddress = 'Abdalpura, Ujjain, Madhya Pradesh 456006';

$reqUpdate = Request::create('/api/v1/food/customer/location', 'POST', [
    'user_id' => '998812',
    'phone' => '9999900001',
    'latitude' => $targetLat,
    'longitude' => $targetLng,
    'city' => $targetCity,
    'address' => $targetAddress,
]);
$resUpdate = $controller->updateLocation($reqUpdate);
$jsonUpdate = json_decode($resUpdate->getContent(), true);

assertTest($jsonUpdate['success'] === true, "updateLocation returned success");

// Verify directly from DB
$dbRow = DB::table('tj_user_app')->where('id', '998812')->first();
echo "Stored DB Row: Lat={$dbRow->latitude}, Lng={$dbRow->longitude}, City={$dbRow->city}\n";
assertTest(abs((float)$dbRow->latitude - $targetLat) < 0.0001, "Database latitude matches {$targetLat}");
assertTest(abs((float)$dbRow->longitude - $targetLng) < 0.0001, "Database longitude matches {$targetLng}");
assertTest($dbRow->city === $targetCity, "Database city is '{$targetCity}'");
assertTest($dbRow->address === $targetAddress, "Database address is '{$targetAddress}'");
echo "\n";

// ----------------------------------------------------------------------
// Step 5: Test Finding Nearest Restaurants with Coordinates
// ----------------------------------------------------------------------
echo "STEP 5: Test Finding Nearest Restaurants with Coordinates\n";
$reqNearby = Request::create('/api/v1/food/customer/nearby', 'GET', [
    'latitude' => $targetLat,
    'longitude' => $targetLng,
    'user_id' => '998812',
    'radius' => 25,
]);
$resNearby = $controller->nearby($reqNearby);
$jsonNearby = json_decode($resNearby->getContent(), true);

assertTest($jsonNearby['success'] === true, "nearby API returned success");
assertTest(count($jsonNearby['data']) >= 3, "Returned at least 3 nearby restaurants within 25km");

$distances = [];
foreach ($jsonNearby['data'] as $idx => $r) {
    echo "  [" . ($idx + 1) . "] {$r['name']} => Distance: {$r['distance_km']} km\n";
    $distances[] = (float) $r['distance_km'];
}

// Verify strict ascending order (nearest first)
$isSorted = true;
for ($i = 0; $i < count($distances) - 1; $i++) {
    if ($distances[$i] > $distances[$i + 1]) {
        $isSorted = false;
        break;
    }
}
assertTest($isSorted, "Restaurants are strictly sorted by nearest distance ascending");
assertTest($jsonNearby['data'][0]['id'] == 901, "Closest restaurant is Mahakal Bhojnalaya");
echo "\n";

// ----------------------------------------------------------------------
// Step 6: Test Finding Nearest Restaurants WITHOUT Coordinates in Request
// (Backend must retrieve saved coordinates from tj_user_app automatically)
// ----------------------------------------------------------------------
echo "STEP 6: Test Auto-Retrieval of User's Saved Location from Database\n";
$reqNoCoords = Request::create('/api/v1/food/customer/nearby', 'GET', [
    'user_id' => '998812',
    'radius' => 25,
]);
$resNoCoords = $controller->nearby($reqNoCoords);
$jsonNoCoords = json_decode($resNoCoords->getContent(), true);

assertTest($jsonNoCoords['success'] === true, "nearby without coords returned success");
assertTest(abs((float)$jsonNoCoords['user_lat'] - $targetLat) < 0.0001, "Auto-loaded user_lat matches stored coordinates");
assertTest(abs((float)$jsonNoCoords['user_lng'] - $targetLng) < 0.0001, "Auto-loaded user_lng matches stored coordinates");
assertTest($jsonNoCoords['city'] === 'Ujjain', "Auto-loaded city matches 'Ujjain'");
assertTest(count($jsonNoCoords['data']) >= 3, "Successfully computed distances using stored user coordinates");
echo "Top restaurant from DB stored location: {$jsonNoCoords['data'][0]['name']} ({$jsonNoCoords['data'][0]['distance_km']} km)\n";
assertTest($jsonNoCoords['data'][0]['id'] == 901, "Nearest restaurant accurately matched from DB coordinates");
echo "\n";

echo "=======================================================\n";
echo ">>> ALL 14 TESTS PASSED! USER LOCATION & NEAREST RESTAURANTS VERIFIED! <<<\n";
echo "=======================================================\n";

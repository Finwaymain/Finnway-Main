<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Driver;
use App\Models\Food\FoodCategory;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodProduct;
use App\Models\Food\FoodRestaurant;
use App\Models\UserApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

function runStep($title, callable $fn) {
    echo "\n=======================================================\n";
    echo "STEP: {$title}\n";
    echo "=======================================================\n";
    try {
        $result = $fn();
        echo "[PASS] {$title}\n";
        return $result;
    } catch (\Throwable $e) {
        echo "[FAIL] {$title}: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}

echo "\n>>> STARTING FOOD DELIVERY AUTOMATED TEST SUITE <<<\n";

// 1. Setup Test Users
$test1Customer = runStep("Setup Test Customer 'test1'", function () {
    $user = UserApp::where('phone', '9999900001')->orWhere('email', 'test1@fiinway.test')->first();
    if (!$user) {
        $user = new UserApp();
        $user->prenom = 'Test1';
        $user->nom = 'Customer';
        $user->phone = '9999900001';
        $user->email = 'test1@fiinway.test';
        $user->mdp = md5('123456');
        $user->statut = 'yes';
        $user->login_type = 'phone';
        $user->amount = 5000.00;
        $user->creer = date('Y-m-d H:i:s');
        $user->modifier = date('Y-m-d H:i:s');
        $user->save();
    } else {
        $user->prenom = 'Test1';
        $user->nom = 'Customer';
        $user->statut = 'yes';
        $user->amount = 5000.00;
        $user->save();
    }
    echo "Customer 'test1' Ready: ID={$user->id}, Name={$user->prenom} {$user->nom}, Phone={$user->phone}, Wallet=₹{$user->amount}\n";
    return $user;
});

$test2Driver = runStep("Setup Test Food Delivery Driver 'test2'", function () {
    $driver = Driver::where('phone', '9999900002')->orWhere('email', 'test2@fiinway.test')->first();
    if (!$driver) {
        $driver = new Driver();
        $driver->prenom = 'Test2';
        $driver->nom = 'FoodRider';
        $driver->phone = '9999900002';
        $driver->email = 'test2@fiinway.test';
        $driver->mdp = md5('123456');
        $driver->statut = 'yes';
        $driver->online = 'yes';
        $driver->is_verified = 1;
        $driver->latitude = '28.6139';
        $driver->longitude = '77.2090';
        $driver->statut_vehicule = 'DL-01-FD-2026';
        $driver->creer = date('Y-m-d H:i:s');
        $driver->save();
    } else {
        $driver->prenom = 'Test2';
        $driver->nom = 'FoodRider';
        $driver->statut = 'yes';
        $driver->online = 'yes';
        $driver->is_verified = 1;
        $driver->latitude = '28.6139';
        $driver->longitude = '77.2090';
        $driver->statut_vehicule = 'DL-01-FD-2026';
        $driver->save();
    }

    // Assign ONLY Food Delivery category (12889 under category 12888)
    DB::table('tj_conducteur_categories')->where('driver_id', $driver->id)->delete();
    DB::table('tj_conducteur_categories')->insert([
        'driver_id' => $driver->id,
        'category_id' => 12888,
        'subcategory_id' => 12889, // Food Delivery
        'statut' => 'approved',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "Food Driver 'test2' Ready: ID={$driver->id}, Name={$driver->prenom} {$driver->nom}, Phone={$driver->phone}, Subcategory=12889 (Food Delivery)\n";
    return $driver;
});

$testParcelDriver = runStep("Setup Test Parcel-Only Driver 'test_parcel'", function () {
    $driver = Driver::where('phone', '9999900003')->orWhere('email', 'testparcel@fiinway.test')->first();
    if (!$driver) {
        $driver = new Driver();
        $driver->prenom = 'ParcelOnly';
        $driver->nom = 'Driver';
        $driver->phone = '9999900003';
        $driver->email = 'testparcel@fiinway.test';
        $driver->mdp = md5('123456');
        $driver->statut = 'yes';
        $driver->online = 'yes';
        $driver->is_verified = 1;
        $driver->latitude = '28.6139';
        $driver->longitude = '77.2090';
        $driver->statut_vehicule = 'DL-01-PL-9999';
        $driver->creer = date('Y-m-d H:i:s');
        $driver->save();
    } else {
        $driver->prenom = 'ParcelOnly';
        $driver->nom = 'Driver';
        $driver->statut = 'yes';
        $driver->online = 'yes';
        $driver->is_verified = 1;
        $driver->latitude = '28.6139';
        $driver->longitude = '77.2090';
        $driver->save();
    }

    // Assign strictly Parcel Delivery subcategory (12890)
    DB::table('tj_conducteur_categories')->where('driver_id', $driver->id)->delete();
    DB::table('tj_conducteur_categories')->insert([
        'driver_id' => $driver->id,
        'category_id' => 12888,
        'subcategory_id' => 12890, // Parcel Delivery ONLY
        'statut' => 'approved',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "Parcel-Only Driver Ready: ID={$driver->id}, Name={$driver->prenom} {$driver->nom}, Phone={$driver->phone}, Subcategory=12890 (Parcel Delivery)\n";
    return $driver;
});

// 2. Setup Restaurant and Menu
$testRestaurant = runStep("Setup Test Restaurant & Products", function () {
    $restaurant = FoodRestaurant::where('slug', 'test-delhi-bistro')->first();
    if (!$restaurant) {
        $restaurant = FoodRestaurant::create([
            'name' => 'Test Delhi Bistro',
            'slug' => 'test-delhi-bistro',
            'owner_phone' => '9876543210',
            'address' => 'Connaught Place, New Delhi',
            'latitude' => 28.6315,
            'longitude' => 77.2167,
            'delivery_radius_km' => 15.0,
            'onboarding_status' => 'approved',
            'operational_status' => 'open',
            'delivery_available' => true,
            'cuisines' => ['North Indian', 'Biryani'],
        ]);
    } else {
        $restaurant->onboarding_status = 'approved';
        $restaurant->operational_status = 'open';
        $restaurant->delivery_available = true;
        $restaurant->save();
    }

    $category = FoodCategory::firstOrCreate(
        ['restaurant_id' => $restaurant->id, 'name' => 'Main Course'],
        ['is_active' => true, 'sort_order' => 1]
    );

    $product = FoodProduct::where('restaurant_id', $restaurant->id)->where('name', 'Butter Chicken Deluxe')->first();
    if (!$product) {
        $product = FoodProduct::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Butter Chicken Deluxe',
            'food_type' => 'non_veg',
            'restaurant_price' => 280.00,
            'discount_price' => 260.00,
            'is_active' => true,
            'availability' => 'available',
        ]);
    }

    echo "Restaurant Ready: ID={$restaurant->id}, Name={$restaurant->name}, Lat={$restaurant->latitude}, Lng={$restaurant->longitude}\n";
    echo "Product Ready: ID={$product->id}, Name={$product->name}, Price=₹{$product->restaurant_price}\n";
    return ['restaurant' => $restaurant, 'product' => $product];
});

// 3. Customer 'test1' places a Food Order
$order = runStep("Customer 'test1' Places Food Order", function () use ($test1Customer, $testRestaurant) {
    $controller = new \App\Http\Controllers\API\v1\Food\CustomerFoodController();
    $restaurant = $testRestaurant['restaurant'];
    $product = $testRestaurant['product'];

    $request = Request::create('/api/v1/food/customer/orders', 'POST', [
        'restaurant_id' => $restaurant->id,
        'customer_id' => $test1Customer->id,
        'customer_name' => "{$test1Customer->prenom} {$test1Customer->nom}",
        'customer_phone' => $test1Customer->phone,
        'delivery_address' => 'Barakhamba Road, New Delhi',
        'delivery_lat' => 28.6289,
        'delivery_lng' => 77.2285,
        'distance_km' => 1.8,
        'payment_method' => 'cod',
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
            ]
        ],
    ]);

    $response = $controller->placeOrder($request);
    $data = json_decode($response->getContent(), true);

    if (!$data['success']) {
        throw new Exception("placeOrder failed: " . ($data['error'] ?? 'Unknown error'));
    }

    $createdOrder = FoodOrder::find($data['data']['id']);
    echo "Order Placed Successfully! Order #{$createdOrder->order_number}, ID={$createdOrder->id}, Payable=₹{$createdOrder->customer_payable}, Status={$createdOrder->order_status}\n";
    echo "Generated Delivery OTP for Customer Handshake: {$createdOrder->delivery_otp}\n";
    return $createdOrder;
});

// 4. Restaurant Marks Order 'ready_for_pickup'
runStep("Restaurant Prepares Food & Marks 'ready_for_pickup'", function () use ($order) {
    $controller = new \App\Http\Controllers\API\v1\Food\RestaurantOrderController();
    $request = Request::create("/api/v1/food/restaurant/orders/{$order->id}/status", 'POST', [
        'restaurant_id' => $order->restaurant_id,
        'status' => 'ready_for_pickup'
    ]);

    $response = $controller->updateStatus($request, $order->id);
    $data = json_decode($response->getContent(), true);

    if (!$data['success']) {
        throw new Exception("updateStatus ready_for_pickup failed: " . ($data['error'] ?? 'Unknown'));
    }

    $order->refresh();
    echo "Restaurant marked order as ready! Status={$order->order_status}\n";
});

// 5. Test Strict Role Segregation: Parcel Driver vs Food Driver
runStep("Strict Segregation Test: Parcel Driver Querying Incoming Orders", function () use ($testParcelDriver) {
    $riderController = new \App\Http\Controllers\API\v1\Food\RiderFoodController();
    $request = Request::create('/api/v1/food/rider/incoming', 'GET', [
        'rider_id' => $testParcelDriver->id,
        'latitude' => 28.6139,
        'longitude' => 77.2090,
        'radius_km' => 10,
    ]);

    $response = $riderController->incoming($request);
    $data = json_decode($response->getContent(), true);

    if (count($data['data']) > 0) {
        throw new Exception("FAIL: Parcel-only driver was able to see food orders! Count: " . count($data['data']));
    }

    echo "VERIFIED: Parcel driver received 0 food delivery orders. Message: " . ($data['message'] ?? 'Filtered') . "\n";
});

runStep("Strict Segregation Test: Parcel Driver Attempting to Accept Food Order", function () use ($testParcelDriver, $order) {
    $riderController = new \App\Http\Controllers\API\v1\Food\RiderFoodController();
    $request = Request::create("/api/v1/food/rider/orders/{$order->id}/accept", 'POST', [
        'rider_id' => $testParcelDriver->id,
    ]);

    $response = $riderController->accept($request, $order->id);
    $data = json_decode($response->getContent(), true);

    if ($data['success']) {
        throw new Exception("FAIL: Parcel driver was permitted to accept a food delivery order!");
    }

    echo "VERIFIED: Backend successfully rejected parcel driver acceptance: " . $data['error'] . "\n";
});

// 6. Food Delivery Partner 'test2' Queries & Accepts Food Order
runStep("Food Driver 'test2' Queries Incoming Food Orders", function () use ($test2Driver, $order) {
    $riderController = new \App\Http\Controllers\API\v1\Food\RiderFoodController();
    $request = Request::create('/api/v1/food/rider/incoming', 'GET', [
        'rider_id' => $test2Driver->id,
        'latitude' => 28.6139,
        'longitude' => 77.2090,
        'radius_km' => 10,
    ]);

    $response = $riderController->incoming($request);
    $data = json_decode($response->getContent(), true);

    if (!$data['success'] || count($data['data']) === 0) {
        throw new Exception("Food driver test2 did NOT receive the incoming food order!");
    }

    echo "VERIFIED: Food driver test2 found " . count($data['data']) . " ready food order(s) nearby!\n";
    $found = false;
    foreach ($data['data'] as $o) {
        if ($o['id'] == $order->id) $found = true;
    }
    if (!$found) throw new Exception("Expected order #{$order->order_number} not found in incoming list.");
    echo "Order #{$order->order_number} successfully matched in Food Driver test2 queue!\n";
});

runStep("Food Driver 'test2' Accepts Food Delivery Order", function () use ($test2Driver, $order) {
    $riderController = new \App\Http\Controllers\API\v1\Food\RiderFoodController();
    $request = Request::create("/api/v1/food/rider/orders/{$order->id}/accept", 'POST', [
        'rider_id' => $test2Driver->id,
        'rider_name' => "{$test2Driver->prenom} {$test2Driver->nom}",
        'rider_phone' => $test2Driver->phone,
        'latitude' => 28.6139,
        'longitude' => 77.2090,
    ]);

    $response = $riderController->accept($request, $order->id);
    $data = json_decode($response->getContent(), true);

    if (!$data['success']) {
        throw new Exception("Food driver accept failed: " . ($data['error'] ?? 'Unknown'));
    }

    $order->refresh();
    $test2Driver->refresh();

    if ($order->order_status !== 'rider_assigned') throw new Exception("Order status is not 'rider_assigned'");
    if ($test2Driver->driver_on_ride !== 'yes') throw new Exception("Driver state driver_on_ride is not 'yes'");

    echo "VERIFIED: Order #{$order->order_number} accepted by Rider {$order->rider_name} (ID: {$order->rider_id})\n";
    echo "Pickup OTP generated for restaurant handover: {$order->pickup_otp}\n";
    echo "Driver status: driver_on_ride={$test2Driver->driver_on_ride}\n";
});

// 7. Milestone: Rider arrives at restaurant
runStep("Rider Milestone: 'arrived_restaurant'", function () use ($test2Driver, $order) {
    $riderController = new \App\Http\Controllers\API\v1\Food\RiderFoodController();
    $request = Request::create("/api/v1/food/rider/orders/{$order->id}/status", 'POST', [
        'rider_id' => $test2Driver->id,
        'status' => 'arrived_restaurant',
        'latitude' => 28.6315,
        'longitude' => 77.2167,
    ]);

    $response = $riderController->updateStatus($request, $order->id);
    $data = json_decode($response->getContent(), true);
    if (!$data['success']) throw new Exception("arrived_restaurant failed: " . ($data['error'] ?? ''));

    $order->refresh();
    echo "VERIFIED: Rider arrived at restaurant. Order status={$order->order_status}\n";
});

// 8. Handshake 1: Restaurant Pickup with Pickup OTP
runStep("Handshake 1: Rider Picks Up Food with Pickup OTP", function () use ($test2Driver, $order) {
    $riderController = new \App\Http\Controllers\API\v1\Food\RiderFoodController();
    
    // First test invalid OTP
    $badRequest = Request::create("/api/v1/food/rider/orders/{$order->id}/status", 'POST', [
        'rider_id' => $test2Driver->id,
        'status' => 'picked_up',
        'pickup_otp' => '0000',
    ]);
    $badResp = json_decode($riderController->updateStatus($badRequest, $order->id)->getContent(), true);
    if ($badResp['success']) throw new Exception("Backend accepted invalid pickup OTP!");
    echo "VERIFIED: Invalid pickup OTP was properly rejected.\n";

    // Now valid OTP
    $validRequest = Request::create("/api/v1/food/rider/orders/{$order->id}/status", 'POST', [
        'rider_id' => $test2Driver->id,
        'status' => 'picked_up',
        'pickup_otp' => $order->pickup_otp,
    ]);
    $validResp = json_decode($riderController->updateStatus($validRequest, $order->id)->getContent(), true);
    if (!$validResp['success']) throw new Exception("Valid pickup failed: " . ($validResp['error'] ?? ''));

    $order->refresh();
    echo "VERIFIED: Food picked up successfully! Order status={$order->order_status}, Picked up at={$order->picked_up_at}\n";
});

// 9. Rider Live GPS streaming while out for delivery
runStep("Rider Live GPS Streaming & Customer Map Tracking Check", function () use ($test2Driver, $order, $test1Customer) {
    $riderController = new \App\Http\Controllers\API\v1\Food\RiderFoodController();
    $customerController = new \App\Http\Controllers\API\v1\Food\CustomerFoodController();

    // Rider moves along route: Lat 28.6300, Lng 77.2200
    $locRequest = Request::create('/api/v1/food/rider/location', 'POST', [
        'rider_id' => $test2Driver->id,
        'latitude' => 28.6300,
        'longitude' => 77.2200,
    ]);
    $locResp = json_decode($riderController->updateLocation($locRequest)->getContent(), true);
    if (!$locResp['success']) throw new Exception("updateLocation failed!");
    echo "Rider GPS updated to (28.6300, 77.2200)\n";

    // Customer polls track endpoint
    $trackRequest = Request::create("/api/v1/food/customer/orders/{$order->id}/track", 'GET', [
        'customer_id' => $test1Customer->id
    ]);
    $trackResp = json_decode($customerController->track($trackRequest, $order->id)->getContent(), true);

    if (!$trackResp['success']) throw new Exception("Customer track failed: " . ($trackResp['error'] ?? ''));
    $trackData = $trackResp['data'];

    echo "Customer Track API Response:\n";
    echo " - Order Status: {$trackData['order_status']}\n";
    echo " - Restaurant: {$trackData['restaurant']['name']} ({$trackData['restaurant']['latitude']}, {$trackData['restaurant']['longitude']})\n";
    echo " - Customer Destination: ({$trackData['delivery_lat']}, {$trackData['delivery_lng']})\n";
    echo " - Live Rider: {$trackData['rider']['name']} at ({$trackData['rider']['latitude']}, {$trackData['rider']['longitude']})\n";
    echo " - Delivery OTP: {$trackData['delivery_otp']}\n";

    if (empty($trackData['rider']['latitude']) || abs($trackData['rider']['latitude'] - 28.6300) > 0.0001) {
        throw new Exception("Customer live track did not reflect updated rider latitude!");
    }
    echo "VERIFIED: Customer map receives accurate real-time rider coordinates and delivery OTP!\n";
});

// 10. Milestone: Rider arrives at customer doorstep
runStep("Rider Milestone: 'arrived_customer'", function () use ($test2Driver, $order) {
    $riderController = new \App\Http\Controllers\API\v1\Food\RiderFoodController();
    $request = Request::create("/api/v1/food/rider/orders/{$order->id}/status", 'POST', [
        'rider_id' => $test2Driver->id,
        'status' => 'arrived_customer',
        'latitude' => 28.6289,
        'longitude' => 77.2285,
    ]);

    $response = $riderController->updateStatus($request, $order->id);
    $data = json_decode($response->getContent(), true);
    if (!$data['success']) throw new Exception("arrived_customer failed: " . ($data['error'] ?? ''));

    $order->refresh();
    echo "VERIFIED: Rider arrived at customer doorstep! Order status={$order->order_status}\n";
});

// 11. Handshake 2: Complete Delivery with Customer Delivery OTP
runStep("Handshake 2: Rider Delivers Food with Customer Delivery OTP", function () use ($test2Driver, $order) {
    $riderController = new \App\Http\Controllers\API\v1\Food\RiderFoodController();

    // Reject bad delivery OTP
    $badRequest = Request::create("/api/v1/food/rider/orders/{$order->id}/status", 'POST', [
        'rider_id' => $test2Driver->id,
        'status' => 'delivered',
        'delivery_otp' => '9999',
    ]);
    $badResp = json_decode($riderController->updateStatus($badRequest, $order->id)->getContent(), true);
    if ($badResp['success']) throw new Exception("Backend accepted invalid delivery OTP!");
    echo "VERIFIED: Invalid customer delivery OTP was rejected.\n";

    // Valid delivery OTP
    $validRequest = Request::create("/api/v1/food/rider/orders/{$order->id}/status", 'POST', [
        'rider_id' => $test2Driver->id,
        'status' => 'delivered',
        'delivery_otp' => $order->delivery_otp,
    ]);
    $validResp = json_decode($riderController->updateStatus($validRequest, $order->id)->getContent(), true);
    if (!$validResp['success']) throw new Exception("Delivery failed: " . ($validResp['error'] ?? ''));

    $order->refresh();
    $test2Driver->refresh();

    if ($order->order_status !== 'delivered') throw new Exception("Order is not marked 'delivered'!");
    if ($test2Driver->driver_on_ride !== 'no') throw new Exception("Driver state was not reset to 'no'!");

    // Check COD due payment created
    $due = \App\Models\Food\FoodDuePayment::where('order_id', $order->id)->where('party_type', 'rider')->first();
    if (!$due) throw new Exception("COD Due payment record was not created for rider!");

    echo "VERIFIED: Order delivered successfully! Status={$order->order_status}, Delivered at={$order->delivered_at}\n";
    echo "VERIFIED: Rider COD collection recorded: Amount=₹{$due->amount}, Status={$due->status}\n";
    echo "VERIFIED: Rider state reset to driver_on_ride={$test2Driver->driver_on_ride}\n";
});

echo "\n*******************************************************\n";
echo "ALL TESTS PASSED SUCCESSFULLY!\n";
echo " - Test Customer: test1 (ID: {$test1Customer->id})\n";
echo " - Test Food Driver: test2 (ID: {$test2Driver->id})\n";
echo " - Test Parcel Driver: test_parcel (ID: {$testParcelDriver->id}) - Excluded as required\n";
echo " - Complete Flow Verified: Order -> Prep -> Strict Food Dispatch -> Rider Accept -> Pickup OTP -> Live GPS Track -> Doorstep -> Delivery OTP -> Complete\n";
echo "*******************************************************\n\n";

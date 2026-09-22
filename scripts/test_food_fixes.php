<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Food\FoodProduct;
use App\Models\Food\FoodRestaurant;
use App\Services\Food\FoodPricingEngine;
use App\Http\Controllers\API\v1\Food\CustomerFoodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=======================================================\n";
echo ">>> RUNNING FOOD DELIVERY BACKEND FIXES TEST <<<\n";
echo "=======================================================\n\n";

function assertTest($condition, $stepName) {
    if ($condition) {
        echo "[PASS] $stepName\n";
    } else {
        echo "[FAIL] $stepName\n";
        exit(1);
    }
}

// -------------------------------------------------------------
// Test 1: Product pricing logic for 250 with MRP 320
// -------------------------------------------------------------
echo "STEP 1: Testing Product Pricing Logic (Restaurant Price: 250, MRP: 320)\n";
$engine = new FoodPricingEngine();
$testProd = new FoodProduct([
    'id' => 9991,
    'restaurant_id' => 98,
    'name' => 'Test Item 250',
    'restaurant_price' => 250,
    'discount_price' => 320, // MRP
]);
$testRest = FoodRestaurant::first() ?? new FoodRestaurant(['id' => 98]);

$priceRes = $engine->customerUnitPrice($testProd, $testRest);
echo "Price calculation result:\n";
print_r($priceRes);

assertTest($priceRes['restaurant_price'] === 250.0, "restaurant_price is 250");
assertTest($priceRes['base_price'] === 250.0, "base_price is 250 (NOT 320!)");
assertTest($priceRes['mrp'] === 320.0, "mrp is detected as 320");
assertTest($priceRes['customer_price'] !== 345.0, "customer_price is NOT 345!");
echo "\n";

// -------------------------------------------------------------
// Test 2: Admin Taxes API
// -------------------------------------------------------------
echo "STEP 2: Testing Admin Taxes API (/api/v1/food/customer/taxes)\n";
$ctrl = new CustomerFoodController();
$taxReq = Request::create('/api/v1/food/customer/taxes', 'GET', ['payment_method' => 'wallet']);
$taxResp = $ctrl->getTaxes($taxReq);
$taxJson = json_decode($taxResp->getContent(), true);

assertTest($taxJson['success'] === true, "getTaxes returned success");
assertTest(isset($taxJson['data']) && is_array($taxJson['data']), "Taxes data is an array");
echo "Active Admin Taxes from tj_tax:\n";
foreach ($taxJson['data'] as $tx) {
    echo " - {$tx['label']} (Type: {$tx['type']}, Value: {$tx['value']})\n";
}
echo "\n";

// -------------------------------------------------------------
// Test 3: Rejection of Cash on Delivery (COD)
// -------------------------------------------------------------
echo "STEP 3: Testing Rejection of Cash on Delivery (COD)\n";
$realProd = FoodProduct::first();
$codReq = Request::create('/api/v1/food/customer/orders', 'POST', [
    'payment_method' => 'cod',
    'restaurant_id' => $realProd->restaurant_id,
    'items' => [
        ['product_id' => $realProd->id, 'quantity' => 1]
    ]
]);
$codResp = $ctrl->placeOrder($codReq);
$codJson = json_decode($codResp->getContent(), true);

echo "Response Status: " . $codResp->getStatusCode() . "\n";
echo "Response Body: " . $codResp->getContent() . "\n";

assertTest($codResp->getStatusCode() === 422, "COD returns HTTP 422 status");
assertTest($codJson['success'] === false, "COD request was rejected");
assertTest(str_contains(strtolower($codJson['error']), 'cash on delivery is not available'), "Clear error message rejecting COD");
echo "COD Rejection message: " . $codJson['error'] . "\n\n";

echo "=======================================================\n";
echo ">>> ALL BACKEND FIXES VERIFIED SUCCESSFULLY! <<<\n";
echo "=======================================================\n";

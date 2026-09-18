<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\ParcelOrder;
use App\Models\UserApp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ParcelDeliveryFlowTest extends TestCase
{
    protected string $apiKey = 'f7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2';
    protected string $accessToken = 'test_access_token_parcel';
    protected int $testUserId = 998811;
    protected int $testDriverId = 887722;
    protected ?int $createdParcelId = null;
    protected ?int $createdParcelId2 = null;

    protected function getHeaders(): array
    {
        return [
            'apikey' => $this->apiKey,
            'accesstoken' => $this->accessToken,
            'Accept' => 'application/json',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();

        // 1. Ensure test user exists in tj_user_app
        DB::table('tj_user_app')->updateOrInsert(
            ['id' => $this->testUserId],
            [
                'nom' => 'Sharma',
                'prenom' => 'Aakash',
                'phone' => '+919988112233',
                'email' => 'aakash.test@example.com',
                'amount' => 1500.00,
                'statut' => 'yes',
                'login_type' => 'phone',
                'creer' => date('Y-m-d H:i:s'),
                'modifier' => date('Y-m-d H:i:s'),
            ]
        );

        // 2. Ensure test driver exists in tj_conducteur with parcel_delivery = yes
        DB::table('tj_conducteur')->updateOrInsert(
            ['id' => $this->testDriverId],
            [
                'nom' => 'Kumar',
                'prenom' => 'Ramesh',
                'phone' => '+918877223344',
                'email' => 'ramesh.parcel@example.com',
                'amount' => 500.00,
                'statut' => 'yes',
                'online' => 'yes',
                'driver_on_ride' => 'no',
                'parcel_delivery' => 'yes',
                'is_verified' => 1,
                'latitude' => '28.6315',
                'longitude' => '77.2167',
                'creer' => date('Y-m-d H:i:s'),
                'modifier' => date('Y-m-d H:i:s'),
            ]
        );

        // 3. Ensure valid access token in users_access for API auth
        DB::table('users_access')->updateOrInsert(
            ['accesstoken' => $this->accessToken],
            [
                'user_id' => (string) $this->testUserId,
                'user_type' => 'customer',
            ]
        );

        // 4. Ensure at least one parcel category exists
        $cat = DB::table('parcel_category')->where('status', 'yes')->first();
        if (!$cat) {
            DB::table('parcel_category')->insert([
                'id' => 999,
                'title' => 'Standard Box',
                'image' => 'default_parcel.png',
                'status' => 'yes',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function tearDown(): void
    {
        // Clean up created parcel orders
        if ($this->createdParcelId) {
            DB::table('parcel_orders')->where('id', $this->createdParcelId)->delete();
        }
        if ($this->createdParcelId2) {
            DB::table('parcel_orders')->where('id', $this->createdParcelId2)->delete();
        }
        DB::table('parcel_orders')->where('id_user_app', $this->testUserId)->delete();
        DB::table('tj_transaction')->where('id_user_app', $this->testUserId)->delete();
        DB::table('tj_conducteur_transaction')->where('id_conducteur', $this->testDriverId)->delete();
        DB::table('users_access')->where('accesstoken', $this->accessToken)->delete();
        DB::table('tj_conducteur')->where('id', $this->testDriverId)->delete();
        DB::table('tj_user_app')->where('id', $this->testUserId)->delete();

        parent::tearDown();
    }

    /**
     * Test 1: Discover parcel categories
     */
    public function test_1_get_parcel_categories(): void
    {
        $response = $this->withHeaders($this->getHeaders())
            ->getJson('/api/v1/get-parcel-category');

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('title', $data[0]);
    }

    /**
     * Test 2: Create / Register a new parcel delivery order
     */
    public function test_2_parcel_register_creates_order_and_otp(): void
    {
        $cat = DB::table('parcel_category')->where('status', 'yes')->first();

        $payload = [
            'id_user_app' => $this->testUserId,
            'source' => 'Connaught Place, New Delhi',
            'lat_source' => '28.6315',
            'lng_source' => '77.2167',
            'destination' => 'India Gate, New Delhi',
            'lat_destination' => '28.6129',
            'lng_destination' => '77.2295',
            'distance' => '3.5',
            'distance_unit' => 'KM',
            'amount' => '120.00',
            'parcel_type' => $cat->id,
            'parcel_weight' => '2.5',
            'parcel_dimension' => '30x20x15',
            'sender_name' => 'Aakash Sharma',
            'sender_phone' => '+919988112233',
            'receiver_name' => 'Sunil Gupta',
            'receiver_phone' => '+919876543210',
            'note' => 'Handle with care fragile parcel',
        ];

        $response = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/parcel-register', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $responseData = $response->json('data');
        $orderData = is_array($responseData) && isset($responseData[0]) ? $responseData[0] : $responseData;

        $this->assertNotNull($orderData);
        $this->assertEquals('new', $orderData['status']);
        $this->assertNotEmpty($orderData['otp']);
        $this->assertEquals(6, strlen((string)$orderData['otp']));
        $this->assertEquals('120.00', $orderData['amount']);

        $this->createdParcelId = (int)$orderData['id'];
        $this->assertDatabaseHas('parcel_orders', [
            'id' => $this->createdParcelId,
            'id_user_app' => $this->testUserId,
            'status' => 'new',
        ]);
    }

    /**
     * Test 3: Search driver parcel orders nearby
     */
    public function test_3_search_driver_parcel_orders(): void
    {
        $this->test_2_parcel_register_creates_order_and_otp();

        $response = $this->withHeaders($this->getHeaders())
            ->getJson('/api/v1/search-driver-parcel-order?' . http_build_query([
                'id_driver' => $this->testDriverId,
                'lat' => '28.6315',
                'lng' => '77.2167',
            ]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $orders = $response->json('data');
        $this->assertIsArray($orders);
        $this->assertNotEmpty($orders);

        $found = collect($orders)->firstWhere('id', (string)$this->createdParcelId);
        $this->assertNotNull($found, 'Created parcel should be visible to nearby parcel driver');
        $this->assertEquals('new', $found['status']);
    }

    /**
     * Test 4: Driver accepts / confirms parcel order
     */
    public function test_4_driver_confirms_parcel_order(): void
    {
        $this->test_2_parcel_register_creates_order_and_otp();

        $response = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/parcel-confirm', [
                'id_parcel' => $this->createdParcelId,
                'driver_id' => $this->testDriverId,
                'driver_name' => 'Ramesh Kumar',
                'id_user' => $this->testUserId,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $data = $response->json('data');
        $this->assertEquals('confirmed', $data['status']);
        $this->assertEquals((string)$this->testDriverId, $data['driver_id']);
        $this->assertEquals('Ramesh Kumar', $data['driver_name']);

        $this->assertDatabaseHas('parcel_orders', [
            'id' => $this->createdParcelId,
            'id_conducteur' => $this->testDriverId,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Test 5: Driver starts ride / marks onride with OTP verification
     */
    public function test_5_driver_starts_parcel_onride(): void
    {
        $this->test_4_driver_confirms_parcel_order();

        $order = ParcelOrder::find($this->createdParcelId);
        $validOtp = $order->otp;

        // Test with invalid OTP if show_ride_otp is enabled
        $settings = DB::table('tj_settings')->select('show_ride_otp')->first();
        if ($settings && $settings->show_ride_otp == 'yes') {
            $wrongOtpResponse = $this->withHeaders($this->getHeaders())
                ->postJson('/api/v1/parcel-onride', [
                    'id_parcel' => $this->createdParcelId,
                    'driver_id' => $this->testDriverId,
                    'driver_name' => 'Ramesh Kumar',
                    'id_user' => $this->testUserId,
                    'otp' => '000000',
                ]);
            $wrongOtpResponse->assertJson(['success' => 'failed']);
        }

        // Test with valid OTP
        $response = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/parcel-onride', [
                'id_parcel' => $this->createdParcelId,
                'driver_id' => $this->testDriverId,
                'driver_name' => 'Ramesh Kumar',
                'id_user' => $this->testUserId,
                'otp' => $validOtp,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $this->assertDatabaseHas('parcel_orders', [
            'id' => $this->createdParcelId,
            'status' => 'onride',
        ]);

        $driver = Driver::find($this->testDriverId);
        $this->assertEquals('yes', $driver->driver_on_ride);
    }

    /**
     * Test 6: Verify delivery OTP via OtpVerificationController
     */
    public function test_6_delivery_otp_verification(): void
    {
        $this->test_5_driver_starts_parcel_onride();

        $order = ParcelOrder::find($this->createdParcelId);

        // Incorrect OTP
        $wrongResponse = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/verify-otp', [
                'ride_type' => 'parcel',
                'ride_id' => $this->createdParcelId,
                'id_user_app' => $this->testUserId,
                'otp' => '111111',
            ]);
        $wrongResponse->assertJson(['success' => 'Failed']);

        // Correct OTP
        $correctResponse = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/verify-otp', [
                'ride_type' => 'parcel',
                'ride_id' => $this->createdParcelId,
                'id_user_app' => $this->testUserId,
                'otp' => (string)$order->otp,
            ]);
        $correctResponse->assertJson([
            'success' => 'success',
            'message' => 'Successfully Verified OTP',
        ]);
    }

    /**
     * Test 7: Driver completes parcel delivery and releases on_ride status
     */
    public function test_7_driver_completes_parcel_delivery(): void
    {
        $this->test_5_driver_starts_parcel_onride();

        $response = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/parcel-complete', [
                'id_parcel' => $this->createdParcelId,
                'driver_name' => 'Ramesh Kumar',
                'id_user' => $this->testUserId,
                'from_id' => $this->testDriverId,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $this->assertDatabaseHas('parcel_orders', [
            'id' => $this->createdParcelId,
            'status' => 'completed',
        ]);

        // Driver must be released from active ride
        $driver = Driver::find($this->testDriverId);
        $this->assertEquals('no', $driver->driver_on_ride);
    }

    /**
     * Test 8: Settle parcel payment via Cash and record commission
     */
    public function test_8_parcel_payment_by_cash(): void
    {
        $this->test_7_driver_completes_parcel_delivery();

        $response = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/parcel-payment-by-cash', [
                'id_parcel' => $this->createdParcelId,
                'id_driver' => $this->testDriverId,
                'id_user_app' => $this->testUserId,
                'amount' => '120.00',
                'paymethod' => 'Cash',
                'discount' => '0.00',
                'tip' => '10.00',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $this->assertDatabaseHas('parcel_orders', [
            'id' => $this->createdParcelId,
            'payment_status' => 'yes',
        ]);

        // Commission transaction must be recorded
        $this->assertDatabaseHas('tj_conducteur_transaction', [
            'id_conducteur' => $this->testDriverId,
            'payment_method' => 'Commission',
            'id_ride' => $this->createdParcelId,
        ]);
    }

    /**
     * Test 9: Settle parcel payment via User Wallet
     */
    public function test_9_parcel_payment_by_wallet(): void
    {
        $this->test_7_driver_completes_parcel_delivery();

        $initialUserWallet = floatval(UserApp::find($this->testUserId)->amount);
        $initialDriverWallet = floatval(Driver::find($this->testDriverId)->amount);

        $response = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/parcel-pay-requete-wallet', [
                'id_parcel' => $this->createdParcelId,
                'id_driver' => $this->testDriverId,
                'id_user_app' => $this->testUserId,
                'amount' => '100.00',
                'paymethod' => 'Wallet',
                'payment_status' => 'yes',
                'discount' => '0.00',
                'tip' => '0.00',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'success',
            ]);

        $updatedUserWallet = floatval(UserApp::find($this->testUserId)->amount);
        $updatedDriverWallet = floatval(Driver::find($this->testDriverId)->amount);

        // Assert user wallet was debited
        $this->assertLessThan($initialUserWallet, $updatedUserWallet);
        // Assert driver wallet was credited
        $this->assertGreaterThan($initialDriverWallet, $updatedDriverWallet);
    }

    /**
     * Test 10: Fetch driver parcel orders, user parcel orders, and parcel details
     */
    public function test_10_fetch_parcel_orders_and_details(): void
    {
        $this->test_7_driver_completes_parcel_delivery();

        // 1. Driver Orders
        $driverOrdersResp = $this->withHeaders($this->getHeaders())
            ->getJson('/api/v1/get-driver-parcel-orders?id_driver=' . $this->testDriverId);

        $driverOrdersResp->assertStatus(200)
            ->assertJson(['success' => 'success']);
        $this->assertNotEmpty($driverOrdersResp->json('data'));

        // 2. User Orders
        $userOrdersResp = $this->withHeaders($this->getHeaders())
            ->getJson('/api/v1/get-user-parcel-orders?id_user_app=' . $this->testUserId);

        $userOrdersResp->assertStatus(200)
            ->assertJson(['success' => 'success']);
        $this->assertNotEmpty($userOrdersResp->json('data'));

        // 3. Parcel Detail
        $detailResp = $this->withHeaders($this->getHeaders())
            ->getJson('/api/v1/get-parcel-detail?parcel_id=' . $this->createdParcelId);

        $detailResp->assertStatus(200)
            ->assertJson(['success' => 'success']);

        $detail = $detailResp->json('data');
        $this->assertEquals((string)$this->createdParcelId, $detail['id']);
        $this->assertEquals('completed', $detail['status']);
        $this->assertEquals('Aakash Sharma', $detail['sender_name']);
        $this->assertEquals('Sunil Gupta', $detail['receiver_name']);
    }

    /**
     * Test 11: Parcel Rejection by driver and Cancellation by user
     */
    public function test_11_parcel_rejection_and_cancellation(): void
    {
        $cat = DB::table('parcel_category')->where('status', 'yes')->first();

        // Register 2nd order
        $regResponse = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/parcel-register', [
                'id_user_app' => $this->testUserId,
                'source' => 'Noida Sector 18',
                'lat_source' => '28.5708',
                'lng_source' => '77.3260',
                'destination' => 'Noida Sector 62',
                'lat_destination' => '28.6280',
                'lng_destination' => '77.3649',
                'distance' => '8.0',
                'distance_unit' => 'KM',
                'amount' => '250.00',
                'parcel_type' => $cat->id,
                'parcel_weight' => '5.0',
                'parcel_dimension' => '40x30x20',
                'sender_name' => 'Aakash Sharma',
                'sender_phone' => '+919988112233',
                'receiver_name' => 'Meera Jain',
                'receiver_phone' => '+919123456780',
            ]);

        $regData = $regResponse->json('data');
        $order2 = is_array($regData) && isset($regData[0]) ? $regData[0] : $regData;
        $this->createdParcelId2 = (int)$order2['id'];

        // Confirm by driver
        $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/parcel-confirm', [
                'id_parcel' => $this->createdParcelId2,
                'driver_id' => $this->testDriverId,
                'driver_name' => 'Ramesh Kumar',
                'id_user' => $this->testUserId,
            ]);

        // Driver rejects the order
        $rejectResp = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/parcel-rejected', [
                'id_parcel' => $this->createdParcelId2,
                'id_user' => $this->testUserId,
                'name' => 'Ramesh Kumar',
                'from_id' => $this->testDriverId,
                'reason' => 'Vehicle puncture',
                'user_cat' => 'driver',
            ]);

        $rejectResp->assertStatus(200)
            ->assertJson(['success' => 'success']);

        $this->assertDatabaseHas('parcel_orders', [
            'id' => $this->createdParcelId2,
            'status' => 'driver_rejected',
        ]);

        // User cancels the order
        $cancelResp = $this->withHeaders($this->getHeaders())
            ->postJson('/api/v1/parcel-canceled', [
                'parcel_id' => $this->createdParcelId2,
                'reason' => 'Changed delivery plans',
            ]);

        $cancelResp->assertStatus(200)
            ->assertJson(['success' => 'success']);

        $this->assertDatabaseHas('parcel_orders', [
            'id' => $this->createdParcelId2,
            'status' => 'canceled',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Food\FoodCategory;
use App\Models\Food\FoodOnboardingPayment;
use App\Models\Food\FoodOwner;
use App\Models\Food\FoodProduct;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodRestaurantType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestaurantModuleTest extends TestCase
{
    protected string $apiKey = 'f7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2';
    protected string $testPhone = '+919988776655';
    protected ?FoodOwner $testOwner = null;
    protected ?User $adminUser = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->adminUser = User::where('role', 'super_admin')->first() ?: User::first();

        // Clean up test phone if exists
        $existing = FoodOwner::where('phone', $this->testPhone)->first();
        if ($existing) {
            FoodProduct::whereHas('restaurant', function ($q) use ($existing) {
                $q->where('owner_id', $existing->id);
            })->delete();
            FoodCategory::whereHas('restaurant', function ($q) use ($existing) {
                $q->where('owner_id', $existing->id);
            })->delete();
            FoodOnboardingPayment::whereHas('restaurant', function ($q) use ($existing) {
                $q->where('owner_id', $existing->id);
            })->delete();
            FoodRestaurant::where('owner_id', $existing->id)->delete();
            $existing->delete();
        }
    }

    protected function tearDown(): void
    {
        if ($this->testOwner) {
            FoodProduct::whereHas('restaurant', function ($q) {
                $q->where('owner_id', $this->testOwner->id);
            })->delete();
            FoodCategory::whereHas('restaurant', function ($q) {
                $q->where('owner_id', $this->testOwner->id);
            })->delete();
            FoodOnboardingPayment::whereHas('restaurant', function ($q) {
                $q->where('owner_id', $this->testOwner->id);
            })->delete();
            FoodRestaurant::where('owner_id', $this->testOwner->id)->delete();
            $this->testOwner->delete();
        }
        parent::tearDown();
    }

    protected function apiHeaders(array $extra = []): array
    {
        return array_merge([
            'apikey' => $this->apiKey,
            'Accept' => 'application/json',
        ], $extra);
    }

    protected function authHeaders(string $token, array $extra = []): array
    {
        return $this->apiHeaders(array_merge([
            'X-Restaurant-Token' => $token,
        ], $extra));
    }

    // ==========================================
    // 1. API KEY AUTHENTICATION MIDDLEWARE TESTS
    // ==========================================

    public function test_api_requires_apikey_header(): void
    {
        $response = $this->postJson('/api/v1/food/auth/check-user', [
            'phone' => $this->testPhone,
        ]);
        $response->assertStatus(401);
    }

    public function test_api_rejects_invalid_apikey(): void
    {
        $response = $this->postJson('/api/v1/food/auth/check-user', [
            'phone' => $this->testPhone,
        ], ['apikey' => 'invalid-dummy-key']);
        $response->assertStatus(401);
    }

    // ==========================================
    // 2. PARTNER AUTH FLOW (PHONE -> OTP -> MPIN)
    // ==========================================

    public function test_check_user_validation_and_flow(): void
    {
        // Invalid phone
        $response = $this->postJson('/api/v1/food/auth/check-user', [
            'phone' => '12345',
        ], $this->apiHeaders());
        $response->assertStatus(200)->assertJson(['success' => false]);

        // Unregistered phone
        $response = $this->postJson('/api/v1/food/auth/check-user', [
            'phone' => $this->testPhone,
        ], $this->apiHeaders());
        $response->assertStatus(200)->assertJson([
            'success' => true,
            'data' => [
                'exists' => false,
                'has_mpin' => false,
                'profile_completed' => false,
            ],
        ]);
    }

    public function test_send_and_verify_otp_flow(): void
    {
        // Send OTP
        $sendResp = $this->postJson('/api/v1/food/auth/send-otp', [
            'phone' => $this->testPhone,
            'mode' => 'signup',
        ], $this->apiHeaders());
        $sendResp->assertStatus(200)->assertJson(['success' => true]);

        // Verify invalid OTP
        $invalidResp = $this->postJson('/api/v1/food/auth/verify-otp', [
            'phone' => $this->testPhone,
            'otp' => '9999',
        ], $this->apiHeaders());
        $invalidResp->assertStatus(200)->assertJson(['success' => false, 'error' => 'Invalid OTP.']);

        // Verify correct OTP
        $validResp = $this->postJson('/api/v1/food/auth/verify-otp', [
            'phone' => $this->testPhone,
            'otp' => '1234',
            'name' => 'Royal Spice Owner',
            'email' => 'owner@royalspice.test',
        ], $this->apiHeaders());

        $validResp->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['token', 'owner' => ['id', 'phone', 'name', 'email']]
            ]);

        $this->testOwner = FoodOwner::where('phone', $this->testPhone)->first();
        $this->assertNotNull($this->testOwner);
        $this->assertEquals('Royal Spice Owner', $this->testOwner->name);
    }

    public function test_mpin_setup_and_login_flow(): void
    {
        $this->test_send_and_verify_otp_flow();

        // Setup MPIN with invalid length
        $invalidMpinResp = $this->postJson('/api/v1/food/auth/setup-mpin', [
            'phone' => $this->testPhone,
            'mpin' => '123',
        ], $this->apiHeaders());
        $invalidMpinResp->assertStatus(200)->assertJson(['success' => false]);

        // Setup valid 4-digit MPIN
        $validMpinResp = $this->postJson('/api/v1/food/auth/setup-mpin', [
            'phone' => $this->testPhone,
            'mpin' => '4826',
        ], $this->apiHeaders());
        $validMpinResp->assertStatus(200)->assertJson(['success' => true]);

        // Check user status now reflects MPIN set
        $checkResp = $this->postJson('/api/v1/food/auth/check-user', [
            'phone' => $this->testPhone,
        ], $this->apiHeaders());
        $checkResp->assertStatus(200)->assertJson([
            'success' => true,
            'data' => [
                'exists' => true,
                'has_mpin' => true,
                'profile_completed' => true,
            ],
        ]);

        // Login with wrong MPIN
        $wrongLoginResp = $this->postJson('/api/v1/food/auth/login-mpin', [
            'phone' => $this->testPhone,
            'mpin' => '0000',
        ], $this->apiHeaders());
        $wrongLoginResp->assertStatus(200)->assertJson(['success' => false, 'error' => 'Invalid MPIN.']);

        // Login with correct MPIN
        $correctLoginResp = $this->postJson('/api/v1/food/auth/login-mpin', [
            'phone' => $this->testPhone,
            'mpin' => '4826',
        ], $this->apiHeaders());
        $correctLoginResp->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['token', 'owner']
            ]);
    }

    // ==========================================
    // 3. ONBOARDING & PROFILE FLOW
    // ==========================================

    public function test_restaurant_onboarding_submission_and_profile(): void
    {
        $this->test_mpin_setup_and_login_flow();
        $this->testOwner->refresh();
        $token = $this->testOwner->access_token;

        // Unauthenticated access
        $unauthResp = $this->getJson('/api/v1/food/restaurant/me', $this->apiHeaders());
        $unauthResp->assertStatus(401);

        // Submit Onboarding with 38 operational fields
        $onboardingData = [
            'business_type' => 'actual_restaurant',
            'name' => 'Royal Spice Bistro',
            'category' => 'North Indian, Mughlai, Biryani',
            'description' => 'Authentic Mughlai and Tandoori specialties.',
            'address' => 'Plot 42, Food Street, Indiranagar',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'pincode' => '560038',
            'latitude' => '12.9716',
            'longitude' => '77.5946',
            'opening_time' => '10:00:00',
            'closing_time' => '23:30:00',
            'avg_prep_minutes' => 25,
            'delivery_radius_km' => 8.5,
            'min_order_amount' => 150,
            'fssai_number' => '11223344556677',
            'gst_number' => '29AAAAA0000A1Z5',
            'pan_number' => 'ABCDE1234F',
            'bank_account_name' => 'Royal Spice Bistro LLP',
            'bank_name' => 'HDFC Bank',
            'bank_account_number' => '50100234567890',
            'bank_ifsc' => 'HDFC0001234',
            'upi_id' => 'royalspice@okhdfcbank',
            'pure_veg' => false,
        ];

        $submitResp = $this->postJson('/api/v1/food/restaurant/onboarding', $onboardingData, $this->authHeaders($token));
        $submitResp->assertStatus(200)->assertJson(['success' => true]);

        // Verify Restaurant is retrieved
        $meResp = $this->getJson('/api/v1/food/restaurant/me', $this->authHeaders($token));
        $meResp->assertStatus(200)->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Royal Spice Bistro',
                'city' => 'Bengaluru',
                'fssai_number' => '11223344556677',
            ],
        ]);
    }

    // ==========================================
    // 4. MENU CATEGORY & PRODUCT MANAGEMENT
    // ==========================================

    public function test_menu_category_and_product_crud(): void
    {
        $this->test_restaurant_onboarding_submission_and_profile();
        $this->testOwner->refresh();
        $token = $this->testOwner->access_token;

        // Create Category
        $catResp = $this->postJson('/api/v1/food/restaurant/categories', [
            'name' => 'Tandoori Starters',
            'description' => 'Charcoal roasted authentic delicacies',
            'sort_order' => 1,
        ], $this->authHeaders($token));
        $catResp->assertStatus(200)->assertJson(['success' => true]);
        $categoryId = $catResp->json('data.id');

        // Create Product
        $prodResp = $this->postJson('/api/v1/food/restaurant/products', [
            'category_id' => $categoryId,
            'name' => 'Murgh Malai Tikka',
            'description' => 'Creamy chicken skewers cooked in clay oven',
            'price' => 380.00,
            'food_type' => 'non_veg',
            'prep_time_minutes' => 20,
            'is_in_stock' => true,
        ], $this->authHeaders($token));
        $prodResp->assertStatus(200)->assertJson(['success' => true]);
        $productId = $prodResp->json('data.id');

        // Toggle stock availability
        $toggleResp = $this->postJson("/api/v1/food/restaurant/products/{$productId}/availability", [
            'is_in_stock' => false,
        ], $this->authHeaders($token));
        $toggleResp->assertStatus(200)->assertJson(['success' => true]);

        // Verify product reflects out of stock
        $product = FoodProduct::find($productId);
        $this->assertFalse((bool) $product->is_in_stock);
    }

    // ==========================================
    // 5. LIVE OPERATIONS & OPERATIONAL TOGGLE
    // ==========================================

    public function test_operational_status_and_incoming_polling(): void
    {
        $this->test_restaurant_onboarding_submission_and_profile();
        $restaurant = FoodRestaurant::where('owner_id', $this->testOwner->id)->first();
        $restaurant->onboarding_status = 'active';
        $restaurant->save();
        $token = $this->testOwner->access_token;

        // Toggle operational status to open
        $statusResp = $this->postJson('/api/v1/food/restaurant/operational-status', [
            'status' => 'open',
        ], $this->authHeaders($token));
        $statusResp->assertStatus(200)->assertJson(['success' => true]);

        $restaurant = FoodRestaurant::where('owner_id', $this->testOwner->id)->first();
        $this->assertEquals('open', $restaurant->operational_status);

        // Poll incoming orders
        $pollResp = $this->getJson('/api/v1/food/restaurant/orders/incoming', $this->authHeaders($token));
        $pollResp->assertStatus(200)->assertJson(['success' => true]);

        // Dashboard stats
        $dashResp = $this->getJson('/api/v1/food/restaurant/dashboard', $this->authHeaders($token));
        $dashResp->assertStatus(200)->assertJson(['success' => true]);
    }

    // ==========================================
    // 6. COD COMPANY DUE SETTLEMENT
    // ==========================================

    public function test_company_due_payment_submission(): void
    {
        $this->test_restaurant_onboarding_submission_and_profile();
        $this->testOwner->refresh();
        $token = $this->testOwner->access_token;

        $dueResp = $this->postJson('/api/v1/food/restaurant/dues/pay', [
            'amount' => 1250.00,
            'payment_mode' => 'upi',
            'transaction_reference' => 'UPI/UTR/987654321012',
            'notes' => 'Settling COD company commission for current week',
        ], $this->authHeaders($token));

        $dueResp->assertStatus(200)->assertJson(['success' => true]);
        $paymentId = $dueResp->json('data.id');
        $this->assertNotNull($paymentId);

        // Verify dues list reflects the pending payment
        $duesList = $this->getJson('/api/v1/food/restaurant/dues', $this->authHeaders($token));
        $duesList->assertStatus(200)->assertJson(['success' => true]);
    }

    // ==========================================
    // 7. ADMIN RESTAURANT CONTROL CENTER POWERS
    // ==========================================

    public function test_admin_restaurant_control_center_and_powers(): void
    {
        $this->test_company_due_payment_submission();
        $restaurant = FoodRestaurant::where('owner_id', $this->testOwner->id)->first();

        // 7.1 Admin restaurants listing
        $listResp = $this->actingAs($this->adminUser)->get('/admin/food/restaurants');
        $listResp->assertStatus(200);
        $listResp->assertSee('Royal Spice Bistro');

        // 7.2 Admin 7-tab Super Control Center
        $showResp = $this->actingAs($this->adminUser)->get("/admin/food/restaurants/{$restaurant->id}");
        $showResp->assertStatus(200);
        $showResp->assertSee('Royal Spice Bistro');
        $showResp->assertSee('Overview');
        $showResp->assertSee('Compliance Documents');
        $showResp->assertSee('Menu & Catalog', false);
        $showResp->assertSee('Commission & Rules', false);
        $showResp->assertSee('COD Dues & Settlements', false);

        // 7.3 Admin Document Verification
        $verifyDocResp = $this->actingAs($this->adminUser)->postJson("/admin/food/restaurants/{$restaurant->id}/verify-doc", [
            'document_type' => 'fssai_doc',
            'status' => 'verified',
            'notes' => 'FSSAI License verified against govt portal',
        ]);
        $verifyDocResp->assertStatus(200)->assertJson(['success' => true]);

        $restaurant->refresh();
        $docStatus = is_string($restaurant->doc_status) ? json_decode($restaurant->doc_status, true) : $restaurant->doc_status;
        $this->assertEquals('verified', $docStatus['fssai_doc']['status'] ?? null);

        // 7.4 Admin Request Re-upload for GST Doc
        $reuploadResp = $this->actingAs($this->adminUser)->postJson("/admin/food/restaurants/{$restaurant->id}/request-reupload", [
            'document_type' => 'gst_doc',
            'reason' => 'Uploaded image is blurry, please provide clear PDF certificate',
        ]);
        $reuploadResp->assertStatus(200)->assertJson(['success' => true]);

        $restaurant->refresh();
        $docStatus = is_string($restaurant->doc_status) ? json_decode($restaurant->doc_status, true) : $restaurant->doc_status;
        $this->assertEquals('reupload_requested', $docStatus['gst_doc']['status'] ?? null);

        // 7.5 Admin Custom Commission Override
        $commResp = $this->actingAs($this->adminUser)->postJson("/admin/food/restaurants/{$restaurant->id}/commission", [
            'custom_commission_rate' => 14.50,
        ]);
        $commResp->assertStatus(200)->assertJson(['success' => true]);

        $restaurant->refresh();
        $this->assertEquals(14.50, (float) $restaurant->custom_commission_rate);

        // 7.6 Admin Operational Status Override (Emergency close/open)
        $opResp = $this->actingAs($this->adminUser)->postJson("/admin/food/restaurants/{$restaurant->id}/status", [
            'operational_status' => 'closed',
        ]);
        $opResp->assertStatus(200)->assertJson(['success' => true]);

        $restaurant->refresh();
        $this->assertEquals('closed', $restaurant->operational_status);

        // 7.7 Admin Menu Item Creation
        $itemResp = $this->actingAs($this->adminUser)->postJson("/admin/food/restaurants/{$restaurant->id}/products", [
            'name' => 'Paneer Butter Masala Special',
            'price' => 320.00,
            'food_type' => 'veg',
            'is_in_stock' => 1,
            'is_recommended' => 1,
        ]);
        $itemResp->assertStatus(200)->assertJson(['success' => true]);

        $createdProduct = FoodProduct::where('restaurant_id', $restaurant->id)
            ->where('name', 'Paneer Butter Masala Special')
            ->first();
        $this->assertNotNull($createdProduct);

        // Admin toggle stock
        $toggleAdminResp = $this->actingAs($this->adminUser)->postJson("/admin/food/restaurants/{$restaurant->id}/products/{$createdProduct->id}/stock");
        $toggleAdminResp->assertStatus(200)->assertJson(['success' => true]);

        $createdProduct->refresh();
        $this->assertFalse((bool) $createdProduct->is_in_stock);

        // 7.8 Admin COD Due Payment Approval
        $pendingPayment = \App\Models\Food\FoodDuePayment::where('restaurant_id', $restaurant->id)->first();
        if ($pendingPayment) {
            $approveDueResp = $this->actingAs($this->adminUser)->postJson("/admin/food/restaurants/{$restaurant->id}/dues/{$pendingPayment->id}/approve");
            $approveDueResp->assertStatus(200)->assertJson(['success' => true]);

            $pendingPayment->refresh();
            $this->assertEquals('paid', $pendingPayment->status);
        }
    }

    // ==========================================
    // 8. PARTNER AUTH EDGE CASES & SECURITY
    // ==========================================

    public function test_partner_auth_edge_cases_and_security(): void
    {
        $this->test_mpin_setup_and_login_flow();
        $this->testOwner->refresh();

        // 8.1 Expired / Invalid token on protected endpoint
        $invalidTokenResp = $this->getJson('/api/v1/food/restaurant/me', $this->authHeaders('non-existent-expired-token-12345'));
        $invalidTokenResp->assertStatus(401)
            ->assertJson(['success' => false, 'error' => 'Invalid or expired restaurant token.']);

        // 8.2 Blocked account status
        $this->testOwner->status = 'blocked';
        $this->testOwner->save();

        $blockedLoginResp = $this->postJson('/api/v1/food/auth/login-mpin', [
            'phone' => $this->testPhone,
            'mpin' => '4826',
        ], $this->apiHeaders());
        $blockedLoginResp->assertStatus(200)
            ->assertJson(['success' => false, 'error' => 'Account blocked. Contact support.']);

        // Reset status for teardown
        $this->testOwner->status = 'active';
        $this->testOwner->save();
    }

    // ==========================================
    // 9. ONBOARDING EDGE CASES & VALIDATION
    // ==========================================

    public function test_onboarding_edge_cases_validation(): void
    {
        $this->test_mpin_setup_and_login_flow();
        $this->testOwner->refresh();
        $token = $this->testOwner->access_token;

        // 9.1 Missing restaurant name
        $noNameResp = $this->postJson('/api/v1/food/restaurant/onboarding', [
            'business_type' => 'actual_restaurant',
            'name' => '',
        ], $this->authHeaders($token));
        $noNameResp->assertStatus(200)
            ->assertJson(['success' => false, 'error' => 'Restaurant name is required.']);

        // 9.2 Non-existent business type
        $invalidTypeResp = $this->postJson('/api/v1/food/restaurant/onboarding', [
            'business_type' => 'space_station_canteen',
            'name' => 'Galactic Eats',
        ], $this->authHeaders($token));
        $invalidTypeResp->assertStatus(200)
            ->assertJson(['success' => false, 'error' => 'Invalid or inactive restaurant type.']);
    }

    // ==========================================
    // 10. MENU ITEM DELETION & INVENTORY EDGE CASES
    // ==========================================

    public function test_menu_deletion_and_stock_controls(): void
    {
        $this->test_menu_category_and_product_crud();
        $this->testOwner->refresh();
        $token = $this->testOwner->access_token;
        $restaurant = FoodRestaurant::where('owner_id', $this->testOwner->id)->first();

        $product = FoodProduct::where('restaurant_id', $restaurant->id)->first();
        $this->assertNotNull($product);

        // Partner deletes product
        $delProdResp = $this->deleteJson("/api/v1/food/restaurant/products/{$product->id}", [], $this->authHeaders($token));
        $delProdResp->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNull(FoodProduct::find($product->id));

        // Category deletion
        $category = FoodCategory::where('restaurant_id', $restaurant->id)->first();
        if ($category) {
            $delCatResp = $this->deleteJson("/api/v1/food/restaurant/categories/{$category->id}", [], $this->authHeaders($token));
            $delCatResp->assertStatus(200)->assertJson(['success' => true]);
            $this->assertNull(FoodCategory::find($category->id));
        }
    }

    // ==========================================
    // 11. ADMIN SUSPENSION & REJECTION CONTROLS
    // ==========================================

    public function test_admin_rejection_and_suspension_flow(): void
    {
        $this->test_restaurant_onboarding_submission_and_profile();
        $restaurant = FoodRestaurant::where('owner_id', $this->testOwner->id)->first();

        // 11.1 Admin Rejection
        $rejectResp = $this->actingAs($this->adminUser)->postJson("/admin/food/restaurants/{$restaurant->id}/reject", [
            'reason' => 'Invalid FSSAI certificate provided. Name mismatch.',
        ]);
        $rejectResp->assertStatus(200)->assertJson(['success' => true]);

        $restaurant->refresh();
        $this->assertEquals('rejected', $restaurant->onboarding_status);
        $this->assertEquals('Invalid FSSAI certificate provided. Name mismatch.', $restaurant->rejection_reason);

        // 11.2 Admin Suspension
        $suspendResp = $this->actingAs($this->adminUser)->postJson("/admin/food/restaurants/{$restaurant->id}/suspend");
        $suspendResp->assertStatus(200)->assertJson(['success' => true]);

        $restaurant->refresh();
        $this->assertEquals('suspended', $restaurant->onboarding_status);
        $this->assertEquals('closed', $restaurant->operational_status);
    }

    // ==========================================
    // 12. CUSTOMER DISCOVERY AND MENU APIS
    // ==========================================

    public function test_customer_discovery_and_menu_apis(): void
    {
        $this->test_restaurant_onboarding_submission_and_profile();
        $restaurant = FoodRestaurant::where('owner_id', $this->testOwner->id)->first();
        $restaurant->onboarding_status = 'active';
        $restaurant->operational_status = 'open';
        $restaurant->save();

        // Customer nearby restaurants discovery
        $nearbyResp = $this->getJson('/api/v1/food/customer/nearby?lat=12.9716&lng=77.5946', $this->apiHeaders());
        $nearbyResp->assertStatus(200)->assertJson(['success' => true]);

        // Customer restaurant menu viewing
        $menuResp = $this->getJson("/api/v1/food/customer/restaurants/{$restaurant->id}/menu", $this->apiHeaders());
        $menuResp->assertStatus(200)->assertJson(['success' => true]);
    }
}

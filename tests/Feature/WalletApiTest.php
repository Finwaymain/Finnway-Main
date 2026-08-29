<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    public function test_wallet_endpoint_requires_user_id_and_category(): void
    {
        $response = $this->getJson('/api/v1/wallet');

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'Failed',
                'error' => 'some field are missing',
            ]);
    }

    public function test_wallet_endpoint_returns_failed_for_unknown_user(): void
    {
        $response = $this->getJson('/api/v1/wallet?id_user=999999999&user_cat=user_app');

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'Failed',
            ]);
    }

    public function test_smart_value_profile_requires_account_number(): void
    {
        $response = $this->postJson('/api/v1/get_profile/smart-value', []);

        $response->assertStatus(422)
            ->assertJson([
                'res' => 'error',
            ]);
    }
}

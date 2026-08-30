<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\ServiceRewardConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServiceWiseRewardConfigTest extends TestCase
{
    private function getAdminUser()
    {
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@fiinway.test',
                'password' => bcrypt('password'),
            ]);
        }
        return $user;
    }

    /**
     * Test 1: Service-wise Reward Configuration page renders Reward Mode dropdown and Value columns
     */
    public function test_referral_page_renders_reward_mode_dropdown_and_value_columns(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get('/referral-engine');
        $response->assertStatus(200)
            ->assertSee('Business Value')
            ->assertSee('Customer Value')
            ->assertSee('Percentage')
            ->assertSee('Flat')
            ->assertDontSee('Max Limit');
    }

    /**
     * Test 2: Admin can save and persist Percentage Mode rewards to database
     */
    public function test_admin_can_save_percentage_mode_rewards(): void
    {
        $admin = $this->getAdminUser();

        // Create or find a test sub-category with a valid parent
        $parentCat = DB::table('tj_categorie_user')->whereNull('parent_id')->orWhere('parent_id', 0)->first();
        $parentId = $parentCat ? $parentCat->id : 999777;
        if (!$parentCat) {
            DB::table('tj_categorie_user')->insert([
                'id' => $parentId,
                'libelle' => 'Test Parent Category',
                'parent_id' => null,
                'statut' => 'yes',
            ]);
        }

        $testCatId = 999888;
        DB::table('tj_categorie_user')->updateOrInsert(
            ['id' => $testCatId],
            [
                'libelle' => 'Test Percentage SubService',
                'parent_id' => $parentId,
                'statut' => 'yes',
            ]
        );

        $payload = [
            'service_rewards_submit' => '1',
            "srv_cat_{$testCatId}_mode" => 'percentage',
            "srv_cat_{$testCatId}_business_val" => '3.5%',
            "srv_cat_{$testCatId}_customer_val" => '1.5%',
            "srv_cat_{$testCatId}_status" => '1',
        ];

        $response = $this->actingAs($admin)->post('/referral-engine/update', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify in database
        $this->assertDatabaseHas('service_reward_configs', [
            'category_id' => $testCatId,
            'reward_mode' => 'percentage',
            'business_value' => '3.5%',
            'customer_value' => '1.5%',
            'is_active' => true,
        ]);

        // Clean up test rows
        DB::table('service_reward_configs')->where('category_id', $testCatId)->delete();
        DB::table('tj_categorie_user')->where('id', $testCatId)->delete();
    }

    /**
     * Test 3: Admin can save and persist Flat Mode rewards to database
     */
    public function test_admin_can_save_flat_mode_rewards(): void
    {
        $admin = $this->getAdminUser();

        $parentCat = DB::table('tj_categorie_user')->whereNull('parent_id')->orWhere('parent_id', 0)->first();
        $parentId = $parentCat ? $parentCat->id : 999777;

        $testCatId = 999889;
        DB::table('tj_categorie_user')->updateOrInsert(
            ['id' => $testCatId],
            [
                'libelle' => 'Test Flat SubService',
                'parent_id' => $parentId,
                'statut' => 'yes',
            ]
        );

        $payload = [
            'service_rewards_submit' => '1',
            "srv_cat_{$testCatId}_mode" => 'flat',
            "srv_cat_{$testCatId}_business_val" => '50',
            "srv_cat_{$testCatId}_customer_val" => '25',
            "srv_cat_{$testCatId}_status" => '1',
        ];

        $response = $this->actingAs($admin)->post('/referral-engine/update', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify in database
        $this->assertDatabaseHas('service_reward_configs', [
            'category_id' => $testCatId,
            'reward_mode' => 'flat',
            'business_value' => '50',
            'customer_value' => '25',
            'is_active' => true,
        ]);

        // Clean up test rows
        DB::table('service_reward_configs')->where('category_id', $testCatId)->delete();
        DB::table('tj_categorie_user')->where('id', $testCatId)->delete();
    }
}

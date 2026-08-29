<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;

class AdminZoneManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

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
     * Test 1: Unauthenticated user is redirected to login
     */
    public function test_guest_is_redirected_from_zone_index(): void
    {
        $response = $this->get('/zone');
        $response->assertRedirect('/login');
    }

    /**
     * Test 2: Authenticated admin can access Zone listing page
     */
    public function test_admin_can_view_zone_index(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get('/zone');
        $response->assertStatus(200)
            ->assertSee('Zone')
            ->assertSee('Zone Name');
    }

    /**
     * Test 3: Authenticated admin can access Zone create page
     */
    public function test_admin_can_view_zone_create(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get('/zone/create');
        $response->assertStatus(200)
            ->assertSee('Create Zone');
    }

    /**
     * Test 4: Admin can store a new Zone with coordinates
     */
    public function test_admin_can_create_zone(): void
    {
        $admin = $this->getAdminUser();

        $coordinates = [
            [
                ['lat' => 19.0760, 'lng' => 72.8777],
                ['lat' => 19.0800, 'lng' => 72.8800],
                ['lat' => 19.0700, 'lng' => 72.8900],
                ['lat' => 19.0760, 'lng' => 72.8777],
            ]
        ];

        $payload = [
            'name' => 'Mumbai Central Test Zone',
            'status' => 'on',
            'coordinates' => json_encode($coordinates),
        ];

        $response = $this->actingAs($admin)->post('/zone/store', $payload);
        $response->assertRedirect('/zone');

        $this->assertDatabaseHas('zones', [
            'name' => 'Mumbai Central Test Zone',
            'status' => 'yes',
        ]);
    }

    /**
     * Test 5: Admin can toggle zone status via AJAX
     */
    public function test_admin_can_toggle_zone_status(): void
    {
        $admin = $this->getAdminUser();

        $zone = Zone::where('name', 'Mumbai Central Test Zone')->first();
        if ($zone) {
            $response = $this->actingAs($admin)->post('/zone/switch', [
                'id' => $zone->id,
                'ischeck' => 'false',
            ]);
            $response->assertStatus(200);

            $this->assertDatabaseHas('zones', [
                'id' => $zone->id,
                'status' => 'no',
            ]);
        }
    }

    /**
     * Test 6: Admin can delete zone
     */
    public function test_admin_can_delete_zone(): void
    {
        $admin = $this->getAdminUser();

        $zone = Zone::where('name', 'Mumbai Central Test Zone')->first();
        if ($zone) {
            $response = $this->actingAs($admin)->get("/zone/delete/{$zone->id}");
            $response->assertStatus(302);

            $this->assertDatabaseMissing('zones', [
                'id' => $zone->id,
            ]);
        }
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DriverConsoleResolutionTest extends TestCase
{
    protected string $apiKey = 'base64:nTfofcBByTDenJQYlsRbH0JjeVFW5lWsIIyXtq8/9sU=';
    protected int $transportDriverId = 998849;
    protected int $deliveryDriverId = 998850;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('tj_categorie_user')->updateOrInsert(
            ['id' => 12880],
            ['libelle' => 'Transport & Mobility', 'parent_id' => null, 'statut' => 1]
        );
        DB::table('tj_categorie_user')->updateOrInsert(
            ['id' => 12881],
            ['libelle' => 'Cab Driver', 'parent_id' => 12880, 'statut' => 1]
        );
        DB::table('tj_categorie_user')->updateOrInsert(
            ['id' => 12888],
            ['libelle' => 'Delivery & Logistics', 'parent_id' => null, 'statut' => 1]
        );
        DB::table('tj_categorie_user')->updateOrInsert(
            ['id' => 12890],
            ['libelle' => 'Parcel Delivery', 'parent_id' => 12888, 'statut' => 1]
        );
        DB::table('tj_categorie_user')->updateOrInsert(
            ['id' => 12901],
            ['libelle' => 'Marketplace & Sellers', 'parent_id' => null, 'statut' => 1]
        );
        DB::table('tj_categorie_user')->updateOrInsert(
            ['id' => 12902],
            ['libelle' => 'Online Seller', 'parent_id' => 12901, 'statut' => 1]
        );

        DB::table('tj_conducteur')->updateOrInsert(
            ['id' => $this->transportDriverId],
            [
                'nom' => 'Vvhh',
                'prenom' => 'Sabjj',
                'phone' => '+919988584770',
                'parcel_delivery' => 'yes',
                'category_id' => 12881,
                'bank_name' => 'HDFC Bank',
                'account_no' => '123456789012',
                'ifsc_code' => 'HDFC0001234',
                'photo_nic_path' => 'nic_test.jpg',
                'onboarding_completed' => 'yes',
                'is_verified' => 1,
                'statut' => 'yes',
                'statut_vehicule' => 'yes',
                'amount' => '300.00',
            ]
        );

        DB::table('tj_conducteur_categories')->where('driver_id', $this->transportDriverId)->delete();
        DB::table('tj_conducteur_categories')->insert([
            ['driver_id' => $this->transportDriverId, 'category_id' => 12902, 'subcategory_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['driver_id' => $this->transportDriverId, 'category_id' => 12881, 'subcategory_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('tj_vehicule')->updateOrInsert(
            ['id_conducteur' => $this->transportDriverId],
            [
                'brand' => 'Maruti Suzuki',
                'model' => 'WagonR',
                'numberplate' => 'FFGH',
                'statut' => 'yes',
            ]
        );

        DB::table('tj_conducteur')->updateOrInsert(
            ['id' => $this->deliveryDriverId],
            [
                'nom' => 'Rider',
                'prenom' => 'Dave',
                'phone' => '+919988584771',
                'parcel_delivery' => 'yes',
                'category_id' => 12890,
                'bank_name' => 'ICICI Bank',
                'account_no' => '987654321098',
                'ifsc_code' => 'ICIC0001234',
                'photo_nic_path' => 'nic_test_delivery.jpg',
                'onboarding_completed' => 'yes',
                'is_verified' => 1,
                'statut' => 'yes',
                'statut_vehicule' => 'yes',
                'amount' => '150.00',
            ]
        );

        DB::table('tj_conducteur_categories')->where('driver_id', $this->deliveryDriverId)->delete();
        DB::table('tj_conducteur_categories')->insert([
            ['driver_id' => $this->deliveryDriverId, 'category_id' => 12890, 'subcategory_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_transport_driver_receives_taxi_primary_console()
    {
        $response = $this->withHeaders([
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->postJson('/api/v1/profilebyphone', [
            'phone' => '+919988584770',
            'user_cat' => 'driver',
            'login_type' => 'phone',
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals('taxi', $data['primary_console']);
        $this->assertTrue($data['is_transport_category']);
        $this->assertFalse($data['is_delivery_partner']);
    }

    public function test_delivery_driver_receives_delivery_primary_console()
    {
        $response = $this->withHeaders([
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->postJson('/api/v1/profilebyphone', [
            'phone' => '+919988584771',
            'user_cat' => 'driver',
            'login_type' => 'phone',
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals('delivery', $data['primary_console']);
        $this->assertTrue($data['is_delivery_partner']);
        $this->assertFalse($data['is_transport_category']);
    }

    protected function tearDown(): void
    {
        DB::table('tj_vehicule')->where('id_conducteur', $this->transportDriverId)->delete();
        DB::table('tj_conducteur_categories')->whereIn('driver_id', [$this->transportDriverId, $this->deliveryDriverId])->delete();
        DB::table('tj_conducteur')->whereIn('id', [$this->transportDriverId, $this->deliveryDriverId])->delete();
        parent::tearDown();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Currency;
use App\Models\Settings;
use App\Models\PaymentMethod;
use App\Models\Driver;
use App\Models\AccessToken;
use App\Models\DriverDocument;
use App\Models\Zone;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed Admin User
        User::firstOrCreate(
            ['email' => 'admin@fooddelivery.com'],
            [
                'id' => 1,
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'created_at' => '2026-08-01 06:08:38',
                'updated_at' => '2026-08-01 06:08:38',
            ]
        );

        // 2. Seed Default Currency
        Currency::firstOrCreate(
            ['libelle' => 'USD'],
            [
                'symbole' => '$',
                'statut' => 'yes',
                'symbol_at_right' => 'false',
                'decimal_digit' => 2,
                'creer' => now(),
                'modifier' => now(),
            ]
        );

        // 3. Seed Default Settings
        Settings::firstOrCreate(
            ['title' => 'Fiinway'],
            [
                'footer' => 'Fiinway',
                'email' => 'admin@fooddelivery.com',
                'delivery_distance' => '100',
                'minimum_deposit_amount' => 10.00,
                'minimum_withdrawal_amount' => 50.00,
                'referral_amount' => 5.00,
                'parcel_active' => 'yes',
                'delivery_charge_parcel' => 5.00,
                'subscription_model' => 'no',
                'commission_model' => 'no',
                'creer' => now(),
                'modifier' => now(),
            ]
        );

        // 4. Seed Default Payment Methods
        $paymentMethods = [
            ['libelle' => 'Cash', 'statut' => 'yes'],
            ['libelle' => 'Wallet', 'statut' => 'yes'],
            ['libelle' => 'Stripe', 'statut' => 'yes'],
            ['libelle' => 'Razorpay', 'statut' => 'yes'],
        ];
        foreach ($paymentMethods as $pm) {
            PaymentMethod::firstOrCreate(
                ['libelle' => $pm['libelle']],
                [
                    'statut' => $pm['statut'],
                    'creer' => now(),
                    'modifier' => now(),
                ]
            );
        }

        // 5. Seed test driver
        $driver = Driver::firstOrCreate(
            ['email' => 'driver@fooddelivery.com'],
            [
                'id' => 1,
                'nom' => 'Test',
                'prenom' => 'Driver',
                'phone' => '1234567890',
                'mdp' => Hash::make('12345678'),
                'statut' => 'yes',
                'is_verified' => 0,
            ]
        );

        // 6. Seed access token for driver
        AccessToken::firstOrCreate(
            ['accesstoken' => 'test-driver-token'],
            [
                'user_id' => $driver->id,
                'user_type' => 'driver',
            ]
        );

        // 7. Seed default admin documents
        $documents = [
            ['id' => 1, 'title' => 'Aadhar Card', 'is_enabled' => 'Yes'],
            ['id' => 2, 'title' => 'Driving License', 'is_enabled' => 'Yes'],
            ['id' => 3, 'title' => 'PAN Card', 'is_enabled' => 'Yes'],
        ];
        foreach ($documents as $doc) {
            DriverDocument::firstOrCreate(
                ['id' => $doc['id']],
                [
                    'title' => $doc['title'],
                    'is_enabled' => $doc['is_enabled'],
                ]
            );
        }

        // 8. Seed default zone
        Zone::firstOrCreate(
            ['name' => 'Default Zone'],
            [
                'status' => 'yes',
            ]
        );

        // 9. Call other seeders
        $this->call(MarketplaceCategorySeeder::class);
        $this->call(UserCategorySeeder::class);
        $this->call(AllServicesCategorySeeder::class);
        $this->call(VehicleDataSeeder::class);
    }
}

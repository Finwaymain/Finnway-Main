<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleType;
use App\Models\Brand;
use App\Models\CarModel;
use Illuminate\Support\Facades\DB;

class VehicleDataSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Bike' => ['price' => '10', 'base_price' => 10.00, 'per_km_price' => 2.00, 'image' => 'https://cdn-icons-png.flaticon.com/512/3721/3721619.png'],
            'Auto' => ['price' => '20', 'base_price' => 20.00, 'per_km_price' => 3.00, 'image' => 'https://cdn-icons-png.flaticon.com/512/3201/3201844.png'],
            'Mini' => ['price' => '40', 'base_price' => 40.00, 'per_km_price' => 5.00, 'image' => 'https://cdn-icons-png.flaticon.com/512/55/55283.png'],
            'Sedan' => ['price' => '50', 'base_price' => 50.00, 'per_km_price' => 6.00, 'image' => 'https://cdn-icons-png.flaticon.com/512/3204/3204918.png'],
            'SUV' => ['price' => '70', 'base_price' => 70.00, 'per_km_price' => 8.00, 'image' => 'https://cdn-icons-png.flaticon.com/512/2830/2830305.png'],
            'XL (6–7 Seater)' => ['price' => '90', 'base_price' => 90.00, 'per_km_price' => 10.00, 'image' => 'https://cdn-icons-png.flaticon.com/512/867/867375.png'],
            'Luxury' => ['price' => '120', 'base_price' => 120.00, 'per_km_price' => 12.00, 'image' => 'https://cdn-icons-png.flaticon.com/512/3204/3204908.png'],
            'Premium XL (Luxury MPV/SUV)' => ['price' => '150', 'base_price' => 150.00, 'per_km_price' => 15.00, 'image' => 'https://cdn-icons-png.flaticon.com/512/2830/2830307.png'],
            'Pickup' => ['price' => '60', 'base_price' => 60.00, 'per_km_price' => 7.00, 'image' => 'https://cdn-icons-png.flaticon.com/512/2830/2830305.png'],
            'Truck' => ['price' => '100', 'base_price' => 100.00, 'per_km_price' => 11.00, 'image' => 'https://cdn-icons-png.flaticon.com/512/2830/2830305.png'],
        ];

        $categoryIds = [];

        foreach ($categories as $libelle => $data) {
            $cat = VehicleType::firstOrCreate(
                ['libelle' => $libelle],
                [
                    'prix' => $data['price'],
                    'base_price' => $data['base_price'],
                    'per_km_price' => $data['per_km_price'],
                    'image' => $data['image'],
                    'selected_image' => $data['image'],
                    'status' => 'Yes',
                    'creer' => now(),
                    'modifier' => now(),
                ]
            );
            // Ensure values are updated if the record already existed
            $cat->update([
                'base_price' => $data['base_price'],
                'per_km_price' => $data['per_km_price'],
            ]);
            $categoryIds[$libelle] = $cat->id;
        }

        $brandNames = [
            'Hero', 'Honda', 'TVS', 'Bajaj', 'Royal Enfield', 'Yamaha', 'Suzuki', 'KTM', 'Ather', 'Ola', 'Simple', 'Ultraviolette',
            'Piaggio', 'Mahindra', 'Maruti Suzuki', 'Hyundai', 'Tata', 'Toyota', 'Citroen', 'Renault', 'Volkswagen', 'Skoda',
            'Kia', 'MG', 'Nissan', 'Jeep', 'Force', 'Audi', 'BMW', 'Mercedes-Benz', 'Lexus', 'Volvo', 'Jaguar', 'Land Rover', 'Porsche',
            'Ashok Leyland', 'Eicher', 'Isuzu', 'BharatBenz', 'Scania'
        ];

        $brandIds = [];
        foreach ($brandNames as $brandName) {
            $brand = Brand::firstOrCreate(
                ['name' => $brandName],
                [
                    'status' => 'yes',
                    'modifier' => now(),
                ]
            );
            $brandIds[$brandName] = $brand->id;
        }



        // $modelsData is defined at the bottom of this file (global scope).
        // We use a static loader to access it since PHP class methods don't
        // see file-level variables without explicit import.
        static $modelsDataLoaded = null;
        if ($modelsDataLoaded === null) {
            // Pull the globally-scoped $modelsData after the file has been parsed
            $modelsDataLoaded = self::getModelsData();
        }
        foreach ($modelsDataLoaded as $data) {
            $brandId = $brandIds[$data['brand']] ?? null;
            $categoryId = $categoryIds[$data['category']] ?? null;

            if ($brandId && $categoryId) {
                CarModel::firstOrCreate(
                    ['name' => $data['name'], 'brand_id' => $brandId, 'vehicle_type_id' => $categoryId],
                    [
                        'status' => 'yes',
                        'modifier' => now(),
                    ]
                );
            }
        }

        // Seed category to vehicle type mappings
        DB::table('tj_category_user_vehicle_type')->truncate();
        
        // ─── Transport & Mobility ──────────────────────────────────────────────
        $mappings = [
            // Transport & Mobility subcategories
            'Cab Driver'   => ['Mini', 'Sedan', 'SUV', 'XL (6–7 Seater)', 'Luxury', 'Premium XL (Luxury MPV/SUV)'],
            'Bike Rider'   => ['Bike'],
            'Auto Driver'  => ['Auto'],
            'E-Rickshaw'   => ['Auto'],
            'Pickup'       => ['Pickup'],
            'Truck Owner'  => ['Truck'],
            'Fleet Owner'  => ['Bike', 'Auto', 'Mini', 'Sedan', 'SUV', 'XL (6–7 Seater)', 'Luxury', 'Premium XL (Luxury MPV/SUV)', 'Pickup', 'Truck'],

            // ─── Delivery & Logistics ──────────────────────────────────────────
            // Exact names from UserCategorySeeder.php:
            'Food Delivery'                    => ['Bike'],
            'Parcel Delivery'                  => ['Bike', 'Auto', 'Mini'],
            'Pickup & Drop (Personal runner)'  => ['Bike', 'Auto'],
            'Logistics Partner'                => ['Pickup', 'Truck'],
            'Packers & Movers'                 => ['Pickup', 'Truck'],
        ];

        foreach ($mappings as $userCatLibelle => $vehCatLibelles) {
            $userCat = DB::table('tj_categorie_user')->where('libelle', $userCatLibelle)->first();
            if ($userCat) {
                foreach ($vehCatLibelles as $vehCatLibelle) {
                    $vehCatId = $categoryIds[$vehCatLibelle] ?? null;
                    if ($vehCatId) {
                        DB::table('tj_category_user_vehicle_type')->insertOrIgnore([
                            'category_user_id' => $userCat->id,
                            'vehicle_type_id' => $vehCatId,
                        ]);
                    }
                }
            }
        }
    }

    public static function getModelsData() {
        return [

/*
|--------------------------------------------------------------------------
| HERO
|--------------------------------------------------------------------------
*/

['brand'=>'Hero','name'=>'Splendor Plus','category'=>'Bike'],
['brand'=>'Hero','name'=>'HF Deluxe','category'=>'Bike'],
['brand'=>'Hero','name'=>'Passion Plus','category'=>'Bike'],
['brand'=>'Hero','name'=>'Passion XTEC','category'=>'Bike'],
['brand'=>'Hero','name'=>'Glamour','category'=>'Bike'],
['brand'=>'Hero','name'=>'Super Splendor','category'=>'Bike'],
['brand'=>'Hero','name'=>'Xpulse 200','category'=>'Bike'],
['brand'=>'Hero','name'=>'Xpulse 200T','category'=>'Bike'],
['brand'=>'Hero','name'=>'Karizma XMR','category'=>'Bike'],
['brand'=>'Hero','name'=>'Xtreme 125R','category'=>'Bike'],
['brand'=>'Hero','name'=>'Xtreme 160R','category'=>'Bike'],
['brand'=>'Hero','name'=>'Xtreme 250R','category'=>'Bike'],
['brand'=>'Hero','name'=>'Mavrick 440','category'=>'Bike'],
['brand'=>'Hero','name'=>'Destini 125','category'=>'Bike'],
['brand'=>'Hero','name'=>'Pleasure+','category'=>'Bike'],
['brand'=>'Hero','name'=>'Xoom','category'=>'Bike'],

/*
|--------------------------------------------------------------------------
| HONDA
|--------------------------------------------------------------------------
*/

['brand'=>'Honda','name'=>'Shine','category'=>'Bike'],
['brand'=>'Honda','name'=>'SP125','category'=>'Bike'],
['brand'=>'Honda','name'=>'SP160','category'=>'Bike'],
['brand'=>'Honda','name'=>'Unicorn','category'=>'Bike'],
['brand'=>'Honda','name'=>'Hornet 2.0','category'=>'Bike'],
['brand'=>'Honda','name'=>'CB200X','category'=>'Bike'],
['brand'=>'Honda','name'=>'CB300F','category'=>'Bike'],
['brand'=>'Honda','name'=>'CB350','category'=>'Bike'],
['brand'=>'Honda','name'=>'Hness CB350','category'=>'Bike'],
['brand'=>'Honda','name'=>'CB350RS','category'=>'Bike'],
['brand'=>'Honda','name'=>'Activa 6G','category'=>'Bike'],
['brand'=>'Honda','name'=>'Activa 125','category'=>'Bike'],
['brand'=>'Honda','name'=>'Dio','category'=>'Bike'],
['brand'=>'Honda','name'=>'Aviator','category'=>'Bike'],

/*
|--------------------------------------------------------------------------
| TVS
|--------------------------------------------------------------------------
*/

['brand'=>'TVS','name'=>'Apache RTR 160','category'=>'Bike'],
['brand'=>'TVS','name'=>'Apache RTR 180','category'=>'Bike'],
['brand'=>'TVS','name'=>'Apache RTR 200','category'=>'Bike'],
['brand'=>'TVS','name'=>'Apache RR310','category'=>'Bike'],
['brand'=>'TVS','name'=>'Raider 125','category'=>'Bike'],
['brand'=>'TVS','name'=>'Sport','category'=>'Bike'],
['brand'=>'TVS','name'=>'Star City+','category'=>'Bike'],
['brand'=>'TVS','name'=>'Ronin','category'=>'Bike'],
['brand'=>'TVS','name'=>'Jupiter','category'=>'Bike'],
['brand'=>'TVS','name'=>'Ntorq 125','category'=>'Bike'],
['brand'=>'TVS','name'=>'iQube','category'=>'Bike'],

/*
|--------------------------------------------------------------------------
| BAJAJ
|--------------------------------------------------------------------------
*/

['brand'=>'Bajaj','name'=>'Pulsar 125','category'=>'Bike'],
['brand'=>'Bajaj','name'=>'Pulsar 150','category'=>'Bike'],
['brand'=>'Bajaj','name'=>'Pulsar N160','category'=>'Bike'],
['brand'=>'Bajaj','name'=>'Pulsar NS160','category'=>'Bike'],
['brand'=>'Bajaj','name'=>'Pulsar NS200','category'=>'Bike'],
['brand'=>'Bajaj','name'=>'Pulsar N250','category'=>'Bike'],
['brand'=>'Bajaj','name'=>'Dominar 250','category'=>'Bike'],
['brand'=>'Bajaj','name'=>'Dominar 400','category'=>'Bike'],
['brand'=>'Bajaj','name'=>'CT110X','category'=>'Bike'],
['brand'=>'Bajaj','name'=>'Platina 110','category'=>'Bike'],
['brand'=>'Bajaj','name'=>'Chetak EV','category'=>'Bike'],

/*
|--------------------------------------------------------------------------
| ROYAL ENFIELD
|--------------------------------------------------------------------------
*/

['brand'=>'Royal Enfield','name'=>'Classic 350','category'=>'Bike'],
['brand'=>'Royal Enfield','name'=>'Bullet 350','category'=>'Bike'],
['brand'=>'Royal Enfield','name'=>'Hunter 350','category'=>'Bike'],
['brand'=>'Royal Enfield','name'=>'Meteor 350','category'=>'Bike'],
['brand'=>'Royal Enfield','name'=>'Himalayan 450','category'=>'Bike'],
['brand'=>'Royal Enfield','name'=>'Interceptor 650','category'=>'Bike'],
['brand'=>'Royal Enfield','name'=>'Continental GT650','category'=>'Bike'],
['brand'=>'Royal Enfield','name'=>'Super Meteor 650','category'=>'Bike'],

/*
|--------------------------------------------------------------------------
| MARUTI SUZUKI
|--------------------------------------------------------------------------
*/

['brand'=>'Maruti Suzuki','name'=>'Alto K10','category'=>'Mini'],
['brand'=>'Maruti Suzuki','name'=>'S-Presso','category'=>'Mini'],
['brand'=>'Maruti Suzuki','name'=>'Celerio','category'=>'Mini'],
['brand'=>'Maruti Suzuki','name'=>'WagonR','category'=>'Mini'],
['brand'=>'Maruti Suzuki','name'=>'Ignis','category'=>'Mini'],
['brand'=>'Maruti Suzuki','name'=>'Swift','category'=>'Mini'],
['brand'=>'Maruti Suzuki','name'=>'Baleno','category'=>'Mini'],
['brand'=>'Maruti Suzuki','name'=>'Dzire','category'=>'Sedan'],
['brand'=>'Maruti Suzuki','name'=>'Ciaz','category'=>'Sedan'],
['brand'=>'Maruti Suzuki','name'=>'Brezza','category'=>'SUV'],
['brand'=>'Maruti Suzuki','name'=>'Fronx','category'=>'SUV'],
['brand'=>'Maruti Suzuki','name'=>'Jimny','category'=>'SUV'],
['brand'=>'Maruti Suzuki','name'=>'Grand Vitara','category'=>'SUV'],
['brand'=>'Maruti Suzuki','name'=>'Ertiga','category'=>'XL (6–7 Seater)'],
['brand'=>'Maruti Suzuki','name'=>'XL6','category'=>'XL (6–7 Seater)'],
['brand'=>'Maruti Suzuki','name'=>'Invicto','category'=>'Premium XL (Luxury MPV/SUV)'],

/*
|--------------------------------------------------------------------------
| HYUNDAI
|--------------------------------------------------------------------------
*/

['brand'=>'Hyundai','name'=>'Grand i10 Nios','category'=>'Mini'],
['brand'=>'Hyundai','name'=>'i20','category'=>'Mini'],
['brand'=>'Hyundai','name'=>'Exter','category'=>'SUV'],
['brand'=>'Hyundai','name'=>'Venue','category'=>'SUV'],
['brand'=>'Hyundai','name'=>'Creta','category'=>'SUV'],
['brand'=>'Hyundai','name'=>'Alcazar','category'=>'XL (6–7 Seater)'],
['brand'=>'Hyundai','name'=>'Verna','category'=>'Sedan'],
['brand'=>'Hyundai','name'=>'Tucson','category'=>'Luxury'],
['brand'=>'Hyundai','name'=>'Ioniq 5','category'=>'Luxury'],

/*
|--------------------------------------------------------------------------
| TATA
|--------------------------------------------------------------------------
*/

['brand'=>'Tata','name'=>'Tiago','category'=>'Mini'],
['brand'=>'Tata','name'=>'Tigor','category'=>'Sedan'],
['brand'=>'Tata','name'=>'Altroz','category'=>'Mini'],
['brand'=>'Tata','name'=>'Punch','category'=>'SUV'],
['brand'=>'Tata','name'=>'Nexon','category'=>'SUV'],
['brand'=>'Tata','name'=>'Harrier','category'=>'SUV'],
['brand'=>'Tata','name'=>'Safari','category'=>'XL (6–7 Seater)'],
['brand'=>'Tata','name'=>'Curvv','category'=>'SUV'],

/*
|--------------------------------------------------------------------------
| TOYOTA
|--------------------------------------------------------------------------
*/

['brand'=>'Toyota','name'=>'Glanza','category'=>'Mini'],
['brand'=>'Toyota','name'=>'Urban Cruiser Hyryder','category'=>'SUV'],
['brand'=>'Toyota','name'=>'Innova Crysta','category'=>'XL (6–7 Seater)'],
['brand'=>'Toyota','name'=>'Innova Hycross','category'=>'Premium XL (Luxury MPV/SUV)'],
['brand'=>'Toyota','name'=>'Fortuner','category'=>'Premium XL (Luxury MPV/SUV)'],
['brand'=>'Toyota','name'=>'Hilux','category'=>'Pickup'],
['brand'=>'Toyota','name'=>'Camry','category'=>'Luxury'],
['brand'=>'Toyota','name'=>'Vellfire','category'=>'Premium XL (Luxury MPV/SUV)'],
['brand'=>'Toyota','name'=>'Land Cruiser 300','category'=>'Premium XL (Luxury MPV/SUV)'],

/*
|--------------------------------------------------------------------------
| MAHINDRA
|--------------------------------------------------------------------------
*/

['brand'=>'Mahindra','name'=>'Bolero','category'=>'SUV'],
['brand'=>'Mahindra','name'=>'Bolero Neo','category'=>'SUV'],
['brand'=>'Mahindra','name'=>'Scorpio Classic','category'=>'SUV'],
['brand'=>'Mahindra','name'=>'Scorpio N','category'=>'SUV'],
['brand'=>'Mahindra','name'=>'Thar','category'=>'SUV'],
['brand'=>'Mahindra','name'=>'Thar Roxx','category'=>'SUV'],
['brand'=>'Mahindra','name'=>'XUV 3XO','category'=>'SUV'],
['brand'=>'Mahindra','name'=>'XUV700','category'=>'XL (6–7 Seater)'],
['brand'=>'Mahindra','name'=>'Marazzo','category'=>'XL (6–7 Seater)'],
['brand'=>'Mahindra','name'=>'Bolero Pickup','category'=>'Pickup'],
['brand'=>'Mahindra','name'=>'Jeeto','category'=>'Pickup'],

/*
|--------------------------------------------------------------------------
| ISUZU
|--------------------------------------------------------------------------
*/

['brand'=>'Isuzu','name'=>'D-Max','category'=>'Pickup'],
['brand'=>'Isuzu','name'=>'V-Cross','category'=>'Pickup'],
['brand'=>'Isuzu','name'=>'S-CAB','category'=>'Pickup'],

/*
|--------------------------------------------------------------------------
| FORCE
|--------------------------------------------------------------------------
*/

['brand'=>'Force','name'=>'Gurkha','category'=>'SUV'],
['brand'=>'Force','name'=>'Traveller','category'=>'XL (6–7 Seater)'],

/*
|--------------------------------------------------------------------------
| LUXURY
|--------------------------------------------------------------------------
*/

['brand'=>'BMW','name'=>'3 Series','category'=>'Luxury'],
['brand'=>'BMW','name'=>'5 Series','category'=>'Luxury'],
['brand'=>'BMW','name'=>'X1','category'=>'Luxury'],
['brand'=>'BMW','name'=>'X3','category'=>'Luxury'],
['brand'=>'BMW','name'=>'X5','category'=>'Premium XL (Luxury MPV/SUV)'],

['brand'=>'Mercedes-Benz','name'=>'A-Class','category'=>'Luxury'],
['brand'=>'Mercedes-Benz','name'=>'C-Class','category'=>'Luxury'],
['brand'=>'Mercedes-Benz','name'=>'E-Class','category'=>'Luxury'],
['brand'=>'Mercedes-Benz','name'=>'GLA','category'=>'Luxury'],
['brand'=>'Mercedes-Benz','name'=>'GLC','category'=>'Luxury'],
['brand'=>'Mercedes-Benz','name'=>'GLS','category'=>'Premium XL (Luxury MPV/SUV)'],

['brand'=>'Audi','name'=>'A4','category'=>'Luxury'],
['brand'=>'Audi','name'=>'A6','category'=>'Luxury'],
['brand'=>'Audi','name'=>'Q3','category'=>'Luxury'],
['brand'=>'Audi','name'=>'Q5','category'=>'Luxury'],
['brand'=>'Audi','name'=>'Q7','category'=>'Premium XL (Luxury MPV/SUV)'],

['brand'=>'Volvo','name'=>'XC40','category'=>'Luxury'],
['brand'=>'Volvo','name'=>'XC60','category'=>'Luxury'],
['brand'=>'Volvo','name'=>'XC90','category'=>'Premium XL (Luxury MPV/SUV)'],

['brand'=>'Jaguar','name'=>'F-Pace','category'=>'Luxury'],

['brand'=>'Land Rover','name'=>'Defender','category'=>'Premium XL (Luxury MPV/SUV)'],
['brand'=>'Land Rover','name'=>'Discovery','category'=>'Premium XL (Luxury MPV/SUV)'],
['brand'=>'Land Rover','name'=>'Range Rover Sport','category'=>'Premium XL (Luxury MPV/SUV)'],
['brand'=>'Land Rover','name'=>'Range Rover','category'=>'Premium XL (Luxury MPV/SUV)'],

['brand'=>'Lexus','name'=>'ES300h','category'=>'Luxury'],
['brand'=>'Lexus','name'=>'RX','category'=>'Premium XL (Luxury MPV/SUV)'],
['brand'=>'Lexus','name'=>'LM','category'=>'Premium XL (Luxury MPV/SUV)'],

['brand'=>'Porsche','name'=>'Macan','category'=>'Luxury'],
['brand'=>'Porsche','name'=>'Cayenne','category'=>'Premium XL (Luxury MPV/SUV)'],

/*
|--------------------------------------------------------------------------
| AUTO
|--------------------------------------------------------------------------
*/

['brand'=>'Bajaj','name'=>'RE Compact','category'=>'Auto'],
['brand'=>'Bajaj','name'=>'RE Maxima','category'=>'Auto'],
['brand'=>'Piaggio','name'=>'Ape Xtra','category'=>'Auto'],
['brand'=>'Piaggio','name'=>'Ape City','category'=>'Auto'],
['brand'=>'Piaggio','name'=>'Ape E-City','category'=>'Auto'],
['brand'=>'Mahindra','name'=>'Treo','category'=>'Auto'],
['brand'=>'Mahindra','name'=>'Alfa Plus','category'=>'Auto'],
['brand'=>'Mahindra','name'=>'E-Alfa Mini','category'=>'Auto'],

/*
|--------------------------------------------------------------------------
| TRUCK
|--------------------------------------------------------------------------
*/

['brand'=>'Tata','name'=>'Ace Gold','category'=>'Truck'],
['brand'=>'Tata','name'=>'Intra V10','category'=>'Truck'],
['brand'=>'Tata','name'=>'Intra V30','category'=>'Truck'],
['brand'=>'Tata','name'=>'407 Gold','category'=>'Truck'],
['brand'=>'Ashok Leyland','name'=>'Dost+','category'=>'Truck'],
['brand'=>'Ashok Leyland','name'=>'Bada Dost','category'=>'Truck'],
['brand'=>'Ashok Leyland','name'=>'Partner','category'=>'Truck'],
['brand'=>'Eicher','name'=>'Pro 2049','category'=>'Truck'],
['brand'=>'Eicher','name'=>'Pro 2095XP','category'=>'Truck'],
['brand'=>'BharatBenz','name'=>'1917R','category'=>'Truck'],
['brand'=>'Scania','name'=>'G410','category'=>'Truck'],

];
    }
}
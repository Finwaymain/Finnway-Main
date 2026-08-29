<?php
$mappings = [
    // Transport & Mobility subcategories
    'Cab Driver'   => ['Mini', 'Sedan', 'SUV', 'XL (6–7 Seater)', 'Luxury', 'Premium XL (Luxury MPV/SUV)'],
    'Bike Rider'   => ['Bike'],
    'Auto Driver'  => ['Auto'],
    'E-Rickshaw'   => ['Auto'],
    'Pickup'       => ['Pickup'],
    'Truck Owner'  => ['Truck'],
    'Fleet Owner'  => ['Bike', 'Auto', 'Mini', 'Sedan', 'SUV', 'XL (6–7 Seater)', 'Luxury', 'Premium XL (Luxury MPV/SUV)', 'Pickup', 'Truck'],

    // Delivery & Logistics
    'Food Delivery'                    => ['Bike'],
    'Parcel Delivery'                  => ['Bike', 'Auto', 'Mini'],
    'Pickup & Drop (Personal runner)'  => ['Bike', 'Auto'],
    'Logistics Partner'                => ['Pickup', 'Truck'],
    'Packers & Movers'                 => ['Pickup', 'Truck'],
];

DB::table('tj_category_user_vehicle_type')->truncate();
$categoryIds = DB::table('tj_type_vehicule')->pluck('id', 'libelle')->toArray();

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
echo "Done seeding mappings\n";

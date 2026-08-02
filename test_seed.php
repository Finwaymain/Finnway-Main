<?php
$mappings = [
    'Auto Driver'  => ['Auto'],
];
$categoryIds = DB::table('tj_type_vehicule')->pluck('id', 'libelle')->toArray();
$count = 0;
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
                $count++;
            } else {
                echo "vehCatId not found for $vehCatLibelle\n";
            }
        }
    } else {
        echo "userCat not found for $userCatLibelle\n";
    }
}
echo "Inserted $count rows\n";

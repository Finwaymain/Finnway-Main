<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the tj_category_user_vehicle_type pivot table.
     *
     * Maps Transport & Mobility subcategories → their allowed vehicle types.
     * This is the DB source of truth for businessRequiresVehicle() on the frontend.
     *
     * Vehicle type IDs from tj_type_vehicule:
     *   1=Bike, 2=Auto, 3=Mini, 4=Sedan, 5=SUV, 6=XL, 7=Luxury, 11=Pickup, 13=Truck
     *
     * Subcategory IDs from tj_categorie_user (parent_id = 9342 = Transport & Mobility):
     *   9343=Cab Driver, 9344=Bike Rider, 9345=Auto Driver,
     *   9346=E-Rickshaw, 9347=Pickup, 9348=Fleet Owner, 9349=Truck Owner
     */
    public function up(): void
    {
        // Resolve IDs dynamically so this works even if IDs change after re-seeding
        $transportParent = DB::table('tj_categorie_user')
            ->whereNull('parent_id')
            ->where('libelle', 'like', '%Transport%')
            ->value('id');

        if (!$transportParent) {
            return; // Transport category doesn't exist yet — skip
        }

        $subs = DB::table('tj_categorie_user')
            ->where('parent_id', $transportParent)
            ->pluck('id', 'libelle');

        // Resolve vehicle type IDs
        $vt = DB::table('tj_type_vehicule')->pluck('id', 'libelle');

        $mappings = [
            'Cab Driver'   => ['Mini', 'Sedan', 'SUV', 'XL (6–7 Seater)', 'Luxury'],
            'Bike Rider'   => ['Bike'],
            'Auto Driver'  => ['Auto'],
            'E-Rickshaw'   => ['Auto'],
            'Pickup'       => ['Pickup'],
            'Fleet Owner'  => ['Bike', 'Auto', 'Mini', 'Sedan', 'SUV', 'XL (6–7 Seater)', 'Luxury', 'Pickup', 'Truck'],
            'Truck Owner'  => ['Truck'],
        ];

        $rows = [];
        foreach ($mappings as $subName => $vehicleTypes) {
            $subId = $subs[$subName] ?? null;
            if (!$subId) continue;

            foreach ($vehicleTypes as $vtName) {
                // Try exact match first, then partial
                $vtId = $vt[$vtName] ?? null;
                if (!$vtId) {
                    // partial match (handles "XL (6–7 Seater)" vs "XL (6\u20137 Seater)" encoding)
                    foreach ($vt as $name => $id) {
                        if (str_contains($name, explode(' ', $vtName)[0])) {
                            $vtId = $id;
                            break;
                        }
                    }
                }
                if (!$vtId) continue;

                // Avoid duplicates
                $exists = DB::table('tj_category_user_vehicle_type')
                    ->where('category_user_id', $subId)
                    ->where('vehicle_type_id', $vtId)
                    ->exists();

                if (!$exists) {
                    $rows[] = [
                        'category_user_id' => $subId,
                        'vehicle_type_id'  => $vtId,
                    ];
                }
            }
        }

        if (!empty($rows)) {
            DB::table('tj_category_user_vehicle_type')->insert($rows);
        }
    }

    public function down(): void
    {
        // Remove only the rows we inserted (Transport & Mobility subcategories)
        $transportParent = DB::table('tj_categorie_user')
            ->whereNull('parent_id')
            ->where('libelle', 'like', '%Transport%')
            ->value('id');

        if (!$transportParent) return;

        $subIds = DB::table('tj_categorie_user')
            ->where('parent_id', $transportParent)
            ->pluck('id');

        DB::table('tj_category_user_vehicle_type')
            ->whereIn('category_user_id', $subIds)
            ->delete();
    }
};

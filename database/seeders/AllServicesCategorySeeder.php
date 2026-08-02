<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the consumer-facing "All Services" catalog (the "More" section in the
 * user app) into tj_categorie_user. This is a separate tree from
 * UserCategorySeeder, which seeds provider/driver signup categories — same
 * table, unrelated data, distinguished by their own top-level parent rows.
 */
class AllServicesCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Home Services' => [
                'Cleaner', 'Electrician', 'Plumber', 'Carpenter', 'Painter', 'Pest Control',
            ],
            'Repair & Maintenance' => [
                'Electrician', 'Plumber', 'Carpenter', 'Painter', 'Mason (Raj Mistri)', 'Welder',
                'Handyman', 'Door Repair', 'Window Repair', 'Furniture Repair',
            ],
            'AC & Appliances' => [
                'AC Installation', 'AC Repair', 'AC Gas Filling', 'Refrigerator Repair',
                'Washing Machine Repair', 'Microwave Repair', 'Water Purifier (RO) Service',
                'Geyser Repair', 'Chimney Service', 'Dishwasher Repair', 'TV Repair', 'Fan Repair',
                'Cooler Repair', 'Inverter Repair', 'Generator Repair',
            ],
            'Cleaning Services' => [
                'Home Cleaning', 'Deep Cleaning', 'Bathroom Cleaning', 'Kitchen Cleaning',
                'Sofa Cleaning', 'Carpet Cleaning', 'Mattress Cleaning', 'Water Tank Cleaning',
                'Floor Cleaning', 'Glass Cleaning', 'Office Cleaning',
            ],
            'Interior & Renovation' => [
                'Interior Designer', 'Modular Kitchen', 'False Ceiling', 'Flooring Work',
                'Wallpaper Installation', 'Tile Installation', 'POP Work', 'Curtain Installation',
                'Furniture Installation',
            ],
            'Outdoor Services' => [
                'Gardening', 'Lawn Maintenance', 'Tree Cutting', 'Plant Care', 'Landscape Design',
            ],
            'Security & Safety' => [
                'CCTV Installation', 'CCTV Repair', 'Smart Lock Installation', 'Security Guard',
                'Fire Safety Equipment', 'Video Door Phone Installation',
            ],
            'Smart Home Services' => [
                'Smart Light Installation', 'Home Automation', 'Wi-Fi Setup', 'Network Installation',
                'Smart Door Lock Setup',
            ],
            'Water Services' => [
                'Borewell Service', 'Water Tank Repair', 'Pipeline Repair', 'Motor Pump Repair',
                'Water Leakage Detection',
            ],
            'Construction Services' => [
                'Home Construction', 'House Renovation', 'Civil Contractor', 'Building Repair',
                'Roof Repair', 'Waterproofing',
            ],
            'Furniture Services' => [
                'Furniture Assembly', 'Furniture Shifting', 'Office Furniture Setup',
                'Bed Installation', 'Wardrobe Installation',
            ],
            'Pest Control' => [
                'Termite Control', 'Cockroach Control', 'Mosquito Control', 'Rodent Control',
                'General Pest Control',
            ],
            'Shifting Services' => [
                'House Shifting', 'Office Shifting', 'Packers & Movers', 'Local Moving',
                'Interstate Moving',
            ],
            'Personal Home Assistance' => [
                'Maid Service', 'Cook', 'Babysitter', 'Elder Care', 'Patient Care', 'Driver on Demand',
            ],
            'Pet Services' => [
                'Pet Grooming', 'Pet Walking', 'Pet Boarding', 'Veterinary Visit',
            ],
            'Laundry & Textile' => [
                'Laundry Pickup', 'Dry Cleaning', 'Ironing Service', 'Shoe Cleaning', 'Curtain Washing',
            ],
            'Technology Services' => [
                'Laptop Repair', 'Computer Repair', 'Printer Repair', 'CCTV Installation',
                'Wi-Fi Installation', 'Smart Home Installation', 'TV Wall Mount Installation',
            ],
            'Personal Services' => [
                'Barber & Saloon Service', 'Salon Spa & Others (Female)', 'Massage Therapist',
            ],
            'Education Services' => [
                'Home Tutor', 'Music Teacher', 'Dance Teacher', 'Yoga Trainer', 'Gym Trainer',
                'Language Tutor',
            ],
            'Healthcare Services' => [
                'Doctor Home Visit', 'Physiotherapist', 'Lab Sample Collection', 'Nursing Care',
                'Ambulance Booking',
            ],
            // Also promoted as standalone quick-access tiles on the All Services grid —
            // each is a leaf (books directly, no sub-grid), matching the reference UI.
            'Doctor Home Visit' => [],
            'Physiotherapy' => [],
            'Lab Sample Collection' => [],
            'Nursing Care' => [],
            'Ambulance Booking' => [],
            'Miscellaneous' => [
                'Scrap Collection', 'Water Can Delivery', 'Gas Cylinder Delivery', 'Home Decoration',
                'Event Decoration', 'Festival Decoration', 'Tent & Furniture Rental', 'Home Inspection',
            ],
        ];

        // Wipe any previously-seeded rows from this catalog only (type-tagged, so
        // this can never touch UserCategorySeeder's provider-signup tree) and
        // reinsert clean — cascades to children via the parent_id FK.
        DB::table('tj_categorie_user')->where('type', 'consumer_service')->whereNull('parent_id')->delete();

        foreach ($categories as $parentName => $subCategories) {
            $parentId = DB::table('tj_categorie_user')->insertGetId([
                'libelle' => $parentName,
                'parent_id' => null,
                'type' => 'consumer_service',
                'statut' => true,
                'creer' => date('Y-m-d H:i:s'),
                'modifier' => date('Y-m-d H:i:s'),
            ]);

            foreach ($subCategories as $subName) {
                DB::table('tj_categorie_user')->insert([
                    'libelle' => $subName,
                    'parent_id' => $parentId,
                    'type' => 'consumer_service',
                    'statut' => true,
                    'creer' => date('Y-m-d H:i:s'),
                    'modifier' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}

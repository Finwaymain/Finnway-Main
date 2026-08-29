<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the consumer-facing Home Services catalog shown when tapping "More"
 * in Quick Access. Tagged type=consumer_service so it never mixes with
 * provider signup categories (UserCategorySeeder).
 */
class AllServicesCategorySeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('tj_categorie_user')) {
            return;
        }

        $categories = [
            'Home Services' => [
                'icon' => 'icon:home_rounded',
                'children' => ['Cleaner', 'Electrician', 'Plumber', 'Carpenter', 'Painter', 'Pest Control'],
            ],
            'Repair & Maintenance' => [
                'icon' => 'icon:build_rounded',
                'children' => [
                    'Electrician', 'Plumber', 'Carpenter', 'Painter', 'Mason (Raj Mistri)', 'Welder',
                    'Handyman', 'Door Repair', 'Window Repair', 'Furniture Repair',
                ],
            ],
            'AC & Appliances' => [
                'icon' => 'icon:ac_unit_rounded',
                'children' => [
                    'AC Installation', 'AC Repair', 'AC Gas Filling', 'Refrigerator Repair',
                    'Washing Machine Repair', 'Microwave Repair', 'Water Purifier (RO) Service',
                    'Geyser Repair', 'Chimney Service', 'Dishwasher Repair', 'TV Repair', 'Fan Repair',
                    'Cooler Repair', 'Inverter Repair', 'Generator Repair',
                ],
            ],
            'Cleaning Services' => [
                'icon' => 'icon:cleaning_services_rounded',
                'children' => [
                    'Home Cleaning', 'Deep Cleaning', 'Bathroom Cleaning', 'Kitchen Cleaning',
                    'Sofa Cleaning', 'Carpet Cleaning', 'Mattress Cleaning', 'Water Tank Cleaning',
                    'Floor Cleaning', 'Glass Cleaning', 'Office Cleaning',
                ],
            ],
            'Interior & Renovation' => [
                'icon' => 'icon:chair_rounded',
                'children' => [
                    'Interior Designer', 'Modular Kitchen', 'False Ceiling', 'Flooring Work',
                    'Wallpaper Installation', 'Tile Installation', 'POP Work', 'Curtain Installation',
                    'Furniture Installation',
                ],
            ],
            'Outdoor Services' => [
                'icon' => 'icon:grass_rounded',
                'children' => ['Gardening', 'Lawn Maintenance', 'Tree Cutting', 'Plant Care', 'Landscape Design'],
            ],
            'Security & Safety' => [
                'icon' => 'icon:security_rounded',
                'children' => [
                    'CCTV Installation', 'CCTV Repair', 'Smart Lock Installation', 'Security Guard',
                    'Fire Safety Equipment', 'Video Door Phone Installation',
                ],
            ],
            'Smart Home Services' => [
                'icon' => 'icon:wifi_rounded',
                'children' => [
                    'Smart Light Installation', 'Home Automation', 'Wi-Fi Setup', 'Network Installation',
                    'Smart Door Lock Setup',
                ],
            ],
            'Water Services' => [
                'icon' => 'icon:water_drop_rounded',
                'children' => [
                    'Borewell Service', 'Water Tank Repair', 'Pipeline Repair', 'Motor Pump Repair',
                    'Water Leakage Detection',
                ],
            ],
        ];

        $extendedPath = database_path('data/home_service_catalog_extended.php');
        if (file_exists($extendedPath)) {
            $categories = array_merge($categories, require $extendedPath);
        } else {
            $categories = array_merge($categories, [
            'Construction Services' => [
                'icon' => 'icon:construction_rounded',
                'children' => [
                    'Home Construction', 'House Renovation', 'Civil Contractor', 'Building Repair',
                    'Roof Repair', 'Waterproofing',
                ],
            ],
            'Furniture Services' => [
                'icon' => 'icon:chair_alt_rounded',
                'children' => [
                    'Furniture Assembly', 'Furniture Shifting', 'Office Furniture Setup',
                    'Bed Installation', 'Wardrobe Installation',
                ],
            ],
            'Pest Control' => [
                'icon' => 'icon:pest_control_rounded',
                'children' => [
                    'Termite Control', 'Cockroach Control', 'Mosquito Control', 'Rodent Control',
                    'General Pest Control',
                ],
            ],
            'Shifting Services' => [
                'icon' => 'icon:local_shipping_rounded',
                'children' => [
                    'House Shifting', 'Office Shifting', 'Packers & Movers', 'Local Moving', 'Interstate Moving',
                ],
            ],
            'Personal Home Assistance' => [
                'icon' => 'icon:person_rounded',
                'children' => ['Maid Service', 'Cook', 'Babysitter', 'Elder Care', 'Patient Care', 'Driver on Demand'],
            ],
            'Pet Services' => [
                'icon' => 'icon:pets_rounded',
                'children' => ['Pet Grooming', 'Pet Walking', 'Pet Boarding', 'Veterinary Visit'],
            ],
            'Laundry & Textile' => [
                'icon' => 'icon:local_laundry_service_rounded',
                'children' => ['Laundry Pickup', 'Dry Cleaning', 'Ironing Service', 'Shoe Cleaning', 'Curtain Washing'],
            ],
            'Technology Services' => [
                'icon' => 'icon:computer_rounded',
                'children' => [
                    'Laptop Repair', 'Computer Repair', 'Printer Repair', 'CCTV Installation',
                    'Wi-Fi Installation', 'Smart Home Installation', 'TV Wall Mount Installation',
                ],
            ],
            'Personal Services' => [
                'icon' => 'icon:face_rounded',
                'children' => ['Barber & Saloon Service', 'Salon Spa & Others (Female)', 'Massage Therapist'],
            ],
            'Education Services' => [
                'icon' => 'icon:school_rounded',
                'children' => [
                    'Home Tutor' => [
                        'School Tuition (Class 1-12)',
                        'Mathematics & Science Tutor',
                        'Physics & Chemistry Tutor',
                        'CBSE / ICSE Board Tutor',
                        'JEE / NEET Competitive Exam Tutor',
                        'English & Foreign Language Tutor',
                        'Computer & Coding Tutor',
                    ],
                    'Music Teacher',
                    'Dance Teacher',
                    'Yoga Trainer',
                    'Gym Trainer',
                    'Language Tutor',
                ],
            ],
            'Healthcare Services' => [
                'icon' => 'icon:favorite_rounded',
                'children' => [
                    'Doctor Home Visit' => [
                        'General Physician Visit',
                        'Emergency Doctor Visit',
                        'Respiratory & Fever Care',
                        'Heart & BP Care',
                        'Diabetes Care',
                        'Pediatric (Child Doctor) Visit',
                        'Elderly Care Visit',
                        "Women's Health Visit",
                        'Minor Injury & Wound Care',
                        'ECG & Home Diagnostics',
                    ],
                    'Physiotherapy' => [
                        'Orthopedic Physiotherapy',
                        'Post-Surgery Rehabilitation',
                        'Neuro Physiotherapy',
                        'Cardio & Pulmonary Rehab',
                        'Pediatric & Geriatric Physiotherapy',
                        'Sports Injury Rehabilitation',
                        'Pain Management Therapy',
                        'Mobility & Exercise Therapy',
                    ],
                    'Lab Sample Collection' => [
                        'Complete Blood Count (CBC)',
                        'Blood Sugar & HbA1c',
                        'Thyroid Profile (T3, T4, TSH)',
                        'Lipid Profile (Heart Risk)',
                        'Liver Function Test (LFT)',
                        'Kidney Function Test (KFT)',
                        'Vitamin D & Vitamin B12 Test',
                        'Iron Profile & Ferritin',
                        'Routine Urine & Stool Examination',
                        'Fever Panel (COVID, Dengue, Malaria, Typhoid)',
                        'Cardiac Risk Markers & ECG at Home',
                        'Hormone Panel (Testosterone, Estrogen, Cortisol)',
                        'Pregnancy & Women\'s Health Tests (Beta hCG, PCOS)',
                        'Full Body Health Checkup Package',
                        'Senior Citizen Health Package',
                        'Diabetes Care Health Suite',
                        'Home Blood Sample Collection (Standard)',
                        'Express Home Blood Sample Collection (1 Hr)',
                    ],
                    'Lab Technician' => [
                        'Complete Blood Count (CBC)',
                        'Blood Sugar & HbA1c',
                        'Thyroid Profile (T3, T4, TSH)',
                        'Lipid Profile (Heart Risk)',
                        'Liver Function Test (LFT)',
                        'Kidney Function Test (KFT)',
                        'Vitamin D & Vitamin B12 Test',
                        'Iron Profile & Ferritin',
                        'Routine Urine & Stool Examination',
                        'Fever Panel (COVID, Dengue, Malaria, Typhoid)',
                        'Cardiac Risk Markers & ECG at Home',
                        'Hormone Panel (Testosterone, Estrogen, Cortisol)',
                        'Pregnancy & Women\'s Health Tests (Beta hCG, PCOS)',
                        'Full Body Health Checkup Package',
                        'Senior Citizen Health Package',
                        'Diabetes Care Health Suite',
                        'Home Blood Sample Collection (Standard)',
                        'Express Home Blood Sample Collection (1 Hr)',
                    ],
                    'Nursing Care' => [
                        'General Nurse Visit',
                        'Injection & IV Drip Care',
                        'Wound & Surgical Dressing',
                        'Vital Monitoring',
                        'Bedridden Patient Care',
                        'Catheter & Tube Care',
                        'Elderly Care Nursing',
                        'Post-Surgery Nursing Care',
                    ],
                    'Ambulance Booking',
                ],
            ],
            // Standalone quick tiles (with drill-down options where applicable)
            'Doctor Home Visit' => [
                'icon' => 'icon:medical_services_rounded',
                'children' => [
                    'General Physician Visit',
                    'Emergency Doctor Visit',
                    'Respiratory & Fever Care',
                    'Heart & BP Care',
                    'Diabetes Care',
                    'Pediatric (Child Doctor) Visit',
                    'Elderly Care Visit',
                    "Women's Health Visit",
                    'Minor Injury & Wound Care',
                    'ECG & Home Diagnostics',
                ],
            ],
            'Physiotherapy' => [
                'icon' => 'icon:accessibility_new_rounded',
                'children' => [
                    'Orthopedic Physiotherapy',
                    'Post-Surgery Rehabilitation',
                    'Neuro Physiotherapy',
                    'Cardio & Pulmonary Rehab',
                    'Pediatric & Geriatric Physiotherapy',
                    'Sports Injury Rehabilitation',
                    'Pain Management Therapy',
                    'Mobility & Exercise Therapy',
                ],
            ],
            'Lab Sample Collection' => [
                'icon' => 'icon:biotech_rounded',
                'children' => [
                    'Complete Blood Count (CBC)',
                    'Blood Sugar & HbA1c',
                    'Thyroid Profile (T3, T4, TSH)',
                    'Lipid Profile (Heart Risk)',
                    'Liver Function Test (LFT)',
                    'Kidney Function Test (KFT)',
                    'Vitamin D & Vitamin B12 Test',
                    'Iron Profile & Ferritin',
                    'Routine Urine & Stool Examination',
                    'Fever Panel (COVID, Dengue, Malaria, Typhoid)',
                    'Cardiac Risk Markers & ECG at Home',
                    'Hormone Panel (Testosterone, Estrogen, Cortisol)',
                    'Pregnancy & Women\'s Health Tests (Beta hCG, PCOS)',
                    'Full Body Health Checkup Package',
                    'Senior Citizen Health Package',
                    'Diabetes Care Health Suite',
                    'Home Blood Sample Collection (Standard)',
                    'Express Home Blood Sample Collection (1 Hr)',
                ],
            ],
            'Lab Technician' => [
                'icon' => 'icon:biotech_rounded',
                'children' => [
                    'Complete Blood Count (CBC)',
                    'Blood Sugar & HbA1c',
                    'Thyroid Profile (T3, T4, TSH)',
                    'Lipid Profile (Heart Risk)',
                    'Liver Function Test (LFT)',
                    'Kidney Function Test (KFT)',
                    'Vitamin D & Vitamin B12 Test',
                    'Iron Profile & Ferritin',
                    'Routine Urine & Stool Examination',
                    'Fever Panel (COVID, Dengue, Malaria, Typhoid)',
                    'Cardiac Risk Markers & ECG at Home',
                    'Hormone Panel (Testosterone, Estrogen, Cortisol)',
                    'Pregnancy & Women\'s Health Tests (Beta hCG, PCOS)',
                    'Full Body Health Checkup Package',
                    'Senior Citizen Health Package',
                    'Diabetes Care Health Suite',
                    'Home Blood Sample Collection (Standard)',
                    'Express Home Blood Sample Collection (1 Hr)',
                ],
            ],
            'Nursing Care' => [
                'icon' => 'icon:local_hospital_rounded',
                'children' => [
                    'General Nurse Visit',
                    'Injection & IV Drip Care',
                    'Wound & Surgical Dressing',
                    'Vital Monitoring',
                    'Bedridden Patient Care',
                    'Catheter & Tube Care',
                    'Elderly Care Nursing',
                    'Post-Surgery Nursing Care',
                ],
            ],
            'Ambulance Booking' => ['icon' => 'icon:emergency_rounded', 'children' => []],
            ]);
        }

        $parentIds = DB::table('tj_categorie_user')
            ->where('type', 'consumer_service')
            ->whereNull('parent_id')
            ->pluck('id');

        if ($parentIds->isNotEmpty()) {
            $this->deleteConsumerDescendants($parentIds->all());
            DB::table('tj_categorie_user')->whereIn('id', $parentIds)->delete();
        }
        DB::table('tj_categorie_user')->where('type', 'consumer_service')->delete();

        $now = date('Y-m-d H:i:s');

        foreach ($categories as $parentName => $meta) {
            $parentId = DB::table('tj_categorie_user')->insertGetId([
                'libelle' => $parentName,
                'parent_id' => null,
                'type' => 'consumer_service',
                'image' => $meta['icon'],
                'statut' => true,
                'creer' => $now,
                'modifier' => $now,
            ]);

            $this->insertChildren($meta['children'], $parentId, $now);
        }
    }

    private function deleteConsumerDescendants(array $parentIds): void
    {
        $childIds = DB::table('tj_categorie_user')->whereIn('parent_id', $parentIds)->pluck('id')->all();
        if (empty($childIds)) {
            return;
        }
        $this->deleteConsumerDescendants($childIds);
        DB::table('tj_categorie_user')->whereIn('id', $childIds)->delete();
    }

    private function insertChildren(array $children, int $parentId, string $now): void
    {
        foreach ($children as $key => $value) {
            if (is_int($key)) {
                $name = $value;
                $subChildren = [];
            } else {
                $name = $key;
                $subChildren = is_array($value) ? $value : [];
            }

            $childId = DB::table('tj_categorie_user')->insertGetId([
                'libelle' => $name,
                'parent_id' => $parentId,
                'type' => 'consumer_service',
                'image' => null,
                'statut' => true,
                'creer' => $now,
                'modifier' => $now,
            ]);

            if (!empty($subChildren)) {
                $this->insertChildren($subChildren, $childId, $now);
            }
        }
    }
}

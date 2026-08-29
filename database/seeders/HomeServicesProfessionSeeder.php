<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Ensures every Home Services profession from the PDF exists under 🧹 Home Services.
 */
class HomeServicesProfessionSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/home_services_professions.php');
        if (!is_file($path)) {
            return;
        }

        $groups = require $path;
        $parentId = DB::table('tj_categorie_user')
            ->where('libelle', '🧹 Home Services')
            ->whereNull('parent_id')
            ->value('id');

        if (!$parentId) {
            return;
        }

        $images = $this->defaultImages();
        $now = date('Y-m-d H:i:s');

        foreach ($groups as $group) {
            foreach ($group['professions'] as $profession) {
                $existing = DB::table('tj_categorie_user')
                    ->where('parent_id', $parentId)
                    ->where('libelle', $profession)
                    ->value('id');

                if ($existing) {
                    DB::table('tj_categorie_user')->where('id', $existing)->update([
                        'image' => $images[$profession] ?? $this->groupFallbackImage($group['group']),
                        'modifier' => $now,
                    ]);
                    continue;
                }

                DB::table('tj_categorie_user')->insert([
                    'libelle' => $profession,
                    'parent_id' => $parentId,
                    'image' => $images[$profession] ?? $this->groupFallbackImage($group['group']),
                    'creer' => $now,
                    'modifier' => $now,
                ]);
            }
        }
    }

    private function groupFallbackImage(string $group): string
    {
        $map = [
            'Repair & Maintenance' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/electrician_1783252378172_YnUB2plUW.svg',
            'AC & Appliances' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/ac_appliance_repair_1783252479347_sqCawy2gQ.svg',
            'Cleaning Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/cleaner_1783252376233_GeWRTB0K6.svg',
            'Interior & Renovation' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/interior_designer_1783252446712_gfNnDnIiS.svg',
            'Outdoor Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/nursery_plants_1783252452557_A_QjusYqa.svg',
            'Security & Safety' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/electronics_gadgets_1783252402768_YgztkvaII.svg',
            'Smart Home Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/electronics_gadgets_1783252402768_YgztkvaII.svg',
            'Water Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/plumber_1783252380238_OcZYoo-bd.svg',
            'Construction Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/contractor_1783252443187_cEAalAnn6.svg',
            'Furniture Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/hardware_building_1783252404610_-z5upApQI.svg',
            'Pest Control' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/pest_control_1783252386499_Z2tbcTdNd.svg',
            'Shifting Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/packers_movers_1783248995361_pVl-giuZ2.png',
            'Personal Home Assistance' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/cleaner_1783252376233_GeWRTB0K6.svg',
            'Pet Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/organic_produce_1783252454315_hb3qgr-1F.svg',
            'Laundry & Textile' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/clothing_apparel_1783252400924_JfyzOYrex.svg',
            'Technology Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/laptop_pc_repair_1783252477518_TSreUfoPS.svg',
            'Personal Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/salons_spas_1783252408067_4X6Rdsf1c.svg',
            'Education Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/tutor_1783252414646_w1Ba8CL7l.svg',
            'Healthcare Services' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/doctor_1783252344276_RO3yap1dB.svg',
            'Miscellaneous' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/online_seller_1783252329076_jxSeCfE7v.svg',
        ];

        return $map[$group] ?? 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/cleaner_1783252376233_GeWRTB0K6.svg';
    }

    private function defaultImages(): array
    {
        return [
            'Electrician' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/electrician_1783252378172_YnUB2plUW.svg',
            'Plumber' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/plumber_1783252380238_OcZYoo-bd.svg',
            'Carpenter' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/carpenter_1783252381962_2x2DAQa-X.svg',
            'Painter' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/painter_1783252383787_5lbjBdoW_.svg',
            'Pest Control' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/pest_control_1783252386499_Z2tbcTdNd.svg',
            'General Pest Control' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/pest_control_1783252386499_Z2tbcTdNd.svg',
            'Packers & Movers' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/packers_movers_1783248995361_pVl-giuZ2.png',
            'Home Tutor' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/tutor_1783252414646_w1Ba8CL7l.svg',
            'Doctor Home Visit' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/doctor_1783252344276_RO3yap1dB.svg',
            'Physiotherapist' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/personal_trainer_1783252468648_07eoWWYLj.svg',
            'Nurse' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/doctor_1783252344276_RO3yap1dB.svg',
            'Lab Technician' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/diagnostic_lab_1783252347533_ayrBI028D.svg',
            'Interior Designer' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/interior_designer_1783252446712_gfNnDnIiS.svg',
            'Barber & Saloon Service' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/salons_spas_1783252408067_4X6Rdsf1c.svg',
            'Salon Spa & Others (Female)' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/makeup_artist_1783252412683_tv4sEQZFU.svg',
            'Massage Therapist' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/yoga_instructor_1783252470442_1CifitCnO.svg',
            'Music Teacher' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/tutor_1783252414646_w1Ba8CL7l.svg',
            'Dance Teacher' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/tutor_1783252414646_w1Ba8CL7l.svg',
            'Yoga Trainer' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/yoga_instructor_1783252470442_1CifitCnO.svg',
            'Gym Trainer' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/personal_trainer_1783252468648_07eoWWYLj.svg',
            'Language Tutor' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/tutor_1783252414646_w1Ba8CL7l.svg',
            'Ambulance Booking' => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/ambulance_service_1783252349237_V-C_vo0XH.svg',
        ];
    }
}

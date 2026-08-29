<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 20 parent categories and their subcategories
        $categories = [
            '🚕 Transport & Mobility' => [
                'Cab Driver',
                'Bike Rider',
                'Auto Driver',
                'E-Rickshaw',
                'Pickup',
                'Fleet Owner',
                'Truck Owner',
            ],
            '📦 Delivery & Logistics' => [
                'Food Delivery',
                'Parcel Delivery',
                'Pickup & Drop (Personal runner)',
                'Logistics Partner',
                'Packers & Movers',
            ],
            '🍴 Food & Kitchen' => [
                'Home Kitchen',
                'Tiffin Service',
                'Restaurant',
                'Cafe',
                'Bakery',
                'Caterer',
            ],
            '🛒 Marketplace & Sellers' => [
                'Online Seller',
                'Retail Shop',
                'Wholesale Business',
                'Distributor',
                'Manufacturer',
                'Reseller',
            ],
            '🏥 Healthcare' => [
                'Medical Store',
                'Clinic',
                'Doctor',
                'Hospital',
                'Diagnostic Lab',
                'Ambulance Service',
            ],
            '🎓 Education' => [
                'Student',
                'School',
                'College',
                'Coaching Center',
                'Training Institute',
            ],
            '♻️ Green Exchange' => [
                'Scrap Collector',
                'Scrap Dealer',
                'Recycler',
                'Waste Management',
            ],
            '🏨 Travel & Hospitality' => [
                'Hotel',
                'Guest House',
                'Homestay',
                'Tour Guide',
                'Travel Agent',
            ],
            '🧹 Home Services' => [],
            '💼 Professional Services' => [
                'Lawyer',
                'CA / Accountant',
                'Consultant',
                'Architect',
                'Graphic Designer',
                'Developer / IT',
            ],
            '🏪 Merchants & Businesses' => [
                'Grocery Store',
                'Clothing & Apparel',
                'Electronics & Gadgets',
                'Hardware & Building',
                'Stationeries & Books',
                'Salons & Spas',
            ],
            '🧑‍💻 Freelancers & Self-Employed' => [
                'Photographer',
                'Makeup Artist',
                'Tutor',
                'Freelance Writer',
                'Event Planner',
            ],
            '🏭 Manufacturing & Wholesale' => [
                'Garment Factory',
                'Food Processing',
                'Packaging Material',
                'Chemical & Plastic',
                'Metal & Steel',
            ],
            '💳 Finance & Insurance' => [
                'Loan Agent',
                'Insurance Advisor',
                'Tax Consultant',
                'Investment Planner',
            ],
            '🌐 Digital & Online Services' => [
                'Digital Marketer',
                'SEO Specialist',
                'Content Creator',
                'E-learning Instructor',
            ],
            '🏗️ Construction & Real Estate' => [
                'Contractor',
                'Real Estate Agent',
                'Interior Designer',
                'Building Material Supplier',
            ],
            '🌾 Agriculture & Farming' => [
                'Farmer',
                'Nursery & Plants',
                'Organic Produce',
                'Dairy Farm',
                'Poultry Farm',
            ],
            '🎪 Events & Entertainment' => [
                'DJ & Sound',
                'Anchor / Host',
                'Stage Decorator',
                'Catering Service',
                'Event Security',
            ],
            '🏋️ Fitness & Wellness' => [
                'Personal Trainer',
                'Yoga Instructor',
                'Dietitian / Nutritionist',
                'Gym Owner',
            ],
            '🔧 Repair & Maintenance' => [
                'Mobile Repair',
                'Laptop / PC Repair',
                'AC & Appliance Repair',
                'Car / Bike Mechanic',
            ],
        ];

        $images = [
            // 🚕 Transport & Mobility
            'Cab Driver'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/cab_driver_1783249005054_POPCcx_rS.png',
            'Bike Rider'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/bike_rider_1783249014170_y9S6MIU-6.png',
            'Auto Driver'                     => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/auto_driver_1783249025380_UwdRqhmeh.png',
            'E-Rickshaw'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/e_rickshaw_1783249036861_bQ0GLsQCX.png',
            'Pickup'                          => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/pickup_1783249046817_ahalwPogm.png',
            'Fleet Owner'                     => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/fleet_owner_1783249066518_RPGlyIoDk.png',
            'Truck Owner'                     => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/truck_owner_1783249056776_8plB41LrU.png',
            // 📦 Delivery & Logistics
            'Food Delivery'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/food_delivery_1783248956277_4I15Fbkj6.png',
            'Parcel Delivery'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/parcel_delivery_1783248966496_RNsc20cCL.png',
            'Pickup & Drop'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/pickup_drop_runner_-u1aS4yT_.png',
            'Logistics Partner'               => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/logistics_partner_1783248985222_CII49P2RC.png',
            'Packers & Movers'                => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/packers_movers_1783248995361_pVl-giuZ2.png',
            // 🍴 Food & Kitchen
            'Home Kitchen'                    => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/home_kitchen_1783252307452_ue1MQN8Uw.png',
            'Tiffin Service'                  => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/tiffin_service_1783252315714_lC7KwVHAi.png',
            'Restaurant'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/restaurant_1783252318219_io0obrdvx.png',
            'Cafe'                            => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/cafe_1783252321903_mEyI5gCjA.png',
            'Bakery'                          => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/bakery_1783252324584__jtufGkDR.png',
            'Caterer'                         => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/caterer_1783252327324_pw819vi0x.svg',
            // 🛒 Marketplace & Sellers
            'Online Seller'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/online_seller_1783252329076_jxSeCfE7v.svg',
            'Retail Shop'                     => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/retail_shop_1783252330827_buGqbBNG3.svg',
            'Wholesale Business'              => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/wholesale_business_1783252332685_-wrTG9MH1.svg',
            'Distributor'                     => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/distributor_1783252334338_xBnv-E9Al.svg',
            'Manufacturer'                    => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/manufacturer_1783252336032_GzpTeTCh5.svg',
            'Reseller'                        => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/reseller_1783252338369_dcAaGHGoV.svg',
            // 🏥 Healthcare
            'Medical Store'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/medical_store_1783252340784_bqThJtIS3.svg',
            'Clinic'                          => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/clinic_1783252342494_mThqOOsq2.svg',
            'Doctor'                          => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/doctor_1783252344276_RO3yap1dB.svg',
            'Hospital'                        => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/hospital_1783252345877_RQcBYodrT.svg',
            'Diagnostic Lab'                  => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/diagnostic_lab_1783252347533_ayrBI028D.svg',
            'Ambulance Service'               => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/ambulance_service_1783252349237_V-C_vo0XH.svg',
            // 🎓 Education
            'Student'                         => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/student_1783252350982_Ay7exBpvs.svg',
            'School'                          => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/school_1783252352726_b6rQGCnFO.svg',
            'College'                         => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/college_1783252354523_8lLGPe71h.svg',
            'Coaching Center'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/coaching_center_1783252356274_kusGbCRPU.svg',
            'Training Institute'              => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/training_institute_1783252358034_fxXp3QcSw.svg',
            // ♻️ Green Exchange
            'Scrap Collector'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/scrap_collector_1783252359871_STudvfH-7.svg',
            'Scrap Dealer'                    => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/scrap_dealer_1783252361703_tgE4hTi4G.svg',
            'Recycler'                        => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/recycler_1783252363342_f6JxQRw5v.svg',
            'Waste Management'                => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/waste_management_1783252365082_LcSD-BbdJ.svg',
            // 🏨 Travel & Hospitality
            'Hotel'                           => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/hotel_1783252367131_TpnRtO5Ue.svg',
            'Guest House'                     => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/guest_house_1783252368954_dqAr3WlJa.svg',
            'Homestay'                        => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/homestay_1783252370612_9UEUVBHAS.svg',
            'Tour Guide'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/tour_guide_1783252372455_tQaF3kgl9.svg',
            'Travel Agent'                    => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/travel_agent_1783252374299_5lY3AlsMe.svg',
            // 🧹 Home Services
            'Cleaner'                         => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/cleaner_1783252376233_GeWRTB0K6.svg',
            'Electrician'                     => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/electrician_1783252378172_YnUB2plUW.svg',
            'Plumber'                         => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/plumber_1783252380238_OcZYoo-bd.svg',
            'Carpenter'                       => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/carpenter_1783252381962_2x2DAQa-X.svg',
            'Painter'                         => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/painter_1783252383787_5lbjBdoW_.svg',
            'Pest Control'                    => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/pest_control_1783252386499_Z2tbcTdNd.svg',
            'Mason (Raj Mistri)'              => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/carpenter_1783252381962_2x2DAQa-X.svg',
            'Welder'                          => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/carpenter_1783252381962_2x2DAQa-X.svg',
            'Handyman'                        => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/electrician_1783252378172_YnUB2plUW.svg',
            'AC & Appliance Technician'       => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/ac_appliance_repair_1783252479347_sqCawy2gQ.svg',
            'Interior Designer'               => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/interior_designer_1783252446712_gfNnDnIiS.svg',
            'Gardening & Outdoor'             => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/nursery_plants_1783252452557_A_QjusYqa.svg',
            'CCTV & Security'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/electronics_gadgets_1783252402768_YgztkvaII.svg',
            'Smart Home Technician'           => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/electronics_gadgets_1783252402768_YgztkvaII.svg',
            'Water Tank & Plumbing'           => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/plumber_1783252380238_OcZYoo-bd.svg',
            'Construction Worker'             => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/contractor_1783252443187_cEAalAnn6.svg',
            'Furniture Services'              => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/hardware_building_1783252404610_-z5upApQI.svg',
            'Packers & Movers'                => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/packers_movers_1783248995361_pVl-giuZ2.png',
            'Maid / Cook / Babysitter'        => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/cleaner_1783252376233_GeWRTB0K6.svg',
            'Pet Care'                        => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/organic_produce_1783252454315_hb3qgr-1F.svg',
            'Laundry Service'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/clothing_apparel_1783252400924_JfyzOYrex.svg',
            'Computer & IT Repair'            => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/laptop_pc_repair_1783252477518_TSreUfoPS.svg',
            'Barber & Salon'                  => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/salons_spas_1783252408067_4X6Rdsf1c.svg',
            'Salon & Spa'                     => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/makeup_artist_1783252412683_tv4sEQZFU.svg',
            'Massage Therapist'               => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/yoga_instructor_1783252470442_1CifitCnO.svg',
            'Home Tutor'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/tutor_1783252414646_w1Ba8CL7l.svg',
            'Nurse'                           => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/doctor_1783252344276_RO3yap1dB.svg',
            'Doctor Home Visit'               => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/doctor_1783252344276_RO3yap1dB.svg',
            'Physiotherapist'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/personal_trainer_1783252468648_07eoWWYLj.svg',
            'Lab Technician'                  => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/diagnostic_lab_1783252347533_ayrBI028D.svg',
            'Miscellaneous Services'          => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/online_seller_1783252329076_jxSeCfE7v.svg',
            // 💼 Professional Services
            'Lawyer'                          => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/lawyer_1783252388438_b0PFtyoCt.svg',
            'CA / Accountant'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/ca_accountant_1783252390171_F5S81fZOv.svg',
            'Consultant'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/consultant_1783252391885_12x5S7Ku4.svg',
            'Architect'                       => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/architect_1783252393701_ucrHInuQm.svg',
            'Graphic Designer'                => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/graphic_designer_1783252395556_4zEQIwPxs.svg',
            'Developer / IT'                  => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/developer_it_1783252397340_1tpYJDpsP.svg',
            // 🏪 Merchants & Businesses
            'Grocery Store'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/grocery_store_1783252399080_bfvcGWV1a.svg',
            'Clothing & Apparel'              => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/clothing_apparel_1783252400924_JfyzOYrex.svg',
            'Electronics & Gadgets'           => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/electronics_gadgets_1783252402768_YgztkvaII.svg',
            'Hardware & Building'             => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/hardware_building_1783252404610_-z5upApQI.svg',
            'Stationeries & Books'            => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/stationeries_books_1783252406363_xmZSNHEXC.svg',
            'Salons & Spas'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/salons_spas_1783252408067_4X6Rdsf1c.svg',
            // 🧑‍💻 Freelancers & Self-Employed
            'Photographer'                    => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/photographer_1783252411061_yex7UXaCM.svg',
            'Makeup Artist'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/makeup_artist_1783252412683_tv4sEQZFU.svg',
            'Tutor'                           => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/tutor_1783252414646_w1Ba8CL7l.svg',
            'Freelance Writer'                => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/freelance_writer_1783252416488_X-XqexPB_.svg',
            'Event Planner'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/event_planner_1783252418331_maPo4Uf8i.svg',
            // 🏭 Manufacturing & Wholesale
            'Garment Factory'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/garment_factory_1783252420421_54lnENl4y.svg',
            'Food Processing'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/food_processing_1783252422157_q8EkiUIBN.svg',
            'Packaging Material'              => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/packaging_material_1783252423862_hAJzuKK9p.svg',
            'Chemical & Plastic'              => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/chemical_plastic_1783252425502_jt41toDVp.svg',
            'Metal & Steel'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/metal_steel_1783252427261_VprXTuJ8o.svg',
            // 💳 Finance & Insurance
            'Loan Agent'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/loan_agent_1783252428982_8HliLZgFz.svg',
            'Insurance Advisor'               => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/insurance_advisor_1783252430632_8OTATLCt7.svg',
            'Tax Consultant'                  => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/tax_consultant_1783252432484_Foe5XFnsR.svg',
            'Investment Planner'              => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/investment_planner_1783252434133_Q1S5xDNCI.svg',
            // 🌐 Digital & Online Services
            'Digital Marketer'                => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/digital_marketer_1783252435946_SOgHYjOyB.svg',
            'SEO Specialist'                  => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/seo_specialist_1783252437790_suDLk6RS_x.svg',
            'Content Creator'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/content_creator_1783252439587_SSY4unMat.svg',
            'E-learning Instructor'           => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/e_learning_instructor_1783252441397_G41Tp56zo.svg',
            // 🏗️ Construction & Real Estate
            'Contractor'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/contractor_1783252443187_cEAalAnn6.svg',
            'Real Estate Agent'               => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/real_estate_agent_1783252444961_UfInXbAQK.svg',
            'Interior Designer'               => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/interior_designer_1783252446712_gfNnDnIiS.svg',
            'Building Material Supplier'      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/building_material_supplier_1783252448445__ZJUdZFpF.svg',
            // 🌾 Agriculture & Farming
            'Farmer'                          => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/farmer_1783252450809_vwczJFDC1.svg',
            'Nursery & Plants'                => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/nursery_plants_1783252452557_A_QjusYqa.svg',
            'Organic Produce'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/organic_produce_1783252454315_hb3qgr-1F.svg',
            'Dairy Farm'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/dairy_farm_1783252456029_4vwantDpd.svg',
            'Poultry Farm'                    => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/poultry_farm_1783252458012_WyvWLFXs-.svg',
            // 🎪 Events & Entertainment
            'DJ & Sound'                      => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/dj_sound_1783252459766_nGXDv8R90.svg',
            'Anchor / Host'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/anchor_host_1783252461742_uyzeksVJ9.svg',
            'Stage Decorator'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/stage_decorator_1783252463379_8BtZ5WHHm.svg',
            'Catering Service'                => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/catering_service_1783252465221_J6ffcuUw8.svg',
            'Event Security'                  => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/event_security_1783252466895_LOVe8XKma.svg',
            // 🏋️ Fitness & Wellness
            'Personal Trainer'                => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/personal_trainer_1783252468648_07eoWWYLj.svg',
            'Yoga Instructor'                 => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/yoga_instructor_1783252470442_1CifitCnO.svg',
            'Dietitian / Nutritionist'        => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/dietitian_nutritionist_1783252472250_2e0oaChlI.svg',
            'Gym Owner'                       => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/gym_owner_1783252474047_VRW5opifx.svg',
            // 🔧 Repair & Maintenance
            'Mobile Repair'                   => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/mobile_repair_1783252475823_Cy2R6EBnH.svg',
            'Laptop / PC Repair'              => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/laptop_pc_repair_1783252477518_TSreUfoPS.svg',
            'AC & Appliance Repair'           => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/ac_appliance_repair_1783252479347_sqCawy2gQ.svg',
            'Car / Bike Mechanic'             => 'https://ik.imagekit.io/77z5w3wmv/fiinway_categories/car_bike_mechanic_1783252481176_vyuboqg0l.svg',
        ];

        foreach ($categories as $parentName => $subCategories) {
            $parentId = DB::table('tj_categorie_user')->where('libelle', $parentName)->whereNull('parent_id')->value('id');
            if (!$parentId) {
                $parentId = DB::table('tj_categorie_user')->insertGetId([
                    'libelle' => $parentName,
                    'parent_id' => null,
                    'creer' => date('Y-m-d H:i:s'),
                    'modifier' => date('Y-m-d H:i:s'),
                    'image' => null,
                ]);
            }

            foreach ($subCategories as $subName) {
                $subId = DB::table('tj_categorie_user')->where('libelle', $subName)->where('parent_id', $parentId)->value('id');
                if (!$subId) {
                    DB::table('tj_categorie_user')->insert([
                        'libelle' => $subName,
                        'parent_id' => $parentId,
                        'creer' => date('Y-m-d H:i:s'),
                        'modifier' => date('Y-m-d H:i:s'),
                        'image' => $images[$subName] ?? null,
                    ]);
                } else {
                    DB::table('tj_categorie_user')->where('id', $subId)->update([
                        'image' => $images[$subName] ?? null,
                        'modifier' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $this->call(HomeServicesProfessionSeeder::class);
    }
}

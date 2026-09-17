<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_kits')) {
            Schema::table('driver_kits', function (Blueprint $table) {
                if (!Schema::hasColumn('driver_kits', 'sku')) {
                    $table->string('sku', 60)->nullable()->after('category_code');
                }
                if (!Schema::hasColumn('driver_kits', 'mrp')) {
                    $table->decimal('mrp', 10, 2)->default(999.00)->after('price');
                }
                if (!Schema::hasColumn('driver_kits', 'cashback_amount')) {
                    $table->decimal('cashback_amount', 10, 2)->default(0.00)->after('mrp');
                }
                if (!Schema::hasColumn('driver_kits', 'stock_quantity')) {
                    $table->integer('stock_quantity')->default(500)->after('cashback_amount');
                }
                if (!Schema::hasColumn('driver_kits', 'booking_required')) {
                    $table->boolean('booking_required')->default(true)->after('is_compulsory');
                }
                if (!Schema::hasColumn('driver_kits', 'sizes')) {
                    $table->json('sizes')->nullable()->after('items_included');
                }
                if (!Schema::hasColumn('driver_kits', 'colors')) {
                    $table->json('colors')->nullable()->after('sizes');
                }
                if (!Schema::hasColumn('driver_kits', 'images')) {
                    $table->json('images')->nullable()->after('image');
                }
                if (!Schema::hasColumn('driver_kits', 'reorder_days_limit')) {
                    $table->integer('reorder_days_limit')->default(90)->after('booking_required');
                }
                if (!Schema::hasColumn('driver_kits', 'return_window_days')) {
                    $table->integer('return_window_days')->default(7)->after('reorder_days_limit');
                }
                if (!Schema::hasColumn('driver_kits', 'display_order')) {
                    $table->integer('display_order')->default(0)->after('return_window_days');
                }
                if (!Schema::hasColumn('driver_kits', 'category_id')) {
                    $table->unsignedBigInteger('category_id')->nullable()->after('category_code');
                }
            });

            // Seed / update comprehensive category marketing kits
            $kits = [
                [
                    'category_code' => 'bike',
                    'sku' => 'FW-KIT-BIKE-01',
                    'title' => 'Bike Partner Welcome Kit',
                    'description' => 'Official Fiinway safety & marketing kit for 2-wheeler bike taxi and parcel delivery partners. Mandatory for verified bike partners.',
                    'price' => 1499.00,
                    'mrp' => 2499.00,
                    'cashback_amount' => 150.00,
                    'stock_quantity' => 1000,
                    'is_compulsory' => true,
                    'booking_required' => true,
                    'image' => 'assets/images/kits/bike_kit.png',
                    'items_included' => json_encode([
                        'Fiinway T-Shirt (Sizes S to XXL)',
                        'Fiinway Branded Safety Helmet (ISI Certified)',
                        'Fiinway Bike Delivery Bag',
                        'Fiinway Reflective Safety Jacket',
                        'Mobile Holder for Bike',
                        'Partner ID Card + Lanyard',
                        'Bike Tank & Side Panel Stickers',
                        'Emergency Contact Sticker'
                    ]),
                    'sizes' => json_encode(['S', 'M', 'L', 'XL', 'XXL']),
                    'display_order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'car',
                    'sku' => 'FW-KIT-CAB-01',
                    'title' => 'Cab & Taxi Partner Kit',
                    'description' => 'Complete vehicle branding and professional driver uniform package for 4-wheeler cab drivers.',
                    'price' => 999.00,
                    'mrp' => 1999.00,
                    'cashback_amount' => 100.00,
                    'stock_quantity' => 800,
                    'is_compulsory' => true,
                    'booking_required' => true,
                    'image' => 'assets/images/kits/car_kit.png',
                    'items_included' => json_encode([
                        'Fiinway T-Shirt (Sizes S to XXL)',
                        'Fiinway Branded Cap',
                        'Partner ID Card + Lanyard',
                        'Car Seat Headrest Covers (Set of 2)',
                        'Fiinway Dashboard Fragrance / Air Freshener',
                        'Fiinway Rear Windshield Sticker',
                        'Fiinway Door Decal Stickers (Left & Right)',
                        'In-Car Feedback QR Code Danglers',
                        'Fiinway Umbrella'
                    ]),
                    'sizes' => json_encode(['S', 'M', 'L', 'XL', 'XXL']),
                    'display_order' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'auto',
                    'sku' => 'FW-KIT-AUTO-01',
                    'title' => 'Auto & E-Rickshaw Partner Kit',
                    'description' => 'Official branding kit for 3-wheeler auto rickshaw and e-rickshaw drivers.',
                    'price' => 799.00,
                    'mrp' => 1599.00,
                    'cashback_amount' => 80.00,
                    'stock_quantity' => 900,
                    'is_compulsory' => false,
                    'booking_required' => false,
                    'image' => 'assets/images/kits/auto_kit.png',
                    'items_included' => json_encode([
                        'Fiinway T-Shirt (S to XXL)',
                        'Fiinway Cap',
                        'Partner ID Card + Lanyard',
                        'Auto Hood / Back Banner',
                        'Inside QR Code Dangler',
                        'Side Strips (Fiinway Branding)',
                        'Mobile Charger Mount'
                    ]),
                    'sizes' => json_encode(['S', 'M', 'L', 'XL', 'XXL']),
                    'display_order' => 3,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'male_salon',
                    'sku' => 'FW-KIT-MSALON-01',
                    'title' => 'Male Salon Partner Kit',
                    'description' => 'Haircut, styling, grooming and beard care official kit for professional barbers and stylists.',
                    'price' => 899.00,
                    'mrp' => 1799.00,
                    'cashback_amount' => 100.00,
                    'stock_quantity' => 500,
                    'is_compulsory' => true,
                    'booking_required' => true,
                    'image' => 'assets/images/kits/male_salon_kit.png',
                    'items_included' => json_encode([
                        'Fiinway T-Shirt (1)',
                        'Apron (1)',
                        'Cap (1)',
                        'ID Card + Lanyard (1)',
                        'Scissor & Comb Set (1)',
                        'Towel (1)',
                        'Mirror/Shop Sticker (1 Set)',
                        'Service Menu Card (1)',
                        'QR Code Stand (1)',
                        'Counter Standee (1)'
                    ]),
                    'sizes' => json_encode(['S', 'M', 'L', 'XL', 'XXL']),
                    'display_order' => 4,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'female_salon',
                    'sku' => 'FW-KIT-FSALON-01',
                    'title' => 'Female Salon Partner Kit',
                    'description' => 'Hair, skin, makeup, nails, and beauty specialist complete kit with official branding.',
                    'price' => 899.00,
                    'mrp' => 1799.00,
                    'cashback_amount' => 100.00,
                    'stock_quantity' => 500,
                    'is_compulsory' => true,
                    'booking_required' => true,
                    'image' => 'assets/images/kits/female_salon_kit.png',
                    'items_included' => json_encode([
                        'Fiinway T-Shirt (1)',
                        'Apron (1)',
                        'Cap (1)',
                        'ID Card + Lanyard (1)',
                        'Makeup Brush Set (1)',
                        'Towel (1)',
                        'Mirror/Shop Sticker (1 Set)',
                        'Service Menu Card (1)',
                        'QR Code Stand (1)',
                        'Counter Standee (1)'
                    ]),
                    'sizes' => json_encode(['S', 'M', 'L', 'XL', 'XXL']),
                    'display_order' => 5,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'cook_made',
                    'sku' => 'FW-KIT-COOK-01',
                    'title' => 'Cook / Made Partner Kit',
                    'description' => 'Home cook, tiffin, and cloud kitchen starter pack with hygiene apparel and takeaway packaging.',
                    'price' => 799.00,
                    'mrp' => 1599.00,
                    'cashback_amount' => 80.00,
                    'stock_quantity' => 500,
                    'is_compulsory' => true,
                    'booking_required' => true,
                    'image' => 'assets/images/kits/cook_kit.png',
                    'items_included' => json_encode([
                        'Fiinway T-Shirt (1)',
                        'Apron (1)',
                        'Chef Cap (1)',
                        'Food Packaging Containers (1 Set)',
                        'Tiffin / Delivery Bag (1)',
                        'ID Card + Lanyard (1)',
                        'Menu Card (1)',
                        'Food Packaging Stickers (1 Set)',
                        'QR Code Stand (1)',
                        'Counter Standee (1)'
                    ]),
                    'sizes' => json_encode(['S', 'M', 'L', 'XL', 'XXL']),
                    'display_order' => 6,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'tutor',
                    'sku' => 'FW-KIT-TUTOR-01',
                    'title' => 'Tutor Partner Kit',
                    'description' => 'Home tuition, online educator and academic coach kit with professional branding.',
                    'price' => 699.00,
                    'mrp' => 1399.00,
                    'cashback_amount' => 70.00,
                    'stock_quantity' => 400,
                    'is_compulsory' => false,
                    'booking_required' => false,
                    'image' => 'assets/images/kits/tutor_kit.png',
                    'items_included' => json_encode([
                        'Fiinway T-Shirt (1)',
                        'Cap (1)',
                        'ID Card + Lanyard (1)',
                        'Notebook (1)',
                        'Pen Set (1)',
                        'Subject Tag Stickers (1 Set)',
                        'Service Menu / Profile Card (1)',
                        'QR Code Stand (1)',
                        'Counter Standee (1)',
                        'Laptop Bag (Optional)'
                    ]),
                    'sizes' => json_encode(['S', 'M', 'L', 'XL', 'XXL']),
                    'display_order' => 7,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'cleaner',
                    'sku' => 'FW-KIT-CLEAN-01',
                    'title' => 'House & Deep Cleaning Partner Kit',
                    'description' => 'Housekeeping uniform, color-coded microfiber cloths and service progress signs for home cleaning specialists.',
                    'price' => 899.00,
                    'mrp' => 1699.00,
                    'cashback_amount' => 90.00,
                    'stock_quantity' => 600,
                    'is_compulsory' => true,
                    'booking_required' => true,
                    'image' => 'assets/images/kits/cleaner_kit.png',
                    'items_included' => json_encode([
                        'Fiinway Housekeeping Apron / T-Shirt',
                        'Microfiber Cleaning Cloths (Set of 4, Color Coded)',
                        'Heavy-Duty Gloves',
                        'ID Card + Lanyard',
                        'Carry Caddy / Cleaning Basket',
                        '"Service in Progress" Door Sign',
                        'QR Code Stand'
                    ]),
                    'sizes' => json_encode(['S', 'M', 'L', 'XL', 'XXL']),
                    'display_order' => 8,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'technician',
                    'sku' => 'FW-KIT-TECH-01',
                    'title' => 'Technician & Repair Partner Kit',
                    'description' => 'Appliance repair, AC technician, electrician, and plumber heavy-duty tool kit and uniform.',
                    'price' => 999.00,
                    'mrp' => 1999.00,
                    'cashback_amount' => 100.00,
                    'stock_quantity' => 700,
                    'is_compulsory' => true,
                    'booking_required' => true,
                    'image' => 'assets/images/kits/tech_kit.png',
                    'items_included' => json_encode([
                        'Heavy-Duty Tool Bag (Fiinway Branded)',
                        'Fiinway Work T-Shirt / Dungaree',
                        'Safety Gloves & Goggles',
                        'ID Card + Lanyard',
                        'Fiinway Measuring Tape (5M)',
                        'Service Checklist Notepad',
                        'Doorstep Visit Boot Covers',
                        'QR Code Stand'
                    ]),
                    'sizes' => json_encode(['S', 'M', 'L', 'XL', 'XXL']),
                    'display_order' => 9,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'home_service',
                    'sku' => 'FW-KIT-HSVC-01',
                    'title' => 'Home Service Specialist Pro Kit',
                    'description' => 'Comprehensive gear and branding for multi-service verified home service technicians.',
                    'price' => 999.00,
                    'mrp' => 1999.00,
                    'cashback_amount' => 100.00,
                    'stock_quantity' => 500,
                    'is_compulsory' => true,
                    'booking_required' => true,
                    'image' => 'assets/images/kits/service_kit.png',
                    'items_included' => json_encode([
                        'Fiinway Branded T-Shirt',
                        'Certified Safety Helmet',
                        'Service Partner ID Badge',
                        'Heavy-Duty Tool Bag Organizer',
                        'Floor Service Mat'
                    ]),
                    'sizes' => json_encode(['S', 'M', 'L', 'XL', 'XXL']),
                    'display_order' => 10,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($kits as $kitData) {
                $categoryCode = $kitData['category_code'];
                $existing = DB::table('driver_kits')->where('category_code', $categoryCode)->first();
                if ($existing) {
                    DB::table('driver_kits')->where('id', $existing->id)->update([
                        'sku' => $kitData['sku'],
                        'title' => $kitData['title'],
                        'description' => $kitData['description'],
                        'price' => $kitData['price'],
                        'mrp' => $kitData['mrp'],
                        'cashback_amount' => $kitData['cashback_amount'],
                        'stock_quantity' => $kitData['stock_quantity'],
                        'is_compulsory' => $kitData['is_compulsory'],
                        'booking_required' => $kitData['booking_required'],
                        'items_included' => $kitData['items_included'],
                        'sizes' => $kitData['sizes'],
                        'display_order' => $kitData['display_order'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('driver_kits')->insert($kitData);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('driver_kits')) {
            Schema::table('driver_kits', function (Blueprint $table) {
                $table->dropColumn([
                    'sku', 'mrp', 'cashback_amount', 'stock_quantity',
                    'booking_required', 'sizes', 'colors', 'images',
                    'reorder_days_limit', 'return_window_days', 'display_order',
                    'category_id'
                ]);
            });
        }
    }
};

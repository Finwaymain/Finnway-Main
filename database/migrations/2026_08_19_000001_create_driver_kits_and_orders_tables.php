<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('driver_kits')) {
            Schema::create('driver_kits', function (Blueprint $table) {
                $table->id();
                $table->string('category_code', 50)->unique(); // 'bike', 'auto', 'car', 'home_service', 'all'
                $table->string('title', 150);
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(499.00);
                $table->string('image')->nullable();
                $table->json('items_included')->nullable(); // e.g. ["Fiinway Branded T-Shirt", "Certified Safety Helmet", "Partner ID Card"]
                $table->boolean('is_compulsory')->default(false); // Category-specific compulsory flag
                $table->boolean('is_active')->default(true);
                $table->string('checkout_url')->nullable();
                $table->timestamps();
            });

            // Seed default category-specific kits
            DB::table('driver_kits')->insert([
                [
                    'category_code' => 'bike',
                    'title' => 'Two-Wheeler Partner Welcome Kit',
                    'description' => 'Official Fiinway onboarding kit for 2-wheeler bike taxi and parcel delivery partners. Safety helmet and branded T-shirt are required.',
                    'price' => 599.00,
                    'image' => 'assets/images/kits/bike_kit.png',
                    'items_included' => json_encode(['Fiinway Branded T-Shirt', 'Certified Safety Helmet', 'Partner ID Card & Lanyard', 'Vehicle Safety Decal']),
                    'is_compulsory' => true, // Bike requires helmet & t-shirt by policy
                    'is_active' => true,
                    'checkout_url' => '/onboarding/kit-purchase',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'auto',
                    'title' => 'Auto Rickshaw Partner Kit',
                    'description' => 'Official onboarding kit for Auto Rickshaw partners. Includes branded driver apparel and official vehicle markings.',
                    'price' => 399.00,
                    'image' => 'assets/images/kits/auto_kit.png',
                    'items_included' => json_encode(['Fiinway Branded T-Shirt', 'Partner ID Card & Lanyard', 'Official Auto Decal Sticker']),
                    'is_compulsory' => false,
                    'is_active' => true,
                    'checkout_url' => '/onboarding/kit-purchase',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'car',
                    'title' => 'Cab & Four-Wheeler Driver Kit',
                    'description' => 'Professional starter kit for Car & Cab drivers. Includes premium driver uniform and car branding pack.',
                    'price' => 399.00,
                    'image' => 'assets/images/kits/car_kit.png',
                    'items_included' => json_encode(['Fiinway Branded T-Shirt', 'Partner ID Card & Lanyard', 'Car Windshield Tag']),
                    'is_compulsory' => false,
                    'is_active' => true,
                    'checkout_url' => '/onboarding/kit-purchase',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'home_service',
                    'title' => 'Home Service Specialist Pro Kit',
                    'description' => 'Professional kit for home repair, cleaning, electrical, and plumbing service partners. Protective gear and branded uniform included.',
                    'price' => 699.00,
                    'image' => 'assets/images/kits/service_kit.png',
                    'items_included' => json_encode(['Fiinway Branded T-Shirt', 'Certified Safety Helmet', 'Service Partner ID Badge', 'Tool Bag Organizer']),
                    'is_compulsory' => true,
                    'is_active' => true,
                    'checkout_url' => '/onboarding/kit-purchase',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'category_code' => 'all',
                    'title' => 'Partner Starter Kit',
                    'description' => 'Official Fiinway apparel, ID badge, and safety gear package for verified service partners.',
                    'price' => 499.00,
                    'image' => 'assets/images/kits/default_kit.png',
                    'items_included' => json_encode(['Fiinway Branded T-Shirt', 'Partner ID Card & Lanyard']),
                    'is_compulsory' => false,
                    'is_active' => true,
                    'checkout_url' => '/onboarding/kit-purchase',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if (!Schema::hasTable('driver_kit_orders')) {
            Schema::create('driver_kit_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id')->index();
                $table->unsignedBigInteger('kit_id')->nullable()->index();
                $table->string('order_number', 50)->unique();
                $table->string('category_code', 50)->default('bike');
                $table->string('kit_title', 150);
                $table->decimal('amount', 10, 2);
                $table->string('tshirt_size', 20)->nullable(); // 'S', 'M', 'L', 'XL', 'XXL'
                $table->string('receiver_name', 150)->nullable();
                $table->string('receiver_phone', 30)->nullable();
                $table->text('shipping_address')->nullable();
                $table->string('payment_method', 50)->default('online');
                $table->string('payment_status', 30)->default('paid'); // 'pending', 'paid', 'failed'
                $table->string('delivery_status', 30)->default('processing'); // 'processing', 'dispatched', 'delivered'
                $table->string('tracking_number', 100)->nullable();
                $table->string('courier_partner', 100)->nullable();
                $table->string('transaction_id', 100)->nullable();
                $table->timestamp('purchased_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('driver_kit_orders');
        Schema::dropIfExists('driver_kits');
    }
};

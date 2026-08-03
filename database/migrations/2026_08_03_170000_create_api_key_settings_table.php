<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('api_key_settings')) {
            Schema::create('api_key_settings', function (Blueprint $table) {
                $table->id();
                $table->string('group', 50); // maps, payment, whatsapp, sms, push
                $table->string('provider', 50); // google_maps, razorpay, stripe, whatsapp_biz, twilio, fcm
                $table->string('key_name', 100); // e.g. google_maps_key, razorpay_key_id
                $table->text('key_value')->nullable();
                $table->text('secret_value')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_sandbox')->default(false);
                $table->json('additional_params')->nullable();
                $table->timestamps();
            });

            // Seed default entries
            DB::table('api_key_settings')->insert([
                [
                    'group' => 'maps',
                    'provider' => 'google_maps',
                    'key_name' => 'google_maps_api_key',
                    'key_value' => 'AIzaSy_demo_google_maps_key',
                    'secret_value' => null,
                    'is_active' => true,
                    'is_sandbox' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'group' => 'payment',
                    'provider' => 'razorpay',
                    'key_name' => 'razorpay_key_id',
                    'key_value' => 'rzp_test_demo_key_id',
                    'secret_value' => 'rzp_test_demo_secret',
                    'is_active' => true,
                    'is_sandbox' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'group' => 'payment',
                    'provider' => 'stripe',
                    'key_name' => 'stripe_publishable_key',
                    'key_value' => 'pk_test_demo_stripe_key',
                    'secret_value' => 'sk_test_demo_stripe_secret',
                    'is_active' => true,
                    'is_sandbox' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'group' => 'whatsapp',
                    'provider' => 'whatsapp_biz',
                    'key_name' => 'whatsapp_access_token',
                    'key_value' => 'EAAG_demo_whatsapp_access_token',
                    'secret_value' => 'phone_number_id_1234567890',
                    'is_active' => true,
                    'is_sandbox' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'group' => 'push',
                    'provider' => 'fcm',
                    'key_name' => 'fcm_server_key',
                    'key_value' => 'AAAA_demo_fcm_server_key',
                    'secret_value' => null,
                    'is_active' => true,
                    'is_sandbox' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('api_key_settings');
    }
};

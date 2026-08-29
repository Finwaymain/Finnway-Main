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
        // 1. Quick Questions (Dynamic FAQ / Prompts managed from Admin)
        if (!Schema::hasTable('support_quick_questions')) {
            Schema::create('support_quick_questions', function (Blueprint $table) {
                $table->id();
                $table->string('user_type', 20)->default('customer'); // 'customer', 'business', 'all'
                $table->string('category', 100)->nullable(); // e.g. 'Rides', 'Payouts', 'KYC', etc.
                $table->text('question');
                $table->text('auto_reply')->nullable();
                $table->integer('sort_order')->default(0);
                $table->string('status', 20)->default('active'); // 'active', 'inactive'
                $table->timestamps();
            });

            // Seed default questions
            $defaultQuestions = [
                // Customer questions
                [
                    'user_type' => 'customer',
                    'category' => 'Ride & Taxi',
                    'question' => 'I have an issue with my recent ride.',
                    'auto_reply' => 'We are sorry to hear that! Please share your Ride ID and details of what happened so our support team can investigate immediately.',
                    'sort_order' => 1,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_type' => 'customer',
                    'category' => 'Payment & Wallet',
                    'question' => 'Amount was deducted but wallet balance was not credited.',
                    'auto_reply' => 'Please provide your transaction reference or screenshot. Our finance team verifies failed banking transactions within 15 minutes.',
                    'sort_order' => 2,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_type' => 'customer',
                    'category' => 'Home Services',
                    'question' => 'Serviceman did not arrive on scheduled time.',
                    'auto_reply' => 'Please mention your Service Booking ID. We will contact the service provider and update you right away.',
                    'sort_order' => 3,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_type' => 'customer',
                    'category' => 'Parcel Delivery',
                    'question' => 'Need status update on my parcel delivery.',
                    'auto_reply' => 'Please share your parcel tracking number or booking ID to check realtime location.',
                    'sort_order' => 4,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_type' => 'customer',
                    'category' => 'Rewards & Cashback',
                    'question' => 'How does the Medical Cashback & Referral program work?',
                    'auto_reply' => 'You earn instant cashback on verified medical prescriptions and referral commissions whenever your invited friends ride or book services.',
                    'sort_order' => 5,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Driver / Business questions
                [
                    'user_type' => 'business',
                    'category' => 'Payouts & Earnings',
                    'question' => 'My wallet withdrawal or payout is delayed.',
                    'auto_reply' => 'Standard bank settlement takes 24 hours. Please share your withdrawal request ID so we can expedite processing.',
                    'sort_order' => 1,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_type' => 'business',
                    'category' => 'Ride & Trip',
                    'question' => 'Customer cancelled or fare was incorrectly calculated.',
                    'auto_reply' => 'Please provide the Ride ID. Our operations team will review the GPS route and adjust your fare if eligible.',
                    'sort_order' => 2,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_type' => 'business',
                    'category' => 'Document & KYC',
                    'question' => 'When will my driving license / vehicle document be verified?',
                    'auto_reply' => 'Document verification is typically completed within 2 to 4 business hours. If it has been longer, please message us here.',
                    'sort_order' => 3,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_type' => 'business',
                    'category' => 'Service Orders',
                    'question' => 'Issue with customer location / order assignment.',
                    'auto_reply' => 'Please provide the order/booking number and describe the problem you encountered.',
                    'sort_order' => 4,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('support_quick_questions')->insert($defaultQuestions);
        }

        // 2. Support Tickets (One active chat conversation thread per user/driver)
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number', 50)->unique();
                $table->unsignedBigInteger('user_id');
                $table->string('user_type', 20)->default('customer'); // 'customer' or 'business'
                $table->string('user_name', 150)->nullable();
                $table->string('user_phone', 50)->nullable();
                $table->string('user_email', 150)->nullable();
                $table->string('user_photo', 255)->nullable();
                $table->string('topic', 150)->nullable();
                $table->text('last_message')->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->string('last_sender', 20)->default('user'); // 'user', 'admin'
                $table->integer('unread_admin_count')->default(0);
                $table->integer('unread_user_count')->default(0);
                $table->string('status', 20)->default('active'); // 'active', 'resolved', 'closed'
                $table->timestamps();

                $table->index(['user_id', 'user_type']);
                $table->index(['user_type', 'status']);
            });
        }

        // 3. Support Messages (Chat message history for a ticket)
        if (!Schema::hasTable('support_messages')) {
            Schema::create('support_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id');
                $table->unsignedBigInteger('sender_id')->default(0); // 0 for admin, or user id
                $table->string('sender_type', 20)->default('customer'); // 'customer', 'business', 'admin'
                $table->string('sender_name', 150)->nullable();
                $table->text('message');
                $table->string('attachment', 255)->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->foreign('ticket_id')->references('id')->on('support_tickets')->onDelete('cascade');
                $table->index(['ticket_id', 'created_at']);
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
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_quick_questions');
    }
};

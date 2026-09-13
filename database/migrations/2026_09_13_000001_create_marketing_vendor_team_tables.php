<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Marketing Vendors Table
        if (!Schema::hasTable('marketing_vendors')) {
            Schema::create('marketing_vendors', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // ID in tj_conducteur or tj_user_app
                $table->string('user_type', 20)->default('driver'); // 'driver' or 'customer'
                $table->string('vendor_code', 30)->unique()->nullable(); // e.g. TM00101
                $table->string('team_location', 191);
                $table->string('team_type', 100);
                $table->text('remarks')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
                $table->decimal('rate_per_customer', 10, 2)->default(0.00); // Admin sets on approval
                $table->decimal('rate_per_business', 10, 2)->default(0.00); // Admin sets on approval
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'user_type']);
                $table->index('vendor_code');
                $table->index('status');
            });
        }

        // 2. Marketing Team Members (Freelancers) Table
        if (!Schema::hasTable('marketing_team_members')) {
            Schema::create('marketing_team_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id'); // References marketing_vendors.id
                $table->unsignedBigInteger('user_id'); // ID in tj_conducteur or tj_user_app
                $table->string('user_type', 20)->default('driver'); // 'driver' or 'customer'
                $table->string('member_code', 30)->unique(); // e.g. FR10001
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();

                $table->index('vendor_id');
                $table->index(['user_id', 'user_type']);
                $table->index('member_code');
                $table->index('status');
            });
        }

        // 3. Marketing Acquisitions (Customers & Business Users acquired) Table
        if (!Schema::hasTable('marketing_acquisitions')) {
            Schema::create('marketing_acquisitions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id');
                $table->unsignedBigInteger('team_member_id');
                $table->unsignedBigInteger('acquired_user_id'); // ID of acquired user
                $table->string('acquired_user_type', 20); // 'customer' or 'business'
                $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
                $table->unsignedBigInteger('verified_by')->nullable(); // Admin ID
                $table->timestamp('verified_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->decimal('payout_rate_applied', 10, 2)->default(0.00);
                $table->enum('payout_status', ['unpaid', 'paid'])->default('unpaid');
                $table->timestamp('paid_at')->nullable();
                $table->string('payout_reference', 100)->nullable();
                $table->timestamps();

                $table->index('vendor_id');
                $table->index('team_member_id');
                $table->index(['acquired_user_id', 'acquired_user_type']);
                $table->index('verification_status');
                $table->index('payout_status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('marketing_acquisitions');
        Schema::dropIfExists('marketing_team_members');
        Schema::dropIfExists('marketing_vendors');
    }
};

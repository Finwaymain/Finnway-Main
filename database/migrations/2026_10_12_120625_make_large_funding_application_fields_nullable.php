<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('large_funding_applications', function (Blueprint $table) {
            $table->string('applicant_name', 150)->nullable()->change();
            $table->string('email', 150)->nullable()->change();
            $table->string('applicant_role', 60)->nullable()->change();
            $table->string('pan_number', 20)->nullable()->change();
            $table->string('city', 100)->nullable()->change();
            $table->string('state', 100)->nullable()->change();
            $table->string('pincode', 20)->nullable()->change();

            $table->string('business_name', 200)->nullable()->change();
            $table->string('business_type', 60)->nullable()->change();
            $table->string('business_category', 100)->nullable()->change();
            $table->text('business_description')->nullable()->change();
            $table->string('business_registration_status', 30)->nullable()->change();
            $table->text('business_address')->nullable()->change();

            $table->string('required_funding_amount', 60)->nullable()->change();
            $table->string('funding_type', 60)->nullable()->change();
            $table->string('funding_purpose', 100)->nullable()->change();
            $table->string('funding_timeline', 60)->nullable()->change();
            $table->text('expected_use_of_funds')->nullable()->change();

            $table->string('business_stage', 60)->nullable()->change();
            $table->string('business_start_date', 60)->nullable()->change();
            $table->string('monthly_revenue', 60)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('large_funding_applications', function (Blueprint $table) {
            //
        });
    }
};

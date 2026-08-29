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
        Schema::table('tj_user_app', function (Blueprint $table) {
            if (!Schema::hasColumn('tj_user_app', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('tj_user_app', 'branch_name')) {
                $table->string('branch_name')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('tj_user_app', 'holder_name')) {
                $table->string('holder_name')->nullable()->after('branch_name');
            }
            if (!Schema::hasColumn('tj_user_app', 'account_no')) {
                $table->string('account_no')->nullable()->after('holder_name');
            }
            if (!Schema::hasColumn('tj_user_app', 'ifsc_code')) {
                $table->string('ifsc_code')->nullable()->after('account_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tj_user_app', function (Blueprint $table) {
            $cols = ['bank_name', 'branch_name', 'holder_name', 'account_no', 'ifsc_code'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('tj_user_app', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
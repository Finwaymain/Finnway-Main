<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('referral') && !Schema::hasColumn('referral', 'user_type')) {
            Schema::table('referral', function (Blueprint $table) {
                $table->string('user_type', 50)->default('customer')->after('user_id');
            });
        }

        if (Schema::hasTable('tj_user_app') && !Schema::hasColumn('tj_user_app', 'referral_code')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                $table->string('referral_code', 50)->nullable();
            });
        }

        if (Schema::hasTable('tj_conducteur') && !Schema::hasColumn('tj_conducteur', 'referral_code')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                $table->string('referral_code', 50)->nullable();
            });
        }

        // Standardize existing referral records to FIIN + 6 digits
        $referrals = DB::table('referral')->get();
        foreach ($referrals as $ref) {
            $seqId = $ref->id;
            $code = 'FIIN' . str_pad((string)$seqId, 6, '0', STR_PAD_LEFT);
            $userId = (int)$ref->user_id;

            // Determine user_type
            $type = 'customer';
            $isDriver = DB::table('tj_conducteur')->where('id', $userId)->exists();
            $isUser = DB::table('tj_user_app')->where('id', $userId)->exists();

            if ($isDriver && !$isUser) {
                $type = 'driver';
            }

            DB::table('referral')->where('id', $seqId)->update([
                'referral_code' => $code,
                'user_type'     => $type,
            ]);

            if ($type === 'driver' && Schema::hasColumn('tj_conducteur', 'referral_code')) {
                DB::table('tj_conducteur')->where('id', $userId)->update(['referral_code' => $code]);
            } elseif ($type === 'customer' && Schema::hasColumn('tj_user_app', 'referral_code')) {
                DB::table('tj_user_app')->where('id', $userId)->update(['referral_code' => $code]);
            }
        }
    }

    public function down(): void
    {
        // No-op
    }
};

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReferralCodeService
{
    /**
     * Generate a globally unique, distinct referral code for Consumer or Business (Driver) users.
     * - Consumer (Customer): FIINC + 6 unique uppercase chars (e.g., FIINC8X92K1)
     * - Business (Driver/Provider): FIINB + 6 unique uppercase chars (e.g., FIINB4M7P9Q)
     */
    public static function generateUniqueCode(string $userType = 'customer', ?int $userId = null): string
    {
        $isDriver = in_array(strtolower(trim($userType)), ['driver', 'conducteur', 'business', 'provider']);
        $prefix = $isDriver ? 'FIINB' : 'FIINC';
        
        // Exclude ambiguous characters 0, O, I, 1 to prevent readability confusion
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $charsLen = strlen($chars);

        do {
            $randomString = '';
            for ($i = 0; $i < 6; $i++) {
                $randomString .= $chars[random_int(0, $charsLen - 1)];
            }
            $code = $prefix . $randomString;

            // Ensure 100% uniqueness across referral table, tj_user_app, and tj_conducteur
            $existsInReferral = DB::table('referral')->where('referral_code', $code)->exists();
            $existsInUser     = Schema::hasColumn('tj_user_app', 'referral_code') 
                                ? DB::table('tj_user_app')->where('referral_code', $code)->exists() 
                                : false;
            $existsInDriver   = Schema::hasColumn('tj_conducteur', 'referral_code') 
                                ? DB::table('tj_conducteur')->where('referral_code', $code)->exists() 
                                : false;

        } while ($existsInReferral || $existsInUser || $existsInDriver);

        return $code;
    }

    /**
     * Get or create a unique referral code for a user/driver and sync it across all tables.
     */
    public static function getOrCreateReferralCode(int $userId, string $userType = 'customer'): string
    {
        $isDriver = in_array(strtolower(trim($userType)), ['driver', 'conducteur', 'business', 'provider']);
        $type = $isDriver ? 'driver' : 'customer';

        // 1. Check referral table
        $refRow = DB::table('referral')->where('user_id', $userId)->where('user_type', $type)->first();
        if ($refRow && !empty($refRow->referral_code) && $refRow->referral_code !== 'TEMP') {
            $existingCode = trim($refRow->referral_code);
            // Check for collision with any OTHER user
            $duplicateCount = DB::table('referral')
                ->where('referral_code', $existingCode)
                ->where(function($q) use ($userId, $type) {
                    $q->where('user_id', '!=', $userId)->orWhere('user_type', '!=', $type);
                })
                ->count();

            if ($duplicateCount === 0) {
                self::syncCodeToUserTable($userId, $type, $existingCode);
                return $existingCode;
            }
        }

        // 2. Check user's own table
        $table = ($type === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        if (Schema::hasColumn($table, 'referral_code')) {
            $userCode = DB::table($table)->where('id', $userId)->value('referral_code');
            if (!empty($userCode) && $userCode !== 'TEMP') {
                $userCode = trim($userCode);
                $duplicateCount = DB::table($table)->where('referral_code', $userCode)->where('id', '!=', $userId)->count();
                if ($duplicateCount === 0) {
                    self::syncCodeToReferralTable($userId, $type, $userCode);
                    return $userCode;
                }
            }
        }

        // 3. Generate brand new globally unique code
        $newCode = self::generateUniqueCode($type, $userId);

        self::syncCodeToReferralTable($userId, $type, $newCode);
        self::syncCodeToUserTable($userId, $type, $newCode);

        return $newCode;
    }

    /**
     * Sync referral code to the referral table.
     */
    public static function syncCodeToReferralTable(int $userId, string $userType, string $code): void
    {
        $existing = DB::table('referral')->where('user_id', $userId)->where('user_type', $userType)->first();
        if ($existing) {
            DB::table('referral')->where('id', $existing->id)->update(['referral_code' => $code]);
        } else {
            DB::table('referral')->insert([
                'user_id'       => $userId,
                'user_type'     => $userType,
                'referral_code' => $code,
                'code_used'     => 'false',
                'creer'         => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Sync referral code to the user or driver profile table.
     */
    public static function syncCodeToUserTable(int $userId, string $userType, string $code): void
    {
        $table = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        if (Schema::hasColumn($table, 'referral_code')) {
            DB::table($table)->where('id', $userId)->update(['referral_code' => $code]);
        }
    }

    /**
     * Resolve referrer from any valid referral code format.
     */
    public static function resolveReferrer(string $referralCode): ?array
    {
        $code = strtoupper(trim($referralCode));
        if (empty($code)) {
            return null;
        }

        // 1. Direct match in referral table
        $refRow = DB::table('referral')->where(DB::raw('UPPER(referral_code)'), $code)->first();
        if ($refRow && !empty($refRow->user_id)) {
            $type = $refRow->user_type ?: 'customer';
            return ['user_id' => (int)$refRow->user_id, 'user_type' => $type];
        }

        // 2. Direct match in tj_user_app (consumer)
        if (Schema::hasColumn('tj_user_app', 'referral_code')) {
            $userMatch = DB::table('tj_user_app')->where(DB::raw('UPPER(referral_code)'), $code)->first();
            if ($userMatch) {
                return ['user_id' => (int)$userMatch->id, 'user_type' => 'customer'];
            }
        }

        // 3. Direct match in tj_conducteur (business driver)
        if (Schema::hasColumn('tj_conducteur', 'referral_code')) {
            $driverMatch = DB::table('tj_conducteur')->where(DB::raw('UPPER(referral_code)'), $code)->first();
            if ($driverMatch) {
                return ['user_id' => (int)$driverMatch->id, 'user_type' => 'driver'];
            }
        }

        // 4. Legacy FIIN numeric fallback
        $cleanNumeric = preg_replace('/^(FIINC|FIINB|FIINU|FIIN)0*/i', '', $code);
        if (is_numeric($cleanNumeric) && (int)$cleanNumeric > 0) {
            $targetId = (int)$cleanNumeric;
            $refById = DB::table('referral')->where('id', $targetId)->first();
            if ($refById && !empty($refById->user_id)) {
                return ['user_id' => (int)$refById->user_id, 'user_type' => $refById->user_type ?: 'customer'];
            }
        }

        return null;
    }
}

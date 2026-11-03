<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReferralCodeService
{
    /**
     * Generate a globally unique, distinct referral code for Consumer, Business (Driver), or Restaurant users.
     * - Consumer (Customer): FIINC + 6 unique uppercase chars (e.g., FIINC8X92K1)
     * - Business (Driver/Provider): FIINB + 6 unique uppercase chars (e.g., FIINB4M7P9Q)
     * - Restaurant (Merchant): FIINR + 6 unique uppercase chars (e.g., FIINR3K8P9W)
     */
    public static function generateUniqueCode(string $userType = 'customer', ?int $userId = null): string
    {
        $cleanType = strtolower(trim($userType));
        $isRestaurant = in_array($cleanType, ['restaurant', 'food', 'merchant']);
        $isDriver = in_array($cleanType, ['driver', 'conducteur', 'business', 'provider']);
        $prefix = $isRestaurant ? 'FIINR' : ($isDriver ? 'FIINB' : 'FIINC');
        
        // Exclude ambiguous characters 0, O, I, 1 to prevent readability confusion
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $charsLen = strlen($chars);

        do {
            $randomString = '';
            for ($i = 0; $i < 6; $i++) {
                $randomString .= $chars[random_int(0, $charsLen - 1)];
            }
            $code = $prefix . $randomString;

            // Ensure 100% uniqueness across referral table, tj_user_app, tj_conducteur, food_owners, food_restaurants
            $existsInReferral   = DB::table('referral')->where('referral_code', $code)->exists();
            $existsInUser       = Schema::hasColumn('tj_user_app', 'referral_code') 
                                    ? DB::table('tj_user_app')->where('referral_code', $code)->exists() 
                                    : false;
            $existsInDriver     = Schema::hasColumn('tj_conducteur', 'referral_code') 
                                    ? DB::table('tj_conducteur')->where('referral_code', $code)->exists() 
                                    : false;
            $existsInFoodOwner  = (Schema::hasTable('food_owners') && Schema::hasColumn('food_owners', 'referral_code'))
                                    ? DB::table('food_owners')->where('referral_code', $code)->exists()
                                    : false;
            $existsInRestaurant = (Schema::hasTable('food_restaurants') && Schema::hasColumn('food_restaurants', 'referral_code'))
                                    ? DB::table('food_restaurants')->where('referral_code', $code)->exists()
                                    : false;

        } while ($existsInReferral || $existsInUser || $existsInDriver || $existsInFoodOwner || $existsInRestaurant);

        return $code;
    }

    /**
     * Get or create a unique referral code for a user/driver/restaurant and sync it across all tables.
     */
    public static function getOrCreateReferralCode(int $userId, string $userType = 'customer'): string
    {
        $cleanType = strtolower(trim($userType));
        $isRestaurant = in_array($cleanType, ['restaurant', 'food', 'merchant']);
        $isDriver = in_array($cleanType, ['driver', 'conducteur', 'business', 'provider']);
        $type = $isRestaurant ? 'restaurant' : ($isDriver ? 'driver' : 'customer');
        $expectedPrefix = $isRestaurant ? 'FIINR' : ($isDriver ? 'FIINB' : 'FIINC');

        // 1. Check referral table
        $refRow = DB::table('referral')->where('user_id', $userId)->where('user_type', $type)->first();
        if ($refRow && !empty($refRow->referral_code) && $refRow->referral_code !== 'TEMP') {
            $existingCode = strtoupper(trim($refRow->referral_code));
            // Accept if code starts with expected prefix or any valid FIIN prefix series
            if (str_starts_with($existingCode, $expectedPrefix) || str_starts_with($existingCode, 'FIIN')) {
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
        }

        // 2. Check user's own table
        if ($type === 'restaurant') {
            if (Schema::hasTable('food_owners') && Schema::hasColumn('food_owners', 'referral_code')) {
                $ownerCode = DB::table('food_owners')->where('id', $userId)->value('referral_code');
                if (!empty($ownerCode) && $ownerCode !== 'TEMP') {
                    $ownerCode = strtoupper(trim($ownerCode));
                    if (str_starts_with($ownerCode, $expectedPrefix) || str_starts_with($ownerCode, 'FIIN')) {
                        $duplicateCount = DB::table('food_owners')->where('referral_code', $ownerCode)->where('id', '!=', $userId)->count();
                        if ($duplicateCount === 0) {
                            self::syncCodeToReferralTable($userId, $type, $ownerCode);
                            self::syncCodeToUserTable($userId, $type, $ownerCode);
                            return $ownerCode;
                        }
                    }
                }
            }
        } else {
            $table = ($type === 'driver') ? 'tj_conducteur' : 'tj_user_app';
            if (Schema::hasColumn($table, 'referral_code')) {
                $userCode = DB::table($table)->where('id', $userId)->value('referral_code');
                if (!empty($userCode) && $userCode !== 'TEMP') {
                    $userCode = strtoupper(trim($userCode));
                    if (str_starts_with($userCode, $expectedPrefix) || str_starts_with($userCode, 'FIIN')) {
                        $duplicateCount = DB::table($table)->where('referral_code', $userCode)->where('id', '!=', $userId)->count();
                        if ($duplicateCount === 0) {
                            self::syncCodeToReferralTable($userId, $type, $userCode);
                            return $userCode;
                        }
                    }
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
     * Sync referral code to the user, driver, or restaurant profile table.
     */
    public static function syncCodeToUserTable(int $userId, string $userType, string $code): void
    {
        if ($userType === 'restaurant') {
            if (Schema::hasTable('food_owners') && Schema::hasColumn('food_owners', 'referral_code')) {
                DB::table('food_owners')->where('id', $userId)->update(['referral_code' => $code]);
            }
            if (Schema::hasTable('food_restaurants') && Schema::hasColumn('food_restaurants', 'referral_code')) {
                DB::table('food_restaurants')->where('owner_id', $userId)->update(['referral_code' => $code]);
            }
            return;
        }

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

        // 4. Direct match in food_owners (restaurant merchant)
        if (Schema::hasTable('food_owners') && Schema::hasColumn('food_owners', 'referral_code')) {
            $ownerMatch = DB::table('food_owners')->where(DB::raw('UPPER(referral_code)'), $code)->first();
            if ($ownerMatch) {
                return ['user_id' => (int)$ownerMatch->id, 'user_type' => 'restaurant'];
            }
        }

        // 5. Direct match in food_restaurants (restaurant outlet)
        if (Schema::hasTable('food_restaurants') && Schema::hasColumn('food_restaurants', 'referral_code')) {
            $restMatch = DB::table('food_restaurants')->where(DB::raw('UPPER(referral_code)'), $code)->first();
            if ($restMatch) {
                return [
                    'user_id' => (int)$restMatch->owner_id,
                    'user_type' => 'restaurant',
                    'restaurant_id' => (int)$restMatch->id,
                ];
            }
        }

        // Generate normalized variants for VR/SV/FR/FIIN (e.g., VR00034 vs VR34 vs VR034)
        $codeVariants = [$code];
        if (preg_match('/^([A-Z]+)0*(\d+)$/', $code, $m)) {
            $prefix = $m[1];
            $cleanNum = ltrim($m[2], '0');
            $codeVariants[] = $prefix . $cleanNum;
            $codeVariants[] = $prefix . '0' . $cleanNum;
            $codeVariants[] = $prefix . sprintf('%05d', (int)$cleanNum);
            $codeVariants[] = $prefix . sprintf('%06d', (int)$cleanNum);
        }
        $codeVariants = array_values(array_unique($codeVariants));

        // 6. Match in marketing_vendors (vendor / sub-vendor / head-vendor)
        if (Schema::hasTable('marketing_vendors')) {
            $vendorMatch = DB::table('marketing_vendors')
                ->where(function($q) use ($codeVariants) {
                    $q->whereIn(DB::raw('UPPER(vendor_code)'), $codeVariants);
                    if (Schema::hasColumn('marketing_vendors', 'sub_vendor_code')) {
                        $q->orWhereIn(DB::raw('UPPER(sub_vendor_code)'), $codeVariants);
                    }
                    if (Schema::hasColumn('marketing_vendors', 'freelancer_code')) {
                        $q->orWhereIn(DB::raw('UPPER(freelancer_code)'), $codeVariants);
                    }
                })
                ->first();
            if ($vendorMatch) {
                return [
                    'user_id'   => (int)$vendorMatch->user_id,
                    'user_type' => $vendorMatch->hierarchy_level > 0 ? 'sub_vendor' : 'vendor',
                    'vendor_id' => (int)$vendorMatch->id,
                ];
            }
        }

        // 7. Match in marketing_team_members (freelancer / agent)
        if (Schema::hasTable('marketing_team_members') && Schema::hasColumn('marketing_team_members', 'member_code')) {
            $memberMatch = DB::table('marketing_team_members')->whereIn(DB::raw('UPPER(member_code)'), $codeVariants)->first();
            if ($memberMatch) {
                return [
                    'user_id'   => (int)$memberMatch->user_id,
                    'user_type' => 'freelance',
                    'vendor_id' => (int)$memberMatch->vendor_id,
                    'member_id' => (int)$memberMatch->id,
                ];
            }
        }

        // 8. Legacy FIIN numeric fallback
        $cleanNumeric = preg_replace('/^(FIINC|FIINB|FIINR|FIINU|FIIN)0*/i', '', $code);
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

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PromotionalService
{
    /**
     * Get active master promotional configuration
     */
    public static function getActiveConfig()
    {
        if (!Schema::hasTable('promotional_configs')) {
            return null;
        }

        return DB::table('promotional_configs')
            ->where('status', 'active')
            ->first();
    }

    /**
     * Grant Welcome Bonus to newly registered Consumer or Driver
     *
     * @param int $userId
     * @param string $userType ('customer' or 'driver')
     * @param string|null $referralCode
     * @return bool
     */
    public static function grantWelcomeBonus(int $userId, string $userType, ?string $referralCode = null): bool
    {
        try {
            $config = self::getActiveConfig();
            if (!$config) {
                return false;
            }

            // Verify applicable roles
            if (!empty($config->applicable_roles) && $config->applicable_roles !== 'all') {
                if ($config->applicable_roles !== $userType) {
                    return false;
                }
            }

            // Avoid duplicate grants
            $existing = DB::table('user_promotions')
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->first();

            if ($existing) {
                return false;
            }

            $hasCode = !empty(trim((string)$referralCode));

            $initialBonus       = $hasCode ? (float)$config->bonus_with_code : (float)$config->bonus_without_code;
            $totalUses          = $hasCode ? (int)$config->uses_with_code : (int)$config->uses_without_code;
            $discountPerService = (float)$config->discount_per_service;

            // Compute expiry
            $expiryDate = null;
            if (!empty($config->custom_expiry_date) && Carbon::parse($config->custom_expiry_date)->isFuture()) {
                $expiryDate = Carbon::parse($config->custom_expiry_date)->endOfDay();
            } elseif (!empty($config->expiry_days) && (int)$config->expiry_days > 0) {
                $expiryDate = Carbon::now()->addDays((int)$config->expiry_days);
            }

            DB::table('user_promotions')->insert([
                'user_id'              => $userId,
                'user_type'            => $userType,
                'initial_bonus'        => $initialBonus,
                'remaining_bonus'      => $initialBonus,
                'discount_per_service' => $discountPerService,
                'total_uses'           => $totalUses,
                'uses_remaining'       => $totalUses,
                'joined_with_code'     => $hasCode ? 1 : 0,
                'referral_code_used'   => $hasCode ? trim((string)$referralCode) : null,
                'expiry_date'          => $expiryDate,
                'status'               => 'active',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            \Log::info("PromotionalService: Welcome bonus of ₹{$initialBonus} ({$totalUses} uses) granted to {$userType} #{$userId} (HasCode: " . ($hasCode ? 'Yes' : 'No') . ")");
            return true;

        } catch (\Throwable $e) {
            \Log::error("PromotionalService::grantWelcomeBonus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Upgrade direct join promotion to with-code promotion if user later applies referral code
     */
    public static function upgradeToReferralBonus(int $userId, string $userType, string $referralCode): bool
    {
        try {
            $config = self::getActiveConfig();
            if (!$config) {
                return false;
            }

            $promo = DB::table('user_promotions')
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->first();

            if (!$promo) {
                return self::grantWelcomeBonus($userId, $userType, $referralCode);
            }

            // Only upgrade if currently without code
            if (!$promo->joined_with_code) {
                $bonusDiff = max(0, (float)$config->bonus_with_code - (float)$config->bonus_without_code);
                $usesDiff  = max(0, (int)$config->uses_with_code - (int)$config->uses_without_code);

                DB::table('user_promotions')->where('id', $promo->id)->update([
                    'initial_bonus'      => (float)$config->bonus_with_code,
                    'remaining_bonus'    => (float)$promo->remaining_bonus + $bonusDiff,
                    'total_uses'         => (int)$config->uses_with_code,
                    'uses_remaining'     => (int)$promo->uses_remaining + $usesDiff,
                    'joined_with_code'   => 1,
                    'referral_code_used' => $referralCode,
                    'status'             => 'active',
                    'updated_at'         => now(),
                ]);

                \Log::info("PromotionalService: Upgraded user #{$userId} ($userType) to with-code promo (+₹{$bonusDiff}, +{$usesDiff} uses)");
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            \Log::error("PromotionalService::upgradeToReferralBonus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active promotion details for a user/driver
     *
     * @param int $userId
     * @param string $userType
     * @return array
     */
    public static function getUserPromotion(int $userId, string $userType = 'customer'): array
    {
        $default = [
            'has_promotion'        => false,
            'balance'              => '0.00',
            'discount_per_service' => '0.00',
            'uses_remaining'       => 0,
            'total_uses'           => 0,
            'expiry_date'          => null,
            'is_active'            => false,
            'status'               => 'none',
        ];

        if (!Schema::hasTable('user_promotions')) {
            return $default;
        }

        $promo = DB::table('user_promotions')
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->first();

        if (!$promo) {
            return $default;
        }

        $now = Carbon::now();
        $isExpired = !empty($promo->expiry_date) && Carbon::parse($promo->expiry_date)->isPast();
        $isExhausted = (int)$promo->uses_remaining <= 0 || (float)$promo->remaining_bonus <= 0;

        if ($isExpired && $promo->status !== 'expired') {
            DB::table('user_promotions')->where('id', $promo->id)->update(['status' => 'expired', 'updated_at' => now()]);
            $promo->status = 'expired';
        } elseif ($isExhausted && $promo->status !== 'exhausted') {
            DB::table('user_promotions')->where('id', $promo->id)->update(['status' => 'exhausted', 'updated_at' => now()]);
            $promo->status = 'exhausted';
        }

        $isActive = ($promo->status === 'active' && !$isExpired && !$isExhausted);

        return [
            'has_promotion'        => $isActive,
            'balance'              => number_format(max(0, (float)$promo->remaining_bonus), 2, '.', ''),
            'discount_per_service' => number_format((float)$promo->discount_per_service, 2, '.', ''),
            'uses_remaining'       => max(0, (int)$promo->uses_remaining),
            'total_uses'           => (int)$promo->total_uses,
            'expiry_date'          => $promo->expiry_date ? Carbon::parse($promo->expiry_date)->format('d M Y, h:i A') : null,
            'is_active'            => $isActive,
            'status'               => $promo->status,
            'promo_id'             => $promo->id,
        ];
    }

    /**
     * Calculate Marketing Fare Breakdown
     * If user has promo:
     *   Base: ₹100
     *   Promo Amount: +₹50
     *   Booking Total: ₹150
     *   Welcome Discount: -₹50
     *   Final Payable: ₹100
     *
     * @param int $userId
     * @param string $userType
     * @param float $basePrice
     * @return array
     */
    public static function calculatePromoFare(int $userId, string $userType, float $basePrice): array
    {
        $promo = self::getUserPromotion($userId, $userType);

        if (!$promo['is_active']) {
            return [
                'is_promo_available'      => false,
                'base_price'              => $basePrice,
                'promotional_amount'      => 0.00,
                'displayed_booking_total' => $basePrice,
                'welcome_discount'        => 0.00,
                'final_payable'           => $basePrice,
            ];
        }

        $config = self::getActiveConfig();
        $minBill = $config ? (float)$config->min_bill_amount : 0.00;
        $maxBill = $config ? (float)$config->max_bill_amount : 999999.00;

        if ($basePrice < $minBill || $basePrice > $maxBill) {
            return [
                'is_promo_available'      => false,
                'base_price'              => $basePrice,
                'promotional_amount'      => 0.00,
                'displayed_booking_total' => $basePrice,
                'welcome_discount'        => 0.00,
                'final_payable'           => $basePrice,
            ];
        }

        $discount = (float)$promo['discount_per_service'];
        // Ensure discount doesn't exceed current remaining balance
        $currentBalance = (float)$promo['balance'];
        if ($discount > $currentBalance) {
            $discount = $currentBalance;
        }

        $promotionalAmount = $discount;
        $bookingTotal = round($basePrice + $promotionalAmount, 2);
        $welcomeDiscount = $promotionalAmount;
        $finalPayable = $basePrice;

        return [
            'is_promo_available'      => true,
            'base_price'              => $basePrice,
            'promotional_amount'      => $promotionalAmount,
            'displayed_booking_total' => $bookingTotal,
            'welcome_discount'        => $welcomeDiscount,
            'final_payable'           => $finalPayable,
            'uses_remaining'          => $promo['uses_remaining'],
            'balance'                 => $promo['balance'],
        ];
    }

    /**
     * Apply and record promo usage upon service booking or completion
     *
     * @param int $userId
     * @param string $userType
     * @param string $serviceType ('cab' or 'home_service')
     * @param string|int $bookingId
     * @param float $servicePrice
     * @return bool
     */
    public static function applyPromoUsage(int $userId, string $userType, string $serviceType, $bookingId, float $servicePrice): bool
    {
        try {
            $promo = self::getUserPromotion($userId, $userType);
            if (!$promo['is_active']) {
                return false;
            }

            $discount = (float)$promo['discount_per_service'];
            $currentBalance = (float)$promo['balance'];
            if ($discount > $currentBalance) {
                $discount = $currentBalance;
            }

            $newBalance = max(0, $currentBalance - $discount);
            $newUses = max(0, $promo['uses_remaining'] - 1);
            $newStatus = ($newUses <= 0 || $newBalance <= 0) ? 'exhausted' : 'active';

            // Update user promo
            DB::table('user_promotions')->where('id', $promo['promo_id'])->update([
                'remaining_bonus' => $newBalance,
                'uses_remaining'  => $newUses,
                'status'          => $newStatus,
                'updated_at'      => now(),
            ]);

            // Log usage
            DB::table('user_promotion_logs')->insert([
                'user_promotion_id'  => $promo['promo_id'],
                'user_id'            => $userId,
                'user_type'          => $userType,
                'service_type'       => $serviceType,
                'booking_id'         => (string)$bookingId,
                'service_price'      => $servicePrice,
                'promotional_amount' => $discount,
                'booking_total'      => $servicePrice + $discount,
                'discount_applied'   => $discount,
                'final_payable'      => $servicePrice,
                'created_at'         => now(),
            ]);

            \Log::info("PromotionalService::applyPromoUsage applied ₹{$discount} on {$serviceType} #{$bookingId} for {$userType} #{$userId}. Uses left: {$newUses}");
            return true;

        } catch (\Throwable $e) {
            \Log::error("PromotionalService::applyPromoUsage error: " . $e->getMessage());
            return false;
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\UserApp;
use App\Models\Driver;

class ReferralRewardService
{
    /**
     * Process dynamic referral reward for an event/service executed by a referred user.
     *
     * @param int $refereeId ID of the referred user/driver performing the activity
     * @param string $refereeType 'customer' or 'driver'
     * @param string $eventSlug e.g. 'marketplace_order', 'medical_cashback', 'service_booking', 'consumer_subscription', 'business_subscription', 'cab_ride', 'wallet_recharge', 'qr_payment'
     * @param float $transactionAmount Total transaction amount
     * @param string $eventTitle Human-readable event description (e.g. 'Marketplace Purchase', 'Medical Cashback Card', 'Cab Ride')
     * @return array
     */
    public static function processReward($refereeId, $refereeType, $eventSlug, $transactionAmount, $eventTitle = '')
    {
        if ($transactionAmount <= 0) {
            return ['reward_processed' => false, 'reason' => 'Invalid transaction amount'];
        }

        // 1. Resolve Referee's Referrer
        $refereeUser = ($refereeType === 'driver') 
            ? Driver::find($refereeId) 
            : UserApp::find($refereeId);

        if (!$refereeUser) {
            return ['reward_processed' => false, 'reason' => 'Referee user record not found'];
        }

        $referrerId = null;
        $referrerType = 'customer';
        $referrerRefCode = $refereeUser->ref_by ?? null;

        if (empty($referrerRefCode) && Schema::hasTable('referral')) {
            $refRow = DB::table('referral')->where('user_id', $refereeId)->first();
            if ($refRow && !empty($refRow->referral_by_id)) {
                $referrerId = (int)$refRow->referral_by_id;
                $referrerRefCode = $refRow->referral_code ?? null;
            }
        }

        if (!$referrerId) {
            if (empty($referrerRefCode)) {
                return ['reward_processed' => false, 'reason' => 'No referrer code linked'];
            }

            $codeLower = strtolower(trim($referrerRefCode));

            // Primary: look up from referral table (source of truth for unified FIIN codes)
            $refTableRow = DB::table('referral')->whereRaw('LOWER(referral_code) = ?', [$codeLower])->first();
            if ($refTableRow && !empty($refTableRow->user_id)) {
                $referrerId   = (int)$refTableRow->user_id;
                $referrerType = $refTableRow->user_type ?? null;
                if (empty($referrerType)) {
                    $referrerType = DB::table('tj_conducteur')->where('id', $referrerId)->exists() ? 'driver' : 'customer';
                }
            }

            // Legacy fallback: FIINU = customer, FIINB = driver
            if (!$referrerId) {
                if (str_starts_with($codeLower, 'fiinu')) {
                    $numericId = (int)preg_replace('/[^0-9]/', '', $codeLower);
                    if ($numericId > 0 && DB::table('tj_user_app')->where('id', $numericId)->exists()) {
                        $referrerId = $numericId;
                        $referrerType = 'customer';
                    }
                } elseif (str_starts_with($codeLower, 'fiinb')) {
                    $numericId = (int)preg_replace('/[^0-9]/', '', $codeLower);
                    if ($numericId > 0 && DB::table('tj_conducteur')->where('id', $numericId)->exists()) {
                        $referrerId = $numericId;
                        $referrerType = 'driver';
                    }
                }
            }

            if (!$referrerId) {
                // Find referrer by referral_code
                $referrer = UserApp::where(DB::raw('LOWER(referral_code)'), $codeLower)->first();
                if ($referrer) {
                    $referrerId = $referrer->id;
                    $referrerType = 'customer';
                } else {
                    $referrer = Driver::where(DB::raw('LOWER(referral_code)'), $codeLower)->first();
                    if ($referrer) {
                        $referrerId = $referrer->id;
                        $referrerType = 'driver';
                    }
                }
            }
        }

        if (!$referrerId) {
            return ['reward_processed' => false, 'reason' => 'No valid referrer linked'];
        }

        // Determine Referrer Type if not resolved
        if ($referrerId && !isset($referrer)) {
            $rUser = UserApp::find($referrerId);
            if ($rUser) {
                $referrerType = 'customer';
            } else {
                $rDriver = Driver::find($referrerId);
                if ($rDriver) {
                    $referrerType = 'driver';
                }
            }
        }

        $isSelfReferral = ($referrerId && (int)$referrerId === (int)$refereeId && $referrerType === $refereeType);
        if ($isSelfReferral) {
            return ['reward_processed' => false, 'reason' => 'Referrer invalid or self-referral'];
        }

        // 2. Fetch Admin Reward Rule dynamically from service_reward_configs, api_key_settings, or tj_settings
        $rewardMode = 'percentage';
        $rewardValue = null;
        $isRuleFound = false;

        // A. Primary: Check service_reward_configs table
        if (Schema::hasTable('service_reward_configs')) {
            $slugClean = str_replace('-', '_', strtolower($eventSlug));
            $config = DB::table('service_reward_configs')
                ->where(function($q) use ($eventSlug, $slugClean) {
                    $q->where('service_slug', $eventSlug)
                      ->orWhere('service_slug', $slugClean)
                      ->orWhere('service_slug', 'like', "%{$slugClean}%")
                      ->orWhere('service_name', 'like', "%{$eventSlug}%");
                })
                ->first();

            if ($config) {
                $isRuleFound = true;
                if (!$config->is_active) {
                    return ['reward_processed' => false, 'reason' => "Event '{$eventSlug}' reward is disabled by Admin"];
                }

                $rewardMode = strtolower($config->reward_mode ?? 'percentage');
                $rewardValue = ($referrerType === 'driver') 
                    ? ($config->business_value ?? $config->customer_value)
                    : ($config->customer_value ?? $config->business_value);
            }
        }

        // B. Secondary: Check api_key_settings event rules (e.g. event_rule_service_booking_value)
        if (!$isRuleFound || $rewardValue === null || $rewardValue === '') {
            $eventKey = str_replace('-', '_', strtolower($eventSlug));
            
            // Normalize slug to match admin event keys: service_booking, user_subscription, marketplace_purchase, etc.
            if (str_contains($eventKey, 'ride') || str_contains($eventKey, 'cab') || str_contains($eventKey, 'transport') || str_contains($eventKey, 'service')) {
                $eventKey = 'service_booking';
            } elseif (str_contains($eventKey, 'subscription') || str_contains($eventKey, 'plan')) {
                $eventKey = 'user_subscription';
            } elseif (str_contains($eventKey, 'market') || str_contains($eventKey, 'order')) {
                $eventKey = 'marketplace_purchase';
            } elseif (str_contains($eventKey, 'qr')) {
                $eventKey = 'qr_payment';
            } elseif (str_contains($eventKey, 'wallet') || str_contains($eventKey, 'transfer')) {
                $eventKey = 'wallet_payment_transfer';
            }

            $enableKey = "event_rule_{$eventKey}_enable";
            $typeKey   = "event_rule_{$eventKey}_type";
            $valKey    = "event_rule_{$eventKey}_value";

            $isEnabled = DB::table('api_key_settings')->where('key_name', $enableKey)->value('key_value');
            if ($isEnabled !== '0') {
                $storedType = DB::table('api_key_settings')->where('key_name', $typeKey)->value('key_value');
                $storedVal  = DB::table('api_key_settings')->where('key_name', $valKey)->value('key_value');

                if ($storedVal !== null && $storedVal !== '') {
                    $isRuleFound = true;
                    $rewardMode  = strtolower($storedType ?? 'percentage');
                    $rewardValue = (string)$storedVal;
                }
            }
        }

        // C. Tertiary: Check general referral reward setting in api_key_settings
        if (!$isRuleFound || $rewardValue === null || $rewardValue === '') {
            $generalMode = DB::table('api_key_settings')->where('key_name', 'referral_reward_mode')->value('key_value');
            $generalVal  = DB::table('api_key_settings')->where('key_name', 'referral_reward_value')->value('key_value');
            if ($generalVal !== null && $generalVal !== '') {
                $isRuleFound = true;
                $rewardMode  = strtolower($generalMode ?? 'percentage');
                $rewardValue = (string)$generalVal;
            }
        }

        // D. Fallback: tj_settings
        if (!$isRuleFound || $rewardValue === null || $rewardValue === '') {
            $settings = DB::table('tj_settings')->first();
            if ($settings && isset($settings->referral_amount) && (float)$settings->referral_amount > 0) {
                $rewardValue = (string)$settings->referral_amount;
                $rewardMode = 'flat';
            } else {
                $rewardValue = '2'; // Default 2% per system rule
                $rewardMode = 'percentage';
            }
        }

        // 3. Dynamically Calculate Reward Amount from Admin DB Values
        $rewardAmount = 0.0;
        $cleanVal = floatval(preg_replace('/[^0-9.]/', '', (string)$rewardValue));

        if ($rewardMode === 'flat' || str_contains(strtolower((string)$rewardValue), 'flat')) {
            $rewardAmount = $cleanVal;
        } else {
            // Percentage mode (e.g. 2%, 5%)
            $percent = $cleanVal;
            $rewardAmount = round(($transactionAmount * $percent) / 100, 2);
        }

        if ($rewardAmount <= 0) {
            return ['reward_processed' => false, 'reason' => 'Calculated reward amount is 0'];
        }

        // 4. Credit reward to Referrer's Wallet safely handling NULL values
        $dateNow = date('Y-m-d H:i:s');
        $displayTitle = !empty($eventTitle) ? $eventTitle : ucfirst(str_replace('_', ' ', $eventSlug));
        $desc = "Referral reward from {$displayTitle} by " . ($refereeType === 'driver' ? "business partner #" : "user #") . $refereeId;

        // 4. Credit reward to Referrer's Wallet & create transaction in respective table
        if ($referrerType === 'driver') {
            $currVal = (float)DB::table('tj_conducteur')->where('id', $referrerId)->value('amount');
            DB::table('tj_conducteur')->where('id', $referrerId)->update(['amount' => $currVal + $rewardAmount]);

            if (Schema::hasTable('tj_conducteur_transaction')) {
                DB::table('tj_conducteur_transaction')->insert([
                    'id_conducteur'    => $referrerId,
                    'amount'           => $rewardAmount,
                    'deduction_type'   => 'credit',
                    'payment_method'   => 'Referral Reward',
                    'payment_status'   => 'success',
                    'withdraw_status'  => 'completed',
                    'user_type'        => 'driver',
                    'receiver_user_id' => $refereeId,
                    'sender_user_type' => $refereeType,
                    'description'      => $desc,
                    'note'             => "Referral Reward: {$displayTitle} #" . $refereeId,
                    'type'             => 'credit',
                    'date'             => date('Y-m-d'),
                    'creer'            => $dateNow,
                    'modifier'         => $dateNow,
                ]);
            }
        } else {
            $currVal = (float)DB::table('tj_user_app')->where('id', $referrerId)->value('amount');
            DB::table('tj_user_app')->where('id', $referrerId)->update(['amount' => $currVal + $rewardAmount]);

            if (Schema::hasTable('tj_transaction')) {
                $txData = [
                    'id_user_app'      => $referrerId,
                    'sender_user_id'   => $refereeId,
                    'sender_user_type' => $refereeType,
                    'amount'           => $rewardAmount,
                    'deduction_type'   => 1,
                    'user_type'        => 'customer',
                    'payment_method'   => 'Referral Reward',
                    'payment_status'   => 'success',
                    'withdraw_status'  => 'completed',
                    'description'      => $desc,
                    'type'             => 'credit',
                    'date'             => date('Y-m-d'),
                    'creer'            => $dateNow,
                    'modifier'         => $dateNow,
                ];
                DB::table('tj_transaction')->insert($txData);
            }
        }

        // 6. Record Referral Activity
        if (Schema::hasTable('referral')) {
            DB::table('referral')->updateOrInsert(
                ['user_id' => $refereeId],
                [
                    'referral_by_id' => $referrerId,
                    'referral_code' => $referrerRefCode,
                    'code_used' => 'true',
                    'creer' => $dateNow
                ]
            );
        }

        // 7. Check if App Install / Registration referral reward threshold is now unlocked
        self::checkAndProcessAppInstallReward($refereeId, $refereeType);

        return [
            'reward_processed' => true,
            'referrer_id' => $referrerId,
            'reward_amount' => $rewardAmount,
            'reward_mode' => $rewardMode,
            'reward_value' => $rewardValue,
            'event_slug' => $eventSlug,
            'event_title' => $displayTitle
        ];
    }

    /**
     * Check if a referred user/driver has completed the required minimum services
     * and minimum purchase amount to qualify the referrer for the App Install / Registration reward.
     *
     * @param int $refereeId
     * @param string $refereeType 'customer' or 'driver'
     * @return array
     */
    public static function checkAndProcessAppInstallReward($refereeId, $refereeType = 'customer')
    {
        if (empty($refereeId)) {
            return ['reward_processed' => false, 'reason' => 'Invalid referee ID'];
        }

        // 1. Resolve Referrer
        $refereeUser = ($refereeType === 'driver') 
            ? Driver::find($refereeId) 
            : UserApp::find($refereeId);

        if (!$refereeUser) {
            return ['reward_processed' => false, 'reason' => 'Referee user not found'];
        }

        $referrerId = null;
        $referrerType = 'customer';
        $refRecord = null;

        if (Schema::hasTable('referral')) {
            $refRecord = DB::table('referral')
                ->where('user_id', $refereeId)
                ->where('user_type', $refereeType)
                ->first();

            if (!$refRecord) {
                $refRecord = DB::table('referral')->where('user_id', $refereeId)->first();
            }

            if ($refRecord && !empty($refRecord->app_install_reward_paid)) {
                return ['reward_processed' => false, 'reason' => 'App install referral reward already credited'];
            }

            if ($refRecord && !empty($refRecord->referral_by_id)) {
                $referrerId = (int)$refRecord->referral_by_id;
                $referrerType = $refRecord->referral_by_type ?? 'customer';
            }
        }

        if (!$referrerId && !empty($refereeUser->ref_by)) {
            $codeLower = strtolower(trim($refereeUser->ref_by));
            $refTableRow = DB::table('referral')->whereRaw('LOWER(referral_code) = ?', [$codeLower])->first();
            if ($refTableRow && !empty($refTableRow->user_id)) {
                $referrerId = (int)$refTableRow->user_id;
                $referrerType = $refTableRow->user_type ?? 'customer';
            }
        }

        $isSelfReferral = ($referrerId && (int)$referrerId === (int)$refereeId && $referrerType === $refereeType);
        if (!$referrerId || $isSelfReferral) {
            return ['reward_processed' => false, 'reason' => 'No valid referrer linked or self-referral'];
        }

        // 2. Fetch Admin Rules for App Install
        $ruleKey = ($refereeType === 'driver') ? 'app_install_business' : 'app_install_user';
        $fallbackKey = ($refereeType === 'driver') ? 'registration' : 'app_install';

        $enable = DB::table('api_key_settings')->where('key_name', "event_rule_{$ruleKey}_enable")->value('key_value')
            ?? DB::table('api_key_settings')->where('key_name', "event_rule_{$fallbackKey}_enable")->value('key_value')
            ?? '1';

        if ($enable === '0') {
            return ['reward_processed' => false, 'reason' => 'App install reward rule is disabled'];
        }

        $type = DB::table('api_key_settings')->where('key_name', "event_rule_{$ruleKey}_type")->value('key_value')
            ?? DB::table('api_key_settings')->where('key_name', "event_rule_{$fallbackKey}_type")->value('key_value')
            ?? 'flat';

        $value = DB::table('api_key_settings')->where('key_name', "event_rule_{$ruleKey}_value")->value('key_value')
            ?? DB::table('api_key_settings')->where('key_name', "event_rule_{$fallbackKey}_value")->value('key_value')
            ?? (($refereeType === 'driver') ? '5' : '10');

        $minServices = (int) (DB::table('api_key_settings')->where('key_name', "event_rule_{$ruleKey}_min_services")->value('key_value')
            ?? DB::table('api_key_settings')->where('key_name', "event_rule_{$fallbackKey}_min_services")->value('key_value')
            ?? '0');

        $minAmount = (float) (DB::table('api_key_settings')->where('key_name', "event_rule_{$ruleKey}_min_amount")->value('key_value')
            ?? DB::table('api_key_settings')->where('key_name', "event_rule_{$fallbackKey}_min_amount")->value('key_value')
            ?? '0');

        // 3. Count Completed Services and Calculate Total Purchase Amount
        $completedCount = 0;
        $totalSpend = 0.0;

        if ($refereeType === 'driver') {
            if (Schema::hasTable('tj_requete')) {
                $rides = DB::table('tj_requete')->where('id_conducteur', $refereeId)->where('statut', 'completed')->get();
                $completedCount += $rides->count();
                $totalSpend += (float)$rides->sum('montant');
            }
            if (Schema::hasTable('service_requests')) {
                $services = DB::table('service_requests')->where('driver_id', $refereeId)->whereIn('status', ['Completed', 'completed'])->get();
                $completedCount += $services->count();
                $totalSpend += (float)$services->sum('amount');
            }
            if (Schema::hasTable('parcel_orders')) {
                $parcels = DB::table('parcel_orders')->where('id_conducteur', $refereeId)->where('status', 'completed')->get();
                $completedCount += $parcels->count();
                $totalSpend += (float)$parcels->sum('amount');
            }
        } else {
            if (Schema::hasTable('tj_requete')) {
                $rides = DB::table('tj_requete')->where('id_user_app', $refereeId)->where('statut', 'completed')->get();
                $completedCount += $rides->count();
                $totalSpend += (float)$rides->sum('montant');
            }
            if (Schema::hasTable('service_requests')) {
                $services = DB::table('service_requests')->where('user_id', $refereeId)->whereIn('status', ['Completed', 'completed'])->get();
                $completedCount += $services->count();
                $totalSpend += (float)$services->sum('amount');
            }
            if (Schema::hasTable('parcel_orders')) {
                $parcels = DB::table('parcel_orders')->where('id_user_app', $refereeId)->where('status', 'completed')->get();
                $completedCount += $parcels->count();
                $totalSpend += (float)$parcels->sum('amount');
            }
        }

        // 4. Verify Qualification Thresholds (ANY ONE condition qualifies the reward)
        $isServicesQualified = ($minServices > 0 && $completedCount >= $minServices);
        $isAmountQualified   = ($minAmount > 0 && $totalSpend >= $minAmount);

        if ($minServices > 0 && $minAmount > 0) {
            $isQualified = ($isServicesQualified || $isAmountQualified);
        } elseif ($minServices > 0) {
            $isQualified = $isServicesQualified;
        } elseif ($minAmount > 0) {
            $isQualified = $isAmountQualified;
        } else {
            // Default: qualify after at least 1 completed service
            $isQualified = ($completedCount >= 1);
        }

        if (!$isQualified) {
            return [
                'reward_processed'   => false,
                'qualified'          => false,
                'reason'             => "Neither min services ({$completedCount}/{$minServices}) nor min spend (₹{$totalSpend}/₹{$minAmount}) milestone reached",
                'completed_services' => $completedCount,
                'min_services'       => $minServices,
                'total_spend'        => $totalSpend,
                'min_amount'         => $minAmount
            ];
        }

        // 5. Calculate Reward Amount
        $rewardAmount = 0.0;
        $cleanVal = floatval(preg_replace('/[^0-9.]/', '', (string)$value));

        if (strtolower($type) === 'flat') {
            $rewardAmount = $cleanVal;
        } else {
            $rewardAmount = round(($totalSpend * $cleanVal) / 100, 2);
        }

        if ($rewardAmount <= 0) {
            return ['reward_processed' => false, 'reason' => 'Calculated reward amount is 0'];
        }

        // 6. Credit Referrer Wallet & Record Ledger Entry
        $dateNow = date('Y-m-d H:i:s');
        $ruleTitle = ($refereeType === 'driver') ? 'App Install Business' : 'App Install User';
        $partnerLabel = ($refereeType === 'driver') ? "business partner #{$refereeId}" : "user #{$refereeId}";
        $desc = "{$ruleTitle} Referral Reward (Qualified: {$completedCount} services, ₹" . number_format($totalSpend, 2) . " spend) by {$partnerLabel}";
        $note = "{$ruleTitle} Referral: #{$refereeId}";

        if ($referrerType === 'driver') {
            $currBal = (float)DB::table('tj_conducteur')->where('id', $referrerId)->value('amount');
            DB::table('tj_conducteur')->where('id', $referrerId)->update(['amount' => $currBal + $rewardAmount]);

            if (Schema::hasTable('tj_conducteur_transaction')) {
                DB::table('tj_conducteur_transaction')->insert([
                    'id_conducteur'    => $referrerId,
                    'amount'           => $rewardAmount,
                    'deduction_type'   => 'credit',
                    'payment_method'   => 'Referral Reward',
                    'payment_status'   => 'success',
                    'withdraw_status'  => 'completed',
                    'user_type'        => 'driver',
                    'receiver_user_id' => $refereeId,
                    'sender_user_type' => $refereeType,
                    'description'      => $desc,
                    'note'             => $note,
                    'type'             => 'credit',
                    'date'             => date('Y-m-d'),
                    'creer'            => $dateNow,
                    'modifier'         => $dateNow,
                ]);
            }
        } else {
            $currBal = (float)DB::table('tj_user_app')->where('id', $referrerId)->value('amount');
            DB::table('tj_user_app')->where('id', $referrerId)->update(['amount' => $currBal + $rewardAmount]);

            if (Schema::hasTable('tj_transaction')) {
                DB::table('tj_transaction')->insert([
                    'id_user_app'      => $referrerId,
                    'sender_user_id'   => $refereeId,
                    'sender_user_type' => $refereeType,
                    'amount'           => $rewardAmount,
                    'deduction_type'   => 1,
                    'user_type'        => 'customer',
                    'payment_method'   => 'Referral Reward',
                    'payment_status'   => 'success',
                    'withdraw_status'  => 'completed',
                    'description'      => $desc,
                    'type'             => 'credit',
                    'date'             => date('Y-m-d'),
                    'creer'            => $dateNow,
                    'modifier'         => $dateNow,
                ]);
            }
        }

        // 7. Mark App Install Reward as Paid in Referral Table
        if (Schema::hasTable('referral')) {
            DB::table('referral')->where('user_id', $refereeId)->update([
                'app_install_reward_paid'   => 1,
                'app_install_reward_amount' => $rewardAmount,
                'app_install_reward_date'   => $dateNow,
                'code_used'                 => 'true',
            ]);
        }

        return [
            'reward_processed'   => true,
            'qualified'          => true,
            'referrer_id'        => $referrerId,
            'referrer_type'      => $referrerType,
            'reward_amount'      => $rewardAmount,
            'completed_services' => $completedCount,
            'total_spend'        => $totalSpend,
            'rule_name'          => $ruleTitle,
        ];
    }
}

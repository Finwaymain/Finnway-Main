<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VendorTeamService
{
    /**
     * Submit Vendor Application
     */
    public static function applyForVendor(int $userId, string $userType, string $teamLocation, string $teamType, ?string $remarks = null): array
    {
        try {
            if (!Schema::hasTable('marketing_vendors')) {
                return ['success' => false, 'message' => 'Marketing vendor system is not configured.'];
            }

            $userType = self::normalizeUserType($userType);

            // Check if already applied or approved
            $existing = DB::table('marketing_vendors')
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->first();

            if ($existing) {
                if ($existing->status === 'approved') {
                    return [
                        'success'   => true,
                        'message'   => 'You are already an approved Vendor.',
                        'status'    => 'approved',
                        'vendor_id' => $existing->id,
                        'data'      => $existing,
                    ];
                }

                if ($existing->status === 'pending') {
                    return [
                        'success'   => true,
                        'message'   => 'Your vendor application is already under review.',
                        'status'    => 'pending',
                        'vendor_id' => $existing->id,
                        'data'      => $existing,
                    ];
                }

                // If rejected or suspended, update application
                DB::table('marketing_vendors')->where('id', $existing->id)->update([
                    'team_location'    => $teamLocation,
                    'team_type'        => $teamType,
                    'remarks'          => $remarks,
                    'status'           => 'pending',
                    'rejection_reason' => null,
                    'updated_at'       => now(),
                ]);

                return [
                    'success'   => true,
                    'message'   => 'Your application has been re-submitted for Admin approval.',
                    'status'    => 'pending',
                    'vendor_id' => $existing->id,
                ];

            }

            // Create new vendor application
            $vendorId = DB::table('marketing_vendors')->insertGetId([
                'user_id'            => $userId,
                'user_type'          => $userType,
                'vendor_code'        => null, // Generated upon approval
                'team_location'      => $teamLocation,
                'team_type'          => $teamType,
                'remarks'            => $remarks,
                'status'             => 'pending',
                'rate_per_customer'  => 0.00,
                'rate_per_business'  => 0.00,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Vendor application submitted successfully. Awaiting Admin review.',
                'status'  => 'pending',
                'vendor_id' => $vendorId,
            ];

        } catch (\Throwable $e) {
            Log::error("VendorTeamService::applyForVendor error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to submit application: ' . $e->getMessage()];
        }
    }

    /**
     * Admin Approves Vendor Application & Sets Custom Payout Rates
     */
    public static function approveVendor(int $vendorId, float $ratePerCustomer, float $ratePerBusiness, ?int $adminId = null): array
    {
        try {
            $vendor = DB::table('marketing_vendors')->where('id', $vendorId)->first();
            if (!$vendor) {
                return ['success' => false, 'message' => 'Vendor application not found.'];
            }

            // Generate unique Vendor Code if not already set (e.g. TM00101)
            $vendorCode = $vendor->vendor_code;
            if (empty($vendorCode)) {
                $vendorCode = self::generateUniqueCode('TM', 'marketing_vendors', 'vendor_code');
            }

            DB::table('marketing_vendors')->where('id', $vendorId)->update([
                'status'            => 'approved',
                'vendor_code'       => $vendorCode,
                'rate_per_customer' => max(0, $ratePerCustomer),
                'rate_per_business' => max(0, $ratePerBusiness),
                'approved_by'       => $adminId,
                'approved_at'       => now(),
                'rejection_reason'  => null,
                'updated_at'        => now(),
            ]);

            Log::info("VendorTeamService: Approved Vendor #{$vendorId} with code {$vendorCode}. Rates: Cust ₹{$ratePerCustomer}, Biz ₹{$ratePerBusiness}");

            return [
                'success'     => true,
                'message'     => 'Vendor approved successfully.',
                'vendor_code' => $vendorCode,
            ];

        } catch (\Throwable $e) {
            Log::error("VendorTeamService::approveVendor error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to approve vendor: ' . $e->getMessage()];
        }
    }

    /**
     * Admin Rejects Vendor Application
     */
    public static function rejectVendor(int $vendorId, string $reason, ?int $adminId = null): bool
    {
        try {
            DB::table('marketing_vendors')->where('id', $vendorId)->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'approved_by'      => $adminId,
                'updated_at'       => now(),
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error("VendorTeamService::rejectVendor error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a given code belongs to an approved Vendor (starts with TM)
     */
    public static function findApprovedVendorByCode(string $code): ?object
    {
        $code = strtoupper(trim($code));
        if (!str_starts_with($code, 'TM')) {
            return null;
        }

        if (!Schema::hasTable('marketing_vendors')) {
            return null;
        }

        return DB::table('marketing_vendors')
            ->where('vendor_code', $code)
            ->where('status', 'approved')
            ->first();
    }

    /**
     * Check if a given code belongs to an active Team Member / Freelancer (starts with FR)
     */
    public static function findActiveTeamMemberByCode(string $code): ?object
    {
        $code = strtoupper(trim($code));
        if (!str_starts_with($code, 'FR')) {
            return null;
        }

        if (!Schema::hasTable('marketing_team_members')) {
            return null;
        }

        return DB::table('marketing_team_members')
            ->where('member_code', $code)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Register a user as a Team Member under an Approved Vendor
     */
    public static function registerTeamMember(object $vendor, int $userId, string $userType): array
    {
        try {
            $userType = self::normalizeUserType($userType);

            // Check if user is already a team member under any vendor
            $existing = DB::table('marketing_team_members')
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->first();

            if ($existing) {
                return [
                    'success'     => true,
                    'member_code' => $existing->member_code,
                    'member_id'   => $existing->id,
                    'message'     => 'Already registered as Team Member.',
                ];
            }

            // Generate unique Member Code (e.g. FR10001)
            $memberCode = self::generateUniqueCode('FR', 'marketing_team_members', 'member_code');

            $memberId = DB::table('marketing_team_members')->insertGetId([
                'vendor_id'   => $vendor->id,
                'user_id'     => $userId,
                'user_type'   => $userType,
                'member_code' => $memberCode,
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            Log::info("VendorTeamService: Registered user #{$userId} ($userType) as Team Member under Vendor #{$vendor->id} with code {$memberCode}");

            return [
                'success'     => true,
                'member_code' => $memberCode,
                'member_id'   => $memberId,
                'message'     => 'Registered as Team Member successfully.',
            ];

        } catch (\Throwable $e) {
            Log::error("VendorTeamService::registerTeamMember error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Record a Marketing Acquisition (Customer or Business user joined via FR code)
     */
    public static function recordAcquisition(object $teamMember, int $acquiredUserId, string $acquiredUserType): array
    {
        try {
            $acquiredUserType = self::normalizeUserType($acquiredUserType);

            // Duplicate check
            $existing = DB::table('marketing_acquisitions')
                ->where('acquired_user_id', $acquiredUserId)
                ->where('acquired_user_type', $acquiredUserType)
                ->first();

            if ($existing) {
                return ['success' => false, 'message' => 'Acquisition already recorded.'];
            }

            $acquisitionId = DB::table('marketing_acquisitions')->insertGetId([
                'vendor_id'            => $teamMember->vendor_id,
                'team_member_id'       => $teamMember->id,
                'acquired_user_id'     => $acquiredUserId,
                'acquired_user_type'   => $acquiredUserType,
                'verification_status'  => 'pending',
                'verified_by'          => null,
                'verified_at'          => null,
                'payout_rate_applied'  => 0.00,
                'payout_status'        => 'unpaid',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            Log::info("VendorTeamService: Recorded acquisition of {$acquiredUserType} #{$acquiredUserId} under Freelancer #{$teamMember->id} (Vendor #{$teamMember->vendor_id})");

            return ['success' => true, 'acquisition_id' => $acquisitionId];

        } catch (\Throwable $e) {
            Log::error("VendorTeamService::recordAcquisition error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check role/status of a user in the Vendor/Team system
     */
    public static function getUserRoleStatus(int $userId, string $userType): array
    {
        $userType = self::normalizeUserType($userType);

        // 1. Check if Vendor
        $vendor = DB::table('marketing_vendors')
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->first();

        if ($vendor) {
            return [
                'role'             => 'vendor',
                'status'           => $vendor->status,
                'vendor_id'        => $vendor->id,
                'vendor_code'      => $vendor->vendor_code,
                'team_location'    => $vendor->team_location,
                'team_type'        => $vendor->team_type,
                'rejection_reason' => $vendor->rejection_reason,
            ];
        }

        // 2. Check if Team Member
        $member = DB::table('marketing_team_members')
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->first();

        if ($member) {
            return [
                'role'        => 'team_member',
                'status'      => $member->status,
                'member_id'   => $member->id,
                'member_code' => $member->member_code,
                'vendor_id'   => $member->vendor_id,
            ];
        }

        return [
            'role'   => 'none',
            'status' => 'none',
        ];
    }

    /**
     * Vendor Dashboard Statistics & Team Performance
     */
    public static function getVendorDashboardStats(int $userId, string $userType): ?array
    {
        $userType = self::normalizeUserType($userType);
        $vendor = DB::table('marketing_vendors')
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('status', 'approved')
            ->first();

        if (!$vendor) {
            return null;
        }

        $vendorId = $vendor->id;

        // Team members counts
        $totalMembers = DB::table('marketing_team_members')->where('vendor_id', $vendorId)->count();
        $activeMembers = DB::table('marketing_team_members')->where('vendor_id', $vendorId)->where('status', 'active')->count();
        $inactiveMembers = max(0, $totalMembers - $activeMembers);

        // Acquisitions counts
        $customerJoined = DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendorId)
            ->where('acquired_user_type', 'customer')
            ->count();

        $customerVerified = DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendorId)
            ->where('acquired_user_type', 'customer')
            ->where('verification_status', 'verified')
            ->count();

        $businessJoined = DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendorId)
            ->where('acquired_user_type', 'business')
            ->count();

        $businessVerified = DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendorId)
            ->where('acquired_user_type', 'business')
            ->where('verification_status', 'verified')
            ->count();

        $rateCustomer = (float)$vendor->rate_per_customer;
        $rateBusiness = (float)$vendor->rate_per_business;

        // Verified earnings
        $totalEarned = round(($customerVerified * $rateCustomer) + ($businessVerified * $rateBusiness), 2);

        // Paid earnings
        $paidEarned = (float)DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendorId)
            ->where('payout_status', 'paid')
            ->sum('payout_rate_applied');

        $pendingPayout = max(0, round($totalEarned - $paidEarned, 2));

        // Team members list with individual stats
        $membersRaw = DB::table('marketing_team_members')
            ->where('marketing_team_members.vendor_id', $vendorId)
            ->orderBy('marketing_team_members.id', 'desc')
            ->get();

        $teamMembers = [];
        foreach ($membersRaw as $m) {
            $name = 'Freelancer';
            $phone = '';
            if ($m->user_type === 'customer') {
                $u = DB::table('tj_user_app')->where('id', $m->user_id)->select('prenom', 'nom', 'phone')->first();
                if ($u) {
                    $name = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Freelancer';
                    $phone = $u->phone ?? '';
                }
            } else {
                $d = DB::table('tj_conducteur')->where('id', $m->user_id)->select('prenom', 'nom', 'phone')->first();
                if ($d) {
                    $name = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Freelancer';
                    $phone = $d->phone ?? '';
                }
            }

            $mCustTotal = DB::table('marketing_acquisitions')
                ->where('team_member_id', $m->id)
                ->where('acquired_user_type', 'customer')
                ->count();

            $mCustVer = DB::table('marketing_acquisitions')
                ->where('team_member_id', $m->id)
                ->where('acquired_user_type', 'customer')
                ->where('verification_status', 'verified')
                ->count();

            $mBizTotal = DB::table('marketing_acquisitions')
                ->where('team_member_id', $m->id)
                ->where('acquired_user_type', 'business')
                ->count();

            $mBizVer = DB::table('marketing_acquisitions')
                ->where('team_member_id', $m->id)
                ->where('acquired_user_type', 'business')
                ->where('verification_status', 'verified')
                ->count();

            $teamMembers[] = [
                'member_id'           => $m->id,
                'member_code'         => $m->member_code,
                'name'                => $name,
                'phone'               => $phone,
                'status'              => $m->status,
                'joined_at'           => Carbon::parse($m->created_at)->format('d M Y'),
                'customers_total'     => $mCustTotal,
                'customers_verified'  => $mCustVer,
                'businesses_total'    => $mBizTotal,
                'businesses_verified' => $mBizVer,
            ];
        }

        return [
            'vendor_id'           => $vendor->id,
            'vendor_code'         => $vendor->vendor_code,
            'team_location'       => $vendor->team_location,
            'team_type'           => $vendor->team_type,
            'rate_per_customer'   => number_format($rateCustomer, 2, '.', ''),
            'rate_per_business'   => number_format($rateBusiness, 2, '.', ''),
            'total_members'       => $totalMembers,
            'freelancers_count'   => $totalMembers,
            'active_members'      => $activeMembers,
            'inactive_members'    => $inactiveMembers,
            'customer_joined'     => $customerJoined,
            'total_customers'     => $customerJoined,
            'customer_verified'   => $customerVerified,
            'verified_customers'  => $customerVerified,
            'business_joined'     => $businessJoined,
            'total_businesses'    => $businessJoined,
            'business_verified'   => $businessVerified,
            'verified_businesses' => $businessVerified,
            'total_earned'        => number_format($totalEarned, 2, '.', ''),
            'total_earnings'      => number_format($totalEarned, 2, '.', ''),
            'paid_earned'         => number_format($paidEarned, 2, '.', ''),
            'paid_earnings'       => number_format($paidEarned, 2, '.', ''),
            'pending_payout'      => number_format($pendingPayout, 2, '.', ''),
            'team_members'        => $teamMembers,
        ];
    }

    /**
     * Team Member Dashboard Statistics & Acquisitions Detail
     */
    public static function getTeamMemberDashboardStats(int $userId, string $userType): ?array
    {
        $userType = self::normalizeUserType($userType);
        $member = DB::table('marketing_team_members')
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->first();

        if (!$member) {
            return null;
        }

        $vendor = DB::table('marketing_vendors')->where('id', $member->vendor_id)->first();

        $customerTotal = DB::table('marketing_acquisitions')
            ->where('team_member_id', $member->id)
            ->where('acquired_user_type', 'customer')
            ->count();

        $businessTotal = DB::table('marketing_acquisitions')
            ->where('team_member_id', $member->id)
            ->where('acquired_user_type', 'business')
            ->count();

        $acquisitionsRaw = DB::table('marketing_acquisitions')
            ->where('team_member_id', $member->id)
            ->orderBy('id', 'desc')
            ->get();

        $recentAcquisitions = [];
        foreach ($acquisitionsRaw as $acq) {
            $name = $acq->acquired_user_type === 'business' ? 'Business Partner' : 'Customer User';
            $phone = '';
            $kyc = 'Pending';

            if ($acq->acquired_user_type === 'customer') {
                $u = DB::table('tj_user_app')->where('id', $acq->acquired_user_id)->first();
                if ($u) {
                    $name = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Customer User';
                    $phone = $u->phone ?? '';
                    $kyc = ($u->statut_nic === 'yes') ? 'Verified' : 'Pending';
                }
            } else {
                $d = DB::table('tj_conducteur')->where('id', $acq->acquired_user_id)->first();
                if ($d) {
                    $name = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Business Partner';
                    $phone = $d->phone ?? '';
                    $kyc = ($d->is_verified == 1) ? 'Verified' : 'Pending';
                }
            }

            $maskedPhone = '';
            if (!empty($phone)) {
                $digits = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($digits) >= 10) {
                    $maskedPhone = substr($digits, 0, 3) . '****' . substr($digits, -3);
                } else {
                    $maskedPhone = $phone;
                }
            }

            $recentAcquisitions[] = [
                'id'                  => $acq->id,
                'user_name'           => $name,
                'phone'               => $maskedPhone,
                'user_type'           => $acq->acquired_user_type,
                'user_type_label'     => $acq->acquired_user_type === 'business' ? 'Business Driver' : 'Customer',
                'kyc_status'          => $kyc,
                'verification_status' => $acq->verification_status,
                'joined_date'         => Carbon::parse($acq->created_at)->format('d M Y, h:i A'),
            ];
        }

        return [
            'member_id'                => $member->id,
            'member_code'              => $member->member_code,
            'status'                   => $member->status,
            'team_location'            => $vendor->team_location ?? 'Regional Territory',
            'team_type'                => $vendor->team_type ?? 'Field Marketing',
            'customer_joined'          => $customerTotal,
            'acquired_customers_count' => $customerTotal,
            'business_joined'          => $businessTotal,
            'acquired_businesses_count'=> $businessTotal,
            'total_acquisitions'       => $customerTotal + $businessTotal,
            'total_acquisitions_count' => $customerTotal + $businessTotal,
            'recent_acquisitions'      => $recentAcquisitions,
            'joined_at'                => Carbon::parse($member->created_at)->format('d M Y'),
        ];
    }

    /**
     * Admin Verifies an Acquisition
     */
    public static function verifyAcquisition(int $acquisitionId, ?int $adminId = null): bool
    {
        try {
            $acq = DB::table('marketing_acquisitions')->where('id', $acquisitionId)->first();
            if (!$acq || $acq->verification_status === 'verified') {
                return false;
            }

            $vendor = DB::table('marketing_vendors')->where('id', $acq->vendor_id)->first();
            $rate = 0.00;
            if ($vendor) {
                $rate = $acq->acquired_user_type === 'business'
                    ? (float)$vendor->rate_per_business
                    : (float)$vendor->rate_per_customer;
            }

            DB::table('marketing_acquisitions')->where('id', $acquisitionId)->update([
                'verification_status' => 'verified',
                'verified_by'         => $adminId,
                'verified_at'         => now(),
                'payout_rate_applied' => $rate,
                'rejection_reason'    => null,
                'updated_at'          => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("VendorTeamService::verifyAcquisition error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin Rejects an Acquisition
     */
    public static function rejectAcquisition(int $acquisitionId, string $reason, ?int $adminId = null): bool
    {
        try {
            DB::table('marketing_acquisitions')->where('id', $acquisitionId)->update([
                'verification_status' => 'rejected',
                'rejection_reason'    => $reason,
                'verified_by'         => $adminId,
                'payout_rate_applied' => 0.00,
                'updated_at'          => now(),
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error("VendorTeamService::rejectAcquisition error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique sequential code with prefix (e.g. TM00101, FR10001)
     */
    private static function generateUniqueCode(string $prefix, string $table, string $column): string
    {
        $maxAttempts = 50;
        $startNum = ($prefix === 'TM') ? 100 : 1000;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $count = DB::table($table)->count();
            $num = $startNum + $count + $i + 1;
            $code = $prefix . str_pad((string)$num, 5, '0', STR_PAD_LEFT);

            $exists = DB::table($table)->where($column, $code)->exists();
            if (!$exists) {
                return $code;
            }
        }

        // Fallback with random suffix
        return $prefix . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 6));
    }

    private static function normalizeUserType(string $type): string
    {
        $t = strtolower(trim($type));
        return in_array($t, ['driver', 'conducteur', 'business', 'provider'], true) ? 'business' : 'customer';
    }
}

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

        $customerPending = DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendorId)
            ->where('acquired_user_type', 'customer')
            ->where('verification_status', 'pending')
            ->count();

        $customerRejected = DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendorId)
            ->where('acquired_user_type', 'customer')
            ->where('verification_status', 'rejected')
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

        $businessPending = DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendorId)
            ->where('acquired_user_type', 'business')
            ->where('verification_status', 'pending')
            ->count();

        $businessRejected = DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendorId)
            ->where('acquired_user_type', 'business')
            ->where('verification_status', 'rejected')
            ->count();

        $totalVerified = $customerVerified + $businessVerified;
        $totalPending  = $customerPending + $businessPending;
        $totalRejected = $customerRejected + $businessRejected;
        $totalInstall  = $customerJoined + $businessJoined;

        $rateCustomer = (float)$vendor->rate_per_customer;
        $rateBusiness = (float)$vendor->rate_per_business;

        // Verified Due & Upcoming Income
        $customerDue = round($customerVerified * $rateCustomer, 2);
        $businessDue = round($businessVerified * $rateBusiness, 2);
        $totalVerifiedDue = round($customerDue + $businessDue, 2);

        $customerUpcoming = round($customerPending * $rateCustomer, 2);
        $businessUpcoming = round($businessPending * $rateBusiness, 2);
        $totalUpcomingIncome = round($customerUpcoming + $businessUpcoming, 2);

        // Verified earnings
        $totalEarned = $totalVerifiedDue;

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
            $photo = null;
            $zone = $vendor->team_location ?? 'DELHI';

            if ($m->user_type === 'customer') {
                $u = DB::table('tj_user_app')->where('id', $m->user_id)->first();
                if ($u) {
                    $name = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Freelancer';
                    $phone = $u->phone ?? '';
                    $photo = $u->photo_path ?? null;
                }
            } else {
                $d = DB::table('tj_conducteur')->where('id', $m->user_id)->first();
                if ($d) {
                    $name = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Freelancer';
                    $phone = $d->phone ?? '';
                    $photo = $d->photo_path ?? null;
                }
            }

            $acqsRaw = DB::table('marketing_acquisitions')
                ->where('team_member_id', $m->id)
                ->orderBy('id', 'desc')
                ->get();

            $acquisitions = [];
            $mCustTotal = 0;
            $mCustVer = 0;
            $mCustPend = 0;
            $mCustRej = 0;
            $mBizTotal = 0;
            $mBizVer = 0;
            $mBizPend = 0;
            $mBizRej = 0;
            $verCount = 0;
            $pendCount = 0;
            $rejCount = 0;

            foreach ($acqsRaw as $acq) {
                if ($acq->acquired_user_type === 'customer') {
                    $mCustTotal++;
                    if ($acq->verification_status === 'verified') {
                        $mCustVer++;
                    } elseif ($acq->verification_status === 'rejected') {
                        $mCustRej++;
                    } else {
                        $mCustPend++;
                    }
                } else {
                    $mBizTotal++;
                    if ($acq->verification_status === 'verified') {
                        $mBizVer++;
                    } elseif ($acq->verification_status === 'rejected') {
                        $mBizRej++;
                    } else {
                        $mBizPend++;
                    }
                }

                if ($acq->verification_status === 'verified') {
                    $verCount++;
                } elseif ($acq->verification_status === 'rejected') {
                    $rejCount++;
                } else {
                    $pendCount++;
                }

                $acqName = $acq->acquired_user_type === 'business' ? 'Partner Driver' : 'Customer User';
                $acqPhone = '';
                $acqZone = $zone;

                if ($acq->acquired_user_type === 'customer') {
                    $au = DB::table('tj_user_app')->where('id', $acq->acquired_user_id)->first();
                    if ($au) {
                        $acqName = trim(($au->prenom ?? '') . ' ' . ($au->nom ?? '')) ?: 'Customer User';
                        $acqPhone = $au->phone ?? '';
                    }
                } else {
                    $ad = DB::table('tj_conducteur')->where('id', $acq->acquired_user_id)->first();
                    if ($ad) {
                        $acqName = trim(($ad->prenom ?? '') . ' ' . ($ad->nom ?? '')) ?: 'Partner Driver';
                        $acqPhone = $ad->phone ?? '';
                    }
                }

                // Masking in format: 888XXXX231
                $maskedPhone = '';
                if (!empty($acqPhone)) {
                    $digits = preg_replace('/[^0-9]/', '', $acqPhone);
                    if (strlen($digits) >= 10) {
                        $maskedPhone = substr($digits, 0, 3) . 'XXXX' . substr($digits, -3);
                    } else {
                        $maskedPhone = $acqPhone;
                    }
                }

                // 72-hour countdown for pending verification
                $hoursLeft = null;
                if ($acq->verification_status === 'pending') {
                    $createdTime = Carbon::parse($acq->created_at);
                    $deadline = $createdTime->copy()->addHours(72);
                    $diffHours = now()->diffInHours($deadline, false);
                    $hoursLeft = max(0, (int)$diffHours);
                }

                $acquisitions[] = [
                    'id'                  => $acq->id,
                    'name'                => $acqName,
                    'phone'               => $maskedPhone,
                    'zone'                => $acqZone,
                    'date'                => Carbon::parse($acq->created_at)->format('d-m-Y'),
                    'user_type'           => $acq->acquired_user_type,
                    'verification_status' => $acq->verification_status,
                    'hours_left'          => $hoursLeft,
                    'rejection_reason'    => $acq->rejection_reason ?? $acq->remarks ?? null,
                ];
            }

            $mEarnings = round(($mCustVer * $rateCustomer) + ($mBizVer * $rateBusiness), 2);
            $mPendingEarnings = round(($mCustPend * $rateCustomer) + ($mBizPend * $rateBusiness), 2);

            $teamMembers[] = [
                'member_id'           => $m->id,
                'member_code'         => $m->member_code,
                'name'                => $name,
                'phone'               => $phone,
                'photo'               => $photo,
                'zone'                => $zone,
                'status'              => $m->status,
                'joined_at'           => Carbon::parse($m->created_at)->format('d M Y'),
                'total_users'         => $verCount + $pendCount + $rejCount,
                'verified_count'      => $verCount,
                'pending_count'       => $pendCount,
                'rejected_count'      => $rejCount,
                'customers_total'     => $mCustTotal,
                'customers_verified'  => $mCustVer,
                'customers_pending'   => $mCustPend,
                'customers_rejected'  => $mCustRej,
                'businesses_total'    => $mBizTotal,
                'businesses_verified' => $mBizVer,
                'businesses_pending'  => $mBizPend,
                'businesses_rejected' => $mBizRej,
                'total_earnings'      => $mEarnings,
                'verified_earnings'   => $mEarnings,
                'pending_earnings'    => $mPendingEarnings,
                'acquisitions'        => $acquisitions,
            ];
        }

        return [
            'vendor_id'            => $vendor->id,
            'vendor_code'          => $vendor->vendor_code,
            'team_location'        => $vendor->team_location,
            'team_type'            => $vendor->team_type,
            'rate_per_customer'    => number_format($rateCustomer, 2, '.', ''),
            'rate_per_business'    => number_format($rateBusiness, 2, '.', ''),
            'total_members'        => $totalMembers,
            'freelancers_count'    => $totalMembers,
            'active_members'       => $activeMembers,
            'inactive_members'     => $inactiveMembers,
            'customer_joined'      => $customerJoined,
            'total_customers'      => $customerJoined,
            'customer_verified'    => $customerVerified,
            'verified_customers'   => $customerVerified,
            'customer_pending'     => $customerPending,
            'customer_rejected'    => $customerRejected,
            'business_joined'      => $businessJoined,
            'total_businesses'     => $businessJoined,
            'business_verified'    => $businessVerified,
            'verified_businesses'  => $businessVerified,
            'business_pending'     => $businessPending,
            'business_rejected'    => $businessRejected,
            'total_verified'       => $totalVerified,
            'total_pending'        => $totalPending,
            'total_rejected'       => $totalRejected,
            'total_install'        => $totalInstall,
            'customer_due'         => number_format($customerDue, 2, '.', ''),
            'business_due'         => number_format($businessDue, 2, '.', ''),
            'total_verified_due'   => number_format($totalVerifiedDue, 2, '.', ''),
            'customer_upcoming'    => number_format($customerUpcoming, 2, '.', ''),
            'business_upcoming'    => number_format($businessUpcoming, 2, '.', ''),
            'total_upcoming_income'=> number_format($totalUpcomingIncome, 2, '.', ''),
            'total_earned'         => number_format($totalEarned, 2, '.', ''),
            'total_earnings'       => number_format($totalEarned, 2, '.', ''),
            'paid_earned'          => number_format($paidEarned, 2, '.', ''),
            'paid_earnings'        => number_format($paidEarned, 2, '.', ''),
            'pending_payout'       => number_format($pendingPayout, 2, '.', ''),
            'team_members'         => $teamMembers,
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

        $customerVerified = DB::table('marketing_acquisitions')
            ->where('team_member_id', $member->id)
            ->where('acquired_user_type', 'customer')
            ->where('verification_status', 'verified')
            ->count();

        $customerPending = DB::table('marketing_acquisitions')
            ->where('team_member_id', $member->id)
            ->where('acquired_user_type', 'customer')
            ->where('verification_status', 'pending')
            ->count();

        $customerRejected = DB::table('marketing_acquisitions')
            ->where('team_member_id', $member->id)
            ->where('acquired_user_type', 'customer')
            ->where('verification_status', 'rejected')
            ->count();

        $businessTotal = DB::table('marketing_acquisitions')
            ->where('team_member_id', $member->id)
            ->where('acquired_user_type', 'business')
            ->count();

        $businessVerified = DB::table('marketing_acquisitions')
            ->where('team_member_id', $member->id)
            ->where('acquired_user_type', 'business')
            ->where('verification_status', 'verified')
            ->count();

        $businessPending = DB::table('marketing_acquisitions')
            ->where('team_member_id', $member->id)
            ->where('acquired_user_type', 'business')
            ->where('verification_status', 'pending')
            ->count();

        $businessRejected = DB::table('marketing_acquisitions')
            ->where('team_member_id', $member->id)
            ->where('acquired_user_type', 'business')
            ->where('verification_status', 'rejected')
            ->count();

        $totalVerified = $customerVerified + $businessVerified;
        $totalPending  = $customerPending + $businessPending;
        $totalRejected = $customerRejected + $businessRejected;
        $totalUsers    = $customerTotal + $businessTotal;

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
                    $maskedPhone = substr($digits, 0, 3) . 'XXXX' . substr($digits, -3);
                } else {
                    $maskedPhone = $phone;
                }
            }

            // 72-hour countdown for pending verification
            $hoursLeft = null;
            if ($acq->verification_status === 'pending') {
                $createdTime = Carbon::parse($acq->created_at);
                $deadline = $createdTime->copy()->addHours(72);
                $diffHours = now()->diffInHours($deadline, false);
                $hoursLeft = max(0, (int)$diffHours);
            }

            $recentAcquisitions[] = [
                'id'                  => $acq->id,
                'user_name'           => $name,
                'phone'               => $maskedPhone,
                'zone'                => $vendor->team_location ?? 'DELHI',
                'user_type'           => $acq->acquired_user_type,
                'user_type_label'     => $acq->acquired_user_type === 'business' ? 'Business Driver' : 'Customer',
                'kyc_status'          => $kyc,
                'verification_status' => $acq->verification_status,
                'hours_left'          => $hoursLeft,
                'rejection_reason'    => $acq->rejection_reason ?? $acq->remarks ?? null,
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
            'customer_verified'        => $customerVerified,
            'customer_pending'         => $customerPending,
            'customer_rejected'        => $customerRejected,
            'business_joined'          => $businessTotal,
            'acquired_businesses_count'=> $businessTotal,
            'business_verified'        => $businessVerified,
            'business_pending'         => $businessPending,
            'business_rejected'        => $businessRejected,
            'total_acquisitions'       => $totalUsers,
            'total_acquisitions_count' => $totalUsers,
            'total_users'              => $totalUsers,
            'total_verified'           => $totalVerified,
            'total_pending'            => $totalPending,
            'total_rejected'           => $totalRejected,
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

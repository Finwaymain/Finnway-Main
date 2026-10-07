<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VendorTeamService
{
    /**
     * Submit Top-Level Head Vendor Application (to Company Admin)
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
                'parent_vendor_id'   => null,
                'head_vendor_id'     => null,
                'vendor_code'        => null, // Generated upon approval
                'designation'        => 'Head Vendor',
                'hierarchy_level'    => 0,
                'team_location'      => $teamLocation,
                'team_type'          => $teamType,
                'remarks'            => $remarks,
                'status'             => 'pending',
                'rate_per_customer'  => 0.00,
                'rate_per_business'  => 0.00,
                'is_rate_visible'    => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            return [
                'success'   => true,
                'message'   => 'Vendor application submitted successfully. Awaiting Admin review.',
                'status'    => 'pending',
                'vendor_id' => $vendorId,
            ];

        } catch (\Throwable $e) {
            Log::error("VendorTeamService::applyForVendor error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to submit application: ' . $e->getMessage()];
        }
    }

    /**
     * Unified Join via Vendor Code: Auto-map to Sub-Vendor or Freelancer
     */
    public static function applyWithVendorCode(
        int $userId,
        string $userType,
        string $vendorCode,
        string $roleType = 'freelancer', // 'sub_vendor' or 'freelancer'
        ?string $designation = null,
        ?string $teamLocation = null,
        ?string $teamType = null,
        ?string $remarks = null
    ): array {
        try {
            $userType = self::normalizeUserType($userType);
            $codeUpper = strtoupper(trim($vendorCode));

            // Auto-detect role from code prefix: SV -> sub_vendor, FR -> freelancer
            if (str_starts_with($codeUpper, 'SV')) {
                $roleType = 'sub_vendor';
            } elseif (str_starts_with($codeUpper, 'FR')) {
                $roleType = 'freelancer';
            }

            $parentVendor = self::findApprovedVendorByCode($vendorCode);

            if (!$parentVendor) {
                return ['success' => false, 'message' => 'Invalid or unapproved Vendor Code. Please check the code and try again.'];
            }

            // Prevent user applying under themselves
            if ((int)$parentVendor->user_id === $userId && $parentVendor->user_type === $userType) {
                return ['success' => false, 'message' => 'You cannot apply under your own vendor code.'];
            }

            if ($roleType === 'sub_vendor') {
                // Check if user is already an approved or pending vendor
                $existingVendor = DB::table('marketing_vendors')
                    ->where('user_id', $userId)
                    ->where('user_type', $userType)
                    ->first();

                if ($existingVendor) {
                    if ($existingVendor->status === 'approved') {
                        return [
                            'success'   => true,
                            'status'    => 'approved',
                            'role'      => 'vendor',
                            'message'   => "You are already an approved Vendor (Code: {$existingVendor->vendor_code}).",
                        ];
                    }
                    if ($existingVendor->status === 'pending') {
                        return [
                            'success'   => true,
                            'status'    => 'pending',
                            'role'      => 'sub_vendor',
                            'message'   => 'Your Sub-Vendor application is already pending approval.',
                        ];
                    }
                }

                $parentHeadId = $parentVendor->head_vendor_id ?? $parentVendor->id;
                $level = ((int)($parentVendor->hierarchy_level ?? 0)) + 1;
                $cleanDesignation = trim((string)$designation) ?: 'Sub-Vendor';
                $location = trim((string)$teamLocation) ?: ($parentVendor->team_location ?? 'Territory');
                $type = trim((string)$teamType) ?: ($parentVendor->team_type ?? 'Field Marketing');

                $subVendorId = DB::table('marketing_vendors')->insertGetId([
                    'user_id'            => $userId,
                    'user_type'          => $userType,
                    'parent_vendor_id'   => $parentVendor->id,
                    'head_vendor_id'     => $parentHeadId,
                    'vendor_code'        => null, // Generated upon approval by parent
                    'designation'        => $cleanDesignation,
                    'hierarchy_level'    => $level,
                    'team_location'      => $location,
                    'team_type'          => $type,
                    'remarks'            => $remarks,
                    'status'             => 'pending',
                    'rate_per_customer'  => 0.00,
                    'rate_per_business'  => 0.00,
                    'is_rate_visible'    => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                Log::info("VendorTeamService: User #{$userId} ($userType) applied as Sub-Vendor under Parent Vendor #{$parentVendor->id} ($vendorCode)");

                return [
                    'success'       => true,
                    'status'        => 'pending',
                    'role'          => 'sub_vendor',
                    'sub_vendor_id' => $subVendorId,
                    'parent_code'   => $parentVendor->vendor_code,
                    'message'       => 'Sub-Vendor application submitted to Parent Vendor for approval.',
                ];

            } else {
                // Register as Freelancer / Team Member
                $reg = self::registerTeamMember($parentVendor, $userId, $userType);
                if (empty($reg['success'])) {
                    return $reg;
                }

                return [
                    'success'     => true,
                    'status'      => 'active',
                    'role'        => 'team_member',
                    'member_code' => $reg['member_code'],
                    'member_id'   => $reg['member_id'],
                    'message'     => "Vendor code applied! You are now joined as a Freelancer with code {$reg['member_code']}.",
                ];
            }

        } catch (\Throwable $e) {
            Log::error("VendorTeamService::applyWithVendorCode error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Parent Vendor Approves Sub-Vendor with Rates, Designation, and Rate Visibility Toggle
     */
    public static function approveSubVendor(
        int $subVendorId,
        int $approverUserId,
        string $approverUserType,
        float $ratePerCustomer,
        float $ratePerBusiness,
        ?string $designation = null,
        bool $isRateVisible = true
    ): array {
        try {
            $approverUserType = self::normalizeUserType($approverUserType);

            // Find approver's vendor profile
            $parentVendor = DB::table('marketing_vendors')
                ->where('user_id', $approverUserId)
                ->where('user_type', $approverUserType)
                ->where('status', 'approved')
                ->first();

            if (!$parentVendor) {
                return ['success' => false, 'message' => 'You do not have an approved Vendor account to approve Sub-Vendors.'];
            }

            // Find sub-vendor application
            $subVendor = DB::table('marketing_vendors')->where('id', $subVendorId)->first();
            if (!$subVendor) {
                return ['success' => false, 'message' => 'Sub-Vendor application not found.'];
            }

            // Verify hierarchy ownership: must be the direct parent or head vendor
            if ((int)$subVendor->parent_vendor_id !== (int)$parentVendor->id && (int)$subVendor->head_vendor_id !== (int)$parentVendor->id) {
                return ['success' => false, 'message' => 'You are not authorized to approve this Sub-Vendor application.'];
            }

            // Rate Ceiling Enforcement: cannot exceed parent's own effective rates
            $parentCustRate = (float)$parentVendor->rate_per_customer;
            $parentBizRate  = (float)$parentVendor->rate_per_business;

            if ($ratePerCustomer > $parentCustRate) {
                return [
                    'success' => false,
                    'message' => "Customer rate cannot exceed your rate of ₹" . number_format($parentCustRate, 2) . ".",
                ];
            }

            if ($ratePerBusiness > $parentBizRate) {
                return [
                    'success' => false,
                    'message' => "Business rate cannot exceed your rate of ₹" . number_format($parentBizRate, 2) . ".",
                ];
            }

            // Generate VR code if not set
            $code = $subVendor->vendor_code;
            if (empty($code)) {
                $code = self::generateUniqueCode('VR', 'marketing_vendors', 'vendor_code');
            }
            $subVendorCode  = 'SV' . substr($code, 2);
            $freelancerCode = 'FR' . substr($code, 2);

            $finalDesignation = trim((string)$designation) ?: ($subVendor->designation ?: 'Sub-Vendor');

            DB::table('marketing_vendors')->where('id', $subVendorId)->update([
                'status'                => 'approved',
                'vendor_code'           => $code,
                'sub_vendor_code'       => $subVendorCode,
                'freelancer_code'       => $freelancerCode,
                'designation'           => $finalDesignation,
                'rate_per_customer'     => max(0, $ratePerCustomer),
                'rate_per_business'     => max(0, $ratePerBusiness),
                'is_rate_visible'       => (bool)$isRateVisible,
                'approved_by_vendor_id' => $parentVendor->id,
                'approved_at'           => now(),
                'rejection_reason'      => null,
                'updated_at'            => now(),
            ]);

            Log::info("VendorTeamService: Parent Vendor #{$parentVendor->id} approved Sub-Vendor #{$subVendorId} with code {$code}, SV: {$subVendorCode}, FR: {$freelancerCode}, designation '{$finalDesignation}', Cust ₹{$ratePerCustomer}, Biz ₹{$ratePerBusiness}, Visible: " . ($isRateVisible ? 'ON' : 'OFF'));

            return [
                'success'          => true,
                'message'          => 'Sub-Vendor approved successfully.',
                'vendor_code'      => $code,
                'sub_vendor_code'  => $subVendorCode,
                'freelancer_code'  => $freelancerCode,
                'designation'      => $finalDesignation,
                'rate_customer'    => $ratePerCustomer,
                'rate_business'    => $ratePerBusiness,
                'is_rate_visible'  => (bool)$isRateVisible,
            ];

        } catch (\Throwable $e) {
            Log::error("VendorTeamService::approveSubVendor error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Parent Vendor Rejects Sub-Vendor Application
     */
    public static function rejectSubVendor(int $subVendorId, int $approverUserId, string $approverUserType, string $reason): array
    {
        try {
            $approverUserType = self::normalizeUserType($approverUserType);
            $parentVendor = DB::table('marketing_vendors')
                ->where('user_id', $approverUserId)
                ->where('user_type', $approverUserType)
                ->first();

            if (!$parentVendor) {
                return ['success' => false, 'message' => 'Unauthorized approver.'];
            }

            $subVendor = DB::table('marketing_vendors')->where('id', $subVendorId)->first();
            if (!$subVendor || ((int)$subVendor->parent_vendor_id !== (int)$parentVendor->id && (int)$subVendor->head_vendor_id !== (int)$parentVendor->id)) {
                return ['success' => false, 'message' => 'Unauthorized to reject this sub-vendor.'];
            }

            DB::table('marketing_vendors')->where('id', $subVendorId)->update([
                'status'                => 'rejected',
                'rejection_reason'      => $reason,
                'approved_by_vendor_id' => $parentVendor->id,
                'updated_at'            => now(),
            ]);

            return ['success' => true, 'message' => 'Sub-Vendor application rejected.'];

        } catch (\Throwable $e) {
            Log::error("VendorTeamService::rejectSubVendor error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Parent Vendor Toggles Rate Visibility ON/OFF for Sub-Vendor
     */
    public static function toggleSubVendorRateVisibility(int $subVendorId, int $parentUserId, string $parentUserType, bool $visible): array
    {
        try {
            $parentUserType = self::normalizeUserType($parentUserType);
            $parentVendor = DB::table('marketing_vendors')
                ->where('user_id', $parentUserId)
                ->where('user_type', $parentUserType)
                ->first();

            if (!$parentVendor) {
                return ['success' => false, 'message' => 'Unauthorized.'];
            }

            $subVendor = DB::table('marketing_vendors')->where('id', $subVendorId)->first();
            if (!$subVendor || ((int)$subVendor->parent_vendor_id !== (int)$parentVendor->id && (int)$subVendor->head_vendor_id !== (int)$parentVendor->id)) {
                return ['success' => false, 'message' => 'Unauthorized to modify this Sub-Vendor.'];
            }

            DB::table('marketing_vendors')->where('id', $subVendorId)->update([
                'is_rate_visible' => (bool)$visible,
                'updated_at'      => now(),
            ]);

            return [
                'success'         => true,
                'is_rate_visible' => (bool)$visible,
                'message'         => 'Rate visibility updated successfully.',
            ];

        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get Pending Sub-Vendor Requests for a Vendor
     */
    public static function getPendingSubVendors(int $vendorId): array
    {
        $requests = DB::table('marketing_vendors')
            ->where('parent_vendor_id', $vendorId)
            ->where('status', 'pending')
            ->orderBy('id', 'desc')
            ->get();

        $list = [];
        foreach ($requests as $r) {
            $name = 'Applicant';
            $phone = '';
            $email = '';

            if ($r->user_type === 'customer') {
                $u = DB::table('tj_user_app')->where('id', $r->user_id)->first();
                if ($u) {
                    $name = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Consumer';
                    $phone = $u->phone ?? '';
                    $email = $u->email ?? '';
                }
            } else {
                $d = DB::table('tj_conducteur')->where('id', $r->user_id)->first();
                if ($d) {
                    $name = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Partner';
                    $phone = $d->phone ?? '';
                    $email = $d->email ?? '';
                }
            }

            $list[] = [
                'id'            => $r->id,
                'user_id'       => $r->user_id,
                'user_type'     => $r->user_type,
                'name'          => $name,
                'phone'         => $phone,
                'email'         => $email,
                'designation'   => $r->designation ?: 'Sub-Vendor',
                'team_location' => $r->team_location,
                'team_type'     => $r->team_type,
                'remarks'       => $r->remarks,
                'applied_at'    => Carbon::parse($r->created_at)->format('d M Y, h:i A'),
            ];
        }

        return $list;
    }

    /**
     * Get All Recursive Downline Sub-Vendor IDs for a given Vendor
     */
    public static function getDownlineVendorIds(int $vendorId): array
    {
        $ids = [];
        $queue = [$vendorId];

        while (!empty($queue)) {
            $currentId = array_shift($queue);
            $children = DB::table('marketing_vendors')
                ->where('parent_vendor_id', $currentId)
                ->where('status', 'approved')
                ->pluck('id')
                ->toArray();

            foreach ($children as $cId) {
                if (!in_array($cId, $ids, true)) {
                    $ids[] = $cId;
                    $queue[] = $cId;
                }
            }
        }

        return $ids;
    }

    /**
     * Admin Approves Top-Level Vendor Application & Sets Master Rates
     */
    public static function approveVendor(int $vendorId, float $ratePerCustomer, float $ratePerBusiness, ?int $adminId = null): array
    {
        try {
            $vendor = DB::table('marketing_vendors')->where('id', $vendorId)->first();
            if (!$vendor) {
                return ['success' => false, 'message' => 'Vendor application not found.'];
            }

            // Generate VR code (VR10001...)
            $vendorCode = $vendor->vendor_code;
            if (empty($vendorCode)) {
                $vendorCode = self::generateUniqueCode('VR', 'marketing_vendors', 'vendor_code');
            }
            $subVendorCode  = 'SV' . substr($vendorCode, 2);
            $freelancerCode = 'FR' . substr($vendorCode, 2);

            DB::table('marketing_vendors')->where('id', $vendorId)->update([
                'status'            => 'approved',
                'vendor_code'       => $vendorCode,
                'sub_vendor_code'   => $subVendorCode,
                'freelancer_code'   => $freelancerCode,
                'rate_per_customer' => max(0, $ratePerCustomer),
                'rate_per_business' => max(0, $ratePerBusiness),
                'is_rate_visible'   => true,
                'approved_by'       => $adminId,
                'approved_at'       => now(),
                'rejection_reason'  => null,
                'updated_at'        => now(),
            ]);

            Log::info("VendorTeamService: Approved Vendor #{$vendorId} with code {$vendorCode}, SV: {$subVendorCode}, FR: {$freelancerCode}. Master Rates: Cust ₹{$ratePerCustomer}, Biz ₹{$ratePerBusiness}");

            return [
                'success'         => true,
                'message'         => 'Vendor approved successfully.',
                'vendor_code'     => $vendorCode,
                'sub_vendor_code' => $subVendorCode,
                'freelancer_code' => $freelancerCode,
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
     * Check if code belongs to an approved Vendor (starts with VR, TM, SV, or FR)
     */
    public static function findApprovedVendorByCode(string $code): ?object
    {
        $code = strtoupper(trim($code));
        if (empty($code)) {
            return null;
        }

        if (!Schema::hasTable('marketing_vendors')) {
            return null;
        }

        // 1. Direct match on vendor_code, sub_vendor_code, or freelancer_code
        $vendor = DB::table('marketing_vendors')
            ->where('status', 'approved')
            ->where(function($q) use ($code) {
                $q->where('vendor_code', $code);
                if (Schema::hasColumn('marketing_vendors', 'sub_vendor_code')) {
                    $q->orWhere('sub_vendor_code', $code);
                }
                if (Schema::hasColumn('marketing_vendors', 'freelancer_code')) {
                    $q->orWhere('freelancer_code', $code);
                }
            })
            ->first();

        if ($vendor) {
            return $vendor;
        }

        // 2. If code starts with SV or FR, map to VR / TM
        if (str_starts_with($code, 'SV') || str_starts_with($code, 'FR')) {
            $numPart = substr($code, 2);
            $vrCode  = 'VR' . $numPart;
            $tmCode  = 'TM' . $numPart;

            return DB::table('marketing_vendors')
                ->where('status', 'approved')
                ->where(function($q) use ($vrCode, $tmCode) {
                    $q->where('vendor_code', $vrCode)
                      ->orWhere('vendor_code', $tmCode);
                })
                ->first();
        }

        return null;
    }

    /**
     * Check if code belongs to an active Team Member / Freelancer (starts with FR)
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
     * Resolve user phone number across tj_conducteur and tj_user_app
     */
    public static function getUserPhone(int $userId, string $userType): ?string
    {
        if ($userId <= 0) return null;
        $userType = self::normalizeUserType($userType);

        $phone = null;
        if ($userType === 'customer') {
            $phone = DB::table('tj_user_app')->where('id', $userId)->value('phone');
            if (!$phone) {
                $phone = DB::table('tj_conducteur')->where('id', $userId)->value('phone');
            }
        } else {
            $phone = DB::table('tj_conducteur')->where('id', $userId)->value('phone');
            if (!$phone) {
                $phone = DB::table('tj_user_app')->where('id', $userId)->value('phone');
            }
        }
        return $phone ? trim((string)$phone) : null;
    }

    /**
     * Find linked driver & customer IDs across tj_conducteur and tj_user_app by phone
     */
    public static function findUserIdsByPhone(string $phone): array
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (empty($clean) || strlen($clean) < 7) {
            return ['drivers' => [], 'customers' => []];
        }
        $last10 = strlen($clean) >= 10 ? substr($clean, -10) : $clean;

        $drivers = DB::table('tj_conducteur')
            ->where('phone', 'like', "%{$last10}")
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->toArray();

        $customers = DB::table('tj_user_app')
            ->where('phone', 'like', "%{$last10}")
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->toArray();

        return ['drivers' => $drivers, 'customers' => $customers];
    }

    /**
     * Resiliently resolve and reconcile Team Member / Freelancer
     * Ensures an existing freelancer code is NEVER changed or duplicated for the same person
     */
    public static function resolveAndReconcileTeamMember(int $userId, string $userType): ?object
    {
        try {
            if (!Schema::hasTable('marketing_team_members')) {
                return null;
            }

            $userType = self::normalizeUserType($userType);
            $phone = self::getUserPhone($userId, $userType);

            // Step 1: Collect all candidate records
            $candidates = collect();

            if ($userId > 0) {
                $direct = DB::table('marketing_team_members')
                    ->where('user_id', $userId)
                    ->get();
                $candidates = $candidates->merge($direct);
            }

            if (!empty($phone)) {
                $linked = self::findUserIdsByPhone($phone);
                $driverIds = $linked['drivers'];
                $custIds = $linked['customers'];

                if (!empty($driverIds) || !empty($custIds)) {
                    $byPhone = DB::table('marketing_team_members')
                        ->where(function ($q) use ($driverIds, $custIds) {
                            if (!empty($driverIds)) {
                                $q->orWhere(function ($sub) use ($driverIds) {
                                    $sub->whereIn('user_id', $driverIds);
                                });
                            }
                            if (!empty($custIds)) {
                                $q->orWhere(function ($sub) use ($custIds) {
                                    $sub->whereIn('user_id', $custIds);
                                });
                            }
                        })
                        ->get();
                    $candidates = $candidates->merge($byPhone);
                }
            }

            // Safety net for Ajit Shelke (FR01015) if phone matches 9970601711
            if (!empty($phone) && str_contains(preg_replace('/[^0-9]/', '', $phone), '9970601711')) {
                $specificAjit = DB::table('marketing_team_members')->where('member_code', 'FR01015')->first();
                if ($specificAjit) {
                    $candidates->push($specificAjit);
                }
            }

            $uniqueCandidates = $candidates->unique('id')->values();

            if ($uniqueCandidates->isEmpty()) {
                return null;
            }

            if ($uniqueCandidates->count() === 1) {
                $member = $uniqueCandidates->first();
                // Ensure user_id and user_type are aligned to active session
                if ($userId > 0 && ($member->user_id !== $userId || $member->user_type !== $userType)) {
                    DB::table('marketing_team_members')
                        ->where('id', $member->id)
                        ->update([
                            'user_id'    => $userId,
                            'user_type'  => $userType,
                            'status'     => 'active',
                            'updated_at' => now(),
                        ]);
                    $member->user_id = $userId;
                    $member->user_type = $userType;
                }
                return $member;
            }

            // Step 2: Multiple records found for the same person! Reconcile them.
            $scored = $uniqueCandidates->map(function ($cand) {
                $acqCount = DB::table('marketing_acquisitions')
                    ->where('team_member_id', $cand->id)
                    ->count();
                return [
                    'member'    => $cand,
                    'acq_count' => $acqCount,
                ];
            });

            // Primary canonical record: the one with the most acquisitions, or older id
            $sorted = $scored->sort(function ($a, $b) {
                if ($a['acq_count'] !== $b['acq_count']) {
                    return $b['acq_count'] <=> $a['acq_count']; // descending by acquisitions
                }
                return $a['member']->id <=> $b['member']->id; // ascending by id (older is original)
            })->values();

            $canonical = $sorted->first()['member'];

            // Preserve the most recent non-null vendor_id across candidates
            $latestVendorId = null;
            foreach ($sorted->reverse() as $item) {
                if (!empty($item['member']->vendor_id)) {
                    $latestVendorId = $item['member']->vendor_id;
                    break;
                }
            }

            // Merge duplicates into canonical
            for ($i = 1; $i < $sorted->count(); $i++) {
                $dup = $sorted[$i]['member'];
                // Move acquisitions pointing to duplicate
                DB::table('marketing_acquisitions')
                    ->where('team_member_id', $dup->id)
                    ->update([
                        'team_member_id' => $canonical->id,
                    ]);

                // Move payment ledgers pointing to duplicate
                if (Schema::hasTable('marketing_payment_ledgers')) {
                    DB::table('marketing_payment_ledgers')
                        ->where('team_member_id', $dup->id)
                        ->update(['team_member_id' => $canonical->id]);
                }

                // Delete duplicate row
                DB::table('marketing_team_members')->where('id', $dup->id)->delete();
                Log::info("VendorTeamService: Reconciled duplicate freelancer member #{$dup->id} ({$dup->member_code}) into canonical #{$canonical->id} ({$canonical->member_code})");
            }

            // Update canonical with active user credentials and latest vendor_id
            $updateData = [
                'status'     => 'active',
                'updated_at' => now(),
            ];
            if ($userId > 0) {
                $updateData['user_id'] = $userId;
                $updateData['user_type'] = $userType;
                $canonical->user_id = $userId;
                $canonical->user_type = $userType;
            }
            if ($latestVendorId && (int)$canonical->vendor_id !== (int)$latestVendorId) {
                $updateData['vendor_id'] = $latestVendorId;
                $canonical->vendor_id = $latestVendorId;
            }

            DB::table('marketing_team_members')
                ->where('id', $canonical->id)
                ->update($updateData);

            return $canonical;

        } catch (\Throwable $e) {
            Log::error("VendorTeamService::resolveAndReconcileTeamMember error: " . $e->getMessage());
            return DB::table('marketing_team_members')->where('user_id', $userId)->first();
        }
    }

    /**
     * Reconcile all team members under a specific Vendor
     */
    public static function reconcileVendorTeamMembers(int $vendorId): void
    {
        try {
            if (!Schema::hasTable('marketing_team_members')) return;

            $members = DB::table('marketing_team_members')
                ->where('vendor_id', $vendorId)
                ->get();

            // 1. Direct healing for Ajit Shelke (+919970601711) and FR01015
            // Ajit acquired Amruta Gadhave under FR01015. Santosh Mahato was mistakenly linked to FR01015,
            // while FR01018 was created as an accidental duplicate and FR01017 as an orphaned ghost row.
            $ajit = DB::table('tj_conducteur')->where('phone', 'like', '%9970601711%')->first()
                ?? DB::table('tj_user_app')->where('phone', 'like', '%9970601711%')->first();

            if ($ajit) {
                $fr01015 = DB::table('marketing_team_members')->where('member_code', 'FR01015')->first();
                $fr01018 = DB::table('marketing_team_members')->where('member_code', 'FR01018')->first();

                if ($fr01015) {
                    // Ensure FR01015 belongs to Ajit Shelke
                    DB::table('marketing_team_members')
                        ->where('id', $fr01015->id)
                        ->update([
                            'user_id'    => $ajit->id,
                            'user_type'  => 'business',
                            'status'     => 'active',
                            'updated_at' => now(),
                        ]);

                    // If FR01018 exists, move any acquisitions and delete duplicate FR01018
                    if ($fr01018) {
                        try {
                            DB::table('marketing_acquisitions')
                                ->where('team_member_id', $fr01018->id)
                                ->update(['team_member_id' => $fr01015->id]);
                        } catch (\Throwable $e) {}

                        try {
                            if (Schema::hasTable('marketing_payment_ledgers') && Schema::hasColumn('marketing_payment_ledgers', 'team_member_id')) {
                                DB::table('marketing_payment_ledgers')
                                    ->where('team_member_id', $fr01018->id)
                                    ->update(['team_member_id' => $fr01015->id]);
                            }
                        } catch (\Throwable $e) {}

                        DB::table('marketing_team_members')->where('id', $fr01018->id)->delete();
                        Log::info("VendorTeamService: Deleted duplicate FR01018 and consolidated to FR01015 for Ajit Shelke");
                    }
                } elseif ($fr01018) {
                    // If only FR01018 exists, rename to FR01015
                    DB::table('marketing_team_members')
                        ->where('id', $fr01018->id)
                        ->update([
                            'member_code' => 'FR01015',
                            'user_id'     => $ajit->id,
                            'user_type'   => 'business',
                            'status'      => 'active',
                        ]);
                }
            }

            // Always ensure FR01018 is deleted if FR01015 exists
            $check15 = DB::table('marketing_team_members')->where('member_code', 'FR01015')->first();
            if ($check15) {
                $dup18 = DB::table('marketing_team_members')->where('member_code', 'FR01018')->first();
                if ($dup18) {
                    try {
                        DB::table('marketing_acquisitions')
                            ->where('team_member_id', $dup18->id)
                            ->update(['team_member_id' => $check15->id]);
                    } catch (\Throwable $e) {}
                    DB::table('marketing_team_members')->where('id', $dup18->id)->delete();
                }
            }

            // 2. Remove orphaned ghost members with 0 acquisitions and no valid user (e.g. FR01017)
            $ghostMembers = DB::table('marketing_team_members')
                ->where('member_code', 'FR01017')
                ->get();
            foreach ($ghostMembers as $gm) {
                if ($check15) {
                    try {
                        DB::table('marketing_acquisitions')
                            ->where('team_member_id', $gm->id)
                            ->update(['team_member_id' => $check15->id]);
                    } catch (\Throwable $e) {}
                }
                DB::table('marketing_team_members')->where('id', $gm->id)->delete();
            }

            $allVendorMembers = DB::table('marketing_team_members')->where('vendor_id', $vendorId)->get();
            foreach ($allVendorMembers as $vm) {
                if ($check15 && $vm->id === $check15->id) continue;
                $acqCount = DB::table('marketing_acquisitions')->where('team_member_id', $vm->id)->count();
                if ($acqCount === 0) {
                    $uFound = null;
                    if ($vm->user_type === 'customer') {
                        $uFound = DB::table('tj_user_app')->where('id', $vm->user_id)->first()
                            ?? DB::table('tj_conducteur')->where('id', $vm->user_id)->first();
                    } else {
                        $uFound = DB::table('tj_conducteur')->where('id', $vm->user_id)->first()
                            ?? DB::table('tj_user_app')->where('id', $vm->user_id)->first();
                    }
                    if (!$uFound || (empty($uFound->phone) && empty($uFound->email))) {
                        DB::table('marketing_team_members')->where('id', $vm->id)->delete();
                        Log::info("VendorTeamService: Deleted ghost freelancer member #{$vm->id} ({$vm->member_code}) with no valid user and 0 acquisitions");
                    }
                }
            }

            // Re-fetch and merge duplicates for any user sharing phone or user_id
            $members = DB::table('marketing_team_members')
                ->where('vendor_id', $vendorId)
                ->get();

            $processed = [];
            foreach ($members as $m) {
                if (in_array($m->id, $processed, true)) continue;
                if ($m->user_id > 0) {
                    $reconciled = self::resolveAndReconcileTeamMember($m->user_id, $m->user_type);
                    if ($reconciled) {
                        $processed[] = $reconciled->id;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("VendorTeamService::reconcileVendorTeamMembers error: " . $e->getMessage());
        }
    }

    /**
     * Global reconciliation across all vendors
     */
    public static function reconcileAllDuplicateFreelancers(): void
    {
        try {
            if (!Schema::hasTable('marketing_vendors')) return;
            $vendorIds = DB::table('marketing_vendors')->pluck('id');
            foreach ($vendorIds as $vId) {
                self::reconcileVendorTeamMembers($vId);
            }
        } catch (\Throwable $e) {
            Log::error("VendorTeamService::reconcileAllDuplicateFreelancers error: " . $e->getMessage());
        }
    }

    /**
     * Register a user as a Team Member under an Approved Vendor/Sub-Vendor
     */
    public static function registerTeamMember(object $vendor, int $userId, string $userType): array
    {
        try {
            $userType = self::normalizeUserType($userType);

            // Resilient check: reuse existing code if user or phone is already registered
            $existing = self::resolveAndReconcileTeamMember($userId, $userType);

            if ($existing) {
                // If the user already had a member record, ensure they are assigned/transferred to this vendor
                if ((int)$existing->vendor_id !== (int)$vendor->id) {
                    DB::table('marketing_team_members')
                        ->where('id', $existing->id)
                        ->update([
                            'vendor_id'  => $vendor->id,
                            'status'     => 'active',
                            'updated_at' => now(),
                        ]);
                    $existing->vendor_id = $vendor->id;
                    Log::info("VendorTeamService: Assigned/transferred freelancer #{$existing->id} ({$existing->member_code}) to Vendor #{$vendor->id} ({$vendor->vendor_code})");
                }

                return [
                    'success'     => true,
                    'member_code' => $existing->member_code,
                    'member_id'   => $existing->id,
                    'message'     => "Joined as Freelancer successfully under Vendor {$vendor->vendor_code}.",
                ];
            }

            // Generate unique Member Code (FR10001...)
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

            Log::info("VendorTeamService: Registered user #{$userId} ($userType) as Freelancer under Vendor #{$vendor->id} with code {$memberCode}");

            return [
                'success'     => true,
                'member_code' => $memberCode,
                'member_id'   => $memberId,
                'message'     => 'Registered as Freelancer successfully.',
            ];

        } catch (\Throwable $e) {
            Log::error("VendorTeamService::registerTeamMember error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Record a Marketing Acquisition (Customer or Business user joined via FR code)
     */
    public static function recordAcquisition(object $teamMember, int $acquiredUserId, string $acquiredUserType, string $serviceCategory = 'General'): array
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

        // 1. Check if Vendor / Sub-Vendor
        $vendor = DB::table('marketing_vendors')
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->first();

        if ($vendor) {
            $isApproved = ($vendor->status === 'approved');
            $numPart = substr((string)($vendor->vendor_code ?? ''), 2);
            $subVendorCode = $vendor->sub_vendor_code ?: ($numPart ? 'SV' . $numPart : null);
            $freelancerCode = $vendor->freelancer_code ?: ($numPart ? 'FR' . $numPart : null);

            // Fetch parent vendor info if applicable
            $parentVendorInfo = null;
            if ($vendor->parent_vendor_id) {
                $pVendor = DB::table('marketing_vendors')->where('id', $vendor->parent_vendor_id)->first();
                if ($pVendor) {
                    $parentVendorInfo = [
                        'id'            => $pVendor->id,
                        'vendor_code'   => $pVendor->vendor_code,
                        'designation'   => $pVendor->designation,
                        'team_location' => $pVendor->team_location,
                    ];
                }
            }

            return [
                'role'               => $isApproved ? 'vendor' : ($vendor->status === 'rejected' ? 'rejected' : 'pending'),
                'status'             => $vendor->status,
                'application_status' => $vendor->status,
                'application_type'   => $vendor->parent_vendor_id ? 'sub_vendor' : 'head_vendor',
                'is_approved'        => $isApproved,
                'is_head_vendor'     => is_null($vendor->parent_vendor_id),
                'vendor_id'          => $vendor->id,
                'vendor_code'        => $isApproved ? $vendor->vendor_code : null,
                'sub_vendor_code'    => $isApproved ? $subVendorCode : null,
                'freelancer_code'    => $isApproved ? $freelancerCode : null,
                'designation'        => $vendor->designation ?: ($vendor->parent_vendor_id ? 'Sub-Vendor' : 'Head Vendor'),
                'hierarchy_level'    => (int)($vendor->hierarchy_level ?? 0),
                'parent_vendor_id'   => $vendor->parent_vendor_id,
                'parent_vendor'      => $parentVendorInfo,
                'head_vendor_id'     => $vendor->head_vendor_id,
                'team_location'      => $vendor->team_location,
                'team_type'          => $vendor->team_type,
                'is_rate_visible'    => (bool)($vendor->is_rate_visible ?? true),
                'rejection_reason'   => $vendor->rejection_reason,
                'application_data'   => [
                    'id'               => $vendor->id,
                    'status'           => $vendor->status,
                    'designation'      => $vendor->designation ?: ($vendor->parent_vendor_id ? 'Sub-Vendor' : 'Head Vendor'),
                    'team_location'    => $vendor->team_location,
                    'team_type'        => $vendor->team_type,
                    'parent_vendor'    => $parentVendorInfo,
                    'parent_code'      => $parentVendorInfo['vendor_code'] ?? null,
                    'created_at'       => $vendor->created_at,
                ],
            ];
        }

        // 2. Check if Freelancer / Team Member
        $member = self::resolveAndReconcileTeamMember($userId, $userType);

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
     * Vendor Dashboard Statistics & Multi-Level Work/Data Tracking
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
        $isHeadVendor = is_null($vendor->parent_vendor_id);

        // Downline vendor subtree (all recursive descendants)
        $downlineVendorIds = self::getDownlineVendorIds($vendorId);
        $allScopeVendorIds = array_merge([$vendorId], $downlineVendorIds);

        // Direct Sub-Vendors
        $directSubVendorsRaw = DB::table('marketing_vendors')
            ->where('parent_vendor_id', $vendorId)
            ->orderBy('id', 'desc')
            ->get();

        $directSubVendors = [];
        foreach ($directSubVendorsRaw as $sv) {
            $svName = 'Sub-Vendor';
            $svPhone = '';
            if ($sv->user_type === 'customer') {
                $u = DB::table('tj_user_app')->where('id', $sv->user_id)->first();
                if ($u) {
                    $svName = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Consumer';
                    $svPhone = $u->phone ?? '';
                }
            } else {
                $d = DB::table('tj_conducteur')->where('id', $sv->user_id)->first();
                if ($d) {
                    $svName = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Partner';
                    $svPhone = $d->phone ?? '';
                }
            }

            // Downline tree for this child
            $svSubTree = array_merge([$sv->id], self::getDownlineVendorIds($sv->id));
            $svFreelancers = DB::table('marketing_team_members')->whereIn('vendor_id', $svSubTree)->count();
            $svAcquisitions = DB::table('marketing_acquisitions')->whereIn('vendor_id', $svSubTree)->count();
            $svVerified = DB::table('marketing_acquisitions')->whereIn('vendor_id', $svSubTree)->where('verification_status', 'verified')->count();

            // Fetch detailed acquisitions for this sub-vendor's team
            $svAcqsRaw = DB::table('marketing_acquisitions')
                ->whereIn('vendor_id', $svSubTree)
                ->orderByDesc('id')
                ->limit(100)
                ->get();

            $svAcquisitionsList = [];
            $svPendCount = 0;
            $svRejCount = 0;
            $svVerCount = 0;

            foreach ($svAcqsRaw as $acq) {
                if ($acq->verification_status === 'verified') $svVerCount++;
                elseif ($acq->verification_status === 'rejected') $svRejCount++;
                else $svPendCount++;

                $acqName = $acq->acquired_user_type === 'business' ? 'Partner Driver' : 'Customer User';
                $acqPhone = '';

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

                $maskedPhone = '';
                if (!empty($acqPhone)) {
                    $digits = preg_replace('/[^0-9]/', '', $acqPhone);
                    $maskedPhone = strlen($digits) >= 10 ? substr($digits, 0, 3) . 'XXXX' . substr($digits, -3) : $acqPhone;
                }

                $hoursLeft = null;
                if ($acq->verification_status === 'pending') {
                    $createdTime = Carbon::parse($acq->created_at);
                    $deadline = $createdTime->copy()->addHours(72);
                    $diffHours = now()->diffInHours($deadline, false);
                    $hoursLeft = max(0, (int)$diffHours);
                }

                $svAcquisitionsList[] = [
                    'id'                  => $acq->id,
                    'name'                => $acqName,
                    'phone'               => $maskedPhone,
                    'zone'                => $sv->team_location ?? 'DELHI',
                    'date'                => Carbon::parse($acq->created_at)->format('d-m-Y'),
                    'user_type'           => $acq->acquired_user_type,
                    'verification_status' => $acq->verification_status,
                    'hours_left'          => $hoursLeft,
                    'rejection_reason'    => $acq->rejection_reason ?? null,
                ];
            }

            $maskedSvPhone = '';
            if (!empty($svPhone)) {
                $svDigits = preg_replace('/[^0-9]/', '', $svPhone);
                $maskedSvPhone = strlen($svDigits) >= 4 ? 'xxxxxx' . substr($svDigits, -4) : $svPhone;
            }

            $directSubVendors[] = [
                'id'                 => $sv->id,
                'vendor_code'        => $sv->vendor_code ?: 'Pending',
                'name'               => $svName,
                'phone'              => $maskedSvPhone,
                'designation'        => $sv->designation ?: 'Sub-Vendor',
                'hierarchy_level'    => (int)$sv->hierarchy_level,
                'status'             => $sv->status,
                'is_rate_visible'    => (bool)($sv->is_rate_visible ?? true),
                'rate_per_customer'  => number_format((float)$sv->rate_per_customer, 2, '.', ''),
                'rate_per_business'  => number_format((float)$sv->rate_per_business, 2, '.', ''),
                'freelancers_count'  => $svFreelancers,
                'acquisitions_count' => $svAcquisitions,
                'total_users'        => $svAcquisitions,
                'verified_count'     => $svVerified,
                'pending_count'      => $svPendCount,
                'rejected_count'     => $svRejCount,
                'joined_at'          => Carbon::parse($sv->created_at)->format('d M Y'),
                'acquisitions'       => $svAcquisitionsList,
            ];
        }

        // Pending Sub-Vendor Requests awaiting this vendor's approval
        $pendingSubVendors = self::getPendingSubVendors($vendorId);

        // Direct Freelancers under this vendor
        $directMembersCount = DB::table('marketing_team_members')->where('vendor_id', $vendorId)->count();
        // Total Freelancers in entire downline chain
        $chainMembersCount = DB::table('marketing_team_members')->whereIn('vendor_id', $allScopeVendorIds)->count();

        // Chain-wide Acquisitions
        $customerJoined = DB::table('marketing_acquisitions')->whereIn('vendor_id', $allScopeVendorIds)->where('acquired_user_type', 'customer')->count();
        $customerVerified = DB::table('marketing_acquisitions')->whereIn('vendor_id', $allScopeVendorIds)->where('acquired_user_type', 'customer')->where('verification_status', 'verified')->count();
        $customerPending = DB::table('marketing_acquisitions')->whereIn('vendor_id', $allScopeVendorIds)->where('acquired_user_type', 'customer')->where('verification_status', 'pending')->count();
        $customerRejected = DB::table('marketing_acquisitions')->whereIn('vendor_id', $allScopeVendorIds)->where('acquired_user_type', 'customer')->where('verification_status', 'rejected')->count();

        $businessJoined = DB::table('marketing_acquisitions')->whereIn('vendor_id', $allScopeVendorIds)->where('acquired_user_type', 'business')->count();
        $businessVerified = DB::table('marketing_acquisitions')->whereIn('vendor_id', $allScopeVendorIds)->where('acquired_user_type', 'business')->where('verification_status', 'verified')->count();
        $businessPending = DB::table('marketing_acquisitions')->whereIn('vendor_id', $allScopeVendorIds)->where('acquired_user_type', 'business')->where('verification_status', 'pending')->count();
        $businessRejected = DB::table('marketing_acquisitions')->whereIn('vendor_id', $allScopeVendorIds)->where('acquired_user_type', 'business')->where('verification_status', 'rejected')->count();

        $totalVerified = $customerVerified + $businessVerified;
        $totalPending  = $customerPending + $businessPending;
        $totalRejected = $customerRejected + $businessRejected;
        $totalInstall  = $customerJoined + $businessJoined;

        // Rate privacy: Sub-Vendors do NOT see Admin master rates
        $rateCustomer = (float)$vendor->rate_per_customer;
        $rateBusiness = (float)$vendor->rate_per_business;
        $isRateVisible = (bool)($vendor->is_rate_visible ?? true);

        // Financial Earnings Calculation:
        // Use marketing_payment_ledgers if records exist, otherwise calculate from verified acquisitions
        $hasLedger = DB::table('marketing_payment_ledgers')->where('vendor_id', $vendorId)->exists();

        if ($hasLedger) {
            $totalEarned = (float)DB::table('marketing_payment_ledgers')->where('vendor_id', $vendorId)->sum('earned_amount');
            $paidEarned = (float)DB::table('marketing_payment_ledgers')->where('vendor_id', $vendorId)->sum('paid_amount');
            $pendingPayout = (float)DB::table('marketing_payment_ledgers')->where('vendor_id', $vendorId)->sum('pending_amount');
            $customerDue = (float)DB::table('marketing_payment_ledgers')->where('vendor_id', $vendorId)->where('acquired_user_type', 'customer')->sum('earned_amount');
            $businessDue = (float)DB::table('marketing_payment_ledgers')->where('vendor_id', $vendorId)->where('acquired_user_type', 'business')->sum('earned_amount');
        } else {
            $customerDue = round($customerVerified * $rateCustomer, 2);
            $businessDue = round($businessVerified * $rateBusiness, 2);
            $totalEarned = round($customerDue + $businessDue, 2);
            $paidEarned = (float)DB::table('marketing_acquisitions')->where('vendor_id', $vendorId)->where('payout_status', 'paid')->sum('payout_rate_applied');
            $pendingPayout = max(0, round($totalEarned - $paidEarned, 2));
        }

        $customerUpcoming = round($customerPending * $rateCustomer, 2);
        $businessUpcoming = round($businessPending * $rateBusiness, 2);
        $totalUpcomingIncome = round($customerUpcoming + $businessUpcoming, 2);

        // Direct Freelancers List with their acquisition items
        self::reconcileVendorTeamMembers($vendorId);

        $membersRaw = DB::table('marketing_team_members')
            ->where('vendor_id', $vendorId)
            ->orderBy('id', 'desc')
            ->get();

        $teamMembers = [];
        foreach ($membersRaw as $m) {
            $name = 'Freelancer';
            $phone = '';
            $photo = null;

            if ($m->user_type === 'customer') {
                $u = DB::table('tj_user_app')->where('id', $m->user_id)->first();
                if (!$u) {
                    $u = DB::table('tj_conducteur')->where('id', $m->user_id)->first();
                }
                if ($u) {
                    $name = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Freelancer';
                    $phone = $u->phone ?? '';
                    $photo = $u->photo_path ?? null;
                }
            } else {
                $d = DB::table('tj_conducteur')->where('id', $m->user_id)->first();
                if (!$d) {
                    $d = DB::table('tj_user_app')->where('id', $m->user_id)->first();
                }
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
            $mCustTotal = 0; $mCustVer = 0; $mCustPend = 0; $mCustRej = 0;
            $mBizTotal = 0; $mBizVer = 0; $mBizPend = 0; $mBizRej = 0;
            $verCount = 0; $pendCount = 0; $rejCount = 0;

            foreach ($acqsRaw as $acq) {
                if ($acq->acquired_user_type === 'customer') {
                    $mCustTotal++;
                    if ($acq->verification_status === 'verified') $mCustVer++;
                    elseif ($acq->verification_status === 'rejected') $mCustRej++;
                    else $mCustPend++;
                } else {
                    $mBizTotal++;
                    if ($acq->verification_status === 'verified') $mBizVer++;
                    elseif ($acq->verification_status === 'rejected') $mBizRej++;
                    else $mBizPend++;
                }

                if ($acq->verification_status === 'verified') $verCount++;
                elseif ($acq->verification_status === 'rejected') $rejCount++;
                else $pendCount++;

                $acqName = $acq->acquired_user_type === 'business' ? 'Partner Driver' : 'Customer User';
                $acqPhone = '';

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

                $maskedPhone = '';
                if (!empty($acqPhone)) {
                    $digits = preg_replace('/[^0-9]/', '', $acqPhone);
                    $maskedPhone = strlen($digits) >= 10 ? substr($digits, 0, 3) . 'XXXX' . substr($digits, -3) : $acqPhone;
                }

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
                    'zone'                => $vendor->team_location ?? 'DELHI',
                    'date'                => Carbon::parse($acq->created_at)->format('d-m-Y'),
                    'user_type'           => $acq->acquired_user_type,
                    'verification_status' => $acq->verification_status,
                    'hours_left'          => $hoursLeft,
                    'rejection_reason'    => $acq->rejection_reason ?? null,
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
                'total_earnings'      => ($isHeadVendor || $isRateVisible) ? $mEarnings : null,
                'verified_earnings'   => ($isHeadVendor || $isRateVisible) ? $mEarnings : null,
                'pending_earnings'    => ($isHeadVendor || $isRateVisible) ? $mPendingEarnings : null,
                'acquisitions'        => $acquisitions,
            ];
        }

        // Parent Vendor Info (if Sub-Vendor)
        $parentVendorInfo = null;
        if (!$isHeadVendor) {
            $pv = DB::table('marketing_vendors')->where('id', $vendor->parent_vendor_id)->first();
            if ($pv) {
                $parentVendorInfo = [
                    'id'          => $pv->id,
                    'vendor_code' => $pv->vendor_code,
                    'designation' => $pv->designation ?: 'Parent Vendor',
                ];
            }
        }

        $numPart = substr((string)($vendor->vendor_code ?? ''), 2);
        $subVendorCode = $vendor->sub_vendor_code ?: ($numPart ? 'SV' . $numPart : null);
        $freelancerCode = $vendor->freelancer_code ?: ($numPart ? 'FR' . $numPart : null);

        return [
            'status'                     => 'approved',
            'is_approved'                => true,
            'vendor_id'                  => $vendor->id,
            'vendor_code'                => $vendor->vendor_code,
            'sub_vendor_code'            => $subVendorCode,
            'freelancer_code'            => $freelancerCode,
            'is_head_vendor'             => $isHeadVendor,
            'parent_vendor'              => $parentVendorInfo,
            'designation'                => $vendor->designation ?: ($isHeadVendor ? 'Head Vendor' : 'Sub-Vendor'),
            'hierarchy_level'            => (int)($vendor->hierarchy_level ?? 0),
            'team_location'              => $vendor->team_location,
            'team_type'                  => $vendor->team_type,
            'is_rate_visible'            => $isRateVisible,

            // Rate values: redacted if not visible to sub-vendor
            'rate_per_customer'          => ($isHeadVendor || $isRateVisible) ? number_format($rateCustomer, 2, '.', '') : null,
            'rate_per_business'          => ($isHeadVendor || $isRateVisible) ? number_format($rateBusiness, 2, '.', '') : null,

            // Counts
            'total_sub_vendors'          => count($directSubVendors),
            'all_downline_vendors_count' => count($downlineVendorIds),
            'direct_sub_vendors'         => $directSubVendors,
            'pending_sub_vendors'        => $pendingSubVendors,
            'pending_sub_vendors_count'  => count($pendingSubVendors),

            'freelancers_count'          => $chainMembersCount,
            'direct_freelancers_count'   => $directMembersCount,

            'customer_joined'            => $customerJoined,
            'total_customers'            => $customerJoined,
            'customer_verified'          => $customerVerified,
            'verified_customers'         => $customerVerified,
            'customer_pending'           => $customerPending,
            'customer_rejected'          => $customerRejected,

            'business_joined'            => $businessJoined,
            'total_businesses'           => $businessJoined,
            'business_verified'          => $businessVerified,
            'verified_businesses'        => $businessVerified,
            'business_pending'           => $businessPending,
            'business_rejected'          => $businessRejected,

            'total_verified'             => $totalVerified,
            'total_pending'              => $totalPending,
            'total_rejected'             => $totalRejected,
            'total_install'              => $totalInstall,
            'total_completed_work'       => $totalVerified,

            // Financials: redacted if not visible to sub-vendor
            'customer_due'               => ($isHeadVendor || $isRateVisible) ? number_format($customerDue, 2, '.', '') : null,
            'business_due'               => ($isHeadVendor || $isRateVisible) ? number_format($businessDue, 2, '.', '') : null,
            'total_verified_due'         => ($isHeadVendor || $isRateVisible) ? number_format($totalEarned, 2, '.', '') : null,
            'customer_upcoming'          => ($isHeadVendor || $isRateVisible) ? number_format($customerUpcoming, 2, '.', '') : null,
            'business_upcoming'          => ($isHeadVendor || $isRateVisible) ? number_format($businessUpcoming, 2, '.', '') : null,
            'total_upcoming_income'      => ($isHeadVendor || $isRateVisible) ? number_format($totalUpcomingIncome, 2, '.', '') : null,
            'total_earned'               => ($isHeadVendor || $isRateVisible) ? number_format($totalEarned, 2, '.', '') : null,
            'total_earnings'             => ($isHeadVendor || $isRateVisible) ? number_format($totalEarned, 2, '.', '') : null,
            'paid_earned'                => ($isHeadVendor || $isRateVisible) ? number_format($paidEarned, 2, '.', '') : null,
            'paid_earnings'              => ($isHeadVendor || $isRateVisible) ? number_format($paidEarned, 2, '.', '') : null,
            'pending_payout'             => ($isHeadVendor || $isRateVisible) ? number_format($pendingPayout, 2, '.', '') : null,

            'team_members'               => $teamMembers,
        ];
    }

    /**
     * Team Member / Freelancer Dashboard Statistics
     */
    public static function getTeamMemberDashboardStats(int $userId, string $userType): ?array
    {
        $userType = self::normalizeUserType($userType);
        $member = self::resolveAndReconcileTeamMember($userId, $userType);

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
                $maskedPhone = strlen($digits) >= 10 ? substr($digits, 0, 3) . 'XXXX' . substr($digits, -3) : $phone;
            }

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
                'rejection_reason'    => $acq->rejection_reason ?? null,
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
     * Admin Verifies an Acquisition & Writes Granular Payment Ledgers Up the Chain
     */
    public static function verifyAcquisition(int $acquisitionId, ?int $adminId = null): bool
    {
        try {
            $acq = DB::table('marketing_acquisitions')->where('id', $acquisitionId)->first();
            if (!$acq || $acq->verification_status === 'verified') {
                return false;
            }

            $immediateVendor = DB::table('marketing_vendors')->where('id', $acq->vendor_id)->first();
            if (!$immediateVendor) {
                return false;
            }

            $rate = $acq->acquired_user_type === 'business'
                ? (float)$immediateVendor->rate_per_business
                : (float)$immediateVendor->rate_per_customer;

            DB::table('marketing_acquisitions')->where('id', $acquisitionId)->update([
                'verification_status' => 'verified',
                'verified_by'         => $adminId,
                'verified_at'         => now(),
                'payout_rate_applied' => $rate,
                'rejection_reason'    => null,
                'updated_at'          => now(),
            ]);

            // ── GENERATE PAYMENT LEDGER ENTRIES UP THE HIERARCHY CHAIN ──────────
            self::generateLedgerEntriesForAcquisition($acq, $immediateVendor);

            return true;
        } catch (\Throwable $e) {
            Log::error("VendorTeamService::verifyAcquisition error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate Payment Ledger entries for each vendor level in the chain
     */
    private static function generateLedgerEntriesForAcquisition(object $acq, object $immediateVendor): void
    {
        if (!Schema::hasTable('marketing_payment_ledgers')) {
            return;
        }

        // Trace chain upwards to root Head Vendor
        $chain = [];
        $curr = $immediateVendor;
        while ($curr) {
            $chain[] = $curr;
            if (empty($curr->parent_vendor_id)) {
                break;
            }
            $curr = DB::table('marketing_vendors')->where('id', $curr->parent_vendor_id)->first();
        }

        $headVendor = end($chain) ?: $immediateVendor;
        $isBiz = ($acq->acquired_user_type === 'business');

        // Traverse from immediate vendor upwards and compute earnings
        $prevChildRate = 0.00;

        foreach ($chain as $idx => $v) {
            $applicableRate = $isBiz ? (float)$v->rate_per_business : (float)$v->rate_per_customer;

            if ($idx === 0) {
                // Immediate vendor earns their direct applicable rate
                $earned = $applicableRate;
            } else {
                // Parent / Head vendor earns the margin difference: (parent_rate - child_rate)
                $earned = max(0, $applicableRate - $prevChildRate);
            }

            $prevChildRate = $applicableRate;

            DB::table('marketing_payment_ledgers')->insert([
                'acquisition_id'     => $acq->id,
                'head_vendor_id'     => $headVendor->id,
                'parent_vendor_id'   => $v->parent_vendor_id,
                'vendor_id'          => $v->id,
                'team_member_id'     => $acq->team_member_id,
                'acquired_user_id'   => $acq->acquired_user_id,
                'acquired_user_type' => $acq->acquired_user_type,
                'service_category'   => 'General',
                'rate_applied'       => $applicableRate,
                'earned_amount'      => $earned,
                'paid_amount'        => 0.00,
                'pending_amount'     => $earned,
                'payment_status'     => 'unpaid',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    /**
     * Get Granular Payment Ledger Records for a Vendor
     */
    public static function getVendorPaymentLedger(int $vendorId, array $filters = []): array
    {
        if (!Schema::hasTable('marketing_payment_ledgers')) {
            return [];
        }

        $query = DB::table('marketing_payment_ledgers')
            ->where('marketing_payment_ledgers.vendor_id', $vendorId)
            ->leftJoin('marketing_team_members', 'marketing_team_members.id', '=', 'marketing_payment_ledgers.team_member_id')
            ->leftJoin('marketing_vendors as sub_v', 'sub_v.id', '=', 'marketing_payment_ledgers.parent_vendor_id')
            ->select(
                'marketing_payment_ledgers.*',
                'marketing_team_members.member_code as freelancer_code'
            )
            ->orderBy('marketing_payment_ledgers.id', 'desc');

        if (!empty($filters['status']) && in_array($filters['status'], ['unpaid', 'partially_paid', 'paid'], true)) {
            $query->where('marketing_payment_ledgers.payment_status', $filters['status']);
        }

        if (!empty($filters['user_type'])) {
            $query->where('marketing_payment_ledgers.acquired_user_type', $filters['user_type']);
        }

        $ledgers = $query->limit(100)->get();

        $result = [];
        foreach ($ledgers as $l) {
            $userName = $l->acquired_user_type === 'business' ? 'Business Partner' : 'Customer User';
            $userPhone = '';

            if ($l->acquired_user_type === 'customer') {
                $u = DB::table('tj_user_app')->where('id', $l->acquired_user_id)->first();
                if ($u) {
                    $userName = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Customer User';
                    $userPhone = $u->phone ?? '';
                }
            } else {
                $d = DB::table('tj_conducteur')->where('id', $l->acquired_user_id)->first();
                if ($d) {
                    $userName = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Business Partner';
                    $userPhone = $d->phone ?? '';
                }
            }

            $maskedPhone = '';
            if (!empty($userPhone)) {
                $digits = preg_replace('/[^0-9]/', '', $userPhone);
                $maskedPhone = strlen($digits) >= 10 ? substr($digits, 0, 3) . 'XXXX' . substr($digits, -3) : $userPhone;
            }

            $result[] = [
                'id'                 => $l->id,
                'transaction_id'     => 'LEDG-' . str_pad((string)$l->id, 6, '0', STR_PAD_LEFT),
                'date'               => Carbon::parse($l->created_at)->format('d M Y, h:i A'),
                'acquired_user_id'   => $l->acquired_user_id,
                'acquired_user_type' => $l->acquired_user_type,
                'user_name'          => $userName,
                'user_phone'         => $maskedPhone,
                'service_category'   => $l->service_category,
                'freelancer_code'    => $l->freelancer_code ?: 'Direct',
                'rate_applied'       => number_format((float)$l->rate_applied, 2, '.', ''),
                'earned_amount'      => number_format((float)$l->earned_amount, 2, '.', ''),
                'paid_amount'        => number_format((float)$l->paid_amount, 2, '.', ''),
                'pending_amount'     => number_format((float)$l->pending_amount, 2, '.', ''),
                'payment_status'     => $l->payment_status,
                'payout_reference'   => $l->payout_reference,
            ];
        }

        return $result;
    }

    /**
     * Chain-wise Consolidated Report
     */
    public static function getConsolidatedReport(int $vendorId, ?string $period = 'all', ?string $serviceCategory = 'all'): array
    {
        $allScopeIds = array_merge([$vendorId], self::getDownlineVendorIds($vendorId));

        $acqQuery = DB::table('marketing_acquisitions')->whereIn('vendor_id', $allScopeIds);

        if ($period === 'today') {
            $acqQuery->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $acqQuery->where('created_at', '>=', now()->subDays(7));
        } elseif ($period === 'month') {
            $acqQuery->where('created_at', '>=', now()->subDays(30));
        }

        $totalUsers = (clone $acqQuery)->count();
        $totalVerified = (clone $acqQuery)->where('verification_status', 'verified')->count();
        $totalPending = (clone $acqQuery)->where('verification_status', 'pending')->count();
        $totalRejected = (clone $acqQuery)->where('verification_status', 'rejected')->count();

        $custCount = (clone $acqQuery)->where('acquired_user_type', 'customer')->count();
        $bizCount = (clone $acqQuery)->where('acquired_user_type', 'business')->count();

        // Financial totals from ledgers if available
        $totalEarned = 0.00;
        $totalPaid = 0.00;
        $totalPendingDue = 0.00;

        if (Schema::hasTable('marketing_payment_ledgers')) {
            $totalEarned = (float)DB::table('marketing_payment_ledgers')->where('vendor_id', $vendorId)->sum('earned_amount');
            $totalPaid = (float)DB::table('marketing_payment_ledgers')->where('vendor_id', $vendorId)->sum('paid_amount');
            $totalPendingDue = (float)DB::table('marketing_payment_ledgers')->where('vendor_id', $vendorId)->sum('pending_amount');
        }

        return [
            'period'             => $period,
            'service_category'   => $serviceCategory,
            'total_acquisitions' => $totalUsers,
            'total_verified'     => $totalVerified,
            'total_pending'      => $totalPending,
            'total_rejected'     => $totalRejected,
            'customers_count'    => $custCount,
            'businesses_count'   => $bizCount,
            'total_earned'       => number_format($totalEarned, 2, '.', ''),
            'total_paid'         => number_format($totalPaid, 2, '.', ''),
            'total_pending_due'  => number_format($totalPendingDue, 2, '.', ''),
        ];
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

            // Cancel any unpaid ledger entries
            if (Schema::hasTable('marketing_payment_ledgers')) {
                DB::table('marketing_payment_ledgers')
                    ->where('acquisition_id', $acquisitionId)
                    ->where('payment_status', 'unpaid')
                    ->delete();
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("VendorTeamService::rejectAcquisition error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique sequential code with prefix (e.g. VR10001, FR10001)
     */
    private static function generateUniqueCode(string $prefix, string $table, string $column): string
    {
        $maxAttempts = 50;
        $startNum = in_array($prefix, ['VR', 'TM'], true) ? 10000 : 1000;

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

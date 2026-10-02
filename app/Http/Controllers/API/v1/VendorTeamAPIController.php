<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Services\VendorTeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorTeamAPIController extends Controller
{
    /**
     * Resolve User ID and Type from Request (supports header, token, or query/body)
     */
    private function resolveUser(Request $request): array
    {
        $userId = $request->header('id_user')
            ?? $request->input('id_user')
            ?? $request->input('user_id');

        $userCat = $request->header('user_cat')
            ?? $request->input('user_cat')
            ?? $request->input('user_type')
            ?? 'driver';

        return [(int)$userId, strtolower(trim((string)$userCat))];
    }

    /**
     * Helper to get approved vendor for current request
     */
    private function resolveApprovedVendor(Request $request): ?object
    {
        [$userId, $userCat] = $this->resolveUser($request);
        if (!$userId) return null;

        $userCat = in_array($userCat, ['driver', 'conducteur', 'business', 'provider'], true) ? 'business' : 'customer';

        return DB::table('marketing_vendors')
            ->where('user_id', $userId)
            ->where('user_type', $userCat)
            ->where('status', 'approved')
            ->first();
    }

    /**
     * GET /api/v1/vendor-team/status
     * Returns user's status: 'none', 'pending', 'vendor', or 'team_member'
     */
    public function getStatus(Request $request)
    {
        [$userId, $userCat] = $this->resolveUser($request);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required.',
            ], 422);
        }

        $roleInfo = VendorTeamService::getUserRoleStatus($userId, $userCat);

        return response()->json([
            'success' => true,
            'data'    => $roleInfo,
        ]);
    }

    /**
     * POST /api/v1/vendor-team/apply
     * Submit application for Head Vendor role to Admin
     */
    public function apply(Request $request)
    {
        [$userId, $userCat] = $this->resolveUser($request);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required.',
            ], 422);
        }

        $teamLocation = trim((string)$request->input('team_location'));
        $teamType     = trim((string)$request->input('team_type'));
        $remarks      = $request->input('remarks');

        if (empty($teamLocation)) {
            return response()->json([
                'success' => false,
                'message' => 'Team location / territory is required.',
            ], 422);
        }

        if (empty($teamType)) {
            return response()->json([
                'success' => false,
                'message' => 'Team type is required.',
            ], 422);
        }

        $result = VendorTeamService::applyForVendor($userId, $userCat, $teamLocation, $teamType, $remarks);

        return response()->json($result);
    }

    /**
     * POST /api/v1/vendor-team/join
     * Unified join with code: selects role as 'sub_vendor' or 'freelancer'
     */
    public function joinWithCode(Request $request)
    {
        [$userId, $userCat] = $this->resolveUser($request);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required.',
            ], 422);
        }

        $vendorCode   = trim((string)($request->input('vendor_code') ?? $request->input('code')));
        $roleType     = strtolower(trim((string)$request->input('role_type', 'freelancer')));
        $designation  = $request->input('designation');
        $teamLocation = $request->input('team_location');
        $teamType     = $request->input('team_type');
        $remarks      = $request->input('remarks');

        if (empty($vendorCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor Joining Code is required.',
            ], 422);
        }

        $result = VendorTeamService::applyWithVendorCode(
            $userId,
            $userCat,
            $vendorCode,
            $roleType,
            $designation,
            $teamLocation,
            $teamType,
            $remarks
        );

        return response()->json($result);
    }

    /**
     * GET /api/v1/vendor-team/pending-sub-vendors
     * Returns pending sub-vendor requests for parent vendor approval
     */
    public function getPendingSubVendors(Request $request)
    {
        $vendor = $this->resolveApprovedVendor($request);
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Approved Vendor account required.'], 403);
        }

        $list = VendorTeamService::getPendingSubVendors($vendor->id);

        return response()->json([
            'success' => true,
            'data'    => $list,
            'parent_limits' => [
                'max_rate_customer' => (float)$vendor->rate_per_customer,
                'max_rate_business' => (float)$vendor->rate_per_business,
            ],
        ]);
    }

    /**
     * POST /api/v1/vendor-team/approve-sub-vendor
     * Parent Vendor approves Sub-Vendor with rates, ceiling check, designation, and visibility
     */
    public function approveSubVendor(Request $request)
    {
        [$userId, $userCat] = $this->resolveUser($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 422);
        }

        $subVendorId    = (int)$request->input('sub_vendor_id');
        $rateCustomer   = (float)$request->input('rate_per_customer', 0);
        $rateBusiness   = (float)$request->input('rate_per_business', 0);
        $designation    = $request->input('designation');
        $isRateVisible  = filter_var($request->input('is_rate_visible', true), FILTER_VALIDATE_BOOLEAN);

        if (!$subVendorId) {
            return response()->json(['success' => false, 'message' => 'Sub-Vendor ID is required.'], 422);
        }

        $result = VendorTeamService::approveSubVendor(
            $subVendorId,
            $userId,
            $userCat,
            $rateCustomer,
            $rateBusiness,
            $designation,
            $isRateVisible
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * POST /api/v1/vendor-team/reject-sub-vendor
     */
    public function rejectSubVendor(Request $request)
    {
        [$userId, $userCat] = $this->resolveUser($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 422);
        }

        $subVendorId = (int)$request->input('sub_vendor_id');
        $reason      = trim((string)$request->input('reason', 'Application rejected by parent vendor.'));

        $result = VendorTeamService::rejectSubVendor($subVendorId, $userId, $userCat, $reason);

        return response()->json($result);
    }

    /**
     * POST /api/v1/vendor-team/toggle-rate-visibility
     * Parent Vendor toggles rate visibility ON/OFF for their Sub-Vendor
     */
    public function toggleRateVisibility(Request $request)
    {
        [$userId, $userCat] = $this->resolveUser($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 422);
        }

        $subVendorId = (int)$request->input('sub_vendor_id');
        $visible     = filter_var($request->input('is_rate_visible', true), FILTER_VALIDATE_BOOLEAN);

        $result = VendorTeamService::toggleSubVendorRateVisibility($subVendorId, $userId, $userCat, $visible);

        return response()->json($result);
    }

    /**
     * GET /api/v1/vendor-team/payment-ledger
     * Returns granular payment ledger records for current vendor
     */
    public function getPaymentLedger(Request $request)
    {
        $vendor = $this->resolveApprovedVendor($request);
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Approved Vendor account required.'], 403);
        }

        $filters = [
            'status'    => $request->input('status'),
            'user_type' => $request->input('user_type'),
        ];

        $ledger = VendorTeamService::getVendorPaymentLedger($vendor->id, $filters);

        return response()->json([
            'success' => true,
            'data'    => $ledger,
        ]);
    }

    /**
     * GET /api/v1/vendor-team/consolidated-report
     * Returns chain-wise performance and financial summary
     */
    public function getConsolidatedReport(Request $request)
    {
        $vendor = $this->resolveApprovedVendor($request);
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Approved Vendor account required.'], 403);
        }

        $period   = $request->input('period', 'all');
        $category = $request->input('service_category', 'all');

        $report = VendorTeamService::getConsolidatedReport($vendor->id, $period, $category);

        return response()->json([
            'success' => true,
            'data'    => $report,
        ]);
    }

    /**
     * GET /api/v1/vendor-team/vendor-dashboard
     * Full analytics dashboard for approved Vendor
     */
    public function getVendorDashboard(Request $request)
    {
        [$userId, $userCat] = $this->resolveUser($request);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required.',
            ], 422);
        }

        $stats = VendorTeamService::getVendorDashboardStats($userId, $userCat);

        if (!$stats) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have an approved Vendor account.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }

    /**
     * GET /api/v1/vendor-team/member-dashboard
     * Freelancer stats dashboard (counts only — rates & earnings hidden)
     */
    public function getMemberDashboard(Request $request)
    {
        [$userId, $userCat] = $this->resolveUser($request);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required.',
            ], 422);
        }

        $stats = VendorTeamService::getTeamMemberDashboardStats($userId, $userCat);

        if (!$stats) {
            return response()->json([
                'success' => false,
                'message' => 'Team member profile not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }
}

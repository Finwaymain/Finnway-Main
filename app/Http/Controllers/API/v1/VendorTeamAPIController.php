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
     * Submit application for Vendor / Team Manager role
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

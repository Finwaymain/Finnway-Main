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
        $rawId = null;
        $candidates = [
            $request->header('id_user'),
            $request->header('id_driver'),
            $request->header('driver_id'),
            $request->header('user_id'),
            $request->input('id_user'),
            $request->input('id_driver'),
            $request->input('driver_id'),
            $request->input('user_id'),
            $request->input('id_conducteur'),
            $request->input('id_user_app'),
        ];
        foreach ($candidates as $c) {
            if ($c !== null && $c !== '' && (int)$c > 0) {
                $rawId = (int)$c;
                break;
            }
        }

        $rawCat = $request->header('user_cat')
            ?? $request->header('user_type')
            ?? $request->input('user_cat')
            ?? $request->input('user_type');

        if (empty($rawCat)) {
            if ($request->has('driver_id') || $request->has('id_driver') || $request->has('id_conducteur')) {
                $rawCat = 'driver';
            } else {
                $rawCat = 'driver';
            }
        }

        return [(int)$rawId, strtolower(trim((string)$rawCat))];
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
        $codeUpper    = strtoupper($vendorCode);

        // Auto-detect role from prefix
        if (str_starts_with($codeUpper, 'SV')) {
            $roleType = 'sub_vendor';
        } elseif (str_starts_with($codeUpper, 'FR')) {
            $roleType = 'freelancer';
        } else {
            $roleType = strtolower(trim((string)$request->input('role_type', 'sub_vendor')));
        }
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
        $rateRestaurant = (float)$request->input('rate_per_restaurant', 0);
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
            $isRateVisible,
            $rateRestaurant
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
            $pendingVendor = DB::table('marketing_vendors')
                ->where('user_id', $userId)
                ->where('user_type', $userCat)
                ->where('status', 'pending')
                ->first();

            if ($pendingVendor) {
                return response()->json([
                    'success'            => false,
                    'status'             => 'pending',
                    'application_status' => 'pending',
                    'message'            => 'Your Sub-Vendor application is awaiting review and rate assignment.',
                    'data'               => [
                        'status'           => 'pending',
                        'designation'      => $pendingVendor->designation ?: 'Sub-Vendor',
                        'team_location'    => $pendingVendor->team_location,
                        'team_type'        => $pendingVendor->team_type,
                        'parent_vendor_id' => $pendingVendor->parent_vendor_id,
                        'created_at'       => $pendingVendor->created_at,
                    ]
                ], 403);
            }

            return response()->json([
                'success' => false,
                'status'  => 'none',
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

    /**
     * POST /api/v1/vendor-team/upload-id-photo
     * Upload or update the Partner ID Card photo (supports multipart file or base64 image string).
     */
    public function uploadIdPhoto(Request $request)
    {
        [$userId, $userCat] = $this->resolveUser($request);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required.',
            ], 422);
        }

        $destDir = public_path('assets/images/partner_id_photos');
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        $fileName = null;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uploaded photo file is invalid.',
                ], 422);
            }
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $ext = 'jpg';
            }
            $fileName = 'partner_id_' . $userId . '_' . time() . '.' . $ext;
            $file->move($destDir, $fileName);
        } elseif ($request->filled('photo_base64')) {
            $rawBase64 = (string) $request->input('photo_base64');
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $rawBase64, $m)) {
                $ext = strtolower($m[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $rawBase64 = substr($rawBase64, strpos($rawBase64, ',') + 1);
            }
            $decoded = base64_decode($rawBase64);
            if (!$decoded || strlen($decoded) < 50) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image data.',
                ], 422);
            }
            $fileName = 'partner_id_' . $userId . '_' . time() . '.' . $ext;
            file_put_contents($destDir . DIRECTORY_SEPARATOR . $fileName, $decoded);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please select a photo to upload.',
            ], 422);
        }

        $relativePath = 'assets/images/partner_id_photos/' . $fileName;
        $photoUrl = VendorTeamService::savePartnerIdCardPhoto((int) $userId, (string) $userCat, $relativePath);

        return response()->json([
            'success'   => true,
            'message'   => 'ID Card photo uploaded successfully!',
            'photo_url' => $photoUrl,
        ]);
    }

    /**
     * POST /api/v1/vendor-team/save-document-image
     * Stores rendered ID card or Authorization Letter PNG and returns direct download & view URLs
     */
    public function saveDocumentImage(Request $request)
    {
        $rawBase64 = (string) $request->input('image_base64', '');
        $requestedName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', (string) $request->input('filename', 'Fiinway_Partner_Document.png'));
        if (!str_ends_with(strtolower($requestedName), '.png')) {
            $requestedName .= '.png';
        }

        if (preg_match('/^data:image\/\w+;base64,/', $rawBase64)) {
            $rawBase64 = substr($rawBase64, strpos($rawBase64, ',') + 1);
        }

        $decoded = base64_decode($rawBase64);
        if (!$decoded || strlen($decoded) < 100) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document image data.',
            ], 422);
        }

        $destDir = public_path('uploads/partner_documents');
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        $uniqueName = pathinfo($requestedName, PATHINFO_FILENAME) . '_' . time() . '.png';
        file_put_contents($destDir . DIRECTORY_SEPARATOR . $uniqueName, $decoded);

        return response()->json([
            'success'      => true,
            'filename'     => $uniqueName,
            'image_url'    => url('uploads/partner_documents/' . $uniqueName),
            'download_url' => url('api/v1/vendor-team/download-document/' . $uniqueName),
        ]);
    }

    /**
     * GET /api/v1/vendor-team/download-document/{filename}
     * Serves the generated Partner ID Card or Authorization Letter PNG as a binary file attachment
     */
    public function downloadDocument(Request $request, $filename)
    {
        $safeName = basename((string) $filename);
        $path = public_path('uploads/partner_documents/' . $safeName);

        if (!is_file($path)) {
            abort(404, 'Document not found.');
        }

        return response()->download($path, $safeName, [
            'Content-Type'                => 'application/octet-stream',
            'Content-Disposition'         => 'attachment; filename="' . $safeName . '"',
            'Content-Transfer-Encoding'   => 'binary',
            'X-Content-Type-Options'      => 'nosniff',
            'Cache-Control'               => 'no-cache, must-revalidate',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}

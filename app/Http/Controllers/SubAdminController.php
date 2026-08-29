<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SubAdminController extends Controller
{
    /**
     * Map of all available sidebar permission keys and descriptions.
     */
    public static function getPermissionsMap()
    {
        return [
            'kyc_approval' => [
                'name' => 'KYC Approval & Active/Deactive',
                'description' => 'Access to approve/reject KYC verifications and toggle active/deactive status for users and drivers.',
                'default' => true,
                'icon' => 'mdi mdi-checkbox-marked-circle-outline'
            ],
            'reply_customer_msg' => [
                'name' => 'Reply Customer MSG',
                'description' => 'Access to view and respond to customer tickets, complaints, and customer care inquiries.',
                'default' => true,
                'icon' => 'mdi mdi-forum'
            ],
            'report_download' => [
                'name' => 'All Type of Report Download',
                'description' => 'Access to view and download all system reports (User Reports, Driver Reports, Travel Reports, etc.).',
                'default' => true,
                'icon' => 'mdi mdi-file-download'
            ],
            'earnings' => [
                'name' => 'Earning Management (10 Sections)',
                'description' => 'Access to view Revenue Dashboard, Service & Marketplace Commissions, P&L Statement, and Earning Reports.',
                'default' => false,
                'icon' => 'mdi mdi-cash-usd'
            ],
            'dashboard' => [
                'name' => 'Dashboard Access',
                'description' => 'Access to view main admin dashboard stats and charts.',
                'default' => false,
                'icon' => 'mdi mdi-home'
            ],
            'user_management' => [
                'name' => 'User Management',
                'description' => 'Access to Consumers, Business Users, and User Activity Logs.',
                'default' => false,
                'icon' => 'mdi mdi-account-multiple'
            ],
            'business_categories' => [
                'name' => 'Business Categories',
                'description' => 'Access to manage business categories.',
                'default' => false,
                'icon' => 'mdi mdi-briefcase'
            ],
            'premium_plans' => [
                'name' => 'Premium Plans',
                'description' => 'Access to Consumer and Business subscription plans.',
                'default' => false,
                'icon' => 'mdi mdi-star-circle'
            ],
            'wallet_financials' => [
                'name' => 'Wallet & Financials',
                'description' => 'Access to transactions, payout requests, and financial engine.',
                'default' => false,
                'icon' => 'mdi mdi-wallet'
            ],
            'referral_engine' => [
                'name' => 'Refer & Earn Engine',
                'description' => 'Access to referral reward configurations.',
                'default' => false,
                'icon' => 'mdi mdi-share-variant'
            ],
            'services_transport' => [
                'name' => 'Services & Transport',
                'description' => 'Access to service requests, vehicle types, brands, models, and operating zones.',
                'default' => false,
                'icon' => 'mdi mdi-wrench'
            ],
            'rides_logistics' => [
                'name' => 'Rides & Logistics',
                'description' => 'Access to all rides, parcel deliveries, and logistics live map.',
                'default' => false,
                'icon' => 'mdi mdi-car'
            ],
            'marketing_campaigns' => [
                'name' => 'Marketing & Campaigns',
                'description' => 'Access to multi-channel campaigns, discount coupons, banners, and push notifications.',
                'default' => false,
                'icon' => 'mdi mdi-bullhorn'
            ],
            'system_settings' => [
                'name' => 'System Settings',
                'description' => 'Access to general settings, API keys, tax configuration, and email templates.',
                'default' => false,
                'icon' => 'mdi mdi-settings'
            ],
            'audit_logs' => [
                'name' => 'Audit Logs',
                'description' => 'Access to view system audit logs.',
                'default' => false,
                'icon' => 'mdi mdi-receipt'
            ],
        ];
    }

    /**
     * Display list of all Sub-Admin staff users.
     */
    public function index()
    {
        $subAdmins = User::where('role', 'sub_admin')->orderBy('id', 'desc')->get();
        $permissionsMap = self::getPermissionsMap();

        return view('administration_tools.sub_admins.index', compact('subAdmins', 'permissionsMap'));
    }

    /**
     * Show form for creating a new Sub-Admin.
     */
    public function create()
    {
        $permissionsMap = self::getPermissionsMap();
        return view('administration_tools.sub_admins.create', compact('permissionsMap'));
    }

    /**
     * Store new Sub-Admin user in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'permissions' => 'nullable|array',
        ]);

        // Default Granted Access: KYC Approval, Reply Customer MSG, All Report Download Access
        $grantedPermissions = $request->input('permissions', ['kyc_approval', 'reply_customer_msg', 'report_download']);

        // Calculate next ID safely to prevent Duplicate entry 0 for key users.PRIMARY (1062) error
        $maxId = (int)\Illuminate\Support\Facades\DB::table('users')->max('id');
        $nextId = max($maxId + 1, 1);

        $user = new User();
        $user->id = $nextId;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 'sub_admin';
        $user->permissions = $grantedPermissions;
        $user->is_active = $request->has('is_active') ? (bool)$request->is_active : true;
        $user->save();

        return redirect()->route('sub-admins.index')->with('success', 'Sub-Admin created successfully with granted dashboard access!');
    }

    /**
     * Show form for editing existing Sub-Admin staff.
     */
    public function edit($id)
    {
        $subAdmin = User::where('role', 'sub_admin')->findOrFail($id);
        $permissionsMap = self::getPermissionsMap();

        return view('administration_tools.sub_admins.edit', compact('subAdmin', 'permissionsMap'));
    }

    /**
     * Update Sub-Admin staff details and sidebar access permissions.
     */
    public function update(Request $request, $id)
    {
        $subAdmin = User::where('role', 'sub_admin')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($subAdmin->id)],
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
        ]);

        $subAdmin->name = $request->name;
        $subAdmin->email = $request->email;
        if ($request->filled('password')) {
            $subAdmin->password = Hash::make($request->password);
        }
        $subAdmin->permissions = $request->input('permissions', []);
        $subAdmin->is_active = $request->has('is_active') ? (bool)$request->is_active : false;
        $subAdmin->save();

        return redirect()->route('sub-admins.index')->with('success', 'Sub-Admin permissions updated successfully!');
    }

    /**
     * Toggle active/deactive status of Sub-Admin.
     */
    public function toggleStatus($id)
    {
        $subAdmin = User::where('role', 'sub_admin')->findOrFail($id);
        $subAdmin->is_active = !$subAdmin->is_active;
        $subAdmin->save();

        return redirect()->back()->with('success', 'Sub-Admin status updated successfully!');
    }

    /**
     * Delete Sub-Admin user.
     */
    public function destroy($id)
    {
        $subAdmin = User::where('role', 'sub_admin')->findOrFail($id);
        $subAdmin->delete();

        return redirect()->route('sub-admins.index')->with('success', 'Sub-Admin user deleted successfully!');
    }
}

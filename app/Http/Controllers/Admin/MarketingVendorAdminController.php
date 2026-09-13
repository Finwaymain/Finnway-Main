<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VendorTeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MarketingVendorAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List all Vendors with status filters
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = DB::table('marketing_vendors')
            ->orderBy('created_at', 'desc');

        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected', 'suspended'])) {
            $query->where('status', $status);
        }

        $vendors = $query->paginate(20);

        // Enhance with user details and counts
        foreach ($vendors as $v) {
            $name = 'Vendor';
            $phone = '';
            $email = '';

            if ($v->user_type === 'customer') {
                $u = DB::table('tj_user_app')->where('id', $v->user_id)->first();
                if ($u) {
                    $name = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Consumer';
                    $phone = $u->phone ?? '';
                    $email = $u->email ?? '';
                }
            } else {
                $d = DB::table('tj_conducteur')->where('id', $v->user_id)->first();
                if ($d) {
                    $name = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Partner';
                    $phone = $d->phone ?? '';
                    $email = $d->email ?? '';
                }
            }

            $v->applicant_name = $name;
            $v->applicant_phone = $phone;
            $v->applicant_email = $email;

            // Counts
            $v->total_members = DB::table('marketing_team_members')->where('vendor_id', $v->id)->count();
            $v->total_customers = DB::table('marketing_acquisitions')
                ->where('vendor_id', $v->id)
                ->where('acquired_user_type', 'customer')
                ->count();
            $v->verified_customers = DB::table('marketing_acquisitions')
                ->where('vendor_id', $v->id)
                ->where('acquired_user_type', 'customer')
                ->where('verification_status', 'verified')
                ->count();

            $v->total_businesses = DB::table('marketing_acquisitions')
                ->where('vendor_id', $v->id)
                ->where('acquired_user_type', 'business')
                ->count();
            $v->verified_businesses = DB::table('marketing_acquisitions')
                ->where('vendor_id', $v->id)
                ->where('acquired_user_type', 'business')
                ->where('verification_status', 'verified')
                ->count();

            $v->total_earnings = round(
                ($v->verified_customers * (float)$v->rate_per_customer) +
                ($v->verified_businesses * (float)$v->rate_per_business),
                2
            );
        }

        $counts = [
            'all'      => DB::table('marketing_vendors')->count(),
            'pending'  => DB::table('marketing_vendors')->where('status', 'pending')->count(),
            'approved' => DB::table('marketing_vendors')->where('status', 'approved')->count(),
            'rejected' => DB::table('marketing_vendors')->where('status', 'rejected')->count(),
        ];

        return view('admin.marketing_vendors.index', compact('vendors', 'status', 'counts'));
    }

    /**
     * Approve Vendor Application & Set Custom Payout Rates
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'rate_per_customer' => 'required|numeric|min:0',
            'rate_per_business' => 'required|numeric|min:0',
        ]);

        $rateCustomer = (float)$request->input('rate_per_customer');
        $rateBusiness = (float)$request->input('rate_per_business');

        $result = VendorTeamService::approveVendor((int)$id, $rateCustomer, $rateBusiness, Auth::id());

        if ($result['success']) {
            return redirect()->back()->with('success', 'Vendor approved successfully! Generated Vendor Code: ' . $result['vendor_code']);
        }

        return redirect()->back()->with('error', $result['message'] ?? 'Failed to approve vendor.');
    }

    /**
     * Reject Vendor Application
     */
    public function reject(Request $request, $id)
    {
        $reason = $request->input('rejection_reason', 'Application did not meet requirements.');
        $ok = VendorTeamService::rejectVendor((int)$id, $reason, Auth::id());

        if ($ok) {
            return redirect()->back()->with('success', 'Vendor application rejected.');
        }

        return redirect()->back()->with('error', 'Failed to reject vendor.');
    }

    /**
     * Detailed Vendor Profile with Team Members and Acquired Users
     */
    public function show($id)
    {
        $vendor = DB::table('marketing_vendors')->where('id', $id)->first();
        if (!$vendor) {
            return redirect()->route('admin.marketing-vendors.index')->with('error', 'Vendor not found.');
        }

        // Applicant info
        $name = 'Vendor';
        $phone = '';
        $email = '';
        if ($vendor->user_type === 'customer') {
            $u = DB::table('tj_user_app')->where('id', $vendor->user_id)->first();
            if ($u) {
                $name = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Consumer';
                $phone = $u->phone ?? '';
                $email = $u->email ?? '';
            }
        } else {
            $d = DB::table('tj_conducteur')->where('id', $vendor->user_id)->first();
            if ($d) {
                $name = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Partner';
                $phone = $d->phone ?? '';
                $email = $d->email ?? '';
            }
        }
        $vendor->applicant_name = $name;
        $vendor->applicant_phone = $phone;
        $vendor->applicant_email = $email;

        // Team members under this vendor
        $teamMembers = DB::table('marketing_team_members')
            ->where('vendor_id', $vendor->id)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($teamMembers as $m) {
            $mName = 'Freelancer';
            $mPhone = '';
            if ($m->user_type === 'customer') {
                $mu = DB::table('tj_user_app')->where('id', $m->user_id)->first();
                if ($mu) {
                    $mName = trim(($mu->prenom ?? '') . ' ' . ($mu->nom ?? '')) ?: 'Consumer';
                    $mPhone = $mu->phone ?? '';
                }
            } else {
                $md = DB::table('tj_conducteur')->where('id', $m->user_id)->first();
                if ($md) {
                    $mName = trim(($md->prenom ?? '') . ' ' . ($md->nom ?? '')) ?: 'Partner';
                    $mPhone = $md->phone ?? '';
                }
            }
            $m->name = $mName;
            $m->phone = $mPhone;

            $m->customers_count = DB::table('marketing_acquisitions')
                ->where('team_member_id', $m->id)
                ->where('acquired_user_type', 'customer')
                ->count();
            $m->businesses_count = DB::table('marketing_acquisitions')
                ->where('team_member_id', $m->id)
                ->where('acquired_user_type', 'business')
                ->count();
        }

        // Acquired users
        $acquisitions = DB::table('marketing_acquisitions')
            ->where('marketing_acquisitions.vendor_id', $vendor->id)
            ->join('marketing_team_members', 'marketing_team_members.id', '=', 'marketing_acquisitions.team_member_id')
            ->select(
                'marketing_acquisitions.*',
                'marketing_team_members.member_code as freelancer_code'
            )
            ->orderBy('marketing_acquisitions.id', 'desc')
            ->paginate(30);

        foreach ($acquisitions as $acq) {
            $acqName = 'User';
            $acqPhone = '';
            $acqKyc = 'No';

            if ($acq->acquired_user_type === 'customer') {
                $u = DB::table('tj_user_app')->where('id', $acq->acquired_user_id)->first();
                if ($u) {
                    $acqName = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Consumer';
                    $acqPhone = $u->phone ?? '';
                    $acqKyc = ($u->statut_nic === 'yes') ? 'Verified' : 'No';
                }
            } else {
                $d = DB::table('tj_conducteur')->where('id', $acq->acquired_user_id)->first();
                if ($d) {
                    $acqName = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Partner';
                    $acqPhone = $d->phone ?? '';
                    $acqKyc = ($d->is_verified == 1) ? 'Verified' : 'No';
                }
            }

            $acq->user_name = $acqName;
            $acq->user_phone = $acqPhone;
            $acq->kyc_status = $acqKyc;
        }

        // Financial Summary
        $custVer = DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendor->id)
            ->where('acquired_user_type', 'customer')
            ->where('verification_status', 'verified')
            ->count();

        $bizVer = DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendor->id)
            ->where('acquired_user_type', 'business')
            ->where('verification_status', 'verified')
            ->count();

        $totalEarned = round(
            ($custVer * (float)$vendor->rate_per_customer) +
            ($bizVer * (float)$vendor->rate_per_business),
            2
        );

        $paidEarned = (float)DB::table('marketing_acquisitions')
            ->where('vendor_id', $vendor->id)
            ->where('payout_status', 'paid')
            ->sum('payout_rate_applied');

        $pendingPayout = max(0, round($totalEarned - $paidEarned, 2));

        $stats = [
            'total_members'       => count($teamMembers),
            'total_acquisitions'  => DB::table('marketing_acquisitions')->where('vendor_id', $vendor->id)->count(),
            'pending_verify'      => DB::table('marketing_acquisitions')->where('vendor_id', $vendor->id)->where('verification_status', 'pending')->count(),
            'verified_customers'  => $custVer,
            'verified_businesses' => $bizVer,
            'total_earned'        => $totalEarned,
            'paid_earned'         => $paidEarned,
            'pending_payout'      => $pendingPayout,
        ];

        return view('admin.marketing_vendors.show', compact('vendor', 'teamMembers', 'acquisitions', 'stats'));
    }

    /**
     * Update Vendor Payout Rates
     */
    public function updateRates(Request $request, $id)
    {
        $request->validate([
            'rate_per_customer' => 'required|numeric|min:0',
            'rate_per_business' => 'required|numeric|min:0',
        ]);

        DB::table('marketing_vendors')->where('id', $id)->update([
            'rate_per_customer' => max(0, (float)$request->input('rate_per_customer')),
            'rate_per_business' => max(0, (float)$request->input('rate_per_business')),
            'updated_at'        => now(),
        ]);

        return redirect()->back()->with('success', 'Vendor payout rates updated successfully.');
    }

    /**
     * Verify Acquisition
     */
    public function verifyAcquisition(Request $request, $id)
    {
        $ok = VendorTeamService::verifyAcquisition((int)$id, Auth::id());
        if ($ok) {
            return redirect()->back()->with('success', 'User acquisition verified successfully. Earnings credited to vendor summary.');
        }
        return redirect()->back()->with('error', 'Failed to verify acquisition or already verified.');
    }

    /**
     * Reject Acquisition
     */
    public function rejectAcquisition(Request $request, $id)
    {
        $reason = $request->input('reason', 'Verification criteria not met.');
        $ok = VendorTeamService::rejectAcquisition((int)$id, $reason, Auth::id());
        if ($ok) {
            return redirect()->back()->with('success', 'Acquisition marked as rejected.');
        }
        return redirect()->back()->with('error', 'Failed to reject acquisition.');
    }

    /**
     * Settle Vendor Payout
     */
    public function settlePayout(Request $request, $id)
    {
        $vendor = DB::table('marketing_vendors')->where('id', $id)->first();
        if (!$vendor) {
            return redirect()->back()->with('error', 'Vendor not found.');
        }

        $reference = trim((string)$request->input('payout_reference', 'Bank Transfer'));

        // Mark all verified unpaid acquisitions as paid
        $updated = DB::table('marketing_acquisitions')
            ->where('vendor_id', $id)
            ->where('verification_status', 'verified')
            ->where('payout_status', 'unpaid')
            ->update([
                'payout_status'    => 'paid',
                'paid_at'          => now(),
                'payout_reference' => $reference,
                'updated_at'       => now(),
            ]);

        return redirect()->back()->with('success', "Settled payout for {$updated} verified acquisitions (Ref: {$reference}).");
    }
}

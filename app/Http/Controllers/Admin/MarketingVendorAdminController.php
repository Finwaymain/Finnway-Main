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
     * List all Vendors with status & hierarchy filters
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $type   = $request->get('type', 'all'); // 'all', 'head', 'sub'

        $query = DB::table('marketing_vendors')
            ->orderBy('created_at', 'desc');

        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected', 'suspended'])) {
            $query->where('status', $status);
        }

        if ($type === 'head') {
            $query->whereNull('parent_vendor_id');
        } elseif ($type === 'sub') {
            $query->whereNotNull('parent_vendor_id');
        }

        $vendors = $query->paginate(20);

        // Enhance with user details, lineage, and counts
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

            // Parent Vendor info if sub-vendor
            $v->parent_code = null;
            $v->parent_name = null;
            if ($v->parent_vendor_id) {
                $pv = DB::table('marketing_vendors')->where('id', $v->parent_vendor_id)->first();
                if ($pv) {
                    $v->parent_code = $pv->vendor_code;
                    $pName = 'Parent';
                    if ($pv->user_type === 'customer') {
                        $pu = DB::table('tj_user_app')->where('id', $pv->user_id)->first();
                        if ($pu) $pName = trim(($pu->prenom ?? '') . ' ' . ($pu->nom ?? '')) ?: 'Parent';
                    } else {
                        $pd = DB::table('tj_conducteur')->where('id', $pv->user_id)->first();
                        if ($pd) $pName = trim(($pd->prenom ?? '') . ' ' . ($pd->nom ?? '')) ?: 'Parent';
                    }
                    $v->parent_name = $pName;
                }
            }

            // Downline Sub-Vendors count
            $v->sub_vendors_count = DB::table('marketing_vendors')->where('parent_vendor_id', $v->id)->count();

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
            'all'          => DB::table('marketing_vendors')->count(),
            'head_vendors' => DB::table('marketing_vendors')->whereNull('parent_vendor_id')->count(),
            'sub_vendors'  => DB::table('marketing_vendors')->whereNotNull('parent_vendor_id')->count(),
            'pending'      => DB::table('marketing_vendors')->where('status', 'pending')->count(),
            'approved'     => DB::table('marketing_vendors')->where('status', 'approved')->count(),
            'rejected'     => DB::table('marketing_vendors')->where('status', 'rejected')->count(),
            'all_users'    => DB::table('marketing_acquisitions')->count(),
        ];

        // For rejected vendors – load users who joined via their code so admin can verify/reject them
        $rejectedAcquisitions = [];
        if ($status === 'rejected') {
            foreach ($vendors as $v) {
                $acqs = DB::table('marketing_acquisitions')
                    ->where('vendor_id', $v->id)
                    ->orderBy('id', 'desc')
                    ->get();

                foreach ($acqs as $acq) {
                    $acqName = 'User'; $acqPhone = ''; $acqType = $acq->acquired_user_type ?? 'customer';
                    if ($acqType === 'customer') {
                        $u = DB::table('tj_user_app')->where('id', $acq->acquired_user_id)->first();
                        if ($u) { $acqName = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Consumer'; $acqPhone = $u->phone ?? ''; }
                    } else {
                        $d = DB::table('tj_conducteur')->where('id', $acq->acquired_user_id)->first();
                        if ($d) { $acqName = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Partner'; $acqPhone = $d->phone ?? ''; }
                    }
                    $acq->user_name = $acqName;
                    $acq->user_phone = $acqPhone;
                    $acq->vendor_code = $v->vendor_code ?: 'VR' . $v->id;
                    $acq->vendor_name = $v->applicant_name;
                    $rejectedAcquisitions[] = $acq;
                }
            }
        }

        // For all_users tab – load all acquired users with member and vendor details
        $allUsers = [];
        if ($status === 'all_users') {
            $allUsers = DB::table('marketing_acquisitions')
                ->leftJoin('marketing_team_members', 'marketing_team_members.id', '=', 'marketing_acquisitions.team_member_id')
                ->leftJoin('marketing_vendors', 'marketing_vendors.id', '=', 'marketing_acquisitions.vendor_id')
                ->select(
                    'marketing_acquisitions.*',
                    'marketing_team_members.member_code as freelancer_code',
                    'marketing_team_members.user_id as freelancer_user_id',
                    'marketing_team_members.user_type as freelancer_user_type',
                    'marketing_vendors.vendor_code as vendor_code_label'
                )
                ->orderBy('marketing_acquisitions.id', 'desc')
                ->paginate(50);

            $customerIds = [];
            $driverIds = [];
            $flCustomerIds = [];
            $flDriverIds = [];

            foreach ($allUsers as $acq) {
                if ($acq->acquired_user_type === 'customer') {
                    $customerIds[] = $acq->acquired_user_id;
                } else {
                    $driverIds[] = $acq->acquired_user_id;
                }

                if (!empty($acq->freelancer_user_id)) {
                    if (($acq->freelancer_user_type ?? 'customer') === 'customer') {
                        $flCustomerIds[] = $acq->freelancer_user_id;
                    } else {
                        $flDriverIds[] = $acq->freelancer_user_id;
                    }
                }
            }

            $customers = !empty($customerIds) ? DB::table('tj_user_app')->whereIn('id', array_unique($customerIds))->get()->keyBy('id') : collect();
            $drivers = !empty($driverIds) ? DB::table('tj_conducteur')->whereIn('id', array_unique($driverIds))->get()->keyBy('id') : collect();
            $flCustomers = !empty($flCustomerIds) ? DB::table('tj_user_app')->whereIn('id', array_unique($flCustomerIds))->get()->keyBy('id') : collect();
            $flDrivers = !empty($flDriverIds) ? DB::table('tj_conducteur')->whereIn('id', array_unique($flDriverIds))->get()->keyBy('id') : collect();

            foreach ($allUsers as $acq) {
                $acqName = 'User';
                $acqPhone = '';

                if ($acq->acquired_user_type === 'customer') {
                    $u = $customers->get($acq->acquired_user_id);
                    if ($u) {
                        $acqName = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: 'Consumer';
                        $acqPhone = $u->phone ?? '';
                    }
                } else {
                    $d = $drivers->get($acq->acquired_user_id);
                    if ($d) {
                        $acqName = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Partner';
                        $acqPhone = $d->phone ?? '';
                    }
                }

                $acq->user_name = $acqName;
                $acq->user_phone = $acqPhone;

                $flName = 'Freelancer';
                if (!empty($acq->freelancer_user_id)) {
                    if (($acq->freelancer_user_type ?? 'customer') === 'customer') {
                        $fu = $flCustomers->get($acq->freelancer_user_id);
                        if ($fu) $flName = trim(($fu->prenom ?? '') . ' ' . ($fu->nom ?? '')) ?: 'Freelancer';
                    } else {
                        $fd = $flDrivers->get($acq->freelancer_user_id);
                        if ($fd) $flName = trim(($fd->prenom ?? '') . ' ' . ($fd->nom ?? '')) ?: 'Freelancer';
                    }
                }
                $acq->freelancer_name = $flName;

                if (empty($acq->vendor_code_label) && $acq->vendor_id) {
                    $acq->vendor_code_label = 'VR' . $acq->vendor_id;
                }
            }
        }

        return view('admin.marketing_vendors.index', compact('vendors', 'counts', 'status', 'type', 'rejectedAcquisitions', 'allUsers'));
    }

    /**
     * Show Vendor Detail with Multi-Level Sub-Vendor Flow, Team & Payment Ledger
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

        // Parent Vendor Info (if Sub-Vendor)
        $parentVendor = null;
        if ($vendor->parent_vendor_id) {
            $parentVendor = DB::table('marketing_vendors')->where('id', $vendor->parent_vendor_id)->first();
            if ($parentVendor) {
                $pName = 'Parent';
                if ($parentVendor->user_type === 'customer') {
                    $pu = DB::table('tj_user_app')->where('id', $parentVendor->user_id)->first();
                    if ($pu) $pName = trim(($pu->prenom ?? '') . ' ' . ($pu->nom ?? '')) ?: 'Parent';
                } else {
                    $pd = DB::table('tj_conducteur')->where('id', $parentVendor->user_id)->first();
                    if ($pd) $pName = trim(($pd->prenom ?? '') . ' ' . ($pd->nom ?? '')) ?: 'Parent';
                }
                $parentVendor->applicant_name = $pName;
            }
        }

        // Downline Sub-Vendors (Direct Children)
        $subVendors = DB::table('marketing_vendors')
            ->where('parent_vendor_id', $vendor->id)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($subVendors as $sv) {
            $svName = 'Sub-Vendor';
            $svPhone = '';
            if ($sv->user_type === 'customer') {
                $su = DB::table('tj_user_app')->where('id', $sv->user_id)->first();
                if ($su) { $svName = trim(($su->prenom ?? '') . ' ' . ($su->nom ?? '')) ?: 'Consumer'; $svPhone = $su->phone ?? ''; }
            } else {
                $sd = DB::table('tj_conducteur')->where('id', $sv->user_id)->first();
                if ($sd) { $svName = trim(($sd->prenom ?? '') . ' ' . ($sd->nom ?? '')) ?: 'Partner'; $svPhone = $sd->phone ?? ''; }
            }
            $sv->applicant_name = $svName;
            $sv->applicant_phone = $svPhone;
            $sv->members_count = DB::table('marketing_team_members')->where('vendor_id', $sv->id)->count();
            $sv->acquisitions_count = DB::table('marketing_acquisitions')->where('vendor_id', $sv->id)->count();
        }

        // Reconcile and heal any duplicate or unlinked team members
        \App\Services\VendorTeamService::reconcileVendorTeamMembers($vendor->id);

        // Team members under this vendor
        $teamMembers = DB::table('marketing_team_members')
            ->where('vendor_id', $vendor->id)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($teamMembers as $m) {
            $mName = 'Freelancer';
            $mPhone = '';

            $userObj = null;
            if ($m->user_type === 'customer') {
                $userObj = DB::table('tj_user_app')->where('id', $m->user_id)->first();
                if (!$userObj) {
                    $userObj = DB::table('tj_conducteur')->where('id', $m->user_id)->first();
                }
            } else {
                $userObj = DB::table('tj_conducteur')->where('id', $m->user_id)->first();
                if (!$userObj) {
                    $userObj = DB::table('tj_user_app')->where('id', $m->user_id)->first();
                }
            }

            if ($userObj) {
                $mName = trim(($userObj->prenom ?? '') . ' ' . ($userObj->nom ?? '')) ?: 'Freelancer';
                $mPhone = $userObj->phone ?? '';
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
                'marketing_team_members.member_code as freelancer_code',
                'marketing_team_members.user_id as freelancer_user_id',
                'marketing_team_members.user_type as freelancer_user_type'
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

            $flName = '—';
            if (!empty($acq->freelancer_user_id)) {
                if (($acq->freelancer_user_type ?? 'customer') === 'customer') {
                    $fu = DB::table('tj_user_app')->where('id', $acq->freelancer_user_id)->first();
                    if ($fu) $flName = trim(($fu->prenom ?? '') . ' ' . ($fu->nom ?? '')) ?: 'Freelancer';
                } else {
                    $fd = DB::table('tj_conducteur')->where('id', $acq->freelancer_user_id)->first();
                    if ($fd) $flName = trim(($fd->prenom ?? '') . ' ' . ($fd->nom ?? '')) ?: 'Freelancer';
                }
            }
            $acq->freelancer_name = $flName;
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

        // Payment Ledgers
        $ledgers = VendorTeamService::getVendorPaymentLedger($vendor->id);

        $stats = [
            'total_sub_vendors'   => count($subVendors),
            'total_members'       => count($teamMembers),
            'total_acquisitions'  => DB::table('marketing_acquisitions')->where('vendor_id', $vendor->id)->count(),
            'pending_verify'      => DB::table('marketing_acquisitions')->where('vendor_id', $vendor->id)->where('verification_status', 'pending')->count(),
            'verified_customers'  => $custVer,
            'verified_businesses' => $bizVer,
            'total_earned'        => $totalEarned,
            'paid_earned'         => $paidEarned,
            'pending_payout'      => $pendingPayout,
        ];

        return view('admin.marketing_vendors.show', compact('vendor', 'parentVendor', 'subVendors', 'teamMembers', 'acquisitions', 'ledgers', 'stats'));
    }

    /**
     * Admin Approves Vendor Application
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'rate_per_customer' => 'required|numeric|min:0',
            'rate_per_business' => 'required|numeric|min:0',
        ]);

        $res = VendorTeamService::approveVendor(
            (int)$id,
            (float)$request->input('rate_per_customer'),
            (float)$request->input('rate_per_business'),
            Auth::id()
        );

        if (!empty($res['success'])) {
            return redirect()->back()->with('success', "Vendor approved successfully with Code {$res['vendor_code']}.");
        }

        return redirect()->back()->with('error', $res['message'] ?? 'Failed to approve vendor.');
    }

    /**
     * Admin Rejects Vendor Application
     */
    public function reject(Request $request, $id)
    {
        $reason = $request->input('reason', 'Application did not meet requirements.');
        $ok = VendorTeamService::rejectVendor((int)$id, $reason, Auth::id());

        if ($ok) {
            return redirect()->back()->with('success', 'Vendor application rejected.');
        }

        return redirect()->back()->with('error', 'Failed to reject vendor.');
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
            return redirect()->back()->with('success', 'User acquisition verified successfully. Earnings and payment ledgers credited.');
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
     * Delete Vendor Request (any status)
     */
    public function destroy($id)
    {
        $vendor = DB::table('marketing_vendors')->where('id', $id)->first();
        if (!$vendor) {
            return redirect()->back()->with('error', 'Vendor not found.');
        }

        // Remove associated acquisitions and team members
        $teamMemberIds = DB::table('marketing_team_members')->where('vendor_id', $id)->pluck('id');
        if ($teamMemberIds->isNotEmpty()) {
            DB::table('marketing_acquisitions')->whereIn('team_member_id', $teamMemberIds)->delete();
        }
        DB::table('marketing_acquisitions')->where('vendor_id', $id)->delete();
        DB::table('marketing_team_members')->where('vendor_id', $id)->delete();
        DB::table('marketing_vendors')->where('id', $id)->delete();

        return redirect()->route('admin.marketing-vendors.index', ['status' => 'all'])
            ->with('success', 'Vendor deleted successfully.');
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

        // Settle ledgers
        DB::table('marketing_payment_ledgers')
            ->where('vendor_id', $id)
            ->where('payment_status', 'unpaid')
            ->update([
                'paid_amount'      => DB::raw('earned_amount'),
                'pending_amount'   => 0.00,
                'payment_status'   => 'paid',
                'payout_reference' => $reference,
                'paid_at'          => now(),
                'updated_at'       => now(),
            ]);

        return redirect()->back()->with('success', "Settled payout for {$updated} verified acquisitions (Ref: {$reference}).");
    }
}

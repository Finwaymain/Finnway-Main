<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceCustomer;
use App\Models\Finance\FinanceDailySchedule;
use App\Models\Finance\FinanceDocument;
use App\Models\Finance\FinanceDocumentRequest;
use App\Models\Finance\FinanceLenderPartner;
use App\Models\Finance\FinanceLoanApplication;
use App\Models\Finance\FinanceLoanProduct;
use App\Models\Finance\FinanceTransaction;
use App\Models\Finance\FinanceWallet;
use App\Services\PhoneService;
use Illuminate\Http\Request;

class AdminFinanceController extends Controller
{
    public function dashboard()
    {
        $today = date('Y-m-d');
        return view('admin.finance.dashboard', [
            'totalCustomers' => FinanceCustomer::count(),
            'totalDrivers' => FinanceCustomer::where('user_type', 'driver')->count(),
            'totalUsers' => FinanceCustomer::where('user_type', 'customer')->count(),
            'activeApplications' => FinanceLoanApplication::whereNotIn('application_status', ['DISBURSED', 'REJECTED', 'CLOSED'])->count(),
            'pendingValidation' => FinanceLoanApplication::whereIn('application_status', ['PROOF_SUBMITTED', 'VALIDATION_PENDING'])->count(),
            'pendingDisbursement' => FinanceLoanApplication::whereIn('application_status', ['LOAN_APPROVED', 'DISBURSEMENT_PENDING', 'DISBURSEMENT_PROCESSING'])->count(),
            'totalDisbursedAmount' => FinanceLoanApplication::where('application_status', 'DISBURSED')->sum('approved_amount'),
            'todayDueAmount' => FinanceDailySchedule::where('schedule_date', $today)->sum('total_due'),
            'todayCollectedAmount' => FinanceDailySchedule::where('schedule_date', $today)->where('status', 'paid')->sum('paid_amount'),
            'overdueCount' => FinanceDailySchedule::where('schedule_date', '<', $today)->where('status', '!=', 'paid')->count(),
            'recentApplications' => FinanceLoanApplication::with('customer')->latest('id')->limit(10)->get(),
        ]);
    }

    public function customers(Request $request)
    {
        $query = FinanceCustomer::query()->latest('id');
        if ($search = $request->input('search')) {
            $variants = PhoneService::getVariants($search);
            $query->where(function ($q) use ($search, $variants) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereIn('phone', $variants)
                  ->orWhere('pan', 'like', "%{$search}%");
            });
        }
        if ($type = $request->input('user_type')) {
            $query->where('user_type', $type);
        }

        $customers = $query->paginate(25);
        return view('admin.finance.customers', compact('customers'));
    }

    public function customerDetails($id)
    {
        $customer = FinanceCustomer::with([
            'documents',
            'applications.product',
            'wallets',
            'dailySchedules' => function ($q) { $q->latest('schedule_date')->limit(30); },
            'transactions' => function ($q) { $q->latest('id')->limit(30); }
        ])->findOrFail($id);

        return view('admin.finance.customer_360', compact('customer'));
    }

    public function applications(Request $request)
    {
        $query = FinanceLoanApplication::with(['customer', 'product', 'lender'])->latest('id');
        if ($status = $request->input('status')) {
            $query->where('application_status', $status);
        }
        if ($category = $request->input('category')) {
            $query->where('loan_category', $category);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_phone', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(25);
        return view('admin.finance.applications', compact('applications'));
    }

    public function applicationDetails($id)
    {
        $application = FinanceLoanApplication::with(['customer.documents', 'product', 'lender', 'dailySchedules'])->findOrFail($id);
        return view('admin.finance.application_details', compact('application'));
    }

    public function reviewDocument(Request $request, $id)
    {
        $doc = FinanceDocument::findOrFail($id);
        $action = $request->input('action'); // 'verify', 'reject', 'reupload'
        $remark = $request->input('remark');

        if ($action === 'verify') {
            $doc->status = 'verified';
            $doc->verified_at = now();
            $doc->admin_remark = $remark ?: 'Verified by administration.';
        } elseif ($action === 'reject') {
            $doc->status = 'rejected';
            $doc->admin_remark = $remark ?: 'Document rejected.';
        } elseif ($action === 'reupload') {
            $doc->status = 'reupload_required';
            $doc->admin_remark = $remark ?: 'Please upload a clearer copy.';
        }

        $doc->save();
        return back()->with('success', 'Document status updated.');
    }

    public function updateApplicationStatus(Request $request, $id)
    {
        $app = FinanceLoanApplication::findOrFail($id);
        $status = $request->input('status');
        $remarks = $request->input('remarks');

        $app->application_status = $status;
        if ($remarks) {
            $app->admin_remarks = $remarks;
        }

        if ($status === 'REJECTED') {
            $app->rejection_reason = $request->input('rejection_reason', 'Underwriting criteria not met.');
            $app->reapply_locked_until = now()->addDays(3); // 3-Day Reapply Lock (Doc 2)
        } elseif ($status === 'LOAN_APPROVED') {
            $approvedAmt = floatval($request->input('approved_amount', $app->requested_amount));
            $app->approved_amount = $approvedAmt;

            // If Virtual Loan or Zero-CIBIL, activate wallet
            if (str_contains($app->loan_category, 'virtual') || str_contains($app->loan_category, 'zero')) {
                FinanceWallet::updateOrCreate(
                    ['customer_id' => $app->customer_id, 'wallet_type' => 'virtual_loan'],
                    [
                        'approved_limit' => $approvedAmt,
                        'available_balance' => $approvedAmt,
                        'daily_usage_limit' => min(5000, $approvedAmt),
                        'today_usage_permission' => 'ACTIVE',
                        'status' => 'active',
                        'valid_until' => now()->addMonths(6),
                    ]
                );

                // Generate initial 30 days daily recovery schedule (Doc 5)
                $dailyEmi = round($approvedAmt / 60, 2);
                for ($d = 1; $d <= 30; $d++) {
                    FinanceDailySchedule::updateOrCreate(
                        ['application_id' => $app->id, 'schedule_date' => now()->addDays($d)->toDateString()],
                        [
                            'customer_id' => $app->customer_id,
                            'day_number' => $d,
                            'emi_amount' => $dailyEmi,
                            'total_due' => $dailyEmi,
                            'status' => 'pending',
                        ]
                    );
                }
            }
        } elseif ($status === 'DISBURSED') {
            $app->disbursement_status = 'disbursed';
            $app->disbursement_txn_ref = $request->input('txn_ref', 'DISB' . time());
            $app->disbursed_at = now();
        }

        $app->save();
        return back()->with('success', "Application updated to {$status}.");
    }

    public function lenderPartners()
    {
        $partners = FinanceLenderPartner::orderBy('sort_order')->get();
        return view('admin.finance.lenders', compact('partners'));
    }

    public function saveLenderPartner(Request $request, $id = null)
    {
        $partner = $id ? FinanceLenderPartner::findOrFail($id) : new FinanceLenderPartner();
        $partner->fill($request->only([
            'name', 'logo', 'min_loan_amount', 'max_loan_amount', 'interest_rate_display',
            'tenure_display', 'processing_fee_display', 'application_url', 'status', 'sort_order'
        ]));
        $partner->save();

        return redirect()->route('admin.finance.lenders')->with('success', 'Lender partner saved.');
    }

    public function recoveryCenter()
    {
        $today = date('Y-m-d');
        $todaySchedules = FinanceDailySchedule::with(['customer', 'application'])
            ->where('schedule_date', $today)
            ->paginate(30);

        $overdueSchedules = FinanceDailySchedule::with(['customer', 'application'])
            ->where('schedule_date', '<', $today)
            ->where('status', '!=', 'paid')
            ->paginate(30);

        return view('admin.finance.recovery', compact('todaySchedules', 'overdueSchedules'));
    }

    public function toggleUsageLock(Request $request, $customerId)
    {
        $wallet = FinanceWallet::where('customer_id', $customerId)
            ->where('wallet_type', 'virtual_loan')
            ->first();

        if ($wallet) {
            $wallet->today_usage_permission = ($wallet->today_usage_permission === 'ACTIVE') ? 'LOCKED' : 'ACTIVE';
            $wallet->save();
            return back()->with('success', "Customer loan usage permission is now {$wallet->today_usage_permission}.");
        }

        return back()->with('error', 'Virtual credit wallet not found.');
    }
}

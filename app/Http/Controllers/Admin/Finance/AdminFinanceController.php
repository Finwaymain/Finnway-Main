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

    public function requestDocument(Request $request, $id)
    {
        $application = FinanceLoanApplication::findOrFail($id);

        $request->validate([
            'document_type' => 'required',
            'admin_remark' => 'nullable|string',
        ]);

        $docTypes = is_array($request->input('document_type'))
            ? $request->input('document_type')
            : [$request->input('document_type')];

        FinanceDocumentRequest::create([
            'customer_id' => $application->customer_id,
            'application_id' => $application->id,
            'requested_documents' => $docTypes,
            'admin_remark' => $request->input('admin_remark', 'Disbursement stage verification document requested by administration.'),
            'status' => 'pending',
            'created_by' => auth()->id() ?? 1,
        ]);

        $application->application_status = 'ADDITIONAL_DOCS_REQUESTED';
        $application->save();

        return back()->with('success', 'Document request sent to applicant successfully.');
    }

    /**
     * Loan Products Master & Processing Fee Configuration
     */
    public function products()
    {
        if (FinanceLoanProduct::count() === 0) {
            try {
                (new \Database\Seeders\FinanceProductSeeder())->run();
            } catch (\Throwable $e) {}
        }

        $products = FinanceLoanProduct::orderBy('sort_order')->get();
        return view('admin.finance.products', compact('products'));
    }

    /**
     * Save / Update Loan Product & Processing Fee Configuration
     */
    public function saveProduct(Request $request, $id = null)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'category' => 'required|string|max:50',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'min_tenure_months' => 'nullable|integer|min:1',
            'max_tenure_months' => 'nullable|integer|min:1',
            'interest_rate_p_a' => 'required|numeric|min:0',
            'processing_fee_type' => 'required|in:fixed,percentage',
            'processing_fee_value' => 'required|numeric|min:0',
            'processing_fee_slabs' => 'nullable|string',
            'daily_repayment_amount' => 'nullable|numeric|min:0',
            'daily_usage_limit' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $product = $id ? FinanceLoanProduct::findOrFail($id) : new FinanceLoanProduct();

        $product->name = $validated['name'];
        $product->code = $validated['code'];
        $product->category = $validated['category'];
        $product->min_amount = $validated['min_amount'];
        $product->max_amount = $validated['max_amount'];
        $product->min_tenure_months = $validated['min_tenure_months'] ?? 1;
        $product->max_tenure_months = $validated['max_tenure_months'] ?? 60;
        $product->interest_rate_p_a = $validated['interest_rate_p_a'];
        $product->is_interest_free = $request->has('is_interest_free') || floatval($validated['interest_rate_p_a']) == 0.0;
        $product->processing_fee_type = $validated['processing_fee_type'];
        $product->processing_fee_value = $validated['processing_fee_value'];

        // Slabs JSON parsing
        if (!empty($validated['processing_fee_slabs'])) {
            $decoded = json_decode($validated['processing_fee_slabs'], true);
            $product->processing_fee_slabs = is_array($decoded) ? $decoded : null;
        }

        $product->daily_repayment_amount = $validated['daily_repayment_amount'] ?? null;
        $product->daily_usage_limit = $validated['daily_usage_limit'] ?? null;
        $product->is_active = $request->has('is_active') ? true : false;
        $product->sort_order = $validated['sort_order'] ?? 0;
        $product->save();

        return redirect()->route('admin.finance.products')->with('success', "Loan Product '{$product->name}' and Processing Fee settings updated successfully.");
    }

    /**
     * Admin Decides / Overrides Processing Fee for a Loan Application
     */
    public function updateApplicationFee(Request $request, $id)
    {
        $application = FinanceLoanApplication::findOrFail($id);

        $validated = $request->validate([
            'processing_fee_amount' => 'required|numeric|min:0',
            'processing_fee_status' => 'required|in:pending,paid,waived',
            'remarks' => 'nullable|string|max:255',
        ]);

        $baseFee = floatval($validated['processing_fee_amount']);
        $status = $validated['processing_fee_status'];

        if ($status === 'waived') {
            $tax = 0.00;
            $totalFee = 0.00;
        } else {
            $tax = round($baseFee * 0.18, 2);
            $totalFee = $baseFee + $tax;
        }

        $application->processing_fee_amount = $baseFee;
        $application->processing_fee_tax = $tax;
        $application->processing_fee_total = $totalFee;
        $application->processing_fee_status = $status;

        if ($status === 'paid') {
            $application->processing_fee_payment_method = $request->input('payment_method', 'admin_approved');
            $application->processing_fee_txn_id = $request->input('txn_id', 'ADM-FEE-' . time());

            if ($application->application_status === 'APPLICATION_CREATED') {
                $application->application_status = 'FEE_PAID';
            }

            FinanceTransaction::create([
                'customer_id' => $application->customer_id,
                'application_id' => $application->id,
                'txn_number' => $application->processing_fee_txn_id,
                'txn_type' => 'fee_payment',
                'amount' => $totalFee,
                'direction' => 'debit',
                'payment_method' => $application->processing_fee_payment_method,
                'payment_gateway_ref' => 'Admin Decision',
                'status' => 'success',
                'notes' => 'Fee set by Admin: ' . ($validated['remarks'] ?? 'Processing fee confirmed'),
            ]);
        }

        $application->save();

        return back()->with('success', 'Application processing fee updated successfully.');
    }

    /**
     * Admin Adjusts Customer Credit Line / Wallet
     */
    public function adjustWallet(Request $request, $walletId)
    {
        $wallet = FinanceWallet::findOrFail($walletId);

        $validated = $request->validate([
            'approved_limit' => 'required|numeric|min:0',
            'available_balance' => 'required|numeric|min:0',
            'daily_usage_limit' => 'required|numeric|min:0',
            'today_usage_permission' => 'required|in:ACTIVE,LOCKED',
            'status' => 'required|in:active,frozen,closed',
        ]);

        $wallet->approved_limit = $validated['approved_limit'];
        $wallet->available_balance = $validated['available_balance'];
        $wallet->daily_usage_limit = $validated['daily_usage_limit'];
        $wallet->today_usage_permission = $validated['today_usage_permission'];
        $wallet->status = $validated['status'];
        $wallet->save();

        return back()->with('success', 'Customer wallet and credit limits adjusted successfully.');
    }

    /**
     * Admin Records Manual Daily Recovery Payment
     */
    public function markRecoveryPaid(Request $request, $scheduleId)
    {
        $schedule = FinanceDailySchedule::findOrFail($scheduleId);

        $schedule->paid_amount = $schedule->total_due;
        $schedule->status = 'paid';
        $schedule->paid_at = now();
        $schedule->payment_method = 'cash_offline';
        $schedule->txn_id = 'REC-MANUAL-' . time();
        $schedule->save();

        // Check if customer has any pending schedule left for today or earlier
        $hasPending = FinanceDailySchedule::where('customer_id', $schedule->customer_id)
            ->where('schedule_date', '<=', date('Y-m-d'))
            ->where('status', '!=', 'paid')
            ->exists();

        if (!$hasPending) {
            FinanceWallet::where('customer_id', $schedule->customer_id)
                ->where('wallet_type', 'virtual_loan')
                ->update(['today_usage_permission' => 'ACTIVE']);
        }

        FinanceTransaction::create([
            'customer_id' => $schedule->customer_id,
            'application_id' => $schedule->application_id,
            'txn_number' => $schedule->txn_id,
            'txn_type' => 'daily_repayment',
            'amount' => $schedule->total_due,
            'direction' => 'credit',
            'payment_method' => 'cash_offline',
            'payment_gateway_ref' => 'Admin Offline Collection',
            'status' => 'success',
            'notes' => 'Manual collection recorded by Admin for ' . $schedule->schedule_date,
        ]);

        return back()->with('success', 'Daily EMI marked as collected and paid. Usage permission refreshed.');
    }
}


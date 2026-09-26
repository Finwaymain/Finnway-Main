<?php

namespace App\Http\Controllers\API\v1\Finance;

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
use App\Models\UserApp;
use App\Models\Driver;
use App\Services\PhoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FinanceApiController extends Controller
{
    /**
     * Resolve Customer Master Profile (Customer 360)
     */
    protected function resolveCustomer(Request $request): ?FinanceCustomer
    {
        $rawPhone = $request->input('phone', $request->input('mobile', $request->query('phone', $request->query('mobile'))));
        $userType = strtolower($request->input('user_type', $request->query('user_type', 'customer')));
        $userId = $request->input('user_id', $request->query('user_id'));
        $driverId = $request->input('driver_id', $request->query('driver_id'));
        $passedName = $request->input('name', $request->query('name'));

        $phone = PhoneService::normalize((string) $rawPhone);
        if (empty($phone) && empty($userId) && empty($driverId)) {
            return null;
        }

        $variants = $phone ? PhoneService::getVariants($phone) : [];

        // 1. Try finding existing FinanceCustomer
        $customer = null;
        if (!empty($variants)) {
            $customer = FinanceCustomer::whereIn('phone', $variants)->first();
        }
        if (!$customer && $userId) {
            $customer = FinanceCustomer::where('user_id', $userId)->where('user_type', 'customer')->first();
        }
        if (!$customer && $driverId) {
            $customer = FinanceCustomer::where('driver_id', $driverId)->where('user_type', 'driver')->first();
        }

        // 2. If not found, resolve from tj_user_app or tj_conducteur and create Master Customer
        if (!$customer) {
            $name = '';
            $email = '';
            $pan = null;

            if ($userType === 'driver' || $driverId) {
                $driver = $driverId ? Driver::find($driverId) : ($phone ? Driver::whereIn('phone', $variants)->first() : null);
                if ($driver) {
                    $driverId = $driver->id;
                    $name = trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? ''));
                    $email = $driver->email ?? '';
                    $phone = $phone ?: PhoneService::normalize($driver->phone);
                }
            } else {
                $user = $userId ? UserApp::find($userId) : ($phone ? UserApp::whereIn('phone', $variants)->first() : null);
                if ($user) {
                    $userId = $user->id;
                    $name = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? ''));
                    $email = $user->email ?? '';
                    $phone = $phone ?: PhoneService::normalize($user->phone);
                }
            }

            if ($phone) {
                $customer = FinanceCustomer::create([
                    'user_type' => ($userType === 'driver' || $driverId) ? 'driver' : 'customer',
                    'user_id' => $userId,
                    'driver_id' => $driverId,
                    'phone' => $phone,
                    'name' => $name ?: ($passedName ?: 'Valued Customer'),
                    'email' => $email,
                    'account_status' => 'active',
                    'kyc_status' => 'pending',
                ]);
            }
        }

        return $customer;
    }


    /**
     * Auth & Context Initialization for WebView
     */
    public function getContext(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to resolve user context. Valid phone or session required.',
            ], 422);
        }

        // Fetch reusable documents within 5-day window (Doc 4)
        $reusableDocs = FinanceDocument::where('customer_id', $customer->id)
            ->where('status', 'verified')
            ->where('is_reusable', true)
            ->where('created_at', '>=', now()->subDays(5))
            ->get(['id', 'document_type', 'document_number', 'file_path', 'verified_at']);

        // Fetch active loans & applications
        $applications = FinanceLoanApplication::where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->get();

        // Fetch active wallets
        $wallets = FinanceWallet::where('customer_id', $customer->id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'reusable_documents' => $reusableDocs,
                'applications' => $applications,
                'wallets' => $wallets,
            ],
        ]);
    }

    /**
     * Active Products Master List
     */
    public function getProducts(Request $request)
    {
        $category = $request->query('category');
        $query = FinanceLoanProduct::where('is_active', true)->orderBy('sort_order');
        if ($category) {
            $query->where('category', $category);
        }
        $products = $query->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Indicative Eligibility & EMI Calculation Engine
     */
    public function calculateEligibility(Request $request)
    {
        $productCode = $request->input('product_code');
        $requestedAmount = floatval($request->input('requested_amount', 50000));
        $tenureMonths = intval($request->input('tenure_months', 24));

        $product = FinanceLoanProduct::where('code', $productCode)->first();
        if (!$product) {
            return response()->json(['success' => false, 'error' => 'Invalid product code.'], 404);
        }

        $minAmount = floatval($product->min_amount);
        $maxAmount = floatval($product->max_amount);
        $amount = min(max($requestedAmount, $minAmount), $maxAmount);

        // Indicative fee
        $fee = floatval($product->processing_fee_value);
        if ($product->processing_fee_type === 'percentage') {
            $fee = round(($amount * $fee) / 100, 2);
        }
        // Check slab overrides if configured (Doc 1 & 4)
        if (!empty($product->processing_fee_slabs)) {
            foreach ($product->processing_fee_slabs as $slab) {
                if (isset($slab['amount']) && floatval($slab['amount']) == $amount) {
                    $fee = floatval($slab['fee'] ?? $fee);
                    break;
                }
            }
        }

        $gst = round($fee * 0.18, 2); // 18% GST on processing fee
        $totalFee = $fee + $gst;

        // EMI Calculation
        $ratePA = floatval($product->interest_rate_p_a);
        $monthlyRate = ($ratePA / 12) / 100;
        $emi = 0.0;
        $totalRepayment = $amount;

        if ($ratePA > 0 && $tenureMonths > 0) {
            $emi = round(($amount * $monthlyRate * pow(1 + $monthlyRate, $tenureMonths)) / (pow(1 + $monthlyRate, $tenureMonths) - 1), 2);
            $totalRepayment = round($emi * $tenureMonths, 2);
        } elseif ($product->is_interest_free && $tenureMonths > 0) {
            $emi = round($amount / $tenureMonths, 2);
            $totalRepayment = $amount;
        }

        // Daily EMI for zero_cibil daily loans (Doc 5)
        $dailyEmi = $product->daily_repayment_amount ?: ($amount > 0 ? round($amount / 100, 2) : 500);

        return response()->json([
            'success' => true,
            'data' => [
                'product_code' => $product->code,
                'requested_amount' => $amount,
                'indicative_amount' => $amount,
                'tenure_months' => $tenureMonths,
                'interest_rate_p_a' => $ratePA,
                'is_interest_free' => $product->is_interest_free,
                'estimated_emi' => $emi,
                'daily_emi' => $dailyEmi,
                'total_repayment' => $totalRepayment,
                'processing_fee_base' => $fee,
                'processing_fee_tax' => $gst,
                'processing_fee_total' => $totalFee,
            ],
        ]);
    }

    /**
     * Upload Document to Common Vault (Doc 4)
     */
    public function uploadVaultDocument(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) {
            return response()->json(['success' => false, 'error' => 'Customer profile not found.'], 404);
        }

        $docType = $request->input('document_type');
        if (empty($docType)) {
            return response()->json(['success' => false, 'error' => 'Document type is required.'], 422);
        }

        $file = $request->file('file') ?: $request->file('document');
        if (!$file) {
            // Check base64
            $base64 = $request->input('base64');
            if (empty($base64)) {
                return response()->json(['success' => false, 'error' => 'No file or image provided.'], 422);
            }
            $data = explode(',', $base64);
            $content = base64_decode(end($data));
            $filename = 'finance/docs/' . $customer->id . '/' . $docType . '_' . time() . '.jpg';
            Storage::disk('public')->put($filename, $content);
            $path = $filename;
            $origName = $docType . '.jpg';
        } else {
            $path = $file->store('finance/docs/' . $customer->id, 'public');
            $origName = $file->getClientOriginalName();
        }

        $doc = FinanceDocument::create([
            'customer_id' => $customer->id,
            'document_type' => $docType,
            'document_number' => $request->input('document_number'),
            'file_path' => $path,
            'file_name' => $origName,
            'status' => 'verified', // Auto-accepted for processing, subject to admin review
            'verified_at' => now(),
            'is_reusable' => true,
            'reuse_valid_until' => now()->addDays(5), // 5-Day Smart Reuse
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded and added to vault.',
            'data' => $doc,
        ]);
    }

    /**
     * Initiate Loan Application
     */
    public function initiateApplication(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) {
            $inputPhone = $request->input('applicant_phone', $request->input('phone', $request->input('mobile')));
            $normalizedPhone = PhoneService::normalize((string) $inputPhone);
            if (!empty($normalizedPhone)) {
                $customer = FinanceCustomer::create([
                    'user_type' => $request->input('user_type', 'customer'),
                    'phone' => $normalizedPhone,
                    'name' => $request->input('applicant_name', 'Valued Customer'),
                    'pan' => $request->input('pan') ? strtoupper($request->input('pan')) : null,
                    'account_status' => 'active',
                    'kyc_status' => 'pending',
                ]);
            }
        }

        if (!$customer) {
            return response()->json(['success' => false, 'error' => 'Please provide a valid 10-digit mobile number.'], 422);
        }

        // Update customer details if provided
        if ($request->filled('pan')) {
            $customer->pan = strtoupper($request->input('pan'));
        }
        if ($request->filled('applicant_name') && ($customer->name === 'Valued Customer' || empty($customer->name))) {
            $customer->name = $request->input('applicant_name');
        }
        $customer->save();

        // Process uploaded KYC documents (Aadhaar Front, Aadhaar Back, PAN Card, Bank Passbook)
        $docMap = [
            'aadhaar_front' => 'Aadhaar Card Front',
            'aadhaar_back' => 'Aadhaar Card Back',
            'pan_card' => 'PAN Card Front',
            'bank_passbook' => 'Bank Passbook Front',
        ];

        foreach ($docMap as $field => $label) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store('finance/docs/' . $customer->id, 'public');
                FinanceDocument::create([
                    'customer_id' => $customer->id,
                    'document_type' => $field,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'status' => 'verified',
                    'verified_at' => now(),
                    'is_reusable' => true,
                    'reuse_valid_until' => now()->addDays(5),
                ]);
            }
        }

        // Check 3-day reapply lock (Doc 2 & 3)
        $latestApp = FinanceLoanApplication::where('customer_id', $customer->id)
            ->where('application_status', 'REJECTED')
            ->latest('id')
            ->first();
        if ($latestApp && $latestApp->reapply_locked_until && $latestApp->reapply_locked_until->isFuture()) {
            return response()->json([
                'success' => false,
                'error' => 'Your previous application was rejected. You can reapply after ' . $latestApp->reapply_locked_until->diffForHumans() . '.',
            ], 422);
        }

        $productCode = $request->input('product_code', 'zero_cibil_daily');
        $product = FinanceLoanProduct::where('code', $productCode)->first();
        $requestedAmount = floatval($request->input('requested_amount', 30000));
        $tenure = intval($request->input('tenure_months', 24));

        // Generate Prefix
        $prefix = 'FIIN-CL-';
        if (str_contains($productCode, 'business')) $prefix = 'FIIN-BL-';
        elseif (str_contains($productCode, 'virtual')) $prefix = 'FIIN-VL-';
        elseif (str_contains($productCode, 'student')) $prefix = 'FIIN-SC-';
        elseif (str_contains($productCode, 'zero')) $prefix = 'FIIN-ZC-';

        $appNumber = $prefix . date('Ymd') . '-' . rand(1000, 9999);

        // Calculate fees
        $feeBase = floatval($product ? $product->processing_fee_value : 2000);
        $tax = round($feeBase * 0.18, 2);
        $totalFee = $feeBase + $tax;

        $application = FinanceLoanApplication::create([
            'application_number' => $appNumber,
            'customer_id' => $customer->id,
            'product_id' => $product ? $product->id : null,
            'user_type' => $customer->user_type,
            'applicant_name' => $request->input('applicant_name', $customer->name),
            'applicant_phone' => $customer->phone,
            'loan_category' => $productCode,
            'requested_amount' => $requestedAmount,
            'indicative_amount' => $requestedAmount,
            'tenure_months' => $tenure,
            'estimated_emi' => floatval($request->input('estimated_emi', 0)),
            'estimated_total_repayment' => floatval($request->input('estimated_total_repayment', $requestedAmount)),
            'processing_fee_amount' => $feeBase,
            'processing_fee_tax' => $tax,
            'processing_fee_total' => $totalFee,
            'processing_fee_status' => 'pending',
            'application_status' => 'APPLICATION_CREATED',
            'business_details' => $request->input('business_details'),
            'student_details' => $request->input('student_details'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application created successfully.',
            'data' => $application,
        ]);
    }


    /**
     * Confirm Processing Fee Payment
     */
    public function confirmFeePayment(Request $request, $id)
    {
        $application = FinanceLoanApplication::find($id);
        if (!$application) {
            return response()->json(['success' => false, 'error' => 'Application not found.'], 404);
        }

        $paymentMethod = $request->input('payment_method', 'online');
        $txnId = $request->input('txn_id', 'TXN' . time() . rand(100, 999));

        $application->processing_fee_status = 'paid';
        $application->processing_fee_payment_method = $paymentMethod;
        $application->processing_fee_txn_id = $txnId;
        $application->application_status = 'FEE_PAID';
        $application->save();

        FinanceTransaction::create([
            'customer_id' => $application->customer_id,
            'application_id' => $application->id,
            'txn_number' => $txnId,
            'txn_type' => 'fee_payment',
            'amount' => $application->processing_fee_total,
            'direction' => 'debit',
            'payment_method' => $paymentMethod,
            'payment_gateway_ref' => $txnId,
            'status' => 'success',
            'notes' => 'Processing fee for ' . $application->application_number,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Processing fee payment recorded.',
            'data' => $application,
        ]);
    }

    /**
     * Lender Partners List (Active Banks/NBFCs)
     */
    public function getLenderPartners(Request $request)
    {
        $partners = FinanceLenderPartner::active()->orderBy('sort_order')->get();
        return response()->json(['success' => true, 'data' => $partners]);
    }

    /**
     * Single-Partner Selection & Lock (Doc 2 & 3)
     */
    public function selectPartner(Request $request, $id)
    {
        $application = FinanceLoanApplication::find($id);
        if (!$application) {
            return response()->json(['success' => false, 'error' => 'Application not found.'], 404);
        }

        $partnerId = $request->input('partner_id');
        $partner = FinanceLenderPartner::find($partnerId);
        if (!$partner) {
            return response()->json(['success' => false, 'error' => 'Lender partner not found.'], 404);
        }

        // Single-partner lock: partner is recorded and others are locked
        $application->selected_lender_id = $partner->id;
        $application->selected_lender_name = $partner->name;
        $application->partner_selection_time = now();
        $application->application_status = 'PARTNER_SELECTED';
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Partner selected and locked. Proceed to partner portal.',
            'data' => [
                'application' => $application,
                'partner' => $partner,
                'portal_url' => $partner->application_url,
            ],
        ]);
    }

    /**
     * Submit Lender Completion Proof (Screenshot)
     */
    public function submitProof(Request $request, $id)
    {
        $application = FinanceLoanApplication::find($id);
        if (!$application) {
            return response()->json(['success' => false, 'error' => 'Application not found.'], 404);
        }

        $file = $request->file('proof');
        if (!$file) {
            return response()->json(['success' => false, 'error' => 'Proof screenshot is required.'], 422);
        }

        $path = $file->store('finance/proofs/' . $application->customer_id, 'public');
        $application->process_completion_proof_url = $path;
        $application->proof_submitted_at = now();
        $application->proof_remarks = $request->input('remarks');
        $application->application_status = 'VALIDATION_PENDING';
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Proof submitted. Review is underway (estimated: 3 minutes).',
            'data' => $application,
        ]);
    }

    /**
     * Submit Agent Selfie Verification
     */
    public function submitAgentSelfie(Request $request, $id)
    {
        $application = FinanceLoanApplication::find($id);
        if (!$application) {
            return response()->json(['success' => false, 'error' => 'Application not found.'], 404);
        }

        $file = $request->file('selfie');
        if (!$file) {
            return response()->json(['success' => false, 'error' => 'Selfie image is required.'], 422);
        }

        $path = $file->store('finance/selfies/' . $application->customer_id, 'public');
        $application->agent_selfie_url = $path;
        $application->agent_selfie_submitted_at = now();
        $application->application_status = 'AGENT_VERIFIED';
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Agent selfie submitted successfully.',
            'data' => $application,
        ]);
    }

    /**
     * Submit Disbursement Bank Account
     */
    public function submitDisbursementAccount(Request $request, $id)
    {
        $application = FinanceLoanApplication::find($id);
        if (!$application) {
            return response()->json(['success' => false, 'error' => 'Application not found.'], 404);
        }

        $application->disbursement_bank_name = $request->input('bank_name');
        $application->disbursement_account_name = $request->input('account_name');
        $application->disbursement_account_number = $request->input('account_number');
        $application->disbursement_ifsc = strtoupper(trim((string) $request->input('ifsc')));
        $application->disbursement_account_type = $request->input('account_type', 'savings');
        $application->disbursement_status = 'processing';
        $application->application_status = 'DISBURSEMENT_PROCESSING';
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Disbursement details submitted.',
            'data' => $application,
        ]);
    }

    /**
     * User Finance Dashboard (Active loans, daily limit & usage lock status)
     */
    public function getDashboard(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) {
            return response()->json(['success' => false, 'error' => 'Customer profile not found.'], 404);
        }

        // Active Wallets
        $wallets = FinanceWallet::where('customer_id', $customer->id)->get();

        // Today's Daily Schedule for Zero-CIBIL Daily Recovery (Doc 5)
        $today = date('Y-m-d');
        $todaySchedule = FinanceDailySchedule::where('customer_id', $customer->id)
            ->where('schedule_date', $today)
            ->first();

        // Overdue Count
        $overdueCount = FinanceDailySchedule::where('customer_id', $customer->id)
            ->where('schedule_date', '<', $today)
            ->where('status', '!=', 'paid')
            ->count();

        $totalOverdueAmount = FinanceDailySchedule::where('customer_id', $customer->id)
            ->where('schedule_date', '<', $today)
            ->where('status', '!=', 'paid')
            ->sum('total_due');

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'wallets' => $wallets,
                'today_schedule' => $todaySchedule,
                'overdue_count' => $overdueCount,
                'overdue_amount' => floatval($totalOverdueAmount),
                'daily_usage_locked' => ($overdueCount > 0 || ($todaySchedule && $todaySchedule->status !== 'paid')),
            ],
        ]);
    }

    /**
     * Daily EMI Repayment (Instantly Unlocks Usage Limit - Doc 5)
     */
    public function repayDailyEmi(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) {
            return response()->json(['success' => false, 'error' => 'Customer profile not found.'], 404);
        }

        $scheduleId = $request->input('schedule_id');
        $schedule = null;
        if ($scheduleId) {
            $schedule = FinanceDailySchedule::where('customer_id', $customer->id)->find($scheduleId);
        } else {
            // Find earliest pending/overdue schedule
            $schedule = FinanceDailySchedule::where('customer_id', $customer->id)
                ->where('status', '!=', 'paid')
                ->orderBy('schedule_date')
                ->first();
        }

        if (!$schedule) {
            return response()->json(['success' => false, 'error' => 'No pending daily repayments found.'], 404);
        }

        $paymentMethod = $request->input('payment_method', 'online');
        $txnId = 'REPAY' . time() . rand(100, 999);

        $schedule->paid_amount = $schedule->total_due;
        $schedule->status = 'paid';
        $schedule->paid_at = now();
        $schedule->payment_method = $paymentMethod;
        $schedule->txn_id = $txnId;
        $schedule->save();

        // Check if all past & today's schedules are paid -> UNLOCK USAGE (Doc 5)
        $hasPending = FinanceDailySchedule::where('customer_id', $customer->id)
            ->where('schedule_date', '<=', date('Y-m-d'))
            ->where('status', '!=', 'paid')
            ->exists();

        if (!$hasPending) {
            FinanceWallet::where('customer_id', $customer->id)
                ->where('wallet_type', 'virtual_loan')
                ->update(['today_usage_permission' => 'ACTIVE']);
        }

        FinanceTransaction::create([
            'customer_id' => $customer->id,
            'application_id' => $schedule->application_id,
            'txn_number' => $txnId,
            'txn_type' => 'daily_repayment',
            'amount' => $schedule->total_due,
            'direction' => 'credit',
            'payment_method' => $paymentMethod,
            'payment_gateway_ref' => $txnId,
            'status' => 'success',
            'notes' => 'Daily EMI paid for ' . $schedule->schedule_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daily repayment successful. Today\'s loan usage limit is unlocked!',
            'data' => [
                'schedule' => $schedule,
                'usage_unlocked' => !$hasPending,
            ],
        ]);
    }
}

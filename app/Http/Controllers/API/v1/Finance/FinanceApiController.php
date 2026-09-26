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
        $rawPhone = $request->input('phone', $request->input('mobile', $request->input('applicant_phone', $request->query('phone', $request->query('mobile')))));
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
        if (FinanceLoanProduct::count() === 0) {
            try {
                (new \Database\Seeders\FinanceProductSeeder())->run();
            } catch (\Throwable $e) {}
        }

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
        if (!$product && FinanceLoanProduct::count() === 0) {
            try {
                (new \Database\Seeders\FinanceProductSeeder())->run();
                $product = FinanceLoanProduct::where('code', $productCode)->first();
            } catch (\Throwable $e) {}
        }

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
                $variants = PhoneService::getVariants($normalizedPhone);
                $customer = FinanceCustomer::whereIn('phone', $variants)->first();
                if (!$customer) {
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

        // Process uploaded KYC documents (Aadhaar Front, Aadhaar Back, PAN Card)
        // NOTE: Bank Passbook/Statement/Cheque are NOT accepted here.
        // Admin will request bank documents at disbursement stage via the doc request engine.
        $docMap = [
            'aadhaar_front' => 'Aadhaar Card Front',
            'aadhaar_back' => 'Aadhaar Card Back',
            'pan_card' => 'PAN Card Front',
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

    // ─────────────────────────────────────────────────────────────
    // 14. SAVE APPLICANT DETAILS  POST .../applications/{id}/save-applicant-details
    // ─────────────────────────────────────────────────────────────
    public function saveApplicantDetails(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $application = FinanceLoanApplication::findOrFail($id);

        $validated = $request->validate([
            'full_name'       => 'required|string|max:150',
            'date_of_birth'   => 'required|date',
            'gender'          => 'required|in:male,female,other',
            'employment_type' => 'required|in:salaried,self_employed,business,student,other',
            'monthly_income'  => 'nullable|numeric|min:0',
            'email'           => 'nullable|email',
            'address_line1'   => 'nullable|string|max:255',
            'city'            => 'nullable|string|max:100',
            'state'           => 'nullable|string|max:100',
            'pincode'         => 'nullable|string|max:10',
        ]);

        $application->applicant_details = array_merge(
            $application->applicant_details ?? [],
            $validated
        );
        $application->application_status = 'applicant_details_saved';
        $application->save();

        return response()->json(['success' => true, 'message' => 'Applicant details saved.', 'data' => $application]);
    }

    // ─────────────────────────────────────────────────────────────
    // 15. SAVE BUSINESS DETAILS  POST .../applications/{id}/save-business-details
    // ─────────────────────────────────────────────────────────────
    public function saveBusinessDetails(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $application = FinanceLoanApplication::findOrFail($id);

        $validated = $request->validate([
            'business_name'     => 'required|string|max:200',
            'business_type'     => 'required|string|max:100',
            'gst_number'        => 'nullable|string|max:20',
            'pan_number'        => 'nullable|string|max:20',
            'annual_turnover'   => 'nullable|numeric|min:0',
            'years_in_business' => 'nullable|integer|min:0',
            'business_address'  => 'nullable|string|max:500',
            'business_city'     => 'nullable|string|max:100',
            'business_state'    => 'nullable|string|max:100',
            'business_pincode'  => 'nullable|string|max:10',
        ]);

        $application->business_details = array_merge(
            $application->business_details ?? [],
            $validated
        );
        $application->application_status = 'business_details_saved';
        $application->save();

        return response()->json(['success' => true, 'message' => 'Business details saved.', 'data' => $application]);
    }

    // ─────────────────────────────────────────────────────────────
    // 16. SELECT TENURE  POST .../applications/{id}/select-tenure
    // ─────────────────────────────────────────────────────────────
    public function selectTenure(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $application = FinanceLoanApplication::findOrFail($id);

        $validated = $request->validate([
            'tenure_months'   => 'required|integer|min:1|max:360',
            'confirmed_emi'   => 'required|numeric|min:0',
        ]);

        $application->tenure_months = $validated['tenure_months'];
        $application->applicant_details = array_merge(
            $application->applicant_details ?? [],
            ['confirmed_emi' => $validated['confirmed_emi']]
        );
        $application->application_status = 'tenure_selected';
        $application->save();

        return response()->json(['success' => true, 'message' => 'Tenure confirmed.', 'data' => $application]);
    }

    // ─────────────────────────────────────────────────────────────
    // 17. VERIFY PARTNER (before lock)  POST .../applications/{id}/verify-partner
    // ─────────────────────────────────────────────────────────────
    public function verifyPartner(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $application = FinanceLoanApplication::findOrFail($id);

        if ($application->partner_lock_status === 'locked') {
            return response()->json(['success' => false, 'message' => 'Partner already locked for this application.'], 422);
        }

        $validated = $request->validate([
            'lender_id'          => 'required|integer',
            'applicant_name_at_lender' => 'required|string|max:150',
            'lender_reference_no'      => 'nullable|string|max:100',
        ]);

        $lender = FinanceLenderPartner::find($validated['lender_id']);
        if (!$lender || !$lender->is_active) {
            return response()->json(['success' => false, 'message' => 'Invalid or inactive lender.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Partner verified. Proceed to confirm lock.',
            'data' => [
                'lender_name'  => $lender->name,
                'lender_id'    => $lender->id,
                'applicant_name_at_lender' => $validated['applicant_name_at_lender'],
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 18. ACTIVATE WALLET  POST .../applications/{id}/activate-wallet
    // Called by admin after approving a Flow B application
    // ─────────────────────────────────────────────────────────────
    public function activateWallet(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $application = FinanceLoanApplication::findOrFail($id);

        if ($application->flow_type !== 'internal_wallet') {
            return response()->json(['success' => false, 'message' => 'Wallet activation is only for internal loan products.'], 422);
        }

        if (FinanceWallet::where('application_id', $application->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Wallet already activated for this application.'], 409);
        }

        $validated = $request->validate([
            'approved_amount' => 'required|numeric|min:1',
            'daily_limit'     => 'required|numeric|min:1',
            'tenure_months'   => 'required|integer|min:1',
        ]);

        $wallet = FinanceWallet::create([
            'customer_id'       => $application->customer_id,
            'application_id'    => $application->id,
            'wallet_type'       => 'credit_line',
            'total_limit'       => $validated['approved_amount'],
            'available_balance' => $validated['approved_amount'],
            'daily_usage_limit' => $validated['daily_limit'],
            'status'            => 'active',
            'activated_at'      => now(),
            'expires_at'        => now()->addMonths($validated['tenure_months']),
        ]);

        $application->approved_amount = $validated['approved_amount'];
        $application->application_status = 'active';
        $application->disbursement_date = now()->toDateString();
        $application->save();

        return response()->json(['success' => true, 'message' => 'Wallet activated successfully.', 'data' => $wallet]);
    }

    // ─────────────────────────────────────────────────────────────
    // 19. GET WALLET STATUS  GET .../wallet/{wallet_id}/status
    // ─────────────────────────────────────────────────────────────
    public function getWalletStatus(Request $request, $walletId): \Illuminate\Http\JsonResponse
    {
        $wallet = FinanceWallet::findOrFail($walletId);

        $todaySchedule = FinanceDailySchedule::where('application_id', $wallet->application_id)
            ->where('schedule_date', now()->toDateString())
            ->first();

        $hasPendingToday = $todaySchedule && $todaySchedule->status === 'pending';

        return response()->json([
            'success' => true,
            'data' => [
                'wallet'           => $wallet,
                'today_schedule'   => $todaySchedule,
                'usage_unlocked'   => !$hasPendingToday,
                'available_today'  => $hasPendingToday ? 0 : $wallet->daily_usage_limit,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 20. PROCESS QR PAYMENT  POST .../wallet/{wallet_id}/qr-payment
    // 8-condition gate before approving any daily QR spend
    // ─────────────────────────────────────────────────────────────
    public function processQrPayment(Request $request, $walletId): \Illuminate\Http\JsonResponse
    {
        $wallet = FinanceWallet::findOrFail($walletId);

        $validated = $request->validate([
            'amount'       => 'required|numeric|min:1',
            'merchant_qr'  => 'required|string',
            'description'  => 'nullable|string|max:255',
        ]);

        $amount = $validated['amount'];

        // 8-condition gate
        if ($wallet->status !== 'active')
            return response()->json(['success' => false, 'message' => 'Wallet is not active.'], 422);
        if ($wallet->available_balance < $amount)
            return response()->json(['success' => false, 'message' => 'Insufficient wallet balance.'], 422);
        if ($amount > $wallet->daily_usage_limit)
            return response()->json(['success' => false, 'message' => 'Amount exceeds daily usage limit.'], 422);
        if (now()->gt($wallet->expires_at))
            return response()->json(['success' => false, 'message' => 'Wallet has expired.'], 422);

        $todaySchedule = FinanceDailySchedule::where('application_id', $wallet->application_id)
            ->where('schedule_date', now()->toDateString())
            ->first();
        if ($todaySchedule && $todaySchedule->status === 'pending')
            return response()->json(['success' => false, 'message' => 'Today\'s EMI is pending. Pay EMI to unlock usage.'], 422);

        $todaySpent = FinanceTransaction::where('wallet_id', $wallet->id)
            ->whereDate('created_at', now()->toDateString())
            ->where('txn_type', 'qr_payment')
            ->sum('amount');
        if (($todaySpent + $amount) > $wallet->daily_usage_limit)
            return response()->json(['success' => false, 'message' => 'Daily spending limit would be exceeded.'], 422);
        if (empty($validated['merchant_qr']))
            return response()->json(['success' => false, 'message' => 'Invalid QR code.'], 422);
        if ($wallet->customer_id !== $this->resolveCustomer($request)->id)
            return response()->json(['success' => false, 'message' => 'Wallet does not belong to this customer.'], 403);

        $txnId = 'QR-' . strtoupper(uniqid());
        $wallet->available_balance -= $amount;
        $wallet->save();

        FinanceTransaction::create([
            'customer_id'        => $wallet->customer_id,
            'wallet_id'          => $wallet->id,
            'application_id'     => $wallet->application_id,
            'txn_number'         => $txnId,
            'txn_type'           => 'qr_payment',
            'amount'             => $amount,
            'direction'          => 'debit',
            'payment_method'     => 'qr',
            'payment_gateway_ref'=> $validated['merchant_qr'],
            'status'             => 'success',
            'notes'              => $validated['description'] ?? 'QR Payment',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment successful.',
            'data'    => ['txn_number' => $txnId, 'amount' => $amount, 'new_balance' => $wallet->available_balance],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 21. SUBMIT ADDITIONAL DOCS (Admin-requested — bank docs etc.)
    //     POST .../applications/{id}/additional-docs
    // ─────────────────────────────────────────────────────────────
    public function submitAdditionalDocs(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $application = FinanceLoanApplication::findOrFail($id);
        $customer = FinanceCustomer::findOrFail($application->customer_id);

        $request->validate([
            'doc_request_id' => 'required|integer',
            'files'          => 'required|array|min:1',
            'files.*'        => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $docRequest = FinanceDocumentRequest::findOrFail($request->input('doc_request_id'));

        if ($docRequest->application_id !== $application->id) {
            return response()->json(['success' => false, 'message' => 'Document request does not belong to this application.'], 403);
        }

        $uploadedPaths = [];
        $docType = is_array($docRequest->requested_documents) ? ($docRequest->requested_documents[0] ?? 'additional_doc') : ($docRequest->document_type ?? 'additional_doc');
        foreach ($request->file('files') as $file) {
            $path = $file->store('finance/additional-docs/' . $customer->id, 'public');
            FinanceDocument::create([
                'customer_id'    => $customer->id,
                'document_type'  => $docType,
                'file_path'      => $path,
                'file_name'      => $file->getClientOriginalName(),
                'status'         => 'pending',
                'is_reusable'    => false,
            ]);
            $uploadedPaths[] = $path;
        }

        $docRequest->status = 'submitted';
        $docRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Documents submitted successfully. Admin will review shortly.',
            'data'    => ['uploaded' => count($uploadedPaths)],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 22. GET APPLICATION STATUS  GET .../applications/{id}/status
    // ─────────────────────────────────────────────────────────────
    public function getApplicationStatus(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $application = FinanceLoanApplication::with([
            'documents',
            'documentRequests',
            'wallet',
        ])->findOrFail($id);

        $pendingDocRequests = FinanceDocumentRequest::where('application_id', $application->id)
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'application'          => $application,
                'pending_doc_requests' => $pendingDocRequests,
                'has_pending_docs'     => $pendingDocRequests->count() > 0,
            ],
        ]);
    }
}

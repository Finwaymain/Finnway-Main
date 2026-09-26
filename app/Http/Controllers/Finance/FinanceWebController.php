<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceCustomer;
use App\Models\Finance\FinanceDocument;
use App\Models\Finance\FinanceDocumentRequest;
use App\Models\Finance\FinanceLenderPartner;
use App\Models\Finance\FinanceLoanApplication;
use App\Models\Finance\FinanceLoanProduct;
use App\Models\Finance\FinanceWallet;
use Illuminate\Http\Request;

/**
 * FinanceWebController
 *
 * Serves all Blade views for the Finance WebView portal.
 * Loaded inside Flutter via WebView — NOT a native Flutter UI.
 *
 * Flow A (External Bank): Cash Loan (26 screens), Business Loan (20 screens)
 * Flow B (Internal Wallet): Zero-CIBIL (7 screens), Virtual Loan (5 screens), Student Credit (9 screens)
 */
class FinanceWebController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // SHARED HELPERS
    // ─────────────────────────────────────────────────────────────

    private function resolveContext(Request $request): array
    {
        $phone = $request->query('phone', $request->query('mobile', $request->input('phone', $request->input('mobile'))));
        $customer = null;
        $application = null;

        if ($phone) {
            $customer = FinanceCustomer::where('phone', $phone)->first();
            if ($customer) {
                $application = FinanceLoanApplication::where('customer_id', $customer->id)
                    ->orderBy('id', 'desc')
                    ->first();
            }
        }

        // Amount resolution (Request parameter -> Application -> Default 25,000)
        $rawAmount = $request->query('amount', $request->input('amount', $application->requested_amount ?? 25000));
        $amount = floatval($rawAmount);
        if ($amount <= 0) {
            $amount = 25000;
        }

        // Tenure resolution (Request parameter -> Application -> Default 12 months)
        $rawTenure = $request->query('tenure', $request->input('tenure', $application->tenure_months ?? 12));
        $tenure = intval($rawTenure);
        if ($tenure <= 0) {
            $tenure = 12;
        }

        $loanType = $request->query('loan_type', $request->input('loan_type', $application->loan_type ?? 'low_cibil'));

        // Mathematical EMI calculation: P * r * (1+r)^n / ((1+r)^n - 1)
        // Standard personal loan indicative rate: 9% p.a. -> monthly r = 0.09 / 12 = 0.0075
        $annualRate = 0.09;
        $monthlyRate = $annualRate / 12;
        $pow = pow(1 + $monthlyRate, $tenure);
        $monthlyEmi = $pow > 1 ? ($amount * $monthlyRate * $pow) / ($pow - 1) : ($amount / $tenure);
        $totalRepayment = $monthlyEmi * $tenure;
        $totalInterest = max(0, $totalRepayment - $amount);

        // Dynamic fee calculation (2% of amount, min 999, max 2500, unless overridden by admin)
        $baseFee = max(999, min(2500, round($amount * 0.02, 2)));
        if ($application && $application->processing_fee_base > 0) {
            $baseFee = floatval($application->processing_fee_base);
        }
        $feeTax = round($baseFee * 0.18, 2);
        $totalFee = $baseFee + $feeTax;

        // Product category & Lender existence check: Flow A has lender, Flow B does NOT have lender
        $productCategory = $application->loan_category ?? $request->query('category', 'low_cibil_cash');
        $hasLender = in_array($productCategory, ['low_cibil_cash', 'prime_cash', 'business_msme', 'cash_loan', 'business_loan']);

        return [
            'customer' => $customer,
            'phone' => $phone,
            'application' => $application,
            'amount' => $amount,
            'tenure' => $tenure,
            'loanType' => $loanType,
            'emi' => round($monthlyEmi),
            'totalRepayment' => round($totalRepayment),
            'totalInterest' => round($totalInterest),
            'baseFee' => $baseFee,
            'feeTax' => $feeTax,
            'totalFee' => $totalFee,
            'hasLender' => $hasLender,
        ];
    }

    private function getApplicationOr404($id): FinanceLoanApplication
    {
        return FinanceLoanApplication::findOrFail($id);
    }

    // ─────────────────────────────────────────────────────────────
    // HUB — Product Selection Landing
    // ─────────────────────────────────────────────────────────────

    public function hub(Request $request)
    {
        $cardType = strtolower(trim($request->query('card_type', '')));
        if ($cardType) {
            $params = $request->all();
            if (in_array($cardType, ['zero_cibil', '0 cibil loan', 'interest_free', 'interest free loan'])) {
                return redirect()->route('finance.zero_cibil.s01_intro', $params);
            }
            if (in_array($cardType, ['low_cibil', 'low cibil loan', 'cash', 'cash loan', 'cash_loan'])) {
                return redirect()->route('finance.cash_loan.s01_apply', $params);
            }
            if (in_array($cardType, ['business', 'business loan', 'business_loan'])) {
                return redirect()->route('finance.business_loan.s01_apply', $params);
            }
            if (in_array($cardType, ['virtual', 'virtual loan', 'virtual_loan'])) {
                return redirect()->route('finance.virtual_loan.s01_apply', $params);
            }
            if (in_array($cardType, ['student', 'student credit', 'student_credit'])) {
                return redirect()->route('finance.student_credit.s01_apply', $params);
            }
        }

        $products = FinanceLoanProduct::where('is_active', true)->get();
        $ctx = $this->resolveContext($request);
        return view('finance.hub', array_merge($ctx, ['products' => $products]));
    }

    // ─────────────────────────────────────────────────────────────
    // STATE PERSISTENCE ENGINE (Dynamic Step Saver)
    // ─────────────────────────────────────────────────────────────

    public function saveCashLoanStep(Request $request)
    {
        $step = $request->input('step', 's01');
        $phone = $request->input('phone', $request->query('phone', $request->input('mobile')));
        $queryParams = ['phone' => $phone];

        // 1. Ensure Customer exists
        $customer = null;
        if ($phone) {
            $customer = FinanceCustomer::firstOrCreate(
                ['phone' => $phone],
                [
                    'full_name' => $request->input('applicant_name') ?: 'Customer',
                    'email' => $request->input('email'),
                    'pan_number' => $request->input('pan_number'),
                    'user_type' => 'customer',
                    'status' => 'active',
                ]
            );
            if ($request->filled('applicant_name')) {
                $customer->update(['full_name' => $request->input('applicant_name')]);
            }
        }

        // 2. Fetch or create Application
        $application = null;
        if ($customer) {
            $application = FinanceLoanApplication::where('customer_id', $customer->id)
                ->whereNotIn('application_status', ['DISBURSED', 'REJECTED'])
                ->orderBy('id', 'desc')
                ->first();

            if (!$application) {
                $category = $request->input('loan_type', 'low_cibil') === 'good_cibil' ? 'prime_cash' : 'low_cibil_cash';
                $product = FinanceLoanProduct::where('category', $category)->first();
                $applicantName = $request->input('applicant_name') ?: ($customer->full_name ?: 'Applicant');
                $initAmount = floatval($request->input('requested_amount', $request->input('amount', 25000)));
                if ($initAmount <= 0) $initAmount = 25000;

                $application = FinanceLoanApplication::create([
                    'customer_id' => $customer->id,
                    'loan_product_id' => $product->id ?? 1,
                    'application_number' => 'FIIN-' . strtoupper(uniqid()),
                    'applicant_name' => $applicantName,
                    'applicant_phone' => $customer->phone,
                    'loan_category' => $category,
                    'requested_amount' => $initAmount,
                    'tenure_months' => intval($request->input('tenure', 12)),
                    'application_status' => 'DRAFT',
                    'partner_lock_status' => 'unlocked',
                    'processing_fee_base' => 999.00,
                    'processing_fee_tax' => 179.82,
                    'processing_fee_total' => 1178.82,
                    'fee_payment_status' => 'pending',
                ]);
            }
        }

        // 3. Handle step specific saves
        if ($step === 's01') {
            $loanType = $request->input('loan_type', 'low_cibil');
            $category = $loanType === 'good_cibil' ? 'prime_cash' : 'low_cibil_cash';
            if ($application) {
                $application->update(['loan_category' => $category]);
            }
            $queryParams['loan_type'] = $loanType;
            return redirect()->route('finance.cash_loan.s02_type_consent', $queryParams);
        }

        if ($step === 's02') {
            return redirect()->route('finance.cash_loan.s03_applicant_details', $queryParams);
        }

        if ($step === 's03') {
            $name = $request->input('applicant_name', 'Customer');
            $pan = strtoupper($request->input('pan_number', ''));
            $email = $request->input('email', '');
            $dob = $request->input('dob', null);
            $income = floatval($request->input('monthly_income', 0));
            $reqAmt = floatval($request->input('requested_amount', 25000));
            if ($reqAmt <= 0) $reqAmt = 25000;

            if ($customer) {
                $customer->update([
                    'full_name' => $name,
                    'pan_number' => $pan,
                    'email' => $email,
                    'dob' => $dob,
                    'monthly_income' => $income,
                ]);
            }
            if ($application) {
                $application->update([
                    'applicant_name' => $name,
                    'pan_number' => $pan,
                    'requested_amount' => $reqAmt,
                ]);
            }
            $queryParams['amount'] = $reqAmt;
            return redirect()->route('finance.cash_loan.s04_eligibility', $queryParams);
        }

        if ($step === 's05') {
            $amount = floatval($request->input('amount', 25000));
            if ($amount <= 0) $amount = 25000;
            $tenure = intval($request->input('tenure', 12));
            if ($tenure <= 0) $tenure = 12;

            if ($application) {
                // Dynamically recompute processing fee based on amount
                $baseFee = max(999, min(2500, round($amount * 0.02, 2)));
                $tax = round($baseFee * 0.18, 2);
                $application->update([
                    'requested_amount' => $amount,
                    'tenure_months' => $tenure,
                    'processing_fee_base' => $baseFee,
                    'processing_fee_tax' => $tax,
                    'processing_fee_total' => $baseFee + $tax,
                ]);
            }
            $queryParams['amount'] = $amount;
            $queryParams['tenure'] = $tenure;
            return redirect()->route('finance.cash_loan.s06_emi', $queryParams);
        }

        if ($step === 's09') {
            if ($application) {
                $application->update([
                    'fee_payment_status' => 'paid',
                    'application_status' => 'FEE_PAID',
                ]);
            }
            $queryParams['amount'] = $application ? $application->requested_amount : 25000;
            return redirect()->route('finance.cash_loan.s10_app_generated', $queryParams);
        }

        return redirect()->route('finance.hub', $queryParams);
    }

    // ─────────────────────────────────────────────────────────────
    // FLOW A — CASH LOAN (26 Screens)
    // ─────────────────────────────────────────────────────────────

    public function cashLoanApply(Request $request)            { return view('finance.cash_loan.s01_apply', $this->resolveContext($request)); }
    public function cashLoanTypeConsent(Request $request)      { return view('finance.cash_loan.s02_type_consent', $this->resolveContext($request)); }
    public function cashLoanApplicantDetails(Request $request) { return view('finance.cash_loan.s03_applicant_details', $this->resolveContext($request)); }
    public function cashLoanEligibility(Request $request)      { return view('finance.cash_loan.s04_eligibility', $this->resolveContext($request)); }
    public function cashLoanAmountTenure(Request $request)     { return view('finance.cash_loan.s05_tenure', $this->resolveContext($request)); }
    public function cashLoanEmi(Request $request)              { return view('finance.cash_loan.s06_emi', $this->resolveContext($request)); }
    public function cashLoanDocuments(Request $request)        { return view('finance.cash_loan.s07_documents', $this->resolveContext($request)); }
    public function cashLoanReadyProcessing(Request $request)  { return view('finance.cash_loan.s08_ready', $this->resolveContext($request)); }
    public function cashLoanFeePayment(Request $request)       { return view('finance.cash_loan.s09_fee_payment', $this->resolveContext($request)); }
    public function cashLoanApplicationGen(Request $request)   { return view('finance.cash_loan.s10_app_generated', $this->resolveContext($request)); }
    public function cashLoanPartnerDashboard(Request $request) { return view('finance.cash_loan.s11_partner_dashboard', $this->resolveContext($request)); }
    public function cashLoanPartnerVerify(Request $request)    { return view('finance.cash_loan.s12_partner_verify', $this->resolveContext($request)); }
    public function cashLoanPartnerRedirect(Request $request)  { return view('finance.cash_loan.s13_partner_redirect', $this->resolveContext($request)); }
    public function cashLoanLenderWebview(Request $request)    { return view('finance.cash_loan.s14_lender_webview', $this->resolveContext($request)); }
    public function cashLoanProofUpload(Request $request)      { return view('finance.cash_loan.s15_proof_upload', $this->resolveContext($request)); }
    public function cashLoanValidation(Request $request)       { return view('finance.cash_loan.s16_validation', $this->resolveContext($request)); }
    public function cashLoanSelfieAgent(Request $request)      { return view('finance.cash_loan.s17_selfie_agent', $this->resolveContext($request)); }
    public function cashLoanTracking(Request $request)         { return view('finance.cash_loan.s18_tracking', $this->resolveContext($request)); }
    public function cashLoanLenderReview(Request $request)     { return view('finance.cash_loan.s19_lender_review', $this->resolveContext($request)); }
    public function cashLoanProcessingWindow(Request $request) { return view('finance.cash_loan.s20_processing', $this->resolveContext($request)); }
    public function cashLoanApproved(Request $request)         { return view('finance.cash_loan.s21_approval', $this->resolveContext($request)); }
    public function cashLoanBankDetails(Request $request)      { return view('finance.cash_loan.s22_bank_details', $this->resolveContext($request)); }
    public function cashLoanDisbursement(Request $request)     { return view('finance.cash_loan.s23_disbursement', $this->resolveContext($request)); }
    public function cashLoanAdditionalDocs(Request $request)   { return view('finance.cash_loan.s24_additional_docs', $this->resolveContext($request)); }
    public function cashLoanDocsSubmitted(Request $request)    { return view('finance.cash_loan.s25_docs_submitted', $this->resolveContext($request)); }
    public function cashLoanFinalResult(Request $request)      { return view('finance.cash_loan.s26_final_result', $this->resolveContext($request)); }

    // ─────────────────────────────────────────────────────────────
    // FLOW A — BUSINESS LOAN (20 Screens)
    // ─────────────────────────────────────────────────────────────

    public function businessLoanApply(Request $request)            { return view('finance.business_loan.s01_apply', $this->resolveContext($request)); }
    public function businessLoanDetails(Request $request)          { return view('finance.business_loan.s02_business_details', $this->resolveContext($request)); }
    public function businessLoanRequirement(Request $request)      { return view('finance.business_loan.s03_loan_requirement', $this->resolveContext($request)); }
    public function businessLoanEligibility(Request $request)      { return view('finance.business_loan.s04_eligibility', $this->resolveContext($request)); }
    public function businessLoanAmountTenure(Request $request)     { return view('finance.business_loan.s05_tenure', $this->resolveContext($request)); }
    public function businessLoanEmi(Request $request)              { return view('finance.business_loan.s06_emi', $this->resolveContext($request)); }
    public function businessLoanDocuments(Request $request)        { return view('finance.business_loan.s07_documents', $this->resolveContext($request)); }
    public function businessLoanVerification(Request $request)     { return view('finance.business_loan.s08_verification', $this->resolveContext($request)); }
    public function businessLoanReadyProcessing(Request $request)  { return view('finance.business_loan.s09_ready', $this->resolveContext($request)); }
    public function businessLoanFeePayment(Request $request)       { return view('finance.business_loan.s10_fee_payment', $this->resolveContext($request)); }
    public function businessLoanApplicationGen(Request $request)   { return view('finance.business_loan.s11_app_generated', $this->resolveContext($request)); }
    public function businessLoanPartnerDashboard(Request $request) { return view('finance.business_loan.s12_partner_dashboard', $this->resolveContext($request)); }
    public function businessLoanPartnerSelect(Request $request)    { return view('finance.business_loan.s13_partner_select', $this->resolveContext($request)); }
    public function businessLoanLenderWebview(Request $request)    { return view('finance.business_loan.s14_lender_webview', $this->resolveContext($request)); }
    public function businessLoanLenderProcessing(Request $request) { return view('finance.business_loan.s15_lender_processing', $this->resolveContext($request)); }
    public function businessLoanAdditionalDocs(Request $request)   { return view('finance.business_loan.s16_additional_docs', $this->resolveContext($request)); }
    public function businessLoanApproved(Request $request)         { return view('finance.business_loan.s17_approval', $this->resolveContext($request)); }
    public function businessLoanBankDetails(Request $request)      { return view('finance.business_loan.s18_bank_details', $this->resolveContext($request)); }
    public function businessLoanDisbursement(Request $request)     { return view('finance.business_loan.s19_disbursement', $this->resolveContext($request)); }
    public function businessLoanFinalStatus(Request $request)      { return view('finance.business_loan.s20_final_status', $this->resolveContext($request)); }

    // ─────────────────────────────────────────────────────────────
    // FLOW B — ZERO-CIBIL DAILY (7 Screens)
    // ─────────────────────────────────────────────────────────────

    public function zeroCibilIntro(Request $request)        { return view('finance.zero_cibil.s01_intro', $this->resolveContext($request)); }
    public function zeroCibilKyc(Request $request)          { return view('finance.zero_cibil.s02_kyc', $this->resolveContext($request)); }
    public function zeroCibilAmountSelect(Request $request) { return view('finance.zero_cibil.s03_amount_select', $this->resolveContext($request)); }
    public function zeroCibilFeePayment(Request $request)   { return view('finance.zero_cibil.s04_fee_payment', $this->resolveContext($request)); }
    public function zeroCibilPending(Request $request)      { return view('finance.zero_cibil.s05_pending', $this->resolveContext($request)); }
    public function zeroCibilWalletActive(Request $request) {
        $phone = $request->query('phone');
        $wallet = null;
        if ($phone) {
            $customer = FinanceCustomer::where('phone', $phone)->first();
            if ($customer) {
                $wallet = FinanceWallet::where('customer_id', $customer->id)->where('status', 'active')->first();
            }
        }
        return view('finance.zero_cibil.s06_wallet_active', array_merge($this->resolveContext($request), ['wallet' => $wallet]));
    }
    public function zeroCibilQrPay(Request $request)        {
        $phone = $request->query('phone');
        $wallet = null;
        if ($phone) {
            $customer = FinanceCustomer::where('phone', $phone)->first();
            if ($customer) {
                $wallet = FinanceWallet::where('customer_id', $customer->id)->where('status', 'active')->first();
            }
        }
        return view('finance.zero_cibil.s07_qr_pay', array_merge($this->resolveContext($request), ['wallet' => $wallet]));
    }

    // ─────────────────────────────────────────────────────────────
    // FLOW B — VIRTUAL LOAN (5 Screens)
    // ─────────────────────────────────────────────────────────────

    public function virtualLoanApply(Request $request)      { return view('finance.virtual_loan.s01_apply', $this->resolveContext($request)); }
    public function virtualLoanKyc(Request $request)        { return view('finance.virtual_loan.s02_kyc', $this->resolveContext($request)); }
    public function virtualLoanFeePayment(Request $request) { return view('finance.virtual_loan.s03_fee_payment', $this->resolveContext($request)); }
    public function virtualLoanPending(Request $request)    { return view('finance.virtual_loan.s04_pending', $this->resolveContext($request)); }
    public function virtualLoanDashboard(Request $request)  {
        $phone = $request->query('phone');
        $wallet = null;
        if ($phone) {
            $customer = FinanceCustomer::where('phone', $phone)->first();
            if ($customer) {
                $wallet = FinanceWallet::where('customer_id', $customer->id)->where('status', 'active')->first();
            }
        }
        return view('finance.virtual_loan.s05_dashboard', array_merge($this->resolveContext($request), ['wallet' => $wallet]));
    }

    // ─────────────────────────────────────────────────────────────
    // FLOW B — STUDENT CREDIT (9 Screens)
    // ─────────────────────────────────────────────────────────────

    public function studentCreditApply(Request $request)          { return view('finance.student_credit.s01_apply', $this->resolveContext($request)); }
    public function studentCreditKyc(Request $request)            { return view('finance.student_credit.s02_kyc', $this->resolveContext($request)); }
    public function studentCreditFeePayment(Request $request)      { return view('finance.student_credit.s03_fee_payment', $this->resolveContext($request)); }
    public function studentCreditPending(Request $request)         { return view('finance.student_credit.s04_pending', $this->resolveContext($request)); }
    public function studentCreditAdditionalDocs(Request $request) { return view('finance.student_credit.s05_additional_docs', $this->resolveContext($request)); }
    public function studentCreditMgmtApproval(Request $request)   { return view('finance.student_credit.s06_mgmt_approval', $this->resolveContext($request)); }
    public function studentCreditApproved(Request $request)       { return view('finance.student_credit.s07_approved', $this->resolveContext($request)); }
    public function studentCreditDashboard(Request $request)      {
        $phone = $request->query('phone');
        $wallet = null;
        if ($phone) {
            $customer = FinanceCustomer::where('phone', $phone)->first();
            if ($customer) {
                $wallet = FinanceWallet::where('customer_id', $customer->id)->where('status', 'active')->first();
            }
        }
        return view('finance.student_credit.s08_dashboard', array_merge($this->resolveContext($request), ['wallet' => $wallet]));
    }
    public function studentCreditQrPay(Request $request)          {
        $phone = $request->query('phone');
        $wallet = null;
        if ($phone) {
            $customer = FinanceCustomer::where('phone', $phone)->first();
            if ($customer) {
                $wallet = FinanceWallet::where('customer_id', $customer->id)->where('status', 'active')->first();
            }
        }
        return view('finance.student_credit.s09_qr_pay', array_merge($this->resolveContext($request), ['wallet' => $wallet]));
    }
}

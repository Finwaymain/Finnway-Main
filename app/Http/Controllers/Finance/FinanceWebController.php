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
        $phone = $request->query('phone');
        $customer = null;
        if ($phone) {
            $customer = FinanceCustomer::where('phone', $phone)->first();
        }
        return ['customer' => $customer, 'phone' => $phone];
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
        $products = FinanceLoanProduct::where('is_active', true)->get();
        $ctx = $this->resolveContext($request);
        return view('finance.hub', array_merge($ctx, ['products' => $products]));
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

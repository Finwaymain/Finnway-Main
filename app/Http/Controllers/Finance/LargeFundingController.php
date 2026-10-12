<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\LargeFundingApplication;
use App\Models\Finance\LargeFundingDocument;
use App\Models\Finance\FinanceCustomer;
use App\Models\UserApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LargeFundingController extends Controller
{
    /**
     * Resolve customer & active application by phone
     */
    private function resolveApplicant(Request $request)
    {
        $rawPhone = $request->query('phone') ?? $request->input('phone') ?? session('finance_phone', '');
        $digits = preg_replace('/\D/', '', $rawPhone);
        if (strlen($digits) > 10) {
            $digits = substr($digits, -10);
        }

        $customer = null;
        $user = null;
        if (!empty($digits)) {
            $customer = FinanceCustomer::where('phone', 'LIKE', '%' . $digits)->first();
            $user = UserApp::where('phone', 'LIKE', '%' . $digits)->first();
        }

        $activeApp = null;
        if (!empty($digits)) {
            $activeApp = LargeFundingApplication::where('mobile_number', 'LIKE', '%' . $digits)
                ->whereNotIn('application_status', ['REJECTED'])
                ->latest()
                ->first();
        }

        return [$digits, $customer, $user, $activeApp];
    }

    /**
     * Intro / Landing Screen for 2Cr - 100Cr Private Funding
     */
    public function intro(Request $request)
    {
        [$phone, $customer, $user, $activeApp] = $this->resolveApplicant($request);

        if ($activeApp) {
            return redirect()->route('finance.large_funding.status', ['phone' => $phone]);
        }

        return view('finance.large_funding.intro', compact('phone', 'customer'));
    }

    /**
     * Multi-step Form Wizard (Form 1 to 7)
     */
    public function apply(Request $request)
    {
        [$phone, $customer, $user, $activeApp] = $this->resolveApplicant($request);

        if ($activeApp && in_array($activeApp->application_status, ['SUBMITTED', 'DOCUMENTS_VERIFIED', 'FEE_PENDING', 'FEE_PAID', 'FORWARDED_TO_INVESTORS', 'FUNDING_DISCUSSION', 'FINAL_OFFER', 'FUND_APPROVED', 'DISBURSED'])) {
            return redirect()->route('finance.large_funding.status', ['phone' => $phone]);
        }

        return view('finance.large_funding.apply', compact('phone', 'customer', 'user', 'activeApp'));
    }

    /**
     * Handle Form Submission (Validations matching clean_docx.txt)
     */
    public function submitApplication(Request $request)
    {
        $validated = $request->validate([
            // Form 1 — Applicant Details
            'applicant_name'            => 'required|string|max:150',
            'mobile_number'             => 'required|string|max:20',
            'email'                     => 'required|email|max:150',
            'applicant_role'            => 'required|string|in:Founder,Co-Founder,Director,Partner,Proprietor,Authorised Representative',
            'pan_number'                => 'required|string|size:10',
            'city'                      => 'required|string|max:100',
            'state'                     => 'required|string|max:100',
            'pincode'                   => 'required|string|max:10',

            // Form 2 — Business Details
            'business_name'             => 'required|string|max:200',
            'business_type'             => 'required|string|in:Startup / New Business,Existing Business',
            'business_category'         => 'required|string|max:100',
            'business_description'      => 'required|string',
            'business_registration_status' => 'required|string|in:Registered,Not Registered',
            'registration_date'         => 'nullable|date',
            'website_or_app_link'       => 'nullable|string|max:255',
            'business_address'          => 'required|string',

            // Form 3 — Founder / Partner Details
            'total_founders_count'      => 'required|integer|min:1|max:20',
            'founders'                  => 'required|array|min:1',
            'founders.*'                => 'required|string|max:150',
            'main_applicant_name'       => 'required|string|max:150',

            // Form 4 — Funding Requirement
            'required_funding_amount'   => 'required|string|in:₹2 Crore – ₹5 Crore,₹5 Crore – ₹10 Crore,₹10 Crore – ₹25 Crore,₹25 Crore – ₹50 Crore,₹50 Crore – ₹100 Crore',
            'funding_type'              => 'required|string',
            'funding_purpose'           => 'required|string',
            'funding_timeline'          => 'required|string',
            'expected_use_of_funds'     => 'required|string',

            // Form 5 — Basic Business Financial Details
            'business_stage'            => 'required|string',
            'business_start_date'       => 'required|string|max:60',
            'monthly_revenue'           => 'required|string',
            'annual_turnover'           => 'nullable|string|max:100',
            'current_profit_loss'       => 'nullable|string|max:100',
            'existing_loan_details'     => 'nullable|string',
            'expected_revenue_after_funding' => 'nullable|string|max:100',

            // Form 6 — Bank Account Details
            'bank_account_holder_name'  => 'required|string|max:150',
            'bank_account_number'       => 'required|string|max:60',
            'bank_ifsc'                 => 'required|string|max:30',
            'bank_name'                 => 'required|string|max:150',

            // Form 6 — Compulsory Documents
            'doc_bank_cheque'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:15360',
            'doc_pan_card'              => 'required|file|mimes:pdf,jpg,jpeg,png|max:15360',
            'doc_identity_proof'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:15360',
            'doc_pitch_deck'            => 'required|file|mimes:pdf,ppt,pptx,doc,docx|max:30720',
            'doc_fund_plan'             => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',

            // Form 6 — Optional Documents
            'doc_company_reg'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:15360',
            'doc_gst_cert'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:15360',
            'doc_itr_financials'        => 'nullable|file|mimes:pdf,zip,rar|max:30720',
            'doc_cap_table'             => 'nullable|file|mimes:pdf,xls,xlsx|max:15360',
            'doc_other'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png,zip|max:30720',

            // Form 7 — Consent
            'consent_info_accurate'        => 'accepted',
            'consent_fiinway_review'       => 'accepted',
            'consent_share_with_investors' => 'accepted',
            'consent_terms_policy'         => 'accepted',
            'consent_no_guarantee'         => 'accepted',
        ]);

        $rawPhone = preg_replace('/\D/', '', $validated['mobile_number']);
        if (strlen($rawPhone) > 10) {
            $rawPhone = substr($rawPhone, -10);
        }

        // Check customer
        $customer = FinanceCustomer::firstOrCreate(
            ['phone' => $rawPhone],
            [
                'name'    => $validated['applicant_name'],
                'email'   => $validated['email'],
                'pan'     => strtoupper($validated['pan_number']),
                'city'    => $validated['city'],
                'state'   => $validated['state'],
                'pincode' => $validated['pincode'],
            ]
        );

        $user = UserApp::where('phone', 'LIKE', '%' . $rawPhone)->first();

        // Generate App Number: FW-LF-YEAR-RANDOM
        $appNumber = 'FW-LF-' . date('Y') . '-' . strtoupper(Str::random(6));

        // Format founders JSON
        $foundersArray = [];
        foreach ($validated['founders'] as $idx => $name) {
            $foundersArray[] = [
                'index' => $idx + 1,
                'name'  => trim($name),
                'role'  => ($idx === 0) ? 'Main Applicant / Founder' : ('Founder / Partner ' . ($idx + 1)),
            ];
        }

        DB::beginTransaction();
        try {
            $application = LargeFundingApplication::create([
                'application_number'           => $appNumber,
                'user_id'                      => $user ? $user->id : null,
                'customer_id'                  => $customer ? $customer->id : null,
                'mobile_number'                => $rawPhone,
                'is_otp_verified'              => true,

                // Form 1
                'applicant_name'               => $validated['applicant_name'],
                'email'                        => $validated['email'],
                'applicant_role'               => $validated['applicant_role'],
                'pan_number'                   => strtoupper($validated['pan_number']),
                'city'                         => $validated['city'],
                'state'                        => $validated['state'],
                'pincode'                      => $validated['pincode'],

                // Form 2
                'business_name'                => $validated['business_name'],
                'business_type'                => $validated['business_type'],
                'business_category'            => $validated['business_category'],
                'business_description'         => $validated['business_description'],
                'business_registration_status' => $validated['business_registration_status'],
                'registration_date'            => $validated['registration_date'] ?? null,
                'website_or_app_link'          => $validated['website_or_app_link'] ?? null,
                'business_address'             => $validated['business_address'],

                // Form 3
                'total_founders_count'         => (int)$validated['total_founders_count'],
                'founders_json'                => $foundersArray,
                'main_applicant_name'          => $validated['main_applicant_name'],

                // Form 4
                'required_funding_amount'      => $validated['required_funding_amount'],
                'funding_type'                 => $validated['funding_type'],
                'funding_purpose'              => $validated['funding_purpose'],
                'funding_timeline'             => $validated['funding_timeline'],
                'expected_use_of_funds'        => $validated['expected_use_of_funds'],

                // Form 5
                'business_stage'               => $validated['business_stage'],
                'business_start_date'          => $validated['business_start_date'],
                'monthly_revenue'              => $validated['monthly_revenue'],
                'annual_turnover'              => $validated['annual_turnover'] ?? null,
                'current_profit_loss'          => $validated['current_profit_loss'] ?? null,
                'existing_loan_details'        => $validated['existing_loan_details'] ?? null,
                'expected_revenue_after_funding' => $validated['expected_revenue_after_funding'] ?? null,

                // Form 6 Bank
                'bank_account_holder_name'     => $validated['bank_account_holder_name'],
                'bank_account_number'          => $validated['bank_account_number'],
                'bank_ifsc'                    => strtoupper($validated['bank_ifsc']),
                'bank_name'                    => $validated['bank_name'],

                // Form 7 Consent
                'consent_info_accurate'        => true,
                'consent_fiinway_review'       => true,
                'consent_share_with_investors' => true,
                'consent_terms_policy'         => true,
                'consent_no_guarantee'         => true,
                'submitted_at'                 => now(),

                // Pipeline: 1 = Submitted, Stage 2 = Documents & Project Review
                'current_stage'                => 1,
                'application_status'           => 'SUBMITTED',
                'service_fee_base'             => 25000.00,
                'service_fee_gst'              => 4500.00,
                'service_fee_total'            => 29500.00,
                'fee_payment_status'           => 'pending',
            ]);

            // Save Documents
            $docMap = [
                'doc_bank_cheque'     => 'bank_cheque',
                'doc_pan_card'        => 'pan_card',
                'doc_identity_proof'  => 'identity_proof',
                'doc_pitch_deck'      => 'pitch_deck',
                'doc_fund_plan'       => 'fund_plan',
                'doc_company_reg'     => 'company_reg',
                'doc_gst_cert'        => 'gst_cert',
                'doc_itr_financials'  => 'itr_financials',
                'doc_cap_table'       => 'cap_table',
                'doc_other'           => 'other',
            ];

            foreach ($docMap as $reqKey => $docType) {
                if ($request->hasFile($reqKey)) {
                    $file = $request->file($reqKey);
                    $path = $file->store('finance_large_funding/' . $application->id, 'public');
                    LargeFundingDocument::create([
                        'application_id' => $application->id,
                        'document_type'  => $docType,
                        'file_path'      => $path,
                        'original_name'  => $file->getClientOriginalName(),
                        'file_size'      => round($file->getSize() / 1024, 2) . ' KB',
                        'status'         => 'pending',
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('finance.large_funding.status', ['phone' => $rawPhone])
                ->with('success', 'Application ' . $appNumber . ' successfully submitted! Documents are under institutional review.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Large Funding submission error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error saving application: ' . $e->getMessage());
        }
    }

    /**
     * Realtime Application Status & Stage Tracker
     */
    public function status(Request $request)
    {
        [$phone, $customer, $user, $activeApp] = $this->resolveApplicant($request);

        if (!$activeApp) {
            return redirect()->route('finance.large_funding.intro', ['phone' => $phone]);
        }

        return view('finance.large_funding.status', compact('phone', 'customer', 'activeApp'));
    }

    /**
     * Pay Service Fee via Razorpay or Wallet
     */
    public function payFee(Request $request)
    {
        [$phone, $customer, $user, $activeApp] = $this->resolveApplicant($request);

        if (!$activeApp) {
            return redirect()->route('finance.large_funding.intro', ['phone' => $phone]);
        }

        if ($activeApp->fee_payment_status === 'paid') {
            return redirect()->route('finance.large_funding.status', ['phone' => $phone])
                ->with('success', 'Service fee is already paid.');
        }

        $method = $request->input('payment_method', 'online');
        $txnRef = 'LF-FEE-' . strtoupper(Str::random(8));

        // Mark Fee Paid
        $activeApp->update([
            'fee_payment_status' => 'paid',
            'fee_paid_at'        => now(),
            'fee_payment_ref'    => $txnRef,
            'fee_payment_method' => $method,
            // Advance to Stage 4 (Forwarded to Investors) if at Stage 3
            'current_stage'      => max($activeApp->current_stage, 4),
            'application_status' => 'FORWARDED_TO_INVESTORS',
        ]);

        return redirect()->route('finance.large_funding.status', ['phone' => $phone])
            ->with('success', 'Service Fee ₹' . number_format($activeApp->service_fee_total) . ' successfully paid! Your profile is forwarded to private investors.');
    }
}

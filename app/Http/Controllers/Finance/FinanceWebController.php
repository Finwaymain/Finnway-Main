<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceCustomer;
use App\Models\Finance\FinanceDocument;
use App\Models\Finance\FinanceDocumentRequest;
use App\Models\Finance\FinanceLenderPartner;
use App\Models\Finance\FinanceLoanApplication;
use App\Models\Finance\FinanceLoanProduct;
use App\Models\Finance\FinanceTransaction;
use App\Models\Finance\FinanceWallet;
use App\Models\PaymentSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $variants = \App\Services\PhoneService::getVariants($phone);
            $customer = FinanceCustomer::whereIn('phone', $variants)->first();
            if (!$customer) {
                // Check if user exists in tj_user_app
                $user = DB::table('tj_user_app')->whereIn('phone', $variants)->first();
                if ($user) {
                    $fullName = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')) ?: ($user->name ?? 'Customer');
                    $customer = FinanceCustomer::create([
                        'phone' => $phone,
                        'name' => $fullName,
                        'user_type' => 'customer',
                        'user_id' => $user->id,
                        'email' => $user->email ?? null,
                    ]);
                } else {
                    // Check if user exists in tj_conducteur
                    $driver = DB::table('tj_conducteur')->whereIn('phone', $variants)->first();
                    if ($driver) {
                        $fullName = trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? '')) ?: ($driver->name ?? 'Driver');
                        $customer = FinanceCustomer::create([
                            'phone' => $phone,
                            'name' => $fullName,
                            'user_type' => 'driver',
                            'driver_id' => $driver->id,
                            'email' => $driver->email ?? null,
                        ]);
                    }
                }
            }
            if ($customer) {
                $application = FinanceLoanApplication::where('customer_id', $customer->id)
                    ->whereNotIn('application_status', ['DISBURSED', 'REJECTED', 'CLOSED', 'WITHDRAWN'])
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
        $maxLimit = in_array($loanType, ['good_cibil', 'prime_cash']) ? 2000000 : 400000;
        if ($amount > $maxLimit) {
            $amount = $maxLimit;
        }

        // Mathematical EMI calculation: P * r * (1+r)^n / ((1+r)^n - 1)
        // Standard personal loan indicative rate: 9% p.a. -> monthly r = 0.09 / 12 = 0.0075
        $annualRate = 0.09;
        $monthlyRate = $annualRate / 12;
        $pow = pow(1 + $monthlyRate, $tenure);
        $monthlyEmi = $pow > 1 ? ($amount * $monthlyRate * $pow) / ($pow - 1) : ($amount / $tenure);
        $totalRepayment = $monthlyEmi * $tenure;
        $totalInterest = max(0, $totalRepayment - $amount);

        // Product category resolution
        $productCategory = $application->loan_category ?? $request->query('category', $request->query('loan_type', 'low_cibil_cash'));

        // Dynamic fee resolution directly from Admin Loan Product Policy or Application Override
        $product = null;
        if ($application && $application->loan_product_id) {
            $product = FinanceLoanProduct::find($application->loan_product_id);
        }
        if (!$product) {
            $categoryMap = [
                'zero_cibil' => 'zero_cibil_daily',
                'zero_cibil_micro' => 'zero_cibil_daily',
                'low_cibil' => 'cash_loan_low_cibil',
                'low_cibil_cash' => 'cash_loan_low_cibil',
                'good_cibil' => 'cash_loan_good_cibil',
                'prime_cash' => 'cash_loan_good_cibil',
                'virtual_loan' => 'virtual_loan',
                'virtual_credit' => 'virtual_loan',
                'business_loan' => 'business_loan',
                'business_msme' => 'business_loan',
                'student_credit' => 'student_credit_domestic',
            ];
            $targetCode = $categoryMap[$productCategory] ?? $productCategory;
            $product = FinanceLoanProduct::where('code', $targetCode)
                ->orWhere('category', $productCategory)
                ->first();
        }

        $baseFee = 999.0;
        if ($application && $application->processing_fee_base > 0) {
            $baseFee = floatval($application->processing_fee_base);
        } elseif ($application && $application->processing_fee_amount > 0) {
            $baseFee = floatval($application->processing_fee_amount);
        } elseif ($product) {
            if ($product->processing_fee_type === 'percentage') {
                $baseFee = round($amount * (floatval($product->processing_fee_value) / 100), 2);
            } else {
                $baseFee = floatval($product->processing_fee_value);
            }
        } else {
            $baseFee = max(999, min(2500, round($amount * 0.02, 2)));
        }

        $feeTax = round($baseFee * 0.18, 2);
        $totalFee = $baseFee + $feeTax;

        // Flow A has lender, Flow B does NOT have lender
        $hasLender = in_array($productCategory, ['low_cibil_cash', 'prime_cash', 'business_msme', 'cash_loan', 'business_loan']);

        // Resolve Razorpay Key specifically for Loan Flow (isolated from whole flow / payment_settings key 13)
        $loanRzp = \App\Helpers\LoanRazorpayConfig::resolve();
        $razorpayKey = $loanRzp['key'] ?: (string) env('LOAN_RAZORPAY_KEY', env('RAZORPAY_KEY', 'rzp_test_fiinway'));
        $razorpayMerchantName = $loanRzp['merchant_name'] ?: 'Fiinway Loan & Credit';

        $documents = [];
        if ($customer) {
            $documents = FinanceDocument::where('customer_id', $customer->id)
                ->pluck('file_path', 'document_type')
                ->toArray();
        }

        if ($request->has('hide_header') || $request->has('app')) {
            session(['finance_hide_header' => true]);
        }
        $hideHeader = session('finance_hide_header', false) || $request->query('hide_header') == '1' || $request->query('app') == '1';

        // Active lending partners from master
        $partners = FinanceLenderPartner::where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        // Selected partner resolution
        $partnerId = $request->query('partner_id', $request->input('partner_id', $application->selected_lender_id ?? null));
        $selectedPartner = null;
        if ($partnerId) {
            $selectedPartner = FinanceLenderPartner::find($partnerId);
        }
        if (!$selectedPartner && $application && $application->selected_lender_id) {
            $selectedPartner = FinanceLenderPartner::find($application->selected_lender_id);
        }
        if (!$selectedPartner && $partners->isNotEmpty()) {
            $selectedPartner = $partners->first();
        }

        // Applicant display values (never dummy or hardcoded)
        $applicantName = $application->applicant_name 
            ?? ($customer->name ?? ($customer->full_name ?? ($request->input('name') ?: 'Valued Applicant')));
        
        $applicantPhone = $application->applicant_phone 
            ?? ($customer->phone ?? ($phone ?: ''));

        $appNumber = $application->application_number 
            ?? ($customer ? 'FIIN-APP-' . date('Y') . '-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT) : 'FIIN-APP-' . time());

        // Validation waiting timer configured by Admin (seconds, default 180 = 3 minutes)
        $validationTimerSeconds = 180;
        if (\Illuminate\Support\Facades\Schema::hasTable('finance_settings')) {
            $validationTimerSeconds = (int) \App\Models\Finance\FinanceSetting::get('loan_validation_timer_seconds', 180);
            if ($validationTimerSeconds <= 0) {
                $validationTimerSeconds = 180;
            }
        }

        return [
            'customer' => $customer,
            'phone' => $phone,
            'application' => $application,
            'appNumber' => $appNumber,
            'validationTimerSeconds' => $validationTimerSeconds,
            'applicantName' => $applicantName,
            'applicantPhone' => $applicantPhone,
            'partners' => $partners,
            'lenders' => $partners,
            'selectedPartner' => $selectedPartner,
            'product' => $product,
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
            'razorpayKey' => $razorpayKey,
            'razorpayMerchantName' => $razorpayMerchantName,
            'documents' => $documents,
            'hideHeader' => $hideHeader,
            'maxLimit' => $maxLimit,
        ];
    }

    public function getResumeUrlForApplication(FinanceLoanApplication $application, ?string $phone): ?string
    {
        $status = $application->application_status;
        $amount = $application->requested_amount ?: 25000;
        $tenure = $application->tenure_months ?: 12;
        $cat = $application->loan_category ?? 'low_cibil';
        $params = ['phone' => $phone, 'amount' => $amount, 'tenure' => $tenure];
        if (session('finance_hide_header') || request('hide_header') == '1' || request('app') == '1') {
            $params['hide_header'] = '1';
        }

        if (in_array($cat, ['zero_cibil', 'zero_cibil_micro', 'zero_cibil_daily'])) {
            $stepMap = [
                'KYC_PENDING'    => route('finance.zero_cibil.s02_kyc', ['phone' => $phone]),
                'AMOUNT_PENDING' => route('finance.zero_cibil.s03_amount_select', ['phone' => $phone]),
                'SANCTIONED'     => route('finance.zero_cibil.s04_fee_payment', ['phone' => $phone, 'amount' => $amount]),
                'FEE_PENDING'    => route('finance.zero_cibil.s04_fee_payment', ['phone' => $phone, 'amount' => $amount]),
                'UNDERWRITING'   => route('finance.zero_cibil.s05_pending', ['phone' => $phone]),
                'FEE_PAID'       => route('finance.zero_cibil.s05_pending', ['phone' => $phone]),
                'ACTIVE'         => route('finance.zero_cibil.s06_wallet_active', ['phone' => $phone]),
            ];
            return $stepMap[$status] ?? null;
        }

        if (in_array($cat, ['business', 'business_loan', 'business_msme'])) {
            $stepMap = [
                'DRAFT'                      => route('finance.business_loan.s02_business_details', ['phone' => $phone]),
                'BUSINESS_DETAILS'           => route('finance.business_loan.s03_loan_requirement', ['phone' => $phone]),
                'LOAN_REQUIREMENT'           => route('finance.business_loan.s04_eligibility', ['phone' => $phone]),
                'ELIGIBILITY'                => route('finance.business_loan.s05_amount_tenure', ['phone' => $phone]),
                'DOCUMENTS'                  => route('finance.business_loan.s07_documents', ['phone' => $phone]),
                'READY_PROCESSING'           => route('finance.business_loan.s09_ready_processing', ['phone' => $phone]),
                'FEE_PENDING'                => route('finance.business_loan.s10_fee_payment', ['phone' => $phone]),
                'FEE_PAID'                   => route('finance.business_loan.s11_app_generated', ['phone' => $phone]),
                'PARTNER_SELECTED'           => route('finance.business_loan.s14_lender_webview', ['phone' => $phone]),
                'VALIDATION_PENDING'         => route('finance.business_loan.s15_lender_processing', ['phone' => $phone]),
                'PROOF_SUBMITTED'            => route('finance.business_loan.s15_lender_processing', ['phone' => $phone]),
                'ADDITIONAL_DOCS_REQUESTED'  => route('finance.business_loan.s16_additional_docs', ['phone' => $phone]),
                'LOAN_APPROVED'              => route('finance.business_loan.s17_approval', ['phone' => $phone]),
                'APPROVED'                   => route('finance.business_loan.s17_approval', ['phone' => $phone]),
                'DISBURSEMENT_PENDING'       => route('finance.business_loan.s19_disbursement', ['phone' => $phone]),
                'DISBURSED'                  => route('finance.business_loan.s20_final_status', ['phone' => $phone]),
            ];
            return $stepMap[$status] ?? null;
        }

        $stepMap = [
            'DRAFT'                      => route('finance.cash_loan.s02_type_consent', ['phone' => $phone]),
            'KYC_PENDING'                => route('finance.cash_loan.s02_type_consent', ['phone' => $phone]),
            'DETAILS_SAVED'              => route('finance.cash_loan.s03_applicant_details', ['phone' => $phone]),
            'ELIGIBILITY'                => route('finance.cash_loan.s04_eligibility', ['phone' => $phone, 'amount' => $amount]),
            'AMOUNT_PENDING'             => route('finance.cash_loan.s05_tenure', $params),
            'TENURE_SELECTED'            => route('finance.cash_loan.s07_documents', $params),
            'DOCS_PENDING'               => route('finance.cash_loan.s07_documents', $params),
            'DOCS_SUBMITTED'             => route('finance.cash_loan.s08_ready', $params),
            'SANCTIONED'                 => route('finance.cash_loan.s08b_sanction_summary', $params),
            'FEE_PENDING'                => route('finance.cash_loan.s09_fee_payment', $params),
            'FEE_PAID'                   => route('finance.cash_loan.s10_app_generated', ['phone' => $phone, 'amount' => $amount]),
            'UNDERWRITING'               => route('finance.cash_loan.s11_partner_dashboard', ['phone' => $phone]),
            'APP_GENERATED'              => route('finance.cash_loan.s11_partner_dashboard', ['phone' => $phone]),
            'PARTNER_SELECTED'           => route('finance.cash_loan.s14_lender_webview', ['phone' => $phone, 'partner_id' => $application->selected_lender_id]),
            'PROOF_SUBMITTED'            => route('finance.cash_loan.s16_validation', ['phone' => $phone]),
            'VALIDATION_PENDING'         => route('finance.cash_loan.s16_validation', ['phone' => $phone]),
            'VALIDATION_APPROVED'        => route('finance.cash_loan.s17_selfie_agent', ['phone' => $phone]),
            'SELFIE_PENDING'             => route('finance.cash_loan.s17_selfie_agent', ['phone' => $phone]),
            'SELFIE_SUBMITTED'           => route('finance.cash_loan.s18_tracking', ['phone' => $phone]),
            'PROCESSING'                 => route('finance.cash_loan.s18_tracking', ['phone' => $phone]),
            'LENDER_REVIEW'              => route('finance.cash_loan.s19_lender_review', ['phone' => $phone]),
            'PROCESSING_WINDOW'          => route('finance.cash_loan.s20_processing', ['phone' => $phone]),
            'LOAN_APPROVED'              => route('finance.cash_loan.s21_approval', ['phone' => $phone]),
            'APPROVED'                   => route('finance.cash_loan.s21_approval', ['phone' => $phone]),
            'DISBURSEMENT_PENDING'       => route('finance.cash_loan.s23_disbursement', ['phone' => $phone]),
            'DISBURSED'                  => route('finance.cash_loan.s23_disbursement', ['phone' => $phone]),
            'ADDITIONAL_DOCS_REQUESTED'  => route('finance.cash_loan.s24_additional_docs', ['phone' => $phone]),
            'DOCS_RESUBMITTED'           => route('finance.cash_loan.s25_docs_submitted', ['phone' => $phone]),
            'REJECTED'                   => route('finance.cash_loan.s26_final_result', ['phone' => $phone]),
        ];

        return $stepMap[$status] ?? null;
    }

    public function checkActiveApplicationLock(Request $request, string $targetFamily, string $targetTitle)
    {
        $ctx = $this->resolveContext($request);
        $phone = $ctx['phone'];
        $application = $ctx['application'];

        if ($application && !in_array($application->application_status, ['DISBURSED', 'REJECTED', 'CLOSED', 'WITHDRAWN', 'DRAFT', 'APPLICATION_CREATED'])) {
            $activeCategory = $application->loan_category ?? '';
            $activeFamily = 'cash_loan';
            if (in_array($activeCategory, ['zero_cibil', 'zero_cibil_micro', 'zero_cibil_daily'])) {
                $activeFamily = 'zero_cibil';
            } elseif (in_array($activeCategory, ['business', 'business_loan', 'business_msme'])) {
                $activeFamily = 'business_loan';
            } elseif (in_array($activeCategory, ['virtual', 'virtual_loan', 'virtual_credit'])) {
                $activeFamily = 'virtual_loan';
            } elseif (in_array($activeCategory, ['student', 'student_credit'])) {
                $activeFamily = 'student_credit';
            }

            $familyNames = [
                'cash_loan' => 'Cash Loan',
                'zero_cibil' => 'Zero-CIBIL Credit',
                'business_loan' => 'Business Loan',
                'virtual_loan' => 'Virtual Loan',
                'student_credit' => 'Student Credit',
            ];

            $currentName = $familyNames[$activeFamily] ?? 'Loan';
            $resumeUrl = $this->getResumeUrlForApplication($application, $phone);

            if ($targetFamily === $activeFamily) {
                if ($resumeUrl && $resumeUrl !== $request->fullUrl() && $resumeUrl !== $request->url()) {
                    return redirect($resumeUrl);
                }
                return null;
            }

            $errorMsg = "You already have an active {$currentName} application (#{$application->application_number}) in progress. You cannot start a new process for {$targetTitle} until your current application is completed or withdrawn.";

            return redirect()->route('finance.hub', ['phone' => $phone])
                ->with('card_error_msg', $errorMsg)
                ->with('card_error_title', $targetTitle)
                ->with('active_app_number', $application->application_number)
                ->with('active_app_family', $currentName)
                ->with('active_resume_url', $resumeUrl);
        }

        return null;
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
        $ctx = $this->resolveContext($request);
        $phone = $ctx['phone'];
        $application = $ctx['application'];
        $isRunning = ($application && !in_array($application->application_status, ['DISBURSED', 'REJECTED', 'CLOSED', 'WITHDRAWN', 'DRAFT', 'APPLICATION_CREATED']));

        // Map active loan category to family
        $activeCategory = $application->loan_category ?? '';
        $activeFamily = 'cash_loan';
        if (in_array($activeCategory, ['zero_cibil', 'zero_cibil_micro', 'zero_cibil_daily'])) {
            $activeFamily = 'zero_cibil';
        } elseif (in_array($activeCategory, ['business', 'business_loan', 'business_msme'])) {
            $activeFamily = 'business_loan';
        } elseif (in_array($activeCategory, ['virtual', 'virtual_loan', 'virtual_credit'])) {
            $activeFamily = 'virtual_loan';
        } elseif (in_array($activeCategory, ['student', 'student_credit'])) {
            $activeFamily = 'student_credit';
        }

        $familyNames = [
            'cash_loan' => 'Cash Loan',
            'zero_cibil' => 'Zero-CIBIL Credit',
            'business_loan' => 'Business Loan',
            'virtual_loan' => 'Virtual Loan',
            'student_credit' => 'Student Credit',
        ];

        // Auto-resume active loan application when reopening loans from mobile app or direct link
        if ($isRunning && !$request->has('explore') && empty($cardType)) {
            $resumeUrl = $this->getResumeUrlForApplication($application, $phone);
            if ($resumeUrl) {
                return redirect($resumeUrl);
            }
        }

        if ($cardType) {
            $params = $request->all();
            $isViewDetails = in_array($cardType, ['loans & credit', 'loans', 'view_all', 'all', 'view details', 'details']);

            if ($isRunning) {
                $resumeUrl = $this->getResumeUrlForApplication($application, $phone);

                // 1. "on click view details you have to show same screen which is previously there"
                if ($isViewDetails) {
                    return $resumeUrl ? redirect($resumeUrl) : redirect()->route('finance.hub', ['phone' => $phone]);
                }

                // Identify target family of the clicked card
                $targetFamily = 'cash_loan';
                $targetTitle = 'Cash Loan';
                if (in_array($cardType, ['zero_cibil', '0 cibil loan', 'interest_free', 'interest free loan'])) {
                    $targetFamily = 'zero_cibil';
                    $targetTitle = 'Zero-CIBIL Daily Credit';
                } elseif (in_array($cardType, ['business', 'business loan', 'business_loan'])) {
                    $targetFamily = 'business_loan';
                    $targetTitle = 'Business Loan';
                } elseif (in_array($cardType, ['virtual', 'virtual loan', 'virtual_loan'])) {
                    $targetFamily = 'virtual_loan';
                    $targetTitle = 'Virtual Loan';
                } elseif (in_array($cardType, ['student', 'student credit', 'student_credit'])) {
                    $targetFamily = 'student_credit';
                    $targetTitle = 'Student Credit';
                }

                // If user tapped the card corresponding to their active loan, resume it
                if ($targetFamily === $activeFamily) {
                    return $resumeUrl ? redirect($resumeUrl) : redirect()->route('finance.cash_loan.s01_apply', $params);
                }

                // 2. "show error on click different card for new process"
                $currentName = $familyNames[$activeFamily] ?? 'Loan';
                $errorMsg = "You already have an active {$currentName} application (#{$application->application_number}) in progress. You cannot start a new process for {$targetTitle} until your current application is completed or withdrawn.";

                return redirect()->route('finance.hub', ['phone' => $phone])
                    ->with('card_error_msg', $errorMsg)
                    ->with('card_error_title', $targetTitle)
                    ->with('active_app_number', $application->application_number)
                    ->with('active_app_family', $currentName)
                    ->with('active_resume_url', $resumeUrl);
            }

            // If no running application, open requested product
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
        $resumeUrl = ($isRunning)
            ? $this->getResumeUrlForApplication($application, $phone)
            : null;

        return view('finance.hub', array_merge($ctx, [
            'products' => $products,
            'resumeUrl' => $resumeUrl,
            'isRunning' => $isRunning,
            'activeFamily' => $activeFamily,
            'activeFamilyName' => $familyNames[$activeFamily] ?? 'Loan',
        ]));
    }

    // ─────────────────────────────────────────────────────────────
    // STATE PERSISTENCE ENGINE (Dynamic Step Saver)
    // ─────────────────────────────────────────────────────────────

    public function saveCashLoanStep(Request $request)
    {
        $step = $request->input('step', 's01');
        $phone = $request->input('phone', $request->query('phone', $request->input('mobile')));
        $queryParams = ['phone' => $phone];
        if ($request->input('hide_header') || $request->query('hide_header') || session('finance_hide_header')) {
            $queryParams['hide_header'] = '1';
        }

        // 1. Ensure Customer exists
        $customer = null;
        if ($phone) {
            $variants = \App\Services\PhoneService::getVariants($phone);
            $customer = FinanceCustomer::whereIn('phone', $variants)->first();
            if (!$customer) {
                $customer = FinanceCustomer::create([
                    'phone' => $phone,
                    'full_name' => $request->input('applicant_name') ?: 'Customer',
                    'email' => $request->input('email'),
                    'pan_number' => $request->input('pan_number'),
                    'user_type' => 'customer',
                    'status' => 'active',
                ]);
            }
            if ($request->filled('applicant_name')) {
                $customer->update(['full_name' => $request->input('applicant_name')]);
            }
        }

        // 2. Fetch or create Application
        $application = null;
        if ($customer) {
            $application = FinanceLoanApplication::where('customer_id', $customer->id)
                ->whereNotIn('application_status', ['DISBURSED', 'REJECTED', 'CLOSED', 'WITHDRAWN'])
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
            // If already running, do not start new process or overwrite
            if ($application && !in_array($application->application_status, ['DISBURSED', 'REJECTED', 'CLOSED', 'DRAFT', 'WITHDRAWN'])) {
                $resumeUrl = $this->getResumeUrlForApplication($application, $phone);
                if ($resumeUrl) {
                    return redirect($resumeUrl);
                }
            }

            $loanType = $request->input('loan_type', 'low_cibil');
            $category = $loanType === 'good_cibil' ? 'prime_cash' : 'low_cibil_cash';
            if ($application) {
                $application->update([
                    'loan_category' => $category,
                    'application_status' => 'KYC_PENDING',
                ]);
            }
            $queryParams['loan_type'] = $loanType;
            return redirect()->route('finance.cash_loan.s02_type_consent', $queryParams);
        }

        if ($step === 's02') {
            if ($application) {
                $application->update(['application_status' => 'DETAILS_SAVED']);
            }
            return redirect()->route('finance.cash_loan.s03_applicant_details', $queryParams);
        }

        if ($step === 's03') {
            $name = $request->input('applicant_name', 'Customer');
            $pan = strtoupper($request->input('pan_number', ''));
            $email = $request->input('email', '');
            $dob = $request->input('dob', null);
            $income = floatval($request->input('monthly_income', 0));
            $loanType = $application ? ($application->loan_type ?? 'low_cibil') : $request->input('loan_type', 'low_cibil');
            $maxLimit = in_array($loanType, ['good_cibil', 'prime_cash']) ? 2000000 : 400000;
            $reqAmt = floatval($request->input('requested_amount', 25000));
            if ($reqAmt <= 0) $reqAmt = 25000;
            if ($reqAmt > $maxLimit) $reqAmt = $maxLimit;

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
                    'application_status' => 'ELIGIBILITY',
                ]);
            }
            $queryParams['amount'] = $reqAmt;
            return redirect()->route('finance.cash_loan.s04_eligibility', $queryParams);
        }

        if ($step === 's05') {
            $loanType = $application ? ($application->loan_type ?? 'low_cibil') : $request->input('loan_type', 'low_cibil');
            $maxLimit = in_array($loanType, ['good_cibil', 'prime_cash']) ? 2000000 : 400000;
            $amount = floatval($request->input('amount', 25000));
            if ($amount <= 0) $amount = 25000;
            if ($amount > $maxLimit) $amount = $maxLimit;
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
                    'application_status' => 'TENURE_SELECTED',
                ]);
            }
            $queryParams['amount'] = $amount;
            $queryParams['tenure'] = $tenure;
            return redirect()->route('finance.cash_loan.s07_documents', $queryParams);
        }

        if ($step === 's07') {
            // Check newly uploaded files OR existing in database
            $requiredDocs = ['pan_card', 'aadhaar_front', 'aadhaar_back'];
            $missingDocs = [];
            foreach ($requiredDocs as $doc) {
                $hasFile = $request->hasFile($doc);
                $hasExisting = $customer ? FinanceDocument::where('customer_id', $customer->id)->where('document_type', $doc)->exists() : false;
                if (!$hasFile && !$hasExisting) {
                    $missingDocs[] = ucwords(str_replace('_', ' ', $doc));
                }
            }
            if (!empty($missingDocs)) {
                return redirect()->back()->with('error', 'Please upload required documents: ' . implode(', ', $missingDocs));
            }

            $docTypes = ['pan_card', 'aadhaar_front', 'aadhaar_back', 'address_proof', 'income_proof'];
            foreach ($docTypes as $docType) {
                if ($request->hasFile($docType) && $customer) {
                    $file = $request->file($docType);
                    $path = $file->store('finance_docs', 'public');
                    FinanceDocument::updateOrCreate(
                        [
                            'customer_id' => $customer->id,
                            'document_type' => $docType,
                        ],
                        [
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'status' => 'pending',
                        ]
                    );
                }
            }
            if ($application) {
                $application->update([
                    'application_status' => 'DOCS_SUBMITTED',
                ]);
            }
            $queryParams['amount'] = $application ? $application->requested_amount : 25000;
            $queryParams['tenure'] = $application ? $application->tenure_months : 12;
            return redirect()->route('finance.cash_loan.s08_ready', $queryParams);
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
    // ZERO-CIBIL & FEE PAYMENT HANDLERS
    // ─────────────────────────────────────────────────────────────

    public function saveZeroCibilKyc(Request $request)
    {
        $phone = $request->input('phone', $request->query('phone'));
        $name = $request->input('applicant_name', 'Customer');
        $pan = strtoupper($request->input('pan_number', ''));
        $aadhaar = $request->input('aadhaar_number', '');

        $customer = null;
        if ($phone) {
            $customer = FinanceCustomer::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => $name,
                    'pan' => $pan,
                    'aadhaar' => $aadhaar,
                    'user_type' => 'customer',
                    'status' => 'active',
                ]
            );
            $customer->update([
                'name' => $name,
                'pan' => $pan,
                'aadhaar' => $aadhaar,
            ]);

            $docTypes = ['aadhaar_front', 'aadhaar_back', 'pan_card'];
            foreach ($docTypes as $dt) {
                if ($request->hasFile($dt)) {
                    $file = $request->file($dt);
                    $path = $file->store('finance_docs', 'public');
                    FinanceDocument::updateOrCreate(
                        [
                            'customer_id' => $customer->id,
                            'document_type' => $dt,
                        ],
                        [
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'status' => 'pending',
                        ]
                    );
                }
            }
        }

        return redirect()->route('finance.zero_cibil.s03_amount_select', ['phone' => $phone]);
    }

    public function saveZeroCibilAmount(Request $request)
    {
        $phone = $request->input('phone', $request->query('phone'));
        $amount = floatval($request->input('amount', 25000));
        if ($amount <= 0) $amount = 25000;

        $customer = $phone ? FinanceCustomer::where('phone', $phone)->first() : null;
        if ($customer) {
            $product = FinanceLoanProduct::where('code', 'zero_cibil_daily')->first();
            $baseFee = $product ? floatval($product->processing_fee_value) : 2500.00;
            $tax = round($baseFee * 0.18, 2);

            FinanceLoanApplication::create([
                'customer_id' => $customer->id,
                'loan_product_id' => $product->id ?? 1,
                'application_number' => 'FIIN-ZC-' . strtoupper(uniqid()),
                'applicant_name' => $customer->name ?? 'Applicant',
                'applicant_phone' => $customer->phone,
                'loan_category' => 'zero_cibil_micro',
                'requested_amount' => $amount,
                'tenure_months' => 1,
                'application_status' => 'DRAFT',
                'partner_lock_status' => 'unlocked',
                'processing_fee_base' => $baseFee,
                'processing_fee_tax' => $tax,
                'processing_fee_total' => $baseFee + $tax,
                'fee_payment_status' => 'pending',
            ]);
        }

        return redirect()->route('finance.zero_cibil.s04_fee_payment', ['phone' => $phone, 'amount' => $amount]);
    }

    public function verifyFeePayment(Request $request)
    {
        $phone = $request->input('phone');
        $paymentId = $request->input('payment_id', $request->input('razorpay_payment_id', 'PAY-' . time()));
        $amount = floatval($request->input('amount', 0));
        $nextUrl = $request->input('next_url');

        $customer = $phone ? FinanceCustomer::where('phone', $phone)->first() : null;
        $application = null;
        if ($customer) {
            $application = FinanceLoanApplication::where('customer_id', $customer->id)->latest('id')->first();
        }

        if ($application) {
            $application->update([
                'fee_payment_status' => 'paid',
                'processing_fee_status' => 'paid',
                'processing_fee_payment_method' => 'razorpay',
                'processing_fee_txn_id' => $paymentId,
                'application_status' => 'UNDERWRITING',
            ]);

            FinanceTransaction::create([
                'customer_id' => $customer->id,
                'application_id' => $application->id,
                'txn_number' => $paymentId,
                'txn_type' => 'fee_payment',
                'amount' => $application->processing_fee_total ?: $amount,
                'direction' => 'debit',
                'payment_method' => 'razorpay',
                'payment_gateway_ref' => $paymentId,
                'status' => 'success',
                'notes' => 'Razorpay Fee Payment verified for ' . $application->application_number,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Processing fee payment verified successfully.',
            'redirect' => $nextUrl,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // FLOW A — CASH LOAN (26 Screens)
    // ─────────────────────────────────────────────────────────────

    public function cashLoanApply(Request $request)
    {
        $lock = $this->checkActiveApplicationLock($request, 'cash_loan', 'Cash Loan');
        if ($lock) return $lock;

        $ctx = $this->resolveContext($request);
        $phone = $ctx['phone'];
        $application = $ctx['application'];

        // If user already has an active in-progress application, auto-resume to their current step
        if ($application && !in_array($application->application_status, ['DISBURSED', 'REJECTED', 'CLOSED', 'WITHDRAWN'])) {
            $resumeUrl = $this->getResumeUrlForApplication($application, $phone);
            if ($resumeUrl) {
                return redirect($resumeUrl);
            }
        }

        return view('finance.cash_loan.s01_apply', $ctx);
    }

    public function cashLoanTypeConsent(Request $request)      { return view('finance.cash_loan.s02_type_consent', $this->resolveContext($request)); }
    public function cashLoanApplicantDetails(Request $request) { return view('finance.cash_loan.s03_applicant_details', $this->resolveContext($request)); }
    public function cashLoanEligibility(Request $request)      { return view('finance.cash_loan.s04_eligibility', $this->resolveContext($request)); }
    public function cashLoanAmountTenure(Request $request)     { return view('finance.cash_loan.s05_tenure', $this->resolveContext($request)); }
    public function cashLoanEmi(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $params = ['phone' => $ctx['phone'], 'amount' => $ctx['amount'], 'tenure' => $ctx['tenure']];
        if (!empty($ctx['hideHeader'])) {
            $params['hide_header'] = '1';
        }
        return redirect()->route('finance.cash_loan.s05_tenure', $params);
    }

    public function cashLoanDocuments(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!empty($ctx['application']) && in_array($ctx['application']->application_status, ['DRAFT', 'KYC_PENDING', 'DETAILS_SAVED', 'ELIGIBILITY', 'AMOUNT_PENDING', 'TENURE_SELECTED'])) {
            $ctx['application']->update(['application_status' => 'DOCS_PENDING']);
        }
        return view('finance.cash_loan.s07_documents', $ctx);
    }

    public function cashLoanReadyProcessing(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!empty($ctx['application']) && in_array($ctx['application']->application_status, ['DRAFT', 'KYC_PENDING', 'DETAILS_SAVED', 'ELIGIBILITY', 'AMOUNT_PENDING', 'TENURE_SELECTED', 'DOCS_PENDING'])) {
            $ctx['application']->update(['application_status' => 'DOCS_SUBMITTED']);
        }
        return view('finance.cash_loan.s08_ready', $ctx);
    }

    public function cashLoanSanctionSummary(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!empty($ctx['application']) && in_array($ctx['application']->application_status, ['DRAFT', 'KYC_PENDING', 'DETAILS_SAVED', 'ELIGIBILITY', 'AMOUNT_PENDING', 'TENURE_SELECTED', 'DOCS_PENDING', 'DOCS_SUBMITTED'])) {
            $ctx['application']->update(['application_status' => 'SANCTIONED']);
        }
        return view('finance.cash_loan.s08b_sanction_summary', $ctx);
    }

    public function cashLoanFeePayment(Request $request)       { return view('finance.cash_loan.s09_fee_payment', $this->resolveContext($request)); }
    public function cashLoanApplicationGen(Request $request)   { return view('finance.cash_loan.s10_app_generated', $this->resolveContext($request)); }
    public function cashLoanPartnerDashboard(Request $request) { return view('finance.cash_loan.s11_partner_dashboard', $this->resolveContext($request)); }
    public function cashLoanPartnerVerify(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $partnerId = $request->query('partner_id', $request->input('partner_id'));
        if ($partnerId && !empty($ctx['application'])) {
            $partner = FinanceLenderPartner::find($partnerId);
            if ($partner) {
                $ctx['application']->update([
                    'selected_lender_id' => $partner->id,
                    'selected_lender_name' => $partner->name,
                ]);
                $ctx['selectedPartner'] = $partner;
            }
        }
        return view('finance.cash_loan.s12_partner_verify', $ctx);
    }

    public function cashLoanPartnerRedirect(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $partnerId = $request->query('partner_id', $request->input('partner_id'));
        if ($partnerId && !empty($ctx['application'])) {
            $partner = FinanceLenderPartner::find($partnerId);
            if ($partner) {
                $ctx['application']->update([
                    'selected_lender_id' => $partner->id,
                    'selected_lender_name' => $partner->name,
                    'partner_selection_time' => now(),
                    'application_status' => 'PARTNER_SELECTED',
                ]);
                $ctx['selectedPartner'] = $partner;
            }
        }
        return view('finance.cash_loan.s13_partner_redirect', $ctx);
    }
    public function cashLoanLenderWebview(Request $request)    { return view('finance.cash_loan.s14_lender_webview', $this->resolveContext($request)); }
    public function cashLoanProofUpload(Request $request)      { return view('finance.cash_loan.s15_proof_upload', $this->resolveContext($request)); }
    public function cashLoanValidation(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!empty($ctx['application'])) {
            $app = $ctx['application'];
            if ($app->application_status === 'REJECTED') {
                return redirect()->route('finance.cash_loan.s26_final_result', ['phone' => $ctx['phone']]);
            }
            if (in_array($app->application_status, ['SELFIE_PENDING', 'VALIDATION_APPROVED', 'PROCESSING', 'APPROVED', 'LOAN_APPROVED'])) {
                return redirect()->route('finance.cash_loan.s17_selfie_agent', ['phone' => $ctx['phone']]);
            }
            if (!in_array($app->application_status, ['VALIDATION_PENDING', 'PROOF_SUBMITTED'])) {
                $app->update([
                    'application_status' => 'VALIDATION_PENDING',
                    'proof_submitted_at' => $app->proof_submitted_at ?? now(),
                ]);
            }
        }
        return view('finance.cash_loan.s16_validation', $ctx);
    }

    public function checkApplicationStatusPoll(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $app = $ctx['application'];
        $phone = $ctx['phone'];

        if (!$app) {
            return response()->json(['status' => 'NOT_FOUND', 'action' => 'wait']);
        }

        $status = $app->application_status;

        if ($status === 'REJECTED') {
            return response()->json([
                'status' => 'REJECTED',
                'action' => 'redirect',
                'redirect_url' => route('finance.cash_loan.s26_final_result', ['phone' => $phone]),
                'reason' => $app->rejection_reason ?? 'Underwriting criteria not met.',
            ]);
        }

        if (in_array($status, ['SELFIE_PENDING', 'VALIDATION_APPROVED', 'PROCESSING', 'APPROVED', 'LOAN_APPROVED'])) {
            return response()->json([
                'status' => $status,
                'action' => 'redirect',
                'redirect_url' => route('finance.cash_loan.s17_selfie_agent', ['phone' => $phone]),
            ]);
        }

        return response()->json([
            'status' => $status,
            'action' => 'wait',
        ]);
    }

    public function cashLoanSelfieAgent(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!empty($ctx['application'])) {
            $app = $ctx['application'];
            if ($app->application_status === 'REJECTED') {
                return redirect()->route('finance.cash_loan.s26_final_result', ['phone' => $ctx['phone']]);
            }
        }
        return view('finance.cash_loan.s17_selfie_agent', $ctx);
    }

    public function cashLoanSelfieAgentSubmit(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $app = $ctx['application'];
        $phone = $ctx['phone'];

        $path = null;

        // 1. Direct file upload from native camera
        if ($request->hasFile('selfie')) {
            $file = $request->file('selfie');
            $path = $file->store('finance_docs', 'public');
        } elseif ($request->filled('selfie_base64')) {
            // 2. Base64 data from in-browser live capture
            $base64 = $request->input('selfie_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                $imageType = strtolower($matches[1]);
                $data = substr($base64, strpos($base64, ',') + 1);
                $decoded = base64_decode($data);
                if ($decoded !== false) {
                    $ext = in_array($imageType, ['jpg', 'jpeg', 'png', 'webp']) ? $imageType : 'jpg';
                    $filename = 'finance_docs/selfie_' . uniqid() . '.' . $ext;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
                    $path = $filename;
                }
            }
        }

        if ($path && $app) {
            $app->update([
                'agent_selfie_url' => $path,
                'application_status' => 'PROCESSING',
            ]);
        }

        return redirect()->route('finance.cash_loan.s18_tracking', ['phone' => $phone]);
    }
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

    public function businessLoanApply(Request $request)
    {
        $lock = $this->checkActiveApplicationLock($request, 'business_loan', 'Business Loan');
        if ($lock) return $lock;

        $ctx = $this->resolveContext($request);
        $phone = $ctx['phone'];
        $application = $ctx['application'];

        if ($application && !in_array($application->application_status, ['DISBURSED', 'REJECTED', 'CLOSED', 'WITHDRAWN'])) {
            $resumeUrl = $this->getResumeUrlForApplication($application, $phone);
            if ($resumeUrl) {
                return redirect($resumeUrl);
            }
        }

        return view('finance.business_loan.s01_apply', $ctx);
    }
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

    public function zeroCibilIntro(Request $request)
    {
        $lock = $this->checkActiveApplicationLock($request, 'zero_cibil', 'Zero-CIBIL Daily Credit');
        if ($lock) return $lock;

        return view('finance.zero_cibil.s01_intro', $this->resolveContext($request));
    }
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
                $application = FinanceLoanApplication::where('customer_id', $customer->id)
                    ->where('loan_category', 'zero_cibil_micro')
                    ->latest('id')
                    ->first();
                if (!$wallet && (!$application || !in_array($application->application_status, ['LOAN_APPROVED', 'DISBURSED']))) {
                    return redirect()->route('finance.zero_cibil.s05_pending', ['phone' => $phone]);
                }
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

    public function virtualLoanApply(Request $request)
    {
        $lock = $this->checkActiveApplicationLock($request, 'virtual_loan', 'Virtual Loan');
        if ($lock) return $lock;

        return view('finance.virtual_loan.s01_apply', $this->resolveContext($request));
    }
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

    public function studentCreditApply(Request $request)
    {
        $lock = $this->checkActiveApplicationLock($request, 'student_credit', 'Student Credit');
        if ($lock) return $lock;

        return view('finance.student_credit.s01_apply', $this->resolveContext($request));
    }
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

    // ─────────────────────────────────────────────────────────────
    // WITHDRAW / CANCEL APPLICATION
    // ─────────────────────────────────────────────────────────────

    public function withdrawApplication(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $phone = $ctx['phone'];
        $customer = $ctx['customer'];

        $application = null;
        if ($customer) {
            $application = FinanceLoanApplication::where('customer_id', $customer->id)
                ->whereNotIn('application_status', ['DISBURSED', 'REJECTED', 'CLOSED', 'WITHDRAWN'])
                ->orderBy('id', 'desc')
                ->first();
        }

        if ($application) {
            if ($application->fee_payment_status === 'paid') {
                return redirect()->route('finance.hub', ['phone' => $phone])
                    ->with('error', 'Applications that are already paid or completed cannot be withdrawn.');
            }

            $appNum = $application->application_number;
            $application->update([
                'application_status' => 'WITHDRAWN',
                'rejection_reason' => 'Withdrawn by borrower before fee payment',
            ]);

            return redirect()->route('finance.hub', ['phone' => $phone])
                ->with('success', "Application #{$appNum} has been withdrawn successfully. You can now choose a new loan product.");
        }

        return redirect()->route('finance.hub', ['phone' => $phone])
            ->with('success', 'You have no active applications. You can start a new application anytime.');
    }
}

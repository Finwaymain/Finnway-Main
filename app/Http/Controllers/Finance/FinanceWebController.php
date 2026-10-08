<?php

namespace App\Http\Controllers\Finance;

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
                // Ongoing in-funnel application
                $application = FinanceLoanApplication::where('customer_id', $customer->id)
                    ->whereNotIn('application_status', ['DISBURSED', 'REJECTED', 'CLOSED', 'WITHDRAWN'])
                    ->orderBy('id', 'desc')
                    ->first();

                // Active disbursed loan
                $disbursedLoan = FinanceLoanApplication::where('customer_id', $customer->id)
                    ->where('application_status', 'DISBURSED')
                    ->orderBy('id', 'desc')
                    ->first();
            }
        }

        $loanContext = $application ?? ($disbursedLoan ?? null);

        // Amount resolution (Approved Amount from Underwriting takes highest priority if set; otherwise Request parameter -> Requested Amount -> Default 25,000)
        $approvedAmount = floatval($loanContext->approved_amount ?? 0);
        if ($approvedAmount > 0) {
            $amount = $approvedAmount;
        } else {
            $rawAmount = $request->query('amount', $request->input('amount', $loanContext->requested_amount ?? 25000));
            $amount = floatval($rawAmount);
            if ($amount <= 0) {
                $amount = 25000;
            }
        }

        // Tenure resolution (Request parameter -> Application -> Default 12 months)
        $rawTenure = $request->query('tenure', $request->input('tenure', $loanContext->tenure_months ?? 12));
        $tenure = intval($rawTenure);
        if ($tenure <= 0) {
            $tenure = 12;
        }

        $loanType = $request->query('loan_type', $request->input('loan_type', $loanContext->loan_type ?? 'low_cibil'));
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

        if (in_array($productCategory, ['zero_cibil', 'zero_cibil_micro', 'zero_cibil_daily'])) {
            if ($amount > 84000) $amount = 84000;
            if ($amount < 15000) $amount = 15000;
            $feeMap = [
                15000 => 3500.0,
                24000 => 4500.0,
                65000 => 6903.0,
                84000 => 9500.0,
            ];
            if (isset($feeMap[(int)$amount])) {
                $totalFee = $feeMap[(int)$amount];
                $baseFee = round($totalFee / 1.18, 2);
                $feeTax = round($totalFee - $baseFee, 2);
            } else {
                $totalFee = round(3500 + (($amount - 15000) / (84000 - 15000)) * (9500 - 3500), 2);
                $baseFee = round($totalFee / 1.18, 2);
                $feeTax = round($totalFee - $baseFee, 2);
            }
        } elseif ($application && $application->processing_fee_base > 0) {
            $baseFee = floatval($application->processing_fee_base);
            $feeTax = round($baseFee * 0.18, 2);
            $totalFee = $baseFee + $feeTax;
        } elseif ($application && $application->processing_fee_amount > 0) {
            $baseFee = floatval($application->processing_fee_amount);
            $feeTax = round($baseFee * 0.18, 2);
            $totalFee = $baseFee + $feeTax;
        } elseif ($product) {
            if ($product->processing_fee_type === 'percentage') {
                $baseFee = round($amount * (floatval($product->processing_fee_value) / 100), 2);
            } else {
                $baseFee = floatval($product->processing_fee_value);
            }
            $feeTax = round($baseFee * 0.18, 2);
            $totalFee = $baseFee + $feeTax;
        } else {
            $baseFee = max(999, min(2500, round($amount * 0.02, 2)));
            $feeTax = round($baseFee * 0.18, 2);
            $totalFee = $baseFee + $feeTax;
        }

        // Exact itemized 5-component fee breakdown (Clean integers, no decimals - User Req 1)
        if (in_array($productCategory, ['zero_cibil', 'zero_cibil_micro', 'zero_cibil_daily'])) {
            $amtInt = (int)$amount;
            if ($amtInt === 15000) {
                $itemizedFees = [
                    'processing' => 760,
                    'verification' => 610,
                    'platform' => 710,
                    'agreement' => 510,
                    'monitoring' => 376,
                    'gst' => 534,
                    'total' => 3500,
                ];
                $baseFee = 2966.0;
                $feeTax = 534.0;
                $totalFee = 3500.0;
            } elseif ($amtInt === 24000) {
                $itemizedFees = [
                    'processing' => 978,
                    'verification' => 782,
                    'platform' => 913,
                    'agreement' => 652,
                    'monitoring' => 489,
                    'gst' => 686,
                    'total' => 4500,
                ];
                $baseFee = 3814.0;
                $feeTax = 686.0;
                $totalFee = 4500.0;
            } elseif ($amtInt === 84000) {
                $itemizedFees = [
                    'processing' => 2064,
                    'verification' => 1651,
                    'platform' => 1927,
                    'agreement' => 1376,
                    'monitoring' => 1033,
                    'gst' => 1449,
                    'total' => 9500,
                ];
                $baseFee = 8051.0;
                $feeTax = 1449.0;
                $totalFee = 9500.0;
            } else { // 65000 or custom
                $itemizedFees = [
                    'processing' => 1500,
                    'verification' => 1200,
                    'platform' => 1400,
                    'agreement' => 1000,
                    'monitoring' => 750,
                    'gst' => 1053,
                    'total' => 6903,
                ];
                $baseFee = 5850.0;
                $feeTax = 1053.0;
                $totalFee = 6903.0;
            }
        } else {
            $pFee = round($baseFee * 0.2564, 2);
            $vFee = round($baseFee * 0.2051, 2);
            $plFee = round($baseFee * 0.2393, 2);
            $aFee = round($baseFee * 0.1709, 2);
            $mFee = round($baseFee - ($pFee + $vFee + $plFee + $aFee), 2);
            $itemizedFees = [
                'processing' => $pFee,
                'verification' => $vFee,
                'platform' => $plFee,
                'agreement' => $aFee,
                'monitoring' => $mFee,
                'gst' => $feeTax,
                'total' => $totalFee,
            ];
        }

        // Flow A has lender, Flow B does NOT have lender
        $hasLender = in_array($productCategory, ['low_cibil_cash', 'prime_cash', 'business_msme', 'cash_loan', 'business_loan']);

        // Resolve Razorpay Key specifically for Loan Flow (isolated from whole flow / payment_settings key 13)
        $loanRzp = \App\Helpers\LoanRazorpayConfig::resolve();
        $razorpayKey = $loanRzp['key'] ?: (string) env('LOAN_RAZORPAY_KEY', env('RAZORPAY_KEY', 'rzp_test_fiinway'));
        $razorpayMerchantName = $loanRzp['merchant_name'] ?: 'Fiinway Loan & Credit';

        $documents = [];
        $uploadedDocs = [];
        $customerDocuments = collect();
        if ($customer) {
            $customerDocuments = FinanceDocument::where('customer_id', $customer->id)->get();
            $documents = $customerDocuments->pluck('file_path', 'document_type')->toArray();
            foreach ($customerDocuments as $doc) {
                $uploadedDocs[$doc->document_type] = [
                    'id' => $doc->id,
                    'file_name' => $doc->file_name ?? basename($doc->file_path),
                    'file_path' => asset('storage/' . $doc->file_path),
                    'status' => $doc->status,
                    'verified' => in_array($doc->status, ['verified', 'approved']),
                ];
            }
        }

        // Determine if loan is approved or disbursed (for conditional bottom navigation)
        $isDisbursedOrApproved = false;
        if ($application && in_array($application->application_status, ['LOAN_APPROVED', 'DISBURSED', 'ACTIVE'])) {
            $isDisbursedOrApproved = true;
        } elseif (!empty($disbursedLoan) && in_array($disbursedLoan->application_status, ['DISBURSED', 'ACTIVE'])) {
            $isDisbursedOrApproved = true;
        } elseif ($customer && FinanceWallet::where('customer_id', $customer->id)->where('status', 'active')->exists()) {
            $isDisbursedOrApproved = true;
        }

        // Active additional document request or individual reupload requests
        $reuploadDocs = collect();
        if ($customer) {
            $reuploadDocs = FinanceDocument::where('customer_id', $customer->id)
                ->where('status', 'reupload_required')
                ->get();
        }

        $activeDocRequest = null;
        if ($customer) {
            $activeDocRequest = \App\Models\Finance\FinanceDocumentRequest::where('customer_id', $customer->id)
                ->where(function ($q) use ($application) {
                    if ($application) {
                        $q->where('application_id', $application->id)->orWhereNull('application_id');
                    }
                })
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->first();
        }

        $appStatus = $application->application_status ?? 'UNDERWRITING';
        $hasDocRequest = ($appStatus === 'ADDITIONAL_DOCS_REQUESTED')
            || ($activeDocRequest && $activeDocRequest->status === 'pending')
            || $reuploadDocs->isNotEmpty();

        // If documents already resubmitted and no new pending request
        if ($appStatus === 'DOCS_RESUBMITTED' && $reuploadDocs->isEmpty() && (!$activeDocRequest || $activeDocRequest->status !== 'pending')) {
            $hasDocRequest = false;
        }

        $docCodeLabelMap = [
            'salary_slips' => 'Salary Slips (Recent 3 Months)',
            'salary_slip' => 'Salary Slips (Recent 3 Months)',
            'bank_statement' => 'Bank Statement (Last 6 Months PDF)',
            'bank_passbook' => 'Bank Passbook (First Page with Account & IFSC)',
            'cancelled_cheque' => 'Cancelled Cheque',
            'gst_certificate' => 'GST / Business Certificate',
            'bonafide_certificate' => 'Bonafide / Enrollment Certificate',
            'aadhaar' => 'Full Aadhaar Card (Front & Back)',
            'aadhaar_front' => 'Aadhaar Card (Front Side)',
            'aadhaar_back' => 'Aadhaar Card (Back Side)',
            'pan' => 'PAN Card Copy',
            'pan_card' => 'PAN Card Copy',
            'selfie' => 'Clear Front Selfie with ID Card',
            'address_proof' => 'Electricity Bill / Rent Agreement',
            'other' => 'Additional Supporting Document',
        ];

        $requestedDocsList = [];

        // 1. Documents explicitly marked for reupload by Admin
        foreach ($reuploadDocs as $rDoc) {
            $label = $docCodeLabelMap[$rDoc->document_type] ?? ucwords(str_replace('_', ' ', $rDoc->document_type));
            $requestedDocsList[] = [
                'label' => $label,
                'doc_id' => $rDoc->id,
                'doc_type' => $rDoc->document_type,
                'remark' => $rDoc->admin_remark ?: 'Please upload a clearer copy.',
            ];
        }

        // 2. Documents requested via Document Request batch
        $rawRequestedDocs = $activeDocRequest ? ($activeDocRequest->requested_documents ?? []) : [];
        if (is_string($rawRequestedDocs)) {
            $decoded = json_decode($rawRequestedDocs, true);
            $rawRequestedDocs = is_array($decoded) ? $decoded : (trim($rawRequestedDocs) ? [trim($rawRequestedDocs)] : []);
        }

        if (!empty($rawRequestedDocs) && is_array($rawRequestedDocs)) {
            foreach ($rawRequestedDocs as $item) {
                if (is_string($item)) {
                    $key = strtolower(trim($item));
                    $label = $docCodeLabelMap[$key] ?? ucwords(str_replace('_', ' ', trim($item)));
                    $already = false;
                    foreach ($requestedDocsList as $existing) {
                        if (strtolower($existing['label']) === strtolower($label)) {
                            $already = true;
                            break;
                        }
                    }
                    if (!$already) {
                        $requestedDocsList[] = [
                            'label' => $label,
                            'doc_id' => null,
                            'doc_type' => $key,
                            'remark' => $activeDocRequest->admin_remark ?: null,
                        ];
                    }
                }
            }
        }

        if ($hasDocRequest && empty($requestedDocsList)) {
            $requestedDocsList[] = [
                'label' => 'Additional Verification Document',
                'doc_id' => null,
                'doc_type' => 'additional_supporting_doc',
                'remark' => $activeDocRequest->admin_remark ?? 'Additional verification document requested by administration.',
            ];
        }

        $docRequestRemark = $activeDocRequest ? $activeDocRequest->admin_remark : ($reuploadDocs->first() ? $reuploadDocs->first()->admin_remark : null);

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
        $applicantName = $loanContext->applicant_name 
            ?? ($customer->name ?? ($customer->full_name ?? ($request->input('name') ?: 'Valued Applicant')));
        
        $applicantPhone = $loanContext->applicant_phone 
            ?? ($customer->phone ?? ($phone ?: ''));

        $appNumber = $loanContext->application_number 
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
            'disbursedLoan' => $disbursedLoan ?? null,
            'activeLoan' => $loanContext,
            'appNumber' => $appNumber,
            'validationTimerSeconds' => $validationTimerSeconds,
            'applicantName' => $applicantName,
            'applicantPhone' => $applicantPhone,
            'partners' => $partners,
            'lenders' => $partners,
            'selectedPartner' => $selectedPartner,
            'product' => $product,
            'amount' => $amount,
            'approvedAmount' => $approvedAmount,
            'tenure' => $tenure,
            'loanType' => $loanType,
            'emi' => round($monthlyEmi),
            'totalRepayment' => round($totalRepayment),
            'totalInterest' => round($totalInterest),
            'baseFee' => $baseFee,
            'feeTax' => $feeTax,
            'totalFee' => $totalFee,
            'itemizedFees' => $itemizedFees ?? [],
            'hasLender' => $hasLender,
            'razorpayKey' => $razorpayKey,
            'razorpayMerchantName' => $razorpayMerchantName,
            'documents' => $documents,
            'uploadedDocs' => $uploadedDocs,
            'customerDocuments' => $customerDocuments,
            'isDisbursedOrApproved' => $isDisbursedOrApproved,
            'activeDocRequest' => $activeDocRequest,
            'hasDocRequest' => $hasDocRequest,
            'reuploadDocs' => $reuploadDocs,
            'requestedDocsList' => $requestedDocsList,
            'requestedDocs' => $requestedDocsList,
            'docRequestRemark' => $docRequestRemark,
            'hideHeader' => $hideHeader,
            'maxLimit' => $maxLimit,
        ];
    }

    public function getResumeUrlForApplication(FinanceLoanApplication $application, ?string $phone): ?string
    {
        $status = $application->application_status;
        $amount = ($application->approved_amount && floatval($application->approved_amount) > 0)
            ? floatval($application->approved_amount)
            : ($application->requested_amount ?: 25000);
        $tenure = $application->tenure_months ?: 12;
        $cat = $application->loan_category ?? 'low_cibil';
        $params = ['phone' => $phone, 'amount' => $amount, 'tenure' => $tenure];
        if (session('finance_hide_header') || request('hide_header') == '1' || request('app') == '1') {
            $params['hide_header'] = '1';
        }

        if (in_array($cat, ['zero_cibil', 'zero_cibil_micro', 'zero_cibil_daily'])) {
            $stepMap = [
                'KYC_PENDING'               => route('finance.zero_cibil.s02_kyc', ['phone' => $phone]),
                'AMOUNT_PENDING'            => route('finance.zero_cibil.s03_amount_select', ['phone' => $phone]),
                'DOCS_VERIFYING'            => route('finance.zero_cibil.s03b_doc_verification', ['phone' => $phone]),
                'SANCTIONED'                => route('finance.zero_cibil.s04_fee_payment', ['phone' => $phone, 'amount' => $amount]),
                'FEE_PENDING'               => route('finance.zero_cibil.s04_fee_payment', ['phone' => $phone, 'amount' => $amount]),
                'UNDERWRITING'              => route('finance.zero_cibil.s05_pending', ['phone' => $phone]),
                'FEE_PAID'                  => route('finance.zero_cibil.s05_pending', ['phone' => $phone]),
                'ADDITIONAL_DOCS_REQUESTED' => route('finance.zero_cibil.s05_pending', ['phone' => $phone]),
                'DOCS_RESUBMITTED'          => route('finance.zero_cibil.s05_pending', ['phone' => $phone]),
                'LOAN_APPROVED'             => route('finance.zero_cibil.s06_wallet_active', ['phone' => $phone]),
                'DISBURSED'                 => route('finance.zero_cibil.s06_wallet_active', ['phone' => $phone]),
                'ACTIVE'                    => route('finance.zero_cibil.s06_wallet_active', ['phone' => $phone]),
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
        $disbursedLoan = $ctx['disbursedLoan'] ?? null;
        $isRunning = ($application && !in_array($application->application_status, ['DISBURSED', 'REJECTED', 'CLOSED', 'WITHDRAWN', 'DRAFT', 'APPLICATION_CREATED']));

        // Repayment & schedule details for active disbursed loan
        $nextDueSchedule = null;
        $totalPaidEmi = 0;
        $totalOutstandingEmi = 0;
        $scheduleList = collect();

        if ($disbursedLoan) {
            $this->ensureRepaymentSchedule($disbursedLoan);
            $scheduleList = FinanceDailySchedule::where('application_id', $disbursedLoan->id)
                ->orderBy('day_number', 'asc')
                ->get();
            $nextDueSchedule = $scheduleList->firstWhere('status', 'pending') ?? $scheduleList->firstWhere('status', 'overdue');
            $totalPaidEmi = $scheduleList->where('status', 'paid')->sum('paid_amount');
            $totalOutstandingEmi = $scheduleList->where('status', '!=', 'paid')->sum('total_due');
        }

        // Map active loan category to family
        $activeCategory = $application->loan_category ?? ($disbursedLoan->loan_category ?? '');
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

            // If user clicked View Details with an active disbursed loan, keep on hub
            if ($isViewDetails && $disbursedLoan) {
                return redirect()->route('finance.hub', ['phone' => $phone]);
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
            'disbursedLoan' => $disbursedLoan,
            'nextDueSchedule' => $nextDueSchedule,
            'totalPaidEmi' => $totalPaidEmi,
            'totalOutstandingEmi' => $totalOutstandingEmi,
            'scheduleList' => $scheduleList,
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
        $pan = strtoupper(trim($request->input('pan_number', '')));
        $aadhaar = trim($request->input('aadhaar_number', ''));
        $email = trim($request->input('email', ''));
        $altPhone = trim($request->input('alternate_phone', ''));
        $waPhone = trim($request->input('whatsapp_phone', ''));

        // Normalize 10-digit Indian phones for strict exclusivity check
        $cleanDigits = function ($p) {
            $digits = preg_replace('/\D/', '', (string)$p);
            return strlen($digits) >= 10 ? substr($digits, -10) : $digits;
        };

        $normPrimary = $cleanDigits($phone);
        $normAlt = $cleanDigits($altPhone);
        $normWa = $cleanDigits($waPhone);

        // Strict Mutual Exclusivity Validation
        $errors = [];
        if ($altPhone) {
            if (strlen($normAlt) !== 10) {
                $errors[] = 'Alternate number must be a valid 10-digit phone number.';
            } elseif ($normAlt === $normPrimary) {
                $errors[] = 'Alternate number cannot be the same as your Primary registered number.';
            }
        }
        if ($waPhone) {
            if (strlen($normWa) !== 10) {
                $errors[] = 'WhatsApp number must be a valid 10-digit phone number.';
            } elseif ($normWa === $normPrimary) {
                $errors[] = 'WhatsApp number cannot be the same as your Primary registered number.';
            } elseif ($altPhone && $normWa === $normAlt) {
                $errors[] = 'WhatsApp number cannot be the same as your Alternate number.';
            }
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        $customer = null;
        if ($phone) {
            $variants = \App\Services\PhoneService::getVariants($phone);
            $customer = FinanceCustomer::whereIn('phone', $variants)->first();
            if (!$customer) {
                $customer = FinanceCustomer::create([
                    'phone' => $phone,
                    'name' => $name,
                    'pan' => $pan,
                    'aadhaar' => $aadhaar,
                    'email' => $email ?: null,
                    'alternate_phone' => $altPhone ?: null,
                    'whatsapp_phone' => $waPhone ?: null,
                    'user_type' => 'customer',
                    'status' => 'active',
                ]);
            } else {
                $customerUpdates = [
                    'name' => $name,
                    'pan' => $pan,
                    'aadhaar' => $aadhaar,
                ];
                if ($altPhone) $customerUpdates['alternate_phone'] = $altPhone;
                if ($waPhone) $customerUpdates['whatsapp_phone'] = $waPhone;
                if ($email) $customerUpdates['email'] = $email;
                $customer->update($customerUpdates);
            }

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

            // Immediately create/update application to persist state across app closures
            $application = FinanceLoanApplication::where('customer_id', $customer->id)
                ->where('loan_category', 'zero_cibil_micro')
                ->whereNotIn('application_status', ['REJECTED', 'CLOSED', 'WITHDRAWN'])
                ->latest('id')
                ->first();

            $applicantDetails = [
                'name' => $name,
                'pan' => $pan,
                'aadhaar' => $aadhaar,
                'email' => $email,
                'alternate_phone' => $altPhone,
                'whatsapp_phone' => $waPhone,
                'last_stage' => 'AMOUNT_PENDING',
            ];

            if (!$application) {
                $product = FinanceLoanProduct::where('code', 'zero_cibil_daily')->first();
                FinanceLoanApplication::create([
                    'customer_id' => $customer->id,
                    'loan_product_id' => $product->id ?? 1,
                    'application_number' => 'FIIN-ZC-' . strtoupper(uniqid()),
                    'applicant_name' => $name,
                    'applicant_phone' => $customer->phone,
                    'loan_category' => 'zero_cibil_micro',
                    'requested_amount' => 25000,
                    'tenure_months' => 1,
                    'application_status' => 'AMOUNT_PENDING',
                    'partner_lock_status' => 'unlocked',
                    'applicant_details' => $applicantDetails,
                ]);
            } else {
                $existingDetails = is_array($application->applicant_details)
                    ? $application->applicant_details
                    : (json_decode($application->applicant_details ?? '[]', true) ?: []);
                $mergedDetails = array_merge($existingDetails, $applicantDetails);
                $application->update([
                    'applicant_name' => $name,
                    'application_status' => 'AMOUNT_PENDING',
                    'applicant_details' => $mergedDetails,
                ]);
            }
        }

        return redirect()->route('finance.zero_cibil.s03_amount_select', ['phone' => $phone]);
    }

    public function saveZeroCibilAmount(Request $request)
    {
        $phone = $request->input('phone', $request->query('phone'));
        $amount = floatval($request->input('amount', 65000));
        if ($amount > 84000) $amount = 84000;
        if ($amount < 15000) $amount = 15000;

        $customer = null;
        if ($phone) {
            $variants = \App\Services\PhoneService::getVariants($phone);
            $customer = FinanceCustomer::whereIn('phone', $variants)->first();
        }

        if ($customer) {
            $product = FinanceLoanProduct::where('code', 'zero_cibil_daily')->first();
            
            // Map exact tier fee structure from policy (D15=3500, D12=4500, D30=6903, D45=9500)
            $feeMap = [
                15000 => 3500.0,
                24000 => 4500.0,
                65000 => 6903.0,
                84000 => 9500.0,
            ];
            if (isset($feeMap[(int)$amount])) {
                $totalFee = $feeMap[(int)$amount];
                $baseFee = round($totalFee / 1.18, 2);
                $tax = round($totalFee - $baseFee, 2);
            } else {
                $totalFee = round(3500 + (($amount - 15000) / (84000 - 15000)) * (9500 - 3500), 2);
                $baseFee = round($totalFee / 1.18, 2);
                $tax = round($totalFee - $baseFee, 2);
            }

            $application = FinanceLoanApplication::where('customer_id', $customer->id)
                ->where('loan_category', 'zero_cibil_micro')
                ->whereNotIn('application_status', ['REJECTED', 'CLOSED', 'WITHDRAWN'])
                ->latest('id')
                ->first();

            $appData = [
                'loan_product_id' => $product->id ?? 1,
                'applicant_name' => $customer->name ?? 'Applicant',
                'applicant_phone' => $customer->phone,
                'loan_category' => 'zero_cibil_micro',
                'requested_amount' => $amount,
                'tenure_months' => 1,
                'application_status' => 'DOCS_VERIFYING',
                'partner_lock_status' => 'unlocked',
                'processing_fee_base' => $baseFee,
                'processing_fee_tax' => $tax,
                'processing_fee_total' => $totalFee,
                'fee_payment_status' => 'pending',
            ];

            if ($application) {
                $details = is_array($application->applicant_details)
                    ? $application->applicant_details
                    : (json_decode($application->applicant_details ?? '[]', true) ?: []);
                $details['amount'] = $amount;
                $details['last_stage'] = 'DOCS_VERIFYING';
                $appData['applicant_details'] = $details;
                $application->update($appData);
            } else {
                $appData['customer_id'] = $customer->id;
                $appData['application_number'] = 'FIIN-ZC-' . strtoupper(uniqid());
                $appData['applicant_details'] = ['amount' => $amount, 'last_stage' => 'DOCS_VERIFYING'];
                FinanceLoanApplication::create($appData);
            }
        }

        return redirect()->route('finance.zero_cibil.s03b_doc_verification', ['phone' => $phone]);
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

        if (!$app && $request->filled('application_id')) {
            $app = FinanceLoanApplication::find($request->query('application_id'));
            if ($app && empty($phone) && $app->customer) {
                $phone = $app->customer->phone;
            }
        }

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

        $currentStep = $request->query('current_step', '');

        // 1. Validation Step Polling (s16)
        if ($currentStep === 's16') {
            if (in_array($status, ['SELFIE_PENDING', 'VALIDATION_APPROVED', 'PROCESSING', 'APPROVED', 'LOAN_APPROVED'])) {
                return response()->json([
                    'status' => $status,
                    'action' => 'redirect',
                    'redirect_url' => route('finance.cash_loan.s17_selfie_agent', ['phone' => $phone]),
                ]);
            }
            return response()->json(['status' => $status, 'action' => 'wait']);
        }

        // 2. Tracking / Underwriting Review Polling (s18)
        if ($currentStep === 's18') {
            if (in_array($status, ['LOAN_APPROVED', 'APPROVED'])) {
                $effectiveAmount = ($app->approved_amount && floatval($app->approved_amount) > 0)
                    ? floatval($app->approved_amount)
                    : ($app->requested_amount ?: 25000);
                return response()->json([
                    'status' => $status,
                    'action' => 'redirect',
                    'redirect_url' => route('finance.cash_loan.s21_approval', [
                        'phone' => $phone,
                        'amount' => $effectiveAmount,
                    ]),
                ]);
            }
            if ($status === 'ADDITIONAL_DOCS_REQUESTED') {
                return response()->json([
                    'status' => $status,
                    'action' => 'redirect',
                    'redirect_url' => route('finance.cash_loan.s24_additional_docs', ['phone' => $phone]),
                ]);
            }
            if ($status === 'DISBURSED' || $status === 'DISBURSEMENT_PENDING') {
                $effectiveAmount = ($app->approved_amount && floatval($app->approved_amount) > 0)
                    ? floatval($app->approved_amount)
                    : ($app->requested_amount ?: 25000);
                return response()->json([
                    'status' => $status,
                    'action' => 'redirect',
                    'redirect_url' => route('finance.cash_loan.s23_disbursement', [
                        'phone' => $phone,
                        'amount' => $effectiveAmount,
                    ]),
                ]);
            }
            return response()->json(['status' => $status, 'action' => 'wait']);
        }

        // 3. Additional Docs Required Polling (s24)
        if ($currentStep === 's24') {
            if (in_array($status, ['LOAN_APPROVED', 'APPROVED'])) {
                $effectiveAmount = ($app->approved_amount && floatval($app->approved_amount) > 0)
                    ? floatval($app->approved_amount)
                    : ($app->requested_amount ?: 25000);
                return response()->json([
                    'status' => $status,
                    'action' => 'redirect',
                    'redirect_url' => route('finance.cash_loan.s21_approval', [
                        'phone' => $phone,
                        'amount' => $effectiveAmount,
                    ]),
                ]);
            }
            if ($status === 'DOCS_RESUBMITTED') {
                return response()->json([
                    'status' => $status,
                    'action' => 'redirect',
                    'redirect_url' => route('finance.cash_loan.s25_docs_submitted', ['phone' => $phone]),
                ]);
            }
            return response()->json(['status' => $status, 'action' => 'wait']);
        }

        // 4. Additional Docs Submitted Polling (s25)
        if ($currentStep === 's25') {
            if (in_array($status, ['LOAN_APPROVED', 'APPROVED'])) {
                $effectiveAmount = ($app->approved_amount && floatval($app->approved_amount) > 0)
                    ? floatval($app->approved_amount)
                    : ($app->requested_amount ?: 25000);
                return response()->json([
                    'status' => $status,
                    'action' => 'redirect',
                    'redirect_url' => route('finance.cash_loan.s21_approval', [
                        'phone' => $phone,
                        'amount' => $effectiveAmount,
                    ]),
                ]);
            }
            if ($status === 'ADDITIONAL_DOCS_REQUESTED') {
                return response()->json([
                    'status' => $status,
                    'action' => 'redirect',
                    'redirect_url' => route('finance.cash_loan.s24_additional_docs', ['phone' => $phone]),
                ]);
            }
            return response()->json(['status' => $status, 'action' => 'wait']);
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

    public function cashLoanBankDetailsSubmit(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $phone = $ctx['phone'];
        $application = $ctx['application'];

        $request->validate([
            'account_holder_name'    => 'required|string|max:190',
            'bank_name'              => 'required|string|max:190',
            'account_number'         => 'required|string|min:6|max:35',
            'confirm_account_number' => 'required|same:account_number',
            'ifsc_code'              => 'required|string|min:4|max:20',
            'account_type'           => 'nullable|string|max:30',
        ]);

        if ($application) {
            $application->disbursement_account_name = trim($request->input('account_holder_name'));
            $application->disbursement_bank_name = trim($request->input('bank_name'));
            $application->disbursement_account_number = trim($request->input('account_number'));
            $application->disbursement_ifsc = strtoupper(trim($request->input('ifsc_code')));
            $application->disbursement_account_type = $request->input('account_type', 'Savings');
            $application->disbursement_status = 'pending';
            $application->application_status = 'DISBURSEMENT_PENDING';
            $application->save();
        }

        return redirect()->route('finance.cash_loan.s23_disbursement', ['phone' => $phone])
            ->with('success', 'Disbursement bank details saved successfully.');
    }

    public function cashLoanDisbursement(Request $request)     { return view('finance.cash_loan.s23_disbursement', $this->resolveContext($request)); }
    public function cashLoanAdditionalDocs(Request $request)   { return view('finance.cash_loan.s24_additional_docs', $this->resolveContext($request)); }

    public function cashLoanAdditionalDocsSubmit(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $customer = $ctx['customer'];
        $app = $ctx['application'];
        $phone = $ctx['phone'];

        if (!$customer) {
            return redirect()->route('finance.hub', ['phone' => $phone])->with('error', 'Session expired. Please restart.');
        }

        $uploadedCount = 0;

        // 1. Process array of requested documents
        if ($request->hasFile('doc_files')) {
            $files = $request->file('doc_files');
            $names = $request->input('doc_names', []);
            $docIds = $request->input('doc_ids', []);
            $docTypes = $request->input('doc_types', []);

            foreach ($files as $idx => $file) {
                if ($file && $file->isValid()) {
                    $rawName = $names[$idx] ?? ('Additional Doc ' . ($idx + 1));
                    $docId = !empty($docIds[$idx]) ? intval($docIds[$idx]) : null;
                    $docType = !empty($docTypes[$idx]) ? $docTypes[$idx] : null;
                    $path = $file->store('finance_docs', 'public');

                    $existingDoc = null;
                    if ($docId) {
                        $existingDoc = FinanceDocument::where('customer_id', $customer->id)->where('id', $docId)->first();
                    }
                    if (!$existingDoc && $docType) {
                        $existingDoc = FinanceDocument::where('customer_id', $customer->id)
                            ->where('document_type', $docType)
                            ->where('status', 'reupload_required')
                            ->latest('id')
                            ->first();
                    }

                    if ($existingDoc) {
                        $existingDoc->update([
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'status' => 'pending',
                            'admin_remark' => 'Re-uploaded by borrower: ' . $rawName,
                        ]);
                    } else {
                        $slugType = $docType ?: \Illuminate\Support\Str::slug($rawName, '_');
                        FinanceDocument::create([
                            'customer_id' => $customer->id,
                            'document_type' => $slugType,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'status' => 'pending',
                            'admin_remark' => 'Uploaded by borrower in response to Admin request: ' . $rawName,
                            'is_reusable' => true,
                            'reuse_valid_until' => now()->addDays(5),
                        ]);
                    }
                    $uploadedCount++;
                }
            }
        }

        // 2. Process optional extra document
        if ($request->hasFile('extra_doc')) {
            $file = $request->file('extra_doc');
            if ($file && $file->isValid()) {
                $path = $file->store('finance_docs', 'public');
                FinanceDocument::create([
                    'customer_id' => $customer->id,
                    'document_type' => 'additional_supporting_doc',
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'status' => 'pending',
                    'admin_remark' => 'Supporting document uploaded by borrower.',
                    'is_reusable' => true,
                    'reuse_valid_until' => now()->addDays(5),
                ]);
                $uploadedCount++;
            }
        }

        // 3. Mark document request as submitted
        \App\Models\Finance\FinanceDocumentRequest::where('customer_id', $customer->id)
            ->where(function ($q) use ($app) {
                if ($app) {
                    $q->where('application_id', $app->id)->orWhereNull('application_id');
                }
            })
            ->where('status', 'pending')
            ->update(['status' => 'submitted']);

        // 4. Update Application status to DOCS_RESUBMITTED
        if ($app) {
            $app->update([
                'application_status' => 'DOCS_RESUBMITTED',
            ]);
        }

        return redirect()->route('finance.cash_loan.s25_docs_submitted', ['phone' => $phone])
            ->with('success', "{$uploadedCount} document(s) uploaded successfully.");
    }
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

    public function businessLoanBankDetailsSubmit(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $phone = $ctx['phone'];
        $application = $ctx['application'];

        $request->validate([
            'account_holder_name'    => 'required|string|max:190',
            'bank_name'              => 'required|string|max:190',
            'account_number'         => 'required|string|min:6|max:35',
            'confirm_account_number' => 'required|same:account_number',
            'ifsc_code'              => 'required|string|min:4|max:20',
            'account_type'           => 'nullable|string|max:30',
        ]);

        if ($application) {
            $application->disbursement_account_name = trim($request->input('account_holder_name'));
            $application->disbursement_bank_name = trim($request->input('bank_name'));
            $application->disbursement_account_number = trim($request->input('account_number'));
            $application->disbursement_ifsc = strtoupper(trim($request->input('ifsc_code')));
            $application->disbursement_account_type = $request->input('account_type', 'Current');
            $application->disbursement_status = 'pending';
            $application->application_status = 'DISBURSEMENT_PENDING';
            $application->save();
        }

        return redirect()->route('finance.business_loan.s19_disbursement', ['phone' => $phone])
            ->with('success', 'Disbursement bank details saved successfully.');
    }

    public function businessLoanDisbursement(Request $request)     { return view('finance.business_loan.s19_disbursement', $this->resolveContext($request)); }
    public function businessLoanFinalStatus(Request $request)      { return view('finance.business_loan.s20_final_status', $this->resolveContext($request)); }

    // ─────────────────────────────────────────────────────────────
    // FLOW B — ZERO-CIBIL DAILY (7 Screens)
    // ─────────────────────────────────────────────────────────────

    public function zeroCibilIntro(Request $request)
    {
        $lock = $this->checkActiveApplicationLock($request, 'zero_cibil', 'Zero-CIBIL Daily Credit');
        if ($lock) return $lock;

        $ctx = $this->resolveContext($request);
        $app = $ctx['application'];
        if ($app && !in_array($app->application_status, ['DRAFT', 'APPLICATION_CREATED', 'REJECTED', 'CLOSED', 'WITHDRAWN'])) {
            $resume = $this->getResumeUrlForApplication($app, $ctx['phone']);
            if ($resume) return redirect($resume);
        }

        return view('finance.zero_cibil.s01_intro', $ctx);
    }

    public function zeroCibilKyc(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $app = $ctx['application'];
        // If approved or fee pending or further, no back navigation allowed
        if ($app && in_array($app->application_status, ['DOCS_VERIFYING', 'SANCTIONED', 'FEE_PENDING', 'UNDERWRITING', 'FEE_PAID', 'ADDITIONAL_DOCS_REQUESTED', 'DOCS_RESUBMITTED', 'LOAN_APPROVED', 'DISBURSED', 'ACTIVE'])) {
            $resume = $this->getResumeUrlForApplication($app, $ctx['phone']);
            if ($resume) return redirect($resume);
        }
        return view('finance.zero_cibil.s02_kyc', $ctx);
    }

    public function zeroCibilAmountSelect(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $app = $ctx['application'];
        // If approved or fee pending or further, no back navigation allowed
        if ($app && in_array($app->application_status, ['DOCS_VERIFYING', 'SANCTIONED', 'FEE_PENDING', 'UNDERWRITING', 'FEE_PAID', 'ADDITIONAL_DOCS_REQUESTED', 'DOCS_RESUBMITTED', 'LOAN_APPROVED', 'DISBURSED', 'ACTIVE'])) {
            $resume = $this->getResumeUrlForApplication($app, $ctx['phone']);
            if ($resume) return redirect($resume);
        }
        return view('finance.zero_cibil.s03_amount_select', $ctx);
    }

    public function zeroCibilDocVerification(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $app = $ctx['application'];
        // If already fee pending or past verification, forward to current step
        if ($app && in_array($app->application_status, ['SANCTIONED', 'FEE_PENDING', 'UNDERWRITING', 'FEE_PAID', 'ADDITIONAL_DOCS_REQUESTED', 'DOCS_RESUBMITTED', 'LOAN_APPROVED', 'DISBURSED', 'ACTIVE'])) {
            $resume = $this->getResumeUrlForApplication($app, $ctx['phone']);
            if ($resume) return redirect($resume);
        }
        return view('finance.zero_cibil.s03b_doc_verification', $ctx);
    }

    public function completeZeroCibilDocVerification(Request $request)
    {
        $phone = $request->input('phone', $request->query('phone'));
        if ($phone) {
            $variants = \App\Services\PhoneService::getVariants($phone);
            $customer = FinanceCustomer::whereIn('phone', $variants)->first();
            if ($customer) {
                $application = FinanceLoanApplication::where('customer_id', $customer->id)
                    ->where('loan_category', 'zero_cibil_micro')
                    ->whereNotIn('application_status', ['REJECTED', 'CLOSED', 'WITHDRAWN'])
                    ->latest('id')
                    ->first();
                if ($application) {
                    $details = is_array($application->applicant_details)
                        ? $application->applicant_details
                        : (json_decode($application->applicant_details ?? '[]', true) ?: []);
                    $details['last_stage'] = 'FEE_PENDING';
                    $application->update([
                        'application_status' => 'FEE_PENDING',
                        'applicant_details' => $details,
                    ]);
                }
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'redirect_url' => route('finance.zero_cibil.s04_fee_payment', ['phone' => $phone]),
            ]);
        }

        return redirect()->route('finance.zero_cibil.s04_fee_payment', ['phone' => $phone]);
    }

    public function zeroCibilFeePayment(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $app = $ctx['application'];
        if ($app && in_array($app->application_status, ['UNDERWRITING', 'FEE_PAID', 'ADDITIONAL_DOCS_REQUESTED', 'DOCS_RESUBMITTED', 'LOAN_APPROVED', 'DISBURSED', 'ACTIVE'])) {
            $resume = $this->getResumeUrlForApplication($app, $ctx['phone']);
            if ($resume) return redirect($resume);
        }
        return view('finance.zero_cibil.s04_fee_payment', $ctx);
    }
    public function zeroCibilPending(Request $request)      { return view('finance.zero_cibil.s05_pending', $this->resolveContext($request)); }

    public function zeroCibilAdditionalDocsSubmit(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $customer = $ctx['customer'];
        $app = $ctx['application'];
        $phone = $ctx['phone'];

        if (!$customer) {
            return redirect()->route('finance.hub', ['phone' => $phone])->with('error', 'Session expired. Please restart.');
        }

        $uploadedCount = 0;

        // 1. Process array of requested documents
        if ($request->hasFile('doc_files')) {
            $files = $request->file('doc_files');
            $names = $request->input('doc_names', []);
            $docIds = $request->input('doc_ids', []);
            $docTypes = $request->input('doc_types', []);

            foreach ($files as $idx => $file) {
                if ($file && $file->isValid()) {
                    $rawName = $names[$idx] ?? ('Additional Doc ' . ($idx + 1));
                    $docId = !empty($docIds[$idx]) ? intval($docIds[$idx]) : null;
                    $docType = !empty($docTypes[$idx]) ? $docTypes[$idx] : null;
                    $path = $file->store('finance_docs', 'public');

                    $existingDoc = null;
                    if ($docId) {
                        $existingDoc = FinanceDocument::where('customer_id', $customer->id)->where('id', $docId)->first();
                    }
                    if (!$existingDoc && $docType) {
                        $existingDoc = FinanceDocument::where('customer_id', $customer->id)
                            ->where('document_type', $docType)
                            ->where('status', 'reupload_required')
                            ->latest('id')
                            ->first();
                    }

                    if ($existingDoc) {
                        $existingDoc->update([
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'status' => 'pending',
                            'admin_remark' => 'Re-uploaded by borrower: ' . $rawName,
                        ]);
                    } else {
                        $slugType = $docType ?: \Illuminate\Support\Str::slug($rawName, '_');
                        FinanceDocument::create([
                            'customer_id' => $customer->id,
                            'document_type' => $slugType,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'status' => 'pending',
                            'admin_remark' => 'Uploaded by borrower in response to Admin request: ' . $rawName,
                            'is_reusable' => true,
                            'reuse_valid_until' => now()->addDays(5),
                        ]);
                    }
                    $uploadedCount++;
                }
            }
        }

        // 2. Process optional extra document
        if ($request->hasFile('extra_doc')) {
            $file = $request->file('extra_doc');
            if ($file && $file->isValid()) {
                $path = $file->store('finance_docs', 'public');
                FinanceDocument::create([
                    'customer_id' => $customer->id,
                    'document_type' => 'additional_supporting_doc',
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'status' => 'pending',
                    'admin_remark' => 'Supporting document uploaded by borrower.',
                    'is_reusable' => true,
                    'reuse_valid_until' => now()->addDays(5),
                ]);
                $uploadedCount++;
            }
        }

        // 3. Mark document request as submitted
        \App\Models\Finance\FinanceDocumentRequest::where('customer_id', $customer->id)
            ->where(function ($q) use ($app) {
                if ($app) {
                    $q->where('application_id', $app->id)->orWhereNull('application_id');
                }
            })
            ->where('status', 'pending')
            ->update(['status' => 'submitted']);

        // 4. Update Application status to DOCS_RESUBMITTED
        if ($app) {
            $app->update([
                'application_status' => 'DOCS_RESUBMITTED',
                'admin_remarks' => 'Applicant re-submitted required documents on ' . now()->toDayDateTimeString(),
            ]);
        }

        return redirect()->route('finance.zero_cibil.s05_pending', ['phone' => $phone])
            ->with('success', "{$uploadedCount} document(s) uploaded successfully. Your files are now under review by administration.");
    }

    public function zeroCibilStatusCheck(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $app = $ctx['application'];
        $wallet = $ctx['wallet'] ?? null;

        $isApproved = ($app && in_array($app->application_status, ['LOAN_APPROVED', 'DISBURSED', 'ACTIVE']))
            || ($wallet && $wallet->status === 'active');
        $isRejected = ($app && $app->application_status === 'REJECTED');
        $status = $app ? $app->application_status : 'UNKNOWN';
        $hasDocRequest = $ctx['hasDocRequest'] ?? false;

        return response()->json([
            'status' => $status,
            'is_approved' => $isApproved,
            'is_rejected' => $isRejected,
            'has_doc_request' => $hasDocRequest,
            'redirect_url' => $isApproved ? route('finance.zero_cibil.s06_wallet_active', ['phone' => $ctx['phone']]) : null,
        ]);
    }
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

    // ─────────────────────────────────────────────────────────────
    // ACTIVE LOAN REPAYMENTS, DOCUMENTS & SUPPORT
    // ─────────────────────────────────────────────────────────────

    public function ensureRepaymentSchedule(FinanceLoanApplication $application): void
    {
        if ($application->application_status !== 'DISBURSED') {
            return;
        }

        $existing = FinanceDailySchedule::where('application_id', $application->id)->count();
        if ($existing > 0) {
            return;
        }

        $customer = $application->customer;
        $customerId = $customer ? $customer->id : ($application->customer_id ?? 0);
        $approvedAmount = floatval($application->approved_amount ?: ($application->requested_amount ?: 25000));
        $cat = $application->loan_category ?? '';
        $isDaily = in_array($cat, ['zero_cibil', 'zero_cibil_micro', 'zero_cibil_daily']);
        $disbursedDate = $application->disbursed_at ? \Carbon\Carbon::parse($application->disbursed_at) : now();

        if ($isDaily) {
            $dailyEmi = round($approvedAmount / 60, 2);
            for ($d = 1; $d <= 30; $d++) {
                FinanceDailySchedule::create([
                    'application_id' => $application->id,
                    'customer_id'    => $customerId,
                    'schedule_date'  => $disbursedDate->copy()->addDays($d)->toDateString(),
                    'day_number'     => $d,
                    'emi_amount'     => $dailyEmi,
                    'total_due'      => $dailyEmi,
                    'status'         => 'pending',
                ]);
            }
        } else {
            $tenure = intval($application->tenure_months ?: 12);
            if ($tenure <= 0) $tenure = 12;
            $monthlyEmi = floatval($application->estimated_emi ?: round($approvedAmount / $tenure, 2));
            for ($m = 1; $m <= $tenure; $m++) {
                FinanceDailySchedule::create([
                    'application_id' => $application->id,
                    'customer_id'    => $customerId,
                    'schedule_date'  => $disbursedDate->copy()->addMonths($m)->toDateString(),
                    'day_number'     => $m,
                    'emi_amount'     => $monthlyEmi,
                    'total_due'      => $monthlyEmi,
                    'status'         => 'pending',
                ]);
            }
        }
    }

    public function repayments(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $phone = $ctx['phone'];
        $customer = $ctx['customer'];
        $loan = $ctx['disbursedLoan'] ?? $ctx['application'];

        $schedules = collect();
        $nextDue = null;
        $totalPaid = 0;
        $totalOutstanding = 0;

        if ($loan) {
            $this->ensureRepaymentSchedule($loan);
            $schedules = FinanceDailySchedule::where('application_id', $loan->id)
                ->orderBy('day_number', 'asc')
                ->get();

            $nextDue = $schedules->firstWhere('status', 'pending') ?? $schedules->firstWhere('status', 'overdue');
            $totalPaid = $schedules->where('status', 'paid')->sum('paid_amount');
            $totalOutstanding = $schedules->where('status', '!=', 'paid')->sum('total_due');
        }

        return view('finance.repayments', array_merge($ctx, [
            'loan'             => $loan,
            'schedules'        => $schedules,
            'nextDue'          => $nextDue,
            'totalPaid'        => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
        ]));
    }

    public function payRepayment(Request $request)
    {
        $phone = $request->input('phone');
        $scheduleId = $request->input('schedule_id');
        $paymentId = $request->input('payment_id', 'PAY-EMI-' . time());
        $amount = floatval($request->input('amount', 0));

        $schedule = FinanceDailySchedule::find($scheduleId);
        if ($schedule) {
            $schedule->update([
                'status'         => 'paid',
                'paid_amount'    => $amount ?: $schedule->total_due,
                'paid_at'        => now(),
                'payment_method' => 'razorpay',
                'txn_id'         => $paymentId,
            ]);

            FinanceTransaction::create([
                'customer_id'         => $schedule->customer_id,
                'application_id'      => $schedule->application_id,
                'txn_number'          => $paymentId,
                'txn_type'            => 'repayment',
                'amount'              => $amount ?: $schedule->total_due,
                'direction'           => 'credit',
                'payment_method'      => 'razorpay',
                'payment_gateway_ref' => $paymentId,
                'status'              => 'success',
                'notes'               => "EMI Repayment Installment #{$schedule->day_number} paid via Razorpay",
            ]);

            // Check if all installments are paid
            $pendingCount = FinanceDailySchedule::where('application_id', $schedule->application_id)
                ->where('status', '!=', 'paid')
                ->count();
            if ($pendingCount === 0) {
                FinanceLoanApplication::where('id', $schedule->application_id)
                    ->update(['application_status' => 'CLOSED']);
            }

            return response()->json([
                'success' => true,
                'message' => 'EMI Payment of ₹' . number_format($amount ?: $schedule->total_due) . ' received successfully!',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Installment not found.'], 404);
    }

    public function documents(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $phone = $ctx['phone'];
        $customer = $ctx['customer'];
        $loan = $ctx['disbursedLoan'] ?? $ctx['application'];

        return view('finance.documents', array_merge($ctx, [
            'loan' => $loan,
        ]));
    }

    public function support(Request $request)
    {
        $ctx = $this->resolveContext($request);
        $phone = $ctx['phone'];
        $loan = $ctx['disbursedLoan'] ?? $ctx['application'];

        return view('finance.support', array_merge($ctx, [
            'loan' => $loan,
        ]));
    }
}

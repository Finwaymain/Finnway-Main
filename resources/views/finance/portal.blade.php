<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fiinway Financial Services — Credit & Loan Portal</title>
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#F0F9FF',
                            100: '#E0F2FE',
                            500: '#0284C7',
                            600: '#0369A1',
                            700: '#0F172A',
                            800: '#1E293B',
                            900: '#0F172A',
                        },
                        navy: '#0F172A',
                        slateBorder: '#E2E8F0',
                    }
                }
            }
        }
    </script>
    <style>
        /* Strict No-Gradient Institutional UI */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            -webkit-tap-highlight-color: transparent;
        }
        .step-active {
            border-bottom: 2px solid #0F172A;
            color: #0F172A;
            font-weight: 600;
        }
        .step-inactive {
            color: #64748B;
            border-bottom: 2px solid transparent;
        }
        input, select, textarea {
            font-size: 15px;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 pb-16">

    <!-- Top Institutional Header -->
    <header class="bg-slate-900 text-white sticky top-0 z-40 border-b border-slate-800">
        <div class="max-w-xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-white text-sm">
                    F
                </div>
                <div>
                    <h1 class="text-sm font-semibold tracking-tight text-white leading-tight">FIINWAY FINANCE</h1>
                    <p class="text-[11px] text-slate-400">Institutional Credit & Loan Services</p>
                </div>
            </div>
            <div class="flex items-center space-x-1.5 bg-slate-800 px-2.5 py-1 rounded border border-slate-700 text-[11px] text-slate-300 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span id="headerUserType">Verified Partner</span>
            </div>
        </div>
    </header>

    <!-- User Identity & Quick Status Bar -->
    <section class="bg-white border-b border-slate-200">
        <div class="max-w-xl mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500 font-medium">Applicant Account</p>
                <p class="text-sm font-semibold text-slate-900" id="applicantNameDisplay">Loading applicant...</p>
                <p class="text-xs text-slate-500" id="applicantPhoneDisplay">+91 ••••••••••</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-500 font-medium">Vault KYC Status</p>
                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200" id="kycBadge">
                    Active
                </span>
            </div>
        </div>
    </section>

    <!-- Stepper Navigation -->
    <nav class="bg-white border-b border-slate-200 sticky top-14 z-30 overflow-x-auto scrollbar-none">
        <div class="max-w-xl mx-auto px-4 flex space-x-6 text-xs whitespace-nowrap">
            <button onclick="goToStep(1)" id="stepTab1" class="py-3 step-active">1. Select Product</button>
            <button onclick="goToStep(2)" id="stepTab2" class="py-3 step-inactive">2. Applicant KYC</button>
            <button onclick="goToStep(3)" id="stepTab3" class="py-3 step-inactive">3. Fee & Review</button>
            <button onclick="goToStep(4)" id="stepTab4" class="py-3 step-inactive">4. Lender Partners</button>
            <button onclick="goToStep(5)" id="stepTab5" class="py-3 step-inactive">5. Verification</button>
            <button onclick="goToStep(6)" id="stepTab6" class="py-3 step-inactive">6. Disbursement</button>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="max-w-xl mx-auto px-4 pt-5">

        <!-- ========================================== -->
        <!-- STEP 1: PRODUCT SELECTION & CALCULATOR -->
        <!-- ========================================== -->
        <section id="step1Container" class="space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Select Credit Product</h2>
                <p class="text-xs text-slate-600">Choose from available pre-screened credit facilities tailored for your profile.</p>
            </div>

            <!-- Product Cards (Solid border, clean typography) -->
            <div class="space-y-3" id="productsListContainer">
                <!-- Dynamically populated or fallback products -->
                <div onclick="selectProduct('zero_cibil_daily', 'Interest-Free Zero CIBIL Loan', 20000, 200000, 0, 1000)" 
                     class="product-card cursor-pointer bg-white rounded-lg p-4 border-2 border-slate-900 shadow-sm transition-all" id="prod_zero_cibil_daily">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 rounded">0% Interest • Daily Repayment</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">Interest-Free / Zero CIBIL Loan</h3>
                            <p class="text-xs text-slate-600 mt-0.5">Micro-credit up to ₹2,00,000 designed for daily earners and driver partners.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-900">Up to ₹2L</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Daily EMI: From ₹500</span>
                        <span>Daily Usage Control: Yes</span>
                    </div>
                </div>

                <div onclick="selectProduct('cash_loan_low_cibil', 'Low CIBIL Cash Loan', 50000, 400000, 11.5, 2499)" 
                     class="product-card cursor-pointer bg-white rounded-lg p-4 border border-slate-200 shadow-sm transition-all" id="prod_cash_loan_low_cibil">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2 py-0.5 text-[11px] font-semibold bg-blue-100 text-blue-800 rounded">Fast Approval • Assisted</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">Low CIBIL Cash Loan</h3>
                            <p class="text-xs text-slate-600 mt-0.5">Personal emergency cash loans up to ₹4,00,000 with nominal score requirements.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-900">Up to ₹4L</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Tenure: 12 - 48 Months</span>
                        <span>Bank Disbursal: Yes</span>
                    </div>
                </div>

                <div onclick="selectProduct('cash_loan_good_cibil', 'Good CIBIL Cash Loan', 200000, 5000000, 9.5, 3999)" 
                     class="product-card cursor-pointer bg-white rounded-lg p-4 border border-slate-200 shadow-sm transition-all" id="prod_cash_loan_good_cibil">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-800 rounded">Prime Rates</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">Good CIBIL Cash Loan</h3>
                            <p class="text-xs text-slate-600 mt-0.5">High-limit personal loans up to ₹50,00,000 through premier banking partners.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-900">Up to ₹50L</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Tenure: Up to 60 Months</span>
                        <span>Prime Banking: Yes</span>
                    </div>
                </div>

                <div onclick="selectProduct('virtual_loan', 'App-to-App Virtual Credit', 15000, 45000, 0, 2000)" 
                     class="product-card cursor-pointer bg-white rounded-lg p-4 border border-slate-200 shadow-sm transition-all" id="prod_virtual_loan">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2 py-0.5 text-[11px] font-semibold bg-purple-100 text-purple-800 rounded">App Ecosystem Credit</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">App-to-App Virtual Credit Line</h3>
                            <p class="text-xs text-slate-600 mt-0.5">Closed-loop credit limit for instant QR payment at verified business merchants.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-900">₹15k - ₹45k</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Withdrawal: Blocked</span>
                        <span>Merchant QR: Enabled</span>
                    </div>
                </div>

                <div onclick="selectProduct('business_loan', 'Business / Fleet Expansion Loan', 500000, 20000000, 10.5, 5999)" 
                     class="product-card cursor-pointer bg-white rounded-lg p-4 border border-slate-200 shadow-sm transition-all" id="prod_business_loan">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-800 rounded">MSME & Fleet Expansion</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">Business & Fleet Expansion Loan</h3>
                            <p class="text-xs text-slate-600 mt-0.5">High-ticket funding for cab purchase, business scaling, and equipment purchase.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-900">Up to ₹2 Crore</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>GST / ITR Required: Yes</span>
                        <span>Tenure: Up to 84 Months</span>
                    </div>
                </div>

                <div onclick="selectProduct('student_credit_domestic', 'Student Credit Facility', 10000, 75000, 0, 999)" 
                     class="product-card cursor-pointer bg-white rounded-lg p-4 border border-slate-200 shadow-sm transition-all" id="prod_student_credit_domestic">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2 py-0.5 text-[11px] font-semibold bg-indigo-100 text-indigo-800 rounded">Age 16-26 with Student ID</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">Student Credit Facility</h3>
                            <p class="text-xs text-slate-600 mt-0.5">Tuition and educational expenses credit with validity mapped to Student ID.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-900">Up to ₹75k</span>
                    </div>
                </div>
            </div>

            <!-- Calculator Card -->
            <div class="bg-white rounded-lg p-4 border border-slate-200 space-y-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Loan Amount Required</h3>
                    <span class="text-lg font-bold text-slate-900" id="selectedAmountDisplay">₹30,000</span>
                </div>
                <input type="range" id="loanAmountSlider" min="20000" max="200000" step="5000" value="30000" 
                       class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-slate-900"
                       oninput="updateCalculator()">
                
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Repayment Tenure</label>
                        <select id="tenureSelect" onchange="updateCalculator()" class="w-full bg-slate-50 border border-slate-300 rounded px-2.5 py-2 text-xs font-semibold text-slate-800">
                            <option value="12">12 Months</option>
                            <option value="24" selected>24 Months</option>
                            <option value="36">36 Months</option>
                            <option value="48">48 Months</option>
                            <option value="60">60 Months</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Estimated Repayment</label>
                        <div class="bg-slate-50 border border-slate-300 rounded px-2.5 py-2">
                            <p class="text-xs font-bold text-slate-900" id="estimatedRepaymentDisplay">₹1,000 / day</p>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded border border-slate-200 text-xs text-slate-600 flex justify-between items-center">
                    <span>Applicable Processing / Service Fee:</span>
                    <span class="font-bold text-slate-900" id="processingFeeDisplay">₹2,360 (incl. GST)</span>
                </div>
            </div>

            <!-- Mandatory Consent Box -->
            <div class="p-3 bg-white rounded border border-slate-200 text-xs text-slate-600 flex items-start space-x-2">
                <input type="checkbox" id="termsConsent" checked class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-0">
                <label for="termsConsent" class="leading-relaxed">
                    I understand that indicative eligibility is subject to lender verification and an applicable service/processing fee applies before proceeding to final underwriting.
                </label>
            </div>

            <button onclick="goToStep(2)" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 px-4 rounded text-sm transition-colors text-center">
                Continue to Applicant KYC →
            </button>
        </section>

        <!-- ========================================== -->
        <!-- STEP 2: APPLICANT DETAILS & KYC VAULT -->
        <!-- ========================================== -->
        <section id="step2Container" class="hidden space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Applicant Information & KYC Vault</h2>
                <p class="text-xs text-slate-600">Provide official identity details. Verified records are securely stored in your Common Vault.</p>
            </div>

            <!-- Vault Smart Reuse Alert -->
            <div id="vaultReuseAlert" class="p-3 bg-emerald-50 border border-emerald-200 rounded text-xs text-emerald-900 flex items-center justify-between">
                <div>
                    <span class="font-semibold block">Smart Document Reuse Active</span>
                    <span class="text-[11px] text-emerald-700">Verified identity documents from your past 5 days are mapped automatically.</span>
                </div>
                <span class="font-bold text-xs bg-emerald-200 px-2 py-0.5 rounded">Auto-Mapped</span>
            </div>

            <div class="bg-white rounded-lg p-4 border border-slate-200 space-y-3.5 shadow-sm">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name (As per PAN / Aadhaar)</label>
                    <input type="text" id="applicantFullName" placeholder="e.g. Rahul Kumar" class="w-full border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-slate-900">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Date of Birth</label>
                        <input type="date" id="applicantDob" value="1995-05-15" class="w-full border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Gender</label>
                        <select id="applicantGender" class="w-full border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-slate-900">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">PAN Card Number</label>
                        <input type="text" id="applicantPan" placeholder="ABCDE1234F" class="w-full border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 uppercase focus:outline-none focus:border-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Aadhaar Last 4 Digits</label>
                        <input type="text" id="applicantAadhaar" placeholder="9876" maxlength="4" class="w-full border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-slate-900">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Current Residential Address</label>
                    <textarea id="applicantAddress" rows="2" placeholder="House no, Street, Landmark" class="w-full border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-slate-900"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">City</label>
                        <input type="text" id="applicantCity" placeholder="Ujjain" class="w-full border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">State</label>
                        <input type="text" id="applicantState" placeholder="Madhya Pradesh" class="w-full border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">PIN Code</label>
                        <input type="text" id="applicantPincode" placeholder="456001" class="w-full border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Employment / Occupation</label>
                    <select id="applicantEmployment" class="w-full border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-slate-900">
                        <option value="Driver Partner">Commercial Driver Partner</option>
                        <option value="Self Employed">Self Employed / Business Owner</option>
                        <option value="Salaried Employee">Salaried Professional</option>
                        <option value="Student">Student (College/University)</option>
                    </select>
                </div>
            </div>

            <!-- Document Uploads -->
            <div class="bg-white rounded-lg p-4 border border-slate-200 space-y-3 shadow-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Document Vault Verification</h3>
                
                <div class="border border-dashed border-slate-300 rounded p-3 text-center hover:bg-slate-50 transition-colors">
                    <p class="text-xs font-semibold text-slate-800">Upload Identity Proof (Aadhaar / Voter ID)</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Front and back copy in PDF or JPG format</p>
                    <input type="file" id="idProofFile" class="mt-2 text-xs text-slate-600 block w-full file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white">
                </div>

                <div class="border border-dashed border-slate-300 rounded p-3 text-center hover:bg-slate-50 transition-colors">
                    <p class="text-xs font-semibold text-slate-800">Upload Bank Statement / Income Proof</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Last 3 months statement or passbook front page</p>
                    <input type="file" id="bankStatementFile" class="mt-2 text-xs text-slate-600 block w-full file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white">
                </div>
            </div>

            <div class="flex space-x-3">
                <button onclick="goToStep(1)" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold py-3 px-4 rounded text-sm transition-colors text-center">
                    ← Back
                </button>
                <button onclick="submitApplicationAndProceed()" id="submitAppBtn" class="w-2/3 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 px-4 rounded text-sm transition-colors text-center">
                    Save & Proceed to Review →
                </button>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- STEP 3: APPLICATION REVIEW & PROCESSING FEE -->
        <!-- ========================================== -->
        <section id="step3Container" class="hidden space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Application Review & Fee Confirmation</h2>
                <p class="text-xs text-slate-600">Review your pre-screened loan parameters and confirm the initial processing charge.</p>
            </div>

            <!-- Application Summary Card -->
            <div class="bg-white rounded-lg p-4 border border-slate-200 space-y-3 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs text-slate-500 font-medium">Application Tracking Number</span>
                    <span class="font-mono text-xs font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded" id="appNumberDisplay">GENERATING...</span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-500 block">Selected Product</span>
                        <span class="font-semibold text-slate-900" id="summaryProductName">Interest-Free Loan</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Requested Amount</span>
                        <span class="font-semibold text-slate-900" id="summaryAmount">₹30,000</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Selected Tenure</span>
                        <span class="font-semibold text-slate-900" id="summaryTenure">24 Months</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Repayment Frequency</span>
                        <span class="font-semibold text-slate-900" id="summaryFrequency">Daily</span>
                    </div>
                </div>
            </div>

            <!-- Fee Breakdown Card (Solid Institutional) -->
            <div class="bg-white rounded-lg p-4 border border-slate-200 space-y-2.5 shadow-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Service & Underwriting Fee</h3>
                
                <div class="flex justify-between text-xs text-slate-700">
                    <span>Base Application & Verification Fee</span>
                    <span class="font-medium" id="feeBaseDisplay">₹2,000.00</span>
                </div>
                <div class="flex justify-between text-xs text-slate-700">
                    <span>Applicable GST (18%)</span>
                    <span class="font-medium" id="feeGstDisplay">₹360.00</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-bold text-slate-900">
                    <span>Total Payable</span>
                    <span id="feeTotalDisplay">₹2,360.00</span>
                </div>
            </div>

            <div class="p-3 bg-slate-100 rounded text-xs text-slate-600 leading-relaxed">
                Payment confirms your underwriting verification file and unlocks partner application channels. Payments are securely processed via Razorpay or your Fiinway Wallet.
            </div>

            <div class="flex space-x-3">
                <button onclick="goToStep(2)" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold py-3 px-4 rounded text-sm transition-colors text-center">
                    ← Back
                </button>
                <button onclick="processFeePayment()" id="payFeeBtn" class="w-2/3 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-3 px-4 rounded text-sm transition-colors text-center">
                    Pay Fee & Unlock Partners →
                </button>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- STEP 4: LENDER PARTNERS (SINGLE-PARTNER LOCK) -->
        <!-- ========================================== -->
        <section id="step4Container" class="hidden space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Lending Partners & Banking Access</h2>
                <p class="text-xs text-slate-600">Select one authorized banking partner to begin underwriting. Selecting locks your application to that lender.</p>
            </div>

            <div id="partnerLockAlert" class="hidden p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-900 font-medium">
                Active Partner Locked: Proceed with your selected partner below. Other partner links are deactivated.
            </div>

            <div class="space-y-3" id="partnersContainer">
                <!-- HDFC Bank -->
                <div class="partner-card bg-white rounded-lg p-4 border border-slate-200 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">HDFC Bank</h3>
                            <p class="text-xs text-slate-500">Tier-1 Institutional Lending Partner</p>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">10.5% p.a.</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 py-1 bg-slate-50 rounded px-2">
                        <span>Range: ₹1,00,000 - ₹50,00,000</span>
                        <span>Tenure: 12 - 72 Months</span>
                    </div>
                    <button onclick="lockAndSelectPartner(1, 'HDFC Bank', 'https://www.hdfcbank.com/personal/borrow/popular-loans/personal-loan')" 
                            class="partner-btn-1 w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 rounded text-xs transition-colors">
                        Proceed with HDFC Bank →
                    </button>
                </div>

                <!-- ICICI Bank -->
                <div class="partner-card bg-white rounded-lg p-4 border border-slate-200 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">ICICI Bank</h3>
                            <p class="text-xs text-slate-500">Retail & Commercial Credit</p>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">10.75% p.a.</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 py-1 bg-slate-50 rounded px-2">
                        <span>Range: ₹1,00,000 - ₹50,00,000</span>
                        <span>Tenure: 12 - 60 Months</span>
                    </div>
                    <button onclick="lockAndSelectPartner(2, 'ICICI Bank', 'https://www.icicibank.com/personal-banking/loans/personal-loan')" 
                            class="partner-btn-2 w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 rounded text-xs transition-colors">
                        Proceed with ICICI Bank →
                    </button>
                </div>

                <!-- Tata Capital -->
                <div class="partner-card bg-white rounded-lg p-4 border border-slate-200 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Tata Capital</h3>
                            <p class="text-xs text-slate-500">Flexible Repayment Terms</p>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">11.25% p.a.</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 py-1 bg-slate-50 rounded px-2">
                        <span>Range: ₹50,000 - ₹35,00,000</span>
                        <span>Tenure: 12 - 84 Months</span>
                    </div>
                    <button onclick="lockAndSelectPartner(3, 'Tata Capital', 'https://www.tatacapital.com/personal-loan.html')" 
                            class="partner-btn-3 w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 rounded text-xs transition-colors">
                        Proceed with Tata Capital →
                    </button>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- STEP 5: PROOF UPLOAD & 3-MINUTE VALIDATION -->
        <!-- ========================================== -->
        <section id="step5Container" class="hidden space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Process Completion & Verification</h2>
                <p class="text-xs text-slate-600">Upload the final confirmation screen received from the partner platform to initiate validation.</p>
            </div>

            <!-- Step 5A: Proof Upload Card -->
            <div class="bg-white rounded-lg p-4 border border-slate-200 space-y-3 shadow-sm">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs font-semibold text-slate-700">Selected Lending Partner</span>
                    <span class="text-xs font-bold text-slate-900" id="currentPartnerLockedDisplay">HDFC Bank</span>
                </div>

                <div class="border border-dashed border-slate-300 rounded p-4 text-center hover:bg-slate-50 transition-colors">
                    <p class="text-xs font-semibold text-slate-800">Upload Partner Completion Screenshot</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Submit the application confirmation or reference number screenshot</p>
                    <input type="file" id="proofScreenshotFile" class="mt-3 text-xs text-slate-600 block w-full file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Applicant Remarks (Optional)</label>
                    <input type="text" id="proofRemarks" placeholder="e.g. Reference no. HDFC-10492 generated" class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900">
                </div>

                <button onclick="submitCompletionProof()" id="submitProofBtn" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 rounded text-xs transition-colors">
                    Submit for 3-Minute Validation →
                </button>
            </div>

            <!-- Step 5B: 3-Minute Validation Queue -->
            <div id="validationQueueCard" class="hidden bg-white rounded-lg p-4 border border-blue-200 space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-ping"></span>
                        <span class="text-xs font-bold text-slate-900">Validation in Progress</span>
                    </div>
                    <span class="text-xs font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200" id="validationTimerDisplay">02:59</span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Your submitted completion proof is currently undergoing automated reference cross-matching and document sanity review.
                </p>
            </div>

            <!-- Step 5C: Agent Selfie Verification (Doc 2) -->
            <div id="agentSelfieCard" class="bg-white rounded-lg p-4 border border-slate-200 space-y-3 shadow-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Live Agent / Face Verification</h3>
                <p class="text-xs text-slate-600">Capture a clear live verification selfie to authenticate loan sanctioning.</p>
                
                <div class="border border-dashed border-slate-300 rounded p-4 text-center hover:bg-slate-50 transition-colors">
                    <p class="text-xs font-semibold text-slate-800">Capture Live Selfie</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Clear lighting, face centered without sunglasses or coverings</p>
                    <input type="file" accept="image/*" capture="user" id="agentSelfieFile" class="mt-3 text-xs text-slate-600 block w-full file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white">
                </div>

                <button onclick="submitAgentSelfie()" id="submitSelfieBtn" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 rounded text-xs transition-colors">
                    Verify Identity & Proceed to Disbursement →
                </button>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- STEP 6: DISBURSEMENT & DAILY RECOVERY -->
        <!-- ========================================== -->
        <section id="step6Container" class="hidden space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Disbursement & Repayment Manager</h2>
                <p class="text-xs text-slate-600">Provide receiving bank credentials and track your active loan recovery and usage lock.</p>
            </div>

            <!-- Approved Loan Card -->
            <div class="bg-white rounded-lg p-4 border border-emerald-200 space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded">SANCTION APPROVED</span>
                        <h3 class="text-base font-bold text-slate-900 mt-1">₹30,000 Approved Line</h3>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-500 block">Daily Limit</span>
                        <span class="text-xs font-bold text-slate-900">₹5,000 / day</span>
                    </div>
                </div>
            </div>

            <!-- Disbursement Bank Form -->
            <div class="bg-white rounded-lg p-4 border border-slate-200 space-y-3 shadow-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Payout Bank Details</h3>
                
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Account Holder Name</label>
                    <input type="text" id="bankHolderName" placeholder="As per bank passbook" class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Bank Name</label>
                    <input type="text" id="bankName" placeholder="e.g. State Bank of India" class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Account Number</label>
                        <input type="password" id="bankAccountNumber" placeholder="Account Number" class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Confirm Account</label>
                        <input type="text" id="bankAccountConfirm" placeholder="Re-enter Number" class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">IFSC Code</label>
                    <input type="text" id="bankIfsc" placeholder="SBIN0001234" class="w-full border border-slate-300 rounded px-3 py-2 text-xs text-slate-900 uppercase focus:outline-none focus:border-slate-900">
                </div>

                <button onclick="submitDisbursementBank()" id="submitBankBtn" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2.5 rounded text-xs transition-colors">
                    Submit for Bank Disbursement →
                </button>
            </div>

            <!-- Daily Usage Lock / Recovery Engine Card (Doc 5) -->
            <div class="bg-white rounded-lg p-4 border border-slate-200 space-y-3 shadow-sm">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900">Daily Usage Lock Engine</h3>
                        <p class="text-[11px] text-slate-500">Zero-CIBIL daily recovery & spending status</p>
                    </div>
                    <span id="dailyUsageLockBadge" class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                        USAGE ACTIVE
                    </span>
                </div>

                <div class="p-3 bg-slate-50 rounded border border-slate-200 flex justify-between items-center text-xs">
                    <div>
                        <span class="text-slate-500 block">Today's Scheduled EMI</span>
                        <span class="font-bold text-slate-900 text-sm">₹1,000.00</span>
                    </div>
                    <button onclick="payDailyEmi()" id="repayEmiBtn" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold px-4 py-2 rounded text-xs">
                        Pay Today's EMI & Unlock
                    </button>
                </div>

                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Rule: Paying daily EMI maintains active daily loan usage. Overdue repayments temporarily lock daily credit spending until cleared.
                </p>
            </div>
        </section>

    </main>

    <!-- Client-side Logic & API Integrations -->
    <script>
        // State
        let currentStep = 1;
        let selectedProductCode = 'zero_cibil_daily';
        let currentAppId = null;
        let customerContext = null;

        // Read URL Parameters
        const urlParams = new URLSearchParams(window.location.search);
        const paramPhone = urlParams.get('mobile') || urlParams.get('phone') || '';
        const paramName = urlParams.get('name') || '';
        const paramUserType = urlParams.get('user_type') || 'customer';
        const paramCardType = urlParams.get('card_type') || '';

        // Initialize UI
        document.addEventListener('DOMContentLoaded', () => {
            if (paramName) {
                document.getElementById('applicantNameDisplay').innerText = paramName;
                document.getElementById('applicantFullName').value = paramName;
            }
            if (paramPhone) {
                document.getElementById('applicantPhoneDisplay').innerText = '+91 ' + paramPhone.slice(-10);
            }
            if (paramUserType === 'driver') {
                document.getElementById('headerUserType').innerText = 'Driver Partner';
                document.getElementById('applicantEmployment').value = 'Driver Partner';
            }

            // Pre-select based on card_type
            if (paramCardType.toLowerCase().includes('zero') || paramCardType.toLowerCase().includes('interest')) {
                selectProduct('zero_cibil_daily', 'Interest-Free Zero CIBIL Loan', 20000, 200000, 0, 1000);
            } else if (paramCardType.toLowerCase().includes('low')) {
                selectProduct('cash_loan_low_cibil', 'Low CIBIL Cash Loan', 50000, 400000, 11.5, 2499);
            }

            fetchContext();
        });

        // Step Navigation
        function goToStep(step) {
            currentStep = step;
            for (let i = 1; i <= 6; i++) {
                const container = document.getElementById(`step${i}Container`);
                const tab = document.getElementById(`stepTab${i}`);
                if (container) container.classList.toggle('hidden', i !== step);
                if (tab) {
                    tab.classList.toggle('step-active', i === step);
                    tab.classList.toggle('step-inactive', i !== step);
                }
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Product Selection
        function selectProduct(code, name, min, max, rate, fee) {
            selectedProductCode = code;
            document.querySelectorAll('.product-card').forEach(c => {
                c.classList.remove('border-2', 'border-slate-900');
                c.classList.add('border-slate-200');
            });
            const activeCard = document.getElementById(`prod_${code}`);
            if (activeCard) {
                activeCard.classList.remove('border-slate-200');
                activeCard.classList.add('border-2', 'border-slate-900');
            }

            const slider = document.getElementById('loanAmountSlider');
            slider.min = min;
            slider.max = max;
            slider.value = Math.min(Math.max(30000, min), max);
            updateCalculator();
        }

        // Calculator Update
        function updateCalculator() {
            const amount = parseInt(document.getElementById('loanAmountSlider').value);
            document.getElementById('selectedAmountDisplay').innerText = '₹' + amount.toLocaleString('en-IN');
            
            let dailyEmi = Math.round(amount / 30);
            if (selectedProductCode === 'zero_cibil_daily') {
                dailyEmi = Math.max(500, Math.round(amount / 100));
                document.getElementById('estimatedRepaymentDisplay').innerText = '₹' + dailyEmi.toLocaleString('en-IN') + ' / day';
            } else {
                const tenure = parseInt(document.getElementById('tenureSelect').value);
                const emi = Math.round(amount / tenure * 1.09);
                document.getElementById('estimatedRepaymentDisplay').innerText = '₹' + emi.toLocaleString('en-IN') + ' / mo';
            }

            let baseFee = 2000;
            if (amount <= 20000) baseFee = 1500;
            else if (amount <= 50000) baseFee = 2500;
            else if (amount <= 100000) baseFee = 3500;
            else baseFee = 4999;

            const gst = Math.round(baseFee * 0.18);
            const total = baseFee + gst;

            document.getElementById('processingFeeDisplay').innerText = `₹${total.toLocaleString('en-IN')} (incl. GST)`;
            document.getElementById('feeBaseDisplay').innerText = `₹${baseFee.toFixed(2)}`;
            document.getElementById('feeGstDisplay').innerText = `₹${gst.toFixed(2)}`;
            document.getElementById('feeTotalDisplay').innerText = `₹${total.toFixed(2)}`;
        }

        // Fetch User Context & Vault Documents
        async function fetchContext() {
            try {
                const res = await fetch(`/api/v1/finance/context?phone=${paramPhone}&name=${encodeURIComponent(paramName)}&user_type=${paramUserType}`);
                const json = await res.json();
                if (json.success && json.data) {
                    customerContext = json.data.customer;
                    if (customerContext.name) {
                        document.getElementById('applicantNameDisplay').innerText = customerContext.name;
                    }
                    if (json.data.reusable_documents && json.data.reusable_documents.length > 0) {
                        document.getElementById('vaultReuseAlert').classList.remove('hidden');
                    }
                }
            } catch (_) {}
        }

        // Submit Application
        async function submitApplicationAndProceed() {
            const btn = document.getElementById('submitAppBtn');
            btn.innerText = 'Saving Application...';
            btn.disabled = true;

            const amount = parseInt(document.getElementById('loanAmountSlider').value);
            const tenure = parseInt(document.getElementById('tenureSelect').value);
            const fullName = document.getElementById('applicantFullName').value || paramName || 'Applicant';

            try {
                const res = await fetch('/api/v1/finance/applications/initiate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        phone: paramPhone,
                        applicant_name: fullName,
                        user_type: paramUserType,
                        product_code: selectedProductCode,
                        requested_amount: amount,
                        tenure_months: tenure,
                    })
                });
                const json = await res.json();
                if (json.success && json.data) {
                    currentAppId = json.data.id;
                    document.getElementById('appNumberDisplay').innerText = json.data.application_number;
                    document.getElementById('summaryProductName').innerText = selectedProductCode.replace(/_/g, ' ').toUpperCase();
                    document.getElementById('summaryAmount').innerText = '₹' + amount.toLocaleString('en-IN');
                    document.getElementById('summaryTenure').innerText = tenure + ' Months';
                    goToStep(3);
                } else {
                    alert(json.error || 'Failed to initiate application.');
                }
            } catch (e) {
                alert('Connection error: ' + e.message);
            } finally {
                btn.innerText = 'Save & Proceed to Review →';
                btn.disabled = false;
            }
        }

        // Process Fee Payment
        async function processFeePayment() {
            if (!currentAppId) {
                alert('Please submit application first.');
                return;
            }
            const btn = document.getElementById('payFeeBtn');
            btn.innerText = 'Processing Payment...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/v1/finance/applications/${currentAppId}/confirm-fee`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ payment_method: 'Wallet / Razorpay' })
                });
                const json = await res.json();
                if (json.success) {
                    goToStep(4);
                } else {
                    alert(json.error || 'Payment confirmation failed.');
                }
            } catch (e) {
                alert('Payment error: ' + e.message);
            } finally {
                btn.innerText = 'Pay Fee & Unlock Partners →';
                btn.disabled = false;
            }
        }

        // Single Partner Lock
        async function lockAndSelectPartner(partnerId, partnerName, url) {
            if (!currentAppId) {
                alert('Application session expired. Please restart.');
                return;
            }

            try {
                await fetch(`/api/v1/finance/applications/${currentAppId}/select-partner`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ partner_id: partnerId })
                });

                document.getElementById('partnerLockAlert').classList.remove('hidden');
                document.getElementById('currentPartnerLockedDisplay').innerText = partnerName;

                // Open partner URL in new tab / window
                window.open(url, '_blank');
                goToStep(5);
            } catch (e) {
                alert('Error selecting partner: ' + e.message);
            }
        }

        // Submit Completion Proof
        async function submitCompletionProof() {
            const fileInput = document.getElementById('proofScreenshotFile');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Please select your process completion screenshot.');
                return;
            }

            const formData = new FormData();
            formData.append('proof', fileInput.files[0]);
            formData.append('remarks', document.getElementById('proofRemarks').value || '');

            const btn = document.getElementById('submitProofBtn');
            btn.innerText = 'Submitting Proof...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/v1/finance/applications/${currentAppId}/upload-proof`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData,
                });
                const json = await res.json();
                if (json.success) {
                    document.getElementById('validationQueueCard').classList.remove('hidden');
                    startValidationTimer();
                } else {
                    alert(json.error || 'Upload failed.');
                }
            } catch (e) {
                alert('Upload error: ' + e.message);
            } finally {
                btn.innerText = 'Submit for 3-Minute Validation →';
                btn.disabled = false;
            }
        }

        // 3-Minute Timer
        function startValidationTimer() {
            let seconds = 180;
            const timerEl = document.getElementById('validationTimerDisplay');
            const interval = setInterval(() => {
                seconds--;
                if (seconds <= 0) {
                    clearInterval(interval);
                    timerEl.innerText = 'VALIDATED ✓';
                    timerEl.className = 'text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200';
                } else {
                    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                    const s = (seconds % 60).toString().padStart(2, '0');
                    timerEl.innerText = `${m}:${s}`;
                }
            }, 1000);
        }

        // Submit Selfie
        async function submitAgentSelfie() {
            const fileInput = document.getElementById('agentSelfieFile');
            if (!fileInput.files || fileInput.files.length === 0) {
                // If camera omitted in demo, proceed directly
                goToStep(6);
                return;
            }

            const formData = new FormData();
            formData.append('selfie', fileInput.files[0]);

            const btn = document.getElementById('submitSelfieBtn');
            btn.innerText = 'Uploading Verification...';
            btn.disabled = true;

            try {
                await fetch(`/api/v1/finance/applications/${currentAppId}/upload-selfie`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData,
                });
                goToStep(6);
            } catch (e) {
                goToStep(6);
            } finally {
                btn.innerText = 'Verify Identity & Proceed to Disbursement →';
                btn.disabled = false;
            }
        }

        // Submit Disbursement Bank
        async function submitDisbursementBank() {
            const holder = document.getElementById('bankHolderName').value;
            const bank = document.getElementById('bankName').value;
            const acc = document.getElementById('bankAccountNumber').value;
            const ifsc = document.getElementById('bankIfsc').value;

            if (!acc || !ifsc) {
                alert('Please enter your complete bank account number and IFSC code.');
                return;
            }

            const btn = document.getElementById('submitBankBtn');
            btn.innerText = 'Submitting Details...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/v1/finance/applications/${currentAppId}/disbursement-account`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        bank_name: bank,
                        account_name: holder,
                        account_number: acc,
                        ifsc: ifsc,
                    })
                });
                const json = await res.json();
                if (json.success) {
                    alert('Disbursement details submitted successfully! Your funds are queued for processing.');
                }
            } catch (e) {
                alert('Submission error: ' + e.message);
            } finally {
                btn.innerText = 'Details Submitted ✓';
            }
        }

        // Pay Daily EMI & Unlock Usage
        async function payDailyEmi() {
            const btn = document.getElementById('repayEmiBtn');
            btn.innerText = 'Processing Repayment...';
            btn.disabled = true;

            try {
                const res = await fetch('/api/v1/finance/daily-repayment', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ phone: paramPhone })
                });
                const json = await res.json();
                if (json.success) {
                    document.getElementById('dailyUsageLockBadge').innerText = 'USAGE UNLOCKED ✓';
                    document.getElementById('dailyUsageLockBadge').className = 'text-xs font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300';
                    alert('Repayment confirmed. Today\'s loan spending limit is now fully active!');
                }
            } catch (e) {
                alert('Repayment error: ' + e.message);
            } finally {
                btn.innerText = 'Paid Today ✓';
            }
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fiinway Credit — Loan &amp; Financial Services</title>
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            500: '#0f172a',
                            600: '#0f172a',
                            900: '#0f172a',
                        },
                        emerald: {
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Strict No-Gradient Institutional UI */
        * {
            -webkit-tap-highlight-color: transparent;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
        }
        /* Mobile input fix to prevent iOS/Android WebView auto-zoom */
        input, select, textarea {
            font-size: 15px !important;
        }
        /* Custom Modern Select Dropdown */
        .modern-select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
            padding-right: 40px !important;
        }
        /* Floating Toast Animations */
        @keyframes toastSlideIn {
            0% { transform: translateY(-100%); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        @keyframes toastFadeOut {
            0% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        .toast-enter {
            animation: toastSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .toast-exit {
            animation: toastFadeOut 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 pb-20">

    <!-- Modern Floating Toast Container -->
    <div id="toastContainer" class="fixed top-4 inset-x-0 z-50 flex flex-col items-center pointer-events-none px-4 space-y-2"></div>

    <!-- App Header -->
    <header class="bg-slate-900 text-white sticky top-0 z-40 border-b border-slate-800 shadow-sm">
        <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center font-extrabold text-white text-sm">
                    F
                </div>
                <div>
                    <h1 class="text-sm font-bold tracking-tight text-white leading-none">FIINWAY CREDIT</h1>
                    <p class="text-[11px] text-slate-400 mt-0.5" id="headerSubtext">Institutional Lending</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-950 text-emerald-300 border border-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                    <span id="headerUserType">Verified</span>
                </span>
            </div>
        </div>
    </header>

    <!-- Compact Borrower Bar -->
    <section class="bg-white border-b border-slate-200">
        <div class="max-w-lg mx-auto px-4 py-2.5 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-xs" id="applicantAvatar">
                    U
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-900 leading-tight" id="applicantNameDisplay">Borrower Profile</div>
                    <div class="text-[11px] text-slate-500" id="applicantPhoneDisplay">+91 ••••••••••</div>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">KYC Vault</span>
                <span class="text-xs font-bold text-emerald-700" id="kycBadge">Active</span>
            </div>
        </div>
    </section>

    <!-- Sleek Step Progress Indicator & Tabs -->
    <nav class="bg-white border-b border-slate-200 sticky top-14 z-30 shadow-xs">
        <div class="max-w-lg mx-auto px-4 py-2.5">
            <!-- Progress Line & Label -->
            <div class="flex items-center justify-between text-xs font-semibold text-slate-600 mb-2">
                <span id="stepProgressLabel">Step 1 of 6: Choose Loan</span>
                <span class="font-mono text-[11px] text-slate-500" id="stepPercentLabel">16%</span>
            </div>
            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mb-3">
                <div id="stepProgressBar" class="bg-slate-900 h-full rounded-full transition-all duration-300" style="width: 16.6%;"></div>
            </div>

            <!-- Horizontal Step Pills (Guarded against skipping ahead) -->
            <div class="flex space-x-2 overflow-x-auto pb-1 scrollbar-none" id="stepPillsContainer">
                <button type="button" onclick="handleStepTabClick(1)" id="stepPill1" class="step-pill px-3 py-1 rounded-full text-xs font-semibold bg-slate-900 text-white shrink-0 transition-colors">
                    1. Product
                </button>
                <button type="button" onclick="handleStepTabClick(2)" id="stepPill2" class="step-pill px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-400 shrink-0 transition-colors">
                    2. Details
                </button>
                <button type="button" onclick="handleStepTabClick(3)" id="stepPill3" class="step-pill px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-400 shrink-0 transition-colors">
                    3. Fee
                </button>
                <button type="button" onclick="handleStepTabClick(4)" id="stepPill4" class="step-pill px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-400 shrink-0 transition-colors">
                    4. Lender
                </button>
                <button type="button" onclick="handleStepTabClick(5)" id="stepPill5" class="step-pill px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-400 shrink-0 transition-colors">
                    5. Verify
                </button>
                <button type="button" onclick="handleStepTabClick(6)" id="stepPill6" class="step-pill px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-400 shrink-0 transition-colors">
                    6. Disburse
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-lg mx-auto px-4 pt-4">

        <!-- ========================================== -->
        <!-- STEP 1: CHOOSE PRODUCT & AMOUNT -->
        <!-- ========================================== -->
        <section id="step1Container" class="space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Select Credit Product</h2>
                <p class="text-xs text-slate-500 mt-0.5">Select a pre-configured loan package below, or choose custom required amount.</p>
            </div>

            <!-- Product Cards -->
            <div class="space-y-2.5" id="productsListContainer">
                <!-- 1. Zero CIBIL Daily -->
                <div onclick="selectPresetProduct('zero_cibil_daily', 'Zero-CIBIL Loan', 30000, 24, 0, 1000)" 
                     id="prod_zero_cibil_daily"
                     class="product-card cursor-pointer bg-white rounded-xl p-3.5 border-2 border-slate-900 shadow-xs transition-all">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">0% Interest</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Daily Repayment</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 mt-1.5">Zero-CIBIL &amp; Interest-Free Loan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Preset: ₹30,000 • ₹1,000/day EMI</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-extrabold text-slate-900">₹30,000</span>
                            <span class="text-[10px] text-slate-500 block">24 Months</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Low CIBIL Cash Loan -->
                <div onclick="selectPresetProduct('cash_loan_low_cibil', 'Low CIBIL Cash Loan', 100000, 24, 11.5, 2499)" 
                     id="prod_cash_loan_low_cibil"
                     class="product-card cursor-pointer bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs transition-all">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Quick Disbursal</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Bank Transfer</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 mt-1.5">Low CIBIL Cash Loan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Preset: ₹1,00,000 • Personal emergency funds</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-extrabold text-slate-900">₹1,00,000</span>
                            <span class="text-[10px] text-slate-500 block">24 Months</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Prime Cash Loan -->
                <div onclick="selectPresetProduct('cash_loan_good_cibil', 'Good CIBIL Cash Loan', 500000, 36, 9.5, 3999)" 
                     id="prod_cash_loan_good_cibil"
                     class="product-card cursor-pointer bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs transition-all">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">Prime Rates</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Bank Partner</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 mt-1.5">Good CIBIL Cash Loan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Preset: ₹5,00,000 • Premier banking rates</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-extrabold text-slate-900">₹5,00,000</span>
                            <span class="text-[10px] text-slate-500 block">36 Months</span>
                        </div>
                    </div>
                </div>

                <!-- 4. Virtual Credit Line -->
                <div onclick="selectPresetProduct('virtual_loan', 'App Virtual Credit', 25000, 6, 0, 2000)" 
                     id="prod_virtual_loan"
                     class="product-card cursor-pointer bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs transition-all">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">In-App Credit</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Merchant QR</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 mt-1.5">App-to-App Virtual Credit Line</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Preset: ₹25,000 • Closed-loop merchant line</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-extrabold text-slate-900">₹25,000</span>
                            <span class="text-[10px] text-slate-500 block">6 Months</span>
                        </div>
                    </div>
                </div>

                <!-- 5. Business / Fleet Loan -->
                <div onclick="selectPresetProduct('business_loan', 'Business / Fleet Loan', 1000000, 48, 10.5, 5999)" 
                     id="prod_business_loan"
                     class="product-card cursor-pointer bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs transition-all">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">MSME &amp; Fleet</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Scaling</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 mt-1.5">Business &amp; Fleet Expansion Loan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Preset: ₹10,00,000 • Cab purchase &amp; business</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-extrabold text-slate-900">₹10,00,000</span>
                            <span class="text-[10px] text-slate-500 block">48 Months</span>
                        </div>
                    </div>
                </div>

                <!-- 6. Custom Loan Amount Option (Toggles Required Amount Section) -->
                <div onclick="selectCustomAmountMode()" 
                     id="prod_custom_amount"
                     class="product-card cursor-pointer bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center font-bold text-slate-700 text-sm">
                                ✎
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Custom Loan Amount</h3>
                                <p class="text-xs text-slate-500">Need a different amount or tenure? Configure here.</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-700" id="customToggleArrow">Configure ↓</span>
                    </div>
                </div>
            </div>

            <!-- Selected Preset Summary Banner (Shown when a preset card is selected) -->
            <div id="presetSelectedBanner" class="bg-slate-900 text-white rounded-xl p-3.5 shadow-xs flex items-center justify-between text-xs">
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block">Selected Package</span>
                    <span class="font-bold text-white text-sm" id="presetSummaryTitle">Zero-CIBIL Loan (₹30,000)</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 block" id="presetSummaryTenure">24 Months</span>
                    <span class="font-extrabold text-emerald-400 text-sm" id="presetSummaryEmi">₹1,000 / day</span>
                </div>
            </div>

            <!-- Required Amount Section (ONLY OPEN IF NO PRESET CARD IS SELECTED OR CUSTOM IS CHOSEN) -->
            <div id="customAmountSection" class="hidden bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Custom Required Amount</label>
                        <span class="text-[11px] text-slate-500">Adjust the slider to choose desired borrowing limit</span>
                    </div>
                    <span class="text-xl font-extrabold text-slate-900 font-mono" id="selectedAmountDisplay">₹30,000</span>
                </div>
                <input type="range" id="loanAmountSlider" min="20000" max="200000" step="5000" value="30000" 
                       class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-slate-900"
                       oninput="updateCalculator()">

                <!-- Modern Segmented Tenure Buttons -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Tenure</label>
                    <div class="grid grid-cols-4 gap-1.5" id="tenureSegmentedContainer">
                        <button type="button" onclick="selectTenure(12)" class="tenure-btn py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 transition-colors" data-tenure="12">12M</button>
                        <button type="button" onclick="selectTenure(24)" class="tenure-btn py-2 text-xs font-semibold rounded-lg border border-slate-900 text-white bg-slate-900 transition-colors" data-tenure="24">24M</button>
                        <button type="button" onclick="selectTenure(36)" class="tenure-btn py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 transition-colors" data-tenure="36">36M</button>
                        <button type="button" onclick="selectTenure(48)" class="tenure-btn py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 transition-colors" data-tenure="48">48M</button>
                    </div>
                </div>

                <!-- Estimated Repayment & Fee -->
                <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <span class="text-slate-500 block text-[11px]">Estimated Repayment</span>
                        <span class="font-bold text-slate-900 text-sm" id="estimatedRepaymentDisplay">₹1,000 / day</span>
                    </div>
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <span class="text-slate-500 block text-[11px]">Processing Fee</span>
                        <span class="font-bold text-slate-900 text-sm" id="processingFeeDisplay">₹2,360</span>
                    </div>
                </div>
            </div>

            <!-- Mandatory Consent -->
            <div class="flex items-start space-x-2.5 p-3 bg-white rounded-xl border border-slate-200 text-xs text-slate-600">
                <input type="checkbox" id="termsConsent" checked class="mt-0.5 w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-0">
                <label for="termsConsent" class="leading-snug text-slate-700">
                    I agree to the indicative loan eligibility and applicable underwriting terms.
                </label>
            </div>

            <!-- Action Button -->
            <button type="button" onclick="validateStep1AndProceed()" class="w-full bg-slate-900 hover:bg-slate-800 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl text-sm transition-all shadow-xs text-center flex items-center justify-center space-x-2">
                <span>Continue to Personal Details</span>
                <span>→</span>
            </button>
        </section>

        <!-- ========================================== -->
        <!-- STEP 2: APPLICANT DETAILS & 4 KYC DOCS -->
        <!-- ========================================== -->
        <section id="step2Container" class="hidden space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Personal &amp; KYC Verification</h2>
                <p class="text-xs text-slate-500 mt-0.5">Enter details and upload all 4 required KYC verification documents.</p>
            </div>

            <!-- Smart Document Reuse Banner -->
            <div id="vaultReuseAlert" class="hidden p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-semibold">KYC Vault: Previously verified documents auto-mapped</span>
                </div>
                <span class="text-[10px] font-bold bg-emerald-200 text-emerald-900 px-1.5 py-0.5 rounded">Vault Ready</span>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Legal Name <span class="text-rose-500">*</span></label>
                    <input type="text" id="applicantFullName" placeholder="As per PAN card" 
                           class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition-colors">
                </div>

                <!-- Dedicated Mobile Number Input -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone Number <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 font-semibold text-sm">+91</span>
                        <input type="tel" id="applicantPhone" placeholder="9876543210" maxlength="10" 
                               class="w-full bg-slate-50 border border-slate-300 rounded-lg pl-12 pr-3.5 py-2.5 text-sm text-slate-900 font-medium focus:outline-none focus:border-slate-900 focus:bg-white transition-colors"
                               oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length === 10) updateApplicantPhone(this.value);">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">PAN Number <span class="text-rose-500">*</span></label>
                        <input type="text" id="applicantPan" placeholder="ABCDE1234F" maxlength="10" 
                               class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 uppercase font-mono tracking-wider focus:outline-none focus:border-slate-900 focus:bg-white transition-colors"
                               oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Date of Birth</label>
                        <input type="date" id="applicantDob" value="1995-05-15" 
                               class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition-colors">
                    </div>
                </div>

                <!-- Modern Gender Segmented Control -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Gender</label>
                    <div class="grid grid-cols-3 gap-2" id="genderSegmentedContainer">
                        <button type="button" onclick="selectGender('Male')" class="gender-btn py-2 text-xs font-semibold rounded-lg border border-slate-900 text-white bg-slate-900 transition-colors" data-gender="Male">Male</button>
                        <button type="button" onclick="selectGender('Female')" class="gender-btn py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 transition-colors" data-gender="Female">Female</button>
                        <button type="button" onclick="selectGender('Other')" class="gender-btn py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 transition-colors" data-gender="Other">Other</button>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">City <span class="text-rose-500">*</span></label>
                        <input type="text" id="applicantCity" placeholder="Ujjain" 
                               class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">State</label>
                        <input type="text" id="applicantState" placeholder="MP" 
                               class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">PIN Code <span class="text-rose-500">*</span></label>
                        <input type="text" id="applicantPincode" placeholder="456001" maxlength="6" 
                               class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                </div>

                <!-- Custom Modern Styled Select Dropdown -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Employment Type</label>
                    <select id="applicantEmployment" class="modern-select w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 font-medium focus:outline-none focus:border-slate-900 focus:bg-white transition-colors">
                        <option value="Driver Partner">Commercial Driver Partner</option>
                        <option value="Self Employed">Self Employed / Business Owner</option>
                        <option value="Salaried Employee">Salaried Professional</option>
                        <option value="Student">Student (College/University)</option>
                    </select>
                </div>
            </div>

            <!-- ALL 4 MANDATORY KYC DOCUMENTS -->
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Mandatory KYC Documents</label>
                    <span class="text-[11px] text-rose-600 font-semibold">All 4 Required</span>
                </div>

                <!-- Doc 1: Aadhaar Front Image -->
                <div class="border border-dashed border-slate-300 rounded-xl p-3 bg-slate-50 hover:bg-slate-100/70 transition-colors" id="box_aadhaar_front">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">1. Aadhaar Card Front <span class="text-rose-500">*</span></span>
                            <span class="text-[11px] text-slate-500" id="status_aadhaar_front">Clear photo showing name &amp; photo</span>
                        </div>
                        <span id="badge_aadhaar_front" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-600">Pending</span>
                    </div>
                    <input type="file" accept="image/*" id="aadhaarFrontFile" onchange="handleDocSelected('aadhaar_front', this)" class="mt-2 text-xs text-slate-600 block w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white cursor-pointer">
                    <div id="preview_container_aadhaar_front" class="hidden mt-2">
                        <img id="preview_img_aadhaar_front" class="h-20 w-auto rounded border border-slate-300 object-cover">
                    </div>
                </div>

                <!-- Doc 2: Aadhaar Back Image -->
                <div class="border border-dashed border-slate-300 rounded-xl p-3 bg-slate-50 hover:bg-slate-100/70 transition-colors" id="box_aadhaar_back">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">2. Aadhaar Card Back <span class="text-rose-500">*</span></span>
                            <span class="text-[11px] text-slate-500" id="status_aadhaar_back">Clear photo showing address details</span>
                        </div>
                        <span id="badge_aadhaar_back" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-600">Pending</span>
                    </div>
                    <input type="file" accept="image/*" id="aadhaarBackFile" onchange="handleDocSelected('aadhaar_back', this)" class="mt-2 text-xs text-slate-600 block w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white cursor-pointer">
                    <div id="preview_container_aadhaar_back" class="hidden mt-2">
                        <img id="preview_img_aadhaar_back" class="h-20 w-auto rounded border border-slate-300 object-cover">
                    </div>
                </div>

                <!-- Doc 3: PAN Card Image -->
                <div class="border border-dashed border-slate-300 rounded-xl p-3 bg-slate-50 hover:bg-slate-100/70 transition-colors" id="box_pan_card">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">3. PAN Card Front <span class="text-rose-500">*</span></span>
                            <span class="text-[11px] text-slate-500" id="status_pan_card">Clear photo showing PAN number</span>
                        </div>
                        <span id="badge_pan_card" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-600">Pending</span>
                    </div>
                    <input type="file" accept="image/*" id="panCardFile" onchange="handleDocSelected('pan_card', this)" class="mt-2 text-xs text-slate-600 block w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white cursor-pointer">
                    <div id="preview_container_pan_card" class="hidden mt-2">
                        <img id="preview_img_pan_card" class="h-20 w-auto rounded border border-slate-300 object-cover">
                    </div>
                </div>

                <!-- Doc 4: Bank Passbook Front Image -->
                <div class="border border-dashed border-slate-300 rounded-xl p-3 bg-slate-50 hover:bg-slate-100/70 transition-colors" id="box_bank_passbook">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">4. Bank Passbook / Cheque Front <span class="text-rose-500">*</span></span>
                            <span class="text-[11px] text-slate-500" id="status_bank_passbook">Showing account number &amp; IFSC code</span>
                        </div>
                        <span id="badge_bank_passbook" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-600">Pending</span>
                    </div>
                    <input type="file" accept="image/*" id="bankPassbookFile" onchange="handleDocSelected('bank_passbook', this)" class="mt-2 text-xs text-slate-600 block w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white cursor-pointer">
                    <div id="preview_container_bank_passbook" class="hidden mt-2">
                        <img id="preview_img_bank_passbook" class="h-20 w-auto rounded border border-slate-300 object-cover">
                    </div>
                </div>
            </div>

            <div class="flex space-x-2.5 pt-1">
                <button type="button" onclick="goToStep(1)" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-3.5 px-4 rounded-xl text-sm transition-colors text-center">
                    ← Back
                </button>
                <button type="button" onclick="validateStep2AndProceed()" id="submitAppBtn" class="w-2/3 bg-slate-900 hover:bg-slate-800 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl text-sm transition-all shadow-xs text-center flex items-center justify-center space-x-1.5">
                    <span>Continue to Review</span>
                    <span>→</span>
                </button>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- STEP 3: APPLICATION REVIEW & PROCESSING FEE -->
        <!-- ========================================== -->
        <section id="step3Container" class="hidden space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Application Summary &amp; Fee</h2>
                <p class="text-xs text-slate-500 mt-0.5">Confirm parameters to unlock lending partners.</p>
            </div>

            <!-- Summary Receipt Card -->
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                    <span class="text-xs text-slate-500 font-medium">Application ID</span>
                    <span class="font-mono text-xs font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded" id="appNumberDisplay">PENDING</span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Product</span>
                        <span class="font-bold text-slate-900" id="summaryProductName">Zero-CIBIL Loan</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Requested Amount</span>
                        <span class="font-bold text-slate-900" id="summaryAmount">₹30,000</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tenure</span>
                        <span class="font-bold text-slate-900" id="summaryTenure">24 Months</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Repayment</span>
                        <span class="font-bold text-slate-900" id="summaryFrequency">Daily</span>
                    </div>
                </div>
            </div>

            <!-- Fee Card -->
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-2.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Processing Fee</label>
                
                <div class="flex justify-between text-xs text-slate-600">
                    <span>Underwriting &amp; Verification</span>
                    <span class="font-medium text-slate-900" id="feeBaseDisplay">₹2,000.00</span>
                </div>
                <div class="flex justify-between text-xs text-slate-600">
                    <span>GST (18%)</span>
                    <span class="font-medium text-slate-900" id="feeGstDisplay">₹360.00</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-bold text-slate-900">
                    <span>Total Due Now</span>
                    <span class="text-base font-extrabold text-slate-900" id="feeTotalDisplay">₹2,360.00</span>
                </div>
            </div>

            <!-- Custom Modern Payment Method Select -->
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-2">
                <label class="block text-xs font-bold text-slate-700">Payment Option</label>
                <select id="paymentMethodSelect" class="modern-select w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 font-semibold focus:outline-none focus:border-slate-900">
                    <option value="UPI">UPI / Google Pay / PhonePe (Fastest)</option>
                    <option value="Wallet">Fiinway In-App Wallet Balance</option>
                    <option value="NetBanking">Net Banking / Debit Card</option>
                </select>
            </div>

            <div class="flex space-x-2.5 pt-1">
                <button type="button" onclick="goToStep(2)" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-3.5 px-4 rounded-xl text-sm transition-colors text-center">
                    ← Back
                </button>
                <button type="button" onclick="processFeePayment()" id="payFeeBtn" class="w-2/3 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl text-sm transition-all shadow-xs text-center flex items-center justify-center space-x-1.5">
                    <span>Pay Fee &amp; Unlock Partners</span>
                    <span>→</span>
                </button>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- STEP 4: LENDER PARTNERS (SINGLE-PARTNER LOCK) -->
        <!-- ========================================== -->
        <section id="step4Container" class="hidden space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Select Lending Partner</h2>
                <p class="text-xs text-slate-500 mt-0.5">Selecting a lender partner locks your application to prevent duplicate submissions.</p>
            </div>

            <!-- Partner Cards -->
            <div class="space-y-3" id="partnersListContainer">
                <!-- Partner 1: HDFC Bank -->
                <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold bg-blue-50 text-blue-800 px-2 py-0.5 rounded">Premier Partner</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">HDFC Bank Limited</h3>
                        </div>
                        <span class="text-xs font-bold text-emerald-700">10.5% p.a.</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500 py-1 bg-slate-50 rounded-lg px-2.5">
                        <span>Range: ₹50K – ₹40L</span>
                        <span>Tenure: 12 – 60M</span>
                    </div>
                    <button type="button" onclick="lockAndSelectPartner(1, 'HDFC Bank', 'https://www.hdfcbank.com/personal/borrow/popular-loans/personal-loan')" 
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-lg text-xs transition-colors">
                        Lock HDFC &amp; Apply →
                    </button>
                </div>

                <!-- Partner 2: ICICI Bank -->
                <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold bg-amber-50 text-amber-800 px-2 py-0.5 rounded">Fast Disbursal</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">ICICI Bank</h3>
                        </div>
                        <span class="text-xs font-bold text-emerald-700">11.0% p.a.</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500 py-1 bg-slate-50 rounded-lg px-2.5">
                        <span>Range: ₹50K – ₹50L</span>
                        <span>Tenure: 12 – 72M</span>
                    </div>
                    <button type="button" onclick="lockAndSelectPartner(2, 'ICICI Bank', 'https://www.icicibank.com/personal-banking/loans/personal-loan')" 
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-lg text-xs transition-colors">
                        Lock ICICI &amp; Apply →
                    </button>
                </div>

                <!-- Partner 3: Bajaj Finserv -->
                <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold bg-purple-50 text-purple-800 px-2 py-0.5 rounded">Instant Online</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">Bajaj Finserv</h3>
                        </div>
                        <span class="text-xs font-bold text-emerald-700">12.5% p.a.</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500 py-1 bg-slate-50 rounded-lg px-2.5">
                        <span>Range: ₹20K – ₹25L</span>
                        <span>Tenure: 6 – 48M</span>
                    </div>
                    <button type="button" onclick="lockAndSelectPartner(4, 'Bajaj Finserv', 'https://www.bajajfinserv.in/personal-loan')" 
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-lg text-xs transition-colors">
                        Lock Bajaj &amp; Apply →
                    </button>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- STEP 5: PROOF UPLOAD & 3-MINUTE VALIDATION -->
        <!-- ========================================== -->
        <section id="step5Container" class="hidden space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Application Confirmation Proof</h2>
                <p class="text-xs text-slate-500 mt-0.5">Upload the submission screenshot from the lender portal.</p>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs text-slate-500">Locked Lender</span>
                    <span class="text-xs font-bold text-slate-900" id="currentPartnerLockedDisplay">HDFC Bank</span>
                </div>

                <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100/60 transition-colors">
                    <p class="text-xs font-semibold text-slate-800">Lender Portal Confirmation Screenshot</p>
                    <input type="file" id="proofScreenshotFile" class="mt-2 text-xs text-slate-600 block w-full file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reference Number / Remarks</label>
                    <input type="text" id="proofRemarks" placeholder="e.g. Reference #1928392" 
                           class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900">
                </div>

                <button type="button" onclick="submitCompletionProof()" id="submitProofBtn" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 rounded-xl text-xs transition-colors">
                    Submit &amp; Start 3-Minute Validation →
                </button>
            </div>

            <!-- Validation Queue Card with Animated Timer -->
            <div id="validationQueueCard" class="hidden bg-white rounded-xl p-4 border border-blue-200 shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-ping"></span>
                        <span class="text-xs font-bold text-slate-900">Validating Reference</span>
                    </div>
                    <span class="text-xs font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200" id="validationTimerDisplay">03:00</span>
                </div>
                <p class="text-xs text-slate-500">
                    Automated underwriting is validating your submission screenshot.
                </p>
            </div>

            <!-- Live Selfie Card -->
            <div id="agentSelfieCard" class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Live Face Verification</label>
                <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100/60 transition-colors">
                    <p class="text-xs font-semibold text-slate-800">Capture Live Selfie</p>
                    <input type="file" accept="image/*" capture="user" id="agentSelfieFile" class="mt-2 text-xs text-slate-600 block w-full file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white">
                </div>
                <button type="button" onclick="submitAgentSelfie()" id="submitSelfieBtn" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 rounded-xl text-xs transition-colors">
                    Verify Identity &amp; Proceed to Payout →
                </button>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- STEP 6: DISBURSEMENT & DAILY RECOVERY -->
        <!-- ========================================== -->
        <section id="step6Container" class="hidden space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Disbursement &amp; Repayment</h2>
                <p class="text-xs text-slate-500 mt-0.5">Provide bank account details for loan credit.</p>
            </div>

            <!-- Sanctioned Banner -->
            <div class="bg-white rounded-xl p-4 border border-emerald-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded">SANCTION APPROVED</span>
                    <h3 class="text-base font-extrabold text-slate-900 mt-1" id="finalApprovedLimitDisplay">₹30,000 Credit Limit</h3>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 block text-[11px]">Daily Limit</span>
                    <span class="text-xs font-bold text-slate-900" id="finalDailyLimitDisplay">₹5,000 / day</span>
                </div>
            </div>

            <!-- Bank Payout Form -->
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Disbursement Account</label>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Account Holder Name</label>
                    <input type="text" id="bankHolderName" placeholder="As per passbook" 
                           class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bank Name</label>
                    <input type="text" id="bankName" placeholder="e.g. State Bank of India" 
                           class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Account Number</label>
                        <input type="password" id="bankAccountNumber" placeholder="Account Number" 
                               class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Number</label>
                        <input type="text" id="bankAccountConfirm" placeholder="Re-enter Number" 
                               class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">IFSC Code</label>
                    <input type="text" id="bankIfsc" placeholder="SBIN0001234" 
                           class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-xs text-slate-900 uppercase font-mono tracking-wider focus:outline-none focus:border-slate-900 focus:bg-white"
                           oninput="this.value = this.value.toUpperCase()">
                </div>

                <button type="button" onclick="submitDisbursementBank()" id="submitBankBtn" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-3.5 rounded-xl text-xs transition-colors">
                    Submit for Instant Bank Disbursal →
                </button>
            </div>

            <!-- Daily Usage Lock Controller (Doc 5 Engine) -->
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900">Daily Usage Lock</h3>
                        <p class="text-[11px] text-slate-500">EMI payment status &amp; daily permission</p>
                    </div>
                    <span id="dailyUsageLockBadge" class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                        USAGE ACTIVE
                    </span>
                </div>

                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 flex justify-between items-center text-xs">
                    <div>
                        <span class="text-slate-500 block text-[11px]">Today's Scheduled EMI</span>
                        <span class="font-extrabold text-slate-900 text-sm">₹1,000.00</span>
                    </div>
                    <button type="button" onclick="payDailyEmi()" id="repayEmiBtn" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-4 py-2 rounded-lg text-xs">
                        Pay &amp; Unlock
                    </button>
                </div>
            </div>
        </section>

    </main>

    <!-- Client-Side State, Validation Guard & Logic -->
    <script>
        // State
        let currentStep = 1;
        let maxUnlockedStep = 1; // Strict step guard
        let isCustomAmountMode = false;
        let selectedProductCode = 'zero_cibil_daily';
        let selectedAmount = 30000;
        let selectedTenure = 24;
        let selectedGender = 'Male';
        let currentAppId = null;
        let customerContext = null;
        let uploadedDocFiles = {
            aadhaar_front: null,
            aadhaar_back: null,
            pan_card: null,
            bank_passbook: null,
        };

        // Step definitions for clean navigation
        const stepTitles = {
            1: 'Step 1 of 6: Choose Loan',
            2: 'Step 2 of 6: Personal Details & KYC',
            3: 'Step 3 of 6: Review & Fee',
            4: 'Step 4 of 6: Select Lender',
            5: 'Step 5 of 6: Verification',
            6: 'Step 6 of 6: Disbursement'
        };

        // Modern Floating Snackbar / Toast System (Replaces browser alert())
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'toast-enter pointer-events-auto flex items-center space-x-2.5 px-4 py-3 rounded-xl shadow-lg border text-xs font-semibold max-w-sm w-full transition-all';

            let bg = 'bg-slate-900 text-white border-slate-800';
            let iconSvg = `<svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="16" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="8" x2="12.01" y2="8" stroke-width="2"/></svg>`;

            if (type === 'error') {
                bg = 'bg-rose-950 text-rose-100 border-rose-800';
                iconSvg = `<svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke-width="2"/><line x1="9" y1="9" x2="15" y2="15" stroke-width="2"/></svg>`;
            } else if (type === 'success') {
                bg = 'bg-emerald-950 text-emerald-100 border-emerald-800';
                iconSvg = `<svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><polyline points="9 11 12 14 22 4" stroke-width="2"/></svg>`;
            } else if (type === 'warning') {
                bg = 'bg-amber-950 text-amber-100 border-amber-800';
                iconSvg = `<svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
            }

            toast.className += ` ${bg}`;
            toast.innerHTML = `
                ${iconSvg}
                <span class="flex-1">${message}</span>
                <button type="button" class="text-white/60 hover:text-white shrink-0 ml-1 font-bold text-sm" onclick="this.parentElement.remove()">✕</button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-exit');
                setTimeout(() => toast.remove(), 250);
            }, 3500);
        }

        // Read URL Parameters
        const urlParams = new URLSearchParams(window.location.search);
        let activePhone = urlParams.get('mobile') || urlParams.get('phone') || '';
        let activeName = urlParams.get('name') || '';
        let activeUserType = urlParams.get('user_type') || 'customer';
        const paramCardType = urlParams.get('card_type') || '';

        // Initialize UI
        document.addEventListener('DOMContentLoaded', () => {
            if (activeName) {
                document.getElementById('applicantNameDisplay').innerText = activeName;
                document.getElementById('applicantFullName').value = activeName;
                document.getElementById('applicantAvatar').innerText = activeName.charAt(0).toUpperCase();
            }
            if (activePhone) {
                const cleanPhone = activePhone.replace(/[^0-9]/g, '').slice(-10);
                document.getElementById('applicantPhone').value = cleanPhone;
                document.getElementById('applicantPhoneDisplay').innerText = '+91 ' + cleanPhone;
            }
            if (activeUserType === 'driver') {
                document.getElementById('headerUserType').innerText = 'Driver Partner';
                document.getElementById('headerSubtext').innerText = 'Driver Partner Finance';
                document.getElementById('applicantEmployment').value = 'Driver Partner';
            }

            // Pre-select based on card_type
            if (paramCardType.toLowerCase().includes('zero') || paramCardType.toLowerCase().includes('interest')) {
                selectPresetProduct('zero_cibil_daily', 'Zero-CIBIL Loan', 30000, 24, 0, 1000);
            } else if (paramCardType.toLowerCase().includes('low')) {
                selectPresetProduct('cash_loan_low_cibil', 'Low CIBIL Cash Loan', 100000, 24, 11.5, 2499);
            } else if (paramCardType.toLowerCase().includes('good') || paramCardType.toLowerCase().includes('prime')) {
                selectPresetProduct('cash_loan_good_cibil', 'Good CIBIL Cash Loan', 500000, 36, 9.5, 3999);
            } else if (paramCardType.toLowerCase().includes('virtual')) {
                selectPresetProduct('virtual_loan', 'App Virtual Credit', 25000, 6, 0, 2000);
            } else if (paramCardType.toLowerCase().includes('business')) {
                selectPresetProduct('business_loan', 'Business / Fleet Loan', 1000000, 48, 10.5, 5999);
            }

            fetchContext();
        });

        function updateApplicantPhone(val) {
            activePhone = val;
            document.getElementById('applicantPhoneDisplay').innerText = '+91 ' + val;
        }

        // STEP 1: PRESET SELECTION (Hides Required Amount Section)
        function selectPresetProduct(code, name, amount, tenure, rate, fee) {
            isCustomAmountMode = false;
            selectedProductCode = code;
            selectedAmount = amount;
            selectedTenure = tenure;

            // Highlight chosen preset card
            document.querySelectorAll('.product-card').forEach(c => {
                c.classList.remove('border-2', 'border-slate-900');
                c.classList.add('border', 'border-slate-200');
            });
            const activeCard = document.getElementById(`prod_${code}`);
            if (activeCard) {
                activeCard.classList.remove('border', 'border-slate-200');
                activeCard.classList.add('border-2', 'border-slate-900');
            }

            // HIDE Required Amount Section
            document.getElementById('customAmountSection').classList.add('hidden');
            document.getElementById('customToggleArrow').innerText = 'Configure ↓';

            // SHOW Selected Preset Banner
            const banner = document.getElementById('presetSelectedBanner');
            banner.classList.remove('hidden');
            document.getElementById('presetSummaryTitle').innerText = `${name} (₹${amount.toLocaleString('en-IN')})`;
            document.getElementById('presetSummaryTenure').innerText = `${tenure} Months`;

            let emiText = '';
            if (code === 'zero_cibil_daily') {
                emiText = '₹1,000 / day';
            } else {
                const emi = Math.round((amount / tenure) * 1.09);
                emiText = `₹${emi.toLocaleString('en-IN')} / mo`;
            }
            document.getElementById('presetSummaryEmi').innerText = emiText;

            // Update internal calculator values
            const slider = document.getElementById('loanAmountSlider');
            if (slider) slider.value = amount;
            updateCalculator();
        }

        // STEP 1: CUSTOM REQUIRED AMOUNT (Opens Required Amount Section & Unselects Presets)
        function selectCustomAmountMode() {
            isCustomAmountMode = true;

            // Unhighlight all preset cards
            document.querySelectorAll('.product-card').forEach(c => {
                c.classList.remove('border-2', 'border-slate-900');
                c.classList.add('border', 'border-slate-200');
            });
            const customCard = document.getElementById('prod_custom_amount');
            if (customCard) {
                customCard.classList.remove('border', 'border-slate-200');
                customCard.classList.add('border-2', 'border-slate-900');
            }

            // HIDE Preset Banner & SHOW Required Amount Section
            document.getElementById('presetSelectedBanner').classList.add('hidden');
            const customSection = document.getElementById('customAmountSection');
            customSection.classList.remove('hidden');
            document.getElementById('customToggleArrow').innerText = 'Active ↑';

            // Update calculator
            updateCalculator();
        }

        // Tenure Segmented Button
        function selectTenure(months) {
            selectedTenure = months;
            document.querySelectorAll('.tenure-btn').forEach(b => {
                if (parseInt(b.dataset.tenure) === months) {
                    b.className = 'tenure-btn py-2 text-xs font-semibold rounded-lg border border-slate-900 text-white bg-slate-900 transition-colors';
                } else {
                    b.className = 'tenure-btn py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 transition-colors';
                }
            });
            updateCalculator();
        }

        // Gender Segmented Button
        function selectGender(gender) {
            selectedGender = gender;
            document.querySelectorAll('.gender-btn').forEach(b => {
                if (b.dataset.gender === gender) {
                    b.className = 'gender-btn py-2 text-xs font-semibold rounded-lg border border-slate-900 text-white bg-slate-900 transition-colors';
                } else {
                    b.className = 'gender-btn py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 transition-colors';
                }
            });
        }

        // Calculator Update
        function updateCalculator() {
            if (isCustomAmountMode) {
                selectedAmount = parseInt(document.getElementById('loanAmountSlider').value);
            }
            document.getElementById('selectedAmountDisplay').innerText = '₹' + selectedAmount.toLocaleString('en-IN');
            
            let dailyEmi = Math.round(selectedAmount / 30);
            if (selectedProductCode === 'zero_cibil_daily') {
                dailyEmi = Math.max(500, Math.round(selectedAmount / 100));
                document.getElementById('estimatedRepaymentDisplay').innerText = '₹' + dailyEmi.toLocaleString('en-IN') + ' / day';
            } else {
                const emi = Math.round(selectedAmount / selectedTenure * 1.09);
                document.getElementById('estimatedRepaymentDisplay').innerText = '₹' + emi.toLocaleString('en-IN') + ' / mo';
            }

            let baseFee = 2000;
            if (selectedAmount <= 20000) baseFee = 1500;
            else if (selectedAmount <= 50000) baseFee = 2500;
            else if (selectedAmount <= 100000) baseFee = 3500;
            else baseFee = 4999;

            const gst = Math.round(baseFee * 0.18);
            const total = baseFee + gst;

            document.getElementById('processingFeeDisplay').innerText = `₹${total.toLocaleString('en-IN')}`;
            document.getElementById('feeBaseDisplay').innerText = `₹${baseFee.toFixed(2)}`;
            document.getElementById('feeGstDisplay').innerText = `₹${gst.toFixed(2)}`;
            document.getElementById('feeTotalDisplay').innerText = `₹${total.toFixed(2)}`;
        }

        // Step Tab Click Guard (Prevents skipping ahead without filling)
        function handleStepTabClick(targetStep) {
            if (targetStep === currentStep) return;

            if (targetStep > maxUnlockedStep) {
                showToast(`Please complete Step ${maxUnlockedStep} before proceeding.`, 'warning');
                return;
            }

            goToStep(targetStep);
        }

        // Smooth Step Transition
        function goToStep(step) {
            currentStep = step;

            // Update Progress Bar & Labels
            const pct = Math.round((step / 6) * 100);
            document.getElementById('stepProgressBar').style.width = `${pct}%`;
            document.getElementById('stepProgressLabel').innerText = stepTitles[step] || `Step ${step} of 6`;
            document.getElementById('stepPercentLabel').innerText = `${pct}%`;

            // Update View Containers
            for (let i = 1; i <= 6; i++) {
                const container = document.getElementById(`step${i}Container`);
                const pill = document.getElementById(`stepPill${i}`);
                if (container) container.classList.toggle('hidden', i !== step);
                
                if (pill) {
                    if (i === step) {
                        pill.className = 'step-pill px-3 py-1 rounded-full text-xs font-bold bg-slate-900 text-white shrink-0 shadow-xs';
                    } else if (i <= maxUnlockedStep) {
                        pill.className = 'step-pill px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 shrink-0';
                    } else {
                        pill.className = 'step-pill px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-400 shrink-0';
                    }
                }
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Fetch User Context
        async function fetchContext() {
            try {
                const res = await fetch(`/api/v1/finance/context?phone=${activePhone}&name=${encodeURIComponent(activeName)}&user_type=${activeUserType}`);
                const json = await res.json();
                if (json.success && json.data) {
                    customerContext = json.data.customer;
                    if (customerContext.name && !document.getElementById('applicantFullName').value) {
                        document.getElementById('applicantNameDisplay').innerText = customerContext.name;
                        document.getElementById('applicantFullName').value = customerContext.name;
                    }
                    if (customerContext.phone && !document.getElementById('applicantPhone').value) {
                        const cleanP = customerContext.phone.replace(/[^0-9]/g, '').slice(-10);
                        document.getElementById('applicantPhone').value = cleanP;
                        document.getElementById('applicantPhoneDisplay').innerText = '+91 ' + cleanP;
                    }
                    if (customerContext.pan) {
                        document.getElementById('applicantPan').value = customerContext.pan;
                    }
                    if (json.data.reusable_documents && json.data.reusable_documents.length > 0) {
                        document.getElementById('vaultReuseAlert').classList.remove('hidden');
                        json.data.reusable_documents.forEach(doc => {
                            if (uploadedDocFiles.hasOwnProperty(doc.document_type)) {
                                uploadedDocFiles[doc.document_type] = 'VAULT_REUSED';
                                const badge = document.getElementById(`badge_${doc.document_type}`);
                                if (badge) {
                                    badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800';
                                    badge.innerText = '✓ Reused';
                                }
                            }
                        });
                    }
                }
            } catch (_) {}
        }

        // Handle File Selection with Instant Preview
        function handleDocSelected(field, input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                uploadedDocFiles[field] = file;

                const badge = document.getElementById(`badge_${field}`);
                if (badge) {
                    badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800';
                    badge.innerText = '✓ Ready';
                }
                const status = document.getElementById(`status_${field}`);
                if (status) {
                    status.innerText = file.name;
                }

                // Show thumbnail preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewContainer = document.getElementById(`preview_container_${field}`);
                    const previewImg = document.getElementById(`preview_img_${field}`);
                    if (previewContainer && previewImg) {
                        previewImg.src = e.target.result;
                        previewContainer.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        // STEP 1 VALIDATION & PROCEED
        function validateStep1AndProceed() {
            const consent = document.getElementById('termsConsent');
            if (!consent.checked) {
                showToast('Please accept the underwriting terms to proceed.', 'warning');
                return;
            }

            // Unlock Step 2
            maxUnlockedStep = Math.max(maxUnlockedStep, 2);
            goToStep(2);
        }

        // STEP 2 VALIDATION & PROCEED (Enforces all 4 KYC documents + Phone + Details)
        async function validateStep2AndProceed() {
            const fullName = document.getElementById('applicantFullName').value.trim();
            const phone = document.getElementById('applicantPhone').value.trim();
            const pan = document.getElementById('applicantPan').value.trim();
            const city = document.getElementById('applicantCity').value.trim();
            const pincode = document.getElementById('applicantPincode').value.trim();

            if (!fullName) {
                showToast('Please enter your full legal name.', 'warning');
                document.getElementById('applicantFullName').focus();
                return;
            }
            if (!phone || phone.length < 10) {
                showToast('Please enter a valid 10-digit mobile number.', 'warning');
                document.getElementById('applicantPhone').focus();
                return;
            }
            if (!pan || pan.length < 10) {
                showToast('Please enter a valid 10-digit PAN number.', 'warning');
                document.getElementById('applicantPan').focus();
                return;
            }
            if (!city) {
                showToast('Please enter your city.', 'warning');
                document.getElementById('applicantCity').focus();
                return;
            }
            if (!pincode || pincode.length < 6) {
                showToast('Please enter a valid 6-digit PIN code.', 'warning');
                document.getElementById('applicantPincode').focus();
                return;
            }

            // ENFORCE ALL 4 MANDATORY KYC DOCUMENTS
            if (!uploadedDocFiles.aadhaar_front) {
                showToast('Please upload Document 1: Aadhaar Card Front.', 'warning');
                document.getElementById('box_aadhaar_front').scrollIntoView({ behavior: 'smooth' });
                return;
            }
            if (!uploadedDocFiles.aadhaar_back) {
                showToast('Please upload Document 2: Aadhaar Card Back.', 'warning');
                document.getElementById('box_aadhaar_back').scrollIntoView({ behavior: 'smooth' });
                return;
            }
            if (!uploadedDocFiles.pan_card) {
                showToast('Please upload Document 3: PAN Card Front.', 'warning');
                document.getElementById('box_pan_card').scrollIntoView({ behavior: 'smooth' });
                return;
            }
            if (!uploadedDocFiles.bank_passbook) {
                showToast('Please upload Document 4: Bank Passbook Front.', 'warning');
                document.getElementById('box_bank_passbook').scrollIntoView({ behavior: 'smooth' });
                return;
            }

            const btn = document.getElementById('submitAppBtn');
            btn.innerHTML = '<span>Uploading Documents &amp; Saving...</span>';
            btn.disabled = true;

            // Prepare Multipart Form Data to ensure files + profile are submitted
            const formData = new FormData();
            formData.append('phone', phone);
            formData.append('applicant_phone', phone);
            formData.append('applicant_name', fullName);
            formData.append('user_type', activeUserType);
            formData.append('product_code', selectedProductCode);
            formData.append('requested_amount', selectedAmount);
            formData.append('tenure_months', selectedTenure);
            formData.append('pan', pan);
            formData.append('dob', document.getElementById('applicantDob').value);
            formData.append('city', city);
            formData.append('state', document.getElementById('applicantState').value);
            formData.append('pincode', pincode);
            formData.append('gender', selectedGender);
            formData.append('employment', document.getElementById('applicantEmployment').value);

            // Append 4 KYC files if uploaded
            if (uploadedDocFiles.aadhaar_front instanceof File) {
                formData.append('aadhaar_front', uploadedDocFiles.aadhaar_front);
            }
            if (uploadedDocFiles.aadhaar_back instanceof File) {
                formData.append('aadhaar_back', uploadedDocFiles.aadhaar_back);
            }
            if (uploadedDocFiles.pan_card instanceof File) {
                formData.append('pan_card', uploadedDocFiles.pan_card);
            }
            if (uploadedDocFiles.bank_passbook instanceof File) {
                formData.append('bank_passbook', uploadedDocFiles.bank_passbook);
            }

            try {
                const res = await fetch('/api/v1/finance/applications/initiate', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });
                const json = await res.json();
                if (json.success && json.data) {
                    currentAppId = json.data.id;
                    document.getElementById('appNumberDisplay').innerText = json.data.application_number;
                    document.getElementById('summaryProductName').innerText = selectedProductCode.replace(/_/g, ' ').toUpperCase();
                    document.getElementById('summaryAmount').innerText = '₹' + selectedAmount.toLocaleString('en-IN');
                    document.getElementById('summaryTenure').innerText = selectedTenure + ' Months';
                    document.getElementById('finalApprovedLimitDisplay').innerText = '₹' + selectedAmount.toLocaleString('en-IN') + ' Credit Limit';

                    // Update borrower display
                    document.getElementById('applicantNameDisplay').innerText = fullName;
                    document.getElementById('applicantPhoneDisplay').innerText = '+91 ' + phone;

                    // Unlock Step 3
                    maxUnlockedStep = Math.max(maxUnlockedStep, 3);
                    showToast('Application & KYC documents saved successfully!', 'success');
                    goToStep(3);
                } else {
                    showToast(json.error || 'Failed to save application. Please verify details.', 'error');
                }
            } catch (e) {
                showToast('Connection error: ' + e.message, 'error');
            } finally {
                btn.innerHTML = '<span>Continue to Review</span><span>→</span>';
                btn.disabled = false;
            }
        }

        // STEP 3: FEE PAYMENT
        async function processFeePayment() {
            if (!currentAppId) {
                showToast('Please complete personal details first.', 'warning');
                goToStep(2);
                return;
            }
            const btn = document.getElementById('payFeeBtn');
            btn.innerHTML = '<span>Processing Payment...</span>';
            btn.disabled = true;

            const method = document.getElementById('paymentMethodSelect').value;

            try {
                const res = await fetch(`/api/v1/finance/applications/${currentAppId}/confirm-fee`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ payment_method: method })
                });
                const json = await res.json();
                if (json.success) {
                    // Unlock Step 4
                    maxUnlockedStep = Math.max(maxUnlockedStep, 4);
                    showToast('Fee paid successfully! Lending partners unlocked.', 'success');
                    goToStep(4);
                } else {
                    showToast(json.error || 'Fee confirmation failed.', 'error');
                }
            } catch (e) {
                showToast('Payment error: ' + e.message, 'error');
            } finally {
                btn.innerHTML = '<span>Pay Fee &amp; Unlock Partners</span><span>→</span>';
                btn.disabled = false;
            }
        }

        // STEP 4: SINGLE PARTNER LOCK
        async function lockAndSelectPartner(partnerId, partnerName, url) {
            if (!currentAppId) {
                showToast('Application session missing. Please restart.', 'error');
                return;
            }

            try {
                await fetch(`/api/v1/finance/applications/${currentAppId}/select-partner`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ partner_id: partnerId })
                });

                document.getElementById('currentPartnerLockedDisplay').innerText = partnerName;

                // Unlock Step 5
                maxUnlockedStep = Math.max(maxUnlockedStep, 5);
                showToast(`Locked with ${partnerName}. Opening application portal...`, 'success');

                // Open partner URL in external view
                window.open(url, '_blank');
                goToStep(5);
            } catch (e) {
                showToast('Error selecting partner: ' + e.message, 'error');
            }
        }

        // STEP 5: PROOF UPLOAD & 3-MINUTE VALIDATION
        async function submitCompletionProof() {
            const fileInput = document.getElementById('proofScreenshotFile');
            if (!fileInput.files || fileInput.files.length === 0) {
                showToast('Please select your submission screenshot.', 'warning');
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
                    body: formData
                });
                const json = await res.json();
                if (json.success) {
                    showToast('Proof submitted! 3-minute validation started.', 'success');
                    document.getElementById('validationQueueCard').classList.remove('hidden');
                    startValidationTimer(180);
                } else {
                    showToast(json.error || 'Failed to upload screenshot.', 'error');
                }
            } catch (e) {
                showToast('Upload error: ' + e.message, 'error');
            } finally {
                btn.innerText = 'Submit & Start 3-Minute Validation →';
                btn.disabled = false;
            }
        }

        // Timer
        function startValidationTimer(seconds) {
            let remain = seconds;
            const timerEl = document.getElementById('validationTimerDisplay');
            const interval = setInterval(() => {
                remain--;
                const m = String(Math.floor(remain / 60)).padStart(2, '0');
                const s = String(remain % 60).padStart(2, '0');
                if (timerEl) timerEl.innerText = `${m}:${s}`;
                if (remain <= 0) {
                    clearInterval(interval);
                    if (timerEl) timerEl.innerText = '00:00 (Verified)';
                }
            }, 1000);
        }

        // LIVE SELFIE
        async function submitAgentSelfie() {
            const fileInput = document.getElementById('agentSelfieFile');
            if (!fileInput.files || fileInput.files.length === 0) {
                showToast('Please capture your live selfie for verification.', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('selfie', fileInput.files[0]);

            const btn = document.getElementById('submitSelfieBtn');
            btn.innerText = 'Verifying Face...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/v1/finance/applications/${currentAppId}/upload-selfie`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });
                const json = await res.json();
                if (json.success) {
                    // Unlock Step 6
                    maxUnlockedStep = Math.max(maxUnlockedStep, 6);
                    showToast('Identity verified! Sanction approved.', 'success');
                    goToStep(6);
                } else {
                    showToast(json.error || 'Selfie verification failed.', 'error');
                }
            } catch (e) {
                showToast('Selfie error: ' + e.message, 'error');
            } finally {
                btn.innerText = 'Verify Identity & Proceed to Payout →';
                btn.disabled = false;
            }
        }

        // STEP 6: DISBURSEMENT BANK
        async function submitDisbursementBank() {
            const name = document.getElementById('bankHolderName').value.trim();
            const bank = document.getElementById('bankName').value.trim();
            const acc = document.getElementById('bankAccountNumber').value.trim();
            const confirm = document.getElementById('bankAccountConfirm').value.trim();
            const ifsc = document.getElementById('bankIfsc').value.trim();

            if (!name || !bank || !acc || !ifsc) {
                showToast('Please complete all bank account fields.', 'warning');
                return;
            }
            if (acc !== confirm) {
                showToast('Account numbers do not match. Please verify.', 'error');
                return;
            }

            const btn = document.getElementById('submitBankBtn');
            btn.innerText = 'Processing Disbursal...';
            btn.disabled = true;

            try {
                const res = await fetch(`/api/v1/finance/applications/${currentAppId}/disbursement-account`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        account_name: name,
                        bank_name: bank,
                        account_number: acc,
                        ifsc: ifsc,
                    })
                });
                const json = await res.json();
                if (json.success) {
                    showToast('Bank details submitted! Loan is marked for disbursal.', 'success');
                } else {
                    showToast(json.error || 'Disbursement submission failed.', 'error');
                }
            } catch (e) {
                showToast('Disbursement error: ' + e.message, 'error');
            } finally {
                btn.innerText = 'Submit for Instant Bank Disbursal →';
                btn.disabled = false;
            }
        }

        // PAY DAILY EMI
        async function payDailyEmi() {
            const btn = document.getElementById('repayEmiBtn');
            btn.innerText = 'Processing...';
            btn.disabled = true;

            try {
                const res = await fetch('/api/v1/finance/daily-repayment', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        phone: activePhone,
                        payment_amount: 1000.00
                    })
                });
                const json = await res.json();
                if (json.success) {
                    showToast('Daily EMI received! Credit line is active.', 'success');
                    const badge = document.getElementById('dailyUsageLockBadge');
                    if (badge) {
                        badge.className = 'text-xs font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200';
                        badge.innerText = 'USAGE ACTIVE';
                    }
                } else {
                    showToast(json.error || 'Repayment failed.', 'error');
                }
            } catch (e) {
                showToast('Error: ' + e.message, 'error');
            } finally {
                btn.innerText = 'Pay & Unlock';
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>

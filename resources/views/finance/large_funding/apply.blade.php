@extends('finance.layouts.base')
@section('title', 'Application Form — Private Funding (₹2Cr to ₹100Cr)')
@section('header-sub', '₹2Cr – ₹100Cr Dossier')

@section('content')
<div style="padding-bottom: 24px;">

    <!-- Wizard Header & Indicator -->
    <div class="fw-card" style="padding: 16px; margin-bottom: 16px; background: #ffffff;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
            <span style="font-size:12px; font-weight:800; color:#4f46e5; text-transform:uppercase; letter-spacing:0.5px;" id="stepBadge">
                Step 1 of 7: Applicant Details
            </span>
            <span style="font-size:12px; font-weight:700; color:#64748b;" id="stepPercent">14%</span>
        </div>
        <div style="height: 6px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
            <div id="stepProgressBar" style="height: 100%; width: 14%; background: linear-gradient(90deg, #4f46e5, #7c3aed); border-radius: 4px; transition: width 0.3s ease;"></div>
        </div>
    </div>

    @if($errors->any())
    <div style="background: #fef2f2; border: 1.5px solid #f87171; border-radius: 10px; padding: 14px; margin-bottom: 16px; color: #991b1b; font-size: 13px;">
        <div style="font-weight:700; margin-bottom:4px;">Please correct the following:</div>
        <ul style="padding-left:18px; margin:0;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('finance.large_funding.submit') }}" enctype="multipart/form-data" id="largeFundingForm">
        @csrf

        {{-- =========================================================================
             FORM 1: APPLICANT DETAILS
        ========================================================================= --}}
        <div class="form-step-section" id="sectionStep1">
            <div class="fw-card">
                <div class="fw-card-title" style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#e0e7ff; color:#4338ca; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:800;">1</span>
                    Applicant Details
                </div>
                <p style="font-size:12.5px; color:#64748b; margin-bottom:16px;">
                    Primary authorized applicant representing the business.
                </p>

                <div class="fw-input-group">
                    <label class="fw-label">Applicant / Main Founder Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="applicant_name" value="{{ old('applicant_name', $customer->name ?? '') }}" class="fw-input" placeholder="Full Legal Name" required>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Mobile Number (OTP Verified) <span style="color:#ef4444;">*</span></label>
                    <input type="tel" name="mobile_number" value="{{ old('mobile_number', $phone ?? '') }}" class="fw-input" readonly style="background:#f8fafc; font-weight:700; color:#0f172a;">
                    <span style="font-size:11px; color:#10b981; font-weight:700; margin-top:3px; display:inline-block;">✓ Phone Verified &amp; Secured</span>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Email ID <span style="color:#ef4444;">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}" class="fw-input" placeholder="founder@company.com" required>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Applicant Role <span style="color:#ef4444;">*</span></label>
                    <select name="applicant_role" class="fw-input" required>
                        <option value="">Select Applicant Role</option>
                        <option value="Founder" {{ old('applicant_role') === 'Founder' ? 'selected' : '' }}>Founder</option>
                        <option value="Co-Founder" {{ old('applicant_role') === 'Co-Founder' ? 'selected' : '' }}>Co-Founder</option>
                        <option value="Director" {{ old('applicant_role') === 'Director' ? 'selected' : '' }}>Director</option>
                        <option value="Partner" {{ old('applicant_role') === 'Partner' ? 'selected' : '' }}>Partner</option>
                        <option value="Proprietor" {{ old('applicant_role') === 'Proprietor' ? 'selected' : '' }}>Proprietor</option>
                        <option value="Authorised Representative" {{ old('applicant_role') === 'Authorised Representative' ? 'selected' : '' }}>Authorised Representative</option>
                    </select>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Applicant PAN Number <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="pan_number" value="{{ old('pan_number', $customer->pan ?? '') }}" class="fw-input" placeholder="ABCDE1234F" maxlength="10" style="text-transform:uppercase;" required>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <div class="fw-input-group">
                        <label class="fw-label">City <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="city" value="{{ old('city', $customer->city ?? '') }}" class="fw-input" placeholder="City" required>
                    </div>
                    <div class="fw-input-group">
                        <label class="fw-label">State <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="state" value="{{ old('state', $customer->state ?? '') }}" class="fw-input" placeholder="State" required>
                    </div>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Pincode <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="pincode" value="{{ old('pincode', $customer->pincode ?? '') }}" class="fw-input" placeholder="6-digit Pincode" maxlength="6" required>
                </div>

                <button type="button" class="btn-step-next" onclick="goToStep(2)" style="width:100%; background:#4f46e5; color:white; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px; margin-top:8px;">
                    Continue to Business Details →
                </button>
            </div>
        </div>

        {{-- =========================================================================
             FORM 2: BUSINESS DETAILS
        ========================================================================= --}}
        <div class="form-step-section" id="sectionStep2" style="display:none;">
            <div class="fw-card">
                <div class="fw-card-title" style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#e0e7ff; color:#4338ca; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:800;">2</span>
                    Business Details
                </div>
                <p style="font-size:12.5px; color:#64748b; margin-bottom:16px;">
                    Information about your enterprise, sector, and official registration.
                </p>

                <div class="fw-input-group">
                    <label class="fw-label">Business / Startup Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="business_name" value="{{ old('business_name') }}" class="fw-input" placeholder="e.g. Acme Technologies Pvt Ltd" required>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Business Type <span style="color:#ef4444;">*</span></label>
                    <select name="business_type" class="fw-input" required>
                        <option value="">Select Business Type</option>
                        <option value="Startup / New Business" {{ old('business_type') === 'Startup / New Business' ? 'selected' : '' }}>Startup / New Business</option>
                        <option value="Existing Business" {{ old('business_type') === 'Existing Business' ? 'selected' : '' }}>Existing Business</option>
                    </select>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Business Category <span style="color:#ef4444;">*</span></label>
                    <select name="business_category" class="fw-input" required>
                        <option value="">Select Business Category</option>
                        <option value="Technology / IT">Technology / IT</option>
                        <option value="Fintech">Fintech</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="Manufacturing">Manufacturing</option>
                        <option value="Trading / Retail">Trading / Retail</option>
                        <option value="E-commerce">E-commerce</option>
                        <option value="Education">Education</option>
                        <option value="Food / Restaurant">Food / Restaurant</option>
                        <option value="Real Estate / Construction">Real Estate / Construction</option>
                        <option value="Agriculture">Agriculture</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Business Description <span style="color:#ef4444;">*</span></label>
                    <textarea name="business_description" rows="3" class="fw-input" placeholder="Brief summary of your business model, products, and target market..." required>{{ old('business_description') }}</textarea>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Business Registration Status <span style="color:#ef4444;">*</span></label>
                    <div style="display:flex; gap:16px; padding:6px 0;">
                        <label style="display:flex; align-items:center; gap:6px; font-weight:600; cursor:pointer;">
                            <input type="radio" name="business_registration_status" value="Registered" checked onchange="toggleRegDate(true)"> Registered
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; font-weight:600; cursor:pointer;">
                            <input type="radio" name="business_registration_status" value="Not Registered" onchange="toggleRegDate(false)"> Not Registered
                        </label>
                    </div>
                </div>

                <div class="fw-input-group" id="regDateGroup">
                    <label class="fw-label">Registration Date (यदि लागू हो)</label>
                    <input type="date" name="registration_date" value="{{ old('registration_date') }}" class="fw-input">
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Business Website / App Link (Optional)</label>
                    <input type="url" name="website_or_app_link" value="{{ old('website_or_app_link') }}" class="fw-input" placeholder="https://example.com">
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Business Address <span style="color:#ef4444;">*</span></label>
                    <textarea name="business_address" rows="2" class="fw-input" placeholder="Full Registered or Operating Address" required>{{ old('business_address') }}</textarea>
                </div>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="button" onclick="goToStep(1)" style="flex:1; background:#f1f5f9; color:#475569; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px;">
                        ← Back
                    </button>
                    <button type="button" onclick="goToStep(3)" style="flex:2; background:#4f46e5; color:white; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px;">
                        Founder Details →
                    </button>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             FORM 3: FOUNDER / PARTNER DETAILS (Dynamic Counter UI)
        ========================================================================= --}}
        <div class="form-step-section" id="sectionStep3" style="display:none;">
            <div class="fw-card">
                <div class="fw-card-title" style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#e0e7ff; color:#4338ca; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:800;">3</span>
                    Founder / Partner Details
                </div>
                <p style="font-size:12.5px; color:#64748b; margin-bottom:16px;">
                    Define the core founding team and equity partners.
                </p>

                <!-- Dynamic Counter Box -->
                <div style="background:#f8fafc; border:1.5px solid #cbd5e1; border-radius:12px; padding:16px; margin-bottom:18px; text-align:center;">
                    <div style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:8px;">
                        Total Number of Founders / Partners <span style="color:#ef4444;">*</span>
                    </div>
                    <div style="display:flex; align-items:center; justify-content:center; gap:16px;">
                        <button type="button" onclick="adjustFounders(-1)" style="width:38px; height:38px; border-radius:50%; border:1.5px solid #cbd5e1; background:white; font-size:20px; font-weight:800; color:#0f172a; cursor:pointer;">-</button>
                        <span id="foundersCountDisplay" style="font-size:24px; font-weight:800; color:#4f46e5; min-width:32px;">1</span>
                        <button type="button" onclick="adjustFounders(1)" style="width:38px; height:38px; border-radius:50%; border:1.5px solid #cbd5e1; background:white; font-size:20px; font-weight:800; color:#0f172a; cursor:pointer;">+</button>
                    </div>
                    <input type="hidden" name="total_founders_count" id="total_founders_count" value="1">
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Main Applicant कौन है? (Main Applicant Name) <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="main_applicant_name" id="main_applicant_name" value="{{ old('main_applicant_name', $customer->name ?? '') }}" class="fw-input" placeholder="Main Founder Name" required>
                </div>

                <!-- Dynamic Name Inputs Container -->
                <div id="dynamicFoundersContainer">
                    <div class="fw-input-group founder-input-row" data-index="1">
                        <label class="fw-label">Main Applicant / Founder Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="founders[]" value="{{ old('applicant_name', $customer->name ?? '') }}" class="fw-input founder-field" placeholder="Full Name" required>
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="button" onclick="goToStep(2)" style="flex:1; background:#f1f5f9; color:#475569; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px;">
                        ← Back
                    </button>
                    <button type="button" onclick="goToStep(4)" style="flex:2; background:#4f46e5; color:white; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px;">
                        Funding Requirement →
                    </button>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             FORM 4: FUNDING REQUIREMENT
        ========================================================================= --}}
        <div class="form-step-section" id="sectionStep4" style="display:none;">
            <div class="fw-card">
                <div class="fw-card-title" style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#e0e7ff; color:#4338ca; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:800;">4</span>
                    Funding Requirement
                </div>
                <p style="font-size:12.5px; color:#64748b; margin-bottom:16px;">
                    Specify capital requirement between ₹2 Crore and ₹100 Crore.
                </p>

                <div class="fw-input-group">
                    <label class="fw-label">Required Funding Amount <span style="color:#ef4444;">*</span></label>
                    <select name="required_funding_amount" class="fw-input" required style="font-weight:700; color:#1e1b4b;">
                        <option value="">Select Funding Amount</option>
                        <option value="₹2 Crore – ₹5 Crore">₹2 Crore – ₹5 Crore</option>
                        <option value="₹5 Crore – ₹10 Crore">₹5 Crore – ₹10 Crore</option>
                        <option value="₹10 Crore – ₹25 Crore">₹10 Crore – ₹25 Crore</option>
                        <option value="₹25 Crore – ₹50 Crore">₹25 Crore – ₹50 Crore</option>
                        <option value="₹50 Crore – ₹100 Crore">₹50 Crore – ₹100 Crore</option>
                    </select>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Funding Type <span style="color:#ef4444;">*</span></label>
                    <select name="funding_type" class="fw-input" required>
                        <option value="">Select Funding Type</option>
                        <option value="Private Investor Funding">Private Investor Funding</option>
                        <option value="Equity Investment">Equity Investment</option>
                        <option value="Business Loan">Business Loan</option>
                        <option value="Open to Suitable Funding Option">Open to Suitable Funding Option</option>
                    </select>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Funding Purpose <span style="color:#ef4444;">*</span></label>
                    <select name="funding_purpose" class="fw-input" required>
                        <option value="">Select Funding Purpose</option>
                        <option value="Business Expansion">Business Expansion</option>
                        <option value="Working Capital">Working Capital</option>
                        <option value="New Project">New Project</option>
                        <option value="Product / Technology Development">Product / Technology Development</option>
                        <option value="Machinery / Infrastructure">Machinery / Infrastructure</option>
                        <option value="Marketing & Sales">Marketing &amp; Sales</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Funding Required Timeline <span style="color:#ef4444;">*</span></label>
                    <select name="funding_timeline" class="fw-input" required>
                        <option value="">Select Timeline</option>
                        <option value="Immediately">Immediately</option>
                        <option value="Within 1–3 Months">Within 1–3 Months</option>
                        <option value="Within 3–6 Months">Within 3–6 Months</option>
                        <option value="Flexible">Flexible</option>
                    </select>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Expected Use of Funds <span style="color:#ef4444;">*</span></label>
                    <textarea name="expected_use_of_funds" rows="3" class="fw-input" placeholder="Breakdown of capital allocation (e.g. 40% Capex, 30% Marketing, 30% R&D)..." required>{{ old('expected_use_of_funds') }}</textarea>
                </div>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="button" onclick="goToStep(3)" style="flex:1; background:#f1f5f9; color:#475569; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px;">
                        ← Back
                    </button>
                    <button type="button" onclick="goToStep(5)" style="flex:2; background:#4f46e5; color:white; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px;">
                        Financial Details →
                    </button>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             FORM 5: FINANCIAL DETAILS
        ========================================================================= --}}
        <div class="form-step-section" id="sectionStep5" style="display:none;">
            <div class="fw-card">
                <div class="fw-card-title" style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#e0e7ff; color:#4338ca; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:800;">5</span>
                    Basic Business Financial Details
                </div>
                <p style="font-size:12.5px; color:#64748b; margin-bottom:16px;">
                    Financial stage, revenue milestones, and debt status.
                </p>

                <div class="fw-input-group">
                    <label class="fw-label">Business Stage <span style="color:#ef4444;">*</span></label>
                    <select name="business_stage" class="fw-input" required>
                        <option value="">Select Stage</option>
                        <option value="Idea Stage">Idea Stage</option>
                        <option value="Business Started, No Revenue Yet">Business Started, No Revenue Yet</option>
                        <option value="Revenue Generating">Revenue Generating</option>
                        <option value="Established Business">Established Business</option>
                    </select>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Business Start Date / Year <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="business_start_date" value="{{ old('business_start_date') }}" class="fw-input" placeholder="e.g. March 2023 or 2022" required>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Current Monthly Revenue <span style="color:#ef4444;">*</span></label>
                    <select name="monthly_revenue" class="fw-input" required>
                        <option value="">Select Monthly Revenue</option>
                        <option value="No Revenue Yet">No Revenue Yet</option>
                        <option value="Below ₹5 Lakh">Below ₹5 Lakh</option>
                        <option value="₹5–25 Lakh">₹5–25 Lakh</option>
                        <option value="₹25 Lakh–₹1 Crore">₹25 Lakh–₹1 Crore</option>
                        <option value="Above ₹1 Crore">Above ₹1 Crore</option>
                    </select>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Annual Turnover (यदि उपलब्ध हो)</label>
                    <input type="text" name="annual_turnover" value="{{ old('annual_turnover') }}" class="fw-input" placeholder="e.g. ₹3.5 Crore">
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Current Profit / Loss (यदि उपलब्ध हो)</label>
                    <input type="text" name="current_profit_loss" value="{{ old('current_profit_loss') }}" class="fw-input" placeholder="e.g. ₹25 Lakhs Net Profit or Operational Breakeven">
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Existing Funding / Loan Details (यदि कोई हो)</label>
                    <textarea name="existing_loan_details" rows="2" class="fw-input" placeholder="Existing bank CC limits, angel rounds, or debt">{{ old('existing_loan_details') }}</textarea>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Expected Revenue After Funding (Optional)</label>
                    <input type="text" name="expected_revenue_after_funding" value="{{ old('expected_revenue_after_funding') }}" class="fw-input" placeholder="e.g. ₹50 Crore in Year 3">
                </div>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="button" onclick="goToStep(4)" style="flex:1; background:#f1f5f9; color:#475569; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px;">
                        ← Back
                    </button>
                    <button type="button" onclick="goToStep(6)" style="flex:2; background:#4f46e5; color:white; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px;">
                        Upload Documents →
                    </button>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             FORM 6: DOCUMENT UPLOAD & BANK DETAILS
        ========================================================================= --}}
        <div class="form-step-section" id="sectionStep6" style="display:none;">
            <div class="fw-card">
                <div class="fw-card-title" style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#e0e7ff; color:#4338ca; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:800;">6</span>
                    Bank Details &amp; Document Upload
                </div>

                <!-- Bank Account Fields -->
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:14px; margin-bottom:18px;">
                    <div style="font-size:12.5px; font-weight:800; color:#1e40af; margin-bottom:10px;">
                        🏦 Company / Applicant Bank Account Details
                    </div>
                    <div class="fw-input-group">
                        <label class="fw-label">Account Holder Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="bank_account_holder_name" value="{{ old('bank_account_holder_name') }}" class="fw-input" placeholder="Name as per Bank" required>
                    </div>
                    <div class="fw-input-group">
                        <label class="fw-label">Bank Account Number <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" class="fw-input" placeholder="Account Number" required>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="fw-input-group">
                            <label class="fw-label">Bank IFSC <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc') }}" class="fw-input" placeholder="HDFC0001234" maxlength="11" style="text-transform:uppercase;" required>
                        </div>
                        <div class="fw-input-group">
                            <label class="fw-label">Bank Name <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="fw-input" placeholder="e.g. HDFC Bank" required>
                        </div>
                    </div>
                </div>

                <div style="font-size:13px; font-weight:800; color:#0f172a; margin-bottom:12px;">
                    Mandatory Dossier Documents (अनिवार्य दस्तावेज़)
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">1. Cancelled Cheque / Bank Statement <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="doc_bank_cheque" class="fw-input" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">2. Main Applicant PAN Card <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="doc_pan_card" class="fw-input" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">3. Valid Identity Proof (Aadhaar / Passport) <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="doc_identity_proof" class="fw-input" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">4. Business Plan / Pitch Deck <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="doc_pitch_deck" class="fw-input" accept=".pdf,.ppt,.pptx,.doc,.docx" required>
                    <span style="font-size:11px; color:#64748b;">PDF or PPT explaining model, market, and financials.</span>
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">5. Fund Utilisation Plan <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="doc_fund_plan" class="fw-input" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                </div>

                <div style="font-size:13px; font-weight:800; color:#0f172a; margin:18px 0 10px;">
                    Optional Documents (यदि लागू हो या उपलब्ध हो)
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Company Registration Certificate (Optional)</label>
                    <input type="file" name="doc_company_reg" class="fw-input" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">GST Certificate (Optional)</label>
                    <input type="file" name="doc_gst_cert" class="fw-input" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">ITR / Financial Statements / Audit Report (Optional)</label>
                    <input type="file" name="doc_itr_financials" class="fw-input" accept=".pdf,.zip,.rar">
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Shareholding Pattern / Cap Table (Optional)</label>
                    <input type="file" name="doc_cap_table" class="fw-input" accept=".pdf,.xls,.xlsx">
                </div>

                <div class="fw-input-group">
                    <label class="fw-label">Other Supporting Documents (Optional)</label>
                    <input type="file" name="doc_other" class="fw-input" accept=".pdf,.jpg,.jpeg,.png,.zip">
                </div>

                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="button" onclick="goToStep(5)" style="flex:1; background:#f1f5f9; color:#475569; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px;">
                        ← Back
                    </button>
                    <button type="button" onclick="goToStep(7)" style="flex:2; background:#4f46e5; color:white; font-weight:700; padding:13px; border-radius:10px; border:none; font-size:14px;">
                        Review &amp; Consent →
                    </button>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             FORM 7: CONSENT & FINAL SUBMIT
        ========================================================================= --}}
        <div class="form-step-section" id="sectionStep7" style="display:none;">
            <div class="fw-card">
                <div class="fw-card-title" style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#e0e7ff; color:#4338ca; width:26px; height:26px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:800;">7</span>
                    Consent &amp; Final Submit
                </div>
                <p style="font-size:12.5px; color:#64748b; margin-bottom:16px;">
                    Please review and accept legal acknowledgments to submit your institutional dossier.
                </p>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px; margin-bottom:18px; display:flex; flex-direction:column; gap:14px;">
                    
                    <label style="display:flex; align-items:flex-start; gap:10px; font-size:12.5px; color:#1e293b; cursor:pointer;">
                        <input type="checkbox" name="consent_info_accurate" value="1" required checked style="width:18px; height:18px; margin-top:2px; accent-color:#4f46e5;">
                        <span>I confirm that all information and documentation provided in this application is true, authentic, and accurate. (दी गई जानकारी सही है)</span>
                    </label>

                    <label style="display:flex; align-items:flex-start; gap:10px; font-size:12.5px; color:#1e293b; cursor:pointer;">
                        <input type="checkbox" name="consent_fiinway_review" value="1" required checked style="width:18px; height:18px; margin-top:2px; accent-color:#4f46e5;">
                        <span>I authorize FIINWAY Capital and Underwriting Committee to review, verify, and authenticate this application. (सत्यापन की अनुमति)</span>
                    </label>

                    <label style="display:flex; align-items:flex-start; gap:10px; font-size:12.5px; color:#1e293b; cursor:pointer;">
                        <input type="checkbox" name="consent_share_with_investors" value="1" required checked style="width:18px; height:18px; margin-top:2px; accent-color:#4f46e5;">
                        <span>I consent that my application and business pitch can be securely shared with suitable Private Investors, HNIs, and Syndicate Lenders. (Investors के साथ शेयर करने की सहमति)</span>
                    </label>

                    <label style="display:flex; align-items:flex-start; gap:10px; font-size:12.5px; color:#1e293b; cursor:pointer;">
                        <input type="checkbox" name="consent_terms_policy" value="1" required checked style="width:18px; height:18px; margin-top:2px; accent-color:#4f46e5;">
                        <span>I have read, understood, and agreed to the FIINWAY Privacy Policy and Terms &amp; Conditions.</span>
                    </label>

                    <label style="display:flex; align-items:flex-start; gap:10px; font-size:12.5px; color:#1e293b; cursor:pointer;">
                        <input type="checkbox" name="consent_no_guarantee" value="1" required checked style="width:18px; height:18px; margin-top:2px; accent-color:#4f46e5;">
                        <span>I understand that submitting this application does not guarantee funding sanction; disbursement is subject to investor due diligence. (Funding की गारंटी नहीं मिलती)</span>
                    </label>

                </div>

                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="goToStep(6)" style="flex:1; background:#f1f5f9; color:#475569; font-weight:700; padding:14px; border-radius:10px; border:none; font-size:14px;">
                        ← Back
                    </button>
                    <button type="submit" id="submitBtn" style="flex:2; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color:white; font-weight:800; padding:14px; border-radius:10px; border:none; font-size:15px; box-shadow: 0 4px 14px rgba(22, 163, 74, 0.4);">
                        Submit Application (जमा करें) ✓
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
let currentStep = 1;
const totalSteps = 7;

function goToStep(step) {
    // Basic client validation on active step
    const currentSection = document.getElementById('sectionStep' + currentStep);
    if (step > currentStep) {
        const requiredInputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
        for (let input of requiredInputs) {
            if (!input.value.trim() && input.type !== 'file') {
                input.focus();
                alert('Please fill all mandatory fields marked with *');
                return;
            }
            if (input.type === 'file' && input.files.length === 0) {
                input.focus();
                alert('Please upload mandatory document: ' + (input.name || 'required document'));
                return;
            }
        }
    }

    // Hide all
    for (let i = 1; i <= totalSteps; i++) {
        const el = document.getElementById('sectionStep' + i);
        if (el) el.style.display = 'none';
    }

    // Show target
    document.getElementById('sectionStep' + step).style.display = 'block';
    currentStep = step;

    // Update Progress
    const percent = Math.round((currentStep / totalSteps) * 100);
    document.getElementById('stepProgressBar').style.width = percent + '%';
    document.getElementById('stepPercent').textContent = percent + '%';
    
    const titles = [
        'Applicant Details',
        'Business Details',
        'Founder / Partner Details',
        'Funding Requirement',
        'Financial Details',
        'Bank Details & Documents',
        'Consent & Final Submit'
    ];
    document.getElementById('stepBadge').textContent = 'Step ' + currentStep + ' of ' + totalSteps + ': ' + titles[currentStep - 1];

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function toggleRegDate(isReg) {
    const grp = document.getElementById('regDateGroup');
    if (grp) {
        grp.style.display = isReg ? 'block' : 'none';
    }
}

let foundersCount = 1;
function adjustFounders(delta) {
    const next = foundersCount + delta;
    if (next < 1 || next > 10) return;
    foundersCount = next;
    document.getElementById('foundersCountDisplay').textContent = foundersCount;
    document.getElementById('total_founders_count').value = foundersCount;
    renderDynamicFounders();
}

function renderDynamicFounders() {
    const container = document.getElementById('dynamicFoundersContainer');
    const mainApplicantName = document.getElementById('main_applicant_name').value || '';

    let html = '';
    for (let i = 1; i <= foundersCount; i++) {
        const label = (i === 1) ? 'Main Applicant / Founder Name <span style="color:#ef4444;">*</span>' : ('Founder / Partner ' + i + ' Name <span style="color:#ef4444;">*</span>');
        const defaultVal = (i === 1) ? mainApplicantName : '';
        html += `
            <div class="fw-input-group founder-input-row" data-index="${i}">
                <label class="fw-label">${label}</label>
                <input type="text" name="founders[]" value="${defaultVal}" class="fw-input founder-field" placeholder="Full Name of Partner ${i}" required>
            </div>
        `;
    }
    container.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    const mainApplicant = document.getElementById('main_applicant_name');
    if (mainApplicant) {
        mainApplicant.addEventListener('input', function() {
            const firstRow = document.querySelector('.founder-input-row[data-index="1"] input');
            if (firstRow) firstRow.value = this.value;
        });
    }
});
</script>
@endsection

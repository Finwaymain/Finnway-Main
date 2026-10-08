@extends('finance.layouts.base')
@section('title', 'Applicant Details — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 2 of 12</span><span>16%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:16%;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s02_type_consent', ['phone' => $phone]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<form id="applicantForm" method="POST" action="{{ route('finance.cash_loan.save_step') }}">
    @csrf
    <input type="hidden" name="step" value="s03">
    <input type="hidden" name="phone" value="{{ $phone }}">
    <input type="hidden" name="loan_type" value="{{ $loanType ?? 'low_cibil' }}">

    @if(session('error'))
        <div class="fw-alert fw-alert-error" style="padding:10px 14px; margin-bottom:12px; font-size:13px; font-weight:600; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:8px;">
            ⚠️ {{ session('error') }}
        </div>
    @endif
    <div id="clientErrorBox" class="fw-alert fw-alert-error" style="display:none; padding:10px 14px; margin-bottom:12px; font-size:13px; font-weight:600; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:8px;"></div>

    <div class="fw-card mt-2 mb-4 pb-3">
        <h3 class="fw-section-title mb-1">Applicant Profile</h3>
        <p class="fw-section-sub">Enter primary borrower identity details as per PAN card.</p>
        
        <div class="mb-4">
            <h5 style="color:var(--navy); font-weight:700; font-size:14px; border-bottom:1.5px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px;">
                1. Personal Details
            </h5>
            <div class="fw-input-group">
                <label class="fw-label">Full Name (As per PAN)</label>
                <input type="text" name="applicant_name" class="fw-input" placeholder="e.g. Rahul Sharma" value="{{ $customer->full_name ?? ($application->applicant_name ?? '') }}" required>
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Date of Birth</label>
                <input type="date" name="dob" class="fw-input" value="{{ $customer->dob ?? '1995-05-15' }}" required>
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Mobile Number</label>
                <input type="tel" name="phone_display" class="fw-input" value="{{ $phone }}" readonly style="background:#f1f5f9; color:#64748b;">
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Email ID</label>
                <input type="email" name="email" class="fw-input" placeholder="rahul@example.com" value="{{ $customer->email ?? '' }}" required>
            </div>
            <div class="fw-input-group">
                <label class="fw-label">PAN Number</label>
                <input type="text" name="pan_number" class="fw-input" maxlength="10" placeholder="ABCDE1234F" style="text-transform:uppercase; font-family:monospace; font-weight:700;" value="{{ $customer->pan_number ?? '' }}" required>
            </div>
        </div>

        <div class="mb-4">
            <h5 style="color:var(--navy); font-weight:700; font-size:14px; border-bottom:1.5px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px;">
                2. Employment & Income
            </h5>
            <div class="fw-input-group">
                <label class="fw-label">Employment Type</label>
                <select name="employment_type" class="fw-input">
                    <option value="Salaried" selected>Salaried Employee</option>
                    <option value="Self-Employed">Self-Employed Professional</option>
                    <option value="Business">Business Owner / Trader</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Company / Employer Name</label>
                <input type="text" name="company_name" class="fw-input" placeholder="e.g. Fiinway Logistics" value="{{ $customer->company_name ?? '' }}">
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Net Monthly Take-Home (₹)</label>
                <input type="number" name="monthly_income" class="fw-input" placeholder="35000" value="{{ $customer->monthly_income ?? '35000' }}" required>
            </div>
        </div>

        <div class="mb-2">
            <h5 style="color:var(--navy); font-weight:700; font-size:14px; border-bottom:1.5px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px;">
                3. Desired Loan Requirement
            </h5>
            <div class="fw-input-group">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="fw-label mb-0">Requested Loan Amount (₹)</label>
                    <span class="badge" style="background:#eff6ff; color:#1e40af; font-size:11.5px; font-weight:700; border:1px solid #bfdbfe; border-radius:6px; padding:3px 8px;">
                        Pre-Sanctioned Max: ₹ {{ number_format($maxLimit) }}
                    </span>
                </div>
                <input type="number" 
                       id="requested_amount" 
                       name="requested_amount" 
                       class="fw-input" 
                       style="font-size:18px; font-weight:800; color:var(--blue);" 
                       value="{{ min($amount, $maxLimit) }}" 
                       min="5000" 
                       max="{{ $maxLimit }}" 
                       required>
                <div id="amountLimitError" class="text-danger mt-1" style="font-size:12px; display:none; font-weight:600;">
                    ⚠️ Amount cannot exceed your pre-sanctioned limit of ₹ {{ number_format($maxLimit) }}.
                </div>
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Primary Loan Purpose</label>
                <select name="loan_purpose" class="fw-input">
                    <option value="Personal Emergency">Personal Emergency / Health</option>
                    <option value="Debt Consolidation">Debt Consolidation</option>
                    <option value="Home Improvement">Home Renovation</option>
                    <option value="Education">Education & Courses</option>
                    <option value="Business">Small Business Needs</option>
                </select>
            </div>
        </div>
    </div>
</form>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" id="applicantSubmitBtn" form="applicantForm" class="fw-btn fw-btn-primary">Submit & Continue &rarr;</button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const maxLimit = {{ $maxLimit }};
    const amtInput = document.getElementById('requested_amount');
    const errBox = document.getElementById('amountLimitError');
    const submitBtn = document.getElementById('applicantSubmitBtn');

    function checkAmount() {
        const val = parseFloat(amtInput.value) || 0;
        if (val > maxLimit) {
            errBox.style.display = 'block';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
        } else {
            errBox.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
    }

    amtInput.addEventListener('input', checkAmount);
    amtInput.addEventListener('blur', function() {
        let val = parseFloat(this.value) || 0;
        if (val > maxLimit) {
            this.value = maxLimit;
            checkAmount();
        }
    });

    document.getElementById('applicantForm').addEventListener('submit', function(e) {
        var v = window.FiinwayValidator;
        var nameInp = document.querySelector('input[name="applicant_name"]');
        var emailInp = document.querySelector('input[name="email"]');
        var panInp = document.querySelector('input[name="pan_number"]');
        var dobInp = document.querySelector('input[name="dob"]');
        var incInp = document.querySelector('input[name="monthly_income"]');
        var firstInvalid = null;

        // Reset errors
        [nameInp, emailInp, panInp, dobInp, incInp].forEach(function(inp) { if (inp) v.clearError(inp); });

        if (nameInp && !v.isValidName(nameInp.value)) {
            v.showError(nameInp, 'Enter a valid full name (letters only, min 3 characters).');
            if (!firstInvalid) firstInvalid = nameInp;
        }

        if (emailInp && !v.isValidEmail(emailInp.value)) {
            v.showError(emailInp, 'Enter a valid email address (e.g. name@domain.com).');
            if (!firstInvalid) firstInvalid = emailInp;
        }

        if (panInp && !v.isValidPan(panInp.value)) {
            v.showError(panInp, 'Enter a valid 10-character PAN (e.g. ABCDE1234F).');
            if (!firstInvalid) firstInvalid = panInp;
        }

        if (dobInp) {
            var age = v.getAge(dobInp.value);
            if (!dobInp.value || age < 18 || age > 75) {
                v.showError(dobInp, 'Applicant must be at least 18 years of age (Current age: ' + age + ').');
                if (!firstInvalid) firstInvalid = dobInp;
            }
        }

        if (incInp && (parseFloat(incInp.value) || 0) < 5000) {
            v.showError(incInp, 'Net monthly take-home must be at least ₹5,000.');
            if (!firstInvalid) firstInvalid = incInp;
        }

        const val = parseFloat(amtInput.value) || 0;
        if (val > maxLimit) {
            amtInput.value = maxLimit;
            checkAmount();
            alert('Requested amount cannot exceed your pre-sanctioned limit of ₹ ' + new Intl.NumberFormat('en-IN').format(maxLimit));
            if (!firstInvalid) firstInvalid = amtInput;
        }

        if (firstInvalid) {
            e.preventDefault();
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
    });
});
</script>
@endpush

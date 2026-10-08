@extends('finance.layouts.base')
@section('title', 'Apply for Student Credit — Fiinway')
@section('header-sub', 'Student Credit')
@section('progress-label', 'Step 1 of 8')
@section('progress-pct', '12')
@section('progress') {{-- trigger hasSection --}} @endsection

@section('content')
<p class="fw-section-title">Student Credit</p>
<p class="fw-section-sub">Apply in minutes. Credit for your academic journey.</p>

<div class="fw-alert fw-alert-info" style="margin-bottom:16px;">
    ℹ️ Applicant must be between <strong>16 – 26 years</strong> of age.
</div>

@if(session('error'))
<div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;">
    {{ session('error') }}
</div>
@endif
@if($errors->any())
<div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;">
    <ul style="margin: 0; padding-left: 20px;">
        @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<form id="studentApplyForm" method="POST" action="{{ route('finance.student_credit.save_apply') }}">
    @csrf
    <input type="hidden" name="phone" value="{{ request('phone', $phone ?? '') }}">

    <div class="fw-card">
        <p class="fw-card-title">Personal Details</p>

        <div class="fw-input-group">
            <label class="fw-label">Full Name <span style="color:red;">*</span></label>
            <input type="text" name="applicant_name" class="fw-input" placeholder="As on Aadhaar" value="{{ old('applicant_name', $application->applicant_name ?? ($customer->name ?? '')) }}" required>
        </div>

        <div class="fw-input-group">
            <label class="fw-label">Date of Birth <span style="color:red;">*</span></label>
            <input type="date" name="dob" class="fw-input" value="{{ old('dob', $customer->dob ?? '') }}" required>
            <div style="font-size:11px;color:var(--gray3);margin-top:3px;">Must be between 16 and 26 years old.</div>
        </div>

        <div class="fw-input-group">
            <label class="fw-label">Mobile Number <span style="color:red;">*</span></label>
            <input type="tel" name="mobile" class="fw-input" maxlength="10"
                   value="{{ old('mobile', $customer->phone ?? request('phone')) }}" placeholder="10-digit mobile" required>
        </div>

        <div class="fw-input-group">
            <label class="fw-label">Aadhaar Number <span style="color:red;">*</span></label>
            <input type="text" name="aadhaar_number" class="fw-input" maxlength="12" placeholder="12-digit Aadhaar" value="{{ old('aadhaar_number', $customer->aadhaar ?? '') }}" required>
        </div>
    </div>

    <div class="fw-card">
        <p class="fw-card-title">Student Details</p>

        <div class="fw-input-group">
            <label class="fw-label">Student Type <span style="color:red;">*</span></label>
            <div style="display:flex;gap:20px;padding:4px 0;">
                @php $sType = old('student_type', $application->applicant_details['student_type'] ?? 'domestic'); @endphp
                <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                    <input type="radio" name="student_type" value="domestic"
                           style="accent-color:var(--blue2);" {{ $sType === 'domestic' ? 'checked' : '' }}>
                    Domestic
                </label>
                <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                    <input type="radio" name="student_type" value="international"
                           style="accent-color:var(--blue2);" {{ $sType === 'international' ? 'checked' : '' }}>
                    International
                </label>
            </div>
        </div>

        <div class="fw-input-group">
            <label class="fw-label">College / University Name <span style="color:red;">*</span></label>
            <input type="text" name="college_name" class="fw-input" placeholder="Full institution name" value="{{ old('college_name', $application->applicant_details['college_name'] ?? '') }}" required>
        </div>

        <div class="fw-input-group">
            <label class="fw-label">Course Name <span style="color:red;">*</span></label>
            <input type="text" name="course_name" class="fw-input" placeholder="e.g. B.Tech, MBA, MBBS" value="{{ old('course_name', $application->applicant_details['course_name'] ?? '') }}" required>
        </div>

        <div class="fw-input-group">
            <label class="fw-label">Student ID Number <span style="color:red;">*</span></label>
            <input type="text" name="student_id" class="fw-input" placeholder="As printed on ID card" value="{{ old('student_id', $application->applicant_details['student_id'] ?? '') }}" required>
        </div>

        <div class="fw-input-group">
            <label class="fw-label">Student ID Expiry Date</label>
            <input type="date" name="student_id_expiry" class="fw-input" value="{{ old('student_id_expiry', $application->applicant_details['student_id_expiry'] ?? '') }}">
        </div>
    </div>

    <div class="fw-card">
        <p class="fw-card-title">Credit Amount Required</p>
        @php
            $currAmt = old('credit_amount', $application->requested_amount ?? 25000);
            $presetList = [10000, 25000, 50000, 75000];
            $isPreset = in_array(intval($currAmt), $presetList);
        @endphp

        <input type="hidden" id="credit_amount" name="credit_amount" value="{{ $currAmt }}">

        <div class="fw-tenure-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:10px;">
            <label class="fw-tenure-chip {{ ($isPreset && $currAmt == 10000) ? 'active' : '' }}">
                <input type="radio" name="credit_preset" value="10000" style="display:none;" {{ ($isPreset && $currAmt == 10000) ? 'checked' : '' }}>
                ₹10,000
            </label>
            <label class="fw-tenure-chip {{ ($isPreset && $currAmt == 25000) ? 'active' : '' }}">
                <input type="radio" name="credit_preset" value="25000" style="display:none;" {{ ($isPreset && $currAmt == 25000) ? 'checked' : '' }}>
                ₹25,000
            </label>
            <label class="fw-tenure-chip {{ ($isPreset && $currAmt == 50000) ? 'active' : '' }}">
                <input type="radio" name="credit_preset" value="50000" style="display:none;" {{ ($isPreset && $currAmt == 50000) ? 'checked' : '' }}>
                ₹50,000
            </label>
            <label class="fw-tenure-chip {{ ($isPreset && $currAmt == 75000) ? 'active' : '' }}">
                <input type="radio" name="credit_preset" value="75000" style="display:none;" {{ ($isPreset && $currAmt == 75000) ? 'checked' : '' }}>
                ₹75,000
            </label>
        </div>

        <label class="fw-tenure-chip {{ !$isPreset ? 'active' : '' }}" style="width:100%;display:block;text-align:center;margin-bottom:10px;">
            <input type="radio" name="credit_preset" value="custom" style="display:none;" {{ !$isPreset ? 'checked' : '' }}>
            Custom Amount
        </label>

        <div id="custom-amount-wrap" style="display:{{ !$isPreset ? 'block' : 'none' }};">
            <div class="fw-input-group" style="margin-bottom:0;">
                <label class="fw-label">Enter Amount (₹)</label>
                <input type="number" id="custom_amount_input" class="fw-input" placeholder="Minimum ₹1,000, Max ₹75,000" min="1000" max="75000" value="{{ !$isPreset ? $currAmt : '' }}">
            </div>
        </div>
    </div>

    <div class="fw-consent">
        <input type="checkbox" name="consent" id="consent" value="1" checked required>
        <label for="consent">I confirm all details are accurate and I am between 16–26 years of age. I authorise Fiinway to verify my student credentials.</label>
    </div>
</form>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="studentApplyForm" class="fw-btn fw-btn-primary w-100">
        Continue →
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var amountField = document.getElementById('credit_amount');
    var customInput = document.getElementById('custom_amount_input');
    var customWrap = document.getElementById('custom-amount-wrap');

    document.querySelectorAll('input[name="credit_preset"]').forEach(function(r){
        r.addEventListener('change', function(){
            document.querySelectorAll('.fw-tenure-chip').forEach(function(c){ c.classList.remove('active'); });
            this.closest('.fw-tenure-chip').classList.add('active');
            if (this.value === 'custom') {
                customWrap.style.display = 'block';
                if (customInput.value) {
                    amountField.value = customInput.value;
                }
            } else {
                customWrap.style.display = 'none';
                amountField.value = this.value;
            }
        });
    });

    if (customInput) {
        customInput.addEventListener('input', function() {
            amountField.value = this.value;
        });
    }

    var form = document.getElementById('studentApplyForm');
    if (!form) return;

    var v = window.FiinwayValidator;
    var nameInp = form.querySelector('input[name="applicant_name"]');
    var dobInp = form.querySelector('input[name="dob"]');
    var mobileInp = form.querySelector('input[name="mobile"]');
    var aadhInp = form.querySelector('input[name="aadhaar_number"]');
    var collegeInp = form.querySelector('input[name="college_name"]');
    var courseInp = form.querySelector('input[name="course_name"]');
    var sidInp = form.querySelector('input[name="student_id"]');

    if (mobileInp) v.setupLiveDigits(mobileInp, 10);
    if (aadhInp) v.setupLiveDigits(aadhInp, 12);

    form.addEventListener('submit', function(e) {
        var firstInvalid = null;

        [nameInp, dobInp, mobileInp, aadhInp, collegeInp, courseInp, sidInp, customInput].forEach(function(inp) {
            if (inp) v.clearError(inp);
        });

        if (nameInp && !v.isValidName(nameInp.value)) {
            v.showError(nameInp, 'Enter a valid full name (min 3 characters, letters only).');
            if (!firstInvalid) firstInvalid = nameInp;
        }

        if (dobInp) {
            if (!dobInp.value) {
                v.showError(dobInp, 'Date of birth is required.');
                if (!firstInvalid) firstInvalid = dobInp;
            } else {
                var age = v.getAge(dobInp.value);
                if (age < 16 || age > 26) {
                    v.showError(dobInp, 'Applicant must be between 16 and 26 years of age (Current age: ' + age + ').');
                    if (!firstInvalid) firstInvalid = dobInp;
                }
            }
        }

        if (mobileInp && !v.isValidPhone(mobileInp.value)) {
            v.showError(mobileInp, 'Enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.');
            if (!firstInvalid) firstInvalid = mobileInp;
        }

        if (aadhInp && !v.isValidAadhaar(aadhInp.value)) {
            v.showError(aadhInp, 'Enter a valid 12-digit Aadhaar number.');
            if (!firstInvalid) firstInvalid = aadhInp;
        }

        if (collegeInp && collegeInp.value.trim().length < 2) {
            v.showError(collegeInp, 'College / University Name is required.');
            if (!firstInvalid) firstInvalid = collegeInp;
        }

        if (courseInp && courseInp.value.trim().length < 2) {
            v.showError(courseInp, 'Course Name is required.');
            if (!firstInvalid) firstInvalid = courseInp;
        }

        if (sidInp && sidInp.value.trim().length < 1) {
            v.showError(sidInp, 'Student ID Number is required.');
            if (!firstInvalid) firstInvalid = sidInp;
        }

        var amtVal = parseFloat(amountField.value) || 0;
        if (amtVal < 1000 || amtVal > 75000) {
            var targetInput = (customWrap.style.display !== 'none' && customInput) ? customInput : nameInp;
            v.showError(targetInput, 'Requested credit amount must be between ₹1,000 and ₹75,000.');
            if (!firstInvalid) firstInvalid = targetInput;
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

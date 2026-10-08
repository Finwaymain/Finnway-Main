@extends('finance.layouts.base')
@section('title', 'Business Details — Fiinway')
@section('header-sub', 'Business Loan')
@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 1 of 10</span><span>10%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:10%"></div></div>
</div>
@endsection
@section('back')
<a href="{{ route('finance.business_loan.s01_apply', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Business Details Form</h2>

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

    <form method="POST" action="{{ route('finance.business_loan.save_business_details') }}" id="businessForm">
        @csrf
        <input type="hidden" name="phone" value="{{ request('phone', $phone ?? '') }}">

        <div class="fw-form-group">
            <label>Business/Company Name <span style="color:red;">*</span></label>
            <input type="text" name="business_name" class="fw-input" placeholder="e.g. Acme Enterprises Pvt Ltd" value="{{ old('business_name', $application->applicant_details['business_name'] ?? ($customer->company_name ?? '')) }}" required>
        </div>
        <div class="fw-form-group">
            <label>Business Type <span style="color:red;">*</span></label>
            <select name="business_type" class="fw-input" required>
                @php $bType = old('business_type', $application->applicant_details['business_type'] ?? 'Proprietorship'); @endphp
                <option value="Proprietorship" {{ $bType === 'Proprietorship' ? 'selected' : '' }}>Proprietorship</option>
                <option value="Partnership" {{ $bType === 'Partnership' ? 'selected' : '' }}>Partnership</option>
                <option value="LLP" {{ $bType === 'LLP' ? 'selected' : '' }}>LLP</option>
                <option value="Pvt.Ltd" {{ $bType === 'Pvt.Ltd' ? 'selected' : '' }}>Pvt.Ltd</option>
                <option value="Public Ltd" {{ $bType === 'Public Ltd' ? 'selected' : '' }}>Public Ltd</option>
                <option value="Other" {{ $bType === 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div class="fw-form-group">
            <label>Owner/Director Name <span style="color:red;">*</span></label>
            <input type="text" name="owner_name" class="fw-input" placeholder="Full name as on PAN" value="{{ old('owner_name', $application->applicant_details['owner_name'] ?? ($customer->name ?? '')) }}" required>
        </div>
        <div class="fw-form-group">
            <label>Mobile Number <span style="color:red;">*</span></label>
            <input type="tel" name="mobile" class="fw-input" maxlength="10" placeholder="10-digit mobile number" value="{{ old('mobile', $application->applicant_details['mobile'] ?? ($customer->phone ?? request('phone'))) }}" required>
        </div>
        <div class="fw-form-group">
            <label>Business Email <span style="color:red;">*</span></label>
            <input type="email" name="email" class="fw-input" placeholder="e.g. director@company.com" value="{{ old('email', $application->applicant_details['email'] ?? ($customer->email ?? '')) }}" required>
        </div>
        <div class="fw-form-group">
            <label>PAN Card Number <span style="color:red;">*</span></label>
            <input type="text" name="pan_number" class="fw-input" maxlength="10" placeholder="e.g. ABCDE1234F" value="{{ old('pan_number', $application->pan_number ?? ($customer->pan ?? '')) }}" style="text-transform: uppercase;" required>
        </div>
        <div class="fw-form-group">
            <label>GST Number (Optional)</label>
            <input type="text" name="gst_number" class="fw-input" maxlength="15" placeholder="e.g. 22AAAAA0000A1Z5" value="{{ old('gst_number', $application->applicant_details['gst'] ?? '') }}" style="text-transform: uppercase;">
        </div>
        <div class="fw-form-group">
            <label>Business Address</label>
            <textarea name="business_address" class="fw-input" rows="2" placeholder="Registered office address">{{ old('business_address', $application->applicant_details['business_address'] ?? '') }}</textarea>
        </div>
        <div class="fw-form-group">
            <label>City</label>
            <input type="text" name="city" class="fw-input" placeholder="City" value="{{ old('city', $application->applicant_details['city'] ?? '') }}">
        </div>
        <div class="fw-form-group">
            <label>State</label>
            <input type="text" name="state" class="fw-input" placeholder="State" value="{{ old('state', $application->applicant_details['state'] ?? '') }}">
        </div>
        <div class="fw-form-group">
            <label>PIN Code</label>
            <input type="text" name="pincode" class="fw-input" maxlength="6" placeholder="6-digit PIN code" value="{{ old('pincode', $application->applicant_details['pincode'] ?? '') }}">
        </div>
        
        <div class="fw-form-group">
            <label>Business Vintage (Years)</label>
            <input type="number" name="business_vintage" class="fw-input" min="0" placeholder="Years in operation" value="{{ old('business_vintage', $application->applicant_details['vintage'] ?? '') }}">
        </div>
        <div class="fw-form-group">
            <label>Industry/Category</label>
            <input type="text" name="industry" class="fw-input" placeholder="e.g. Retail, Manufacturing, IT" value="{{ old('industry', $application->applicant_details['industry'] ?? '') }}">
        </div>
        <div class="fw-form-group">
            <label>Annual Turnover (₹) <span style="color:red;">*</span></label>
            <input type="number" name="annual_turnover" class="fw-input" min="50000" placeholder="Minimum ₹50,000" value="{{ old('annual_turnover', $application->applicant_details['turnover'] ?? '') }}" required>
        </div>
        <div class="fw-form-group">
            <label>Monthly Avg Turnover (₹)</label>
            <input type="number" name="monthly_turnover" class="fw-input" placeholder="e.g. ₹50,000" value="{{ old('monthly_turnover', $application->applicant_details['monthly_turnover'] ?? '') }}">
        </div>
        <div class="fw-form-group">
            <label>Existing Business Loan (₹)</label>
            <input type="number" name="existing_loan" class="fw-input" placeholder="0 if none" value="{{ old('existing_loan', $application->applicant_details['existing_loan'] ?? '') }}">
        </div>
        <div class="fw-form-group">
            <label>Existing EMI (₹)</label>
            <input type="number" name="existing_emi" class="fw-input" placeholder="0 if none" value="{{ old('existing_emi', $application->applicant_details['existing_emi'] ?? '') }}">
        </div>
        <div class="fw-form-group">
            <label>Number of Employees</label>
            <input type="number" name="employees_count" class="fw-input" placeholder="e.g. 5" value="{{ old('employees_count', $application->applicant_details['employees_count'] ?? '') }}">
        </div>
    </form>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="businessForm" class="fw-btn fw-btn-primary w-100">Continue</button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('businessForm');
    if (!form) return;

    var v = window.FiinwayValidator;

    var bNameInp = form.querySelector('input[name="business_name"]');
    var ownerInp = form.querySelector('input[name="owner_name"]');
    var mobileInp = form.querySelector('input[name="mobile"]');
    var emailInp = form.querySelector('input[name="email"]');
    var panInp = form.querySelector('input[name="pan_number"]');
    var gstInp = form.querySelector('input[name="gst_number"]');
    var pinInp = form.querySelector('input[name="pincode"]');
    var turnoverInp = form.querySelector('input[name="annual_turnover"]');

    if (panInp) v.setupLivePan(panInp);
    if (gstInp) v.setupLivePan(gstInp);
    if (mobileInp) v.setupLiveDigits(mobileInp, 10);
    if (pinInp) v.setupLiveDigits(pinInp, 6);

    form.addEventListener('submit', function(e) {
        var firstInvalid = null;

        [bNameInp, ownerInp, mobileInp, emailInp, panInp, gstInp, pinInp, turnoverInp].forEach(function(inp) {
            if (inp) v.clearError(inp);
        });

        if (bNameInp && bNameInp.value.trim().length < 3) {
            v.showError(bNameInp, 'Enter a valid Business/Company Name (min 3 characters).');
            if (!firstInvalid) firstInvalid = bNameInp;
        }

        if (ownerInp && !v.isValidName(ownerInp.value)) {
            v.showError(ownerInp, 'Enter a valid Owner/Director Name (min 3 characters, letters only).');
            if (!firstInvalid) firstInvalid = ownerInp;
        }

        if (mobileInp && !v.isValidPhone(mobileInp.value)) {
            v.showError(mobileInp, 'Enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.');
            if (!firstInvalid) firstInvalid = mobileInp;
        }

        if (emailInp && !v.isValidEmail(emailInp.value)) {
            v.showError(emailInp, 'Enter a valid email address (e.g. director@company.com).');
            if (!firstInvalid) firstInvalid = emailInp;
        }

        if (panInp && !v.isValidPan(panInp.value)) {
            v.showError(panInp, 'Enter a valid 10-character PAN number (e.g. ABCDE1234F).');
            if (!firstInvalid) firstInvalid = panInp;
        }

        if (gstInp && gstInp.value.trim().length > 0) {
            var gstVal = gstInp.value.trim().toUpperCase();
            var gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
            if (!gstRegex.test(gstVal)) {
                v.showError(gstInp, 'GSTIN format is invalid (15 alphanumeric characters).');
                if (!firstInvalid) firstInvalid = gstInp;
            }
        }

        if (pinInp && pinInp.value.trim().length > 0 && !v.isValidPin(pinInp.value)) {
            v.showError(pinInp, 'Enter a valid 6-digit PIN code.');
            if (!firstInvalid) firstInvalid = pinInp;
        }

        if (turnoverInp) {
            var tVal = parseFloat(turnoverInp.value) || 0;
            if (tVal < 50000) {
                v.showError(turnoverInp, 'Annual turnover must be at least ₹50,000 for a business loan.');
                if (!firstInvalid) firstInvalid = turnoverInp;
            }
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

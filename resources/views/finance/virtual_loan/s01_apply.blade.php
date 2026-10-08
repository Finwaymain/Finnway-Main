@extends('finance.layouts.base')
@section('title', 'Apply for Virtual Loan — Fiinway')
@section('header-sub', 'Virtual Loan')
@section('progress-label', 'Step 1 of 5')
@section('progress-pct', '20')
@section('progress') @endsection

@section('content')
<p class="fw-section-title">Virtual Loan Application</p>
<p class="fw-section-sub">Select your approved credit slab and fill basic details.</p>

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

<form id="virtualApplyForm" method="POST" action="{{ route('finance.virtual_loan.save_apply') }}">
    @csrf
    <input type="hidden" name="phone" value="{{ request('phone', $phone ?? '') }}">

    <div class="fw-card">
        <p class="fw-card-title">Select Loan Amount</p>
        @php $selAmt = old('loan_amount', $application->requested_amount ?? 15000); @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
            <label class="fw-tenure-chip {{ $selAmt == 15000 ? 'active' : '' }}" style="text-align:center;">
                <input type="radio" name="loan_amount" value="15000" style="display:none;" {{ $selAmt == 15000 ? 'checked' : '' }}>
                <div style="font-weight:700;font-size:16px;color:var(--navy);">₹15,000</div>
                <div style="font-size:11px;color:var(--gray3);">Fee: ₹2,000</div>
            </label>
            <label class="fw-tenure-chip {{ $selAmt == 20000 ? 'active' : '' }}" style="text-align:center;">
                <input type="radio" name="loan_amount" value="20000" style="display:none;" {{ $selAmt == 20000 ? 'checked' : '' }}>
                <div style="font-weight:700;font-size:16px;color:var(--navy);">₹20,000</div>
                <div style="font-size:11px;color:var(--gray3);">Fee: ₹3,000</div>
            </label>
            <label class="fw-tenure-chip {{ $selAmt == 30000 ? 'active' : '' }}" style="text-align:center;">
                <input type="radio" name="loan_amount" value="30000" style="display:none;" {{ $selAmt == 30000 ? 'checked' : '' }}>
                <div style="font-weight:700;font-size:16px;color:var(--navy);">₹30,000</div>
                <div style="font-size:11px;color:var(--gray3);">Fee: ₹3,000</div>
            </label>
            <label class="fw-tenure-chip {{ $selAmt == 35000 ? 'active' : '' }}" style="text-align:center;">
                <input type="radio" name="loan_amount" value="35000" style="display:none;" {{ $selAmt == 35000 ? 'checked' : '' }}>
                <div style="font-weight:700;font-size:16px;color:var(--navy);">₹35,000</div>
                <div style="font-size:11px;color:var(--gray3);">Fee: ₹4,000</div>
            </label>
        </div>
        <label class="fw-tenure-chip {{ $selAmt == 45000 ? 'active' : '' }}" style="display:block;text-align:center;">
            <input type="radio" name="loan_amount" value="45000" style="display:none;" {{ $selAmt == 45000 ? 'checked' : '' }}>
            <div style="font-weight:700;font-size:16px;color:var(--navy);">₹45,000</div>
            <div style="font-size:11px;color:var(--gray3);">Fee: ₹4,000</div>
        </label>
    </div>

    <div class="fw-card">
        <p class="fw-card-title">Applicant Information</p>

        <div class="fw-input-group">
            <label class="fw-label">Full Name (as on Aadhaar) <span style="color:red;">*</span></label>
            <input type="text" name="applicant_name" class="fw-input" placeholder="e.g. Ramesh Kumar" value="{{ old('applicant_name', $application->applicant_name ?? ($customer->name ?? '')) }}" required>
        </div>

        <div class="fw-input-group">
            <label class="fw-label">Mobile Number <span style="color:red;">*</span></label>
            <input type="tel" name="mobile" class="fw-input" maxlength="10" value="{{ old('mobile', $customer->phone ?? request('phone')) }}" placeholder="10-digit mobile number" required>
        </div>

        <div class="fw-input-group">
            <label class="fw-label">Aadhaar Card Number <span style="color:red;">*</span></label>
            <input type="text" name="aadhaar_number" class="fw-input" maxlength="12" placeholder="12-digit Aadhaar number" value="{{ old('aadhaar_number', $customer->aadhaar ?? '') }}" required>
        </div>

        <div class="fw-input-group">
            <label class="fw-label">PAN Card Number <span style="color:red;">*</span></label>
            <input type="text" name="pan_number" class="fw-input" maxlength="10" placeholder="10-character PAN" style="text-transform:uppercase;" value="{{ old('pan_number', $application->pan_number ?? ($customer->pan ?? '')) }}" required>
        </div>
    </div>

    <div class="fw-consent">
        <input type="checkbox" name="consent" id="consent" value="1" checked required>
        <label for="consent">I authorize Fiinway to verify my credit details for virtual credit activation.</label>
    </div>
</form>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="virtualApplyForm" class="fw-btn fw-btn-primary w-100">
        Proceed to KYC Upload →
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="loan_amount"]').forEach(function(r){
        r.addEventListener('change', function(){
            document.querySelectorAll('.fw-tenure-chip').forEach(function(c){ c.classList.remove('active'); });
            this.closest('.fw-tenure-chip').classList.add('active');
        });
    });

    var form = document.getElementById('virtualApplyForm');
    if (!form) return;

    var v = window.FiinwayValidator;
    var nameInp = form.querySelector('input[name="applicant_name"]');
    var mobileInp = form.querySelector('input[name="mobile"]');
    var aadhInp = form.querySelector('input[name="aadhaar_number"]');
    var panInp = form.querySelector('input[name="pan_number"]');

    if (mobileInp) v.setupLiveDigits(mobileInp, 10);
    if (aadhInp) v.setupLiveDigits(aadhInp, 12);
    if (panInp) v.setupLivePan(panInp);

    form.addEventListener('submit', function(e) {
        var firstInvalid = null;

        [nameInp, mobileInp, aadhInp, panInp].forEach(function(inp) {
            if (inp) v.clearError(inp);
        });

        if (nameInp && !v.isValidName(nameInp.value)) {
            v.showError(nameInp, 'Enter a valid applicant name (min 3 characters, letters only).');
            if (!firstInvalid) firstInvalid = nameInp;
        }

        if (mobileInp && !v.isValidPhone(mobileInp.value)) {
            v.showError(mobileInp, 'Enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.');
            if (!firstInvalid) firstInvalid = mobileInp;
        }

        if (aadhInp && !v.isValidAadhaar(aadhInp.value)) {
            v.showError(aadhInp, 'Enter a valid 12-digit Aadhaar number.');
            if (!firstInvalid) firstInvalid = aadhInp;
        }

        if (panInp && !v.isValidPan(panInp.value)) {
            v.showError(panInp, 'Enter a valid 10-character PAN number (e.g. ABCDE1234F).');
            if (!firstInvalid) firstInvalid = panInp;
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

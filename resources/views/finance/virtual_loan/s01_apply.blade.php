@extends('finance.layouts.base')
@section('title', 'Apply for Virtual Loan — Fiinway')
@section('header-sub', 'Virtual Loan')
@section('progress-label', 'Step 1 of 5')
@section('progress-pct', '20')
@section('progress') @endsection

@section('content')
<p class="fw-section-title">Virtual Loan Application</p>
<p class="fw-section-sub">Select your approved credit slab and fill basic details.</p>

<div class="fw-card">
    <p class="fw-card-title">Select Loan Amount</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
        <label class="fw-tenure-chip active" style="text-align:center;">
            <input type="radio" name="loan_amount" value="15000" style="display:none;" checked>
            <div style="font-weight:700;font-size:16px;color:var(--navy);">₹15,000</div>
            <div style="font-size:11px;color:var(--gray3);">Fee: ₹2,000</div>
        </label>
        <label class="fw-tenure-chip" style="text-align:center;">
            <input type="radio" name="loan_amount" value="20000" style="display:none;">
            <div style="font-weight:700;font-size:16px;color:var(--navy);">₹20,000</div>
            <div style="font-size:11px;color:var(--gray3);">Fee: ₹3,000</div>
        </label>
        <label class="fw-tenure-chip" style="text-align:center;">
            <input type="radio" name="loan_amount" value="30000" style="display:none;">
            <div style="font-weight:700;font-size:16px;color:var(--navy);">₹30,000</div>
            <div style="font-size:11px;color:var(--gray3);">Fee: ₹3,000</div>
        </label>
        <label class="fw-tenure-chip" style="text-align:center;">
            <input type="radio" name="loan_amount" value="35000" style="display:none;">
            <div style="font-weight:700;font-size:16px;color:var(--navy);">₹35,000</div>
            <div style="font-size:11px;color:var(--gray3);">Fee: ₹4,000</div>
        </label>
    </div>
    <label class="fw-tenure-chip" style="display:block;text-align:center;">
        <input type="radio" name="loan_amount" value="45000" style="display:none;">
        <div style="font-weight:700;font-size:16px;color:var(--navy);">₹45,000</div>
        <div style="font-size:11px;color:var(--gray3);">Fee: ₹4,000</div>
    </label>
</div>

<div class="fw-card">
    <p class="fw-card-title">Applicant Information</p>

    <div class="fw-input-group">
        <label class="fw-label">Full Name (as on Aadhaar)</label>
        <input type="text" class="fw-input" placeholder="e.g. Ramesh Kumar" value="{{ $customer->name ?? '' }}">
    </div>

    <div class="fw-input-group">
        <label class="fw-label">Mobile Number</label>
        <input type="tel" class="fw-input" maxlength="10" value="{{ request('phone') }}" placeholder="10-digit mobile number">
    </div>

    <div class="fw-input-group">
        <label class="fw-label">Aadhaar Card Number</label>
        <input type="text" class="fw-input" maxlength="12" placeholder="12-digit Aadhaar number">
    </div>

    <div class="fw-input-group">
        <label class="fw-label">PAN Card Number</label>
        <input type="text" class="fw-input" maxlength="10" placeholder="10-character PAN" style="text-transform:uppercase;">
    </div>
</div>

<div class="fw-consent">
    <input type="checkbox" id="consent" checked>
    <label for="consent">I authorize Fiinway to verify my credit details for virtual credit activation.</label>
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.virtual_loan.s02_kyc', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">Proceed to KYC Upload →</a>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[name="loan_amount"]').forEach(function(r){
    r.addEventListener('change', function(){
        document.querySelectorAll('.fw-tenure-chip').forEach(function(c){ c.classList.remove('active'); });
        this.closest('.fw-tenure-chip').classList.add('active');
    });
});
</script>
@endpush

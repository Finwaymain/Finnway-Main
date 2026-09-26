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

<div class="fw-card">
    <p class="fw-card-title">Personal Details</p>

    <div class="fw-input-group">
        <label class="fw-label">Full Name</label>
        <input type="text" class="fw-input" placeholder="As on Aadhaar">
    </div>

    <div class="fw-input-group">
        <label class="fw-label">Date of Birth</label>
        <input type="date" class="fw-input">
    </div>

    <div class="fw-input-group">
        <label class="fw-label">Mobile Number</label>
        <input type="tel" class="fw-input" maxlength="10"
               value="{{ request('phone') }}" placeholder="10-digit mobile">
    </div>

    <div class="fw-input-group">
        <label class="fw-label">Aadhaar Number</label>
        <input type="text" class="fw-input" maxlength="12" placeholder="12-digit Aadhaar">
    </div>
</div>

<div class="fw-card">
    <p class="fw-card-title">Student Details</p>

    <div class="fw-input-group">
        <label class="fw-label">Student Type</label>
        <div style="display:flex;gap:20px;padding:4px 0;">
            <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                <input type="radio" name="student_type" value="domestic"
                       style="accent-color:var(--blue2);" checked>
                Domestic
            </label>
            <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                <input type="radio" name="student_type" value="international"
                       style="accent-color:var(--blue2);">
                International
            </label>
        </div>
    </div>

    <div class="fw-input-group">
        <label class="fw-label">College / University Name</label>
        <input type="text" class="fw-input" placeholder="Full institution name">
    </div>

    <div class="fw-input-group">
        <label class="fw-label">Course Name</label>
        <input type="text" class="fw-input" placeholder="e.g. B.Tech, MBA, MBBS">
    </div>

    <div class="fw-input-group">
        <label class="fw-label">Student ID Number</label>
        <input type="text" class="fw-input" placeholder="As printed on ID card">
    </div>

    <div class="fw-input-group">
        <label class="fw-label">Student ID Expiry Date</label>
        <input type="date" class="fw-input">
    </div>
</div>

<div class="fw-card">
    <p class="fw-card-title">Credit Amount Required</p>

    <div class="fw-tenure-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:10px;">
        <label class="fw-tenure-chip">
            <input type="radio" name="credit_amount" value="10000" style="display:none;">
            ₹10,000
        </label>
        <label class="fw-tenure-chip">
            <input type="radio" name="credit_amount" value="25000" style="display:none;">
            ₹25,000
        </label>
        <label class="fw-tenure-chip">
            <input type="radio" name="credit_amount" value="50000" style="display:none;">
            ₹50,000
        </label>
        <label class="fw-tenure-chip">
            <input type="radio" name="credit_amount" value="75000" style="display:none;">
            ₹75,000
        </label>
    </div>

    <label class="fw-tenure-chip" style="width:100%;display:block;text-align:center;margin-bottom:10px;">
        <input type="radio" name="credit_amount" value="custom" style="display:none;">
        Custom Amount
    </label>

    <div id="custom-amount-wrap" style="display:none;">
        <div class="fw-input-group" style="margin-bottom:0;">
            <label class="fw-label">Enter Amount (₹)</label>
            <input type="number" class="fw-input" placeholder="Minimum ₹1,000" min="1000">
        </div>
    </div>
</div>

<div class="fw-consent">
    <input type="checkbox" id="consent">
    <label for="consent">I confirm all details are accurate and I am between 16–26 years of age. I authorise Fiinway to verify my student credentials.</label>
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.student_credit.s02_kyc', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">Continue →</a>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[name="credit_amount"]').forEach(function(r){
    r.addEventListener('change', function(){
        document.querySelectorAll('.fw-tenure-chip').forEach(function(c){ c.classList.remove('active'); });
        this.closest('.fw-tenure-chip').classList.add('active');
        document.getElementById('custom-amount-wrap').style.display =
            (this.value === 'custom') ? 'block' : 'none';
    });
});
</script>
@endpush

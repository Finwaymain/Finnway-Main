@extends('finance.layouts.base')
@section('title', 'Processing Fee — Virtual Loan')
@section('header-sub', 'Virtual Loan')
@section('progress-label', 'Step 3 of 5')
@section('progress-pct', '60')
@section('progress') @endsection

@section('back')
<a href="{{ route('finance.virtual_loan.s02_kyc', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<p class="fw-section-title">Loan Processing Fee</p>
<p class="fw-section-sub">Your loan limit is booked. Pay the processing fee to complete activation.</p>

<div class="fw-card">
    <p class="fw-card-title">Booked Loan Summary</p>

    <div class="fw-info-row">
        <span class="fw-info-label">Booked Loan Limit</span>
        <span class="fw-info-value" style="font-weight:700;color:var(--navy);">₹30,000</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Wallet Type</span>
        <span class="fw-info-value">Virtual Business Credit Wallet</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Status</span>
        <span class="fw-badge fw-badge-pending">Pending Fee Payment</span>
    </div>
</div>

<div class="fw-card">
    <p class="fw-card-title">Fee Summary</p>

    <div class="fw-amount-big" style="padding:14px 0 10px;">
        <p class="label">Amount Payable</p>
        <p class="amount">₹3,000</p>
    </div>

    <div class="fw-info-row">
        <span class="fw-info-label">Fee Description</span>
        <span class="fw-info-value">Loan Processing / Service Fee</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Taxes</span>
        <span class="fw-info-value">18% GST Included</span>
    </div>

    <div class="fw-alert fw-alert-info" style="margin-top:12px;margin-bottom:0;">
        ℹ️ This fee activates your credit limit. It is not deducted from your virtual loan balance.
    </div>
</div>

<div class="fw-card">
    <p class="fw-card-title">Select Payment Mode</p>
    <div style="display:flex;flex-direction:column;gap:10px;">
        <label style="display:flex;align-items:center;gap:12px;padding:10px;border:1.5px solid var(--blue2);border-radius:8px;cursor:pointer;background:#f8fbff;">
            <input type="radio" name="pay_mode" value="upi" checked style="accent-color:var(--blue2);">
            <span style="font-size:20px;">📱</span>
            <div>
                <p style="font-size:14px;font-weight:600;color:var(--navy);margin:0;">UPI (Instant)</p>
                <p style="font-size:11px;color:var(--gray3);margin:2px 0 0;">Google Pay, PhonePe, Paytm</p>
            </div>
        </label>
        <label style="display:flex;align-items:center;gap:12px;padding:10px;border:1.5px solid var(--gray2);border-radius:8px;cursor:pointer;">
            <input type="radio" name="pay_mode" value="card" style="accent-color:var(--blue2);">
            <span style="font-size:20px;">💳</span>
            <div>
                <p style="font-size:14px;font-weight:600;color:var(--navy);margin:0;">Debit Card / Net Banking</p>
                <p style="font-size:11px;color:var(--gray3);margin:2px 0 0;">All major Indian banks</p>
            </div>
        </label>
    </div>
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.virtual_loan.s04_pending', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-accent">Pay ₹3,000 &amp; Activate →</a>
@endsection

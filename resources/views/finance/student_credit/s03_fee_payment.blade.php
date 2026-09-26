@extends('finance.layouts.base')
@section('title', 'Processing Fee — Fiinway Student Credit')
@section('header-sub', 'Student Credit')
@section('progress-label', 'Step 3 of 8')
@section('progress-pct', '37')
@section('progress') @endsection

@section('back')
<a href="{{ route('finance.student_credit.s02_kyc', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<p class="fw-section-title">Processing Fee</p>
<p class="fw-section-sub">One-time fee to activate your student credit.</p>

{{-- Credit Summary --}}
<div class="fw-card">
    <p class="fw-card-title">Credit Summary</p>

    <div class="fw-info-row">
        <span class="fw-info-label">Requested Credit Amount</span>
        <span class="fw-info-value">₹{{ number_format($amount) }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Student Type</span>
        <span class="fw-info-value">{{ ucfirst($ctx['student_type'] ?? 'Domestic') }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">College / University</span>
        <span class="fw-info-value" style="text-align:right;max-width:60%;">
            {{ $ctx['college_name'] ?? 'Fiinway Academic Network' }}
        </span>
    </div>
</div>

{{-- Fee Breakup --}}
<div class="fw-card">
    <p class="fw-card-title">Credit Processing / Service Fee</p>

    <div class="fw-amount-big" style="padding:16px 0 10px;">
        <p class="label">Amount Due</p>
        <p class="amount">₹{{ number_format($totalFee, 2) }}</p>
    </div>

    <div class="fw-info-row">
        <span class="fw-info-label">Fee Breakdown</span>
        <span class="fw-info-value">Base: ₹{{ number_format($baseFee, 2) }} + 18% GST: ₹{{ number_format($feeTax, 2) }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Fee Type</span>
        <span class="fw-info-value">One-time, Non-refundable</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">GST / Taxes</span>
        <span class="fw-info-value">Included</span>
    </div>

    <div class="fw-alert fw-alert-info" style="margin-top:12px;margin-bottom:0;">
        ℹ️ This fee covers application processing and credit activation. It is not deducted from your credit balance.
    </div>
</div>

{{-- Payment Methods --}}
<div class="fw-card">
    <p class="fw-card-title">Pay Via</p>
    <div style="display:flex;flex-direction:column;gap:10px;">
        <label style="display:flex;align-items:center;gap:12px;padding:10px;border:1.5px solid var(--gray2);border-radius:8px;cursor:pointer;">
            <input type="radio" name="pay_method" value="upi" style="accent-color:var(--blue2);" checked>
            <span style="font-size:20px;">📱</span>
            <div>
                <p style="font-size:14px;font-weight:600;color:var(--navy);">UPI</p>
                <p style="font-size:11px;color:var(--gray3);">GPay, PhonePe, Paytm &amp; more</p>
            </div>
        </label>
        <label style="display:flex;align-items:center;gap:12px;padding:10px;border:1.5px solid var(--gray2);border-radius:8px;cursor:pointer;">
            <input type="radio" name="pay_method" value="card" style="accent-color:var(--blue2);">
            <span style="font-size:20px;">💳</span>
            <div>
                <p style="font-size:14px;font-weight:600;color:var(--navy);">Debit / Credit Card</p>
                <p style="font-size:11px;color:var(--gray3);">Visa, Mastercard, RuPay</p>
            </div>
        </label>
        <label style="display:flex;align-items:center;gap:12px;padding:10px;border:1.5px solid var(--gray2);border-radius:8px;cursor:pointer;">
            <input type="radio" name="pay_method" value="netbanking" style="accent-color:var(--blue2);">
            <span style="font-size:20px;">🏦</span>
            <div>
                <p style="font-size:14px;font-weight:600;color:var(--navy);">Net Banking</p>
                <p style="font-size:11px;color:var(--gray3);">All major banks</p>
            </div>
        </label>
    </div>
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.student_credit.s04_pending', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-accent">Pay Now &amp; Continue →</a>
@endsection

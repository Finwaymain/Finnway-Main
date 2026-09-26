@extends('finance.layouts.base')
@section('title', 'Processing Fee — Fiinway')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 4 of 6')
@section('progress-pct', '66')
@section('progress', ' ')

@section('back')
<a href="{{ route('finance.zero_cibil.s03_amount_select', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')

<p class="fw-section-title">Activation Fee</p>
<p class="fw-section-sub">Pay the one-time fee to activate your zero-interest credit line.</p>

{{-- Fee Breakdown --}}
<div class="fw-card">
    <div class="fw-card-title">Fee Breakdown</div>
    <div class="fw-info-row">
        <span class="fw-info-label">Requested Credit Limit</span>
        <span class="fw-info-value">₹{{ number_format($amount) }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Processing / Admin Fee</span>
        <span class="fw-info-value">₹{{ number_format($baseFee, 2) }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">GST (18%)</span>
        <span class="fw-info-value">₹{{ number_format($feeTax, 2) }}</span>
    </div>
    <div class="fw-info-row" style="margin-top:4px; padding-top:12px; border-top:2px solid var(--gray2); border-bottom:none">
        <span class="fw-info-label" style="font-weight:700; color:var(--navy)">Total Payable</span>
        <span class="fw-info-value" style="font-size:18px; color:var(--blue)">₹{{ number_format($totalFee, 2) }}</span>
    </div>
</div>

<div class="fw-alert fw-alert-info">
    🔒 Fee policy decided by Fiinway Admin. 100% transparent with zero hidden costs.
</div>

{{-- Payment methods note --}}
<div class="fw-card">
    <div class="fw-card-title">Payment Methods Supported</div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">UPI (Google Pay, PhonePe, Paytm, BHIM)</div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">Debit Card &amp; Net Banking</div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">Instant Payment Verification via Razorpay</div>
    </div>
</div>

<div class="fw-card" style="padding:14px;">
    <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
        <input type="checkbox" id="consent" required checked style="margin-top:3px; accent-color:var(--blue);">
        <span style="font-size:12px; color:var(--text); line-height:1.4;">
            I agree to pay the activation fee of ₹{{ number_format($totalFee, 2) }} to submit my application for underwriting approval.
        </span>
    </label>
</div>

@include('finance.partials.razorpay')

@endsection

@section('sticky-bottom')
<button type="button"
        onclick="initiatePayment()"
        class="fw-btn fw-btn-accent">
    Pay ₹{{ number_format($totalFee, 2) }} via Razorpay →
</button>

<script>
function initiatePayment() {
    var consentBox = document.getElementById('consent');
    if (consentBox && !consentBox.checked) {
        alert("Please confirm the fee declaration checkbox to proceed.");
        return;
    }

    triggerRazorpayCheckout({
        amount: {{ $totalFee }},
        phone: "{{ $phone }}",
        application_id: "{{ $application->id ?? '' }}",
        next_url: "{{ route('finance.zero_cibil.s05_pending', ['phone' => $phone]) }}"
    });
}
</script>
@endsection

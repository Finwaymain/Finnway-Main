@extends('finance.layouts.base')
@section('title', 'Processing Fee — Fiinway')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 3 of 6')
@section('progress-pct', '50')
@section('progress', ' ')

@section('back')
<a href="{{ route('finance.zero_cibil.s03_amount_select', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')

<p class="fw-section-title">Processing Fee</p>
<p class="fw-section-sub">Pay the one-time fee to activate your credit wallet.</p>

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
        <span class="fw-info-value" style="font-size:17px; color:var(--blue)">₹{{ number_format($totalFee, 2) }}</span>
    </div>
</div>

<div class="fw-alert fw-alert-info">
    The processing fee is set by Fiinway admin and transparently calculated. No hidden charges.
</div>

{{-- Payment methods note --}}
<div class="fw-card">
    <div class="fw-card-title">Payment Options</div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">UPI · Net Banking · Debit Card</div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">Instant confirmation after payment</div>
    </div>
</div>

<div class="fw-consent">
    <input type="checkbox" id="consent" required checked>
    <label for="consent">I agree to pay the processing fee of ₹{{ number_format($totalFee, 2) }} to activate my Zero-CIBIL credit wallet.</label>
</div>

@endsection

@section('sticky-bottom')
<a href="{{ route('finance.zero_cibil.s05_pending', ['phone' => request('phone'), 'amount' => $amount]) }}"
   class="fw-btn fw-btn-accent">Pay Now (₹{{ number_format($totalFee, 2) }}) →</a>
@endsection

@extends('finance.layouts.base')
@section('title', 'Activation Fee — Zero-CIBIL')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 5 of 6 · Activation')
@section('progress-pct', '83')
@section('progress', ' ')

@section('back')
<a href="{{ route('finance.zero_cibil.s03_amount_select', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-viewport-container">
    <div>
        {{-- Section Header --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <div>
                <p class="fw-section-title" style="font-size:18px; margin-bottom:2px;">Activation Fee</p>
                <p style="font-size:11px; color:var(--gray3); margin:0;">One-time fee to activate daily revolving credit</p>
            </div>
            <span class="fw-badge-fintech">✓ Pre-Approved</span>
        </div>

        {{-- Limit & Fee Summary Hero Card --}}
        <div class="fw-bank-hero" style="padding:14px 16px; margin-bottom:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="font-size:10px; color:#94a3b8; text-transform:uppercase; font-weight:700;">Pre-Approved Limit</span>
                    <div style="font-size:22px; font-weight:800; color:#ffffff;">₹{{ number_format($amount) }}</div>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:10px; color:#94a3b8; text-transform:uppercase; font-weight:700;">Total Fee Payable</span>
                    <div style="font-size:22px; font-weight:800; color:#00e599;">₹{{ number_format($totalFee, 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Compact Fee Breakdown Card --}}
        <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:8px;">
            <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:6px;">
                Transparent Fee Breakdown
            </div>
            <div class="fw-info-row" style="padding:4px 0; font-size:12px;">
                <span class="fw-info-label">Processing / Underwriting Fee</span>
                <span class="fw-info-value" style="font-size:12px;">₹{{ number_format($baseFee, 2) }}</span>
            </div>
            <div class="fw-info-row" style="padding:4px 0; font-size:12px;">
                <span class="fw-info-label">GST (18% Govt. Tax)</span>
                <span class="fw-info-value" style="font-size:12px;">₹{{ number_format($feeTax, 2) }}</span>
            </div>
            <div class="fw-info-row" style="padding:6px 0 0; margin-top:4px; border-top:1px dashed #cbd5e1; font-size:13px;">
                <span class="fw-info-label" style="font-weight:700; color:var(--navy);">Net Payable via Gateway</span>
                <span class="fw-info-value" style="font-size:14px; color:var(--blue);">₹{{ number_format($totalFee, 2) }}</span>
            </div>
        </div>

        {{-- Payment Methods Accepted --}}
        <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:8px;">
            <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:4px;">
                Instant Supported Channels
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px; color:#475569;">
                <span>🟢 UPI (GPay, PhonePe, Paytm, BHIM)</span>
                <span>💳 Cards &amp; NetBanking</span>
            </div>
        </div>

        {{-- Consent Declaration --}}
        <div class="fw-bank-card" style="padding:8px 12px; margin-bottom:8px; background:#f8fafc;">
            <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                <input type="checkbox" id="feeConsent" required checked style="margin-top:2px; accent-color:var(--blue);">
                <span style="font-size:11px; color:var(--text); line-height:1.3;">
                    I confirm payment of ₹{{ number_format($totalFee, 2) }} to unlock my daily 0% interest credit line.
                </span>
            </label>
        </div>
    </div>

    {{-- Bottom Action (Single Viewport Submission) --}}
    <div style="padding-top:4px;">
        <button type="button" onclick="handlePayFee()" class="fw-btn fw-btn-accent" style="padding:11px 14px; font-size:14px; font-weight:700;">
            Pay ₹{{ number_format($totalFee, 2) }} via Razorpay →
        </button>
    </div>
</div>

@include('finance.partials.razorpay')

<script>
function handlePayFee() {
    var consent = document.getElementById('feeConsent');
    if (consent && !consent.checked) {
        alert("Please confirm the fee declaration to proceed.");
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

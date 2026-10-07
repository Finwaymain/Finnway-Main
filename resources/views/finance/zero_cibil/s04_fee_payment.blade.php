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
        {{-- Section Header with Pre-Approval Badge --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <div>
                <p class="fw-section-title" style="font-size:18px; margin-bottom:2px;">Activation &amp; Processing Fee</p>
                <p style="font-size:11px; color:var(--gray3); margin:0;">One-time fee to activate daily revolving credit</p>
            </div>
            <span class="fw-badge-fintech">✓ Credit Limit Approved</span>
        </div>

        {{-- Limit & Fee Summary Hero Card --}}
        <div class="fw-bank-hero" style="padding:14px 16px; margin-bottom:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="font-size:10px; color:#94a3b8; text-transform:uppercase; font-weight:700;">Approved Credit Limit</span>
                    <div style="font-size:22px; font-weight:800; color:#ffffff;">₹{{ number_format($amount) }}</div>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:10px; color:#94a3b8; text-transform:uppercase; font-weight:700;">Total Payable Fee</span>
                    <div style="font-size:22px; font-weight:800; color:#00e599;">₹{{ number_format($totalFee, 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Itemized 5-Service Fee Breakdown + GST (User Req 3) --}}
        <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:8px;">
            <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:6px; display:flex; justify-content:space-between;">
                <span>Itemized Service Fee Structure</span>
                <span style="font-size:10px; color:var(--gray3);">100% Transparent</span>
            </div>

            @php
                $itemized = $itemizedFees ?? [];
            @endphp

            <div class="fw-info-row" style="padding:3px 0; font-size:11px;">
                <span class="fw-info-label">Application / Processing Service Fee</span>
                <span class="fw-info-value" style="font-size:12px;">₹{{ number_format($itemized['processing'] ?? ($baseFee * 0.2564), 2) }}</span>
            </div>
            <div class="fw-info-row" style="padding:3px 0; font-size:11px;">
                <span class="fw-info-label">KYC &amp; Verification Service Fee</span>
                <span class="fw-info-value" style="font-size:12px;">₹{{ number_format($itemized['verification'] ?? ($baseFee * 0.2051), 2) }}</span>
            </div>
            <div class="fw-info-row" style="padding:3px 0; font-size:11px;">
                <span class="fw-info-label">Platform &amp; Service Fee</span>
                <span class="fw-info-value" style="font-size:12px;">₹{{ number_format($itemized['platform'] ?? ($baseFee * 0.2393), 2) }}</span>
            </div>
            <div class="fw-info-row" style="padding:3px 0; font-size:11px;">
                <span class="fw-info-label">Documentation / Agreement Service Fee</span>
                <span class="fw-info-value" style="font-size:12px;">₹{{ number_format($itemized['agreement'] ?? ($baseFee * 0.1709), 2) }}</span>
            </div>
            <div class="fw-info-row" style="padding:3px 0; font-size:11px;">
                <span class="fw-info-label">Credit Report Monitoring</span>
                <span class="fw-info-value" style="font-size:12px;">₹{{ number_format($itemized['monitoring'] ?? ($baseFee * 0.1283), 2) }}</span>
            </div>
            <div class="fw-info-row" style="padding:3px 0; font-size:11px;">
                <span class="fw-info-label">GST @ 18%</span>
                <span class="fw-info-value" style="font-size:12px; color:var(--navy);">₹{{ number_format($feeTax, 2) }}</span>
            </div>

            <div class="fw-info-row" style="padding:6px 0 0; margin-top:4px; border-top:1px dashed #cbd5e1; font-size:13px;">
                <span class="fw-info-label" style="font-weight:700; color:var(--navy);">Total Net Payable</span>
                <span class="fw-info-value" style="font-size:15px; font-weight:800; color:var(--blue);">₹{{ number_format($totalFee, 2) }}</span>
            </div>
        </div>

        {{-- Payment Methods Accepted --}}
        <div class="fw-bank-card" style="padding:8px 12px; margin-bottom:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px; color:#475569;">
                <span>🟢 UPI (GPay, PhonePe, Paytm, BHIM)</span>
                <span>💳 Debit / NetBanking</span>
            </div>
        </div>

        {{-- Consent Declaration --}}
        <div class="fw-bank-card" style="padding:8px 12px; margin-bottom:8px; background:#f8fafc;">
            <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                <input type="checkbox" id="feeConsent" required checked style="margin-top:2px; accent-color:var(--blue);">
                <span style="font-size:11px; color:var(--text); line-height:1.3;">
                    I agree to pay the activation fee of ₹{{ number_format($totalFee, 2) }} to disburse my daily revolving credit line.
                </span>
            </label>
        </div>
    </div>

    {{-- Bottom Action (Fixed height & safe bottom spacing) --}}
    <div style="padding:14px 0 32px; margin-bottom:20px;">
        <button type="button" onclick="handlePayFee()" class="fw-btn fw-btn-accent" style="min-height:52px; height:52px; font-size:15px; font-weight:800; border-radius:12px; box-shadow:0 4px 14px rgba(245,166,35,0.3);">
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

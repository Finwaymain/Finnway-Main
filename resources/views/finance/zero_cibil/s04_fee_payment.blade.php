@extends('finance.layouts.base')
@section('title', 'Activation Fee — Zero-CIBIL')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 5 of 6 · Activation')
@section('progress-pct', '83')
@section('progress', ' ')

@section('content')
<div class="fw-viewport-container" style="padding-bottom:88px;">
    <div>
        {{-- Section Header with Pre-Approval Badge --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <div>
                <p class="fw-section-title" style="font-size:18px; margin-bottom:2px;">Activation &amp; Processing Fee</p>
                <p style="font-size:11px; color:var(--gray3); margin:0;">One-time fee to activate daily revolving credit</p>
            </div>
            <span class="fw-badge-fintech">✓ Credit Limit Approved</span>
        </div>

        {{-- Limit Summary Hero Card (No Fee in Top Card, .00 on Loan Amount - User Req 1) --}}
        <div class="fw-bank-hero" style="padding:16px 18px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; border:1px solid rgba(255,255,255,0.12);">
            <div>
                <span style="font-size:10px; color:#94a3b8; text-transform:uppercase; font-weight:700; letter-spacing:0.8px;">Approved Credit Limit</span>
                <div style="font-size:24px; font-weight:800; color:#ffffff; margin-top:2px; letter-spacing:-0.5px;">₹{{ number_format($amount, 2) }}</div>
            </div>
            <div style="text-align:right;">
                <span class="fw-badge fw-badge-green" style="font-size:11px; font-weight:700; padding:5px 10px;">
                    ● Limit Approved
                </span>
            </div>
        </div>

        {{-- Itemized 5-Service Fee Breakdown + GST (Zero Decimals on Fees - User Req 1) --}}
        <div class="fw-bank-card" style="padding:12px 14px; margin-bottom:10px;">
            <div style="font-size:12px; font-weight:700; color:var(--navy); margin-bottom:8px; display:flex; justify-content:space-between;">
                <span>Itemized Service Fee Structure</span>
                <span style="font-size:10px; color:var(--gray3);">100% Transparent</span>
            </div>

            @php
                $itemized = $itemizedFees ?? [];
            @endphp

            <div class="fw-info-row" style="padding:4px 0; font-size:12px;">
                <span class="fw-info-label">Application / Processing Service Fee</span>
                <span class="fw-info-value" style="font-size:13px; font-weight:600;">₹{{ number_format(round($itemized['processing'] ?? ($baseFee * 0.2564)), 0) }}</span>
            </div>
            <div class="fw-info-row" style="padding:4px 0; font-size:12px;">
                <span class="fw-info-label">KYC &amp; Verification Service Fee</span>
                <span class="fw-info-value" style="font-size:13px; font-weight:600;">₹{{ number_format(round($itemized['verification'] ?? ($baseFee * 0.2051)), 0) }}</span>
            </div>
            <div class="fw-info-row" style="padding:4px 0; font-size:12px;">
                <span class="fw-info-label">Platform &amp; Service Fee</span>
                <span class="fw-info-value" style="font-size:13px; font-weight:600;">₹{{ number_format(round($itemized['platform'] ?? ($baseFee * 0.2393)), 0) }}</span>
            </div>
            <div class="fw-info-row" style="padding:4px 0; font-size:12px;">
                <span class="fw-info-label">Documentation / Agreement Service Fee</span>
                <span class="fw-info-value" style="font-size:13px; font-weight:600;">₹{{ number_format(round($itemized['agreement'] ?? ($baseFee * 0.1709)), 0) }}</span>
            </div>
            <div class="fw-info-row" style="padding:4px 0; font-size:12px;">
                <span class="fw-info-label">Credit Report Monitoring</span>
                <span class="fw-info-value" style="font-size:13px; font-weight:600;">₹{{ number_format(round($itemized['monitoring'] ?? ($baseFee * 0.1283)), 0) }}</span>
            </div>
            <div class="fw-info-row" style="padding:4px 0; font-size:12px;">
                <span class="fw-info-label">GST @ 18%</span>
                <span class="fw-info-value" style="font-size:13px; font-weight:600; color:var(--navy);">₹{{ number_format(round($feeTax), 0) }}</span>
            </div>

            <div class="fw-info-row" style="padding:8px 0 0; margin-top:6px; border-top:1px dashed #cbd5e1; font-size:14px;">
                <span class="fw-info-label" style="font-weight:700; color:var(--navy);">Total Net Payable</span>
                <span class="fw-info-value" style="font-size:17px; font-weight:800; color:var(--blue);">₹{{ number_format(round($totalFee), 0) }}</span>
            </div>
        </div>

        {{-- Payment Methods Accepted --}}
        <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px; color:#475569;">
                <span>🟢 UPI (GPay, PhonePe, Paytm, BHIM)</span>
                <span>💳 Debit / NetBanking</span>
            </div>
        </div>

        {{-- Consent Declaration --}}
        <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:12px; background:#f8fafc;">
            <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                <input type="checkbox" id="feeConsent" required checked style="margin-top:2px; accent-color:var(--blue);">
                <span style="font-size:11px; color:var(--text); line-height:1.4;">
                    I agree to pay the activation fee of ₹{{ number_format(round($totalFee), 0) }} to disburse my daily revolving credit line.
                </span>
            </label>
        </div>
    </div>

    {{-- Bottom Action (Fixed height & safe bottom spacing) --}}
    <div style="padding-top:4px;">
        <button type="button" onclick="handlePayFee()" class="fw-btn fw-btn-accent" style="min-height:52px; font-size:15px; font-weight:800; border-radius:12px; box-shadow:0 4px 14px rgba(245,166,35,0.3);">
            Pay ₹{{ number_format(round($totalFee), 0) }} via Razorpay →
        </button>
    </div>
</div>

@include('finance.partials.razorpay')

<script>
// Prevent backward navigation once approved (User Req 2)
history.pushState(null, null, location.href);
window.onpopstate = function () {
    history.go(1);
};

function handlePayFee() {
    var consent = document.getElementById('feeConsent');
    if (consent && !consent.checked) {
        alert("Please confirm the fee declaration to proceed.");
        return;
    }

    triggerRazorpayCheckout({
        amount: {{ round($totalFee) }},
        phone: "{{ $phone }}",
        application_id: "{{ $application->id ?? '' }}",
        next_url: "{{ route('finance.zero_cibil.s05_pending', ['phone' => $phone]) }}"
    });
}
</script>
@endsection

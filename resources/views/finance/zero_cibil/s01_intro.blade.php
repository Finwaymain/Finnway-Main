@extends('finance.layouts.base')
@section('title', 'Zero-CIBIL Daily Credit — Fiinway')
@section('header-sub', 'Zero-CIBIL Credit')

@section('content')
<div class="fw-viewport-container">
    <div>
        {{-- Fintech Institutional Hero Card --}}
        <div class="fw-bank-hero" style="margin-bottom:10px; padding:14px 16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:#94a3b8;">
                    Institutional Daily Credit
                </span>
                <span class="fw-badge-fintech">✓ Instant Sanction</span>
            </div>
            <div style="font-size:24px; font-weight:800; letter-spacing:-0.4px; color:#ffffff; margin-bottom:2px;">
                ₹20,000 – ₹2,00,000
            </div>
            <div style="font-size:11px; color:#94a3b8; margin-bottom:10px;">Pre-approved credit line without bureau scoring</div>
            
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <span style="background:rgba(0,229,153,0.15); color:#00e599; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px; border:1px solid rgba(0,229,153,0.3);">
                    0% Interest
                </span>
                <span style="background:rgba(25,118,210,0.18); color:#90caf9; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px; border:1px solid rgba(25,118,210,0.3);">
                    Daily Micro-EMI
                </span>
                <span style="background:rgba(255,184,0,0.15); color:#ffb800; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px; border:1px solid rgba(255,184,0,0.3);">
                    No CIBIL Required
                </span>
            </div>
        </div>

        {{-- 2x2 Feature Grid (Compact Fintech Cards) --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px;">
            <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:0;">
                <div style="font-size:18px; margin-bottom:2px;">⚡</div>
                <div style="font-size:12px; font-weight:700; color:var(--navy);">Instant Sanction</div>
                <div style="font-size:10px; color:var(--gray3); line-height:1.3;">Aadhaar &amp; PAN verified in 2 mins</div>
            </div>
            <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:0;">
                <div style="font-size:18px; margin-bottom:2px;">💳</div>
                <div style="font-size:12px; font-weight:700; color:var(--navy);">Daily Credit Wallet</div>
                <div style="font-size:10px; color:var(--gray3); line-height:1.3;">Spend up to ₹5,000 daily limit</div>
            </div>
            <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:0;">
                <div style="font-size:18px; margin-bottom:2px;">📱</div>
                <div style="font-size:12px; font-weight:700; color:var(--navy);">QR Merchant Pay</div>
                <div style="font-size:10px; color:var(--gray3); line-height:1.3;">Scan any UPI/Merchant QR</div>
            </div>
            <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:0;">
                <div style="font-size:18px; margin-bottom:2px;">🛡️</div>
                <div style="font-size:12px; font-weight:700; color:var(--navy);">Zero Hidden Markups</div>
                <div style="font-size:10px; color:var(--gray3); line-height:1.3;">100% transparent fee policy</div>
            </div>
        </div>

        {{-- Compact Criteria Strip --}}
        <div style="background:#f1f5f9; border-radius:8px; padding:8px 12px; display:flex; align-items:center; gap:8px; font-size:11px; color:#475569;">
            <span style="font-size:14px;">🇮🇳</span>
            <span>Indian citizen · Age 21–60 · Valid Aadhaar &amp; PAN</span>
        </div>
    </div>

    {{-- Bottom Action Area with Stage Transition Loader --}}
    <div style="padding-top:10px;">
        <button type="button" onclick="startZeroCibilApply()" class="fw-btn fw-btn-primary" style="padding:12px 14px; font-size:15px; font-weight:700;">
            Apply for Credit Limit →
        </button>
    </div>
</div>

<script>
function startZeroCibilApply() {
    window.showBankingStageLoader(
        "Initializing Application",
        "Configuring secure zero-CIBIL verification session...",
        20,
        function() {
            window.location.href = "{{ route('finance.zero_cibil.s02_kyc', ['phone' => request('phone')]) }}";
        }
    );
}
</script>
@endsection

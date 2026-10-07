@extends('finance.layouts.base')
@section('title', 'Scan & Pay — Zero-CIBIL')
@section('header-sub', 'Daily Merchant Pay')

@section('back')
<a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}" class="fw-back">← Wallet</a>
@endsection

@section('content')
<div class="fw-viewport-container" style="padding-bottom:88px;">
    <div>
        @php
            $creditAmount = isset($wallet->approved_limit) ? $wallet->approved_limit : ($amount ?? 65000);
            if ($creditAmount > 84000) $creditAmount = 84000;
            if ($creditAmount < 15000) $creditAmount = 15000;

            if ($creditAmount <= 15000) {
                $dailyQuota = 1000;
            } elseif ($creditAmount <= 24000) {
                $dailyQuota = 2000;
            } elseif ($creditAmount <= 65000) {
                $dailyQuota = 3000;
            } else {
                $dailyQuota = 4000;
            }
        @endphp

        {{-- Section Title --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
                <p class="fw-section-title" style="font-size:18px; margin-bottom:2px;">Scan Merchant QR</p>
                <p style="font-size:11px; color:var(--gray3); margin:0;">Pay merchants from daily 0% interest card limit</p>
            </div>
            <span class="fw-badge-fintech" style="font-size:11px; font-weight:700;">Daily Quota: ₹{{ number_format($dailyQuota) }}</span>
        </div>

        {{-- QR Viewfinder --}}
        <div class="fw-bank-card" style="text-align:center; padding:16px 12px; margin-bottom:12px;">
            <div style="width:140px; height:140px; margin:0 auto 8px; border:2px dashed var(--blue2); border-radius:14px; background:#f8fafc; display:flex; flex-direction:column; align-items:center; justify-content:center; position:relative;">
                <span style="font-size:42px;">📷</span>
                <span style="font-size:10px; color:var(--gray3); margin-top:4px; font-weight:600;">Scan QR Code</span>
                <div style="position:absolute; top:8px; left:8px; width:14px; height:14px; border-top:2px solid var(--blue2); border-left:2px solid var(--blue2);"></div>
                <div style="position:absolute; top:8px; right:8px; width:14px; height:14px; border-top:2px solid var(--blue2); border-right:2px solid var(--blue2);"></div>
                <div style="position:absolute; bottom:8px; left:8px; width:14px; height:14px; border-bottom:2px solid var(--blue2); border-left:2px solid var(--blue2);"></div>
                <div style="position:absolute; bottom:8px; right:8px; width:14px; height:14px; border-bottom:2px solid var(--blue2); border-right:2px solid var(--blue2);"></div>
            </div>
            <span style="font-size:11px; color:var(--gray3);">Point camera at any BharatQR / Merchant UPI</span>
        </div>

        {{-- Payment Input & Source Card --}}
        <div class="fw-bank-card" style="padding:14px; margin-bottom:12px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                <div>
                    <label class="fw-label" style="font-size:11px; margin-bottom:4px;">Merchant Code / UPI</label>
                    <input type="text" class="fw-input" id="merchant_code" value="STORE_PARTNER_88@fiinway"
                           style="padding:9px 10px; font-size:12px;">
                </div>
                <div>
                    <label class="fw-label" style="font-size:11px; margin-bottom:4px;">Amount (₹)</label>
                    <input type="number" class="fw-input" id="pay_amt" value="1200" min="50" max="{{ $dailyQuota }}"
                           style="padding:9px 10px; font-size:13px; font-weight:800;">
                </div>
            </div>

            {{-- Payment Source Selector --}}
            <div style="margin-top:6px;">
                <label class="fw-label" style="font-size:11px; margin-bottom:4px;">Select Payment Source</label>
                <select id="payment_source" class="fw-input" style="padding:10px; font-size:12px; font-weight:700; color:var(--navy); background:#ffffff;">
                    <option value="zero_cibil_card" selected>💳 Zero-CIBIL Daily Card (Quota: ₹{{ number_format($dailyQuota) }})</option>
                    <option value="virtual_credit_card">💳 Virtual Credit Card</option>
                    <option value="fiinway_wallet">👛 Fiinway Direct Wallet</option>
                </select>
            </div>

            <div class="fw-info-row" style="padding:8px 0 0; margin-top:8px; font-size:11px;">
                <span class="fw-info-label">Transaction Fee</span>
                <span class="fw-info-value" style="color:#10b981; font-weight:700;">₹0 (Free / Zero Charge)</span>
            </div>
        </div>
    </div>

    {{-- Bottom Action --}}
    <div style="padding-top:8px;">
        <button type="button" onclick="confirmZeroPay()" class="fw-btn fw-btn-accent" style="min-height:52px; font-size:15px; font-weight:800; border-radius:12px;">
            Confirm &amp; Pay via Selected Source →
        </button>
    </div>
</div>

<div id="payment-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,27,45,0.85); z-index:999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:16px; padding:24px; text-align:center; max-width:320px; width:100%;">
        <div style="width:52px; height:52px; background:#eafaf1; color:var(--green); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:28px; margin-bottom:12px;">✓</div>
        <h3 style="font-size:18px; font-weight:800; color:var(--navy); margin-bottom:4px;">Payment Successful!</h3>
        <p style="font-size:13px; color:var(--gray3); margin-bottom:16px;" id="modal-pay-msg">Payment completed successfully.</p>
        <a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="min-height:48px; font-size:14px; font-weight:800; border-radius:10px; display:flex; align-items:center; justify-content:center;">
            Back to Wallet & Card
        </a>
    </div>
</div>

<script>
function confirmZeroPay() {
    var amt = document.getElementById('pay_amt').value || 1200;
    var merchant = document.getElementById('merchant_code').value;
    var sourceSelect = document.getElementById('payment_source');
    var sourceName = sourceSelect.options[sourceSelect.selectedIndex].text.split('(')[0].trim();

    window.showBankingStageLoader(
        "Processing Merchant Payment",
        "Authorizing debit from " + sourceName + " ledger...",
        10,
        function() {
            document.getElementById('modal-pay-msg').textContent = '₹' + amt + ' paid to ' + merchant + ' via ' + sourceName;
            document.getElementById('payment-modal').style.display = 'flex';
        }
    );
}
</script>
@endsection

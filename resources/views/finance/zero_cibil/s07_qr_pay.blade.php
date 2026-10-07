@extends('finance.layouts.base')
@section('title', 'Scan & Pay — Zero-CIBIL')
@section('header-sub', 'Daily Merchant Pay')

@section('back')
<a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}" class="fw-back">← Wallet</a>
@endsection

@section('content')
<div class="fw-viewport-container">
    <div>
        {{-- Section Title --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <div>
                <p class="fw-section-title" style="font-size:18px; margin-bottom:2px;">Scan Merchant QR</p>
                <p style="font-size:11px; color:var(--gray3); margin:0;">Pay merchants from daily 0% interest wallet</p>
            </div>
            <span class="fw-badge-fintech">Daily Limit: ₹5,000</span>
        </div>

        {{-- QR Viewfinder (Compact) --}}
        <div class="fw-bank-card" style="text-align:center; padding:12px 10px; margin-bottom:8px;">
            <div style="width:130px; height:130px; margin:0 auto 6px; border:2px dashed var(--blue2); border-radius:12px; background:#f8fafc; display:flex; flex-direction:column; align-items:center; justify-content:center; position:relative;">
                <span style="font-size:36px;">📷</span>
                <span style="font-size:10px; color:var(--gray3); margin-top:2px;">Scan QR Code</span>
                <div style="position:absolute; top:6px; left:6px; width:12px; height:12px; border-top:2px solid var(--blue2); border-left:2px solid var(--blue2);"></div>
                <div style="position:absolute; top:6px; right:6px; width:12px; height:12px; border-top:2px solid var(--blue2); border-right:2px solid var(--blue2);"></div>
                <div style="position:absolute; bottom:6px; left:6px; width:12px; height:12px; border-bottom:2px solid var(--blue2); border-left:2px solid var(--blue2);"></div>
                <div style="position:absolute; bottom:6px; right:6px; width:12px; height:12px; border-bottom:2px solid var(--blue2); border-right:2px solid var(--blue2);"></div>
            </div>
            <span style="font-size:10px; color:var(--gray3);">Point camera at any BharatQR / Merchant UPI</span>
        </div>

        {{-- Payment Input Card --}}
        <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:8px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <div>
                    <label class="fw-label" style="font-size:10px; margin-bottom:2px;">Merchant Code / UPI</label>
                    <input type="text" class="fw-input" id="merchant_code" value="STORE_PARTNER_88@fiinway"
                           style="padding:8px 10px; font-size:12px;">
                </div>
                <div>
                    <label class="fw-label" style="font-size:10px; margin-bottom:2px;">Amount (₹)</label>
                    <input type="number" class="fw-input" id="pay_amt" value="1200" min="50" max="5000"
                           style="padding:8px 10px; font-size:12px; font-weight:700;">
                </div>
            </div>

            <div class="fw-info-row" style="padding:6px 0 0; margin-top:4px; font-size:11px;">
                <span class="fw-info-label">Payment Source</span>
                <span class="fw-info-value" style="color:var(--green); font-size:11px;">Zero-CIBIL Credit Wallet</span>
            </div>
        </div>
    </div>

    {{-- Bottom Action (Single Viewport) --}}
    <div style="padding-top:4px;">
        <button type="button" onclick="confirmZeroPay()" class="fw-btn fw-btn-accent" style="padding:11px 14px; font-size:14px; font-weight:700;">
            Confirm &amp; Pay via Wallet →
        </button>
    </div>
</div>

<div id="payment-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,27,45,0.85); z-index:999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:14px; padding:22px; text-align:center; max-width:300px; width:100%;">
        <div style="width:50px; height:50px; background:#eafaf1; color:var(--green); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:26px; margin-bottom:10px;">✓</div>
        <h3 style="font-size:17px; font-weight:700; color:var(--navy); margin-bottom:4px;">Payment Successful!</h3>
        <p style="font-size:12px; color:var(--gray3); margin-bottom:14px;" id="modal-pay-msg">Payment completed successfully.</p>
        <a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="padding:10px; font-size:13px;">Back to Wallet</a>
    </div>
</div>

<script>
function confirmZeroPay() {
    var amt = document.getElementById('pay_amt').value || 1200;
    var merchant = document.getElementById('merchant_code').value;

    window.showBankingStageLoader(
        "Processing Merchant Payment",
        "Authorizing debit from Zero-CIBIL revolving credit ledger...",
        20,
        function() {
            document.getElementById('modal-pay-msg').textContent = '₹' + amt + ' paid to ' + merchant;
            document.getElementById('payment-modal').style.display = 'flex';
        }
    );
}
</script>
@endsection

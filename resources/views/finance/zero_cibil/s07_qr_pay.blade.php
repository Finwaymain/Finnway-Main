@extends('finance.layouts.base')
@section('title', 'Scan & Pay — Zero-CIBIL Loan')
@section('header-sub', 'Zero-CIBIL Daily Loan')

@section('back')
<a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}" class="fw-back">← Wallet</a>
@endsection

@section('content')
<p class="fw-section-title">Scan Merchant QR</p>
<p class="fw-section-sub">Pay eligible merchants using your daily 0% interest credit line.</p>

<div class="fw-card" style="text-align:center;padding:24px 16px;">
    <div style="width:200px;height:200px;margin:0 auto 16px;border:2px dashed var(--blue2);border-radius:12px;background:#f8fafc;display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;">
        <span style="font-size:48px;">📷</span>
        <p style="font-size:12px;color:var(--gray3);margin-top:8px;">Scan Merchant QR</p>
        <div style="position:absolute;top:10px;left:10px;width:16px;height:16px;border-top:3px solid var(--blue2);border-left:3px solid var(--blue2);"></div>
        <div style="position:absolute;top:10px;right:10px;width:16px;height:16px;border-top:3px solid var(--blue2);border-right:3px solid var(--blue2);"></div>
        <div style="position:absolute;bottom:10px;left:10px;width:16px;height:16px;border-bottom:3px solid var(--blue2);border-left:3px solid var(--blue2);"></div>
        <div style="position:absolute;bottom:10px;right:10px;width:16px;height:16px;border-bottom:3px solid var(--blue2);border-right:3px solid var(--blue2);"></div>
    </div>

    <p style="font-size:12px;color:var(--gray3);">Or manually enter merchant ID</p>
</div>

<div class="fw-card">
    <p class="fw-card-title">Payment Information</p>

    <div class="fw-input-group">
        <label class="fw-label">Merchant Code / UPI ID</label>
        <input type="text" class="fw-input" id="merchant_code" value="STORE_PARTNER_88@fiinway">
    </div>

    <div class="fw-input-group">
        <label class="fw-label">Amount (₹)</label>
        <input type="number" class="fw-input" id="pay_amt" value="1200" min="50" max="5000">
        <p style="font-size:11px;color:var(--gray3);margin-top:4px;">Today's available limit: ₹5,000</p>
    </div>

    <div class="fw-info-row">
        <span class="fw-info-label">Source</span>
        <span class="fw-info-value" style="color:var(--green);font-weight:600;">Zero-CIBIL Credit Wallet</span>
    </div>
</div>

<div id="payment-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,27,45,0.7);z-index:999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:12px;padding:24px;text-align:center;max-width:320px;width:100%;">
        <div style="width:54px;height:54px;background:#eafaf1;color:var(--green);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:12px;">✓</div>
        <h3 style="font-size:18px;font-weight:700;color:var(--navy);margin-bottom:6px;">Payment Successful!</h3>
        <p style="font-size:13px;color:var(--gray3);margin-bottom:16px;" id="modal-pay-msg">Payment completed successfully.</p>
        <a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Back to Wallet</a>
    </div>
</div>
@endsection

@section('sticky-bottom')
<button type="button" onclick="confirmZeroPay()" class="fw-btn fw-btn-accent">Confirm &amp; Pay Now →</button>
@endsection

@push('scripts')
<script>
function confirmZeroPay() {
    var amt = document.getElementById('pay_amt').value;
    document.getElementById('modal-pay-msg').textContent = '₹' + amt + ' paid to merchant from your Zero-CIBIL Wallet.';
    document.getElementById('payment-modal').style.display = 'flex';
}
</script>
@endpush

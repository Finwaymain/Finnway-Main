@extends('finance.layouts.base')
@section('title', 'Virtual Loan Dashboard — Fiinway')
@section('header-sub', 'Virtual Loan')

@section('content')
<div class="fw-wallet-stats">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-size:12px;font-weight:600;letter-spacing:0.5px;color:#a8c7fa;text-transform:uppercase;">Virtual Business Credit</span>
        <span class="fw-badge fw-badge-success" style="font-size:11px;">Active</span>
    </div>

    <div style="font-size:28px;font-weight:700;color:#ffffff;margin-bottom:4px;">
        ₹{{ isset($wallet->available_limit) ? number_format($wallet->available_limit) : '21,500' }}
    </div>
    <div style="font-size:12px;color:#cbd5e1;margin-bottom:16px;">Available to spend at Fiinway Business Merchants</div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.12);">
        <div>
            <p style="font-size:11px;color:#94a3b8;margin:0;">Approved Limit</p>
            <p style="font-size:14px;font-weight:600;color:#ffffff;margin:2px 0 0;">
                ₹{{ isset($wallet->credit_limit) ? number_format($wallet->credit_limit) : '30,000' }}
            </p>
        </div>
        <div>
            <p style="font-size:11px;color:#94a3b8;margin:0;">Used Limit</p>
            <p style="font-size:14px;font-weight:600;color:#fca5a5;margin:2px 0 0;">
                ₹{{ isset($wallet->used_limit) ? number_format($wallet->used_limit) : '8,500' }}
            </p>
        </div>
    </div>
</div>

{{-- Due Alert --}}
<div class="fw-card" style="border-left:4px solid var(--accent);padding:14px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <p style="font-size:11px;text-transform:uppercase;color:var(--gray3);font-weight:600;margin:0;">Today's Recovery</p>
            <p style="font-size:18px;font-weight:700;color:var(--navy);margin:2px 0 0;">₹1,000</p>
            <p style="font-size:11px;color:var(--gray3);margin:2px 0 0;">Next due date: Today</p>
        </div>
        <button type="button" class="fw-btn fw-btn-secondary" style="font-size:12px;padding:8px 12px;white-space:nowrap;" onclick="alert('Repayment processed successfully!')">
            Repay ₹1,000
        </button>
    </div>
</div>

{{-- Action Grid --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
    <button type="button" onclick="openQrModal()"
       class="fw-btn fw-btn-primary" style="padding:12px 8px;font-size:13px;display:flex;align-items:center;justify-content:center;gap:6px;">
        <span>📷</span> Scan &amp; Pay
    </button>
    <a href="#" onclick="alert('All repayment schedules are on track.')"
       class="fw-btn fw-btn-secondary" style="padding:12px 8px;font-size:13px;display:flex;align-items:center;justify-content:center;gap:6px;">
        <span>📅</span> Schedule
    </a>
</div>

{{-- Loan Details Card --}}
<div class="fw-card">
    <p class="fw-card-title">Loan Parameters &amp; Rules</p>
    <div class="fw-info-row">
        <span class="fw-info-label">Daily Spend Limit</span>
        <span class="fw-info-value">₹5,000 / day</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Per-Business Max</span>
        <span class="fw-info-value">₹2,000 / transaction</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Monthly Per-Business Limit</span>
        <span class="fw-info-value">5 transactions</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Linked Phone</span>
        <span class="fw-info-value">{{ request('phone', '—') }}</span>
    </div>
</div>

<div class="fw-card">
    <p class="fw-card-title">Recent Business Payments</p>
    
    <div style="padding:10px 0;border-bottom:1px solid var(--gray2);display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;gap:10px;align-items:center;">
            <div style="width:34px;height:34px;background:#e8f4fd;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;">🏪</div>
            <div>
                <p style="font-size:13px;font-weight:600;color:var(--navy);margin:0;">Sharma Traders</p>
                <p style="font-size:11px;color:var(--gray3);margin:2px 0 0;">Business App QR Payment</p>
            </div>
        </div>
        <div style="text-align:right;">
            <p style="font-size:13px;font-weight:700;color:var(--navy);margin:0;">- ₹1,500</p>
            <p style="font-size:10px;color:var(--green);margin:2px 0 0;">Completed</p>
        </div>
    </div>

    <div style="padding:10px 0;display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;gap:10px;align-items:center;">
            <div style="width:34px;height:34px;background:#e8f4fd;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;">🏢</div>
            <div>
                <p style="font-size:13px;font-weight:600;color:var(--navy);margin:0;">Metro Supermarket</p>
                <p style="font-size:11px;color:var(--gray3);margin:2px 0 0;">Business App QR Payment</p>
            </div>
        </div>
        <div style="text-align:right;">
            <p style="font-size:13px;font-weight:700;color:var(--navy);margin:0;">- ₹2,000</p>
            <p style="font-size:10px;color:var(--green);margin:2px 0 0;">Completed</p>
        </div>
    </div>
</div>

{{-- QR Scan Modal --}}
<div id="vloan-qr-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,27,45,0.7);z-index:999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:12px;padding:24px;text-align:center;max-width:320px;width:100%;">
        <div style="width:160px;height:160px;margin:0 auto 16px;border:2px dashed var(--blue2);border-radius:10px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f8fafc;">
            <span style="font-size:40px;">📷</span>
            <p style="font-size:11px;color:var(--gray3);margin-top:6px;">Scan Business QR</p>
        </div>
        <div class="fw-input-group" style="text-align:left;">
            <label class="fw-label">Amount (Max ₹2,000)</label>
            <input type="number" class="fw-input" id="vloan_amt" value="1000" max="2000">
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" onclick="closeQrModal()" class="fw-btn fw-btn-secondary" style="flex:1;">Cancel</button>
            <button type="button" onclick="completeVLoanPay()" class="fw-btn fw-btn-primary" style="flex:1;">Pay</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openQrModal() {
    document.getElementById('vloan-qr-modal').style.display = 'flex';
}
function closeQrModal() {
    document.getElementById('vloan-qr-modal').style.display = 'none';
}
function completeVLoanPay() {
    var amt = document.getElementById('vloan_amt').value;
    alert('Payment of ₹' + amt + ' successful to Business Merchant!');
    closeQrModal();
}
</script>
@endpush

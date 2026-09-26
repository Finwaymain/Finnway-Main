@extends('finance.layouts.base')
@section('title', 'Zero-CIBIL Wallet Active — Fiinway')
@section('header-sub', 'Zero-CIBIL Daily Loan')

@section('content')
<div class="fw-wallet-stats">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <span style="font-size:11px; font-weight:700; letter-spacing:0.5px; color:#93c5fd; text-transform:uppercase;">
            Interest-Free Daily Wallet
        </span>
        <span class="fw-badge fw-badge-green" style="font-size:10px;">Active</span>
    </div>

    <div style="font-size:32px; font-weight:800; color:#ffffff; margin-bottom:2px; letter-spacing:-0.5px;">
        ₹{{ isset($wallet->available_balance) ? number_format($wallet->available_balance) : number_format($amount ?? 50000) }}
    </div>
    <div style="font-size:12px; color:#94a3b8; margin-bottom:16px;">Total Available Loan Limit (0% Interest)</div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; padding-top:12px; border-top:1px solid rgba(255,255,255,0.12);">
        <div>
            <p style="font-size:11px; color:#94a3b8; margin:0;">Today's Usage Limit</p>
            <p style="font-size:15px; font-weight:700; color:#34d399; margin:2px 0 0;">
                ₹{{ isset($wallet->daily_usage_limit) ? number_format($wallet->daily_usage_limit) : '5,000' }}
            </p>
        </div>
        <div>
            <p style="font-size:11px; color:#94a3b8; margin:0;">Sanctioned Total</p>
            <p style="font-size:15px; font-weight:700; color:#ffffff; margin:2px 0 0;">
                ₹{{ isset($wallet->approved_limit) ? number_format($wallet->approved_limit) : number_format($amount ?? 50000) }}
            </p>
        </div>
    </div>
</div>

{{-- Daily Recovery Alert Card --}}
<div class="fw-card" style="border-left:4px solid var(--accent); padding:16px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <p style="font-size:11px; text-transform:uppercase; color:var(--gray3); font-weight:700; margin:0;">Today's Daily Repayment</p>
            <p style="font-size:20px; font-weight:800; color:var(--navy); margin:2px 0 0;">₹1,000</p>
            <p style="font-size:11px; color:var(--amber); margin:2px 0 0;">Due Today · Keeps daily usage limit unlocked</p>
        </div>
        <button type="button" class="fw-btn fw-btn-primary" style="font-size:12px; padding:10px 14px; width:auto; border-radius:8px;" onclick="alert('Daily repayment successful! Today\'s usage limit confirmed.')">
            Pay Daily ₹1,000
        </button>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
    <a href="{{ route('finance.zero_cibil.s07_qr_pay', ['phone' => request('phone')]) }}"
       class="fw-btn fw-btn-primary" style="padding:12px 8px; font-size:13px; display:flex; align-items:center; justify-content:center; gap:6px;">
        <span>📷</span> Scan &amp; Pay QR
    </a>
    <a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}"
       class="fw-btn fw-btn-outline" style="padding:12px 8px; font-size:13px; display:flex; align-items:center; justify-content:center; gap:6px;">
        <span>🔄</span> Refresh Status
    </a>
</div>

<div class="fw-card">
    <p class="fw-card-title">Daily Usage Rule</p>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>0% Interest Daily Credit</strong>
            <p style="font-size:12px; color:var(--gray3); margin-top:2px;">No hidden interest or platform markup</p>
        </div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>Daily Usage Unlocks with Daily EMI</strong>
            <p style="font-size:12px; color:var(--gray3); margin-top:2px;">Paying daily recovery keeps next day's ₹5,000 limit active</p>
        </div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>Merchant Payments Only</strong>
            <p style="font-size:12px; color:var(--gray3); margin-top:2px;">Usable at Fiinway verified merchant QR points</p>
        </div>
    </div>
</div>
@endsection

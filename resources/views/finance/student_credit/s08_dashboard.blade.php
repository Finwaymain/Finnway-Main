@extends('finance.layouts.base')
@section('title', 'Student Credit Dashboard — Fiinway')
@section('header-sub', 'Student Credit')

@section('content')
<div class="fw-wallet-stats">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-size:12px;font-weight:600;letter-spacing:0.5px;color:#a8c7fa;text-transform:uppercase;">Student Credit Wallet</span>
        <span class="fw-badge fw-badge-success" style="font-size:11px;">Active</span>
    </div>

    <div style="font-size:28px;font-weight:700;color:#ffffff;margin-bottom:4px;">
        ₹{{ isset($wallet->available_limit) ? number_format($wallet->available_limit) : '25,000' }}
    </div>
    <div style="font-size:12px;color:#cbd5e1;margin-bottom:16px;">Available to spend on campus merchants</div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.12);">
        <div>
            <p style="font-size:11px;color:#94a3b8;margin:0;">Approved Limit</p>
            <p style="font-size:14px;font-weight:600;color:#ffffff;margin:2px 0 0;">
                ₹{{ isset($wallet->credit_limit) ? number_format($wallet->credit_limit) : '25,000' }}
            </p>
        </div>
        <div>
            <p style="font-size:11px;color:#94a3b8;margin:0;">Used Balance</p>
            <p style="font-size:14px;font-weight:600;color:#fca5a5;margin:2px 0 0;">
                ₹{{ isset($wallet->used_limit) ? number_format($wallet->used_limit) : '0' }}
            </p>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
    <a href="{{ route('finance.student_credit.s09_qr_pay', ['phone' => request('phone')]) }}"
       class="fw-btn fw-btn-primary" style="padding:12px 8px;font-size:13px;display:flex;align-items:center;justify-content:center;gap:6px;">
        <span>📷</span> Scan &amp; Pay QR
    </a>
    <a href="#" onclick="alert('Repayment portal: No pending dues for this cycle.')"
       class="fw-btn fw-btn-secondary" style="padding:12px 8px;font-size:13px;display:flex;align-items:center;justify-content:center;gap:6px;">
        <span>💳</span> Repay Dues
    </a>
</div>

<div class="fw-card">
    <p class="fw-card-title">Recent Transactions</p>
    
    <div style="padding:10px 0;border-bottom:1px solid var(--gray2);display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;gap:10px;align-items:center;">
            <div style="width:34px;height:34px;background:#e8f4fd;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;">📚</div>
            <div>
                <p style="font-size:13px;font-weight:600;color:var(--navy);margin:0;">Campus Bookstore</p>
                <p style="font-size:11px;color:var(--gray3);margin:2px 0 0;">App-to-App payment</p>
            </div>
        </div>
        <div style="text-align:right;">
            <p style="font-size:13px;font-weight:700;color:var(--navy);margin:0;">- ₹450</p>
            <p style="font-size:10px;color:var(--green);margin:2px 0 0;">Completed</p>
        </div>
    </div>

    <div style="padding:10px 0;display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;gap:10px;align-items:center;">
            <div style="width:34px;height:34px;background:#e8f4fd;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;">☕</div>
            <div>
                <p style="font-size:13px;font-weight:600;color:var(--navy);margin:0;">University Cafeteria</p>
                <p style="font-size:11px;color:var(--gray3);margin:2px 0 0;">App-to-App payment</p>
            </div>
        </div>
        <div style="text-align:right;">
            <p style="font-size:13px;font-weight:700;color:var(--navy);margin:0;">- ₹120</p>
            <p style="font-size:10px;color:var(--green);margin:2px 0 0;">Completed</p>
        </div>
    </div>
</div>

<div class="fw-card">
    <p class="fw-card-title">Student Credit Support</p>
    <div class="fw-info-row">
        <span class="fw-info-label">Customer ID</span>
        <span class="fw-info-value">{{ $customer->id ?? 'FIN-STU' }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Linked Phone</span>
        <span class="fw-info-value">{{ request('phone', '—') }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Billing Cycle</span>
        <span class="fw-info-value">Monthly (1st of month)</span>
    </div>
</div>
@endsection

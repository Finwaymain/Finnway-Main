@extends('finance.layouts.base')
@section('title', 'Zero-CIBIL Credit Wallet — Fiinway')
@section('header-sub', 'Daily Credit Wallet')

@section('content')
<div class="fw-viewport-container">
    <div>
        {{-- Wallet Balance Hero Card --}}
        <div class="fw-bank-hero" style="padding:16px; margin-bottom:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span style="font-size:10px; font-weight:700; letter-spacing:0.8px; color:#93c5fd; text-transform:uppercase;">
                    0% Interest Daily Wallet
                </span>
                <span class="fw-badge fw-badge-green" style="font-size:10px;">● Active</span>
            </div>

            <div style="font-size:30px; font-weight:800; color:#ffffff; margin-bottom:2px; letter-spacing:-0.5px;">
                ₹{{ isset($wallet->available_balance) ? number_format($wallet->available_balance) : number_format($amount ?? 50000) }}
            </div>
            <div style="font-size:11px; color:#94a3b8; margin-bottom:12px;">Total Available Revolving Limit</div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; padding-top:10px; border-top:1px solid rgba(255,255,255,0.12);">
                <div>
                    <span style="font-size:10px; color:#94a3b8;">Today's Usable Quota</span>
                    <div style="font-size:15px; font-weight:700; color:#34d399;">
                        ₹{{ isset($wallet->daily_usage_limit) ? number_format($wallet->daily_usage_limit) : '5,000' }}
                    </div>
                </div>
                <div>
                    <span style="font-size:10px; color:#94a3b8;">Sanctioned Total</span>
                    <div style="font-size:15px; font-weight:700; color:#ffffff;">
                        ₹{{ isset($wallet->approved_limit) ? number_format($wallet->approved_limit) : number_format($amount ?? 50000) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Daily Recovery Action Card --}}
        <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:8px; border-left:3px solid var(--accent);">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="font-size:10px; text-transform:uppercase; color:var(--gray3); font-weight:700;">Today's Daily Recovery</span>
                    <div style="font-size:17px; font-weight:800; color:var(--navy); margin:1px 0;">₹1,000</div>
                    <span style="font-size:10px; color:var(--amber);">Due Today · Unlocks tomorrow's ₹5,000 limit</span>
                </div>
                <button type="button" class="fw-btn fw-btn-primary" style="font-size:11px; padding:8px 12px; width:auto; border-radius:8px;" onclick="alert('Daily installment recorded! Tomorrow limit remains unlocked.')">
                    Pay Daily ₹1,000
                </button>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
            <a href="{{ route('finance.zero_cibil.s07_qr_pay', ['phone' => request('phone')]) }}"
               class="fw-btn fw-btn-primary" style="padding:10px 8px; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:6px;">
                <span>📷</span> Scan Merchant QR
            </a>
            <a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}"
               class="fw-btn fw-btn-outline" style="padding:10px 8px; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:6px;">
                <span>🔄</span> Refresh Balance
            </a>
        </div>

        {{-- Policy Rules (Compact) --}}
        <div class="fw-bank-card" style="padding:8px 12px; margin-bottom:0; font-size:11px; color:#475569;">
            <div style="font-weight:700; color:var(--navy); margin-bottom:4px;">Wallet Rules</div>
            <div>• Usable for merchant purchases at verified QR points.</div>
            <div>• Repay daily micro-recovery to keep daily usage limit active.</div>
        </div>
    </div>
</div>
@endsection

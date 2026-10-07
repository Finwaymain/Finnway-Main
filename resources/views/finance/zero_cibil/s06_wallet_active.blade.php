@extends('finance.layouts.base')
@section('title', 'Zero-CIBIL Credit Card & Wallet — Fiinway')
@section('header-sub', 'Daily Credit Card')

@section('content')
<div class="fw-viewport-container" style="padding-bottom:88px;">
    <div>
        @php
            $creditAmount = isset($wallet->approved_limit) ? $wallet->approved_limit : ($amount ?? 65000);
            if ($creditAmount > 84000) $creditAmount = 84000;
            if ($creditAmount < 15000) $creditAmount = 15000;

            // Determine daily quota based on 4 slabs
            if ($creditAmount <= 15000) {
                $planCode = 'D15 Plan';
                $dailyQuota = 1000;
            } elseif ($creditAmount <= 24000) {
                $planCode = 'D12 Plan';
                $dailyQuota = 2000;
            } elseif ($creditAmount <= 65000) {
                $planCode = 'D30 Plan';
                $dailyQuota = 3000;
            } else {
                $planCode = 'D45 Plan';
                $dailyQuota = 4000;
            }

            $availBalance = isset($wallet->available_balance) ? $wallet->available_balance : $creditAmount;
            $cardHolder = !empty($applicantName) && $applicantName !== 'Valued Applicant' ? strtoupper($applicantName) : 'VALUED BORROWER';
        @endphp

        {{-- Digital Loan Card (Realistic Credit Card UI) --}}
        <div style="background:linear-gradient(135deg, #09121f 0%, #1e293b 55%, #0f172a 100%); border-radius:18px; padding:20px 18px; color:#ffffff; box-shadow:0 12px 28px rgba(15,23,42,0.35); position:relative; overflow:hidden; margin-bottom:14px; border:1px solid rgba(255,255,255,0.15);">
            {{-- Card Chip & Contactless --}}
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    {{-- EMV Chip --}}
                    <div style="width:38px; height:28px; background:linear-gradient(135deg, #fbbf24 0%, #d97706 100%); border-radius:5px; border:1px solid #b45309; position:relative; display:flex; align-items:center; justify-content:center;">
                        <div style="width:28px; height:18px; border:1px solid rgba(0,0,0,0.25); border-radius:3px;"></div>
                    </div>
                    {{-- Contactless Waves --}}
                    <span style="font-size:16px; opacity:0.8; transform:rotate(90deg); display:inline-block;">📶</span>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px; font-weight:800; letter-spacing:1px; color:#38bdf8;">ZERO-CIBIL DAILY</div>
                    <div style="font-size:9px; color:#94a3b8; letter-spacing:0.5px;">{{ $planCode }}</div>
                </div>
            </div>

            {{-- Card Number --}}
            <div style="font-family:'Courier New', monospace; font-size:17px; font-weight:700; letter-spacing:2.5px; margin-bottom:16px; color:#f8fafc; text-shadow:0 1px 2px rgba(0,0,0,0.5);">
                4532 &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; 9812
            </div>

            {{-- Card Bottom Info --}}
            <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                <div>
                    <div style="font-size:8px; text-transform:uppercase; color:#94a3b8; letter-spacing:0.6px; margin-bottom:2px;">Cardholder Name</div>
                    <div style="font-size:12px; font-weight:800; letter-spacing:0.8px; color:#ffffff;">{{ $cardHolder }}</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:8px; text-transform:uppercase; color:#94a3b8; letter-spacing:0.6px; margin-bottom:2px;">Valid Thru</div>
                    <div style="font-size:11px; font-weight:700; font-family:monospace; color:#ffffff;">10/29</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:8px; text-transform:uppercase; color:#34d399; letter-spacing:0.6px; margin-bottom:2px;">Approved Limit</div>
                    <div style="font-size:14px; font-weight:800; color:#34d399;">₹{{ number_format($creditAmount) }}</div>
                </div>
            </div>
        </div>

        {{-- Limit & Daily Quota Dashboard --}}
        <div class="fw-bank-card" style="padding:14px; margin-bottom:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <span style="font-size:11px; font-weight:700; color:var(--gray3); text-transform:uppercase; letter-spacing:0.5px;">Today's Usable Spending Quota</span>
                <span class="fw-badge fw-badge-green" style="font-size:10px; font-weight:700;">● Active</span>
            </div>
            <div style="display:flex; align-items:baseline; justify-content:space-between; margin-bottom:10px;">
                <div style="font-size:26px; font-weight:800; color:var(--navy);">
                    ₹{{ number_format($dailyQuota) }}
                    <span style="font-size:12px; font-weight:600; color:var(--gray3);">/ day</span>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:10px; color:var(--gray3);">Total Sanctioned Limit</span>
                    <div style="font-size:14px; font-weight:800; color:var(--navy);">₹{{ number_format($creditAmount) }}</div>
                </div>
            </div>
            <div style="background:#f1f5f9; border-radius:6px; height:8px; overflow:hidden; margin-bottom:6px;">
                <div style="background:#10b981; height:100%; width:100%;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:10px; color:var(--gray3);">
                <span>Available Today: ₹{{ number_format($dailyQuota) }}</span>
                <span>0% Interest Micro-Credit</span>
            </div>
        </div>

        {{-- Daily Recovery Action Card --}}
        <div class="fw-bank-card" style="padding:12px 14px; margin-bottom:12px; border-left:4px solid #10b981; background:#f0fdf4;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="font-size:10px; text-transform:uppercase; color:#166534; font-weight:800; letter-spacing:0.5px;">Today's Recovery Status</span>
                    <div style="font-size:17px; font-weight:800; color:#14532d; margin:2px 0;">₹{{ number_format($dailyQuota) }} Quota Ready</div>
                    <span style="font-size:10px; color:#15803d;">Repay tomorrow after use to unlock next day limit</span>
                </div>
                <button type="button" class="fw-btn fw-btn-green" style="font-size:11px; font-weight:800; padding:9px 12px; width:auto; border-radius:8px;" onclick="showRepaymentSuccess()">
                    Repay Installment
                </button>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
            <a href="{{ route('finance.zero_cibil.s07_qr_pay', ['phone' => request('phone')]) }}"
               class="fw-btn fw-btn-primary" style="min-height:48px; font-size:13px; font-weight:800; display:flex; align-items:center; justify-content:center; gap:8px; border-radius:10px;">
                <span>📷</span> Scan Merchant QR
            </a>
            <a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}"
               class="fw-btn fw-btn-outline" style="min-height:48px; font-size:13px; font-weight:800; display:flex; align-items:center; justify-content:center; gap:8px; border-radius:10px;">
                <span>🔄</span> Refresh Card Limit
            </a>
        </div>

        {{-- Official Recovery Process & Safeguard Rules --}}
        <div class="fw-bank-card" style="padding:14px; margin-bottom:10px;">
            <div style="font-size:12px; font-weight:800; color:var(--navy); margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                <span>🛡️</span> Zero-Loss Recovery Process Rules
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; font-size:11px; line-height:1.45; color:#334155;">
                <div style="display:flex; gap:8px;">
                    <span style="color:#10b981; font-weight:800;">✓</span>
                    <div><strong>Next Day Repayment:</strong> After full daily usage repayment is cleared, next day's spending limit is automatically unlocked.</div>
                </div>
                <div style="display:flex; gap:8px;">
                    <span style="color:#f59e0b; font-weight:800;">⚠️</span>
                    <div><strong>2 Days Unpaid:</strong> Agar 2 din tak payment nahi hoti, toh 2 din ka repayment ek saath calculate kiya jayega.</div>
                </div>
                <div style="display:flex; gap:8px;">
                    <span style="color:#ef4444; font-weight:800;">🚨</span>
                    <div><strong>Overdue Penalty:</strong> 3 din se zyada payment pending hone par +₹30/day late fee penalty jod di jayegi.</div>
                </div>
                <div style="display:flex; gap:8px;">
                    <span style="color:#dc2626; font-weight:800;">🛑</span>
                    <div><strong>25% Usage Barred Rule:</strong> Daily expense quota ka &gt;25% use karne ke baad unpaid rehne par card limit temporarily barred/frozen ho jayegi jab tak repayment clear na ho.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showRepaymentSuccess() {
    alert("✓ Repayment installment submitted successfully! Next day's daily spending limit remains unlocked.");
}
</script>
@endsection

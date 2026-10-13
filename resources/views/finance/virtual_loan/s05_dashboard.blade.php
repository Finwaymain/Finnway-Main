@extends('finance.layouts.base')
@section('title', 'Virtual Loan Dashboard — Fiinway')
@section('header-sub', 'Virtual Loan')

@php
    $availBal = $wallet ? floatval($wallet->available_balance) : 0;
    $approvedLim = $wallet ? floatval($wallet->approved_limit) : 0;
    $usedAmt = $wallet ? floatval($wallet->used_amount) : 0;
    $todayDue = $todaySchedule ? floatval($todaySchedule->total_due) : floatval(\App\Models\Finance\FinanceSetting::get('virtual_loan.daily_repayment_amount', 1000));
    $scheduleId = $todaySchedule ? $todaySchedule->id : null;
    $isPaidToday = $todaySchedule && $todaySchedule->status === 'paid';
    $isLocked = ($isDefaulter || ($wallet && $wallet->status === 'blocked'));
@endphp

@section('content')

{{-- Defaulter / Lock Warning Alert --}}
@if($isLocked)
<div class="fw-alert" style="background:#fef2f2;border:1.5px solid #f87171;color:#991b1b;margin-bottom:14px;padding:12px 14px;border-radius:12px;">
    <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:13px;margin-bottom:4px;">
        <span>🚨</span>
        <span>CREDIT USAGE LOCKED (DEFAULTER)</span>
    </div>
    <div style="font-size:12px;color:#7f1d1d;line-height:1.4;">
        @if($overdueCount > 0)
            You have {{ $overdueCount }} overdue repayment(s) totaling <strong>₹{{ number_format($overdueAmount, 2) }}</strong>.
        @else
            Your virtual credit usage has been suspended due to pending repayment or administrative block.
        @endif
        Pay your pending EMI via Razorpay (UPI or Card) below to instantly restore your usage limit.
    </div>
</div>
@endif

<div class="fw-wallet-stats" style="{{ $isLocked ? 'background:linear-gradient(135deg, #1e1b4b 0%, #31102f 100%);' : '' }}">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-size:12px;font-weight:600;letter-spacing:0.5px;color:#a8c7fa;text-transform:uppercase;">Virtual Business Credit</span>
        @if($isLocked)
            <span class="fw-badge" style="background:#fee2e2;color:#991b1b;font-size:11px;font-weight:700;">Locked / Overdue</span>
        @else
            <span class="fw-badge fw-badge-success" style="font-size:11px;">Active</span>
        @endif
    </div>

    <div style="font-size:30px;font-weight:800;color:#ffffff;margin-bottom:4px;letter-spacing:-0.5px;">
        ₹{{ number_format($availBal) }}
    </div>
    <div style="font-size:12px;color:#cbd5e1;margin-bottom:16px;">
        {{ $isLocked ? 'Spending disabled until dues are cleared' : 'Available for In-App Services & Eligible Merchant QRs' }}
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.12);">
        <div>
            <p style="font-size:11px;color:#94a3b8;margin:0;">Approved Limit</p>
            <p style="font-size:15px;font-weight:700;color:#ffffff;margin:2px 0 0;">
                ₹{{ number_format($approvedLim) }}
            </p>
        </div>
        <div>
            <p style="font-size:11px;color:#94a3b8;margin:0;">Used Balance</p>
            <p style="font-size:15px;font-weight:700;color:#fca5a5;margin:2px 0 0;">
                ₹{{ number_format($usedAmt) }}
            </p>
        </div>
    </div>
</div>

{{-- Today's Repayment Recovery Card --}}
<div class="fw-card" style="border-left:4px solid {{ $isLocked ? '#dc2626' : ($isPaidToday ? '#16a34a' : 'var(--accent)') }};padding:14px;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
        <div>
            <p style="font-size:11px;text-transform:uppercase;color:var(--gray3);font-weight:700;margin:0;">
                {{ $overdueCount > 0 ? 'Overdue Due Amount' : "Today's Recovery" }}
            </p>
            <p style="font-size:20px;font-weight:800;color:{{ $isLocked ? '#dc2626' : ($isPaidToday ? '#16a34a' : 'var(--navy)') }};margin:2px 0 0;">
                ₹{{ number_format($overdueCount > 0 ? $overdueAmount : $todayDue) }}
            </p>
            <p style="font-size:11px;color:var(--gray3);margin:2px 0 0;">
                Status: 
                @if($isPaidToday)
                    <span style="color:#16a34a;font-weight:700;">✓ Paid for Today</span>
                @elseif($overdueCount > 0)
                    <span style="color:#dc2626;font-weight:700;">⚠️ Overdue (Immediate Pay Required)</span>
                @else
                    <span style="color:#d97706;font-weight:600;">Pending Due Today</span>
                @endif
            </p>
        </div>

        @if(!$isPaidToday || $overdueCount > 0)
        <button type="button" class="fw-btn fw-btn-primary" style="font-size:12px;padding:9px 14px;white-space:nowrap;background:{{ $isLocked ? '#dc2626' : '#2563eb' }};"
                onclick="triggerVirtualLoanRepayment({{ $overdueCount > 0 ? $overdueAmount : $todayDue }}, '{{ $scheduleId }}')">
            💳 Repay via Razorpay
        </button>
        @else
        <span class="badge" style="background:#ecfdf5;color:#065f46;padding:6px 10px;font-size:11px;font-weight:700;border-radius:6px;">
            ✓ All Clear
        </span>
        @endif
    </div>
</div>

{{-- Quick Actions --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
    <button type="button" onclick="{{ $isLocked ? 'alert(\'Cannot scan and pay: Your Virtual Credit is locked due to pending repayment.\')' : 'openQrModal()' }}"
       class="fw-btn fw-btn-primary" style="padding:12px 8px;font-size:13px;display:flex;align-items:center;justify-content:center;gap:6px;opacity:{{ $isLocked ? '0.6' : '1' }};">
        <span>📷</span> Scan &amp; Pay
    </button>
    <a href="{{ route('finance.repayments', ['phone' => request('phone')]) }}"
       class="fw-btn fw-btn-secondary" style="padding:12px 8px;font-size:13px;display:flex;align-items:center;justify-content:center;gap:6px;">
        <span>📅</span> EMI Schedule
    </a>
</div>

{{-- Permitted Categories & Spending Rules --}}
<div class="fw-card">
    <p class="fw-card-title">Permitted In-App Categories</p>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">
        @foreach((array)$allowedCategories as $cat)
            <span style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;text-transform:capitalize;">
                @if($cat === 'food') 🍔 Food Orders
                @elseif($cat === 'ride') 🚖 Rides / Cab
                @elseif($cat === 'parcel') 📦 Parcel Deliveries
                @elseif($cat === 'marketplace') 🛍️ Marketplace
                @else • {{ ucfirst($cat) }}
                @endif
            </span>
        @endforeach
    </div>

    <div class="fw-info-row">
        <span class="fw-info-label">Daily Overall Spend Limit</span>
        <span class="fw-info-value">₹{{ number_format($dailyLimit) }} / day</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Max Allowed per Merchant</span>
        <span class="fw-info-value">₹{{ number_format($maxPerMerchant) }} / transaction</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Linked Mobile</span>
        <span class="fw-info-value">{{ request('phone', '—') }}</span>
    </div>
</div>

{{-- Recent Transactions --}}
<div class="fw-card">
    <p class="fw-card-title">Recent Transactions</p>
    @if(isset($transactions) && $transactions->count() > 0)
        @foreach($transactions as $tx)
        <div style="padding:10px 0;border-bottom:1px solid var(--gray2);display:flex;justify-content:space-between;align-items:center;">
            <div style="display:flex;gap:10px;align-items:center;">
                <div style="width:34px;height:34px;background:#e8f4fd;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;">
                    {{ $tx->direction === 'credit' ? '📥' : '🏪' }}
                </div>
                <div>
                    <p style="font-size:13px;font-weight:600;color:var(--navy);margin:0;">{{ $tx->notes ?: ucwords(str_replace('_', ' ', $tx->txn_type)) }}</p>
                    <p style="font-size:11px;color:var(--gray3);margin:2px 0 0;">{{ $tx->created_at ? $tx->created_at->format('d M, H:i') : '' }}</p>
                </div>
            </div>
            <div style="text-align:right;">
                <p style="font-size:13px;font-weight:700;color:{{ $tx->direction === 'credit' ? '#16a34a' : 'var(--navy)' }};margin:0;">
                    {{ $tx->direction === 'credit' ? '+' : '-' }} ₹{{ number_format($tx->amount) }}
                </p>
                <p style="font-size:10px;color:{{ $tx->status === 'success' ? '#16a34a' : '#dc2626' }};margin:2px 0 0;text-transform:uppercase;">{{ $tx->status }}</p>
            </div>
        </div>
        @endforeach
    @else
        <div style="padding:20px 0;text-align:center;color:var(--gray3);font-size:12px;">
            No transactions yet. Spend at permitted merchants or in-app services.
        </div>
    @endif
</div>

{{-- QR Scan Simulation Modal --}}
<div id="vloan-qr-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,27,45,0.7);z-index:999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:16px;padding:24px;text-align:center;max-width:320px;width:100%;box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <div style="width:140px;height:140px;margin:0 auto 16px;border:2px dashed var(--blue2);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f8fafc;">
            <span style="font-size:40px;">📷</span>
            <p style="font-size:11px;color:var(--gray3);margin-top:6px;font-weight:600;">Scan Merchant QR</p>
        </div>
        <div class="fw-input-group" style="text-align:left;">
            <label class="fw-label">Amount (Max ₹{{ number_format($maxPerMerchant) }})</label>
            <input type="number" class="fw-input" id="vloan_amt" value="500" max="{{ $maxPerMerchant }}">
        </div>
        <div style="display:flex;gap:8px;margin-top:14px;">
            <button type="button" onclick="closeQrModal()" class="fw-btn fw-btn-secondary" style="flex:1;">Cancel</button>
            <button type="button" onclick="completeVLoanPay()" class="fw-btn fw-btn-primary" style="flex:1;">Pay Merchant</button>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function openQrModal() {
    document.getElementById('vloan-qr-modal').style.display = 'flex';
}
function closeQrModal() {
    document.getElementById('vloan-qr-modal').style.display = 'none';
}
function completeVLoanPay() {
    var amt = parseFloat(document.getElementById('vloan_amt').value);
    var maxLimit = {{ (float)$maxPerMerchant }};
    if (isNaN(amt) || amt <= 0) {
        alert('Please enter a valid amount');
        return;
    }
    if (amt > maxLimit) {
        alert('Amount exceeds per-merchant limit of ₹' + maxLimit);
        return;
    }
    alert('Payment of ₹' + amt + ' simulated successfully to Business Merchant!');
    closeQrModal();
}

function triggerVirtualLoanRepayment(amount, scheduleId) {
    var key = "{{ $razorpayKey ?? '' }}" || "{{ env('LOAN_RAZORPAY_KEY', env('RAZORPAY_KEY', 'rzp_test_fiinway')) }}";
    var options = {
        "key": key,
        "amount": Math.round(Number(amount) * 100),
        "currency": "INR",
        "name": "{{ $razorpayMerchantName ?? 'Fiinway Virtual Loan' }}",
        "description": "Daily EMI Repayment",
        "image": "https://fiinway.com/favicon.ico",
        "handler": function (response) {
            submitRepaymentVerification({
                amount: amount,
                schedule_id: scheduleId,
                payment_id: response.razorpay_payment_id || ('rzp_repay_' + Date.now()),
                phone: "{{ request('phone') }}"
            });
        },
        "prefill": {
            "name": "{{ $customer->name ?? 'Valued Borrower' }}",
            "contact": "{{ request('phone') }}"
        },
        "theme": { "color": "#0f172a" },
        "modal": {
            "ondismiss": function() {
                console.log("Repayment modal dismissed");
            }
        }
    };

    try {
        if (typeof Razorpay !== 'undefined') {
            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function (res) {
                alert("Payment could not be completed: " + (res.error ? res.error.description : 'Failed'));
            });
            rzp.open();
        } else {
            promptSimulationRepay(amount, scheduleId);
        }
    } catch(e) {
        promptSimulationRepay(amount, scheduleId);
    }
}

function promptSimulationRepay(amount, scheduleId) {
    if (confirm("Proceed with simulated repayment of ₹" + amount + "?")) {
        submitRepaymentVerification({
            amount: amount,
            schedule_id: scheduleId,
            payment_id: 'sim_repay_' + Date.now(),
            phone: "{{ request('phone') }}"
        });
    }
}

function submitRepaymentVerification(payload) {
    var repayUrl = "{{ route('finance.virtual_loan.repay') }}";
    var token = "{{ csrf_token() }}";

    fetch(repayUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": token
        },
        body: JSON.stringify(payload)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        alert(data.message || "Repayment processed successfully!");
        if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            window.location.reload();
        }
    })
    .catch(function(err) {
        console.error("Repayment error", err);
        window.location.reload();
    });
}
</script>

@endsection

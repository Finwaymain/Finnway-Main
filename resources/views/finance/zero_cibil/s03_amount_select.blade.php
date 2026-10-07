@extends('finance.layouts.base')
@section('title', 'Select Credit Plan — Zero-CIBIL')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 3 of 6 · Credit Plan')
@section('progress-pct', '50')
@section('progress', ' ')

@section('back')
<a href="{{ route('finance.zero_cibil.s02_kyc', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-viewport-container">
    <div>
        {{-- Section Header --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <div>
                <p class="fw-section-title" style="font-size:18px; margin-bottom:2px;">Select Credit Plan</p>
                <p style="font-size:11px; color:var(--gray3); margin:0;">Zero interest daily revolving wallet (Max ₹84,000)</p>
            </div>
            <span class="fw-badge-fintech">0% Interest</span>
        </div>

        @php
            $plans = [
                [
                    'code' => 'D15',
                    'amount' => 15000,
                    'tenure' => '15 Days',
                    'quota' => '₹1,000 / day',
                    'fee' => 3500,
                    'desc' => 'D15 Micro Credit'
                ],
                [
                    'code' => 'D12',
                    'amount' => 24000,
                    'tenure' => '12 Days',
                    'quota' => '₹2,000 / day',
                    'fee' => 4500,
                    'desc' => 'D12 Smart Credit'
                ],
                [
                    'code' => 'D30',
                    'amount' => 65000,
                    'tenure' => '30 Days',
                    'quota' => '₹3,000 / day',
                    'fee' => 6903,
                    'desc' => 'D30 Popular Credit'
                ],
                [
                    'code' => 'D45',
                    'amount' => 84000,
                    'tenure' => '45 Days',
                    'quota' => '₹4,000 / day',
                    'fee' => 9500,
                    'desc' => 'D45 Max Credit'
                ],
            ];
            $curr = intval($amount ?? 65000);
            if ($curr > 84000) $curr = 84000;
            if ($curr < 15000) $curr = 15000;
        @endphp

        <form id="amountForm" method="POST" action="{{ route('finance.zero_cibil.save_amount') }}">
            @csrf
            <input type="hidden" name="phone" value="{{ request('phone', $phone) }}">
            <input type="hidden" id="chosen-amount" name="amount" value="{{ $curr }}">

            {{-- Selected Plan Display Card --}}
            <div class="fw-bank-hero" style="padding:14px 16px; margin-bottom:10px; text-align:center;">
                <div style="font-size:10px; text-transform:uppercase; letter-spacing:0.8px; color:#94a3b8; font-weight:700;">
                    Selected Credit Limit
                </div>
                <div id="display-amount" style="font-size:28px; font-weight:800; color:#00e599; margin:2px 0; letter-spacing:-0.5px;">
                    ₹{{ number_format($curr) }}
                </div>
                <div style="font-size:11px; color:#94a3b8; display:flex; justify-content:center; gap:12px; margin-top:4px;">
                    <span id="display-quota">Daily Spending Quota: ₹3,000</span>
                    <span>•</span>
                    <span id="display-tenure" style="color:#ffffff; font-weight:600;">Tenure: 30 Days</span>
                </div>
                <div style="font-size:10px; color:#a7f3d0; margin-top:4px;">
                    Repayment Schedule: Next day after usage · Strictly 0% Interest
                </div>
            </div>

            {{-- 4 Official Credit Slabs (Image 2) --}}
            <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:6px;">
                Choose Pre-Approved Credit Slab
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px;">
                @foreach($plans as $p)
                <div class="fw-bank-card plan-card {{ $curr == $p['amount'] ? 'plan-selected' : '' }}"
                     style="padding:10px 12px; margin-bottom:0; cursor:pointer; border:1.5px solid {{ $curr == $p['amount'] ? 'var(--blue)' : '#e2e8f0' }}; transition:all 0.2s;"
                     data-val="{{ $p['amount'] }}"
                     data-quota="{{ $p['quota'] }}"
                     data-tenure="{{ $p['tenure'] }}"
                     onclick="selectPlanCard(this, {{ $p['amount'] }}, '{{ $p['quota'] }}', '{{ $p['tenure'] }}')">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2px;">
                        <span style="font-size:10px; font-weight:800; background:#f1f5f9; padding:2px 6px; border-radius:4px; color:var(--navy);">{{ $p['code'] }}</span>
                        <span style="font-size:10px; color:#00a875; font-weight:700;">0% Int</span>
                    </div>
                    <div style="font-size:16px; font-weight:800; color:var(--navy); margin:2px 0;">
                        ₹{{ number_format($p['amount']) }}
                    </div>
                    <div style="font-size:10px; color:var(--gray3);">
                        Quota: <strong>{{ $p['quota'] }}</strong>
                    </div>
                    <div style="font-size:10px; color:var(--blue); font-weight:600; margin-top:2px;">
                        Tenure: {{ $p['tenure'] }}
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Custom Amount (Capped at 84K) --}}
            <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <label class="fw-label" style="font-size:10px; margin:0;" for="custom-amount">Or Enter Custom Amount (₹)</label>
                    <span style="font-size:10px; color:var(--gray3); font-weight:700;">Max Limit: ₹84,000</span>
                </div>
                <input type="number" id="custom-amount" class="fw-input"
                       style="padding:8px 10px; font-size:14px; font-weight:700;"
                       placeholder="e.g. 65000" min="15000" max="84000" step="1000"
                       value="{{ $curr }}"
                       oninput="selectCustom(this.value)">
                <p style="font-size:10px; color:var(--gray3); margin:4px 0 0;">
                    Repayment installment amount will be calculated and scheduled after underwriting verification.
                </p>
            </div>
        </form>
    </div>

    {{-- Bottom Action (Fixed height & safe bottom spacing) --}}
    <div style="padding:14px 0 32px; margin-bottom:20px;">
        <button type="button" onclick="submitAmountSelection()" class="fw-btn fw-btn-primary" style="min-height:52px; height:52px; font-size:15px; font-weight:800; border-radius:12px; box-shadow:0 4px 14px rgba(26,95,168,0.3);">
            Proceed to Verification & Underwriting →
        </button>
    </div>
</div>

<script>
function selectPlanCard(el, amt, quota, tenure) {
    document.querySelectorAll('.plan-card').forEach(function(c) {
        c.style.borderColor = '#e2e8f0';
        c.style.background = '#ffffff';
    });
    el.style.borderColor = 'var(--blue)';
    el.style.background = '#f0f7ff';

    document.getElementById('chosen-amount').value = amt;
    document.getElementById('custom-amount').value = amt;
    document.getElementById('display-amount').textContent = '₹' + amt.toLocaleString('en-IN');
    document.getElementById('display-quota').textContent = 'Daily Spending Quota: ' + quota;
    document.getElementById('display-tenure').textContent = 'Tenure: ' + tenure;
}

function selectCustom(val) {
    var num = parseInt(val) || 15000;
    if (num > 84000) num = 84000;
    if (num < 15000) num = 15000;

    document.querySelectorAll('.plan-card').forEach(function(c) {
        c.style.borderColor = '#e2e8f0';
        c.style.background = '#ffffff';
    });

    document.getElementById('chosen-amount').value = num;
    document.getElementById('display-amount').textContent = '₹' + num.toLocaleString('en-IN');
    document.getElementById('display-quota').textContent = 'Daily Spending Quota: ₹' + Math.min(4000, Math.round(num * 0.05 / 100) * 100).toLocaleString('en-IN');
    document.getElementById('display-tenure').textContent = 'Tenure: Daily Installment';
}

function submitAmountSelection() {
    var form = document.getElementById('amountForm');
    // Trigger 10s Stage Transition Loading Modal (Req 1 & 5)
    window.showBankingStageLoader(
        "Persisting Sanction Parameters",
        "Configuring zero-CIBIL daily credit allocation...",
        10,
        function() {
            form.submit();
        }
    );
}
</script>
@endsection

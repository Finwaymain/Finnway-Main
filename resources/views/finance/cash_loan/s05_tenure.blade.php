@extends('finance.layouts.base')
@section('title', 'Select Amount & Tenure — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 4 of 11</span><span>36%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:36%;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s04_eligibility', ['phone' => $phone, 'amount' => $amount]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<form id="tenureForm" method="POST" action="{{ route('finance.cash_loan.save_step') }}">
    @csrf
    <input type="hidden" name="step" value="s05">
    <input type="hidden" name="phone" value="{{ $phone }}">
    <input type="hidden" name="tenure" id="tenureInput" value="{{ $tenure }}">

    {{-- 1. TENURE & AMOUNT SELECTION CARD --}}
    <div class="fw-card mt-2 mb-3">
        <h3 class="fw-section-title mb-1">Select Repayment Tenure</h3>
        <p class="fw-section-sub mb-3">Customize your borrowing amount and select a repayment duration.</p>

        {{-- Amount input with strict max limit --}}
        <div class="fw-input-group mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="fw-label mb-0" style="font-weight:700;">Loan Amount (₹)</label>
                <span class="badge" style="background:#eff6ff; color:#1e40af; font-size:11.5px; font-weight:700; border:1px solid #bfdbfe; border-radius:6px; padding:3px 8px;">
                    Pre-Sanctioned Max: ₹ {{ number_format($maxLimit) }}
                </span>
            </div>
            <input type="number" 
                   name="amount" 
                   id="loanAmountInput" 
                   class="fw-input" 
                   style="font-size:20px; font-weight:800; color:var(--blue);" 
                   value="{{ min($amount, $maxLimit) }}" 
                   min="5000" 
                   max="{{ $maxLimit }}" 
                   required>
            <div id="amountLimitError" class="text-danger mt-1" style="font-size:12px; display:none; font-weight:600;">
                ⚠️ Amount cannot exceed your pre-sanctioned limit of ₹ {{ number_format($maxLimit) }}.
            </div>
        </div>

        {{-- Tenure selection chips --}}
        <div class="mb-2">
            <label class="fw-label mb-2" style="font-weight:700;">Select Repayment Tenure (Months)</label>
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px;">
                @foreach([6, 12, 18, 24, 36, 48] as $tOption)
                    <div class="fw-tenure-chip p-3 text-center" 
                         id="tenureChip_{{ $tOption }}"
                         onclick="selectTenure({{ $tOption }})"
                         style="border-radius:10px; border:1.5px solid {{ $tenure == $tOption ? 'var(--blue)' : '#cbd5e1' }}; background:{{ $tenure == $tOption ? 'var(--navy)' : '#ffffff' }}; color:{{ $tenure == $tOption ? '#ffffff' : 'var(--navy)' }}; font-weight:700; font-size:15px; cursor:pointer; transition:all 0.2s;">
                        {{ $tOption }} <span style="font-size:11px; font-weight:500; opacity:0.8;">mo</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 2. REPAYMENT SCHEDULE & CALCULATION (COMBINED ON SAME SCREEN) --}}
    <div class="fw-card mb-4">
        <h3 class="fw-section-title mb-1">Repayment Schedule</h3>
        <p class="fw-section-sub mb-3">Live amortized EMI calculation based on your selected tenure and amount.</p>
        
        <div class="p-4 mb-3 rounded text-center" style="background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:12px;">
            <span class="text-muted d-block mb-1" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Estimated Monthly EMI</span>
            <h2 style="color:var(--navy); font-size:32px; font-weight:800; margin:4px 0; letter-spacing:-0.5px;">
                ₹ <span id="displayEmi">{{ number_format($emi) }}</span>
                <span style="font-size:14px; font-weight:500; color:#64748b;">/ mo</span>
            </h2>
            <span class="badge" id="displayTenureBadge" style="background:#dbeafe; color:#1e40af; font-size:12px; font-weight:700; border-radius:20px; padding:3px 12px; margin-top:4px; display:inline-block;">
                For {{ $tenure }} Months Tenure
            </span>
        </div>

        <div class="mb-3">
            <div class="fw-info-row">
                <span class="fw-info-label">Selected Loan Amount</span>
                <span class="fw-info-value" id="displayAmount" style="color:var(--blue); font-size:15px; font-weight:700;">₹ {{ number_format(min($amount, $maxLimit)) }}</span>
            </div>
            <div class="fw-info-row">
                <span class="fw-info-label">Tenure Duration</span>
                <span class="fw-info-value" id="displayTenure">{{ $tenure }} Months</span>
            </div>
            <div class="fw-info-row">
                <span class="fw-info-label">Indicative Interest Rate</span>
                <span class="fw-info-value">8.5% – 9.5% p.a.</span>
            </div>
            <div class="fw-info-row">
                <span class="fw-info-label">Estimated Total Interest</span>
                <span class="fw-info-value" id="displayInterest">₹ {{ number_format($totalInterest) }}</span>
            </div>
            <div class="fw-info-row" style="border-top:1.5px dashed #cbd5e1; padding-top:12px; margin-top:4px;">
                <span class="fw-info-label" style="font-weight:700; color:var(--navy);">Total Repayable</span>
                <span class="fw-info-value" id="displayTotalRepayable" style="font-size:17px; color:var(--navy); font-weight:800;">₹ {{ number_format($totalRepayment) }}</span>
            </div>
        </div>

        <div class="fw-alert fw-alert-warn" style="font-size:11.5px; margin-bottom:0;">
            <strong>Underwriting Disclosure:</strong> The calculation above reflects an indicative amortization schedule. Final rate, tenure and fee are confirmed upon lender credit assessment.
        </div>
    </div>
</form>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" id="tenureSubmitBtn" form="tenureForm" class="fw-btn fw-btn-primary">
        Confirm Tenure & Proceed to Documents &rarr;
    </button>
    @if(!empty($application) && (empty($application->fee_payment_status) || $application->fee_payment_status !== 'paid'))
    <div style="text-align:center; margin-top:8px;">
        <a href="javascript:void(0)" onclick="if(confirm('Are you sure you want to cancel and withdraw this application ({{ $application->application_number ?? '' }})?\n\nThis will cancel your existing application so you can choose another loan.')) { window.location.href='{{ route('finance.withdraw_application', ['phone' => request('phone') ?? ($phone ?? '')]) }}'; }" style="color:#ef4444; font-size:12.5px; font-weight:600; text-decoration:none; display:inline-block; padding:4px 8px;">
            ✕ Cancel &amp; Withdraw Application
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const maxLimit = {{ $maxLimit }};
    let currentTenure = {{ $tenure }};

    function formatNumber(num) {
        return new Intl.NumberFormat('en-IN').format(num);
    }

    function updateCalculation() {
        const amtInput = document.getElementById('loanAmountInput');
        const errBox = document.getElementById('amountLimitError');
        const submitBtn = document.getElementById('tenureSubmitBtn');
        let amount = parseFloat(amtInput.value) || 0;

        if (amount > maxLimit) {
            errBox.style.display = 'block';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
            return;
        } else {
            errBox.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }

        if (amount < 5000) {
            amount = 5000;
        }

        // Standard personal loan indicative rate: 9% p.a. -> monthly rate = 0.09 / 12
        const annualRate = 0.09;
        const monthlyRate = annualRate / 12;
        const pow = Math.pow(1 + monthlyRate, currentTenure);
        const emi = pow > 1 ? (amount * monthlyRate * pow) / (pow - 1) : (amount / currentTenure);
        const totalRepayment = emi * currentTenure;
        const totalInterest = Math.max(0, totalRepayment - amount);

        document.getElementById('displayEmi').textContent = formatNumber(Math.round(emi));
        document.getElementById('displayTenureBadge').textContent = 'For ' + currentTenure + ' Months Tenure';
        document.getElementById('displayAmount').textContent = '₹ ' + formatNumber(Math.round(amount));
        document.getElementById('displayTenure').textContent = currentTenure + ' Months';
        document.getElementById('displayInterest').textContent = '₹ ' + formatNumber(Math.round(totalInterest));
        document.getElementById('displayTotalRepayable').textContent = '₹ ' + formatNumber(Math.round(totalRepayment));
    }

    window.selectTenure = function(months) {
        currentTenure = months;
        document.getElementById('tenureInput').value = months;
        document.querySelectorAll('.fw-tenure-chip').forEach(el => {
            el.style.border = '1.5px solid #cbd5e1';
            el.style.background = '#ffffff';
            el.style.color = 'var(--navy)';
        });
        const selected = document.getElementById('tenureChip_' + months);
        if (selected) {
            selected.style.border = '1.5px solid var(--blue)';
            selected.style.background = 'var(--navy)';
            selected.style.color = '#ffffff';
        }
        updateCalculation();
    };

    const amtInput = document.getElementById('loanAmountInput');
    amtInput.addEventListener('input', updateCalculation);
    amtInput.addEventListener('blur', function() {
        let val = parseFloat(this.value) || 0;
        if (val > maxLimit) {
            this.value = maxLimit;
            updateCalculation();
        } else if (val < 5000) {
            this.value = 5000;
            updateCalculation();
        }
    });

    document.getElementById('tenureForm').addEventListener('submit', function(e) {
        const val = parseFloat(amtInput.value) || 0;
        if (val > maxLimit) {
            e.preventDefault();
            amtInput.value = maxLimit;
            updateCalculation();
            alert('Loan amount cannot exceed your pre-sanctioned limit of ₹ ' + formatNumber(maxLimit));
        }
    });
});
</script>
@endpush

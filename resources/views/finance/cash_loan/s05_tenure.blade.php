@extends('finance.layouts.base')
@section('title', 'Select Amount & Tenure — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 4 of 12</span><span>33%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:33%;"></div></div>
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

    <div class="fw-card mt-2 mb-4">
        <h3 class="fw-section-title mb-1">Loan Amount & Tenure</h3>
        <p class="fw-section-sub">Select or enter the exact amount you wish to borrow.</p>
        
        <div class="mb-4">
            <label class="fw-label">Desired Loan Amount (₹)</label>
            <div style="position:relative;">
                <span style="position:absolute; left:16px; top:50%; transform:translateY(-50%); font-size:22px; font-weight:800; color:var(--navy);">₹</span>
                <input type="number" 
                       name="amount" 
                       id="loanAmountInput" 
                       class="fw-input" 
                       style="padding-left:38px; font-size:24px; font-weight:800; color:var(--navy); letter-spacing:-0.5px;" 
                       value="{{ $amount }}" 
                       min="5000" 
                       max="5000000" 
                       step="1000" 
                       required>
            </div>
            
            <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:12px; color:#64748b; font-weight:600;">
                <span>Min: ₹5,000</span>
                <span>Max: ₹20,00,000</span>
            </div>

            <!-- Quick Amount Chips -->
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:14px;">
                @foreach([25000, 50000, 100000, 200000, 500000] as $chipAmt)
                    <button type="button" 
                            class="fw-chip-btn" 
                            onclick="setLoanAmount({{ $chipAmt }})"
                            style="flex:1 1 28%; padding:8px 10px; border-radius:8px; border:1.5px solid {{ $amount == $chipAmt ? 'var(--blue)' : '#cbd5e1' }}; background:{{ $amount == $chipAmt ? '#eff6ff' : '#ffffff' }}; color:{{ $amount == $chipAmt ? 'var(--blue)' : '#334155' }}; font-weight:700; font-size:13px; cursor:pointer;">
                        ₹{{ number_format($chipAmt) }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mb-3 pt-2" style="border-top:1.5px solid #f1f5f9;">
            <label class="fw-label mb-2">Select Repayment Tenure (Months)</label>
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
</form>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="tenureForm" class="fw-btn fw-btn-primary">
        Calculate EMI & Continue &rarr;
    </button>
</div>
@endsection

@push('scripts')
<script>
function setLoanAmount(amt) {
    document.getElementById('loanAmountInput').value = amt;
}
function selectTenure(months) {
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
}
</script>
@endpush

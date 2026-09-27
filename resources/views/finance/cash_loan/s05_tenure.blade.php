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
    <input type="hidden" name="amount" value="{{ $amount }}">
    <input type="hidden" name="tenure" id="tenureInput" value="{{ $tenure }}">

    <div class="fw-card mt-2 mb-4">
        <h3 class="fw-section-title mb-3">Select Repayment Tenure</h3>

        {{-- Read-only loan amount display --}}
        <div class="fw-info-row mb-4" style="background:#eff6ff; border-radius:10px; padding:12px 16px; border:1.5px solid #bfdbfe;">
            <span class="fw-info-label" style="font-size:13px; color:#475569;">Loan Amount</span>
            <strong style="font-size:20px; font-weight:800; color:#f5a623;">₹ {{ number_format($amount) }}</strong>
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

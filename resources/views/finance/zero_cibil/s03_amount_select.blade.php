@extends('finance.layouts.base')
@section('title', 'Select Loan Amount — Fiinway')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 3 of 6')
@section('progress-pct', '50')
@section('progress', ' ')

@section('back')
<a href="{{ route('finance.zero_cibil.s02_kyc', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')

<p class="fw-section-title">Select Credit Limit</p>
<p class="fw-section-sub">Choose your desired limit between ₹20,000 – ₹2,00,000.</p>

<form id="amountForm" method="POST" action="{{ route('finance.zero_cibil.save_amount') }}">
    @csrf
    <input type="hidden" name="phone" value="{{ $phone }}">
    <input type="hidden" id="chosen-amount" name="amount" value="{{ $amount ?? 25000 }}">

    {{-- Amount Chips --}}
    <div class="fw-card">
        <div class="fw-card-title">Quick Select Amount</div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            @php
                $amounts = [
                    ['val' => '20000',  'label' => '₹20,000'],
                    ['val' => '25000',  'label' => '₹25,000'],
                    ['val' => '50000',  'label' => '₹50,000'],
                    ['val' => '75000',  'label' => '₹75,000'],
                    ['val' => '100000', 'label' => '₹1,00,000'],
                    ['val' => '200000', 'label' => '₹2,00,000'],
                ];
            @endphp
            @foreach($amounts as $a)
            <button type="button"
                    class="fw-btn fw-btn-outline"
                    style="padding:12px 10px; font-size:14px; font-weight:700;"
                    data-val="{{ $a['val'] }}"
                    onclick="selectChip(this, '{{ $a['val'] }}')">
                {{ $a['label'] }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Custom Amount Input --}}
    <div class="fw-card">
        <div class="fw-card-title">Or Enter Custom Amount</div>
        <div class="fw-input-group">
            <label class="fw-label" for="custom-amount">Credit Amount (₹)</label>
            <input type="number" id="custom-amount" class="fw-input"
                   placeholder="e.g. 35000" min="20000" max="200000" step="1000"
                   value="{{ $amount ?? 25000 }}"
                   oninput="selectCustom(this.value)">
        </div>
        <p style="font-size:12px; color:var(--gray3); margin:0;">Min ₹20,000 · Max ₹2,00,000</p>
    </div>

    <div class="fw-card" style="border-left:4px solid var(--blue2);">
        <div class="fw-info-row" style="padding:0; border:none;">
            <span class="fw-info-label">Daily Recovery Installment</span>
            <span class="fw-info-value" id="daily-recovery-label">₹1,000 / day</span>
        </div>
        <div class="fw-info-row" style="padding-top:8px; border:none;">
            <span class="fw-info-label">Interest Rate</span>
            <span class="fw-info-value" style="color:var(--green);">0% Strictly Interest-Free</span>
        </div>
    </div>
</form>

@endsection

@section('sticky-bottom')
<button type="submit" form="amountForm" class="fw-btn fw-btn-primary" id="continue-btn">
    Proceed to Fee Payment →
</button>
@endsection

@push('scripts')
<script>
function selectChip(btn, val) {
    document.querySelectorAll('[data-val]').forEach(function(b) {
        b.classList.remove('fw-btn-primary');
        b.classList.add('fw-btn-outline');
    });
    btn.classList.remove('fw-btn-outline');
    btn.classList.add('fw-btn-primary');

    document.getElementById('custom-amount').value = val;
    document.getElementById('chosen-amount').value = val;
}

function selectCustom(val) {
    document.querySelectorAll('[data-val]').forEach(function(b) {
        b.classList.remove('fw-btn-primary');
        b.classList.add('fw-btn-outline');
    });
    document.getElementById('chosen-amount').value = val;
}

// Initialize active chip on load
window.addEventListener('DOMContentLoaded', function() {
    var curVal = document.getElementById('chosen-amount').value;
    var match = document.querySelector('[data-val="' + curVal + '"]');
    if (match) {
        selectChip(match, curVal);
    }
});
</script>
@endpush

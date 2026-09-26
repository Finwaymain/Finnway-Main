@extends('finance.layouts.base')
@section('title', 'Select Loan Amount — Fiinway')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 2 of 6')
@section('progress-pct', '33')
@section('progress', ' ')

@section('back')
<a href="{{ route('finance.zero_cibil.s02_kyc', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')

<p class="fw-section-title">Select Amount</p>
<p class="fw-section-sub">Choose a credit limit between ₹20,000 – ₹2,00,000.</p>

{{-- Amount Chips --}}
<div class="fw-card">
    <div class="fw-card-title">Quick Select</div>
    <div class="fw-tenure-grid" id="amount-chips">
        @php
            $amounts = [
                ['val' => '20000',  'label' => '₹20,000'],
                ['val' => '50000',  'label' => '₹50,000'],
                ['val' => '75000',  'label' => '₹75,000'],
                ['val' => '100000', 'label' => '₹1,00,000'],
                ['val' => '150000', 'label' => '₹1,50,000'],
                ['val' => '200000', 'label' => '₹2,00,000'],
            ];
        @endphp
        @foreach($amounts as $a)
        <button type="button"
                class="fw-tenure-chip"
                data-val="{{ $a['val'] }}"
                onclick="selectChip(this)">
            {{ $a['label'] }}
        </button>
        @endforeach
    </div>
</div>

{{-- Selected display --}}
<div class="fw-card" id="selected-display" style="display:none">
    <div class="fw-amount-big" style="padding:8px 0">
        <div class="label">Selected Amount</div>
        <div class="amount" id="selected-label">—</div>
    </div>
</div>

{{-- Custom Amount --}}
<div class="fw-card">
    <div class="fw-card-title">Or Enter Custom Amount</div>
    <div class="fw-input-group">
        <label class="fw-label" for="custom-amount">Amount (₹)</label>
        <input type="number" id="custom-amount" class="fw-input"
               placeholder="e.g. 80000" min="20000" max="200000" step="1000"
               oninput="selectCustom(this.value)">
    </div>
    <p style="font-size:11px; color:var(--gray3)">Min ₹20,000 · Max ₹2,00,000</p>
</div>

<input type="hidden" id="chosen-amount" name="loan_amount" value="">

@endsection

@section('sticky-bottom')
<a href="{{ route('finance.zero_cibil.s04_fee_payment', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary" id="continue-btn">Continue →</a>
@endsection

@push('scripts')
<script>
var selected = null;

function selectChip(el) {
    document.querySelectorAll('#amount-chips .fw-tenure-chip').forEach(function(c){
        c.classList.remove('active');
    });
    el.classList.add('active');
    selected = el.dataset.val;
    document.getElementById('custom-amount').value = '';
    document.getElementById('chosen-amount').value = selected;
    showSelected(el.textContent.trim());
}

function selectCustom(val) {
    document.querySelectorAll('#amount-chips .fw-tenure-chip').forEach(function(c){
        c.classList.remove('active');
    });
    selected = val;
    document.getElementById('chosen-amount').value = val;
    if (val >= 20000 && val <= 200000) {
        showSelected('₹' + Number(val).toLocaleString('en-IN'));
    } else {
        document.getElementById('selected-display').style.display = 'none';
    }
}

function showSelected(label) {
    var disp = document.getElementById('selected-display');
    disp.style.display = '';
    document.getElementById('selected-label').textContent = label;
}
</script>
@endpush

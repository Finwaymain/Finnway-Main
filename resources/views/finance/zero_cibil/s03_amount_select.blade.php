@extends('finance.layouts.base')
@section('title', 'Select Credit Limit — Zero-CIBIL')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 3 of 6 · Credit Limit')
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
                <p class="fw-section-title" style="font-size:18px; margin-bottom:2px;">Select Credit Limit</p>
                <p style="font-size:11px; color:var(--gray3); margin:0;">Zero interest daily revolving wallet</p>
            </div>
            <span class="fw-badge-fintech">0% Interest</span>
        </div>

        <form id="amountForm" method="POST" action="{{ route('finance.zero_cibil.save_amount') }}">
            @csrf
            <input type="hidden" name="phone" value="{{ request('phone', $phone) }}">
            <input type="hidden" id="chosen-amount" name="amount" value="{{ $amount ?? 25000 }}">

            {{-- Dynamic Selection Hero Card --}}
            <div class="fw-bank-hero" style="padding:14px 16px; margin-bottom:8px; text-align:center;">
                <div style="font-size:10px; text-transform:uppercase; letter-spacing:0.8px; color:#94a3b8; font-weight:700;">
                    Selected Credit Limit
                </div>
                <div id="display-amount" style="font-size:28px; font-weight:800; color:#00e599; margin:2px 0 4px; letter-spacing:-0.5px;">
                    ₹{{ number_format($amount ?? 25000) }}
                </div>
                <div style="font-size:11px; color:#94a3b8; display:flex; justify-content:center; gap:12px;">
                    <span>Daily Repayment: <strong style="color:#ffffff;" id="display-daily">₹1,000 / day</strong></span>
                    <span>•</span>
                    <span style="color:#00e599; font-weight:700;">Strictly 0% Interest</span>
                </div>
            </div>

            {{-- Quick Chips (Compact 3x2 Grid) --}}
            <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:8px;">
                <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:6px;">
                    Quick Select Limit
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">
                    @php
                        $amounts = [
                            ['val' => '20000',  'label' => '₹20,000'],
                            ['val' => '25000',  'label' => '₹25,000'],
                            ['val' => '50000',  'label' => '₹50,000'],
                            ['val' => '75000',  'label' => '₹75,000'],
                            ['val' => '100000', 'label' => '₹1,00,000'],
                            ['val' => '200000', 'label' => '₹2,00,000'],
                        ];
                        $curr = $amount ?? 25000;
                    @endphp
                    @foreach($amounts as $a)
                    <button type="button"
                            class="fw-btn {{ $curr == $a['val'] ? 'fw-btn-primary' : 'fw-btn-outline' }}"
                            style="padding:8px 4px; font-size:12px; font-weight:700; border-radius:8px;"
                            data-val="{{ $a['val'] }}"
                            onclick="selectChip(this, '{{ $a['val'] }}')">
                        {{ $a['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Custom Amount Slider / Input --}}
            <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <label class="fw-label" style="font-size:10px; margin:0;" for="custom-amount">Custom Amount (₹)</label>
                    <span style="font-size:10px; color:var(--gray3);">Min ₹20k · Max ₹2L</span>
                </div>
                <input type="number" id="custom-amount" class="fw-input"
                       style="padding:8px 10px; font-size:14px; font-weight:700;"
                       placeholder="e.g. 35000" min="20000" max="200000" step="5000"
                       value="{{ $curr }}"
                       oninput="selectCustom(this.value)">
            </div>
        </form>
    </div>

    {{-- Bottom Action (Single Viewport Submission with 20s Stage Loader) --}}
    <div style="padding-top:6px;">
        <button type="button" onclick="submitAmountSelection()" class="fw-btn fw-btn-primary" style="padding:11px 14px; font-size:14px; font-weight:700;">
            Proceed to Verification & Underwriting →
        </button>
    </div>
</div>

<script>
function updateDisplays(val) {
    var num = parseInt(val) || 25000;
    document.getElementById('chosen-amount').value = num;
    document.getElementById('custom-amount').value = num;
    document.getElementById('display-amount').textContent = '₹' + num.toLocaleString('en-IN');
    
    // Daily repayment indicative calculation (e.g. ₹1,000 for standard amounts)
    var daily = Math.max(500, Math.round(num / 30 / 100) * 100);
    document.getElementById('display-daily').textContent = '₹' + daily.toLocaleString('en-IN') + ' / day';
}

function selectChip(btn, val) {
    document.querySelectorAll('[data-val]').forEach(function(b) {
        b.classList.remove('fw-btn-primary');
        b.classList.add('fw-btn-outline');
    });
    btn.classList.remove('fw-btn-outline');
    btn.classList.add('fw-btn-primary');
    updateDisplays(val);
}

function selectCustom(val) {
    document.querySelectorAll('[data-val]').forEach(function(b) {
        b.classList.remove('fw-btn-primary');
        b.classList.add('fw-btn-outline');
    });
    updateDisplays(val);
}

function submitAmountSelection() {
    var form = document.getElementById('amountForm');
    // Trigger 20-30s Stage Transition Loading Modal (Req 5)
    window.showBankingStageLoader(
        "Persisting Sanction Parameters",
        "Calculating zero-CIBIL daily recovery schedule & quota...",
        22,
        function() {
            form.submit();
        }
    );
}
</script>
@endsection

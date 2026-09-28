@extends('finance.layouts.base')
@section('title', 'Partner Verification — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 10 of 12</span><span style="float:right;">83%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:83%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s11_partner_dashboard', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-3 mb-4">
    <h3 class="fw-h3 mb-3">Enter Verification Details</h3>
    <p class="text-muted small mb-4">Please verify your details before proceeding to the selected lender's portal.</p>

    <!-- Selected Lending Institution Banner -->
    <div class="d-flex align-items-center p-3 mb-4 border rounded" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
        @if(!empty($selectedPartner->logo))
            <img src="{{ asset('storage/' . $selectedPartner->logo) }}" alt="{{ $selectedPartner->name }}" style="width:44px; height:44px; object-fit:contain; border-radius:6px; margin-right:12px; border:1px solid #bbf7d0; background:#fff; padding:2px;">
        @else
            <div style="width:44px; height:44px; background:#002147; color:#fff; border-radius:6px; margin-right:12px; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:700;">
                {{ strtoupper(substr($selectedPartner->name ?? 'BK', 0, 2)) }}
            </div>
        @endif
        <div>
            <div style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase; letter-spacing:0.5px;">Selected Lending Partner</div>
            <strong style="font-size:16px; color:#0f172a;">{{ $selectedPartner->name ?? 'Banking Partner' }}</strong>
            <div style="font-size:11px; color:#64748b;">Rate: {{ $selectedPartner->interest_rate_display ?: '10.5% p.a.' }} &bull; Max: {{ $selectedPartner->tenure_display ?: '60 Mos' }}</div>
        </div>
    </div>

    <div class="mb-4 p-3 border rounded" style="background:#f8f9fa;">
        <div class="mb-3">
            <label class="form-label small text-muted">Full Name (Auto-filled)</label>
            <input type="text" class="fw-input w-100 p-2 border rounded bg-white" value="{{ $applicantName }}" readonly style="font-weight:600; color:#0f172a;">
        </div>
        <div class="mb-3">
            <label class="form-label small text-muted">Application Number (Auto-filled)</label>
            <input type="text" class="fw-input w-100 p-2 border rounded bg-white" value="{{ $appNumber }}" readonly style="font-family:monospace; font-weight:700; color:#002147;">
        </div>
        <div class="mb-2">
            <label class="form-label small text-muted">Mobile Number</label>
            <input type="tel" class="fw-input w-100 p-2 border rounded bg-white" value="{{ $applicantPhone }}" readonly style="font-weight:600; color:#0f172a;">
        </div>
    </div>

    <div class="p-3 rounded" style="background:#fff3cd; border:1px solid #ffeeba;">
        <p class="mb-0 small" style="color:#856404; font-size:12px; line-height:1.5;">
            <strong>Important:</strong> Once you submit and proceed, <strong>{{ $selectedPartner->name ?? 'the selected partner' }}</strong> will become ACTIVE for this application, and all other lender options will be permanently LOCKED.
        </p>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s13_partner_redirect', ['phone' => request('phone'), 'partner_id' => $selectedPartner->id ?? '']) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">
        Submit &amp; Proceed &rarr;
    </a>
</div>
@endsection

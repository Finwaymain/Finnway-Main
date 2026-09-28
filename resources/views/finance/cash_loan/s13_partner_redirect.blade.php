@extends('finance.layouts.base')
@section('title', 'Partner Redirect — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 11 of 12</span><span style="float:right;">91%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:91%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s11_partner_dashboard', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-4 mb-5 pb-4 text-center">
    <h3 class="fw-h3 mb-4">Selected Lending Partner</h3>

    <div class="p-4 border rounded mb-4" style="background:#f8f9fa; border:2px solid #002147 !important;">
        @if(!empty($selectedPartner->logo))
            <img src="{{ asset('storage/' . $selectedPartner->logo) }}" alt="{{ $selectedPartner->name }}" style="width:60px; height:60px; object-fit:contain; border-radius:8px; margin:0 auto 15px auto; display:block; border:1px solid #ddd; background:#fff; padding:4px;">
        @else
            <div style="width:60px; height:60px; background:#002147; color:#fff; border-radius:8px; margin:0 auto 15px auto; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:700;">
                {{ strtoupper(substr($selectedPartner->name ?? 'BK', 0, 2)) }}
            </div>
        @endif
        <h4 style="margin:0 0 10px 0; color:#002147; font-weight:700;">{{ $selectedPartner->name ?? ($application->selected_lender_name ?? 'Lending Partner') }}</h4>
        <span class="badge" style="background:#28a745; color:white; padding:5px 12px; border-radius:12px; font-size:12px; font-weight:700;">✓ ACTIVE FOR THIS APPLICATION</span>
    </div>

    <div class="mb-4 text-start">
        <p class="small text-muted text-center">All other available partners for this application are now <strong class="text-danger">LOCKED</strong>.</p>
    </div>

    <p class="small text-muted mb-4" style="line-height:1.5;">
        You will now be securely redirected to the selected lender's official portal to complete your loan documentation and e-signing process.
    </p>

    <a href="{{ route('finance.cash_loan.s14_lender_webview', ['phone' => request('phone'), 'partner_id' => $selectedPartner->id ?? '']) }}" class="fw-btn fw-btn-primary w-100 d-block p-3" style="font-size:16px;">
        Proceed to {{ $selectedPartner->name ?? 'Lender Portal' }} &rarr;
    </a>
</div>
@endsection

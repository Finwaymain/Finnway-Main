@extends('finance.layouts.base')
@section('title', 'Loan Type Consent — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 1 of 12</span><span>8%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:8%;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s01_apply', ['phone' => $phone]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<form id="consentForm" method="POST" action="{{ route('finance.cash_loan.save_step') }}">
    @csrf
    <input type="hidden" name="step" value="s02">
    <input type="hidden" name="phone" value="{{ $phone }}">
    <input type="hidden" name="loan_type" value="{{ $loanType ?? 'low_cibil' }}">

    <div class="fw-card mb-4 mt-2">
        <h3 class="fw-section-title mb-2">Loan Program Consent</h3>
        <p class="fw-section-sub">Please review the terms of the credit identification platform before proceeding.</p>
        
        <div class="p-3 mb-4 rounded" style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:12px;">
            <div style="font-weight:700; color:var(--navy); font-size:14px; margin-bottom:6px;">
                Credit Program: {{ ($loanType ?? '') === 'good_cibil' ? 'Prime Cash Loan' : 'Low CIBIL Cash Credit' }}
            </div>
            <p class="text-muted mb-0" style="font-size:13px; line-height:1.6;">
                Fiinway functions as a compliant technology interface connecting applicants with RBI-registered lending partners (Banks & NBFCs). All loans are directly underwritten and disbursed by selected lending institutions.
            </p>
        </div>

        <div class="fw-consent" style="border:1.5px solid #cbd5e1; border-radius:12px; padding:16px;">
            <input type="checkbox" id="consentCheck" name="consent" value="1" required checked style="width:18px; height:18px; margin-top:2px;">
            <label for="consentCheck" style="font-size:13px; color:var(--navy); font-weight:500; cursor:pointer;">
                I understand that an underwriting & processing fee is applicable to activate partner matching and lender verification services.
            </label>
        </div>
    </div>
</form>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="consentForm" class="fw-btn fw-btn-primary">Accept & Continue &rarr;</button>
</div>
@endsection

@extends('finance.layouts.base')
@section('title', 'Loan Type Consent — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 1 of 12</span><span style="float:right;">8%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:8%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s01_apply', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mb-4 mt-3">
    <h3 class="fw-h3 mb-3">Loan Details & Consent</h3>
    
    <div class="p-3 mb-4 rounded" style="background:#f8f9fa; border:1px solid #eee;">
        <strong>Selected Loan Type</strong>
        <p class="mt-2 text-muted" style="font-size:14px; line-height:1.5;">
            Fiinway system aapki application ke basis par multiple Bank/NBFC partners ke available loan options identify karega. Where applicable eligibility ke according nominal documentation ke saath loan option available ho sakta hai. Higher limits depend on credit profile and CIBIL score.
        </p>
    </div>

    <label class="d-flex align-items-start" style="cursor:pointer;">
        <input type="checkbox" class="mt-1 me-2" required>
        <span style="font-size:14px; color:#333;">I understand that a processing fee is applicable for this service and I am interested in proceeding.</span>
    </label>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s03_applicant_details', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Accept & Continue</a>
</div>
@endsection

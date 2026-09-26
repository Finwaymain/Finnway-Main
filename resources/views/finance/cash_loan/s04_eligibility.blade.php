@extends('finance.layouts.base')
@section('title', 'Indicative Eligibility — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 3 of 12</span><span>25%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:25%;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s03_applicant_details', ['phone' => $phone, 'amount' => $amount]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-2 mb-4 text-center">
    <div class="p-4" style="background:#eff6ff; border-radius:12px; border:1.5px solid #bfdbfe;">
        <p class="mb-1 text-muted" style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Preliminary Pre-Sanction Limit</p>
        <h2 style="color:var(--navy); font-size:32px; font-weight:800; margin:10px 0;">
            ₹ {{ number_format(max($amount, ($loanType ?? '') === 'good_cibil' ? 2000000 : 400000)) }}
        </h2>
        <span class="badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:700;">
            ✓ High Eligibility Match
        </span>
    </div>

    <div class="mt-4 text-start">
        <div class="fw-info-row">
            <span class="fw-info-label">Applicant Name</span>
            <span class="fw-info-value">{{ $customer->full_name ?? ($application->applicant_name ?? 'Applicant') }}</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Desired Amount</span>
            <span class="fw-info-value" style="color:var(--blue);">₹ {{ number_format($amount) }}</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Credit Profile</span>
            <span class="fw-info-value">{{ ($loanType ?? '') === 'good_cibil' ? 'Prime (Good CIBIL)' : 'Standard (Low CIBIL)' }}</span>
        </div>

        <div class="fw-alert fw-alert-info mt-3 mb-0" style="font-size:12px;">
            Your profile meets the initial criteria for lending partner underwriting. You can customize your exact loan amount and repayment tenure on the next screen.
        </div>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s05_tenure', ['phone' => $phone, 'amount' => $amount, 'tenure' => $tenure]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">
        Customize Amount & Tenure &rarr;
    </a>
</div>
@endsection

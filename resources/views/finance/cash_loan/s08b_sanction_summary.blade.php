@extends('finance.layouts.base')
@section('title', 'Sanction Summary — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 7 of 11</span><span>64%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:64%;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s08_ready', ['phone' => request('phone'), 'amount' => $amount, 'tenure' => $tenure]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-3 mb-4 text-center">
    {{-- Status Badge --}}
    <div style="display:inline-flex; align-items:center; justify-content:center; background:#ecfdf5; border:1.5px solid #a7f3d0; border-radius:24px; padding:8px 20px; margin-bottom:20px;">
        <span style="font-size:18px; margin-right:8px;">🏦</span>
        <span style="color:#065f46; font-weight:800; font-size:14px; letter-spacing:0.3px;">LOAN SANCTIONED</span>
    </div>

    <h3 style="color:var(--navy); font-size:18px; font-weight:800; margin-bottom:4px;">Sanction Letter Summary</h3>
    <p style="color:#64748b; font-size:13px; margin-bottom:24px;">Review your loan details before proceeding to fee payment.</p>
</div>

{{-- Application Details --}}
<div class="fw-card mb-3">
    <h5 style="font-size:12px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.8px; font-weight:700; border-bottom:1px solid #f1f5f9; padding-bottom:10px; margin-bottom:12px;">Application Details</h5>

    <div class="fw-info-row">
        <span class="fw-info-label">App. Number</span>
        <span class="fw-info-value" style="font-size:13px; color:var(--navy); font-weight:700;">
            {{ $application->application_number ?? ('FW-' . str_pad($application->id ?? 0, 7, '0', STR_PAD_LEFT)) }}
        </span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Applicant</span>
        <span class="fw-info-value">{{ $customer->full_name ?? ($application->applicant_name ?? 'Applicant') }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Loan Type</span>
        <span class="fw-info-value">{{ ($loanType ?? '') === 'good_cibil' ? 'Prime Cash Loan' : 'Standard Cash Loan' }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Status</span>
        <span style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700;">✓ Sanctioned</span>
    </div>
</div>

{{-- Loan Financials --}}
<div class="fw-card mb-3">
    <h5 style="font-size:12px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.8px; font-weight:700; border-bottom:1px solid #f1f5f9; padding-bottom:10px; margin-bottom:12px;">Loan Financials</h5>

    <div class="fw-info-row">
        <span class="fw-info-label">Requested Amount</span>
        <span class="fw-info-value" style="color:var(--navy); font-weight:700;">₹ {{ number_format($amount) }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Approved Amount</span>
        <span class="fw-info-value" style="color:#065f46; font-weight:800; font-size:16px;">₹ {{ number_format($amount) }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Repayment Tenure</span>
        <span class="fw-info-value">{{ $tenure }} Months</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Monthly EMI</span>
        <span class="fw-info-value" style="color:var(--blue); font-weight:700;">₹ {{ number_format($emi) }} / mo</span>
    </div>
    <div class="fw-info-row" style="border-top:1px dashed #e2e8f0; padding-top:12px; margin-top:4px;">
        <span class="fw-info-label">Processing Fee</span>
        <span class="fw-info-value" style="color:#b45309; font-weight:700;">₹ {{ number_format($totalFee, 2) }}</span>
    </div>
</div>

{{-- Lender Info --}}
@if($hasLender ?? false)
<div class="fw-card mb-3">
    <h5 style="font-size:12px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.8px; font-weight:700; border-bottom:1px solid #f1f5f9; padding-bottom:10px; margin-bottom:12px;">Lender Partner</h5>
    <div class="fw-info-row">
        <span class="fw-info-label">Lender</span>
        <span class="fw-info-value">{{ $product->lender_name ?? 'Fiinway Lending Partner' }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Interest Rate</span>
        <span class="fw-info-value">{{ $product->interest_rate ?? '18' }}% p.a.</span>
    </div>
</div>
@endif

<div class="fw-alert fw-alert-info" style="font-size:12px; margin-bottom:4px;">
    A one-time processing fee of <strong>₹ {{ number_format($totalFee, 2) }}</strong> is required to proceed. This covers lender platform access and KYC verification charges.
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s09_fee_payment', ['phone' => request('phone'), 'amount' => $amount, 'tenure' => $tenure]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">
        Pay Processing Fee (₹{{ number_format($totalFee, 2) }}) →
    </a>
</div>
@endsection

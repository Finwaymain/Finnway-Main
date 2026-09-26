@extends('finance.layouts.base')
@section('title', 'Estimated EMI — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 5 of 12</span><span>41%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:41%;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s05_tenure', ['phone' => $phone, 'amount' => $amount, 'tenure' => $tenure]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-2 mb-4">
    <h3 class="fw-section-title mb-1">Repayment Schedule</h3>
    <p class="fw-section-sub">Estimated amortized EMI calculation for your selected loan amount.</p>
    
    <div class="p-4 mb-4 rounded text-center" style="background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:12px;">
        <span class="text-muted d-block mb-1" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Estimated Monthly EMI</span>
        <h2 style="color:var(--navy); font-size:34px; font-weight:800; margin:4px 0; letter-spacing:-0.5px;">
            ₹ {{ number_format($emi) }}
            <span style="font-size:14px; font-weight:500; color:#64748b;">/ mo</span>
        </h2>
        <span class="badge" style="background:#dbeafe; color:#1e40af; font-size:12px; font-weight:700; border-radius:20px; padding:3px 12px; margin-top:4px; display:inline-block;">
            For {{ $tenure }} Months Tenure
        </span>
    </div>

    <div class="mb-4">
        <div class="fw-info-row">
            <span class="fw-info-label">Selected Loan Amount</span>
            <span class="fw-info-value" style="color:var(--blue); font-size:16px;">₹ {{ number_format($amount) }}</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Tenure Duration</span>
            <span class="fw-info-value">{{ $tenure }} Months</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Indicative Interest Rate</span>
            <span class="fw-info-value">8.5% – 9.5% p.a.</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Estimated Total Interest</span>
            <span class="fw-info-value">₹ {{ number_format($totalInterest) }}</span>
        </div>
        <div class="fw-info-row" style="border-top:1.5px dashed #cbd5e1; padding-top:12px; margin-top:4px;">
            <span class="fw-info-label" style="font-weight:700; color:var(--navy);">Total Repayable</span>
            <span class="fw-info-value" style="font-size:17px; color:var(--navy); font-weight:800;">₹ {{ number_format($totalRepayment) }}</span>
        </div>
    </div>

    <div class="fw-alert fw-alert-warn" style="font-size:12px; margin-bottom:0;">
        <strong>Underwriting Disclosure:</strong> The calculation above reflects an indicative amortization schedule. Final rate, tenure and fee are confirmed upon lender credit assessment.
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s07_documents', ['phone' => $phone, 'amount' => $amount, 'tenure' => $tenure]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">
        Proceed to Document Checklist &rarr;
    </a>
</div>
@endsection

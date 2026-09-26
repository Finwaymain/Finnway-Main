@extends('finance.layouts.base')
@section('title', 'Indicative Eligibility — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 3 of 12</span><span style="float:right;">25%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:25%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s03_applicant_details', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-3 mb-4 text-center">
    <div class="p-4" style="background:#f0f5ff; border-radius:8px; border:1px solid #d0e0ff;">
        <p class="mb-1 text-muted">You may be eligible for up to</p>
        <h2 style="color:#002147; margin:10px 0;">₹ {{ request('loan_type') == 'good_cibil' ? '20,00,000' : '2,00,000' }}</h2>
        <span class="badge bg-success" style="background-color:#28a745; color:white; padding:5px 10px; border-radius:20px; font-size:12px;">Indicative Eligibility</span>
    </div>

    <div class="mt-4 text-start">
        <ul style="list-style:none; padding:0; margin:0;">
            <li class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                <span class="text-muted">Loan Type</span>
                <strong>Cash Loan</strong>
            </li>
            <li class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                <span class="text-muted">Profile Category</span>
                <strong>{{ request('loan_type') == 'good_cibil' ? 'Good CIBIL' : 'Low CIBIL' }}</strong>
            </li>
        </ul>
        <p class="mt-3 small" style="color:#666; font-style:italic;">
            Note: The amount shown above is an indicative eligibility limit. The final sanctioned loan amount is subject to lender verification and approval.
        </p>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s05_tenure', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Continue</a>
</div>
@endsection

@extends('finance.layouts.base')
@section('title', 'Estimated EMI — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 5 of 12</span><span style="float:right;">41%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:41%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s05_tenure', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-3 mb-4">
    <h3 class="fw-h3 mb-4">Repayment Estimate</h3>
    
    <div class="p-3 mb-4 rounded text-center" style="background:#f0f5ff; border:1px solid #d0e0ff;">
        <span class="text-muted d-block mb-1">Estimated Monthly EMI</span>
        <h2 style="color:#002147; margin:0;">₹ 11,900 - ₹ 12,500</h2>
        <span class="small text-muted">for 18 months</span>
    </div>

    <div class="mb-4">
        <ul style="list-style:none; padding:0; margin:0;">
            <li class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                <span class="text-muted">Loan Amount</span>
                <strong>₹ 2,00,000</strong>
            </li>
            <li class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                <span class="text-muted">Indicative Interest Rate</span>
                <strong>8% - 9% p.a.</strong>
            </li>
            <li class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                <span class="text-muted">Estimated Total Repayment</span>
                <strong>₹ 2,14,200 - ₹ 2,25,000</strong>
            </li>
        </ul>
    </div>

    <div class="p-3 rounded" style="background:#fff3cd; border:1px solid #ffeeba;">
        <p class="mb-0 small" style="color:#856404; font-size:12px;">
            <strong>Note:</strong> This is an Estimated / Indicative EMI. The final interest rate, processing fee, and repayment schedule will be determined by the lending partner upon full verification of your application.
        </p>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s07_documents', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Continue</a>
</div>
@endsection

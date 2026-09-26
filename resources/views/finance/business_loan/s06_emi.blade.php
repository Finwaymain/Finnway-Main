@extends('finance.layouts.base')
@section('title', 'Estimated EMI — Fiinway')
@section('header-sub', 'Business Loan')
@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 5 of 10</span><span>50%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:50%"></div></div>
</div>
@endsection
@section('back')
<a href="{{ route('finance.business_loan.s05_tenure', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Estimated Repayment</h2>
    
    <div style="background:#f9f9f9; padding:15px; border-radius:8px;">
        <p><strong>Loan Amount:</strong> ₹15,00,000</p>
        <p><strong>Tenure:</strong> 36 Months</p>
        <p><strong>Indicative Interest Rate:</strong> 15% p.a.</p>
        <p><strong>Estimated EMI:</strong> ₹52,000</p>
        <p><strong>Total Repayment:</strong> ₹18,72,000</p>
    </div>
    
    <p style="margin-top:20px; font-size:0.9em; color:#666; text-align:center;">
        Indicative EMI — Final terms from lending partner.
    </p>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s07_documents', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

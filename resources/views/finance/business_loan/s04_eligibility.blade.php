@extends('finance.layouts.base')
@section('title', 'Eligibility — Fiinway')
@section('header-sub', 'Business Loan')
@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 3 of 10</span><span>30%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:30%"></div></div>
</div>
@endsection
@section('back')
<a href="{{ route('finance.business_loan.s03_loan_requirement', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading">Indicative Eligibility</h2>
    
    <p>You may be eligible for:</p>
    <div class="fw-amount-big" style="font-size:32px; font-weight:bold; margin:20px 0;">₹{{ $ctx['eligible_amount'] ?? '15,00,000' }}</div>
    <p style="color:#666; font-size:0.9em;">(Max limit: ₹2 Crore)</p>
    
    <div style="margin-top:20px; background:#f4f4f4; padding:10px; border-radius:5px;">
        Indicative — Final amount subject to lender verification.
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s05_tenure', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

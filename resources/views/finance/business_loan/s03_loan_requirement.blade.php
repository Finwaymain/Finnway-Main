@extends('finance.layouts.base')
@section('title', 'Requirement — Fiinway')
@section('header-sub', 'Business Loan')
@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 2 of 10</span><span>20%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:20%"></div></div>
</div>
@endsection
@section('back')
<a href="{{ route('finance.business_loan.s02_business_details', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Loan Requirement</h2>
    
    <div class="fw-form-group">
        <label>Required Loan Amount (₹5L - ₹2Cr)</label>
        <input type="number" class="fw-input" min="500000" max="20000000" value="1000000">
    </div>
    
    <div class="fw-form-group">
        <label>Loan Purpose (Confirm)</label>
        <input type="text" class="fw-input" value="Working Capital" readonly>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s04_eligibility', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

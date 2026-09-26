@extends('finance.layouts.base')
@section('title', 'Ready — Fiinway')
@section('header-sub', 'Business Loan')
@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 8 of 10</span><span>80%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:80%"></div></div>
</div>
@endsection
@section('back')
<a href="{{ route('finance.business_loan.s08_verification', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading" style="color:green;">Application Review Completed</h2>
    
    <div style="background:#f9f9f9; padding:15px; border-radius:8px; margin:20px 0;">
        <p><strong>Requested Amount:</strong> ₹20,00,000</p>
        <p><strong>Indicative Amount:</strong> ₹15,00,000</p>
        <p><strong>Tenure:</strong> 36 Months</p>
        <p><strong>Estimated EMI:</strong> ₹52,000</p>
        <p><strong>Processing Fee:</strong> ₹5,000</p>
    </div>
    
    <p style="text-align:center; font-weight:bold; color:#004080;">Status: Ready for Final Lender Processing</p>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s10_fee_payment', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Proceed to Fee Payment</a>
</div>
@endsection

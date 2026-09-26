@extends('finance.layouts.base')
@section('title', 'Approved — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s15_lender_processing', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading" style="color:green;">🎉 Business Loan Approved</h2>
    
    <div class="fw-amount-big" style="font-size:32px; font-weight:bold; margin:20px 0;">₹15,00,000</div>
    
    <div style="text-align:left; background:#f9f9f9; padding:15px; border-radius:8px;">
        <p><strong>Lender Name:</strong> Bajaj Finserv</p>
        <p><strong>Application No.:</strong> FIIN-BL-2026-123456</p>
        <p><strong>Approved Amount:</strong> ₹15,00,000</p>
        <p><strong>Tenure:</strong> 36 Months</p>
        <p><strong>EMI:</strong> ₹52,000</p>
        <p><strong>Interest Rate:</strong> 15% p.a.</p>
        <p><strong>Processing Charges:</strong> ₹30,000 (2%)</p>
        <p style="margin-top:10px;"><a href="#" style="color:#004080;">View Agreement/Offer Details</a></p>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s18_bank_details', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Proceed to Disbursement</a>
</div>
@endsection

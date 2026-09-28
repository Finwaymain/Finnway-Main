@extends('finance.layouts.base')
@section('title', 'Approved — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s15_lender_processing', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading" style="color:green;">🎉 Business Loan Approved</h2>
    
    <div class="fw-amount-big" style="font-size:32px; font-weight:bold; margin:20px 0;">₹{{ number_format($amount ?? 1500000) }}</div>
    
    <div style="text-align:left; background:#f9f9f9; padding:15px; border-radius:8px;">
        <p><strong>Lender Name:</strong> {{ $selectedPartner->name ?? ($application->selected_lender_name ?? 'Lending Partner') }}</p>
        <p><strong>Application No.:</strong> {{ $appNumber }}</p>
        <p><strong>Approved Amount:</strong> ₹{{ number_format($amount ?? 1500000) }}</p>
        <p><strong>Tenure:</strong> {{ $tenure ?? 36 }} Months</p>
        <p><strong>EMI:</strong> ₹{{ number_format($emi ?? 52000) }}</p>
        <p><strong>Interest Rate:</strong> {{ $selectedPartner->interest_rate_display ?? '15% p.a.' }}</p>
        <p><strong>Processing Charges:</strong> ₹{{ number_format($totalFee ?? 30000) }}</p>
        <p style="margin-top:10px;"><a href="#" style="color:#004080;">View Agreement/Offer Details</a></p>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s18_bank_details', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Proceed to Disbursement</a>
</div>
@endsection

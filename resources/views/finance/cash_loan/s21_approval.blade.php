@extends('finance.layouts.base')
@section('title', 'Approved — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s20_processing', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading" style="color:green;">🎉 Congratulations! Loan Approved</h2>
    
    <div class="fw-amount-big" style="font-size:32px; font-weight:bold; margin:20px 0;">₹{{ number_format($amount ?? 25000) }}</div>
    
    <div style="text-align:left; background:#f9f9f9; padding:15px; border-radius:8px;">
        <p><strong>Lender:</strong> {{ $selectedPartner->name ?? ($application->selected_lender_name ?? 'Lending Partner') }}</p>
        <p><strong>Application No.:</strong> {{ $appNumber }}</p>
        <p><strong>Tenure:</strong> {{ $tenure ?? 12 }} Months</p>
        <p><strong>EMI:</strong> ₹{{ number_format($emi ?? 0) }}</p>
        <p><strong>Interest Rate:</strong> {{ $selectedPartner->interest_rate_display ?? '12% - 16% p.a.' }}</p>
        <p><strong>Processing Charges:</strong> ₹{{ number_format($totalFee ?? 999) }}</p>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s22_bank_details', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Proceed to Disbursement</a>
</div>
@endsection

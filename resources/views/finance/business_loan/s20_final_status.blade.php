@extends('finance.layouts.base')
@section('title', 'Final Status — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s19_disbursement', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading" style="color:green;">🟢 DISBURSED</h2>
    
    <div class="fw-amount-big" style="font-size:32px; font-weight:bold; margin:20px 0;">₹{{ number_format($amount ?? 1500000) }}</div>
    
    <div style="text-align:left; background:#f9f9f9; padding:15px; border-radius:8px;">
        <p><strong>Lender:</strong> {{ $selectedPartner->name ?? ($application->selected_lender_name ?? 'Lending Partner') }}</p>
        <p><strong>Application No.:</strong> {{ $appNumber }}</p>
        <p><strong>Disbursement Date:</strong> {{ date('d M Y') }}</p>
        <p><strong>Tenure:</strong> {{ $tenure ?? 36 }} Months</p>
        <p><strong>EMI:</strong> ₹{{ number_format($emi ?? 52000) }}</p>
        <p><strong>Interest Rate:</strong> {{ $selectedPartner->interest_rate_display ?? '15% p.a.' }}</p>
        <p><strong>Account:</strong> XXXX-XXXX-9876</p>
        <p><strong>Loan Reference Number:</strong> LRN-87654321</p>
    </div>
</div>
@endsection

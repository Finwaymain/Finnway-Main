@extends('finance.layouts.base')
@section('title', 'Final Status — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s19_disbursement', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading" style="color:green;">🟢 DISBURSED</h2>
    
    <div class="fw-amount-big" style="font-size:32px; font-weight:bold; margin:20px 0;">₹15,00,000</div>
    
    <div style="text-align:left; background:#f9f9f9; padding:15px; border-radius:8px;">
        <p><strong>Lender:</strong> Bajaj Finserv</p>
        <p><strong>Application No.:</strong> FIIN-BL-2026-123456</p>
        <p><strong>Disbursement Date:</strong> {{ date('d M Y') }}</p>
        <p><strong>Tenure:</strong> 36 Months</p>
        <p><strong>EMI:</strong> ₹52,000</p>
        <p><strong>Interest Rate:</strong> 15% p.a.</p>
        <p><strong>Account:</strong> XXXX-XXXX-9876</p>
        <p><strong>Loan Reference Number:</strong> LRN-87654321</p>
    </div>
</div>
@endsection

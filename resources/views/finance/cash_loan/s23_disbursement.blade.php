@extends('finance.layouts.base')
@section('title', 'Disbursement — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s22_bank_details', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading" style="color:green;">✓ Disbursement Request Submitted</h2>
    
    <div style="text-align:left; background:#f9f9f9; padding:15px; border-radius:8px; margin:20px 0;">
        <p><strong>Application No.:</strong> FIIN-CL-123456</p>
        <p><strong>Approved Amount:</strong> ₹50,000</p>
        <p><strong>Disbursement Account:</strong> XXXX-XXXX-1234</p>
    </div>
    
    <p style="color:orange; font-weight:bold; font-size:18px;">Status: Disbursement Processing</p>
    
    <p style="margin-top:20px; font-weight:bold;">Expected timeline: Up to 4 Hours*</p>
    <p style="font-size:0.8em; color:#666;">*Actual timing subject to lender/bank processing</p>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="#" class="fw-btn fw-btn-primary">Track Disbursement</a>
</div>
@endsection

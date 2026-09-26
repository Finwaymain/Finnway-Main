@extends('finance.layouts.base')
@section('title', 'Disbursement — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s18_bank_details', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading" style="color:green;">✓ Disbursement Submitted</h2>
    
    <div style="text-align:left; background:#f9f9f9; padding:15px; border-radius:8px; margin:20px 0;">
        <p><strong>Application No.:</strong> FIIN-BL-2026-123456</p>
        <p><strong>Approved Amount:</strong> ₹15,00,000</p>
        <p><strong>Bank Account:</strong> XXXX-XXXX-9876</p>
    </div>
    
    <p style="color:orange; font-weight:bold; font-size:18px;">🟡 Disbursement Processing</p>
    <p style="margin-top:15px; font-size:0.9em; color:#666;">Note: Timing subject to lender/payment system SLA</p>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s20_final_status', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Track Disbursement</a>
</div>
@endsection

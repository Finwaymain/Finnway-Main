@extends('finance.layouts.base')
@section('title', 'Processing — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s19_lender_review', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading">Application Processing</h2>
    <p style="color:orange; font-weight:bold;">Status: Processing in Progress</p>
    
    <p><strong>Application No.:</strong> FIIN-CL-123456</p>
    
    <div class="fw-timer-note" style="margin:20px 0; font-weight:bold;">
        Estimated Processing Window: Up to 20 Minutes
    </div>

    <ul style="list-style:none; padding:0; margin:0; text-align:left;">
        <li>✓ Application</li>
        <li>✓ Documents</li>
        <li>✓ Agent Verification</li>
        <li>✓ Lender Review</li>
        <li>⏳ Final Decision — Processing</li>
    </ul>
    
    <p style="margin-top:20px; font-size:0.9em; color:#666;">Note: Please keep your mobile available for any additional verification or document request.</p>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s21_approval', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Simulate Approval</a>
    <a href="{{ route('finance.cash_loan.s24_additional_docs', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-outline" style="margin-top:10px;">Simulate Additional Docs</a>
</div>
@endsection

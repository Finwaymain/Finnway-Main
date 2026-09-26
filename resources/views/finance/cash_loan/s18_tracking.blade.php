@extends('finance.layouts.base')
@section('title', 'Tracking — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s17_selfie_agent', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Your Loan Applications</h2>
    
    <div style="border:1px solid #ccc; padding:15px; border-radius:8px;">
        <p><strong>Application No.:</strong> FIIN-CL-123456</p>
        <p><strong>Loan Type:</strong> Cash Loan</p>
        <p><strong>Applied Amount:</strong> ₹50,000</p>
        <p><strong>Current Lender:</strong> HDFC Bank</p>
        
        <hr style="margin:10px 0;">
        <ul style="list-style:none; padding:0; margin:0;">
            <li>✓ Application submitted</li>
            <li>✓ Documents submitted</li>
            <li>✓ Lender process completed</li>
            <li>✓ Screenshot submitted</li>
            <li>✓ Agent verification</li>
            <li>⏳ Final review — Pending</li>
        </ul>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s19_lender_review', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

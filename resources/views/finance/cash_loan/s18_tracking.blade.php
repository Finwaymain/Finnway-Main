@extends('finance.layouts.base')
@section('title', 'Tracking — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s17_selfie_agent', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Your Loan Application Status</h2>
    
    <div style="border:1px solid #e2e8f0; padding:16px; border-radius:8px; background:#fff;">
        <p class="mb-2"><strong>Application No.:</strong> <span style="font-family:monospace; font-weight:700;">{{ $appNumber }}</span></p>
        <p class="mb-2"><strong>Loan Type:</strong> {{ $product->title ?? ucwords(str_replace('_', ' ', $loanType ?? 'Cash Loan')) }}</p>
        <p class="mb-2"><strong>Applied Amount:</strong> ₹{{ number_format($amount) }}</p>
        <p class="mb-2"><strong>Current Lender:</strong> {{ $selectedPartner->name ?? ($application->selected_lender_name ?? 'Lending Partner') }}</p>
        
        <hr style="margin:12px 0; border-top:1px solid #e2e8f0;">
        <ul style="list-style:none; padding:0; margin:0; line-height:1.8; font-size:13px;">
            <li>✓ Application submitted</li>
            <li>✓ Documents submitted</li>
            <li>✓ Lender process completed</li>
            <li>✓ Screenshot submitted</li>
            <li>✓ Verification recorded</li>
            <li>⏳ Final underwriting review &mdash; <strong style="color:#d97706;">In Progress</strong></li>
        </ul>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s19_lender_review', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Continue</a>
</div>
@endsection

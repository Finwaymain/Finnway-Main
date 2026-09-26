@extends('finance.layouts.base')
@section('title', 'Processing — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s14_lender_webview', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Lender Processing Status</h2>
    
    <p style="text-align:center; color:#004080; font-weight:bold; margin:20px 0;">Status: {{ $ctx['status'] ?? 'Processing' }}</p>
    
    <div class="fw-status-flow" style="background:#f9f9f9; padding:15px; border-radius:8px;">
        <p>✓ Business KYC</p>
        <p>✓ GST Verification</p>
        <p>🟡 Financial Assessment</p>
        <p>⏳ Credit Assessment</p>
        <p>⏳ Document Verification</p>
        <p>⏳ Lender Decision</p>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s17_approval', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Simulate Approval</a>
    <a href="{{ route('finance.business_loan.s16_additional_docs', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-outline" style="margin-top:10px;">Simulate Additional Docs</a>
</div>
@endsection

@extends('finance.layouts.base')
@section('title', 'Verification — Fiinway')
@section('header-sub', 'Business Loan')
@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 7 of 10</span><span>70%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:70%"></div></div>
</div>
@endsection
@section('back')
<a href="{{ route('finance.business_loan.s07_documents', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading">Business Verification</h2>
    
    <p style="color:orange; font-weight:bold; font-size:18px; margin:20px 0;">🟡 Verification in Progress</p>
    
    <div style="text-align:left; background:#f4f4f4; padding:15px; border-radius:5px;">
        <p><strong>System/Admin is verifying:</strong></p>
        <ul style="margin-top:10px; padding-left:20px;">
            <li>Business details</li>
            <li>GST status</li>
            <li>Annual Turnover</li>
            <li>Existing liabilities</li>
        </ul>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s09_ready', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

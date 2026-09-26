@extends('finance.layouts.base')
@section('title', 'Lender Review — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s18_tracking', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Lender Connection Review</h2>
    
    <p><strong>Current Lender:</strong> HDFC Bank</p>
    <p><strong>Application No.:</strong> FIIN-CL-123456</p>
    <p><strong>Requested Amount:</strong> ₹50,000</p>
    <p><strong>Status:</strong> Under Review</p>
    
    <div style="background:#f4f4f4; padding:10px; margin-top:15px; border-radius:5px;">
        <p>OpenScore Status: Analyzing credit profile...</p>
    </div>
    
    <p style="margin-top:15px;"><a href="#" style="color:#004080; text-decoration:underline;">View Screenshot</a></p>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s20_processing', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

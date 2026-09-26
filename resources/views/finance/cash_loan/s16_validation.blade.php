@extends('finance.layouts.base')
@section('title', 'Validation — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s15_proof_upload', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading" style="color: green;">✓ Application Submitted</h2>
    <p>Application No.: FIIN-CL-123456</p>
    
    <div class="fw-status-flow" style="margin-top:20px; text-align:left;">
        <p>✓ Application Submitted</p>
        <p>🟡 Document/Status Validation</p>
        <p>⏳ Admin Review</p>
        <p>⏳ Next Step</p>
    </div>

    <div class="fw-timer-note" style="margin-top:20px; font-weight:bold;">
        Estimated Validation Window: Up to 3 Minutes
    </div>
    <p style="margin-top:10px;">Your submitted information is being reviewed.</p>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s17_selfie_agent', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

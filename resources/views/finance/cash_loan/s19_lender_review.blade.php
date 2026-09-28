@extends('finance.layouts.base')
@section('title', 'Lender Review — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s18_tracking', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Lender Connection Review</h2>
    
    <p class="mb-2"><strong>Current Lender:</strong> {{ $selectedPartner->name ?? ($application->selected_lender_name ?? 'Lending Partner') }}</p>
    <p class="mb-2"><strong>Application No.:</strong> <span style="font-family:monospace; font-weight:700;">{{ $appNumber }}</span></p>
    <p class="mb-2"><strong>Requested Amount:</strong> ₹{{ number_format($amount) }}</p>
    <p class="mb-3"><strong>Status:</strong> <span class="badge" style="background:#fef3c7; color:#92400e; font-weight:700;">Under Review</span></p>
    
    <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:12px; margin-top:15px; border-radius:6px;">
        <p class="mb-0" style="font-size:12px; color:#475569;">Profile Status: Analyzing credit profile and lender eligibility...</p>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s20_processing', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Continue</a>
</div>
@endsection

@extends('finance.layouts.base')
@section('title', 'Application Generated — Fiinway')
@section('header-sub', 'Cash Loan')

@section('content')
<div class="fw-card mt-5 mb-4 text-center">
    <div style="width:70px; height:70px; background:#e6f4ea; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:20px;">
        <span style="color:#28a745; font-size:35px;">&#10003;</span>
    </div>
    
    <h3 class="fw-h3 mb-2">Payment Successful</h3>
    <p class="text-muted small mb-4">Your application has been successfully generated.</p>

    <div class="p-3 border rounded text-start mb-4" style="background:#f8f9fa;">
        <div class="text-center mb-4 pb-3 border-bottom">
            <span class="text-muted d-block small mb-1">Application Number</span>
            <strong style="font-size:22px; color:#002147; letter-spacing:1px;">{{ $ctx['application']->app_number ?? 'FIIN-LOAN-2026-XXXX' }}</strong>
        </div>

        <ul style="list-style:none; padding:0; margin:0; font-size:14px;">
            <li class="d-flex justify-content-between mb-3">
                <span class="text-muted">Loan Type</span>
                <strong>Cash Loan ({{ request('loan_type') == 'good_cibil' ? 'Good CIBIL' : 'Low CIBIL' }})</strong>
            </li>
            <li class="d-flex justify-content-between mb-3">
                <span class="text-muted">Loan Amount</span>
                <strong>₹ 2,00,000 (Indicative)</strong>
            </li>
            <li class="d-flex justify-content-between mb-3">
                <span class="text-muted">Payment Status</span>
                <strong style="color:#28a745;">Paid</strong>
            </li>
            <li class="d-flex justify-content-between mb-3">
                <span class="text-muted">Application Status</span>
                <strong>Active</strong>
            </li>
            <li class="d-flex justify-content-between">
                <span class="text-muted">Date</span>
                <strong>{{ date('d M Y, h:i A') }}</strong>
            </li>
        </ul>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s11_partner_dashboard', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Continue to Partner Dashboard</a>
</div>
@endsection

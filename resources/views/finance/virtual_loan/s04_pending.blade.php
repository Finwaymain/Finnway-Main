@extends('finance.layouts.base')
@section('title', 'Activation in Progress — Virtual Loan')
@section('header-sub', 'Virtual Loan')
@section('progress-label', 'Step 4 of 5')
@section('progress-pct', '80')
@section('progress') @endsection

@section('content')
<div style="text-align:center;padding:24px 0 16px;">
    <div style="width:64px;height:64px;background:#e8f4fd;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:30px;margin-bottom:12px;">
        ⚙️
    </div>
    <p class="fw-section-title" style="margin-bottom:6px;">Finalizing Wallet Setup</p>
    <p class="fw-section-sub">Payment received. System is configuring your Virtual Loan Ledger and merchant spending rules.</p>
</div>

<div class="fw-card">
    <p class="fw-card-title">Setup Status</p>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>Application &amp; KYC Verified</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Aadhaar &amp; PAN validated</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>Processing Fee Confirmed</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">₹3,000 received successfully</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>Loan Plan Attached</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Daily limit: ₹5,000 · Daily repayment: ₹1,000</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-active" style="background:#eafaf1;border-color:var(--green);color:var(--green);">●</div>
        <div class="fw-check-text">
            <strong>Virtual Ledger Credited</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">₹30,000 ready in Business Credit Wallet</p>
        </div>
    </div>
</div>

<div class="fw-alert fw-alert-info">
    ℹ️ This is a dedicated <strong>Virtual Credit Wallet</strong> usable strictly at eligible Fiinway Business QR merchants. Direct bank withdrawal is restricted.
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.virtual_loan.s05_dashboard', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">Go to Loan Dashboard →</a>
@endsection

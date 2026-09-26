@extends('finance.layouts.base')
@section('title', 'Application Generated — Fiinway')
@section('header-sub', 'Cash Loan')

@section('content')
<div class="fw-card mt-2 mb-4 text-center">
    <div style="width:72px; height:72px; background:#ecfdf5; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:18px; border:2px solid #a7f3d0;">
        <span style="color:#059669; font-size:36px; font-weight:bold;">&#10003;</span>
    </div>
    
    <h3 class="fw-section-title mb-1">Fee Payment Confirmed</h3>
    <p class="fw-section-sub">Your loan application dossier is now verified and active.</p>

    <div class="p-3 border rounded text-start mb-4" style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:12px;">
        <div class="text-center mb-3 pb-3 border-bottom" style="border-bottom:1.5px solid #e2e8f0;">
            <span class="text-muted d-block small mb-1" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Reference Application ID</span>
            <strong style="font-size:21px; color:var(--navy); letter-spacing:1px; font-family:monospace;">
                {{ $application->application_number ?? 'FIIN-' . strtoupper(uniqid()) }}
            </strong>
        </div>

        <div class="fw-info-row">
            <span class="fw-info-label">Applicant Name</span>
            <span class="fw-info-value">{{ $customer->full_name ?? ($application->applicant_name ?? 'Applicant') }}</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Requested Loan Amount</span>
            <span class="fw-info-value" style="color:var(--blue); font-size:16px;">₹ {{ number_format($amount) }}</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Tenure Duration</span>
            <span class="fw-info-value">{{ $tenure }} Months</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Fee Status</span>
            <span class="badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; font-size:11px; font-weight:700;">
                ✓ Verified Paid
            </span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Underwriting Route</span>
            <span class="fw-info-value">
                @if($hasLender)
                    Bank/NBFC Partner Network
                @else
                    Direct Fiinway Credit (No Lender)
                @endif
            </span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Timestamp</span>
            <span class="fw-info-value" style="font-size:12px; font-weight:600; color:#64748b;">{{ date('d M Y, h:i A') }}</span>
        </div>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    @if($hasLender)
        <a href="{{ route('finance.cash_loan.s11_partner_dashboard', ['phone' => $phone, 'amount' => $amount, 'tenure' => $tenure]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">
            Proceed to Lender Partner Selection &rarr;
        </a>
    @else
        <a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => $phone]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">
            Access Credit Line Dashboard &rarr;
        </a>
    @endif
</div>
@endsection

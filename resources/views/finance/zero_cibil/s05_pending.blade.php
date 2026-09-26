@extends('finance.layouts.base')
@section('title', 'Application Pending — Zero-CIBIL Loan')
@section('header-sub', 'Zero-CIBIL Daily Loan')
@section('progress-label', 'Step 5 of 7')
@section('progress-pct', '71')
@section('progress') @endsection

@section('content')
<div style="text-align:center;padding:24px 0 16px;">
    <div style="width:64px;height:64px;background:#e8f4fd;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:30px;margin-bottom:12px;">
        ⏳
    </div>
    <p class="fw-section-title" style="margin-bottom:6px;">Application Under Review</p>
    <p class="fw-section-sub">Your interest-free daily loan application has been received and is being verified.</p>
</div>

<div class="fw-card">
    <p class="fw-card-title">Application Status</p>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>Identity KYC Verified</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Aadhaar and PAN details captured</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>Processing Fee Paid</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">One-time activation fee received</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-active" style="background:#e8f4fd;border-color:var(--blue2);color:var(--blue2);">●</div>
        <div class="fw-check-text">
            <strong>Admin Verification &amp; Limit Sanction</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Reviewing profile for daily limit allocation</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-pending">○</div>
        <div class="fw-check-text">
            <strong>Wallet Activation</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Daily QR payment wallet will be unlocked</p>
        </div>
    </div>
</div>

<div class="fw-alert fw-alert-info">
    ℹ️ Approvals are processed within <strong>1 to 2 hours</strong> during operating hours. Once approved, your daily credit wallet will be instantly activated.
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">Check Wallet Status →</a>
@endsection

@extends('finance.layouts.base')
@section('title', 'Application Status — Zero-CIBIL')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 5 of 6')
@section('progress-pct', '83')
@section('progress', ' ')

@section('content')

@php
    $isApproved = ($application && in_array($application->application_status, ['LOAN_APPROVED', 'DISBURSED'])) || (isset($wallet) && $wallet->status === 'active');
@endphp

@if($isApproved)
    <div style="text-align:center; padding:16px 0 12px;">
        <div style="width:58px; height:58px; background:#ecfdf5; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:26px; margin-bottom:8px; border:2px solid var(--green);">
            ✓
        </div>
        <p class="fw-section-title" style="margin-bottom:4px; color:var(--green);">Credit Line Approved!</p>
        <p class="fw-section-sub">Your zero-interest daily credit wallet is approved and ready for use.</p>
    </div>
@else
    <div style="text-align:center; padding:16px 0 12px;">
        <div style="width:58px; height:58px; background:#e8f4fd; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:26px; margin-bottom:8px; border:2px solid var(--blue2);">
            ⏳
        </div>
        <p class="fw-section-title" style="margin-bottom:4px;">Application Under Underwriting Review</p>
        <p class="fw-section-sub">Your KYC &amp; processing fee are verified. Fiinway Admin is assessing your daily limit.</p>
    </div>
@endif

<div class="fw-card">
    <div class="fw-card-title">Application Summary</div>
    <div class="fw-info-row">
        <span class="fw-info-label">Application Number</span>
        <span class="fw-info-value" style="font-family:monospace;">{{ $application->application_number ?? 'FIIN-ZC-PENDING' }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Requested Limit</span>
        <span class="fw-info-value">₹{{ number_format($amount) }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Fee Status</span>
        <span class="fw-badge fw-badge-green">✓ Paid &amp; Verified</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Underwriting Status</span>
        @if($isApproved)
            <span class="fw-badge fw-badge-green">✓ Approved</span>
        @else
            <span class="fw-badge fw-badge-blue">⏳ Admin Verification In-Progress</span>
        @endif
    </div>
</div>

<div class="fw-card">
    <div class="fw-card-title">Verification Pipeline</div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>1. KYC Documents Uploaded</strong>
            <p style="font-size:12px; color:var(--gray3); margin-top:1px;">Aadhaar &amp; PAN card received</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>2. Processing Fee Received</strong>
            <p style="font-size:12px; color:var(--gray3); margin-top:1px;">Activation charge cleared via Razorpay</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon {{ $isApproved ? 'fw-check-done' : 'fw-check-active' }}"
             style="{{ $isApproved ? '' : 'background:#e8f4fd; border-color:var(--blue2); color:var(--blue2);' }}">
            {{ $isApproved ? '✓' : '●' }}
        </div>
        <div class="fw-check-text">
            <strong>3. Admin Underwriting Approval</strong>
            <p style="font-size:12px; color:var(--gray3); margin-top:1px;">
                {{ $isApproved ? 'Credit limit sanctioned by underwriting desk' : 'Verification team is inspecting your KYC documents' }}
            </p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon {{ $isApproved ? 'fw-check-done' : 'fw-check-pending' }}">
            {{ $isApproved ? '✓' : '○' }}
        </div>
        <div class="fw-check-text">
            <strong>4. Daily Credit Wallet Activation</strong>
            <p style="font-size:12px; color:var(--gray3); margin-top:1px;">
                {{ $isApproved ? 'Wallet active for merchant payments' : 'Unlocks immediately upon admin sanction' }}
            </p>
        </div>
    </div>
</div>

@if(!$isApproved)
    <div class="fw-alert fw-alert-info">
        ℹ️ Verification is typically completed within <strong>1–2 business hours</strong>. You will receive an SMS and push notification once your wallet is unlocked.
    </div>
@endif

@endsection

@section('sticky-bottom')
@if($isApproved)
    <a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}"
       class="fw-btn fw-btn-green">Access Active Credit Wallet →</a>
@else
    <div style="display:flex; flex-direction:column; gap:8px;">
        <a href="{{ route('finance.zero_cibil.s05_pending', ['phone' => request('phone')]) }}"
           class="fw-btn fw-btn-primary">🔄 Check Application Status</a>
        <a href="{{ route('finance.hub', ['phone' => request('phone')]) }}"
           class="fw-btn fw-btn-outline" style="border:none; padding:8px; font-size:13px;">Back to Financial Hub</a>
    </div>
@endif
@endsection

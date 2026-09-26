@extends('finance.layouts.base')
@section('title', 'Zero-CIBIL Daily Credit — Fiinway')
@section('header-sub', 'Zero-CIBIL Credit')

@section('content')

<p class="fw-section-title">Zero-CIBIL Daily Credit</p>
<p class="fw-section-sub">No credit score required. Get a daily-use credit wallet instantly.</p>

{{-- Product Highlights --}}
<div class="fw-card">
    <div class="fw-card-title">Credit Limit</div>
    <div class="fw-amount-big" style="padding:10px 0 14px">
        <div class="fw-amount-big">
            <div class="label">Range</div>
            <div class="amount">₹20,000 – ₹2,00,000</div>
        </div>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Interest Rate</span>
        <span class="fw-info-value" style="color:var(--green)">0% Interest</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Repayment</span>
        <span class="fw-info-value">Daily EMI</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">CIBIL Required</span>
        <span class="fw-info-value" style="color:var(--green)">Not Required</span>
    </div>
</div>

{{-- Key Features --}}
<div class="fw-card">
    <div class="fw-card-title">How It Works</div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div>
            <div class="fw-check-text">No CIBIL Score Required</div>
            <div class="fw-check-sub">Anyone with valid KYC can apply</div>
        </div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div>
            <div class="fw-check-text">Daily EMI Repayment</div>
            <div class="fw-check-sub">Small daily payments, easy on pocket</div>
        </div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div>
            <div class="fw-check-text">Usage Lock on Missed EMI</div>
            <div class="fw-check-sub">Wallet pauses if EMI is missed; resumes on payment</div>
        </div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div>
            <div class="fw-check-text">QR-Based Daily Spending</div>
            <div class="fw-check-sub">Scan & pay at any partner merchant</div>
        </div>
    </div>
</div>

{{-- Eligibility --}}
<div class="fw-alert fw-alert-info">
    <strong>Eligibility:</strong> Indian citizen · Age 21–60 · Valid Aadhaar &amp; PAN
</div>

@endsection

@section('sticky-bottom')
<a href="{{ route('finance.zero_cibil.s02_kyc', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">
    Apply Now →
</a>
@endsection

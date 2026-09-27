@extends('finance.layouts.base')
@section('title', 'Apply for Cash Loan — Fiinway')
@section('header-sub', 'Cash Loan')

@section('back')
<a href="{{ route('finance.hub', ['phone' => $phone]) }}" class="fw-back">← Back to Hub</a>
@endsection

@section('content')
<form id="cashLoanForm" method="POST" action="{{ route('finance.cash_loan.save_step') }}">
    @csrf
    <input type="hidden" name="step" value="s01">
    <input type="hidden" name="phone" value="{{ $phone }}">

@php
    $isRunning = (!empty($application) && !in_array($application->application_status ?? '', ['DISBURSED', 'REJECTED', 'CLOSED']));
    $activeResumeUrl = $isRunning
        ? (new \App\Http\Controllers\Finance\FinanceWebController())->getResumeUrlForApplication($application, $phone)
        : null;
@endphp

@if($isRunning && !empty($activeResumeUrl))
<div class="fw-card mt-2 mb-4 text-center p-4">
    <div style="width:64px; height:64px; border-radius:50%; background:#eff6ff; border:2px solid #bfdbfe; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; font-size:28px;">
        ⏳
    </div>
    <h3 style="color:var(--navy); font-weight:800; font-size:20px; margin-bottom:8px;">Application In Progress</h3>
    <p class="text-muted" style="font-size:13.5px; line-height:1.5; margin-bottom:20px;">
        You already have an active loan application (<strong>#{{ $application->application_number ?? '' }}</strong>) in progress for <strong>₹ {{ number_format($amount) }}</strong>.<br>
        You cannot start a new application while your current process is active.
    </p>

    <div class="p-3 mb-4 text-start" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
        <div class="fw-info-row">
            <span class="fw-info-label">Application Status</span>
            <span class="badge" style="background:#ecfdf5; color:#065f46; font-size:12px; font-weight:700; border-radius:6px; padding:3px 8px;">
                {{ str_replace('_', ' ', $application->application_status) }}
            </span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Applicant</span>
            <span class="fw-info-value">{{ $customer->full_name ?? ($application->applicant_name ?? 'Customer') }}</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Loan Amount</span>
            <span class="fw-info-value" style="color:var(--blue); font-weight:700;">₹ {{ number_format($amount) }}</span>
        </div>
    </div>

    <a href="{{ $activeResumeUrl }}" class="fw-btn fw-btn-primary" style="display:block; text-decoration:none; text-align:center; padding:14px;">
        Resume Active Application &rarr;
    </a>
</div>
@else
<form id="cashLoanForm" method="POST" action="{{ route('finance.cash_loan.save_step') }}">
    @csrf
    <input type="hidden" name="step" value="s01">
    <input type="hidden" name="phone" value="{{ $phone }}">

    <div class="fw-card mb-4">
        <h3 class="fw-section-title mb-2">Select Loan Category</h3>
        <p class="fw-section-sub">Choose the credit line suited to your current CIBIL score.</p>
        
        <label class="fw-radio-card mb-3 d-block border p-3 rounded" style="cursor:pointer; border: 1.5px solid #cbd5e1; border-radius: 12px; background: #ffffff; transition: all 0.2s;">
            <div style="display:flex; align-items:flex-start; gap:12px;">
                <input type="radio" name="loan_type" value="low_cibil" id="low_cibil" {{ ($loanType ?? 'low_cibil') === 'low_cibil' ? 'checked' : '' }} style="margin-top:4px; accent-color: var(--blue);">
                <div class="fw-radio-content">
                    <strong style="color: var(--navy); font-size: 15px; display:block;">Option 1: Low CIBIL Cash Loan</strong>
                    <span class="badge" style="background:#eff6ff; color:#1e40af; font-size:11px; font-weight:700; border-radius:4px; padding:2px 8px; margin:4px 0 6px; display:inline-block;">Up to ₹4,00,000</span>
                    <p class="mb-0 text-muted" style="font-size: 12.5px; line-height:1.4;">Tailored for applicants with score below 700 or fresh to credit. Partner underwriting with fast validation.</p>
                </div>
            </div>
        </label>

        <label class="fw-radio-card mb-3 d-block border p-3 rounded" style="cursor:pointer; border: 1.5px solid #cbd5e1; border-radius: 12px; background: #ffffff; transition: all 0.2s;">
            <div style="display:flex; align-items:flex-start; gap:12px;">
                <input type="radio" name="loan_type" value="good_cibil" id="good_cibil" {{ ($loanType ?? '') === 'good_cibil' ? 'checked' : '' }} style="margin-top:4px; accent-color: var(--blue);">
                <div class="fw-radio-content">
                    <strong style="color: var(--navy); font-size: 15px; display:block;">Option 2: Prime Cash Loan (Good CIBIL)</strong>
                    <span class="badge" style="background:#ecfdf5; color:#065f46; font-size:11px; font-weight:700; border-radius:4px; padding:2px 8px; margin:4px 0 6px; display:inline-block;">Up to ₹20,00,000</span>
                    <p class="mb-0 text-muted" style="font-size: 12.5px; line-height:1.4;">For applicants with 720+ credit score. Lowest interest rate tiers and maximum sanction limits.</p>
                </div>
            </div>
        </label>
    </div>
</form>
@endif
@endsection

@section('sticky-bottom')
@if(!(!empty($application) && !in_array($application->application_status ?? '', ['DISBURSED', 'REJECTED', 'CLOSED'])))
<div class="fw-sticky-bottom">
    <button type="submit" form="cashLoanForm" class="fw-btn fw-btn-primary">Apply Now &rarr;</button>
</div>
@endif
@endsection

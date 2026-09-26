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
                    <span class="badge" style="background:#ecfdf5; color:#065f46; font-size:11px; font-weight:700; border-radius:4px; padding:2px 8px; margin:4px 0 6px; display:inline-block;">Up to ₹50,00,000</span>
                    <p class="mb-0 text-muted" style="font-size: 12.5px; line-height:1.4;">For applicants with 720+ credit score. Lowest interest rate tiers and maximum sanction limits.</p>
                </div>
            </div>
        </label>
    </div>
</form>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="cashLoanForm" class="fw-btn fw-btn-primary">Apply Now &rarr;</button>
</div>
@endsection

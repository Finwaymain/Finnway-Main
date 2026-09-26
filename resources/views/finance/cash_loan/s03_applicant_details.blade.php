@extends('finance.layouts.base')
@section('title', 'Applicant Details — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 2 of 12</span><span>16%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:16%;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s02_type_consent', ['phone' => $phone]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<form id="applicantForm" method="POST" action="{{ route('finance.cash_loan.save_step') }}">
    @csrf
    <input type="hidden" name="step" value="s03">
    <input type="hidden" name="phone" value="{{ $phone }}">
    <input type="hidden" name="loan_type" value="{{ $loanType ?? 'low_cibil' }}">

    <div class="fw-card mt-2 mb-4 pb-3">
        <h3 class="fw-section-title mb-1">Applicant Profile</h3>
        <p class="fw-section-sub">Enter primary borrower identity details as per PAN card.</p>
        
        <div class="mb-4">
            <h5 style="color:var(--navy); font-weight:700; font-size:14px; border-bottom:1.5px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px;">
                1. Personal Details
            </h5>
            <div class="fw-input-group">
                <label class="fw-label">Full Name (As per PAN)</label>
                <input type="text" name="applicant_name" class="fw-input" placeholder="e.g. Rahul Sharma" value="{{ $customer->full_name ?? ($application->applicant_name ?? '') }}" required>
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Date of Birth</label>
                <input type="date" name="dob" class="fw-input" value="{{ $customer->dob ?? '1995-05-15' }}" required>
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Mobile Number</label>
                <input type="tel" name="phone_display" class="fw-input" value="{{ $phone }}" readonly style="background:#f1f5f9; color:#64748b;">
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Email ID</label>
                <input type="email" name="email" class="fw-input" placeholder="rahul@example.com" value="{{ $customer->email ?? '' }}" required>
            </div>
            <div class="fw-input-group">
                <label class="fw-label">PAN Number</label>
                <input type="text" name="pan_number" class="fw-input" maxlength="10" placeholder="ABCDE1234F" style="text-transform:uppercase; font-family:monospace; font-weight:700;" value="{{ $customer->pan_number ?? '' }}" required>
            </div>
        </div>

        <div class="mb-4">
            <h5 style="color:var(--navy); font-weight:700; font-size:14px; border-bottom:1.5px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px;">
                2. Employment & Income
            </h5>
            <div class="fw-input-group">
                <label class="fw-label">Employment Type</label>
                <select name="employment_type" class="fw-input">
                    <option value="Salaried" selected>Salaried Employee</option>
                    <option value="Self-Employed">Self-Employed Professional</option>
                    <option value="Business">Business Owner / Trader</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Company / Employer Name</label>
                <input type="text" name="company_name" class="fw-input" placeholder="e.g. Fiinway Logistics" value="{{ $customer->company_name ?? '' }}">
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Net Monthly Take-Home (₹)</label>
                <input type="number" name="monthly_income" class="fw-input" placeholder="35000" value="{{ $customer->monthly_income ?? '35000' }}" required>
            </div>
        </div>

        <div class="mb-2">
            <h5 style="color:var(--navy); font-weight:700; font-size:14px; border-bottom:1.5px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px;">
                3. Desired Loan Requirement
            </h5>
            <div class="fw-input-group">
                <label class="fw-label">Requested Loan Amount (₹)</label>
                <input type="number" name="requested_amount" class="fw-input" style="font-size:18px; font-weight:800; color:var(--blue);" value="{{ $amount }}" min="5000" max="2000000" required>
            </div>
            <div class="fw-input-group">
                <label class="fw-label">Primary Loan Purpose</label>
                <select name="loan_purpose" class="fw-input">
                    <option value="Personal Emergency">Personal Emergency / Health</option>
                    <option value="Debt Consolidation">Debt Consolidation</option>
                    <option value="Home Improvement">Home Renovation</option>
                    <option value="Education">Education & Courses</option>
                    <option value="Business">Small Business Needs</option>
                </select>
            </div>
        </div>
    </div>
</form>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="applicantForm" class="fw-btn fw-btn-primary">Submit & Continue &rarr;</button>
</div>
@endsection

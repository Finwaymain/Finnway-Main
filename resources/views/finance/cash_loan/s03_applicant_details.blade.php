@extends('finance.layouts.base')
@section('title', 'Applicant Details — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 2 of 12</span><span style="float:right;">16%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:16%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s02_type_consent', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-3 mb-5 pb-4">
    <h3 class="fw-h3 mb-4">Applicant Details</h3>
    
    <div class="mb-4">
        <h5 class="mb-3" style="color:#002147; border-bottom:1px solid #ddd; padding-bottom:5px;">Personal Details</h5>
        <div class="mb-3">
            <label class="form-label small">Full Name</label>
            <input type="text" class="fw-input w-100 p-2 border rounded" placeholder="As per PAN">
        </div>
        <div class="mb-3">
            <label class="form-label small">Date of Birth</label>
            <input type="date" class="fw-input w-100 p-2 border rounded">
        </div>
        <div class="mb-3">
            <label class="form-label small">Mobile Number</label>
            <input type="tel" class="fw-input w-100 p-2 border rounded" value="{{ request('phone') }}" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label small">Email ID</label>
            <input type="email" class="fw-input w-100 p-2 border rounded">
        </div>
        <div class="mb-3">
            <label class="form-label small">PAN Number</label>
            <input type="text" class="fw-input w-100 p-2 border rounded" style="text-transform:uppercase;">
        </div>
    </div>

    <div class="mb-4">
        <h5 class="mb-3" style="color:#002147; border-bottom:1px solid #ddd; padding-bottom:5px;">Address & ID</h5>
        <div class="mb-3">
            <label class="form-label small">Gender</label>
            <select class="fw-input w-100 p-2 border rounded">
                <option>Select</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label small">Full Address</label>
            <textarea class="fw-input w-100 p-2 border rounded" rows="2"></textarea>
        </div>
        <div class="d-flex" style="gap:10px;">
            <div class="mb-3 w-50">
                <label class="form-label small">City</label>
                <input type="text" class="fw-input w-100 p-2 border rounded">
            </div>
            <div class="mb-3 w-50">
                <label class="form-label small">PIN Code</label>
                <input type="text" class="fw-input w-100 p-2 border rounded">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label small">State</label>
            <input type="text" class="fw-input w-100 p-2 border rounded">
        </div>
    </div>

    <div class="mb-4">
        <h5 class="mb-3" style="color:#002147; border-bottom:1px solid #ddd; padding-bottom:5px;">Employment & Income</h5>
        <div class="mb-3">
            <label class="form-label small">Employment Type</label>
            <select class="fw-input w-100 p-2 border rounded">
                <option>Select</option>
                <option>Salaried</option>
                <option>Self-Employed</option>
                <option>Business</option>
                <option>Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label small">Company / Business Name</label>
            <input type="text" class="fw-input w-100 p-2 border rounded">
        </div>
        <div class="mb-3">
            <label class="form-label small">Monthly Income (₹)</label>
            <input type="number" class="fw-input w-100 p-2 border rounded">
        </div>
        <div class="mb-3">
            <label class="form-label small">Existing EMI (₹)</label>
            <input type="number" class="fw-input w-100 p-2 border rounded" value="0">
        </div>
        <div class="mb-3">
            <label class="form-label small">Work Experience (Years)</label>
            <input type="number" class="fw-input w-100 p-2 border rounded">
        </div>
    </div>

    <div class="mb-2">
        <h5 class="mb-3" style="color:#002147; border-bottom:1px solid #ddd; padding-bottom:5px;">Loan Requirement</h5>
        <div class="mb-3">
            <label class="form-label small">Required Loan Amount (₹)</label>
            <input type="number" class="fw-input w-100 p-2 border rounded">
        </div>
        <div class="mb-3">
            <label class="form-label small">Loan Purpose</label>
            <select class="fw-input w-100 p-2 border rounded">
                <option>Select</option>
                <option>Medical Emergency</option>
                <option>Home Renovation</option>
                <option>Education</option>
                <option>Business</option>
                <option>Other</option>
            </select>
        </div>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s04_eligibility', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Submit & Continue</a>
</div>
@endsection

@extends('finance.layouts.base')
@section('title', 'Business Details — Fiinway')
@section('header-sub', 'Business Loan')
@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 1 of 10</span><span>10%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:10%"></div></div>
</div>
@endsection
@section('back')
<a href="{{ route('finance.business_loan.s01_apply', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Business Details Form</h2>
    
    <div class="fw-form-group">
        <label>Business/Company Name</label>
        <input type="text" class="fw-input">
    </div>
    <div class="fw-form-group">
        <label>Business Type</label>
        <select class="fw-input">
            <option>Proprietorship</option>
            <option>Partnership</option>
            <option>LLP</option>
            <option>Pvt.Ltd</option>
            <option>Public Ltd</option>
            <option>Other</option>
        </select>
    </div>
    <div class="fw-form-group"><label>Owner/Director Name</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>Mobile</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>Email</label><input type="email" class="fw-input"></div>
    <div class="fw-form-group"><label>PAN</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>GST Number</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>Business Address</label><textarea class="fw-input"></textarea></div>
    <div class="fw-form-group"><label>City</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>State</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>PIN</label><input type="text" class="fw-input"></div>
    
    <div class="fw-form-group"><label>Business Vintage (Years)</label><input type="number" class="fw-input"></div>
    <div class="fw-form-group"><label>Industry/Category</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>Annual Turnover (₹)</label><input type="number" class="fw-input"></div>
    <div class="fw-form-group"><label>Monthly Avg Turnover (₹)</label><input type="number" class="fw-input"></div>
    <div class="fw-form-group"><label>Existing Business Loan (₹)</label><input type="number" class="fw-input"></div>
    <div class="fw-form-group"><label>Existing EMI (₹)</label><input type="number" class="fw-input"></div>
    <div class="fw-form-group"><label>Number of Employees</label><input type="number" class="fw-input"></div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s03_loan_requirement', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

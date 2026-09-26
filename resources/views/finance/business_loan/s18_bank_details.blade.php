@extends('finance.layouts.base')
@section('title', 'Bank Details — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s17_approval', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Disbursement Bank Details</h2>
    
    <div class="fw-form-group"><label>Account Holder Name</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>Business/Company Name</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>Bank Name</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>Account Number</label><input type="password" class="fw-input"></div>
    <div class="fw-form-group"><label>Confirm Account Number</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group"><label>IFSC Code</label><input type="text" class="fw-input"></div>
    <div class="fw-form-group">
        <label>Account Type</label>
        <select class="fw-input">
            <option>Current</option>
            <option>Savings</option>
        </select>
    </div>
    
    <div class="fw-checkbox-group" style="margin-top:20px;">
        <label><input type="checkbox"> I confirm that the bank account belongs to the applicant/business.</label>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s19_disbursement', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Verify Bank Account</a>
</div>
@endsection

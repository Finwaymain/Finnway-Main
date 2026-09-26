@extends('finance.layouts.base')
@section('title', 'Bank Details — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s21_approval', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Disbursement Bank Details</h2>
    
    <div class="fw-form-group">
        <label>Account Holder Name</label>
        <input type="text" class="fw-input" placeholder="Name as per bank">
    </div>
    <div class="fw-form-group">
        <label>Bank Name</label>
        <input type="text" class="fw-input" placeholder="e.g. HDFC Bank">
    </div>
    <div class="fw-form-group">
        <label>Account Number</label>
        <input type="password" class="fw-input" placeholder="Enter Account Number">
    </div>
    <div class="fw-form-group">
        <label>Confirm Account Number</label>
        <input type="text" class="fw-input" placeholder="Re-enter Account Number">
    </div>
    <div class="fw-form-group">
        <label>IFSC Code</label>
        <input type="text" class="fw-input" placeholder="e.g. HDFC0001234">
    </div>
    <div class="fw-form-group">
        <label>Account Type</label>
        <select class="fw-input">
            <option>Savings</option>
            <option>Current</option>
        </select>
    </div>
    
    <h3 style="font-size:16px; margin-top:20px;">Verify Bank Account</h3>
    <div class="fw-checkbox-group" style="margin-top:10px;">
        <label><input type="checkbox"> I confirm that the bank account belongs to me.</label>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s23_disbursement', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Submit for Disbursement</a>
</div>
@endsection

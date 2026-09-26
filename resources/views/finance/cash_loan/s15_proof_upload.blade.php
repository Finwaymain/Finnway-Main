@extends('finance.layouts.base')
@section('title', 'Upload Proof — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s14_lender_webview', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Complete Your Lender Process</h2>
    <div class="fw-form-group">
        <label>Application Number</label>
        <input type="text" class="fw-input" value="FIIN-CL-123456" readonly>
    </div>
    <div class="fw-form-group">
        <label>Selected Bank/Lender</label>
        <input type="text" class="fw-input" value="HDFC Bank" readonly>
    </div>
    <div class="fw-form-group">
        <label>Loan Application Status</label>
        <select class="fw-input">
            <option>Submitted</option>
            <option>Completed</option>
            <option>Awaiting Docs</option>
            <option>Other</option>
        </select>
    </div>
    <div class="fw-form-group">
        <label>Screenshot Upload</label>
        <input type="file" class="fw-input" accept="image/*">
    </div>
    <div class="fw-form-group">
        <label>Optional Remarks</label>
        <textarea class="fw-input" rows="3"></textarea>
    </div>
    <div class="fw-checkbox-group">
        <label><input type="checkbox"> I have completed the required lender process.</label>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s16_validation', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Submit for Validation</a>
</div>
@endsection

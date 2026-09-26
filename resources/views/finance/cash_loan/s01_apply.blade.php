@extends('finance.layouts.base')
@section('title', 'Apply for Cash Loan — Fiinway')
@section('header-sub', 'Cash Loan')

@section('back')
<a href="{{ url()->previous() }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mb-4">
    <h3 class="fw-h3 mb-3">Select Loan Type</h3>
    
    <label class="fw-radio-card mb-3 d-block border p-3 rounded" style="cursor:pointer; border: 1px solid #ddd;">
        <input type="radio" name="loan_type" value="low_cibil" id="low_cibil" checked>
        <div class="fw-radio-content ms-2 d-inline-block">
            <strong>OPTION 1: LOW CIBIL LOAN</strong>
            <p class="mb-0 small" style="color: #666;">Up to ₹4,00,000 &middot; Processing fee applicable</p>
        </div>
    </label>

    <label class="fw-radio-card mb-3 d-block border p-3 rounded" style="cursor:pointer; border: 1px solid #ddd;">
        <input type="radio" name="loan_type" value="good_cibil" id="good_cibil">
        <div class="fw-radio-content ms-2 d-inline-block">
            <strong>OPTION 2: GOOD CIBIL LOAN</strong>
            <p class="mb-0 small" style="color: #666;">Up to ₹50,00,000 &middot; Processing fee applicable</p>
        </div>
    </label>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s02_type_consent', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Apply Now</a>
</div>
@endsection

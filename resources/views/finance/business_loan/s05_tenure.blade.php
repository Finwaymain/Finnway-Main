@extends('finance.layouts.base')
@section('title', 'Tenure — Fiinway')
@section('header-sub', 'Business Loan')
@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 4 of 10</span><span>40%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:40%"></div></div>
</div>
@endsection
@section('back')
<a href="{{ route('finance.business_loan.s04_eligibility', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Select Tenure</h2>
    
    <p><strong>Indicative Amount:</strong> ₹15,00,000</p>
    
    <div class="fw-form-group" style="margin-top:20px;">
        <label>Tenure (Months)</label>
        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;">
            @foreach([12, 18, 24, 36, 48, 60, 72, 84] as $t)
            <label style="border:1px solid #ccc; padding:10px 15px; border-radius:5px; cursor:pointer;">
                <input type="radio" name="tenure" value="{{ $t }}"> {{ $t }}M
            </label>
            @endforeach
        </div>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s06_emi', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

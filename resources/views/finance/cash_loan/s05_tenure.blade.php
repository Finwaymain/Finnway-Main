@extends('finance.layouts.base')
@section('title', 'Select Tenure — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 4 of 12</span><span style="float:right;">33%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:33%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s04_eligibility', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-3 mb-4">
    <h3 class="fw-h3 mb-3">Loan Amount & Tenure</h3>
    
    <div class="mb-4">
        <label class="form-label text-muted small">Desired Loan Amount (₹)</label>
        <input type="number" class="fw-input w-100 p-2 border rounded fw-bold" style="font-size:20px; color:#002147;" value="200000">
        <div class="d-flex justify-content-between mt-1 small text-muted">
            <span>Min: ₹50,000</span>
            <span>Max: ₹20,00,000</span>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label text-muted small mb-2 d-block">Select Tenure (Months)</label>
        <div style="display:flex; flex-wrap:wrap; gap:10px;">
            <div class="fw-tenure-chip p-2 border rounded text-center" style="flex:1 1 30%; cursor:pointer; background:#f8f9fa;">12</div>
            <div class="fw-tenure-chip p-2 border rounded text-center" style="flex:1 1 30%; cursor:pointer; background:#002147; color:white;">18</div>
            <div class="fw-tenure-chip p-2 border rounded text-center" style="flex:1 1 30%; cursor:pointer; background:#f8f9fa;">24</div>
            <div class="fw-tenure-chip p-2 border rounded text-center" style="flex:1 1 30%; cursor:pointer; background:#f8f9fa;">36</div>
            <div class="fw-tenure-chip p-2 border rounded text-center" style="flex:1 1 30%; cursor:pointer; background:#f8f9fa;">48</div>
            <div class="fw-tenure-chip p-2 border rounded text-center" style="flex:1 1 30%; cursor:pointer; background:#f8f9fa;">60</div>
        </div>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s06_emi', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Continue</a>
</div>
@endsection

@extends('finance.layouts.base')
@section('title', 'Fee Payment — Fiinway')
@section('header-sub', 'Business Loan')
@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 9 of 10</span><span>90%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:90%"></div></div>
</div>
@endsection
@section('back')
<a href="{{ route('finance.business_loan.s09_ready', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Processing Fee</h2>
    
    <div style="background:#f4f4f4; padding:15px; border-radius:8px; margin:20px 0;">
        <p style="display:flex; justify-content:space-between;"><span>Business Loan Processing</span> <span>₹4,237</span></p>
        <p style="display:flex; justify-content:space-between;"><span>GST (18%)</span> <span>₹763</span></p>
        <hr style="margin:10px 0;">
        <p style="display:flex; justify-content:space-between; font-weight:bold; font-size:18px;"><span>Total</span> <span>₹5,000</span></p>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s11_app_generated', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Pay Now</a>
</div>
@endsection

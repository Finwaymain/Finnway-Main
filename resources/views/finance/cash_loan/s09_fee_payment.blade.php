@extends('finance.layouts.base')
@section('title', 'Fee Payment — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 8 of 12</span><span style="float:right;">66%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:66%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s08_ready', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-3 mb-4">
    <h3 class="fw-h3 mb-3">Processing Fee Payment</h3>
    <p class="text-muted small mb-4">Complete your payment to activate the loan processing dashboard.</p>
    
    <div class="p-3 border rounded mb-4" style="background:#fff;">
        <h5 style="color:#002147; font-size:16px; margin-bottom:15px; font-weight:600;">Loan Processing Service</h5>
        
        <div class="d-flex justify-content-between mb-3 text-muted" style="font-size:14px;">
            <span>Processing Fee</span>
            <span>₹ {{ $ctx['fee'] ?? '1,499.00' }}</span>
        </div>
        <div class="d-flex justify-content-between mb-3 text-muted" style="font-size:14px;">
            <span>Applicable Tax (GST @ 18%)</span>
            <span>₹ {{ $ctx['tax'] ?? '269.82' }}</span>
        </div>
        <div class="d-flex justify-content-between pt-3 mt-2" style="border-top:1px dashed #ccc; font-weight:bold; font-size:18px; color:#002147;">
            <span>Total Payable</span>
            <span>₹ {{ $ctx['total'] ?? '1,768.82' }}</span>
        </div>
    </div>

    <div class="p-3 rounded" style="background:#f8f9fa; border:1px solid #eee; text-align:center;">
        <span class="small text-muted d-block mb-2">Secure payment powered by Razorpay</span>
        <div style="color:#ccc; font-size:24px;">&#128274;</div>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s10_app_generated', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Pay Now</a>
</div>
@endsection

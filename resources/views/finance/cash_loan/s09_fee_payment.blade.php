@extends('finance.layouts.base')
@section('title', 'Fee Payment — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 8 of 12</span><span>66%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:66%;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s08_ready', ['phone' => $phone, 'amount' => $amount, 'tenure' => $tenure]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-2 mb-4">
    <h3 class="fw-section-title mb-1">Underwriting &amp; Processing Fee</h3>
    <p class="fw-section-sub">Payment activates your partner loan underwriting and verification dossier.</p>
    
    <div class="p-3 border rounded mb-4" style="background:#ffffff; border:1.5px solid #e2e8f0; border-radius:12px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1.5px solid #f1f5f9; padding-bottom:10px;">
            <span style="color:var(--navy); font-size:15px; font-weight:700;">Loan Amount</span>
            <span style="color:var(--blue); font-size:16px; font-weight:800;">₹ {{ number_format($amount) }}</span>
        </div>
        
        <div class="d-flex justify-content-between mb-3 text-muted" style="font-size:14px;">
            <span>Processing / Service Fee</span>
            <span style="font-weight:600; color:var(--navy);">₹ {{ number_format($baseFee, 2) }}</span>
        </div>
        <div class="d-flex justify-content-between mb-3 text-muted" style="font-size:14px;">
            <span>Mandatory Tax (GST @ 18%)</span>
            <span style="font-weight:600; color:var(--navy);">₹ {{ number_format($feeTax, 2) }}</span>
        </div>
        <div class="d-flex justify-content-between pt-3 mt-2" style="border-top:1.5px dashed #cbd5e1; font-weight:800; font-size:18px; color:var(--navy);">
            <span>Total Payable Amount</span>
            <span style="color:var(--blue);">₹ {{ number_format($totalFee, 2) }}</span>
        </div>
    </div>

    <div class="p-3 rounded" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; text-align:center;">
        <div style="font-size:18px; margin-bottom:4px;">🔒</div>
        <span class="small text-muted d-block" style="font-size:12px; font-weight:500;">
            256-bit encrypted checkout via Razorpay Gateway (UPI, Cards, NetBanking)
        </span>
    </div>
</div>

@include('finance.partials.razorpay')

@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="button"
            onclick="triggerRazorpayCheckout({ amount: {{ $totalFee }}, phone: '{{ $phone }}', application_id: '{{ $application->id ?? '' }}', next_url: '{{ route('finance.cash_loan.s10_app_generated', ['phone' => $phone, 'amount' => $amount]) }}' })"
            class="fw-btn fw-btn-primary">
        Pay ₹ {{ number_format($totalFee, 2) }} via Razorpay →
    </button>
</div>
@endsection

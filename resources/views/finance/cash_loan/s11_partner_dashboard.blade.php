@extends('finance.layouts.base')
@section('title', 'Partner Dashboard — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 9 of 12</span><span style="float:right;">75%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:75%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s10_app_generated', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="mt-3 mb-5 pb-4">
    <h3 class="fw-h3 mb-1" style="color:#002147;">Loan Processing Dashboard</h3>
    <p class="text-muted small mb-4">Select a lending partner to proceed with final verification.</p>

    <!-- Partner Card 1 -->
    <div class="fw-card mb-3 border rounded p-3" style="background:#fff;">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center">
                <div style="width:40px; height:40px; background:#eee; border-radius:5px; margin-right:15px; display:flex; align-items:center; justify-content:center; font-size:10px; color:#999;">LOGO</div>
                <strong style="font-size:16px;">HDFC Bank</strong>
            </div>
            <span class="badge" style="background:#28a745; color:white; font-size:11px;">Available</span>
        </div>
        
        <div class="row mb-3" style="font-size:12px;">
            <div class="col-6 mb-2">
                <span class="text-muted d-block">Loan Range</span>
                <strong>₹50K - ₹25L</strong>
            </div>
            <div class="col-6 mb-2">
                <span class="text-muted d-block">Interest Rate</span>
                <strong>10.5% p.a.</strong>
            </div>
            <div class="col-6">
                <span class="text-muted d-block">Tenure</span>
                <strong>Up to 60 Months</strong>
            </div>
            <div class="col-6">
                <span class="text-muted d-block">Processing Fee</span>
                <strong>Up to 2%</strong>
            </div>
        </div>
        <a href="{{ route('finance.cash_loan.s12_partner_verify', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary w-100 d-block text-center p-2" style="font-size:14px;">View Details &rarr; Proceed</a>
    </div>

    <!-- Partner Card 2 -->
    <div class="fw-card mb-3 border rounded p-3" style="background:#fff; opacity:0.7;">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center">
                <div style="width:40px; height:40px; background:#eee; border-radius:5px; margin-right:15px; display:flex; align-items:center; justify-content:center; font-size:10px; color:#999;">LOGO</div>
                <strong style="font-size:16px;">Bajaj Finserv</strong>
            </div>
            <span class="badge" style="background:#dc3545; color:white; font-size:11px;">&#128274; Locked</span>
        </div>
        
        <div class="row mb-3" style="font-size:12px;">
            <div class="col-6 mb-2">
                <span class="text-muted d-block">Loan Range</span>
                <strong>₹1L - ₹30L</strong>
            </div>
            <div class="col-6 mb-2">
                <span class="text-muted d-block">Interest Rate</span>
                <strong>11% p.a.</strong>
            </div>
            <div class="col-6">
                <span class="text-muted d-block">Tenure</span>
                <strong>Up to 72 Months</strong>
            </div>
            <div class="col-6">
                <span class="text-muted d-block">Processing Fee</span>
                <strong>Up to 1.5%</strong>
            </div>
        </div>
        <button class="fw-btn w-100 d-block text-center p-2" style="font-size:14px; background:#eee; color:#666; cursor:not-allowed;" disabled>Locked</button>
    </div>
</div>
@endsection

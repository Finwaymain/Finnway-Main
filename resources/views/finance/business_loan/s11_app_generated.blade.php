@extends('finance.layouts.base')
@section('title', 'Dashboard — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s10_fee_payment', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading" style="color:green;">Payment Successful ✓</h2>
    <p style="font-size:18px; font-weight:bold; text-align:center; margin:10px 0;">Application No.: FIIN-BL-2026-{{ rand(100000, 999999) }}</p>
    
    <div style="border:1px solid #ddd; padding:15px; border-radius:8px; margin-top:20px;">
        <p><strong>Business Name:</strong> XYZ Enterprises</p>
        <p><strong>Loan Type:</strong> Business Loan</p>
        <p><strong>Requested Amount:</strong> ₹20,00,000</p>
        <p><strong>Indicative Amount:</strong> ₹15,00,000</p>
        <p><strong>Payment Status:</strong> Paid ✓</p>
        <p><strong>Application Status:</strong> Active</p>
        <p><strong>Date:</strong> {{ date('d M Y') }}</p>
        <hr style="margin:10px 0;">
        <p style="color:#004080; font-weight:bold;">Next Step: Select Partner</p>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s12_partner_dashboard', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

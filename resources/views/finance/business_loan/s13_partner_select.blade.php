@extends('finance.layouts.base')
@section('title', 'Select Partner — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s12_partner_dashboard', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Confirm Partner Selection</h2>
    
    <div style="border:2px solid #004080; padding:15px; border-radius:8px; position:relative;">
        <div style="position:absolute; top:-10px; right:10px; background:#004080; color:white; padding:2px 10px; border-radius:10px; font-size:12px;">Selected</div>
        <h3 style="margin:0 0 10px 0;">Bajaj Finserv</h3>
        <p>By confirming, other partner options will be locked for this application.</p>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s14_lender_webview', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Confirm Selection</a>
</div>
@endsection

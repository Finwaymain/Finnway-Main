@extends('finance.layouts.base')
@section('title', 'Business Documents — Fiinway')
@section('header-sub', 'Business Loan')
@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 6 of 10</span><span>60%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:60%"></div></div>
</div>
@endsection
@section('back')
<a href="{{ route('finance.business_loan.s06_emi', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Upload Documents</h2>
    <p style="margin-bottom:15px; font-size:0.9em; color:#666;">Please provide the following documents.</p>
    
    @php
        $docs = [
            'PAN Card',
            'Aadhaar / ID Proof',
            'Address Proof',
            'GST Certificate',
            'Business Registration Certificate',
            'ITR',
            'Balance Sheet',
            'Profit & Loss Statement',
            'GST Returns'
        ];
    @endphp
    
    @foreach($docs as $doc)
    <div class="fw-form-group">
        <label>{{ $doc }}</label>
        <input type="file" class="fw-input">
    </div>
    @endforeach
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s08_verification', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Submit</a>
</div>
@endsection

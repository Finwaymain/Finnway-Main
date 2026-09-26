@extends('finance.layouts.base')
@section('title', 'Apply — Fiinway')
@section('header-sub', 'Business Loan')
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Business Loan — ₹5 Lakh – ₹2 Crore</h2>
    
    <div class="fw-form-group">
        <label>Purpose</label>
        <select class="fw-input" multiple style="height:120px;">
            <option>Business Expansion</option>
            <option>Working Capital</option>
            <option>Machinery/Equipment</option>
            <option>Stock Purchase</option>
            <option>Office/Shop Expansion</option>
            <option>Business Debt Consolidation</option>
            <option>Other</option>
        </select>
        <small style="color:#666;">Hold Ctrl/Cmd to select multiple</small>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.business_loan.s02_business_details', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Apply Now</a>
</div>
@endsection

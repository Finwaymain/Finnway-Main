@extends('finance.layouts.base')
@section('title', 'Additional Docs — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s20_processing', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading" style="color:#d9534f;">⚠️ Additional Documents Required</h2>
    <p><strong>Application No.:</strong> FIIN-CL-123456</p>
    <p style="font-size:0.9em; color:#666;">Requested By Admin</p>
    
    @if(isset($ctx['admin_remark']))
    <div style="background:#fff3cd; padding:10px; margin:10px 0; border-radius:5px; border-left:4px solid #ffeeba;">
        <strong>Admin Remark:</strong> {{ $ctx['admin_remark'] }}
    </div>
    @endif
    
    @php
        $docs = $ctx['doc_requests'] ?? ['6 Months Bank Statement', 'Full Aadhaar Card', 'Fresh Selfie', 'Home Photo', 'House GPS Location', 'Additional Income Proof'];
    @endphp
    
    @foreach($docs as $doc)
    <div class="fw-form-group" style="margin-top:15px;">
        <label>{{ $doc }}</label>
        <input type="file" class="fw-input">
    </div>
    @endforeach
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s25_docs_submitted', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Submit Documents</a>
</div>
@endsection

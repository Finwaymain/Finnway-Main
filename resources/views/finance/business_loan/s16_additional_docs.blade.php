@extends('finance.layouts.base')
@section('title', 'Additional Docs — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s15_lender_processing', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading" style="color:#d9534f;">⚠️ Additional Documents Required</h2>
    <p><strong>Application No.:</strong> FIIN-BL-2026-123456</p>
    
    @if(isset($ctx['admin_remark']))
    <div style="background:#fff3cd; padding:10px; margin:10px 0; border-radius:5px; border-left:4px solid #ffeeba;">
        <strong>Admin Remark:</strong> {{ $ctx['admin_remark'] }}
    </div>
    @endif
    
    @php
        $docs = [
            '12 Month Bank Statement',
            'Latest ITR',
            'GST Return',
            'Balance Sheet',
            'P&L',
            'Business Address Proof',
            'Ownership Proof',
            'Existing Loan Statement',
            'Property/Collateral Document'
        ];
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
    <a href="{{ route('finance.business_loan.s17_approval', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Submit Documents</a>
</div>
@endsection

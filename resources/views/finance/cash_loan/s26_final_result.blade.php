@extends('finance.layouts.base')
@section('title', 'Final Result — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s25_docs_submitted', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    @php
        $status = $ctx['status'] ?? 'APPROVED';
    @endphp
    
    @if($status === 'APPROVED')
        <h2 class="fw-heading" style="color:green;">✓ Final Verification Completed</h2>
        <p style="font-size:18px; font-weight:bold;">Loan Application Approved</p>
        <p style="color:orange; margin-top:10px;">Disbursement Processing</p>
        <p style="margin-top:20px;">Your application has been successfully verified and approved.</p>
    @else
        <h2 class="fw-heading" style="color:#d9534f;">Application Not Approved</h2>
        <p><strong>Reason:</strong> {{ $ctx['reject_reason'] ?? 'Did not meet criteria' }}</p>
        <p style="margin-top:20px;">You can reapply after 3 days.</p>
        <div style="margin-top:20px;">
            <button class="fw-btn fw-btn-outline">View Details</button>
            <button class="fw-btn fw-btn-primary" style="margin-top:10px;">Reapply</button>
        </div>
    @endif
</div>
@endsection

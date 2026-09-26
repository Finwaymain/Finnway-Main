@extends('finance.layouts.base')
@section('title', 'Docs Submitted — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s24_additional_docs', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading" style="color:green;">✓ Documents Submitted</h2>
    <p><strong>Application Number:</strong> FIIN-CL-123456</p>
    
    <table style="width:100%; margin-top:20px; border-collapse:collapse;">
        <tr style="background:#f4f4f4; text-align:left;">
            <th style="padding:10px; border-bottom:1px solid #ccc;">Document</th>
            <th style="padding:10px; border-bottom:1px solid #ccc;">Status</th>
        </tr>
        <tr>
            <td style="padding:10px; border-bottom:1px solid #eee;">6 Months Bank Statement</td>
            <td style="padding:10px; border-bottom:1px solid #eee; color:green;">Submitted</td>
        </tr>
        <tr>
            <td style="padding:10px; border-bottom:1px solid #eee;">Full Aadhaar Card</td>
            <td style="padding:10px; border-bottom:1px solid #eee; color:green;">Submitted</td>
        </tr>
    </table>
    
    <p style="margin-top:20px; color:orange; font-weight:bold;">Status: Admin Review Pending</p>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s26_final_result', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Continue</a>
</div>
@endsection

@extends('finance.layouts.base')
@section('title', 'Application Ready — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 6 of 11</span><span>55%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:55%;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s07_documents', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-4 mb-4 text-center">
    <div style="width:60px; height:60px; background:#e6f4ea; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:15px;">
        <span style="color:#28a745; font-size:30px;">&#10003;</span>
    </div>
    
    <h3 class="fw-h3 mb-2">Application Review Completed</h3>
    <span class="badge" style="background:#17a2b8; color:white; padding:5px 10px; font-size:12px; border-radius:12px; display:inline-block; margin-bottom:20px;">Ready for Final Lender Processing</span>

    <div class="text-start p-3 border rounded mb-4" style="background:#f8f9fa;">
        <h5 class="mb-3" style="font-size:14px; color:#666; text-transform:uppercase; border-bottom:1px solid #ddd; padding-bottom:5px;">Application Summary</h5>
        <ul style="list-style:none; padding:0; margin:0; font-size:14px;">
            <li class="d-flex justify-content-between mb-2 pb-1">
                <span class="text-muted">Indicative Loan Amount</span>
                <strong>₹ {{ number_format($amount) }}</strong>
            </li>
            <li class="d-flex justify-content-between mb-2 pb-1">
                <span class="text-muted">Selected Tenure</span>
                <strong>{{ $tenure }} Months</strong>
            </li>
            <li class="d-flex justify-content-between mb-2 pb-1">
                <span class="text-muted">Estimated EMI</span>
                <strong>₹ {{ number_format($emi) }} / mo</strong>
            </li>
            <li class="d-flex justify-content-between mb-1 pb-1" style="border-top:1px dashed #ccc; padding-top:10px; margin-top:5px;">
                <span class="text-muted">Processing Fee</span>
                <strong>₹ {{ number_format($totalFee, 2) }}</strong>
            </li>
        </ul>
    </div>

    <p class="small text-muted" style="line-height:1.5;">
        Your application is ready to proceed to the lender's final processing stage. Applicable service/processing fees must be paid before accessing the relevant lender platform.
    </p>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s08b_sanction_summary', ['phone' => request('phone'), 'amount' => $amount, 'tenure' => $tenure]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">View Sanction Summary →</a>
    @if(empty($application->fee_payment_status) || $application->fee_payment_status !== 'paid')
    <div style="text-align:center; margin-top:8px;">
        <a href="javascript:void(0)" onclick="if(confirm('Are you sure you want to cancel and withdraw this application ({{ $application->application_number ?? '' }})?\n\nThis will cancel your existing application so you can choose another loan.')) { window.location.href='{{ route('finance.withdraw_application', ['phone' => request('phone') ?? ($phone ?? '')]) }}'; }" style="color:#ef4444; font-size:12.5px; font-weight:600; text-decoration:none; display:inline-block; padding:4px 8px;">
            ✕ Cancel &amp; Withdraw Application
        </a>
    </div>
    @endif
</div>
@endsection

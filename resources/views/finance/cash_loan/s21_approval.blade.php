@extends('finance.layouts.base')
@section('title', 'Approved — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s20_processing', ['phone' => $phone]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card fw-text-center">
    <h2 class="fw-heading" style="color:green;">🎉 Congratulations! Loan Approved</h2>
    
    @php
        $displayAmount = ($application && floatval($application->approved_amount) > 0) ? floatval($application->approved_amount) : $amount;
    @endphp
    <div class="fw-amount-big" style="font-size:32px; font-weight:bold; margin:20px 0; color:#059669;">₹{{ number_format($displayAmount) }}</div>
    
    <div style="text-align:left; background:#f9f9f9; padding:15px; border-radius:8px;">
        <p><strong>Lender:</strong> {{ $selectedPartner->name ?? ($application->selected_lender_name ?? 'Lending Partner') }}</p>
        <p><strong>Application No.:</strong> {{ $appNumber }}</p>
        <p><strong>Approved Loan Amount:</strong> <span style="font-weight: 700; color: #059669;">₹{{ number_format($displayAmount) }}</span></p>
        @if($application && $application->requested_amount && $application->requested_amount != $displayAmount)
        <p style="font-size: 12px; color: #64748b; margin-top: -6px; margin-bottom: 10px;">(Original Demand: ₹{{ number_format($application->requested_amount) }})</p>
        @endif
        <p><strong>Tenure:</strong> {{ $tenure ?? 12 }} Months</p>
        <p><strong>EMI:</strong> ₹{{ number_format($emi ?? 0) }}/month</p>
        <p><strong>Interest Rate:</strong> {{ $selectedPartner->interest_rate_display ?? '12% - 16% p.a.' }}</p>
        <p><strong>Processing Charges:</strong> ₹{{ number_format($totalFee ?? 999) }}</p>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s22_bank_details', ['phone' => $phone, 'amount' => $displayAmount]) }}" class="fw-btn fw-btn-primary">Proceed to Disbursement</a>
</div>
@endsection

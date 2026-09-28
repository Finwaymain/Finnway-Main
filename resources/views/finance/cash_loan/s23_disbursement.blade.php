@extends('finance.layouts.base')
@section('title', 'Disbursement — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s21_approval', ['phone' => $phone]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card fw-text-center" style="padding: 24px 16px;">
    @if($application && $application->application_status === 'DISBURSED')
    <div style="width: 64px; height: 64px; border-radius: 50%; background: #ecfdf5; color: #059669; font-size: 32px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
        ✓
    </div>
    <h2 class="fw-heading" style="color: #059669; font-size: 22px; font-weight: 800; margin-bottom: 6px;">
        Loan Disbursed Successfully!
    </h2>
    <p style="color: #64748b; font-size: 13px; margin-bottom: 20px;">
        Funds have been transferred to your registered bank account.
    </p>
    @else
    <div style="width: 64px; height: 64px; border-radius: 50%; background: #fefce8; color: #d97706; font-size: 32px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
        ⏳
    </div>
    <h2 class="fw-heading" style="color: #d97706; font-size: 22px; font-weight: 800; margin-bottom: 6px;">
        Disbursement Processing
    </h2>
    <p style="color: #64748b; font-size: 13px; margin-bottom: 20px;">
        Bank details received. Funds transfer is currently in queue.
    </p>
    @endif
    
    <div style="text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 10px; margin: 16px 0; font-size: 13px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span style="color: #64748b;">Application No.:</span>
            <span style="font-weight: 700; color: #0f172a; font-family: monospace;">{{ $appNumber }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span style="color: #64748b;">Disbursement Amount:</span>
            <span style="font-weight: 700; color: #059669; font-size: 15px;">₹{{ number_format(($application && floatval($application->approved_amount) > 0) ? floatval($application->approved_amount) : $amount) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span style="color: #64748b;">Destination Bank:</span>
            <span style="font-weight: 600; color: #0f172a;">{{ ($application && $application->disbursement_bank_name) ? $application->disbursement_bank_name : 'HDFC Bank' }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span style="color: #64748b;">Account Holder:</span>
            <span style="font-weight: 600; color: #0f172a;">{{ ($application && $application->disbursement_account_name) ? $application->disbursement_account_name : ($customer->name ?? '—') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span style="color: #64748b;">Account Number:</span>
            <span style="font-weight: 600; color: #0f172a; font-family: monospace;">
                @if($application && $application->disbursement_account_number)
                    •••• •••• {{ substr($application->disbursement_account_number, -4) }}
                @else
                    •••• •••• 1234
                @endif
            </span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span style="color: #64748b;">IFSC Code:</span>
            <span style="font-weight: 600; color: #0f172a; font-family: monospace;">{{ ($application && $application->disbursement_ifsc) ? $application->disbursement_ifsc : '—' }}</span>
        </div>
        @if($application && $application->disbursement_txn_ref)
        <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px dashed #cbd5e1; margin-top: 8px;">
            <span style="color: #64748b;">Bank UTR / Txn Ref:</span>
            <span style="font-weight: 700; color: #2563eb; font-family: monospace;">{{ $application->disbursement_txn_ref }}</span>
        </div>
        @endif
    </div>
    
    <div style="background: {{ ($application && $application->application_status === 'DISBURSED') ? '#ecfdf5' : '#fffbeb' }}; border: 1px solid {{ ($application && $application->application_status === 'DISBURSED') ? '#a7f3d0' : '#fde68a' }}; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
        <div style="font-weight: 700; color: {{ ($application && $application->application_status === 'DISBURSED') ? '#065f46' : '#92400e' }}; font-size: 13px;">
            Status: {{ ($application && $application->application_status === 'DISBURSED') ? '✓ Amount Disbursed' : '⏳ Waiting for Bank Clearance' }}
        </div>
        <div style="font-size: 12px; color: {{ ($application && $application->application_status === 'DISBURSED') ? '#047857' : '#b45309' }}; margin-top: 4px;">
            {{ ($application && $application->application_status === 'DISBURSED') ? 'The amount has been credited to your bank account.' : 'Expected timeline: Within 1-4 business hours.' }}
        </div>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    @if($application && $application->application_status === 'DISBURSED')
    <a href="{{ route('finance.hub', ['phone' => $phone]) }}" class="fw-btn fw-btn-primary w-100" style="padding: 13px; font-size: 15px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 12px rgba(37,99,235,0.25); text-decoration: none; display: block; text-align: center; margin-bottom: 8px;">
        Go to Active Loan Dashboard →
    </a>
    <a href="{{ route('finance.repayments', ['phone' => $phone]) }}" class="fw-btn fw-btn-outline w-100" style="padding: 10px; font-size: 13px; font-weight: 700; border-radius: 8px; text-decoration: none; display: block; text-align: center;">
        💳 View EMI &amp; Repayments
    </a>
    @else
    <a href="{{ route('finance.cash_loan.s23_disbursement', ['phone' => $phone]) }}" class="fw-btn fw-btn-primary w-100" style="padding: 14px; font-size: 15px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 12px rgba(37,99,235,0.25); text-decoration: none; display: block; text-align: center;">
        🔄 Refresh Status
    </a>
    @endif
</div>
@endsection

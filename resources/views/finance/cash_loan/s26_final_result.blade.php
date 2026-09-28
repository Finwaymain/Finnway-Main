@extends('finance.layouts.base')
@section('title', 'Application Status — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.hub', ['phone' => request('phone')]) }}" class="fw-back">← Finance Hub</a>
@endsection

@section('content')
<div class="fw-card fw-text-center" style="padding: 24px 16px;">
    @php
        $status = $application->application_status ?? ($status ?? ($ctx['status'] ?? 'APPROVED'));
        $rejectReason = $application->rejection_reason ?? ($reject_reason ?? ($ctx['reject_reason'] ?? 'Underwriting criteria not met.'));
    @endphp
    
    @if($status === 'APPROVED' || $status === 'DISBURSED')
        <div style="width: 56px; height: 56px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 28px; color: #059669;">
            ✓
        </div>
        <h2 class="fw-heading" style="color: #059669; font-size: 20px; font-weight: 700; margin-bottom: 4px;">
            Final Verification Completed
        </h2>
        <p style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
            Loan Application Approved
        </p>
        <div style="display: inline-block; background: #fffbeb; border: 1px solid #fef3c7; color: #b45309; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 12px; margin-bottom: 16px;">
            Disbursement Processing
        </div>
        <p style="font-size: 13px; color: #475569; line-height: 1.5;">
            Your loan application has been successfully verified and approved by the lending institution.
        </p>
        <div style="margin-top: 24px;">
            <a href="{{ route('finance.cash_loan.s23_disbursement', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">
                Track Disbursement
            </a>
        </div>
    @else
        <div style="width: 56px; height: 56px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 28px; color: #dc2626;">
            ✕
        </div>
        <h2 class="fw-heading" style="color: #dc2626; font-size: 20px; font-weight: 700; margin-bottom: 4px;">
            Application Not Approved
        </h2>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
            Application No.: <strong style="color: #0f172a; font-family: monospace;">{{ $appNumber ?? 'N/A' }}</strong>
        </p>

        <div style="text-align: left; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 10px; padding: 14px 16px; margin: 0 auto 20px; max-width: 340px;">
            <div style="font-size: 12px; font-weight: 700; color: #9f1239; text-transform: uppercase; margin-bottom: 4px;">
                Decision Reason:
            </div>
            <div style="font-size: 13px; color: #881337; font-weight: 600; line-height: 1.5;">
                {{ $rejectReason }}
            </div>
        </div>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin: 0 auto 20px; max-width: 340px; font-size: 12px; color: #475569; line-height: 1.6; text-align: left;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">🔒 3-Day Reapplication Lock:</div>
            Underwriting rules apply a 3-day waiting window before submitting a fresh application for this category.
            @if(!empty($application->reapply_locked_until))
            <div style="margin-top: 6px; font-weight: 600; color: #0284c7;">
                Reapply available on: {{ date('d M Y, H:i', strtotime($application->reapply_locked_until)) }}
            </div>
            @endif
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; max-width: 340px; margin: 0 auto;">
            <a href="{{ route('finance.hub', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">
                Return to Loans Home
            </a>
            <a href="tel:1800123456" class="fw-btn fw-btn-outline" style="border: 1px solid #cbd5e1; color: #0f172a;">
                Contact Support / Helpdesk
            </a>
        </div>
    @endif
</div>
@endsection

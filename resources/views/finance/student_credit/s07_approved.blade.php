@extends('finance.layouts.base')
@section('title', 'Student Credit Activated — Fiinway')
@section('header-sub', 'Student Credit')
@section('progress-label', 'Step 7 of 8')
@section('progress-pct', '90')
@section('progress') @endsection

@section('content')
<div style="text-align:center;padding:20px 0 16px;">
    <div style="width:68px;height:68px;background:#eafaf1;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:32px;margin-bottom:12px;">
        🎉
    </div>
    <p class="fw-section-title" style="margin-bottom:6px;">Credit Limit Activated!</p>
    <p class="fw-section-sub">Your student virtual credit line is now ready for App-to-App merchant payments.</p>
</div>

{{-- Virtual Card Display --}}
<div style="background:var(--navy);border-radius:12px;padding:20px;color:#fff;margin-bottom:16px;box-shadow:0 4px 12px rgba(15,27,45,0.15);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <span style="font-size:14px;font-weight:700;letter-spacing:1px;color:#a8c7fa;">FIINWAY STUDENT</span>
        <span style="font-size:11px;background:rgba(255,255,255,0.15);padding:3px 8px;border-radius:4px;">VIRTUAL CREDIT</span>
    </div>

    <div style="font-size:18px;letter-spacing:3px;font-family:monospace;margin-bottom:20px;">
        •••• •••• •••• {{ substr($phone ?? '9876', -4) }}
    </div>

    <div style="display:flex;justify-content:space-between;align-items:flex-end;">
        <div>
            <p style="font-size:10px;color:#8ab4f8;text-transform:uppercase;margin:0;">Card Holder</p>
            <p style="font-size:13px;font-weight:600;margin:2px 0 0;">{{ $customer->name ?? 'Student Applicant' }}</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:10px;color:#8ab4f8;text-transform:uppercase;margin:0;">Limit</p>
            <p style="font-size:15px;font-weight:700;margin:2px 0 0;color:#81c995;">₹{{ isset($ctx['approved_amount']) ? number_format($ctx['approved_amount']) : '25,000' }}</p>
        </div>
    </div>
</div>

<div class="fw-card">
    <p class="fw-card-title">How To Use Your Credit</p>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">1</div>
        <div class="fw-check-text">
            <strong>Scan Merchant QR</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Scan any eligible campus, mess, canteen, or book merchant QR code</p>
        </div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">2</div>
        <div class="fw-check-text">
            <strong>Pay Instantly App-to-App</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Amount is deducted directly from your virtual credit line</p>
        </div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">3</div>
        <div class="fw-check-text">
            <strong>Repay on Time</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Build your CIBIL score early while studying</p>
        </div>
    </div>
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.student_credit.s08_dashboard', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">Open Student Dashboard →</a>
@endsection

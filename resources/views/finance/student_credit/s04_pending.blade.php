@extends('finance.layouts.base')
@section('title', 'Application Pending — Fiinway Student Credit')
@section('header-sub', 'Student Credit')
@section('progress-label', 'Step 4 of 8')
@section('progress-pct', '50')
@section('progress') @endsection

@section('content')
<div style="text-align:center;padding:24px 0 16px;">
    <div style="width:64px;height:64px;background:#e8f4fd;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:30px;margin-bottom:12px;">
        ⏳
    </div>
    <p class="fw-section-title" style="margin-bottom:6px;">Application Under Review</p>
    <p class="fw-section-sub">Your student credit application is being verified by our management team.</p>
</div>

<div class="fw-card">
    <p class="fw-card-title">Verification Checklist</p>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>Aadhaar KYC Verification</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Identity and age criteria verified (16–26 yrs)</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">
            <strong>Credit Processing Fee Paid</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">One-time fee confirmed</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-active" style="background:#e8f4fd;border-color:var(--blue2);color:var(--blue2);">●</div>
        <div class="fw-check-text">
            <strong>Student Enrollment Verification</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">College ID &amp; 6-month validity check in progress</p>
        </div>
    </div>

    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-pending">○</div>
        <div class="fw-check-text">
            <strong>Management Credit Approval</strong>
            <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Final limit and tenure allocation</p>
        </div>
    </div>
</div>

<div class="fw-alert fw-alert-info">
    ℹ️ Review typically takes <strong>2 to 4 business hours</strong>. You will receive an SMS notification once approved.
</div>

<div class="fw-card" style="margin-top:14px;">
    <p class="fw-card-title">Need to Upload More Proofs?</p>
    <p style="font-size:13px;color:var(--gray3);margin-bottom:12px;">If requested by our team, you can upload bonafide certificate, admission receipt, or visa documents here.</p>
    <a href="{{ route('finance.student_credit.s05_additional_docs', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-secondary" style="font-size:13px;padding:10px;">
        Upload Additional Documents →
    </a>
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.student_credit.s06_mgmt_approval', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">View Approval Status →</a>
@endsection

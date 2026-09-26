@extends('finance.layouts.base')
@section('title', 'Management Review — Fiinway Student Credit')
@section('header-sub', 'Student Credit')
@section('progress-label', 'Step 6 of 8')
@section('progress-pct', '75')
@section('progress') @endsection

@section('back')
<a href="{{ route('finance.student_credit.s04_pending', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<p class="fw-section-title">Management Decision</p>
<p class="fw-section-sub">Credit evaluation completed by Fiinway risk &amp; credit committee.</p>

<div class="fw-card" style="border:1.5px solid var(--green);background:#f6fbf7;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
        <div style="width:40px;height:40px;border-radius:50%;background:#eafaf1;color:var(--green);font-size:22px;display:flex;align-items:center;justify-content:center;font-weight:700;">✓</div>
        <div>
            <p style="font-size:16px;font-weight:700;color:var(--navy);">Credit Approved</p>
            <p style="font-size:12px;color:var(--gray3);">Sanctioned by Management Review</p>
        </div>
    </div>

    <div class="fw-amount-big" style="padding:10px 0;background:transparent;">
        <p class="label">Approved Credit Limit</p>
        <p class="amount" style="color:var(--green);">₹{{ isset($ctx['approved_amount']) ? number_format($ctx['approved_amount']) : '25,000' }}</p>
    </div>

    <div class="fw-info-row">
        <span class="fw-info-label">Student Type</span>
        <span class="fw-info-value">{{ ucfirst($ctx['student_type'] ?? 'Domestic') }}</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Credit Validity</span>
        <span class="fw-info-value">Tied to Student ID Expiry</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Interest Rate</span>
        <span class="fw-info-value">0% during valid study period</span>
    </div>
    <div class="fw-info-row">
        <span class="fw-info-label">Allowed Usage</span>
        <span class="fw-info-value">App-to-App Partner Payments</span>
    </div>
</div>

<div class="fw-card">
    <p class="fw-card-title">Terms &amp; Restrictions</p>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">Usable across all eligible college cafeteria, bookstore, and tuition merchants.</div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">No direct ATM or cash withdrawal permitted (App-to-App payment only).</div>
    </div>
    <div class="fw-check-item">
        <div class="fw-check-icon fw-check-done">✓</div>
        <div class="fw-check-text">Flexible monthly repayments aligned with academic calendar.</div>
    </div>
</div>

<div class="fw-consent">
    <input type="checkbox" id="accept_terms" checked>
    <label for="accept_terms">I accept the approved student credit limit and terms of use.</label>
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.student_credit.s07_approved', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">Activate Student Credit →</a>
@endsection

@extends('finance.layouts.base')
@section('title', 'Partner Verification — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 10 of 12</span><span style="float:right;">83%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:83%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s11_partner_dashboard', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-3 mb-4">
    <h3 class="fw-h3 mb-3">Enter Verification Details</h3>
    <p class="text-muted small mb-4">Please verify your details before proceeding to the selected lender's portal.</p>

    <div class="mb-4 p-3 border rounded" style="background:#f8f9fa;">
        <div class="mb-3">
            <label class="form-label small text-muted">Full Name (Auto-filled)</label>
            <input type="text" class="fw-input w-100 p-2 border rounded bg-white" value="John Doe" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label small text-muted">Application Number (Auto-filled)</label>
            <input type="text" class="fw-input w-100 p-2 border rounded bg-white" value="{{ $ctx['application']->app_number ?? 'FIIN-LOAN-2026-XXXX' }}" readonly>
        </div>
        <div class="mb-2">
            <label class="form-label small text-muted">Mobile Number</label>
            <input type="tel" class="fw-input w-100 p-2 border rounded bg-white" value="{{ request('phone') }}" readonly>
        </div>
    </div>

    <div class="p-3 rounded" style="background:#fff3cd; border:1px solid #ffeeba;">
        <p class="mb-0 small" style="color:#856404; font-size:12px;">
            <strong>Important:</strong> Once you submit and proceed, the selected partner will become ACTIVE for this application, and all other lender options will be permanently LOCKED.
        </p>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s13_partner_redirect', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Submit & Proceed</a>
</div>
@endsection

@extends('finance.layouts.base')
@section('title', 'Upload Documents — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 6 of 12</span><span style="float:right;">50%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:50%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s06_emi', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card mt-3 mb-5 pb-4">
    <h3 class="fw-h3 mb-2">Upload Documents</h3>
    <p class="text-muted small mb-4">Please upload clear, legible copies of the following documents. Max size: 5MB per file.</p>

    <!-- Document Items -->
    <div class="fw-doc-item mb-3 p-3 border rounded d-flex justify-content-between align-items-center" style="background:#fff;">
        <div>
            <strong>1. PAN Card</strong>
            <div class="small text-muted">JPEG, PNG, PDF</div>
        </div>
        <button class="fw-btn p-2 rounded" style="background:#eee; border:1px solid #ccc; font-size:12px;">Upload</button>
    </div>

    <div class="fw-doc-item mb-3 p-3 border rounded d-flex justify-content-between align-items-center" style="background:#fff;">
        <div>
            <strong>2. Aadhaar Front</strong>
            <div class="small text-muted">JPEG, PNG, PDF</div>
        </div>
        <button class="fw-btn p-2 rounded" style="background:#eee; border:1px solid #ccc; font-size:12px;">Upload</button>
    </div>

    <div class="fw-doc-item mb-3 p-3 border rounded d-flex justify-content-between align-items-center" style="background:#fff;">
        <div>
            <strong>3. Aadhaar Back</strong>
            <div class="small text-muted">JPEG, PNG, PDF</div>
        </div>
        <button class="fw-btn p-2 rounded" style="background:#eee; border:1px solid #ccc; font-size:12px;">Upload</button>
    </div>

    <div class="fw-doc-item mb-3 p-3 border rounded d-flex justify-content-between align-items-center" style="background:#fff;">
        <div>
            <strong>4. Address Proof</strong>
            <div class="small text-muted">Utility Bill / Passport / Voter ID</div>
        </div>
        <button class="fw-btn p-2 rounded" style="background:#eee; border:1px solid #ccc; font-size:12px;">Upload</button>
    </div>

    <div class="fw-doc-item mb-3 p-3 border rounded d-flex justify-content-between align-items-center" style="background:#fff;">
        <div>
            <strong>5. Income Proof / Salary Slip</strong>
            <div class="small text-muted">Latest 3 Months</div>
        </div>
        <button class="fw-btn p-2 rounded" style="background:#eee; border:1px solid #ccc; font-size:12px;">Upload</button>
    </div>
</div>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s08_ready', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">Submit Documents</a>
</div>
@endsection

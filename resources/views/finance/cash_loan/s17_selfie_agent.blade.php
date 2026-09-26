@extends('finance.layouts.base')
@section('title', 'Selfie Verification — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s16_validation', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Agent Verification Required</h2>
    
    <ul style="margin-bottom:20px; padding-left:20px;">
        <li>User face visible</li>
        <li>Agent face visible</li>
        <li>Clear image, good lighting</li>
        <li>No face covering</li>
    </ul>

    <div class="fw-form-group fw-text-center">
        <label class="fw-btn fw-btn-outline" style="display:inline-block; cursor:pointer;">
            Open Camera
            <input type="file" accept="image/*" capture="user" style="display:none;">
        </label>
        <p style="margin-top:10px; color:green; display:none;">Selfie Uploaded ✓</p>
    </div>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s18_tracking', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Submit Verification</a>
</div>
@endsection

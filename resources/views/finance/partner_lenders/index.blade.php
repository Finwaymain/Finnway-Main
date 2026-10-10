@extends('finance.layouts.base')
@section('title', 'Lender Loans — Fiinway')
@section('header-sub', 'Lender Loans')

@section('content')
<div style="padding-bottom: 16px;">

    {{-- Breadcrumb back to hub --}}
    <div style="margin-bottom: 12px; display:flex; justify-content:space-between; align-items:center;">
        <a href="{{ route('finance.hub', ['phone' => request('phone') ?? ($phone ?? '')]) }}" style="display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:700; color:#1e293b; text-decoration:none; background:#ffffff; border:1px solid #e2e8f0; padding:5px 10px; border-radius:6px;">
            ← Back
        </a>
        <span style="font-size: 11px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">
            Step 1 of 2
        </span>
    </div>

    {{-- Alert Messages --}}
    @if(session('error'))
    <div style="background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; color: #991b1b; font-size: 12.5px; font-weight: 600; display:flex; align-items:center; gap:6px;">
        <span>⚠</span>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    {{-- Compact Header --}}
    <div style="margin-bottom: 14px;">
        <h2 style="font-size: 19px; font-weight: 800; color: var(--navy); margin-bottom: 2px; letter-spacing: -0.3px;">
            Partner Lender Loans
        </h2>
        <p style="font-size: 12px; color: #64748b; margin-bottom: 0;">
            Verify your details to view available partner lenders.
        </p>
    </div>

    {{-- Profile & Referral Form Card --}}
    <div class="fw-card" style="padding: 16px 14px; margin-bottom: 0;">
        <form method="POST" action="{{ route('finance.lender_loans.step2') }}">
            @csrf
            <input type="hidden" name="phone_fallback" value="{{ $applicantPhone }}">

            <div class="fw-input-group" style="margin-bottom: 12px;">
                <label class="fw-label" style="font-size: 11px; margin-bottom: 4px;">Full Name <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" value="{{ old('name', $applicantName) }}" required class="fw-input" placeholder="Your full name" style="padding: 10px 12px; font-size: 14px;">
            </div>

            <div class="fw-input-group" style="margin-bottom: 12px;">
                <label class="fw-label" style="font-size: 11px; margin-bottom: 4px;">Mobile Number <span style="color:#ef4444;">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone', $applicantPhone) }}" required class="fw-input" placeholder="10-digit mobile number" style="padding: 10px 12px; font-size: 14px;">
            </div>

            <div class="fw-input-group" style="margin-bottom: 12px;">
                <label class="fw-label" style="font-size: 11px; margin-bottom: 4px;">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $applicantEmail) }}" class="fw-input" placeholder="Email address" style="padding: 10px 12px; font-size: 14px;">
            </div>

            <div class="fw-input-group" style="margin-bottom: 18px;">
                <label class="fw-label" style="font-size: 11px; margin-bottom: 4px;">
                    Referral Code <span style="font-weight:400; color:#64748b; text-transform:none;">(Optional)</span>
                </label>
                <input type="text" name="referral_code" value="{{ old('referral_code', $referralCode) }}" class="fw-input" placeholder="Enter referral code (if any)" style="padding: 10px 12px; font-size: 14px; text-transform:uppercase;">
            </div>

            <button type="submit" class="fw-btn-primary" style="width: 100%; border: none; padding: 12px; font-size: 14px; font-weight: 700; border-radius: 8px; cursor: pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                <span>Continue to Lenders</span>
                <span style="font-size: 15px;">→</span>
            </button>
        </form>
    </div>

</div>
@endsection

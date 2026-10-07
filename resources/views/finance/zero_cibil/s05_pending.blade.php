@extends('finance.layouts.base')
@section('title', 'Application Status — Zero-CIBIL')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 6 of 6 · Approval & Review')
@section('progress-pct', '100')
@section('progress', ' ')

@section('content')
<div class="fw-viewport-container">
    <div>
        @php
            $isApproved = ($application && in_array($application->application_status, ['LOAN_APPROVED', 'DISBURSED', 'ACTIVE']))
                || (isset($wallet) && $wallet->status === 'active');
        @endphp

        {{-- Status Hero Card --}}
        @if($isApproved)
            <div class="fw-bank-hero" style="text-align:center; padding:16px 14px; margin-bottom:8px; background:linear-gradient(135deg, #064e3b 0%, #065f46 100%);">
                <div style="font-size:28px; margin-bottom:4px;">🎉</div>
                <div style="font-size:18px; font-weight:800; color:#ffffff; margin-bottom:2px;">Credit Line Approved!</div>
                <div style="font-size:11px; color:#a7f3d0;">Your zero-interest daily credit wallet is approved and ready.</div>
            </div>
        @else
            <div class="fw-bank-hero" style="text-align:center; padding:14px; margin-bottom:8px;">
                <div style="font-size:24px; margin-bottom:4px;">⏳</div>
                <div style="font-size:16px; font-weight:800; color:#ffffff; margin-bottom:2px;">Underwriting Review In-Progress</div>
                <div style="font-size:11px; color:#94a3b8;">KYC &amp; activation fee verified. Admin is sanctioning your daily limit.</div>
            </div>
        @endif

        {{-- Application Summary (Compact Card) --}}
        <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span style="font-size:11px; font-weight:700; color:var(--navy);">Application Summary</span>
                <span style="font-size:10px; font-family:monospace; color:var(--gray3);">{{ $application->application_number ?? 'FIIN-ZC' }}</span>
            </div>

            <div class="fw-info-row" style="padding:4px 0; font-size:12px;">
                <span class="fw-info-label">Sanctioned Limit</span>
                <span class="fw-info-value" style="font-size:13px; color:var(--navy);">₹{{ number_format($amount) }}</span>
            </div>
            <div class="fw-info-row" style="padding:4px 0; font-size:12px;">
                <span class="fw-info-label">Activation Fee</span>
                <span class="fw-badge fw-badge-green" style="font-size:10px;">✓ Paid &amp; Verified</span>
            </div>
            <div class="fw-info-row" style="padding:4px 0 0; font-size:12px;">
                <span class="fw-info-label">Underwriting Desk</span>
                @if($isApproved)
                    <span class="fw-badge fw-badge-green" style="font-size:10px;">✓ Approved</span>
                @else
                    <span class="fw-badge fw-badge-blue" style="font-size:10px;">⏳ In Queue</span>
                @endif
            </div>
        </div>

        {{-- Verification Pipeline (Compact 4 Steps) --}}
        <div class="fw-bank-card" style="padding:10px 12px; margin-bottom:8px;">
            <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:6px;">
                Pipeline Progression
            </div>
            <div style="display:flex; flex-direction:column; gap:6px; font-size:11px;">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <span>✓ 1. KYC Documents Uploaded</span>
                    <span style="color:#00a875; font-weight:700;">Verified</span>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <span>✓ 2. Activation Fee Received</span>
                    <span style="color:#00a875; font-weight:700;">Verified</span>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <span>{{ $isApproved ? '✓' : '●' }} 3. Admin Underwriting Approval</span>
                    <span style="{{ $isApproved ? 'color:#00a875; font-weight:700;' : 'color:var(--blue); font-weight:600;' }}">
                        {{ $isApproved ? 'Sanctioned' : 'Processing' }}
                    </span>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; color:{{ $isApproved ? '#0f1b2d' : 'var(--gray3)' }};">
                    <span>{{ $isApproved ? '✓' : '○' }} 4. Daily Wallet Activated</span>
                    <span style="{{ $isApproved ? 'color:#00a875; font-weight:700;' : '' }}">
                        {{ $isApproved ? 'Ready' : 'Pending' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Action (Single Viewport) --}}
    <div style="padding-top:4px;">
        @if($isApproved)
            <a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone')]) }}"
               class="fw-btn fw-btn-green" style="padding:11px 14px; font-size:14px; font-weight:700;">
                Access Active Credit Wallet →
            </a>
        @else
            <div style="display:flex; gap:8px;">
                <a href="{{ route('finance.zero_cibil.s05_pending', ['phone' => request('phone')]) }}"
                   class="fw-btn fw-btn-primary" style="padding:10px; font-size:13px; font-weight:700; flex:1;">
                    🔄 Refresh Status
                </a>
                <a href="{{ route('finance.hub', ['phone' => request('phone')]) }}"
                   class="fw-btn fw-btn-outline" style="padding:10px; font-size:13px; font-weight:700; flex:1;">
                    Financial Hub
                </a>
            </div>
        @endif
    </div>
</div>

@if(!$isApproved)
<script>
// Live polling every 10 seconds to detect admin sanction
setInterval(function() {
    fetch("{{ route('finance.zero_cibil.s05_pending', ['phone' => request('phone')]) }}", {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function(res) {
        if (res.ok) {
            window.location.reload();
        }
    }).catch(function() {});
}, 10000);
</script>
@endif
@endsection

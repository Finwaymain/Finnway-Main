@extends('finance.layouts.base')
@section('title', 'Partner Dashboard — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 9 of 12</span><span style="float:right;">75%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:75%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s10_app_generated', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="mt-3 mb-5 pb-4">
    <h3 class="fw-h3 mb-1" style="color:#002147;">Loan Processing Dashboard</h3>
    <p class="text-muted small mb-4">Select a lending partner to proceed with final verification.</p>

    @php
        $lockedPartnerId = $application->selected_lender_id ?? null;
    @endphp

    @forelse($partners as $index => $partner)
        @php
            $isCurrentActive = $lockedPartnerId && $lockedPartnerId == $partner->id;
            $isOtherLocked = $lockedPartnerId && $lockedPartnerId != $partner->id;
            
            // Format loan range display
            $minL = $partner->min_loan_amount >= 100000 ? '₹' . round($partner->min_loan_amount / 100000, 1) . 'L' : '₹' . round($partner->min_loan_amount / 1000) . 'K';
            $maxL = $partner->max_loan_amount >= 100000 ? '₹' . round($partner->max_loan_amount / 100000, 1) . 'L' : '₹' . round($partner->max_loan_amount / 1000) . 'K';
            $rangeDisplay = $minL . ' - ' . $maxL;
        @endphp

        <div class="fw-card mb-3 border rounded p-3" style="background:#fff; {{ $isOtherLocked ? 'opacity:0.65;' : '' }}">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center">
                    @if(!empty($partner->logo))
                        <img src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->name }}" style="width:40px; height:40px; object-fit:contain; border-radius:6px; margin-right:12px; border:1px solid #e2e8f0; padding:2px; background:#fff;">
                    @else
                        <div style="width:40px; height:40px; background:#002147; color:#fff; border-radius:6px; margin-right:12px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700;">
                            {{ strtoupper(substr($partner->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <strong style="font-size:16px; color:#0f172a;">{{ $partner->name }}</strong>
                        <div style="font-size:11px; color:#64748b;">Direct Lending Partner</div>
                    </div>
                </div>
                <div>
                    @if($isCurrentActive)
                        <span class="badge" style="background:#059669; color:white; font-size:11px; font-weight:700; padding:4px 8px; border-radius:4px;">✓ Selected</span>
                    @elseif($isOtherLocked)
                        <span class="badge" style="background:#dc2626; color:white; font-size:11px; font-weight:700; padding:4px 8px; border-radius:4px;">🔒 Locked</span>
                    @else
                        <span class="badge" style="background:#28a745; color:white; font-size:11px; font-weight:700; padding:4px 8px; border-radius:4px;">Available</span>
                    @endif
                </div>
            </div>
            
            <div class="row mb-3" style="font-size:12px;">
                <div class="col-6 mb-2">
                    <span class="text-muted d-block" style="font-size:11px;">Loan Range</span>
                    <strong style="color:#0f172a;">{{ $rangeDisplay }}</strong>
                </div>
                <div class="col-6 mb-2">
                    <span class="text-muted d-block" style="font-size:11px;">Interest Rate</span>
                    <strong style="color:#0f172a;">{{ $partner->interest_rate_display ?: '10.5% p.a.' }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block" style="font-size:11px;">Tenure</span>
                    <strong style="color:#0f172a;">{{ $partner->tenure_display ?: 'Up to 60 Months' }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block" style="font-size:11px;">Processing Fee</span>
                    <strong style="color:#0f172a;">{{ $partner->processing_fee_display ?: 'Up to 2%' }}</strong>
                </div>
            </div>

            @if($isOtherLocked)
                <button class="fw-btn w-100 d-block text-center p-2" style="font-size:14px; background:#f1f5f9; color:#94a3b8; border:1px solid #e2e8f0; cursor:not-allowed;" disabled>
                    Locked (Application Bound to {{ $application->selected_lender_name ?? 'Selected Partner' }})
                </button>
            @else
                <a href="{{ route('finance.cash_loan.s12_partner_verify', ['phone' => request('phone'), 'partner_id' => $partner->id]) }}" class="fw-btn fw-btn-primary w-100 d-block text-center p-2" style="font-size:14px;">
                    {{ $isCurrentActive ? 'Continue with ' . $partner->name . ' →' : 'View Details → Proceed' }}
                </a>
            @endif
        </div>
    @empty
        <div class="p-4 text-center border rounded bg-white text-muted">
            No lending partners configured at this time.
        </div>
    @endforelse
</div>
@endsection

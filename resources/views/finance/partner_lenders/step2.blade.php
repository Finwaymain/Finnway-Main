@extends('finance.layouts.base')
@section('title', 'Select Lender — Fiinway')
@section('header-sub', 'Select Lender')

@section('content')
<div style="padding-bottom: 16px;">

    {{-- Breadcrumb back to step 1 --}}
    <div style="margin-bottom: 12px; display:flex; justify-content:space-between; align-items:center;">
        <a href="{{ route('finance.lender_loans.index', ['phone' => $applicantPhone, 'name' => $applicantName, 'email' => $applicantEmail, 'referral_code' => $referralCode]) }}" style="display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:700; color:#1e293b; text-decoration:none; background:#ffffff; border:1px solid #e2e8f0; padding:5px 10px; border-radius:6px;">
            ← Edit Details
        </a>
        <span style="font-size: 11px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 4px 8px; border-radius: 4px;">
            Step 2 of 2
        </span>
    </div>

    {{-- Compact Header --}}
    <div style="margin-bottom: 12px;">
        <h2 style="font-size: 19px; font-weight: 800; color: var(--navy); margin-bottom: 2px; letter-spacing: -0.3px;">
            Choose a Lender
        </h2>
        <div style="font-size: 11.5px; color: #64748b; display:flex; align-items:center; gap:4px;">
            Applying as <strong style="color: #0f172a;">{{ $applicantName }}</strong> ({{ $applicantPhone }})
            @if(!empty($referralCode))
            <span class="badge" style="background:#f1f5f9; color:#0f172a; font-family:monospace; font-size:10px; padding:2px 5px; border-radius:3px;">{{ $referralCode }}</span>
            @endif
        </div>
    </div>

    {{-- Lenders List --}}
    <div style="display: flex; flex-direction: column; gap: 10px;">
        @forelse($lenders as $lender)
        <div class="fw-card" style="padding: 14px 12px; margin-bottom: 0; border-left: 4px solid #0284c7;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                <div>
                    <div style="font-size: 15px; font-weight: 800; color: #0f172a;">
                        {{ $lender->name }}
                    </div>
                </div>
                <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 10.5px; font-weight: 700; padding: 3px 6px; border-radius: 4px; border: 1px solid #a7f3d0;">
                    Verified
                </span>
            </div>

            {{-- 4 Stat Grid --}}
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 6px; background: #f8fafc; padding: 8px 10px; border-radius: 6px; margin-bottom: 10px; border: 1px solid #e2e8f0;">
                <div>
                    <div style="font-size: 9.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Loan Amount</div>
                    <div style="font-size: 12.5px; font-weight: 700; color: #0f172a;">
                        ₹{{ number_format($lender->min_loan_amount) }} – ₹{{ number_format($lender->max_loan_amount) }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 9.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Interest Rate</div>
                    <div style="font-size: 12.5px; font-weight: 700; color: #059669;">
                        {{ $lender->interest_rate_display }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 9.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Tenure</div>
                    <div style="font-size: 11.5px; font-weight: 600; color: #334155;">
                        {{ $lender->tenure_display }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 9.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Processing Fee</div>
                    <div style="font-size: 11.5px; font-weight: 600; color: #334155;">
                        {{ $lender->processing_fee_display ?: 'Standard' }}
                    </div>
                </div>
            </div>

            {{-- Apply Action Button --}}
            <button type="button"
                    onclick="selectLender({{ $lender->id }})"
                    class="fw-btn-primary"
                    style="width: 100%; border: none; padding: 10px; font-size: 13px; font-weight: 700; border-radius: 6px; cursor: pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                <span>Apply on {{ $lender->name }}</span>
                <span style="font-size: 14px;">→</span>
            </button>
        </div>
        @empty
        <div class="fw-card" style="text-align:center; padding:24px 16px; color:#64748b;">
            <div style="font-size:24px; margin-bottom:6px;">🏦</div>
            <div style="font-size:13px; font-weight:700; color:#0f172a;">No Active Lenders Found</div>
            <div style="font-size:11.5px; margin-top:2px;">Please check back shortly.</div>
        </div>
        @endforelse
    </div>

</div>

{{-- Hidden Form for Redirection & Lead Capture --}}
<form id="applyLenderForm" method="POST" action="" style="display:none;">
    @csrf
    <input type="hidden" name="name" value="{{ $applicantName }}">
    <input type="hidden" name="phone" value="{{ $applicantPhone }}">
    <input type="hidden" name="email" value="{{ $applicantEmail }}">
    <input type="hidden" name="referral_code" value="{{ $referralCode }}">
</form>
@endsection

@push('scripts')
<script>
function selectLender(lenderId) {
    const form = document.getElementById('applyLenderForm');
    form.action = "{{ url('/finance/lender-loans/apply') }}/" + lenderId;
    form.submit();
}
</script>
@endpush

@extends('finance.layouts.base')
@section('title', 'Direct Partner Lender Loans — Fiinway')
@section('header-sub', 'Partner Lenders')

@section('content')
<div style="padding-bottom: 24px;">

    {{-- Breadcrumb back to hub --}}
    <div style="margin-bottom: 14px;">
        <a href="{{ route('finance.hub', ['phone' => request('phone') ?? ($phone ?? '')]) }}" style="display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:700; color:#1e293b; text-decoration:none; background:#ffffff; border:1px solid #e2e8f0; padding:6px 12px; border-radius:8px;">
            ← Back to Finance Hub
        </a>
    </div>

    {{-- Alert Messages --}}
    @if(session('error'))
    <div style="background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px; color: #991b1b; font-size: 13px; font-weight: 600; display:flex; align-items:center; gap:8px;">
        <span>⚠</span>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    {{-- Top Hero Banner --}}
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 14px; padding: 18px 16px; margin-bottom: 18px; color: white; border: 1px solid #334155; box-shadow: 0 4px 16px rgba(15,23,42,0.12);">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
            <span style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.8px; background: rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 3px 8px; border-radius: 4px; font-weight: 800;">NBFC &amp; Banking Network</span>
            <span style="font-size: 10.5px; color: #94a3b8;">Instant Portal Access</span>
        </div>
        <h2 style="font-size: 18px; font-weight: 800; color: #ffffff; margin-bottom: 4px; letter-spacing: -0.3px;">
            Partner Lender Loans
        </h2>
        <p style="font-size: 12px; color: #cbd5e1; margin-bottom: 0; line-height: 1.45;">
            Confirm your profile and select from our curated portfolio of verified institutional lenders to begin your loan application directly.
        </p>
    </div>

    {{-- Profile & Referral Form Card --}}
    <div class="fw-card" style="margin-bottom: 20px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 12px;">
            <div style="font-size: 14px; font-weight: 700; color: var(--navy);">
                1. Your Contact &amp; Referral Details
            </div>
            <span style="font-size: 11px; color: #10b981; font-weight: 700; background: #ecfdf5; padding: 2px 8px; border-radius: 4px;">
                Autofilled
            </span>
        </div>

        <div style="font-size: 12px; color: #64748b; margin-bottom: 16px;">
            These details will be linked to your chosen lender application. You may edit them below if needed.
        </div>

        <form id="lenderProfileForm">
            <div class="fw-input-group">
                <label class="fw-label">Full Name <span style="color:#ef4444;">*</span></label>
                <input type="text" id="applicant_name" name="name" value="{{ old('name', $applicantName) }}" required class="fw-input" placeholder="e.g. Rahul Sharma">
            </div>

            <div class="fw-input-group">
                <label class="fw-label">Mobile Number <span style="color:#ef4444;">*</span></label>
                <input type="tel" id="applicant_phone" name="phone" value="{{ old('phone', $applicantPhone) }}" required class="fw-input" placeholder="10-digit mobile number">
            </div>

            <div class="fw-input-group">
                <label class="fw-label">Email Address</label>
                <input type="email" id="applicant_email" name="email" value="{{ old('email', $applicantEmail) }}" class="fw-input" placeholder="e.g. rahul@example.com">
            </div>

            <div class="fw-input-group" style="margin-bottom: 6px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label class="fw-label" style="margin-bottom:0;">Referral Code <span style="font-weight:400; color:#64748b; text-transform:none;">(Optional)</span></label>
                    <span style="font-size: 10.5px; color: #64748b;">Vendor / Driver / Freelancer / User</span>
                </div>
                <input type="text" id="referral_code" name="referral_code" value="{{ old('referral_code', $referralCode) }}" class="fw-input" placeholder="e.g. FIINC123, FIINB456, TM101, FR10001" style="text-transform:uppercase;">
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                Have a referral from an agent, driver, vendor or friend? Enter their code above to tag their benefits.
            </div>
        </form>
    </div>

    {{-- Lenders Marketplace List --}}
    <div style="margin-bottom: 12px; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size: 13px; font-weight: 800; color: var(--navy); text-transform: uppercase; letter-spacing: 0.5px;">
            2. Select Verified Lender Partner ({{ $lenders->count() }})
        </div>
        <span style="font-size: 11px; color: #64748b;">Click to Apply</span>
    </div>

    @forelse($lenders as $lender)
    <div class="fw-card" style="padding: 16px; margin-bottom: 12px; border-left: 4px solid var(--blue); transition: transform 0.15s, box-shadow 0.15s;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
            <div>
                <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 2px;">
                    {{ $lender->name }}
                </div>
                <div style="font-size: 11.5px; color: #64748b;">
                    Official Banking / NBFC Institution
                </div>
            </div>
            <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 4px; border: 1px solid #a7f3d0;">
                Verified
            </span>
        </div>

        {{-- Rate & Limit Grid --}}
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 8px; background: #f8fafc; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; border: 1px solid #e2e8f0;">
            <div>
                <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700;">Loan Amount</div>
                <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 1px;">
                    ₹{{ number_format($lender->min_loan_amount) }} – ₹{{ number_format($lender->max_loan_amount) }}
                </div>
            </div>
            <div>
                <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700;">Interest Rate</div>
                <div style="font-size: 13px; font-weight: 700; color: #059669; margin-top: 1px;">
                    {{ $lender->interest_rate_display }}
                </div>
            </div>
            <div>
                <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700;">Tenure</div>
                <div style="font-size: 12.5px; font-weight: 600; color: #334155; margin-top: 1px;">
                    {{ $lender->tenure_display }}
                </div>
            </div>
            <div>
                <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700;">Processing Fee</div>
                <div style="font-size: 12.5px; font-weight: 600; color: #334155; margin-top: 1px;">
                    {{ $lender->processing_fee_display ?: 'Standard' }}
                </div>
            </div>
        </div>

        {{-- Apply Action Button --}}
        <button type="button"
                onclick="applyWithLender({{ $lender->id }}, '{{ addslashes($lender->name) }}')"
                class="fw-btn-primary"
                style="width: 100%; border: none; padding: 11px; font-size: 13.5px; font-weight: 700; border-radius: 8px; cursor: pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
            <span>Apply on {{ $lender->name }} Portal</span>
            <span style="font-size: 16px;">→</span>
        </button>
    </div>
    @empty
    <div class="fw-card" style="text-align:center; padding:30px 20px; color:#64748b;">
        <div style="font-size:28px; margin-bottom:8px;">🏦</div>
        <div style="font-size:14px; font-weight:700; color:#0f172a; margin-bottom:4px;">No Partner Lenders Currently Active</div>
        <div style="font-size:12px;">Please check back shortly or apply via our standard Cash or Business loan options.</div>
    </div>
    @endforelse

</div>

{{-- Hidden Form for Direct Submission Fallback --}}
<form id="hiddenLenderApplyForm" method="POST" action="" style="display:none;">
    @csrf
    <input type="hidden" name="name" id="post_name">
    <input type="hidden" name="phone" id="post_phone">
    <input type="hidden" name="email" id="post_email">
    <input type="hidden" name="referral_code" id="post_referral_code">
</form>
@endsection

@push('scripts')
<script>
function applyWithLender(lenderId, lenderName) {
    const nameInput = document.getElementById('applicant_name');
    const phoneInput = document.getElementById('applicant_phone');
    const emailInput = document.getElementById('applicant_email');
    const refInput = document.getElementById('referral_code');

    const name = nameInput ? nameInput.value.trim() : '';
    const phone = phoneInput ? phoneInput.value.trim() : '';
    const email = emailInput ? emailInput.value.trim() : '';
    const referralCode = refInput ? refInput.value.trim().toUpperCase() : '';

    if (!name) {
        alert('Please enter your Full Name.');
        if (nameInput) nameInput.focus();
        return;
    }

    if (!phone || phone.length < 8) {
        alert('Please enter a valid Mobile Number.');
        if (phoneInput) phoneInput.focus();
        return;
    }

    // Set hidden form action and fields
    const actionUrl = "{{ url('/finance/lender-loans/apply') }}/" + lenderId;
    const form = document.getElementById('hiddenLenderApplyForm');
    form.action = actionUrl;
    document.getElementById('post_name').value = name;
    document.getElementById('post_phone').value = phone;
    document.getElementById('post_email').value = email;
    document.getElementById('post_referral_code').value = referralCode;

    // Submit form (controller will save lead into database and redirect to lender URL)
    form.submit();
}
</script>
@endpush

@extends('finance.layouts.base')
@section('title', 'Loan Documents — Fiinway')
@section('header-sub', 'My Documents')

@section('back')
<a href="{{ route('finance.hub', ['phone' => $phone]) }}" class="fw-back">← Back to Dashboard</a>
@endsection

@section('content')
<div style="padding-bottom: 24px;">

    {{-- Header Title --}}
    <div style="margin-bottom: 18px;">
        <h1 class="fw-section-title" style="font-size: 20px;">My Loan Documents</h1>
        <p class="fw-section-sub" style="font-size: 12.5px; margin-bottom: 0;">Access your official sanction letter, disbursement receipt, and submitted KYC documents.</p>
    </div>

    @if(empty($loan))
    <div class="fw-card fw-text-center" style="padding: 30px 16px;">
        <div style="font-size: 40px; margin-bottom: 10px;">📁</div>
        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">No Documents Available</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">Documents will be generated once your loan application is sanctioned and processed.</p>
        <a href="{{ route('finance.hub', ['phone' => $phone]) }}" class="fw-btn fw-btn-primary" style="display:inline-block; width:auto; padding:10px 20px;">
            Go to Loan Hub
        </a>
    </div>
    @else

    {{-- Document Cards Grid --}}
    
    {{-- 1. Official Sanction Letter --}}
    <div class="fw-card" style="margin-bottom: 14px; padding: 16px; border-left: 4px solid #1a5fa8;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 8px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: #eff6ff; color: #1a5fa8; display:flex; align-items:center; justify-content:center; font-size: 20px;">
                    📄
                </div>
                <div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Sanction Letter &amp; Agreement</div>
                    <div style="font-size: 11px; color: #64748b;">Ref: {{ $loan->application_number }}</div>
                </div>
            </div>
            <span style="font-size: 10.5px; font-weight: 700; background: #ecfdf5; color: #059669; padding: 3px 8px; border-radius: 20px; border: 1px solid #a7f3d0;">
                Sanctioned ✓
            </span>
        </div>
        <p style="font-size: 12px; color: #475569; margin: 10px 0;">
            Official loan sanction document with lending partner <strong>{{ $loan->selected_lender_name ?? 'Partner Bank' }}</strong>, stating loan amount, tenure, and repayment terms.
        </p>
        <button type="button" onclick="showSanctionModal();" class="fw-btn fw-btn-outline" style="padding: 8px 14px; font-size: 12px; width: 100%;">
            👁 View Sanction Letter
        </button>
    </div>

    {{-- 2. Disbursement Receipt --}}
    @if($loan->application_status === 'DISBURSED' || $loan->disbursed_at)
    <div class="fw-card" style="margin-bottom: 14px; padding: 16px; border-left: 4px solid #10b981;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 8px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: #ecfdf5; color: #059669; display:flex; align-items:center; justify-content:center; font-size: 20px;">
                    💳
                </div>
                <div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Disbursement Confirmation Receipt</div>
                    <div style="font-size: 11px; color: #64748b;">UTR: {{ $loan->disbursement_txn_ref ?? 'CONFIRMED' }}</div>
                </div>
            </div>
            <span style="font-size: 10.5px; font-weight: 700; background: #ecfdf5; color: #059669; padding: 3px 8px; border-radius: 20px; border: 1px solid #a7f3d0;">
                Disbursed ✓
            </span>
        </div>
        <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; font-size: 12px; margin: 10px 0;">
            <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                <span style="color:#64748b;">Disbursed Amount:</span>
                <strong style="color:#059669;">₹{{ number_format($loan->approved_amount ?: $loan->requested_amount) }}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                <span style="color:#64748b;">Credited Bank:</span>
                <span style="font-weight:600;">{{ $loan->disbursement_bank_name ?: 'Bank' }}</span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span style="color:#64748b;">Account Number:</span>
                <span style="font-family:monospace; font-weight:600;">•••• {{ substr($loan->disbursement_account_number ?? '1234', -4) }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- 3. KYC Documents Summary --}}
    <div class="fw-card" style="margin-bottom: 14px; padding: 16px;">
        <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 12px;">
            Submitted Verification Documents
        </div>

        <div style="display:flex; flex-direction:column; gap:10px;">
            <div style="display:flex; align-items:center; justify-content:space-between; padding: 8px 10px; background: #f8fafc; border-radius: 6px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span>🪪</span>
                    <span style="font-size: 12.5px; font-weight: 600; color: #334155;">Aadhaar &amp; Identity Verification</span>
                </div>
                <span style="font-size: 11px; color: #059669; font-weight: 700;">Verified ✓</span>
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; padding: 8px 10px; background: #f8fafc; border-radius: 6px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span>🏦</span>
                    <span style="font-size: 12.5px; font-weight: 600; color: #334155;">Bank Details &amp; Passbook Proof</span>
                </div>
                <span style="font-size: 11px; color: #059669; font-weight: 700;">Verified ✓</span>
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; padding: 8px 10px; background: #f8fafc; border-radius: 6px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span>🤳</span>
                    <span style="font-size: 12.5px; font-weight: 600; color: #334155;">Applicant Live Selfie Verification</span>
                </div>
                <span style="font-size: 11px; color: #059669; font-weight: 700;">Verified ✓</span>
            </div>
        </div>
    </div>

    @endif

</div>

{{-- Modal for Sanction Letter View --}}
@if(!empty($loan))
<div id="sanctionModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#ffffff; border-radius:12px; max-width:440px; width:100%; max-height:85vh; overflow-y:auto; padding:22px; position:relative; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:12px; margin-bottom:16px;">
            <div style="font-size:16px; font-weight:800; color:#0f172a;">Loan Sanction Letter</div>
            <button onclick="closeSanctionModal();" style="background:none; border:none; font-size:20px; color:#64748b; cursor:pointer;">✕</button>
        </div>

        <div style="text-align:center; margin-bottom:16px;">
            <div style="font-size:18px; font-weight:800; color:#1a5fa8;">Fiinway Financial Services</div>
            <div style="font-size:11px; color:#64748b;">In Partnership With {{ $loan->selected_lender_name ?? 'Authorized Lending Partner' }}</div>
        </div>

        <div style="font-size:12px; line-height:1.6; color:#334155;">
            <p><strong>To:</strong> {{ $applicantName }}<br>
            <strong>Mobile:</strong> {{ $applicantPhone }}<br>
            <strong>Date:</strong> {{ $loan->created_at ? $loan->created_at->format('d M Y') : date('d M Y') }}<br>
            <strong>Application No.:</strong> {{ $loan->application_number }}</p>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin:14px 0;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span>Sanctioned Amount:</span>
                    <strong>₹{{ number_format($loan->approved_amount ?: $loan->requested_amount) }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span>Tenure:</span>
                    <strong>{{ $loan->tenure_months ?: 12 }} Months</strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span>Monthly EMI:</span>
                    <strong>₹{{ number_format($loan->estimated_emi ?: round(($loan->approved_amount ?: 25000) / ($loan->tenure_months ?: 12))) }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Disbursement Bank:</span>
                    <strong>{{ $loan->disbursement_bank_name ?: 'HDFC Bank' }}</strong>
                </div>
            </div>

            <p style="font-size:11px; color:#64748b;">
                This sanction letter is electronically generated and confirms the terms agreed between borrower and the lending institution.
            </p>
        </div>

        <button onclick="closeSanctionModal();" class="fw-btn fw-btn-primary" style="margin-top:16px;">
            Close Document
        </button>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function showSanctionModal() {
    const m = document.getElementById('sanctionModal');
    if (m) m.style.display = 'flex';
}
function closeSanctionModal() {
    const m = document.getElementById('sanctionModal');
    if (m) m.style.display = 'none';
}
</script>
@endpush

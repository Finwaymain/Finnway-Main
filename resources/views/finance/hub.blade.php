@extends('finance.layouts.base')
@section('title', 'Finance — Fiinway')
@section('header-sub', 'Finance Products')

@section('content')
<div style="padding-bottom:8px;">

    {{-- Error / Conflict Banner --}}
    @if(session('card_error_msg'))
    <div style="background: #fef2f2; border: 1.5px solid #f87171; border-radius: 12px; padding: 16px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);">
        <div style="display:flex; align-items:flex-start; gap:10px;">
            <span style="font-size:20px; line-height:1;">⚠️</span>
            <div style="flex:1;">
                <div style="font-size:14px; font-weight:700; color:#991b1b; margin-bottom:4px;">
                    Active Application In Progress
                </div>
                <div style="font-size:12.5px; color:#7f1d1d; line-height:1.45; margin-bottom:12px;">
                    {{ session('card_error_msg') }}
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                    @if(session('active_resume_url'))
                    <a href="{{ session('active_resume_url') }}" style="background:#dc2626; color:white; font-size:12.5px; font-weight:700; padding:8px 14px; border-radius:6px; text-decoration:none;">
                        Resume Active Application →
                    </a>
                    @endif
                    @if(!empty($application) && (empty($application->fee_payment_status) || $application->fee_payment_status !== 'paid'))
                    <a href="{{ route('finance.withdraw_application', ['phone' => request('phone') ?? ($phone ?? '')]) }}" style="color:#b91c1c; font-size:12px; font-weight:600; text-decoration:underline;">
                        Cancel &amp; Withdraw Application
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- General Success Banner --}}
    @if(session('success'))
    <div style="background: #ecfdf5; border: 1.5px solid #6ee7b7; border-radius: 10px; padding: 14px; margin-bottom: 18px; color: #065f46; font-size: 13px; font-weight: 600; display:flex; align-items:center; gap:8px;">
        <span>✓</span>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    {{-- General Error Banner --}}
    @if(session('error'))
    <div style="background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 14px; margin-bottom: 18px; color: #991b1b; font-size: 13px; font-weight: 600; display:flex; align-items:center; gap:8px;">
        <span>⚠</span>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    {{-- Active Application In Progress Card --}}
    @if(!empty($resumeUrl) && !empty($application) && empty($disbursedLoan))
    <div style="background: #0f172a; border-radius: 12px; padding: 16px; margin-bottom: 20px; color: white; border: 1.5px solid #334155; box-shadow: 0 4px 12px rgba(15,23,42,0.15);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; background: rgba(245, 166, 35, 0.2); color: #f5a623; padding: 3px 8px; border-radius: 4px; font-weight: 700;">Application In Progress</span>
            <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">{{ $application->application_number ?? '' }}</span>
        </div>
        <div style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">{{ $application->applicant_name ?? 'Loan Applicant' }}</div>
        <div style="font-size: 13px; color: #cbd5e1; margin-bottom: 12px;">
            Amount: <strong style="color:#f5a623;">₹{{ number_format($amount) }}</strong> · {{ $tenure }} Months
        </div>
        <a href="{{ $resumeUrl }}" style="display: block; text-align: center; background: #f5a623; color: #0f172a; font-weight: 800; font-size: 13px; padding: 10px; border-radius: 8px; text-decoration: none;">
            Resume Active Loan Application →
        </a>

        @if(empty($application->fee_payment_status) || $application->fee_payment_status !== 'paid')
        <div style="text-align:center; margin-top:12px; border-top:1px solid #1e293b; padding-top:10px;">
            <a href="{{ route('finance.withdraw_application', ['phone' => request('phone') ?? ($phone ?? '')]) }}" style="color:#f87171; font-size:12px; font-weight:600; text-decoration:none;">
                ✕ Cancel &amp; Withdraw Application
            </a>
        </div>
        @endif
    </div>
    @endif

    {{-- Active Disbursed Loan Dashboard --}}
    @if(!empty($disbursedLoan))
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px; padding: 20px; margin-bottom: 22px; color: white; border: 1.5px solid #334155; box-shadow: 0 10px 25px rgba(15,23,42,0.25);">
        {{-- Status Header --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 14px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,0.3);"></span>
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; background: rgba(16, 185, 129, 0.18); color: #34d399; padding: 4px 10px; border-radius: 20px; font-weight: 800; border: 1px solid rgba(16,185,129,0.3);">
                    🟢 Disbursed &amp; Active
                </span>
            </div>
            <span style="font-size: 11px; color: #94a3b8; font-family: monospace; background: rgba(255,255,255,0.06); padding: 3px 8px; border-radius: 4px;">
                {{ $disbursedLoan->application_number }}
            </span>
        </div>

        {{-- Borrower & Disbursed Amount --}}
        <div style="margin-bottom: 16px;">
            <div style="font-size: 11.5px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Active Loan Facility</div>
            <div style="display:flex; align-items:baseline; gap:8px; margin-top:2px;">
                <div style="font-size: 32px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">
                    ₹{{ number_format($disbursedLoan->approved_amount ?: $disbursedLoan->requested_amount) }}
                </div>
                <div style="font-size: 12px; color: #10b981; font-weight: 700;">✓ Disbursed</div>
            </div>
            <div style="font-size: 12px; color: #cbd5e1; margin-top: 3px;">
                Disbursed on {{ $disbursedLoan->disbursed_at ? $disbursedLoan->disbursed_at->format('d M Y') : 'Recently' }} to <strong>{{ $disbursedLoan->disbursement_bank_name ?: 'Bank Account' }}</strong>
                @if($disbursedLoan->disbursement_account_number)
                (••••{{ substr($disbursedLoan->disbursement_account_number, -4) }})
                @endif
            </div>
            @if($disbursedLoan->disbursement_txn_ref)
            <div style="display:inline-block; font-size: 11px; color: #38bdf8; font-family: monospace; background: rgba(56, 189, 248, 0.12); padding: 2px 8px; border-radius: 4px; margin-top: 6px;">
                Bank UTR: {{ $disbursedLoan->disbursement_txn_ref }}
            </div>
            @endif
        </div>

        {{-- 4 Stat Matrix --}}
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 8px; background: rgba(255,255,255,0.05); padding: 12px; border-radius: 10px; margin-bottom: 16px; border: 1px solid rgba(255,255,255,0.08);">
            <div>
                <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Lender Partner</div>
                <div style="font-size: 13px; font-weight: 700; color: #ffffff; margin-top: 2px;">
                    {{ $disbursedLoan->selected_lender_name ?? ($disbursedLoan->lender->name ?? 'Lending Partner') }}
                </div>
            </div>
            <div>
                <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Tenure</div>
                <div style="font-size: 13px; font-weight: 700; color: #ffffff; margin-top: 2px;">
                    {{ $disbursedLoan->tenure_months ?: 12 }} Months
                </div>
            </div>
            <div>
                <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Monthly EMI</div>
                <div style="font-size: 13px; font-weight: 700; color: #f5a623; margin-top: 2px;">
                    ₹{{ number_format($disbursedLoan->estimated_emi ?: round(($disbursedLoan->approved_amount ?: 25000) / ($disbursedLoan->tenure_months ?: 12))) }}
                </div>
            </div>
            <div>
                <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Next Due Date</div>
                <div style="font-size: 13px; font-weight: 700; color: #ffffff; margin-top: 2px;">
                    {{ $nextDueSchedule ? \Carbon\Carbon::parse($nextDueSchedule->schedule_date)->format('d M Y') : now()->addDays(30)->format('d M Y') }}
                </div>
            </div>
        </div>

        {{-- Next EMI Payment Banner --}}
        @if($nextDueSchedule)
        <div style="background: rgba(245, 166, 35, 0.12); border: 1.5px solid rgba(245, 166, 35, 0.35); border-radius: 10px; padding: 12px 14px; margin-bottom: 16px; display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #f5a623; text-transform: uppercase;">Upcoming Installment #{{ $nextDueSchedule->day_number }}</div>
                <div style="font-size: 16px; font-weight: 800; color: #ffffff;">₹{{ number_format($nextDueSchedule->total_due) }}</div>
                <div style="font-size: 11px; color: #cbd5e1;">Due on {{ \Carbon\Carbon::parse($nextDueSchedule->schedule_date)->format('d M Y') }}</div>
            </div>
            <a href="{{ route('finance.repayments', ['phone' => $phone]) }}" style="background: #f5a623; color: #0f172a; font-weight: 800; font-size: 12px; padding: 8px 14px; border-radius: 8px; text-decoration: none; white-space:nowrap; box-shadow: 0 4px 12px rgba(245, 166, 35, 0.25);">
                💳 Pay EMI →
            </a>
        </div>
        @endif

        {{-- Quick Nav 4 Action Buttons --}}
        <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
            <a href="{{ route('finance.repayments', ['phone' => $phone]) }}" style="background: rgba(255,255,255,0.08); border-radius: 8px; padding: 10px 4px; text-align: center; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <span style="font-size: 18px; margin-bottom: 2px;">💳</span>
                <span style="font-size: 10.5px; font-weight: 600; color: #e2e8f0;">Repay</span>
            </a>
            <a href="{{ route('finance.repayments', ['phone' => $phone]) }}" style="background: rgba(255,255,255,0.08); border-radius: 8px; padding: 10px 4px; text-align: center; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <span style="font-size: 18px; margin-bottom: 2px;">📅</span>
                <span style="font-size: 10.5px; font-weight: 600; color: #e2e8f0;">Schedule</span>
            </a>
            <a href="{{ route('finance.documents', ['phone' => $phone]) }}" style="background: rgba(255,255,255,0.08); border-radius: 8px; padding: 10px 4px; text-align: center; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <span style="font-size: 18px; margin-bottom: 2px;">📁</span>
                <span style="font-size: 10.5px; font-weight: 600; color: #e2e8f0;">Docs</span>
            </a>
            <a href="{{ route('finance.support', ['phone' => $phone]) }}" style="background: rgba(255,255,255,0.08); border-radius: 8px; padding: 10px 4px; text-align: center; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <span style="font-size: 18px; margin-bottom: 2px;">💬</span>
                <span style="font-size: 10.5px; font-weight: 600; color: #e2e8f0;">Support</span>
            </a>
        </div>
    </div>
    @endif

    @if(!empty($disbursedLoan))
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; margin-top:20px;">
        <span style="font-size:12px; font-weight:800; color:var(--navy); text-transform:uppercase; letter-spacing:0.5px;">Explore Other Credit Products</span>
        <span style="font-size:11px; color:#64748b; font-weight:600;">Apply Additional</span>
    </div>
    @else
    <p style="color:var(--gray3);font-size:12px;margin-bottom:16px;">Select a product to begin your application.</p>
    @endif

    {{-- Flow A: Bank/NBFC Loans --}}
    <div class="fw-section-label">Bank & NBFC Loans</div>

    <a href="{{ (!empty($resumeUrl) && ($activeFamily ?? '') === 'cash_loan') ? $resumeUrl : route('finance.cash_loan.s01_apply', ['phone' => request('phone')]) }}"
       class="fw-product-card"
       style="border-left:4px solid var(--blue);"
       onclick="return handleCardClick(event, 'cash_loan', 'Cash Loan — Low CIBIL');">
        <div class="fw-product-icon" style="background:var(--blue);">₹</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Cash Loan — Low CIBIL</div>
            <div class="fw-product-sub">
                @if(!empty($resumeUrl) && ($activeFamily ?? '') === 'cash_loan')
                    <span style="color:#f5a623; font-weight:700;">● Active Application — Tap to Resume</span>
                @else
                    Up to ₹4,00,000 · Processing fee applicable
                @endif
            </div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    <a href="{{ (!empty($resumeUrl) && ($activeFamily ?? '') === 'cash_loan') ? $resumeUrl : route('finance.cash_loan.s01_apply', ['phone' => request('phone'), 'type' => 'good_cibil']) }}"
       class="fw-product-card"
       style="border-left:4px solid var(--blue2);"
       onclick="return handleCardClick(event, 'cash_loan', 'Cash Loan — Good CIBIL');">
        <div class="fw-product-icon" style="background:var(--blue2);">₹</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Cash Loan — Good CIBIL</div>
            <div class="fw-product-sub">Up to ₹50,00,000 · Processing fee applicable</div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    <a href="{{ (!empty($resumeUrl) && ($activeFamily ?? '') === 'business_loan') ? $resumeUrl : route('finance.business_loan.s01_apply', ['phone' => request('phone')]) }}"
       class="fw-product-card"
       style="border-left:4px solid var(--slate);"
       onclick="return handleCardClick(event, 'business_loan', 'Business Loan');">
        <div class="fw-product-icon" style="background:var(--slate);">🏢</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Business Loan</div>
            <div class="fw-product-sub">
                @if(!empty($resumeUrl) && ($activeFamily ?? '') === 'business_loan')
                    <span style="color:#f5a623; font-weight:700;">● Active Application — Tap to Resume</span>
                @else
                    ₹5 Lakh – ₹2 Crore · Processing fee applicable
                @endif
            </div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    <a href="{{ route('finance.lender_loans.index', ['phone' => request('phone')]) }}"
       class="fw-product-card"
       style="border-left:4px solid #0284c7;">
        <div class="fw-product-icon" style="background:#0284c7;">🤝</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Direct Partner Lender Loans</div>
            <div class="fw-product-sub">Instant Referral &amp; Portal Apply · Top NBFC &amp; Bank Partners</div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    {{-- Flow B: Fiinway Internal Credit --}}
    <div class="fw-section-label" style="margin-top:20px;">Fiinway Credit Products</div>

    <a href="{{ (!empty($resumeUrl) && ($activeFamily ?? '') === 'zero_cibil') ? $resumeUrl : route('finance.zero_cibil.s01_intro', ['phone' => request('phone')]) }}"
       class="fw-product-card"
       style="border-left:4px solid var(--green);"
       onclick="return handleCardClick(event, 'zero_cibil', 'Zero-CIBIL Daily Credit');">
        <div class="fw-product-icon" style="background:var(--green);">0%</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Zero-CIBIL Daily Credit</div>
            <div class="fw-product-sub">
                @if(!empty($resumeUrl) && ($activeFamily ?? '') === 'zero_cibil')
                    <span style="color:#f5a623; font-weight:700;">● Active Application — Tap to Resume</span>
                @else
                    ₹20,000 – ₹2,00,000 · Interest-free · Daily repayment
                @endif
            </div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    <a href="{{ (!empty($resumeUrl) && ($activeFamily ?? '') === 'virtual_loan') ? $resumeUrl : route('finance.virtual_loan.s01_apply', ['phone' => request('phone')]) }}"
       class="fw-product-card"
       style="border-left:4px solid var(--accent);"
       onclick="return handleCardClick(event, 'virtual_loan', 'Virtual Loan');">
        <div class="fw-product-icon" style="background:var(--accent);color:var(--navy);">V</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Virtual Loan</div>
            <div class="fw-product-sub">
                @if(!empty($resumeUrl) && ($activeFamily ?? '') === 'virtual_loan')
                    <span style="color:#f5a623; font-weight:700;">● Active Application — Tap to Resume</span>
                @else
                    ₹15,000 – ₹45,000 · Scan & Pay wallet
                @endif
            </div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    <a href="{{ (!empty($resumeUrl) && ($activeFamily ?? '') === 'student_credit') ? $resumeUrl : route('finance.student_credit.s01_apply', ['phone' => request('phone')]) }}"
       class="fw-product-card"
       style="border-left:4px solid var(--amber);"
       onclick="return handleCardClick(event, 'student_credit', 'Student Credit');">
        <div class="fw-product-icon" style="background:var(--amber);">🎓</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Student Credit</div>
            <div class="fw-product-sub">
                @if(!empty($resumeUrl) && ($activeFamily ?? '') === 'student_credit')
                    <span style="color:#f5a623; font-weight:700;">● Active Application — Tap to Resume</span>
                @else
                    Age 16–26 · Domestic & International · App-to-App
                @endif
            </div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>
</div>
@endsection

@push('head')
<style>
.fw-section-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--gray3);
    margin-bottom: 10px;
}
.fw-product-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--white);
    border-radius: 10px;
    padding: 14px 12px;
    margin-bottom: 10px;
    text-decoration: none;
    color: var(--text);
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    transition: box-shadow 0.15s;
}
.fw-product-card:active { box-shadow: 0 2px 8px rgba(0,0,0,0.12); }
.fw-product-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
}
.fw-product-body { flex: 1; min-width: 0; }
.fw-product-title { font-size: 14px; font-weight: 600; color: var(--text); }
.fw-product-sub { font-size: 11px; color: var(--gray3); margin-top: 2px; }
.fw-product-arrow { font-size: 20px; color: var(--gray3); flex-shrink: 0; }
</style>
@endpush

@push('scripts')
<script>
function handleCardClick(event, targetFamily, targetTitle) {
    @if(!empty($isRunning) && !empty($application))
    const activeFamily = "{{ $activeFamily ?? 'cash_loan' }}";
    if (targetFamily !== activeFamily) {
        event.preventDefault();
        window.location.href = "{{ route('finance.hub', ['phone' => request('phone') ?? ($phone ?? '')]) }}?card_type=" + encodeURIComponent(targetFamily);
        return false;
    }
    @endif
    return true;
}
</script>
@endpush

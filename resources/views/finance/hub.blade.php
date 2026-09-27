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
    @if(!empty($resumeUrl) && !empty($application))
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

    <p style="color:var(--gray3);font-size:12px;margin-bottom:16px;">Select a product to begin your application.</p>

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

@extends('finance.layouts.base')
@section('title', 'Finance — Fiinway')
@section('header-sub', 'Finance Products')

@section('content')
<div style="padding-bottom:8px;">
    <p style="color:var(--gray3);font-size:12px;margin-bottom:16px;">Select a product to begin your application.</p>

    {{-- Flow A: Bank/NBFC Loans --}}
    <div class="fw-section-label">Bank & NBFC Loans</div>

    <a href="{{ route('finance.cash_loan.s01_apply', ['phone' => request('phone')]) }}" class="fw-product-card" style="border-left:4px solid var(--blue);">
        <div class="fw-product-icon" style="background:var(--blue);">₹</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Cash Loan — Low CIBIL</div>
            <div class="fw-product-sub">Up to ₹4,00,000 · Processing fee applicable</div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    <a href="{{ route('finance.cash_loan.s01_apply', ['phone' => request('phone'), 'type' => 'good_cibil']) }}" class="fw-product-card" style="border-left:4px solid var(--blue2);">
        <div class="fw-product-icon" style="background:var(--blue2);">₹</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Cash Loan — Good CIBIL</div>
            <div class="fw-product-sub">Up to ₹50,00,000 · Processing fee applicable</div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    <a href="{{ route('finance.business_loan.s01_apply', ['phone' => request('phone')]) }}" class="fw-product-card" style="border-left:4px solid var(--slate);">
        <div class="fw-product-icon" style="background:var(--slate);">🏢</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Business Loan</div>
            <div class="fw-product-sub">₹5 Lakh – ₹2 Crore · Processing fee applicable</div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    {{-- Flow B: Fiinway Internal Credit --}}
    <div class="fw-section-label" style="margin-top:20px;">Fiinway Credit Products</div>

    <a href="{{ route('finance.zero_cibil.s01_intro', ['phone' => request('phone')]) }}" class="fw-product-card" style="border-left:4px solid var(--green);">
        <div class="fw-product-icon" style="background:var(--green);">0%</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Zero-CIBIL Daily Credit</div>
            <div class="fw-product-sub">₹20,000 – ₹2,00,000 · Interest-free · Daily repayment</div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    <a href="{{ route('finance.virtual_loan.s01_apply', ['phone' => request('phone')]) }}" class="fw-product-card" style="border-left:4px solid var(--accent);">
        <div class="fw-product-icon" style="background:var(--accent);color:var(--navy);">V</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Virtual Loan</div>
            <div class="fw-product-sub">₹15,000 – ₹45,000 · Scan & Pay wallet</div>
        </div>
        <div class="fw-product-arrow">›</div>
    </a>

    <a href="{{ route('finance.student_credit.s01_apply', ['phone' => request('phone')]) }}" class="fw-product-card" style="border-left:4px solid var(--amber);">
        <div class="fw-product-icon" style="background:var(--amber);">🎓</div>
        <div class="fw-product-body">
            <div class="fw-product-title">Student Credit</div>
            <div class="fw-product-sub">Age 16–26 · Domestic & International · App-to-App</div>
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

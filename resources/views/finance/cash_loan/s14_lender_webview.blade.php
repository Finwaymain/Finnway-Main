@extends('finance.layouts.base')
@section('title', 'Lender Process — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s13_partner_redirect', ['phone' => request('phone'), 'partner_id' => $selectedPartner->id ?? '']) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-webview-header" style="background:#002147; color:#fff; padding:10px 14px; border-radius:6px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
    <div class="fw-app-no" style="font-family:monospace; font-size:12px; font-weight:700;">Application: {{ $appNumber }}</div>
    <div style="font-size:11px; color:#cbd5e1;">{{ $selectedPartner->name ?? 'Lender Portal' }}</div>
</div>
@php
    $portalUrl = $selectedPartner->application_url ?? ($application->lender->application_url ?? 'https://fiinway.com');
@endphp
<div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
    <iframe src="{{ $portalUrl }}" style="width:100%; height:75vh; border:none; padding:0; margin:0;" title="{{ $selectedPartner->name ?? 'Lender Portal' }}"></iframe>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <a href="{{ route('finance.cash_loan.s15_proof_upload', ['phone' => request('phone'), 'partner_id' => $selectedPartner->id ?? '']) }}" class="fw-btn fw-btn-primary" style="display:block; text-align:center;">
        Process Completed → Upload Proof
    </a>
</div>
@endsection

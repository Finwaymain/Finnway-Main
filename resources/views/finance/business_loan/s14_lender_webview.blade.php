@extends('finance.layouts.base')
@section('title', 'Lender Platform — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s13_partner_select', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-webview-header">
    <div class="fw-app-no">Application No.: FIIN-BL-2026-123456</div>
</div>
<iframe src="{{ $ctx['lender_url'] ?? '#' }}" style="width:100%; height:80vh; border:none; padding:0; margin:0;"></iframe>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom" style="background:none;">
    <a href="{{ route('finance.business_loan.s15_lender_processing', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Process Completed → Upload Proof</a>
</div>
@endsection

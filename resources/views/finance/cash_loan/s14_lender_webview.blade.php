@extends('finance.layouts.base')
@section('title', 'Lender Process — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s13_partner_select', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-webview-header">
    <div class="fw-app-no">Application No.: FIIN-CL-{{ rand(100000, 999999) }}</div>
</div>
<iframe src="{{ $ctx['lender_url'] ?? '#' }}" style="width:100%; height:80vh; border:none; padding:0; margin:0;"></iframe>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom" style="background:none;">
    <a href="{{ route('finance.cash_loan.s15_proof_upload', ['phone' => request('phone')]) }}" class="fw-btn fw-btn-primary">Process Completed → Upload Proof</a>
</div>
@endsection

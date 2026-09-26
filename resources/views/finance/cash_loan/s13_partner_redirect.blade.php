@extends('finance.layouts.base')
@section('title', 'Partner Redirect — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span style="float:left;">Step 11 of 12</span><span style="float:right;">91%</span><div style="clear:both;"></div></div>
    <div class="fw-progress-track" style="background:#e0e0e0; height:6px; border-radius:3px; margin-top:5px;"><div class="fw-progress-fill" style="width:91%; background:#002147; height:100%; border-radius:3px;"></div></div>
</div>
@endsection

@section('content')
<div class="fw-card mt-4 mb-5 pb-4 text-center">
    <h3 class="fw-h3 mb-4">Selected Partner</h3>

    <div class="p-4 border rounded mb-4" style="background:#f8f9fa; border:2px solid #002147 !important;">
        <div style="width:60px; height:60px; background:#fff; border:1px solid #ddd; border-radius:8px; margin:0 auto 15px auto; display:flex; align-items:center; justify-content:center; font-size:12px; color:#999;">LOGO</div>
        <h4 style="margin:0 0 10px 0; color:#002147;">HDFC Bank</h4>
        <span class="badge" style="background:#28a745; color:white; padding:5px 10px; border-radius:12px; font-size:12px;">✓ ACTIVE</span>
    </div>

    <div class="mb-4 text-start">
        <p class="small text-muted text-center">Other available partners for this application are now <strong class="text-danger">LOCKED</strong>.</p>
    </div>

    <p class="small text-muted mb-4" style="line-height:1.5;">
        You will now be securely redirected to the selected lender's official portal to complete your loan documentation and e-signing process.
    </p>

    <!-- Assuming s14_lender_webview will be the next step handled outside of this current task, linking placeholder -->
    <a href="#" class="fw-btn fw-btn-primary w-100 d-block p-3" style="font-size:16px;">Proceed to HDFC Bank &rarr;</a>
</div>
@endsection

@extends('finance.layouts.base')
@section('title', 'Application Status — Fiinway')
@section('header-sub', 'Cash Loan')

{{-- No back button on underwriting status screen per specification --}}
@section('back')
@endsection

@section('content')
<div class="fw-card" style="padding: 24px 16px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #fef3c7; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 10px;">
            ⏳
        </div>
        <h2 class="fw-heading" style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
            Your Loan Application Status
        </h2>
        <div style="display: inline-block; background: #fffbeb; border: 1px solid #fde68a; color: #b45309; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 20px;">
            🟡 Underwriting Review In Progress
        </div>
    </div>
    
    <!-- Application Details Card -->
    <div style="border: 1px solid #e2e8f0; padding: 18px; border-radius: 12px; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.02); margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13px;">
            <span style="color: #64748b;">Application No.:</span>
            <span style="font-family: monospace; font-weight: 700; color: #0f172a; font-size: 14px;">{{ $appNumber }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13px;">
            <span style="color: #64748b;">Loan Type:</span>
            <span style="font-weight: 600; color: #0f172a;">{{ $product->title ?? ucwords(str_replace('_', ' ', $loanType ?? 'Cash Loan')) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13px;">
            <span style="color: #64748b;">Applied Amount:</span>
            <span style="font-weight: 700; color: #2563eb; font-size: 14px;">₹{{ number_format($application->requested_amount ?? $amount) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
            <span style="color: #64748b;">Selected Lender:</span>
            <span style="font-weight: 600; color: #0f172a;">{{ $selectedPartner->name ?? ($application->selected_lender_name ?? 'Lending Partner') }}</span>
        </div>
        
        <hr style="margin: 14px 0; border: none; border-top: 1px solid #f1f5f9;">

        <!-- Milestone Checklist -->
        <div style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
            Application Milestones
        </div>
        <ul style="list-style: none; padding: 0; margin: 0; line-height: 2.0; font-size: 13px;">
            <li style="color: #059669; font-weight: 600;">✓ Application submitted</li>
            <li style="color: #059669; font-weight: 600;">✓ KYC Documents submitted</li>
            <li style="color: #059669; font-weight: 600;">✓ Lender process completed</li>
            <li style="color: #059669; font-weight: 600;">✓ Lender screenshot uploaded</li>
            <li style="color: #059669; font-weight: 600;">✓ Joint selfie verification recorded</li>
            <li style="color: #d97706; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                <span class="pulse-icon" style="display: inline-block;">⏳</span>
                <span>Final underwriting review &mdash; <strong>In Progress</strong></span>
            </li>
        </ul>
    </div>

    <!-- Live Status Notice Box -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; text-align: center;">
        <div style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 4px;">
            Underwriting in Progress
        </div>
        <p style="font-size: 12px; color: #64748b; line-height: 1.5; margin: 0;">
            Your verification selfie and credit details are currently being reviewed by the credit underwriting team.<br>
            <strong>This screen will automatically advance once approved.</strong>
        </p>
    </div>
</div>

{{-- No sticky bottom continue button per specification --}}
@section('sticky-bottom')
@endsection

<style>
@keyframes pulseGlow {
    0% { transform: scale(1); opacity: 0.9; }
    50% { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(1); opacity: 0.9; }
}
.pulse-icon {
    animation: pulseGlow 1.8s infinite ease-in-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var pollUrl = "{{ route('finance.cash_loan.application_status_poll', ['phone' => $phone, 'current_step' => 's18', 'application_id' => $application->id ?? '']) }}";
    
    // Real-Time Underwriting Status Poller (every 2.5s)
    var pollInterval = setInterval(function () {
        fetch(pollUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data && data.action === 'redirect' && data.redirect_url) {
                clearInterval(pollInterval);
                window.location.href = data.redirect_url;
            }
        })
        .catch(function (err) {
            console.log('Underwriting poll waiting...', err);
        });
    }, 2500);
});
</script>
@endsection

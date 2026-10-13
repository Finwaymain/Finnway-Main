@extends('finance.layouts.base')
@section('title', 'Application Status — Virtual Loan')
@section('header-sub', 'Virtual Loan')

@php
    $status = $application->application_status ?? 'UNDER_REVIEW';
    $isUnderReview = in_array($status, ['UNDER_REVIEW', 'SUBMITTED', 'KYC_PENDING']);
    $isApproved = in_array($status, ['APPROVED_FEE_PENDING', 'SANCTIONED', 'FEE_PENDING']);
    $isFeePaid = in_array($status, ['FEE_PAID', 'VERIFICATION_PENDING', 'UNDERWRITING']);
    $isActive = in_array($status, ['ACTIVE', 'DISBURSED', 'LOAN_APPROVED']);
    $isRejected = ($status === 'REJECTED');
    $appAmount = $application->approved_amount ?: ($application->requested_amount ?: 30000);
@endphp

@section('content')

@if($isRejected)
    <div style="text-align:center;padding:24px 0 16px;">
        <div style="width:64px;height:64px;background:#fef2f2;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:30px;margin-bottom:12px;">
            ✕
        </div>
        <p class="fw-section-title" style="margin-bottom:6px;color:#dc2626;">Application Not Approved</p>
        <p class="fw-section-sub">Your application could not be approved at this time based on internal underwriting criteria.</p>
    </div>

    <div class="fw-card">
        <p class="fw-card-title">Decision Details</p>
        <div class="fw-info-row">
            <span class="fw-info-label">Application Number</span>
            <span class="fw-info-value">{{ $application->application_number ?? 'FIIN-VL' }}</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Reason</span>
            <span class="fw-info-value" style="color:#dc2626;">{{ $application->rejection_reason ?: 'Criteria not met.' }}</span>
        </div>
        <div class="fw-alert fw-alert-info" style="margin-top:12px;margin-bottom:0;">
            ℹ️ You may re-apply after 3 days.
        </div>
    </div>
@elseif($isUnderReview)
    <div style="text-align:center;padding:24px 0 16px;">
        <div style="width:64px;height:64px;background:#fef3c7;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:30px;margin-bottom:12px;">
            ⏳
        </div>
        <p class="fw-section-title" style="margin-bottom:6px;">Application Under Review</p>
        <p class="fw-section-sub">Your KYC documents have been submitted. Our team is verifying your profile. You will be notified once approved.</p>
    </div>

    <div class="fw-card">
        <p class="fw-card-title">Application Summary</p>
        <div class="fw-info-row">
            <span class="fw-info-label">Application ID</span>
            <span class="fw-info-value" style="font-family:monospace;font-weight:700;">{{ $application->application_number ?? 'FIIN-VL' }}</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Requested Limit</span>
            <span class="fw-info-value" style="font-weight:700;color:var(--navy);">₹{{ number_format($appAmount) }}</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Current Status</span>
            <span class="fw-badge fw-badge-pending">Under Admin Review</span>
        </div>
    </div>

    <div class="fw-card">
        <p class="fw-card-title">Verification Steps</p>

        <div class="fw-check-item">
            <div class="fw-check-icon fw-check-done">✓</div>
            <div class="fw-check-text">
                <strong>Application Details Submitted</strong>
                <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Name, Mobile &amp; KYC information recorded</p>
            </div>
        </div>

        <div class="fw-check-item">
            <div class="fw-check-icon fw-check-done">✓</div>
            <div class="fw-check-text">
                <strong>Documents Uploaded</strong>
                <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Aadhaar Card &amp; PAN Card verified</p>
            </div>
        </div>

        <div class="fw-check-item">
            <div class="fw-check-icon fw-check-active" style="background:#fef3c7;border-color:#f59e0b;color:#b45309;">●</div>
            <div class="fw-check-text">
                <strong>Admin Approval in Progress</strong>
                <p style="font-size:12px;color:#b45309;margin-top:2px;">Awaiting underwriting review &amp; limit sanction</p>
            </div>
        </div>

        <div class="fw-check-item">
            <div class="fw-check-icon" style="background:#f1f5f9;color:#94a3b8;border:1.5px solid #cbd5e1;">○</div>
            <div class="fw-check-text">
                <strong style="color:#94a3b8;">Processing Fee Payment</strong>
                <p style="font-size:12px;color:#94a3b8;margin-top:2px;">Enabled once approved by administration</p>
            </div>
        </div>

        <div class="fw-check-item">
            <div class="fw-check-icon" style="background:#f1f5f9;color:#94a3b8;border:1.5px solid #cbd5e1;">○</div>
            <div class="fw-check-text">
                <strong style="color:#94a3b8;">Virtual Credit Wallet Credited</strong>
                <p style="font-size:12px;color:#94a3b8;margin-top:2px;">Activated after fee confirmation</p>
            </div>
        </div>
    </div>

    <div class="fw-alert fw-alert-info">
        ℹ️ Please wait while our team reviews your documents. This page automatically updates as soon as your loan is approved.
    </div>
@elseif($isApproved)
    <div style="text-align:center;padding:24px 0 16px;">
        <div style="width:64px;height:64px;background:#ecfdf5;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:30px;margin-bottom:12px;">
            🎉
        </div>
        <p class="fw-section-title" style="margin-bottom:6px;color:#059669;">Loan Approved!</p>
        <p class="fw-section-sub">Your loan limit has been sanctioned by administration. Pay the processing fee to complete wallet activation.</p>
    </div>

    <div class="fw-card">
        <p class="fw-card-title">Sanctioned Limit</p>
        <div class="fw-info-row">
            <span class="fw-info-label">Approved Limit</span>
            <span class="fw-info-value" style="font-weight:700;font-size:18px;color:#059669;">₹{{ number_format($appAmount) }}</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Wallet Type</span>
            <span class="fw-info-value">Virtual Business Credit Wallet</span>
        </div>
        <div class="fw-info-row">
            <span class="fw-info-label">Status</span>
            <span class="fw-badge fw-badge-success">Sanction Approved</span>
        </div>
    </div>
@elseif($isFeePaid)
    <div style="text-align:center;padding:24px 0 16px;">
        <div style="width:64px;height:64px;background:#e8f4fd;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:30px;margin-bottom:12px;">
            ⚙️
        </div>
        <p class="fw-section-title" style="margin-bottom:6px;">Processing Fee Received</p>
        <p class="fw-section-sub">Payment confirmed. System and administration are completing final ledger setup and merchant spending limits.</p>
    </div>

    <div class="fw-card">
        <p class="fw-card-title">Final Setup Status</p>

        <div class="fw-check-item">
            <div class="fw-check-icon fw-check-done">✓</div>
            <div class="fw-check-text">
                <strong>KYC Documents Approved</strong>
                <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Aadhaar &amp; PAN verified</p>
            </div>
        </div>

        <div class="fw-check-item">
            <div class="fw-check-icon fw-check-done">✓</div>
            <div class="fw-check-text">
                <strong>Processing Fee Confirmed</strong>
                <p style="font-size:12px;color:var(--gray3);margin-top:2px;">₹{{ number_format($application->processing_fee_total ?: 3000) }} received successfully</p>
            </div>
        </div>

        <div class="fw-check-item">
            <div class="fw-check-icon fw-check-active" style="background:#e8f4fd;border-color:#0284c7;color:#0284c7;">●</div>
            <div class="fw-check-text">
                <strong>Virtual Ledger Crediting</strong>
                <p style="font-size:12px;color:var(--gray3);margin-top:2px;">Finalizing credit limit allocation</p>
            </div>
        </div>
    </div>

    <div class="fw-alert fw-alert-info">
        ℹ️ Final verification is in progress. Once activated, your credit balance will be available in your Wallet screen.
    </div>
@else
    <div style="text-align:center;padding:24px 0 16px;">
        <div style="width:64px;height:64px;background:#e8f4fd;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:30px;margin-bottom:12px;">
            ✓
        </div>
        <p class="fw-section-title" style="margin-bottom:6px;">Virtual Loan Active</p>
        <p class="fw-section-sub">Your loan limit is active and ready for in-app spending.</p>
    </div>
@endif

<script>
(function() {
    // Auto-poll status check every 5 seconds if under review or waiting for final activation
    var isChecking = {{ ($isUnderReview || $isFeePaid) ? 'true' : 'false' }};
    if (isChecking) {
        var pollUrl = "{{ route('finance.virtual_loan.status_check', ['phone' => request('phone')]) }}";
        setInterval(function() {
            fetch(pollUrl)
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    }
                })
                .catch(function(err) {
                    console.log('Poll error', err);
                });
        }, 5000);
    }
})();
</script>

@endsection

@section('sticky-bottom')
@if($isApproved)
    <a href="{{ route('finance.virtual_loan.s03_fee_payment', ['phone' => request('phone')]) }}"
       class="fw-btn fw-btn-accent">
        Pay Processing Fee to Activate Limit →
    </a>
@elseif($isActive)
    <a href="{{ route('finance.virtual_loan.s05_dashboard', ['phone' => request('phone')]) }}"
       class="fw-btn fw-btn-primary">
        Go to Loan Dashboard →
    </a>
@else
    <a href="{{ route('finance.hub', ['phone' => request('phone')]) }}"
       class="fw-btn fw-btn-secondary">
        Return to Home
    </a>
@endif
@endsection

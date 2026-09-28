@extends('finance.layouts.base')
@section('title', 'Validation — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s15_proof_upload', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card fw-text-center" style="padding: 24px 16px;">
    <div style="width: 52px; height: 52px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 26px; color: #059669;">
        ✓
    </div>
    <h2 class="fw-heading" style="color: #059669; font-size: 20px; font-weight: 700; margin-bottom: 4px;">Application Submitted</h2>
    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
        Application No.: <strong style="color: #0f172a; font-family: monospace; font-size: 14px;">{{ $appNumber }}</strong>
    </p>
    
    <!-- Circular Running Clock / Countdown Timer -->
    <div style="position: relative; width: 170px; height: 170px; margin: 15px auto 20px;">
        <svg width="170" height="170" viewBox="0 0 170 170" style="transform: rotate(-90deg);">
            <!-- Background circle -->
            <circle cx="85" cy="85" r="72" stroke="#f1f5f9" stroke-width="10" fill="transparent" />
            <!-- Progress circle -->
            <circle id="timerProgressCircle" cx="85" cy="85" r="72" stroke="#2563eb" stroke-width="10" fill="transparent"
                stroke-dasharray="452.39" stroke-dashoffset="0" stroke-linecap="round"
                style="transition: stroke-dashoffset 1s linear;" />
        </svg>

        <!-- Center Clock Content -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div style="font-size: 20px; line-height: 1; margin-bottom: 4px;">⏱️</div>
            <div id="countdownTimer" style="font-size: 32px; font-weight: 800; font-family: 'SF Pro Display', monospace; color: #0f172a; letter-spacing: -0.5px;">
                --:--
            </div>
            <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">
                Validating
            </div>
        </div>
    </div>

    <!-- Estimated Window Badge -->
    <div style="display: inline-block; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; font-size: 12px; font-weight: 600; padding: 6px 14px; border-radius: 20px; margin-bottom: 20px;">
        Estimated Validation Window: Up to {{ max(1, ceil(($validationTimerSeconds ?? 180) / 60)) }} {{ max(1, ceil(($validationTimerSeconds ?? 180) / 60)) == 1 ? 'Minute' : 'Minutes' }}
    </div>

    <!-- Live Step Pipeline Flow -->
    <div style="text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin: 0 auto; max-width: 340px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 13px; font-weight: 600; color: #059669;">
            <span style="width: 22px; height: 22px; border-radius: 50%; background: #ecfdf5; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">✓</span>
            <span>Application Submitted</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 13px; font-weight: 700; color: #d97706;">
            <span class="pulse-dot" style="width: 22px; height: 22px; border-radius: 50%; background: #fef3c7; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">🟡</span>
            <span>Document / Status Validation</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 13px; font-weight: 500; color: #64748b;">
            <span style="width: 22px; height: 22px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">⏳</span>
            <span>Admin Underwriting Review</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 500; color: #64748b;">
            <span style="width: 22px; height: 22px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">📷</span>
            <span>Next Step: Agent & Borrower Selfie</span>
        </div>
    </div>

    <p style="margin-top: 20px; font-size: 12px; color: #64748b; line-height: 1.5;">
        Your submitted details are being verified automatically.<br>
        <strong>Please stay on this screen.</strong> It will automatically transition once verified.
    </p>
</div>

<style>
@keyframes pulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(0.95); opacity: 0.8; }
}
.pulse-dot {
    animation: pulse 1.8s infinite ease-in-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Total waiting time configured by Admin in seconds (default 180s)
    var totalSeconds = {{ (int) ($validationTimerSeconds ?? 180) }};
    if (totalSeconds <= 0) totalSeconds = 180;
    
    var remainingSeconds = totalSeconds;
    var timerDisplay = document.getElementById('countdownTimer');
    var progressCircle = document.getElementById('timerProgressCircle');
    var circleCircumference = 2 * Math.PI * 72; // ~452.39
    
    var nextStepUrl = "{{ route('finance.cash_loan.s17_selfie_agent', ['phone' => $phone]) }}";
    var pollUrl = "{{ route('finance.cash_loan.application_status_poll', ['phone' => $phone, 'current_step' => 's16', 'application_id' => $application->id ?? '']) }}";
    
    function updateClockDisplay() {
        var minutes = Math.floor(remainingSeconds / 60);
        var seconds = remainingSeconds % 60;
        timerDisplay.textContent = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        
        // Progress ring offset
        var fraction = (totalSeconds - remainingSeconds) / totalSeconds;
        var offset = circleCircumference * fraction;
        progressCircle.style.strokeDashoffset = offset;
    }
    
    updateClockDisplay();
    
    // 1. Clock Countdown Ticker
    var countdownInterval = setInterval(function () {
        remainingSeconds--;
        if (remainingSeconds <= 0) {
            clearInterval(countdownInterval);
            clearInterval(pollInterval);
            timerDisplay.textContent = '00:00';
            progressCircle.style.strokeDashoffset = circleCircumference;
            // Auto navigate to selfie verification when timer finishes
            window.location.href = nextStepUrl;
            return;
        }
        updateClockDisplay();
    }, 1000);
    
    // 2. Real-Time Admin Status Poller (every 2.5s)
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
                clearInterval(countdownInterval);
                clearInterval(pollInterval);
                window.location.href = data.redirect_url;
            }
        })
        .catch(function (err) {
            console.log('Status poll waiting...', err);
        });
    }, 2500);
});
</script>
@endsection

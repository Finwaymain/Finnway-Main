@extends('finance.layouts.base')
@section('title', 'Document Verification — Zero-CIBIL')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 4 of 6 · Document Verification')
@section('progress-pct', '66')
@section('progress', ' ')

@section('content')
<div class="fw-viewport-container">
    <div>
        {{-- Micro Header --}}
        <div style="text-align:center; margin-bottom:12px;">
            <span class="fw-badge-fintech" style="font-size:10px; margin-bottom:6px;">⚡ Automated Underwriting Engine</span>
            <p class="fw-section-title" style="font-size:20px; margin:4px 0 2px;">Identity &amp; Doc Verification</p>
            <p style="font-size:11px; color:var(--gray3); margin:0;">Please wait while our verification gateway inspects your KYC credentials</p>
        </div>

        {{-- Radar / Circular Verification Animation (Req 4: 2-minute countdown timer) --}}
        <div class="fw-bank-card" style="padding:18px 16px; margin-bottom:10px; text-align:center; background:linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
            <div style="position:relative; width:120px; height:120px; margin:0 auto 12px;">
                <svg viewBox="0 0 100 100" style="width:100%; height:100%; transform:rotate(-90deg);">
                    <circle cx="50" cy="50" r="44" stroke="#e2e8f0" stroke-width="7" fill="none" />
                    <circle id="verificationCircle" cx="50" cy="50" r="44" stroke="#00d09c" stroke-width="7" stroke-linecap="round" fill="none"
                            stroke-dasharray="276" stroke-dashoffset="0" style="transition: stroke-dashoffset 0.5s ease;" />
                </svg>
                <div style="position:absolute; top:0; left:0; right:0; bottom:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    <span id="countdownTimer" style="font-size:24px; font-weight:800; color:var(--navy); font-family:monospace; letter-spacing:-0.5px;">
                        02:00
                    </span>
                    <span style="font-size:9px; font-weight:700; text-transform:uppercase; color:#00a875; letter-spacing:0.5px;" id="verificationStatusText">
                        Scanning
                    </span>
                </div>
            </div>

            <div id="phaseTitle" style="font-size:13px; font-weight:700; color:var(--navy); margin-bottom:3px;">
                Stage 1: Scanning Document Authenticity
            </div>
            <div id="phaseSub" style="font-size:11px; color:var(--gray3); line-height:1.4;">
                Checking Aadhaar front/back and PAN image signatures...
            </div>
        </div>

        {{-- Verification Pipeline Checkmarks --}}
        <div class="fw-bank-card" style="padding:10px 14px; margin-bottom:8px;">
            <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:8px;">
                Underwriting Milestones
            </div>

            <div style="display:flex; flex-direction:column; gap:6px;">
                <div id="step1Row" style="display:flex; align-items:center; justify-content:space-between; font-size:11px; color:var(--text);">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span id="step1Icon" style="color:var(--blue2); font-weight:800;">●</span>
                        <span>1. Aadhaar ID &amp; PAN Card Scan</span>
                    </div>
                    <span id="step1Badge" class="fw-badge fw-badge-blue" style="font-size:9px;">Processing</span>
                </div>

                <div id="step2Row" style="display:flex; align-items:center; justify-content:space-between; font-size:11px; color:var(--gray3);">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span id="step2Icon">○</span>
                        <span>2. UIDAI / NSDL Gateway Matching</span>
                    </div>
                    <span id="step2Badge" class="fw-badge fw-badge-navy" style="font-size:9px;">Waiting</span>
                </div>

                <div id="step3Row" style="display:flex; align-items:center; justify-content:space-between; font-size:11px; color:var(--gray3);">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span id="step3Icon">○</span>
                        <span>3. Zero-CIBIL Policy Risk Assessment</span>
                    </div>
                    <span id="step3Badge" class="fw-badge fw-badge-navy" style="font-size:9px;">Waiting</span>
                </div>

                <div id="step4Row" style="display:flex; align-items:center; justify-content:space-between; font-size:11px; color:var(--gray3);">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span id="step4Icon">○</span>
                        <span>4. Sanction Pre-Approval &amp; Mandate</span>
                    </div>
                    <span id="step4Badge" class="fw-badge fw-badge-navy" style="font-size:9px;">Waiting</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Security Information Note --}}
    <div style="padding-top:4px;">
        <div style="background:#eff6ff; border-radius:8px; padding:8px 12px; display:flex; align-items:center; gap:8px; font-size:10px; color:#1e40af; border:1px solid #bfdbfe;">
            <span>🔒</span>
            <span>Do not press back or refresh. Your verification state is synchronized with Fiinway server.</span>
        </div>
    </div>
</div>

<form id="completeVerificationForm" method="POST" action="{{ route('finance.zero_cibil.complete_doc_verification') }}" style="display:none;">
    @csrf
    <input type="hidden" name="phone" value="{{ request('phone', $phone) }}">
</form>

<script>
(function() {
    var phone = "{{ request('phone', $phone) }}";
    var totalSeconds = 120; // 2 minutes as requested
    var storageKey = 'fiinway_zc_verification_start_' + phone;
    var startTime = parseInt(sessionStorage.getItem(storageKey), 10);
    var now = Math.floor(Date.now() / 1000);

    if (!startTime || (now - startTime) > totalSeconds * 2) {
        startTime = now;
        sessionStorage.setItem(storageKey, startTime);
    }

    var timerEl = document.getElementById('countdownTimer');
    var circle = document.getElementById('verificationCircle');
    var statusText = document.getElementById('verificationStatusText');
    var phaseTitle = document.getElementById('phaseTitle');
    var phaseSub = document.getElementById('phaseSub');
    var totalCircumference = 276;

    function formatTime(sec) {
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
    }

    function setStep(stepNum, status) {
        var row = document.getElementById('step' + stepNum + 'Row');
        var icon = document.getElementById('step' + stepNum + 'Icon');
        var badge = document.getElementById('step' + stepNum + 'Badge');
        if (!row || !icon || !badge) return;

        if (status === 'done') {
            row.style.color = '#0f1b2d';
            icon.textContent = '✓';
            icon.style.color = '#00a875';
            badge.className = 'fw-badge fw-badge-green';
            badge.textContent = 'Passed ✓';
        } else if (status === 'active') {
            row.style.color = '#0f1b2d';
            icon.textContent = '●';
            icon.style.color = '#1a5fa8';
            badge.className = 'fw-badge fw-badge-blue';
            badge.textContent = 'Verifying...';
        }
    }

    var tickInterval = setInterval(function() {
        var currentNow = Math.floor(Date.now() / 1000);
        var elapsed = currentNow - startTime;
        var remaining = Math.max(0, totalSeconds - elapsed);

        timerEl.textContent = formatTime(remaining);
        var progress = Math.min(1, elapsed / totalSeconds);
        circle.style.strokeDashoffset = totalCircumference * (1 - progress);

        // Milestone updates every 30 seconds
        if (elapsed < 30) {
            setStep(1, 'active');
            phaseTitle.textContent = "Stage 1: Scanning Document Authenticity";
            phaseSub.textContent = "Checking Aadhaar front/back and PAN image signatures...";
            statusText.textContent = "Scanning";
        } else if (elapsed < 60) {
            setStep(1, 'done');
            setStep(2, 'active');
            phaseTitle.textContent = "Stage 2: Verifying Identity with UIDAI & NSDL";
            phaseSub.textContent = "Confirming name and date of birth with national registries...";
            statusText.textContent = "Verifying";
        } else if (elapsed < 90) {
            setStep(1, 'done');
            setStep(2, 'done');
            setStep(3, 'active');
            phaseTitle.textContent = "Stage 3: Zero-CIBIL Risk Assessment";
            phaseSub.textContent = "Applying zero-interest credit policy and setting daily limits...";
            statusText.textContent = "Assessing";
        } else if (elapsed < 120) {
            setStep(1, 'done');
            setStep(2, 'done');
            setStep(3, 'done');
            setStep(4, 'active');
            phaseTitle.textContent = "Stage 4: Credit Limit Pre-Approved!";
            phaseSub.textContent = "Generating wallet activation token & fee breakdown...";
            statusText.textContent = "Approved ✓";
        }

        if (remaining <= 0) {
            clearInterval(tickInterval);
            setStep(1, 'done');
            setStep(2, 'done');
            setStep(3, 'done');
            setStep(4, 'done');
            statusText.textContent = "Approved ✓";
            sessionStorage.removeItem(storageKey);

            // Display Pre-Fee Approval Notification (User Req 3)
            var appModal = document.getElementById('approvalModal');
            if (appModal) {
                appModal.style.display = 'flex';
                setTimeout(function() {
                    document.getElementById('completeVerificationForm').submit();
                }, 3500);
            } else {
                document.getElementById('completeVerificationForm').submit();
            }
        }
    }, 1000);
})();
</script>

{{-- Pre-Fee Approval Congratulation Modal (User Req 3) --}}
<div id="approvalModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,27,45,0.85); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#ffffff; border-radius:16px; padding:24px 20px; max-width:340px; width:100%; text-align:center; box-shadow:0 20px 40px rgba(0,0,0,0.4);">
        <div style="width:60px; height:60px; background:#ecfdf5; color:#00a875; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:30px; margin-bottom:12px; border:2px solid #00a875;">
            ✓
        </div>
        <h3 style="font-size:18px; font-weight:800; color:var(--navy); margin-bottom:4px;">Congratulations!</h3>
        <p style="font-size:15px; font-weight:800; color:#00a875; margin-bottom:6px;">Your Credit Limit is Approved!</p>
        <p style="font-size:12px; color:var(--gray3); margin-bottom:16px;">
            Your pre-approved limit of <strong>₹{{ number_format($amount) }}</strong> has successfully passed underwriting verification.
        </p>
        <button type="button" onclick="document.getElementById('completeVerificationForm').submit()" class="fw-btn fw-btn-primary" style="min-height:48px; font-size:14px; font-weight:700; border-radius:10px;">
            Proceed to Activation Fee →
        </button>
    </div>
</div>
@endsection

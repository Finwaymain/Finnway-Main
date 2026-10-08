@extends('finance.layouts.base')
@section('title', 'KYC Verification — Zero-CIBIL')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 2 of 6 · KYC & Identity')
@section('progress-pct', '33')
@section('progress', ' ')

@section('back')
<a href="{{ route('finance.zero_cibil.s01_intro', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-viewport-container">
    <div>
        {{-- Micro Header --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <div>
                <p class="fw-section-title" style="font-size:18px; margin-bottom:2px;">KYC &amp; Contact Details</p>
                <p style="font-size:11px; color:var(--gray3); margin:0;">Zero bureau check · Instant Aadhaar &amp; PAN match</p>
            </div>
            <span class="fw-badge-fintech">🔒 Vault Protected</span>
        </div>

        @if(session('error'))
            <div class="fw-alert fw-alert-error" style="padding:8px 12px; margin-bottom:8px; font-size:12px;">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <div id="clientErrorBox" class="fw-alert fw-alert-error" style="display:none; padding:8px 12px; margin-bottom:8px; font-size:12px;">
        </div>

        @php
            $hasAadhaarFront = !empty($uploadedDocs['aadhaar_front']);
            $hasAadhaarBack  = !empty($uploadedDocs['aadhaar_back']);
            $hasPanCard      = !empty($uploadedDocs['pan_card']);

            $savedDetails = is_array($application->applicant_details ?? null) 
                ? $application->applicant_details 
                : (json_decode($application->applicant_details ?? '[]', true) ?: []);

            $altPhoneVal = $customer->alternate_phone ?? ($savedDetails['alternate_phone'] ?? '');
            $waPhoneVal  = $customer->whatsapp_phone ?? ($savedDetails['whatsapp_phone'] ?? '');
            $emailVal    = $customer->email ?? ($savedDetails['email'] ?? '');
            $nameVal     = $customer->name ?? ($savedDetails['name'] ?? '');
            $panVal      = $customer->pan ?? ($savedDetails['pan'] ?? '');
            $aadhaarVal  = $customer->aadhaar ?? ($savedDetails['aadhaar'] ?? '');
        @endphp

        <form id="kycForm" method="POST" action="{{ route('finance.zero_cibil.save_kyc') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="primary_phone" name="phone" value="{{ request('phone', $phone) }}">

            {{-- 1. Identity Inputs (Compact Grid) --}}
            <div class="fw-bank-card" style="margin-bottom:8px; padding:10px 12px;">
                <div style="display:grid; grid-template-columns:1fr; gap:6px;">
                    <div>
                        <label class="fw-label" style="font-size:10px; margin-bottom:2px;">Full Name (As per Aadhaar)</label>
                        <input type="text" name="applicant_name" class="fw-input" required
                               style="padding:8px 10px; font-size:13px;"
                               value="{{ $nameVal }}" placeholder="e.g. Rahul Sharma">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:6px;">
                    <div>
                        <label class="fw-label" style="font-size:10px; margin-bottom:2px;">PAN Number</label>
                        <input type="text" name="pan_number" class="fw-input" maxlength="10" required
                               style="padding:8px 10px; font-size:13px; text-transform:uppercase; letter-spacing:0.5px;"
                               value="{{ $panVal }}" placeholder="ABCDE1234F">
                    </div>
                    <div>
                        <label class="fw-label" style="font-size:10px; margin-bottom:2px;">Aadhaar Number</label>
                        <input type="text" name="aadhaar_number" class="fw-input" maxlength="12" required
                               style="padding:8px 10px; font-size:13px; letter-spacing:0.5px;"
                               value="{{ $aadhaarVal }}" placeholder="12-digit number">
                    </div>
                </div>
            </div>

            {{-- 2. Contact Profile Isolation (Req 3: Alternate, WhatsApp, Email) --}}
            <div class="fw-bank-card" style="margin-bottom:8px; padding:10px 12px; border-left:3px solid var(--blue2);">
                <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:4px; display:flex; justify-content:space-between;">
                    <span>Contact Verification</span>
                    <span style="font-size:10px; color:var(--gray3); font-weight:normal;">Must be distinct numbers</span>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:6px;">
                    <div>
                        <label class="fw-label" style="font-size:10px; margin-bottom:2px;">Alternate Phone</label>
                        <input type="tel" id="alternate_phone" name="alternate_phone" class="fw-input" maxlength="10" required
                               style="padding:8px 10px; font-size:13px;"
                               value="{{ $altPhoneVal }}" placeholder="10-digit number">
                    </div>
                    <div>
                        <label class="fw-label" style="font-size:10px; margin-bottom:2px;">WhatsApp Phone</label>
                        <input type="tel" id="whatsapp_phone" name="whatsapp_phone" class="fw-input" maxlength="10" required
                               style="padding:8px 10px; font-size:13px;"
                               value="{{ $waPhoneVal }}" placeholder="10-digit number">
                    </div>
                </div>

                <div>
                    <label class="fw-label" style="font-size:10px; margin-bottom:2px;">Email ID</label>
                    <input type="email" id="email" name="email" class="fw-input" required
                           style="padding:8px 10px; font-size:13px;"
                           value="{{ $emailVal }}" placeholder="e.g. applicant@domain.com">
                </div>
            </div>

            {{-- 3. Document Vault & Reflection (Req 2: Show Uploaded Across All Screens) --}}
            <div class="fw-bank-card" style="margin-bottom:8px; padding:10px 12px;">
                <div style="font-size:11px; font-weight:700; color:var(--navy); margin-bottom:6px;">
                    KYC Document Vault
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">
                    {{-- Doc 1: Aadhaar Front --}}
                    <div style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 6px; text-align:center; background:#f8fafc;">
                        <div style="font-size:14px; margin-bottom:2px;">🪪</div>
                        <div style="font-size:10px; font-weight:700; color:var(--navy);">Aadhaar Front</div>
                        @if($hasAadhaarFront)
                            <div style="font-size:9px; color:#00a875; font-weight:700; margin-top:3px;">✓ Uploaded</div>
                            <label style="font-size:9px; color:var(--blue); cursor:pointer; text-decoration:underline; display:block; margin-top:2px;">
                                Replace
                                <input type="file" name="aadhaar_front" accept="image/*,.pdf" style="display:none;" onchange="markSelected(this, 'badge-af')">
                            </label>
                            <span id="badge-af" style="display:none; font-size:8px; color:var(--green);">Selected</span>
                        @else
                            <label style="font-size:10px; color:var(--blue); cursor:pointer; display:block; margin-top:3px; font-weight:600;">
                                📷 Tap to Add
                                <input type="file" name="aadhaar_front" accept="image/*,.pdf" required style="display:none;" onchange="markSelected(this, 'badge-af')">
                            </label>
                            <span id="badge-af" style="display:none; font-size:9px; color:var(--green); font-weight:700;">✓ Selected</span>
                        @endif
                    </div>

                    {{-- Doc 2: Aadhaar Back --}}
                    <div style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 6px; text-align:center; background:#f8fafc;">
                        <div style="font-size:14px; margin-bottom:2px;">🪪</div>
                        <div style="font-size:10px; font-weight:700; color:var(--navy);">Aadhaar Back</div>
                        @if($hasAadhaarBack)
                            <div style="font-size:9px; color:#00a875; font-weight:700; margin-top:3px;">✓ Uploaded</div>
                            <label style="font-size:9px; color:var(--blue); cursor:pointer; text-decoration:underline; display:block; margin-top:2px;">
                                Replace
                                <input type="file" name="aadhaar_back" accept="image/*,.pdf" style="display:none;" onchange="markSelected(this, 'badge-ab')">
                            </label>
                            <span id="badge-ab" style="display:none; font-size:8px; color:var(--green);">Selected</span>
                        @else
                            <label style="font-size:10px; color:var(--blue); cursor:pointer; display:block; margin-top:3px; font-weight:600;">
                                📷 Tap to Add
                                <input type="file" name="aadhaar_back" accept="image/*,.pdf" required style="display:none;" onchange="markSelected(this, 'badge-ab')">
                            </label>
                            <span id="badge-ab" style="display:none; font-size:9px; color:var(--green); font-weight:700;">✓ Selected</span>
                        @endif
                    </div>

                    {{-- Doc 3: PAN Card --}}
                    <div style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 6px; text-align:center; background:#f8fafc;">
                        <div style="font-size:14px; margin-bottom:2px;">💳</div>
                        <div style="font-size:10px; font-weight:700; color:var(--navy);">PAN Card</div>
                        @if($hasPanCard)
                            <div style="font-size:9px; color:#00a875; font-weight:700; margin-top:3px;">✓ Uploaded</div>
                            <label style="font-size:9px; color:var(--blue); cursor:pointer; text-decoration:underline; display:block; margin-top:2px;">
                                Replace
                                <input type="file" name="pan_card" accept="image/*,.pdf" style="display:none;" onchange="markSelected(this, 'badge-pan')">
                            </label>
                            <span id="badge-pan" style="display:none; font-size:8px; color:var(--green);">Selected</span>
                        @else
                            <label style="font-size:10px; color:var(--blue); cursor:pointer; display:block; margin-top:3px; font-weight:600;">
                                📷 Tap to Add
                                <input type="file" name="pan_card" accept="image/*,.pdf" required style="display:none;" onchange="markSelected(this, 'badge-pan')">
                            </label>
                            <span id="badge-pan" style="display:none; font-size:9px; color:var(--green); font-weight:700;">✓ Selected</span>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Bottom Action Area (Fixed height and safe bottom spacing) --}}
    <div style="padding:16px 0 32px; margin-bottom:20px;">
        <button type="button" onclick="validateAndSubmitKyc()" class="fw-btn fw-btn-primary" style="min-height:52px; height:52px; font-size:15px; font-weight:800; border-radius:12px; box-shadow:0 4px 14px rgba(26,95,168,0.3);">
            Save &amp; Continue to Amount Selection →
        </button>
    </div>
</div>

<script>
function markSelected(input, badgeId) {
    if (input.files && input.files[0]) {
        var el = document.getElementById(badgeId);
        if (el) {
            el.style.display = 'block';
            el.textContent = '✓ ' + input.files[0].name.substring(0, 10) + '...';
        }
    }
}

function cleanDigits(val) {
    if (!val) return '';
    var d = String(val).replace(/\D/g, '');
    return d.length >= 10 ? d.slice(-10) : d;
}

function validateAndSubmitKyc() {
    var form = document.getElementById('kycForm');
    var errBox = document.getElementById('clientErrorBox');
    errBox.style.display = 'none';
    errBox.textContent = '';

    // HTML5 native validity check
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    var v = window.FiinwayValidator;
    var nameInp = form.querySelector('input[name="applicant_name"]');
    var panInp = form.querySelector('input[name="pan_number"]');
    var aadhaarInp = form.querySelector('input[name="aadhaar_number"]');
    var emailInp = document.getElementById('email');
    var altInp = document.getElementById('alternate_phone');
    var waInp = document.getElementById('whatsapp_phone');

    var primary = v.cleanDigits(document.getElementById('primary_phone').value);
    var alt = v.cleanDigits(altInp ? altInp.value : '');
    var wa = v.cleanDigits(waInp ? waInp.value : '');
    var aadhaar = v.cleanDigits(aadhaarInp ? aadhaarInp.value : '');

    if (nameInp && !v.isValidName(nameInp.value)) {
        errBox.textContent = 'Full Name must be at least 3 characters and contain letters only.';
        errBox.style.display = 'block';
        nameInp.focus();
        return;
    }

    if (panInp && !v.isValidPan(panInp.value)) {
        errBox.textContent = 'PAN Number must be a valid 10-character code (e.g. ABCDE1234F).';
        errBox.style.display = 'block';
        panInp.focus();
        return;
    }

    if (!aadhaar || aadhaar.length !== 12 || /^(\d)\1{11}$/.test(aadhaar)) {
        errBox.textContent = 'Aadhaar Number must be exactly 12 numeric digits.';
        errBox.style.display = 'block';
        if (aadhaarInp) aadhaarInp.focus();
        return;
    }

    if (emailInp && !v.isValidEmail(emailInp.value)) {
        errBox.textContent = 'Please enter a valid email address (e.g. name@domain.com).';
        errBox.style.display = 'block';
        emailInp.focus();
        return;
    }

    // Strict phone validation
    if (alt.length !== 10 || !/^[6-9]\d{9}$/.test(alt)) {
        errBox.textContent = 'Alternate phone must be a valid 10-digit number starting with 6, 7, 8, or 9.';
        errBox.style.display = 'block';
        if (altInp) altInp.focus();
        return;
    }
    if (wa.length !== 10 || !/^[6-9]\d{9}$/.test(wa)) {
        errBox.textContent = 'WhatsApp phone must be a valid 10-digit number starting with 6, 7, 8, or 9.';
        errBox.style.display = 'block';
        if (waInp) waInp.focus();
        return;
    }
    if (alt === primary) {
        errBox.textContent = 'Alternate number cannot be identical to your Primary registered number (' + primary + ').';
        errBox.style.display = 'block';
        if (altInp) altInp.focus();
        return;
    }
    if (wa === primary) {
        errBox.textContent = 'WhatsApp number cannot be identical to your Primary registered number (' + primary + ').';
        errBox.style.display = 'block';
        if (waInp) waInp.focus();
        return;
    }
    if (wa === alt) {
        errBox.textContent = 'WhatsApp number and Alternate number must be different from each other.';
        errBox.style.display = 'block';
        if (waInp) waInp.focus();
        return;
    }

    // Trigger 10s Stage Transition Loading Modal (Req 1 & 5)
    window.showBankingStageLoader(
        "Encrypting KYC Vault",
        "Hashing Aadhaar & PAN credentials with SHA-256...",
        10,
        function() {
            form.submit();
        }
    );
}
</script>
@endsection

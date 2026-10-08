@extends('finance.layouts.base')
@section('title', 'Application Status — Zero-CIBIL')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 6 of 6 · Approval & Review')
@section('progress-pct', '100')
@section('progress', ' ')

@section('content')
<div class="fw-viewport-container" style="padding-bottom:88px;">
    <div>
        @php
            $appStatus = $application->application_status ?? 'UNDERWRITING';
            $isApproved = ($application && in_array($appStatus, ['LOAN_APPROVED', 'DISBURSED', 'ACTIVE']))
                || (isset($wallet) && $wallet->status === 'active');
            $isRejected = ($application && $appStatus === 'REJECTED');
            $isDocsRequested = ($appStatus === 'ADDITIONAL_DOCS_REQUESTED') || (!empty($hasDocRequest));
            $isDocsResubmitted = ($appStatus === 'DOCS_RESUBMITTED');
            $appNumber = $application->application_number ?? ('FIIN-ZC-' . ($application->id ?? time()));

            $statusBadgeClass = 'fw-badge-blue';
            $statusLabel = 'UNDER PROCESS';
            if ($isApproved) {
                $statusBadgeClass = 'fw-badge-green';
                $statusLabel = 'SANCTIONED';
            } elseif ($isRejected) {
                $statusBadgeClass = 'fw-badge-red';
                $statusLabel = 'REJECTED';
            } elseif ($appStatus === 'ADDITIONAL_DOCS_REQUESTED') {
                $statusBadgeClass = 'fw-badge-amber';
                $statusLabel = 'ACTION REQUIRED';
            } elseif ($appStatus === 'DOCS_RESUBMITTED') {
                $statusBadgeClass = 'fw-badge-blue';
                $statusLabel = 'DOCS RESUBMITTED';
            } elseif ($appStatus === 'FEE_PAID') {
                $statusBadgeClass = 'fw-badge-blue';
                $statusLabel = 'DISBURSAL QUEUE';
            }
        @endphp

        {{-- Status Hero Card --}}
        @if($isApproved)
            <div class="fw-bank-hero" style="text-align:center; padding:18px 14px; margin-bottom:12px; background:linear-gradient(135deg, #064e3b 0%, #065f46 100%);">
                <div style="font-size:32px; margin-bottom:4px;">🎉</div>
                <div style="font-size:18px; font-weight:800; color:#ffffff; margin-bottom:2px;">Credit Limit Approved &amp; Sanctioned!</div>
                <div style="font-size:12px; color:#a7f3d0;">Your zero-interest daily credit card is activated and ready to use.</div>
            </div>
        @elseif($isRejected)
            <div class="fw-bank-hero" style="text-align:center; padding:18px 14px; margin-bottom:12px; background:linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);">
                <div style="font-size:32px; margin-bottom:4px;">⚠️</div>
                <div style="font-size:18px; font-weight:800; color:#ffffff; margin-bottom:2px;">Application Under Review / Rejected</div>
                <div style="font-size:12px; color:#fca5a5;">{{ $application->rejection_reason ?? 'Your application could not be approved at this time.' }}</div>
            </div>
        @elseif($appStatus === 'ADDITIONAL_DOCS_REQUESTED')
            <div class="fw-bank-hero" style="text-align:center; padding:18px 14px; margin-bottom:12px; background:linear-gradient(135deg, #78350f 0%, #b45309 100%); border:1px solid rgba(255,255,255,0.12);">
                <div style="font-size:32px; margin-bottom:6px;">⚠️</div>
                <div style="font-size:17px; font-weight:800; color:#ffffff; margin-bottom:6px; line-height:1.35;">
                    Additional Documents Requested
                </div>
                <div style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.15); border-radius:20px; padding:4px 12px; font-size:11px; color:#fef3c7;">
                    <span>📄</span> Action Required: Please re-upload documents below
                </div>
            </div>
        @elseif($isDocsResubmitted)
            <div class="fw-bank-hero" style="text-align:center; padding:18px 14px; margin-bottom:12px; background:linear-gradient(135deg, #0f1b2d 0%, #1e40af 100%); border:1px solid rgba(255,255,255,0.12);">
                <div style="font-size:32px; margin-bottom:6px;">📩</div>
                <div style="font-size:17px; font-weight:800; color:#ffffff; margin-bottom:6px; line-height:1.35;">
                    Documents Resubmitted — In Review
                </div>
                <div style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.15); border-radius:20px; padding:4px 12px; font-size:11px; color:#93c5fd;">
                    <span>⏱</span> Review in progress by administration
                </div>
            </div>
        @else
            <div class="fw-bank-hero" style="text-align:center; padding:18px 14px; margin-bottom:12px; background:linear-gradient(135deg, #0f1b2d 0%, #1e3a8a 100%); border:1px solid rgba(255,255,255,0.12);">
                <div style="font-size:32px; margin-bottom:6px;">⏳</div>
                <div style="font-size:17px; font-weight:800; color:#ffffff; margin-bottom:6px; line-height:1.35;">
                    Your Disbursal is under process it will active In upto 4 hours
                </div>
                <div style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.1); border-radius:20px; padding:4px 12px; font-size:11px; color:#93c5fd;">
                    <span>⏱</span> Estimated Time: Within 4 Hours
                </div>
            </div>
        @endif

        {{-- Auto Generated Application Number Banner --}}
        <div class="fw-bank-card" style="padding:14px 16px; margin-bottom:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--gray3);">Application Number</div>
                    <div style="font-size:16px; font-weight:800; font-family:monospace; color:var(--navy); margin-top:2px;">
                        {{ $appNumber }}
                    </div>
                </div>
                <span class="fw-badge {{ $statusBadgeClass }}" style="font-size:11px; font-weight:700; padding:4px 8px; {{ $appStatus === 'ADDITIONAL_DOCS_REQUESTED' ? 'background:#fef3c7; color:#b45309; border:1px solid #f59e0b;' : '' }}">
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        {{-- Flash Success Message --}}
        @if(session('success'))
            <div class="fw-bank-card" style="padding:12px 14px; margin-bottom:12px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:12px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:18px;">✓</span>
                    <span style="font-size:12px; font-weight:700; color:#065f46;">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- Notice when documents were resubmitted and no further request pending --}}
        @if($isDocsResubmitted && !$hasDocRequest)
            <div class="fw-bank-card" style="padding:14px; margin-bottom:12px; border:1.5px solid #93c5fd; background:#eff6ff; border-radius:12px;">
                <div style="display:flex; align-items:flex-start; gap:10px;">
                    <span style="font-size:22px;">📩</span>
                    <div>
                        <div style="font-size:13px; font-weight:800; color:#1e40af;">Documents Resubmitted Successfully</div>
                        <div style="font-size:11px; color:#1d4ed8; margin-top:2px; line-height:1.4;">
                            Your uploaded documents have been received. Our administration team is reviewing the newly attached files. Once verified, your loan will be approved and activated.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Additional Document Request by Admin --}}
        @if($hasDocRequest)
            <div class="fw-bank-card" style="padding:14px; margin-bottom:12px; border:1.5px solid #f59e0b; background:#fffbeb; border-radius:12px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span style="font-size:20px;">📄</span>
                    <div>
                        <div style="font-size:13px; font-weight:800; color:#92400e;">Additional Documents Requested by Admin</div>
                        <div style="font-size:11px; color:#b45309;">Please submit the following required documents to approve disbursal.</div>
                    </div>
                </div>
                @if($docRequestRemark)
                    <div style="background:#fef3c7; padding:8px 10px; border-radius:8px; font-size:11px; color:#78350f; margin-bottom:10px;">
                        <strong>Admin Note:</strong> {{ $docRequestRemark }}
                    </div>
                @endif
                <form id="additionalDocsForm" action="{{ route('finance.zero_cibil.additional_docs_submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="phone" value="{{ request('phone', $phone ?? '') }}">
                    <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:12px;">
                        @foreach($requestedDocsList as $idx => $docItem)
                            <div style="background:#ffffff; border:1px solid #fed7aa; border-radius:8px; padding:10px;">
                                <label style="font-size:11px; font-weight:700; color:#374151; display:block; margin-bottom:2px;">
                                    {{ $docItem['label'] }} <span style="color:#ef4444;">*</span>
                                </label>
                                @if(!empty($docItem['remark']))
                                    <div style="font-size:10px; color:#b45309; margin-bottom:6px;">
                                        ⚠️ <strong>Reason:</strong> {{ $docItem['remark'] }}
                                    </div>
                                @endif
                                <input type="hidden" name="doc_names[]" value="{{ $docItem['label'] }}">
                                <input type="hidden" name="doc_types[]" value="{{ $docItem['doc_type'] ?? '' }}">
                                <input type="hidden" name="doc_ids[]" value="{{ $docItem['doc_id'] ?? '' }}">
                                <input type="file" name="doc_files[]" class="fw-input" required accept="image/*,application/pdf" style="font-size:11px; padding:6px 8px;">
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="fw-btn fw-btn-primary" style="min-height:48px; font-size:13px; font-weight:800;">
                        Upload &amp; Submit Documents →
                    </button>
                </form>
            </div>
        @endif

        {{-- Application Summary --}}
        <div class="fw-bank-card" style="padding:12px 14px; margin-bottom:12px;">
            <div style="font-size:12px; font-weight:700; color:var(--navy); margin-bottom:8px;">Application Details</div>

            <div class="fw-info-row" style="padding:6px 0; font-size:12px;">
                <span class="fw-info-label">Applied Credit Limit</span>
                <span class="fw-info-value" style="font-size:14px; font-weight:800; color:var(--navy);">₹{{ number_format($amount) }}</span>
            </div>
            <div class="fw-info-row" style="padding:6px 0; font-size:12px;">
                <span class="fw-info-label">Activation Fee Status</span>
                <span class="fw-badge fw-badge-green" style="font-size:11px; font-weight:700;">✓ Paid &amp; Verified</span>
            </div>
            <div class="fw-info-row" style="padding:6px 0; font-size:12px;">
                <span class="fw-info-label">Disbursal Window</span>
                <span class="fw-info-value" style="font-size:12px; font-weight:700; color:#0284c7;">Active In Upto 4 Hours</span>
            </div>
            <div class="fw-info-row" style="padding:6px 0 0; font-size:12px;">
                <span class="fw-info-label">Underwriting Status</span>
                @if($isApproved)
                    <span class="fw-badge fw-badge-green" style="font-size:11px; font-weight:700;">✓ Approved by Admin</span>
                @elseif($isRejected)
                    <span class="fw-badge fw-badge-red" style="font-size:11px; font-weight:700;">Rejected</span>
                @elseif($appStatus === 'ADDITIONAL_DOCS_REQUESTED')
                    <span class="fw-badge" style="font-size:11px; font-weight:700; background:#fef3c7; color:#b45309; border:1px solid #f59e0b;">⚠️ Additional Docs Required</span>
                @elseif($appStatus === 'DOCS_RESUBMITTED')
                    <span class="fw-badge" style="font-size:11px; font-weight:700; background:#eff6ff; color:#1d4ed8; border:1px solid #93c5fd;">✓ Docs Resubmitted (In Review)</span>
                @else
                    <span class="fw-badge fw-badge-blue" style="font-size:11px; font-weight:700;">⏳ Desk Verification Active</span>
                @endif
            </div>
        </div>

        {{-- Verification Pipeline --}}
        <div class="fw-bank-card" style="padding:12px 14px; margin-bottom:14px;">
            <div style="font-size:12px; font-weight:700; color:var(--navy); margin-bottom:8px;">
                Pipeline Progression
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; font-size:12px;">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <span>✓ 1. KYC Details Submitted</span>
                    <span style="color:#00a875; font-weight:700;">Verified</span>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <span>✓ 2. Activation Fee Received</span>
                    <span style="color:#00a875; font-weight:700;">Verified</span>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <span>{{ $isApproved ? '✓' : '●' }} 3. Admin Document Verification</span>
                    @if($isApproved)
                        <span style="color:#00a875; font-weight:700;">Approved</span>
                    @elseif($appStatus === 'ADDITIONAL_DOCS_REQUESTED')
                        <span style="color:#b45309; font-weight:700;">Action Required</span>
                    @elseif($appStatus === 'DOCS_RESUBMITTED')
                        <span style="color:#2563eb; font-weight:700;">Docs Resubmitted</span>
                    @else
                        <span style="color:var(--blue); font-weight:700;">In Progress</span>
                    @endif
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; color:{{ $isApproved ? '#0f1b2d' : 'var(--gray3)' }};">
                    <span>{{ $isApproved ? '✓' : '○' }} 4. Disbursal &amp; Card Active</span>
                    <span style="{{ $isApproved ? 'color:#00a875; font-weight:700;' : '' }}">
                        {{ $isApproved ? 'Active' : 'Within 4 Hours' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Action --}}
    <div style="padding-top:8px;">
        @if($isApproved)
            <a href="{{ route('finance.zero_cibil.s06_wallet_active', ['phone' => request('phone', $phone ?? '')]) }}"
               class="fw-btn fw-btn-green" style="min-height:52px; font-size:15px; font-weight:800; border-radius:12px; display:flex; align-items:center; justify-content:center;">
                Access Active Credit Card &amp; Wallet →
            </a>
        @else
            <div style="display:flex; gap:10px;">
                <a href="{{ route('finance.zero_cibil.s05_pending', ['phone' => request('phone', $phone ?? '')]) }}"
                   class="fw-btn fw-btn-primary" style="min-height:48px; font-size:13px; font-weight:800; flex:1; display:flex; align-items:center; justify-content:center; border-radius:10px;">
                    🔄 Refresh Status
                </a>
                <a href="{{ route('finance.hub', ['phone' => request('phone', $phone ?? '')]) }}"
                   class="fw-btn fw-btn-outline" style="min-height:48px; font-size:13px; font-weight:800; flex:1; display:flex; align-items:center; justify-content:center; border-radius:10px;">
                    Financial Hub
                </a>
            </div>
        @endif
    </div>
</div>

<script>
// Prevent backward navigation once in disbursal queue (User Req 2)
history.pushState(null, null, location.href);
window.onpopstate = function () {
    history.go(1);
};

(function() {
    var currentStatus = "{{ $appStatus }}";
    var isFormDirty = false;

    // Track user selecting files or interacting with file inputs
    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        input.addEventListener('change', function() {
            if (input.files && input.files.length > 0) {
                isFormDirty = true;
            }
        });
    });

    @if(!$isApproved && !$isRejected)
    // Intelligent polling every 10 seconds - never interrupts user while uploading files
    setInterval(function() {
        if (isFormDirty) {
            return; // Never reload when user has chosen a file to upload!
        }

        // Also check if any file input currently has files selected
        var filesSelected = false;
        document.querySelectorAll('input[type="file"]').forEach(function(input) {
            if (input.files && input.files.length > 0) {
                filesSelected = true;
            }
        });
        if (filesSelected) return;

        fetch("{{ route('finance.zero_cibil.status_check', ['phone' => request('phone', $phone ?? '')]) }}", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data) return;

            if (data.is_approved && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else if (data.status && data.status !== currentStatus) {
                // Status changed on server (e.g., admin requested docs, or docs were accepted)
                window.location.reload();
            }
        })
        .catch(function() {});
    }, 10000);
    @endif
})();
</script>
@endsection

@extends('finance.layouts.base')
@section('title', 'KYC Upload — Zero-CIBIL')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 2 of 6')
@section('progress-pct', '33')
@section('progress', ' ')

@section('back')
<a href="{{ route('finance.zero_cibil.s01_intro', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')

<p class="fw-section-title">KYC Verification</p>
<p class="fw-section-sub">Upload your Aadhaar &amp; PAN card to activate your zero-interest credit line.</p>

<div class="fw-alert fw-alert-info">
    🔒 Bank statements or passbooks are <strong>never required</strong>. Only Aadhaar &amp; PAN are needed.
</div>

<form id="kycForm" method="POST" action="{{ route('finance.zero_cibil.save_kyc') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="phone" value="{{ request('phone') }}">

    {{-- Applicant Personal Information --}}
    <div class="fw-card">
        <p class="fw-card-title">Applicant Identity</p>
        <div class="fw-input-group">
            <label class="fw-label">Full Name</label>
            <input type="text" name="applicant_name" class="fw-input" required
                   value="{{ $customer->name ?? '' }}" placeholder="Full Name as on Aadhaar">
        </div>
        <div class="fw-input-group">
            <label class="fw-label">PAN Number</label>
            <input type="text" name="pan_number" class="fw-input" maxlength="10" required
                   style="text-transform:uppercase;"
                   value="{{ $customer->pan ?? '' }}" placeholder="ABCDE1234F">
        </div>
        <div class="fw-input-group" style="margin-bottom:0;">
            <label class="fw-label">Aadhaar Number</label>
            <input type="text" name="aadhaar_number" class="fw-input" maxlength="12" required
                   value="{{ $customer->aadhaar ?? '' }}" placeholder="12-digit Aadhaar Number">
        </div>
    </div>

    {{-- Doc 1: Aadhaar Front --}}
    <div class="fw-card">
        <div class="fw-doc-item" style="border:none; padding:0; margin-bottom:8px;">
            <div class="fw-doc-item-info">
                <div class="fw-doc-icon">🪪</div>
                <div>
                    <div class="fw-doc-name">Aadhaar Card — Front</div>
                    <div class="fw-doc-status">Clear photo of front side</div>
                </div>
            </div>
            <span class="fw-badge fw-badge-amber" id="badge-aadhaar-front">Pending</span>
        </div>
        <label class="fw-upload-box" id="box-aadhaar-front">
            <div id="preview-aadhaar-front" style="display:none;"></div>
            <div id="placeholder-aadhaar-front">
                <div class="fw-upload-icon">📷</div>
                <strong>Tap to Upload Front Side</strong>
                <p>JPG, PNG, PDF · Max 5 MB</p>
            </div>
            <input type="file" name="aadhaar_front" accept="image/*,.pdf" capture="environment" required
                   onchange="handlePreview('box-aadhaar-front', 'badge-aadhaar-front', 'preview-aadhaar-front', 'placeholder-aadhaar-front', this)">
        </label>
    </div>

    {{-- Doc 2: Aadhaar Back --}}
    <div class="fw-card">
        <div class="fw-doc-item" style="border:none; padding:0; margin-bottom:8px;">
            <div class="fw-doc-item-info">
                <div class="fw-doc-icon">🪪</div>
                <div>
                    <div class="fw-doc-name">Aadhaar Card — Back</div>
                    <div class="fw-doc-status">Clear photo with address</div>
                </div>
            </div>
            <span class="fw-badge fw-badge-amber" id="badge-aadhaar-back">Pending</span>
        </div>
        <label class="fw-upload-box" id="box-aadhaar-back">
            <div id="preview-aadhaar-back" style="display:none;"></div>
            <div id="placeholder-aadhaar-back">
                <div class="fw-upload-icon">📷</div>
                <strong>Tap to Upload Back Side</strong>
                <p>JPG, PNG, PDF · Max 5 MB</p>
            </div>
            <input type="file" name="aadhaar_back" accept="image/*,.pdf" capture="environment" required
                   onchange="handlePreview('box-aadhaar-back', 'badge-aadhaar-back', 'preview-aadhaar-back', 'placeholder-aadhaar-back', this)">
        </label>
    </div>

    {{-- Doc 3: PAN Card --}}
    <div class="fw-card">
        <div class="fw-doc-item" style="border:none; padding:0; margin-bottom:8px;">
            <div class="fw-doc-item-info">
                <div class="fw-doc-icon">💳</div>
                <div>
                    <div class="fw-doc-name">PAN Card</div>
                    <div class="fw-doc-status">Front photo of PAN card</div>
                </div>
            </div>
            <span class="fw-badge fw-badge-amber" id="badge-pan">Pending</span>
        </div>
        <label class="fw-upload-box" id="box-pan">
            <div id="preview-pan" style="display:none;"></div>
            <div id="placeholder-pan">
                <div class="fw-upload-icon">📷</div>
                <strong>Tap to Upload PAN Card</strong>
                <p>JPG, PNG, PDF · Max 5 MB</p>
            </div>
            <input type="file" name="pan_card" accept="image/*,.pdf" capture="environment" required
                   onchange="handlePreview('box-pan', 'badge-pan', 'preview-pan', 'placeholder-pan', this)">
        </label>
    </div>

    <div class="fw-card" style="padding:14px;">
        <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
            <input type="checkbox" id="consent" required checked style="margin-top:3px; accent-color:var(--blue);">
            <span style="font-size:12px; color:var(--text); line-height:1.4;">
                I declare that the information and documents provided belong to me and are authentic.
            </span>
        </label>
    </div>
</form>

@endsection

@section('sticky-bottom')
<button type="submit" form="kycForm" class="fw-btn fw-btn-primary">
    Submit Documents &amp; Continue →
</button>
@endsection

@push('scripts')
<script>
function handlePreview(boxId, badgeId, previewId, placeholderId, input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    
    // Update badge & container styling
    var box = document.getElementById(boxId);
    box.classList.add('fw-upload-done');
    var badge = document.getElementById(badgeId);
    badge.textContent = '✓ Selected';
    badge.className = 'fw-badge fw-badge-green';

    // Show thumbnail preview if image
    if (file.type.match('image.*')) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var previewEl = document.getElementById(previewId);
            previewEl.innerHTML = '<img src="' + e.target.result + '" class="fw-preview-thumb"><p style="font-size:11px;color:#065f46;font-weight:600;margin-top:2px;">Tap to re-select file</p>';
            previewEl.style.display = 'block';
            document.getElementById(placeholderId).style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        var previewEl = document.getElementById(previewId);
        previewEl.innerHTML = '<div style="font-size:24px;margin-bottom:2px;">📄</div><strong style="font-size:12px;color:#065f46;">' + file.name + '</strong><p style="font-size:11px;color:#065f46;">Tap to re-select</p>';
        previewEl.style.display = 'block';
        document.getElementById(placeholderId).style.display = 'none';
    }
}
</script>
@endpush

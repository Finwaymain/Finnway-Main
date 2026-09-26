@extends('finance.layouts.base')
@section('title', 'KYC Upload — Fiinway')
@section('header-sub', 'Zero-CIBIL Credit')
@section('progress-label', 'Step 1 of 6')
@section('progress-pct', '16')
@section('progress', ' ')

@section('back')
<a href="{{ route('finance.zero_cibil.s01_intro', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')

<p class="fw-section-title">KYC Documents</p>
<p class="fw-section-sub">Upload 3 documents to verify your identity.</p>

<div class="fw-alert fw-alert-info" style="margin-bottom:16px">
    Only <strong>Aadhaar Front, Aadhaar Back &amp; PAN Card</strong> required.
</div>

{{-- Doc 1: Aadhaar Front --}}
<div class="fw-card" style="padding:14px">
    <div class="fw-doc-item" style="margin-bottom:12px; border:none; padding:0">
        <div class="fw-doc-item-info">
            <div class="fw-doc-icon">🪪</div>
            <div>
                <div class="fw-doc-name">Aadhaar Card — Front</div>
                <div class="fw-doc-status">JPG / PNG / PDF · Max 5 MB</div>
            </div>
        </div>
        <span class="fw-badge fw-badge-amber" id="badge-aadhaar-front">Pending</span>
    </div>
    <label class="fw-upload-box" id="box-aadhaar-front" for="file-aadhaar-front">
        <div class="fw-upload-icon">⬆</div>
        <strong>Tap to Upload</strong>
        <p>Front side of your Aadhaar card</p>
        <input type="file" id="file-aadhaar-front" name="aadhaar_front" accept="image/*,.pdf"
               onchange="markUploaded('box-aadhaar-front','badge-aadhaar-front',this)">
    </label>
</div>

{{-- Doc 2: Aadhaar Back --}}
<div class="fw-card" style="padding:14px">
    <div class="fw-doc-item" style="margin-bottom:12px; border:none; padding:0">
        <div class="fw-doc-item-info">
            <div class="fw-doc-icon">🪪</div>
            <div>
                <div class="fw-doc-name">Aadhaar Card — Back</div>
                <div class="fw-doc-status">JPG / PNG / PDF · Max 5 MB</div>
            </div>
        </div>
        <span class="fw-badge fw-badge-amber" id="badge-aadhaar-back">Pending</span>
    </div>
    <label class="fw-upload-box" id="box-aadhaar-back" for="file-aadhaar-back">
        <div class="fw-upload-icon">⬆</div>
        <strong>Tap to Upload</strong>
        <p>Back side of your Aadhaar card</p>
        <input type="file" id="file-aadhaar-back" name="aadhaar_back" accept="image/*,.pdf"
               onchange="markUploaded('box-aadhaar-back','badge-aadhaar-back',this)">
    </label>
</div>

{{-- Doc 3: PAN Card --}}
<div class="fw-card" style="padding:14px">
    <div class="fw-doc-item" style="margin-bottom:12px; border:none; padding:0">
        <div class="fw-doc-item-info">
            <div class="fw-doc-icon">💳</div>
            <div>
                <div class="fw-doc-name">PAN Card</div>
                <div class="fw-doc-status">JPG / PNG / PDF · Max 5 MB</div>
            </div>
        </div>
        <span class="fw-badge fw-badge-amber" id="badge-pan">Pending</span>
    </div>
    <label class="fw-upload-box" id="box-pan" for="file-pan">
        <div class="fw-upload-icon">⬆</div>
        <strong>Tap to Upload</strong>
        <p>Your PAN card (front)</p>
        <input type="file" id="file-pan" name="pan_card" accept="image/*,.pdf"
               onchange="markUploaded('box-pan','badge-pan',this)">
    </label>
</div>

<div class="fw-consent">
    <input type="checkbox" id="consent" required>
    <label for="consent">I confirm these documents belong to me and the information is accurate.</label>
</div>

@endsection

@section('sticky-bottom')
<a href="{{ route('finance.zero_cibil.s03_amount_select', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">Submit Documents →</a>
@endsection

@push('scripts')
<script>
function markUploaded(boxId, badgeId, input) {
    if (!input.files.length) return;
    document.getElementById(boxId).classList.add('fw-upload-done');
    var badge = document.getElementById(badgeId);
    badge.textContent = '✓ Uploaded';
    badge.className = 'fw-badge fw-badge-green';
}
</script>
@endpush

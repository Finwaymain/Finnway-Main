@extends('finance.layouts.base')
@section('title', 'KYC Documents — Fiinway Student Credit')
@section('header-sub', 'Student Credit')
@section('progress-label', 'Step 2 of 8')
@section('progress-pct', '25')
@section('progress') @endsection

@section('back')
<a href="{{ route('finance.student_credit.s01_apply', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<p class="fw-section-title">Identity Documents</p>
<p class="fw-section-sub">Upload clear photos. All 3 documents required.</p>

<div class="fw-alert fw-alert-info">
    📋 Student ID must have <strong>at least 6 months</strong> validity remaining from today.
</div>

{{-- Aadhaar Front --}}
<div class="fw-card" style="padding:14px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div class="fw-doc-icon">🪪</div>
        <div>
            <p class="fw-doc-name">Aadhaar Card — Front</p>
            <p class="fw-doc-status">Shows name, DOB, Aadhaar number</p>
        </div>
    </div>
    <label class="fw-upload-box" for="doc_aadhaar_front">
        <input type="file" id="doc_aadhaar_front" name="aadhaar_front"
               accept="image/*,application/pdf" onchange="markDone(this,'lbl_aadhaar_front')">
        <div class="fw-upload-icon">📤</div>
        <strong id="lbl_aadhaar_front">Tap to upload</strong>
        <p>JPG, PNG or PDF · Max 5 MB</p>
    </label>
</div>

{{-- Aadhaar Back --}}
<div class="fw-card" style="padding:14px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div class="fw-doc-icon">🪪</div>
        <div>
            <p class="fw-doc-name">Aadhaar Card — Back</p>
            <p class="fw-doc-status">Shows address and barcode</p>
        </div>
    </div>
    <label class="fw-upload-box" for="doc_aadhaar_back">
        <input type="file" id="doc_aadhaar_back" name="aadhaar_back"
               accept="image/*,application/pdf" onchange="markDone(this,'lbl_aadhaar_back')">
        <div class="fw-upload-icon">📤</div>
        <strong id="lbl_aadhaar_back">Tap to upload</strong>
        <p>JPG, PNG or PDF · Max 5 MB</p>
    </label>
</div>

{{-- Student ID --}}
<div class="fw-card" style="padding:14px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div class="fw-doc-icon">🎓</div>
        <div>
            <p class="fw-doc-name">Student ID Card</p>
            <p class="fw-doc-status">Must be valid for 6+ months</p>
        </div>
    </div>
    <label class="fw-upload-box" for="doc_student_id">
        <input type="file" id="doc_student_id" name="student_id"
               accept="image/*,application/pdf" onchange="markDone(this,'lbl_student_id')">
        <div class="fw-upload-icon">📤</div>
        <strong id="lbl_student_id">Tap to upload</strong>
        <p>JPG, PNG or PDF · Max 5 MB</p>
    </label>
</div>

<div class="fw-alert fw-alert-warn">
    ⚠️ Ensure documents are clear, fully visible, and not expired.
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.student_credit.s03_fee_payment', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">Submit Documents →</a>
@endsection

@push('scripts')
<script>
function markDone(input, labelId) {
    if (input.files && input.files[0]) {
        var lbl = document.getElementById(labelId);
        lbl.textContent = '✓ ' + input.files[0].name;
        input.closest('.fw-upload-box').classList.add('fw-upload-done');
    }
}
</script>
@endpush

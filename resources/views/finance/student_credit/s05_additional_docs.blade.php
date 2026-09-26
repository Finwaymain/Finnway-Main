@extends('finance.layouts.base')
@section('title', 'Additional Documents — Fiinway Student Credit')
@section('header-sub', 'Student Credit')
@section('progress-label', 'Step 5 of 8')
@section('progress-pct', '62')
@section('progress') @endsection

@section('back')
<a href="{{ route('finance.student_credit.s04_pending', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<p class="fw-section-title">Additional Supporting Documents</p>
<p class="fw-section-sub">Upload requested documents to complete your credit assessment.</p>

<div class="fw-alert fw-alert-warn">
    ⚠️ Upload clear photos or PDF copies. Documents must show student name and institution stamp or letterhead.
</div>

{{-- Document 1: College Bonafide / Admission Receipt --}}
<div class="fw-card" style="padding:14px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div class="fw-doc-icon">🏛️</div>
        <div>
            <p class="fw-doc-name">Bonafide / Admission Receipt</p>
            <p class="fw-doc-status">Current academic year fee receipt or bonafide</p>
        </div>
    </div>
    <label class="fw-upload-box" for="doc_bonafide">
        <input type="file" id="doc_bonafide" name="bonafide"
               accept="image/*,application/pdf" onchange="markDone(this,'lbl_bonafide')">
        <div class="fw-upload-icon">📤</div>
        <strong id="lbl_bonafide">Tap to upload</strong>
        <p>JPG, PNG or PDF · Max 5 MB</p>
    </label>
</div>

{{-- Document 2: Semester Marksheet / Course Details --}}
<div class="fw-card" style="padding:14px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div class="fw-doc-icon">📄</div>
        <div>
            <p class="fw-doc-name">Latest Marksheet / Course Proof</p>
            <p class="fw-doc-status">Previous semester marksheet or course syllabus</p>
        </div>
    </div>
    <label class="fw-upload-box" for="doc_marksheet">
        <input type="file" id="doc_marksheet" name="marksheet"
               accept="image/*,application/pdf" onchange="markDone(this,'lbl_marksheet')">
        <div class="fw-upload-icon">📤</div>
        <strong id="lbl_marksheet">Tap to upload</strong>
        <p>JPG, PNG or PDF · Max 5 MB</p>
    </label>
</div>

{{-- Document 3: Passport / Visa (For International) --}}
<div class="fw-card" style="padding:14px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div class="fw-doc-icon">✈️</div>
        <div>
            <p class="fw-doc-name">Passport / Visa (Optional for Domestic)</p>
            <p class="fw-doc-status">Mandatory for international students only</p>
        </div>
    </div>
    <label class="fw-upload-box" for="doc_passport">
        <input type="file" id="doc_passport" name="passport"
               accept="image/*,application/pdf" onchange="markDone(this,'lbl_passport')">
        <div class="fw-upload-icon">📤</div>
        <strong id="lbl_passport">Tap to upload</strong>
        <p>JPG, PNG or PDF · Max 5 MB</p>
    </label>
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.student_credit.s06_mgmt_approval', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">Submit Documents &amp; Check Approval →</a>
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

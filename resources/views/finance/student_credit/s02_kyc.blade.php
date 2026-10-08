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

@if(session('error'))
<div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;">
    {{ session('error') }}
</div>
@endif
@if($errors->any())
<div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;">
    <ul style="margin: 0; padding-left: 20px;">
        @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

@php
    $existingDocs = $customer ? \App\Models\Finance\FinanceDocument::where('customer_id', $customer->id)->pluck('status', 'document_type')->toArray() : [];
@endphp

<form id="studentKycForm" method="POST" action="{{ route('finance.student_credit.save_kyc') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="phone" value="{{ request('phone', $phone ?? '') }}">

    {{-- Aadhaar Front --}}
    <div class="fw-card" style="padding:14px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div class="fw-doc-icon">🪪</div>
            <div>
                <p class="fw-doc-name">Aadhaar Card — Front <span style="color:red;">*</span></p>
                <p class="fw-doc-status">Shows name, DOB, Aadhaar number</p>
            </div>
        </div>
        <label class="fw-upload-box {{ isset($existingDocs['aadhaar_front']) ? 'fw-upload-done' : '' }}" for="doc_aadhaar_front">
            <input type="file" id="doc_aadhaar_front" name="aadhaar_front"
                   accept="image/*,application/pdf" onchange="markDone(this,'lbl_aadhaar_front')">
            <div class="fw-upload-icon">📤</div>
            <strong id="lbl_aadhaar_front">{{ isset($existingDocs['aadhaar_front']) ? '✓ Document on file (tap to replace)' : 'Tap to upload' }}</strong>
            <p>JPG, PNG or PDF · Max 5 MB</p>
        </label>
        <span class="fw-field-error" id="err_aadhaar_front" style="display:none; color:#dc2626; font-size:12px; margin-top:4px;"></span>
    </div>

    {{-- Aadhaar Back --}}
    <div class="fw-card" style="padding:14px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div class="fw-doc-icon">🪪</div>
            <div>
                <p class="fw-doc-name">Aadhaar Card — Back <span style="color:red;">*</span></p>
                <p class="fw-doc-status">Shows address and barcode</p>
            </div>
        </div>
        <label class="fw-upload-box {{ isset($existingDocs['aadhaar_back']) ? 'fw-upload-done' : '' }}" for="doc_aadhaar_back">
            <input type="file" id="doc_aadhaar_back" name="aadhaar_back"
                   accept="image/*,application/pdf" onchange="markDone(this,'lbl_aadhaar_back')">
            <div class="fw-upload-icon">📤</div>
            <strong id="lbl_aadhaar_back">{{ isset($existingDocs['aadhaar_back']) ? '✓ Document on file (tap to replace)' : 'Tap to upload' }}</strong>
            <p>JPG, PNG or PDF · Max 5 MB</p>
        </label>
        <span class="fw-field-error" id="err_aadhaar_back" style="display:none; color:#dc2626; font-size:12px; margin-top:4px;"></span>
    </div>

    {{-- Student ID --}}
    <div class="fw-card" style="padding:14px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div class="fw-doc-icon">🎓</div>
            <div>
                <p class="fw-doc-name">Student ID Card <span style="color:red;">*</span></p>
                <p class="fw-doc-status">Must be valid for 6+ months</p>
            </div>
        </div>
        <label class="fw-upload-box {{ (isset($existingDocs['student_id']) || isset($existingDocs['student_id_card'])) ? 'fw-upload-done' : '' }}" for="doc_student_id">
            <input type="file" id="doc_student_id" name="student_id"
                   accept="image/*,application/pdf" onchange="markDone(this,'lbl_student_id')">
            <div class="fw-upload-icon">📤</div>
            <strong id="lbl_student_id">{{ (isset($existingDocs['student_id']) || isset($existingDocs['student_id_card'])) ? '✓ Document on file (tap to replace)' : 'Tap to upload' }}</strong>
            <p>JPG, PNG or PDF · Max 5 MB</p>
        </label>
        <span class="fw-field-error" id="err_student_id" style="display:none; color:#dc2626; font-size:12px; margin-top:4px;"></span>
    </div>

    <div class="fw-alert fw-alert-warn">
        ⚠️ Ensure documents are clear, fully visible, and not expired.
    </div>
</form>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="studentKycForm" class="fw-btn fw-btn-primary w-100">
        Submit Documents →
    </button>
</div>
@endsection

@push('scripts')
<script>
var hasFront = {{ isset($existingDocs['aadhaar_front']) ? 'true' : 'false' }};
var hasBack = {{ isset($existingDocs['aadhaar_back']) ? 'true' : 'false' }};
var hasStudentId = {{ (isset($existingDocs['student_id']) || isset($existingDocs['student_id_card'])) ? 'true' : 'false' }};

function markDone(input, labelId) {
    if (input.files && input.files[0]) {
        var lbl = document.getElementById(labelId);
        lbl.textContent = '✓ ' + input.files[0].name;
        input.closest('.fw-upload-box').classList.add('fw-upload-done');
        var errSpan = input.closest('.fw-card').querySelector('.fw-field-error');
        if (errSpan) errSpan.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('studentKycForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        var frontInp = document.getElementById('doc_aadhaar_front');
        var backInp = document.getElementById('doc_aadhaar_back');
        var sidInp = document.getElementById('doc_student_id');

        var errFront = document.getElementById('err_aadhaar_front');
        var errBack = document.getElementById('err_aadhaar_back');
        var errSid = document.getElementById('err_student_id');

        var valid = true;

        if (!hasFront && (!frontInp.files || !frontInp.files.length)) {
            errFront.textContent = 'Please select and upload Aadhaar Front image.';
            errFront.style.display = 'block';
            valid = false;
        } else {
            errFront.style.display = 'none';
        }

        if (!hasBack && (!backInp.files || !backInp.files.length)) {
            errBack.textContent = 'Please select and upload Aadhaar Back image.';
            errBack.style.display = 'block';
            valid = false;
        } else {
            errBack.style.display = 'none';
        }

        if (!hasStudentId && (!sidInp.files || !sidInp.files.length)) {
            errSid.textContent = 'Please select and upload Student ID Card image.';
            errSid.style.display = 'block';
            valid = false;
        } else {
            errSid.style.display = 'none';
        }

        if (!valid) {
            e.preventDefault();
            var firstErr = form.querySelector('.fw-field-error[style*="display: block"]');
            if (firstErr) {
                firstErr.closest('.fw-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        }
    });
});
</script>
@endpush

@extends('finance.layouts.base')
@section('title', 'KYC Documents — Virtual Loan')
@section('header-sub', 'Virtual Loan')
@section('progress-label', 'Step 2 of 5')
@section('progress-pct', '40')
@section('progress') @endsection

@section('back')
<a href="{{ route('finance.virtual_loan.s01_apply', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<p class="fw-section-title">KYC Document Upload</p>
<p class="fw-section-sub">Upload clear photos of your identity documents (3 documents required).</p>

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

<form id="virtualKycForm" method="POST" action="{{ route('finance.virtual_loan.save_kyc') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="phone" value="{{ request('phone', $phone ?? '') }}">

    <div class="fw-card" style="padding:14px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div class="fw-doc-icon">🪪</div>
            <div>
                <p class="fw-doc-name">Aadhaar Card — Front <span style="color:red;">*</span></p>
                <p class="fw-doc-status">Clear photo showing face, name, and DOB</p>
            </div>
        </div>
        <label class="fw-upload-box {{ isset($existingDocs['aadhaar_front']) ? 'fw-upload-done' : '' }}" for="doc_aadhaar_front">
            <input type="file" id="doc_aadhaar_front" name="aadhaar_front" accept="image/*,application/pdf" onchange="markDone(this,'lbl_aadhaar_front')">
            <div class="fw-upload-icon">📤</div>
            <strong id="lbl_aadhaar_front">{{ isset($existingDocs['aadhaar_front']) ? '✓ Document on file (tap to replace)' : 'Tap to upload Aadhaar Front' }}</strong>
            <p>JPG, PNG or PDF · Max 5 MB</p>
        </label>
        <span class="fw-field-error" id="err_aadhaar_front" style="display:none; color:#dc2626; font-size:12px; margin-top:4px;"></span>
    </div>

    <div class="fw-card" style="padding:14px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div class="fw-doc-icon">🪪</div>
            <div>
                <p class="fw-doc-name">Aadhaar Card — Back <span style="color:red;">*</span></p>
                <p class="fw-doc-status">Showing address and QR/barcode</p>
            </div>
        </div>
        <label class="fw-upload-box {{ isset($existingDocs['aadhaar_back']) ? 'fw-upload-done' : '' }}" for="doc_aadhaar_back">
            <input type="file" id="doc_aadhaar_back" name="aadhaar_back" accept="image/*,application/pdf" onchange="markDone(this,'lbl_aadhaar_back')">
            <div class="fw-upload-icon">📤</div>
            <strong id="lbl_aadhaar_back">{{ isset($existingDocs['aadhaar_back']) ? '✓ Document on file (tap to replace)' : 'Tap to upload Aadhaar Back' }}</strong>
            <p>JPG, PNG or PDF · Max 5 MB</p>
        </label>
        <span class="fw-field-error" id="err_aadhaar_back" style="display:none; color:#dc2626; font-size:12px; margin-top:4px;"></span>
    </div>

    <div class="fw-card" style="padding:14px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div class="fw-doc-icon">💳</div>
            <div>
                <p class="fw-doc-name">PAN Card <span style="color:red;">*</span></p>
                <p class="fw-doc-status">Valid PAN card with clear signature</p>
            </div>
        </div>
        <label class="fw-upload-box {{ isset($existingDocs['pan']) ? 'fw-upload-done' : '' }}" for="doc_pan">
            <input type="file" id="doc_pan" name="pan" accept="image/*,application/pdf" onchange="markDone(this,'lbl_pan')">
            <div class="fw-upload-icon">📤</div>
            <strong id="lbl_pan">{{ isset($existingDocs['pan']) ? '✓ Document on file (tap to replace)' : 'Tap to upload PAN Card' }}</strong>
            <p>JPG, PNG or PDF · Max 5 MB</p>
        </label>
        <span class="fw-field-error" id="err_pan" style="display:none; color:#dc2626; font-size:12px; margin-top:4px;"></span>
    </div>

    <div class="fw-alert fw-alert-info">
        ℹ️ No bank statement or passbook required at this stage.
    </div>
</form>
@endsection

@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="virtualKycForm" class="fw-btn fw-btn-primary w-100">
        Submit Documents &amp; Continue →
    </button>
</div>
@endsection

@push('scripts')
<script>
var hasFront = {{ isset($existingDocs['aadhaar_front']) ? 'true' : 'false' }};
var hasBack = {{ isset($existingDocs['aadhaar_back']) ? 'true' : 'false' }};
var hasPan = {{ isset($existingDocs['pan']) ? 'true' : 'false' }};

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
    var form = document.getElementById('virtualKycForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        var frontInp = document.getElementById('doc_aadhaar_front');
        var backInp = document.getElementById('doc_aadhaar_back');
        var panInp = document.getElementById('doc_pan');

        var errFront = document.getElementById('err_aadhaar_front');
        var errBack = document.getElementById('err_aadhaar_back');
        var errPan = document.getElementById('err_pan');

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

        if (!hasPan && (!panInp.files || !panInp.files.length)) {
            errPan.textContent = 'Please select and upload PAN Card image.';
            errPan.style.display = 'block';
            valid = false;
        } else {
            errPan.style.display = 'none';
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

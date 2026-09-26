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
<p class="fw-section-sub">Upload clear photos of your identity documents (3 documents only).</p>

<div class="fw-card" style="padding:14px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div class="fw-doc-icon">🪪</div>
        <div>
            <p class="fw-doc-name">Aadhaar Card — Front</p>
            <p class="fw-doc-status">Clear photo showing face, name, and DOB</p>
        </div>
    </div>
    <label class="fw-upload-box" for="doc_aadhaar_front">
        <input type="file" id="doc_aadhaar_front" name="aadhaar_front" accept="image/*,application/pdf" onchange="markDone(this,'lbl_aadhaar_front')">
        <div class="fw-upload-icon">📤</div>
        <strong id="lbl_aadhaar_front">Tap to upload Aadhaar Front</strong>
        <p>JPG, PNG or PDF · Max 5 MB</p>
    </label>
</div>

<div class="fw-card" style="padding:14px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div class="fw-doc-icon">🪪</div>
        <div>
            <p class="fw-doc-name">Aadhaar Card — Back</p>
            <p class="fw-doc-status">Showing address and QR/barcode</p>
        </div>
    </div>
    <label class="fw-upload-box" for="doc_aadhaar_back">
        <input type="file" id="doc_aadhaar_back" name="aadhaar_back" accept="image/*,application/pdf" onchange="markDone(this,'lbl_aadhaar_back')">
        <div class="fw-upload-icon">📤</div>
        <strong id="lbl_aadhaar_back">Tap to upload Aadhaar Back</strong>
        <p>JPG, PNG or PDF · Max 5 MB</p>
    </label>
</div>

<div class="fw-card" style="padding:14px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <div class="fw-doc-icon">💳</div>
        <div>
            <p class="fw-doc-name">PAN Card</p>
            <p class="fw-doc-status">Valid PAN card with clear signature</p>
        </div>
    </div>
    <label class="fw-upload-box" for="doc_pan">
        <input type="file" id="doc_pan" name="pan" accept="image/*,application/pdf" onchange="markDone(this,'lbl_pan')">
        <div class="fw-upload-icon">📤</div>
        <strong id="lbl_pan">Tap to upload PAN Card</strong>
        <p>JPG, PNG or PDF · Max 5 MB</p>
    </label>
</div>

<div class="fw-alert fw-alert-info">
    ℹ️ No bank statement or passbook required at this stage.
</div>
@endsection

@section('sticky-bottom')
<a href="{{ route('finance.virtual_loan.s03_fee_payment', ['phone' => request('phone')]) }}"
   class="fw-btn fw-btn-primary">Submit Documents &amp; Continue →</a>
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

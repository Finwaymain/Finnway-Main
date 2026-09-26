@extends('finance.layouts.base')
@section('title', 'Upload Documents — Fiinway')
@section('header-sub', 'Cash Loan')

@section('progress')
<div class="fw-progress">
    <div class="fw-progress-label"><span>Step 6 of 12</span><span>50%</span></div>
    <div class="fw-progress-track"><div class="fw-progress-fill" style="width:50%;"></div></div>
</div>
@endsection

@section('back')
<a href="{{ route('finance.cash_loan.s06_emi', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card">
    <h3 class="fw-card-title mb-1">Upload Documents</h3>
    <p class="text-muted small mb-4">Please upload clear, legible copies or capture photos. Max size: 5MB per file.</p>

    <form id="docForm" method="POST" action="{{ route('finance.cash_loan.save_step') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="step" value="s07">
        <input type="hidden" name="phone" value="{{ request('phone') }}">

        <!-- 1. PAN Card -->
        <div class="fw-card p-3 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong style="color:var(--navy); font-size:14px;">1. PAN Card</strong>
                    <div class="small text-muted">Clear front photo</div>
                </div>
                <span class="fw-badge fw-badge-amber" id="badge-pan">Pending</span>
            </div>
            <label class="fw-upload-box" id="box-pan" style="margin:0; padding:12px;">
                <div id="preview-pan" style="display:none;"></div>
                <div id="placeholder-pan">
                    <div class="fw-upload-icon" style="font-size:20px;">📷</div>
                    <strong style="font-size:13px;">Tap to Select / Capture PAN</strong>
                    <p style="font-size:11px;">JPG, PNG, PDF</p>
                </div>
                <input type="file" name="pan_card" accept="image/*,.pdf" capture="environment"
                       onchange="previewDoc('box-pan', 'badge-pan', 'preview-pan', 'placeholder-pan', this)">
            </label>
        </div>

        <!-- 2. Aadhaar Front -->
        <div class="fw-card p-3 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong style="color:var(--navy); font-size:14px;">2. Aadhaar Front</strong>
                    <div class="small text-muted">Front side with photo</div>
                </div>
                <span class="fw-badge fw-badge-amber" id="badge-aadhaar-front">Pending</span>
            </div>
            <label class="fw-upload-box" id="box-aadhaar-front" style="margin:0; padding:12px;">
                <div id="preview-aadhaar-front" style="display:none;"></div>
                <div id="placeholder-aadhaar-front">
                    <div class="fw-upload-icon" style="font-size:20px;">📷</div>
                    <strong style="font-size:13px;">Tap to Select / Capture Aadhaar Front</strong>
                    <p style="font-size:11px;">JPG, PNG, PDF</p>
                </div>
                <input type="file" name="aadhaar_front" accept="image/*,.pdf" capture="environment"
                       onchange="previewDoc('box-aadhaar-front', 'badge-aadhaar-front', 'preview-aadhaar-front', 'placeholder-aadhaar-front', this)">
            </label>
        </div>

        <!-- 3. Aadhaar Back -->
        <div class="fw-card p-3 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong style="color:var(--navy); font-size:14px;">3. Aadhaar Back</strong>
                    <div class="small text-muted">Back side with address</div>
                </div>
                <span class="fw-badge fw-badge-amber" id="badge-aadhaar-back">Pending</span>
            </div>
            <label class="fw-upload-box" id="box-aadhaar-back" style="margin:0; padding:12px;">
                <div id="preview-aadhaar-back" style="display:none;"></div>
                <div id="placeholder-aadhaar-back">
                    <div class="fw-upload-icon" style="font-size:20px;">📷</div>
                    <strong style="font-size:13px;">Tap to Select / Capture Aadhaar Back</strong>
                    <p style="font-size:11px;">JPG, PNG, PDF</p>
                </div>
                <input type="file" name="aadhaar_back" accept="image/*,.pdf" capture="environment"
                       onchange="previewDoc('box-aadhaar-back', 'badge-aadhaar-back', 'preview-aadhaar-back', 'placeholder-aadhaar-back', this)">
            </label>
        </div>

        <!-- 4. Address Proof -->
        <div class="fw-card p-3 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong style="color:var(--navy); font-size:14px;">4. Address Proof</strong>
                    <div class="small text-muted">Utility Bill / Voter ID / Passport</div>
                </div>
                <span class="fw-badge fw-badge-amber" id="badge-address">Pending</span>
            </div>
            <label class="fw-upload-box" id="box-address" style="margin:0; padding:12px;">
                <div id="preview-address" style="display:none;"></div>
                <div id="placeholder-address">
                    <div class="fw-upload-icon" style="font-size:20px;">📷</div>
                    <strong style="font-size:13px;">Tap to Select Address Proof</strong>
                    <p style="font-size:11px;">JPG, PNG, PDF</p>
                </div>
                <input type="file" name="address_proof" accept="image/*,.pdf" capture="environment"
                       onchange="previewDoc('box-address', 'badge-address', 'preview-address', 'placeholder-address', this)">
            </label>
        </div>

        <!-- 5. Income Proof -->
        <div class="fw-card p-3 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong style="color:var(--navy); font-size:14px;">5. Income Proof</strong>
                    <div class="small text-muted">Latest Salary Slip / Form 16</div>
                </div>
                <span class="fw-badge fw-badge-amber" id="badge-income">Pending</span>
            </div>
            <label class="fw-upload-box" id="box-income" style="margin:0; padding:12px;">
                <div id="preview-income" style="display:none;"></div>
                <div id="placeholder-income">
                    <div class="fw-upload-icon" style="font-size:20px;">📷</div>
                    <strong style="font-size:13px;">Tap to Select Income Proof</strong>
                    <p style="font-size:11px;">JPG, PNG, PDF</p>
                </div>
                <input type="file" name="income_proof" accept="image/*,.pdf" capture="environment"
                       onchange="previewDoc('box-income', 'badge-income', 'preview-income', 'placeholder-income', this)">
            </label>
        </div>
    </form>
</div>
@endsection

@section('sticky-bottom')
<button type="submit" form="docForm" class="fw-btn fw-btn-primary">
    Submit Documents &amp; Proceed →
</button>
@endsection

@push('scripts')
<script>
function previewDoc(boxId, badgeId, previewId, placeholderId, input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    
    var box = document.getElementById(boxId);
    box.classList.add('fw-upload-done');
    var badge = document.getElementById(badgeId);
    badge.textContent = '✓ Selected';
    badge.className = 'fw-badge fw-badge-green';

    if (file.type.match('image.*')) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var previewEl = document.getElementById(previewId);
            previewEl.innerHTML = '<img src="' + e.target.result + '" class="fw-preview-thumb"><p style="font-size:11px;color:#065f46;font-weight:600;margin-top:2px;">Tap to change photo</p>';
            previewEl.style.display = 'block';
            document.getElementById(placeholderId).style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        var previewEl = document.getElementById(previewId);
        previewEl.innerHTML = '<div style="font-size:22px;margin-bottom:2px;">📄</div><strong style="font-size:12px;color:#065f46;">' + file.name + '</strong><p style="font-size:11px;color:#065f46;">Tap to change</p>';
        previewEl.style.display = 'block';
        document.getElementById(placeholderId).style.display = 'none';
    }
}
</script>
@endpush

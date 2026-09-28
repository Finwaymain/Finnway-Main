@extends('finance.layouts.base')
@section('title', 'Additional Documents — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s18_tracking', ['phone' => $phone]) }}" class="fw-back">← Back</a>
@endsection

@section('content')
<div class="fw-card" style="padding: 24px 16px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #fee2e2; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 10px;">
            ⚠️
        </div>
        <h2 class="fw-heading" style="font-size: 20px; font-weight: 700; color: #dc2626; margin-bottom: 4px;">
            Additional Documents Required
        </h2>
        <div style="display: inline-block; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 20px;">
            Action Needed to Complete Underwriting
        </div>
    </div>

    <!-- Application Summary -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
            <span style="color: #64748b;">Application No.:</span>
            <span style="font-family: monospace; font-weight: 700; color: #0f172a;">{{ $appNumber }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span style="color: #64748b;">Loan Amount:</span>
            <span style="font-weight: 700; color: #059669;">₹{{ number_format(($application && $application->approved_amount > 0) ? $application->approved_amount : $amount) }}</span>
        </div>
    </div>

    <!-- Admin Remark Notice -->
    @if(!empty($docRequestRemark))
    <div style="background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 14px 16px; margin-bottom: 22px;">
        <div style="font-size: 12px; font-weight: 700; color: #b45309; text-transform: uppercase; margin-bottom: 4px;">
            Admin Underwriting Instructions:
        </div>
        <div style="font-size: 13px; color: #92400e; line-height: 1.5; font-weight: 500;">
            {{ $docRequestRemark }}
        </div>
    </div>
    @endif

    <!-- Upload Form -->
    <form method="POST" action="{{ route('finance.cash_loan.s24_additional_docs_submit', ['phone' => $phone]) }}" enctype="multipart/form-data">
        @csrf

        <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 12px;">
            Please upload the following required documents:
        </div>

        @foreach($requestedDocs as $index => $docName)
        <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 14px 16px; margin-bottom: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <label style="font-size: 13px; font-weight: 700; color: #0f172a; margin: 0;">
                    {{ $index + 1 }}. {{ $docName }} <span style="color: #dc2626;">*</span>
                </label>
                <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 11px;">Required</span>
            </div>
            <input type="hidden" name="doc_names[{{ $index }}]" value="{{ $docName }}">
            <input type="file" name="doc_files[{{ $index }}]" class="fw-input" accept="image/*,.pdf" style="font-size: 12px; padding: 8px;" required>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                Accepted: JPG, PNG, PDF (Max 10MB)
            </div>
        </div>
        @endforeach

        <button type="submit" class="fw-btn fw-btn-primary w-100" style="padding: 14px; font-size: 15px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 12px rgba(37,99,235,0.25); margin-top: 8px;">
            📤 Upload &amp; Submit Documents
        </button>
    </form>
</div>
@endsection

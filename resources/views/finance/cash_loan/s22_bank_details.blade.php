@extends('finance.layouts.base')
@section('title', 'Bank Details — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
<a href="{{ route('finance.cash_loan.s21_approval', ['phone' => $phone]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Disbursement Bank Details</h2>
    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
        Enter your active bank account for loan amount disbursement.
    </p>

    @if($errors->any())
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('finance.cash_loan.s22_bank_details_submit', ['phone' => $phone]) }}" id="bankDetailsForm">
        @csrf
        
        <div class="fw-form-group">
            <label>Account Holder Name <span style="color:red;">*</span></label>
            <input type="text" name="account_holder_name" class="fw-input" placeholder="Name as per bank" value="{{ old('account_holder_name', $application->disbursement_account_name ?? ($customer->name ?? '')) }}" required>
        </div>

        <div class="fw-form-group">
            <label>Bank Name <span style="color:red;">*</span></label>
            <input type="text" name="bank_name" class="fw-input" placeholder="e.g. HDFC Bank, SBI, ICICI" value="{{ old('bank_name', $application->disbursement_bank_name ?? '') }}" required>
        </div>

        <div class="fw-form-group">
            <label>Account Number <span style="color:red;">*</span></label>
            <input type="password" name="account_number" id="account_number" class="fw-input" placeholder="Enter Account Number" value="{{ old('account_number', $application->disbursement_account_number ?? '') }}" required>
        </div>

        <div class="fw-form-group">
            <label>Confirm Account Number <span style="color:red;">*</span></label>
            <input type="text" name="confirm_account_number" id="confirm_account_number" class="fw-input" placeholder="Re-enter Account Number" value="{{ old('confirm_account_number', $application->disbursement_account_number ?? '') }}" required>
        </div>

        <div class="fw-form-group">
            <label>IFSC Code <span style="color:red;">*</span></label>
            <input type="text" name="ifsc_code" class="fw-input" placeholder="e.g. HDFC0001234" value="{{ old('ifsc_code', $application->disbursement_ifsc ?? '') }}" style="text-transform: uppercase;" required>
        </div>

        <div class="fw-form-group">
            <label>Account Type <span style="color:red;">*</span></label>
            <select name="account_type" class="fw-input" required>
                <option value="Savings" {{ old('account_type', $application->disbursement_account_type ?? 'Savings') === 'Savings' ? 'selected' : '' }}>Savings</option>
                <option value="Current" {{ old('account_type', $application->disbursement_account_type ?? '') === 'Current' ? 'selected' : '' }}>Current</option>
                <option value="Salary" {{ old('account_type', $application->disbursement_account_type ?? '') === 'Salary' ? 'selected' : '' }}>Salary</option>
            </select>
        </div>
        
        <h3 style="font-size:15px; margin-top:20px; font-weight: 700; color: #0f172a;">Verify Bank Account</h3>
        <div class="fw-checkbox-group" style="margin-top:10px;">
            <label style="display: flex; align-items: center; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="confirm_ownership" value="1" style="width: 17px; height: 17px; margin-right: 8px; cursor: pointer; accent-color: #2563eb;" required checked>
                I confirm that this bank account belongs to me.
            </label>
        </div>
    </form>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="bankDetailsForm" class="fw-btn fw-btn-primary w-100" style="padding: 14px; font-size: 15px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
        Submit for Disbursement
    </button>
</div>
@endsection

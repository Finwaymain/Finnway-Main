@extends('finance.layouts.base')
@section('title', 'Bank Details — Fiinway')
@section('header-sub', 'Business Loan')
@section('back')
<a href="{{ route('finance.business_loan.s17_approval', ['phone' => request('phone')]) }}" class="fw-back">← Back</a>
@endsection
@section('content')
<div class="fw-card">
    <h2 class="fw-heading">Disbursement Bank Details</h2>
    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
        Enter the registered business bank account for loan disbursement.
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

    <form method="POST" action="{{ route('finance.business_loan.s18_bank_details_submit', ['phone' => request('phone')]) }}" id="bizBankDetailsForm">
        @csrf

        <div class="fw-form-group">
            <label>Account Holder / Business Name <span style="color:red;">*</span></label>
            <input type="text" name="account_holder_name" class="fw-input" placeholder="Name as per bank account" value="{{ old('account_holder_name', $application->disbursement_account_name ?? ($customer->name ?? '')) }}" required>
        </div>
        <div class="fw-form-group">
            <label>Bank Name <span style="color:red;">*</span></label>
            <input type="text" name="bank_name" class="fw-input" placeholder="e.g. HDFC Bank, ICICI Bank, SBI" value="{{ old('bank_name', $application->disbursement_bank_name ?? '') }}" required>
        </div>
        <div class="fw-form-group">
            <label>Account Number <span style="color:red;">*</span></label>
            <input type="password" id="account_number" name="account_number" class="fw-input" placeholder="Enter Bank Account Number" value="{{ old('account_number', $application->disbursement_account_number ?? '') }}" required>
        </div>
        <div class="fw-form-group">
            <label>Confirm Account Number <span style="color:red;">*</span></label>
            <input type="text" id="confirm_account_number" name="confirm_account_number" class="fw-input" placeholder="Re-enter Bank Account Number" value="{{ old('confirm_account_number', $application->disbursement_account_number ?? '') }}" required>
        </div>
        <div class="fw-form-group">
            <label>IFSC Code <span style="color:red;">*</span></label>
            <input type="text" name="ifsc_code" class="fw-input" placeholder="e.g. HDFC0001234" value="{{ old('ifsc_code', $application->disbursement_ifsc ?? '') }}" style="text-transform: uppercase;" required>
        </div>
        <div class="fw-form-group">
            <label>Account Type <span style="color:red;">*</span></label>
            <select name="account_type" class="fw-input" required>
                <option value="Current" {{ old('account_type', $application->disbursement_account_type ?? 'Current') === 'Current' ? 'selected' : '' }}>Current Account</option>
                <option value="Savings" {{ old('account_type', $application->disbursement_account_type ?? '') === 'Savings' ? 'selected' : '' }}>Savings Account</option>
            </select>
        </div>
        
        <div class="fw-checkbox-group" style="margin-top:20px;">
            <label style="display: flex; align-items: center; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="confirm_ownership" value="1" style="width: 17px; height: 17px; margin-right: 8px; cursor: pointer; accent-color: #2563eb;" required checked>
                I confirm that this bank account belongs to the applicant / business.
            </label>
        </div>
    </form>
</div>
@endsection
@section('sticky-bottom')
<div class="fw-sticky-bottom">
    <button type="submit" form="bizBankDetailsForm" class="fw-btn fw-btn-primary w-100" style="padding: 14px; font-size: 15px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
        Submit for Disbursement
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('bizBankDetailsForm');
    if (!form) return;

    var v = window.FiinwayValidator;
    var nameInp = form.querySelector('input[name="account_holder_name"]');
    var bankInp = form.querySelector('input[name="bank_name"]');
    var accInp = document.getElementById('account_number');
    var confirmInp = document.getElementById('confirm_account_number');
    var ifscInp = form.querySelector('input[name="ifsc_code"]');

    if (accInp) v.setupLiveDigits(accInp, 18);
    if (confirmInp) v.setupLiveDigits(confirmInp, 18);
    if (ifscInp) v.setupLiveIfsc(ifscInp);

    form.addEventListener('submit', function(e) {
        var firstInvalid = null;

        [nameInp, bankInp, accInp, confirmInp, ifscInp].forEach(function(inp) {
            if (inp) v.clearError(inp);
        });

        if (nameInp && !v.isValidName(nameInp.value)) {
            v.showError(nameInp, 'Enter a valid account holder / business name (min 3 characters).');
            if (!firstInvalid) firstInvalid = nameInp;
        }

        if (bankInp && bankInp.value.trim().length < 2) {
            v.showError(bankInp, 'Enter a valid bank name.');
            if (!firstInvalid) firstInvalid = bankInp;
        }

        var accVal = v.cleanDigits(accInp ? accInp.value : '');
        if (!accVal || accVal.length < 9 || accVal.length > 18) {
            v.showError(accInp, 'Account number must be between 9 and 18 digits.');
            if (!firstInvalid) firstInvalid = accInp;
        }

        var confirmVal = v.cleanDigits(confirmInp ? confirmInp.value : '');
        if (confirmVal !== accVal) {
            v.showError(confirmInp, 'Confirm account number must match account number exactly.');
            if (!firstInvalid) firstInvalid = confirmInp;
        }

        if (ifscInp && !v.isValidIfsc(ifscInp.value)) {
            v.showError(ifscInp, 'Enter a valid 11-character IFSC code (e.g. HDFC0001234).');
            if (!firstInvalid) firstInvalid = ifscInp;
        }

        if (firstInvalid) {
            e.preventDefault();
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
    });
});
</script>
@endpush

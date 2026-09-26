@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('admin.finance.applications') }}" style="color: #64748b; font-size: 13px; text-decoration: none;">
                        ← Back to Pipeline
                    </a>
                    <span style="color: #cbd5e1;">/</span>
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700;">
                        APPLICATION INSPECTOR
                    </span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a;">
                    Application #{{ $application->application_number }}
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($application->customer)
                <a href="{{ route('admin.finance.customer-details', $application->customer->id) }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Borrower 360° Profile
                </a>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 py-2 px-3 mb-4" style="border-radius: 6px; background: #ecfdf5; color: #065f46; font-size: 13px; font-weight: 600; border-left: 4px solid #059669 !important;" role="alert">
            <strong>✓ Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 6px 12px; color: #065f46;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="row g-4">
            <!-- Left Column: Details & Proofs -->
            <div class="col-lg-8">
                <!-- Overview Card -->
                <div class="card border-0 mb-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-3" style="font-size: 16px; font-weight: 700; color: #0f172a;">Loan Overview</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Product Category</div>
                            <div style="font-weight: 700; color: #0f172a; margin-top: 4px;">{{ ucwords(str_replace('_', ' ', $application->loan_category)) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Requested Amount</div>
                            <div style="font-weight: 700; color: #0f172a; margin-top: 4px; font-size: 18px;">₹{{ number_format($application->requested_amount) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Current Application Status</div>
                            <div class="mt-1">
                                <span class="badge" style="background: #f1f5f9; color: #0f172a; font-size: 12px; font-weight: 700; padding: 5px 10px; border-radius: 4px;">
                                    {{ str_replace('_', ' ', $application->application_status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Lender Workflow</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">
                                @php
                                    $isExternalLender = in_array($application->loan_category, ['low_cibil_cash', 'prime_cash', 'business_msme', 'cash_loan', 'business_loan']);
                                @endphp
                                @if($isExternalLender)
                                    @if($application->lender)
                                        {{ $application->lender->name }}
                                        <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 10px;">🔒 Locked</span>
                                    @else
                                        <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 11px;">External Lender Required</span>
                                    @endif
                                @else
                                    <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 11px; border: 1px solid #a7f3d0;">
                                        ⚡ Direct Credit (No External Lender)
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Processing Fee</div>
                            <div style="font-weight: 700; color: #0284c7; margin-top: 4px;">
                                ₹{{ number_format($application->processing_fee_total ?: ($application->processing_fee_amount ? $application->processing_fee_amount * 1.18 : 0), 2) }}
                                <span class="badge" style="background: {{ $application->processing_fee_status === 'paid' ? '#ecfdf5' : ($application->processing_fee_status === 'waived' ? '#ede9fe' : '#fef2f2') }}; color: {{ $application->processing_fee_status === 'paid' ? '#065f46' : ($application->processing_fee_status === 'waived' ? '#5b21b6' : '#991b1b') }}; font-size: 11px;">
                                    {{ ucfirst($application->processing_fee_status ?: 'Pending') }}
                                </span>
                            </div>
                            <div style="font-size: 11px; color: #64748b;">
                                Base: ₹{{ number_format($application->processing_fee_amount ?: 0, 2) }} + 18% GST: ₹{{ number_format($application->processing_fee_tax ?: 0, 2) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Validation Status</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">
                                {{ $application->validation_status ? strtoupper($application->validation_status) : 'PENDING' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proof & Verification Images Card (Docs 2 & 5) -->
                <div class="card border-0 mb-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-3" style="font-size: 16px; font-weight: 700; color: #0f172a;">Verification Proofs</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;">
                                    LENDER PROCESS COMPLETION PROOF
                                </div>
                                @if($application->process_proof_file)
                                    <div class="mb-2">
                                        <a href="{{ asset($application->process_proof_file) }}" target="_blank">
                                            <img src="{{ asset($application->process_proof_file) }}" alt="Process Proof" style="max-height: 220px; width: 100%; object-fit: contain; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px;">
                                        </a>
                                    </div>
                                    <div style="font-size: 11px; color: #64748b;">Uploaded: {{ $application->proof_submitted_at ? date('d M Y, H:i', strtotime($application->proof_submitted_at)) : 'N/A' }}</div>
                                @else
                                    <div class="py-4 text-center text-muted" style="font-size: 13px;">
                                        No process completion screenshot uploaded yet.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;">
                                    AGENT / BORROWER VERIFICATION SELFIE
                                </div>
                                @if($application->agent_selfie_file)
                                    <div class="mb-2">
                                        <a href="{{ asset($application->agent_selfie_file) }}" target="_blank">
                                            <img src="{{ asset($application->agent_selfie_file) }}" alt="Agent Selfie" style="max-height: 220px; width: 100%; object-fit: contain; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px;">
                                        </a>
                                    </div>
                                    <div style="font-size: 11px; color: #64748b;">Selfie verified</div>
                                @else
                                    <div class="py-4 text-center text-muted" style="font-size: 13px;">
                                        No agent selfie uploaded.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Disbursement Bank Details -->
                <div class="card border-0 mb-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-3" style="font-size: 16px; font-weight: 700; color: #0f172a;">Disbursement Account Destination</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div style="font-size: 12px; color: #64748b;">Account Holder</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px;">{{ $application->disbursement_account_name ?: '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div style="font-size: 12px; color: #64748b;">Bank Name</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px;">{{ $application->disbursement_bank_name ?: '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div style="font-size: 12px; color: #64748b;">Account Number</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px; font-family: monospace;">{{ $application->disbursement_account_number ?: '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div style="font-size: 12px; color: #64748b;">IFSC Code</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px; font-family: monospace;">{{ $application->disbursement_ifsc ?: '—' }}</div>
                        </div>
                        @if($application->disbursement_upi_id)
                        <div class="col-md-6 mt-2">
                            <div style="font-size: 12px; color: #64748b;">UPI ID Destination</div>
                            <div style="font-weight: 600; color: #0f172a; font-family: monospace;">{{ $application->disbursement_upi_id }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Underwriting Decision Console -->
            <div class="col-lg-4">
                <div class="card border-0 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-3" style="font-size: 16px; font-weight: 700; color: #0f172a;">Underwriting Console</h5>
                    
                    <form method="POST" action="{{ route('admin.finance.application-status', $application->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Update Status</label>
                            <select name="status" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;" required>
                                <option value="VALIDATION_PENDING" {{ $application->application_status === 'VALIDATION_PENDING' ? 'selected' : '' }}>Validation Pending</option>
                                <option value="LOAN_APPROVED" {{ $application->application_status === 'LOAN_APPROVED' ? 'selected' : '' }}>Approve Loan</option>
                                <option value="DISBURSEMENT_PENDING" {{ $application->application_status === 'DISBURSEMENT_PENDING' ? 'selected' : '' }}>Disbursement Pending</option>
                                <option value="DISBURSED" {{ $application->application_status === 'DISBURSED' ? 'selected' : '' }}>Mark as Disbursed</option>
                                <option value="REJECTED" {{ $application->application_status === 'REJECTED' ? 'selected' : '' }}>Reject (Enforce 3-Day Lock)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Approved Amount (₹)</label>
                            <input type="number" name="approved_amount" value="{{ $application->approved_amount ?: $application->requested_amount }}" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">For virtual/zero-cibil, this updates wallet limit.</div>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Bank Txn Reference (Disbursement)</label>
                            <input type="text" name="txn_ref" value="{{ $application->disbursement_txn_ref }}" placeholder="e.g. UTR192837465" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Rejection Reason (If rejecting)</label>
                            <textarea name="rejection_reason" rows="2" class="form-control form-control-sm" placeholder="Reason communicated to borrower..." style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">{{ $application->rejection_reason }}</textarea>
                            <div style="font-size: 11px; color: #dc2626; margin-top: 4px;">Applies 3-day reapply lock per underwriting rules.</div>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Internal Admin Remarks</label>
                            <textarea name="remarks" rows="2" class="form-control form-control-sm" placeholder="Internal underwriting notes..." style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">{{ $application->admin_remarks }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-sm w-100" style="background: #0f172a; color: #ffffff; font-size: 13px; font-weight: 600; border-radius: 6px; padding: 10px;">
                            Save Decision
                        </button>
                    </form>
                </div>

                <!-- Processing Fee Decision Console (Admin Rights) -->
                <div class="card border-0 p-4 mt-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">⚙️ Processing Fee Decision</h5>
                        <span class="badge" style="background: {{ $application->processing_fee_status === 'paid' ? '#ecfdf5' : ($application->processing_fee_status === 'waived' ? '#ede9fe' : '#fef2f2') }}; color: {{ $application->processing_fee_status === 'paid' ? '#065f46' : ($application->processing_fee_status === 'waived' ? '#5b21b6' : '#991b1b') }}; font-size: 11px;">
                            {{ strtoupper($application->processing_fee_status ?: 'PENDING') }}
                        </span>
                    </div>
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 14px;">
                        Admin can decide, adjust, waive, or confirm payment of the processing fee for this application.
                    </p>

                    <form method="POST" action="{{ route('admin.finance.applications.update-fee', $application->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Base Processing Fee (₹)</label>
                            <input type="number" step="0.01" name="processing_fee_amount" id="adminFeeInput" value="{{ $application->processing_fee_amount ?: 2000.00 }}" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;" required>
                            <div id="feeBreakdownPreview" style="font-size: 11px; color: #0284c7; margin-top: 4px;">
                                + 18% GST (₹<span id="taxPreview">{{ number_format(($application->processing_fee_amount ?: 2000.00) * 0.18, 2) }}</span>) = Total: ₹<span id="totalPreview">{{ number_format(($application->processing_fee_amount ?: 2000.00) * 1.18, 2) }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Fee Status</label>
                            <select name="processing_fee_status" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;" required>
                                <option value="pending" {{ $application->processing_fee_status === 'pending' ? 'selected' : '' }}>Pending (Applicant to Pay Online)</option>
                                <option value="paid" {{ $application->processing_fee_status === 'paid' ? 'selected' : '' }}>Mark as Paid (Admin Approved / Offline)</option>
                                <option value="waived" {{ $application->processing_fee_status === 'waived' ? 'selected' : '' }}>Waive Fee (₹0 Total Fee)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Fee Remarks / Reference</label>
                            <input type="text" name="remarks" placeholder="e.g. Approved promotional rate or offline cash receipt" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <button type="submit" class="btn btn-sm w-100" style="background: #0284c7; color: #ffffff; font-size: 13px; font-weight: 600; border-radius: 6px; padding: 9px;">
                            Save Processing Fee Decision
                        </button>
                    </form>
                </div>

                <!-- Document Request Console (Disbursement Stage Bank Docs) -->
                <div class="card border-0 p-4 mt-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-2" style="font-size: 16px; font-weight: 700; color: #0f172a;">Request Documents</h5>
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 14px;">
                        Request bank passbook, statement, or cancelled cheque from borrower at disbursement stage.
                    </p>

                    <form method="POST" action="{{ route('admin.finance.applications.request-document', $application->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Document Type</label>
                            <select name="document_type" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;" required>
                                <option value="bank_passbook">Bank Passbook (First Page with Account & IFSC)</option>
                                <option value="bank_statement">Bank Statement (Last 6 Months)</option>
                                <option value="cancelled_cheque">Cancelled Cheque</option>
                                <option value="salary_slips">Salary Slips (Recent)</option>
                                <option value="gst_certificate">GST Certificate</option>
                                <option value="bonafide_certificate">Bonafide / Enrollment Certificate</option>
                                <option value="other">Other Supporting Document</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Request Reason / Instructions</label>
                            <textarea name="admin_remark" rows="2" class="form-control form-control-sm" placeholder="Please upload bank passbook or statement to confirm disbursement bank account..." style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-sm w-100" style="background: #1a5fa8; color: #ffffff; font-size: 13px; font-weight: 600; border-radius: 6px; padding: 10px;">
                            📩 Send Document Request
                        </button>
                    </form>

                    @if($application->documentRequests && $application->documentRequests->count() > 0)
                    <div class="mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Requested Documents</div>
                        @foreach($application->documentRequests as $req)
                        <div class="p-2 mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 11px;">
                            <div class="d-flex justify-content-between">
                                <strong style="color: #0f172a;">{{ is_array($req->requested_documents) ? implode(', ', $req->requested_documents) : $req->requested_documents }}</strong>
                                <span class="badge" style="background: {{ $req->status === 'verified' ? '#ecfdf5' : '#fef3c7' }}; color: {{ $req->status === 'verified' ? '#065f46' : '#92400e' }};">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </div>
                            <div style="color: #64748b; margin-top: 2px;">{{ $req->admin_remark }}</div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const feeInput = document.getElementById('adminFeeInput');
    const taxPreview = document.getElementById('taxPreview');
    const totalPreview = document.getElementById('totalPreview');

    if (feeInput && taxPreview && totalPreview) {
        feeInput.addEventListener('input', function() {
            const val = parseFloat(this.value) || 0;
            const tax = Math.round(val * 0.18 * 100) / 100;
            const total = Math.round((val + tax) * 100) / 100;
            taxPreview.textContent = tax.toFixed(2);
            totalPreview.textContent = total.toFixed(2);
        });
    }
});
</script>
@endsection

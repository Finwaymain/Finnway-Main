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
                            @if($application->approved_amount && $application->approved_amount > 0)
                                <div style="font-size: 12px; color: #059669; font-weight: 700; margin-top: 2px;">
                                    ✓ Approved Amount: ₹{{ number_format($application->approved_amount) }}
                                </div>
                            @endif
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
                                        <a href="{{ $application->process_proof_file }}" target="_blank">
                                            <img src="{{ $application->process_proof_file }}" alt="Process Proof" style="max-height: 220px; width: 100%; object-fit: contain; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px;">
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
                                        <a href="{{ $application->agent_selfie_file }}" target="_blank">
                                            <img src="{{ $application->agent_selfie_file }}" alt="Agent Selfie" style="max-height: 220px; width: 100%; object-fit: contain; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px;">
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

                <!-- Borrower Submitted & Additional Documents Vault -->
                <div class="card border-0 mb-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">
                                📄 Borrower KYC &amp; Additional Uploaded Documents
                            </h5>
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                Review, verify individual documents or request re-upload
                            </div>
                        </div>
                        @if($application->customer && $application->customer->documents->isNotEmpty())
                        <form method="POST" action="{{ route('admin.finance.applications.verify-all-docs', $application->id) }}" onsubmit="return confirm('Verify all pending documents for this borrower?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success" style="font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 6px;">
                                ✓ Verify All Documents
                            </button>
                        </form>
                        @endif
                    </div>

                    @if($application->application_status === 'DOCS_RESUBMITTED')
                    <div class="alert alert-success py-2 px-3 mb-3 border-0 d-flex align-items-center justify-content-between" style="background: #ecfdf5; color: #065f46; font-size: 13px; border-radius: 6px; border-left: 4px solid #059669 !important;">
                        <div>
                            <strong>🔔 Resubmitted Documents Received!</strong> The applicant has uploaded the requested files. Please inspect each file below and approve the loan.
                        </div>
                    </div>
                    @endif

                    @php
                        $allDocs = $application->customer ? $application->customer->documents : collect();
                    @endphp

                    @if($allDocs->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 13px; vertical-align: middle;">
                            <thead>
                                <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                                    <th style="padding: 10px 12px;">Document Name</th>
                                    <th style="padding: 10px 12px;">Preview</th>
                                    <th style="padding: 10px 12px;">Uploaded</th>
                                    <th style="padding: 10px 12px;">Status</th>
                                    <th style="padding: 10px 12px;">Admin Remark</th>
                                    <th style="padding: 10px 12px; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allDocs as $doc)
                                <tr>
                                    <td style="padding: 10px 12px; font-weight: 600; color: #0f172a;">
                                        {{ ucwords(str_replace('_', ' ', $doc->document_type)) }}
                                        @if($doc->file_name)
                                            <div style="font-size: 11px; color: #64748b; font-weight: 400;">{{ $doc->file_name }}</div>
                                        @endif
                                    </td>
                                    <td style="padding: 10px 12px;">
                                        <a href="{{ $doc->url }}" target="_blank" class="btn btn-sm" style="background: #f1f5f9; color: #0f172a; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 10px; border: 1px solid #cbd5e1; text-decoration: none;">
                                            View File ↗
                                        </a>
                                    </td>
                                    <td style="padding: 10px 12px; color: #64748b; font-size: 12px;">
                                        {{ $doc->created_at ? $doc->created_at->format('d M Y, H:i') : '—' }}
                                    </td>
                                    <td style="padding: 10px 12px;">
                                        @php
                                            $sbg = '#fef3c7'; $sfg = '#92400e';
                                            if ($doc->status === 'verified') { $sbg = '#ecfdf5'; $sfg = '#065f46'; }
                                            elseif ($doc->status === 'rejected') { $sbg = '#fef2f2'; $sfg = '#991b1b'; }
                                            elseif ($doc->status === 'reupload_required') { $sbg = '#fee2e2'; $sfg = '#b91c1c'; }
                                        @endphp
                                        <span class="badge" style="background: {{ $sbg }}; color: {{ $sfg }}; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 4px;">
                                            {{ strtoupper($doc->status) }}
                                        </span>
                                    </td>
                                    <td style="padding: 10px 12px; color: #64748b; font-size: 12px;">
                                        {{ $doc->admin_remark ?: '—' }}
                                    </td>
                                    <td style="padding: 10px 12px; text-align: right;">
                                        <div class="btn-group btn-group-sm">
                                            <form method="POST" action="{{ route('admin.finance.document-review', $doc->id) }}" style="display:inline-block;">
                                                @csrf
                                                <input type="hidden" name="action" value="verify">
                                                <button type="submit" class="btn btn-sm" style="background: #059669; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 4px 0 0 4px;" title="Verify">
                                                    ✓ Verify
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.finance.document-review', $doc->id) }}" style="display:inline-block;">
                                                @csrf
                                                <input type="hidden" name="action" value="reupload">
                                                <button type="submit" class="btn btn-sm" style="background: #d97706; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 0;" title="Request Reupload">
                                                    Reupload
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.finance.document-review', $doc->id) }}" style="display:inline-block;">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-sm" style="background: #dc2626; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 0 4px 4px 0;" title="Reject">
                                                    ✕
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="py-4 text-center text-muted" style="font-size: 13px;">
                        No documents uploaded yet by this borrower.
                    </div>
                    @endif
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
                    
                    @if(in_array($application->application_status, ['VALIDATION_PENDING', 'PROOF_SUBMITTED', 'PARTNER_SELECTED']))
                    <div class="card border-0 mb-3 p-3" style="background: #fefce8; border: 1px solid #fef08a !important; border-radius: 8px;">
                        <div style="font-size: 13px; font-weight: 700; color: #854d0e; margin-bottom: 4px;">
                            ⏱️ Validation Step Active
                        </div>
                        <div style="font-size: 12px; color: #713f12; margin-bottom: 12px;">
                            Borrower is currently in the validation stage. You can verify and accept or reject:
                        </div>
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('admin.finance.application-status', $application->id) }}" style="flex: 1;">
                                @csrf
                                <input type="hidden" name="status" value="SELFIE_PENDING">
                                <button type="submit" class="btn btn-sm w-100" style="background: #059669; color: #fff; font-weight: 600; font-size: 12px; padding: 7px; border-radius: 6px;">
                                    ✓ Accept (Move to Selfie)
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger" style="font-weight: 600; font-size: 12px; padding: 7px; border-radius: 6px;" onclick="document.getElementById('statusSelect').value='REJECTED'; document.getElementById('rejectionReasonInput').focus();">
                                ✕ Reject
                            </button>
                        </div>
                    </div>
                    @endif

                    @if(in_array($application->application_status, ['PROCESSING', 'SELFIE_SUBMITTED', 'UNDERWRITING']))
                    <div class="card border-0 mb-3 p-3" style="background: #eff6ff; border: 1px solid #bfdbfe !important; border-radius: 8px;">
                        <div style="font-size: 13px; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                            📋 Final Underwriting Decision
                        </div>
                        <div style="font-size: 12px; color: #1e3a8a; margin-bottom: 12px;">
                            Verification selfie is recorded. Applicant is currently on the Tracking screen waiting for your decision:
                        </div>
                        <form method="POST" action="{{ route('admin.finance.application-status', $application->id) }}">
                            @csrf
                            <input type="hidden" name="status" value="LOAN_APPROVED">
                            <div class="mb-2">
                                <label style="font-size: 11px; font-weight: 700; color: #1e3a8a;">Approved Loan Amount (₹):</label>
                                <input type="number" name="approved_amount" value="{{ $application->approved_amount ?: $application->requested_amount }}" class="form-control form-control-sm" style="font-weight: 700; font-size: 13px; border: 1px solid #93c5fd; background: #fff;" required>
                                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">Applicant requested: ₹{{ number_format($application->requested_amount ?? 25000) }}</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm w-100" style="background: #059669; color: #fff; font-weight: 700; font-size: 12px; padding: 8px; border-radius: 6px;">
                                    ✓ Approve &amp; Release Loan Now
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" style="font-weight: 600; font-size: 12px; padding: 8px; border-radius: 6px; white-space: nowrap;" onclick="document.getElementById('statusSelect').value='REJECTED'; document.getElementById('rejectionReasonInput').focus();">
                                    ✕ Reject
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                    @if($application->application_status === 'DOCS_RESUBMITTED')
                    <div class="card border-0 mb-3 p-3" style="background: #ecfdf5; border: 1px solid #a7f3d0 !important; border-radius: 8px;">
                        <div style="font-size: 13px; font-weight: 700; color: #065f46; margin-bottom: 4px;">
                            🎉 Additional Documents Resubmitted!
                        </div>
                        <div style="font-size: 12px; color: #047857; margin-bottom: 12px;">
                            Borrower has uploaded the requested documents. Review them in the table and approve the application:
                        </div>
                        <form method="POST" action="{{ route('admin.finance.application-status', $application->id) }}">
                            @csrf
                            <input type="hidden" name="status" value="LOAN_APPROVED">
                            <div class="mb-2">
                                <label style="font-size: 11px; font-weight: 700; color: #065f46;">Approved Amount (₹):</label>
                                <input type="number" name="approved_amount" value="{{ $application->approved_amount ?: $application->requested_amount }}" class="form-control form-control-sm" style="font-weight: 700; font-size: 13px; border: 1px solid #6ee7b7; background: #fff;" required>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm w-100" style="background: #059669; color: #fff; font-weight: 700; font-size: 12px; padding: 8px; border-radius: 6px;">
                                    ✓ Approve Resubmitted Docs &amp; Release Loan
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" style="font-weight: 600; font-size: 12px; padding: 8px; border-radius: 6px; white-space: nowrap;" onclick="document.getElementById('statusSelect').value='REJECTED'; document.getElementById('rejectionReasonInput').focus();">
                                    ✕ Reject
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                    @if($application->application_status === 'ADDITIONAL_DOCS_REQUESTED')
                    <div class="card border-0 mb-3 p-3" style="background: #fffbeb; border: 1px solid #fde68a !important; border-radius: 8px;">
                        <div style="font-size: 13px; font-weight: 700; color: #b45309; margin-bottom: 4px;">
                            ⏳ Waiting for Additional Documents
                        </div>
                        <div style="font-size: 12px; color: #92400e;">
                            Borrower is currently on Step 24 uploading the requested documents.
                        </div>
                    </div>
                    @endif

                    @if(!in_array($application->application_status, ['DISBURSED', 'REJECTED', 'CLOSED']))
                    <div class="card border-0 mb-3 p-3" style="background: #f8fafc; border: 1px solid #cbd5e1 !important; border-radius: 8px;">
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                            📩 Request Additional Documents
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                            Select one or multiple documents to demand from borrower at Step 24:
                        </div>
                        <form method="POST" action="{{ route('admin.finance.applications.request-document', $application->id) }}">
                            @csrf
                            <div class="mb-2">
                                <label style="font-size: 11px; font-weight: 700; color: #475569;">Select Documents Required (Multi-Select):</label>
                                <div style="font-size: 12px; color: #334155; display: grid; grid-template-columns: 1fr; gap: 5px; max-height: 180px; overflow-y: auto; background: #ffffff; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;">
                                    <label style="margin: 0; font-weight: 500; cursor: pointer;"><input type="checkbox" name="document_type[]" value="Latest 3 Months Salary Slips"> 📄 Latest 3 Months Salary Slips</label>
                                    <label style="margin: 0; font-weight: 500; cursor: pointer;"><input type="checkbox" name="document_type[]" value="6 Months Bank Statement (PDF)"> 🏦 6 Months Bank Statement (PDF)</label>
                                    <label style="margin: 0; font-weight: 500; cursor: pointer;"><input type="checkbox" name="document_type[]" value="Bank Passbook (First Page with Account & IFSC)"> 📖 Bank Passbook (Account & IFSC)</label>
                                    <label style="margin: 0; font-weight: 500; cursor: pointer;"><input type="checkbox" name="document_type[]" value="Cancelled Cheque"> 🧾 Cancelled Cheque</label>
                                    <label style="margin: 0; font-weight: 500; cursor: pointer;"><input type="checkbox" name="document_type[]" value="Full Aadhaar Card (Front & Back)"> 🆔 Full Aadhaar Card (Front & Back)</label>
                                    <label style="margin: 0; font-weight: 500; cursor: pointer;"><input type="checkbox" name="document_type[]" value="PAN Card Copy"> 🪪 PAN Card Copy</label>
                                    <label style="margin: 0; font-weight: 500; cursor: pointer;"><input type="checkbox" name="document_type[]" value="Electricity Bill / Rent Agreement"> 🏠 Electricity Bill / Rent Agreement</label>
                                    <label style="margin: 0; font-weight: 500; cursor: pointer;"><input type="checkbox" name="document_type[]" value="Clear Selfie with ID Card"> 🤳 Clear Selfie with ID Card</label>
                                    <label style="margin: 0; font-weight: 500; cursor: pointer;"><input type="checkbox" name="document_type[]" value="Business GST / ITR Certificate"> 💼 Business GST / ITR Certificate</label>
                                    <label style="margin: 0; font-weight: 500; cursor: pointer;"><input type="checkbox" name="document_type[]" value="Bonafide / Enrollment Certificate"> 🎓 Bonafide / Enrollment Certificate</label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label style="font-size: 11px; font-weight: 700; color: #475569;">Other / Custom Document (Optional):</label>
                                <input type="text" name="custom_document" class="form-control form-control-sm" placeholder="e.g. Property Tax Receipt or Form 16" style="font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            </div>
                            <div class="mb-2">
                                <label style="font-size: 11px; font-weight: 700; color: #475569;">Instructions / Remarks for Borrower:</label>
                                <textarea name="admin_remark" rows="2" class="form-control form-control-sm" placeholder="e.g. Bank statement must show salary credit and be original downloaded PDF" style="font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm w-100" style="background: #2563eb; color: #fff; font-weight: 700; font-size: 12px; padding: 8px; border-radius: 6px;">
                                📤 Send Document Request
                            </button>
                        </form>

                        @if($application->documentRequests && $application->documentRequests->count() > 0)
                        <div class="mt-3 pt-2" style="border-top: 1px dashed #cbd5e1;">
                            <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Requested Documents History</div>
                            @foreach($application->documentRequests->sortByDesc('id') as $req)
                            <div class="p-2 mb-2" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong style="color: #0f172a;">
                                        @php
                                            $reqDocs = is_array($req->requested_documents) ? $req->requested_documents : (json_decode($req->requested_documents, true) ?: [$req->requested_documents]);
                                        @endphp
                                        {{ implode(', ', (array)$reqDocs) }}
                                    </strong>
                                    <span class="badge" style="background: {{ $req->status === 'verified' ? '#ecfdf5' : ($req->status === 'submitted' ? '#dbeafe' : '#fef3c7') }}; color: {{ $req->status === 'verified' ? '#065f46' : ($req->status === 'submitted' ? '#1e40af' : '#92400e') }}; font-size: 10px;">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </div>
                                @if($req->admin_remark)
                                <div style="color: #64748b; margin-top: 2px;">{{ $req->admin_remark }}</div>
                                @endif
                                <div style="color: #94a3b8; font-size: 10px; margin-top: 2px;">Requested: {{ $req->created_at ? $req->created_at->format('d M, H:i') : '' }}</div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif

                    <form method="POST" action="{{ route('admin.finance.application-status', $application->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Update Status</label>
                            <select name="status" id="statusSelect" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;" required>
                                <option value="VALIDATION_PENDING" {{ $application->application_status === 'VALIDATION_PENDING' ? 'selected' : '' }}>Validation Pending</option>
                                <option value="SELFIE_PENDING" {{ in_array($application->application_status, ['SELFIE_PENDING', 'VALIDATION_APPROVED']) ? 'selected' : '' }}>✓ Accept &amp; Require Agent Selfie (Step 17)</option>
                                <option value="PROCESSING" {{ in_array($application->application_status, ['PROCESSING', 'SELFIE_SUBMITTED', 'UNDERWRITING']) ? 'selected' : '' }}>Underwriting Review in Progress (Step 18)</option>
                                <option value="DOCS_RESUBMITTED" {{ $application->application_status === 'DOCS_RESUBMITTED' ? 'selected' : '' }}>Additional Docs Resubmitted (Step 25)</option>
                                <option value="ADDITIONAL_DOCS_REQUESTED" {{ $application->application_status === 'ADDITIONAL_DOCS_REQUESTED' ? 'selected' : '' }}>Request Additional Docs (Step 24)</option>
                                <option value="LOAN_APPROVED" {{ $application->application_status === 'LOAN_APPROVED' ? 'selected' : '' }}>Approve Loan (Step 21)</option>
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
                            <textarea name="rejection_reason" id="rejectionReasonInput" rows="2" class="form-control form-control-sm" placeholder="Reason communicated to borrower..." style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">{{ $application->rejection_reason }}</textarea>
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

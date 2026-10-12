@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('admin.finance.large_funding.index') }}" style="color: #64748b; font-size: 13px; text-decoration: none;">
                        ← Back to Pipeline
                    </a>
                    <span style="color: #cbd5e1;">/</span>
                    <span class="badge" style="background: #4f46e5; color: #ffffff; font-size: 11px; font-weight: 700;">
                        SYNDICATE DOSSIER INSPECTOR
                    </span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a;">
                    Application #{{ $application->application_number }} — {{ $application->business_name }}
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 13px; font-weight: 700; padding: 8px 14px; border-radius: 6px;">
                    Current: Stage {{ $application->current_stage }} ({{ $application->stage_label }})
                </span>
                <form method="POST" action="{{ route('admin.finance.large_funding.destroy', $application->id) }}" onsubmit="return confirm('Permanently delete application {{ $application->application_number }} and all associated files?');" style="display:inline-block; margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-size: 12px; font-weight: 700; border-radius: 6px; padding: 7px 12px; cursor: pointer;">
                        🗑 Delete Application
                    </button>
                </form>
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

        @if($errors->any())
        <div class="alert alert-danger border-0 py-2 px-3 mb-4" style="border-radius: 6px; background: #fef2f2; color: #991b1b; font-size: 13px;">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="row g-4">
            {{-- LEFT COLUMN: DOSSIER DETAILS (Forms 1 to 6) --}}
            <div class="col-lg-8">
                
                <!-- Overview Card -->
                <div class="card border-0 mb-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-3" style="font-size: 16px; font-weight: 700; color: #0f172a;">🏢 Enterprise &amp; Funding Overview</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Business / Startup Name</div>
                            <div style="font-weight: 700; color: #0f172a; font-size: 15px; margin-top: 2px;">{{ $application->business_name }}</div>
                            <div style="font-size: 11px; color: #4f46e5; font-weight: 600;">{{ $application->business_category }} · {{ $application->business_type }}</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Requested Funding Amount</div>
                            <div style="font-weight: 800; color: #0f172a; font-size: 18px; margin-top: 2px;">{{ $application->required_funding_amount }}</div>
                            <div style="font-size: 11px; color: #64748b;">{{ $application->funding_type }}</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Approved Amount (If Sanctioned)</div>
                            <div style="font-weight: 800; color: #059669; font-size: 18px; margin-top: 2px;">
                                {{ $application->approved_amount ? '₹' . number_format($application->approved_amount) : 'Pending Sanction' }}
                            </div>
                            <div style="font-size: 11px; color: #059669;">{{ $application->approved_investor_name ?: 'Investor syndicate' }}</div>
                        </div>

                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Registration Status</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px;">
                                {{ $application->business_registration_status }}
                                @if($application->registration_date)
                                    ({{ $application->registration_date->format('d M Y') }})
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Funding Purpose</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px;">{{ $application->funding_purpose }} ({{ $application->funding_timeline }})</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Website / App Link</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px;">
                                @if($application->website_or_app_link)
                                    <a href="{{ $application->website_or_app_link }}" target="_blank" style="color:#2563eb;">{{ $application->website_or_app_link }} ↗</a>
                                @else
                                    <span style="color:#94a3b8;">Not Provided</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 pt-3" style="border-top: 1px solid #f1f5f9;">
                        <div style="font-size: 12px; color: #64748b; font-weight: 600;">Business Description:</div>
                        <p style="font-size: 13px; color: #1e293b; margin: 4px 0 0; line-height: 1.45;">{{ $application->business_description }}</p>
                    </div>

                    <div class="mt-2 pt-2" style="border-top: 1px solid #f1f5f9;">
                        <div style="font-size: 12px; color: #64748b; font-weight: 600;">Expected Use of Funds:</div>
                        <p style="font-size: 13px; color: #1e293b; margin: 4px 0 0; line-height: 1.45;">{{ $application->expected_use_of_funds }}</p>
                    </div>
                </div>

                <!-- Applicant & Founders Card -->
                <div class="card border-0 mb-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-3" style="font-size: 16px; font-weight: 700; color: #0f172a;">👥 Applicant &amp; Founding Team ({{ $application->total_founders_count }} Partners)</h5>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Main Applicant</div>
                            <div style="font-weight: 700; color: #0f172a;">{{ $application->applicant_name }}</div>
                            <div style="font-size: 12px; color: #64748b;">{{ $application->applicant_role }}</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Contact</div>
                            <div style="font-weight: 600; color: #0f172a;">{{ $application->mobile_number }}</div>
                            <div style="font-size: 12px; color: #64748b;">{{ $application->email }}</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">PAN &amp; Location</div>
                            <div style="font-weight: 700; color: #0f172a; font-family: monospace;">{{ $application->pan_number }}</div>
                            <div style="font-size: 12px; color: #64748b;">{{ $application->city }}, {{ $application->state }} - {{ $application->pincode }}</div>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px;">
                        <div style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                            Core Partners / Co-Founders Breakdown:
                        </div>
                        @if(!empty($application->founders_json) && is_array($application->founders_json))
                            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                                @foreach($application->founders_json as $f)
                                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 12px; font-size: 12.5px;">
                                    <strong>{{ $f['role'] ?? 'Partner' }}:</strong> {{ $f['name'] ?? '—' }}
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div style="font-size: 12px; color: #64748b;">{{ $application->applicant_name }} (Sole Founder)</div>
                        @endif
                    </div>
                </div>

                <!-- Financial Health & Bank Card -->
                <div class="card border-0 mb-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-3" style="font-size: 16px; font-weight: 700; color: #0f172a;">📊 Financial Health &amp; Corporate Bank Account</h5>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Business Stage</div>
                            <div style="font-weight: 700; color: #0f172a;">{{ $application->business_stage }}</div>
                            <div style="font-size: 11px; color: #64748b;">Since: {{ $application->business_start_date }}</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Current Monthly Revenue</div>
                            <div style="font-weight: 700; color: #0f172a;">{{ $application->monthly_revenue }}</div>
                            <div style="font-size: 11px; color: #64748b;">Turnover: {{ $application->annual_turnover ?: 'Not specified' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 12px; color: #64748b;">Profit / Loss Status</div>
                            <div style="font-weight: 700; color: #0f172a;">{{ $application->current_profit_loss ?: 'Not specified' }}</div>
                            <div style="font-size: 11px; color: #64748b;">Post-funding target: {{ $application->expected_revenue_after_funding ?: '—' }}</div>
                        </div>
                    </div>

                    <!-- Destination Bank Details -->
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px;">
                        <div style="font-size: 12px; font-weight: 700; color: #1e40af; margin-bottom: 6px;">Disbursement Destination Bank Account:</div>
                        <div class="row g-2" style="font-size: 12.5px;">
                            <div class="col-md-4"><strong>Bank:</strong> {{ $application->bank_name ?: '—' }}</div>
                            <div class="col-md-4"><strong>A/C Holder:</strong> {{ $application->bank_account_holder_name ?: '—' }}</div>
                            <div class="col-md-4"><strong>A/C No:</strong> <span style="font-family: monospace; font-weight: 700;">{{ $application->bank_account_number ?: '—' }}</span> (IFSC: {{ $application->bank_ifsc }})</div>
                        </div>
                    </div>
                </div>

                <!-- Dossier Documents & Pitch Deck Card (With Individual Document Approval Actions) -->
                <div class="card border-0 mb-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">📁 Uploaded Dossier Documents &amp; Pitch Decks</h5>
                        
                        <!-- Verify All Documents Button Form (ZERO MODAL) -->
                        <form method="POST" action="{{ route('admin.finance.large_funding.verify_all_docs', $application->id) }}" onsubmit="return confirm('Verify and approve all uploaded documents for this application?');">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background: #10b981; color: white; font-weight: 700; font-size: 12px; border-radius: 6px; padding: 6px 14px;">
                                ✓ Verify All Documents &amp; Project
                            </button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" style="font-size: 12.5px;">
                            <thead>
                                <tr style="background: #f8fafc; font-weight: 600;">
                                    <th>Document Type</th>
                                    <th>File Preview</th>
                                    <th>Status</th>
                                    <th style="width: 250px;">Inline Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($application->documents as $doc)
                                <tr>
                                    <td style="font-weight: 600; color: #1e293b;">
                                        {{ $doc->document_name }}
                                        <div style="font-size: 11px; color: #64748b;">{{ $doc->original_name }} ({{ $doc->file_size }})</div>
                                    </td>
                                    <td>
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm" style="background: #f1f5f9; color: #2563eb; font-weight: 600; font-size: 11px; padding: 4px 10px;">
                                            Open / Download ↗
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: {{ $doc->status === 'verified' ? '#ecfdf5' : ($doc->status === 'rejected' ? '#fef2f2' : '#fffbeb') }}; color: {{ $doc->status === 'verified' ? '#065f46' : ($doc->status === 'rejected' ? '#991b1b' : '#b45309') }}; font-size: 11px;">
                                            {{ strtoupper($doc->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Inline Action Form (ZERO MODAL) -->
                                        <form method="POST" action="{{ route('admin.finance.large_funding.review_doc', $doc->id) }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="text" name="admin_feedback" value="{{ $doc->admin_feedback }}" placeholder="Feedback note..." class="form-control form-control-sm" style="font-size: 11px; width: 130px;">
                                            @if($doc->status !== 'verified')
                                            <button type="submit" name="status" value="verified" class="btn btn-sm btn-success" style="font-size: 11px; padding: 3px 8px;" title="Approve">
                                                ✓
                                            </button>
                                            @endif
                                            @if($doc->status !== 'rejected')
                                            <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger" style="font-size: 11px; padding: 3px 8px;" title="Reject">
                                                ✕
                                            </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">No files uploaded.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: ZERO-MODAL ACTION CARDS --}}
            <div class="col-lg-4">

                {{-- ACTION CARD 1: SERVICE FEE ADJUSTMENT (ZERO MODAL) --}}
                <div class="card border-0 mb-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">⚙️ Service Fee Decision</h5>
                        <span class="badge" style="background: {{ $application->fee_payment_status === 'paid' ? '#ecfdf5' : ($application->fee_payment_status === 'waived' ? '#ede9fe' : '#fef2f2') }}; color: {{ $application->fee_payment_status === 'paid' ? '#065f46' : ($application->fee_payment_status === 'waived' ? '#5b21b6' : '#991b1b') }}; font-size: 11px;">
                            {{ strtoupper($application->fee_payment_status) }}
                        </span>
                    </div>
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 14px;">
                        Adjust or verify the syndicate underwriting service fee (includes 18% GST).
                    </p>

                    <form method="POST" action="{{ route('admin.finance.large_funding.update_fee', $application->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Base Service Fee (₹)</label>
                            <input type="number" step="0.01" name="service_fee_base" id="adminBaseFee" value="{{ $application->service_fee_base ?: 25000 }}" class="form-control form-control-sm" style="font-size: 13px;" required>
                            <div id="feeLivePreview" style="font-size: 11px; color: #4f46e5; margin-top: 4px; font-weight: 600;">
                                + 18% GST (₹<span id="gstPreview">{{ number_format(($application->service_fee_base ?: 25000) * 0.18, 2) }}</span>) = Total: ₹<span id="totalFeePreview">{{ number_format(($application->service_fee_base ?: 25000) * 1.18, 2) }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Payment Deadline (Optional)</label>
                            <input type="datetime-local" name="fee_deadline" value="{{ $application->fee_deadline ? $application->fee_deadline->format('Y-m-d\TH:i') : '' }}" class="form-control form-control-sm" style="font-size: 13px;">
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Fee Status Decision</label>
                            <select name="fee_payment_status" class="form-control form-control-sm" style="font-size: 13px;" required>
                                <option value="pending" {{ $application->fee_payment_status === 'pending' ? 'selected' : '' }}>Pending (Borrower Pays Online)</option>
                                <option value="paid" {{ $application->fee_payment_status === 'paid' ? 'selected' : '' }}>Mark as Paid (Direct Receipt / Admin Cleared)</option>
                                <option value="waived" {{ $application->fee_payment_status === 'waived' ? 'selected' : '' }}>Waive Fee (₹0 Free Assessment)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Remarks / Reference</label>
                            <input type="text" name="remarks" placeholder="Receipt UTR or note..." class="form-control form-control-sm" style="font-size: 13px;">
                        </div>

                        <button type="submit" class="btn btn-sm w-100" style="background: #4f46e5; color: white; font-weight: 700; font-size: 13px; padding: 10px; border-radius: 6px;">
                            Save Fee Decision
                        </button>
                    </form>
                </div>

                {{-- ACTION CARD 2: REALTIME PIPELINE STAGE CONTROLLER (ZERO MODAL) --}}
                <div class="card border-0 mb-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-2" style="font-size: 16px; font-weight: 700; color: #0f172a;">🚀 Pipeline Stage Progression</h5>
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 14px;">
                        Directly transition borrower through Stages 1 to 8 in real-time.
                    </p>

                    <form method="POST" action="{{ route('admin.finance.large_funding.update_stage', $application->id) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Select Target Stage</label>
                            <select name="stage" id="stageSelect" class="form-control form-control-sm" style="font-weight: 700; font-size: 13px;" onchange="handleStageChange(this.value)" required>
                                <option value="1" {{ $application->current_stage == 1 ? 'selected' : '' }}>1. Application Submitted</option>
                                <option value="2" {{ $application->current_stage == 2 ? 'selected' : '' }}>2. Documents & Project Review</option>
                                <option value="3" {{ $application->current_stage == 3 ? 'selected' : '' }}>3. Service Fee Payment</option>
                                <option value="4" {{ $application->current_stage == 4 ? 'selected' : '' }}>4. Forwarded to Investors / Lenders</option>
                                <option value="5" {{ $application->current_stage == 5 ? 'selected' : '' }}>5. Funding Discussion</option>
                                <option value="6" {{ $application->current_stage == 6 ? 'selected' : '' }}>6. Final Offer & Approval</option>
                                <option value="7" {{ $application->current_stage == 7 ? 'selected' : '' }}>7. Funding Approved (Screen Transitions!)</option>
                                <option value="8" {{ $application->current_stage == 8 ? 'selected' : '' }}>8. Amount Credited (Disbursed)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Status Keyword</label>
                            <select name="status" id="statusSelect" class="form-control form-control-sm" style="font-size: 13px;" required>
                                <option value="SUBMITTED" {{ $application->application_status === 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED</option>
                                <option value="DOCUMENTS_VERIFIED" {{ $application->application_status === 'DOCUMENTS_VERIFIED' ? 'selected' : '' }}>DOCUMENTS_VERIFIED</option>
                                <option value="FEE_PENDING" {{ $application->application_status === 'FEE_PENDING' ? 'selected' : '' }}>FEE_PENDING</option>
                                <option value="FEE_PAID" {{ $application->application_status === 'FEE_PAID' ? 'selected' : '' }}>FEE_PAID</option>
                                <option value="FORWARDED_TO_INVESTORS" {{ $application->application_status === 'FORWARDED_TO_INVESTORS' ? 'selected' : '' }}>FORWARDED_TO_INVESTORS</option>
                                <option value="FUNDING_DISCUSSION" {{ $application->application_status === 'FUNDING_DISCUSSION' ? 'selected' : '' }}>FUNDING_DISCUSSION</option>
                                <option value="FINAL_OFFER" {{ $application->application_status === 'FINAL_OFFER' ? 'selected' : '' }}>FINAL_OFFER</option>
                                <option value="FUND_APPROVED" {{ $application->application_status === 'FUND_APPROVED' ? 'selected' : '' }}>FUND_APPROVED (Celebration Screen)</option>
                                <option value="DISBURSED" {{ $application->application_status === 'DISBURSED' ? 'selected' : '' }}>DISBURSED</option>
                                <option value="REJECTED" {{ $application->application_status === 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                            </select>
                        </div>

                        <!-- Stage 7 Dynamic Box: Approved Amount & Syndicate Entity -->
                        <div id="stage7ApprovalBox" style="display: {{ $application->current_stage >= 7 ? 'block' : 'none' }}; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 12px; margin-bottom: 14px;">
                            <div style="font-size: 12px; font-weight: 800; color: #166534; margin-bottom: 8px;">
                                🏆 Sanction Approval Details (Shown on User Screen)
                            </div>
                            <div class="mb-2">
                                <label style="font-size: 11px; font-weight: 700; color: #166534;">Sanctioned Amount (₹):</label>
                                <input type="number" step="0.01" name="approved_amount" value="{{ $application->approved_amount ?: 100000000 }}" class="form-control form-control-sm" placeholder="e.g. 100000000 for 10 Cr" style="font-weight: 700;">
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 700; color: #166534;">Investor Entity Name:</label>
                                <input type="text" name="approved_investor_name" value="{{ $application->approved_investor_name ?: 'Fiinway Capital Partners & Syndicate' }}" class="form-control form-control-sm">
                            </div>
                        </div>

                        <!-- Stage 8 Dynamic Box: Disbursement UTR -->
                        <div id="stage8DisbursementBox" style="display: {{ $application->current_stage == 8 ? 'block' : 'none' }}; background: #eff6ff; border: 1.5px solid #93c5fd; border-radius: 6px; padding: 12px; margin-bottom: 14px;">
                            <div style="font-size: 12px; font-weight: 800; color: #1e40af; margin-bottom: 8px;">
                                💳 Bank Transfer Reference
                            </div>
                            <label style="font-size: 11px; font-weight: 700; color: #1e40af;">Banking UTR / Transaction ID:</label>
                            <input type="text" name="disbursement_utr" value="{{ $application->disbursement_utr }}" placeholder="e.g. UTR8273619283" class="form-control form-control-sm">
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Admin Underwriting Notes</label>
                            <textarea name="admin_notes" rows="2" class="form-control form-control-sm" placeholder="Internal committee notes...">{{ $application->admin_notes }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-sm w-100" style="background: #0f172a; color: white; font-weight: 700; font-size: 13px; padding: 10px; border-radius: 6px;">
                            Update Pipeline Milestone
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseInput = document.getElementById('adminBaseFee');
    const gstPreview = document.getElementById('gstPreview');
    const totalPreview = document.getElementById('totalFeePreview');

    if (baseInput && gstPreview && totalPreview) {
        baseInput.addEventListener('input', function() {
            const val = parseFloat(this.value) || 0;
            const gst = Math.round(val * 0.18 * 100) / 100;
            const tot = Math.round((val + gst) * 100) / 100;
            gstPreview.textContent = gst.toLocaleString('en-IN', {minimumFractionDigits: 2});
            totalPreview.textContent = tot.toLocaleString('en-IN', {minimumFractionDigits: 2});
        });
    }
});

function handleStageChange(stage) {
    const s7Box = document.getElementById('stage7ApprovalBox');
    const s8Box = document.getElementById('stage8DisbursementBox');
    const statusSelect = document.getElementById('statusSelect');

    const statusMap = {
        '1': 'SUBMITTED',
        '2': 'DOCUMENTS_VERIFIED',
        '3': 'FEE_PENDING',
        '4': 'FORWARDED_TO_INVESTORS',
        '5': 'FUNDING_DISCUSSION',
        '6': 'FINAL_OFFER',
        '7': 'FUND_APPROVED',
        '8': 'DISBURSED'
    };

    if (statusMap[stage]) {
        statusSelect.value = statusMap[stage];
    }

    if (parseInt(stage) >= 7) {
        s7Box.style.display = 'block';
    } else {
        s7Box.style.display = 'none';
    }

    if (parseInt(stage) === 8) {
        s8Box.style.display = 'block';
    } else {
        s8Box.style.display = 'none';
    }
}
</script>
@endsection

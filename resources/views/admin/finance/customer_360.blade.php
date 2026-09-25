@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Back & Title -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('admin.finance.customers') }}" style="color: #64748b; font-size: 13px; text-decoration: none;">
                        ← Back to Borrowers
                    </a>
                    <span style="color: #cbd5e1;">/</span>
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700;">
                        CUSTOMER 360° MASTER
                    </span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a;">
                    {{ $customer->name }}
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form method="POST" action="{{ route('admin.finance.toggle-lock', $customer->id) }}">
                    @csrf
                    @php
                        $wallet = $customer->wallets->where('wallet_type', 'virtual_loan')->first();
                        $isLocked = $wallet && $wallet->today_usage_permission === 'LOCKED';
                    @endphp
                    <button type="submit" class="btn btn-sm" style="background: {{ $isLocked ? '#059669' : '#dc2626' }}; color: #ffffff; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px;">
                        {{ $isLocked ? 'Unlock Daily Credit Usage' : 'Lock Daily Credit Usage' }}
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

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 py-2 px-3 mb-4" style="border-radius: 6px; background: #fef2f2; color: #991b1b; font-size: 13px; font-weight: 600; border-left: 4px solid #ef4444 !important;" role="alert">
            <strong>⚠ Error:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 6px 12px; color: #991b1b;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <!-- Profile Overview Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Identity &amp; Linking</div>
                    <div class="mt-2" style="font-size: 14px; font-weight: 600; color: #0f172a;">{{ $customer->phone }}</div>
                    <div class="mt-1" style="font-size: 12px; color: #64748b;">
                        Type: <span class="badge" style="background: #f1f5f9; color: #334155;">{{ ucfirst($customer->user_type) }}</span>
                        @if($customer->driver_id)
                            • Driver ID #{{ $customer->driver_id }}
                        @elseif($customer->user_id)
                            • User ID #{{ $customer->user_id }}
                        @endif
                    </div>
                    <div class="mt-2" style="font-size: 12px; color: #64748b;">
                        PAN: <strong style="color: #0f172a;">{{ $customer->pan ?: 'Not linked' }}</strong>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Credit Health</div>
                    <div class="mt-2" style="font-size: 20px; font-weight: 700; color: #0f172a;">
                        {{ $customer->cibil_score ? $customer->cibil_score : 'Not Scored' }}
                    </div>
                    <div class="mt-1" style="font-size: 12px; color: #64748b;">
                        Bracket: <strong style="color: #0f172a;">{{ ucfirst($customer->cibil_bracket) }}</strong>
                    </div>
                    <div class="mt-1" style="font-size: 12px; color: #64748b;">
                        KYC: <span class="badge" style="background: {{ $customer->kyc_status === 'VERIFIED' ? '#ecfdf5' : '#fef3c7' }}; color: {{ $customer->kyc_status === 'VERIFIED' ? '#065f46' : '#92400e' }};">{{ $customer->kyc_status }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Virtual Credit Line</div>
                    <div class="mt-2" style="font-size: 20px; font-weight: 700; color: #059669;">
                        ₹{{ number_format($customer->virtual_credit_limit) }}
                    </div>
                    <div class="mt-1" style="font-size: 12px; color: #64748b;">
                        Available: <strong>₹{{ number_format($wallet->available_balance ?? 0) }}</strong>
                    </div>
                    <div class="mt-1" style="font-size: 12px;">
                        Today Usage: 
                        <span class="badge" style="background: {{ ($wallet && $wallet->today_usage_permission === 'ACTIVE') ? '#ecfdf5' : '#fef2f2' }}; color: {{ ($wallet && $wallet->today_usage_permission === 'ACTIVE') ? '#065f46' : '#991b1b' }};">
                            {{ $wallet->today_usage_permission ?? 'INACTIVE' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Student / Business Profile</div>
                    <div class="mt-2" style="font-size: 13px; color: #334155;">
                        Student Mode: <strong>{{ $customer->is_student ? 'Active (' . $customer->college_name . ')' : 'No' }}</strong>
                    </div>
                    <div class="mt-1" style="font-size: 13px; color: #334155;">
                        Business: <strong>{{ $customer->business_name ? $customer->business_name . ' (' . $customer->business_type . ')' : 'Individual' }}</strong>
                    </div>
                    @if($customer->gst_number)
                    <div class="mt-1" style="font-size: 12px; color: #64748b;">
                        GST: <span style="font-family: monospace;">{{ $customer->gst_number }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Section 1: Common KYC Vault (5-Day Reuse Engine) -->
        <div class="card border-0 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="p-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #e2e8f0;">
                <div>
                    <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">Common KYC Document Vault</h5>
                    <div style="font-size: 12px; color: #64748b;">Smart 5-day document reuse engine across all loan products</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                            <th style="padding: 12px 16px;">Document Type</th>
                            <th style="padding: 12px 16px;">File Preview</th>
                            <th style="padding: 12px 16px;">Uploaded Date</th>
                            <th style="padding: 12px 16px;">Vault Validity (5 Days)</th>
                            <th style="padding: 12px 16px;">Verification Status</th>
                            <th style="padding: 12px 16px;">Admin Remark</th>
                            <th style="padding: 12px 16px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->documents as $doc)
                        <tr>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                                {{ ucwords(str_replace('_', ' ', $doc->document_type)) }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-sm" style="background: #f1f5f9; color: #0f172a; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 3px 8px; border: 1px solid #cbd5e1;">
                                    View File ↗
                                </a>
                            </td>
                            <td style="padding: 12px 16px; color: #64748b;">
                                {{ $doc->created_at->format('d M Y, H:i') }}
                            </td>
                            <td style="padding: 12px 16px;">
                                @if($doc->isReusable())
                                    <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 600;">
                                        Reusable (Expires {{ $doc->valid_until->format('d M') }})
                                    </span>
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #94a3b8; font-size: 11px;">
                                        Expired Vault
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px;">
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
                            <td style="padding: 12px 16px; color: #64748b; font-size: 12px;">
                                {{ $doc->admin_remark ?: '—' }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="{{ route('admin.finance.document-review', $doc->id) }}" style="display:inline-block;">
                                        @csrf
                                        <input type="hidden" name="action" value="verify">
                                        <button type="submit" class="btn btn-sm" style="background: #059669; color: #fff; font-size: 11px; border-radius: 4px 0 0 4px; padding: 3px 8px;" title="Approve">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.finance.document-review', $doc->id) }}" style="display:inline-block;">
                                        @csrf
                                        <input type="hidden" name="action" value="reupload">
                                        <button type="submit" class="btn btn-sm" style="background: #d97706; color: #fff; font-size: 11px; border-radius: 0; padding: 3px 8px;" title="Reupload">
                                            Reupload
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.finance.document-review', $doc->id) }}" style="display:inline-block;">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm" style="background: #dc2626; color: #fff; font-size: 11px; border-radius: 0 4px 4px 0; padding: 3px 8px;" title="Reject">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-3 text-muted">
                                No KYC documents uploaded to vault yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Loan Applications -->
        <div class="card border-0 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">Loan Applications Pipeline</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                            <th style="padding: 12px 16px;">App Number</th>
                            <th style="padding: 12px 16px;">Product</th>
                            <th style="padding: 12px 16px;">Requested</th>
                            <th style="padding: 12px 16px;">Approved</th>
                            <th style="padding: 12px 16px;">Status</th>
                            <th style="padding: 12px 16px;">Fee Paid</th>
                            <th style="padding: 12px 16px;">Date</th>
                            <th style="padding: 12px 16px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->applications as $app)
                        <tr>
                            <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">
                                {{ $app->application_number }}
                            </td>
                            <td style="padding: 12px 16px;">
                                {{ $app->product->title ?? ucwords(str_replace('_', ' ', $app->loan_category)) }}
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                                ₹{{ number_format($app->requested_amount) }}
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #059669;">
                                {{ $app->approved_amount ? '₹' . number_format($app->approved_amount) : '—' }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 4px;">
                                    {{ str_replace('_', ' ', $app->application_status) }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: {{ $app->fee_payment_status === 'paid' ? '#ecfdf5' : '#fef2f2' }}; color: {{ $app->fee_payment_status === 'paid' ? '#065f46' : '#991b1b' }};">
                                    {{ ucfirst($app->fee_payment_status) }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; color: #64748b;">
                                {{ $app->created_at->format('d M Y') }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <a href="{{ route('admin.finance.application-details', $app->id) }}" class="btn btn-sm" style="background: #0f172a; color: #fff; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 10px;">
                                    Inspect Flow
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-3 text-muted">
                                No loan applications on file.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 3: Daily Repayment Schedule (Doc 5 Engine) -->
        <div class="card border-0 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">Daily Recovery Schedule &amp; Lock Ledger</h5>
                <div style="font-size: 12px; color: #64748b;">Daily usage lock automatically activates when overdue</div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                            <th style="padding: 12px 16px;">Day #</th>
                            <th style="padding: 12px 16px;">Schedule Date</th>
                            <th style="padding: 12px 16px;">Daily EMI Due</th>
                            <th style="padding: 12px 16px;">Amount Paid</th>
                            <th style="padding: 12px 16px;">Status</th>
                            <th style="padding: 12px 16px;">Lock Impact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->dailySchedules as $sch)
                        <tr>
                            <td style="padding: 12px 16px; font-weight: 600;">Day {{ $sch->day_number }}</td>
                            <td style="padding: 12px 16px;">{{ date('d M Y', strtotime($sch->schedule_date)) }}</td>
                            <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">₹{{ number_format($sch->total_due, 2) }}</td>
                            <td style="padding: 12px 16px; color: #059669; font-weight: 600;">₹{{ number_format($sch->paid_amount, 2) }}</td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: {{ $sch->status === 'paid' ? '#ecfdf5' : '#fef2f2' }}; color: {{ $sch->status === 'paid' ? '#065f46' : '#991b1b' }}; font-weight: 700; font-size: 11px;">
                                    {{ strtoupper($sch->status) }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; font-size: 12px;">
                                @if($sch->status === 'paid')
                                    <span style="color: #059669; font-weight: 600;">✓ Usage Active</span>
                                @else
                                    <span style="color: #dc2626; font-weight: 600;">🔒 Daily Usage Locked</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">
                                No active daily repayment schedules.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

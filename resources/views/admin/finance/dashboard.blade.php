@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        INSTITUTIONAL FINANCE
                    </span>
                    <span style="color: #64748b; font-size: 13px; font-weight: 600;">Ecosystem Operations</span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Credit &amp; Lending Dashboard
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.finance.products') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    ⚙️ Products &amp; Fees
                </a>
                <a href="{{ route('admin.finance.recovery') }}" class="btn btn-sm" style="background: #0f172a; color: #ffffff; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px;">
                    Daily Recovery Center
                </a>
                <a href="{{ route('admin.finance.applications') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Review Applications
                </a>
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

        <!-- Metrics Grid -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Master Customers</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <div style="font-size: 26px; font-weight: 700; color: #0f172a;">{{ number_format($totalCustomers) }}</div>
                        <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 11px; font-weight: 600;">
                            {{ $totalDrivers }} Drivers • {{ $totalUsers }} Users
                        </span>
                    </div>
                    <div class="mt-2" style="font-size: 12px; color: #94a3b8;">Unified Single Master with KYC Vault</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Active Applications</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <div style="font-size: 26px; font-weight: 700; color: #0f172a;">{{ number_format($activeApplications) }}</div>
                        <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 700;">
                            {{ $pendingValidation }} Need Verification
                        </span>
                    </div>
                    <div class="mt-2" style="font-size: 12px; color: #94a3b8;">{{ $pendingDisbursement }} awaiting disbursement</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Disbursed</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <div style="font-size: 26px; font-weight: 700; color: #059669;">₹{{ number_format($totalDisbursedAmount) }}</div>
                    </div>
                    <div class="mt-2" style="font-size: 12px; color: #94a3b8;">Direct bank &amp; closed-loop lines</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Today's Recovery</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <div style="font-size: 26px; font-weight: 700; color: #0f172a;">₹{{ number_format($todayCollectedAmount) }}</div>
                        <span class="badge" style="background: #f1f5f9; color: #0f172a; font-size: 11px; font-weight: 700;">
                            ₹{{ number_format($todayDueAmount) }} Due
                        </span>
                    </div>
                    <div class="mt-2" style="font-size: 12px; color: {{ $overdueCount > 0 ? '#dc2626' : '#94a3b8' }}; font-weight: {{ $overdueCount > 0 ? '600' : 'normal' }};">
                        {{ $overdueCount }} overdue loan accounts
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Applications Table -->
        <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="p-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #e2e8f0;">
                <div>
                    <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">Recent Loan Applications</h5>
                    <div style="font-size: 12px; color: #64748b;">Latest submissions requiring review or disbursement tracking</div>
                </div>
                <a href="{{ route('admin.finance.applications') }}" class="btn btn-sm" style="font-size: 12px; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 6px;">
                    View All
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                            <th style="padding: 12px 16px;">Application No</th>
                            <th style="padding: 12px 16px;">Applicant</th>
                            <th style="padding: 12px 16px;">Type</th>
                            <th style="padding: 12px 16px;">Product / Category</th>
                            <th style="padding: 12px 16px;">Requested</th>
                            <th style="padding: 12px 16px;">Status</th>
                            <th style="padding: 12px 16px;">Date</th>
                            <th style="padding: 12px 16px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentApplications as $app)
                        <tr>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                                {{ $app->application_number }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 600; color: #0f172a;">{{ $app->applicant_name }}</div>
                                <div style="font-size: 11px; color: #64748b;">{{ $app->applicant_phone }}</div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: {{ $app->customer && $app->customer->user_type === 'driver' ? '#e0f2fe' : '#f1f5f9' }}; color: {{ $app->customer && $app->customer->user_type === 'driver' ? '#0369a1' : '#334155' }}; font-weight: 600; font-size: 11px;">
                                    {{ ucfirst($app->customer->user_type ?? 'User') }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; color: #334155;">
                                {{ ucwords(str_replace('_', ' ', $app->loan_category)) }}
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                                ₹{{ number_format($app->requested_amount) }}
                            </td>
                            <td style="padding: 12px 16px;">
                                @php
                                    $bg = '#f1f5f9'; $fg = '#334155';
                                    if ($app->application_status === 'DISBURSED') { $bg = '#ecfdf5'; $fg = '#065f46'; }
                                    elseif (in_array($app->application_status, ['PROOF_SUBMITTED', 'VALIDATION_PENDING'])) { $bg = '#fef3c7'; $fg = '#92400e'; }
                                    elseif ($app->application_status === 'REJECTED') { $bg = '#fef2f2'; $fg = '#991b1b'; }
                                    elseif ($app->application_status === 'LOAN_APPROVED') { $bg = '#eff6ff'; $fg = '#1e40af'; }
                                @endphp
                                <span class="badge" style="background: {{ $bg }}; color: {{ $fg }}; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 4px;">
                                    {{ str_replace('_', ' ', $app->application_status) }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; color: #64748b; font-size: 12px;">
                                {{ $app->created_at->format('d M Y, H:i') }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <a href="{{ route('admin.finance.application-details', $app->id) }}" class="btn btn-sm" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 10px;">
                                    Review
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted" style="font-size: 13px;">
                                No loan applications submitted yet.
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

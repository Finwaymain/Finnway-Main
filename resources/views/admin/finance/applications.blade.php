@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        LOAN PIPELINE
                    </span>
                    <span style="color: #64748b; font-size: 13px; font-weight: 600;">Applications &amp; Underwriting</span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Loan Applications Pipeline
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Dashboard
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 mb-4 p-3" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <form method="GET" action="{{ route('admin.finance.applications') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Application #, Applicant name, phone..." style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <option value="">All Application Statuses</option>
                        <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                        <option value="FEE_PAID" {{ request('status') === 'FEE_PAID' ? 'selected' : '' }}>Fee Paid</option>
                        <option value="PARTNER_SELECTED" {{ request('status') === 'PARTNER_SELECTED' ? 'selected' : '' }}>Partner Selected</option>
                        <option value="PROOF_SUBMITTED" {{ request('status') === 'PROOF_SUBMITTED' ? 'selected' : '' }}>Proof Submitted</option>
                        <option value="VALIDATION_PENDING" {{ request('status') === 'VALIDATION_PENDING' ? 'selected' : '' }}>Validation Pending</option>
                        <option value="LOAN_APPROVED" {{ request('status') === 'LOAN_APPROVED' ? 'selected' : '' }}>Loan Approved</option>
                        <option value="DISBURSEMENT_PENDING" {{ request('status') === 'DISBURSEMENT_PENDING' ? 'selected' : '' }}>Disbursement Pending</option>
                        <option value="DISBURSED" {{ request('status') === 'DISBURSED' ? 'selected' : '' }}>Disbursed</option>
                        <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejected (3-Day Lock)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <option value="">All Loan Products</option>
                        <option value="virtual_credit" {{ request('category') === 'virtual_credit' ? 'selected' : '' }}>Virtual Loan</option>
                        <option value="low_cibil_cash" {{ request('category') === 'low_cibil_cash' ? 'selected' : '' }}>Cash Loan (Low CIBIL)</option>
                        <option value="prime_cash" {{ request('category') === 'prime_cash' ? 'selected' : '' }}>Cash Loan (Good CIBIL)</option>
                        <option value="zero_cibil_micro" {{ request('category') === 'zero_cibil_micro' ? 'selected' : '' }}>Zero-CIBIL Loan</option>
                        <option value="student_credit" {{ request('category') === 'student_credit' ? 'selected' : '' }}>Student Credit</option>
                        <option value="business_msme" {{ request('category') === 'business_msme' ? 'selected' : '' }}>Business Loan</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm w-100" style="background: #0f172a; color: #ffffff; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 6px 12px;">
                        Filter
                    </button>
                    <a href="{{ route('admin.finance.applications') }}" class="btn btn-sm" style="background: #f1f5f9; color: #475569; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 6px 12px;">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Applications Table -->
        <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                            <th style="padding: 12px 16px;">App Number</th>
                            <th style="padding: 12px 16px;">Borrower Profile</th>
                            <th style="padding: 12px 16px;">Category</th>
                            <th style="padding: 12px 16px;">Requested / Approved</th>
                            <th style="padding: 12px 16px;">Lender Lock</th>
                            <th style="padding: 12px 16px;">Fee &amp; Proof</th>
                            <th style="padding: 12px 16px;">Status</th>
                            <th style="padding: 12px 16px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                        <tr>
                            <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">
                                {{ $app->application_number }}
                                <div style="font-size: 11px; color: #64748b; font-weight: normal;">
                                    {{ $app->created_at->format('d M Y, H:i') }}
                                </div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 600; color: #0f172a;">{{ $app->applicant_name }}</div>
                                <div style="font-size: 12px; color: #64748b;">
                                    {{ $app->applicant_phone }} • 
                                    <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 10px;">{{ ucfirst($app->customer->user_type ?? 'User') }}</span>
                                </div>
                            </td>
                            <td style="padding: 12px 16px; color: #334155;">
                                {{ ucwords(str_replace('_', ' ', $app->loan_category)) }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: #0f172a;">₹{{ number_format($app->requested_amount) }}</div>
                                @if($app->approved_amount)
                                    <div style="font-size: 11px; color: #059669; font-weight: 600;">Appr: ₹{{ number_format($app->approved_amount) }}</div>
                                @endif
                            </td>
                            <td style="padding: 12px 16px;">
                                @php
                                    $isExternalLender = in_array($app->loan_category, ['low_cibil_cash', 'prime_cash', 'business_msme', 'cash_loan', 'business_loan']);
                                @endphp
                                @if($isExternalLender)
                                    @if($app->lender)
                                        <span class="badge" style="background: #f1f5f9; color: #0f172a; font-weight: 600; border: 1px solid #cbd5e1;">
                                            🔒 {{ $app->lender->name }}
                                        </span>
                                    @else
                                        <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 11px;">External Lender</span>
                                    @endif
                                @else
                                    <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 11px; border: 1px solid #a7f3d0;">
                                        ⚡ Direct Credit (No Lender)
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; font-size: 12px;">
                                <div>
                                    Fee: <span class="badge" style="background: {{ $app->fee_payment_status === 'paid' ? '#ecfdf5' : '#fef2f2' }}; color: {{ $app->fee_payment_status === 'paid' ? '#065f46' : '#991b1b' }};">{{ ucfirst($app->fee_payment_status) }}</span>
                                </div>
                                <div class="mt-1">
                                    Proof: <span class="badge" style="background: {{ $app->process_proof_file ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $app->process_proof_file ? '#065f46' : '#64748b' }};">{{ $app->process_proof_file ? 'Uploaded' : 'Pending' }}</span>
                                </div>
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
                            <td style="padding: 12px 16px; text-align: right;">
                                <a href="{{ route('admin.finance.application-details', $app->id) }}" class="btn btn-sm" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 5px 12px;">
                                    Review
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No applications match the specified criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
            <div class="p-3" style="border-top: 1px solid #e2e8f0;">
                {{ $applications->withQueryString()->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

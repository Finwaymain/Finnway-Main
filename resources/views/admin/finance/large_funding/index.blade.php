@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #4f46e5; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        HIGH-TICKET SYNDICATE
                    </span>
                    <span style="color: #64748b; font-size: 13px; font-weight: 600;">Private Investor Pipeline (₹2Cr to ₹100Cr)</span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Business Loan &amp; Private Funding Pipeline
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Dashboard
                </a>
                <a href="{{ route('admin.finance.applications') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Standard Loans
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
                    <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">Total Large Applications</div>
                    <div style="font-size: 26px; font-weight: 700; color: #0f172a; margin-top: 4px;">{{ number_format($totalCount) }}</div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">₹2Cr to ₹100Cr Portfolio</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 12px; font-weight: 600; color: #d97706; text-transform: uppercase;">Underwriting Review</div>
                    <div style="font-size: 26px; font-weight: 700; color: #b45309; margin-top: 4px;">{{ number_format($underReview) }}</div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Stages 1 &amp; 2 Verification</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 12px; font-weight: 600; color: #4f46e5; text-transform: uppercase;">In Investor Syndicate</div>
                    <div style="font-size: 26px; font-weight: 700; color: #4338ca; margin-top: 4px;">{{ number_format($inSyndicate) }}</div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Stages 4, 5 &amp; 6 Discussion</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 p-3 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div style="font-size: 12px; font-weight: 600; color: #059669; text-transform: uppercase;">Approved Facilities</div>
                    <div style="font-size: 26px; font-weight: 700; color: #047857; margin-top: 4px;">{{ number_format($approvedCount) }}</div>
                    <div style="font-size: 11px; color: #059669; font-weight: 700; margin-top: 2px;">
                        Total Value: ₹{{ number_format($approvedAmount) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 mb-4 p-3" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <form method="GET" action="{{ route('admin.finance.large_funding.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="App #, Business name, Applicant, PAN..." style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>
                <div class="col-md-3">
                    <select name="stage" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <option value="">All Milestone Stages (1 to 8)</option>
                        <option value="1" {{ request('stage') == '1' ? 'selected' : '' }}>Stage 1: Application Submitted</option>
                        <option value="2" {{ request('stage') == '2' ? 'selected' : '' }}>Stage 2: Documents & Project Review</option>
                        <option value="3" {{ request('stage') == '3' ? 'selected' : '' }}>Stage 3: Service Fee Payment</option>
                        <option value="4" {{ request('stage') == '4' ? 'selected' : '' }}>Stage 4: Forwarded to Investors</option>
                        <option value="5" {{ request('stage') == '5' ? 'selected' : '' }}>Stage 5: Funding Discussion</option>
                        <option value="6" {{ request('stage') == '6' ? 'selected' : '' }}>Stage 6: Final Offer & Approval</option>
                        <option value="7" {{ request('stage') == '7' ? 'selected' : '' }}>Stage 7: Funding Approved</option>
                        <option value="8" {{ request('stage') == '8' ? 'selected' : '' }}>Stage 8: Amount Credited</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="fee_status" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <option value="">All Fee Statuses</option>
                        <option value="pending" {{ request('fee_status') === 'pending' ? 'selected' : '' }}>Fee Pending</option>
                        <option value="paid" {{ request('fee_status') === 'paid' ? 'selected' : '' }}>Fee Paid</option>
                        <option value="waived" {{ request('fee_status') === 'waived' ? 'selected' : '' }}>Fee Waived</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm w-100" style="background: #4f46e5; color: #ffffff; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 6px 12px;">
                        Filter
                    </button>
                    <a href="{{ route('admin.finance.large_funding.index') }}" class="btn btn-sm" style="background: #f1f5f9; color: #475569; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 6px 12px;">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Applications Table (Zero Modals) -->
        <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                            <th style="padding: 12px 16px;">Application #</th>
                            <th style="padding: 12px 16px;">Enterprise &amp; Applicant</th>
                            <th style="padding: 12px 16px;">Category &amp; Sector</th>
                            <th style="padding: 12px 16px;">Required Capital</th>
                            <th style="padding: 12px 16px;">Service Fee</th>
                            <th style="padding: 12px 16px;">Current Milestone</th>
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
                                <div style="font-weight: 700; color: #1e1b4b; font-size: 13.5px;">{{ $app->business_name }}</div>
                                <div style="font-size: 12px; color: #475569;">
                                    {{ $app->applicant_name }} ({{ $app->applicant_role }}) • {{ $app->mobile_number }}
                                </div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: #e0e7ff; color: #4338ca; font-size: 11px; font-weight: 600;">
                                    {{ $app->business_category }}
                                </span>
                                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                    {{ $app->business_type }}
                                </div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 800; color: #0f172a; font-size: 14px;">
                                    {{ $app->required_funding_amount }}
                                </div>
                                <div style="font-size: 11px; color: #64748b;">
                                    {{ $app->funding_type }}
                                </div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: #4f46e5;">
                                    ₹{{ number_format($app->service_fee_total, 2) }}
                                </div>
                                <span class="badge" style="background: {{ $app->fee_payment_status === 'paid' ? '#ecfdf5' : ($app->fee_payment_status === 'waived' ? '#ede9fe' : '#fef2f2') }}; color: {{ $app->fee_payment_status === 'paid' ? '#065f46' : ($app->fee_payment_status === 'waived' ? '#5b21b6' : '#991b1b') }}; font-size: 10px;">
                                    {{ strtoupper($app->fee_payment_status) }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700; padding: 5px 8px; border-radius: 4px;">
                                    Stage {{ $app->current_stage }}: {{ $app->stage_label }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <div style="display:inline-flex; align-items:center; gap:6px;">
                                    <a href="{{ route('admin.finance.large_funding.show', $app->id) }}" class="btn btn-sm" style="background: #4f46e5; color: #ffffff; font-size: 12px; font-weight: 700; border-radius: 6px; padding: 6px 12px; text-decoration: none;">
                                        Inspect →
                                    </a>
                                    <form method="POST" action="{{ route('admin.finance.large_funding.destroy', $app->id) }}" onsubmit="return confirm('Are you sure you want to delete application {{ $app->application_number }}? This will permanently wipe all uploaded documents and reset test state.');" style="display:inline-block; margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-size: 12px; font-weight: 700; border-radius: 6px; padding: 6px 10px; cursor: pointer;">
                                            🗑 Del
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="padding: 30px; text-align: center; color: #94a3b8;">
                                No large business funding applications found matching criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($applications->hasPages())
            <div class="p-3 border-top">
                {{ $applications->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

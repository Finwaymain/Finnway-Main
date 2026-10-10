@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        BANKING &amp; NBFC NETWORK
                    </span>
                    <span style="color: #64748b; font-size: 13px; font-weight: 600;">Lending Institutions</span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Lender Partners &amp; Web Portals
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Dashboard
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

        <div class="row g-4">
            <!-- Partners Table -->
            <div class="col-lg-8">
                <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                        <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">Configured Lending Partners</h5>
                        <div style="font-size: 12px; color: #64748b;">Partners presented to applicants in the single-partner lock stage</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 13px;">
                            <thead>
                                <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                                    <th style="padding: 12px 16px;">Institution</th>
                                    <th style="padding: 12px 16px;">Amount Range</th>
                                    <th style="padding: 12px 16px;">Interest Rate</th>
                                    <th style="padding: 12px 16px;">Tenure</th>
                                    <th style="padding: 12px 16px;">Status</th>
                                    <th style="padding: 12px 16px; text-align: right;">Portal Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($partners as $partner)
                                <tr>
                                    <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">
                                        {{ $partner->name }}
                                    </td>
                                    <td style="padding: 12px 16px; color: #334155;">
                                        ₹{{ number_format($partner->min_loan_amount) }} – ₹{{ number_format($partner->max_loan_amount) }}
                                    </td>
                                    <td style="padding: 12px 16px; font-weight: 600; color: #059669;">
                                        {{ $partner->interest_rate_display }}
                                    </td>
                                    <td style="padding: 12px 16px; color: #64748b;">
                                        {{ $partner->tenure_display }}
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <span class="badge" style="background: {{ $partner->status === 'active' ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $partner->status === 'active' ? '#065f46' : '#94a3b8' }}; font-weight: 700;">
                                            {{ strtoupper($partner->status) }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 16px; text-align: right;">
                                        <a href="{{ $partner->application_url }}" target="_blank" class="btn btn-sm" style="background: #f1f5f9; color: #0f172a; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 10px; border: 1px solid #cbd5e1;">
                                            Open URL ↗
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No banking partners configured.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add / Edit Partner Form -->
            <div class="col-lg-4">
                <div class="card border-0 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-3" style="font-size: 16px; font-weight: 700; color: #0f172a;">Add Partner Institution</h5>
                    
                    <form method="POST" action="{{ route('admin.finance.lenders.save') }}">
                        @csrf
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Bank / Institution Name</label>
                            <input type="text" name="name" required placeholder="e.g. HDFC Bank, Bajaj Finserv" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label style="font-size: 12px; font-weight: 600; color: #475569;">Min Loan (₹)</label>
                                <input type="number" name="min_loan_amount" value="50000" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                            </div>
                            <div class="col-6">
                                <label style="font-size: 12px; font-weight: 600; color: #475569;">Max Loan (₹)</label>
                                <input type="number" name="max_loan_amount" value="5000000" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Interest Rate Display</label>
                            <input type="text" name="interest_rate_display" value="10.5% - 14% p.a." class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Tenure Display</label>
                            <input type="text" name="tenure_display" value="12 - 60 Months" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Application / Portal URL</label>
                            <input type="url" name="application_url" required placeholder="https://partner-portal.com/apply" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label style="font-size: 12px; font-weight: 600; color: #475569;">Status</label>
                                <select name="status" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label style="font-size: 12px; font-weight: 600; color: #475569;">Sort Order</label>
                                <input type="number" name="sort_order" value="0" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm w-100" style="background: #0f172a; color: #ffffff; font-size: 13px; font-weight: 600; border-radius: 6px; padding: 10px;">
                            Save Partner Institution
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── User Leads & Referral Redirection Tracking ────────────────────── -->
        <div class="card border-0 mt-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background: #0284c7; color: #ffffff; font-size: 11px; font-weight: 700; padding: 3px 6px; border-radius: 4px;">
                                REFERRAL LEADS
                            </span>
                            <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">Lender Leads &amp; Redirect History</h5>
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            Tracks all users who completed the lender form, selected an institution, and got redirected to the external affiliate/portal link.
                        </div>
                    </div>

                    <!-- Metrics Badges -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 12px; padding: 6px 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                            Total Clicks: <strong style="color:#0f172a;">{{ $totalLeads ?? 0 }}</strong>
                        </span>
                        <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 12px; padding: 6px 10px; border-radius: 6px; border: 1px solid #a7f3d0;">
                            Today: <strong style="color:#047857;">{{ $todayLeads ?? 0 }}</strong>
                        </span>
                        <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 12px; padding: 6px 10px; border-radius: 6px; border: 1px solid #fde68a;">
                            With Referral: <strong style="color:#b45309;">{{ $referredLeads ?? 0 }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Search & Filters -->
                <form method="GET" action="{{ route('admin.finance.lenders') }}" class="mt-3">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, email, or referral code..." class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>
                        <div class="col-md-3">
                            <select name="lender_id" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                                <option value="">All Lenders</option>
                                @foreach($partners as $p)
                                <option value="{{ $p->id }}" {{ request('lender_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="referrer_type" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                                <option value="">All Referrers</option>
                                <option value="customer" {{ request('referrer_type') == 'customer' ? 'selected' : '' }}>User (Customer)</option>
                                <option value="driver" {{ request('referrer_type') == 'driver' ? 'selected' : '' }}>Driver</option>
                                <option value="vendor" {{ request('referrer_type') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                <option value="sub_vendor" {{ request('referrer_type') == 'sub_vendor' ? 'selected' : '' }}>Sub Vendor</option>
                                <option value="freelance" {{ request('referrer_type') == 'freelance' ? 'selected' : '' }}>Freelancer</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-sm flex-fill" style="background: #0f172a; color: #ffffff; font-size: 12px; font-weight: 600; border-radius: 6px;">
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'lender_id', 'referrer_type']))
                            <a href="{{ route('admin.finance.lenders') }}" class="btn btn-sm btn-light" style="font-size: 12px; border: 1px solid #cbd5e1;">
                                Clear
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Leads Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                            <th style="padding: 12px 16px;">Applicant Name</th>
                            <th style="padding: 12px 16px;">Phone &amp; Email</th>
                            <th style="padding: 12px 16px;">Selected Lender</th>
                            <th style="padding: 12px 16px;">Referral Code</th>
                            <th style="padding: 12px 16px;">Referrer Persona</th>
                            <th style="padding: 12px 16px;">Timestamp</th>
                            <th style="padding: 12px 16px; text-align: right;">Affiliate Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                        <tr>
                            <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">
                                {{ $lead->applicant_name }}
                            </td>
                            <td style="padding: 12px 16px; color: #334155;">
                                <div><strong>{{ $lead->phone }}</strong></div>
                                @if($lead->email)
                                <div style="font-size: 11px; color: #64748b;">{{ $lead->email }}</div>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0284c7;">
                                {{ $lead->lender_name ?? ($lead->lender->name ?? 'Lender #' . $lead->lender_id) }}
                            </td>
                            <td style="padding: 12px 16px;">
                                @if($lead->referral_code)
                                <span class="badge" style="background: #f8fafc; color: #0f172a; font-family: monospace; font-size: 12px; padding: 4px 8px; border: 1px solid #cbd5e1;">
                                    {{ $lead->referral_code }}
                                </span>
                                @else
                                <span style="color: #94a3b8; font-size: 12px;">Direct (None)</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px;">
                                @if($lead->referrer_type)
                                <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                    {{ str_replace('_', ' ', $lead->referrer_type) }}
                                </span>
                                @if($lead->referrer_id)
                                <span style="font-size: 11px; color: #64748b; font-family: monospace;">#{{ $lead->referrer_id }}</span>
                                @endif
                                @else
                                <span style="color: #94a3b8; font-size: 12px;">—</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; color: #64748b; font-size: 12px;">
                                {{ $lead->created_at ? $lead->created_at->format('d M Y, h:i A') : '—' }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                @if($lead->affiliate_url)
                                <a href="{{ $lead->affiliate_url }}" target="_blank" class="btn btn-sm" style="background: #f1f5f9; color: #0f172a; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 3px 8px; border: 1px solid #cbd5e1;">
                                    Portal ↗
                                </a>
                                @else
                                <span style="color: #94a3b8; font-size: 11px;">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No referral leads or redirects recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($leads->hasPages())
            <div class="p-3" style="border-top: 1px solid #e2e8f0;">
                {{ $leads->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

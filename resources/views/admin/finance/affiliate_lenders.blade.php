@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #0284c7; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        AFFILIATE &amp; PARTNER NETWORK
                    </span>
                    <span style="color: #64748b; font-size: 13px; font-weight: 600;">Third-Party Affiliate Lenders</span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Affiliate Lenders &amp; Referral Leads
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.finance.lenders') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Cash Loan Partners
                </a>
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

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 py-2 px-3 mb-4" style="border-radius: 6px; background: #fef2f2; color: #991b1b; font-size: 13px; font-weight: 600; border-left: 4px solid #ef4444 !important;" role="alert">
            <strong>⚠ Error:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 6px 12px; color: #991b1b;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <!-- ── Top Section: Configured Affiliate Lenders & Add Form ──────────── -->
        <div class="row g-4 mb-4">
            <!-- Affiliate Lenders Table -->
            <div class="col-lg-8">
                <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                        <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">Configured Affiliate Lenders</h5>
                        <div style="font-size: 12px; color: #64748b;">Affiliate lenders presented to users in the "Direct Partner Lender Loans" screen</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 13px;">
                            <thead>
                                <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                                    <th style="padding: 12px 16px;">Lender / Partner</th>
                                    <th style="padding: 12px 16px;">Loan Limit Range</th>
                                    <th style="padding: 12px 16px;">Interest Rate</th>
                                    <th style="padding: 12px 16px;">Status</th>
                                    <th style="padding: 12px 16px;">Affiliate Portal Link</th>
                                    <th style="padding: 12px 16px; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($affiliateLenders as $affiliate)
                                <tr>
                                    <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">
                                        {{ $affiliate->name }}
                                    </td>
                                    <td style="padding: 12px 16px; color: #334155;">
                                        ₹{{ number_format($affiliate->min_loan_amount) }} – ₹{{ number_format($affiliate->max_loan_amount) }}
                                    </td>
                                    <td style="padding: 12px 16px; font-weight: 600; color: #059669;">
                                        {{ $affiliate->interest_rate_display }}
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <span class="badge" style="background: {{ $affiliate->status === 'active' ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $affiliate->status === 'active' ? '#065f46' : '#94a3b8' }}; font-weight: 700;">
                                            {{ strtoupper($affiliate->status) }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <a href="{{ $affiliate->affiliate_url }}" target="_blank" class="btn btn-sm" style="background: #f1f5f9; color: #0f172a; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 10px; border: 1px solid #cbd5e1; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block;">
                                            Open URL ↗
                                        </a>
                                    </td>
                                    <td style="padding: 12px 16px; text-align: right;">
                                        <form method="POST" action="{{ route('admin.finance.affiliate_lenders.delete', $affiliate->id) }}" onsubmit="return confirm('Remove this affiliate lender?');" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #991b1b; font-size: 11px; font-weight: 700; border-radius: 4px; padding: 4px 8px; border: 1px solid #fca5a5;">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No affiliate lenders configured yet. Add your first affiliate partner using the form on the right.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add / Edit Affiliate Lender Form -->
            <div class="col-lg-4">
                <div class="card border-0 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-3" style="font-size: 16px; font-weight: 700; color: #0f172a;">Add Affiliate Lender Link</h5>
                    
                    <form method="POST" action="{{ route('admin.finance.affiliate_lenders.save') }}">
                        @csrf
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Affiliate Partner / Platform Name</label>
                            <input type="text" name="name" required placeholder="e.g. MoneyControl Loans, Paisabazaar, Bajaj Finserv" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
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
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Affiliate / Portal Referral URL</label>
                            <input type="url" name="affiliate_url" required placeholder="https://affiliate-network.com/campaign/apply?ref=fiinway" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
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

                        <button type="submit" class="btn btn-sm w-100" style="background: #0284c7; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 6px; padding: 10px;">
                            Save Affiliate Lender
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── User Leads & Referral Redirection Tracking ────────────────────── -->
        <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background: #0284c7; color: #ffffff; font-size: 11px; font-weight: 700; padding: 3px 6px; border-radius: 4px;">
                                LEADS &amp; REDIRECTS
                            </span>
                            <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">User Affiliate Leads &amp; Referral Tracking</h5>
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            Records user name, phone, optional referral code, selected affiliate lender, and redirect timestamp.
                        </div>
                    </div>

                    <!-- Metrics Badges -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 12px; padding: 6px 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                            Total Leads: <strong style="color:#0f172a;">{{ $totalLeads ?? 0 }}</strong>
                        </span>
                        <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 12px; padding: 6px 10px; border-radius: 6px; border: 1px solid #a7f3d0;">
                            Today: <strong style="color:#047857;">{{ $todayLeads ?? 0 }}</strong>
                        </span>
                        <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 12px; padding: 6px 10px; border-radius: 6px; border: 1px solid #fde68a;">
                            With Referral Code: <strong style="color:#b45309;">{{ $referredLeads ?? 0 }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Search & Filters -->
                <form method="GET" action="{{ route('admin.finance.affiliate_lenders') }}" class="mt-3">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user name, phone, email, or referral code..." class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>
                        <div class="col-md-3">
                            <select name="lender_id" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                                <option value="">All Affiliate Lenders</option>
                                @foreach($affiliateLenders as $l)
                                <option value="{{ $l->id }}" {{ request('lender_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
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
                            <button type="submit" class="btn btn-sm flex-fill" style="background: #0284c7; color: #ffffff; font-size: 12px; font-weight: 700; border-radius: 6px;">
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'lender_id', 'referrer_type']))
                            <a href="{{ route('admin.finance.affiliate_lenders') }}" class="btn btn-sm btn-light" style="font-size: 12px; border: 1px solid #cbd5e1;">
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
                            <th style="padding: 12px 16px;">Selected Affiliate Lender</th>
                            <th style="padding: 12px 16px;">Referral Code</th>
                            <th style="padding: 12px 16px;">Referrer Persona</th>
                            <th style="padding: 12px 16px;">Date &amp; Time</th>
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
                            <td style="padding: 12px 16px; font-weight: 700; color: #0284c7;">
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
                                No affiliate referral leads recorded yet.
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

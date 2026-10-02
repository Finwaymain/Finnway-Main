@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1360px; margin: 0 auto; padding-top: 24px; padding-bottom: 60px;">

        <!-- Back Button & Breadcrumb -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('admin.marketing-vendors.index') }}" class="btn btn-sm btn-outline-secondary font-weight-semibold" style="border-radius: 8px;">
                &larr; Back to Vendors List
            </a>
            <span class="text-muted" style="font-size: 12.5px;">Vendor ID #{{ $vendor->id }} &bull; Created {{ date('M d, Y', strtotime($vendor->created_at)) }}</span>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 12px; background: #ecfdf5; color: #065f46;" role="alert">
            <strong>✓ Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 12px; background: #fef2f2; color: #991b1b;" role="alert">
            <strong>⚠ Error:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <!-- Hierarchy Lineage Ribbon -->
        <div class="card border-0 shadow-sm mb-3 px-3 py-2.5" style="border-radius: 10px; background: #ffffff; border-left: 4px solid #4f46e5 !important; border: 1px solid #e2e8f0;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2" style="font-size: 12.5px;">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="font-weight-bold text-dark">🌳 Hierarchy Lineage:</span>
                    @if(empty($vendor->parent_vendor_id))
                        <span class="badge" style="background: #e0e7ff; color: #3730a3; font-weight: 800; font-size: 11px; padding: 4px 8px; border-radius: 6px;">
                            👑 Top-Level Head Vendor
                        </span>
                        <span class="text-muted font-weight-semibold">({{ $vendor->vendor_code ?: 'VR' . $vendor->id }} - {{ $vendor->applicant_name }})</span>
                    @else
                        <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 800; font-size: 11px; padding: 4px 8px; border-radius: 6px;">
                            ↳ Sub-Vendor (Level {{ $vendor->hierarchy_level }})
                        </span>
                        @if($parentVendor)
                            <span class="text-muted">Parent:</span>
                            <a href="{{ route('admin.marketing-vendors.show', $parentVendor->id) }}" class="font-weight-bold text-primary" style="text-decoration: underline;">
                                {{ $parentVendor->vendor_code ?: 'VR' . $parentVendor->id }} ({{ $parentVendor->applicant_name }})
                            </a>
                            <span class="text-muted">&rarr;</span>
                        @endif
                        <span class="font-weight-bold text-dark">{{ $vendor->vendor_code ?: 'VR' . $vendor->id }} ({{ $vendor->applicant_name }})</span>
                        @if(!empty($vendor->designation))
                            <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 11px; font-weight: 700;">🏷 {{ $vendor->designation }}</span>
                        @endif
                    @endif
                </div>

                @if(isset($vendor->is_rate_visible))
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted" style="font-size: 11.5px;">Rate Visibility:</span>
                    <span class="badge {{ $vendor->is_rate_visible ? 'bg-success' : 'bg-secondary' }} text-white" style="font-size: 10px;">
                        {{ $vendor->is_rate_visible ? 'ON (Visible)' : 'OFF (Hidden)' }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Profile Header Card -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 14px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold mr-3" style="width: 56px; height: 56px; font-size: 22px; flex-shrink: 0;">
                            {{ strtoupper(substr($vendor->applicant_name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h2 class="font-weight-bold mb-0 text-dark" style="font-size: 22px;">
                                    {{ $vendor->applicant_name }}
                                </h2>
                                @if($vendor->status === 'approved')
                                    <span class="badge bg-success text-white px-2 py-1 ml-2" style="font-size: 11px;">Active Vendor</span>
                                @elseif($vendor->status === 'pending')
                                    <span class="badge bg-warning text-white px-2 py-1 ml-2" style="font-size: 11px;">Pending Approval</span>
                                @else
                                    <span class="badge bg-danger text-white px-2 py-1 ml-2" style="font-size: 11px;">{{ ucfirst($vendor->status) }}</span>
                                @endif

                                @if(!empty($vendor->designation))
                                    <span class="badge" style="background: #e2e8f0; color: #1e293b; font-size: 11px; font-weight: 700;">
                                        {{ $vendor->designation }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-muted d-flex flex-wrap align-items-center gap-3" style="font-size: 13px;">
                                <span>📞 {{ $vendor->applicant_phone ?: 'No phone' }}</span>
                                @if($vendor->applicant_email)
                                <span>✉ {{ $vendor->applicant_email }}</span>
                                @endif
                                <span>📍 {{ $vendor->team_location }}</span>
                                <span>🏢 {{ $vendor->team_type }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @if($vendor->vendor_code)
                        <div class="text-right mr-3">
                            <small class="text-muted text-uppercase d-block" style="font-size: 10.5px; font-weight: 700;">Vendor Code</small>
                            <span class="badge bg-dark text-white px-3 py-1 font-monospace" style="font-size: 15px; letter-spacing: 1px;">
                                {{ $vendor->vendor_code }}
                            </span>
                        </div>
                        @endif

                        <button type="button" class="btn btn-sm btn-outline-primary font-weight-semibold" onclick="toggleSection('ratesBox')" style="border-radius: 8px;">
                            ⚙ Edit Payout Rates
                        </button>

                        @if($stats['pending_payout'] > 0)
                        <button type="button" class="btn btn-sm btn-success font-weight-bold" onclick="toggleSection('payoutBox')" style="border-radius: 8px;">
                            ₹ Settle Payout (₹{{ number_format($stats['pending_payout'], 2) }})
                        </button>
                        @endif
                    </div>
                </div>

                @if(!empty($vendor->remarks))
                <div class="mt-3 pt-3 border-top text-muted" style="font-size: 12.5px;">
                    <strong>Application Remarks:</strong> {{ $vendor->remarks }}
                </div>
                @endif
            </div>
        </div>

        <!-- Inline Section 1: Configure Payout Rates -->
        <div id="ratesBox" class="card shadow-sm border-0 mb-4" style="display: none; border-radius: 14px; background: #f0fdf4; border: 1.5px solid #86efac !important;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="font-weight-bold text-success mb-0" style="font-size: 16px;">
                        ⚙ Configure Vendor Payout Rates
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSection('ratesBox')" style="border-radius: 6px;">
                        ✕ Close
                    </button>
                </div>
                <p class="text-muted" style="font-size: 13px;">
                    Set custom rupee earnings credited to <strong>{{ $vendor->applicant_name }}</strong> for each verified customer and business user acquired by their freelancer network.
                </p>

                <form action="{{ route('admin.marketing-vendors.rates', $vendor->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="font-weight-semibold text-dark" style="font-size: 13px;">Rate per Verified Customer (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                <input type="number" step="0.01" min="0" name="rate_per_customer" class="form-control" value="{{ $vendor->rate_per_customer }}" required>
                            </div>
                            <small class="text-muted">Credited when customer registration is verified by admin.</small>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label class="font-weight-semibold text-dark" style="font-size: 13px;">Rate per Verified Business / Partner (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                <input type="number" step="0.01" min="0" name="rate_per_business" class="form-control" value="{{ $vendor->rate_per_business }}" required>
                            </div>
                            <small class="text-muted">Credited when driver/business partner onboarding is verified.</small>
                        </div>

                        <div class="col-md-2 d-flex align-items-center mb-3 pt-md-2">
                            <button type="submit" class="btn btn-success font-weight-bold w-100" style="height: 38px; border-radius: 8px;">
                                Save Rates
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inline Section 2: Settle Payout -->
        <div id="payoutBox" class="card shadow-sm border-0 mb-4" style="display: none; border-radius: 14px; background: #fefce8; border: 1.5px solid #fde047 !important;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="font-weight-bold text-warning text-dark mb-0" style="font-size: 16px;">
                        ₹ Record Vendor Payout Settlement
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSection('payoutBox')" style="border-radius: 6px;">
                        ✕ Close
                    </button>
                </div>

                <form action="{{ route('admin.marketing-vendors.payout', $vendor->id) }}" method="POST">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-semibold text-dark" style="font-size: 13px;">Total Unsettled Dues</label>
                            <div class="h4 font-weight-bold text-success mb-0">₹{{ number_format($stats['pending_payout'], 2) }}</div>
                            <small class="text-muted">{{ $stats['verified_customers'] + $stats['verified_businesses'] }} verified acquisitions ready</small>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label class="font-weight-semibold text-dark" style="font-size: 13px;">Bank / Payment Reference <span class="text-danger">*</span></label>
                            <input type="text" name="payout_reference" class="form-control" placeholder="e.g. UTR12345678, IMPS, Cheque #4421" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <button type="submit" class="btn btn-primary font-weight-bold w-100" style="height: 38px; border-radius: 8px;" onclick="return confirm('Confirm settlement of ₹{{ number_format($stats['pending_payout'], 2) }} for {{ $vendor->applicant_name }}?')">
                                Confirm Settlement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="text-muted text-uppercase" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">Sub-Vendors</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-dark" style="font-size: 24px;">{{ $stats['total_sub_vendors'] }}</span>
                        <span class="badge bg-indigo text-white px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #4f46e5;">Direct</span>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="text-muted text-uppercase" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">Freelancers</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-dark" style="font-size: 24px;">{{ $stats['total_members'] }}</span>
                        <span class="badge bg-light text-muted px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px;">FR Direct</span>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="text-muted text-uppercase" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">Total Users Joined</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-primary" style="font-size: 24px;">{{ $stats['total_acquisitions'] }}</span>
                        <span class="badge bg-warning text-white px-2 py-0.5" style="border-radius: 6px; font-size: 10px;">{{ $stats['pending_verify'] }} Pending</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 11px;">
                        {{ $stats['verified_customers'] }} Cust &bull; {{ $stats['verified_businesses'] }} Biz verified
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #bbf7d0 !important;">
                    <div class="text-success text-uppercase" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">Total Verified Earnings</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-success" style="font-size: 24px;">₹{{ number_format($stats['total_earned'], 2) }}</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 11px;">
                        Cust: ₹{{ number_format($vendor->rate_per_customer, 2) }} | Biz: ₹{{ number_format($vendor->rate_per_business, 2) }}
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-6 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #fed7aa !important;">
                    <div class="text-warning text-uppercase" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">Unsettled Payout</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-warning" style="font-size: 22px;">₹{{ number_format($stats['pending_payout'], 2) }}</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 10.5px;">
                        ₹{{ number_format($stats['paid_earned'], 2) }} Paid
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content with 4 Tabs -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; border: 1px solid #e2e8f0 !important; background: #ffffff;">
            <div class="card-header bg-white border-bottom p-3">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="tabBtnAcquisitions" class="btn btn-sm btn-primary font-weight-bold" onclick="selectVendorTab('acquisitions')" style="border-radius: 8px;">
                        Acquired Users ({{ $acquisitions->total() }})
                    </button>
                    <button type="button" id="tabBtnSubVendors" class="btn btn-sm btn-light text-muted font-weight-bold" onclick="selectVendorTab('subVendors')" style="border-radius: 8px;">
                        🏢 Sub-Vendor Flow ({{ count($subVendors) }})
                    </button>
                    <button type="button" id="tabBtnMembers" class="btn btn-sm btn-light text-muted font-weight-bold" onclick="selectVendorTab('members')" style="border-radius: 8px;">
                        Freelancer Team ({{ count($teamMembers) }})
                    </button>
                    <button type="button" id="tabBtnLedger" class="btn btn-sm btn-light text-muted font-weight-bold" onclick="selectVendorTab('ledger')" style="border-radius: 8px;">
                        🧾 Payment Ledger ({{ count($ledgers) }})
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <!-- Panel 1: User Acquisitions & Verification -->
                <div id="panelAcquisitions" style="display: block;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light" style="background: #f8fafc;">
                                <tr class="text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <th>Date Joined</th>
                                    <th>Joined User</th>
                                    <th>Type</th>
                                    <th>Freelancer Code</th>
                                    <th>KYC Status</th>
                                    <th>Verification</th>
                                    <th>Payout Rate</th>
                                    <th>Payout Status</th>
                                    <th class="text-right" style="min-width: 200px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($acquisitions as $acq)
                                <tr>
                                    <td>
                                        <div class="font-weight-semibold text-dark">{{ date('d M Y', strtotime($acq->created_at)) }}</div>
                                        <div class="text-muted" style="font-size: 11px;">{{ date('h:i A', strtotime($acq->created_at)) }}</div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $acq->user_name }}</div>
                                        <div class="text-muted" style="font-size: 12px;">{{ $acq->user_phone }}</div>
                                    </td>
                                    <td>
                                        @if($acq->acquired_user_type === 'customer')
                                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">Customer</span>
                                        @else
                                            <span class="badge bg-info text-white px-2 py-1" style="font-size: 11px;">Business / Driver</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: 12.5px;">{{ $acq->freelancer_name ?? '—' }}</div>
                                        <span class="badge bg-dark text-white px-2 py-0.5" style="font-size: 10px;">{{ $acq->freelancer_code }}</span>
                                    </td>
                                    <td>
                                        @if($acq->kyc_status === 'Verified')
                                            <span class="badge bg-success text-white px-2 py-1">Verified KYC</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-2 py-1">Pending KYC</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($acq->verification_status === 'verified')
                                            <span class="badge bg-success text-white px-2 py-1">Verified</span>
                                        @elseif($acq->verification_status === 'pending')
                                            <span class="badge bg-warning text-white px-2 py-1">Pending Review</span>
                                        @else
                                            <span class="badge bg-danger text-white px-2 py-1">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($acq->verification_status === 'verified')
                                            <strong class="text-success">₹{{ number_format($acq->payout_rate_applied, 2) }}</strong>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($acq->payout_status === 'paid')
                                            <span class="badge bg-success text-white px-2 py-1">Paid</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-2 py-1">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($acq->verification_status === 'pending')
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            <form action="{{ route('admin.marketing-vendors.verify-acquisition', $acq->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-success font-weight-bold px-2 py-1" style="border-radius: 6px; font-size: 11.5px;" onclick="return confirm('Verify this user acquisition and credit earnings to {{ $vendor->applicant_name }}?')">
                                                    ✓ Verify
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-xs btn-outline-danger font-weight-bold px-2 py-1" style="border-radius: 6px; font-size: 11.5px;" onclick="toggleSection('rejectAcq{{ $acq->id }}')">
                                                ✕ Reject
                                            </button>
                                        </div>

                                        <div id="rejectAcq{{ $acq->id }}" class="mt-2 p-2 bg-light border rounded text-left" style="display: none;">
                                            <form action="{{ route('admin.marketing-vendors.reject-acquisition', $acq->id) }}" method="POST">
                                                @csrf
                                                <input type="text" name="reason" class="form-control form-control-sm mb-1" placeholder="Rejection reason..." required>
                                                <button type="submit" class="btn btn-sm btn-danger btn-block py-0" style="font-size: 11px;">Confirm Reject</button>
                                            </form>
                                        </div>
                                        @else
                                            <span class="text-muted font-italic" style="font-size: 11.5px;">Reviewed</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <p class="mb-1" style="font-size: 15px; font-weight: 600;">No Users Acquired Yet</p>
                                        <small>When customers or drivers join using this vendor's freelancer codes, they will appear here for verification.</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($acquisitions->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $acquisitions->links() }}
                    </div>
                    @endif
                </div>

                <!-- Panel 2: Sub-Vendor Flow -->
                <div id="panelSubVendors" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light" style="background: #f8fafc;">
                                <tr class="text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <th>Sub-Vendor</th>
                                    <th>Vendor Code</th>
                                    <th>Designation</th>
                                    <th>Hierarchy Level</th>
                                    <th>Rates (Cust / Biz)</th>
                                    <th>Rate Visibility</th>
                                    <th>Freelancers</th>
                                    <th>Total Acquired</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subVendors as $sv)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $sv->applicant_name }}</div>
                                        <div class="text-muted" style="font-size: 11.5px;">{{ $sv->applicant_phone }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark text-white px-2 py-1 font-monospace" style="font-size: 11px;">
                                            {{ $sv->vendor_code ?: 'Pending' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 11px; font-weight: 700; border: 1px solid #cbd5e1;">
                                            {{ $sv->designation ?: 'Sub-Vendor' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 10.5px;">
                                            Level {{ $sv->hierarchy_level }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px;">
                                            <div>Cust: <strong>₹{{ number_format($sv->rate_per_customer, 2) }}</strong></div>
                                            <div>Biz: <strong>₹{{ number_format($sv->rate_per_business, 2) }}</strong></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $sv->is_rate_visible ? 'bg-success' : 'bg-secondary' }} text-white" style="font-size: 10px;">
                                            {{ $sv->is_rate_visible ? 'ON' : 'OFF' }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ $sv->members_count }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $sv->acquisitions_count }}</strong>
                                    </td>
                                    <td>
                                        @if($sv->status === 'approved')
                                            <span class="badge bg-success text-white px-2 py-1">Active</span>
                                        @elseif($sv->status === 'pending')
                                            <span class="badge bg-warning text-white px-2 py-1">Pending</span>
                                        @else
                                            <span class="badge bg-danger text-white px-2 py-1">{{ ucfirst($sv->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.marketing-vendors.show', $sv->id) }}" class="btn btn-sm btn-outline-primary font-weight-bold px-2 py-1" style="font-size: 11.5px; border-radius: 6px;">
                                            View Sub-Tree &rarr;
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <p class="mb-1" style="font-size: 15px; font-weight: 600;">No Sub-Vendors Added Under This Vendor</p>
                                        <small>When users apply using this vendor's code and select Sub-Vendor, they will be listed here.</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Panel 3: Freelancer Team Members -->
                <div id="panelMembers" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light" style="background: #f8fafc;">
                                <tr class="text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <th>#</th>
                                    <th>Freelancer Name</th>
                                    <th>Freelancer Code</th>
                                    <th>Role / Type</th>
                                    <th>Customers Acquired</th>
                                    <th>Businesses Acquired</th>
                                    <th>Joined Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teamMembers as $mIndex => $m)
                                <tr>
                                    <td class="text-muted">{{ $mIndex + 1 }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $m->name }}</div>
                                        <div class="text-muted" style="font-size: 12px;">{{ $m->phone ?: 'No phone' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark text-white px-2 py-1 font-monospace" style="font-size: 11px;">
                                            {{ $m->member_code }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-white px-2 py-1" style="font-size: 11px;">
                                            {{ ucfirst($m->user_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ $m->customers_count }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-info">{{ $m->businesses_count }}</strong>
                                    </td>
                                    <td>{{ date('d M Y', strtotime($m->created_at)) }}</td>
                                    <td>
                                        @if($m->status === 'active')
                                            <span class="badge bg-success text-white px-2 py-1">Active</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-2 py-1">{{ ucfirst($m->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <p class="mb-1" style="font-size: 15px; font-weight: 600;">No Freelancer Team Members Yet</p>
                                        <small>Share Vendor Code <strong>{{ $vendor->vendor_code }}</strong> with freelancers to onboard them.</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Panel 4: Payment Ledger -->
                <div id="panelLedger" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light" style="background: #f8fafc;">
                                <tr class="text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <th>Transaction ID</th>
                                    <th>Date</th>
                                    <th>User Acquired</th>
                                    <th>Freelancer</th>
                                    <th>Service</th>
                                    <th>Rate Applied</th>
                                    <th>Earned</th>
                                    <th>Paid</th>
                                    <th>Pending</th>
                                    <th>Payment Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ledgers as $ledger)
                                <tr>
                                    <td>
                                        <span class="font-monospace font-weight-bold text-dark">{{ $ledger['transaction_id'] }}</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; color: #475569;">{{ $ledger['date'] }}</div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $ledger['user_name'] }}</div>
                                        <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10px;">{{ ucfirst($ledger['acquired_user_type']) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark text-white px-2 py-0.5" style="font-size: 10.5px;">
                                            {{ $ledger['freelancer_code'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-0.5">{{ $ledger['service_category'] }}</span>
                                    </td>
                                    <td>
                                        <strong>₹{{ $ledger['rate_applied'] }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-success">₹{{ $ledger['earned_amount'] }}</strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">₹{{ $ledger['paid_amount'] }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-warning">₹{{ $ledger['pending_amount'] }}</strong>
                                    </td>
                                    <td>
                                        @if($ledger['payment_status'] === 'paid')
                                            <span class="badge bg-success text-white px-2 py-1">Paid</span>
                                        @else
                                            <span class="badge bg-warning text-white px-2 py-1">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <p class="mb-1" style="font-size: 15px; font-weight: 600;">No Payment Ledger Entries Recorded Yet</p>
                                        <small>Ledger entries are created automatically when user acquisitions are verified by Admin.</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Pure Vanilla JavaScript: Toggle Inline Sections & Switch Tabs -->
<script>
function toggleSection(elementId) {
    var el = document.getElementById(elementId);
    if (!el) return;
    if (el.style.display === 'none' || el.style.display === '') {
        el.style.display = 'block';
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        el.style.display = 'none';
    }
}

function selectVendorTab(tab) {
    var panels = {
        'acquisitions': document.getElementById('panelAcquisitions'),
        'subVendors':   document.getElementById('panelSubVendors'),
        'members':      document.getElementById('panelMembers'),
        'ledger':       document.getElementById('panelLedger')
    };

    var buttons = {
        'acquisitions': document.getElementById('tabBtnAcquisitions'),
        'subVendors':   document.getElementById('tabBtnSubVendors'),
        'members':      document.getElementById('tabBtnMembers'),
        'ledger':       document.getElementById('tabBtnLedger')
    };

    for (var key in panels) {
        if (panels[key]) {
            if (key === tab) {
                panels[key].style.display = 'block';
                if (buttons[key]) buttons[key].className = 'btn btn-sm btn-primary font-weight-bold';
            } else {
                panels[key].style.display = 'none';
                if (buttons[key]) buttons[key].className = 'btn btn-sm btn-light text-muted font-weight-bold';
            }
        }
    }
}
</script>
@endsection

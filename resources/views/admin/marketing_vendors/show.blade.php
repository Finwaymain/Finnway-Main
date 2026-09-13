@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1320px; margin: 0 auto; padding-top: 24px; padding-bottom: 60px;">

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

        <!-- Inline Section 1: Configure Payout Rates (NO MODAL) -->
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
                            <label class="font-weight-semibold text-dark" style="font-size: 13px;">Rate per Verified Business User / Driver (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                <input type="number" step="0.01" min="0" name="rate_per_business" class="form-control" value="{{ $vendor->rate_per_business }}" required>
                            </div>
                            <small class="text-muted">Credited when business user/driver is verified by admin.</small>
                        </div>
                        <div class="col-md-2 d-flex align-items-center mb-3">
                            <button type="submit" class="btn btn-success font-weight-bold w-100" style="height: 38px; margin-top: 6px;">
                                Save Rates
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inline Section 2: Settle Payout (NO MODAL) -->
        <div id="payoutBox" class="card shadow-sm border-0 mb-4" style="display: none; border-radius: 14px; background: #fffbeb; border: 1.5px solid #fde68a !important;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="font-weight-bold text-warning mb-0" style="font-size: 16px;">
                        ₹ Settle Vendor Payout
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSection('payoutBox')" style="border-radius: 6px;">
                        ✕ Close
                    </button>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="p-3 bg-white rounded border border-warning">
                            <span class="text-muted text-uppercase d-block" style="font-size: 11px; font-weight: 700;">Unsettled Amount Due</span>
                            <span class="font-weight-bold text-warning" style="font-size: 26px;">₹{{ number_format($stats['pending_payout'], 2) }}</span>
                            <small class="text-muted d-block mt-1">Ready for clearance</small>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <form action="{{ route('admin.marketing-vendors.payout', $vendor->id) }}" method="POST">
                            @csrf
                            <div class="form-group mb-2">
                                <label class="font-weight-semibold text-dark" style="font-size: 13px;">Payment Reference / Bank Transaction ID <span class="text-danger">*</span></label>
                                <input type="text" name="payout_reference" class="form-control" placeholder="e.g. UPI Ref / NEFT / IMPS #987654321" required>
                                <small class="text-muted">Enter the banking transaction reference number.</small>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleSection('payoutBox')">Cancel</button>
                                <button type="submit" class="btn btn-success btn-sm font-weight-bold" onclick="return confirm('Confirm marking ₹{{ number_format($stats['pending_payout'], 2) }} as paid to {{ $vendor->applicant_name }}?');">
                                    ✓ Confirm & Mark as Paid
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial & Acquisition Metric Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Team Freelancers</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-dark" style="font-size: 26px;">{{ $stats['total_members'] }}</span>
                        <span class="badge bg-light text-muted px-2 py-1" style="border-radius: 6px;">FR Members</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Total Users Joined</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-primary" style="font-size: 26px;">{{ $stats['total_acquisitions'] }}</span>
                        <span class="badge bg-warning text-white px-2 py-1" style="border-radius: 6px;">{{ $stats['pending_verify'] }} Pending Verify</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 11.5px;">
                        {{ $stats['verified_customers'] }} Cust &bull; {{ $stats['verified_businesses'] }} Biz verified
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #bbf7d0 !important;">
                    <div class="text-success text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Total Verified Earnings</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-success" style="font-size: 26px;">₹{{ number_format($stats['total_earned'], 2) }}</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 11.5px;">
                        Cust: ₹{{ number_format($vendor->rate_per_customer, 2) }} | Biz: ₹{{ number_format($vendor->rate_per_business, 2) }}
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #fed7aa !important;">
                    <div class="text-warning text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Unsettled Payout</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-warning" style="font-size: 26px;">₹{{ number_format($stats['pending_payout'], 2) }}</span>
                        <span class="badge bg-success text-white px-2 py-1" style="border-radius: 6px;">₹{{ number_format($stats['paid_earned'], 2) }} Paid</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 11.5px;">
                        Ready for bank clearance
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content (ZERO BOOTSTRAP TABS, PURE JAVASCRIPT SWITCHING) -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; border: 1px solid #e2e8f0 !important; background: #ffffff;">
            <div class="card-header bg-white border-bottom p-3">
                <div class="d-flex gap-2">
                    <button type="button" id="tabBtnAcquisitions" class="btn btn-sm btn-primary font-weight-bold" onclick="selectVendorTab('acquisitions')" style="border-radius: 8px;">
                        Acquired Users & Verification ({{ $acquisitions->total() }})
                    </button>
                    <button type="button" id="tabBtnMembers" class="btn btn-sm btn-light text-muted font-weight-bold" onclick="selectVendorTab('members')" style="border-radius: 8px;">
                        Freelancer Team Members ({{ count($teamMembers) }})
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
                                    <th class="text-right" style="min-width: 220px;">Action</th>
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
                                        <span class="badge bg-secondary text-white px-2 py-1 font-monospace" style="font-size: 11px;">
                                            {{ $acq->freelancer_code }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($acq->kyc_status === 'Verified')
                                            <span class="badge bg-success text-white px-2 py-1">Verified</span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">Not Verified</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($acq->verification_status === 'verified')
                                            <span class="badge bg-success text-white px-2 py-1">Verified</span>
                                            <div class="text-muted" style="font-size: 10px;">{{ date('d M Y', strtotime($acq->verified_at)) }}</div>
                                        @elseif($acq->verification_status === 'pending')
                                            <span class="badge bg-warning text-white px-2 py-1">Pending</span>
                                        @elseif($acq->verification_status === 'rejected')
                                            <span class="badge bg-danger text-white px-2 py-1">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($acq->verification_status === 'verified')
                                            <strong class="text-success font-weight-bold">₹{{ number_format($acq->payout_rate_applied, 2) }}</strong>
                                        @else
                                            <span class="text-muted italic" style="font-size: 12px;">
                                                ₹{{ number_format($acq->acquired_user_type === 'customer' ? $vendor->rate_per_customer : $vendor->rate_per_business, 2) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($acq->payout_status === 'paid')
                                            <span class="badge bg-primary text-white px-2 py-1">Paid</span>
                                            @if($acq->payout_reference)
                                                <div class="text-muted" style="font-size: 10px;">Ref: {{ $acq->payout_reference }}</div>
                                            @endif
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($acq->verification_status === 'pending')
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            <!-- Direct Verify Form (NO MODAL) -->
                                            <form action="{{ route('admin.marketing-vendors.verify-acquisition', $acq->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Verify user acquisition of {{ $acq->user_name }}? Vendor will be credited ₹{{ number_format($acq->acquired_user_type === 'customer' ? $vendor->rate_per_customer : $vendor->rate_per_business, 2) }}.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success font-weight-semibold" style="border-radius: 6px;">
                                                    ✓ Verify
                                                </button>
                                            </form>

                                            <!-- Inline Reject Trigger (NO MODAL) -->
                                            <button type="button" class="btn btn-sm btn-outline-danger font-weight-semibold" onclick="toggleSection('rejectBox{{ $acq->id }}')" style="border-radius: 6px;">
                                                ✕ Reject
                                            </button>
                                        </div>

                                        <!-- Inline Rejection Form Box (NO MODAL) -->
                                        <div id="rejectBox{{ $acq->id }}" style="display: none; margin-top: 8px; background: #fef2f2; border: 1.5px solid #fca5a5; padding: 10px; border-radius: 8px; text-align: left;">
                                            <form action="{{ route('admin.marketing-vendors.reject-acquisition', $acq->id) }}" method="POST">
                                                @csrf
                                                <label class="font-weight-bold text-danger mb-1" style="font-size: 11.5px;">Rejection Reason <span class="text-danger">*</span></label>
                                                <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="e.g. Duplicate account, fake registration, incomplete KYC..." required style="border-radius: 6px;">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <button type="button" class="btn btn-light btn-sm py-1 px-2" style="font-size: 11px;" onclick="toggleSection('rejectBox{{ $acq->id }}')">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-sm py-1 px-2 font-weight-bold" style="font-size: 11px;">Confirm Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                        @else
                                        <span class="text-muted" style="font-size: 12px;">Settled</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <p class="mb-1" style="font-size: 15px; font-weight: 600;">No Acquired Users Yet</p>
                                        <small>When freelancers under this vendor refer customers or business drivers using their `FR...` code, they will appear here for admin verification.</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end p-3">
                        {{ $acquisitions->links() }}
                    </div>
                </div>

                <!-- Panel 2: Freelancer Team Members -->
                <div id="panelMembers" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light" style="background: #f8fafc;">
                                <tr class="text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <th>Freelancer Name</th>
                                    <th>Team Member Code</th>
                                    <th>Phone</th>
                                    <th>Role / User Type</th>
                                    <th>Customers Acquired</th>
                                    <th>Businesses Acquired</th>
                                    <th>Joined Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teamMembers as $m)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $m->name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark text-white px-2 py-1 font-monospace" style="font-size: 12px; letter-spacing: 0.5px;">
                                            {{ $m->member_code }}
                                        </span>
                                    </td>
                                    <td>{{ $m->phone ?: 'N/A' }}</td>
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
                                        <p class="mb-1" style="font-size: 15px; font-weight: 600;">No Team Members Registered Yet</p>
                                        <small>Share the Vendor Code (<strong>{{ $vendor->vendor_code }}</strong>) with freelancers. When they register using this code, they join this vendor's team.</small>
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

<!-- Pure Vanilla JavaScript: Toggle Inline Sections & Switch Tabs (ZERO BOOTSTRAP MODALS) -->
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
    var pAcq = document.getElementById('panelAcquisitions');
    var pMem = document.getElementById('panelMembers');
    var btnAcq = document.getElementById('tabBtnAcquisitions');
    var btnMem = document.getElementById('tabBtnMembers');

    if (tab === 'acquisitions') {
        pAcq.style.display = 'block';
        pMem.style.display = 'none';
        btnAcq.className = 'btn btn-sm btn-primary font-weight-bold';
        btnMem.className = 'btn btn-sm btn-light text-muted font-weight-bold';
    } else {
        pAcq.style.display = 'none';
        pMem.style.display = 'block';
        btnAcq.className = 'btn btn-sm btn-light text-muted font-weight-bold';
        btnMem.className = 'btn btn-sm btn-primary font-weight-bold';
    }
}
</script>
@endsection

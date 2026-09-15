@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1320px; margin: 0 auto; padding-top: 24px; padding-bottom: 60px;">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 14px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <span class="badge bg-primary text-white px-3 py-1 text-uppercase mb-2" style="font-size: 11px; letter-spacing: 0.5px; border-radius: 20px;">
                                    Field Marketing System
                                </span>
                                <h2 class="font-weight-bold mb-1 text-dark" style="font-size: 24px; letter-spacing: -0.3px;">
                                    Vendor & Multi-Tier Team Management
                                </h2>
                                <p class="text-muted mb-0" style="font-size: 13px;">
                                    Manage team manager vendors, approve custom customer/business download rates, verify acquisitions, and settle payouts.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius: 12px; background: #ecfdf5; color: #065f46;" role="alert">
            <strong>✓ Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" style="border-radius: 12px; background: #fef2f2; color: #991b1b;" role="alert">
            <strong>⚠ Error:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <!-- Summary KPI Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Total Vendors</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-dark" style="font-size: 26px;">{{ $counts['all'] }}</span>
                        <span class="badge bg-light text-muted px-2 py-1" style="border-radius: 8px;">All Registered</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #fed7aa !important;">
                    <div class="text-warning text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Pending Approval</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-warning" style="font-size: 26px;">{{ $counts['pending'] }}</span>
                        <span class="badge bg-warning text-white px-2 py-1" style="border-radius: 8px;">Action Required</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #bbf7d0 !important;">
                    <div class="text-success text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Active Approved Vendors</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-success" style="font-size: 26px;">{{ $counts['approved'] }}</span>
                        <span class="badge bg-success text-white px-2 py-1" style="border-radius: 8px;">Running</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="text-muted text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Rejected / Inactive</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="font-weight-bold text-secondary" style="font-size: 26px;">{{ $counts['rejected'] }}</span>
                        <span class="badge bg-light text-muted px-2 py-1" style="border-radius: 8px;">Closed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; border: 1px solid #e2e8f0 !important;">
            <div class="card-body p-0">
                <ul class="nav nav-tabs border-0 px-3 pt-3" style="gap: 8px;">
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'all' ? 'active font-weight-bold text-primary' : 'text-muted' }}" href="{{ route('admin.marketing-vendors.index', ['status' => 'all']) }}">
                            All Vendors ({{ $counts['all'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'pending' ? 'active font-weight-bold text-warning' : 'text-muted' }}" href="{{ route('admin.marketing-vendors.index', ['status' => 'pending']) }}">
                            Pending Approval <span class="badge bg-warning text-white ml-1">{{ $counts['pending'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'approved' ? 'active font-weight-bold text-success' : 'text-muted' }}" href="{{ route('admin.marketing-vendors.index', ['status' => 'approved']) }}">
                            Approved ({{ $counts['approved'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'rejected' ? 'active font-weight-bold text-danger' : 'text-muted' }}" href="{{ route('admin.marketing-vendors.index', ['status' => 'rejected']) }}">
                            Rejected ({{ $counts['rejected'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'all_users' ? 'active font-weight-bold text-purple' : 'text-muted' }}" href="{{ route('admin.marketing-vendors.index', ['status' => 'all_users']) }}" style="{{ $status === 'all_users' ? 'color:#6d28d9 !important;' : '' }}">
                            <i class="mdi mdi-account-multiple mr-1"></i>All Registered Users
                            <span class="badge ml-1" style="background:#6d28d9; color:#fff;">{{ $counts['all_users'] }}</span>
                        </a>
                    </li>
                </ul>

                @if($status !== 'all_users')
                <!-- Vendors Table -->
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light" style="background: #f8fafc;">
                            <tr class="text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <th>Vendor / Applicant</th>
                                <th>Vendor Code</th>
                                <th>Location &amp; Type</th>
                                <th>Assigned Rates</th>
                                <th>Team Size</th>
                                <th>Joined Users</th>
                                <th>Total Verified Earnings</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $v)
                            <tr>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $v->applicant_name }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ $v->applicant_phone }}</div>
                                    <span class="badge {{ $v->user_type === 'driver' ? 'bg-info text-white' : 'bg-secondary text-white' }}" style="font-size: 10px;">
                                        {{ ucfirst($v->user_type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($v->vendor_code)
                                        <span class="badge bg-dark text-white px-2 py-1 font-monospace" style="font-size: 12px; letter-spacing: 0.5px;">
                                            {{ $v->vendor_code }}
                                        </span>
                                    @else
                                        <span class="text-muted italic" style="font-size: 12px;">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-weight-semibold text-dark">{{ $v->team_location }}</div>
                                    <div class="text-muted" style="font-size: 11.5px;">{{ $v->team_type }}</div>
                                </td>
                                <td>
                                    @if($v->status === 'approved')
                                        <div><span class="text-muted">Cust:</span> <strong>₹{{ number_format($v->rate_per_customer, 2) }}</strong></div>
                                        <div><span class="text-muted">Biz:</span> <strong>₹{{ number_format($v->rate_per_business, 2) }}</strong></div>
                                    @else
                                        <span class="text-muted">Rate pending</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                        {{ $v->total_members }} Members
                                    </span>
                                </td>
                                <td>
                                    <div><span class="text-muted">Cust:</span> <strong>{{ $v->total_customers }}</strong> <small class="text-success font-weight-bold">({{ $v->verified_customers }} ver)</small></div>
                                    <div><span class="text-muted">Biz:</span> <strong>{{ $v->total_businesses }}</strong> <small class="text-success font-weight-bold">({{ $v->verified_businesses }} ver)</small></div>
                                </td>
                                <td>
                                    <strong class="text-success font-weight-bold" style="font-size: 14px;">
                                        ₹{{ number_format($v->total_earnings, 2) }}
                                    </strong>
                                </td>
                                <td>
                                    @if($v->status === 'approved')
                                        <span class="badge bg-success text-white px-2 py-1">Approved</span>
                                    @elseif($v->status === 'pending')
                                        <span class="badge bg-warning text-white px-2 py-1">Pending Review</span>
                                    @elseif($v->status === 'rejected')
                                        <span class="badge bg-danger text-white px-2 py-1">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary text-white px-2 py-1">{{ ucfirst($v->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                     <div class="d-flex flex-column align-items-end gap-1">
                                         <div class="btn-group" role="group">
                                             @if($v->status === 'pending')
                                             <button type="button" class="btn btn-sm btn-success font-weight-semibold" onclick="toggleIndexSection('approveBox{{ $v->id }}')">
                                                 Approve &amp; Set Rates
                                             </button>
                                             <button type="button" class="btn btn-sm btn-outline-danger font-weight-semibold" onclick="toggleIndexSection('rejectBox{{ $v->id }}')">
                                                 Reject
                                             </button>
                                             @endif
                                             <a href="{{ route('admin.marketing-vendors.show', $v->id) }}" class="btn btn-sm btn-primary font-weight-semibold">
                                                 View Profile &amp; Team
                                             </a>
                                         </div>

                                         {{-- Delete button (all vendors) --}}
                                         <form action="{{ route('admin.marketing-vendors.delete', $v->id) }}" method="POST" onsubmit="return confirm('Delete vendor {{ $v->applicant_name }}? This will also remove their team members and acquisition records. Cannot be undone.');">
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px; border-radius: 6px;">
                                                 <i class="mdi mdi-delete-outline mr-1"></i>Delete Vendor
                                             </button>
                                         </form>
                                     </div>

                                     @if($v->status === 'pending')
                                     <!-- Inline Approve Panel -->
                                     <div id="approveBox{{ $v->id }}" style="display: none; margin-top: 10px; background: #f0fdf4; border: 1.5px solid #86efac; padding: 12px; border-radius: 10px; text-align: left;">
                                         <form action="{{ route('admin.marketing-vendors.approve', $v->id) }}" method="POST">
                                             @csrf
                                             <div class="font-weight-bold text-success mb-1" style="font-size: 13px;">Approve Vendor &amp; Configure Rates</div>
                                             <div class="row">
                                                 <div class="col-6 mb-2">
                                                     <label style="font-size: 11.5px; font-weight: 600;">Rate/Customer (₹) *</label>
                                                     <input type="number" step="0.01" min="0" name="rate_per_customer" class="form-control form-control-sm" value="15.00" required>
                                                 </div>
                                                 <div class="col-6 mb-2">
                                                     <label style="font-size: 11.5px; font-weight: 600;">Rate/Business (₹) *</label>
                                                     <input type="number" step="0.01" min="0" name="rate_per_business" class="form-control form-control-sm" value="50.00" required>
                                                 </div>
                                             </div>
                                             <div class="d-flex justify-content-end gap-1">
                                                 <button type="button" class="btn btn-light btn-sm py-1 px-2" style="font-size: 11px;" onclick="toggleIndexSection('approveBox{{ $v->id }}')">Cancel</button>
                                                 <button type="submit" class="btn btn-success btn-sm py-1 px-2 font-weight-bold" style="font-size: 11px;">✓ Confirm Approval</button>
                                             </div>
                                         </form>
                                     </div>

                                     <!-- Inline Reject Panel -->
                                     <div id="rejectBox{{ $v->id }}" style="display: none; margin-top: 10px; background: #fef2f2; border: 1.5px solid #fca5a5; padding: 12px; border-radius: 10px; text-align: left;">
                                         <form action="{{ route('admin.marketing-vendors.reject', $v->id) }}" method="POST">
                                             @csrf
                                             <div class="font-weight-bold text-danger mb-1" style="font-size: 13px;">Reject Vendor Application</div>
                                             <div class="form-group mb-2">
                                                 <label style="font-size: 11.5px; font-weight: 600;">Rejection Reason *</label>
                                                 <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Reason..." value="Application did not meet territory requirements." required>
                                             </div>
                                             <div class="d-flex justify-content-end gap-1">
                                                 <button type="button" class="btn btn-light btn-sm py-1 px-2" style="font-size: 11px;" onclick="toggleIndexSection('rejectBox{{ $v->id }}')">Cancel</button>
                                                 <button type="submit" class="btn btn-danger btn-sm py-1 px-2 font-weight-bold" style="font-size: 11px;">Confirm Reject</button>
                                             </div>
                                         </form>
                                     </div>
                                     @endif
                                </td>
                            </tr>

                            {{-- Rejected tab: Show users joined under this vendor --}}
                            @if($status === 'rejected' && !empty($rejectedAcquisitions[$v->id]) && count($rejectedAcquisitions[$v->id]) > 0)
                            <tr>
                                <td colspan="9" class="p-0">
                                    <div style="background: #fff7ed; border-top: 2px solid #f97316; border-bottom: 2px dashed #fed7aa; padding: 12px 16px;">
                                        <div class="font-weight-bold mb-2" style="font-size: 12px; color: #c2410c;">
                                            <i class="mdi mdi-account-group mr-1"></i>
                                            Users joined via <strong>{{ $v->applicant_name }}</strong>'s code
                                            <span class="badge ml-1" style="background:#f97316; color:#fff; font-size:10px;">{{ count($rejectedAcquisitions[$v->id]) }}</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0" style="font-size: 12px; background:#fff;">
                                                <thead style="background:#fff7ed;">
                                                    <tr class="text-muted text-uppercase" style="font-size:10px;">
                                                        <th>#</th><th>Name / Phone</th><th>Type</th><th>Status</th><th>Registered</th><th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($rejectedAcquisitions[$v->id] as $i => $acq)
                                                    <tr>
                                                        <td class="text-muted font-weight-bold">{{ $i+1 }}</td>
                                                        <td><div class="font-weight-bold">{{ $acq->user_name }}</div><div class="text-muted" style="font-size:11px;">{{ $acq->user_phone }}</div></td>
                                                        <td><span class="badge {{ $acq->acquired_user_type === 'customer' ? 'bg-info' : 'bg-secondary' }} text-white" style="font-size:10px;">{{ ucfirst($acq->acquired_user_type ?? 'customer') }}</span></td>
                                                        <td>
                                                            @if($acq->verification_status === 'verified') <span class="badge bg-success text-white" style="font-size:10px;">Verified</span>
                                                            @elseif($acq->verification_status === 'rejected') <span class="badge bg-danger text-white" style="font-size:10px;">Rejected</span>
                                                            @else <span class="badge bg-warning text-white" style="font-size:10px;">Pending</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-muted" style="font-size:11px;">{{ $acq->created_at ? \Carbon\Carbon::parse($acq->created_at)->format('d M Y') : '-' }}</td>
                                                        <td class="text-center">
                                                            @if($acq->verification_status !== 'verified')
                                                            <form action="{{ route('admin.marketing-vendors.verify-acquisition', $acq->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-xs btn-success py-0 px-2" style="font-size:11px;">✓ Verify</button></form>
                                                            @endif
                                                            @if($acq->verification_status !== 'rejected')
                                                            <button class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size:11px;" onclick="toggleIndexSection('acqRej{{ $acq->id }}')">✕ Reject</button>
                                                            <div id="acqRej{{ $acq->id }}" style="display:none;margin-top:4px;background:#fef2f2;border:1px solid #fca5a5;padding:8px;border-radius:8px;text-align:left;">
                                                                <form action="{{ route('admin.marketing-vendors.reject-acquisition', $acq->id) }}" method="POST">@csrf
                                                                    <input type="text" name="reason" class="form-control form-control-sm mb-1" value="Verification criteria not met." style="font-size:11px;">
                                                                    <div class="d-flex gap-1"><button type="button" class="btn btn-light btn-sm py-0 px-2" style="font-size:10px;" onclick="toggleIndexSection('acqRej{{ $acq->id }}')">Cancel</button><button type="submit" class="btn btn-danger btn-sm py-0 px-2" style="font-size:10px;">Reject</button></div>
                                                                </form>
                                                            </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr><td colspan="9" class="text-center py-5 text-muted"><div class="mb-2" style="font-size:32px;">👥</div><h6 class="font-weight-bold text-dark">No vendors found in this filter</h6></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($vendors->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $vendors->appends(['status' => $status])->links() }}
                </div>
                @endif

                @else
                {{-- ALL USERS TAB: Every user registered through any freelancer code --}}
                <div class="p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge" style="background:#6d28d9;color:#fff;font-size:12px;padding:6px 12px;border-radius:8px;">
                            <i class="mdi mdi-account-multiple mr-1"></i>Total: {{ count($allUsers) }} users
                        </span>
                        <span class="text-muted" style="font-size:12px;">All users who registered via any freelancer's code under any vendor</span>
                    </div>
                    <div class="table-responsive" style="border:1.5px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                        <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                            <thead style="background:#f8fafc;">
                                <tr class="text-muted text-uppercase" style="font-size:11px;letter-spacing:0.5px;">
                                    <th>#</th>
                                    <th>Registered User</th>
                                    <th>Type</th>
                                    <th>Freelancer (Code)</th>
                                    <th>Under Vendor</th>
                                    <th>Verification Status</th>
                                    <th>Payout Status</th>
                                    <th>Registered On</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allUsers as $i => $acq)
                                <tr>
                                    <td class="text-muted font-weight-bold" style="font-size:12px;">{{ $i+1 }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $acq->user_name }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ $acq->user_phone }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $acq->acquired_user_type === 'customer' ? 'bg-info' : 'bg-secondary' }} text-white" style="font-size:11px;">
                                            {{ ucfirst($acq->acquired_user_type ?? 'customer') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size:12px;">{{ $acq->freelancer_name ?? 'Freelancer' }}</div>
                                        <span class="badge bg-secondary text-white font-monospace" style="font-size:10px;">{{ $acq->freelancer_code ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if($acq->vendor_code_label)
                                            <span class="badge bg-dark text-white font-monospace" style="font-size:10px;">{{ $acq->vendor_code_label }}</span>
                                        @else
                                            <span class="text-muted" style="font-size:11px;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($acq->verification_status === 'verified')
                                            <span class="badge bg-success text-white px-2 py-1">Verified</span>
                                        @elseif($acq->verification_status === 'rejected')
                                            <span class="badge bg-danger text-white px-2 py-1">Rejected</span>
                                        @else
                                            <span class="badge bg-warning text-white px-2 py-1">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($acq->payout_status === 'paid')
                                            <span class="badge bg-primary text-white px-2 py-1">Paid</span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="text-muted" style="font-size:11px;">
                                        {{ $acq->created_at ? \Carbon\Carbon::parse($acq->created_at)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @if($acq->verification_status !== 'verified')
                                        <form action="{{ route('admin.marketing-vendors.verify-acquisition', $acq->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success py-0 px-2" style="font-size:11px;border-radius:5px;">✓ Verify</button>
                                        </form>
                                        @endif
                                        @if($acq->verification_status !== 'rejected')
                                        <button class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size:11px;border-radius:5px;" onclick="toggleIndexSection('auRej{{ $acq->id }}')">✕ Reject</button>
                                        <div id="auRej{{ $acq->id }}" style="display:none;margin-top:4px;background:#fef2f2;border:1px solid #fca5a5;padding:8px;border-radius:8px;text-align:left;min-width:200px;">
                                            <form action="{{ route('admin.marketing-vendors.reject-acquisition', $acq->id) }}" method="POST">
                                                @csrf
                                                <input type="text" name="reason" class="form-control form-control-sm mb-1" value="Verification criteria not met." style="font-size:11px;">
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-light btn-sm py-0 px-2" style="font-size:10px;" onclick="toggleIndexSection('auRej{{ $acq->id }}')">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-sm py-0 px-2 font-weight-bold" style="font-size:10px;">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-center py-5 text-muted"><div style="font-size:32px;">👤</div><h6 class="font-weight-bold text-dark">No users registered yet via freelancer codes</h6></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>
        </div>

    </div>
</div>

<script>
function toggleIndexSection(elementId) {
    var el = document.getElementById(elementId);
    if (!el) return;
    if (el.style.display === 'none' || el.style.display === '') {
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }
}
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 20px 24px 60px;">

        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #e0e7ff; color: #4338ca; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 6px;">
                        Field Marketing System
                    </span>
                    <span class="text-muted" style="font-size: 12px;">• Territory &amp; Team Management</span>
                </div>
                <h3 class="font-weight-bold mb-0 text-dark" style="font-size: 21px; letter-spacing: -0.3px;">
                    Marketing Vendors &amp; Teams
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.marketing-vendors.index', ['status' => $status]) }}" class="btn btn-sm btn-light border" style="font-size: 12px; font-weight: 600; border-radius: 8px; padding: 6px 12px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1" style="vertical-align: -1px;"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    Refresh
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2 px-3 mb-3" style="border-radius: 10px; background: #ecfdf5; color: #065f46; font-size: 13px;" role="alert">
            <strong>✓ Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 6px 12px;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm py-2 px-3 mb-3" style="border-radius: 10px; background: #fef2f2; color: #991b1b; font-size: 13px;" role="alert">
            <strong>⚠ Error:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 6px 12px;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <!-- Compact KPI Summary Metric Cards -->
        <div class="row g-2 mb-3">
            <div class="col-xl-3 col-sm-6 mb-2 mb-xl-0">
                <a href="{{ route('admin.marketing-vendors.index', ['status' => 'all']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm p-3 h-100 kpi-card {{ $status === 'all' ? 'kpi-active' : '' }}" style="border-radius: 10px; background: #ffffff; border-left: 4px solid #6366f1 !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">Total Vendors</div>
                                <div class="font-weight-bold text-dark mt-1" style="font-size: 22px; line-height: 1;">{{ $counts['all'] }}</div>
                            </div>
                            <span class="kpi-icon-box" style="background: #e0e7ff; color: #4338ca;">👥</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 mb-2 mb-xl-0">
                <a href="{{ route('admin.marketing-vendors.index', ['status' => 'pending']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm p-3 h-100 kpi-card {{ $status === 'pending' ? 'kpi-active' : '' }}" style="border-radius: 10px; background: #ffffff; border-left: 4px solid #f59e0b !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-warning text-uppercase" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">Pending Approval</div>
                                <div class="font-weight-bold text-warning mt-1" style="font-size: 22px; line-height: 1;">{{ $counts['pending'] }}</div>
                            </div>
                            <span class="kpi-icon-box" style="background: #fef3c7; color: #b45309;">⏳</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 mb-2 mb-xl-0">
                <a href="{{ route('admin.marketing-vendors.index', ['status' => 'approved']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm p-3 h-100 kpi-card {{ $status === 'approved' ? 'kpi-active' : '' }}" style="border-radius: 10px; background: #ffffff; border-left: 4px solid #10b981 !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-success text-uppercase" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">Active Approved</div>
                                <div class="font-weight-bold text-success mt-1" style="font-size: 22px; line-height: 1;">{{ $counts['approved'] }}</div>
                            </div>
                            <span class="kpi-icon-box" style="background: #dcfce7; color: #15803d;">✓</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 mb-2 mb-xl-0">
                <a href="{{ route('admin.marketing-vendors.index', ['status' => 'rejected']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm p-3 h-100 kpi-card {{ $status === 'rejected' ? 'kpi-active' : '' }}" style="border-radius: 10px; background: #ffffff; border-left: 4px solid #ef4444 !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-danger text-uppercase" style="font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;">Rejected</div>
                                <div class="font-weight-bold text-danger mt-1" style="font-size: 22px; line-height: 1;">{{ $counts['rejected'] }}</div>
                            </div>
                            <span class="kpi-icon-box" style="background: #fee2e2; color: #b91c1c;">✕</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0 !important; background: #ffffff;">
            
            <!-- Compact Filter Tabs -->
            <div class="p-2 border-bottom bg-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <ul class="nav nav-pills compact-tabs gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" href="{{ route('admin.marketing-vendors.index', ['status' => 'all']) }}">
                            All Vendors <span class="tab-badge">{{ $counts['all'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'pending' ? 'active active-pending' : '' }}" href="{{ route('admin.marketing-vendors.index', ['status' => 'pending']) }}">
                            Pending Review <span class="tab-badge badge-warning-custom">{{ $counts['pending'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'approved' ? 'active active-approved' : '' }}" href="{{ route('admin.marketing-vendors.index', ['status' => 'approved']) }}">
                            Approved <span class="tab-badge badge-success-custom">{{ $counts['approved'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'rejected' ? 'active active-rejected' : '' }}" href="{{ route('admin.marketing-vendors.index', ['status' => 'rejected']) }}">
                            Rejected <span class="tab-badge badge-danger-custom">{{ $counts['rejected'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item ml-auto">
                        <a class="nav-link {{ $status === 'all_users' ? 'active active-purple' : '' }}" href="{{ route('admin.marketing-vendors.index', ['status' => 'all_users']) }}">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1" style="vertical-align: -1px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            All Registered Users <span class="tab-badge badge-purple-custom">{{ $counts['all_users'] }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            @if($status !== 'all_users')
            <!-- Vendors Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 modern-compact-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="min-width: 220px;">Vendor / Applicant</th>
                            <th style="min-width: 120px;">Vendor Code</th>
                            <th style="min-width: 160px;">Location &amp; Type</th>
                            <th style="min-width: 140px;">Assigned Rates</th>
                            <th style="min-width: 100px;">Team Size</th>
                            <th style="min-width: 130px;">Joined Users</th>
                            <th style="min-width: 110px;">Status</th>
                            <th class="text-right" style="min-width: 180px; width: 1%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $index => $v)
                        <tr>
                            <!-- # -->
                            <td class="text-muted font-weight-bold" style="font-size: 11.5px;">
                                {{ $vendors->firstItem() ? ($vendors->firstItem() + $index) : ($index + 1) }}
                            </td>

                            <!-- Vendor / Applicant -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="vendor-avatar {{ $v->user_type === 'driver' ? 'avatar-driver' : 'avatar-user' }}">
                                        {{ strtoupper(substr($v->applicant_name ?: 'V', 0, 1)) }}
                                    </div>
                                    <div style="line-height: 1.25;">
                                        <div class="font-weight-bold text-dark" style="font-size: 13px;">
                                            {{ $v->applicant_name ?: 'Unnamed Applicant' }}
                                        </div>
                                        <div class="text-muted" style="font-size: 11.5px;">
                                            {{ $v->applicant_phone ?: 'No phone' }}
                                        </div>
                                        <div class="mt-1 d-flex align-items-center gap-1">
                                            <span class="user-type-badge {{ $v->user_type === 'driver' ? 'badge-driver' : 'badge-consumer' }}">
                                                {{ $v->user_type === 'driver' ? 'Partner Driver' : 'Consumer User' }}
                                            </span>
                                            @if($v->created_at)
                                            <span class="text-muted" style="font-size: 10.5px;">
                                                • {{ \Carbon\Carbon::parse($v->created_at)->format('d M Y') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Vendor Code -->
                            <td>
                                @if(!empty($v->vendor_code))
                                    <span class="code-badge">
                                        {{ $v->vendor_code }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 11px; font-weight: 500;">
                                        Pending Setup
                                    </span>
                                @endif
                            </td>

                            <!-- Location & Type -->
                            <td>
                                <div class="font-weight-semibold text-dark" style="font-size: 12.5px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1 text-muted" style="vertical-align: -1px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    {{ $v->team_location ?: 'All Territories' }}
                                </div>
                                <div class="text-muted mt-0.5" style="font-size: 11px;">
                                    {{ $v->team_type ?: 'Field Team' }}
                                </div>
                            </td>

                            <!-- Assigned Rates -->
                            <td>
                                @if($v->status === 'approved')
                                    <div style="font-size: 11.5px; line-height: 1.4;">
                                        <div><span class="text-muted">Cust:</span> <strong class="text-dark">₹{{ number_format($v->rate_per_customer, 2) }}</strong></div>
                                        <div><span class="text-muted">Biz:</span> <strong class="text-dark">₹{{ number_format($v->rate_per_business, 2) }}</strong></div>
                                    </div>
                                @else
                                    <span class="rate-pending-tag">
                                        Rates Pending
                                    </span>
                                @endif
                            </td>

                            <!-- Team Size -->
                            <td>
                                <span class="team-count-badge">
                                    {{ $v->total_members }} {{ $v->total_members === 1 ? 'member' : 'members' }}
                                </span>
                            </td>

                            <!-- Joined Users -->
                            <td>
                                <div style="font-size: 11.5px; line-height: 1.35;">
                                    <div>
                                        <span class="text-muted">Users:</span> 
                                        <strong>{{ $v->total_customers }}</strong> 
                                        @if($v->verified_customers > 0)
                                            <span class="text-success font-weight-bold">({{ $v->verified_customers }} ver)</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-muted">Biz:</span> 
                                        <strong>{{ $v->total_businesses }}</strong> 
                                        @if($v->verified_businesses > 0)
                                            <span class="text-success font-weight-bold">({{ $v->verified_businesses }} ver)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td>
                                @if($v->status === 'approved')
                                    <span class="status-pill status-pill-approved">
                                        <span class="status-dot"></span> Approved
                                    </span>
                                @elseif($v->status === 'pending')
                                    <span class="status-pill status-pill-pending">
                                        <span class="status-dot"></span> Pending Review
                                    </span>
                                @elseif($v->status === 'rejected')
                                    <span class="status-pill status-pill-rejected">
                                        <span class="status-dot"></span> Rejected
                                    </span>
                                @else
                                    <span class="status-pill status-pill-secondary">
                                        {{ ucfirst($v->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Action Toolbar (Compact single row) -->
                            <td class="text-right text-nowrap">
                                <div class="action-btn-group">
                                    @if($v->status === 'pending')
                                        <!-- Compact Quick Approve -->
                                        <button type="button" 
                                                class="btn-action btn-action-approve"
                                                title="Approve &amp; Configure Rates"
                                                onclick="openApproveModal({{ $v->id }}, '{{ addslashes($v->applicant_name ?: 'Vendor') }}', '{{ addslashes($v->applicant_phone ?: '') }}', '{{ addslashes($v->team_location ?: 'Territory') }}', '{{ $v->rate_per_customer > 0 ? $v->rate_per_customer : '15.00' }}', '{{ $v->rate_per_business > 0 ? $v->rate_per_business : '50.00' }}')">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>Approve</span>
                                        </button>

                                        <!-- Compact Reject -->
                                        <button type="button" 
                                                class="btn-action btn-action-reject"
                                                title="Reject Application"
                                                onclick="openRejectModal({{ $v->id }}, '{{ addslashes($v->applicant_name ?: 'Vendor') }}')">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            <span>Reject</span>
                                        </button>
                                    @endif

                                    <!-- Compact View Profile -->
                                    <a href="{{ route('admin.marketing-vendors.show', $v->id) }}" 
                                       class="btn-action btn-action-view" 
                                       title="View Profile &amp; Team Members">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <span>View</span>
                                    </a>

                                    <!-- Compact Delete Button -->
                                    <form action="{{ route('admin.marketing-vendors.delete', $v->id) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('Delete vendor {{ addslashes($v->applicant_name ?: 'Vendor') }}? This will permanently remove their records and team.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-delete" title="Delete Vendor Record">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Rejected tab: Show acquisitions under this vendor --}}
                        @if($status === 'rejected' && !empty($rejectedAcquisitions[$v->id]) && count($rejectedAcquisitions[$v->id]) > 0)
                        <tr>
                            <td colspan="9" class="p-0 border-0">
                                <div style="background: #fff7ed; border-top: 1.5px solid #fdba74; border-bottom: 1.5px solid #fed7aa; padding: 10px 16px;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="font-weight-bold" style="font-size: 12px; color: #c2410c;">
                                            👥 Users joined via <strong>{{ $v->applicant_name }}</strong>'s code
                                            <span class="badge ml-1" style="background:#f97316; color:#fff; font-size:10px; border-radius: 4px;">{{ count($rejectedAcquisitions[$v->id]) }}</span>
                                        </div>
                                    </div>
                                    <div class="table-responsive bg-white rounded border">
                                        <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                                            <thead class="bg-light text-muted" style="font-size: 10.5px; text-transform: uppercase;">
                                                <tr>
                                                    <th style="width: 40px;">#</th>
                                                    <th>Name / Phone</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th>Registered</th>
                                                    <th class="text-right">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($rejectedAcquisitions[$v->id] as $i => $acq)
                                                <tr>
                                                    <td class="text-muted font-weight-bold">{{ $i + 1 }}</td>
                                                    <td>
                                                        <div class="font-weight-bold text-dark">{{ $acq->user_name }}</div>
                                                        <div class="text-muted" style="font-size: 11px;">{{ $acq->user_phone }}</div>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $acq->acquired_user_type === 'customer' ? 'bg-info' : 'bg-secondary' }} text-white" style="font-size: 10px;">
                                                            {{ ucfirst($acq->acquired_user_type ?? 'customer') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($acq->verification_status === 'verified') 
                                                            <span class="badge bg-success text-white" style="font-size: 10px;">Verified</span>
                                                        @elseif($acq->verification_status === 'rejected') 
                                                            <span class="badge bg-danger text-white" style="font-size: 10px;">Rejected</span>
                                                        @else 
                                                            <span class="badge bg-warning text-white" style="font-size: 10px;">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-muted" style="font-size: 11px;">
                                                        {{ $acq->created_at ? \Carbon\Carbon::parse($acq->created_at)->format('d M Y') : '-' }}
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="d-inline-flex gap-1 align-items-center">
                                                            @if($acq->verification_status !== 'verified')
                                                            <form action="{{ route('admin.marketing-vendors.verify-acquisition', $acq->id) }}" method="POST" class="d-inline m-0">
                                                                @csrf
                                                                <button type="submit" class="btn btn-xs btn-success py-1 px-2" style="font-size: 11px; border-radius: 6px;">✓ Verify</button>
                                                            </form>
                                                            @endif
                                                            @if($acq->verification_status !== 'rejected')
                                                            <form action="{{ route('admin.marketing-vendors.reject-acquisition', $acq->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Reject this acquisition?');">
                                                                @csrf
                                                                <input type="hidden" name="reason" value="Verification criteria not met.">
                                                                <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2" style="font-size: 11px; border-radius: 6px;">✕ Reject</button>
                                                            </form>
                                                            @endif
                                                        </div>
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
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div style="font-size: 32px; opacity: 0.5;">👥</div>
                                <h6 class="font-weight-bold text-dark mt-2 mb-1">No vendors found in this view</h6>
                                <p class="text-muted mb-0" style="font-size: 12.5px;">There are no records matching the selected status filter.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($vendors->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted" style="font-size: 12px;">
                    Showing {{ $vendors->firstItem() }} to {{ $vendors->lastItem() }} of {{ $vendors->total() }} entries
                </span>
                <div>
                    {{ $vendors->appends(['status' => $status])->links() }}
                </div>
            </div>
            @endif

            @else
            {{-- ALL USERS TAB: Every user registered through any freelancer code --}}
            <div class="p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" style="background:#6d28d9; color:#fff; font-size: 11.5px; padding: 5px 10px; border-radius: 6px;">
                            Total: {{ count($allUsers) }} users
                        </span>
                        <span class="text-muted" style="font-size: 12px;">All consumers/businesses acquired through freelancer invite codes</span>
                    </div>
                </div>

                <div class="table-responsive border rounded" style="background: #ffffff;">
                    <table class="table table-hover align-middle mb-0 modern-compact-table">
                        <thead>
                            <tr>
                                <th style="width: 45px;">#</th>
                                <th>Registered User</th>
                                <th>Type</th>
                                <th>Freelancer (Code)</th>
                                <th>Under Vendor</th>
                                <th>Verification</th>
                                <th>Payout</th>
                                <th>Registered Date</th>
                                <th class="text-right" style="min-width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allUsers as $i => $acq)
                            <tr>
                                <td class="text-muted font-weight-bold" style="font-size: 11.5px;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 12.5px;">{{ $acq->user_name }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $acq->user_phone }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $acq->acquired_user_type === 'customer' ? 'bg-info' : 'bg-secondary' }} text-white" style="font-size: 10.5px;">
                                        {{ ucfirst($acq->acquired_user_type ?? 'customer') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 12px;">{{ $acq->freelancer_name ?? 'Freelancer' }}</div>
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 10px;">{{ $acq->freelancer_code ?? '-' }}</span>
                                </td>
                                <td>
                                    @if($acq->vendor_code_label)
                                        <span class="badge bg-dark text-white font-monospace" style="font-size: 10px;">{{ $acq->vendor_code_label }}</span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($acq->verification_status === 'verified')
                                        <span class="status-pill status-pill-approved" style="font-size: 11px; padding: 2px 7px;">
                                            <span class="status-dot"></span> Verified
                                        </span>
                                    @elseif($acq->verification_status === 'rejected')
                                        <span class="status-pill status-pill-rejected" style="font-size: 11px; padding: 2px 7px;">
                                            <span class="status-dot"></span> Rejected
                                        </span>
                                    @else
                                        <span class="status-pill status-pill-pending" style="font-size: 11px; padding: 2px 7px;">
                                            <span class="status-dot"></span> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($acq->payout_status === 'paid')
                                        <span class="badge bg-primary text-white px-2 py-1" style="font-size: 10px;">Paid</span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 10px;">Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size: 11.5px;">
                                    {{ $acq->created_at ? \Carbon\Carbon::parse($acq->created_at)->format('d M Y') : '-' }}
                                </td>
                                <td class="text-right text-nowrap">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        @if($acq->verification_status !== 'verified')
                                        <form action="{{ route('admin.marketing-vendors.verify-acquisition', $acq->id) }}" method="POST" class="d-inline m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success py-1 px-2" style="font-size: 11px; border-radius: 6px;">
                                                ✓ Verify
                                            </button>
                                        </form>
                                        @endif
                                        @if($acq->verification_status !== 'rejected')
                                        <form action="{{ route('admin.marketing-vendors.reject-acquisition', $acq->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Reject verification for {{ addslashes($acq->user_name) }}?');">
                                            @csrf
                                            <input type="hidden" name="reason" value="Verification criteria not met.">
                                            <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2" style="font-size: 11px; border-radius: 6px;">
                                                ✕ Reject
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div style="font-size: 32px;">👤</div>
                                    <h6 class="font-weight-bold text-dark mt-2">No users registered yet via freelancer codes</h6>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- 1. SLEEK APPROVE & SET RATES MODAL         -->
<!-- ========================================== -->
<div class="modal fade" id="approveVendorModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 440px;">
        <div class="modal-content border-0 shadow" style="border-radius: 14px; overflow: hidden;">
            <form id="approveVendorForm" action="" method="POST">
                @csrf
                <div class="modal-header py-3 px-4" style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0;">
                    <div>
                        <span class="badge bg-success text-white px-2 py-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px; border-radius: 4px;">Approval &amp; Activation</span>
                        <h5 class="modal-title font-weight-bold text-dark mt-1" id="approveModalLabel" style="font-size: 16px;">Approve Marketing Vendor</h5>
                    </div>
                    <button type="button" class="close text-dark" data-dismiss="modal" onclick="closeApproveModal()" aria-label="Close" style="font-size: 20px; line-height: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <!-- Vendor Summary Card -->
                    <div class="p-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="vendor-avatar avatar-driver" style="width: 34px; height: 34px; font-size: 13px;">
                                <span id="approveModalInitials">V</span>
                            </div>
                            <div>
                                <div class="font-weight-bold text-dark" id="approveModalVendorName" style="font-size: 13.5px;">Vendor Name</div>
                                <div class="text-muted" id="approveModalVendorMeta" style="font-size: 11.5px;">Phone • Location</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold text-dark mb-1" style="font-size: 12.5px;">
                            Rate per Customer Download (₹) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white text-muted" style="border-radius: 8px 0 0 8px;">₹</span>
                            <input type="number" step="0.01" min="0" name="rate_per_customer" id="approveCustRateInput" class="form-control form-control-sm font-weight-bold" value="15.00" required style="border-radius: 0 8px 8px 0; font-size: 13px;">
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Commission paid to this vendor per verified customer app registration.</small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label font-weight-bold text-dark mb-1" style="font-size: 12.5px;">
                            Rate per Business Download (₹) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white text-muted" style="border-radius: 8px 0 0 8px;">₹</span>
                            <input type="number" step="0.01" min="0" name="rate_per_business" id="approveBizRateInput" class="form-control form-control-sm font-weight-bold" value="50.00" required style="border-radius: 0 8px 8px 0; font-size: 13px;">
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Commission paid to this vendor per verified business app registration.</small>
                    </div>
                </div>
                <div class="modal-footer py-2 px-4 bg-light border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-light border font-weight-semibold" data-dismiss="modal" onclick="closeApproveModal()" style="font-size: 12px; border-radius: 8px; padding: 6px 14px;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-success font-weight-bold" style="font-size: 12px; border-radius: 8px; padding: 6px 18px;">
                        ✓ Confirm Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. SLEEK REJECT VENDOR MODAL               -->
<!-- ========================================== -->
<div class="modal fade" id="rejectVendorModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 440px;">
        <div class="modal-content border-0 shadow" style="border-radius: 14px; overflow: hidden;">
            <form id="rejectVendorForm" action="" method="POST">
                @csrf
                <div class="modal-header py-3 px-4" style="background: #fef2f2; border-bottom: 1px solid #fecdd3;">
                    <div>
                        <span class="badge bg-danger text-white px-2 py-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px; border-radius: 4px;">Decline Application</span>
                        <h5 class="modal-title font-weight-bold text-danger mt-1" id="rejectModalLabel" style="font-size: 16px;">Reject Vendor Application</h5>
                    </div>
                    <button type="button" class="close text-dark" data-dismiss="modal" onclick="closeRejectModal()" aria-label="Close" style="font-size: 20px; line-height: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3" style="font-size: 12.5px;">
                        Are you sure you want to reject application for <strong class="text-dark" id="rejectModalVendorName">this vendor</strong>?
                    </p>
                    <div class="form-group mb-0">
                        <label class="form-label font-weight-bold text-dark mb-1" style="font-size: 12px;">
                            Rejection Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required style="font-size: 12.5px; border-radius: 8px;">Application did not meet territory requirements.</textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-4 bg-light border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-light border font-weight-semibold" data-dismiss="modal" onclick="closeRejectModal()" style="font-size: 12px; border-radius: 8px; padding: 6px 14px;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-danger font-weight-bold" style="font-size: 12px; border-radius: 8px; padding: 6px 18px;">
                        ✕ Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ── Custom UI & Compact Table Styles ── */
.kpi-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    border: 1px solid #e2e8f0 !important;
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important;
}
.kpi-active {
    background: #f8fafc !important;
    border-color: #cbd5e1 !important;
}
.kpi-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

/* Compact Tabs */
.compact-tabs .nav-link {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    border-radius: 8px;
    padding: 6px 12px;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    gap: 6px;
    background: transparent;
}
.compact-tabs .nav-link:hover {
    color: #1e293b;
    background: #f1f5f9;
}
.compact-tabs .nav-link.active {
    background: #4f46e5 !important;
    color: #ffffff !important;
}
.compact-tabs .nav-link.active-pending {
    background: #f59e0b !important;
    color: #ffffff !important;
}
.compact-tabs .nav-link.active-approved {
    background: #10b981 !important;
    color: #ffffff !important;
}
.compact-tabs .nav-link.active-rejected {
    background: #ef4444 !important;
    color: #ffffff !important;
}
.compact-tabs .nav-link.active-purple {
    background: #7c3aed !important;
    color: #ffffff !important;
}
.tab-badge {
    font-size: 10.5px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    background: rgba(0,0,0,0.08);
    color: inherit;
}
.compact-tabs .nav-link.active .tab-badge {
    background: rgba(255,255,255,0.25);
    color: #ffffff;
}

/* Modern Compact Table */
.modern-compact-table {
    border-collapse: separate;
    border-spacing: 0;
}
.modern-compact-table thead th {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #475569;
    background: #f8fafc;
    border-bottom: 1.5px solid #e2e8f0;
    padding: 10px 14px;
    vertical-align: middle;
    white-space: nowrap;
}
.modern-compact-table tbody td {
    padding: 9px 14px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 12.5px;
}
.modern-compact-table tbody tr:hover td {
    background-color: #fcfcfd;
}

/* Vendor Avatar */
.vendor-avatar {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12.5px;
    flex-shrink: 0;
}
.avatar-driver {
    background: #e0e7ff;
    color: #4338ca;
}
.avatar-user {
    background: #f1f5f9;
    color: #475569;
}

/* Badges */
.user-type-badge {
    font-size: 9.5px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.badge-driver {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #dbeafe;
}
.badge-consumer {
    background: #f3f4f6;
    color: #4b5563;
    border: 1px solid #e5e7eb;
}

.code-badge {
    display: inline-block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #0f172a;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.rate-pending-tag {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    color: #b45309;
    background: #fffbeb;
    padding: 2px 7px;
    border-radius: 5px;
    border: 1px solid #fef3c7;
}

.team-count-badge {
    display: inline-block;
    font-size: 11.5px;
    font-weight: 600;
    color: #334155;
    background: #f8fafc;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

/* Status Pills */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 20px;
    text-transform: capitalize;
}
.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.status-pill-approved {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.status-pill-approved .status-dot {
    background: #10b981;
}
.status-pill-pending {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fde68a;
}
.status-pill-pending .status-dot {
    background: #f59e0b;
}
.status-pill-rejected {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecdd3;
}
.status-pill-rejected .status-dot {
    background: #ef4444;
}
.status-pill-secondary {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

/* Compact Action Buttons Toolbar */
.action-btn-group {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    vertical-align: middle;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    height: 27px;
    font-size: 11.5px;
    font-weight: 600;
    line-height: 1;
    border-radius: 6px;
    border: 1px solid transparent;
    transition: all 0.15s ease;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
}

.btn-action-approve {
    background: #10b981;
    color: #ffffff;
    border-color: #059669;
}
.btn-action-approve:hover {
    background: #059669;
    color: #ffffff;
}

.btn-action-reject {
    background: #ffffff;
    color: #ef4444;
    border-color: #fca5a5;
}
.btn-action-reject:hover {
    background: #fef2f2;
    color: #dc2626;
    border-color: #f87171;
}

.btn-action-view {
    background: #f8fafc;
    color: #334155;
    border-color: #cbd5e1;
}
.btn-action-view:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}

.btn-action-delete {
    background: #ffffff;
    color: #94a3b8;
    border-color: #e2e8f0;
    padding: 4px 6px;
}
.btn-action-delete:hover {
    background: #fef2f2;
    color: #ef4444;
    border-color: #fca5a5;
}
</style>

<script>
function openApproveModal(id, name, phone, location, custRate, bizRate) {
    const form = document.getElementById('approveVendorForm');
    form.action = "{{ url('admin/marketing-vendors') }}/" + id + "/approve";
    
    document.getElementById('approveModalVendorName').textContent = name;
    document.getElementById('approveModalVendorMeta').textContent = (phone || 'No phone') + ' • ' + (location || 'Territory');
    document.getElementById('approveModalInitials').textContent = (name || 'V').charAt(0).toUpperCase();
    
    document.getElementById('approveCustRateInput').value = custRate || '15.00';
    document.getElementById('approveBizRateInput').value = bizRate || '50.00';

    if (window.jQuery && $('#approveVendorModal').modal) {
        $('#approveVendorModal').modal('show');
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        new bootstrap.Modal(document.getElementById('approveVendorModal')).show();
    } else {
        document.getElementById('approveVendorModal').classList.add('show');
        document.getElementById('approveVendorModal').style.display = 'block';
    }
}

function closeApproveModal() {
    if (window.jQuery && $('#approveVendorModal').modal) {
        $('#approveVendorModal').modal('hide');
    } else {
        document.getElementById('approveVendorModal').classList.remove('show');
        document.getElementById('approveVendorModal').style.display = 'none';
    }
}

function openRejectModal(id, name) {
    const form = document.getElementById('rejectVendorForm');
    form.action = "{{ url('admin/marketing-vendors') }}/" + id + "/reject";
    
    document.getElementById('rejectModalVendorName').textContent = name;

    if (window.jQuery && $('#rejectVendorModal').modal) {
        $('#rejectVendorModal').modal('show');
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        new bootstrap.Modal(document.getElementById('rejectVendorModal')).show();
    } else {
        document.getElementById('rejectVendorModal').classList.add('show');
        document.getElementById('rejectVendorModal').style.display = 'block';
    }
}

function closeRejectModal() {
    if (window.jQuery && $('#rejectVendorModal').modal) {
        $('#rejectVendorModal').modal('hide');
    } else {
        document.getElementById('rejectVendorModal').classList.remove('show');
        document.getElementById('rejectVendorModal').style.display = 'none';
    }
}
</script>
@endsection

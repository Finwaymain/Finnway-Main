@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f1f5f9; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 20px 20px 60px;">

        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 6px; border: 1px solid #c7d2fe;">
                        Field Marketing System
                    </span>
                    <span style="color: #475569; font-size: 12.5px; font-weight: 600;">• Territory &amp; Team Management</span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 22px; color: #0f172a; letter-spacing: -0.3px;">
                    Marketing Vendors &amp; Teams
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.marketing-vendors.index', ['status' => $status]) }}" class="btn btn-sm" style="background: #ffffff; color: #1e293b; font-size: 12px; font-weight: 700; border-radius: 8px; padding: 6px 14px; border: 1.5px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mr-1" style="vertical-align: -1px;"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    Refresh
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2 px-3 mb-3" style="border-radius: 10px; background: #ecfdf5; color: #065f46; font-size: 13px; font-weight: 600; border-left: 4px solid #10b981 !important;" role="alert">
            <strong>✓ Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 6px 12px; color: #065f46;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm py-2 px-3 mb-3" style="border-radius: 10px; background: #fef2f2; color: #991b1b; font-size: 13px; font-weight: 600; border-left: 4px solid #ef4444 !important;" role="alert">
            <strong>⚠ Error:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 6px 12px; color: #991b1b;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <!-- Compact KPI Summary Metric Cards -->
        <div class="row g-2 mb-3">
            <div class="col-xl-3 col-sm-6 mb-2 mb-xl-0">
                <a href="{{ route('admin.marketing-vendors.index', ['status' => 'all']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm p-3 h-100 kpi-card {{ $status === 'all' ? 'kpi-active' : '' }}" style="border-radius: 10px; background: #ffffff; border-left: 4px solid #4f46e5 !important; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Total Vendors</div>
                                <div class="mt-1" style="font-size: 24px; font-weight: 900; color: #0f172a; line-height: 1;">{{ $counts['all'] }}</div>
                            </div>
                            <span class="kpi-icon-box" style="background: #e0e7ff; color: #3730a3; font-weight: bold;">👥</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 mb-2 mb-xl-0">
                <a href="{{ route('admin.marketing-vendors.index', ['status' => 'pending']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm p-3 h-100 kpi-card {{ $status === 'pending' ? 'kpi-active' : '' }}" style="border-radius: 10px; background: #ffffff; border-left: 4px solid #d97706 !important; border: 1px solid #fed7aa;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div style="font-size: 11px; font-weight: 800; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px;">Pending Approval</div>
                                <div class="mt-1" style="font-size: 24px; font-weight: 900; color: #b45309; line-height: 1;">{{ $counts['pending'] }}</div>
                            </div>
                            <span class="kpi-icon-box" style="background: #fef3c7; color: #92400e; font-weight: bold;">⏳</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 mb-2 mb-xl-0">
                <a href="{{ route('admin.marketing-vendors.index', ['status' => 'approved']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm p-3 h-100 kpi-card {{ $status === 'approved' ? 'kpi-active' : '' }}" style="border-radius: 10px; background: #ffffff; border-left: 4px solid #16a34a !important; border: 1px solid #bbf7d0;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div style="font-size: 11px; font-weight: 800; color: #15803d; text-transform: uppercase; letter-spacing: 0.5px;">Active Approved</div>
                                <div class="mt-1" style="font-size: 24px; font-weight: 900; color: #15803d; line-height: 1;">{{ $counts['approved'] }}</div>
                            </div>
                            <span class="kpi-icon-box" style="background: #dcfce7; color: #166534; font-weight: bold;">✓</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 mb-2 mb-xl-0">
                <a href="{{ route('admin.marketing-vendors.index', ['status' => 'rejected']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm p-3 h-100 kpi-card {{ $status === 'rejected' ? 'kpi-active' : '' }}" style="border-radius: 10px; background: #ffffff; border-left: 4px solid #dc2626 !important; border: 1px solid #fecdd3;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div style="font-size: 11px; font-weight: 800; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.5px;">Rejected</div>
                                <div class="mt-1" style="font-size: 24px; font-weight: 900; color: #b91c1c; line-height: 1;">{{ $counts['rejected'] }}</div>
                            </div>
                            <span class="kpi-icon-box" style="background: #fee2e2; color: #991b1b; font-weight: bold;">✕</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #cbd5e1 !important; background: #ffffff;">
            
            <!-- Compact Filter Tabs -->
            <div class="p-2 border-bottom bg-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px; border-bottom: 1.5px solid #e2e8f0 !important;">
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
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mr-1" style="vertical-align: -1px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
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
                            <th style="width: 45px; text-align: center;">#</th>
                            <th style="min-width: 220px;">Vendor / Applicant</th>
                            <th style="min-width: 120px;">Vendor Code</th>
                            <th style="min-width: 150px;">Territory &amp; Type</th>
                            <th style="min-width: 130px;">Assigned Rates</th>
                            <th style="min-width: 95px;">Team Size</th>
                            <th style="min-width: 130px;">Joined Users</th>
                            <th style="min-width: 115px;">Status</th>
                            <th class="text-right" style="min-width: 170px; width: 1%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $index => $v)
                        <tr id="vendorRow{{ $v->id }}">
                            <!-- # -->
                            <td class="text-center font-weight-bold" style="font-size: 11.5px; color: #475569;">
                                {{ $vendors->firstItem() ? ($vendors->firstItem() + $index) : ($index + 1) }}
                            </td>

                            <!-- Vendor / Applicant -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="vendor-avatar {{ $v->user_type === 'driver' ? 'avatar-driver' : 'avatar-user' }}">
                                        {{ strtoupper(substr($v->applicant_name ?: 'V', 0, 1)) }}
                                    </div>
                                    <div style="line-height: 1.25;">
                                        <div class="font-weight-bold" style="font-size: 13px; color: #0f172a;">
                                            {{ $v->applicant_name ?: 'Unnamed Applicant' }}
                                        </div>
                                        <div style="font-size: 11.5px; color: #334155; font-weight: 600;">
                                            {{ $v->applicant_phone ?: 'No phone' }}
                                        </div>
                                        <div class="mt-1 d-flex align-items-center gap-1">
                                            <span class="user-type-badge {{ $v->user_type === 'driver' ? 'badge-driver' : 'badge-consumer' }}">
                                                {{ $v->user_type === 'driver' ? 'Partner Driver' : 'Consumer User' }}
                                            </span>
                                            @if($v->created_at)
                                            <span style="color: #64748b; font-size: 11px; font-weight: 500;">
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
                                    <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 11px; font-weight: 600;">
                                        Pending Setup
                                    </span>
                                @endif
                            </td>

                            <!-- Territory & Type -->
                            <td>
                                <div class="font-weight-bold" style="font-size: 12.5px; color: #0f172a;">
                                    📍 {{ $v->team_location ?: 'All Territories' }}
                                </div>
                                <div class="mt-0.5" style="font-size: 11.5px; color: #475569; font-weight: 500;">
                                    {{ $v->team_type ?: 'Field Team' }}
                                </div>
                            </td>

                            <!-- Assigned Rates -->
                            <td>
                                @if($v->status === 'approved')
                                    <div style="font-size: 12px; line-height: 1.4;">
                                        <div><span style="color: #64748b; font-weight: 500;">Cust:</span> <strong style="color: #0f172a;">₹{{ number_format($v->rate_per_customer, 2) }}</strong></div>
                                        <div><span style="color: #64748b; font-weight: 500;">Biz:</span> <strong style="color: #0f172a;">₹{{ number_format($v->rate_per_business, 2) }}</strong></div>
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
                                <div style="font-size: 12px; line-height: 1.35;">
                                    <div>
                                        <span style="color: #64748b; font-weight: 500;">Users:</span> 
                                        <strong style="color: #0f172a;">{{ $v->total_customers }}</strong> 
                                        @if($v->verified_customers > 0)
                                            <span style="color: #15803d; font-weight: 800;">({{ $v->verified_customers }} ver)</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span style="color: #64748b; font-weight: 500;">Biz:</span> 
                                        <strong style="color: #0f172a;">{{ $v->total_businesses }}</strong> 
                                        @if($v->verified_businesses > 0)
                                            <span style="color: #15803d; font-weight: 800;">({{ $v->verified_businesses }} ver)</span>
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

                            <!-- Compact Single-Row Action Toolbar (NO MODAL) -->
                            <td class="text-right text-nowrap">
                                <div class="action-btn-group">
                                    @if($v->status === 'pending')
                                        <!-- Inline Approve Toggler -->
                                        <button type="button" 
                                                class="btn-action btn-action-approve"
                                                title="Approve &amp; Configure Rates"
                                                onclick="openApproveBar({{ $v->id }})">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>Approve</span>
                                        </button>

                                        <!-- Inline Reject Toggler -->
                                        <button type="button" 
                                                class="btn-action btn-action-reject"
                                                title="Reject Application"
                                                onclick="openRejectBar({{ $v->id }})">
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

                        <!-- DEDICATED COLLAPSIBLE ACTION SUB-ROW (NO MODAL: Pure HTML row, perfectly clickable) -->
                        <tr id="actionRow{{ $v->id }}" style="display: none; background: #f8fafc;">
                            <td colspan="9" style="padding: 10px 16px; border-top: none; border-bottom: 2px solid #cbd5e1;">
                                
                                <!-- 1. Inline Quick Approve Bar -->
                                <div id="approveBox{{ $v->id }}" style="display: none;">
                                    <form action="{{ route('admin.marketing-vendors.approve', $v->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2.5" style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge" style="background: #15803d; color: #ffffff; font-size: 11px; font-weight: 700; padding: 3px 7px; border-radius: 4px;">
                                                    Approve Vendor
                                                </span>
                                                <strong style="font-size: 13px; color: #14532d;">
                                                    {{ $v->applicant_name }} ({{ $v->applicant_phone ?: 'No phone' }})
                                                </strong>
                                                <span style="color: #166534; font-size: 12px; font-weight: 500;">• Territory: {{ $v->team_location ?: 'General' }}</span>
                                            </div>

                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <div class="d-flex align-items-center gap-1">
                                                    <label class="mb-0 font-weight-bold" style="font-size: 12px; color: #0f172a; white-space: nowrap;">
                                                        Rate/Customer:
                                                    </label>
                                                    <div class="input-group input-group-sm" style="width: 115px;">
                                                        <div class="input-group-prepend"><span class="input-group-text bg-white font-weight-bold" style="color: #0f172a; font-size: 12px;">₹</span></div>
                                                        <input type="number" step="0.01" min="0" name="rate_per_customer" class="form-control form-control-sm font-weight-bold" value="{{ $v->rate_per_customer > 0 ? $v->rate_per_customer : '15.00' }}" required style="font-size: 12px; color: #0f172a;">
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center gap-1">
                                                    <label class="mb-0 font-weight-bold" style="font-size: 12px; color: #0f172a; white-space: nowrap;">
                                                        Rate/Business:
                                                    </label>
                                                    <div class="input-group input-group-sm" style="width: 115px;">
                                                        <div class="input-group-prepend"><span class="input-group-text bg-white font-weight-bold" style="color: #0f172a; font-size: 12px;">₹</span></div>
                                                        <input type="number" step="0.01" min="0" name="rate_per_business" class="form-control form-control-sm font-weight-bold" value="{{ $v->rate_per_business > 0 ? $v->rate_per_business : '50.00' }}" required style="font-size: 12px; color: #0f172a;">
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center gap-1">
                                                    <button type="submit" class="btn btn-sm btn-success font-weight-bold" style="font-size: 12px; padding: 4px 12px; border-radius: 6px;">
                                                        ✓ Confirm Approval
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light border font-weight-bold" onclick="closeActionRow({{ $v->id }})" style="font-size: 12px; color: #334155; padding: 4px 10px; border-radius: 6px;">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- 2. Inline Quick Reject Bar -->
                                <div id="rejectBox{{ $v->id }}" style="display: none;">
                                    <form action="{{ route('admin.marketing-vendors.reject', $v->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2.5" style="background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 8px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge" style="background: #b91c1c; color: #ffffff; font-size: 11px; font-weight: 700; padding: 3px 7px; border-radius: 4px;">
                                                    Decline Application
                                                </span>
                                                <strong style="font-size: 13px; color: #991b1b;">
                                                    Reject {{ $v->applicant_name }}
                                                </strong>
                                            </div>

                                            <div class="d-flex flex-grow-1 align-items-center gap-2" style="max-width: 480px;">
                                                <input type="text" name="rejection_reason" class="form-control form-control-sm font-weight-medium" value="Application did not meet territory requirements." placeholder="Enter reason..." required style="font-size: 12px; color: #0f172a; border-color: #fca5a5;">
                                            </div>

                                            <div class="d-flex align-items-center gap-1">
                                                <button type="submit" class="btn btn-sm btn-danger font-weight-bold" style="font-size: 12px; padding: 4px 12px; border-radius: 6px;">
                                                    ✕ Confirm Reject
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border font-weight-bold" onclick="closeActionRow({{ $v->id }})" style="font-size: 12px; color: #334155; padding: 4px 10px; border-radius: 6px;">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
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
                                            <thead class="bg-light" style="font-size: 10.5px; text-transform: uppercase; color: #334155;">
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
                                                    <td class="font-weight-bold" style="color: #475569;">{{ $i + 1 }}</td>
                                                    <td>
                                                        <div class="font-weight-bold" style="color: #0f172a;">{{ $acq->user_name }}</div>
                                                        <div style="font-size: 11px; color: #334155; font-weight: 500;">{{ $acq->user_phone }}</div>
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
                                                    <td style="font-size: 11px; color: #475569; font-weight: 500;">
                                                        {{ $acq->created_at ? \Carbon\Carbon::parse($acq->created_at)->format('d M Y') : '-' }}
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="d-inline-flex gap-1 align-items-center">
                                                            @if($acq->verification_status !== 'verified')
                                                            <form action="{{ route('admin.marketing-vendors.verify-acquisition', $acq->id) }}" method="POST" class="d-inline m-0">
                                                                @csrf
                                                                <button type="submit" class="btn btn-xs btn-success py-1 px-2" style="font-size: 11px; border-radius: 6px; font-weight: 700;">✓ Verify</button>
                                                            </form>
                                                            @endif
                                                            @if($acq->verification_status !== 'rejected')
                                                            <form action="{{ route('admin.marketing-vendors.reject-acquisition', $acq->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Reject this acquisition?');">
                                                                @csrf
                                                                <input type="hidden" name="reason" value="Verification criteria not met.">
                                                                <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2" style="font-size: 11px; border-radius: 6px; font-weight: 700;">✕ Reject</button>
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
                            <td colspan="9" class="text-center py-5">
                                <div style="font-size: 32px; opacity: 0.6;">👥</div>
                                <h6 class="font-weight-bold mt-2 mb-1" style="color: #0f172a; font-size: 15px;">No vendors found in this view</h6>
                                <p style="color: #64748b; font-size: 12.5px;" class="mb-0">There are no records matching the selected status filter.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($vendors->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center" style="background: #ffffff; border-color: #e2e8f0 !important;">
                <span style="font-size: 12.5px; color: #475569; font-weight: 600;">
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
                        <span class="badge" style="background:#6d28d9; color:#fff; font-size: 11.5px; font-weight: 700; padding: 5px 10px; border-radius: 6px;">
                            Total: {{ count($allUsers) }} users
                        </span>
                        <span style="color: #475569; font-size: 12px; font-weight: 600;">All consumers/businesses acquired through freelancer invite codes</span>
                    </div>
                </div>

                <div class="table-responsive border rounded" style="background: #ffffff; border-color: #cbd5e1 !important;">
                    <table class="table table-hover align-middle mb-0 modern-compact-table">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">#</th>
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
                                <td class="text-center font-weight-bold" style="font-size: 11.5px; color: #475569;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold" style="font-size: 12.5px; color: #0f172a;">{{ $acq->user_name }}</div>
                                    <div style="font-size: 11px; color: #334155; font-weight: 600;">{{ $acq->user_phone }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $acq->acquired_user_type === 'customer' ? 'badge-consumer' : 'badge-driver' }}" style="font-size: 10.5px;">
                                        {{ ucfirst($acq->acquired_user_type ?? 'customer') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="font-weight-bold" style="font-size: 12px; color: #0f172a;">{{ $acq->freelancer_name ?? 'Freelancer' }}</div>
                                    <span class="badge font-monospace" style="font-size: 10px; background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1;">{{ $acq->freelancer_code ?? '-' }}</span>
                                </td>
                                <td>
                                    @if($acq->vendor_code_label)
                                        <span class="code-badge" style="font-size: 10px;">{{ $acq->vendor_code_label }}</span>
                                    @else
                                        <span style="color: #64748b; font-size: 11px;">—</span>
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
                                        <span class="badge" style="background: #2563eb; color: #fff; font-size: 10px; font-weight: 700; padding: 3px 6px;">Paid</span>
                                    @else
                                        <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 10px; font-weight: 700; padding: 3px 6px;">Unpaid</span>
                                    @endif
                                </td>
                                <td style="font-size: 11.5px; color: #475569; font-weight: 600;">
                                    {{ $acq->created_at ? \Carbon\Carbon::parse($acq->created_at)->format('d M Y') : '-' }}
                                </td>
                                <td class="text-right text-nowrap">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        @if($acq->verification_status !== 'verified')
                                        <form action="{{ route('admin.marketing-vendors.verify-acquisition', $acq->id) }}" method="POST" class="d-inline m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success py-1 px-2" style="font-size: 11px; border-radius: 6px; font-weight: 700;">
                                                ✓ Verify
                                            </button>
                                        </form>
                                        @endif
                                        @if($acq->verification_status !== 'rejected')
                                        <form action="{{ route('admin.marketing-vendors.reject-acquisition', $acq->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Reject verification for {{ addslashes($acq->user_name) }}?');">
                                            @csrf
                                            <input type="hidden" name="reason" value="Verification criteria not met.">
                                            <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2" style="font-size: 11px; border-radius: 6px; font-weight: 700;">
                                                ✕ Reject
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div style="font-size: 32px; opacity: 0.6;">👤</div>
                                    <h6 class="font-weight-bold mt-2" style="color: #0f172a;">No users registered yet via freelancer codes</h6>
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

<style>
/* ── High-Contrast & Compact Table Styles ── */
.kpi-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    border: 1px solid #cbd5e1 !important;
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}
.kpi-active {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
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
    font-weight: 700;
    color: #334155;
    border-radius: 8px;
    padding: 6px 12px;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    gap: 6px;
    background: transparent;
}
.compact-tabs .nav-link:hover {
    color: #0f172a;
    background: #f1f5f9;
}
.compact-tabs .nav-link.active {
    background: #4f46e5 !important;
    color: #ffffff !important;
}
.compact-tabs .nav-link.active-pending {
    background: #d97706 !important;
    color: #ffffff !important;
}
.compact-tabs .nav-link.active-approved {
    background: #16a34a !important;
    color: #ffffff !important;
}
.compact-tabs .nav-link.active-rejected {
    background: #dc2626 !important;
    color: #ffffff !important;
}
.compact-tabs .nav-link.active-purple {
    background: #7c3aed !important;
    color: #ffffff !important;
}
.tab-badge {
    font-size: 10.5px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 10px;
    background: #e2e8f0;
    color: #1e293b;
}
.compact-tabs .nav-link.active .tab-badge {
    background: rgba(255,255,255,0.3);
    color: #ffffff;
}

/* Modern Compact Table */
.modern-compact-table {
    border-collapse: separate;
    border-spacing: 0;
}
.modern-compact-table thead th {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #1e293b;
    background: #f8fafc;
    border-bottom: 2px solid #cbd5e1;
    padding: 10px 14px;
    vertical-align: middle;
    white-space: nowrap;
}
.modern-compact-table tbody td {
    padding: 9px 14px;
    vertical-align: middle;
    border-bottom: 1px solid #e2e8f0;
    font-size: 12.5px;
    color: #1e293b;
}
.modern-compact-table tbody tr:hover td {
    background-color: #f8fafc;
}

/* Vendor Avatar */
.vendor-avatar {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 13px;
    flex-shrink: 0;
}
.avatar-driver {
    background: #e0e7ff;
    color: #3730a3;
    border: 1px solid #c7d2fe;
}
.avatar-user {
    background: #f1f5f9;
    color: #1e293b;
    border: 1px solid #cbd5e1;
}

/* Badges with Strong Contrast */
.user-type-badge {
    font-size: 9.5px;
    font-weight: 800;
    padding: 1px 6px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.badge-driver {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}
.badge-consumer {
    background: #f3f4f6;
    color: #1f2937;
    border: 1px solid #d1d5db;
}

.code-badge {
    display: inline-block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
    color: #0f172a;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
}

.rate-pending-tag {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    color: #92400e;
    background: #fef3c7;
    padding: 2px 7px;
    border-radius: 5px;
    border: 1px solid #fde68a;
}

.team-count-badge {
    display: inline-block;
    font-size: 11.5px;
    font-weight: 700;
    color: #0f172a;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
}

/* Status Pills */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 800;
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
    color: #334155;
    border: 1px solid #cbd5e1;
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
    padding: 4px 9px;
    height: 27px;
    font-size: 11.5px;
    font-weight: 700;
    line-height: 1;
    border-radius: 6px;
    border: 1.5px solid transparent;
    transition: all 0.15s ease;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
}

.btn-action-approve {
    background: #16a34a;
    color: #ffffff;
    border-color: #15803d;
}
.btn-action-approve:hover {
    background: #15803d;
    color: #ffffff;
}

.btn-action-reject {
    background: #ffffff;
    color: #dc2626;
    border-color: #f87171;
}
.btn-action-reject:hover {
    background: #fef2f2;
    color: #b91c1c;
    border-color: #ef4444;
}

.btn-action-view {
    background: #ffffff;
    color: #1e293b;
    border-color: #94a3b8;
}
.btn-action-view:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #64748b;
}

.btn-action-delete {
    background: #ffffff;
    color: #dc2626;
    border-color: #cbd5e1;
    padding: 4px 7px;
}
.btn-action-delete:hover {
    background: #fee2e2;
    color: #b91c1c;
    border-color: #f87171;
}
</style>

<script>
function openApproveBar(id) {
    const actionRow = document.getElementById('actionRow' + id);
    const approveBox = document.getElementById('approveBox' + id);
    const rejectBox = document.getElementById('rejectBox' + id);
    if (!actionRow || !approveBox) return;

    if (rejectBox) rejectBox.style.display = 'none';

    if (actionRow.style.display === 'none' || actionRow.style.display === '') {
        actionRow.style.display = 'table-row';
        approveBox.style.display = 'block';
    } else if (approveBox.style.display === 'block') {
        actionRow.style.display = 'none';
        approveBox.style.display = 'none';
    } else {
        approveBox.style.display = 'block';
    }
}

function openRejectBar(id) {
    const actionRow = document.getElementById('actionRow' + id);
    const approveBox = document.getElementById('approveBox' + id);
    const rejectBox = document.getElementById('rejectBox' + id);
    if (!actionRow || !rejectBox) return;

    if (approveBox) approveBox.style.display = 'none';

    if (actionRow.style.display === 'none' || actionRow.style.display === '') {
        actionRow.style.display = 'table-row';
        rejectBox.style.display = 'block';
    } else if (rejectBox.style.display === 'block') {
        actionRow.style.display = 'none';
        rejectBox.style.display = 'none';
    } else {
        rejectBox.style.display = 'block';
    }
}

function closeActionRow(id) {
    const actionRow = document.getElementById('actionRow' + id);
    const approveBox = document.getElementById('approveBox' + id);
    const rejectBox = document.getElementById('rejectBox' + id);
    if (actionRow) actionRow.style.display = 'none';
    if (approveBox) approveBox.style.display = 'none';
    if (rejectBox) rejectBox.style.display = 'none';
}
</script>
@endsection

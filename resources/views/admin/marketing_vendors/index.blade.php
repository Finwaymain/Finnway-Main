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
                </ul>

                <!-- Vendors Table -->
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light" style="background: #f8fafc;">
                            <tr class="text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <th>Vendor / Applicant</th>
                                <th>Vendor Code</th>
                                <th>Location & Type</th>
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
                                    <div class="btn-group" role="group">
                                        @if($v->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-success font-weight-semibold" data-toggle="modal" data-target="#approveModal{{ $v->id }}">
                                            Approve & Set Rates
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger font-weight-semibold" data-toggle="modal" data-target="#rejectModal{{ $v->id }}">
                                            Reject
                                        </button>
                                        @endif
                                        <a href="{{ route('admin.marketing-vendors.show', $v->id) }}" class="btn btn-sm btn-primary font-weight-semibold">
                                            View Profile & Team
                                        </a>
                                    </div>

                                    <!-- Approve Modal -->
                                    <div class="modal fade" id="approveModal{{ $v->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-left" role="document">
                                            <div class="modal-content" style="border-radius: 14px;">
                                                <form action="{{ route('admin.marketing-vendors.approve', $v->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title font-weight-bold">Approve Vendor & Configure Payout Rates</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="text-muted" style="font-size: 13px;">
                                                            Applicant: <strong>{{ $v->applicant_name }}</strong> ({{ $v->team_location }})<br>
                                                            Team Type: <strong>{{ $v->team_type }}</strong>
                                                        </p>
                                                        @if(!empty($v->remarks))
                                                        <div class="alert alert-light p-2 mb-3 border text-muted" style="font-size: 12px;">
                                                            <strong>Applicant Remarks:</strong> {{ $v->remarks }}
                                                        </div>
                                                        @endif
                                                        <div class="form-group mb-3">
                                                            <label class="font-weight-semibold">Payout Rate per Verified Customer (₹) <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                                                <input type="number" step="0.01" min="0" name="rate_per_customer" class="form-control" value="15.00" required>
                                                            </div>
                                                            <small class="text-muted">How much vendor earns when a verified customer joins.</small>
                                                        </div>
                                                        <div class="form-group mb-3">
                                                            <label class="font-weight-semibold">Payout Rate per Verified Business User / Driver (₹) <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                                                <input type="number" step="0.01" min="0" name="rate_per_business" class="form-control" value="50.00" required>
                                                            </div>
                                                            <small class="text-muted">How much vendor earns when a verified business driver joins.</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success btn-sm font-weight-bold">Approve Vendor</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $v->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-left" role="document">
                                            <div class="modal-content" style="border-radius: 14px;">
                                                <form action="{{ route('admin.marketing-vendors.reject', $v->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title font-weight-bold">Reject Vendor Application</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold">Rejection Reason</label>
                                                            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Enter reason for rejection..." required>Does not meet territory requirements.</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger btn-sm font-weight-bold">Reject Application</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="mb-2" style="font-size: 32px;">👥</div>
                                    <h6 class="font-weight-bold text-dark">No vendors found in this filter</h6>
                                    <p class="mb-0" style="font-size: 13px;">Applications submitted by partners from the mobile app will show up here.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($vendors->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $vendors->appends(['status' => $status])->links() }}
                </div>
                @endif

            </div>
        </div>

    </div>
</div>
@endsection

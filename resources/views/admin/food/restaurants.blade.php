@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="font-weight-bold mb-1">Restaurant Partners Management</h3>
            <p class="text-muted mb-0">Onboarding review, compliance verification, outlet catalog, and live operational status.</p>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('admin.food.live') }}" class="btn btn-outline-secondary mr-2">
                Live Radar
            </a>
            <a href="{{ route('admin.food.types') }}" class="btn btn-primary">
                Partner Types & Fees
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-4 mb-2">
            <div class="metric-card">
                <div class="metric-title">Total Outlets</div>
                <div class="metric-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 mb-2">
            <div class="metric-card">
                <div class="metric-title">Active & Live</div>
                <div class="metric-value">{{ number_format($stats['active']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-4 mb-2">
            <div class="metric-card">
                <div class="metric-title">Pending Review</div>
                <div class="metric-value">
                    {{ number_format($stats['pending']) }}
                    @if($stats['pending'] > 0)
                        <span class="badge badge-warning ml-1" style="font-size: 11px;">Action Required</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-2">
            <div class="metric-card">
                <div class="metric-title">Doc Resubmit</div>
                <div class="metric-value">{{ number_format($stats['resubmit']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="metric-card">
                <div class="metric-title">Currently Open</div>
                <div class="metric-value">{{ number_format($stats['open_now']) }}</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="get" action="{{ route('admin.food.restaurants') }}" class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by restaurant name, owner phone, or city...">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select name="status" class="form-control">
                        <option value="">All Onboarding Statuses</option>
                        <option value="pending_approval" @selected(request('status')=='pending_approval')>Pending Approval</option>
                        <option value="doc_resubmission_required" @selected(request('status')=='doc_resubmission_required')>Doc Resubmission Required</option>
                        <option value="active" @selected(request('status')=='active')>Active & Live</option>
                        <option value="payment_pending" @selected(request('status')=='payment_pending')>Onboarding Fee Pending</option>
                        <option value="suspended" @selected(request('status')=='suspended')>Suspended</option>
                        <option value="rejected" @selected(request('status')=='rejected')>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select name="ops_status" class="form-control">
                        <option value="">All Operational Statuses</option>
                        <option value="open" @selected(request('ops_status')=='open')>Open for Orders</option>
                        <option value="busy" @selected(request('ops_status')=='busy')>Busy (Prep Delay)</option>
                        <option value="temporarily_closed" @selected(request('ops_status')=='temporarily_closed')>Paused</option>
                        <option value="closed" @selected(request('ops_status')=='closed')>Closed</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-primary btn-block mr-2">Filter</button>
                    @if(request()->hasAny(['q', 'status', 'ops_status']))
                        <a href="{{ route('admin.food.restaurants') }}" class="btn btn-outline-secondary" title="Reset Filters">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Restaurants Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Restaurant</th>
                        <th>Owner / Contact</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Operational</th>
                        <th>Commission</th>
                        <th class="text-right" style="min-width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($restaurants as $r)
                        <tr>
                            <td class="font-weight-bold text-muted">#{{ $r->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        @if($r->logo)
                                            <img src="{{ asset('storage/' . $r->logo) }}" alt="{{ $r->name }}" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center font-weight-bold" style="width: 40px; height: 40px; font-size: 14px; background-color: #f3f4f6; color: #111827; border: 1px solid #e5e7eb;">
                                                {{ strtoupper(substr($r->name, 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.food.restaurants.show', $r->id) }}" class="font-weight-bold text-dark d-block">
                                            {{ $r->name }}
                                        </a>
                                        <div class="small text-muted">
                                            <span class="badge badge-light">{{ ucwords(str_replace('_', ' ', $r->business_type ?: 'restaurant')) }}</span>
                                            @if($r->pure_veg)
                                                <span class="badge badge-success ml-1">Pure Veg</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $r->owner_name ?: ($r->owner ? $r->owner->name : 'N/A') }}</div>
                                <div class="small text-muted">{{ $r->owner_phone ?: ($r->owner ? $r->owner->phone : 'N/A') }}</div>
                            </td>
                            <td>
                                <div>{{ $r->city ?: 'N/A' }}</div>
                                <div class="small text-muted">{{ $r->area ?: $r->address }}</div>
                            </td>
                            <td>
                                @if($r->onboarding_status === 'active')
                                    <span class="badge badge-success">Active</span>
                                @elseif($r->onboarding_status === 'pending_approval')
                                    <span class="badge badge-warning">Pending Approval</span>
                                @elseif($r->onboarding_status === 'doc_resubmission_required')
                                    <span class="badge badge-info">Doc Resubmit</span>
                                @elseif($r->onboarding_status === 'payment_pending')
                                    <span class="badge badge-light">Fee Pending</span>
                                @elseif($r->onboarding_status === 'suspended')
                                    <span class="badge badge-danger">Suspended</span>
                                @else
                                    <span class="badge badge-light">{{ ucfirst($r->onboarding_status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($r->operational_status === 'open')
                                    <span class="badge badge-success">Open</span>
                                @elseif($r->operational_status === 'busy')
                                    <span class="badge badge-warning">Busy</span>
                                @elseif($r->operational_status === 'temporarily_closed')
                                    <span class="badge badge-light">Paused</span>
                                @else
                                    <span class="badge badge-light text-muted">Closed</span>
                                @endif
                            </td>
                            <td>
                                @if($r->custom_commission_rate !== null)
                                    <span class="badge badge-light font-weight-bold">{{ $r->custom_commission_rate }}% (Custom)</span>
                                @else
                                    <span class="text-muted small">Default</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <a href="{{ route('admin.food.restaurants.show', $r->id) }}" class="btn btn-sm btn-outline-secondary">
                                        Manage
                                    </a>
                                    @if($r->onboarding_status === 'pending_approval')
                                        <form method="post" action="{{ route('admin.food.restaurants.approve', $r->id) }}" class="d-inline ml-1" onsubmit="return confirm('Approve and activate this restaurant partner?');">
                                            @csrf
                                            <button class="btn btn-sm btn-primary">
                                                Approve
                                            </button>
                                        </form>
                                    @endif
                                    <form method="post" action="{{ route('admin.food.restaurants.destroy', $r->id) }}" class="d-inline ml-1" onsubmit="return confirm('Are you sure you want to delete restaurant \'{{ addslashes($r->name) }}\' (ID: #{{ $r->id }})? This action cannot be undone.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No restaurants found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($restaurants->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="text-muted small">
                    Showing {{ $restaurants->firstItem() }} to {{ $restaurants->lastItem() }} of {{ $restaurants->total() }} restaurants
                </div>
                <div>
                    {{ $restaurants->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

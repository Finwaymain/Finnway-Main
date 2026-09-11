@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">🍽️ Restaurant Partners Management</h3>
            <p class="text-muted mb-0">Monitor onboarding applications, compliance verification, menus, and live operational statuses.</p>
        </div>
        <div>
            <a href="{{ route('admin.food.live') }}" class="btn btn-outline-primary mr-2">
                <i class="fa fa-map-marker mr-1"></i> Live Kitchens Radar
            </a>
            <a href="{{ route('admin.food.types') }}" class="btn btn-primary">
                <i class="fa fa-cog mr-1"></i> Partner Types & Fees
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-4 mb-2">
            <div class="card shadow-sm border-0 border-left border-primary" style="border-left-width: 4px !important;">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase font-weight-bold">Total Partners</div>
                    <div class="h4 font-weight-bold text-primary mb-0">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 mb-2">
            <div class="card shadow-sm border-0 border-left border-success" style="border-left-width: 4px !important;">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase font-weight-bold">Active & Verified</div>
                    <div class="h4 font-weight-bold text-success mb-0">{{ number_format($stats['active']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-4 mb-2">
            <div class="card shadow-sm border-0 border-left border-warning" style="border-left-width: 4px !important;">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase font-weight-bold">Pending Review</div>
                    <div class="h4 font-weight-bold text-warning mb-0">
                        {{ number_format($stats['pending']) }}
                        @if($stats['pending'] > 0)
                            <span class="badge badge-warning small ml-1">Needs Action</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-2">
            <div class="card shadow-sm border-0 border-left border-info" style="border-left-width: 4px !important;">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase font-weight-bold">Doc Resubmission</div>
                    <div class="h4 font-weight-bold text-info mb-0">{{ number_format($stats['resubmit']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card shadow-sm border-0 border-left border-success" style="border-left-width: 4px !important;">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase font-weight-bold">Currently Open (Live)</div>
                    <div class="h4 font-weight-bold text-success mb-0">
                        <span class="text-success mr-1">●</span> {{ number_format($stats['open_now']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="get" action="{{ route('admin.food.restaurants') }}" class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input name="q" value="{{ request('q') }}" class="form-control border-left-0" placeholder="Search by restaurant name, owner phone, or city...">
                    </div>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select name="status" class="form-control custom-select">
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
                    <select name="ops_status" class="form-control custom-select">
                        <option value="">All Operational Statuses</option>
                        <option value="open" @selected(request('ops_status')=='open')>🟢 Open for Orders</option>
                        <option value="busy" @selected(request('ops_status')=='busy')>🟡 Busy (+ Prep Delay)</option>
                        <option value="temporarily_closed" @selected(request('ops_status')=='temporarily_closed')>🟠 Paused</option>
                        <option value="closed" @selected(request('ops_status')=='closed')>🔴 Closed</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-primary btn-block mr-2">Filter</button>
                    @if(request()->hasAny(['q', 'status', 'ops_status']))
                        <a href="{{ route('admin.food.restaurants') }}" class="btn btn-light" title="Reset Filters"><i class="fa fa-times"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Restaurants Table -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase small text-muted font-weight-bold">
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
                                            <img src="{{ asset('storage/' . $r->logo) }}" alt="{{ $r->name }}" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary font-weight-bold border" style="width: 44px; height: 44px; font-size: 16px;">
                                                {{ strtoupper(substr($r->name, 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.food.restaurants.show', $r->id) }}" class="font-weight-bold text-dark d-block">
                                            {{ $r->name }}
                                        </a>
                                        <small class="text-muted">
                                            <span class="badge badge-light border">{{ ucwords(str_replace('_', ' ', $r->business_type ?: 'restaurant')) }}</span>
                                            @if($r->pure_veg)
                                                <span class="badge badge-success">Pure Veg</span>
                                            @endif
                                            @if(!empty($r->cuisines) && is_array($r->cuisines))
                                                <span class="text-secondary ml-1">• {{ implode(', ', array_slice($r->cuisines, 0, 3)) }}{{ count($r->cuisines) > 3 ? ' +' . (count($r->cuisines) - 3) : '' }}</span>
                                            @elseif($r->sub_category)
                                                <span class="text-secondary ml-1">• {{ $r->sub_category }}</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $r->owner_name ?: ($r->owner ? $r->owner->name : 'N/A') }}</div>
                                <small class="text-muted d-block">
                                    <i class="fa fa-phone mr-1"></i>{{ $r->owner_phone ?: ($r->owner ? $r->owner->phone : 'N/A') }}
                                </small>
                            </td>
                            <td>
                                <div>{{ $r->city ?: 'N/A' }}</div>
                                <small class="text-muted">{{ $r->area ?: $r->address }}</small>
                            </td>
                            <td>
                                @if($r->onboarding_status === 'active')
                                    <span class="badge badge-success px-2 py-1">Active</span>
                                @elseif($r->onboarding_status === 'pending_approval')
                                    <span class="badge badge-warning px-2 py-1">Pending Approval</span>
                                @elseif($r->onboarding_status === 'doc_resubmission_required')
                                    <span class="badge badge-info px-2 py-1">Doc Resubmit</span>
                                @elseif($r->onboarding_status === 'payment_pending')
                                    <span class="badge badge-secondary px-2 py-1">Fee Pending</span>
                                @elseif($r->onboarding_status === 'suspended')
                                    <span class="badge badge-danger px-2 py-1">Suspended</span>
                                @else
                                    <span class="badge badge-light border px-2 py-1">{{ ucfirst($r->onboarding_status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($r->operational_status === 'open')
                                    <span class="text-success font-weight-bold"><span class="mr-1">●</span> Open</span>
                                @elseif($r->operational_status === 'busy')
                                    <span class="text-warning font-weight-bold"><span class="mr-1">●</span> Busy</span>
                                @elseif($r->operational_status === 'temporarily_closed')
                                    <span class="text-info font-weight-bold"><span class="mr-1">●</span> Paused</span>
                                @else
                                    <span class="text-muted"><span class="mr-1">●</span> Closed</span>
                                @endif
                            </td>
                            <td>
                                @if($r->custom_commission_rate !== null)
                                    <span class="badge badge-primary">{{ $r->custom_commission_rate }}% (Custom)</span>
                                @else
                                    <span class="text-muted small">Platform Default</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <a href="{{ route('admin.food.restaurants.show', $r->id) }}" class="btn btn-sm btn-info" title="Manage Partner Superpowers">
                                        <i class="fa fa-sliders-h mr-1"></i> Manage
                                    </a>
                                    @if($r->onboarding_status === 'pending_approval')
                                        <form method="post" action="{{ route('admin.food.restaurants.approve', $r->id) }}" class="d-inline ml-1" onsubmit="return confirm('Approve and activate this restaurant partner?');">
                                            @csrf
                                            <button class="btn btn-sm btn-success" title="Quick Approve">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa fa-store fa-3x mb-3 text-light"></i>
                                <p class="mb-0">No restaurants found matching your criteria.</p>
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

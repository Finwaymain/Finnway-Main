@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <!-- Header with Live Controls -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <div class="d-flex align-items-center">
                <span class="badge badge-danger p-2 mr-2 d-inline-flex align-items-center">
                    <span class="spinner-grow spinner-grow-sm mr-1" role="status" aria-hidden="true" style="width: 0.75rem; height: 0.75rem;"></span>
                    LIVE
                </span>
                <h3 class="font-weight-bold text-dark mb-0">Operations Radar & KDS Tracker</h3>
            </div>
            <p class="text-muted mt-1 mb-0">Real-time pipeline monitoring from placement, kitchen preparation, to rider delivery.</p>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-md-0">
            <span class="badge badge-light border px-3 py-2 mr-2 text-muted" id="refreshTimer">
                <i class="fa fa-sync-alt fa-spin mr-1 text-primary"></i> Auto-refresh: <strong id="countdown">15</strong>s
            </span>
            <button class="btn btn-outline-secondary btn-sm mr-2" id="toggleRefreshBtn" onclick="toggleAutoRefresh()">
                <i class="fa fa-pause mr-1" id="refreshIcon"></i> <span id="refreshBtnText">Pause</span>
            </button>
            <a href="{{ route('admin.food.live') }}" class="btn btn-primary btn-sm mr-2">
                <i class="fa fa-redo mr-1"></i> Refresh Now
            </a>
            <a href="{{ route('admin.food.orders') }}" class="btn btn-outline-dark btn-sm">
                <i class="fa fa-list mr-1"></i> Orders Hub
            </a>
        </div>
    </div>

    <!-- Live Stage Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm border-left-warning" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold">New / Placed</span>
                        <h3 class="font-weight-bold text-dark mb-0 mt-1">{{ $stageNew->count() }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-light text-warning">
                        <i class="fa fa-bell fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm border-left-info" style="border-left: 4px solid #17a2b8 !important;">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold">Kitchen Preparing</span>
                        <h3 class="font-weight-bold text-dark mb-0 mt-1">{{ $stagePrep->count() }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-light text-info">
                        <i class="fa fa-fire fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm border-left-primary" style="border-left: 4px solid #007bff !important;">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold">Ready / Rider En Route</span>
                        <h3 class="font-weight-bold text-dark mb-0 mt-1">{{ $stageReady->count() }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-light text-primary">
                        <i class="fa fa-motorcycle fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm border-left-success" style="border-left: 4px solid #28a745 !important;">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold">Out for Delivery</span>
                        <h3 class="font-weight-bold text-dark mb-0 mt-1">{{ $stageOnTheWay->count() }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-light text-success">
                        <i class="fa fa-route fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Pipeline Kanban Board -->
    <div class="row">
        <!-- Stage 1: New / Placed -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark">
                        <i class="fa fa-clock text-warning mr-1"></i> 1. New Orders
                    </span>
                    <span class="badge badge-warning badge-pill px-2">{{ $stageNew->count() }}</span>
                </div>
                <div class="card-body p-2" style="max-height: 700px; overflow-y: auto;">
                    @forelse($stageNew as $order)
                        <div class="card shadow-sm border-0 mb-2">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-secondary font-weight-bold">#{{ $order->order_number }}</span>
                                    <span class="badge badge-light border text-danger small">
                                        {{ $order->created_at ? $order->created_at->diffForHumans(null, true) : 'just now' }}
                                    </span>
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">{{ optional($order->restaurant)->name ?? 'Restaurant' }}</h6>
                                <p class="small text-muted mb-2">
                                    <i class="fa fa-user mr-1"></i> {{ $order->customer_name ?: 'Customer' }} ({{ $order->customer_phone ?: 'N/A' }})
                                </p>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                    <span class="text-muted">{{ $order->items->count() }} items</span>
                                    <span class="font-weight-bold text-dark">₹{{ number_format($order->customer_payable, 2) }}</span>
                                </div>
                                @if($order->is_test)
                                    <div class="mt-2"><span class="badge badge-warning small">TEST ORDER</span></div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fa fa-check-circle fa-2x mb-2 text-muted"></i>
                            <div>No new orders pending</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Stage 2: Kitchen Preparing -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark">
                        <i class="fa fa-utensils text-info mr-1"></i> 2. In Kitchen
                    </span>
                    <span class="badge badge-info badge-pill px-2">{{ $stagePrep->count() }}</span>
                </div>
                <div class="card-body p-2" style="max-height: 700px; overflow-y: auto;">
                    @forelse($stagePrep as $order)
                        <div class="card shadow-sm border-0 mb-2">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-secondary font-weight-bold">#{{ $order->order_number }}</span>
                                    <span class="badge badge-info small text-uppercase">{{ str_replace('_', ' ', $order->order_status) }}</span>
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">{{ optional($order->restaurant)->name ?? 'Restaurant' }}</h6>
                                <p class="small text-muted mb-2">
                                    <i class="fa fa-stopwatch mr-1"></i> Prep SLA: {{ $order->prep_minutes ?: 20 }} mins
                                </p>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                    <span class="text-muted">{{ $order->items->count() }} items</span>
                                    <span class="font-weight-bold text-dark">₹{{ number_format($order->customer_payable, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fa fa-check-circle fa-2x mb-2 text-muted"></i>
                            <div>No orders currently cooking</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Stage 3: Ready / Rider Assigned -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark">
                        <i class="fa fa-box text-primary mr-1"></i> 3. Ready / Pickup
                    </span>
                    <span class="badge badge-primary badge-pill px-2">{{ $stageReady->count() }}</span>
                </div>
                <div class="card-body p-2" style="max-height: 700px; overflow-y: auto;">
                    @forelse($stageReady as $order)
                        <div class="card shadow-sm border-0 mb-2">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-secondary font-weight-bold">#{{ $order->order_number }}</span>
                                    @if($order->pickup_otp)
                                        <span class="badge badge-light border text-dark font-weight-bold">
                                            Pickup OTP: <span class="text-danger">{{ $order->pickup_otp }}</span>
                                        </span>
                                    @endif
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">{{ optional($order->restaurant)->name ?? 'Restaurant' }}</h6>
                                <div class="small p-2 bg-light rounded mb-2">
                                    <i class="fa fa-motorcycle text-primary mr-1"></i>
                                    <strong>Rider:</strong> {{ $order->rider_name ?: 'Dispatching / En Route' }}
                                    @if($order->rider_phone)
                                        <span class="text-muted d-block mt-1"><i class="fa fa-phone mr-1"></i> {{ $order->rider_phone }}</span>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                    <span class="badge badge-info">{{ str_replace('_', ' ', $order->order_status) }}</span>
                                    <span class="font-weight-bold text-dark">₹{{ number_format($order->customer_payable, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fa fa-check-circle fa-2x mb-2 text-muted"></i>
                            <div>No orders waiting for pickup</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Stage 4: Out for Delivery -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark">
                        <i class="fa fa-shipping-fast text-success mr-1"></i> 4. Out For Delivery
                    </span>
                    <span class="badge badge-success badge-pill px-2">{{ $stageOnTheWay->count() }}</span>
                </div>
                <div class="card-body p-2" style="max-height: 700px; overflow-y: auto;">
                    @forelse($stageOnTheWay as $order)
                        <div class="card shadow-sm border-0 mb-2">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-secondary font-weight-bold">#{{ $order->order_number }}</span>
                                    @if($order->delivery_otp)
                                        <span class="badge badge-success small font-weight-bold">
                                            Drop OTP: {{ $order->delivery_otp }}
                                        </span>
                                    @endif
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">To: {{ $order->customer_name ?: 'Customer' }}</h6>
                                <p class="small text-muted mb-2 text-truncate" title="{{ $order->delivery_address }}">
                                    <i class="fa fa-map-marker-alt text-danger mr-1"></i> {{ $order->delivery_address ?: 'Customer Address' }}
                                </p>
                                <div class="small p-2 bg-light rounded mb-2">
                                    <i class="fa fa-motorcycle text-success mr-1"></i>
                                    <strong>Rider:</strong> {{ $order->rider_name ?: 'Active Courier' }}
                                    @if($order->rider_phone)
                                        <span class="text-muted d-block mt-1"><i class="fa fa-phone mr-1"></i> {{ $order->rider_phone }}</span>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                    <span class="text-muted">{{ $order->distance_km ? $order->distance_km . ' km' : 'En route' }}</span>
                                    <span class="font-weight-bold text-dark">₹{{ number_format($order->customer_payable, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fa fa-check-circle fa-2x mb-2 text-muted"></i>
                            <div>No orders currently on route</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Active Open Kitchens & Recent Activity -->
    <div class="row mt-2">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-store text-success mr-2"></i> Active Kitchen Outlets ({{ $restaurants->count() }})
                    </h5>
                    <a href="{{ route('admin.food.restaurants') }}" class="btn btn-outline-secondary btn-sm">Manage All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Outlet Name</th>
                                    <th>Type</th>
                                    <th>City</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($restaurants as $r)
                                    <tr>
                                        <td class="font-weight-bold">{{ $r->name }}</td>
                                        <td>
                                            @php $bType = $r->business_type ?: 'restaurant'; @endphp
                                            <span class="badge {{ $bType === 'cloud_kitchen' ? 'badge-primary' : 'badge-secondary' }}">
                                                {{ ucwords(str_replace('_', ' ', $bType)) }}
                                            </span>
                                        </td>
                                        <td>{{ $r->city ?: 'N/A' }}</td>
                                        <td><span class="badge badge-success">Open</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No open kitchen outlets right now.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-check-double text-primary mr-2"></i> Recently Delivered Orders
                    </h5>
                    <a href="{{ route('admin.food.orders') }}" class="btn btn-outline-secondary btn-sm">Full History</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Restaurant</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDelivered as $ro)
                                    <tr>
                                        <td class="font-weight-bold">#{{ $ro->order_number }}</td>
                                        <td>{{ optional($ro->restaurant)->name ?? 'Restaurant' }}</td>
                                        <td class="font-weight-bold">₹{{ number_format($ro->customer_payable, 2) }}</td>
                                        <td><span class="badge badge-success text-capitalize">{{ $ro->order_status }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No delivered orders yet today.</td>
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

<script>
    let timeLeft = 15;
    let autoRefreshActive = true;
    let timerInterval = null;

    function startTimer() {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            if (!autoRefreshActive) return;
            timeLeft--;
            const countEl = document.getElementById('countdown');
            if (countEl) countEl.innerText = timeLeft;
            if (timeLeft <= 0) {
                window.location.reload();
            }
        }, 1000);
    }

    function toggleAutoRefresh() {
        autoRefreshActive = !autoRefreshActive;
        const icon = document.getElementById('refreshIcon');
        const text = document.getElementById('refreshBtnText');
        const badge = document.getElementById('refreshTimer');
        
        if (autoRefreshActive) {
            timeLeft = 15;
            icon.className = 'fa fa-pause mr-1';
            text.innerText = 'Pause';
            badge.classList.remove('text-muted');
            badge.classList.add('text-success');
        } else {
            icon.className = 'fa fa-play mr-1';
            text.innerText = 'Resume';
            badge.classList.add('text-muted');
            badge.classList.remove('text-success');
        }
    }

    document.addEventListener('DOMContentLoaded', startTimer);
</script>
@endsection

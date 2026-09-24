@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="font-weight-bold mb-1">Live Operations Radar</h3>
            <p class="text-muted mb-0">Pipeline tracking for active in-flight customer orders and dispatch status.</p>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-md-0">
            <span class="badge badge-light px-3 py-2 mr-2" id="refreshTimer">
                Auto-refresh in <strong id="countdown">15</strong>s
            </span>
            <button class="btn btn-outline-secondary btn-sm mr-2" id="toggleRefreshBtn" onclick="toggleAutoRefresh()">
                <span id="refreshBtnText">Pause</span>
            </button>
            <a href="{{ route('admin.food.live') }}" class="btn btn-primary btn-sm mr-2">
                Refresh Now
            </a>
            <a href="{{ route('admin.food.orders') }}" class="btn btn-outline-secondary btn-sm">
                Orders Hub
            </a>
        </div>
    </div>

    <!-- Live Stage Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-title">1. New Orders</div>
                <div class="metric-value">{{ $stageNew->count() }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-title">2. In Kitchen</div>
                <div class="metric-value">{{ $stagePrep->count() }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-title">3. Ready / Pickup</div>
                <div class="metric-value">{{ $stageReady->count() }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-title">4. Out for Delivery</div>
                <div class="metric-value">{{ $stageOnTheWay->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Pipeline Kanban Columns -->
    <div class="row">
        <!-- Stage 1: New / Placed -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">New Placed</span>
                    <span class="badge badge-light">{{ $stageNew->count() }}</span>
                </div>
                <div class="card-body p-2" style="max-height: 700px; overflow-y: auto; background-color: #f9fafb;">
                    @forelse($stageNew as $order)
                        <div class="card mb-2" style="border: 1px solid #e5e7eb;">
                            <div class="card-body p-3 bg-white">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-light font-weight-bold">#{{ $order->order_number }}</span>
                                    <span class="small text-muted font-weight-bold">
                                        {{ $order->created_at ? $order->created_at->diffForHumans(null, true) : 'just now' }}
                                    </span>
                                </div>
                                <div class="font-weight-bold text-dark mb-1">{{ optional($order->restaurant)->name ?? 'Restaurant' }}</div>
                                <div class="small text-muted mb-2">
                                    {{ $order->customer_name ?: 'Customer' }} ({{ $order->customer_phone ?: 'N/A' }})
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                    <span class="text-muted">{{ $order->items->count() }} items</span>
                                    <span class="font-weight-bold text-dark">₹{{ number_format($order->customer_payable, 2) }}</span>
                                </div>
                                @if($order->is_test)
                                    <div class="mt-2"><span class="badge badge-warning">Test Order</span></div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            No new orders
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Stage 2: Kitchen Preparing -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">Kitchen Prep</span>
                    <span class="badge badge-light">{{ $stagePrep->count() }}</span>
                </div>
                <div class="card-body p-2" style="max-height: 700px; overflow-y: auto; background-color: #f9fafb;">
                    @forelse($stagePrep as $order)
                        <div class="card mb-2" style="border: 1px solid #e5e7eb;">
                            <div class="card-body p-3 bg-white">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-light font-weight-bold">#{{ $order->order_number }}</span>
                                    <span class="badge badge-info text-capitalize">{{ str_replace('_', ' ', $order->order_status) }}</span>
                                </div>
                                <div class="font-weight-bold text-dark mb-1">{{ optional($order->restaurant)->name ?? 'Restaurant' }}</div>
                                <div class="small text-muted mb-2">
                                    Prep Target: {{ $order->prep_minutes ?: 20 }} mins
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                    <span class="text-muted">{{ $order->items->count() }} items</span>
                                    <span class="font-weight-bold text-dark">₹{{ number_format($order->customer_payable, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            No orders preparing
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Stage 3: Ready / Rider Assigned -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">Ready / Pickup</span>
                    <span class="badge badge-light">{{ $stageReady->count() }}</span>
                </div>
                <div class="card-body p-2" style="max-height: 700px; overflow-y: auto; background-color: #f9fafb;">
                    @forelse($stageReady as $order)
                        <div class="card mb-2" style="border: 1px solid #e5e7eb;">
                            <div class="card-body p-3 bg-white">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-light font-weight-bold">#{{ $order->order_number }}</span>
                                    @if($order->pickup_otp)
                                        <span class="badge badge-light font-weight-bold">OTP: {{ $order->pickup_otp }}</span>
                                    @endif
                                </div>
                                <div class="font-weight-bold text-dark mb-1">{{ optional($order->restaurant)->name ?? 'Restaurant' }}</div>
                                <div class="small p-2 rounded mb-2" style="background-color: #f9fafb; border: 1px solid #e5e7eb;">
                                    <strong>Courier:</strong> {{ $order->rider_name ?: 'Assigning Courier' }}
                                    @if($order->rider_phone)
                                        <div class="text-muted">{{ $order->rider_phone }}</div>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                    <span class="badge badge-light text-capitalize">{{ str_replace('_', ' ', $order->order_status) }}</span>
                                    <span class="font-weight-bold text-dark">₹{{ number_format($order->customer_payable, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            No orders waiting pickup
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Stage 4: Out for Delivery -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">Out for Delivery</span>
                    <span class="badge badge-light">{{ $stageOnTheWay->count() }}</span>
                </div>
                <div class="card-body p-2" style="max-height: 700px; overflow-y: auto; background-color: #f9fafb;">
                    @forelse($stageOnTheWay as $order)
                        <div class="card mb-2" style="border: 1px solid #e5e7eb;">
                            <div class="card-body p-3 bg-white">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-light font-weight-bold">#{{ $order->order_number }}</span>
                                    @if($order->delivery_otp)
                                        <span class="badge badge-success font-weight-bold">Drop OTP: {{ $order->delivery_otp }}</span>
                                    @endif
                                </div>
                                <div class="font-weight-bold text-dark mb-1">To: {{ $order->customer_name ?: 'Customer' }}</div>
                                <div class="small text-muted mb-2 text-truncate" title="{{ $order->delivery_address }}">
                                    {{ $order->delivery_address ?: 'Customer Address' }}
                                </div>
                                <div class="small p-2 rounded mb-2" style="background-color: #f9fafb; border: 1px solid #e5e7eb;">
                                    <strong>Courier:</strong> {{ $order->rider_name ?: 'Active Courier' }}
                                    @if($order->rider_phone)
                                        <div class="text-muted">{{ $order->rider_phone }}</div>
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
                            No orders in transit
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Active Open Kitchens & Recent Deliveries -->
    <div class="row mt-2">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">Active Outlets ({{ $restaurants->count() }})</h5>
                    <a href="{{ route('admin.food.restaurants') }}" class="btn btn-outline-secondary btn-sm">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table mb-0">
                            <thead>
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
                                            <span class="badge badge-light">
                                                {{ ucwords(str_replace('_', ' ', $bType)) }}
                                            </span>
                                        </td>
                                        <td>{{ $r->city ?: 'N/A' }}</td>
                                        <td><span class="badge badge-success">Open</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No open outlets currently online.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">Recent Completed Orders</h5>
                    <a href="{{ route('admin.food.orders') }}" class="btn btn-outline-secondary btn-sm">Full History</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table mb-0">
                            <thead>
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
                                        <td colspan="4" class="text-center py-4 text-muted">No delivered orders recorded yet.</td>
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
        const text = document.getElementById('refreshBtnText');
        if (autoRefreshActive) {
            timeLeft = 15;
            text.innerText = 'Pause';
        } else {
            text.innerText = 'Resume';
        }
    }

    document.addEventListener('DOMContentLoaded', startTimer);
</script>
@endsection

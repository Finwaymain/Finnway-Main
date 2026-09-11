@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">📦 Food Orders Hub</h3>
            <p class="text-muted mb-0">Track customer meal orders, live preparation milestones, rider dispatches, and delivery handoffs.</p>
        </div>
        <div>
            <a href="{{ route('admin.food.live') }}" class="btn btn-outline-danger mr-2">
                <i class="fa fa-radar mr-1"></i> Live Kitchens Radar
            </a>
            <a href="{{ route('admin.food.restaurants') }}" class="btn btn-outline-primary">
                <i class="fa fa-store mr-1"></i> Manage Restaurants
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="get" action="{{ route('admin.food.orders') }}" class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <div class="btn-group btn-group-sm flex-wrap" role="group">
                        <a href="{{ route('admin.food.orders') }}" class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
                            All Orders
                        </a>
                        <a href="{{ route('admin.food.orders', ['status' => 'placed']) }}" class="btn {{ request('status') === 'placed' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Placed
                        </a>
                        <a href="{{ route('admin.food.orders', ['status' => 'preparing']) }}" class="btn {{ request('status') === 'preparing' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Preparing
                        </a>
                        <a href="{{ route('admin.food.orders', ['status' => 'ready_for_pickup']) }}" class="btn {{ request('status') === 'ready_for_pickup' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Ready for Pickup
                        </a>
                        <a href="{{ route('admin.food.orders', ['status' => 'out_for_delivery']) }}" class="btn {{ request('status') === 'out_for_delivery' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Out for Delivery
                        </a>
                        <a href="{{ route('admin.food.orders', ['status' => 'delivered']) }}" class="btn {{ request('status') === 'delivered' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Delivered
                        </a>
                        <a href="{{ route('admin.food.orders', ['status' => 'cancelled']) }}" class="btn {{ request('status') === 'cancelled' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Cancelled
                        </a>
                    </div>
                </div>
                <div class="col-md-6 text-md-right">
                    <span class="text-muted small">
                        Showing {{ $orders->count() }} of {{ $orders->total() }} orders recorded
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Order ID / Number</th>
                        <th>Restaurant</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Payable</th>
                        <th>Payment</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        <tr>
                            <td>
                                <div class="font-weight-bold text-dark">#{{ $o->id }}</div>
                                <code class="small text-muted">{{ $o->order_number }}</code>
                                @if($o->is_test)
                                    <span class="badge badge-warning ml-1">Test Order</span>
                                @endif
                            </td>
                            <td>
                                @if($o->restaurant)
                                    <a href="{{ route('admin.food.restaurants.show', $o->restaurant->id) }}" class="font-weight-bold text-primary">
                                        {{ $o->restaurant->name }}
                                    </a>
                                    <small class="text-muted d-block">{{ $o->restaurant->city }}</small>
                                @else
                                    <span class="text-muted">Restaurant #{{ $o->restaurant_id }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $o->customer_name ?: 'Guest Customer' }}</div>
                                <small class="text-muted"><i class="fa fa-phone mr-1"></i>{{ $o->customer_phone ?: 'N/A' }}</small>
                            </td>
                            <td>
                                @if($o->order_status === 'delivered')
                                    <span class="badge badge-success px-2 py-1">Delivered</span>
                                @elseif(in_array($o->order_status, ['preparing', 'accepted', 'ready_for_pickup']))
                                    <span class="badge badge-warning px-2 py-1">{{ ucwords(str_replace('_', ' ', $o->order_status)) }}</span>
                                @elseif($o->order_status === 'out_for_delivery')
                                    <span class="badge badge-info px-2 py-1">Out for Delivery</span>
                                @elseif(in_array($o->order_status, ['cancelled', 'rejected']))
                                    <span class="badge badge-danger px-2 py-1">{{ ucfirst($o->order_status) }}</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1">{{ ucfirst($o->order_status) }}</span>
                                @endif
                            </td>
                            <td class="font-weight-bold text-dark">
                                ₹{{ number_format($o->customer_payable, 2) }}
                            </td>
                            <td>
                                <span class="badge badge-light border">{{ strtoupper($o->payment_method ?: 'ONLINE') }}</span>
                                <small class="d-block {{ $o->payment_status === 'paid' ? 'text-success' : 'text-warning' }}">
                                    {{ ucfirst($o->payment_status ?: 'pending') }}
                                </small>
                            </td>
                            <td class="text-muted small">
                                {{ $o->created_at ? $o->created_at->format('d M Y, h:i A') : 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa fa-receipt fa-3x mb-3 text-light"></i>
                                <p class="mb-0">No food orders recorded matching the selected filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="text-muted small">
                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                </div>
                <div>
                    {{ $orders->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

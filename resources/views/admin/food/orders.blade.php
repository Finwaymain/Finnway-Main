@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div class="mb-2 mb-md-0">
            <h3 class="font-weight-bold text-dark mb-1">📦 Food Orders Hub</h3>
            <p class="text-muted mb-0">Track customer meal orders, live preparation milestones, rider dispatches, and delete/manage test orders.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-danger mr-2 mb-1" data-toggle="modal" data-target="#clearAllOrdersModal">
                <i class="fa fa-trash mr-1"></i> Clear All Orders
            </button>
            <a href="{{ route('admin.food.live') }}" class="btn btn-outline-danger mr-2 mb-1">
                <i class="fa fa-radar mr-1"></i> Live Kitchens Radar
            </a>
            <a href="{{ route('admin.food.restaurants') }}" class="btn btn-outline-primary mb-1">
                <i class="fa fa-store mr-1"></i> Manage Restaurants
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="get" action="{{ route('admin.food.orders') }}" class="row align-items-center">
                <div class="col-md-7 mb-2 mb-md-0">
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
                <div class="col-md-5 text-md-right">
                    <span class="text-muted small">
                        Showing {{ $orders->count() }} of {{ $orders->total() }} orders recorded
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <form id="bulkDeleteForm" method="post" action="{{ route('admin.food.orders.bulkDelete') }}">
        @csrf
        <div id="bulkActionBar" class="alert alert-warning d-none align-items-center justify-content-between py-2 px-3 mb-3 shadow-sm border-warning">
            <div class="d-flex align-items-center">
                <i class="fa fa-check-square mr-2 text-warning"></i>
                <span id="selectedCountText" class="font-weight-bold">0 orders selected</span>
            </div>
            <button type="submit" class="btn btn-sm btn-danger font-weight-bold shadow-xs" onclick="return confirm('Are you sure you want to permanently delete the selected orders? This action cannot be reversed.')">
                <i class="fa fa-trash mr-1"></i> Delete Selected Orders
            </button>
        </div>

        <!-- Orders Table -->
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" id="selectAllCheckbox" title="Select all orders on this page">
                            </th>
                            <th>Order ID / Number</th>
                            <th>Restaurant</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Payable</th>
                            <th>Payment</th>
                            <th>Date & Time</th>
                            <th class="text-center" style="width: 90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $o)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="order_ids[]" value="{{ $o->id }}" class="order-checkbox">
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">#{{ $o->id }}</div>
                                    <code class="small text-muted">{{ $o->order_number }}</code>
                                    @if($o->is_test)
                                        <span class="badge badge-warning ml-1">Test Order</span>
                                    @endif
                                    @if($o->pickup_otp)
                                        <div class="small mt-1"><span class="badge badge-light border text-purple">OTP: {{ $o->pickup_otp }}</span></div>
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
                                    @elseif(in_array($o->order_status, ['preparing', 'accepted', 'restaurant_accepted', 'ready_for_pickup']))
                                        <span class="badge badge-warning px-2 py-1">{{ ucwords(str_replace('_', ' ', $o->order_status)) }}</span>
                                    @elseif($o->order_status === 'rider_at_restaurant')
                                        <span class="badge badge-success px-2 py-1">Rider Arrived</span>
                                    @elseif(in_array($o->order_status, ['rider_assigned', 'food_picked_up', 'out_for_delivery']))
                                        <span class="badge badge-info px-2 py-1">{{ ucwords(str_replace('_', ' ', $o->order_status)) }}</span>
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
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" title="Delete this order" onclick="confirmSingleDelete({{ $o->id }}, '{{ $o->order_number ?: $o->id }}')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
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
    </form>
</div>

<!-- Hidden Single Delete Form -->
<form id="singleDeleteForm" method="post" action="" style="display:none;">
    @csrf
</form>

<!-- Modal: Clear All Orders -->
<div class="modal fade" id="clearAllOrdersModal" tabindex="-1" role="dialog" aria-labelledby="clearAllOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('admin.food.orders.clearAll') }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="clearAllOrdersModalLabel">
                        <i class="fa fa-exclamation-triangle mr-1"></i> Clear All Food Orders
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger font-weight-bold">
                        Warning: This will permanently delete ALL food orders, order line items, disputes, transactions, and settlements from the database!
                    </div>
                    <p class="text-muted small mb-3">
                        This action cannot be undone. To prevent accidental deletion, please type <strong class="text-danger">CLEAR</strong> below to confirm:
                    </p>
                    <div class="form-group mb-0">
                        <input type="text" name="confirmation" class="form-control form-control-lg text-center font-weight-bold" placeholder="Type CLEAR to confirm" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">
                        <i class="fa fa-trash mr-1"></i> Permanently Delete All Orders
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var selectAll = document.getElementById('selectAllCheckbox');
    var checkboxes = document.querySelectorAll('.order-checkbox');
    var bulkBar = document.getElementById('bulkActionBar');
    var countText = document.getElementById('selectedCountText');

    function updateBulkBar() {
        var checked = document.querySelectorAll('.order-checkbox:checked');
        var count = checked.length;
        if (count > 0) {
            bulkBar.classList.remove('d-none');
            bulkBar.classList.add('d-flex');
            countText.textContent = count + ' order' + (count > 1 ? 's' : '') + ' selected';
        } else {
            bulkBar.classList.add('d-none');
            bulkBar.classList.remove('d-flex');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (cb) {
                cb.checked = selectAll.checked;
            });
            updateBulkBar();
        });
    }

    checkboxes.forEach(function (cb) {
        cb.addEventListener('change', function () {
            if (!this.checked && selectAll) {
                selectAll.checked = false;
            }
            updateBulkBar();
        });
    });
});

function confirmSingleDelete(id, orderNum) {
    if (confirm('Are you sure you want to permanently delete Order #' + orderNum + '? This will also delete related items, transactions, and disputes.')) {
        var form = document.getElementById('singleDeleteForm');
        form.action = '{{ url("admin/food/orders") }}/' + id + '/delete';
        form.submit();
    }
}
</script>
@endsection

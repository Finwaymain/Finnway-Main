@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="mb-1 font-weight-bold">Food Operations Dashboard</h3>
            <p class="text-muted mb-0">Operational metrics, live dispatch status, partner onboarding, and system controls.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
            <a href="{{ route('admin.food.live') }}" class="btn btn-primary mr-2">
                Live Operations Radar
            </a>
            <a href="{{ route('admin.food.orders') }}" class="btn btn-outline-secondary mr-2">
                Orders Hub
            </a>
            <a href="{{ route('admin.food.restaurants', ['status' => 'pending_approval']) }}" class="btn btn-outline-secondary mr-2">
                Pending Approvals ({{ $pendingApprovals }})
            </a>
            <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#resetTablesModal">
                Database Reset
            </button>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="row">
        @foreach([
            ["Live In-Flight Orders", $liveOrders, route('admin.food.live')],
            ["Open Kitchens", $activeRestaurants, route('admin.food.restaurants')],
            ["Pending Approvals", $pendingApprovals, route('admin.food.restaurants', ['status' => 'pending_approval'])],
            ["Open Disputes", $openDisputes, route('admin.food.disputes')],
            ["Today Orders", $todayOrders, route('admin.food.orders')],
            ["Today Gross Sales", "₹" . number_format($todaySales, 2), route('admin.food.orders')],
        ] as $c)
        <div class="col-xl-4 col-md-6 mb-3">
            <a href="{{ $c[2] }}" class="text-decoration-none">
                <div class="metric-card">
                    <div class="metric-title">{{ $c[0] }}</div>
                    <div class="metric-value">{{ $c[1] }}</div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- Management Portals -->
    <div class="card mb-4 mt-2">
        <div class="card-header py-3">
            <h5 class="mb-0 font-weight-bold">Management Modules</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.restaurants') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <div class="font-weight-bold mb-1">Restaurants & Outlets</div>
                        <div class="small text-muted font-weight-normal">Manage outlets, menus, and onboarding</div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.orders') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <div class="font-weight-bold mb-1">Customer Orders</div>
                        <div class="small text-muted font-weight-normal">Search, monitor, and audit orders</div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.types') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <div class="font-weight-bold mb-1">Kitchen Types & Fees</div>
                        <div class="small text-muted font-weight-normal">Cloud kitchen and restaurant tiers</div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.commissions') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <div class="font-weight-bold mb-1">Commissions & Markup</div>
                        <div class="small text-muted font-weight-normal">Platform rates and pricing calculations</div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.charges') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <div class="font-weight-bold mb-1">Charges & Delivery</div>
                        <div class="small text-muted font-weight-normal">Base fare, distance slabs, and surges</div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.settings') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <div class="font-weight-bold mb-1">Delivery Settings</div>
                        <div class="small text-muted font-weight-normal">Radius, SLAs, and concurrency limits</div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.settlements') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <div class="font-weight-bold mb-1">Settlements & Payouts</div>
                        <div class="small text-muted font-weight-normal">Merchant payouts and financial statements</div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.disputes') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <div class="font-weight-bold mb-1">Disputes & Tickets</div>
                        <div class="small text-muted font-weight-normal">Customer complaints and resolutions</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Administrative Controls -->
    <div class="card mb-4" style="border: 1px solid #fecaca !important;">
        <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background-color: #fef2f2 !important; border-bottom: 1px solid #fecaca !important;">
            <h5 class="mb-0 font-weight-bold text-danger">Database Maintenance & Test Data Cleanup</h5>
            <span class="badge badge-danger">Super Admin</span>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Wipe test records, remove orphaned test items, or reset food delivery data for fresh testing.
            </p>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 p-3">
                        <h6 class="font-weight-bold mb-2">1. Reset Orders & History</h6>
                        <p class="small text-muted mb-3">
                            Truncates all customer orders, items, milestones, transactions, and disputes. Restaurants and menus are preserved.
                        </p>
                        <button type="button" class="btn btn-outline-secondary btn-block mt-auto" onclick="openResetModal('orders', 'Reset All Orders & History')">
                            Reset Orders Only
                        </button>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card h-100 p-3">
                        <h6 class="font-weight-bold mb-2">2. Reset Restaurants & Menus</h6>
                        <p class="small text-muted mb-3">
                            Deletes all registered restaurants, owner accounts, menus, categories, and associated orders.
                        </p>
                        <button type="button" class="btn btn-outline-secondary btn-block mt-auto" onclick="openResetModal('restaurants', 'Reset All Restaurants & Menus')">
                            Reset Restaurants Only
                        </button>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card h-100 p-3" style="background-color: #fafafa !important;">
                        <h6 class="font-weight-bold text-danger mb-2">3. Factory Reset All Tables</h6>
                        <p class="small text-muted mb-3">
                            Completely truncates all food tables (outlets, owners, products, orders, charges, settlements) and resets defaults.
                        </p>
                        <button type="button" class="btn btn-danger btn-block mt-auto" onclick="openResetModal('all', 'Full Factory Reset (All Food Tables)')">
                            Factory Reset All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Database Table Reset Confirmation -->
<div class="modal fade" id="resetTablesModal" tabindex="-1" role="dialog" aria-labelledby="resetTablesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <form method="post" action="{{ route('admin.food.reset') }}">
                @csrf
                <input type="hidden" name="scope" id="resetScopeInput" value="all">

                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="resetTablesModalLabel">Confirm Database Reset</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger mb-3" style="background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b;">
                        <span id="resetModalDescription">This operation will permanently delete records from MySQL.</span>
                    </div>

                    <p class="text-muted small mb-2">
                        To authorize this operation, type <strong>RESET</strong> in capital letters below:
                    </p>

                    <div class="form-group mb-0">
                        <input type="text" name="confirmation" id="resetConfirmationField" class="form-control text-center font-weight-bold" placeholder="Type RESET to confirm" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f9fafb; border-top: 1px solid #e5e7eb;">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Execute Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResetModal(scope, title) {
    document.getElementById('resetScopeInput').value = scope;
    document.getElementById('resetTablesModalLabel').textContent = title;
    var desc = document.getElementById('resetModalDescription');
    if (scope === 'orders') {
        desc.textContent = 'Warning: This will permanently delete ALL food orders, order items, delivery milestones, transactions, and disputes.';
    } else if (scope === 'restaurants') {
        desc.textContent = 'Warning: This will permanently delete ALL restaurants, partner accounts, menu products, categories, and related orders.';
    } else {
        desc.textContent = 'CRITICAL WARNING: This will factory reset EVERY table related to food delivery, partners, menus, and orders!';
    }
    document.getElementById('resetConfirmationField').value = '';
    $('#resetTablesModal').modal('show');
}
</script>
@endsection

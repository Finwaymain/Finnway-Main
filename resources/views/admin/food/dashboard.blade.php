@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">🍽️ Food Command Dashboard</h3>
            <p class="text-muted mb-0">High-level operational overview, live orders, active restaurant partners, and database controls.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
            <a href="{{ route('admin.food.live') }}" class="btn btn-danger mr-2">
                <i class="fa fa-radar mr-1"></i> Open Live Radar
            </a>
            <a href="{{ route('admin.food.orders') }}" class="btn btn-primary mr-2">
                <i class="fa fa-receipt mr-1"></i> Orders Hub
            </a>
            <a href="{{ route('admin.food.restaurants', ['status' => 'pending_approval']) }}" class="btn btn-warning mr-2">
                <i class="fa fa-clock mr-1"></i> Pending Approvals ({{ $pendingApprovals }})
            </a>
            <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#resetTablesModal">
                <i class="fa fa-trash-alt mr-1"></i> Reset Tables
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row">
        @foreach([
            ["Live Orders", $liveOrders, "danger", "fa fa-fire", route('admin.food.orders')],
            ["Open Kitchens", $activeRestaurants, "success", "fa fa-store", route('admin.food.restaurants')],
            ["Pending Approvals", $pendingApprovals, "warning", "fa fa-user-clock", route('admin.food.restaurants', ['status' => 'pending_approval'])],
            ["Open Disputes", $openDisputes, "info", "fa fa-balance-scale", route('admin.food.disputes')],
            ["Today Orders", $todayOrders, "primary", "fa fa-shopping-bag", route('admin.food.orders')],
            ["Today Gross Sales", "₹" . number_format($todaySales, 2), "dark", "fa fa-rupee-sign", route('admin.food.orders')],
        ] as $c)
        <div class="col-xl-4 col-md-6 mb-3">
            <a href="{{ $c[4] }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-left-{{ $c[2] }}" style="border-left: 4px solid !important;">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase font-weight-bold">{{ $c[0] }}</span>
                            <h2 class="font-weight-bold text-dark mb-0 mt-1">{{ $c[1] }}</h2>
                        </div>
                        <div class="p-3 rounded-circle bg-light text-{{ $c[2] }}">
                            <i class="{{ $c[3] }} fa-2x"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- Quick Navigation Matrix -->
    <div class="card shadow-sm border-0 mb-4 mt-2">
        <div class="card-header bg-white py-3">
            <h5 class="font-weight-bold text-dark mb-0">Management Portals</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.restaurants') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <i class="fa fa-utensils text-primary mr-2 fa-lg"></i>
                        <strong>All Restaurants</strong>
                        <small class="d-block text-muted">View, edit, onboard, & manage outlets</small>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.orders') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <i class="fa fa-receipt text-success mr-2 fa-lg"></i>
                        <strong>Orders Hub</strong>
                        <small class="d-block text-muted">Search, monitor, & delete meal orders</small>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.types') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <i class="fa fa-layer-group text-info mr-2 fa-lg"></i>
                        <strong>Kitchen Types</strong>
                        <small class="d-block text-muted">Cloud kitchens, dine-in & fees</small>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <a href="{{ route('admin.food.commissions') }}" class="btn btn-outline-secondary btn-block text-left py-3">
                        <i class="fa fa-percentage text-warning mr-2 fa-lg"></i>
                        <strong>Commission & Markup</strong>
                        <small class="d-block text-muted">Platform commissions & pricing engine</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone: Admin Reset Center -->
    <div class="card shadow-sm border-danger mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="font-weight-bold mb-0">
                <i class="fa fa-exclamation-triangle mr-1"></i> Admin Danger Zone: Food & Restaurant Database Reset
            </h5>
            <span class="badge badge-light text-danger font-weight-bold">Super Admin Authority</span>
        </div>
        <div class="card-body">
            <p class="text-muted">
                These tools allow super administrators to cleanly wipe test data, remove orphaned database records, or reset all food tables back to a fresh factory state.
            </p>

            <div class="row">
                <!-- Option 1: Reset Orders Only -->
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-warning">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="font-weight-bold text-dark"><i class="fa fa-receipt text-warning mr-1"></i> 1. Reset Orders & History</h6>
                                <p class="small text-muted mb-3">
                                    Truncates all customer food orders, order items, delivery records, settlements, transactions, and disputes. Restaurants and menu products are preserved.
                                </p>
                            </div>
                            <button type="button" class="btn btn-outline-warning btn-block font-weight-bold" onclick="openResetModal('orders', 'Reset All Food Orders & History')">
                                Reset Orders Only
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Option 2: Reset Restaurants & Menus -->
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-danger">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="font-weight-bold text-dark"><i class="fa fa-store-slash text-danger mr-1"></i> 2. Reset Restaurants & Menus</h6>
                                <p class="small text-muted mb-3">
                                    Deletes all registered restaurants, partner owners, menu items, categories, product variants, and all associated food orders.
                                </p>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-block font-weight-bold" onclick="openResetModal('restaurants', 'Reset All Restaurants & Menus')">
                                Reset Restaurants & Menus
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Option 3: Full Factory Reset -->
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-danger bg-light">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="font-weight-bold text-danger"><i class="fa fa-bomb text-danger mr-1"></i> 3. Full Factory Reset (All Tables)</h6>
                                <p class="small text-muted mb-3">
                                    Completely truncates all food tables (restaurants, owners, products, orders, payments, reviews) and seeds clean baseline types. 100% fresh start.
                                </p>
                            </div>
                            <button type="button" class="btn btn-danger btn-block font-weight-bold shadow-xs" onclick="openResetModal('all', 'Full Factory Reset (All Food Tables)')">
                                Factory Reset Everything
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Database Table Reset Confirmation -->
<div class="modal fade" id="resetTablesModal" tabindex="-1" role="dialog" aria-labelledby="resetTablesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('admin.food.reset') }}">
                @csrf
                <input type="hidden" name="scope" id="resetScopeInput" value="all">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="resetTablesModalLabel">
                        <i class="fa fa-radiation mr-1"></i> Confirm Food Database Reset
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger font-weight-bold mb-3">
                        <span id="resetModalDescription">Warning: This operation will permanently wipe selected food and restaurant data from MySQL.</span>
                    </div>

                    <p class="text-muted small mb-2">
                        Affected tables will be truncated immediately. To prevent accidental data loss, please type <strong class="text-danger">RESET</strong> in capital letters to authorize this action:
                    </p>

                    <div class="form-group mb-0">
                        <input type="text" name="confirmation" id="resetConfirmationField" class="form-control form-control-lg text-center font-weight-bold" placeholder="Type RESET to confirm" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">
                        <i class="fa fa-trash-alt mr-1"></i> Execute Table Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResetModal(scope, title) {
    document.getElementById('resetScopeInput').value = scope;
    document.getElementById('resetTablesModalLabel').innerHTML = '<i class="fa fa-radiation mr-1"></i> ' + title;
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

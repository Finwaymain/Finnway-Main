@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="font-weight-bold mb-1">Platform Commissions & Price Markups</h3>
            <p class="text-muted mb-0">Configure platform commissions, restaurant rate overrides, and pricing markups.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#addCommissionModal">
                Add Commission Rule
            </button>
            <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#addMarkupModal">
                Add Markup Rule
            </button>
        </div>
    </div>

    <!-- Commissions Table Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="font-weight-bold mb-0 text-dark">Platform Commission Rules</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Rule ID</th>
                        <th>Scope</th>
                        <th>Rule Type</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Date Configured</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $r)
                        <tr>
                            <td class="font-weight-bold">#{{ $r->id }}</td>
                            <td>
                                <span class="badge badge-light border">{{ ucfirst(str_replace('_', ' ', $r->scope)) }}</span>
                                @if($r->scope === 'restaurant')
                                    <div class="small font-weight-bold text-dark mt-1">{{ optional($r->restaurant)->name ?: ('Restaurant #' . $r->restaurant_id) }}</div>
                                @elseif($r->scope === 'restaurant_type')
                                    <div class="small font-weight-bold text-dark mt-1">{{ optional($r->restaurantType)->name ?: ('Type #' . $r->restaurant_type_id) }}</div>
                                @endif
                            </td>
                            <td>{{ ucfirst($r->rule_type) }}</td>
                            <td class="font-weight-bold text-primary">
                                {{ $r->rule_value }}{{ $r->rule_type === 'percentage' ? '%' : ' ₹' }}
                            </td>
                            <td>
                                @if($r->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Disabled</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $r->created_at ? $r->created_at->format('d M Y') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No custom commission rules configured. Default system commission applies.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Markups Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="font-weight-bold mb-0 text-dark">Customer Price Markups</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Markup ID</th>
                        <th>Scope</th>
                        <th>Type</th>
                        <th>Markup Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($markups as $m)
                        <tr>
                            <td class="font-weight-bold">#{{ $m->id }}</td>
                            <td><span class="badge badge-light border">{{ ucfirst($m->scope) }}</span></td>
                            <td>{{ ucfirst($m->rule_type) }}</td>
                            <td class="font-weight-bold text-success">
                                {{ $m->rule_value }}{{ $m->rule_type === 'percentage' ? '%' : ' ₹' }}
                            </td>
                            <td>
                                @if($m->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Disabled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No price markups configured. Menu items sell at original restaurant catalog price.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Commission Modal -->
<div class="modal fade" id="addCommissionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('admin.food.commissions.save') }}">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold">Add Commission Rule</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Scope</label>
                        <select name="scope" id="commissionScope" class="form-control custom-select">
                            <option value="global">Global (All Restaurants)</option>
                            <option value="restaurant_type">By Restaurant Type</option>
                            <option value="restaurant">Specific Restaurant</option>
                        </select>
                    </div>

                    <!-- Dynamic Restaurant Selection (Shown when Scope = Specific Restaurant) -->
                    <div class="form-group" id="commissionRestaurantGroup" style="display: none;">
                        <label class="small font-weight-bold text-muted">Select Restaurant *</label>
                        <select name="restaurant_id" id="commissionRestaurantSelect" class="form-control custom-select">
                            <option value="">-- Choose Restaurant --</option>
                            @foreach($restaurants as $rest)
                                <option value="{{ $rest->id }}">{{ $rest->name }} (ID: #{{ $rest->id }}{{ $rest->city ? ' - ' . $rest->city : '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dynamic Restaurant Type Selection (Shown when Scope = By Restaurant Type) -->
                    <div class="form-group" id="commissionTypeGroup" style="display: none;">
                        <label class="small font-weight-bold text-muted">Select Restaurant Type *</label>
                        <select name="restaurant_type_id" id="commissionTypeSelect" class="form-control custom-select">
                            <option value="">-- Choose Type --</option>
                            @foreach($restaurantTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Rule Type</label>
                            <select name="rule_type" class="form-control custom-select">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Commission Value</label>
                            <input type="number" step="0.1" name="rule_value" class="form-control" placeholder="e.g. 15" required>
                        </div>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="comActive" name="is_active" value="1" checked>
                        <label class="custom-control-label small font-weight-bold text-muted" for="comActive">Rule is active</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Commission Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Markup Modal -->
<div class="modal fade" id="addMarkupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('admin.food.markups.save') }}">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold">Add Price Markup Rule</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Scope</label>
                        <select name="scope" id="markupScope" class="form-control custom-select">
                            <option value="global">Global</option>
                            <option value="restaurant">Specific Restaurant</option>
                        </select>
                    </div>

                    <!-- Dynamic Restaurant Selection for Markup -->
                    <div class="form-group" id="markupRestaurantGroup" style="display: none;">
                        <label class="small font-weight-bold text-muted">Select Restaurant *</label>
                        <select name="restaurant_id" id="markupRestaurantSelect" class="form-control custom-select">
                            <option value="">-- Choose Restaurant --</option>
                            @foreach($restaurants as $rest)
                                <option value="{{ $rest->id }}">{{ $rest->name }} (ID: #{{ $rest->id }}{{ $rest->city ? ' - ' . $rest->city : '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Type</label>
                            <select name="rule_type" class="form-control custom-select">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Markup Value</label>
                            <input type="number" step="0.1" name="rule_value" class="form-control" placeholder="e.g. 10" required>
                        </div>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="mkActive" name="is_active" value="1" checked>
                        <label class="custom-control-label small font-weight-bold text-muted" for="mkActive">Markup is active</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Markup Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Commission Scope Switcher
    const comScope = document.getElementById('commissionScope');
    const comRestGrp = document.getElementById('commissionRestaurantGroup');
    const comTypeGrp = document.getElementById('commissionTypeGroup');

    function updateCommissionScope() {
        if (!comScope) return;
        const val = comScope.value;
        if (comRestGrp) comRestGrp.style.display = (val === 'restaurant') ? 'block' : 'none';
        if (comTypeGrp) comTypeGrp.style.display = (val === 'restaurant_type') ? 'block' : 'none';
    }

    if (comScope) {
        comScope.addEventListener('change', updateCommissionScope);
        updateCommissionScope();
    }

    // Markup Scope Switcher
    const mkScope = document.getElementById('markupScope');
    const mkRestGrp = document.getElementById('markupRestaurantGroup');

    function updateMarkupScope() {
        if (!mkScope) return;
        const val = mkScope.value;
        if (mkRestGrp) mkRestGrp.style.display = (val === 'restaurant') ? 'block' : 'none';
    }

    if (mkScope) {
        mkScope.addEventListener('change', updateMarkupScope);
        updateMarkupScope();
    }
});
</script>
@endsection

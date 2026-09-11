@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">⚡ Platform Charges & Delivery Fees</h3>
            <p class="text-muted mb-0">Manage customer convenience fees, packaging rules, and dynamic distance-based delivery slabs.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#addChargeModal">
                <i class="fa fa-plus mr-1"></i> Add Fee / Surcharge
            </button>
            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#addDeliveryModal">
                <i class="fa fa-motorcycle mr-1"></i> Add Delivery Slab
            </button>
        </div>
    </div>

    <!-- Charges Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="font-weight-bold mb-0 text-dark">Platform Fees & Packaging Surcharges</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Rule Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Charge Value</th>
                        <th>Min/Max Order</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($charges as $c)
                        <tr>
                            <td class="font-weight-bold">{{ $c->name }}</td>
                            <td><code>{{ $c->code }}</code></td>
                            <td><span class="badge badge-light border">{{ ucfirst($c->charge_type) }}</span></td>
                            <td class="font-weight-bold text-dark">
                                {{ $c->charge_value }}{{ $c->charge_type === 'percentage' ? '%' : ' ₹' }}
                            </td>
                            <td class="text-muted small">
                                ₹{{ number_format($c->min_amount ?: 0) }} - ₹{{ number_format($c->max_amount ?: 50000) }}
                            </td>
                            <td>
                                @if($c->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Disabled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No custom platform fee rules configured.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delivery Rules Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="font-weight-bold mb-0 text-dark">Distance-Based Delivery Charge Tiers</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Tier Name</th>
                        <th>Base Fee</th>
                        <th>Free Delivery Radius</th>
                        <th>Base Distance</th>
                        <th>Per KM Rate</th>
                        <th>Max Delivery Range</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($delivery as $d)
                        <tr>
                            <td class="font-weight-bold">{{ $d->name }}</td>
                            <td class="font-weight-bold text-primary">₹{{ number_format($d->base_charge, 2) }}</td>
                            <td>{{ $d->free_radius_km ? $d->free_radius_km . ' KM' : 'None' }}</td>
                            <td>{{ $d->base_radius_km }} KM</td>
                            <td>₹{{ number_format($d->per_km_charge, 2) }}/km</td>
                            <td>{{ $d->max_distance_km ? $d->max_distance_km . ' KM' : 'Unlimited' }}</td>
                            <td>
                                @if($d->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Disabled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No delivery tier rules configured. Standard ₹35 base delivery charge applies.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Charge Modal -->
<div class="modal fade" id="addChargeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('admin.food.charges.save') }}">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold">Add Platform Charge Rule</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Charge Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Platform Convenience Fee" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Unique Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. platform_fee, packaging_charge" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Type</label>
                            <select name="charge_type" class="form-control custom-select">
                                <option value="fixed">Fixed Amount (₹)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Value</label>
                            <input type="number" step="0.1" name="charge_value" class="form-control" placeholder="e.g. 5.00" required>
                        </div>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="chgActive" name="is_active" value="1" checked>
                        <label class="custom-control-label small font-weight-bold text-muted" for="chgActive">Rule is active</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Charge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Delivery Modal -->
<div class="modal fade" id="addDeliveryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('admin.food.delivery.save') }}">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold">Add Delivery Charge Rule</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Rule Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. City Standard Delivery" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Base Charge (₹)</label>
                            <input type="number" step="1" name="base_charge" class="form-control" value="35" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Base Radius (KM)</label>
                            <input type="number" step="0.5" name="base_radius_km" class="form-control" value="3" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Per KM Charge (₹)</label>
                            <input type="number" step="0.5" name="per_km_charge" class="form-control" value="10" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Max Distance (KM)</label>
                            <input type="number" step="1" name="max_distance_km" class="form-control" value="15">
                        </div>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="delActive" name="is_active" value="1" checked>
                        <label class="custom-control-label small font-weight-bold text-muted" for="delActive">Rule is active</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Delivery Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

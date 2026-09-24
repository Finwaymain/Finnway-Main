@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">⚙️ Food Delivery Global Settings</h3>
            <p class="text-muted mb-0">Configure delivery radius, multi-order dispatch concurrency, operational SLAs, and platform defaults.</p>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('admin.food.charges') }}" class="btn btn-outline-primary btn-sm mr-2">
                <i class="fa fa-money-bill mr-1"></i> Delivery Charges & Slabs
            </a>
            <a href="{{ route('admin.food.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left mr-1"></i> Food Dashboard
            </a>
        </div>
    </div>

    <form method="post" action="{{ route('admin.food.settings.save') }}">
        @csrf

        <div class="row">
            <!-- 1. Delivery Radius & Geofencing -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="font-weight-bold text-dark mb-0">
                            <i class="fa fa-map-marked-alt text-primary mr-2"></i> Delivery Radius & Geofencing
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Customer Restaurant Search Radius
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="1" name="nearby_restaurant_radius_km" value="{{ $settings['nearby_restaurant_radius_km'] ?? '15' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">km</span>
                                </div>
                            </div>
                            <small class="text-muted">Maximum radius within which customers can discover and order from restaurants (Default: 15 km).</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Rider Pickup Search Radius
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0.5" name="nearby_food_pickup_radius_km" value="{{ $settings['nearby_food_pickup_radius_km'] ?? '2' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">km</span>
                                </div>
                            </div>
                            <small class="text-muted">Initial search radius around the kitchen to dispatch nearest available riders.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Max Detour Distance for Batched Orders
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0" name="max_extra_delivery_distance_km" value="{{ $settings['max_extra_delivery_distance_km'] ?? '2' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">km</span>
                                </div>
                            </div>
                            <small class="text-muted">Maximum extra route deviation allowed when bundling multi-orders for a single courier.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-dark mb-1">
                                Max Detour Delay for Batched Orders
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="0" name="max_extra_delivery_time_min" value="{{ $settings['max_extra_delivery_time_min'] ?? '15' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">mins</span>
                                </div>
                            </div>
                            <small class="text-muted">Maximum additional customer wait time allowed when bundling multiple drop-offs.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Rider Concurrency & Batching Limits -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="font-weight-bold text-dark mb-0">
                            <i class="fa fa-motorcycle text-success mr-2"></i> Rider Concurrency & Capacity Limits
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Max Concurrent Food Orders Per Rider
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" max="5" name="max_food_orders_per_rider" value="{{ $settings['max_food_orders_per_rider'] ?? '2' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">orders</span>
                                </div>
                            </div>
                            <small class="text-muted">Maximum active food orders a single courier can carry simultaneously.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Max Concurrent Parcel Orders Per Rider
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="0" max="5" name="max_parcel_orders_per_rider" value="{{ $settings['max_parcel_orders_per_rider'] ?? '2' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">orders</span>
                                </div>
                            </div>
                            <small class="text-muted">Maximum active parcel deliveries allowed when cross-dispatching with food orders.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-dark mb-1">
                                Max Total Active Combined Orders
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" max="10" name="max_total_active_orders_per_rider" value="{{ $settings['max_total_active_orders_per_rider'] ?? '3' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">orders</span>
                                </div>
                            </div>
                            <small class="text-muted">Hard ceiling for total simultaneous assigned deliveries (Food + Parcel combined).</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Operational SLAs & Escalation Timers -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="font-weight-bold text-dark mb-0">
                            <i class="fa fa-stopwatch text-warning mr-2"></i> Operational SLAs & Kitchen Timers
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Default Kitchen Preparation SLA
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="5" name="default_prep_time_min" value="{{ $settings['default_prep_time_min'] ?? '20' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">mins</span>
                                </div>
                            </div>
                            <small class="text-muted">Default food preparation countdown assigned if outlet does not specify custom prep time.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Order Acceptance SLA / Escalation Trigger
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" name="order_accept_escalation_min" value="{{ $settings['order_accept_escalation_min'] ?? '5' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">mins</span>
                                </div>
                            </div>
                            <small class="text-muted">If a kitchen does not accept order within this duration, an admin alert is triggered.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-dark mb-1">
                                Kitchen Prep Delay Alert Trigger
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" name="prep_delay_admin_alert_min" value="{{ $settings['prep_delay_admin_alert_min'] ?? '10' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">mins</span>
                                </div>
                            </div>
                            <small class="text-muted">Alert operations if food is still preparing past SLA target by this buffer time.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Onboarding Rules & Default Financials -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="font-weight-bold text-dark mb-0">
                            <i class="fa fa-file-invoice-dollar text-info mr-2"></i> Onboarding Rules & Platform Financials
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Restaurant Onboarding Approval Mode
                            </label>
                            <select name="restaurant_auto_approval" class="form-control">
                                <option value="0" {{ ($settings['restaurant_auto_approval'] ?? '0') == '0' ? 'selected' : '' }}>
                                    Manual Verification (Admin reviews documents before activation)
                                </option>
                                <option value="1" {{ ($settings['restaurant_auto_approval'] ?? '0') == '1' ? 'selected' : '' }}>
                                    Auto Approval (Activate immediately upon registration completion)
                                </option>
                            </select>
                            <small class="text-muted">Mode of onboarding verification for newly registered restaurants and cloud kitchens.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Default Customer Platform Fee
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₹</span>
                                </div>
                                <input type="number" step="0.5" min="0" name="default_platform_fee" value="{{ $settings['default_platform_fee'] ?? '5.00' }}" class="form-control">
                            </div>
                            <small class="text-muted">Flat convenience fee charged to customer per food order.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-dark mb-1">
                                Default Restaurant Commission Rate
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0" max="100" name="default_commission_rate" value="{{ $settings['default_commission_rate'] ?? '10.0' }}" class="form-control">
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">%</span>
                                </div>
                            </div>
                            <small class="text-muted">Baseline commission rate deducted from outlet sales if no custom contract is set.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional / Dynamic Keys (Preserves any custom system keys) -->
        @php
            $standardKeys = [
                'nearby_restaurant_radius_km',
                'nearby_food_pickup_radius_km',
                'max_extra_delivery_distance_km',
                'max_extra_delivery_time_min',
                'max_food_orders_per_rider',
                'max_parcel_orders_per_rider',
                'max_total_active_orders_per_rider',
                'order_accept_escalation_min',
                'prep_delay_admin_alert_min',
                'default_prep_time_min',
                'restaurant_auto_approval',
                'default_platform_fee',
                'default_commission_rate',
            ];
            $customSettings = collect($settings)->except($standardKeys);
        @endphp

        @if($customSettings->isNotEmpty())
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-sliders-h text-secondary mr-2"></i> Custom Configuration Parameters
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        @foreach($customSettings as $k => $v)
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase mb-1">
                                    {{ ucwords(str_replace('_', ' ', $k)) }}
                                </label>
                                <input type="text" name="{{ $k }}" value="{{ $v }}" class="form-control">
                                <small class="text-muted">Key: <code>{{ $k }}</code></small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Floating or Fixed Action Bar -->
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap">
                <span class="text-muted small">
                    <i class="fa fa-info-circle text-primary mr-1"></i> Changes take effect immediately across all food dispatch and calculation engines.
                </span>
                <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm">
                    <i class="fa fa-save mr-1"></i> Save All Settings
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

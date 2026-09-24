@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="font-weight-bold mb-1">Food Delivery Global Settings</h3>
            <p class="text-muted mb-0">Delivery radius, multi-order dispatch concurrency, operational SLAs, and platform defaults.</p>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('admin.food.charges') }}" class="btn btn-outline-secondary btn-sm mr-2">
                Delivery Charges & Slabs
            </a>
            <a href="{{ route('admin.food.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                Dashboard
            </a>
        </div>
    </div>

    <form method="post" action="{{ route('admin.food.settings.save') }}">
        @csrf

        <div class="row">
            <!-- 1. Delivery Radius & Geofencing -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h5 class="mb-0 font-weight-bold">Delivery Radius & Geofencing</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label class="mb-1">
                                Customer Restaurant Search Radius
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="1" name="nearby_restaurant_radius_km" value="{{ $settings['nearby_restaurant_radius_km'] ?? '15' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">km</span>
                                </div>
                            </div>
                            <small class="text-muted">Maximum radius within which customers discover restaurants (Default: 15 km).</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="mb-1">
                                Rider Pickup Search Radius
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0.5" name="nearby_food_pickup_radius_km" value="{{ $settings['nearby_food_pickup_radius_km'] ?? '2' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">km</span>
                                </div>
                            </div>
                            <small class="text-muted">Search radius around kitchen to dispatch nearby couriers.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="mb-1">
                                Max Detour Distance for Batched Orders
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0" name="max_extra_delivery_distance_km" value="{{ $settings['max_extra_delivery_distance_km'] ?? '2' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">km</span>
                                </div>
                            </div>
                            <small class="text-muted">Maximum route deviation allowed when bundling multi-orders for a single courier.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="mb-1">
                                Max Detour Delay for Batched Orders
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="0" name="max_extra_delivery_time_min" value="{{ $settings['max_extra_delivery_time_min'] ?? '15' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">mins</span>
                                </div>
                            </div>
                            <small class="text-muted">Maximum customer wait time increase allowed for bundled drop-offs.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Rider Concurrency & Batching Limits -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h5 class="mb-0 font-weight-bold">Rider Concurrency & Capacity Limits</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label class="mb-1">
                                Max Concurrent Food Orders Per Rider
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" max="5" name="max_food_orders_per_rider" value="{{ $settings['max_food_orders_per_rider'] ?? '2' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">orders</span>
                                </div>
                            </div>
                            <small class="text-muted">Maximum active food orders a courier can carry simultaneously.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="mb-1">
                                Max Concurrent Parcel Orders Per Rider
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="0" max="5" name="max_parcel_orders_per_rider" value="{{ $settings['max_parcel_orders_per_rider'] ?? '2' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">orders</span>
                                </div>
                            </div>
                            <small class="text-muted">Maximum active parcel deliveries when cross-dispatching with food.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="mb-1">
                                Max Total Active Combined Orders
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" max="10" name="max_total_active_orders_per_rider" value="{{ $settings['max_total_active_orders_per_rider'] ?? '3' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">orders</span>
                                </div>
                            </div>
                            <small class="text-muted">Combined ceiling for simultaneous deliveries (Food + Parcel).</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Operational SLAs & Escalation Timers -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h5 class="mb-0 font-weight-bold">Operational SLAs & Kitchen Timers</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label class="mb-1">
                                Default Kitchen Preparation SLA
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="5" name="default_prep_time_min" value="{{ $settings['default_prep_time_min'] ?? '20' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">mins</span>
                                </div>
                            </div>
                            <small class="text-muted">Default food preparation duration when restaurant does not specify prep SLA.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="mb-1">
                                Order Acceptance SLA / Escalation Trigger
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" name="order_accept_escalation_min" value="{{ $settings['order_accept_escalation_min'] ?? '5' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">mins</span>
                                </div>
                            </div>
                            <small class="text-muted">Duration before an unaccepted order triggers an admin alert.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="mb-1">
                                Kitchen Prep Delay Alert Trigger
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" name="prep_delay_admin_alert_min" value="{{ $settings['prep_delay_admin_alert_min'] ?? '10' }}" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">mins</span>
                                </div>
                            </div>
                            <small class="text-muted">Alert operations if food is still in kitchen past target SLA by this duration.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Onboarding Rules & Default Financials -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h5 class="mb-0 font-weight-bold">Onboarding Rules & Financial Defaults</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label class="mb-1">
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
                            <small class="text-muted">Verification workflow for newly registered restaurants and cloud kitchens.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="mb-1">
                                Default Customer Platform Fee
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₹</span>
                                </div>
                                <input type="number" step="0.5" min="0" name="default_platform_fee" value="{{ $settings['default_platform_fee'] ?? '5.00' }}" class="form-control">
                            </div>
                            <small class="text-muted">Convenience fee charged per food order.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="mb-1">
                                Default Restaurant Commission Rate
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0" max="100" name="default_commission_rate" value="{{ $settings['default_commission_rate'] ?? '10.0' }}" class="form-control">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="text-muted">Base commission deducted from food total if no custom restaurant rate is configured.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0 font-weight-bold">Additional Configuration Parameters</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        @foreach($customSettings as $k => $v)
                            <div class="col-md-6 form-group mb-3">
                                <label class="mb-1 text-uppercase">
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

        <div class="card">
            <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap">
                <span class="text-muted small">
                    Settings update takes effect immediately across all food calculations.
                </span>
                <button type="submit" class="btn btn-primary px-4 py-2">
                    Save Settings
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')
@section('content')

<style>
.page-header-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.page-header-card h3 {
    color: #0f172a;
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0;
}
.page-header-card p {
    color: #64748b;
    font-size: 0.875rem;
    margin: 4px 0 0 0;
}

.section-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    overflow: hidden;
}
.section-card .section-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-card .section-header .header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.section-card .section-header .icon-badge {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #eff6ff;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.section-card .section-header h5 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
}
.section-card .section-body {
    padding: 20px;
}

.form-label-custom {
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 6px;
    display: block;
}
.form-control-custom {
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    font-size: 0.9rem;
    color: #1e293b;
    padding: 9px 13px;
    height: auto;
    transition: all 0.15s ease;
}
.form-control-custom:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    outline: none;
}

/* Category Checkbox Pills */
.cat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    user-select: none;
    transition: all 0.15s ease;
    margin-right: 8px;
    margin-bottom: 10px;
}
.cat-pill.active {
    background: #eff6ff;
    border-color: #2563eb;
    color: #1d4ed8;
}
.cat-pill .check-icon {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #2563eb;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
}

/* Switch Element */
.switch-label {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    margin: 0;
    vertical-align: middle;
}
.switch-label input { opacity: 0; width: 0; height: 0; }
.switch-slider {
    position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1; transition: .2s ease; border-radius: 24px;
}
.switch-slider:before {
    position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
    background-color: white; transition: .2s ease; border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.switch-label input:checked + .switch-slider { background-color: #2563eb; }
.switch-label input:checked + .switch-slider:before { transform: translateX(20px); }

/* Option box */
.prof-option-box {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.prof-option-box .opt-title { font-weight: 600; font-size: 0.875rem; color: #1e293b; margin: 0; }
.prof-option-box .opt-sub { font-size: 0.75rem; color: #64748b; margin: 2px 0 0 0; }

.discount-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    text-align: center;
}
.discount-box label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 6px;
    display: block;
    text-transform: uppercase;
}

.btn-primary-custom {
    background: #2563eb;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 10px 28px;
    font-weight: 600;
    font-size: 0.925rem;
    transition: all 0.15s ease;
}
.btn-primary-custom:hover {
    background: #1d4ed8;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(37,99,235,0.25);
}
.btn-light-custom {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 10px 24px;
    font-weight: 600;
    font-size: 0.925rem;
}
.btn-light-custom:hover { background: #e2e8f0; color: #1e293b; }

.point-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 6px 12px;
    margin-bottom: 8px;
}

/* Service Matrix Table */
.matrix-table {
    width: 100%;
    border-collapse: collapse;
}
.matrix-table th {
    background: #f8fafc;
    color: #0f172a;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 12px 16px;
    border-bottom: 2px solid #e2e8f0;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.matrix-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.875rem;
}
</style>

<div class="page-wrapper">
    <div class="container-fluid pt-3">

        <!-- Header -->
        <div class="page-header-card d-flex align-items-center justify-content-between">
            <div>
                <h3><i class="mdi mdi-pencil-box-outline text-primary mr-2"></i>Edit Business Plan</h3>
                <p>Modify subscription details, permissions, cashback rates, and limits.</p>
            </div>
            <a href="{{ url('subscription-plans') }}" class="btn btn-light-custom btn-sm">
                <i class="fa fa-arrow-left mr-1"></i> Back to Plans
            </a>
        </div>

        @if($errors->any())
        <div class="alert alert-danger" style="border-radius:10px;">
            <strong><i class="fa fa-exclamation-circle mr-1"></i>Please correct the errors below:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('subscription-plans.update', $subscriptionPlan->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Section 1: Plan Tier -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-layers-outline"></i></div>
                        <h5>Plan Tier</h5>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label-custom">Select Plan Tier <span class="text-danger">*</span></label>
                            <select name="plan_tier" class="form-control form-control-custom">
                                <option value="basic" {{ old('plan_tier', $subscriptionPlan->plan_tier ?? 'basic') === 'basic' ? 'selected' : '' }}>Basic — Starter business features</option>
                                <option value="professional" {{ old('plan_tier', $subscriptionPlan->plan_tier ?? '') === 'professional' ? 'selected' : '' }}>Professional — Standard features & limits</option>
                                <option value="premium_plus" {{ old('plan_tier', $subscriptionPlan->plan_tier ?? '') === 'premium_plus' ? 'selected' : '' }}>Premium Plus — Maximum benefits & quotas</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Basic Details -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-information-outline"></i></div>
                        <h5>Basic Information</h5>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="planName" class="form-control form-control-custom" placeholder="e.g. Professional Plan" value="{{ old('planName', $subscriptionPlan->name) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Plan Type <span class="text-danger">*</span></label>
                            <select name="planType" id="planTypeSelect" class="form-control form-control-custom">
                                <option value="free" {{ old('planType', $subscriptionPlan->type) == 'free' ? 'selected' : '' }}>Free Plan</option>
                                <option value="paid" {{ old('planType', $subscriptionPlan->type) == 'paid' ? 'selected' : '' }}>Paid Plan</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 plan_price_div {{ old('planType', $subscriptionPlan->type) == 'paid' ? '' : 'd-none' }}">
                            <label class="form-label-custom">Plan Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                <input type="number" name="planPrice" class="form-control form-control-custom" placeholder="0.00" min="0" step="0.01" value="{{ old('planPrice', $subscriptionPlan->price) }}">
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Validity Period</label>
                            <select name="plan_validity_days" id="validityTypeSelect" class="form-control form-control-custom">
                                <option value="unlimited" {{ old('plan_validity_days', $subscriptionPlan->expiryDay == '-1' ? 'unlimited' : 'limited') == 'unlimited' ? 'selected' : '' }}>Unlimited / Lifetime</option>
                                <option value="limited" {{ old('plan_validity_days', $subscriptionPlan->expiryDay != '-1' ? 'limited' : 'unlimited') == 'limited' ? 'selected' : '' }}>Fixed Days</option>
                            </select>
                            <div class="mt-2 expiry-limit-div {{ old('plan_validity_days', $subscriptionPlan->expiryDay != '-1' ? 'limited' : 'unlimited') == 'limited' ? '' : 'd-none' }}">
                                <input type="number" name="plan_validity" class="form-control form-control-custom" placeholder="Enter number of days (e.g. 30)" value="{{ old('plan_validity', $subscriptionPlan->expiryDay) }}">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Display Order <span class="text-danger">*</span></label>
                            <input type="number" name="order" class="form-control form-control-custom" placeholder="1" min="0" value="{{ old('order', $subscriptionPlan->place) }}" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label-custom">Plan Status</label>
                            <div class="prof-option-box">
                                <div>
                                    <p class="opt-title">Enable Plan</p>
                                    <p class="opt-sub">Allow users to view and purchase this plan</p>
                                </div>
                                <label class="switch-label">
                                    <input type="checkbox" name="status" id="statusSwitch" {{ old('status', $subscriptionPlan->isEnable == 'true') ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label-custom">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control form-control-custom" rows="2" placeholder="Short description of this plan...">{{ old('description', $subscriptionPlan->description) }}</textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Plan Image</label>
                            <input type="file" name="image" class="form-control-file" accept="image/*" onchange="previewImg(this)">
                            <div id="img_preview" class="mt-2 {{ $subscriptionPlan->image ? '' : 'd-none' }}">
                                <img id="preview_src" src="{{ $subscriptionPlan->image ? asset('assets/images/subscription/' . $subscriptionPlan->image) : '#' }}" alt="Preview" style="height:50px; border-radius:6px; border:1px solid #cbd5e1;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Business Categories Section (Fetched from DB) -->
            @php
                $dbCategories = isset($categories) && count($categories) > 0 ? $categories : (Illuminate\Support\Facades\Schema::hasTable('tj_category') ? Illuminate\Support\Facades\DB::table('tj_category')->get() : collect([]));
            @endphp
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-shape-outline"></i></div>
                        <h5>Business Categories (Dynamic from DB)</h5>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllCats(true)">Select All</button>
                </div>
                <div class="section-body">
                    <p class="text-muted small mb-3">Choose the specific business categories where this plan applies:</p>
                    <div class="d-flex flex-wrap align-items-center">
                        @if($dbCategories->count() > 0)
                            @foreach($dbCategories as $cat)
                            @php
                                $catName = $cat->nom ?? $cat->title ?? $cat->name ?? 'Category #'.$cat->id;
                            @endphp
                            <label class="cat-pill active">
                                <span class="check-icon"><i class="fa fa-check"></i></span>
                                <input type="checkbox" name="categories[]" value="{{ $cat->id }}" checked class="cat-checkbox" style="display:none;">
                                {{ $catName }}
                            </label>
                            @endforeach
                        @else
                            @php
                                $defaultCats = ['Cab Driver', 'Bike Taxi', 'Auto Rickshaw', 'Delivery Partner', 'Merchant', 'Home Services', 'Food Delivery'];
                            @endphp
                            @foreach($defaultCats as $cat)
                            <label class="cat-pill active">
                                <span class="check-icon"><i class="fa fa-check"></i></span>
                                <input type="checkbox" name="categories[]" value="{{ $cat }}" checked class="cat-checkbox" style="display:none;">
                                {{ $cat }}
                            </label>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Section 3: Feature Highlights -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-checkbox-marked-circle-outline"></i></div>
                        <h5>Plan Features (Bullet Points)</h5>
                    </div>
                </div>
                <div class="section-body">
                    <div id="points_container">
                        @php
                            $pts = is_array($subscriptionPlan->plan_points) ? $subscriptionPlan->plan_points : (json_decode($subscriptionPlan->plan_points, true) ?? ['Standard Provider Features']);
                        @endphp
                        @foreach($pts as $pt)
                        <div class="point-row d-flex align-items-center gap-2">
                            <i class="mdi mdi-check text-success mr-2"></i>
                            <input type="text" class="form-control form-control-custom border-0 bg-transparent" name="plan_points[]" value="{{ $pt }}">
                            <button type="button" class="btn btn-sm text-danger ml-auto" onclick="removePoint(this)"><i class="fa fa-trash"></i></button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addPoint()" class="btn btn-sm btn-light-custom mt-2">
                        <i class="fa fa-plus mr-1"></i> Add Feature
                    </button>
                </div>
            </div>

            <!-- Section 4: Booking Limits & Quotas -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-calendar-clock"></i></div>
                        <h5>Booking Limits & Free Quotas</h5>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Booking Limit</label>
                            <select name="set_booking_limit" id="bookingLimitSelect" class="form-control form-control-custom">
                                <option value="unlimited" {{ old('set_booking_limit', $subscriptionPlan->bookingLimit == '-1' ? 'unlimited' : 'limited') == 'unlimited' ? 'selected' : '' }}>Unlimited Bookings</option>
                                <option value="limited" {{ old('set_booking_limit', $subscriptionPlan->bookingLimit != '-1' ? 'limited' : 'unlimited') == 'limited' ? 'selected' : '' }}>Limited Bookings</option>
                            </select>
                            <div class="mt-2 booking-limit-div {{ old('set_booking_limit', $subscriptionPlan->bookingLimit != '-1' ? 'limited' : 'unlimited') == 'limited' ? '' : 'd-none' }}">
                                <input type="number" name="booking_limit" class="form-control form-control-custom" placeholder="Max orders (e.g. 100)" value="{{ old('booking_limit', $subscriptionPlan->bookingLimit) }}">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Free Ride Limit</label>
                            <input type="number" name="free_ride_limit" class="form-control form-control-custom" min="0" placeholder="0" value="{{ old('free_ride_limit', $subscriptionPlan->free_ride_limit ?? 0) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Free Ride Reset</label>
                            <select name="free_ride_reset" class="form-control form-control-custom">
                                <option value="monthly" {{ old('free_ride_reset', $subscriptionPlan->free_ride_reset ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('free_ride_reset', $subscriptionPlan->free_ride_reset ?? '') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('free_ride_reset', $subscriptionPlan->free_ride_reset ?? '') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Cashback Configuration -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-cash-refund"></i></div>
                        <h5>Cashback Settings</h5>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light border rounded">
                                <span class="form-label-custom mb-2"><i class="mdi mdi-arrow-up-bold-circle text-primary mr-1"></i>Sender Cashback</span>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small text-muted mb-1">Type</label>
                                        <select name="sender_cashback_type" class="form-control form-control-custom">
                                            <option value="percentage" {{ old('sender_cashback_type', $subscriptionPlan->sender_cashback_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                            <option value="flat" {{ old('sender_cashback_type', $subscriptionPlan->sender_cashback_type ?? '') === 'flat' ? 'selected' : '' }}>Flat (₹)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted mb-1">Value</label>
                                        <input type="number" name="sender_cashback_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('sender_cashback_value', $subscriptionPlan->sender_cashback_value ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light border rounded">
                                <span class="form-label-custom mb-2"><i class="mdi mdi-arrow-down-bold-circle text-success mr-1"></i>Receiver Cashback</span>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small text-muted mb-1">Type</label>
                                        <select name="receiver_cashback_type" class="form-control form-control-custom">
                                            <option value="percentage" {{ old('receiver_cashback_type', $subscriptionPlan->receiver_cashback_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                            <option value="flat" {{ old('receiver_cashback_type', $subscriptionPlan->receiver_cashback_type ?? '') === 'flat' ? 'selected' : '' }}>Flat (₹)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted mb-1">Value</label>
                                        <input type="number" name="receiver_cashback_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('receiver_cashback_value', $subscriptionPlan->receiver_cashback_value ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 6: Category Discounts -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-percent-outline"></i></div>
                        <h5>Category Discounts (%)</h5>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row">
                        @php 
                            $discounts = [
                                'discount_home_service' => ['Home Service', 'mdi-home-outline'],
                                'discount_travel'       => ['Travel', 'mdi-airplane-takeoff'],
                                'discount_hotel'        => ['Hotel', 'mdi-office-building'],
                                'discount_food'         => ['Food', 'mdi-food-fork-drink'],
                                'discount_medical'      => ['Medical', 'mdi-medical-bag'],
                                'discount_marketplace'  => ['Marketplace', 'mdi-store-outline'],
                                'shopping_discount'     => ['Shopping', 'mdi-cart-outline']
                            ]; 
                        @endphp
                        @foreach($discounts as $field => [$label, $icon])
                        <div class="col-md-3 col-6 mb-3">
                            <div class="discount-box">
                                <label><i class="mdi {{ $icon }} text-primary mr-1"></i>{{ $label }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="{{ $field }}" class="form-control form-control-custom text-center font-weight-bold" min="0" max="100" step="0.1" value="{{ old($field, $subscriptionPlan->$field ?? 0) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Section 7: Wallet & Loan Privileges -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-wallet-outline"></i></div>
                        <h5>Wallet & Loan Privileges</h5>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Wallet Top-Up Bonus</label>
                            <input type="number" name="wallet_increment_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('wallet_increment_value', $subscriptionPlan->wallet_increment_value ?? 0) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Bonus Period</label>
                            <select name="wallet_increment_period" class="form-control form-control-custom">
                                <option value="daily" {{ ($subscriptionPlan->wallet_increment_period ?? '') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ ($subscriptionPlan->wallet_increment_period ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ ($subscriptionPlan->wallet_increment_period ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Referral Bonus Type</label>
                            <select name="referral_bonus_type" class="form-control form-control-custom">
                                <option value="flat" {{ ($subscriptionPlan->referral_bonus_type ?? 'flat') == 'flat' ? 'selected' : '' }}>Flat (₹)</option>
                                <option value="percentage" {{ ($subscriptionPlan->referral_bonus_type ?? '') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Referral Bonus Value</label>
                            <input type="number" name="referral_bonus_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('referral_bonus_value', $subscriptionPlan->referral_bonus_value ?? 0) }}">
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-2">
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-3">
                                <div class="prof-option-box">
                                    <div>
                                        <p class="opt-title">Enable Loan Access</p>
                                        <p class="opt-sub">Allow providers on this plan to request business loans</p>
                                    </div>
                                    <label class="switch-label">
                                        <input type="checkbox" name="loan_enabled" id="loanSwitch" {{ old('loan_enabled', $subscriptionPlan->loan_enabled) ? 'checked' : '' }}>
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Max Loan Amount (₹)</label>
                                <input type="number" name="loan_max_amount" class="form-control form-control-custom" min="0" value="{{ old('loan_max_amount', $subscriptionPlan->loan_max_amount ?? 0) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Service Permission Matrix (Fetched from DB Categories) -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-grid"></i></div>
                        <h5>Service Permission Matrix (Dynamic API/DB Data)</h5>
                    </div>
                </div>
                <div class="section-body p-0">
                    <div class="table-responsive">
                        <table class="matrix-table">
                            <thead>
                                <tr>
                                    <th style="width:40%;">Service / Category</th>
                                    <th style="text-align:center;">Basic</th>
                                    <th style="text-align:center;">Professional</th>
                                    <th style="text-align:center;">Premium Plus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($dbCategories->count() > 0)
                                    @foreach($dbCategories as $idx => $cat)
                                    @php
                                        $catTitle = $cat->nom ?? $cat->title ?? $cat->name ?? 'Category #'.$cat->id;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $catTitle }}</strong></td>
                                        <td style="text-align:center;">
                                            <label class="switch-label">
                                                <input type="checkbox" name="matrix[{{ $cat->id }}][basic]" {{ $idx % 2 == 0 ? 'checked' : '' }}>
                                                <span class="switch-slider"></span>
                                            </label>
                                        </td>
                                        <td style="text-align:center;">
                                            <label class="switch-label">
                                                <input type="checkbox" name="matrix[{{ $cat->id }}][professional]" checked>
                                                <span class="switch-slider"></span>
                                            </label>
                                        </td>
                                        <td style="text-align:center;">
                                            <label class="switch-label">
                                                <input type="checkbox" name="matrix[{{ $cat->id }}][premium_plus]" checked>
                                                <span class="switch-slider"></span>
                                            </label>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    @php
                                        $matrixDefault = ['QR Payment', 'Wallet', 'Cab Booking', 'Bike Taxi', 'Parcel', 'Marketplace', 'Travel', 'Hotels', 'Healthcare Cards', 'Loan Services', 'Premium Listing', 'Priority Search'];
                                    @endphp
                                    @foreach($matrixDefault as $idx => $serv)
                                    <tr>
                                        <td><strong>{{ $serv }}</strong></td>
                                        <td style="text-align:center;">
                                            <label class="switch-label">
                                                <input type="checkbox" name="matrix[{{ $idx }}][basic]" {{ $idx < 3 ? 'checked' : '' }}>
                                                <span class="switch-slider"></span>
                                            </label>
                                        </td>
                                        <td style="text-align:center;">
                                            <label class="switch-label">
                                                <input type="checkbox" name="matrix[{{ $idx }}][professional]" {{ $idx < 10 ? 'checked' : '' }}>
                                                <span class="switch-slider"></span>
                                            </label>
                                        </td>
                                        <td style="text-align:center;">
                                            <label class="switch-label">
                                                <input type="checkbox" name="matrix[{{ $idx }}][premium_plus]" checked>
                                                <span class="switch-slider"></span>
                                            </label>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="d-flex align-items-center gap-3 mb-5">
                <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save mr-1"></i> Update Business Plan</button>
                <a href="{{ url('subscription-plans') }}" class="btn btn-light-custom">Cancel</a>
            </div>

        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#planTypeSelect').on('change', function() {
        if ($(this).val() === 'paid') {
            $('.plan_price_div').removeClass('d-none');
        } else {
            $('.plan_price_div').addClass('d-none');
        }
    });

    $('#validityTypeSelect').on('change', function() {
        if ($(this).val() === 'limited') {
            $('.expiry-limit-div').removeClass('d-none');
        } else {
            $('.expiry-limit-div').addClass('d-none');
        }
    });

    $('#bookingLimitSelect').on('change', function() {
        if ($(this).val() === 'limited') {
            $('.booking-limit-div').removeClass('d-none');
        } else {
            $('.booking-limit-div').addClass('d-none');
        }
    });
});

function addPoint() {
    const html = `
    <div class="point-row d-flex align-items-center gap-2">
        <i class="mdi mdi-check text-success mr-2"></i>
        <input type="text" class="form-control form-control-custom border-0 bg-transparent" name="plan_points[]" placeholder="Enter feature text...">
        <button type="button" class="btn btn-sm text-danger ml-auto" onclick="removePoint(this)"><i class="fa fa-trash"></i></button>
    </div>`;
    $('#points_container').append(html);
}

function removePoint(btn) {
    if ($('#points_container .point-row').length > 1) {
        $(btn).closest('.point-row').remove();
    }
}

function previewImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#preview_src').attr('src', e.target.result);
            $('#img_preview').removeClass('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function selectAllCats(select) {
    $('.cat-pill').addClass('active');
    $('.cat-checkbox').prop('checked', true);
}
</script>
@endsection
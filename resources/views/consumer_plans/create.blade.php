@extends('layouts.app')
@section('content')
@php $isEdit = isset($plan); $formAction = $isEdit ? route('consumer-plans.update', $plan->id) : route('consumer-plans.store'); @endphp

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

/* Custom Checkbox & Toggle Box */
.prof-option-box {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
    cursor: pointer;
    user-select: none;
}
.prof-option-box:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.prof-option-box .opt-title {
    font-weight: 600;
    font-size: 0.875rem;
    color: #1e293b;
    margin: 0;
}
.prof-option-box .opt-sub {
    font-size: 0.75rem;
    color: #64748b;
    margin: 2px 0 0 0;
}

/* Elegant Switch Element */
.switch-label {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    margin: 0;
    vertical-align: middle;
}
.switch-label input {
    opacity: 0;
    width: 0;
    height: 0;
}
.switch-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .2s ease;
    border-radius: 24px;
}
.switch-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .2s ease;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.switch-label input:checked + .switch-slider {
    background-color: #2563eb;
}
.switch-label input:checked + .switch-slider:before {
    transform: translateX(20px);
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
</style>

<div class="page-wrapper">
    <div class="container-fluid pt-3">

        <!-- Header -->
        <div class="page-header-card d-flex align-items-center justify-content-between">
            <div>
                <h3><i class="mdi mdi-crown text-primary mr-2"></i>{{ $isEdit ? 'Edit' : 'Create' }} Consumer Premium Plan</h3>
                <p>Configure consumer subscription rates, cashback, service discounts, and quotas.</p>
            </div>
            <a href="{{ route('consumer-plans.index') }}" class="btn btn-light-custom btn-sm">
                <i class="fa fa-arrow-left mr-1"></i> Back to Plans
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" style="border-radius:10px;">
                <strong><i class="fa fa-exclamation-circle mr-1"></i>Please correct the errors below:</strong>
                <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ $formAction }}" method="POST" id="consumer_plan_form">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <!-- Section 1: Basic Details -->
            <div class="section-card">
                <div class="section-header">
                    <div class="icon-badge"><i class="mdi mdi-information-outline"></i></div>
                    <h5>Basic Information</h5>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-custom" placeholder="e.g. Premium Plus" value="{{ old('name', $plan->name ?? '') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Price (₹/year) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                <input type="number" name="price" class="form-control form-control-custom" placeholder="1100" min="0" step="0.01" value="{{ old('price', $plan->price ?? '') }}" required>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Validity (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="validity_days" class="form-control form-control-custom" placeholder="365" min="1" value="{{ old('validity_days', $plan->validity_days ?? 365) }}" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label-custom">Display Order</label>
                            <input type="number" name="display_order" class="form-control form-control-custom" min="0" value="{{ old('display_order', $plan->display_order ?? 0) }}">
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-7 mb-3">
                            <label class="form-label-custom">Description</label>
                            <textarea name="description" class="form-control form-control-custom" rows="2" placeholder="Plan highlights & benefits description...">{{ old('description', $plan->description ?? '') }}</textarea>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label-custom">Plan Status</label>
                            <div class="prof-option-box">
                                <div>
                                    <p class="opt-title">Enable Plan</p>
                                    <p class="opt-sub">Allow consumers to purchase this plan</p>
                                </div>
                                <label class="switch-label">
                                    <input type="checkbox" name="status" id="statusSwitch" {{ old('status', ($plan->status ?? 'active') === 'active') ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Cashback Configuration -->
            <div class="section-card">
                <div class="section-header">
                    <div class="icon-badge"><i class="mdi mdi-cash-refund"></i></div>
                    <h5>Cashback Benefits</h5>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light border rounded">
                                <span class="form-label-custom mb-2"><i class="mdi mdi-arrow-up-bold-circle text-primary mr-1"></i>Sender Cashback</span>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small text-muted mb-1">Type</label>
                                        <select name="sender_cashback_type" id="sender_cashback_type" class="form-control form-control-custom" onchange="updateCashbackLabels()">
                                            <option value="percentage" {{ old('sender_cashback_type', $plan->sender_cashback_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                            <option value="flat" {{ old('sender_cashback_type', $plan->sender_cashback_type ?? '') === 'flat' ? 'selected' : '' }}>Flat (₹)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted mb-1" id="sender_cashback_label">Enter Percentage (%)</label>
                                        <input type="number" name="sender_cashback_value" id="sender_cashback_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('sender_cashback_value', $plan->sender_cashback_value ?? 0) }}">
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
                                        <select name="receiver_cashback_type" id="receiver_cashback_type" class="form-control form-control-custom" onchange="updateCashbackLabels()">
                                            <option value="percentage" {{ old('receiver_cashback_type', $plan->receiver_cashback_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                            <option value="flat" {{ old('receiver_cashback_type', $plan->receiver_cashback_type ?? '') === 'flat' ? 'selected' : '' }}>Flat (₹)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted mb-1" id="receiver_cashback_label">Enter Percentage (%)</label>
                                        <input type="number" name="receiver_cashback_value" id="receiver_cashback_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('receiver_cashback_value', $plan->receiver_cashback_value ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Category Benefits, Quotas & Discounts Table -->
            <div class="section-card">
                <div class="section-header">
                    <div class="icon-badge"><i class="mdi mdi-table-edit"></i></div>
                    <h5>Category Benefits, Quotas & Discounts</h5>
                </div>
                <div class="section-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0 text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-left pl-4" style="width: 28%;">Category / Service</th>
                                    <th style="width: 24%;">Max Amount (₹)</th>
                                    <th style="width: 24%;">Booking Quota / Month</th>
                                    <th style="width: 24%;">Discount (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-left font-weight-bold pl-4">
                                        <i class="mdi mdi-office-building text-primary mr-2"></i>Hotel Booking
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                            <input type="number" name="min_amount_hotel" class="form-control text-center font-weight-bold" min="0" step="0.01" value="{{ old('min_amount_hotel', $plan->min_amount_hotel ?? 0) }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="quota_hotel_booking" class="form-control form-control-sm text-center font-weight-bold" min="0" value="{{ old('quota_hotel_booking', $plan->quota_hotel_booking ?? 0) }}" placeholder="e.g. 5">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="discount_hotel" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_hotel', $plan->discount_hotel ?? 0) }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold pl-4">
                                        <i class="mdi mdi-home-outline text-primary mr-2"></i>Home Services
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                            <input type="number" name="min_amount_home_service" class="form-control text-center font-weight-bold" min="0" step="0.01" value="{{ old('min_amount_home_service', $plan->min_amount_home_service ?? 0) }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="quota_home_service" class="form-control form-control-sm text-center font-weight-bold" min="0" value="{{ old('quota_home_service', $plan->quota_home_service ?? 0) }}" placeholder="e.g. 5">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="discount_home_service" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_home_service', $plan->discount_home_service ?? 0) }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold pl-4">
                                        <i class="mdi mdi-store-outline text-primary mr-2"></i>Shopping & Marketplace
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                            <input type="number" name="min_amount_shopping" class="form-control text-center font-weight-bold" min="0" step="0.01" value="{{ old('min_amount_shopping', $plan->min_amount_shopping ?? 0) }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="quota_shopping" class="form-control form-control-sm text-center font-weight-bold" min="0" value="{{ old('quota_shopping', $plan->quota_shopping ?? 0) }}" placeholder="e.g. 10">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="discount_marketplace" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_marketplace', $plan->discount_marketplace ?? 0) }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold pl-4">
                                        <i class="mdi mdi-food-fork-drink text-primary mr-2"></i>Food Delivery
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                            <input type="number" name="min_amount_food" class="form-control text-center font-weight-bold" min="0" step="0.01" value="{{ old('min_amount_food', $plan->min_amount_food ?? 0) }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="quota_food" class="form-control form-control-sm text-center font-weight-bold" min="0" value="{{ old('quota_food', $plan->quota_food ?? 0) }}" placeholder="e.g. 15">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="discount_food" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_food', $plan->discount_food ?? 0) }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold pl-4">
                                        <i class="mdi mdi-airplane-takeoff text-primary mr-2"></i>Travel Booking
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                            <input type="number" name="min_amount_travel" class="form-control text-center font-weight-bold" min="0" step="0.01" value="{{ old('min_amount_travel', $plan->min_amount_travel ?? 0) }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="quota_travel" class="form-control form-control-sm text-center font-weight-bold" min="0" value="{{ old('quota_travel', $plan->quota_travel ?? 0) }}" placeholder="e.g. 5">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="discount_travel" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_travel', $plan->discount_travel ?? 0) }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold pl-4">
                                        <i class="mdi mdi-medical-bag text-primary mr-2"></i>Medical & Healthcare
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                            <input type="number" name="min_amount_medical" class="form-control text-center font-weight-bold" min="0" step="0.01" value="{{ old('min_amount_medical', $plan->min_amount_medical ?? 0) }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="quota_medical" class="form-control form-control-sm text-center font-weight-bold" min="0" value="{{ old('quota_medical', $plan->quota_medical ?? 0) }}" placeholder="e.g. 3">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="discount_healthcare" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_healthcare', $plan->discount_healthcare ?? $plan->discount_medical ?? 0) }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold pl-4">
                                        <i class="mdi mdi-car text-primary mr-2"></i>Cab & Rides
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                            <input type="number" name="min_amount_cab" class="form-control text-center font-weight-bold" min="0" step="0.01" value="{{ old('min_amount_cab', $plan->min_amount_cab ?? 0) }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="free_ride_limit" class="form-control form-control-sm text-center font-weight-bold" min="0" value="{{ old('free_ride_limit', $plan->free_ride_limit ?? 0) }}" placeholder="e.g. 20">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="discount_cab" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_cab', $plan->discount_cab ?? 0) }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold pl-4">
                                        <i class="mdi mdi-truck-delivery-outline text-primary mr-2"></i>Shipping & Delivery
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                            <input type="number" name="shipping_min_order" class="form-control text-center font-weight-bold" min="0" step="0.01" value="{{ old('shipping_min_order', $plan->shipping_min_order ?? 0) }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="free_shipping_count" class="form-control form-control-sm text-center font-weight-bold" min="0" value="{{ old('free_shipping_count', $plan->free_shipping_count ?? 0) }}" placeholder="e.g. 10">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="discount_delivery" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_delivery', $plan->discount_delivery ?? 0) }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 bg-light border-top">
                        <!-- Per-Service Delivery Discounts -->
                        <label class="form-label-custom mb-1"><i class="mdi mdi-truck-fast-outline text-warning mr-1"></i>Per-Service Delivery Fee Discounts (%)</label>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="small text-muted mb-1">Food Delivery Discount</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="discount_delivery_food" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_delivery_food', $plan->discount_delivery_food ?? 0) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small text-muted mb-1">Shopping Delivery Discount</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="discount_delivery_shopping" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_delivery_shopping', $plan->discount_delivery_shopping ?? 0) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small text-muted mb-1">Home Service Delivery Discount</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="discount_delivery_home_service" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_delivery_home_service', $plan->discount_delivery_home_service ?? 0) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small text-muted mb-1">Medical Delivery Discount</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="discount_delivery_medical" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_delivery_medical', $plan->discount_delivery_medical ?? 0) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small text-muted mb-1">Parcel / Courier Delivery Discount</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="discount_delivery_parcel" class="form-control text-center font-weight-bold" min="0" max="100" step="0.01" value="{{ old('discount_delivery_parcel', $plan->discount_delivery_parcel ?? 0) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Credit & Vouchers -->
                        <div class="border-top pt-3 mt-2">
                            <label class="form-label-custom mb-2"><i class="mdi mdi-wallet-outline text-info mr-1"></i>Virtual Credit, Bonuses & Vouchers</label>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="small text-muted mb-1">Virtual Credit Limit (₹)</label>
                                    <input type="number" name="virtual_credit_limit" class="form-control form-control-sm" min="0" step="0.01" value="{{ old('virtual_credit_limit', $plan->virtual_credit_limit ?? 0) }}">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small text-muted mb-1">Monthly Bonus (₹)</label>
                                    <input type="number" name="wallet_monthly_bonus" class="form-control form-control-sm" min="0" step="0.01" value="{{ old('wallet_monthly_bonus', $plan->wallet_monthly_bonus ?? 0) }}">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small text-muted mb-1">Annual Voucher Value (₹)</label>
                                    <input type="number" name="annual_voucher_value" class="form-control form-control-sm" min="0" step="0.01" value="{{ old('annual_voucher_value', $plan->annual_voucher_value ?? 0) }}">
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-2">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-2">
                                    <div class="prof-option-box">
                                        <div>
                                            <p class="opt-title mb-0 font-weight-bold">Enable Loan Access</p>
                                            <p class="opt-sub mb-0 text-muted small">Allow consumers on this plan to request loans</p>
                                        </div>
                                        <label class="switch-label ml-3">
                                            <input type="checkbox" name="loan_enabled" id="loanSwitch" {{ old('loan_enabled', $plan->loan_enabled ?? false) ? 'checked' : '' }}>
                                            <span class="switch-slider"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small text-muted mb-1">Max Loan Amount (₹)</label>
                                    <input type="number" name="loan_max_amount" class="form-control form-control-sm" min="0" step="0.01" value="{{ old('loan_max_amount', $plan->loan_max_amount ?? 0) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="d-flex align-items-center gap-3 mb-5">
                <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save mr-1"></i> {{ $isEdit ? 'Update' : 'Save' }} Consumer Plan</button>
                <a href="{{ route('consumer-plans.index') }}" class="btn btn-light-custom">Cancel</a>
            </div>

        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateCashbackLabels() {
    var senderSelect = document.getElementById('sender_cashback_type');
    var senderLabel = document.getElementById('sender_cashback_label');
    var senderInput = document.getElementById('sender_cashback_value');

    if (senderSelect && senderLabel) {
        if (senderSelect.value === 'flat') {
            senderLabel.innerText = 'Enter Value (₹)';
            if (senderInput) senderInput.placeholder = 'Enter value';
        } else {
            senderLabel.innerText = 'Enter Percentage (%)';
            if (senderInput) senderInput.placeholder = 'Enter percentage';
        }
    }

    var receiverSelect = document.getElementById('receiver_cashback_type');
    var receiverLabel = document.getElementById('receiver_cashback_label');
    var receiverInput = document.getElementById('receiver_cashback_value');

    if (receiverSelect && receiverLabel) {
        if (receiverSelect.value === 'flat') {
            receiverLabel.innerText = 'Enter Value (₹)';
            if (receiverInput) receiverInput.placeholder = 'Enter value';
        } else {
            receiverLabel.innerText = 'Enter Percentage (%)';
            if (receiverInput) receiverInput.placeholder = 'Enter percentage';
        }
    }
}

$(document).ready(function() {
    updateCashbackLabels();
});
</script>
@endsection

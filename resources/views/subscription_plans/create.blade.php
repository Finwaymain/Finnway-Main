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

/* Category Checkbox Pills (Admin Blue Theme) */
.category-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
    cursor: pointer;
    user-select: none;
    transition: all 0.15s ease;
    margin-right: 10px;
    margin-bottom: 12px;
}
.category-pill:hover { border-color: #2563eb; background: #eff6ff; }
.category-pill.active {
    background: #eff6ff;
    border-color: #2563eb;
    color: #1d4ed8;
    box-shadow: 0 1px 3px rgba(37,99,235,0.1);
}
.category-pill .check-box-icon {
    width: 20px;
    height: 20px;
    border-radius: 5px;
    background: #2563eb;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
}
.category-pill:not(.active) .check-box-icon {
    background: #e2e8f0;
    color: transparent;
}

/* Switch Element (Admin Blue Theme) */
.switch-label {
    position: relative;
    display: inline-block;
    width: 46px;
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
.switch-label input:checked + .switch-slider:before { transform: translateX(22px); }

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

.discount-item-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}
.discount-item-row:last-child { border-bottom: none; }
.discount-item-row label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
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

.category-free-order-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>

<div class="page-wrapper">
    <div class="container-fluid pt-3">

        <!-- Header -->
        <div class="page-header-card d-flex align-items-center justify-content-between">
            <div>
                <h3><i class="mdi mdi-briefcase-plus text-primary mr-2"></i>Create Business Plan</h3>
                <p>Configure plan pricing, category access, free order quotas, discounts, and loan features.</p>
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

        <form action="{{ route('subscription-plans.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="order" value="1">
            <input type="hidden" name="set_booking_limit" value="unlimited">

            <!-- Section 1: Plan Tier & Basic Details -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-information-outline"></i></div>
                        <h5>Basic Plan Details</h5>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Select Plan Tier <span class="text-danger">*</span></label>
                            <select name="plan_tier" class="form-control form-control-custom">
                                <option value="basic" {{ old('plan_tier','basic') === 'basic' ? 'selected' : '' }}>Basic Plan — Starter business features</option>
                                <option value="professional" {{ old('plan_tier') === 'professional' ? 'selected' : '' }}>Professional Plan — Recommended</option>
                                <option value="premium_plus" {{ old('plan_tier') === 'premium_plus' ? 'selected' : '' }}>Premium Plus — Maximum benefits</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="planName" class="form-control form-control-custom" placeholder="e.g. Professional Plan" value="{{ old('planName') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Plan Type <span class="text-danger">*</span></label>
                            <select name="planType" id="planTypeSelect" class="form-control form-control-custom">
                                <option value="free" {{ old('planType','free') == 'free' ? 'selected' : '' }}>Free Plan</option>
                                <option value="paid" {{ old('planType') == 'paid' ? 'selected' : '' }}>Paid Plan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-3 plan_price_div {{ old('planType','free') == 'paid' ? '' : 'd-none' }}">
                            <label class="form-label-custom">Plan Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                <input type="number" name="planPrice" class="form-control form-control-custom" placeholder="2500" min="0" step="0.01" value="{{ old('planPrice') }}">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Validity Period</label>
                            <select name="plan_validity_days" id="validityTypeSelect" class="form-control form-control-custom">
                                <option value="unlimited" {{ old('plan_validity_days','unlimited') == 'unlimited' ? 'selected' : '' }}>Unlimited / Lifetime</option>
                                <option value="limited" {{ old('plan_validity_days') == 'limited' ? 'selected' : '' }}>Fixed Days</option>
                            </select>
                            <div class="mt-2 expiry-limit-div {{ old('plan_validity_days') == 'limited' ? '' : 'd-none' }}">
                                <input type="number" name="plan_validity" class="form-control form-control-custom" placeholder="Enter number of days (e.g. 365)" value="{{ old('plan_validity', 365) }}">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Plan Status</label>
                            <div class="prof-option-box">
                                <div>
                                    <p class="opt-title">Enable Plan</p>
                                    <p class="opt-sub">Allow users to view and purchase this plan</p>
                                </div>
                                <label class="switch-label">
                                    <input type="checkbox" name="status" id="statusSwitch" {{ old('status', true) ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label-custom">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control form-control-custom" rows="2" placeholder="Short description of this plan...">{{ old('description', 'Choose a plan to unlock premium features and grow your business.') }}</textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Plan Image</label>
                            <input type="file" name="image" class="form-control-file" accept="image/*" onchange="previewImg(this)">
                            <div id="img_preview" class="mt-2 d-none">
                                <img id="preview_src" src="#" alt="Preview" style="height:50px; border-radius:6px; border:1px solid #cbd5e1;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Business Categories & Free Orders Quotas (Admin Blue Theme) -->
            @php
                $fixedCategories = [
                    'Cab Driver',
                    'Bike Taxi',
                    'Auto Rickshaw',
                    'Delivery Partner',
                    'Home Services',
                    'Food Delivery'
                ];
            @endphp

            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-shape-outline"></i></div>
                        <h5>Business Categories & Free Orders</h5>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold" onclick="applyToAllCategories(true)">
                        Apply To All
                    </button>
                </div>
                <div class="section-body">
                    <!-- Search Input -->
                    <div class="mb-3">
                        <div class="input-group" style="max-width: 450px;">
                            <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span></div>
                            <input type="text" id="categorySearchInput" class="form-control form-control-custom border-left-0" placeholder="Select Categories (Multiple allowed)">
                        </div>
                    </div>

                    <!-- Category Pills (Admin Blue Theme) -->
                    <div class="d-flex flex-wrap align-items-center mb-4" id="categoryPillContainer">
                        @foreach($fixedCategories as $cat)
                        <div class="category-pill active" onclick="toggleCategoryPill(this, '{{ $cat }}')">
                            <span class="check-box-icon"><i class="fa fa-check"></i></span>
                            <input type="checkbox" name="categories[]" value="{{ $cat }}" checked class="category-checkbox" style="display:none;">
                            <span>{{ $cat }}</span>
                        </div>
                        @endforeach
                    </div>

                    <!-- Free Orders / Rides Per Category Settings -->
                    <div class="border-top pt-3">
                        <label class="form-label-custom mb-2"><i class="mdi mdi-ticket-percent text-primary mr-1"></i>Free Orders / Rides Limit per Category (When User Buys Plan)</label>
                        <p class="text-muted small mb-3">Set how many free bookings/orders a provider gets for each category under this plan:</p>

                        <div class="row" id="freeOrdersContainer">
                            @foreach($fixedCategories as $cat)
                            <div class="col-md-6 col-lg-4 mb-2 category-free-order-item" id="free_order_item_{{ Str::slug($cat) }}">
                                <div class="category-free-order-row">
                                    <span class="font-weight-bold text-dark small"><i class="mdi mdi-check-circle text-primary mr-1"></i>{{ $cat }}</span>
                                    <div class="input-group input-group-sm" style="width:130px;">
                                        <input type="number" name="category_free_orders[{{ $cat }}]" class="form-control form-control-custom text-center font-weight-bold" min="0" value="150" placeholder="Orders">
                                        <div class="input-group-append"><span class="input-group-text small">free</span></div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Service Discount & Interest-Free Loan Cards (Admin Blue Theme) -->
            <div class="row">
                <!-- Service Discount Card -->
                <div class="col-md-6 mb-3">
                    <div class="section-card h-100">
                        <div class="section-header">
                            <div class="header-left">
                                <div class="icon-badge"><i class="mdi mdi-percent-outline"></i></div>
                                <h5>Service Discount</h5>
                            </div>
                            <label class="switch-label">
                                <input type="checkbox" name="service_discount_enabled" id="serviceDiscountSwitch" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                        <div class="section-body">
                            <p class="text-muted small mb-3">Discount percentage applied to bookings under each service:</p>

                            <div class="discount-item-row">
                                <label>Home Services</label>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <input type="number" name="discount_home_service" class="form-control form-control-custom text-center font-weight-bold" min="0" max="100" value="{{ old('discount_home_service', 20) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>

                            <div class="discount-item-row">
                                <label>Travel</label>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <input type="number" name="discount_travel" class="form-control form-control-custom text-center font-weight-bold" min="0" max="100" value="{{ old('discount_travel', 15) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>

                            <div class="discount-item-row">
                                <label>Hotels</label>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <input type="number" name="discount_hotel" class="form-control form-control-custom text-center font-weight-bold" min="0" max="100" value="{{ old('discount_hotel', 10) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>

                            <div class="discount-item-row">
                                <label>Food</label>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <input type="number" name="discount_food" class="form-control form-control-custom text-center font-weight-bold" min="0" max="100" value="{{ old('discount_food', 25) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>

                            <div class="discount-item-row">
                                <label>Medical</label>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <input type="number" name="discount_medical" class="form-control form-control-custom text-center font-weight-bold" min="0" max="100" value="{{ old('discount_medical', 12) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>

                            <div class="discount-item-row">
                                <label>Marketplace</label>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <input type="number" name="discount_marketplace" class="form-control form-control-custom text-center font-weight-bold" min="0" max="100" value="{{ old('discount_marketplace', 18) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>

                            <div class="discount-item-row">
                                <label>Transaction</label>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <input type="number" name="discount_transaction" class="form-control form-control-custom text-center font-weight-bold" min="0" max="100" value="{{ old('discount_transaction', 10) }}">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Interest-Free Loan Card -->
                <div class="col-md-6 mb-3">
                    <div class="section-card h-100">
                        <div class="section-header">
                            <div class="header-left">
                                <div class="icon-badge"><i class="mdi mdi-cash-multiple"></i></div>
                                <h5>Interest-Free Loan</h5>
                            </div>
                            <label class="switch-label">
                                <input type="checkbox" name="loan_enabled" id="interestFreeLoanSwitch" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                        <div class="section-body">
                            <p class="text-muted small mb-4">Set eligibility loan amounts for providers on this plan:</p>

                            <div class="mb-4">
                                <label class="form-label-custom">Base Loan Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                    <input type="number" name="loan_min_amount" class="form-control form-control-custom font-weight-bold" placeholder="50000" value="{{ old('loan_min_amount', 50000) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom">Maximum Loan Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                    <input type="number" name="loan_max_amount" class="form-control form-control-custom font-weight-bold" placeholder="500000" value="{{ old('loan_max_amount', 500000) }}">
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded mt-4">
                                <p class="small text-muted mb-0"><i class="fa fa-info-circle text-primary mr-1"></i>Providers subscribing to this plan will be eligible for up to ₹5,00,000 interest-free business loan access.</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Plan Features (Bullet Points) -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-checkbox-marked-circle-outline"></i></div>
                        <h5>Plan Key Benefits (Bullet Points)</h5>
                    </div>
                </div>
                <div class="section-body">
                    <div id="points_container">
                        @php
                            $defaultBenefits = [
                                'Business Verified Batch',
                                'Premium Listing (Appears at top)',
                                'QR Pay Send & Receive (Benefit up to 2%)',
                                'Daily Value Increment (Up to 2%)',
                                'Free Incoming Bookings (150)',
                                'Interest-Free Loan Eligibility (Up to ₹5 Lakh)',
                                'Value Transfer Cashback (Up to 2%)',
                                'Wallet Enabled',
                                'Professional Dashboard',
                                'Priority Support'
                            ];
                        @endphp
                        @if(old('plan_points'))
                            @foreach(old('plan_points') as $pt)
                            <div class="point-row d-flex align-items-center gap-2">
                                <i class="mdi mdi-check text-primary mr-2"></i>
                                <input type="text" class="form-control form-control-custom border-0 bg-transparent" name="plan_points[]" value="{{ $pt }}">
                                <button type="button" class="btn btn-sm text-danger ml-auto" onclick="removePoint(this)"><i class="fa fa-trash"></i></button>
                            </div>
                            @endforeach
                        @else
                            @foreach($defaultBenefits as $benefit)
                            <div class="point-row d-flex align-items-center gap-2">
                                <i class="mdi mdi-check text-primary mr-2"></i>
                                <input type="text" class="form-control form-control-custom border-0 bg-transparent" name="plan_points[]" value="{{ $benefit }}">
                                <button type="button" class="btn btn-sm text-danger ml-auto" onclick="removePoint(this)"><i class="fa fa-trash"></i></button>
                            </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" onclick="addPoint()" class="btn btn-sm btn-light-custom mt-2">
                        <i class="fa fa-plus mr-1"></i> Add Key Benefit
                    </button>
                </div>
            </div>

            <!-- Section 5: Cashback & Wallet Settings -->
            <div class="section-card">
                <div class="section-header">
                    <div class="header-left">
                        <div class="icon-badge"><i class="mdi mdi-cash-refund"></i></div>
                        <h5>Cashback & Wallet Settings</h5>
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
                                            <option value="percentage" {{ old('sender_cashback_type','percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                            <option value="flat" {{ old('sender_cashback_type') === 'flat' ? 'selected' : '' }}>Flat (₹)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted mb-1">Value</label>
                                        <input type="number" name="sender_cashback_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('sender_cashback_value', 2) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light border rounded">
                                <span class="form-label-custom mb-2"><i class="mdi mdi-arrow-down-bold-circle text-primary mr-1"></i>Receiver Cashback</span>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small text-muted mb-1">Type</label>
                                        <select name="receiver_cashback_type" class="form-control form-control-custom">
                                            <option value="percentage" {{ old('receiver_cashback_type','percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                            <option value="flat" {{ old('receiver_cashback_type') === 'flat' ? 'selected' : '' }}>Flat (₹)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted mb-1">Value</label>
                                        <input type="number" name="receiver_cashback_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('receiver_cashback_value', 2) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="d-flex align-items-center gap-3 mb-5">
                <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save mr-1"></i> Save Business Plan</button>
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

    // Category search filter
    $('#categorySearchInput').on('keyup', function() {
        const term = $(this).val().toLowerCase();
        $('.category-pill').each(function() {
            const text = $(this).text().toLowerCase();
            if (text.includes(term)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});

function toggleCategoryPill(element, catName) {
    const checkbox = $(element).find('.category-checkbox');
    const isChecked = checkbox.prop('checked');
    checkbox.prop('checked', !isChecked);

    const safeId = 'free_order_item_' + catName.toLowerCase().replace(/[^a-z0-9]/g, '-');

    if (!isChecked) {
        $(element).addClass('active');
        $(element).find('.check-box-icon').html('<i class="fa fa-check"></i>');
        $('#' + safeId).slideDown(150);
    } else {
        $(element).removeClass('active');
        $(element).find('.check-box-icon').html('');
        $('#' + safeId).slideUp(150);
    }
}

function applyToAllCategories(select) {
    $('.category-pill').addClass('active');
    $('.category-checkbox').prop('checked', true);
    $('.category-pill .check-box-icon').html('<i class="fa fa-check"></i>');
    $('.category-free-order-item').slideDown(150);
}

function addPoint() {
    const html = `
    <div class="point-row d-flex align-items-center gap-2">
        <i class="mdi mdi-check text-primary mr-2"></i>
        <input type="text" class="form-control form-control-custom border-0 bg-transparent" name="plan_points[]" placeholder="Enter key benefit text...">
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
</script>
@endsection

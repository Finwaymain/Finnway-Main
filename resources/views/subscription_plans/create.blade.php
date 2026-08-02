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

/* Tier Selector Cards */
.tier-radio-input {
    position: absolute !important;
    opacity: 0 !important;
    width: 0 !important;
    height: 0 !important;
    pointer-events: none !important;
    margin: 0 !important;
    padding: 0 !important;
}
.tier-select-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 16px;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s ease;
    margin-bottom: 0;
}
.tier-select-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.tier-select-card .tier-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #1e293b;
    margin-bottom: 2px;
}
.tier-select-card .tier-sub {
    font-size: 0.78rem;
    color: #64748b;
}
.tier-select-card i {
    font-size: 1.6rem;
    color: #94a3b8;
    margin-bottom: 8px;
    display: block;
}
.tier-radio-input:checked + .tier-select-card {
    background: #f0f6ff;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37,99,235,0.12);
}
.tier-radio-input:checked + .tier-select-card .tier-title { color: #1d4ed8; }
.tier-radio-input:checked + .tier-select-card i { color: #2563eb; }

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
</style>

<div class="page-wrapper">
    <div class="container-fluid pt-3">

        <!-- Header -->
        <div class="page-header-card d-flex align-items-center justify-content-between">
            <div>
                <h3><i class="mdi mdi-briefcase-plus text-primary mr-2"></i>Create Business Plan</h3>
                <p>Configure subscription details, benefits, cashback and limits for providers.</p>
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

            <!-- Section 1: Plan Tier -->
            <div class="section-card">
                <div class="section-header">
                    <div class="icon-badge"><i class="mdi mdi-layers-outline"></i></div>
                    <h5>Plan Tier</h5>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label-custom">Select Plan Tier <span class="text-danger">*</span></label>
                            <select name="plan_tier" class="form-control form-control-custom">
                                <option value="basic" {{ old('plan_tier','basic') === 'basic' ? 'selected' : '' }}>Basic — Starter business features</option>
                                <option value="professional" {{ old('plan_tier') === 'professional' ? 'selected' : '' }}>Professional — Standard features & limits</option>
                                <option value="premium_plus" {{ old('plan_tier') === 'premium_plus' ? 'selected' : '' }}>Premium Plus — Maximum benefits & quotas</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Basic Details -->
            <div class="section-card">
                <div class="section-header">
                    <div class="icon-badge"><i class="mdi mdi-information-outline"></i></div>
                    <h5>Basic Information</h5>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="planName" class="form-control form-control-custom" placeholder="e.g. Premium Monthly" value="{{ old('planName') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Plan Type <span class="text-danger">*</span></label>
                            <select name="planType" id="planTypeSelect" class="form-control form-control-custom">
                                <option value="free" {{ old('planType','free') == 'free' ? 'selected' : '' }}>Free Plan</option>
                                <option value="paid" {{ old('planType') == 'paid' ? 'selected' : '' }}>Paid Plan</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 plan_price_div {{ old('planType','free') == 'paid' ? '' : 'd-none' }}">
                            <label class="form-label-custom">Plan Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                <input type="number" name="planPrice" class="form-control form-control-custom" placeholder="0.00" min="0" step="0.01" value="{{ old('planPrice') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Validity Period</label>
                            <select name="plan_validity_days" id="validityTypeSelect" class="form-control form-control-custom">
                                <option value="unlimited" {{ old('plan_validity_days','unlimited') == 'unlimited' ? 'selected' : '' }}>Unlimited / Lifetime</option>
                                <option value="limited" {{ old('plan_validity_days') == 'limited' ? 'selected' : '' }}>Fixed Days</option>
                            </select>
                            <div class="mt-2 expiry-limit-div {{ old('plan_validity_days') == 'limited' ? '' : 'd-none' }}">
                                <input type="number" name="plan_validity" class="form-control form-control-custom" placeholder="Enter number of days (e.g. 30)" value="{{ old('plan_validity') }}">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Display Order <span class="text-danger">*</span></label>
                            <input type="number" name="order" class="form-control form-control-custom" placeholder="1" min="0" value="{{ old('order', 1) }}" required>
                        </div>
                        <div class="col-md-5 mb-3">
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
                            <textarea name="description" class="form-control form-control-custom" rows="2" placeholder="Short description of this plan...">{{ old('description') }}</textarea>
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

            <!-- Section 3: Feature Highlights -->
            <div class="section-card">
                <div class="section-header">
                    <div class="icon-badge"><i class="mdi mdi-checkbox-marked-circle-outline"></i></div>
                    <h5>Plan Features (Bullet Points)</h5>
                </div>
                <div class="section-body">
                    <div id="points_container">
                        @if(old('plan_points'))
                            @foreach(old('plan_points') as $pt)
                            <div class="point-row d-flex align-items-center gap-2">
                                <i class="mdi mdi-check text-success mr-2"></i>
                                <input type="text" class="form-control form-control-custom border-0 bg-transparent" name="plan_points[]" value="{{ $pt }}">
                                <button type="button" class="btn btn-sm text-danger ml-auto" onclick="removePoint(this)"><i class="fa fa-trash"></i></button>
                            </div>
                            @endforeach
                        @else
                        <div class="point-row d-flex align-items-center gap-2">
                            <i class="mdi mdi-check text-success mr-2"></i>
                            <input type="text" class="form-control form-control-custom border-0 bg-transparent" name="plan_points[]" placeholder="e.g. Priority order access">
                            <button type="button" class="btn btn-sm text-danger ml-auto" onclick="removePoint(this)"><i class="fa fa-trash"></i></button>
                        </div>
                        @endif
                    </div>
                    <button type="button" onclick="addPoint()" class="btn btn-sm btn-light-custom mt-2">
                        <i class="fa fa-plus mr-1"></i> Add Feature
                    </button>
                </div>
            </div>

            <!-- Section 4: Booking Limits & Quotas -->
            <div class="section-card">
                <div class="section-header">
                    <div class="icon-badge"><i class="mdi mdi-calendar-clock"></i></div>
                    <h5>Booking Limits & Free Quotas</h5>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Booking Limit</label>
                            <select name="set_booking_limit" id="bookingLimitSelect" class="form-control form-control-custom">
                                <option value="unlimited" {{ old('set_booking_limit','unlimited') == 'unlimited' ? 'selected' : '' }}>Unlimited Bookings</option>
                                <option value="limited" {{ old('set_booking_limit') == 'limited' ? 'selected' : '' }}>Limited Bookings</option>
                            </select>
                            <div class="mt-2 booking-limit-div {{ old('set_booking_limit') == 'limited' ? '' : 'd-none' }}">
                                <input type="number" name="booking_limit" class="form-control form-control-custom" placeholder="Max orders (e.g. 100)" value="{{ old('booking_limit') }}">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Free Ride Limit</label>
                            <input type="number" name="free_ride_limit" class="form-control form-control-custom" min="0" placeholder="0" value="{{ old('free_ride_limit', 0) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label-custom">Free Ride Reset</label>
                            <select name="free_ride_reset" class="form-control form-control-custom">
                                <option value="monthly" {{ old('free_ride_reset','monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('free_ride_reset') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('free_ride_reset') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Cashback Configuration -->
            <div class="section-card">
                <div class="section-header">
                    <div class="icon-badge"><i class="mdi mdi-cash-refund"></i></div>
                    <h5>Cashback Settings</h5>
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
                                        <input type="number" name="sender_cashback_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('sender_cashback_value', 0) }}">
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
                                            <option value="percentage" {{ old('receiver_cashback_type','percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                            <option value="flat" {{ old('receiver_cashback_type') === 'flat' ? 'selected' : '' }}>Flat (₹)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted mb-1">Value</label>
                                        <input type="number" name="receiver_cashback_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('receiver_cashback_value', 0) }}">
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
                    <div class="icon-badge"><i class="mdi mdi-percent-outline"></i></div>
                    <h5>Category Discounts (%)</h5>
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
                                    <input type="number" name="{{ $field }}" class="form-control form-control-custom text-center font-weight-bold" min="0" max="100" step="0.1" value="{{ old($field, 0) }}">
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
                    <div class="icon-badge"><i class="mdi mdi-wallet-outline"></i></div>
                    <h5>Wallet & Loan Privileges</h5>
                </div>
                <div class="section-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Wallet Top-Up Bonus</label>
                            <input type="number" name="wallet_increment_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('wallet_increment_value', 0) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Bonus Period</label>
                            <select name="wallet_increment_period" class="form-control form-control-custom">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Referral Bonus Type</label>
                            <select name="referral_bonus_type" class="form-control form-control-custom">
                                <option value="flat">Flat (₹)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label-custom">Referral Bonus Value</label>
                            <input type="number" name="referral_bonus_value" class="form-control form-control-custom" min="0" step="0.01" value="{{ old('referral_bonus_value', 0) }}">
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
                                        <input type="checkbox" name="loan_enabled" id="loanSwitch" {{ old('loan_enabled') ? 'checked' : '' }}>
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Max Loan Amount (₹)</label>
                                <input type="number" name="loan_max_amount" class="form-control form-control-custom" min="0" value="{{ old('loan_max_amount', 0) }}">
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
</script>
@endsection

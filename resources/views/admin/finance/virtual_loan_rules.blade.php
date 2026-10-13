@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1200px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('admin.finance.dashboard') }}" style="color: #64748b; font-size: 13px; text-decoration: none;">
                        ← Finance Overview
                    </a>
                    <span style="color: #cbd5e1;">/</span>
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        APP TO APP VIRTUAL LOAN RULES
                    </span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Virtual Loan Spending &amp; Category Rules
                </h3>
                <div style="font-size: 13px; color: #64748b; margin-top: 3px;">
                    Configure in-app category permissions, merchant limits, daily repayments, and slab fees per the App-to-App specification.
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.finance.applications') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Loan Applications
                </a>
                <a href="{{ route('admin.finance.settings.payment') }}" class="btn btn-sm" style="background: #0f172a; color: #ffffff; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px;">
                    💳 Razorpay Settings
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 py-3 px-4 mb-4" style="border-radius: 8px; background: #ecfdf5; color: #065f46; font-size: 13px; font-weight: 600; border-left: 4px solid #059669 !important;" role="alert">
            <strong>✓ Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 10px 14px; color: #065f46;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.finance.virtual_loan_rules.save') }}">
            @csrf

            <div class="row g-4">
                <!-- Column 1: Categories & Spending Limits -->
                <div class="col-lg-6">
                    <!-- In-App Category Restrictions -->
                    <div class="card border-0 mb-4 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                            <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">
                                🏪 Eligible In-App Service Categories
                            </h5>
                            <p class="text-muted mb-0" style="font-size: 12px;">Virtual credit can only be used for the selected service categories.</p>
                        </div>
                        <div class="card-body p-4">
                            @php
                                $allowedCats = (array) ($rules['allowed_categories'] ?? ['food', 'ride', 'parcel', 'marketplace']);
                            @endphp

                            <div class="space-y-3">
                                <div class="form-check p-3 mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; gap: 12px;">
                                    <input class="form-check-input" type="checkbox" name="allowed_categories[]" value="food" id="cat_food" {{ in_array('food', $allowedCats) ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #2563eb; margin: 0;">
                                    <label class="form-check-label mb-0" for="cat_food" style="font-weight: 600; font-size: 13px; color: #1e293b; cursor: pointer;">
                                        🍔 Food &amp; Restaurant Orders (Fiinway Food)
                                        <div style="font-size: 11px; font-weight: 400; color: #64748b;">Allow customers to order from food merchants using virtual credit</div>
                                    </label>
                                </div>

                                <div class="form-check p-3 mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; gap: 12px;">
                                    <input class="form-check-input" type="checkbox" name="allowed_categories[]" value="ride" id="cat_ride" {{ in_array('ride', $allowedCats) ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #2563eb; margin: 0;">
                                    <label class="form-check-label mb-0" for="cat_ride" style="font-weight: 600; font-size: 13px; color: #1e293b; cursor: pointer;">
                                        🚖 Cab &amp; Taxi Rides
                                        <div style="font-size: 11px; font-weight: 400; color: #64748b;">Allow driver trip fare payments via virtual loan balance</div>
                                    </label>
                                </div>

                                <div class="form-check p-3 mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; gap: 12px;">
                                    <input class="form-check-input" type="checkbox" name="allowed_categories[]" value="parcel" id="cat_parcel" {{ in_array('parcel', $allowedCats) ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #2563eb; margin: 0;">
                                    <label class="form-check-label mb-0" for="cat_parcel" style="font-weight: 600; font-size: 13px; color: #1e293b; cursor: pointer;">
                                        📦 Parcel &amp; Courier Deliveries
                                        <div style="font-size: 11px; font-weight: 400; color: #64748b;">Allow courier booking payments using virtual credit balance</div>
                                    </label>
                                </div>

                                <div class="form-check p-3 mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; gap: 12px;">
                                    <input class="form-check-input" type="checkbox" name="allowed_categories[]" value="marketplace" id="cat_market" {{ in_array('marketplace', $allowedCats) ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #2563eb; margin: 0;">
                                    <label class="form-check-label mb-0" for="cat_market" style="font-weight: 600; font-size: 13px; color: #1e293b; cursor: pointer;">
                                        🛍️ Marketplace &amp; Retail Shopping
                                        <div style="font-size: 11px; font-weight: 400; color: #64748b;">Allow local marketplace merchant purchases</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Per-Merchant & Spending Limits -->
                    <div class="card border-0 mb-4 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                            <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">
                                🛡️ Merchant &amp; Spending Limits
                            </h5>
                            <p class="text-muted mb-0" style="font-size: 12px;">Safeguards per merchant and daily limits to prevent credit abuse.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label style="font-size: 12px; font-weight: 700; color: #334155;">
                                    Per-Merchant Maximum Amount (₹)
                                </label>
                                <input type="number" step="0.01" name="max_per_merchant_amount" value="{{ $rules['max_per_merchant_amount'] }}" class="form-control" style="font-size: 13px; font-weight: 600;" required>
                                <small class="text-muted">Maximum limit a customer can spend at a single business per transaction (Default: ₹2,000).</small>
                            </div>

                            <div class="mb-3">
                                <label style="font-size: 12px; font-weight: 700; color: #334155;">
                                    Monthly Transactions per Merchant Limit
                                </label>
                                <input type="number" name="max_monthly_tx_per_merchant" value="{{ $rules['max_monthly_tx_per_merchant'] }}" class="form-control" style="font-size: 13px; font-weight: 600;" required>
                                <small class="text-muted">Max number of transactions allowed at the same merchant in a month (Default: 5).</small>
                            </div>

                            <div class="mb-3">
                                <label style="font-size: 12px; font-weight: 700; color: #334155;">
                                    Daily Overall Spend Limit (₹)
                                </label>
                                <input type="number" step="0.01" name="daily_transaction_limit" value="{{ $rules['daily_transaction_limit'] }}" class="form-control" style="font-size: 13px; font-weight: 600;" required>
                                <small class="text-muted">Maximum combined amount a customer can spend across all businesses per day (Default: ₹5,000).</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Recovery, Slabs & Verification -->
                <div class="col-lg-6">
                    <!-- Verification Mode -->
                    <div class="card border-0 mb-4 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                            <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">
                                ⚙️ Verification &amp; Activation Mode
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="p-3 mb-2" style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                                <div class="form-check form-switch d-flex align-items-center justify-content-between p-0 mb-0">
                                    <label class="form-check-label mb-0" for="auto_verify_switch" style="cursor: pointer;">
                                        <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Automatic Verification upon Fee Payment</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                            When <strong>ON</strong>: Loan wallet activates automatically as soon as the processing fee is paid.<br>
                                            When <strong>OFF</strong>: Admin must manually verify documents in Admin Panel before credit is unlocked.
                                        </div>
                                    </label>
                                    <input class="form-check-input ms-3" type="checkbox" name="auto_verify" id="auto_verify_switch" value="1" {{ $rules['auto_verify'] ? 'checked' : '' }} style="width: 44px; height: 22px; cursor: pointer; accent-color: #16a34a;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Repayment, Grace & Late Fees -->
                    <div class="card border-0 mb-4 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                            <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">
                                💳 Daily Repayment &amp; Defaulter Rules
                            </h5>
                            <p class="text-muted mb-0" style="font-size: 12px;">Configure daily EMI collection and penalty calculation.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label style="font-size: 12px; font-weight: 700; color: #334155;">Daily Repayment Amount (₹)</label>
                                    <input type="number" step="0.01" name="daily_repayment_amount" value="{{ $rules['daily_repayment_amount'] }}" class="form-control" style="font-size: 13px;" required>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size: 12px; font-weight: 700; color: #334155;">Grace Period (Days)</label>
                                    <input type="number" name="grace_period_days" value="{{ $rules['grace_period_days'] }}" class="form-control" style="font-size: 13px;" required>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size: 12px; font-weight: 700; color: #334155;">Late Fee per Missed Day (₹)</label>
                                    <input type="number" step="0.01" name="late_fee" value="{{ $rules['late_fee'] }}" class="form-control" style="font-size: 13px;" required>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size: 12px; font-weight: 700; color: #334155;">Penalty / Interest Rate (%)</label>
                                    <input type="number" step="0.01" name="penalty_rate" value="{{ $rules['penalty_rate'] }}" class="form-control" style="font-size: 13px;" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Processing Fee per Slab -->
                    <div class="card border-0 mb-4 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                            <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">
                                📋 Slab-Wise Processing Fees (₹)
                            </h5>
                            <p class="text-muted mb-0" style="font-size: 12px;">Admin-customizable fee for each approved loan slab.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" style="font-size: 13px;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Loan Amount Slab</th>
                                            <th>One-Time Processing Fee (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>₹15,000</strong></td>
                                            <td><input type="number" step="0.01" name="fee_15000" value="{{ $rules['slab_fees'][15000] ?? 2000 }}" class="form-control form-control-sm" style="font-weight: 600;"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>₹20,000</strong></td>
                                            <td><input type="number" step="0.01" name="fee_20000" value="{{ $rules['slab_fees'][20000] ?? 3000 }}" class="form-control form-control-sm" style="font-weight: 600;"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>₹30,000</strong></td>
                                            <td><input type="number" step="0.01" name="fee_30000" value="{{ $rules['slab_fees'][30000] ?? 3000 }}" class="form-control form-control-sm" style="font-weight: 600;"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>₹35,000</strong></td>
                                            <td><input type="number" step="0.01" name="fee_35000" value="{{ $rules['slab_fees'][35000] ?? 4000 }}" class="form-control form-control-sm" style="font-weight: 600;"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>₹45,000</strong></td>
                                            <td><input type="number" step="0.01" name="fee_45000" value="{{ $rules['slab_fees'][45000] ?? 4000 }}" class="form-control form-control-sm" style="font-weight: 600;"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Save Bar -->
            <div class="p-3 bg-white border rounded-3 shadow-sm d-flex justify-content-end align-items-center gap-3">
                <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-outline-secondary px-4" style="font-size: 13px; font-weight: 600;">Cancel</a>
                <button type="submit" class="btn btn-primary px-5" style="background: #0f172a; border-color: #0f172a; font-size: 13px; font-weight: 700; padding: 10px 24px;">
                    💾 Save Virtual Loan Rules
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

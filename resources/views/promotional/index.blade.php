@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="container-fluid" style="padding-top: 24px; padding-bottom: 60px;">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 16px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                    <div class="card-body p-4 text-white">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge {{ $config->status === 'active' ? 'bg-success' : 'bg-danger' }} px-3 py-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px; border-radius: 30px;">
                                        ● System {{ strtoupper($config->status) }}
                                    </span>
                                    <span class="badge bg-white text-dark px-3 py-1 font-weight-bold" style="font-size: 11px; border-radius: 30px;">
                                        Non-Cash Service Discounts
                                    </span>
                                </div>
                                <h2 class="font-weight-bold mb-1 text-white" style="font-size: 26px;">
                                    🎁 Promotional & Welcome Bonus Engine
                                </h2>
                                <p class="text-white-50 mb-0" style="font-size: 14px;">
                                    Configure independent welcome bonus credits, per-service discounts, validity, and bill thresholds for Tier 1 (Referral) and Tier 2 (Direct Join).
                                </p>
                            </div>
                            <div class="text-right d-flex gap-4">
                                <div class="pr-3 border-right border-secondary">
                                    <span class="d-block text-white-50" style="font-size: 12px;">Tier 1 (With Code)</span>
                                    <span class="text-warning font-weight-bold" style="font-size: 20px;">₹{{ number_format($config->bonus_with_code ?? 300, 0) }}</span>
                                    <small class="d-block text-white-50">₹{{ number_format($config->discount_per_service_with_code ?? 50, 0) }}/service ({{ $config->uses_with_code ?? 6 }}x)</small>
                                </div>
                                <div>
                                    <span class="d-block text-white-50" style="font-size: 12px;">Tier 2 (Direct Join)</span>
                                    <span class="text-info font-weight-bold" style="font-size: 20px;">₹{{ number_format($config->bonus_without_code ?? 150, 0) }}</span>
                                    <small class="d-block text-white-50">₹{{ number_format($config->discount_per_service_without_code ?? 50, 0) }}/service ({{ $config->uses_without_code ?? 3 }}x)</small>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius: 12px; background: #ecfdf5; color: #065f46;" role="alert">
            <strong>✓ Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" style="border-radius: 12px; background: #fef2f2; color: #991b1b;" role="alert">
            <strong>⚠ Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <!-- Key Metrics Cards -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
                    <div class="card-body p-3">
                        <span class="text-muted text-uppercase font-weight-bold" style="font-size: 11px;">Total Promo Users</span>
                        <h3 class="font-weight-bold mt-2 mb-0 text-dark">{{ number_format($stats['total_promo_users']) }}</h3>
                        <small class="text-success font-weight-bold">Active: {{ number_format($stats['active_promo_users']) }}</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
                    <div class="card-body p-3">
                        <span class="text-muted text-uppercase font-weight-bold" style="font-size: 11px;">Bonus Granted</span>
                        <h3 class="font-weight-bold mt-2 mb-0 text-primary">₹{{ number_format($stats['total_bonus_granted'], 0) }}</h3>
                        <small class="text-muted">Allocated credits</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
                    <div class="card-body p-3">
                        <span class="text-muted text-uppercase font-weight-bold" style="font-size: 11px;">Active Bonus Pool</span>
                        <h3 class="font-weight-bold mt-2 mb-0 text-warning">₹{{ number_format($stats['total_bonus_remaining'], 0) }}</h3>
                        <small class="text-muted">Unused credits</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
                    <div class="card-body p-3">
                        <span class="text-muted text-uppercase font-weight-bold" style="font-size: 11px;">Total Discounts Given</span>
                        <h3 class="font-weight-bold mt-2 mb-0 text-success">₹{{ number_format($stats['total_discount_availed'], 2) }}</h3>
                        <small class="text-muted">Redeemed across services</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
                    <div class="card-body p-3">
                        <span class="text-muted text-uppercase font-weight-bold" style="font-size: 11px;">Redeemed Services</span>
                        <h3 class="font-weight-bold mt-2 mb-0 text-info">{{ number_format($stats['total_uses_redeemed']) }}</h3>
                        <small class="text-muted">Successful discount bookings</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Master Configuration Form (Full Width — Tier 1 & Tier 2 completely separated) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-header bg-white border-bottom py-3 px-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="font-weight-bold mb-0 text-dark">
                                    ⚙️ Master Promotional Configuration
                                </h5>
                                <small class="text-muted">Independent configurations for Referral Users (Tier 1) and Direct Join Users (Tier 2).</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('promotional.update') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- ==================== TIER 1: WITH REFERRAL CODE ==================== -->
                                <div class="col-lg-6 mb-4">
                                    <div class="h-100 p-4 rounded" style="background: #f8fafc; border: 2px solid #e2e8f0; border-top: 4px solid #3b82f6;">
                                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary text-white mr-2 px-3 py-1 font-weight-bold" style="font-size: 12px; border-radius: 20px;">Tier 1</span>
                                                <h5 class="font-weight-bold mb-0 text-dark">Join With Reference / Referral Code</h5>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-3" style="font-size: 12px;">Applies to users who enter a valid referral code during onboarding or registration.</p>

                                        <!-- Bonus & Uses -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Welcome Bonus Amount (₹)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text bg-white font-weight-bold">₹</span></div>
                                                    <input type="number" step="0.01" name="bonus_with_code" class="form-control font-weight-bold text-primary" value="{{ old('bonus_with_code', $config->bonus_with_code ?? 300.00) }}" required>
                                                </div>
                                                <small class="text-muted">Default: ₹300.00</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Total Service Uses</label>
                                                <input type="number" name="uses_with_code" class="form-control font-weight-bold" value="{{ old('uses_with_code', $config->uses_with_code ?? 6) }}" required>
                                                <small class="text-muted">Default: 6 uses (e.g. ₹300 ÷ ₹50 = 6)</small>
                                            </div>
                                        </div>

                                        <!-- Discount & Validity -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Discount Per Service (₹)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text bg-white font-weight-bold">₹</span></div>
                                                    <input type="number" step="0.01" name="discount_per_service_with_code" class="form-control font-weight-bold text-success" value="{{ old('discount_per_service_with_code', $config->discount_per_service_with_code ?? $config->discount_per_service ?? 50.00) }}" required>
                                                </div>
                                                <small class="text-muted">Default: ₹50.00</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Expiry (Days from Join)</label>
                                                <input type="number" name="expiry_days_with_code" class="form-control" value="{{ old('expiry_days_with_code', $config->expiry_days_with_code ?? $config->expiry_days ?? 30) }}" required>
                                                <small class="text-muted">Default: 30 days</small>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Fixed Expiry Date (Optional)</label>
                                                <input type="date" name="custom_expiry_date_with_code" class="form-control" value="{{ old('custom_expiry_date_with_code', $config->custom_expiry_date_with_code ?? $config->custom_expiry_date) }}">
                                                <small class="text-muted">Overrides days if set (dd-mm-yyyy)</small>
                                            </div>
                                        </div>

                                        <!-- Bill Thresholds -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Min Bill Amount (₹)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                                    <input type="number" step="0.01" name="min_bill_with_code" class="form-control" value="{{ old('min_bill_with_code', $config->min_bill_with_code ?? $config->min_bill_amount ?? 50.00) }}" required>
                                                </div>
                                                <small class="text-muted">Default: ₹50.00</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Max Bill Amount (₹)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                                    <input type="number" step="0.01" name="max_bill_with_code" class="form-control" value="{{ old('max_bill_with_code', $config->max_bill_with_code ?? $config->max_bill_amount ?? 100000.00) }}" required>
                                                </div>
                                                <small class="text-muted">Default: ₹100000.00</small>
                                            </div>
                                        </div>

                                        <!-- Roles -->
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Applicable User Roles</label>
                                                @php $roleTier1 = old('applicable_roles_with_code', $config->applicable_roles_with_code ?? $config->applicable_roles ?? 'all'); @endphp
                                                <select name="applicable_roles_with_code" class="form-control">
                                                    <option value="all" {{ $roleTier1 === 'all' ? 'selected' : '' }}>All Users (Consumer & Drivers)</option>
                                                    <option value="customer" {{ $roleTier1 === 'customer' ? 'selected' : '' }}>Consumers Only</option>
                                                    <option value="driver" {{ $roleTier1 === 'driver' ? 'selected' : '' }}>Drivers / Business Only</option>
                                                </select>
                                                <small class="text-muted">Who is eligible for Tier 1 Welcome Bonus</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ==================== TIER 2: DIRECT JOIN (NO CODE) ==================== -->
                                <div class="col-lg-6 mb-4">
                                    <div class="h-100 p-4 rounded" style="background: #f8fafc; border: 2px solid #e2e8f0; border-top: 4px solid #06b6d4;">
                                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-info text-white mr-2 px-3 py-1 font-weight-bold" style="font-size: 12px; border-radius: 20px;">Tier 2</span>
                                                <h5 class="font-weight-bold mb-0 text-dark">Direct Join Without Reference Code</h5>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-3" style="font-size: 12px;">Applies to users who register directly without any referral or invite code.</p>

                                        <!-- Bonus & Uses -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Welcome Bonus Amount (₹)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text bg-white font-weight-bold">₹</span></div>
                                                    <input type="number" step="0.01" name="bonus_without_code" class="form-control font-weight-bold text-info" value="{{ old('bonus_without_code', $config->bonus_without_code ?? 150.00) }}" required>
                                                </div>
                                                <small class="text-muted">Default: ₹150.00</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Total Service Uses</label>
                                                <input type="number" name="uses_without_code" class="form-control font-weight-bold" value="{{ old('uses_without_code', $config->uses_without_code ?? 3) }}" required>
                                                <small class="text-muted">Default: 3 uses (e.g. ₹150 ÷ ₹50 = 3)</small>
                                            </div>
                                        </div>

                                        <!-- Discount & Validity -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Discount Per Service (₹)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text bg-white font-weight-bold">₹</span></div>
                                                    <input type="number" step="0.01" name="discount_per_service_without_code" class="form-control font-weight-bold text-success" value="{{ old('discount_per_service_without_code', $config->discount_per_service_without_code ?? $config->discount_per_service ?? 50.00) }}" required>
                                                </div>
                                                <small class="text-muted">Default: ₹50.00</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Expiry (Days from Join)</label>
                                                <input type="number" name="expiry_days_without_code" class="form-control" value="{{ old('expiry_days_without_code', $config->expiry_days_without_code ?? $config->expiry_days ?? 30) }}" required>
                                                <small class="text-muted">Default: 30 days</small>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Fixed Expiry Date (Optional)</label>
                                                <input type="date" name="custom_expiry_date_without_code" class="form-control" value="{{ old('custom_expiry_date_without_code', $config->custom_expiry_date_without_code ?? $config->custom_expiry_date) }}">
                                                <small class="text-muted">Overrides days if set (dd-mm-yyyy)</small>
                                            </div>
                                        </div>

                                        <!-- Bill Thresholds -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Min Bill Amount (₹)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                                    <input type="number" step="0.01" name="min_bill_without_code" class="form-control" value="{{ old('min_bill_without_code', $config->min_bill_without_code ?? $config->min_bill_amount ?? 50.00) }}" required>
                                                </div>
                                                <small class="text-muted">Default: ₹50.00</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Max Bill Amount (₹)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                                    <input type="number" step="0.01" name="max_bill_without_code" class="form-control" value="{{ old('max_bill_without_code', $config->max_bill_without_code ?? $config->max_bill_amount ?? 100000.00) }}" required>
                                                </div>
                                                <small class="text-muted">Default: ₹100000.00</small>
                                            </div>
                                        </div>

                                        <!-- Roles -->
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <label class="font-weight-bold text-dark" style="font-size: 13px;">Applicable User Roles</label>
                                                @php $roleTier2 = old('applicable_roles_without_code', $config->applicable_roles_without_code ?? $config->applicable_roles ?? 'all'); @endphp
                                                <select name="applicable_roles_without_code" class="form-control">
                                                    <option value="all" {{ $roleTier2 === 'all' ? 'selected' : '' }}>All Users (Consumer & Drivers)</option>
                                                    <option value="customer" {{ $roleTier2 === 'customer' ? 'selected' : '' }}>Consumers Only</option>
                                                    <option value="driver" {{ $roleTier2 === 'driver' ? 'selected' : '' }}>Drivers / Business Only</option>
                                                </select>
                                                <small class="text-muted">Who is eligible for Tier 2 Welcome Bonus</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- System Status & Submit Footer -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between p-3 rounded mb-4" style="background: #f1f5f9; border: 1px solid #cbd5e1;">
                                <div>
                                    <h6 class="font-weight-bold mb-0 text-dark">Promotional System Master Switch</h6>
                                    <small class="text-muted">Turn the entire promotional bonus and discount engine ON or OFF system-wide.</small>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <select name="status" class="form-control font-weight-bold {{ $config->status === 'active' ? 'text-success' : 'text-danger' }}" style="width: 150px;">
                                        <option value="active" {{ $config->status === 'active' ? 'selected' : '' }}>● ON (Active)</option>
                                        <option value="inactive" {{ $config->status === 'inactive' ? 'selected' : '' }}>○ OFF (Disabled)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary font-weight-bold px-5 py-2" style="border-radius: 10px; background: #6AA720; border-color: #6AA720; font-size: 15px;">
                                    Save & Apply Promotional Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Promotion Redemptions Ledger -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="font-weight-bold mb-0 text-dark">🧾 Live Promotional Redemptions Ledger</h5>
                            <small class="text-muted">Real-time audit log of service bookings where welcome promotional discount was redeemed.</small>
                        </div>
                        <span class="badge bg-light text-muted border px-2 py-1">Latest 25 redemptions</span>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: 13px;">
                                <thead class="bg-light text-muted text-uppercase" style="font-size: 11px;">
                                    <tr>
                                        <th class="px-4 py-3">Log ID</th>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Service Type</th>
                                        <th>Booking ID</th>
                                        <th class="text-right">Base Price</th>
                                        <th class="text-right">Promo Markup</th>
                                        <th class="text-right">Booking Total</th>
                                        <th class="text-right">Discount Applied</th>
                                        <th class="text-right">Final Payable</th>
                                        <th class="px-4 text-right">Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($logs as $log)
                                    <tr>
                                        <td class="px-4 font-weight-bold">#{{ $log->id }}</td>
                                        <td>User #{{ $log->user_id }}</td>
                                        <td>
                                            <span class="badge {{ $log->user_type === 'customer' ? 'bg-primary text-white' : 'bg-success text-white' }} px-2 py-0.5">
                                                {{ ucfirst($log->user_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $log->service_type === 'cab' ? '🚖 Cab / Ride' : '🧹 Home Service' }}
                                            </span>
                                        </td>
                                        <td class="font-weight-bold text-dark">#{{ $log->booking_id }}</td>
                                        <td class="text-right">₹{{ number_format($log->service_price, 2) }}</td>
                                        <td class="text-right text-primary">+₹{{ number_format($log->promotional_amount, 2) }}</td>
                                        <td class="text-right font-weight-bold">₹{{ number_format($log->booking_total, 2) }}</td>
                                        <td class="text-right text-success font-weight-bold">-₹{{ number_format($log->discount_applied, 2) }}</td>
                                        <td class="text-right font-weight-bold text-dark">₹{{ number_format($log->final_payable, 2) }}</td>
                                        <td class="px-4 text-right text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-5 text-muted">
                                            <i class="mdi mdi-ticket-percent-outline d-block mb-2" style="font-size: 36px; opacity: 0.4;"></i>
                                            No promotional redemptions recorded yet. Newly registered users will appear here when they book a service!
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

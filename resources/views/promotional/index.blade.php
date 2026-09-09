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
                                    Configure auto-grant welcome bonus credits, per-service discounts, and service-booking marketing deductions.
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="d-block text-white-50" style="font-size: 12px;">Active Discount Per Service</span>
                                <span class="text-warning font-weight-bold" style="font-size: 24px;">₹{{ number_format($config->discount_per_service, 2) }}</span>
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

        <!-- Configuration Form & Marketing Rule Guide -->
        <div class="row mb-4">
            <!-- Left: Settings Form -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-header bg-white border-bottom py-3 px-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        <h5 class="font-weight-bold mb-0 text-dark">
                            ⚙️ Master Promotional Configuration
                        </h5>
                        <small class="text-muted">Control bonus values, per-booking discount limits, and duration.</small>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('promotional.update') }}" method="POST">
                            @csrf

                            <!-- Section 1: Referral Code Join Tier -->
                            <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-primary text-white mr-2">Tier 1</span>
                                    <h6 class="font-weight-bold mb-0 text-dark">Join With Reference / Referral Code</h6>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="font-weight-semibold text-dark" style="font-size: 13px;">Welcome Bonus Amount (₹)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                            <input type="number" step="0.01" name="bonus_with_code" class="form-control" value="{{ old('bonus_with_code', $config->bonus_with_code) }}" required>
                                        </div>
                                        <small class="text-muted">Default: ₹300.00</small>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="font-weight-semibold text-dark" style="font-size: 13px;">Total Service Uses</label>
                                        <input type="number" name="uses_with_code" class="form-control" value="{{ old('uses_with_code', $config->uses_with_code) }}" required>
                                        <small class="text-muted">Default: 6 uses (e.g. ₹300 ÷ ₹50 = 6)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Direct Join Tier -->
                            <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-info text-white mr-2">Tier 2</span>
                                    <h6 class="font-weight-bold mb-0 text-dark">Direct Join Without Reference Code</h6>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="font-weight-semibold text-dark" style="font-size: 13px;">Welcome Bonus Amount (₹)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                            <input type="number" step="0.01" name="bonus_without_code" class="form-control" value="{{ old('bonus_without_code', $config->bonus_without_code) }}" required>
                                        </div>
                                        <small class="text-muted">Default: ₹150.00</small>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="font-weight-semibold text-dark" style="font-size: 13px;">Total Service Uses</label>
                                        <input type="number" name="uses_without_code" class="form-control" value="{{ old('uses_without_code', $config->uses_without_code) }}" required>
                                        <small class="text-muted">Default: 3 uses (e.g. ₹150 ÷ ₹50 = 3)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Usage Rules & Thresholds -->
                            <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <h6 class="font-weight-bold mb-2 text-dark">Discount Limits & Validity</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-semibold text-dark" style="font-size: 13px;">Discount Per Service (₹)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                            <input type="number" step="0.01" name="discount_per_service" class="form-control" value="{{ old('discount_per_service', $config->discount_per_service) }}" required>
                                        </div>
                                        <small class="text-muted">Default: ₹50.00</small>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-semibold text-dark" style="font-size: 13px;">Expiry (Days from Join)</label>
                                        <input type="number" name="expiry_days" class="form-control" value="{{ old('expiry_days', $config->expiry_days) }}" required>
                                        <small class="text-muted">Default: 30 days</small>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-semibold text-dark" style="font-size: 13px;">Fixed Expiry Date (Optional)</label>
                                        <input type="date" name="custom_expiry_date" class="form-control" value="{{ old('custom_expiry_date', $config->custom_expiry_date) }}">
                                        <small class="text-muted">Overrides days if set</small>
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <label class="font-weight-semibold text-dark" style="font-size: 13px;">Min Bill Amount (₹)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                            <input type="number" step="0.01" name="min_bill_amount" class="form-control" value="{{ old('min_bill_amount', $config->min_bill_amount) }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <label class="font-weight-semibold text-dark" style="font-size: 13px;">Max Bill Amount (₹)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                                            <input type="number" step="0.01" name="max_bill_amount" class="form-control" value="{{ old('max_bill_amount', $config->max_bill_amount) }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <label class="font-weight-semibold text-dark" style="font-size: 13px;">Applicable User Roles</label>
                                        <select name="applicable_roles" class="form-control">
                                            <option value="all" {{ $config->applicable_roles === 'all' ? 'selected' : '' }}>All Users (Consumer & Drivers)</option>
                                            <option value="customer" {{ $config->applicable_roles === 'customer' ? 'selected' : '' }}>Consumers Only</option>
                                            <option value="driver" {{ $config->applicable_roles === 'driver' ? 'selected' : '' }}>Drivers / Business Only</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: System Status Switch -->
                            <div class="d-flex align-items-center justify-content-between p-3 rounded mb-4" style="background: #f1f5f9; border: 1px solid #cbd5e1;">
                                <div>
                                    <h6 class="font-weight-bold mb-0 text-dark">Promotional System Master Status</h6>
                                    <small class="text-muted">Turn the entire promotional bonus and discount engine ON or OFF system-wide.</small>
                                </div>
                                <div>
                                    <select name="status" class="form-control font-weight-bold {{ $config->status === 'active' ? 'text-success' : 'text-danger' }}" style="width: 130px;">
                                        <option value="active" {{ $config->status === 'active' ? 'selected' : '' }}>● ON (Active)</option>
                                        <option value="inactive" {{ $config->status === 'inactive' ? 'selected' : '' }}>○ OFF (Disabled)</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2" style="border-radius: 10px; background: #6AA720; border-color: #6AA720;">
                                Save & Apply Promotional Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Marketing Logic Guide -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 16px; background: #ffffff;">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="font-weight-bold mb-0 text-dark">💡 Marketing Strategy Breakdown</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info border-0" style="border-radius: 12px; font-size: 13px; line-height: 1.6;">
                            <strong>User Psychology Flow:</strong><br>
                            When a customer has promotional balance (e.g. ₹50 discount available) and books a ₹100 service:
                            <ul class="mb-0 mt-2 pl-3">
                                <li>Service price shown: <strong>₹150</strong></li>
                                <li>Option given to apply <strong>₹50 Welcome Promo Cash</strong></li>
                                <li>Promo applied: <strong>-₹50</strong></li>
                                <li>User pays: <strong>₹100</strong></li>
                            </ul>
                        </div>

                        <div class="p-3 mb-3 rounded" style="background: #faf5ff; border: 1px solid #e9d5ff;">
                            <h6 class="font-weight-bold text-purple mb-1" style="font-size: 13px; color: #7e22ce;">🔒 Non-Cash Guarantee</h6>
                            <p class="mb-0 text-muted" style="font-size: 12px;">
                                Promotional balance is strictly isolated from withdrawable funds (<code>tj_user_app.amount</code>). Users <strong>cannot</strong> withdraw, transfer, or convert promo money to real cash.
                            </p>
                        </div>

                        <div class="p-3 rounded" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                            <h6 class="font-weight-bold text-success mb-1" style="font-size: 13px;">📱 In-App Wallet Display</h6>
                            <p class="mb-0 text-muted" style="font-size: 12px;">
                                Rendered as an active <strong>Promotion Card</strong> at the top right of the Smart Value Balance card in the webview wallet, displaying remaining discount allowance and validity.
                            </p>
                        </div>
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

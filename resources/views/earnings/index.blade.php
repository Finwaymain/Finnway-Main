@extends('layouts.app')

@section('content')
<!-- Chart.js for Visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
    --primary-color: #1e3a8a;
    --card-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04);
}

.dashboard-wrapper {
    background-color: #f1f5f9;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #000000;
    padding-bottom: 70px;
}

/* Header Bar */
.header-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 22px 28px;
    box-shadow: var(--card-shadow);
    border: 1.5px solid #e2e8f0;
}

.dashboard-title {
    font-weight: 900;
    font-size: 26px;
    color: #000000;
    letter-spacing: -0.5px;
}

/* Sticky Segmented Navigation Pills */
.sticky-nav-container {
    position: sticky;
    top: 70px;
    z-index: 99;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 14px;
    padding: 10px 14px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
    border: 1px solid #cbd5e1;
    overflow-x: auto;
    white-space: nowrap;
}

.segmented-control {
    display: flex;
    gap: 8px;
    align-items: center;
}

.segmented-item {
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    text-decoration: none;
    transition: all 0.2s ease;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.segmented-item:hover, .segmented-item.active {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
    text-decoration: none;
}

/* Modern Big Number Cards */
.big-stat-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: var(--card-shadow);
    border: 1.5px solid #e2e8f0;
    position: relative;
    overflow: hidden;
    height: 100%;
    transition: transform 0.2s ease;
}
.big-stat-card:hover {
    transform: translateY(-3px);
}

.stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.stat-tag {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #64748b;
}

.stat-icon {
    font-size: 24px;
    color: #0f172a;
}

.stat-big-value {
    font-size: 28px;
    font-weight: 900;
    color: #000000;
    line-height: 1.2;
    margin-bottom: 6px;
    letter-spacing: -0.5px;
}

.stat-subtext {
    font-size: 13px;
    font-weight: 700;
    color: #475569;
}

/* Section Containers */
.section-box {
    background: #ffffff;
    border-radius: 18px;
    padding: 26px 30px;
    box-shadow: var(--card-shadow);
    border: 1.5px solid #e2e8f0;
    margin-bottom: 30px;
    scroll-margin-top: 150px;
}

.section-box-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 22px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f1f5f9;
}

.section-heading {
    font-size: 20px;
    font-weight: 900;
    color: #000000;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

/* Buttons */
.btn-export-excel {
    background: #ecfdf5;
    color: #047857;
    border: 1.5px solid #10b981;
    border-radius: 10px;
    padding: 7px 16px;
    font-weight: 800;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}
.btn-export-excel:hover {
    background: #059669;
    color: #ffffff;
    border-color: #059669;
    text-decoration: none;
}

.btn-primary-dark {
    background: #0f172a;
    color: #ffffff;
    border-radius: 10px;
    padding: 9px 18px;
    font-weight: 800;
    font-size: 13px;
    border: none;
}
.btn-primary-dark:hover {
    background: #1e293b;
    color: #ffffff;
}

/* Tables */
.table-custom {
    width: 100%;
    margin-bottom: 0;
}
.table-custom thead th {
    background: #f8fafc;
    color: #0f172a;
    font-weight: 800;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 18px;
    border: none;
    border-bottom: 2px solid #e2e8f0;
}
.table-custom tbody td {
    padding: 16px 18px;
    vertical-align: middle;
    border-top: 1px solid #f1f5f9;
    font-size: 14px;
    font-weight: 600;
    color: #000000;
}
.table-custom tbody tr:hover {
    background-color: #f8fafc;
}

/* Text formatting */
.text-dark-bold {
    color: #000000 !important;
    font-weight: 800 !important;
}

.highlight-green { color: #047857 !important; font-weight: 900; }
.highlight-danger { color: #b91c1c !important; font-weight: 900; }
.highlight-blue { color: #1d4ed8 !important; font-weight: 900; }
.highlight-purple { color: #6d28d9 !important; font-weight: 900; }

.badge-dark-success { background: #dcfce7; color: #14532d; font-weight: 800; padding: 5px 12px; border-radius: 8px; }
.badge-dark-danger { background: #fee2e2; color: #7f1d1d; font-weight: 800; padding: 5px 12px; border-radius: 8px; }
.badge-dark-warning { background: #fef3c7; color: #78350f; font-weight: 800; padding: 5px 12px; border-radius: 8px; }
.badge-dark-primary { background: #dbeafe; color: #1e3a8a; font-weight: 800; padding: 5px 12px; border-radius: 8px; }
</style>

<div class="page-wrapper dashboard-wrapper">
    <div class="container-fluid px-3 px-lg-4 py-4">

        <!-- ── TOP EXECUTIVE CONTROL BAR ─────────────────────────────────── -->
        <div class="header-card mb-3">
            <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge badge-dark-success">Live Financial Intelligence</span>
                        <span class="text-dark-bold font-13">• 10 Unified Analytics Sections</span>
                    </div>
                    <h1 class="dashboard-title mb-1">FIINWAY All-in-One Earning Management</h1>
                    <p class="text-dark-bold font-14 mb-0">Unified financial intelligence, revenue breakdown, commission metrics & profit/loss statement</p>
                </div>

                <!-- Global Date Range Filter & Master Export -->
                <form action="{{ route('earnings.index') }}" method="GET" class="d-flex align-items-center flex-wrap gap-2 mb-0">
                    <div>
                        <select name="date_range" id="date_range_select" class="form-control font-weight-700 text-dark-bold" style="border-radius: 10px; border: 1.5px solid #94a3b8;" onchange="toggleCustomDate(this.value)">
                            <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="this_week" {{ $dateRange == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="this_year" {{ $dateRange == 'this_year' ? 'selected' : '' }}>This Year</option>
                            <option value="all" {{ $dateRange == 'all' ? 'selected' : '' }}>All Time</option>
                            <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                        </select>
                    </div>

                    <div id="custom_date_inputs" class="d-flex align-items-center gap-1 {{ $dateRange == 'custom' ? '' : 'd-none' }}">
                        <input type="date" name="start_date" class="form-control font-weight-700" style="border-radius: 10px;" value="{{ request('start_date', date('Y-m-01')) }}">
                        <input type="date" name="end_date" class="form-control font-weight-700" style="border-radius: 10px;" value="{{ request('end_date', date('Y-m-d')) }}">
                    </div>

                    <button type="submit" class="btn btn-primary-dark">
                        <i class="mdi mdi-filter-variant mr-1"></i> Apply Filter
                    </button>

                    <a href="{{ route('earnings.export', request()->all()) }}" class="btn btn-export-excel" style="background: #059669; color: #ffffff;">
                        <i class="mdi mdi-file-excel mr-1"></i> Export Master Report (CSV)
                    </a>
                </form>
            </div>
        </div>

        <!-- ── STICKY SEGMENTED NAVIGATION PILLS ────────────────────────── -->
        <div class="sticky-nav-container mb-4">
            <div class="segmented-control">
                <a class="segmented-item" href="#sec-1-revenue"><i class="mdi mdi-chart-box-outline"></i> 1. Revenue</a>
                <a class="segmented-item" href="#sec-2-services"><i class="mdi mdi-briefcase-check-outline"></i> 2. Service Comm</a>
                <a class="segmented-item" href="#sec-3-marketplace"><i class="mdi mdi-storefront-outline"></i> 3. Marketplace</a>
                <a class="segmented-item" href="#sec-4-premium"><i class="mdi mdi-diamond-outline"></i> 4. Premium Plans</a>
                <a class="segmented-item" href="#sec-5-referral"><i class="mdi mdi-account-group-outline"></i> 5. Referrals</a>
                <a class="segmented-item" href="#sec-6-payments"><i class="mdi mdi-credit-card-chip-outline"></i> 6. Payments</a>
                <a class="segmented-item" href="#sec-7-cashback"><i class="mdi mdi-ticket-percent-outline"></i> 7. Cashback / Burn</a>
                <a class="segmented-item" href="#sec-8-settlement"><i class="mdi mdi-bank-transfer"></i> 8. Settlements</a>
                <a class="segmented-item" href="#sec-9-pnl"><i class="mdi mdi-scale-balance"></i> 9. Profit & Loss</a>
                <a class="segmented-item" href="#sec-10-reports"><i class="mdi mdi-file-table-outline"></i> 10. Reports</a>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- SECTION 1: 📊 REVENUE DASHBOARD -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <div class="section-box" id="sec-1-revenue">
            <div class="section-box-header">
                <div>
                    <h2 class="section-heading">
                        <i class="mdi mdi-chart-box-outline text-primary"></i> 1. Revenue Dashboard
                    </h2>
                    <span class="text-dark-bold font-13">Gross GMV, Net Admin Revenue & Multi-Period Snapshot</span>
                </div>
                <a href="{{ route('earnings.export', request()->all()) }}" class="btn-export-excel">
                    <i class="mdi mdi-file-excel-outline"></i> Export Revenue (Excel)
                </a>
            </div>

            <!-- Big Stat Cards Row 1: Period Gross GMV Snapshot -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-3 col-xl-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">TODAY GROSS COLLECTION</span>
                            <i class="mdi mdi-calendar-today stat-icon text-primary"></i>
                        </div>
                        <div class="stat-big-value">₹{{ number_format($stats['revToday'], 2) }}</div>
                        <div class="stat-subtext">Real-time daily GMV collection</div>
                    </div>
                </div>

                <div class="col-12 col-md-3 col-xl-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">THIS WEEK</span>
                            <i class="mdi mdi-calendar-week stat-icon text-info"></i>
                        </div>
                        <div class="stat-big-value">₹{{ number_format($stats['revWeek'], 2) }}</div>
                        <div class="stat-subtext">Weekly total volume</div>
                    </div>
                </div>

                <div class="col-12 col-md-3 col-xl-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">THIS MONTH</span>
                            <i class="mdi mdi-calendar-month stat-icon text-success"></i>
                        </div>
                        <div class="stat-big-value">₹{{ number_format($stats['revMonth'], 2) }}</div>
                        <div class="stat-subtext">Monthly total GMV</div>
                    </div>
                </div>

                <div class="col-12 col-md-3 col-xl-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">FILTERED GROSS GMV</span>
                            <i class="mdi mdi-chart-areaspline stat-icon text-dark"></i>
                        </div>
                        <div class="stat-big-value text-dark-bold">₹{{ number_format($stats['grossRevenue'], 2) }}</div>
                        <div class="stat-subtext font-12">
                            <span class="text-success font-weight-bold">Online: ₹{{ number_format($stats['onlineGrossVolume'] ?? 0, 2) }}</span> • 
                            <span class="text-danger font-weight-bold">Cash: ₹{{ number_format($stats['cashGrossVolume'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Big Stat Cards Row 2: Admin Revenue Pillars, GST & Due Recovery -->
            <div class="row g-3 mb-4">
                <!-- Card 1: Net Admin Revenue -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="big-stat-card" style="border-left: 4px solid #6d28d9;">
                        <div class="stat-header">
                            <span class="stat-tag text-purple">NET ADMIN REVENUE</span>
                            <i class="mdi mdi-cash-multiple stat-icon text-purple"></i>
                        </div>
                        <div class="stat-big-value highlight-purple">₹{{ number_format($stats['netRevenue'], 2) }}</div>
                        <div class="stat-subtext mb-1 font-12 text-dark-bold">
                            Commissions (₹{{ number_format($stats['totalCommissionEarned'], 2) }}) + Platform Fees (₹{{ number_format($stats['platformFeeTotal'] ?? 0, 2) }})
                        </div>
                        <div class="font-11 text-muted">
                            <span class="badge badge-dark-success">Realized: ₹{{ number_format($stats['realizedAdminRevenue'] ?? $stats['netRevenue'], 2) }}</span>
                            @if(($stats['dueAdminRevenue'] ?? 0) > 0)
                                <span class="badge badge-dark-danger ml-1">Due Cash: ₹{{ number_format($stats['dueAdminRevenue'], 2) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card 2: Platform Fees (Admin Revenue Source #2) -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="big-stat-card" style="border-left: 4px solid #0284c7;">
                        <div class="stat-header">
                            <span class="stat-tag text-info">PLATFORM FEES COLLECTED</span>
                            <i class="mdi mdi-layers-triple stat-icon text-info"></i>
                        </div>
                        <div class="stat-big-value highlight-blue">₹{{ number_format($stats['platformFeeTotal'] ?? 0, 2) }}</div>
                        <div class="stat-subtext mb-1">Admin Revenue Source #2</div>
                        <div class="font-11 text-muted">
                            <span>Online: ₹{{ number_format($stats['platformFeeOnline'] ?? 0, 2) }}</span> • 
                            <span>Cash: ₹{{ number_format($stats['platformFeeCash'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: GST Tax Collected (Separate from Admin Revenue) -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="big-stat-card" style="border-left: 4px solid #f59e0b; background: #fffbeb;">
                        <div class="stat-header">
                            <span class="stat-tag text-warning">GST TAX COLLECTED</span>
                            <i class="mdi mdi-receipt-text-check stat-icon text-warning"></i>
                        </div>
                        <div class="stat-big-value text-warning">₹{{ number_format($stats['gstCollectedTotal'] ?? 0, 2) }}</div>
                        <div class="stat-subtext mb-1">
                            <span class="badge badge-dark-warning font-11">Tax Liability • Not Admin Earning</span>
                        </div>
                        <div class="font-11 text-muted">
                            <span>Online GST: ₹{{ number_format($stats['gstCollectedOnline'] ?? 0, 2) }}</span> • 
                            <span>Cash GST: ₹{{ number_format($stats['gstCollectedCash'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Provider Cash Debt & Due Recovery -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="big-stat-card" style="border-left: 4px solid #dc2626; background: #fef2f2;">
                        <div class="stat-header">
                            <span class="stat-tag text-danger">PENDING DUE RECOVERY</span>
                            <i class="mdi mdi-cash-refund stat-icon text-danger"></i>
                        </div>
                        <div class="stat-big-value highlight-danger">₹{{ number_format($stats['pendingDriverDebt'] ?? 0, 2) }}</div>
                        <div class="stat-subtext mb-1">
                            <span class="badge badge-dark-danger font-11">{{ $stats['driversWithDebtCount'] ?? 0 }} Providers in Cash Debt</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <span class="font-11 text-muted">Auto-deducted on Top-up</span>
                            <a href="#sec-8-settlement" class="btn btn-xs btn-outline-danger font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 6px;">
                                <i class="mdi mdi-eye mr-1"></i> Due Ledger
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="p-3 rounded" style="background: #ffffff; border: 1.5px solid #e2e8f0; height: 320px;">
                        <h4 class="text-dark-bold font-15 mb-2">6-Month Gross GMV vs Admin Commission Trend</h4>
                        <div style="height: 260px;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="p-3 rounded" style="background: #ffffff; border: 1.5px solid #e2e8f0; height: 320px;">
                        <h4 class="text-dark-bold font-15 mb-2">Payment Modes Distribution</h4>
                        <div style="height: 260px;">
                            <canvas id="paymentDonutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- SECTION 2: 💼 SERVICE COMMISSION -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <div class="section-box" id="sec-2-services">
            <div class="section-box-header">
                <div>
                    <h2 class="section-heading">
                        <i class="mdi mdi-briefcase-check-outline text-success"></i> 2. Service Commission Breakdown
                    </h2>
                    <span class="text-dark-bold font-13">Cab, Home Services, Food, Parcel, Travel & Other Services</span>
                </div>
                <a href="{{ route('earnings.export.services', request()->all()) }}" class="btn-export-excel">
                    <i class="mdi mdi-file-excel-outline"></i> Export Service Comm (Excel)
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Service Stream</th>
                            <th>Commission Config Rate</th>
                            <th>Total Bookings</th>
                            <th>Gross Sales (GMV)</th>
                            <th>Admin Commission Earned</th>
                            <th>Platform Fee Collected</th>
                            <th>GST Tax Collected</th>
                            <th>Net Admin Earning</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['servicesBreakdown'] as $sb)
                        <tr>
                            <td class="text-dark-bold font-15">{{ $sb['service'] }}</td>
                            <td><span class="badge badge-dark-primary font-13">{{ $sb['rate'] }}</span></td>
                            <td class="text-dark-bold font-15">{{ number_format($sb['bookings']) }} Bookings</td>
                            <td class="text-dark-bold font-15">₹{{ number_format($sb['gross'], 2) }}</td>
                            <td class="highlight-green font-16">₹{{ number_format($sb['commission'], 2) }}</td>
                            <td class="highlight-blue font-16">₹{{ number_format($sb['platform_fee'], 2) }}</td>
                            <td class="text-warning font-16 font-weight-bold">₹{{ number_format($sb['gst'], 2) }}</td>
                            <td class="highlight-purple font-16 font-weight-bold">₹{{ number_format($sb['admin_earning'], 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="bg-light font-weight-900" style="font-size: 16px;">
                            <td colspan="3" class="text-dark-bold">TOTAL SERVICE STREAM EARNINGS</td>
                            <td class="text-dark-bold font-16">₹{{ number_format(collect($stats['servicesBreakdown'])->sum('gross'), 2) }}</td>
                            <td class="highlight-green font-16">₹{{ number_format($stats['totalCommissionEarned'], 2) }}</td>
                            <td class="highlight-blue font-16">₹{{ number_format($stats['platformFeeTotal'], 2) }}</td>
                            <td class="text-warning font-16 font-weight-bold">₹{{ number_format(collect($stats['servicesBreakdown'])->sum('gst'), 2) }}</td>
                            <td class="highlight-purple font-18">₹{{ number_format($stats['totalCommissionEarned'] + $stats['platformFeeTotal'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- SECTION 3: 🛒 MARKETPLACE COMMISSION -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <div class="section-box" id="sec-3-marketplace">
            <div class="section-box-header">
                <div>
                    <h2 class="section-heading">
                        <i class="mdi mdi-storefront-outline text-info"></i> 3. Marketplace Commission
                    </h2>
                    <span class="text-dark-bold font-13">Product Sales, Seller Commission & Category Breakdowns</span>
                </div>
                <a href="{{ route('earnings.export.marketplace', request()->all()) }}" class="btn-export-excel">
                    <i class="mdi mdi-file-excel-outline"></i> Export Marketplace (Excel)
                </a>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">TOTAL PRODUCT SALES (GMV)</span>
                            <i class="mdi mdi-shopping stat-icon text-info"></i>
                        </div>
                        <div class="stat-big-value">₹{{ number_format($stats['marketplaceProductSales'], 2) }}</div>
                        <div class="stat-subtext">Gross e-commerce sales</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">SELLER PLATFORM COMMISSION (10%)</span>
                            <i class="mdi mdi-percent stat-icon text-success"></i>
                        </div>
                        <div class="stat-big-value highlight-green">₹{{ number_format($stats['marketplaceSellerComm'], 2) }}</div>
                        <div class="stat-subtext">Admin marketplace share</div>
                    </div>
                </div>
            </div>

            <h4 class="text-dark-bold font-16 mb-3">Recent Marketplace Orders & Commission</h4>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>#Order ID</th>
                            <th>Buyer Name</th>
                            <th>Buyer Phone</th>
                            <th>Total Order Amount</th>
                            <th>Admin Commission (10%)</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['recentMarketplaceOrders'] as $order)
                        <tr>
                            <td class="text-dark-bold text-primary font-15">#{{ $order->id }}</td>
                            <td class="text-dark-bold font-15">{{ $order->buyer_name ?: 'Customer' }}</td>
                            <td class="text-dark-bold">{{ $order->phone ?: 'N/A' }}</td>
                            <td class="text-dark-bold font-15">₹{{ number_format((float)$order->total_amount, 2) }}</td>
                            <td class="highlight-green font-15">₹{{ number_format($order->seller_commission, 2) }}</td>
                            <td><span class="badge badge-dark-success">{{ ucfirst($order->status ?: 'Completed') }}</span></td>
                            <td class="text-dark-bold font-13">{{ $order->created_at }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-dark-bold py-4">No marketplace orders recorded in this date range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- SECTION 4: 💎 PREMIUM PLAN REVENUE -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <div class="section-box" id="sec-4-premium">
            <div class="section-box-header">
                <div>
                    <h2 class="section-heading">
                        <i class="mdi mdi-diamond-outline text-purple"></i> 4. Premium Plan Revenue
                    </h2>
                    <span class="text-dark-bold font-13">Consumer Plans, Business Plans, Purchases & Renewals</span>
                </div>
                <a href="{{ route('earnings.export.premium', request()->all()) }}" class="btn-export-excel">
                    <i class="mdi mdi-file-excel-outline"></i> Export Premium Plans (Excel)
                </a>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">CONSUMER ACTIVE PLANS</span>
                            <i class="mdi mdi-account-star stat-icon text-primary"></i>
                        </div>
                        <div class="stat-big-value">{{ number_format($stats['consumerPlanCount']) }}</div>
                        <div class="stat-subtext">Active subscribed consumers</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">BUSINESS ACTIVE PLANS</span>
                            <i class="mdi mdi-store-check stat-icon text-info"></i>
                        </div>
                        <div class="stat-big-value">{{ number_format($stats['businessPlanCount']) }}</div>
                        <div class="stat-subtext">Active subscribed partners</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">TOTAL SUBSCRIPTION REVENUE</span>
                            <i class="mdi mdi-cash-check stat-icon text-purple"></i>
                        </div>
                        <div class="stat-big-value highlight-purple">₹{{ number_format($stats['totalSubscriptionRevenue'], 2) }}</div>
                        <div class="stat-subtext">All plan earnings</div>
                    </div>
                </div>
            </div>

            <h4 class="text-dark-bold font-16 mb-3">Recent Subscription History</h4>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Subscriber Name</th>
                            <th>Contact Phone</th>
                            <th>Plan Title</th>
                            <th>Amount Paid</th>
                            <th>Subscription Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['subHistoryList'] as $sub)
                        <tr>
                            <td class="text-dark-bold font-15">#{{ $sub->id }}</td>
                            <td class="text-dark-bold font-15">{{ $sub->subscriber_name ?: 'User #' . $sub->user_id }}</td>
                            <td class="text-dark-bold">{{ $sub->phone ?: 'N/A' }}</td>
                            <td><span class="badge badge-dark-primary font-13">{{ $sub->plan_title }}</span></td>
                            <td class="highlight-green font-15">₹{{ number_format($sub->price, 2) }}</td>
                            <td class="text-dark-bold font-13">{{ $sub->created_at }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-dark-bold py-4">No subscription records found for this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- SECTION 5: 🤝 REFERRAL COST & REVENUE -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <div class="section-box" id="sec-5-referral">
            <div class="section-box-header">
                <div>
                    <h2 class="section-heading">
                        <i class="mdi mdi-account-group-outline text-pink"></i> 5. Referral Cost & Revenue
                    </h2>
                    <span class="text-dark-bold font-13">Referral-Generated Transactions, Rewards Paid & Net Contribution</span>
                </div>
                <a href="{{ route('earnings.export.referral', request()->all()) }}" class="btn-export-excel">
                    <i class="mdi mdi-file-excel-outline"></i> Export Referrals (Excel)
                </a>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">TOTAL REFERRALS</span>
                            <i class="mdi mdi-link-variant stat-icon text-pink"></i>
                        </div>
                        <div class="stat-big-value">{{ number_format($stats['referralCount']) }}</div>
                        <div class="stat-subtext">Referral links created</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">REWARDS DISTRIBUTED (BURN)</span>
                            <i class="mdi mdi-fire stat-icon text-danger"></i>
                        </div>
                        <div class="stat-big-value highlight-danger">₹{{ number_format($stats['referralRewardsPaid'], 2) }}</div>
                        <div class="stat-subtext">Admin reward payout</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">REFERRED USERS REVENUE</span>
                            <i class="mdi mdi-currency-inr stat-icon text-primary"></i>
                        </div>
                        <div class="stat-big-value">₹{{ number_format($stats['revenueByReferredUsers'], 2) }}</div>
                        <div class="stat-subtext">Gross generated by invitees</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">NET CONTRIBUTION</span>
                            <i class="mdi mdi-trending-up stat-icon text-success"></i>
                        </div>
                        <div class="stat-big-value highlight-green">₹{{ number_format($stats['netReferralContribution'], 2) }}</div>
                        <div class="stat-subtext">Net ROI on referrals</div>
                    </div>
                </div>
            </div>

            <h4 class="text-dark-bold font-16 mb-3">Recent Referrals Network List</h4>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>User / Referrer Name</th>
                            <th>Contact Phone</th>
                            <th>User Type</th>
                            <th>Referral Code</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['referralList'] as $ref)
                        <tr>
                            <td class="text-dark-bold font-15">#{{ $ref->id }}</td>
                            <td class="text-dark-bold font-15">{{ $ref->user_name }}</td>
                            <td class="text-dark-bold">{{ $ref->phone ?: 'N/A' }}</td>
                            <td><span class="badge badge-dark-primary font-13">{{ ucfirst($ref->user_type ?: 'Customer') }}</span></td>
                            <td class="text-dark-bold font-15 text-primary">{{ $ref->referral_code ?: 'N/A' }}</td>
                            <td class="text-dark-bold font-13">{{ $ref->creer }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-dark-bold py-4">No referral records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- SECTION 6: 💳 PAYMENT & TRANSACTION REVENUE -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <div class="section-box" id="sec-6-payments">
            <div class="section-box-header">
                <div>
                    <h2 class="section-heading">
                        <i class="mdi mdi-credit-card-chip-outline text-warning"></i> 6. Payment & Transaction Revenue
                    </h2>
                    <span class="text-dark-bold font-13">Payment Volume, Gateway Charges, Platform Charges & Company Share</span>
                </div>
                <a href="{{ route('earnings.export.payments', request()->all()) }}" class="btn-export-excel">
                    <i class="mdi mdi-file-excel-outline"></i> Export Payments (Excel)
                </a>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">TOTAL PAYMENT VOLUME</span>
                            <i class="mdi mdi-wallet-giftcard stat-icon text-primary"></i>
                        </div>
                        <div class="stat-big-value">₹{{ number_format($stats['totalPaymentVolume'], 2) }}</div>
                        <div class="stat-subtext font-12">
                            <span>External Inflow: ₹{{ number_format($stats['externalGatewayVolume'] ?? 0, 2) }}</span> • 
                            <span>Wallet Spend: ₹{{ number_format($stats['internalWalletSpent'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">GATEWAY CHARGES (EST. 2%)</span>
                            <i class="mdi mdi-bank stat-icon text-warning"></i>
                        </div>
                        <div class="stat-big-value text-warning">₹{{ number_format($stats['gatewayCharges'], 2) }}</div>
                        <div class="stat-subtext">2% on UPI/Cards/Gateways (0% for Wallet)</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">PLATFORM CHARGES</span>
                            <i class="mdi mdi-tag-check stat-icon text-info"></i>
                        </div>
                        <div class="stat-big-value text-info">₹{{ number_format($stats['platformCharges'], 2) }}</div>
                        <div class="stat-subtext">Fixed booking fees</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">FAILED / REFUNDED TXNS</span>
                            <i class="mdi mdi-close-circle stat-icon text-danger"></i>
                        </div>
                        <div class="stat-big-value highlight-danger">₹{{ number_format($stats['failedTxnsAmount'], 2) }}</div>
                        <div class="stat-subtext">{{ $stats['failedTxnsCount'] }} Failed / Cancelled transactions</div>
                    </div>
                </div>
            </div>

            <h4 class="text-dark-bold font-16 mb-3">Recent Transactions Log</h4>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>#TXN</th>
                            <th>User Name</th>
                            <th>Contact Phone</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['recentTransactions'] as $txn)
                        <tr>
                            <td class="text-dark-bold text-primary font-15">#{{ $txn->id }}</td>
                            <td class="text-dark-bold font-15">{{ $txn->user_name ?: 'User #' . $txn->id_user_app }}</td>
                            <td class="text-dark-bold">{{ $txn->phone ?: 'N/A' }}</td>
                            <td class="text-dark-bold font-15">₹{{ number_format((float)$txn->amount, 2) }}</td>
                            <td><span class="badge badge-dark-primary font-13">{{ $txn->payment_method ?: 'Online' }}</span></td>
                            <td>
                                @if(in_array(strtolower($txn->payment_status), ['success', 'paid']))
                                    <span class="badge badge-dark-success">Success</span>
                                @else
                                    <span class="badge badge-dark-danger">{{ ucfirst($txn->payment_status ?: 'Failed') }}</span>
                                @endif
                            </td>
                            <td class="text-dark-bold font-13">{{ $txn->creer }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-dark-bold py-4">No transactions recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- SECTION 7: 🎁 CASHBACK & DISCOUNT COST -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <div class="section-box" id="sec-7-cashback">
            <div class="section-box-header">
                <div>
                    <h2 class="section-heading">
                        <i class="mdi mdi-ticket-percent-outline text-danger"></i> 7. Cashback & Promotional Burn Cost
                    </h2>
                    <span class="text-dark-bold font-13">Cashback Given, Ride Discounts, Referral Costs & Total Marketing Burn</span>
                </div>
                <a href="{{ route('earnings.export.profit-loss', request()->all()) }}" class="btn-export-excel">
                    <i class="mdi mdi-file-excel-outline"></i> Export Burn Cost (Excel)
                </a>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">CASHBACK & BONUSES</span>
                            <i class="mdi mdi-gift stat-icon text-danger"></i>
                        </div>
                        <div class="stat-big-value highlight-danger">₹{{ number_format($stats['cashbackGiven'], 2) }}</div>
                        <div class="stat-subtext">Direct wallet credit bonuses</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">DISCOUNTS GIVEN</span>
                            <i class="mdi mdi-tag-off stat-icon text-warning"></i>
                        </div>
                        <div class="stat-big-value text-warning">₹{{ number_format($stats['discountsGiven'], 2) }}</div>
                        <div class="stat-subtext">Coupon & promotion codes</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">MEDICAL CASHBACK CLAIMS</span>
                            <i class="mdi mdi-medical-bag stat-icon text-danger"></i>
                        </div>
                        <div class="stat-big-value highlight-danger">₹{{ number_format($stats['medicalCashbackGiven'], 2) }}</div>
                        <div class="stat-subtext">Approved claim payouts</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">TOTAL PROMOTIONAL BURN</span>
                            <i class="mdi mdi-fire stat-icon text-danger"></i>
                        </div>
                        <div class="stat-big-value highlight-danger">₹{{ number_format($stats['totalPromotionalCost'], 2) }}</div>
                        <div class="stat-subtext">Total user acquisition spend</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- SECTION 8: 🏦 SETTLEMENT & PROVIDER PAYOUT -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <div class="section-box" id="sec-8-settlement">
            <div class="section-box-header">
                <div>
                    <h2 class="section-heading">
                        <i class="mdi mdi-bank-transfer text-warning"></i> 8. Settlement & Provider Payout
                    </h2>
                    <span class="text-dark-bold font-13">Business Collection, Company Commission, Provider Payable & Settlement Status</span>
                </div>
                <a href="{{ route('earnings.export.settlement', request()->all()) }}" class="btn-export-excel">
                    <i class="mdi mdi-file-excel-outline"></i> Export Settlements (Excel)
                </a>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">BUSINESS COLLECTION (GMV)</span>
                            <i class="mdi mdi-cash stat-icon text-primary"></i>
                        </div>
                        <div class="stat-big-value">₹{{ number_format($stats['businessCollection'], 2) }}</div>
                        <div class="stat-subtext">Gross revenue collected from users</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">COMPANY COMMISSION & FEES</span>
                            <i class="mdi mdi-shield-check stat-icon text-success"></i>
                        </div>
                        <div class="stat-big-value highlight-green">₹{{ number_format($stats['companyCommission'], 2) }}</div>
                        <div class="stat-subtext">Admin retained earnings</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="big-stat-card">
                        <div class="stat-header">
                            <span class="stat-tag">PROVIDER PAYABLE AMOUNT</span>
                            <i class="mdi mdi-account-cash stat-icon text-warning"></i>
                        </div>
                        <div class="stat-big-value highlight-danger">₹{{ number_format($stats['providerPayable'], 2) }}</div>
                        <div class="stat-subtext">Total amount payable to partners</div>
                    </div>
                </div>
            </div>

            <h4 class="text-dark-bold font-16 mb-3">Recent Provider Withdrawal & Settlement Requests</h4>
            <div class="table-responsive mb-4">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Provider / Driver Name</th>
                            <th>Contact Phone</th>
                            <th>Contact Email</th>
                            <th>Settlement Amount</th>
                            <th>Status</th>
                            <th>Date Requested</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['recentSettlements'] as $bs)
                        <tr>
                            <td class="text-dark-bold text-warning font-15">#{{ $bs->id }}</td>
                            <td class="text-dark-bold font-15">{{ $bs->provider_name ?: 'Service Provider' }}</td>
                            <td class="text-dark-bold">{{ $bs->phone ?: 'N/A' }}</td>
                            <td class="text-dark-bold">{{ $bs->email ?: 'N/A' }}</td>
                            <td class="text-dark-bold font-16">₹{{ number_format((float)$bs->amount, 2) }}</td>
                            <td>
                                @if($bs->statut == 1)
                                    <span class="badge badge-dark-success">Settled / Paid</span>
                                @else
                                    <span class="badge badge-dark-warning">Pending Approval</span>
                                @endif
                            </td>
                            <td class="text-dark-bold font-13">{{ $bs->creer }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-dark-bold py-4">No settlement requests recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Provider Cash Debt & Recovery Ledger -->
            <div class="p-3 rounded mb-2" style="background: #fff5f5; border: 1.5px solid #fecaca;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h4 class="text-danger font-weight-bold font-16 mb-0">
                            <i class="mdi mdi-cash-refund mr-1"></i> Outstanding Cash Collections Due from Service Providers (Recovery Queue)
                        </h4>
                        <span class="text-muted font-12">Unpaid admin commissions, platform fees & GST from cash bookings (Auto-deducted when provider recharges wallet)</span>
                    </div>
                    <span class="badge badge-dark-danger font-13">Total Due: ₹{{ number_format($stats['pendingDriverDebt'] ?? 0, 2) }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr style="background: #fee2e2;">
                                <th>Driver / Provider</th>
                                <th>Contact Phone</th>
                                <th>Account No.</th>
                                <th>Negative Balance (Debt Owed)</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['driversDebtList'] ?? [] as $debtor)
                            <tr>
                                <td class="text-dark-bold font-15">{{ trim(($debtor->prenom ?? '') . ' ' . ($debtor->nom ?? '')) ?: 'Provider #' . $debtor->id }}</td>
                                <td class="text-dark-bold">{{ $debtor->phone ?: 'N/A' }}</td>
                                <td class="text-muted font-13 font-mono">{{ $debtor->ac_no ?: '-' }}</td>
                                <td class="highlight-danger font-16">₹{{ number_format(abs((float)$debtor->amount), 2) }}</td>
                                <td><span class="badge badge-dark-danger">Cash Recovery Pending</span></td>
                                <td>
                                    <a href="{{ url('walletstransactions/driver/' . $debtor->id) }}" class="btn btn-xs btn-outline-danger font-weight-bold px-2 py-1" style="border-radius: 6px;">
                                        <i class="mdi mdi-history mr-1"></i> Transaction Ledger
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-success font-weight-bold py-3">
                                    <i class="mdi mdi-check-circle mr-1"></i> All service providers are fully settled. No outstanding cash debt!
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- SECTION 9: 📈 PROFIT & LOSS STATEMENT -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <div class="section-box" id="sec-9-pnl">
            <div class="section-box-header">
                <div>
                    <h2 class="section-heading">
                        <i class="mdi mdi-scale-balance text-purple"></i> 9. Accurate Profit & Loss Statement
                    </h2>
                    <span class="text-dark-bold font-13">Gross Revenue, Payouts, Gateway Charges, Promotional Burn & True Net Admin Profit</span>
                </div>
                <a href="{{ route('earnings.export.profit-loss', request()->all()) }}" class="btn-export-excel">
                    <i class="mdi mdi-file-excel-outline"></i> Export P&L Statement (Excel)
                </a>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Financial Line Item</th>
                                    <th class="text-right">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-dark-bold font-16">Total Gross Ecosystem Revenue (GMV)</td>
                                    <td class="text-right text-dark-bold font-16">₹{{ number_format($stats['totalRevenuePnl'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-danger font-15">− Provider / Business Payouts</td>
                                    <td class="text-right text-danger font-15">-₹{{ number_format($stats['providerPayable'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-danger font-15">− Estimated Payment Gateway Charges (2%)</td>
                                    <td class="text-right text-danger font-15">-₹{{ number_format($stats['gatewayCharges'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-danger font-15">− Cashback & Wallet Bonuses Given</td>
                                    <td class="text-right text-danger font-15">-₹{{ number_format($stats['cashbackGiven'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-danger font-15">− Referral Rewards Paid</td>
                                    <td class="text-right text-danger font-15">-₹{{ number_format($stats['referralRewardsPaid'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-danger font-15">− Medical Cashback Approved Claims</td>
                                    <td class="text-right text-danger font-15">-₹{{ number_format($stats['medicalCashbackGiven'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-danger font-15">− Refunds & Cancelled Failed Payments</td>
                                    <td class="text-right text-danger font-15">-₹{{ number_format($stats['refundsPnl'], 2) }}</td>
                                </tr>
                                <tr class="bg-light" style="border-top: 2px solid #000000;">
                                    <td class="text-dark-bold font-18">NET ADMIN PROFIT</td>
                                    <td class="text-right font-20 {{ $stats['netProfitPnl'] >= 0 ? 'highlight-green' : 'highlight-danger' }}">
                                        ₹{{ number_format($stats['netProfitPnl'], 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="p-4 rounded text-center d-flex flex-column justify-content-center h-100" style="background: #f8fafc; border: 2px solid #cbd5e1;">
                        <span class="stat-tag mb-2">NET PROFIT MARGIN</span>
                        <div class="font-36 font-weight-900 mb-2 {{ $stats['netProfitPnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $stats['profitMarginPnl'] }}%
                        </div>
                        <p class="text-dark-bold font-14 mb-3">
                            Actual real-world business margin after accounting for all provider payouts, payment gateway costs, and marketing burn liabilities.
                        </p>
                        <div class="p-3 rounded bg-white border">
                            <div class="d-flex justify-content-between font-14 font-weight-800 text-dark-bold mb-1">
                                <span>Gross Company Earning:</span>
                                <span class="highlight-purple">₹{{ number_format($stats['netRevenue'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between font-14 font-weight-800 text-dark-bold">
                                <span>Total Promotional Burn:</span>
                                <span class="highlight-danger">-₹{{ number_format($stats['totalPromotionalCost'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────────────────────── -->
        <!-- SECTION 10: 📑 EARNING REPORTS & BREAKDOWNS -->
        <!-- ─────────────────────────────────────────────────────────────── -->
        <div class="section-box" id="sec-10-reports">
            <div class="section-box-header">
                <div>
                    <h2 class="section-heading">
                        <i class="mdi mdi-file-table-outline text-primary"></i> 10. Earning Reports & Analytics
                    </h2>
                    <span class="text-dark-bold font-13">Date-wise, Service-wise, Business-wise & Payment-wise Exportable Intelligence</span>
                </div>
                <a href="{{ route('earnings.export.reports', request()->all()) }}" class="btn-export-excel">
                    <i class="mdi mdi-file-excel-outline"></i> Export Daily Reports (Excel)
                </a>
            </div>

            <h4 class="text-dark-bold font-16 mb-3">Date-wise Transaction & Commission Summary</h4>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Completed Rides / Bookings</th>
                            <th>Gross Booking Value</th>
                            <th>Admin Commission Earned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['dailyReports'] as $day)
                        <tr>
                            <td class="text-dark-bold font-15">{{ date('D, d M Y', strtotime($day->date)) }}</td>
                            <td class="text-dark-bold font-15">{{ $day->total_rides }} Completed</td>
                            <td class="text-dark-bold font-15">₹{{ number_format((float)$day->gross_amount, 2) }}</td>
                            <td class="highlight-green font-16">₹{{ number_format((float)$day->commission, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-dark-bold py-4">No daily activity records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
function toggleCustomDate(val) {
    var el = document.getElementById('custom_date_inputs');
    if (val === 'custom') {
        el.classList.remove('d-none');
    } else {
        el.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Trend Line Chart
    var trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($stats['chartLabels']) !!},
            datasets: [
                {
                    label: 'Gross GMV (₹)',
                    data: {!! json_encode($stats['chartGrossData']) !!},
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 5
                },
                {
                    label: 'Admin Commission (₹)',
                    data: {!! json_encode($stats['chartNetData']) !!},
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'Plus Jakarta Sans', weight: '700', size: 12 } } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { weight: '700' } } },
                y: { grid: { color: '#e2e8f0' }, ticks: { font: { weight: '700' }, callback: function(v) { return '₹' + v.toLocaleString('en-IN'); } } }
            }
        }
    });

    // 2. Payment Modes Donut Chart
    var donutCtx = document.getElementById('paymentDonutChart').getContext('2d');
    var paymentData = {!! json_encode($stats['paymentModeData']) !!};
    var donutLabels = paymentData.length ? paymentData.map(p => p.payment_method) : ['Online / UPI', 'Wallet', 'Cash'];
    var donutValues = paymentData.length ? paymentData.map(p => p.total) : [100, 50, 20];

    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: donutLabels,
            datasets: [{
                data: donutValues,
                backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'],
                borderWidth: 3,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', weight: '700', size: 11 } } }
            },
            cutout: '68%'
        }
    });
});
</script>
@endsection

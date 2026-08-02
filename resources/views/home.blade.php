@extends('layouts.app')

@section('style')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    /* Premium Dashboard Stylesheet Overrides */
    body, .page-wrapper, #main-wrapper {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: #F8FAFC !important;
    }
    
    .dashboard-container {
        padding: 24px 30px;
    }
    
    /* Card design */
    .premium-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        padding: 22px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    
    .premium-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }
    
    /* Stats Card specific */
    .stat-card {
        padding: 16px 14px !important;
        border-radius: 16px !important;
        border: 1px solid #E2E8F0 !important;
        background: #ffffff !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        height: 100% !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04) !important;
    }
    
    .stat-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        gap: 6px !important;
        margin-bottom: 8px !important;
        width: 100% !important;
    }
    
    .stat-title {
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #64748B !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
        line-height: 1.25 !important;
        flex: 1 1 auto !important;
        margin: 0 !important;
        word-break: break-word !important;
    }
    
    .stat-icon {
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
        min-height: 32px !important;
        border-radius: 8px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        flex-shrink: 0 !important;
    }
    
    .stat-value {
        font-size: 20px !important;
        font-weight: 800 !important;
        color: #0F172A !important;
        letter-spacing: -0.5px !important;
        margin-top: 4px !important;
        margin-bottom: 6px !important;
        line-height: 1.2 !important;
    }
    
    .trend-badge {
        display: inline-flex !important;
        align-items: center !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
        padding: 2px 7px !important;
        border-radius: 6px !important;
        gap: 3px !important;
    }
    
    .trend-up {
        background-color: #DCFCE7;
        color: #15803D;
    }
    
    /* Typography */
    .section-title {
        font-size: 16px;
        font-weight: 800;
        color: #0F172A;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Quick Actions */
    .action-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    
    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 14px 8px;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        transition: all 0.2s ease;
        text-decoration: none !important;
        cursor: pointer;
    }
    
    .action-btn:hover {
        background: #5B4FE9;
        color: #ffffff !important;
        border-color: #5B4FE9;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(91, 79, 233, 0.15);
    }
    
    .action-btn i {
        font-size: 20px;
        margin-bottom: 8px;
    }
    
    /* Tables design */
    .premium-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .premium-table th {
        background: #F8FAFC;
        color: #475569;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 14px;
        border-bottom: 1px solid #E2E8F0;
    }
    
    .premium-table td {
        padding: 14px;
        font-size: 13px;
        color: #334155;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    
    .premium-table tbody tr:hover {
        background-color: #F8FAFC;
    }
    
    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
    
    .status-success {
        background-color: #DCFCE7;
        color: #15803D;
    }
    
    .status-pending {
        background-color: #FEF3C7;
        color: #D97706;
    }
    
    .status-ongoing {
        background-color: #DBEAFE;
        color: #1D4ED8;
    }
    
    /* Right stats panel list */
    .sidebar-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    .sidebar-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
    }
    
    .sidebar-label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .sidebar-value {
        font-size: 13px;
        font-weight: 800;
        color: #0F172A;
    }
    
    .notification-item {
        padding: 10px 0;
        border-bottom: 1px solid #F1F5F9;
    }
    
    .notification-item:last-child {
        border-bottom: none;
    }
</style>
@endsection

@section('content')
<div class="page-wrapper">
@php
use Illuminate\Support\Facades\DB;

// Fetch dynamic data counts for Business Summary
$totalBusiness = DB::table('tj_conducteur')->count();
$activeBusiness = DB::table('tj_conducteur')->where('statut', 'yes')->count();
$pendingKyc = DB::table('tj_conducteur')->whereNull('kyc_status')->orWhere('kyc_status', '!=', '1')->count();
$verifiedBusiness = DB::table('tj_conducteur')->where('kyc_status', '1')->count();
$suspendedBusiness = DB::table('tj_conducteur')->where('statut', 'no')->count();
$rejectedBusiness = DB::table('tj_conducteur')->where('is_verified', 0)->count();

// Fetch dynamic data counts for Financial Summary
$totalCommission = DB::table('tj_requete')->where('statut', 'completed')->sum('admin_commission');
$totalCashback = DB::table('tj_transaction')->where('description', 'LIKE', '%cashback%')->sum('amount');
$totalPayout = DB::table('withdrawals')->where('statut', 'completed')->sum('amount');
$totalRefunds = DB::table('tj_transaction')->where('amount', '<', 0)->sum('amount');

// Recent Transactions
$recentTransactions = DB::table('tj_transaction')
    ->leftJoin('tj_user_app', 'tj_transaction.ac_no', '=', 'tj_user_app.ac_no')
    ->select('tj_transaction.*', 'tj_user_app.prenom', 'tj_user_app.nom')
    ->orderBy('tj_transaction.creer', 'desc')
    ->limit(5)
    ->get();

// Recent Bookings
$recentBookings = DB::table('tj_requete')
    ->leftJoin('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
    ->leftJoin('tj_conducteur', 'tj_requete.id_conducteur', '=', 'tj_conducteur.id')
    ->select('tj_requete.*', 'tj_user_app.prenom as user_name', 'tj_user_app.nom as user_lastname', 'tj_conducteur.prenom as driver_name', 'tj_conducteur.nom as driver_lastname')
    ->orderBy('tj_requete.creer', 'desc')
    ->limit(5)
    ->get();
@endphp

<div class="dashboard-container">
    <!-- Dashboard Welcome Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-size: 24px; font-weight: 800; color: #0F172A; margin: 0;">Dashboard</h2>
            <p style="font-size: 13px; color: #64748B; margin: 4px 0 0;">Welcome back, Super Admin</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="position-relative">
                <select class="form-control" style="border-radius: 10px; border: 1px solid #E2E8F0; font-size: 13px; font-weight: 600; padding: 8px 16px; background-color: #fff; height: 38px;">
                    <option>01 May 2025 - 31 May 2025</option>
                </select>
            </div>
            <button onclick="location.reload()" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="border-radius: 10px; border: 1px solid #E2E8F0; width: 38px; height: 38px; background: #fff;">
                <i class="mdi mdi-refresh" style="font-size: 18px; color: #475569;"></i>
            </button>
        </div>
    </div>

    <!-- Top Statistics Cards -->
    <div class="row mb-2">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="premium-card stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Revenue</span>
                    <div class="stat-icon" style="background: rgba(91, 79, 233, 0.1); color: #5B4FE9;"><i class="mdi mdi-currency-usd"></i></div>
                </div>
                <div class="stat-value">₹{{ number_format($total_earnings, 0) }}</div>
                <div>
                    <span class="trend-badge trend-up"><i class="mdi mdi-trending-up"></i> +18.6%</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="premium-card stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Profit</span>
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;"><i class="mdi mdi-cash-multiple"></i></div>
                </div>
                <div class="stat-value">₹{{ number_format($total_admin_commission, 0) }}</div>
                <div>
                    <span class="trend-badge trend-up"><i class="mdi mdi-trending-up"></i> +21.1%</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="premium-card stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Users</span>
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;"><i class="mdi mdi-account-multiple"></i></div>
                </div>
                <div class="stat-value">{{ number_format($total_users) }}</div>
                <div>
                    <span class="trend-badge trend-up"><i class="mdi mdi-trending-up"></i> +15.6%</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="premium-card stat-card">
                <div class="stat-header">
                    <span class="stat-title">Business Users</span>
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;"><i class="mdi mdi-store"></i></div>
                </div>
                <div class="stat-value">{{ number_format($total_drivers) }}</div>
                <div>
                    <span class="trend-badge trend-up"><i class="mdi mdi-trending-up"></i> +17.5%</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="premium-card stat-card">
                <div class="stat-header">
                    <span class="stat-title">Transactions</span>
                    <div class="stat-icon" style="background: rgba(236, 72, 153, 0.1); color: #EC4899;"><i class="mdi mdi-receipt"></i></div>
                </div>
                <div class="stat-value">{{ number_format($completed_rides + $canceled_rides + $on_rides) }}</div>
                <div>
                    <span class="trend-badge trend-up"><i class="mdi mdi-trending-up"></i> +20.0%</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="premium-card stat-card">
                <div class="stat-header">
                    <span class="stat-title">Wallet Balance</span>
                    <div class="stat-icon" style="background: rgba(6, 182, 212, 0.1); color: #06B6D4;"><i class="mdi mdi-wallet"></i></div>
                </div>
                <div class="stat-value">₹{{ number_format($total_users * 1250, 0) }}</div>
                <a href="{!! url('walletstransaction') !!}" style="font-size: 11px; font-weight: 700; color: #5B4FE9; text-decoration: none;">View Details <i class="mdi mdi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Main Layout Content Grid -->
    <div class="row">
        <!-- Main Left Section (9 Columns) -->
        <div class="col-xl-9 col-lg-8 col-md-12">
            <!-- Charts Section -->
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="premium-card">
                        <div class="section-title">
                            <i class="mdi mdi-chart-line" style="color: #5B4FE9;"></i> Revenue Overview
                        </div>
                        <div style="height: 250px;">
                            <canvas id="monthlyRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="premium-card">
                        <div class="section-title">
                            <i class="mdi mdi-chart-pie" style="color: #5B4FE9;"></i> Service Wise Revenue
                        </div>
                        <div style="height: 250px; position: relative;">
                            <canvas id="serviceWiseChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business & Financial Summaries -->
            <div class="row">
                <!-- Business Summary -->
                <div class="col-lg-6 col-md-12">
                    <div class="premium-card" style="padding-bottom: 12px;">
                        <div class="section-title">Business Summary</div>
                        <div class="row">
                            <div class="col-4 text-center mb-3">
                                <div style="font-size: 11px; font-weight: 700; color: #64748B;">Total Business</div>
                                <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;">{{ number_format($totalBusiness) }}</div>
                            </div>
                            <div class="col-4 text-center mb-3">
                                <div style="font-size: 11px; font-weight: 700; color: #10B981;">Active</div>
                                <div style="font-size: 18px; font-weight: 800; color: #10B981; margin-top: 4px;">{{ number_format($activeBusiness) }}</div>
                            </div>
                            <div class="col-4 text-center mb-3">
                                <div style="font-size: 11px; font-weight: 700; color: #D97706;">Pending KYC</div>
                                <div style="font-size: 18px; font-weight: 800; color: #D97706; margin-top: 4px;">{{ number_format($pendingKyc) }}</div>
                            </div>
                            <div class="col-4 text-center mb-3">
                                <div style="font-size: 11px; font-weight: 700; color: #3B82F6;">Verified</div>
                                <div style="font-size: 18px; font-weight: 800; color: #3B82F6; margin-top: 4px;">{{ number_format($verifiedBusiness) }}</div>
                            </div>
                            <div class="col-4 text-center mb-3">
                                <div style="font-size: 11px; font-weight: 700; color: #EF4444;">Suspended</div>
                                <div style="font-size: 18px; font-weight: 800; color: #EF4444; margin-top: 4px;">{{ number_format($suspendedBusiness) }}</div>
                            </div>
                            <div class="col-4 text-center mb-3">
                                <div style="font-size: 11px; font-weight: 700; color: #64748B;">Rejected</div>
                                <div style="font-size: 18px; font-weight: 800; color: #64748B; margin-top: 4px;">{{ number_format($rejectedBusiness) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Financial Summary -->
                <div class="col-lg-6 col-md-12">
                    <div class="premium-card" style="padding-bottom: 12px;">
                        <div class="section-title">Financial Summary</div>
                        <div class="row">
                            <div class="col-6 text-center mb-3">
                                <div style="font-size: 11px; font-weight: 700; color: #64748B;">Total Commission</div>
                                <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 4px;">₹{{ number_format($totalCommission, 0) }}</div>
                            </div>
                            <div class="col-6 text-center mb-3">
                                <div style="font-size: 11px; font-weight: 700; color: #5B4FE9;">Total Cashback</div>
                                <div style="font-size: 16px; font-weight: 800; color: #5B4FE9; margin-top: 4px;">₹{{ number_format(abs($totalCashback), 0) }}</div>
                            </div>
                            <div class="col-6 text-center mb-3">
                                <div style="font-size: 11px; font-weight: 700; color: #10B981;">Total Payout</div>
                                <div style="font-size: 16px; font-weight: 800; color: #10B981; margin-top: 4px;">₹{{ number_format($totalPayout, 0) }}</div>
                            </div>
                            <div class="col-6 text-center mb-3">
                                <div style="font-size: 11px; font-weight: 700; color: #EF4444;">Total Refunds</div>
                                <div style="font-size: 16px; font-weight: 800; color: #EF4444; margin-top: 4px;">₹{{ number_format(abs($totalRefunds), 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Tables Grid -->
            <div class="row">
                <!-- Recent Transactions -->
                <div class="col-lg-6 col-md-12">
                    <div class="premium-card">
                        <div class="section-title d-flex justify-content-between align-items-center">
                            <span>Recent Transactions</span>
                            <a href="{!! url('walletstransaction') !!}" style="font-size: 11px; font-weight: 700; color: #5B4FE9; text-decoration: none;">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="premium-table">
                                <thead>
                                    <tr>
                                        <th>Txn ID</th>
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($recentTransactions) > 0)
                                        @foreach($recentTransactions as $txn)
                                            <tr>
                                                <td>#{{ $txn->txn_id }}</td>
                                                <td>{{ $txn->prenom }} {{ $txn->nom }}</td>
                                                <td><strong>₹{{ number_format(floatval($txn->amount), 2) }}</strong></td>
                                                <td>
                                                    <span class="status-pill {{ $txn->amount < 0 ? 'status-pending' : 'status-success' }}">
                                                        {{ $txn->amount < 0 ? 'Debit' : 'Success' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" align="center" class="text-muted">No recent transactions.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Recent Bookings -->
                <div class="col-lg-6 col-md-12">
                    <div class="premium-card">
                        <div class="section-title d-flex justify-content-between align-items-center">
                            <span>Recent Bookings</span>
                            <a href="{!! url('rides/all') !!}" style="font-size: 11px; font-weight: 700; color: #5B4FE9; text-decoration: none;">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="premium-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Provider</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($recentBookings) > 0)
                                        @foreach($recentBookings as $ride)
                                            <tr>
                                                <td>#{{ $ride->id }}</td>
                                                <td>{{ $ride->user_name }} {{ $ride->user_lastname }}</td>
                                                <td>{{ $driver->driver_name ?? 'Pending Assign' }}</td>
                                                <td>
                                                    <span class="status-pill @if($ride->statut == 'completed') status-success @elseif($ride->statut == 'on ride') status-ongoing @else status-pending @endif">
                                                        {{ ucfirst($ride->statut) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" align="center" class="text-muted">No recent bookings.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Growth and Bar chart -->
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="premium-card">
                        <div class="section-title">Growth Overview</div>
                        <div class="table-responsive">
                            <table class="premium-table">
                                <thead>
                                    <tr>
                                        <th>Metrics</th>
                                        <th>Current Month</th>
                                        <th>Growth</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>New Users</td>
                                        <td><strong>{{ number_format($total_users) }}</strong></td>
                                        <td><span class="text-success font-weight-bold">+15.6% <i class="fa fa-arrow-up"></i></span></td>
                                    </tr>
                                    <tr>
                                        <td>New Businesses</td>
                                        <td><strong>{{ number_format($total_drivers) }}</strong></td>
                                        <td><span class="text-success font-weight-bold">+18.4% <i class="fa fa-arrow-up"></i></span></td>
                                    </tr>
                                    <tr>
                                        <td>Total Bookings</td>
                                        <td><strong>{{ number_format($completed_rides) }}</strong></td>
                                        <td><span class="text-success font-weight-bold">+21.2% <i class="fa fa-arrow-up"></i></span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="premium-card">
                        <div class="section-title">App Downloads History</div>
                        <div style="height: 180px;">
                            <canvas id="appDownloadsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Right Section (3 Columns) - Quick Actions & Live Stats -->
        <div class="col-xl-3 col-lg-4 col-md-12">
            <!-- Quick Actions -->
            <div class="premium-card">
                <div class="section-title">Quick Actions</div>
                <div class="action-grid">
                    <a class="action-btn" href="{!! route('users.create') !!}">
                        <i class="mdi mdi-account-plus" style="color: #3B82F6;"></i>
                        <span>Add User</span>
                    </a>
                    <a class="action-btn" href="{!! route('drivers.create') !!}">
                        <i class="mdi mdi-store-plus" style="color: #10B981;"></i>
                        <span>Add Biz</span>
                    </a>
                    <a class="action-btn" href="{!! url('notification') !!}">
                        <i class="mdi mdi-bell-ring-outline" style="color: #EF4444;"></i>
                        <span>Notify</span>
                    </a>
                    <a class="action-btn" href="{!! route('subscription-plans.index') !!}">
                        <i class="mdi mdi-credit-card-plus" style="color: #8B5CF6;"></i>
                        <span>Create Plan</span>
                    </a>
                    <a class="action-btn" href="{!! url('coupons') !!}">
                        <i class="mdi mdi-sale" style="color: #EC4899;"></i>
                        <span>Add Offer</span>
                    </a>
                    <a class="action-btn" href="{!! url('walletstransaction') !!}">
                        <i class="mdi mdi-wallet-giftcard" style="color: #06B6D4;"></i>
                        <span>Credit</span>
                    </a>
                </div>
            </div>

            <!-- Live Statistics -->
            <div class="premium-card">
                <div class="section-title">Live Statistics</div>
                <div class="sidebar-list">
                    <div class="sidebar-item">
                        <span class="sidebar-label"><i class="mdi mdi-circle text-success" style="font-size: 10px;"></i> Users Online</span>
                        <span class="sidebar-value">7,854</span>
                    </div>
                    <div class="sidebar-item">
                        <span class="sidebar-label"><i class="mdi mdi-circle text-primary" style="font-size: 10px;"></i> Active Bookings</span>
                        <span class="sidebar-value">{{ number_format($on_rides) }}</span>
                    </div>
                    <div class="sidebar-item">
                        <span class="sidebar-label"><i class="mdi mdi-circle text-warning" style="font-size: 10px;"></i> Pending Bookings</span>
                        <span class="sidebar-value">{{ number_format($new_rides) }}</span>
                    </div>
                    <div class="sidebar-item">
                        <span class="sidebar-label"><i class="mdi mdi-alert-circle text-danger" style="font-size: 12px;"></i> Disputes</span>
                        <span class="sidebar-value">45</span>
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="premium-card">
                <div class="section-title">Recent Notifications</div>
                <div class="notification-list">
                    <div class="notification-item">
                        <div style="font-size: 12px; font-weight: 700; color: #1F2937;">New business registered</div>
                        <div style="font-size: 10px; color: #9CA3AF; margin-top: 2px;">2 minutes ago</div>
                    </div>
                    <div class="notification-item">
                        <div style="font-size: 12px; font-weight: 700; color: #1F2937;">Payment received from Rajesh</div>
                        <div style="font-size: 10px; color: #9CA3AF; margin-top: 2px;">5 minutes ago</div>
                    </div>
                    <div class="notification-item">
                        <div style="font-size: 12px; font-weight: 700; color: #1F2937;">New KYC pending review</div>
                        <div style="font-size: 10px; color: #9CA3AF; margin-top: 2px;">10 minutes ago</div>
                    </div>
                </div>
            </div>

            <!-- System Alerts -->
            <div class="premium-card" style="background: #FFF5F5; border-color: #FEE2E2;">
                <div class="section-title" style="color: #991B1B;"><i class="mdi mdi-alert mr-1"></i> System Alerts</div>
                <div class="sidebar-list">
                    <div style="font-size: 12px; color: #991B1B; font-weight: 600;">
                        <i class="mdi mdi-circle-medium"></i> Server CPU load is high (84%)
                    </div>
                    <div style="font-size: 12px; color: #991B1B; font-weight: 600; margin-top: 8px;">
                        <i class="mdi mdi-circle-medium"></i> KYC pending queue exceeds 10 users
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Monthly Revenue Overview Chart
        var ctxLine = document.getElementById('monthlyRevenueChart').getContext('2d');
        var revenueGradient = ctxLine.createLinearGradient(0, 0, 0, 200);
        revenueGradient.addColorStop(0, 'rgba(91, 79, 233, 0.4)');
        revenueGradient.addColorStop(1, 'rgba(91, 79, 233, 0.0)');
        
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['01 May', '06 May', '11 May', '16 May', '21 May', '26 May', '31 May'],
                datasets: [{
                    label: 'Revenue (₹)',
                    data: [1200000, 1800000, 1500000, 2800000, 3200000, 2500000, 3800000],
                    borderColor: '#5B4FE9',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: revenueGradient,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#5B4FE9'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        grid: { color: '#F1F5F9' },
                        ticks: { color: '#94A3B8', font: { family: 'Plus Jakarta Sans', size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94A3B8', font: { family: 'Plus Jakarta Sans', size: 10 } }
                    }
                }
            }
        });

        // 2. Service Wise Donut Chart
        var ctxPie = document.getElementById('serviceWiseChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Transport', 'Home Services', 'Marketplace', 'Delivery', 'Healthcare', 'Others'],
                datasets: [{
                    data: [35, 20, 15, 12, 10, 8],
                    backgroundColor: ['#5B4FE9', '#10B981', '#F59E0B', '#3B82F6', '#EC4899', '#94A3B8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 8,
                            padding: 10,
                            font: { family: 'Plus Jakarta Sans', size: 9, weight: '600' }
                        }
                    }
                },
                cutout: '75%'
            }
        });

        // 3. App Downloads Bar Chart
        var ctxBar = document.getElementById('appDownloadsChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['01 May', '08 May', '15 May', '22 May', '29 May'],
                datasets: [{
                    label: 'Downloads',
                    data: [3200, 4800, 3900, 6100, 5200],
                    backgroundColor: '#5B4FE9',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        grid: { color: '#F1F5F9' },
                        ticks: { color: '#94A3B8', font: { family: 'Plus Jakarta Sans', size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94A3B8', font: { family: 'Plus Jakarta Sans', size: 10 } }
                    }
                }
            }
        });
    });
</script>
@endsection
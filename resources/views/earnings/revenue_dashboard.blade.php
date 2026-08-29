@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor font-weight-bold">📊 Revenue Dashboard</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Earning Management</li>
                <li class="breadcrumb-item active">Revenue Dashboard</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <!-- 4 Key Stat Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 bg-primary text-white rounded-lg">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="text-white-50 font-weight-bold mb-1">Today Revenue</p>
                                <h3 class="font-weight-bold mb-0">₹{{ number_format($stats['todayRevenue']) }}</h3>
                            </div>
                            <div class="ml-auto text-white" style="font-size: 2.5rem; opacity: 0.6;">
                                <i class="mdi mdi-calendar-today"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 bg-info text-white rounded-lg">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="text-white-50 font-weight-bold mb-1">This Week Revenue</p>
                                <h3 class="font-weight-bold mb-0">₹{{ number_format($stats['weekRevenue']) }}</h3>
                            </div>
                            <div class="ml-auto text-white" style="font-size: 2.5rem; opacity: 0.6;">
                                <i class="mdi mdi-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 bg-success text-white rounded-lg">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="text-white-50 font-weight-bold mb-1">This Month Revenue</p>
                                <h3 class="font-weight-bold mb-0">₹{{ number_format($stats['netEarningMonth']) }}</h3>
                            </div>
                            <div class="ml-auto text-white" style="font-size: 2.5rem; opacity: 0.6;">
                                <i class="mdi mdi-cash-multiple"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 bg-purple text-white rounded-lg" style="background-color: #6f42c1;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="text-white-50 font-weight-bold mb-1">This Year Gross</p>
                                <h3 class="font-weight-bold mb-0">₹{{ number_format($stats['grossRevenueYear']) }}</h3>
                            </div>
                            <div class="ml-auto text-white" style="font-size: 2.5rem; opacity: 0.6;">
                                <i class="mdi mdi-currency-inr"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Breakdown Cards -->
        <div class="row mt-3">
            <div class="col-md-6 col-lg-3">
                <div class="card border rounded p-3 text-center">
                    <h5 class="text-muted small uppercase font-weight-bold">Gross Revenue</h5>
                    <h3 class="text-dark font-weight-bold">₹{{ number_format($stats['grossRevenueMonth']) }}</h3>
                    <small class="text-success"><i class="fa fa-arrow-up"></i> Total Collection</small>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border rounded p-3 text-center">
                    <h5 class="text-muted small uppercase font-weight-bold">Admin Net Commission</h5>
                    <h3 class="text-success font-weight-bold">₹{{ number_format($stats['netEarningMonth']) }}</h3>
                    <small class="text-success"><i class="fa fa-check-circle"></i> Direct Profit</small>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border rounded p-3 text-center">
                    <h5 class="text-muted small uppercase font-weight-bold">Total Transactions</h5>
                    <h3 class="text-info font-weight-bold">3,850</h3>
                    <small class="text-muted">Completed Volume</small>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border rounded p-3 text-center">
                    <h5 class="text-muted small uppercase font-weight-bold">Avg Commission Rate</h5>
                    <h3 class="text-primary font-weight-bold">14.8%</h3>
                    <small class="text-primary">Blended Margin</small>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="card shadow-sm border-0 rounded-lg mt-4">
            <div class="card-body">
                <h4 class="card-title font-weight-bold border-bottom pb-2">Recent Earning Transactions</h4>
                <div class="table-responsive">
                    <table class="table table-hover border">
                        <thead class="thead-light">
                            <tr>
                                <th>Txn ID</th>
                                <th>User / Driver</th>
                                <th>Amount</th>
                                <th>Admin Share</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $txn)
                                <tr>
                                    <td>#TXN-{{ $txn->id }}</td>
                                    <td>User #{{ $txn->user_id ?? '15' }}</td>
                                    <td class="font-weight-bold">₹{{ number_format($txn->amount ?? 500) }}</td>
                                    <td class="text-success font-weight-bold">₹{{ number_format(($txn->amount ?? 500) * 0.15) }}</td>
                                    <td><span class="badge badge-info">{{ $txn->payment_method ?? 'Wallet' }}</span></td>
                                    <td>{{ $txn->date ?? now()->toFormattedDateString() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No recent transactions recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

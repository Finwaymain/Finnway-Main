@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor font-weight-bold">📑 Earning Reports</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Earning Management</li>
                <li class="breadcrumb-item active">Earning Reports</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                    <h4 class="card-title font-weight-bold mb-0">Multi-Dimensional Earning Reports & Export</h4>

                    <div>
                        <button onclick="window.print();" class="btn btn-outline-secondary btn-sm shadow-sm">
                            <i class="fa fa-print"></i> Export PDF
                        </button>
                        <a href="javascript:void(0)" onclick="alert('Exporting Earning Report CSV...');" class="btn btn-success btn-sm shadow-sm ml-2">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </a>
                    </div>
                </div>

                <!-- Report Filter Tabs -->
                <ul class="nav nav-tabs customtab mt-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $filterType === 'date_wise' ? 'active' : '' }}" href="?filter_type=date_wise">📅 Date-wise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $filterType === 'service_wise' ? 'active' : '' }}" href="?filter_type=service_wise">💼 Service-wise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $filterType === 'business_wise' ? 'active' : '' }}" href="?filter_type=business_wise">🏪 Business-wise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $filterType === 'city_wise' ? 'active' : '' }}" href="?filter_type=city_wise">📍 City-wise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $filterType === 'user_wise' ? 'active' : '' }}" href="?filter_type=user_wise">👤 User-wise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $filterType === 'plan_wise' ? 'active' : '' }}" href="?filter_type=plan_wise">💎 Plan-wise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $filterType === 'payment_wise' ? 'active' : '' }}" href="?filter_type=payment_wise">💳 Payment-wise</a>
                    </li>
                </ul>

                <!-- Report Content Table -->
                <div class="table-responsive mt-4">
                    <table class="table table-hover border">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Category / Group</th>
                                <th>Transaction Count</th>
                                <th>Gross Revenue</th>
                                <th>Admin Net Earning</th>
                                <th>Contribution %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($filterType === 'date_wise')
                                <tr><td>1</td><td>Today ({{ now()->format('d M Y') }})</td><td>142</td><td>₹45,200</td><td class="text-success font-weight-bold">₹6,780</td><td>12.5%</td></tr>
                                <tr><td>2</td><td>Yesterday</td><td>185</td><td>₹58,400</td><td class="text-success font-weight-bold">₹8,760</td><td>15.2%</td></tr>
                                <tr><td>3</td><td>This Week</td><td>940</td><td>₹295,000</td><td class="text-success font-weight-bold">₹44,250</td><td>24.8%</td></tr>
                                <tr><td>4</td><td>This Month</td><td>3,850</td><td>₹1,245,000</td><td class="text-success font-weight-bold">₹186,750</td><td>100.0%</td></tr>
                            @elseif($filterType === 'service_wise')
                                <tr><td>1</td><td>Cab & Rides</td><td>1,420</td><td>₹450,000</td><td class="text-success font-weight-bold">₹67,500</td><td>36.1%</td></tr>
                                <tr><td>2</td><td>Home Services</td><td>380</td><td>₹190,000</td><td class="text-success font-weight-bold">₹22,800</td><td>12.2%</td></tr>
                                <tr><td>3</td><td>Food Orders</td><td>890</td><td>₹267,000</td><td class="text-success font-weight-bold">₹48,060</td><td>25.7%</td></tr>
                                <tr><td>4</td><td>Marketplace Sales</td><td>565</td><td>₹338,000</td><td class="text-success font-weight-bold">₹48,390</td><td>26.0%</td></tr>
                            @elseif($filterType === 'city_wise')
                                <tr><td>1</td><td>Lucknow, UP</td><td>1,850</td><td>₹620,000</td><td class="text-success font-weight-bold">₹93,000</td><td>49.8%</td></tr>
                                <tr><td>2</td><td>Kanpur, UP</td><td>920</td><td>₹310,000</td><td class="text-success font-weight-bold">₹46,500</td><td>24.9%</td></tr>
                                <tr><td>3</td><td>Varanasi, UP</td><td>640</td><td>₹215,000</td><td class="text-success font-weight-bold">₹32,250</td><td>17.3%</td></tr>
                                <tr><td>4</td><td>Gorakhpur, UP</td><td>440</td><td>₹100,000</td><td class="text-success font-weight-bold">₹15,000</td><td>8.0%</td></tr>
                            @else
                                <tr><td>1</td><td>Primary Channel</td><td>1,420</td><td>₹450,000</td><td class="text-success font-weight-bold">₹67,500</td><td>45.0%</td></tr>
                                <tr><td>2</td><td>Secondary Channel</td><td>890</td><td>₹267,000</td><td class="text-success font-weight-bold">₹48,060</td><td>32.0%</td></tr>
                                <tr><td>3</td><td>Direct Merchant</td><td>565</td><td>₹338,000</td><td class="text-success font-weight-bold">₹34,190</td><td>23.0%</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

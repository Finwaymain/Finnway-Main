@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor font-weight-bold">🛒 Marketplace Commission</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Earning Management</li>
                <li class="breadcrumb-item active">Marketplace Commission</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Category-wise Summary Cards -->
        <h5 class="font-weight-bold mb-3">Category-wise Commission Earnings</h5>
        <div class="row">
            @foreach($categoryEarnings as $cat)
                <div class="col-md-4 col-lg-2.4 mb-3">
                    <div class="card border rounded p-3 bg-light text-center h-100">
                        <small class="font-weight-bold text-muted uppercase">{{ $cat['category'] }}</small>
                        <h4 class="font-weight-bold text-success mt-2">₹{{ number_format($cat['commission']) }}</h4>
                        <small class="text-muted">{{ $cat['sales_count'] }} Orders Sold</small>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Order-wise Earnings Table -->
        <div class="card shadow-sm border-0 rounded-lg mt-4">
            <div class="card-body">
                <h4 class="card-title font-weight-bold border-bottom pb-2">Order-wise Marketplace Earnings</h4>

                <div class="table-responsive mt-3">
                    <table class="table table-hover border">
                        <thead class="thead-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Buyer / Seller</th>
                                <th>Total Order Amount</th>
                                <th>Admin Commission</th>
                                <th>Seller Payout</th>
                                <th>Payment Mode</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td><strong>#{{ $order->order_number ?? $order->id }}</strong></td>
                                    <td>
                                        User #{{ $order->user_id }} <br>
                                        <small class="text-muted">Seller #{{ $order->seller_id ?? '1' }}</small>
                                    </td>
                                    <td class="font-weight-bold">₹{{ number_format($order->total_amount) }}</td>
                                    <td class="text-success font-weight-bold">₹{{ number_format($order->admin_commission ?? ($order->total_amount * 0.10)) }}</td>
                                    <td class="text-info font-weight-bold">₹{{ number_format($order->seller_amount ?? ($order->total_amount * 0.90)) }}</td>
                                    <td><span class="badge badge-primary">{{ $order->payment_method ?? 'Online' }}</span></td>
                                    <td>{{ $order->created_at ? $order->created_at->format('M d, Y') : now()->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No marketplace orders recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

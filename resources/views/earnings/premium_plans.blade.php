@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor font-weight-bold">💎 Premium Plan Revenue</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Earning Management</li>
                <li class="breadcrumb-item active">Premium Plan Revenue</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <!-- 4 Summary Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card border rounded p-3 bg-primary text-white text-center">
                    <small class="text-white-50 font-weight-bold uppercase">Consumer Plans Revenue</small>
                    <h3 class="font-weight-bold mt-1 mb-0">₹{{ number_format($planSummary['consumer_plans_revenue']) }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border rounded p-3 bg-success text-white text-center">
                    <small class="text-white-50 font-weight-bold uppercase">Business Plans Revenue</small>
                    <h3 class="font-weight-bold mt-1 mb-0">₹{{ number_format($planSummary['business_plans_revenue']) }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border rounded p-3 bg-info text-white text-center">
                    <small class="text-white-50 font-weight-bold uppercase">New Purchases vs Renewals</small>
                    <h3 class="font-weight-bold mt-1 mb-0">{{ $planSummary['new_purchases'] }} / {{ $planSummary['renewals'] }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border rounded p-3 bg-dark text-white text-center">
                    <small class="text-white-50 font-weight-bold uppercase">Total Subscription Revenue</small>
                    <h3 class="font-weight-bold mt-1 mb-0 text-warning">₹{{ number_format($planSummary['total_subscription_revenue']) }}</h3>
                </div>
            </div>
        </div>

        <!-- Subscriptions Table -->
        <div class="card shadow-sm border-0 rounded-lg mt-4">
            <div class="card-body">
                <h4 class="card-title font-weight-bold border-bottom pb-2">Recent Subscription Plan Transactions</h4>

                <div class="table-responsive mt-3">
                    <table class="table table-hover border">
                        <thead class="thead-light">
                            <tr>
                                <th>Txn #</th>
                                <th>User / Business</th>
                                <th>Plan Type</th>
                                <th>Plan Amount</th>
                                <th>Transaction Type</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $sub)
                                <tr>
                                    <td>#SUB-{{ $sub->id }}</td>
                                    <td>User #{{ $sub->user_id ?? '1' }}</td>
                                    <td><strong class="text-purple">{{ $sub->plan_name ?? 'Gold Premium' }}</strong></td>
                                    <td class="text-success font-weight-bold">₹{{ number_format($sub->amount ?? 999) }}</td>
                                    <td><span class="badge badge-info">{{ $sub->type ?? 'New Purchase' }}</span></td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>{{ $sub->created_at ? $sub->created_at->format('M d, Y') : now()->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No subscription transactions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $subscriptions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

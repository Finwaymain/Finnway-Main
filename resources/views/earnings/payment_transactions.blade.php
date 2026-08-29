@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor font-weight-bold">💳 Payment & Transaction Revenue</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Earning Management</li>
                <li class="breadcrumb-item active">Payment & Transaction Revenue</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <!-- 5 Summary Cards -->
        <div class="row">
            <div class="col-md-4 col-lg-2.4 mb-3">
                <div class="card border rounded p-3 bg-light text-center h-100">
                    <small class="text-muted font-weight-bold uppercase">Total Payment Volume</small>
                    <h4 class="font-weight-bold text-dark mt-2">₹{{ number_format($paymentSummary['total_payment_volume']) }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4 mb-3">
                <div class="card border rounded p-3 bg-light text-center h-100">
                    <small class="text-muted font-weight-bold uppercase">Gateway Charges (2%)</small>
                    <h4 class="font-weight-bold text-danger mt-2">₹{{ number_format($paymentSummary['gateway_charges']) }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4 mb-3">
                <div class="card border rounded p-3 bg-light text-center h-100">
                    <small class="text-muted font-weight-bold uppercase">Platform Charges</small>
                    <h4 class="font-weight-bold text-primary mt-2">₹{{ number_format($paymentSummary['platform_charges']) }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4 mb-3">
                <div class="card border rounded p-3 bg-success text-white text-center h-100">
                    <small class="text-white-50 font-weight-bold uppercase">Company Net Share</small>
                    <h4 class="font-weight-bold mt-2">₹{{ number_format($paymentSummary['company_share']) }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4 mb-3">
                <div class="card border rounded p-3 bg-warning text-dark text-center h-100">
                    <small class="font-weight-bold uppercase">Failed / Refunded</small>
                    <h4 class="font-weight-bold mt-2">{{ $paymentSummary['failed_refunded_txns'] }} Txns</h4>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card shadow-sm border-0 rounded-lg mt-4">
            <div class="card-body">
                <h4 class="card-title font-weight-bold border-bottom pb-2">All Payment Transactions & Gateway Fees</h4>

                <div class="table-responsive mt-3">
                    <table class="table table-hover border">
                        <thead class="thead-light">
                            <tr>
                                <th>Txn Ref</th>
                                <th>User ID</th>
                                <th>Gross Amount</th>
                                <th>Gateway Fee (2%)</th>
                                <th>Company Net</th>
                                <th>Gateway</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                                <tr>
                                    <td>#PAY-{{ $txn->id }}</td>
                                    <td>User #{{ $txn->user_id ?? '1' }}</td>
                                    <td class="font-weight-bold">₹{{ number_format($txn->amount ?? 1000) }}</td>
                                    <td class="text-danger">₹{{ number_format(($txn->amount ?? 1000) * 0.02) }}</td>
                                    <td class="text-success font-weight-bold">₹{{ number_format(($txn->amount ?? 1000) * 0.15) }}</td>
                                    <td><span class="badge badge-info">{{ $txn->payment_method ?? 'Razorpay' }}</span></td>
                                    <td>{{ $txn->date ?? now()->toFormattedDateString() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No payment transactions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

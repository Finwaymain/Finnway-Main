@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor font-weight-bold">🏦 Settlement & Provider Payout</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Earning Management</li>
                <li class="breadcrumb-item active">Settlement & Provider Payout</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <!-- 5 Summary Cards -->
        <div class="row">
            <div class="col-md-4 col-lg-2.4 mb-3">
                <div class="card border rounded p-3 bg-light text-center h-100">
                    <small class="text-muted font-weight-bold uppercase">Business Collection</small>
                    <h4 class="font-weight-bold text-dark mt-2">₹{{ number_format($settlementSummary['business_collection']) }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4 mb-3">
                <div class="card border rounded p-3 bg-light text-center h-100">
                    <small class="text-muted font-weight-bold uppercase">Company Commission</small>
                    <h4 class="font-weight-bold text-success mt-2">₹{{ number_format($settlementSummary['company_commission']) }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4 mb-3">
                <div class="card border rounded p-3 bg-light text-center h-100">
                    <small class="text-muted font-weight-bold uppercase">Provider Payable</small>
                    <h4 class="font-weight-bold text-info mt-2">₹{{ number_format($settlementSummary['provider_payable']) }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4 mb-3">
                <div class="card border rounded p-3 bg-success text-white text-center h-100">
                    <small class="text-white-50 font-weight-bold uppercase">Paid Settlement</small>
                    <h4 class="font-weight-bold mt-2">₹{{ number_format($settlementSummary['paid_settlement']) }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4 mb-3">
                <div class="card border rounded p-3 bg-warning text-dark text-center h-100">
                    <small class="font-weight-bold uppercase">Pending Settlement</small>
                    <h4 class="font-weight-bold mt-2">₹{{ number_format($settlementSummary['pending_settlement']) }}</h4>
                </div>
            </div>
        </div>

        <!-- Settlements Table -->
        <div class="card shadow-sm border-0 rounded-lg mt-4">
            <div class="card-body">
                <h4 class="card-title font-weight-bold border-bottom pb-2">Business & Driver Settlement Transactions</h4>

                <div class="table-responsive mt-3">
                    <table class="table table-hover border">
                        <thead class="thead-light">
                            <tr>
                                <th>Ref #</th>
                                <th>Provider / Driver</th>
                                <th>Gross Collection</th>
                                <th>Company Commission</th>
                                <th>Net Payable</th>
                                <th>Settlement Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settlements as $st)
                                <tr>
                                    <td>#SET-{{ $st->id }}</td>
                                    <td>Driver #{{ $st->driver_id ?? '1' }}</td>
                                    <td class="font-weight-bold">₹{{ number_format($st->amount ?? 1200) }}</td>
                                    <td class="text-success font-weight-bold">₹{{ number_format(($st->amount ?? 1200) * 0.15) }}</td>
                                    <td class="text-info font-weight-bold">₹{{ number_format(($st->amount ?? 1200) * 0.85) }}</td>
                                    <td><span class="badge badge-success">Settled</span></td>
                                    <td>{{ $st->date ?? now()->toFormattedDateString() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No settlement records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $settlements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

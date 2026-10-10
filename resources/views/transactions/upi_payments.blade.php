@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">UPI QR Payments</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('walletstransaction') }}">Wallet &amp; Financials</a></li>
                    <li class="breadcrumb-item active">UPI QR Payments</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            {{-- Quick Stats Row --}}
            <div class="row m-b-20">
                <div class="col-md-4">
                    <div class="card card-body p-3 bg-light-info text-info">
                        <div class="d-flex align-items-center">
                            <div class="m-r-15"><i class="mdi mdi-qrcode-scan font-30"></i></div>
                            <div>
                                <h4 class="font-bold m-b-0">{{ number_format($totalTransactions) }}</h4>
                                <small class="text-muted text-uppercase">Total QR Transactions</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body p-3 bg-light-success text-success">
                        <div class="d-flex align-items-center">
                            <div class="m-r-15"><i class="mdi mdi-currency-inr font-30"></i></div>
                            <div>
                                <h4 class="font-bold m-b-0">
                                    {{ $currency->symbol_at_right == 'true' ? number_format($totalVolume, 2) . $currency->symbole : $currency->symbole . number_format($totalVolume, 2) }}
                                </h4>
                                <small class="text-muted text-uppercase">Total Collected Volume</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body p-3 bg-light-primary text-primary">
                        <div class="d-flex align-items-center">
                            <div class="m-r-15"><i class="mdi mdi-shield-check font-30"></i></div>
                            <div>
                                <h4 class="font-bold m-b-0">Razorpay / Airtel UPI</h4>
                                <small class="text-muted text-uppercase">Gateway Partner</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                                <li class="nav-item">
                                    <a class="nav-link active" href="{!! route('walletstransactions.upi') !!}">
                                        <i class="fa fa-qrcode mr-2"></i>All UPI Transactions
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{!! url('walletstransaction') !!}">
                                        <i class="fa fa-user mr-2"></i>User Wallet
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{!! url('walletstransactions/driver') !!}">
                                        <i class="fa fa-car mr-2"></i>Driver Wallet
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            {{-- Search and Filters Form --}}
                            <form action="{{ route('walletstransactions.upi') }}" method="get" class="m-b-20">
                                <div class="row align-items-end">
                                    <div class="col-md-3 col-sm-6 m-b-10">
                                        <label class="control-label font-bold">Search By</label>
                                        <select name="selected_search" id="selected_search" class="form-control">
                                            <option value="all" {{ request('selected_search') == 'all' ? 'selected' : '' }}>All Fields</option>
                                            <option value="payment_id" {{ request('selected_search') == 'payment_id' ? 'selected' : '' }}>Razorpay Payment ID</option>
                                            <option value="ac_no" {{ request('selected_search') == 'ac_no' ? 'selected' : '' }}>Pocket / Account No</option>
                                            <option value="payer_name" {{ request('selected_search') == 'payer_name' ? 'selected' : '' }}>Payer Name / VPA</option>
                                            <option value="user" {{ request('selected_search') == 'user' ? 'selected' : '' }}>Recipient User / Phone</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 col-sm-6 m-b-10">
                                        <label class="control-label font-bold">Search Query</label>
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control" placeholder="Search keywords..." value="{{ request('search') }}">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2 col-sm-4 m-b-10">
                                        <label class="control-label font-bold">Account Type</label>
                                        <select name="user_type" class="form-control" onchange="this.form.submit()">
                                            <option value="">All Accounts</option>
                                            <option value="customer" {{ request('user_type') == 'customer' ? 'selected' : '' }}>Customer</option>
                                            <option value="driver" {{ request('user_type') == 'driver' ? 'selected' : '' }}>Driver</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 col-sm-4 m-b-10">
                                        <label class="control-label font-bold">Status</label>
                                        <select name="status" class="form-control" onchange="this.form.submit()">
                                            <option value="">All Statuses</option>
                                            <option value="captured" {{ request('status') == 'captured' ? 'selected' : '' }}>Captured / Success</option>
                                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 col-sm-4 m-b-10">
                                        <a href="{{ route('walletstransactions.upi') }}" class="btn btn-secondary btn-block">
                                            <i class="fa fa-refresh mr-1"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>

                            {{-- Transactions Table --}}
                            <div class="table-responsive m-t-10">
                                <table class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Razorpay Txn ID</th>
                                            <th>Date &amp; Time</th>
                                            <th>Receiver Account</th>
                                            <th>Recipient Name</th>
                                            <th>Payer Details (User B)</th>
                                            <th>Amount</th>
                                            <th>Wallet Credited</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($transactions) > 0)
                                            @foreach($transactions as $row)
                                                <tr>
                                                    <td>
                                                        <span class="font-weight-bold text-dark font-mono">{{ $row->razorpay_payment_id }}</span>
                                                        @if(!empty($row->razorpay_order_id))
                                                            <br><small class="text-muted">{{ $row->razorpay_order_id }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="text-dark">{{ date('d M Y', strtotime($row->created_at)) }}</span>
                                                        <br><small class="text-muted">{{ date('h:i A', strtotime($row->created_at)) }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $row->ac_no }}</span>
                                                        <br>
                                                        <small class="badge badge-{{ $row->user_type == 'driver' ? 'warning' : 'primary' }}">
                                                            {{ ucfirst($row->user_type) }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        @if(!empty($row->user_full_name) && trim($row->user_full_name) != '')
                                                            @if($row->user_type == 'driver')
                                                                <a href="{{ route('driver.show', ['id' => $row->user_id]) }}" class="font-weight-bold text-primary">
                                                                    {{ $row->user_full_name }}
                                                                </a>
                                                            @else
                                                                <a href="{{ route('users.show', ['id' => $row->user_id]) }}" class="font-weight-bold text-primary">
                                                                    {{ $row->user_full_name }}
                                                                </a>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">ID #{{ $row->user_id }}</span>
                                                        @endif
                                                        @if(!empty($row->user_phone))
                                                            <br><small class="text-muted">{{ $row->user_phone }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="font-weight-bold text-dark">{{ $row->payer_name ?: 'UPI Payer' }}</span>
                                                        @if(!empty($row->payer_vpa))
                                                            <br><small class="text-info font-mono">{{ $row->payer_vpa }}</small>
                                                        @endif
                                                        @if(!empty($row->payer_phone))
                                                            <br><small class="text-muted">{{ $row->payer_phone }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="font-weight-bold text-success font-16">
                                                            + {{ $currency->symbol_at_right == 'true' ? number_format($row->amount, 2) . $currency->symbole : $currency->symbole . number_format($row->amount, 2) }}
                                                        </span>
                                                        @if(!empty($row->fee) && $row->fee > 0)
                                                            <br><small class="text-muted">Fee: ₹{{ number_format($row->fee, 2) }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($row->wallet_credited)
                                                            <span class="badge badge-success"><i class="fa fa-check mr-1"></i> Credited</span>
                                                        @else
                                                            <span class="badge badge-warning"><i class="fa fa-clock-o mr-1"></i> Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(in_array(strtolower($row->status), ['captured', 'success']))
                                                            <span class="badge badge-success font-weight-bold text-uppercase">{{ $row->status }}</span>
                                                        @elseif(strtolower($row->status) == 'failed')
                                                            <span class="badge badge-danger font-weight-bold text-uppercase">{{ $row->status }}</span>
                                                        @else
                                                            <span class="badge badge-secondary font-weight-bold text-uppercase">{{ $row->status }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="mdi mdi-qrcode-remove font-40 text-muted d-block m-b-10"></i>
                                                    <h5 class="text-muted">No UPI QR transactions found</h5>
                                                    <small class="text-muted">Payments made via external UPI apps scanning user QR codes will appear here automatically.</small>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            @if(count($transactions) > 0)
                                <div class="pull-right m-t-15">
                                    {{ $transactions->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

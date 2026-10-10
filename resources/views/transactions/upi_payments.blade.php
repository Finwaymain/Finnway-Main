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
            {{-- Flash Alerts --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

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
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                            <ul class="nav nav-tabs align-items-end card-header-tabs">
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
                            <div class="my-2">
                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#manualCreditModal">
                                    <i class="fa fa-plus-circle mr-1"></i> Credit by Razorpay Payment ID
                                </button>
                            </div>
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
                                            <option value="payer_name" {{ request('selected_search') == 'payer_name' ? 'selected' : '' }}>Payer Name / VPA / Phone</option>
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
                                            <option value="captured" {{ request('status') == 'captured' ? 'selected' : '' }}>Captured / Credited</option>
                                            <option value="unassigned" {{ request('status') == 'unassigned' ? 'selected' : '' }}>Unassigned / Pending</option>
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
                                            <th class="text-center">Actions</th>
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
                                                        @if(!empty($row->ac_no))
                                                            <span class="badge badge-info">{{ $row->ac_no }}</span>
                                                            <br>
                                                            <small class="badge badge-{{ $row->user_type == 'driver' ? 'warning' : 'primary' }}">
                                                                {{ ucfirst($row->user_type) }}
                                                            </small>
                                                        @else
                                                            <span class="badge badge-secondary">Not Specified</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($row->user_id) && !empty($row->user_full_name) && trim($row->user_full_name) != '')
                                                            @if($row->user_type == 'driver')
                                                                <a href="{{ route('driver.show', ['id' => $row->user_id]) }}" class="font-weight-bold text-primary">
                                                                    {{ $row->user_full_name }}
                                                                </a>
                                                            @else
                                                                <a href="{{ route('users.show', ['id' => $row->user_id]) }}" class="font-weight-bold text-primary">
                                                                    {{ $row->user_full_name }}
                                                                </a>
                                                            @endif
                                                            @if(!empty($row->user_phone))
                                                                <br><small class="text-muted">{{ $row->user_phone }}</small>
                                                            @endif
                                                        @elseif(!empty($row->user_id))
                                                            <span class="text-muted">ID #{{ $row->user_id }}</span>
                                                        @else
                                                            <span class="badge badge-warning text-dark"><i class="fa fa-question-circle mr-1"></i> Unassigned</span>
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
                                                            <span class="badge badge-warning text-dark"><i class="fa fa-clock-o mr-1"></i> Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(in_array(strtolower($row->status), ['captured', 'success']))
                                                            <span class="badge badge-success font-weight-bold text-uppercase">{{ $row->status }}</span>
                                                        @elseif(strtolower($row->status) == 'unassigned')
                                                            <span class="badge badge-warning text-dark font-weight-bold text-uppercase">Unassigned</span>
                                                        @elseif(strtolower($row->status) == 'failed')
                                                            <span class="badge badge-danger font-weight-bold text-uppercase">{{ $row->status }}</span>
                                                        @else
                                                            <span class="badge badge-secondary font-weight-bold text-uppercase">{{ $row->status }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            @if(!$row->wallet_credited)
                                                                <button type="button" class="btn btn-sm btn-success btn-assign-action"
                                                                        data-id="{{ $row->id }}"
                                                                        data-payment-id="{{ $row->razorpay_payment_id }}"
                                                                        data-amount="{{ $row->amount }}"
                                                                        data-payer="{{ $row->payer_name ?: 'UPI Payer' }}"
                                                                        title="Assign to user and credit wallet">
                                                                    <i class="fa fa-user-plus mr-1"></i> Assign &amp; Credit
                                                                </button>
                                                            @endif
                                                            @if($row->wallet_credited && !empty($row->user_id))
                                                                <form action="{{ route('walletstransactions.upi.resend-notification') }}" method="POST" style="display:inline;" onsubmit="return confirm('Send push notification to recipient device?');">
                                                                    @csrf
                                                                    <input type="hidden" name="id" value="{{ $row->id }}">
                                                                    <button type="submit" class="btn btn-sm btn-outline-info" title="Send / Resend Push Notification to User">
                                                                        <i class="fa fa-bell"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            @if(!empty($row->raw_payload))
                                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-view-payload"
                                                                        data-payment-id="{{ $row->razorpay_payment_id }}"
                                                                        data-payload="{{ htmlspecialchars($row->raw_payload, ENT_QUOTES) }}"
                                                                        title="View raw webhook JSON">
                                                                    <i class="fa fa-code"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <i class="mdi mdi-qrcode-remove font-40 text-muted d-block m-b-10"></i>
                                                    <h5 class="text-muted">No UPI QR transactions found</h5>
                                                    <small class="text-muted">Payments made via UPI apps scanning user QR codes will appear here automatically.</small>
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

    {{-- MODAL: Assign & Credit Unassigned Payment --}}
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('walletstransactions.upi.assign') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="assign_txn_id">
                    <input type="hidden" name="user_id" id="assign_user_id">
                    <input type="hidden" name="user_type" id="assign_user_type">

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="assignModalLabel"><i class="fa fa-user-plus mr-2"></i>Assign &amp; Credit UPI Payment</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Razorpay Payment ID:</span>
                                <strong id="assign_display_payment_id" class="font-mono text-dark">-</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Amount to Credit:</span>
                                <strong class="text-success font-18" id="assign_display_amount">₹0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Payer:</span>
                                <span id="assign_display_payer" class="text-dark">-</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Select Recipient User or Driver <span class="text-danger">*</span></label>
                            <input type="text" id="assign_user_search" class="form-control" placeholder="Type user name, mobile number, or account number..." autocomplete="off">
                            <small class="text-muted">Search customers and drivers to receive this payment.</small>
                            <div id="assign_search_results" class="list-group mt-2" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                        </div>

                        {{-- Selected User Banner --}}
                        <div id="assign_selected_user_box" class="p-3 bg-light-success border border-success rounded mb-3" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-success mb-0" id="assign_selected_name">User Name</h6>
                                    <small class="text-muted" id="assign_selected_details">Ph: - | A/C: -</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="assign_clear_user">Change</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="assign_submit_btn" disabled>
                            <i class="fa fa-check mr-1"></i> Confirm &amp; Credit Wallet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Manual Credit by Payment ID --}}
    <div class="modal fade" id="manualCreditModal" tabindex="-1" role="dialog" aria-labelledby="manualCreditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('walletstransactions.upi.manual-credit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" id="manual_user_id">
                    <input type="hidden" name="user_type" id="manual_user_type">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="manualCreditModalLabel"><i class="fa fa-plus-circle mr-2"></i>Manual Credit by Razorpay Payment ID</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Use this to credit a payment directly if it was captured in Razorpay Dashboard but did not credit automatically.</p>

                        <div class="form-group">
                            <label class="font-weight-bold">Razorpay Payment ID <span class="text-danger">*</span></label>
                            <input type="text" name="razorpay_payment_id" class="form-control font-mono" placeholder="e.g. pay_PXXXXXXXXXXXXX" required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="e.g. 50.00" required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Recipient User / Driver <span class="text-danger">*</span></label>
                            <input type="text" id="manual_user_search" class="form-control" placeholder="Type user name, mobile or account number..." autocomplete="off">
                            <div id="manual_search_results" class="list-group mt-2" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                        </div>

                        {{-- Selected User Box --}}
                        <div id="manual_selected_user_box" class="p-3 bg-light-primary border border-primary rounded mb-3" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-primary mb-0" id="manual_selected_name">User Name</h6>
                                    <small class="text-muted" id="manual_selected_details">Ph: - | A/C: -</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="manual_clear_user">Change</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Payer Name (Optional)</label>
                                <input type="text" name="payer_name" class="form-control" placeholder="e.g. Amit Kumar">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Payer Phone (Optional)</label>
                                <input type="text" name="payer_phone" class="form-control" placeholder="e.g. 9876543210">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="manual_submit_btn" disabled>
                            <i class="fa fa-check mr-1"></i> Credit Wallet Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: View Raw Webhook Payload --}}
    <div class="modal fade" id="payloadModal" tabindex="-1" role="dialog" aria-labelledby="payloadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="payloadModalLabel"><i class="fa fa-code mr-2"></i>Raw Webhook Payload (<span id="payload_payment_id" class="font-mono"></span>)</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <pre id="payload_content" class="bg-dark text-success p-3 m-0" style="max-height: 500px; overflow-y: auto; font-family: monospace; font-size: 13px;"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var searchUsersUrl = "{{ route('walletstransactions.upi.search-users') }}";

    // Open Assign Modal
    $('.btn-assign-action').on('click', function() {
        var id = $(this).data('id');
        var paymentId = $(this).data('payment-id');
        var amount = parseFloat($(this).data('amount')).toFixed(2);
        var payer = $(this).data('payer');

        $('#assign_txn_id').val(id);
        $('#assign_display_payment_id').text(paymentId);
        $('#assign_display_amount').text('₹' + amount);
        $('#assign_display_payer').text(payer);

        $('#assign_user_id').val('');
        $('#assign_user_type').val('');
        $('#assign_user_search').val('').show();
        $('#assign_selected_user_box').hide();
        $('#assign_search_results').hide().empty();
        $('#assign_submit_btn').prop('disabled', true);

        $('#assignModal').modal('show');
    });

    // View Payload Modal
    $('.btn-view-payload').on('click', function() {
        var paymentId = $(this).data('payment-id');
        var raw = $(this).attr('data-payload');
        $('#payload_payment_id').text(paymentId);
        try {
            var parsed = JSON.parse(raw);
            $('#payload_content').text(JSON.stringify(parsed, null, 2));
        } catch(e) {
            $('#payload_content').text(raw);
        }
        $('#payloadModal').modal('show');
    });

    // Helper for AJAX User Searching
    function setupUserSearch(inputSelector, resultsSelector, hiddenIdSelector, hiddenTypeSelector, boxSelector, nameSelector, detailsSelector, submitBtnSelector) {
        var timer = null;
        $(inputSelector).on('keyup', function() {
            var q = $(this).val().trim();
            clearTimeout(timer);
            if (q.length < 2) {
                $(resultsSelector).hide().empty();
                return;
            }
            timer = setTimeout(function() {
                $.getJSON(searchUsersUrl, { q: q }, function(data) {
                    var $res = $(resultsSelector).empty();
                    if (!data || data.length === 0) {
                        $res.html('<div class="list-group-item text-muted">No users or drivers found.</div>').show();
                        return;
                    }
                    $.each(data, function(i, item) {
                        var $item = $('<a href="#" class="list-group-item list-group-item-action"></a>')
                            .text(item.label)
                            .data('user', item)
                            .on('click', function(e) {
                                e.preventDefault();
                                var u = $(this).data('user');
                                $(hiddenIdSelector).val(u.id);
                                $(hiddenTypeSelector).val(u.user_type);
                                $(nameSelector).text(u.name + ' (' + u.user_type.toUpperCase() + ')');
                                $(detailsSelector).text('Phone: ' + (u.phone || '-') + ' | A/C: ' + (u.ac_no || '-') + ' | Balance: ₹' + u.amount);
                                $(boxSelector).show();
                                $(inputSelector).hide();
                                $(resultsSelector).hide();
                                $(submitBtnSelector).prop('disabled', false);
                            });
                        $res.append($item);
                    });
                    $res.show();
                });
            }, 300);
        });
    }

    // Initialize Assign Modal Search
    setupUserSearch(
        '#assign_user_search',
        '#assign_search_results',
        '#assign_user_id',
        '#assign_user_type',
        '#assign_selected_user_box',
        '#assign_selected_name',
        '#assign_selected_details',
        '#assign_submit_btn'
    );

    $('#assign_clear_user').on('click', function() {
        $('#assign_user_id').val('');
        $('#assign_user_type').val('');
        $('#assign_selected_user_box').hide();
        $('#assign_user_search').val('').show().focus();
        $('#assign_submit_btn').prop('disabled', true);
    });

    // Initialize Manual Credit Modal Search
    setupUserSearch(
        '#manual_user_search',
        '#manual_search_results',
        '#manual_user_id',
        '#manual_user_type',
        '#manual_selected_user_box',
        '#manual_selected_name',
        '#manual_selected_details',
        '#manual_submit_btn'
    );

    $('#manual_clear_user').on('click', function() {
        $('#manual_user_id').val('');
        $('#manual_user_type').val('');
        $('#manual_selected_user_box').hide();
        $('#manual_user_search').val('').show().focus();
        $('#manual_submit_btn').prop('disabled', true);
    });
});
</script>
@endsection

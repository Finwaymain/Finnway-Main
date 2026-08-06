@extends('layouts.app')

@section('content')

    <div class="page-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.user_plural') }} Schedule Show</h3>
            </div>

            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">Users Schedule Show</li>
                </ol>
            </div>

            <div></div>
        </div>

        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center"></div>

                            <form action="{{ route('users') }}" method="get" id="filterForm">
                                <div class="d-flex top-title-right align-self-center">
                                    <div class="select-box pl-3">
                                        <select class="form-control status_selector filteredRecords" name="status_selector">
                                            <option value="">{{ trans('lang.status') }}</option>
                                            <option value="active"
                                                {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'active' ? 'selected ' : '' }}>
                                                {{ trans('lang.active') }}</option>
                                            <option value="inactive"
                                                {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'inactive' ? 'selected' : '' }}>
                                                {{ trans('lang.in_active') }}</option>
                                        </select>
                                    </div>
                                    <div class="select-box pl-3">
                                        <input type="text" placeholder="dd-mm-yyyy" class="form-control filteredRecords"
                                            id="daterange" name="daterange" value="{{ request('daterange') }}" readonly />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">

                            <div id="data-table_processing" class="dataTables_processing panel panel-default"
                                style="display: none;">{{ trans('lang.processing') }}
                            </div>

                            <div class="userlist-topsearch d-flex mb-3">
                                <div class="userlist-top-left">
                                    <a class="nav-link" href="{!! route('users.create') !!}">
                                        <i class="fa fa-plus mr-2"></i>{{ trans('lang.user_create') }}
                                    </a>
                                </div>

                                <div id="users-table_filter" class="ml-auto">
                                    <label>{{ trans('lang.search_by') }}
                                        <div class="form-group mb-0">
                                            <form action="{{ route('users_shudule') }}" method="get">
                                                @if (isset($_GET['selected_search']) && $_GET['selected_search'] != '')
                                                    <select name="selected_search" id="selected_search"
                                                        class="form-control input-sm">
                                                        <option value="prenom"
                                                            @if ($_GET['selected_search'] == 'prenom') selected="selected" @endif>
                                                            {{ trans('lang.user_name') }}</option>
                                                        <option value="email"
                                                            @if ($_GET['selected_search'] == 'email') selected="selected" @endif>
                                                            {{ trans('lang.email') }}</option>
                                                        <option value="phone"
                                                            @if ($_GET['selected_search'] == 'phone') selected="selected" @endif>
                                                            {{ trans('lang.user_phone') }}</option>
                                                    </select>
                                                @else
                                                    <select name="selected_search" id="selected_search"
                                                        class="form-control input-sm">
                                                        <option value="prenom">{{ trans('lang.user_name') }}</option>
                                                        <option value="email">{{ trans('lang.email') }}</option>
                                                        <option value="phone">{{ trans('lang.user_phone') }}</option>
                                                    </select>
                                                @endif

                                                <div class="search-box position-relative">
                                                    @if (isset($_GET['search']) && $_GET['search'] != '')
                                                        <input type="text" class="search form-control" name="search"
                                                            id="search" value="{{ $_GET['search'] }}">
                                                    @else
                                                        <input type="text" class="search form-control" name="search"
                                                            id="search">
                                                    @endif

                                                    <button type="submit" class="btn-flat position-absolute">
                                                        <i class="fa fa-search"></i>
                                                    </button>
                                                    <a class="btn btn-warning btn-flat"
                                                        href="{{ url('users_shudule') }}">Clear</a>
                                                </div>
                                            </form>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="dropdown text-right mb-3">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download"></i> {{ trans('lang.export_as') }}
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('export.data', ['type' => 'excel', 'model' => 'UserApp']) }}">
                                            {{ trans('lang.export_excel') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('export.data', ['type' => 'pdf', 'model' => 'UserApp']) }}">
                                            {{ trans('lang.export_pdf') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('export.data', ['type' => 'csv', 'model' => 'UserApp']) }}">
                                            {{ trans('lang.export_csv') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <!-- Schedule Buttons -->
                            <div class="row mb-3">
                                <div class="col-12 d-flex flex-wrap" style="gap:10px;">
                                    <button type="button" id="schedule1" class="btn btn-primary">Sender Receiver</button>
                                    <button type="button" id="schedule2" class="btn btn-primary">Daily Increment</button>
                                    <button type="button" id="schedule3" class="btn btn-primary">Deduction</button>
                                    <button type="button" id="schedule4" class="btn btn-primary">Refer & Earn</button>
                                    <button type="button" id="TransferWalletAll" class="btn btn-primary">Transfer Wallet
                                        All</button>
                                </div>
                            </div>

                            <div class="table-responsive m-t-10">
                                <table id="example24"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all">
                                                <input type="checkbox" id="is_active">
                                                <label class="col-3 control-label" for="is_active">
                                                    {{-- <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                        <i class="fa fa-trash"></i> All
                                                    </a> --}}
                                                </label>
                                            </th>
                                            <th>{{ trans('lang.status') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
                                            <th>Kyc Status</th>
                                            <th>Wallet</th>
                                            <th>{{ trans('lang.user_name') }}</th>
                                            <th>{{ trans('lang.user_phone') }}</th>
                                            <th>Mpin</th>
                                            <th>Pocket No</th>
                                            <th>{{ trans('lang.extra_image') }}</th>
                                            <th>{{ trans('lang.wallet_history') }}</th>
                                            <th>{{ trans('lang.email') }}</th>
                                        </tr>
                                    </thead>

                                    <tbody id="append_list12">
                                        @if (count($users) > 0)
                                            @foreach ($users as $customer)
                                                <tr>
                                                    <td class="delete-all">
                                                        <input type="checkbox" id="is_open_{{ $customer->id }}"
                                                            class="is_open common_selector" dataid="{{ $customer->id }}"
                                                            value="{{ $customer->ac_no }}">
                                                        <label class="col-3 control-label"
                                                            for="is_open_{{ $customer->id }}"></label>
                                                    </td>

                                                    @if ($customer->statut == 'yes')
                                                        <td>
                                                            <label class="switch">
                                                                <input type="checkbox" checked id="{{ $customer->id }}"
                                                                    name="isActive">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </td>
                                                    @else
                                                        <td>
                                                            <label class="switch">
                                                                <input type="checkbox" id="{{ $customer->id }}"
                                                                    name="isActive">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </td>
                                                    @endif

                                                    <td class="action-btn">
                                                        <a href="{{ route('users.show', ['id' => $customer->id]) }}"
                                                            data-toggle="tooltip" data-original-title="Details">
                                                            <i class="fa fa-eye"></i>
                                                        </a>

                                                        <a href="{{ route('users.edit', ['id' => $customer->id]) }}">
                                                            <i class="fa fa-edit"></i>
                                                        </a>

                                                        <a id="'+val.id+'" class="delete-btn" name="user-delete"
                                                            href="{{ route('user.delete', ['id' => $customer->id]) }}">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </td>

                                                    <!-- KYC Status -->
                                                    <td>
                                                        @if ($customer->kyc_status == 1)
                                                            <label class="switch">
                                                                <input type="checkbox" class="KycStatusSwitch"
                                                                    value="{{ $customer->id }}" checked>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        @else
                                                            <label class="switch">
                                                                <input type="checkbox" class="KycStatusSwitch"
                                                                    value="{{ $customer->id }}">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        @endif
                                                        <br>

                                                        @if (!empty($customer->start_date) && !empty($customer->end_date))
                                                            <button type="button"
                                                                class="btn btn-success py-0 px-1 btn-sm rounded-circle"
                                                                data-toggle="tooltip" data-placement="top"
                                                                title="Sender Receiver" style="height:20px;width:20px">
                                                            </button>
                                                        @endif

                                                        @if (!empty($customer->start_date2) && !empty($customer->end_date2))
                                                            <button type="button"
                                                                class="btn btn-primary py-0 px-1 btn-sm rounded-circle"
                                                                data-toggle="tooltip" data-placement="top"
                                                                title="Daily Increment" style="height:20px;width:20px">
                                                            </button>
                                                        @endif

                                                        @if (!empty($customer->start_date3) && !empty($customer->end_date3))
                                                            <button type="button"
                                                                class="btn btn-danger py-0 px-1 btn-sm rounded-circle"
                                                                data-toggle="tooltip" data-placement="top"
                                                                title="Deduction" style="height:20px;width:20px">
                                                            </button>
                                                        @endif

                                                        @if (empty($customer->start_date) && empty($customer->start_date2) && empty($customer->start_date3))
                                                            <span>Sorry No Schedule</span>
                                                        @endif
                                                    </td>

                                                    <!-- Wallet -->
                                                    <td>
                                                        ₹{{ $customer->amount + $customer->earn_amount }}<br>
                                                        <a href="javascript:void(0);"
                                                            class="btn py-0 px-1 btn-sm btn-info mdi mdi-6px mdi mdi-wallet me-3 editwallet"
                                                            data-amount="{{ $customer->amount }}"
                                                            data-earn_amount="{{ $customer->earn_amount }}"
                                                            data-ac_no="{{ $customer->ac_no }}"></a>
                                                    </td>

                                                    <td>
                                                        <a href="{{ route('users.show', ['id' => $customer->id]) }}">
                                                            {{ $customer->prenom }} {{ $customer->nom }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $customer->phone }}</td>
                                                    <td>{{ $customer->m_pin }}</td>
                                                    <td>{{ $customer->ac_no }}</td>

                                                    @if (file_exists(public_path('assets/images/users' . '/' . $customer->photo_path)) && !empty($customer->photo_path))
                                                        <td>
                                                            <img class="rounded" style="width:50px"
                                                                src="{{ asset('assets/images/users') . '/' . $customer->photo_path }}"
                                                                alt="image">
                                                        </td>
                                                    @else
                                                        <td>
                                                            <img class="rounded" style="width:50px"
                                                                src="{{ asset('assets/images/placeholder_image.jpg') }}"
                                                                alt="image">
                                                        </td>
                                                    @endif

                                                    <td>
                                                        <a
                                                            href="{{ route('users.walletstransaction', ['id' => $customer->id]) }}">
                                                            {{ trans('lang.wallet_history') }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $customer->email }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="11" align="center">{{ trans('lang.no_result') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>

                                {{ $users->appends(request()->query())->links('pagination.pagination') }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Wallet Modal -->
    <div class="modal fade" id="editmodalwallet" tabindex="-1" aria-labelledby="editModalWalletLabel"
        aria-hidden="true" data-backdrop="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="edit_formwallet" action="javascript:void(0);" enctype="multipart/form-data"
                        method="post">
                        @csrf
                        <input type="hidden" name="ac_no" id="wallet_ac_no" value="" class="form-control">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_wallet">Wallet</label>
                                    <input type="text" id="edit_wallet" style="border:none;font-weight:bold">
                                    <input type="text" name="amount" id="edit_wallet1" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="earn_wallet">Earn Wallet</label>
                                    <input type="text" id="edit_earn_wallet" style="border:none;font-weight:bold">
                                    <input type="text" name="earn_amount" id="edit_earn_wallet1"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="earn_wallet">Description</label>
                                    <textarea name="description" placeholder="Description" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>

                        <button class="float-right btn btn-primary btn_wallet">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Wallet All Modal -->
    <div class="modal fade" id="editwalletall" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Transfer Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="edit_formwallet_all" action="javascript:void(0);" method="post">
                        @csrf
                        <input type="text" name="ac_no" id="wallet_id" value="" class="form-control mb-3"
                            readonly>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Wallet</label>
                                    <input type="text" name="amount" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Earn Wallet</label>
                                    <input type="text" name="earn_amount" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" placeholder="Description"></textarea>
                                </div>
                            </div>
                        </div>

                        <button class="float-right btn btn-primary wallet_all">Update All</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sender Receiver Modal -->
    <div class="modal fade" id="editallwalletall" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Sender Receiver</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="edit_formallwallet_all" action="javascript:void(0);" method="post">
                        @csrf
                        <input type="text" name="ac_no" id="wallet_all_id" value=""
                            class="form-control mb-3" readonly>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Percentage Sender</label>
                                    <input type="text" name="per_sender" class="form-control"
                                        placeholder="Percentage Sender">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Percentage Receiver</label>
                                    <input type="text" name="per_receiver" class="form-control"
                                        placeholder="Percentage Receiver">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sender Description</label>
                                    <textarea name="sender_desc" class="form-control" placeholder="Sender Desc"></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Receiver Description</label>
                                    <textarea name="receiver_desc" class="form-control" placeholder="Receiver Desc"></textarea>
                                </div>
                            </div>
                        </div>

                        <button class="float-right btn btn-primary btn_wallet_all">Update All</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Increment Modal -->
    <div class="modal fade" id="editallwalletall2" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Daily Increment</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="edit_formallwallet_all2" action="javascript:void(0);" method="post">
                        @csrf
                        <input type="text" name="ac_no" id="wallet_all_id2" value=""
                            class="form-control mb-3" readonly>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date2" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date2" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Percentage</label>
                                    <input type="text" name="percentage" class="form-control"
                                        placeholder="Percentage">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description_2nd" class="form-control" placeholder="Description"></textarea>
                                </div>
                            </div>
                        </div>

                        <button class="float-right btn btn-primary btn_wallet_all2">Update All</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Deduction Modal -->
    <div class="modal fade" id="editallwalletall3" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Deduction</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="edit_formallwallet_all3" action="javascript:void(0);" method="post">
                        @csrf
                        <input type="text" name="ac_no" id="wallet_all_id3" value=""
                            class="form-control mb-3" readonly>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date3" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date3" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Percentage</label>
                                    <input type="text" id="per_3rd" name="per_3rd"
                                        oninput="clearOtherInput('amount_3rd')" class="form-control"
                                        placeholder="Percentage">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="text" id="amount_3rd" name="amount_3rd"
                                        oninput="clearOtherInput('per_3rd')" class="form-control" placeholder="Amount">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description_3rd" class="form-control" placeholder="Description"></textarea>
                                </div>
                            </div>
                        </div>

                        <button class="float-right btn btn-primary btn_wallet_all3">Update All</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Refer & Earn Modal -->
    <div class="modal fade" id="refer_earnmodal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Refer & Earn</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="edit_refer_earn" action="javascript:void(0);" method="post">
                        @csrf
                        <input type="text" name="ac_no" id="refer_earn_id" value=""
                            class="form-control mb-3" readonly>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date4" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date4" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="text" id="amount_4th" name="amount_4th" class="form-control"
                                        placeholder="Amount">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description_4th" class="form-control" placeholder="Description"></textarea>
                                </div>
                            </div>
                        </div>

                        <button class="float-right btn btn-primary btn_refer_earn">Update All</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <!-- KYC Status -->
    <script>
        $('.KycStatusSwitch').change(function() {
            var currentCheckbox = $(this);
            var isChecked = currentCheckbox.is(':checked');
            var switchLabel = currentCheckbox.val();
            var checkedVal = isChecked ? 1 : 0;
            var tableName = "tj_user_app";

            swal({
                title: "Are you sure?",
                text: "You are about to update the KYC status.",
                icon: "warning",
                buttons: ["Cancel", "Yes, update it"],
                dangerMode: true,
            }).then((willUpdate) => {
                if (willUpdate) {
                    var formData = new FormData();
                    formData.append('id', switchLabel);
                    formData.append('kyc_status', checkedVal);

                    $.ajax({
                        type: "POST",
                        url: "{{ url('switch_kyc_status_update') }}/" + tableName,
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        encode: true,
                        success: function(data) {
                            swal("KYC Status Updated Successfully", "", "success");
                        },
                        error: function(errResponse) {
                            swal("Something Went Wrong!", "", "error");
                            currentCheckbox.prop('checked', !isChecked);
                        }
                    });
                } else {
                    currentCheckbox.prop('checked', !isChecked);
                    swal("KYC Status Update Cancelled", "", "info");
                }
            });
        });
    </script>

    <!-- Single Wallet -->
    <script>
        $('.modal .close').on('click', function() {
            $(this).closest('.modal').modal("hide");
        });

        $(document).on('click', '.editwallet', function() {
            var ac_no = $(this).data('ac_no');
            var amount = $(this).data('amount');
            var earn_amount = $(this).data('earn_amount');

            $("#wallet_ac_no").val(ac_no);
            $("#edit_wallet").val(amount);
            $("#edit_earn_wallet").val(earn_amount);
            $("#editmodalwallet").modal("show");
        });

        $("#edit_formwallet").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                type: "post",
                url: "{{ url('/update_user_wallet') }}",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                encode: true,
                success: function(data) {
                    if (data.success == 'success') {
                        $(".btn_wallet").prop("disabled", false);
                        $("#edit_formwallet")[0].reset();
                        $("#editmodalwallet").modal("hide");
                        swal("Wallet Updated Successfull", "", "success");
                        window.location.reload();
                    } else {
                        swal("Wallet Not Update!", "", "error");
                        $(".btn_wallet").prop('disabled', false);
                    }
                },
                error: function(errResponse) {
                    swal("Somthing Went Wrong!", "", "error");
                    $(".btn_wallet").prop('disabled', false);
                }
            });
        });
    </script>

    <!-- Main Script -->
    <script type="text/javascript">
        $(document).ready(function() {

            $(".shadow-sm").hide();

            $('.status_selector').select2({
                placeholder: '{{ trans('lang.status') }}',
                minimumResultsForSearch: Infinity,
                allowClear: true
            });

            $('select').on("select2:unselecting", function(e) {
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 0);
            });

            function setDate() {
                let initialDateRange = $('#daterange').val();

                $('#daterange').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        format: 'DD-MM-YYYY',
                        cancelLabel: 'Clear'
                    }
                });

                if (initialDateRange) {
                    let dates = initialDateRange.split(' - ');
                    $('#daterange').data('daterangepicker').setStartDate(dates[0]);
                    $('#daterange').data('daterangepicker').setEndDate(dates[1]);
                    $('#daterange').val(initialDateRange);
                }

                $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                        'DD-MM-YYYY'));
                    $('.filteredRecords').trigger('change');
                });

                $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    $('.filteredRecords').trigger('change');
                });
            }

            setDate();

            $('.filteredRecords').change(async function() {
                $('#filterForm').submit();
            });

            function getSelectedAcNos() {
                var selected = [];
                $('#example24 .common_selector:checked').each(function() {
                    selected.push($(this).val());
                });
                return selected;
            }

            function updateAllAcNoFields() {
                var selectedAcNos = getSelectedAcNos().join(',');
                $('#wallet_id').val(selectedAcNos);
                $('#wallet_all_id').val(selectedAcNos);
                $('#wallet_all_id2').val(selectedAcNos);
                $('#wallet_all_id3').val(selectedAcNos);
                $('#refer_earn_id').val(selectedAcNos);
            }

            function hasSelection() {
                return $('#example24 .common_selector:checked').length > 0;
            }

            function syncHeaderCheckbox() {
                var total = $('#example24 .common_selector').length;
                var checked = $('#example24 .common_selector:checked').length;
                $('#is_active').prop('checked', total > 0 && total === checked);
            }

            $("#is_active").click(function() {
                var isChecked = $(this).prop('checked');
                $("#example24 .is_open").prop('checked', isChecked);
                updateAllAcNoFields();
            });

            $(document).on('change', '.common_selector', function() {
                syncHeaderCheckbox();
                updateAllAcNoFields();
            });

            $('#schedule1').on('click', function() {
                updateAllAcNoFields();
                if (!hasSelection()) {
                    swal("Please select at least one user", "", "warning");
                    return;
                }
                $('#editallwalletall').modal('show');
            });

            $('#schedule2').on('click', function() {
                updateAllAcNoFields();
                if (!hasSelection()) {
                    swal("Please select at least one user", "", "warning");
                    return;
                }
                $('#editallwalletall2').modal('show');
            });

            $('#schedule3').on('click', function() {
                updateAllAcNoFields();
                if (!hasSelection()) {
                    swal("Please select at least one user", "", "warning");
                    return;
                }
                $('#editallwalletall3').modal('show');
            });

            $('#schedule4').on('click', function() {
                updateAllAcNoFields();
                if (!hasSelection()) {
                    swal("Please select at least one user", "", "warning");
                    return;
                }
                $('#refer_earnmodal').modal('show');
            });

            $('#TransferWalletAll').on('click', function() {
                updateAllAcNoFields();
                if (!hasSelection()) {
                    swal("Please select at least one user", "", "warning");
                    return;
                }
                $('#editwalletall').modal('show');
            });

            $("#deleteAll").click(function() {
                if ($('#example24 .is_open:checked').length) {
                    if (confirm('{{ trans('lang.selected_delete_alert') }}')) {
                        var arrayUsers = [];

                        $('#example24 .is_open:checked').each(function() {
                            var dataId = $(this).attr('dataId');
                            arrayUsers.push(dataId);
                        });

                        arrayUsers = JSON.stringify(arrayUsers);

                        var url = "{{ url('user/delete', 'id') }}";
                        url = url.replace('id', arrayUsers);
                        $(this).attr('href', url);
                    }
                } else {
                    alert('{{ trans('lang.select_delete_alert') }}');
                }
            });

            $(document).on("click", "input[name='isActive']", function(e) {
                var ischeck = $(this).is(':checked');
                var id = this.id;

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ url('switch') }}',
                    method: "POST",
                    data: {
                        'ischeck': ischeck,
                        'id': id
                    },
                    success: function(data) {},
                });
            });

        });
    </script>

    <!-- Bulk Transfer Wallet -->
    <script>
        $("#edit_formwallet_all").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                type: "post",
                url: "{{ url('/update_transfer_wallet_all') }}",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                encode: true,
                success: function(data) {
                    if (data.success == 'success') {
                        $(".wallet_all").prop("disabled", false);
                        $("#edit_formwallet_all")[0].reset();
                        $("#editwalletall").modal("hide");
                        swal("Wallet Updated Successfull", "", "success");
                        window.location.reload();
                    } else {
                        swal("Wallet Not Update!", "", "error");
                        $(".wallet_all").prop('disabled', false);
                    }
                },
                error: function(errResponse) {
                    swal("Somthing Went Wrong!", "", "error");
                    $(".wallet_all").prop('disabled', false);
                }
            });
        });
    </script>

    <!-- Sender Receiver -->
    <script>
        $("#edit_formallwallet_all").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                type: "post",
                url: "{{ url('/update_wallet_all') }}",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                encode: true,
                success: function(data) {
                    if (data.success == 'success') {
                        $(".btn_wallet_all").prop("disabled", false);
                        $("#edit_formallwallet_all")[0].reset();
                        $("#editallwalletall").modal("hide");
                        swal("Wallet Updated Successfull", "", "success");
                        window.location.reload();
                    } else {
                        swal("Wallet Not Update!", "", "error");
                        $(".btn_wallet_all").prop('disabled', false);
                    }
                },
                error: function(errResponse) {
                    swal("Somthing Went Wrong!", "", "error");
                    $(".btn_wallet_all").prop('disabled', false);
                }
            });
        });
    </script>

    <!-- Daily Increment -->
    <script>
        $("#edit_formallwallet_all2").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                type: "post",
                url: "{{ url('/update_wallet_all2') }}",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                encode: true,
                success: function(data) {
                    if (data.success == 'success') {
                        $(".btn_wallet_all2").prop("disabled", false);
                        $("#edit_formallwallet_all2")[0].reset();
                        $("#editallwalletall2").modal("hide");
                        swal("Wallet Updated Successfull", "", "success");
                        window.location.reload();
                    } else {
                        swal("Wallet Not Update!", "", "error");
                        $(".btn_wallet_all2").prop('disabled', false);
                    }
                },
                error: function(errResponse) {
                    swal("Somthing Went Wrong!", "", "error");
                    $(".btn_wallet_all2").prop('disabled', false);
                }
            });
        });
    </script>

    <!-- Deduction -->
    <script>
        $("#edit_formallwallet_all3").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                type: "post",
                url: "{{ url('/update_wallet_all3') }}",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                encode: true,
                success: function(data) {
                    if (data.success == 'success') {
                        $(".btn_wallet_all3").prop("disabled", false);
                        $("#edit_formallwallet_all3")[0].reset();
                        $("#editallwalletall3").modal("hide");
                        swal("Wallet Updated Successfull", "", "success");
                        window.location.reload();
                    } else {
                        swal("Wallet Not Update!", "", "error");
                        $(".btn_wallet_all3").prop('disabled', false);
                    }
                },
                error: function(errResponse) {
                    swal("Somthing Went Wrong!", "", "error");
                    $(".btn_wallet_all3").prop('disabled', false);
                }
            });
        });
    </script>

    <!-- Refer & Earn -->
    <script>
        $("#edit_refer_earn").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                type: "post",
                url: "{{ url('/update_refer_earn') }}",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                encode: true,
                success: function(data) {
                    if (data.success == 'success') {
                        $(".btn_refer_earn").prop("disabled", false);
                        $("#edit_refer_earn")[0].reset();
                        $("#refer_earnmodal").modal("hide");
                        swal("Refer And Earn Updated Successfull", "", "success");
                        window.location.reload();
                    } else {
                        swal("Refer Not Update!", "", "error");
                        $(".btn_refer_earn").prop('disabled', false);
                    }
                },
                error: function(errResponse) {
                    swal("Somthing Went Wrong!", "", "error");
                    $(".btn_refer_earn").prop('disabled', false);
                }
            });
        });
    </script>

    <script>
        function clearOtherInput(otherInputId) {
            document.getElementById(otherInputId).value = '';
        }
    </script>
@endsection

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

                            <div class="userlist-topsearch d-flex mb-3 align-items-center flex-wrap" style="gap:8px;">
                                <a class="btn btn-primary btn-sm" href="{!! route('users.create') !!}">
                                    <i class="fa fa-plus mr-1"></i>Add Consumer
                                </a>

                                <form action="{{ route('users_shudule') }}" method="get" class="d-flex align-items-center" style="gap:6px; flex-wrap:wrap;">
                                    <select name="selected_search" id="selected_search" class="form-control form-control-sm" style="width:130px;">
                                        <option value="prenom" {{ (isset($_GET['selected_search']) && $_GET['selected_search'] == 'prenom') ? 'selected' : '' }}>{{ trans('lang.user_name') }}</option>
                                        <option value="email" {{ (isset($_GET['selected_search']) && $_GET['selected_search'] == 'email') ? 'selected' : '' }}>{{ trans('lang.email') }}</option>
                                        <option value="phone" {{ (isset($_GET['selected_search']) && $_GET['selected_search'] == 'phone') ? 'selected' : '' }}>{{ trans('lang.user_phone') }}</option>
                                    </select>
                                    <input type="text" class="form-control form-control-sm" name="search" id="search" placeholder="Search..." value="{{ $_GET['search'] ?? '' }}" style="width:180px;">
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search mr-1"></i> Search</button>
                                    <a class="btn btn-sm btn-warning" href="{{ url('users_shudule') }}">Clear</a>
                                </form>

                                <div class="dropdown ml-auto">
                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-download mr-1"></i> {{ trans('lang.export_as') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('export.data', ['type' => 'excel', 'model' => 'UserApp']) }}">
                                                {{ trans('lang.export_excel') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('export.data', ['type' => 'pdf', 'model' => 'UserApp']) }}">
                                                {{ trans('lang.export_pdf') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('export.data', ['type' => 'csv', 'model' => 'UserApp']) }}">
                                                {{ trans('lang.export_csv') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Schedule Buttons -->
                            <div class="row mb-3">
                                <div class="col-12 d-flex flex-wrap" style="gap:10px;">
                                    <button type="button" class="btn btn-primary btn-schedule-trigger" data-target-form="sender_receiver" data-title="Sender Receiver Schedule">
                                        <i class="fa fa-exchange-alt mr-1"></i> Sender Receiver
                                    </button>
                                    <button type="button" class="btn btn-info text-white btn-schedule-trigger" data-target-form="daily_increment" data-title="Daily Increment Schedule">
                                        <i class="fa fa-chart-line mr-1"></i> Daily Increment
                                    </button>
                                    <button type="button" class="btn btn-warning text-white btn-schedule-trigger" data-target-form="deduction" data-title="Deduction Schedule">
                                        <i class="fa fa-minus-circle mr-1"></i> Deduction
                                    </button>
                                    <button type="button" class="btn btn-success btn-schedule-trigger" data-target-form="refer_earn" data-title="Refer & Earn Schedule">
                                        <i class="fa fa-gift mr-1"></i> Refer & Earn
                                    </button>
                                    <button type="button" class="btn btn-dark btn-schedule-trigger" data-target-form="transfer_bulk" data-title="Transfer / Adjust Wallet (Bulk)">
                                        <i class="fa fa-wallet mr-1"></i> Transfer Wallet All
                                    </button>
                                </div>
                            </div>

                            <!-- INLINE ACTION PANEL (NO POPUP MODALS) -->
                            <div id="inlineActionPanel" class="card shadow-sm border mb-4" style="display: none; background: #f8fafc; border-left: 5px solid #4f46e5 !important; border-radius: 8px;">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                                    <h5 id="panelTitle" class="mb-0 font-weight-bold text-primary" style="font-size: 17px;"></h5>
                                    <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold" onclick="closeInlinePanel()">
                                        <i class="fa fa-times mr-1"></i> Close Panel
                                    </button>
                                </div>
                                <div class="card-body p-4">
                                    <div id="selectedCountBadge" class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between">
                                        <span><i class="fa fa-users mr-2"></i><strong>Target Users:</strong> <span id="targetUsersText">All visible users on this page</span></span>
                                        <span class="badge badge-primary font-weight-bold px-2 py-1" id="targetUsersCount">0 users</span>
                                    </div>

                                    <!-- 1. Sender Receiver Form -->
                                    <form id="form_sender_receiver" class="inline-operation-form" style="display:none;" action="javascript:void(0);">
                                        @csrf
                                        <input type="hidden" name="ac_no" class="panel-ac-no">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="font-weight-bold">Start Date <span class="text-danger">*</span></label>
                                                <input type="date" name="start_date" class="form-control" required>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="font-weight-bold">End Date <span class="text-danger">*</span></label>
                                                <input type="date" name="end_date" class="form-control" required>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="font-weight-bold">Percentage Sender (%) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="per_sender" class="form-control" placeholder="e.g. 5" required>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="font-weight-bold">Percentage Receiver (%) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="per_receiver" class="form-control" placeholder="e.g. 5" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Sender Description</label>
                                                <textarea name="sender_desc" class="form-control" rows="2" placeholder="Description for sender"></textarea>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Receiver Description</label>
                                                <textarea name="receiver_desc" class="form-control" rows="2" placeholder="Description for receiver"></textarea>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold btn-submit-action">
                                            <i class="fa fa-check mr-1"></i> Apply Sender Receiver Schedule
                                        </button>
                                    </form>

                                    <!-- 2. Daily Increment Form -->
                                    <form id="form_daily_increment" class="inline-operation-form" style="display:none;" action="javascript:void(0);">
                                        @csrf
                                        <input type="hidden" name="ac_no" class="panel-ac-no">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">Start Date <span class="text-danger">*</span></label>
                                                <input type="date" name="start_date2" class="form-control" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">End Date <span class="text-danger">*</span></label>
                                                <input type="date" name="end_date2" class="form-control" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">Increment Percentage (%) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="percentage" class="form-control" placeholder="e.g. 2.5" required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="font-weight-bold">Description</label>
                                                <textarea name="description_2nd" class="form-control" rows="2" placeholder="Daily increment note"></textarea>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-info text-white px-4 py-2 font-weight-bold btn-submit-action">
                                            <i class="fa fa-check mr-1"></i> Apply Daily Increment Schedule
                                        </button>
                                    </form>

                                    <!-- 3. Deduction Form -->
                                    <form id="form_deduction" class="inline-operation-form" style="display:none;" action="javascript:void(0);">
                                        @csrf
                                        <input type="hidden" name="ac_no" class="panel-ac-no">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="font-weight-bold">Start Date <span class="text-danger">*</span></label>
                                                <input type="date" name="start_date3" class="form-control" required>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="font-weight-bold">End Date <span class="text-danger">*</span></label>
                                                <input type="date" name="end_date3" class="form-control" required>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="font-weight-bold">Deduction Percentage (%)</label>
                                                <input type="number" step="0.01" name="per_3rd" id="inline_per_3rd" class="form-control" placeholder="Percentage" oninput="clearOtherInline('inline_amount_3rd')">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="font-weight-bold">Or Fixed Amount (₹)</label>
                                                <input type="number" step="0.01" name="amount_3rd" id="inline_amount_3rd" class="form-control" placeholder="Fixed amount" oninput="clearOtherInline('inline_per_3rd')">
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="font-weight-bold">Description</label>
                                                <textarea name="description_3rd" class="form-control" rows="2" placeholder="Deduction note"></textarea>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-warning text-white px-4 py-2 font-weight-bold btn-submit-action">
                                            <i class="fa fa-check mr-1"></i> Apply Deduction Schedule
                                        </button>
                                    </form>

                                    <!-- 4. Refer & Earn Form -->
                                    <form id="form_refer_earn" class="inline-operation-form" style="display:none;" action="javascript:void(0);">
                                        @csrf
                                        <input type="hidden" name="ac_no" class="panel-ac-no">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">Start Date <span class="text-danger">*</span></label>
                                                <input type="date" name="start_date4" class="form-control" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">End Date <span class="text-danger">*</span></label>
                                                <input type="date" name="end_date4" class="form-control" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">Referral Bonus Amount (₹) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="amount_4th" class="form-control" placeholder="e.g. 50" required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="font-weight-bold">Description</label>
                                                <textarea name="description_4th" class="form-control" rows="2" placeholder="Referral bonus note"></textarea>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold btn-submit-action">
                                            <i class="fa fa-check mr-1"></i> Apply Refer & Earn Schedule
                                        </button>
                                    </form>

                                    <!-- 5. Bulk Transfer Wallet Form -->
                                    <form id="form_transfer_bulk" class="inline-operation-form" style="display:none;" action="javascript:void(0);">
                                        @csrf
                                        <input type="hidden" name="ac_no" class="panel-ac-no">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Wallet Amount Adjustment (₹)</label>
                                                <input type="number" step="0.01" name="amount" class="form-control" placeholder="e.g. 100 or -50">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Earn Wallet Adjustment (₹)</label>
                                                <input type="number" step="0.01" name="earn_amount" class="form-control" placeholder="e.g. 50">
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="font-weight-bold">Transaction Description</label>
                                                <textarea name="description" class="form-control" rows="2" placeholder="Reason for transfer/adjustment"></textarea>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-dark px-4 py-2 font-weight-bold btn-submit-action">
                                            <i class="fa fa-check mr-1"></i> Update Selected Wallets
                                        </button>
                                    </form>

                                    <!-- 6. Single User Wallet Form -->
                                    <form id="form_single_wallet" class="inline-operation-form" style="display:none;" action="javascript:void(0);">
                                        @csrf
                                        <input type="hidden" name="ac_no" id="single_wallet_ac_no">
                                        <input type="hidden" name="user_id" id="single_wallet_user_id">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">Target User</label>
                                                <input type="text" id="single_display_ac_no" class="form-control bg-light font-weight-bold" readonly>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">Add / Deduct Wallet (₹)</label>
                                                <input type="number" step="0.01" name="amount" id="single_input_amount" class="form-control" placeholder="e.g. 50 or -50">
                                                <small class="text-muted font-weight-bold">Current Balance: ₹<span id="single_curr_wallet">0</span></small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">Add / Deduct Earn Wallet (₹)</label>
                                                <input type="number" step="0.01" name="earn_amount" id="single_input_earn" class="form-control" placeholder="e.g. 25 or -25">
                                                <small class="text-muted font-weight-bold">Current Balance: ₹<span id="single_curr_earn">0</span></small>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="font-weight-bold">Description</label>
                                                <textarea name="description" class="form-control" rows="2" placeholder="e.g. Added ₹50 bonus credit to wallet"></textarea>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold btn-submit-action">
                                            <i class="fa fa-check mr-1"></i> Update User Wallet
                                        </button>
                                    </form>
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
                                                <label class="col-3 control-label" for="is_active"></label>
                                            </th>
                                            <th>{{ trans('lang.status') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
                                            <th>Kyc Status</th>
                                            <th>Aadhaar Number</th>
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
                                                            value="{{ !empty($customer->ac_no) ? $customer->ac_no : $customer->id }}">
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

                                                    <!-- Aadhaar Number -->
                                                    <td>
                                                        @if (!empty($customer->aadhar_number))
                                                            <span class="badge bg-success text-white" style="font-family: monospace; font-size: 11px;">{{ $customer->aadhar_number }}</span>
                                                        @else
                                                            <span class="badge bg-secondary text-white" style="font-size: 11px;">Not Submitted</span>
                                                        @endif
                                                    </td>

                                                    <!-- Wallet -->
                                                    <td>
                                                        <strong>{{ $currency_symbol ?? \App\Helpers\Helper::getCurrencySymbol() }}{{ $customer->amount + $customer->earn_amount }}</strong><br>
                                                        <button type="button"
                                                            class="btn py-1 px-2 btn-sm btn-outline-info font-weight-bold mt-1 editwallet"
                                                            data-amount="{{ $customer->amount }}"
                                                            data-earn_amount="{{ $customer->earn_amount }}"
                                                            data-ac_no="{{ !empty($customer->ac_no) ? $customer->ac_no : $customer->id }}"
                                                            data-id="{{ $customer->id }}"
                                                            data-name="{{ $customer->prenom }} {{ $customer->nom }}">
                                                            <i class="fa fa-wallet mr-1"></i> Edit Wallet
                                                        </button>
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
                                                <td colspan="13" align="center">{{ trans('lang.no_result') }}</td>
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

@endsection

@section('scripts')
    <script type="text/javascript">
        // Helper: Collect account numbers
        function getSelectedAcNos() {
            var selected = [];
            $('#example24 .common_selector:checked').each(function() {
                var val = $(this).val();
                if (val && val !== '') {
                    selected.push(val);
                }
            });
            return selected;
        }

        function getAllVisibleAcNos() {
            var all = [];
            $('#example24 .common_selector').each(function() {
                var val = $(this).val();
                if (val && val !== '') {
                    all.push(val);
                }
            });
            return all;
        }

        function syncPanelTargetUsers() {
            var selected = getSelectedAcNos();
            var all = getAllVisibleAcNos();
            var targetAcNos = selected.length > 0 ? selected : all;

            $('.panel-ac-no').val(targetAcNos.join(','));

            if (selected.length > 0) {
                $('#targetUsersText').html('<strong>' + selected.length + ' selected users</strong>');
                $('#targetUsersCount').text(selected.length + ' users');
            } else {
                $('#targetUsersText').html('<em>All ' + all.length + ' visible users on this page</em>');
                $('#targetUsersCount').text(all.length + ' users');
            }
        }

        function openInlinePanel(formId, title) {
            $('.inline-operation-form').hide();
            $('#panelTitle').html('<i class="fa fa-cog mr-2"></i>' + title);
            syncPanelTargetUsers();
            $('#' + formId).show();
            $('#inlineActionPanel').slideDown(250);

            // Smooth scroll to panel
            $('html, body').animate({
                scrollTop: $("#inlineActionPanel").offset().top - 80
            }, 300);
        }

        function closeInlinePanel() {
            $('#inlineActionPanel').slideUp(200);
        }

        function notifyUser(title, text, icon) {
            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                Swal.fire({
                    title: title,
                    text: text || '',
                    icon: icon || 'info',
                    confirmButtonColor: '#4f46e5'
                });
            } else {
                alert(title + (text ? "\n" + text : ""));
            }
        }

        // Action Buttons Click Handlers
        $(document).on('click', '.btn-schedule-trigger', function(e) {
            e.preventDefault();
            var targetForm = $(this).data('target-form');
            var title = $(this).data('title');
            openInlinePanel('form_' + targetForm, title);
        });

        // Single User Wallet Edit Click
        $(document).on('click', '.editwallet', function(e) {
            e.preventDefault();
            var ac_no = $(this).data('ac_no');
            var userId = $(this).data('id');
            var userName = $(this).data('name') || '';
            var amount = $(this).data('amount');
            var earn_amount = $(this).data('earn_amount');

            $("#single_wallet_ac_no").val(ac_no);
            $("#single_wallet_user_id").val(userId);
            $("#single_display_ac_no").val(userName + ' (ID: #' + userId + (ac_no && ac_no != userId ? ', Acc: ' + ac_no : '') + ')');
            $("#single_curr_wallet").text(amount || 0);
            $("#single_curr_earn").text(earn_amount || 0);
            $("#single_input_amount").val('');
            $("#single_input_earn").val('');

            openInlinePanel('form_single_wallet', 'Update Wallet: ' + userName + ' (#' + userId + ')');
        });

        // Checkbox events
        $("#is_active").click(function() {
            var isChecked = $(this).prop('checked');
            $("#example24 .is_open").prop('checked', isChecked);
            syncPanelTargetUsers();
        });

        $(document).on('change', '.common_selector', function() {
            var total = $('#example24 .common_selector').length;
            var checked = $('#example24 .common_selector:checked').length;
            $('#is_active').prop('checked', total > 0 && total === checked);
            syncPanelTargetUsers();
        });

        // Form Submit Handler Generator
        function handleAjaxFormSubmit(formSelector, url, successMsg) {
            $(formSelector).submit(function(e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('.btn-submit-action');
                var formData = new FormData(this);

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Processing...');

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    success: function(data) {
                        $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Apply');
                        if (data.success == 'success') {
                            $form[0].reset();
                            closeInlinePanel();
                            notifyUser(successMsg, "", "success");
                            setTimeout(function() { window.location.reload(); }, 1200);
                        } else {
                            notifyUser("Update Failed!", data.message || "", "error");
                        }
                    },
                    error: function(errResponse) {
                        $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Apply');
                        notifyUser("Something Went Wrong!", "Please check your input fields.", "error");
                    }
                });
            });
        }

        // Register All Inline Forms
        handleAjaxFormSubmit('#form_sender_receiver', "{{ url('/update_wallet_all') }}", "Sender Receiver Schedule Updated!");
        handleAjaxFormSubmit('#form_daily_increment', "{{ url('/update_wallet_all2') }}", "Daily Increment Schedule Updated!");
        handleAjaxFormSubmit('#form_deduction', "{{ url('/update_wallet_all3') }}", "Deduction Schedule Updated!");
        handleAjaxFormSubmit('#form_refer_earn', "{{ url('/update_refer_earn') }}", "Refer & Earn Schedule Updated!");
        handleAjaxFormSubmit('#form_transfer_bulk', "{{ url('/update_transfer_wallet_all') }}", "Bulk Wallets Updated!");
        handleAjaxFormSubmit('#form_single_wallet', "{{ url('/update_user_wallet') }}", "User Wallet Updated Successfully!");

        // KYC Switch
        $(document).on('change', '.KycStatusSwitch', function() {
            var currentCheckbox = $(this);
            var isChecked = currentCheckbox.is(':checked');
            var switchLabel = currentCheckbox.val();
            var checkedVal = isChecked ? 1 : 0;
            var tableName = "tj_user_app";

            if (confirm("Are you sure you want to update KYC status?")) {
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
                    success: function(data) {
                        notifyUser("KYC Status Updated", "", "success");
                    },
                    error: function() {
                        notifyUser("Something Went Wrong!", "", "error");
                        currentCheckbox.prop('checked', !isChecked);
                    }
                });
            } else {
                currentCheckbox.prop('checked', !isChecked);
            }
        });

        // Active Status Toggle
        $(document).on("click", "input[name='isActive']", function() {
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
                success: function() {},
            });
        });

        function clearOtherInline(otherInputId) {
            var el = document.getElementById(otherInputId);
            if (el) el.value = '';
        }
    </script>
@endsection

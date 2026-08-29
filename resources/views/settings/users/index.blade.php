@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Consumer Users</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">Consumers</li>
            </ol>
        </div>
    </div>

    {{-- ========== QUICK EDIT MODAL ========== --}}
    <div class="modal fade" id="quickEditModal" tabindex="-1" aria-labelledby="quickEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="quickEditModalLabel"><i class="fa fa-edit me-2"></i>Quick Edit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="qe_user_id">
                    <input type="hidden" id="qe_field">
                    <input type="hidden" id="qe_user_type" value="consumer">
                    <div id="qe_form_area"></div>
                    <div id="qe_alert" class="d-none mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fa fa-times me-1"></i>Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="qe_save_btn"><i class="fa fa-save me-1"></i>Save Changes</button>
                </div>
            </div>
        </div>
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
                                        <option value="">{{trans("lang.status")}}</option>
                                        <option value="active" {{isset($_GET['status_selector']) && $_GET['status_selector']=='active' ? 'selected':'' }}>{{trans("lang.active")}}</option>
                                        <option value="inactive" {{isset($_GET['status_selector']) && $_GET['status_selector']=='inactive' ? 'selected':'' }}>{{trans("lang.in_active")}}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <input type="text" placeholder="dd-mm-yyyy" class="form-control filteredRecords" id="daterange" name="daterange" value="{{ request('daterange') }}" readonly />
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
                        <div class="userlist-topsearch d-flex mb-3 align-items-center flex-wrap" style="gap:8px;">
                            <a class="btn btn-primary btn-sm" href="{!! route('users.create') !!}"><i class="fa fa-plus mr-1"></i>Add Consumer</a>
                            <form action="{{ route('users') }}" method="get" class="d-flex align-items-center ml-auto" style="gap:6px; flex-wrap:wrap;">
                                <select name="selected_search" class="form-control form-control-sm" style="width:130px;">
                                    <option value="prenom" {{ (isset($_GET['selected_search']) && $_GET['selected_search']=='prenom') ? 'selected' : '' }}>Name</option>
                                    <option value="email" {{ (isset($_GET['selected_search']) && $_GET['selected_search']=='email') ? 'selected' : '' }}>Email</option>
                                    <option value="phone" {{ (isset($_GET['selected_search']) && $_GET['selected_search']=='phone') ? 'selected' : '' }}>Mobile</option>
                                </select>
                                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search..." value="{{ $_GET['search'] ?? '' }}" style="width:180px;">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></button>
                                <a class="btn btn-sm btn-warning" href="{{url('users')}}">Clear</a>
                            </form>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download mr-1"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
                                    <li><a class="dropdown-item" href="{{ route('export.data', ['type'=>'excel','model'=>'UserApp']) }}">Excel</a></li>
                                    <li><a class="dropdown-item" href="{{ route('export.data', ['type'=>'pdf','model'=>'UserApp']) }}">PDF</a></li>
                                    <li><a class="dropdown-item" href="{{ route('export.data', ['type'=>'csv','model'=>'UserApp']) }}">CSV</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Schedule Buttons -->
                        <div class="row mb-3">
                            <div class="col-12 d-flex flex-wrap" style="gap:8px;">
                                <button type="button" class="btn btn-primary btn-sm btn-schedule-trigger font-weight-bold" data-target-form="sender_receiver" data-title="Sender Receiver Schedule">
                                    <i class="fa fa-exchange-alt mr-1"></i> Sender Receiver
                                </button>
                                <button type="button" class="btn btn-info btn-sm text-white btn-schedule-trigger font-weight-bold" data-target-form="daily_increment" data-title="Daily Increment Schedule">
                                    <i class="fa fa-chart-line mr-1"></i> Daily Increment
                                </button>
                                <button type="button" class="btn btn-warning btn-sm text-white btn-schedule-trigger font-weight-bold" data-target-form="deduction" data-title="Deduction Schedule">
                                    <i class="fa fa-minus-circle mr-1"></i> Deduction
                                </button>
                                <button type="button" class="btn btn-success btn-sm btn-schedule-trigger font-weight-bold" data-target-form="refer_earn" data-title="Refer & Earn Schedule">
                                    <i class="fa fa-gift mr-1"></i> Refer & Earn
                                </button>
                                <button type="button" class="btn btn-dark btn-sm btn-schedule-trigger font-weight-bold" data-target-form="transfer_bulk" data-title="Transfer / Adjust Wallet (Bulk)">
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

                        <div class="table-responsive">
                            <table id="example24" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:40px;"><input type="checkbox" id="is_active"><label class="m-0 ml-1" for="is_active"><a id="deleteAll" class="do_not_delete text-danger" href="javascript:void(0)"><i class="fa fa-trash"></i></a></label></th>
                                        <th class="text-center">S No</th>
                                        <th>Role / Category</th>
                                        <th>Type</th>
                                        <th>User Name <small class="text-muted">(click to edit)</small></th>
                                        <th>Email <small class="text-muted">(click)</small></th>
                                        <th>Mobile <small class="text-muted">(click)</small></th>
                                        <th>Alternate No <small class="text-muted">(click)</small></th>
                                        <th>Wallet Balance</th>
                                        <th>Cashback</th>
                                        <th>Refer &amp; Earn</th>
                                        <th>KYC Status</th>
                                        <th>Aadhaar No <small class="text-muted">(click)</small></th>
                                        <th>Status</th>
                                        <th>Active Plan <small class="text-muted">(click)</small></th>
                                        <th>MPIN</th>
                                        <th>Pocket No</th>
                                        <th>Registration Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($users) > 0)
                                        @foreach($users as $index => $customer)
                                        <tr>
                                            {{-- Select --}}
                                            <td class="delete-all text-center">
                                                <input type="checkbox" id="is_open_{{$customer->id}}" class="is_open common_selector" dataid="{{$customer->id}}" value="{{ !empty($customer->ac_no) ? $customer->ac_no : $customer->id }}">
                                                <label for="is_open_{{$customer->id}}" class="m-0"></label>
                                            </td>
                                            {{-- S No --}}
                                            <td class="text-center font-weight-bold">{{ $users->firstItem() + $index }}</td>
                                            {{-- Role --}}
                                            <td><span class="badge badge-info"><i class="fa fa-user mr-1"></i>Consumer</span></td>
                                            {{-- Type --}}
                                            <td><span class="badge badge-secondary">Individual</span></td>
                                            {{-- User Name → click to edit popup --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger font-weight-bold text-primary"
                                                   data-id="{{ $customer->id }}"
                                                   data-field="name"
                                                   data-user-type="consumer"
                                                   data-prenom="{{ $customer->prenom }}"
                                                   data-nom="{{ $customer->nom }}"
                                                   data-label="User Name"
                                                   title="Click to edit name">
                                                    {{ $customer->prenom }} {{ $customer->nom }}
                                                </a>
                                            </td>
                                            {{-- Email → click to edit --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger text-dark"
                                                   data-id="{{ $customer->id }}"
                                                   data-field="email"
                                                   data-user-type="consumer"
                                                   data-value="{{ $customer->email }}"
                                                   data-label="Email Address"
                                                   title="Click to edit email">
                                                    {{ $customer->email ?: '—' }}
                                                </a>
                                            </td>
                                            {{-- Mobile → click to edit --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger font-weight-bold text-dark"
                                                   data-id="{{ $customer->id }}"
                                                   data-field="phone"
                                                   data-user-type="consumer"
                                                   data-value="{{ $customer->phone }}"
                                                   data-label="Mobile Number"
                                                   title="Click to edit mobile">
                                                    {{ $customer->phone }}
                                                </a>
                                            </td>
                                            {{-- Alternate → click to edit --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger text-muted"
                                                   data-id="{{ $customer->id }}"
                                                   data-field="alternate_phone"
                                                   data-user-type="consumer"
                                                   data-value="{{ $customer->alternate_phone }}"
                                                   data-label="Alternate Number"
                                                   title="Click to edit alternate number">
                                                    {{ $customer->alternate_phone ?: '—' }}
                                                </a>
                                            </td>
                                            {{-- Wallet --}}
                                            <td>
                                                <a href="{{route('users.walletstransaction', ['id'=>$customer->id])}}" class="badge badge-success px-2 py-1" style="font-size:13px;" title="View Wallet History">
                                                    {{ $currency_symbol ?? \App\Helpers\Helper::getCurrencySymbol() }}{{ number_format(floatval($customer->amount ?? 0), 2) }}
                                                </a>
                                                <br>
                                                <button type="button"
                                                    class="btn py-0 px-2 btn-xs btn-outline-info font-weight-bold mt-1 editwallet"
                                                    data-amount="{{ $customer->amount }}"
                                                    data-earn_amount="{{ $customer->earn_amount }}"
                                                    data-ac_no="{{ !empty($customer->ac_no) ? $customer->ac_no : $customer->id }}"
                                                    data-id="{{ $customer->id }}"
                                                    data-name="{{ $customer->prenom }} {{ $customer->nom }}"
                                                    style="font-size: 11px;">
                                                    <i class="fa fa-wallet mr-1"></i> Edit Wallet
                                                </button>
                                            </td>
                                            {{-- Cashback --}}
                                            <td>
                                                <a href="{{route('users.walletstransaction', ['id'=>$customer->id])}}" class="badge badge-warning px-2 py-1 text-dark" style="font-size:13px;" title="View Cashback History">
                                                    {{ $currency_symbol ?? \App\Helpers\Helper::getCurrencySymbol() }}{{ number_format(floatval($customer->earn_amount ?? 0), 2) }}
                                                </a>
                                            </td>
                                            {{-- Refer & Earn --}}
                                            <td>
                                                @php
                                                    $rCode = $customer->referral_code ?? null;
                                                    if (empty($rCode) && \Illuminate\Support\Facades\Schema::hasTable('referral')) {
                                                        $rCode = \DB::table('referral')->where('user_id', $customer->id)->value('referral_code');
                                                    }
                                                    if (empty($rCode)) {
                                                        $rCode = 'FIIN' . str_pad((string)$customer->id, 6, '0', STR_PAD_LEFT);
                                                    }
                                                @endphp
                                                <a href="{{ url('referral-engine') }}" class="badge badge-info px-2 py-1" style="font-size:13px;" title="Refer & Earn Engine">
                                                    <i class="fa fa-gift mr-1"></i>{{ $rCode }}
                                                </a>
                                            </td>
                                            {{-- KYC Status → link to KYC page --}}
                                            <td>
                                                <a href="{{route('users.kycVerification')}}" title="Manage KYC Status">
                                                    @if(($customer->kyc_status ?? '') == '1')
                                                        <span class="badge badge-success"><i class="fa fa-check-circle mr-1"></i>Approved</span>
                                                    @else
                                                        <span class="badge badge-danger"><i class="fa fa-times-circle mr-1"></i>Pending</span>
                                                    @endif
                                                </a>
                                            </td>
                                            {{-- Aadhaar → click to edit --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger badge badge-light border font-weight-bold"
                                                   style="font-family:monospace;"
                                                   data-id="{{ $customer->id }}"
                                                   data-field="aadhar_number"
                                                   data-user-type="consumer"
                                                   data-value="{{ $customer->aadhar_number }}"
                                                   data-label="Aadhaar Number"
                                                   title="Click to edit Aadhaar">
                                                    {{ $customer->aadhar_number ?: 'N/A' }}
                                                </a>
                                            </td>
                                            {{-- Status Toggle --}}
                                            <td>
                                                <label class="switch mb-0">
                                                    <input type="checkbox" class="user-status-toggle" data-id="{{ $customer->id }}" {{ $customer->statut == 'yes' ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                            {{-- Active Plan → click to edit --}}
                                            @php
                                                $planDisplay = $customer->consumer_plan_display ?? 'Standard';
                                                if (empty($customer->consumer_plan_display) && !empty($customer->consumer_plan)) {
                                                    $rawPlan = trim((string)$customer->consumer_plan);
                                                    if (str_starts_with($rawPlan, '{') || str_starts_with($rawPlan, '[')) {
                                                        $decoded = json_decode($rawPlan, true);
                                                        $planDisplay = is_array($decoded) ? ($decoded['name'] ?? $decoded['title'] ?? $decoded['plan_name'] ?? 'Standard') : $rawPlan;
                                                    } else {
                                                        $planDisplay = $rawPlan;
                                                    }
                                                }
                                            @endphp
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger badge badge-primary px-2 py-1 font-weight-bold"
                                                   data-id="{{ $customer->id }}"
                                                   data-field="active_plan"
                                                   data-user-type="consumer"
                                                   data-value="{{ $planDisplay }}"
                                                   data-label="Active Plan"
                                                   title="Click to edit/upgrade plan">
                                                    <i class="fa fa-star mr-1"></i>{{ $planDisplay }}
                                                </a>
                                            </td>
                                            {{-- MPIN --}}
                                            <td style="white-space: nowrap;">
                                                @if(!empty($customer->m_pin))
                                                    <div class="d-inline-flex align-items-center bg-white border px-2 py-1 rounded shadow-sm" style="font-family: monospace;">
                                                        <span class="mpin-val font-weight-bold" data-secret="{{ $customer->m_pin }}" data-masked="••••" style="color: #0f172a; letter-spacing: 2px; font-size: 13px;">••••</span>
                                                        <a href="javascript:void(0)" class="text-primary ml-2 toggle-mpin-eye" onclick="toggleMpinSecret(this)" title="Show/Hide MPIN">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">N/A</span>
                                                @endif
                                            </td>
                                            {{-- Pocket No --}}
                                            <td><span class="font-weight-bold" style="font-family:monospace;">{{ $customer->ac_no ?: 'N/A' }}</span></td>
                                            {{-- Reg Date --}}
                                            <td><small class="text-muted">{{ date('d M Y h:i A', strtotime($customer->creer)) }}</small></td>
                                            {{-- Actions --}}
                                            <td class="text-center">
                                                <a href="{{route('users.show', ['id'=>$customer->id])}}" class="btn btn-xs btn-outline-info px-2 py-1" title="View Details"><i class="fa fa-eye"></i></a>
                                                <a href="{{route('users.edit', ['id'=>$customer->id])}}" class="btn btn-xs btn-outline-primary px-2 py-1" title="Full Edit"><i class="fa fa-edit"></i></a>
                                                <a href="{{route('user.delete', ['id'=>$customer->id])}}" class="delete-btn btn btn-xs btn-outline-danger px-2 py-1" title="Delete"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="19" class="text-center py-4 text-muted">No consumers found.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} consumers
                            </div>
                            {{ $users->appends(request()->query())->links('pagination.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<style>
/* Hide table until DataTables is initialized to prevent UI glitch */
#example24_wrapper {
    opacity: 0;
    transition: opacity 0.3s ease;
}
#example24_wrapper.dt-initialized {
    opacity: 1;
}
</style>
<script>
var QUICK_UPDATE_URL = "{{ route('users.quickUpdate') }}";
var CSRF_TOKEN       = "{{ csrf_token() }}";
var _qeModal;

// Initialize DataTables properly to prevent UI glitch
$(document).ready(function() {
    // Hide loader and show content
    $('#pageLoader').hide();
    $('#contentSection').fadeIn();
    
    if ($.fn.DataTable && $('#example24').length) {
        $('#example24').DataTable({
            'pageLength': 20,
            'lengthMenu': [10, 20, 50, 100],
            'order': [[1, 'desc']],
            'columnDefs': [
                { 'orderable': false, 'targets': [0, 17] },
                { 'searchable': false, 'targets': [0, 17] }
            ],
            'language': {
                'search': '_INPUT_',
                'searchPlaceholder': 'Search...'
            },
            'initComplete': function() {
                $('#example24_wrapper').addClass('dt-initialized');
            }
        });
    }
    setDate();
});

function setDate() {
    let initialDateRange = $('#daterange').val();
    if ($.fn.daterangepicker) {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: { format: 'DD-MM-YYYY', cancelLabel: 'Clear' }
        });
        if (initialDateRange) {
            let dates = initialDateRange.split(' - ');
            if (dates.length === 2 && $('#daterange').data('daterangepicker')) {
                $('#daterange').data('daterangepicker').setStartDate(dates[0]);
                $('#daterange').data('daterangepicker').setEndDate(dates[1]);
                $('#daterange').val(initialDateRange);
            }
        }
        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
            $('#filterForm').submit();
        });
        $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#filterForm').submit();
        });
    }
}

$(document).on('change', '.filteredRecords', function() {
    $('#filterForm').submit();
});

// ---- Select All Checkbox ----
$(document).on('click', '#is_active', function() {
    $(".is_open").prop('checked', $(this).prop('checked'));
});

// ---- Delete All (Bulk Delete) ----
$(document).on('click', '#deleteAll', function(e) {
    e.preventDefault();
    var checkedBoxes = $('.is_open:checked');
    if (checkedBoxes.length) {
        if (confirm('Are you sure you want to delete the selected consumer(s)?')) {
            var arrayUsers = [];
            checkedBoxes.each(function() {
                var dataId = $(this).attr('dataid') || $(this).data('id');
                if (dataId) arrayUsers.push(dataId);
            });
            if (arrayUsers.length) {
                var url = "{{ url('user/delete') }}/" + encodeURIComponent(JSON.stringify(arrayUsers));
                window.location.href = url;
            }
        }
    } else {
        alert('Please select at least one record to delete.');
    }
});

// ---- Single Delete Confirmation ----
$(document).on('click', '.delete-btn', function(e) {
    if (!confirm('Are you sure you want to delete this record?')) {
        e.preventDefault();
    }
});

// ---- Quick Edit Form Builder ----
function buildFormHtml(field, data) {
    if (field === 'name') {
        return `
            <div class="form-row">
                <div class="col-6 form-group">
                    <label class="font-weight-bold">First Name</label>
                    <input type="text" id="qe_prenom" class="form-control" value="${escHtml(data.prenom || '')}">
                </div>
                <div class="col-6 form-group">
                    <label class="font-weight-bold">Last Name</label>
                    <input type="text" id="qe_nom" class="form-control" value="${escHtml(data.nom || '')}">
                </div>
            </div>`;
    } else if (field === 'active_plan') {
        return `
            <div class="form-group">
                <label class="font-weight-bold">Active Plan</label>
                <select id="qe_value" class="form-control">
                    <option value="Standard" ${data.value=='Standard'?'selected':''}>Standard</option>
                    <option value="Silver" ${data.value=='Silver'?'selected':''}>Silver</option>
                    <option value="Gold" ${data.value=='Gold'?'selected':''}>Gold</option>
                    <option value="Platinum" ${data.value=='Platinum'?'selected':''}>Platinum</option>
                </select>
            </div>`;
    } else {
        var inputType = field === 'email' ? 'email' : 'text';
        var placeholder = {
            email: 'Enter email address',
            phone: 'Enter mobile number',
            alternate_phone: 'Enter alternate number',
            aadhar_number: 'Enter 12-digit Aadhaar number'
        }[field] || 'Enter value';
        return `
            <div class="form-group">
                <label class="font-weight-bold">${escHtml(data.label)}</label>
                <input type="${inputType}" id="qe_value" class="form-control" value="${escHtml(data.value || '')}" placeholder="${placeholder}">
            </div>`;
    }
}

function escHtml(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ---- Trigger Popup Edit ----
$(document).on('click', '.qe-trigger', function() {
    var $el = $(this);
    var field = $el.data('field');
    var data = {
        prenom: $el.data('prenom'),
        nom: $el.data('nom'),
        value: $el.data('value'),
        label: $el.data('label')
    };
    $('#qe_user_id').val($el.data('id'));
    $('#qe_field').val(field);
    $('#qe_user_type').val($el.data('user-type') || 'consumer');
    $('#quickEditModalLabel').html('<i class="fa fa-edit me-2"></i>Edit: ' + escHtml(data.label || field));
    $('#qe_form_area').html(buildFormHtml(field, data));
    $('#qe_alert').addClass('d-none').html('');
    window._qeTriggerEl = $el;
    if (!_qeModal) { _qeModal = new bootstrap.Modal(document.getElementById('quickEditModal')); }
    _qeModal.show();
});

// ---- Save Quick Edit ----
$('#qe_save_btn').on('click', function() {
    var id       = $('#qe_user_id').val();
    var field    = $('#qe_field').val();
    var userType = $('#qe_user_type').val();
    var $trigger = window._qeTriggerEl;
    $('#qe_alert').addClass('d-none').html('');

    var postData = {
        _token: CSRF_TOKEN,
        id: id,
        field: field,
        user_type: userType
    };

    if (field === 'name') {
        postData.prenom = $('#qe_prenom').val();
        postData.nom    = $('#qe_nom').val();
    } else {
        postData.value = $('#qe_value').val();
    }

    $('#qe_save_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Saving...');

    $.ajax({
        url: QUICK_UPDATE_URL,
        method: 'POST',
        data: postData,
        success: function(res) {
            if (res.success) {
                if (field === 'name') {
                    $trigger.text(postData.prenom + ' ' + postData.nom);
                    $trigger.data('prenom', postData.prenom).data('nom', postData.nom);
                } else {
                    $trigger.text(postData.value || '—');
                    $trigger.data('value', postData.value);
                }
                _qeModal.hide();
                $.toast({ heading: 'Success', text: 'Updated successfully!', icon: 'success', position: 'top-right', hideAfter: 3000 });
            } else {
                $('#qe_alert').removeClass('d-none').addClass('alert alert-danger').html(res.message || 'Update failed');
            }
        },
        error: function(xhr) {
            $('#qe_alert').removeClass('d-none').addClass('alert alert-danger').html('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
        },
        complete: function() {
            $('#qe_save_btn').prop('disabled', false).html('<i class="fa fa-save me-1"></i>Save Changes');
        }
    });
});

$(document).on('change', '.user-status-toggle', function(e) {
    var checkbox = $(this);
    var isChecked = checkbox.is(':checked');
    var id = checkbox.data('id');
    var newStatusText = isChecked ? 'Active' : 'Inactive';
    var statusVal = isChecked ? 'yes' : 'no';

    // Revert visual switch state until user confirms
    checkbox.prop('checked', !isChecked);

    Swal.fire({
        title: 'Change Consumer Status?',
        text: 'Are you sure you want to set this Consumer to ' + newStatusText + '?',
        icon: isChecked ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: isChecked ? '#10b981' : '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, change to ' + newStatusText,
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) {
            checkbox.prop('checked', isChecked);
            $.ajax({
                url: "{{ url('/users/switch') }}",
                type: "POST",
                data: {
                    _token: CSRF_TOKEN,
                    id: id,
                    status: statusVal
                },
                success: function(res) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Consumer set to ' + newStatusText,
                        showConfirmButton: false,
                        timer: 2500
                    });
                },
                error: function(xhr) {
                    checkbox.prop('checked', !isChecked);
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update status.';
                    Swal.fire('Action Blocked', msg, 'error');
                }
            });
        }
    });
});

// ==========================================
// SCHEDULES & WALLET ACTION PANEL LOGIC
// ==========================================
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
    try {
        syncPanelTargetUsers();
    } catch(e) {
        console.error("Target sync error:", e);
    }
    $('#' + formId).show();
    $('#inlineActionPanel').slideDown(250);

    try {
        var panelEl = document.getElementById("inlineActionPanel");
        if (panelEl) {
            panelEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    } catch(e) {}
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
    } else if ($.toast) {
        $.toast({ heading: title, text: text || '', icon: icon || 'info', position: 'top-right', hideAfter: 3000 });
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

function toggleMpinSecret(btn) {
    var $span = $(btn).siblings('.mpin-val');
    var $icon = $(btn).find('i');
    var secret = $span.data('secret');
    var masked = $span.data('masked');
    if ($span.text() === masked) {
        $span.text(secret).css({'color': '#4338ca', 'font-size': '13px', 'letter-spacing': '1px'});
        $icon.removeClass('fa-eye').addClass('fa-eye-slash text-danger');
    } else {
        $span.text(masked).css({'color': '#0f172a', 'font-size': '13px', 'letter-spacing': '2px'});
        $icon.removeClass('fa-eye-slash text-danger').addClass('fa-eye text-primary');
    }
}

function clearOtherInline(otherInputId) {
    document.getElementById(otherInputId).value = '';
}
</script>
@endsection
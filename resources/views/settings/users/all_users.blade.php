@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Master User Database</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                <li class="breadcrumb-item">User Management</li>
                <li class="breadcrumb-item active">All Users</li>
            </ol>
        </div>
    </div>

    {{-- ========== QUICK EDIT MODAL ========== --}}
    <div class="modal fade" id="quickEditModal" tabindex="-1" aria-labelledby="quickEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg,#5B4FE9,#7c73f5); color:#fff;">
                    <h5 class="modal-title" id="quickEditModalLabel"><i class="fa fa-edit me-2"></i>Quick Edit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="qe_id">
                    <input type="hidden" id="qe_field">
                    <input type="hidden" id="qe_user_type">
                    <div id="qe_form_area"></div>
                    <div id="qe_alert" class="d-none mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fa fa-times me-1"></i>Cancel</button>
                    <button type="button" class="btn btn-sm text-white" id="qe_save_btn" style="background:#5B4FE9;"><i class="fa fa-save me-1"></i>Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        {{-- Filter --}}
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('users.all') }}" class="row align-items-center">
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size:12px;">User Type</label>
                        <select name="user_type_filter" class="form-control form-control-sm">
                            <option value="">All Users</option>
                            <option value="consumer" {{ request('user_type_filter')=='consumer' ? 'selected':'' }}>Consumers</option>
                            <option value="driver"   {{ request('user_type_filter')=='driver'   ? 'selected':'' }}>Business / Drivers</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size:12px;">Search By</label>
                        <select name="selected_search" class="form-control form-control-sm">
                            <option value="prenom" {{ request('selected_search')=='prenom' ? 'selected':'' }}>Name</option>
                            <option value="phone"  {{ request('selected_search')=='phone'  ? 'selected':'' }}>Mobile</option>
                            <option value="email"  {{ request('selected_search')=='email'  ? 'selected':'' }}>Email</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size:12px;">Keyword</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size:12px;">Per Page</label>
                        <select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="50" {{ request('per_page', 50) == 50 ? 'selected':'' }}>50 / page</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected':'' }}>100 / page</option>
                            <option value="200" {{ request('per_page') == 200 ? 'selected':'' }}>200 / page</option>
                        </select>
                    </div>
                    <div class="col-md-3 mt-md-4 text-right">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter mr-1"></i>Filter</button>
                        <a href="{{ route('users.all') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="allUsersTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:30px;"><input type="checkbox" id="is_active"><label class="m-0 ml-1" for="is_active"><a id="deleteAll" class="do_not_delete text-danger" href="javascript:void(0)"><i class="fa fa-trash"></i></a></label></th>
                                        <th class="text-center">#</th>
                                        <th>Role/Cat</th>
                                        <th>Type</th>
                                        <th>Name</th>
                                        <th>Zone</th>
                                        <th class="text-center">Rating</th>
                                        <th class="text-center">Tot Book</th>
                                        <th>Top Svc</th>
                                        <th class="text-right">Tot Earn</th>
                                        <th class="text-right">Tod Earn</th>
                                        <th class="text-right">Wallet</th>
                                        <th class="text-right">Cashback</th>
                                        <th class="text-right">Promo Val</th>
                                        <th class="text-right">W/D Req</th>
                                        <th class="text-right">W/D Setld</th>
                                        <th class="text-center">W/D Stage</th>
                                        <th class="text-center">Ref Code</th>
                                        <th>Ref By</th>
                                        <th>Mobile</th>
                                        <th>Email</th>
                                        <th>Alt No</th>
                                        <th class="text-center">KYC</th>
                                        <th>Aadhaar</th>
                                        <th class="text-center">Status</th>
                                        <th>Plan/Doc</th>
                                        <th class="text-center">MPIN</th>
                                        <th>Pocket</th>
                                        <th>Reg Date</th>
                                        <th class="text-center">Act</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($users) > 0)
                                        @foreach($users as $index => $user)
                                        <tr>
                                            {{-- Select --}}
                                            <td class="delete-all text-center">
                                                <input type="checkbox" id="is_open_{{$user->id}}_{{$user->user_type}}" class="is_open" dataid="{{$user->id}}">
                                                <label for="is_open_{{$user->id}}_{{$user->user_type}}" class="m-0"></label>
                                            </td>
                                            {{-- # --}}
                                            <td class="text-center font-weight-bold">{{ $users->firstItem() + $index }}</td>
                                            {{-- Role/Cat --}}
                                            <td>
                                                @if($user->user_type == 'consumer')
                                                    <span class="badge badge-info badge-compact"><i class="fa fa-user mr-1"></i>User</span>
                                                @else
                                                    <div class="d-flex flex-wrap align-items-center" style="gap:2px; max-width:170px;">
                                                        @if(!empty($user->category_list) && count($user->category_list) > 0)
                                                            @foreach(array_slice($user->category_list, 0, 2) as $catName)
                                                                <span class="badge badge-primary badge-compact">{{ $catName }}</span>
                                                            @endforeach
                                                            @if(count($user->category_list) > 2)
                                                                <span class="badge badge-light border text-muted badge-compact">+{{ count($user->category_list) - 2 }}</span>
                                                            @endif
                                                        @else
                                                            <span class="badge badge-primary badge-compact">{{ $user->role ?? 'Provider' }}</span>
                                                        @endif

                                                        @if(!empty($user->vehicle_types) && count($user->vehicle_types) > 0)
                                                            <span class="badge badge-dark badge-compact text-warning"><i class="fa fa-motorcycle mr-1"></i>{{ $user->vehicle_types[0] }}</span>
                                                        @endif

                                                        @if(!empty($user->specific_services) && count($user->specific_services) > 0)
                                                            <span class="badge badge-success badge-compact"><i class="fa fa-wrench mr-1"></i>{{ $user->specific_services[0] }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            {{-- Type --}}
                                            <td>
                                                @if($user->user_type == 'consumer')
                                                    <span class="badge badge-secondary badge-compact">Indiv</span>
                                                @else
                                                    <span class="badge badge-warning text-dark badge-compact font-weight-bold">Biz</span>
                                                @endif
                                            </td>
                                            {{-- Name --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger font-weight-bold text-primary"
                                                   data-id="{{ $user->id }}"
                                                   data-field="name"
                                                   data-user-type="{{ $user->user_type }}"
                                                   data-prenom="{{ $user->prenom }}"
                                                   data-nom="{{ $user->nom }}"
                                                   data-label="Name"
                                                   title="Click to edit">{{ $user->prenom }} {{ $user->nom }}</a>
                                                @if(!empty($user->business_name))
                                                    <small class="text-muted d-block" style="font-size:10px;"><i class="fa fa-building mr-1"></i>{{ Str::limit($user->business_name, 15) }}</small>
                                                @endif
                                            </td>
                                            {{-- Zone --}}
                                            <td>
                                                @if(!empty($user->zone_name))
                                                    <span class="badge badge-light border text-dark badge-compact">{{ $user->zone_name }}</span>
                                                @else
                                                    <span class="text-muted small">null</span>
                                                @endif
                                            </td>
                                            {{-- Rating --}}
                                            <td class="text-center">
                                                <span class="badge badge-warning text-dark badge-compact font-weight-bold"><i class="fa fa-star text-warning mr-1"></i>{{ number_format($user->rating ?? 5.0, 1) }}</span>
                                            </td>
                                            {{-- Tot Book --}}
                                            <td class="text-center font-weight-bold">
                                                <span class="badge {{ ($user->tot_book ?? 0) > 0 ? 'badge-info' : 'badge-light text-muted' }} badge-compact">{{ $user->tot_book ?? 0 }}</span>
                                            </td>
                                            {{-- Top Svc --}}
                                            <td>
                                                @if(!empty($user->top_svc) && $user->top_svc !== '—')
                                                    <span class="badge badge-success badge-compact">{{ $user->top_svc }}</span>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            {{-- Tot Earn --}}
                                            <td class="text-right font-weight-bold text-success">
                                                {{ ($user->tot_earn ?? 0) > 0 ? '₹' . number_format($user->tot_earn, 0) : '₹0' }}
                                            </td>
                                            {{-- Tod Earn --}}
                                            <td class="text-right font-weight-bold {{ ($user->tod_earn ?? 0) > 0 ? 'text-primary' : 'text-muted' }}">
                                                {{ ($user->tod_earn ?? 0) > 0 ? '₹' . number_format($user->tod_earn, 0) : '₹0' }}
                                            </td>
                                            {{-- Wallet --}}
                                            <td class="text-right">
                                                @if($user->user_type == 'consumer')
                                                    <a href="{{ route('users.walletstransaction', ['id'=>$user->id]) }}" class="badge badge-success badge-compact" title="Wallet">
                                                @else
                                                    <a href="{{ route('walletstransactions.driver', ['id'=>$user->id]) }}" class="badge badge-success badge-compact" title="Wallet">
                                                @endif
                                                    ₹{{ number_format(floatval($user->amount ?? 0), 0) }}</a>
                                            </td>
                                            {{-- Cashback --}}
                                            <td class="text-right">
                                                @if($user->user_type == 'consumer')
                                                    <a href="{{ route('users.walletstransaction', ['id'=>$user->id]) }}" class="badge badge-warning px-1 py-0 text-dark badge-compact" title="Cashback">
                                                @else
                                                    <a href="{{ route('walletstransactions.driver', ['id'=>$user->id]) }}" class="badge badge-warning px-1 py-0 text-dark badge-compact" title="Cashback">
                                                @endif
                                                    ₹{{ number_format(floatval($user->earn_amount ?? 0), 0) }}</a>
                                            </td>
                                            {{-- Promo Val --}}
                                            <td class="text-right">
                                                @if(($user->promo_val ?? 0) > 0)
                                                    <span class="badge badge-primary badge-compact font-weight-bold">₹{{ number_format($user->promo_val, 0) }}</span>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            {{-- W/D Req --}}
                                            <td class="text-right">
                                                @if(($user->with_req ?? 0) > 0)
                                                    <span class="badge badge-danger badge-compact font-weight-bold">₹{{ number_format($user->with_req, 0) }}</span>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            {{-- W/D Setld --}}
                                            <td class="text-right font-weight-bold text-success">
                                                @if(($user->with_settled ?? 0) > 0)
                                                    ₹{{ number_format($user->with_settled, 0) }}
                                                @else
                                                    <span class="text-muted small font-weight-normal">—</span>
                                                @endif
                                            </td>
                                            {{-- W/D Stage --}}
                                            <td class="text-center">
                                                @if(($user->with_stage ?? 'None') === 'Pend')
                                                    <span class="badge badge-warning text-dark badge-compact font-weight-bold">Pend</span>
                                                @elseif(($user->with_stage ?? 'None') === 'Paid')
                                                    <span class="badge badge-success badge-compact font-weight-bold">Paid</span>
                                                @elseif(($user->with_stage ?? 'None') === 'Rej')
                                                    <span class="badge badge-danger badge-compact font-weight-bold">Rej</span>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            {{-- Ref Code --}}
                                            <td class="text-center">
                                                @if(!empty($user->referral_code))
                                                    <span class="badge {{ $user->user_type == 'consumer' ? 'badge-info' : 'badge-dark' }} badge-compact font-weight-bold"
                                                          style="font-family:monospace; cursor:pointer;"
                                                          onclick="navigator.clipboard.writeText('{{ $user->referral_code }}'); alert('Copied: {{ $user->referral_code }}');"
                                                          title="Click to copy">{{ $user->referral_code }}</span>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            {{-- Ref By --}}
                                            <td>
                                                @if(!empty($user->referred_by_name) || !empty($user->referred_by_code))
                                                    <span class="font-weight-bold" style="color: #1e293b;">
                                                        {{ Str::limit($user->referred_by_name ?: 'Unknown', 14) }}
                                                    </span>
                                                    @if(!empty($user->referred_by_type))
                                                        <span class="badge {{ $user->referred_by_type == 'Business' ? 'badge-warning text-dark' : 'badge-info' }} badge-compact ml-1">
                                                            {{ $user->referred_by_type == 'Business' ? 'Biz' : 'User' }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">NA</span>
                                                @endif
                                            </td>
                                            {{-- Mobile --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger font-weight-bold text-dark"
                                                   data-id="{{ $user->id }}"
                                                   data-field="phone"
                                                   data-user-type="{{ $user->user_type }}"
                                                   data-value="{{ $user->phone }}"
                                                   data-label="Mobile"
                                                   title="Edit">{{ $user->phone }}</a>
                                            </td>
                                            {{-- Email --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger text-dark"
                                                   data-id="{{ $user->id }}"
                                                   data-field="email"
                                                   data-user-type="{{ $user->user_type }}"
                                                   data-value="{{ $user->email }}"
                                                   data-label="Email"
                                                   title="Edit">{{ $user->email ? Str::limit($user->email, 16) : '—' }}</a>
                                            </td>
                                            {{-- Alt No --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger text-muted"
                                                   data-id="{{ $user->id }}"
                                                   data-field="alternate_phone"
                                                   data-user-type="{{ $user->user_type }}"
                                                   data-value="{{ $user->alternate_phone }}"
                                                   data-label="Alt No"
                                                   title="Edit">{{ $user->alternate_phone ? Str::limit($user->alternate_phone, 10) : '—' }}</a>
                                            </td>
                                            {{-- KYC --}}
                                            <td class="text-center">
                                                <a href="{{ route('users.kycVerification') }}" title="KYC">
                                                    @if(($user->kyc_status ?? '') == '1')
                                                        <span class="badge badge-success badge-compact">Appr</span>
                                                    @else
                                                        <span class="badge badge-danger badge-compact">Pend</span>
                                                    @endif
                                                </a>
                                            </td>
                                            {{-- Aadhaar --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger font-weight-bold text-dark"
                                                   style="font-family:monospace; font-size:11px;"
                                                   data-id="{{ $user->id }}"
                                                   data-field="aadhar_number"
                                                   data-user-type="{{ $user->user_type }}"
                                                   data-value="{{ $user->aadhar_no }}"
                                                   data-label="Aadhaar"
                                                   title="Edit">{{ $user->aadhar_no ? Str::limit($user->aadhar_no, 12) : '—' }}</a>
                                            </td>
                                            {{-- Status --}}
                                            <td class="text-center">
                                                <label class="switch mb-0">
                                                    <input type="checkbox"
                                                           class="any-status-toggle"
                                                           data-id="{{ $user->id }}"
                                                           data-type="{{ $user->user_type }}"
                                                           {{ $user->statut == 'yes' ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                            {{-- Plan/Doc --}}
                                            @php
                                                $planDisplay = $user->active_plan_display ?? 'Std';
                                                if (empty($user->active_plan_display) && !empty($user->active_plan)) {
                                                    $rawPlan = trim((string)$user->active_plan);
                                                    if (str_starts_with($rawPlan, '{') || str_starts_with($rawPlan, '[')) {
                                                        $decoded = json_decode($rawPlan, true);
                                                        $planDisplay = is_array($decoded) ? ($decoded['name'] ?? $decoded['title'] ?? 'Std') : $rawPlan;
                                                    } else {
                                                        $planDisplay = $rawPlan;
                                                    }
                                                }
                                            @endphp
                                            <td>
                                                @if($user->user_type == 'consumer')
                                                    <a href="javascript:void(0)"
                                                       class="qe-trigger badge badge-primary badge-compact"
                                                       data-id="{{ $user->id }}"
                                                       data-field="active_plan"
                                                       data-user-type="consumer"
                                                       data-value="{{ $planDisplay }}"
                                                       data-label="Plan"
                                                       title="Plan">{{ Str::limit($planDisplay, 10) }}</a>
                                                @else
                                                    <a href="{{ route('driver.documentView', ['id'=>$user->id]) }}" class="badge badge-info badge-compact" title="Docs">Docs</a>
                                                @endif
                                            </td>
                                            {{-- MPIN --}}
                                            <td class="text-center" style="white-space: nowrap;">
                                                @if(!empty($user->mpin))
                                                    <div class="d-inline-flex align-items-center" style="font-family: monospace;">
                                                        <span class="mpin-val" data-secret="{{ $user->mpin }}" data-masked="••" style="font-size:11px;">••</span>
                                                        <a href="javascript:void(0)" class="text-primary ml-1 toggle-mpin-eye" onclick="toggleMpinSecret(this)" title="Show"><i class="fa fa-eye" style="font-size:10px;"></i></a>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            {{-- Pocket --}}
                                            <td><span class="font-weight-bold text-dark" style="font-family:monospace; font-size:11px;">{{ $user->ac_no ?: '—' }}</span></td>
                                            {{-- Reg Date --}}
                                            <td><small class="text-muted">{{ date('d M y', strtotime($user->creer)) }}</small></td>
                                            {{-- Act --}}
                                            <td class="text-center" style="white-space:nowrap;">
                                                @if($user->user_type == 'consumer')
                                                    <a href="{{ route('users.show', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-info" title="View"><i class="fa fa-eye"></i></a>
                                                    <a href="{{ route('users.edit', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                                    <a href="{{ route('user.delete', ['id'=>$user->id]) }}" class="delete-btn btn btn-xs btn-outline-danger" title="Delete"><i class="fa fa-trash"></i></a>
                                                @else
                                                    <a href="{{ route('driver.show', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-info" title="View"><i class="fa fa-eye"></i></a>
                                                    <a href="{{ route('driver.documentView', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-warning" title="Docs"><i class="fa fa-file-pdf-o"></i></a>
                                                    <a href="{{ route('drivers.edit', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                                    <a href="{{ route('driver.delete', ['id'=>$user->id]) }}" class="delete-btn btn btn-xs btn-outline-danger" title="Delete"><i class="fa fa-trash"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="30" class="text-center py-3 text-muted">No users found.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap" style="gap:10px;">
                            <div class="d-flex align-items-center" style="gap:12px;">
                                <div class="text-muted small">
                                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="text-muted small mr-1 font-weight-bold">Per Page:</span>
                                    <select class="form-control form-control-sm py-0" style="width: auto; height: 28px; font-size: 12px;" onchange="updatePerPage(this.value)">
                                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                                    </select>
                                </div>
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
#allUsersTable_wrapper {
    opacity: 0;
    transition: opacity 0.3s ease;
}
#allUsersTable_wrapper.dt-initialized {
    opacity: 1;
}

/* Compact Table Layout - Remove extra gaps & bring details close together */
.page-wrapper {
    padding-top: 10px !important;
}
.page-wrapper .container-fluid {
    padding-left: 8px !important;
    padding-right: 8px !important;
}
.page-wrapper .page-titles {
    margin-bottom: 6px !important;
    padding: 4px 0 !important;
}
.card {
    margin-bottom: 8px !important;
    border-radius: 6px !important;
}
.card .card-body {
    padding: 6px 8px !important;
}
.table-responsive {
    margin: 0 !important;
}
#allUsersTable {
    margin: 0 !important;
    font-size: 11px !important;
}
#allUsersTable th {
    padding: 4px 5px !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    background-color: #f1f5f9 !important;
    color: #1e293b !important;
    white-space: nowrap !important;
    text-transform: uppercase !important;
    letter-spacing: 0.2px !important;
    border-bottom: 2px solid #cbd5e1 !important;
    vertical-align: middle !important;
}
#allUsersTable td {
    padding: 3px 5px !important;
    font-size: 11px !important;
    line-height: 1.15 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
}
#allUsersTable tr:hover {
    background-color: #f8fafc !important;
}
.badge-compact {
    font-size: 9.5px !important;
    padding: 1px 4px !important;
    line-height: 1.1 !important;
    border-radius: 3px !important;
    font-weight: 600 !important;
}
.btn-xs {
    padding: 1px 4px !important;
    font-size: 10.5px !important;
    line-height: 1.2 !important;
}
.switch {
    position: relative;
    display: inline-block;
    width: 24px;
    height: 14px;
    margin: 0 !important;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .2s;
    border-radius: 14px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 10px;
    width: 10px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    transition: .2s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #5B4FE9;
}
input:checked + .slider:before {
    transform: translateX(10px);
}
</style>
<script>
var USER_UPDATE_URL   = "{{ route('users.quickUpdate') }}";
var DRIVER_UPDATE_URL = "{{ route('driver.quickUpdate') }}";
var CSRF_TOKEN        = "{{ csrf_token() }}";

function updatePerPage(val) {
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', val);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

// Initialize DataTables properly to prevent UI glitch
$(document).ready(function() {
    // Hide loader and show content
    $('#pageLoader').hide();
    $('#filterSection').fadeIn();
    
    $('#allUsersTable').DataTable({
        'paging': false,
        'info': false,
        'order': [[1, 'desc']],
        'columnDefs': [
            { 'orderable': false, 'targets': [0, -1] },
            { 'searchable': false, 'targets': [0, -1] }
        ],
        'language': {
            'search': '_INPUT_',
            'searchPlaceholder': 'Search on page...'
        },
        'initComplete': function() {
            $('#allUsersTable_wrapper').addClass('dt-initialized');
        }
    });
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
        if (confirm('Are you sure you want to delete the selected user(s)?')) {
            var consumerIds = [];
            var driverIds = [];
            checkedBoxes.each(function() {
                var dataId = $(this).attr('dataid') || $(this).data('id');
                var type = $(this).data('type');
                if (dataId) {
                    if (type === 'driver') {
                        driverIds.push(dataId);
                    } else {
                        consumerIds.push(dataId);
                    }
                }
            });
            if (consumerIds.length) {
                window.location.href = "{{ url('user/delete') }}/" + encodeURIComponent(JSON.stringify(consumerIds));
            } else if (driverIds.length) {
                window.location.href = "{{ url('driver/delete') }}/" + encodeURIComponent(JSON.stringify(driverIds));
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

function escHtml(str) {
    return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function buildFormHtml(field, data) {
    if (field === 'name') {
        return `
            <div class="form-row">
                <div class="col-6 form-group">
                    <label class="font-weight-bold">First Name</label>
                    <input type="text" id="qe_prenom" class="form-control" value="${escHtml(data.prenom)}">
                </div>
                <div class="col-6 form-group">
                    <label class="font-weight-bold">Last Name</label>
                    <input type="text" id="qe_nom" class="form-control" value="${escHtml(data.nom)}">
                </div>
            </div>`;
    }
    if (field === 'active_plan') {
        return `
            <div class="form-group">
                <label class="font-weight-bold">Active Plan</label>
                <select id="qe_value" class="form-control">
                    <option value="Standard" ${data.value=='Standard'?'selected':''}>Standard</option>
                    <option value="Silver"   ${data.value=='Silver'?'selected':''}>Silver</option>
                    <option value="Gold"     ${data.value=='Gold'?'selected':''}>Gold</option>
                    <option value="Platinum" ${data.value=='Platinum'?'selected':''}>Platinum</option>
                </select>
            </div>`;
    }
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
            <input type="${inputType}" id="qe_value" class="form-control" value="${escHtml(data.value)}" placeholder="${placeholder}">
        </div>`;
}

$(document).on('click', '.qe-trigger', function() {
    var $el = $(this);
    var data = {
        prenom: $el.data('prenom') || '',
        nom:    $el.data('nom') || '',
        value:  $el.data('value') || '',
        label:  $el.data('label') || $el.data('field')
    };
    $('#qe_id').val($el.data('id'));
    $('#qe_field').val($el.data('field'));
    $('#qe_user_type').val($el.data('user-type') || 'consumer');
    $('#quickEditModalLabel').html('<i class="fa fa-edit me-2"></i>Edit: ' + escHtml(data.label));
    $('#qe_form_area').html(buildFormHtml($el.data('field'), data));
    $('#qe_alert').addClass('d-none').html('');
    window._qeTriggerEl = $el;
    if (!window._qeModal) { window._qeModal = new bootstrap.Modal(document.getElementById('quickEditModal')); }
    window._qeModal.show();
});

$('#qe_save_btn').on('click', function() {
    var id       = $('#qe_id').val();
    var field    = $('#qe_field').val();
    var userType = $('#qe_user_type').val();
    var $trigger = window._qeTriggerEl;
    var url      = (userType === 'driver') ? DRIVER_UPDATE_URL : USER_UPDATE_URL;

    var postData = { _token: CSRF_TOKEN, id: id, field: field, user_type: userType };
    if (field === 'name') {
        postData.prenom = $('#qe_prenom').val();
        postData.nom    = $('#qe_nom').val();
    } else {
        postData.value = $('#qe_value').val();
    }

    $('#qe_save_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Saving...');
    $('#qe_alert').addClass('d-none').html('');

    $.ajax({
        url: url,
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
                window._qeModal.hide();
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

$(document).on('change', '.any-status-toggle', function(e) {
    var checkbox = $(this);
    var isChecked = checkbox.is(':checked');
    var id   = checkbox.data('id');
    var type = checkbox.data('type');
    var newStatusText = isChecked ? 'Active' : 'Inactive';
    var stat = isChecked ? 'yes' : 'no';
    var url  = (type === 'driver' || type === 'business') ? "{{ url('/driver/switch') }}" : "{{ url('/users/switch') }}";

    // Revert visual checkbox until confirmed
    checkbox.prop('checked', !isChecked);

    Swal.fire({
        title: 'Change Status?',
        text: 'Are you sure you want to set this user to ' + newStatusText + '?',
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
                url: url,
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    id: id,
                    ischeck: isChecked ? 'true' : 'false',
                    status: stat
                },
                success: function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Status changed to ' + newStatusText,
                        showConfirmButton: false,
                        timer: 2000
                    });
                },
                error: function(xhr) {
                    checkbox.prop('checked', !isChecked);
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Status toggle failed.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        }
    });
});
</script>
@endsection

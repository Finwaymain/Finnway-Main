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
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size:12px;">User Type</label>
                        <select name="user_type_filter" class="form-control form-control-sm">
                            <option value="">All Users (Consumer &amp; Business)</option>
                            <option value="consumer" {{ request('user_type_filter')=='consumer' ? 'selected':'' }}>Consumers (Customers)</option>
                            <option value="driver"   {{ request('user_type_filter')=='driver'   ? 'selected':'' }}>Business / Drivers / Delivery</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size:12px;">Search By</label>
                        <select name="selected_search" class="form-control form-control-sm">
                            <option value="prenom" {{ request('selected_search')=='prenom' ? 'selected':'' }}>Name</option>
                            <option value="phone"  {{ request('selected_search')=='phone'  ? 'selected':'' }}>Mobile</option>
                            <option value="email"  {{ request('selected_search')=='email'  ? 'selected':'' }}>Email</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size:12px;">Keyword</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 mt-md-4 text-right">
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
                                        <th class="text-center" style="width:40px;"><input type="checkbox" id="is_active"><label class="m-0 ml-1" for="is_active"><a id="deleteAll" class="do_not_delete text-danger" href="javascript:void(0)"><i class="fa fa-trash"></i></a></label></th>
                                        <th class="text-center">S No</th>
                                        <th>Role / Category</th>
                                        <th>Type</th>
                                        <th>User Name <small class="text-muted">(click)</small></th>
                                        <th class="text-center">Referral Code</th>
                                        <th>Email <small class="text-muted">(click)</small></th>
                                        <th>Mobile <small class="text-muted">(click)</small></th>
                                        <th>Alternate No <small class="text-muted">(click)</small></th>
                                        <th>Wallet Balance</th>
                                        <th>Cashback</th>
                                        <th>Refer &amp; Earn</th>
                                        <th>KYC Status</th>
                                        <th>Aadhaar No <small class="text-muted">(click)</small></th>
                                        <th>Status</th>
                                        <th>Active Plan / Doc</th>
                                        <th>MPIN</th>
                                        <th>Pocket No</th>
                                        <th>Registration Date</th>
                                        <th class="text-center">Actions</th>
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
                                            {{-- S No --}}
                                            <td class="text-center font-weight-bold">{{ $users->firstItem() + $index }}</td>
                                            {{-- Role --}}
                                            <td>
                                                @if($user->user_type == 'consumer')
                                                    <span class="badge badge-info"><i class="fa fa-user mr-1"></i>Consumer</span>
                                                @else
                                                    @if(!empty($user->category_list) && count($user->category_list) > 0)
                                                        @foreach($user->category_list as $catName)
                                                            <span class="badge badge-primary px-2 py-1 mb-1 d-inline-block">
                                                                <i class="fa fa-briefcase mr-1"></i>{{ $catName }}
                                                            </span><br>
                                                        @endforeach
                                                    @else
                                                        <span class="badge badge-primary"><i class="fa fa-briefcase mr-1"></i>{{ $user->role ?? 'Business Provider' }}</span>
                                                    @endif
                                                @endif
                                            </td>
                                            {{-- Type --}}
                                            <td>
                                                @if($user->user_type == 'consumer')
                                                    <span class="badge badge-secondary">Individual</span>
                                                @else
                                                    <span class="badge badge-warning text-dark">Business</span>
                                                @endif
                                            </td>
                                            {{-- User Name → popup --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger font-weight-bold text-primary"
                                                   data-id="{{ $user->id }}"
                                                   data-field="name"
                                                   data-user-type="{{ $user->user_type }}"
                                                   data-prenom="{{ $user->prenom }}"
                                                   data-nom="{{ $user->nom }}"
                                                   data-label="User Name"
                                                   title="Click to edit">{{ $user->prenom }} {{ $user->nom }}</a>
                                                @if(!empty($user->business_name))
                                                    <br><small class="text-muted"><i class="fa fa-building mr-1"></i>{{ $user->business_name }}</small>
                                                @endif
                                            </td>
                                            {{-- Referral Code --}}
                                            <td class="text-center">
                                                @if(!empty($user->referral_code))
                                                    <span class="badge {{ $user->user_type == 'consumer' ? 'badge-info' : 'badge-dark' }} px-2 py-1 font-weight-bold shadow-sm"
                                                          style="font-family: monospace; font-size: 13px; letter-spacing: 0.5px; cursor: pointer;"
                                                          onclick="navigator.clipboard.writeText('{{ $user->referral_code }}'); alert('Referral code copied: {{ $user->referral_code }}');"
                                                          title="Click to copy referral code">
                                                        <i class="fa fa-ticket mr-1"></i>{{ $user->referral_code }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            {{-- Email → popup --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger text-dark"
                                                   data-id="{{ $user->id }}"
                                                   data-field="email"
                                                   data-user-type="{{ $user->user_type }}"
                                                   data-value="{{ $user->email }}"
                                                   data-label="Email Address"
                                                   title="Click to edit">{{ $user->email ?: '—' }}</a>
                                            </td>
                                            {{-- Mobile → popup --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger font-weight-bold text-dark"
                                                   data-id="{{ $user->id }}"
                                                   data-field="phone"
                                                   data-user-type="{{ $user->user_type }}"
                                                   data-value="{{ $user->phone }}"
                                                   data-label="Mobile Number"
                                                   title="Click to edit">{{ $user->phone }}</a>
                                            </td>
                                            {{-- Alternate → popup --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger text-muted"
                                                   data-id="{{ $user->id }}"
                                                   data-field="alternate_phone"
                                                   data-user-type="{{ $user->user_type }}"
                                                   data-value="{{ $user->alternate_phone }}"
                                                   data-label="Alternate Number"
                                                   title="Click to edit">{{ $user->alternate_phone ?: '—' }}</a>
                                            </td>
                                            {{-- Wallet --}}
                                            <td>
                                                @if($user->user_type == 'consumer')
                                                    <a href="{{ route('users.walletstransaction', ['id'=>$user->id]) }}" class="badge badge-success px-2 py-1" style="font-size:13px;" title="Wallet History">
                                                @else
                                                    <a href="{{ route('walletstransactions.driver', ['id'=>$user->id]) }}" class="badge badge-success px-2 py-1" style="font-size:13px;" title="Wallet History">
                                                @endif
                                                    {{ $currency_symbol ?? \App\Helpers\Helper::getCurrencySymbol() }}{{ number_format(floatval($user->amount ?? 0), 2) }}</a>
                                            </td>
                                            {{-- Cashback --}}
                                            <td>
                                                @if($user->user_type == 'consumer')
                                                    <a href="{{ route('users.walletstransaction', ['id'=>$user->id]) }}" class="badge badge-warning px-2 py-1 text-dark" style="font-size:13px;" title="Cashback History">
                                                @else
                                                    <a href="{{ route('walletstransactions.driver', ['id'=>$user->id]) }}" class="badge badge-warning px-2 py-1 text-dark" style="font-size:13px;" title="Cashback History">
                                                @endif
                                                    {{ $currency_symbol ?? \App\Helpers\Helper::getCurrencySymbol() }}{{ number_format(floatval($user->earn_amount ?? 0), 2) }}</a>
                                            </td>
                                            {{-- Refer & Earn --}}
                                            <td><span class="badge badge-info px-2 py-1" style="font-size:13px;">—</span></td>
                                            {{-- KYC --}}
                                            <td>
                                                <a href="{{ route('users.kycVerification') }}" title="Manage KYC">
                                                    @if(($user->kyc_status ?? '') == '1')
                                                        <span class="badge badge-success"><i class="fa fa-check-circle mr-1"></i>Approved</span>
                                                    @else
                                                        <span class="badge badge-danger"><i class="fa fa-times-circle mr-1"></i>Pending</span>
                                                    @endif
                                                </a>
                                            </td>
                                            {{-- Aadhaar → popup --}}
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="qe-trigger badge badge-light border font-weight-bold"
                                                   style="font-family:monospace;"
                                                   data-id="{{ $user->id }}"
                                                   data-field="aadhar_number"
                                                   data-user-type="{{ $user->user_type }}"
                                                   data-value="{{ $user->aadhar_no }}"
                                                   data-label="Aadhaar Number"
                                                   title="Click to edit Aadhaar">{{ $user->aadhar_no ?: 'N/A' }}</a>
                                            </td>
                                            {{-- Status --}}
                                            <td>
                                                <label class="switch mb-0">
                                                    <input type="checkbox"
                                                           class="any-status-toggle"
                                                           data-id="{{ $user->id }}"
                                                           data-type="{{ $user->user_type }}"
                                                           {{ $user->statut == 'yes' ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                            {{-- Active Plan / Doc --}}
                                            @php
                                                $planDisplay = $user->active_plan_display ?? 'Standard';
                                                if (empty($user->active_plan_display) && !empty($user->active_plan)) {
                                                    $rawPlan = trim((string)$user->active_plan);
                                                    if (str_starts_with($rawPlan, '{') || str_starts_with($rawPlan, '[')) {
                                                        $decoded = json_decode($rawPlan, true);
                                                        $planDisplay = is_array($decoded) ? ($decoded['name'] ?? $decoded['title'] ?? $decoded['plan_name'] ?? 'Standard') : $rawPlan;
                                                    } else {
                                                        $planDisplay = $rawPlan;
                                                    }
                                                }
                                            @endphp
                                            <td>
                                                @if($user->user_type == 'consumer')
                                                    <a href="javascript:void(0)"
                                                       class="qe-trigger badge badge-primary px-2 py-1 font-weight-bold"
                                                       data-id="{{ $user->id }}"
                                                       data-field="active_plan"
                                                       data-user-type="consumer"
                                                       data-value="{{ $planDisplay }}"
                                                       data-label="Active Plan"
                                                       title="Edit / Upgrade Plan">
                                                        <i class="fa fa-star mr-1"></i>{{ $planDisplay }}
                                                    </a>
                                                @else
                                                    <a href="{{ route('driver.documentView', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-info px-2 py-1 font-weight-bold" title="View Documents">
                                                        <i class="fa fa-file-text-o mr-1"></i>View Docs
                                                    </a>
                                                @endif
                                            </td>
                                            {{-- MPIN --}}
                                            <td style="white-space: nowrap;">
                                                @if(!empty($user->mpin))
                                                    <div class="d-inline-flex align-items-center bg-white border px-2 py-1 rounded shadow-sm" style="font-family: monospace;">
                                                        <span class="mpin-val font-weight-bold" data-secret="{{ $user->mpin }}" data-masked="••••" style="color: #0f172a; letter-spacing: 2px; font-size: 13px;">••••</span>
                                                        <a href="javascript:void(0)" class="text-primary ml-2 toggle-mpin-eye" onclick="toggleMpinSecret(this)" title="Show/Hide MPIN">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">N/A</span>
                                                @endif
                                            </td>
                                            {{-- Pocket No --}}
                                            <td><span class="font-weight-bold text-dark" style="font-family:monospace;">{{ $user->ac_no ?: 'N/A' }}</span></td>
                                            {{-- Reg Date --}}
                                            <td><small class="text-muted">{{ date('d M Y h:i A', strtotime($user->creer)) }}</small></td>
                                            {{-- Actions --}}
                                            <td class="text-center" style="white-space:nowrap;">
                                                @if($user->user_type == 'consumer')
                                                    <a href="{{ route('users.show', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-info px-2 py-1" title="Details"><i class="fa fa-eye"></i></a>
                                                    <a href="{{ route('users.edit', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-primary px-2 py-1" title="Full Edit"><i class="fa fa-edit"></i></a>
                                                    <a href="{{ route('user.delete', ['id'=>$user->id]) }}" class="delete-btn btn btn-xs btn-outline-danger px-2 py-1" title="Delete"><i class="fa fa-trash"></i></a>
                                                @else
                                                    <a href="{{ route('driver.show', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-info px-2 py-1" title="Details"><i class="fa fa-eye"></i></a>
                                                    <a href="{{ route('driver.documentView', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-warning px-2 py-1" title="Docs"><i class="fa fa-file-pdf-o"></i></a>
                                                    <a href="{{ route('drivers.edit', ['id'=>$user->id]) }}" class="btn btn-xs btn-outline-primary px-2 py-1" title="Full Edit"><i class="fa fa-edit"></i></a>
                                                    <a href="{{ route('driver.delete', ['id'=>$user->id]) }}" class="delete-btn btn btn-xs btn-outline-danger px-2 py-1" title="Delete"><i class="fa fa-trash"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="20" class="text-center py-4 text-muted">No users found.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
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
</style>
<script>
var USER_UPDATE_URL   = "{{ route('users.quickUpdate') }}";
var DRIVER_UPDATE_URL = "{{ route('driver.quickUpdate') }}";
var CSRF_TOKEN        = "{{ csrf_token() }}";

// Initialize DataTables properly to prevent UI glitch
$(document).ready(function() {
    // Hide loader and show content
    $('#pageLoader').hide();
    $('#filterSection').fadeIn();
    
    $('#allUsersTable').DataTable({
        'pageLength': 20,
        'lengthMenu': [10, 20, 50, 100],
        'order': [[1, 'desc']],
        'columnDefs': [
            { 'orderable': false, 'targets': [0, 18] },
            { 'searchable': false, 'targets': [0, 18] }
        ],
        'language': {
            'search': '_INPUT_',
            'searchPlaceholder': 'Search...'
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

@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor font-weight-bold">Business Provider KYC Verification</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                <li class="breadcrumb-item">User Management</li>
                <li class="breadcrumb-item active">KYC Verification</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Search & Category Filters Bar -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('users.kycVerification') }}" class="row align-items-center">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size: 12px;">Service Role / Category Filter</label>
                        <select name="role_filter" class="form-control form-control-sm" style="border-radius: 8px;">
                            <option value="">All Service Roles & Professions</option>
                            @if(isset($categoriesList) && count($categoriesList) > 0)
                                @foreach($categoriesList as $cat)
                                    <option value="{{ $cat->id }}" {{ request('role_filter') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->libelle }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size: 12px;">Keyword Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" style="border-radius: 8px;" placeholder="Search Name, Phone, Email, or Business Name..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2 mt-md-4 text-right">
                        <button type="submit" class="btn btn-sm btn-primary px-3 font-weight-bold" style="background-color: #5B4FE9; border-radius: 8px;"><i class="fa fa-filter mr-1"></i> Filter</button>
                        <a href="{{ route('users.kycVerification') }}" class="btn btn-sm btn-outline-secondary px-3 font-weight-bold" style="border-radius: 8px;">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Provider KYC Table Card -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h5 class="m-0 font-weight-bold text-dark"><i class="mdi mdi-account-search text-primary mr-2" style="font-size: 20px;"></i> Pending Provider List</h5>
                        <span class="badge badge-pill badge-light text-muted border px-3 py-2" style="font-size: 12px;">Showing {{ $pendingBusinessUsers->count() }} of {{ $pendingBusinessUsers->total() }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="width: 100%;">
                                <thead style="background-color: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
                                    <tr>
                                        <th class="py-3 px-4 text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Photo</th>
                                        <th class="py-3 px-4 text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Name</th>
                                        <th class="py-3 px-4 text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Role / Category</th>
                                        <th class="py-3 px-4 text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Mobile</th>
                                        <th class="py-3 px-4 text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Email</th>
                                        <th class="py-3 px-4 text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Uploaded Documents</th>
                                        <th class="py-3 px-4 text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">KYC Status</th>
                                        <th class="py-3 px-4 text-muted text-uppercase font-weight-bold text-center" style="font-size: 11px; letter-spacing: 0.5px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($pendingBusinessUsers->count() > 0)
                                        @foreach($pendingBusinessUsers as $user)
                                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                                <td class="py-3 px-4">
                                                    @if(!empty($user->photo_path))
                                                        <img class="rounded-circle shadow-sm" style="width:46px; height:46px; object-fit: cover; border: 2px solid #5B4FE9;" src="{{ asset('assets/images/driver/'.$user->photo_path) }}" alt="user">
                                                    @else
                                                        <img class="rounded-circle" style="width:46px; height:46px; border: 2px solid #E2E8F0;" src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="user">
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4">
                                                    <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $user->prenom }} {{ $user->nom }}</div>
                                                    @if(!empty($user->business_name) && strtolower($user->business_name) != strtolower($user->prenom . ' ' . $user->nom))
                                                        <span class="text-muted font-weight-normal" style="font-size: 12px;"><i class="fa fa-building-o mr-1"></i> {{ $user->business_name }}</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4">
                                                    <span class="badge px-3 py-2 font-weight-bold text-white shadow-sm" style="font-size: 11px; background: linear-gradient(135deg, #5B4FE9 0%, #3B82F6 100%); border-radius: 20px;">
                                                        <i class="mdi mdi-briefcase mr-1"></i> {{ $user->role ?? 'Service Provider' }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <span class="font-weight-semibold text-dark" style="font-size: 13px;"><i class="fa fa-phone mr-1 text-muted"></i> {{ $user->phone }}</span>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <span class="text-muted" style="font-size: 13px;">{{ $user->email ?? 'N/A' }}</span>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <div class="d-flex" style="gap: 6px;">
                                                        @if(!empty($user->licence))
                                                            <a href="{{ asset('assets/images/driver/'.$user->licence) }}" target="_blank" class="btn btn-xs btn-outline-primary" style="border-radius: 6px; font-size: 11px; padding: 4px 10px;" title="View Licence Document">
                                                                <i class="fa fa-id-card mr-1"></i> Licence
                                                            </a>
                                                        @endif
                                                        @if(!empty($user->cnib))
                                                            <a href="{{ asset('assets/images/driver/'.$user->cnib) }}" target="_blank" class="btn btn-xs btn-outline-info" style="border-radius: 6px; font-size: 11px; padding: 4px 10px;" title="View CNIB Document">
                                                                <i class="fa fa-file-text mr-1"></i> CNIB
                                                            </a>
                                                        @endif
                                                        @if(empty($user->licence) && empty($user->cnib))
                                                            <span class="badge badge-light text-muted font-italic" style="font-size: 11px;">No Docs Uploaded</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4">
                                                    @if($user->kyc_status == '2')
                                                        <span class="badge badge-danger px-3 py-2" style="border-radius: 12px;"><i class="fa fa-times-circle mr-1"></i> Rejected</span>
                                                    @else
                                                        <span class="badge badge-warning text-dark font-weight-bold px-3 py-2" style="border-radius: 12px; background-color: #FEF3C7; color: #92400E !important;">
                                                            <i class="fa fa-clock-o mr-1"></i> Pending Verification
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    <div class="d-flex justify-content-center" style="gap: 6px;">
                                                        <button class="btn btn-sm btn-success text-white font-weight-bold action-kyc shadow-sm" style="border-radius: 8px; padding: 6px 14px; background-color: #10B981; border: none;" data-id="{{ $user->id }}" data-type="driver" data-status="approved">
                                                            <i class="fa fa-check mr-1"></i> Approve
                                                        </button>
                                                        <button class="btn btn-sm btn-danger text-white font-weight-bold action-kyc shadow-sm" style="border-radius: 8px; padding: 6px 14px; background-color: #EF4444; border: none;" data-id="{{ $user->id }}" data-type="driver" data-status="disapproved">
                                                            <i class="fa fa-times mr-1"></i> Reject
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" align="center" class="text-muted py-5">
                                                <div style="background: #F0FDF4; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                                                    <i class="mdi mdi-checkbox-marked-circle-outline text-success" style="font-size: 32px;"></i>
                                                </div>
                                                <h5 class="text-dark font-weight-bold mb-1">Queue Clear!</h5>
                                                <p class="text-muted mb-0" style="font-size: 13px;">No pending Service or Business Provider KYC verification requests found.</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 border-top bg-light">
                            {{ $pendingBusinessUsers->links('pagination.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).on('click', '.action-kyc', function() {
            var userId = $(this).data('id');
            var userType = $(this).data('type');
            var status = $(this).data('status');
            var actionText = (status === 'approved') ? 'Approve' : 'Reject';

            if (confirm("Are you sure you want to " + actionText + " KYC verification for this provider?")) {
                $.ajax({
                    url: "{{ route('users.kyc.update') }}",
                    type: "POST",
                    data: {
                        id: userId,
                        type: userType,
                        status: status
                    },
                    success: function(response) {
                        if(response.success) {
                            alert("KYC status updated successfully!");
                            location.reload();
                        } else {
                            alert("Failed to update status.");
                        }
                    },
                    error: function() {
                        alert("Error updating KYC status.");
                    }
                });
            }
        });
    });
</script>
@endsection

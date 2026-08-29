@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Create Sub-Admin Staff</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sub-admins.index') }}">Sub-Admins</a></li>
                <li class="breadcrumb-item active">Create Staff</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body">
                <h4 class="card-title font-weight-bold border-bottom pb-2">Sub-Admin User Details & Permission Assignment</h4>
                
                <form action="{{ route('sub-admins.store') }}" method="POST">
                    @csrf

                    <div class="row mt-3">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Staff Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Rahul Sharma" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. subadmin@fiinway.com" value="{{ old('email') }}" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Login Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Account Status</label>
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" checked>
                                <label class="custom-control-label font-weight-bold text-success" for="is_active">Active Account</label>
                            </div>
                        </div>
                    </div>

                    <h5 class="font-weight-bold mt-4 mb-2 text-primary border-bottom pb-2">
                        <i class="mdi mdi-shield-key"></i> Dashboard Sidebar Access Permissions
                    </h5>
                    <p class="text-muted small">Select which sections this Sub-Admin can access. Only permitted pages will be visible in their sidebar.</p>

                    <!-- Default Access Granted Alert Banner -->
                    <div class="alert alert-info border-info">
                        <i class="mdi mdi-information"></i> <strong>Default Granted Access for New Sub-Admins:</strong>
                        <ul class="mb-0 mt-1 pl-3">
                            <li><strong>KYC Approval / Active & Deactive Status Access</strong></li>
                            <li><strong>Reply Customer MSG (Customer Care & Complaints)</strong></li>
                            <li><strong>All Type of Report Download Access</strong></li>
                        </ul>
                    </div>

                    <div class="row">
                        @foreach($permissionsMap as $key => $perm)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border rounded p-3 h-100 {{ $perm['default'] ? 'bg-light border-primary' : '' }}">
                                    <div class="custom-control custom-checkbox">
                                        <input 
                                            type="checkbox" 
                                            name="permissions[]" 
                                            value="{{ $key }}" 
                                            class="custom-control-input" 
                                            id="perm_{{ $key }}"
                                            {{ (is_array(old('permissions')) && in_array($key, old('permissions'))) || (!old('permissions') && $perm['default']) ? 'checked' : '' }}
                                        >
                                        <label class="custom-control-label font-weight-bold text-dark" for="perm_{{ $key }}">
                                            <i class="{{ $perm['icon'] }} text-primary"></i> {{ $perm['name'] }}
                                            @if($perm['default'])
                                                <span class="badge badge-info ml-1">Default Granted</span>
                                            @endif
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2 pl-4">{{ $perm['description'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-2 border-top">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fa fa-save"></i> Save & Create Sub-Admin Staff
                        </button>
                        <a href="{{ route('sub-admins.index') }}" class="btn btn-secondary px-4 ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

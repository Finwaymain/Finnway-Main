@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Edit Sub-Admin Permissions</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sub-admins.index') }}">Sub-Admins</a></li>
                <li class="breadcrumb-item active">Edit Access</li>
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
                <h4 class="card-title font-weight-bold border-bottom pb-2">Edit Sub-Admin: {{ $subAdmin->name }}</h4>
                
                <form action="{{ route('sub-admins.update', $subAdmin->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mt-3">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Staff Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $subAdmin->name) }}" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $subAdmin->email) }}" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Update Password (Leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control" placeholder="Optional new password">
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Account Status</label>
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" {{ $subAdmin->is_active ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold {{ $subAdmin->is_active ? 'text-success' : 'text-danger' }}" for="is_active">
                                    {{ $subAdmin->is_active ? 'Active Account' : 'Deactivated Account' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <h5 class="font-weight-bold mt-4 mb-2 text-primary border-bottom pb-2">
                        <i class="mdi mdi-shield-key"></i> Dashboard Sidebar Access Permissions
                    </h5>
                    <p class="text-muted small">Only checked sections will be visible in this Sub-Admin's dashboard sidebar.</p>

                    @php
                        $userPerms = is_array($subAdmin->permissions) ? $subAdmin->permissions : json_decode($subAdmin->permissions ?? '[]', true);
                        if (!is_array($userPerms)) $userPerms = [];
                    @endphp

                    <div class="row">
                        @foreach($permissionsMap as $key => $perm)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border rounded p-3 h-100 {{ in_array($key, $userPerms) ? 'bg-light border-primary' : '' }}">
                                    <div class="custom-control custom-checkbox">
                                        <input 
                                            type="checkbox" 
                                            name="permissions[]" 
                                            value="{{ $key }}" 
                                            class="custom-control-input" 
                                            id="perm_{{ $key }}"
                                            {{ in_array($key, $userPerms) ? 'checked' : '' }}
                                        >
                                        <label class="custom-control-label font-weight-bold text-dark" for="perm_{{ $key }}">
                                            <i class="{{ $perm['icon'] }} text-primary"></i> {{ $perm['name'] }}
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2 pl-4">{{ $perm['description'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-2 border-top">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fa fa-save"></i> Update Permissions
                        </button>
                        <a href="{{ route('sub-admins.index') }}" class="btn btn-secondary px-4 ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

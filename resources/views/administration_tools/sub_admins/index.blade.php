@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Sub-Admin Staff Management</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Sub-Admins</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-circle"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-lg">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title font-weight-bold mb-0">Sub-Admin Users & Dashboard Access</h4>
                            <a href="{{ route('sub-admins.create') }}" class="btn btn-primary btn-rounded shadow-sm">
                                <i class="fa fa-plus-circle"></i> Create Sub-Admin Staff
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped border">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Staff Name</th>
                                        <th>Email Address</th>
                                        <th>Granted Dashboard Permissions</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subAdmins as $key => $subAdmin)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                <strong>{{ $subAdmin->name }}</strong>
                                                <span class="badge badge-info ml-1">Sub-Admin</span>
                                            </td>
                                            <td>{{ $subAdmin->email }}</td>
                                            <td>
                                                @php
                                                    $perms = is_array($subAdmin->permissions) ? $subAdmin->permissions : json_decode($subAdmin->permissions ?? '[]', true);
                                                @endphp
                                                @if(empty($perms))
                                                    <span class="badge badge-secondary">No Access Granted</span>
                                                @else
                                                    @foreach($perms as $pKey)
                                                        @if(isset($permissionsMap[$pKey]))
                                                            <span class="badge badge-primary mb-1 p-2" title="{{ $permissionsMap[$pKey]['description'] }}">
                                                                <i class="{{ $permissionsMap[$pKey]['icon'] }}"></i> {{ $permissionsMap[$pKey]['name'] }}
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary mb-1 p-2">{{ $pKey }}</span>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>
                                                @if($subAdmin->is_active)
                                                    <span class="badge badge-success px-3 py-1">Active</span>
                                                @else
                                                    <span class="badge badge-danger px-3 py-1">Deactive</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('sub-admins.toggle-status', $subAdmin->id) }}" class="btn btn-sm {{ $subAdmin->is_active ? 'btn-warning' : 'btn-success' }}" title="Toggle Status">
                                                    <i class="fa {{ $subAdmin->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                </a>
                                                <a href="{{ route('sub-admins.edit', $subAdmin->id) }}" class="btn btn-sm btn-info" title="Edit Permissions">
                                                    <i class="fa fa-edit"></i> Edit Access
                                                </a>
                                                <form action="{{ route('sub-admins.destroy', $subAdmin->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Sub-Admin user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Sub-Admin">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="mdi mdi-account-off text-muted" style="font-size: 3rem;"></i>
                                                <p class="mt-2">No Sub-Admin staff users created yet. Click <strong>"Create Sub-Admin Staff"</strong> to add one.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

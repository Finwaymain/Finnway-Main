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

    <div class="container-fluid">
        <!-- Search & Filter Options -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('users.all') }}" class="row align-items-center">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size: 12px;">User Type</label>
                        <select name="user_type_filter" class="form-control form-control-sm">
                            <option value="">All Users (Consumers & Providers)</option>
                            <option value="consumer" {{ request('user_type_filter') == 'consumer' ? 'selected' : '' }}>Consumers Only (Customers)</option>
                            <option value="driver" {{ request('user_type_filter') == 'driver' ? 'selected' : '' }}>Business Users (Service Providers)</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size: 12px;">Search By</label>
                        <select name="selected_search" class="form-control form-control-sm">
                            <option value="prenom" {{ request('selected_search') == 'prenom' ? 'selected' : '' }}>Name</option>
                            <option value="phone" {{ request('selected_search') == 'phone' ? 'selected' : '' }}>Mobile Number</option>
                            <option value="email" {{ request('selected_search') == 'email' ? 'selected' : '' }}>Email</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="form-label font-weight-bold text-muted mb-1" style="font-size: 12px;">Keyword</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2 mt-md-4 text-right">
                        <button type="submit" class="btn btn-sm btn-primary" style="background-color: #5B4FE9;"><i class="fa fa-filter mr-1"></i> Filter</button>
                        <a href="{{ route('users.all') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Master List Grid -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered stylish-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>User ID</th>
                                        <th>Role / Category</th>
                                        <th>Type</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Wallet Balance</th>
                                        <th>KYC Status</th>
                                        <th>Status</th>
                                        <th>Registration Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($users) > 0)
                                        @foreach($users as $user)
                                            <tr>
                                                <td><span class="badge badge-secondary">#{{ $user->id }}</span></td>
                                                <td>
                                                    @if($user->user_type == 'consumer')
                                                        <span class="badge badge-info">
                                                            <i class="mdi mdi-account"></i> Customer
                                                        </span>
                                                    @else
                                                        <span class="badge badge-primary">
                                                            <i class="mdi mdi-briefcase"></i> {{ $user->role }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->user_type == 'consumer')
                                                        <span class="badge badge-info">Consumer</span>
                                                    @else
                                                        <span class="badge badge-warning">Business</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->user_type == 'consumer')
                                                        <a href="{{ route('users.show', ['id' => $user->id]) }}">
                                                            <strong>{{ $user->prenom }} {{ $user->nom }}</strong>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('driver.show', ['id' => $user->id]) }}">
                                                            <strong>{{ $user->business_name ?? $user->prenom . ' ' . $user->nom }}</strong>
                                                        </a>
                                                        @if(!empty($user->business_name))
                                                            <br><small class="text-muted">Owner: {{ $user->prenom }} {{ $user->nom }}</small>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td><span style="font-weight: 600; color: #334155;">{{ $user->phone }}</span></td>
                                                <td><strong>₹{{ number_format(floatval($user->amount), 2) }}</strong></td>
                                                <td>
                                                    @if($user->kyc_status == '1')
                                                        <span class="badge badge-success">Verified</span>
                                                    @else
                                                        <span class="badge badge-danger">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->statut == 'yes')
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ date('d M Y H:i', strtotime($user->creer)) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="9" align="center" class="text-muted py-4">No users found matching filter criteria.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $users->links('pagination.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

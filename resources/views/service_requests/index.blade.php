@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Home Service Bookings (Live Monitor)</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">Service Bookings</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Analytics Stat Cards -->
        <div class="row mb-3">
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card mb-0">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Total Bookings</h6>
                        <h4 class="mb-0 font-weight-bold text-dark">{{ $totalBookings }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card mb-0">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Auto-Accepted</h6>
                        <h4 class="mb-0 font-weight-bold text-success">{{ $autoAccepted }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card mb-0">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Searching Radius</h6>
                        <h4 class="mb-0 font-weight-bold text-warning">{{ $pendingMatch }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card mb-0">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Completed Services</h6>
                        <h4 class="mb-0 font-weight-bold text-primary">{{ $completed }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="card-title text-dark mb-0 font-weight-bold">Live Service Orders</h4>
                            <span class="badge badge-info" style="font-size: 11px;">Auto-Matched by App System</span>
                        </div>
                        
                        <!-- Status Filter Tabs -->
                        <div class="btn-group">
                            <a href="{{ route('service_requests', ['status' => 'all']) }}" class="btn btn-sm {{ $status == 'all' || !$status ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                            <a href="{{ route('service_requests', ['status' => 'pending']) }}" class="btn btn-sm {{ $status == 'pending' ? 'btn-warning' : 'btn-outline-secondary' }}">Pending Search</a>
                            <a href="{{ route('service_requests', ['status' => 'accepted']) }}" class="btn btn-sm {{ $status == 'accepted' ? 'btn-info' : 'btn-outline-secondary' }}">Accepted</a>
                            <a href="{{ route('service_requests', ['status' => 'in_progress']) }}" class="btn btn-sm {{ $status == 'in_progress' ? 'btn-purple' : 'btn-outline-secondary' }}">In Progress</a>
                            <a href="{{ route('service_requests', ['status' => 'completed']) }}" class="btn btn-sm {{ $status == 'completed' ? 'btn-success' : 'btn-outline-secondary' }}">Completed</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Customer</th>
                                        <th>Service Requested</th>
                                        <th>Scheduled Date/Time</th>
                                        <th>Assigned Provider</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $req)
                                    <tr>
                                        <td><strong>#SR-{{ $req->id }}</strong></td>
                                        <td>
                                            @if($req->user)
                                                <div class="font-weight-bold text-dark">{{ $req->user->prenom }} {{ $req->user->nom }}</div>
                                                <small class="text-muted">{{ $req->user->phone ?? $req->user->email }}</small>
                                            @else
                                                <span class="text-muted">User #{{ $req->user_id }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-primary">{{ $req->service_name ?? 'Home Service' }}</span>
                                            @if($req->description)
                                                <div class="small text-muted text-truncate" style="max-width: 200px;">{{ $req->description }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div><i class="fa fa-calendar-o text-muted mr-1"></i>{{ $req->preferred_date ?? 'Immediate' }}</div>
                                            <small class="text-muted"><i class="fa fa-clock-o mr-1"></i>{{ $req->preferred_time ?? 'NOW' }}</small>
                                        </td>
                                        <td>
                                            @if($req->provider)
                                                <div class="font-weight-bold text-success"><i class="fa fa-check-circle mr-1"></i>{{ $req->provider->prenom }} {{ $req->provider->nom }}</div>
                                                <small class="text-muted">{{ $req->provider->phone }}</small>
                                            @elseif($req->driver_id)
                                                <span class="text-dark">Provider #{{ $req->driver_id }}</span>
                                            @else
                                                <span class="badge badge-warning"><i class="fa fa-spinner fa-spin mr-1"></i>Searching Nearby (5-30km)</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($req->status == 'completed')
                                                <span class="badge badge-success">Completed</span>
                                            @elseif($req->status == 'accepted')
                                                <span class="badge badge-info">Auto-Accepted</span>
                                            @elseif($req->status == 'in_progress' || $req->status == 'on_the_way' || $req->status == 'reached')
                                                <span class="badge badge-primary">In Progress</span>
                                            @elseif($req->status == 'cancelled' || $req->status == 'rejected')
                                                <span class="badge badge-danger">Cancelled</span>
                                            @else
                                                <span class="badge badge-warning">Matching Provider...</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('service_requests.show', $req->id) }}" class="btn btn-sm btn-outline-primary" title="View Order Details"><i class="fa fa-eye"></i> View</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No home service bookings found matching current filter.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            {{ $requests->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

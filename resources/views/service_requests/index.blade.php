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
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="card mb-0">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Total Bookings</h6>
                        <h4 class="mb-0 font-weight-bold text-dark">{{ $totalBookings }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="card mb-0">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Active Searching</h6>
                        <h4 class="mb-0 font-weight-bold text-warning">{{ $pendingMatch }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card mb-0 border-left border-danger" style="border-left-width: 4px !important;">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-danger text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Timed Out (&gt;{{ $thresholdMinutes ?? 15 }}m)</h6>
                        <h4 class="mb-0 font-weight-bold text-danger">{{ $timedOutCount ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="card mb-0">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Auto-Accepted</h6>
                        <h4 class="mb-0 font-weight-bold text-info">{{ $autoAccepted }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card mb-0">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Completed Services</h6>
                        <h4 class="mb-0 font-weight-bold text-success">{{ $completed }}</h4>
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
                            <span class="badge badge-info" style="font-size: 11px;">{{ $thresholdMinutes ?? 5 }}-Min Provider Search Window</span>
                        </div>
                        
                        <!-- Status Filter Tabs -->
                        <div class="btn-group flex-wrap">
                            <a href="{{ route('service_requests', ['status' => 'all']) }}" class="btn btn-sm {{ $status == 'all' || !$status ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                            <a href="{{ route('service_requests', ['status' => 'pending']) }}" class="btn btn-sm {{ $status == 'pending' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-secondary' }}">Searching (&lt; {{ $thresholdMinutes ?? 5 }}m)</a>
                            <a href="{{ route('service_requests', ['status' => 'timed_out']) }}" class="btn btn-sm {{ $status == 'timed_out' ? 'btn-danger font-weight-bold' : 'btn-outline-danger' }}">Timed Out ({{ $timedOutCount ?? 0 }})</a>
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
                                        <th>Status / Search Threshold</th>
                                        <th style="text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $req)
                                    @php
                                        $createdTime = \Carbon\Carbon::parse($req->created_at);
                                        $minsAgo = $createdTime->diffInMinutes(now());
                                        $isTimedOut = ($req->status == 'cancelled' && !$req->driver_id) || ($req->status == 'pending' && !$req->driver_id && $minsAgo >= ($thresholdMinutes ?? 5));
                                    @endphp
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
                                            @elseif($isTimedOut)
                                                <span class="badge badge-danger"><i class="fa fa-times-circle mr-1"></i>No Provider Accepted</span>
                                                <div class="small text-danger font-weight-bold">Timed out &gt; {{ $thresholdMinutes ?? 5 }}m</div>
                                            @else
                                                <span class="badge badge-warning"><i class="fa fa-spinner fa-spin mr-1"></i>Searching Nearby</span>
                                                <div class="small text-muted">{{ $minsAgo }}m elapsed / {{ $thresholdMinutes ?? 5 }}m window</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($req->status == 'completed')
                                                <span class="badge badge-success">Completed</span>
                                            @elseif($req->status == 'accepted')
                                                <span class="badge badge-info">Auto-Accepted</span>
                                            @elseif($req->status == 'in_progress' || $req->status == 'on_the_way' || $req->status == 'reached')
                                                <span class="badge badge-primary">In Progress</span>
                                            @elseif($isTimedOut)
                                                <span class="badge badge-danger font-weight-bold px-2 py-1"><i class="fa fa-clock-o mr-1"></i>Timed Out / Cancelled</span>
                                            @elseif($req->status == 'cancelled' || $req->status == 'rejected')
                                                <span class="badge badge-danger">Cancelled</span>
                                            @else
                                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1"><i class="fa fa-hourglass-half mr-1"></i>Searching ({{ $minsAgo }}m / {{ $thresholdMinutes ?? 5 }}m)</span>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <a href="{{ route('service_requests.show', $req->id) }}" class="btn btn-sm btn-outline-primary" title="View Order Details"><i class="fa fa-eye"></i></a>

                                                @if($req->status == 'pending' || $isTimedOut)
                                                <form action="{{ route('service_requests.retry', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restart nearby search with a fresh 15-minute window?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Restart Provider Search"><i class="fa fa-refresh"></i> Retry</button>
                                                </form>

                                                <form action="{{ route('service_requests.cancel', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this booking request?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Booking"><i class="fa fa-times"></i></button>
                                                </form>
                                                @endif
                                            </div>
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

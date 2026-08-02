@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Booking #SR-{{ $booking->id }} Details</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{route('service_requests')}}">Service Bookings</a></li>
                <li class="breadcrumb-item active">#SR-{{ $booking->id }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Left Side: Order & Customer Summary -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h4 class="card-title text-primary mb-0 font-weight-bold"><i class="mdi mdi-wrench mr-1"></i> {{ $booking->service_name ?? 'Home Service Request' }}</h4>
                        @if($booking->status == 'completed')
                            <span class="badge badge-success px-3 py-1">Completed</span>
                        @elseif($booking->status == 'accepted')
                            <span class="badge badge-info px-3 py-1">Auto-Accepted by Provider</span>
                        @elseif($booking->status == 'in_progress')
                            <span class="badge badge-primary px-3 py-1">In Progress</span>
                        @else
                            <span class="badge badge-warning px-3 py-1">{{ ucfirst($booking->status) }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase mb-2" style="font-size: 11px; font-weight: 700;">Scheduled Date & Time</h6>
                                <p class="mb-0 font-weight-bold text-dark"><i class="fa fa-calendar text-primary mr-1"></i> {{ $booking->preferred_date ?? 'Immediate' }}</p>
                                <p class="text-muted small"><i class="fa fa-clock-o text-primary mr-1"></i> {{ $booking->preferred_time ?? 'NOW' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase mb-2" style="font-size: 11px; font-weight: 700;">Verification OTP</h6>
                                <span class="badge badge-light border text-dark px-3 py-2 font-weight-bold" style="font-size: 16px; letter-spacing: 2px;">
                                    {{ $booking->otp ?? 'Auto-Generated' }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 11px; font-weight: 700;">Problem Description</h6>
                            <div class="p-3 bg-light rounded border text-dark" style="font-size: 13px;">
                                {{ $booking->description ?? 'No specific problem description provided by customer.' }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 11px; font-weight: 700;">Location / Service Address</h6>
                            <p class="mb-1 font-weight-bold text-dark"><i class="fa fa-map-marker text-danger mr-1"></i> {{ $booking->address_type ?? 'Current Location' }}</p>
                            @if($booking->lat && $booking->lng)
                                <p class="text-muted small mb-0">GPS Coordinates: {{ $booking->lat }}, {{ $booking->lng }}</p>
                                <a href="https://www.google.com/maps?q={{ $booking->lat }},{{ $booking->lng }}" target="_blank" class="btn btn-xs btn-outline-primary mt-1"><i class="fa fa-external-link"></i> View on Google Maps</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Auto-Assigned Provider & Customer Cards -->
            <div class="col-md-4">
                <!-- Customer Card -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title text-dark mb-0 font-weight-bold"><i class="mdi mdi-account-circle mr-1"></i> Customer</h5>
                    </div>
                    <div class="card-body">
                        @if($booking->user)
                            <div class="d-flex align-items-center mb-3">
                                <img src="{{ $booking->user->user_profile ?? asset('/images/user.png') }}" alt="user" class="rounded-circle mr-3" style="width: 48px; height: 48px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $booking->user->prenom }} {{ $booking->user->nom }}</h6>
                                    <small class="text-muted">Customer ID #{{ $booking->user->id }}</small>
                                </div>
                            </div>
                            <p class="mb-1 text-muted small"><i class="fa fa-phone mr-2"></i>{{ $booking->user->phone ?? 'N/A' }}</p>
                            <p class="mb-0 text-muted small"><i class="fa fa-envelope mr-2"></i>{{ $booking->user->email ?? 'N/A' }}</p>
                        @else
                            <p class="text-muted mb-0">Customer ID #{{ $booking->user_id }}</p>
                        @endif
                    </div>
                </div>

                <!-- Auto-Matched Service Provider Card -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title text-dark mb-0 font-weight-bold"><i class="mdi mdi-account-badge mr-1"></i> Auto-Matched Service Provider</h5>
                    </div>
                    <div class="card-body">
                        @if($booking->provider)
                            <div class="d-flex align-items-center mb-3">
                                <img src="{{ $booking->provider->user_profile ?? asset('/images/user.png') }}" alt="provider" class="rounded-circle mr-3" style="width: 48px; height: 48px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $booking->provider->prenom }} {{ $booking->provider->nom }}</h6>
                                    <small class="text-success"><i class="fa fa-check-circle mr-1"></i>Auto-Accepted Request</small>
                                </div>
                            </div>
                            <p class="mb-1 text-muted small"><i class="fa fa-phone mr-2"></i>{{ $booking->provider->phone ?? 'N/A' }}</p>
                            <p class="mb-0 text-muted small"><i class="fa fa-envelope mr-2"></i>{{ $booking->provider->email ?? 'N/A' }}</p>
                        @else
                            <div class="p-3 bg-light text-center rounded border">
                                <i class="fa fa-spinner fa-spin text-warning mb-2" style="font-size: 24px;"></i>
                                <h6 class="text-dark font-weight-bold mb-1">Searching Nearby Providers</h6>
                                <p class="text-muted small mb-0">System automatically dispatches booking to providers within 5-30 km radius.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <a href="{{ route('service_requests') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left mr-1"></i> Back to Bookings List</a>
        </div>
    </div>
</div>
@endsection

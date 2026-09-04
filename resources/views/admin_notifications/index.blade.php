@extends('layouts.app')

@section('content')
<div class="page-wrapper">

    <div class="row page-titles">
        <div class="col-md-6 align-self-center">
            <h3 class="text-themecolor" style="font-weight: 700; color: #0F172A; display: flex; align-items: center; gap: 10px;">
                <i class="mdi mdi-bell-ring-outline text-primary"></i> Notifications & Admin Action Requests
            </h3>
            <span class="text-muted" style="font-size: 13px;">Manage all incoming system requests, customer care messages, payout approvals, and verification tasks.</span>
        </div>

        <div class="col-md-6 align-self-center text-right">
            <ol class="breadcrumb" style="background: transparent; margin: 0; padding: 0; display: inline-flex;">
                <li class="breadcrumb-item">
                    <a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{trans('lang.notification')}}
                </li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">

        <!-- Top Stat Badges Summary -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%); color: #fff;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase" style="font-size: 11px; letter-spacing: 0.5px; opacity: 0.8; font-weight: 600;">Total Pending Actions</span>
                            <h3 class="mb-0 mt-1 font-weight-bold" style="color: #F8FAFC;">{{ $counts['total_pending'] ?? 0 }}</h3>
                            <small style="opacity: 0.75; font-size: 11px;">Requires Admin Attention</small>
                        </div>
                        <div style="width: 46px; height: 46px; border-radius: 10px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                            <i class="mdi mdi-alert-circle-outline text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <a href="{{ route('notifications', ['tab' => 'complaints']) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #EF4444 !important; background: #fff;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600;">Customer Care</span>
                                <h3 class="mb-0 mt-1 font-weight-bold" style="color: #EF4444;">{{ $counts['complaints'] ?? 0 }}</h3>
                                <small class="text-muted" style="font-size: 11px;">Complaints / Queries</small>
                            </div>
                            <div style="width: 46px; height: 46px; border-radius: 10px; background: #FEE2E2; color: #EF4444; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <i class="mdi mdi-headset"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <a href="{{ route('notifications', ['tab' => 'withdrawals']) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #F59E0B !important; background: #fff;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600;">Payout Requests</span>
                                <h3 class="mb-0 mt-1 font-weight-bold" style="color: #F59E0B;">{{ $counts['withdrawals'] ?? 0 }}</h3>
                                <small class="text-muted" style="font-size: 11px;">Driver & User Withdrawals</small>
                            </div>
                            <div style="width: 46px; height: 46px; border-radius: 10px; background: #FEF3C7; color: #F59E0B; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <i class="mdi mdi-cash-multiple"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <a href="{{ route('notifications', ['tab' => 'driver_kyc']) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #EA580C !important; background: #fff;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase" style="font-size: 11px; font-weight: 600;">Driver KYC & Docs</span>
                                <h3 class="mb-0 mt-1 font-weight-bold" style="color: #EA580C;">{{ $counts['driver_kyc'] ?? 0 }}</h3>
                                <small class="text-muted" style="font-size: 11px;">Pending Verifications</small>
                            </div>
                            <div style="width: 46px; height: 46px; border-radius: 10px; background: #FFEDD5; color: #EA580C; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <i class="mdi mdi-account-check-outline"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            
            <!-- Filter Tabs & Actions Header -->
            <div class="card-header bg-white border-bottom p-3" style="border-color: #F1F5F9 !important;">
                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 15px;">
                    
                    <!-- Category Filter Tabs -->
                    <ul class="nav nav-pills custom-notif-pills" style="gap: 8px; margin: 0;">
                        <li class="nav-item">
                            <a class="nav-link {{ $currentTab == 'all' ? 'active' : '' }}" href="{{ route('notifications', ['tab' => 'all']) }}">
                                All Requests <span class="badge {{ $currentTab == 'all' ? 'badge-light text-dark' : 'badge-secondary' }} ml-1">{{ $counts['all'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentTab == 'complaints' ? 'active' : '' }}" href="{{ route('notifications', ['tab' => 'complaints']) }}">
                                <i class="mdi mdi-headset mr-1"></i> Customer Care
                                @if(($counts['complaints'] ?? 0) > 0)
                                    <span class="badge badge-danger ml-1">{{ $counts['complaints'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentTab == 'withdrawals' ? 'active' : '' }}" href="{{ route('notifications', ['tab' => 'withdrawals']) }}">
                                <i class="mdi mdi-cash-multiple mr-1"></i> Payouts
                                @if(($counts['withdrawals'] ?? 0) > 0)
                                    <span class="badge badge-warning ml-1 text-dark">{{ $counts['withdrawals'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentTab == 'marketplace_payouts' ? 'active' : '' }}" href="{{ route('notifications', ['tab' => 'marketplace_payouts']) }}">
                                <i class="mdi mdi-storefront-outline mr-1"></i> Marketplace
                                @if(($counts['marketplace_payouts'] ?? 0) > 0)
                                    <span class="badge badge-info ml-1">{{ $counts['marketplace_payouts'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentTab == 'driver_kyc' ? 'active' : '' }}" href="{{ route('notifications', ['tab' => 'driver_kyc']) }}">
                                <i class="mdi mdi-account-check-outline mr-1"></i> Driver KYC
                                @if(($counts['driver_kyc'] ?? 0) > 0)
                                    <span class="badge badge-danger ml-1">{{ $counts['driver_kyc'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentTab == 'rides' ? 'active' : '' }}" href="{{ route('notifications', ['tab' => 'rides']) }}">
                                <i class="mdi mdi-car mr-1"></i> Rides
                                @if(($counts['rides'] ?? 0) > 0)
                                    <span class="badge badge-primary ml-1">{{ $counts['rides'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentTab == 'services' ? 'active' : '' }}" href="{{ route('notifications', ['tab' => 'services']) }}">
                                <i class="mdi mdi-wrench-outline mr-1"></i> Home Services
                                @if(($counts['services'] ?? 0) > 0)
                                    <span class="badge badge-purple ml-1">{{ $counts['services'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentTab == 'medical_claims' ? 'active' : '' }}" href="{{ route('notifications', ['tab' => 'medical_claims']) }}">
                                <i class="mdi mdi-hospital-box-outline mr-1"></i> Medical
                                @if(($counts['medical_claims'] ?? 0) > 0)
                                    <span class="badge badge-success ml-1">{{ $counts['medical_claims'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentTab == 'parcels' ? 'active' : '' }}" href="{{ route('notifications', ['tab' => 'parcels']) }}">
                                <i class="mdi mdi-package-variant-closed mr-1"></i> Parcels
                                @if(($counts['parcels'] ?? 0) > 0)
                                    <span class="badge badge-secondary ml-1">{{ $counts['parcels'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentTab == 'broadcast' ? 'active' : '' }}" href="{{ route('notifications', ['tab' => 'broadcast']) }}">
                                <i class="mdi mdi-bullhorn-outline mr-1"></i> Broadcast Logs
                            </a>
                        </li>
                    </ul>

                    <!-- Action: Send Broadcast Button -->
                    <div class="ml-auto">
                        <a href="{{ route('notifications.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="mdi mdi-bullhorn mr-1"></i> Send Broadcast
                        </a>
                    </div>
                </div>

                <!-- Search & Filters Toolbar -->
                <div class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between" style="gap: 15px; border-color: #F1F5F9 !important;">
                    <form action="{{ route('notifications') }}" method="get" class="d-flex align-items-center" style="gap: 10px; max-width: 450px; width: 100%;">
                        <input type="hidden" name="tab" value="{{ $currentTab }}">
                        <div class="position-relative w-100">
                            <i class="mdi mdi-magnify" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 16px;"></i>
                            <input type="text" class="form-control form-control-sm pl-4" name="search" value="{{ $search }}" placeholder="Search by name, phone, title, or order ID..." style="border-radius: 8px; border: 1px solid #CBD5E1; padding-left: 36px !important; height: 38px;">
                        </div>
                        <button type="submit" class="btn btn-dark btn-sm px-3" style="border-radius: 8px; height: 38px;">Search</button>
                        @if(!empty($search))
                            <a href="{{ route('notifications', ['tab' => $currentTab]) }}" class="btn btn-outline-secondary btn-sm px-3" style="border-radius: 8px; height: 38px; display: flex; align-items: center;">Clear</a>
                        @endif
                    </form>

                    <div class="text-muted" style="font-size: 12px; font-weight: 500;">
                        Showing {{ count($notifications) }} of {{ $pagination['total'] ?? count($notifications) }} requests
                    </div>
                </div>
            </div>

            <!-- Notification Items List -->
            <div class="card-body p-0">
                @if(count($notifications) > 0)
                    <div class="list-group list-group-flush notif-item-list">
                        @foreach($notifications as $notif)
                            <div class="list-group-item p-3 notif-row position-relative {{ $notif['is_pending'] ? 'notif-pending-row' : '' }}" 
                                 onclick="handleNotifClick(event, '{{ $notif['url'] }}')"
                                 style="cursor: pointer; transition: background 0.15s ease-in-out; border-bottom: 1px solid #F1F5F9;">
                                
                                <div class="d-flex align-items-start justify-content-between" style="gap: 16px;">
                                    
                                    <!-- Left: Category Icon -->
                                    <div class="flex-shrink-0">
                                        <div style="width: 44px; height: 44px; border-radius: 12px; background: {{ $notif['icon_bg'] }}; color: {{ $notif['icon_color'] }}; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                                            <i class="{{ $notif['icon'] }}"></i>
                                        </div>
                                    </div>

                                    <!-- Middle: Content & Metadata -->
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <div class="d-flex flex-wrap align-items-center mb-1" style="gap: 8px;">
                                            <span class="badge text-uppercase" style="font-size: 10px; font-weight: 700; background: {{ $notif['icon_bg'] }}; color: {{ $notif['icon_color'] }}; border-radius: 6px; padding: 4px 8px;">
                                                {{ $notif['category_pill'] }}
                                            </span>
                                            
                                            <h5 class="mb-0 font-weight-bold" style="font-size: 14px; color: #0F172A;">
                                                {{ $notif['title'] }}
                                            </h5>

                                            <span class="badge {{ $notif['status_class'] }} ml-auto" style="font-size: 11px; padding: 4px 8px; border-radius: 6px;">
                                                {{ $notif['status'] }}
                                            </span>
                                        </div>

                                        <p class="mb-2 text-secondary" style="font-size: 13px; line-height: 1.4; color: #475569 !important;">
                                            {{ $notif['message'] }}
                                        </p>

                                        <div class="d-flex align-items-center text-muted" style="font-size: 11.5px; gap: 14px;">
                                            <span><i class="mdi mdi-clock-outline mr-1"></i> {{ $notif['time_formatted'] }}</span>
                                            <span><i class="mdi mdi-tag-outline mr-1"></i> {{ $notif['category_label'] }}</span>
                                            @if($notif['is_pending'])
                                                <span class="text-danger font-weight-bold"><i class="mdi mdi-alert-circle-outline mr-1"></i> Requires Action</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Right: Direct Action Button -->
                                    <div class="flex-shrink-0 text-right d-flex flex-column align-items-end justify-content-center" style="min-width: 140px;">
                                        <a href="{{ $notif['url'] }}" class="btn btn-sm btn-outline-primary notif-action-btn" style="border-radius: 8px; font-weight: 600; font-size: 12px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 6px;">
                                            <span>{{ $notif['action_label'] }}</span>
                                            <i class="mdi mdi-arrow-right font-weight-bold"></i>
                                        </a>

                                        @if($currentTab === 'broadcast' && $notif['category'] === 'broadcast')
                                            <a href="{{ route('notifications.delete', ['id' => $notif['raw_id']]) }}" 
                                               onclick="event.stopPropagation(); return confirm('Delete this broadcast log?');"
                                               class="btn btn-link text-danger p-0 mt-2" style="font-size: 11px;">
                                                <i class="fa fa-trash mr-1"></i> Delete
                                            </a>
                                        @endif
                                    </div>

                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if(($pagination['last_page'] ?? 1) > 1)
                        <div class="p-3 border-top bg-white d-flex justify-content-between align-items-center" style="border-color: #F1F5F9 !important;">
                            <div class="text-muted" style="font-size: 13px;">
                                Page {{ $pagination['current_page'] }} of {{ $pagination['last_page'] }} (Total: {{ $pagination['total'] }})
                            </div>
                            <ul class="pagination pagination-sm mb-0">
                                @if($pagination['current_page'] > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ route('notifications', ['tab' => $currentTab, 'search' => $search, 'page' => $pagination['current_page'] - 1]) }}">Previous</a>
                                    </li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                @endif

                                @for($p = max(1, $pagination['current_page'] - 2); $p <= min($pagination['last_page'], $pagination['current_page'] + 2); $p++)
                                    <li class="page-item {{ $p == $pagination['current_page'] ? 'active' : '' }}">
                                        <a class="page-link" href="{{ route('notifications', ['tab' => $currentTab, 'search' => $search, 'page' => $p]) }}">{{ $p }}</a>
                                    </li>
                                @endfor

                                @if($pagination['current_page'] < $pagination['last_page'])
                                    <li class="page-item">
                                        <a class="page-link" href="{{ route('notifications', ['tab' => $currentTab, 'search' => $search, 'page' => $pagination['current_page'] + 1]) }}">Next</a>
                                    </li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                                @endif
                            </ul>
                        </div>
                    @endif

                @else
                    <!-- Empty State -->
                    <div class="text-center py-5 px-3">
                        <div style="width: 70px; height: 70px; border-radius: 50%; background: #F8FAFC; border: 2px dashed #CBD5E1; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; color: #94A3B8; margin-bottom: 16px;">
                            <i class="mdi mdi-check-circle-outline"></i>
                        </div>
                        <h4 class="font-weight-bold text-dark mb-1" style="font-size: 17px;">All Caught Up!</h4>
                        <p class="text-muted mb-3" style="font-size: 13px; max-width: 420px; margin: 0 auto;">
                            @if(!empty($search))
                                No requests found matching "{{ $search }}". Try searching with another term.
                            @else
                                No pending requests found in the <strong>{{ ucfirst(str_replace('_', ' ', $currentTab)) }}</strong> section.
                            @endif
                        </p>
                        @if(!empty($search) || $currentTab !== 'all')
                            <a href="{{ route('notifications', ['tab' => 'all']) }}" class="btn btn-outline-dark btn-sm px-3" style="border-radius: 8px;">
                                View All Requests
                            </a>
                        @endif
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>

<style>
    .custom-notif-pills .nav-link {
        border-radius: 8px;
        color: #475569;
        font-weight: 600;
        font-size: 12.5px;
        padding: 8px 14px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        transition: all 0.15s ease-in-out;
    }
    .custom-notif-pills .nav-link:hover {
        background: #EDF2F7;
        color: #0F172A;
    }
    .custom-notif-pills .nav-link.active {
        background: #0284C7 !important;
        color: #ffffff !important;
        border-color: #0284C7 !important;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
    }
    .notif-row:hover {
        background: #F8FAFC !important;
    }
    .notif-pending-row {
        background: #FCFDFE;
    }
    .notif-action-btn:hover {
        background: #0284C7 !important;
        color: #fff !important;
        border-color: #0284C7 !important;
    }
    .badge-purple {
        background-color: #8B5CF6;
        color: #fff;
    }
</style>

@endsection

@section('scripts')
<script type="text/javascript">
    function handleNotifClick(event, url) {
        // If the user clicked directly on a button or link inside, don't double trigger
        if (event.target.closest('a') || event.target.closest('button') || event.target.closest('input')) {
            return;
        }
        if (url) {
            window.location.href = url;
        }
    }
</script>
@endsection
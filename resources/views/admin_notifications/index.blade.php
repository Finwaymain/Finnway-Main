@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="padding-top: 15px;">

    <div class="container-fluid" style="padding: 0 20px;">

        <!-- Top Header & Primary Actions (Compact) -->
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap: 10px;">
            <div class="d-flex align-items-center" style="gap: 10px;">
                <h4 class="mb-0 font-weight-bold" style="color: #0F172A; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    <i class="mdi mdi-bell-outline text-primary"></i> Notifications & Admin Requests
                </h4>
                @if(($counts['total_pending'] ?? 0) > 0)
                    <span class="badge badge-danger" style="font-size: 11px; padding: 4px 8px; border-radius: 12px; font-weight: 600;">
                        {{ $counts['total_pending'] }} Action Required
                    </span>
                @else
                    <span class="badge badge-success" style="font-size: 11px; padding: 4px 8px; border-radius: 12px; font-weight: 600;">
                        All Caught Up
                    </span>
                @endif
            </div>

            <!-- Right: Status Toggle & Broadcast Button -->
            <div class="d-flex align-items-center" style="gap: 8px;">
                <!-- Quick Filter: All vs Only Pending -->
                <div class="btn-group btn-group-sm" role="group" style="box-shadow: none;">
                    <a href="{{ route('notifications', ['tab' => $currentTab, 'search' => $search, 'status' => 'all']) }}" 
                       class="btn btn-sm {{ $statusFilter !== 'pending' ? 'btn-dark' : 'btn-outline-secondary' }}" 
                       style="font-size: 11.5px; padding: 4px 10px; font-weight: 600;">
                        All ({{ $counts['all'] ?? 0 }})
                    </a>
                    <a href="{{ route('notifications', ['tab' => $currentTab, 'search' => $search, 'status' => 'pending']) }}" 
                       class="btn btn-sm {{ $statusFilter === 'pending' ? 'btn-danger' : 'btn-outline-danger' }}" 
                       style="font-size: 11.5px; padding: 4px 10px; font-weight: 600;">
                        <i class="mdi mdi-alert-circle-outline mr-1"></i> Actions Required ({{ $counts['total_pending'] ?? 0 }})
                    </a>
                </div>

                <a href="{{ route('notifications.create') }}" class="btn btn-primary btn-sm px-2.5 py-1" style="font-size: 12px; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="mdi mdi-plus"></i> Send Broadcast
                </a>
            </div>
        </div>

        <!-- Compressed Category Quick Chips Strip -->
        <div class="notif-chips-container mb-2.5 d-flex align-items-center" style="gap: 6px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none;">
            @php
                $chipTabs = [
                    'all' => ['label' => 'All', 'icon' => 'mdi-view-grid-outline', 'count' => $counts['all'] ?? 0],
                    'complaints' => ['label' => 'Customer Care', 'icon' => 'mdi-headset', 'count' => $counts['complaints'] ?? 0, 'alert' => true],
                    'withdrawals' => ['label' => 'Payouts', 'icon' => 'mdi-cash-multiple', 'count' => $counts['withdrawals'] ?? 0, 'alert' => true],
                    'marketplace_payouts' => ['label' => 'Marketplace', 'icon' => 'mdi-storefront-outline', 'count' => $counts['marketplace_payouts'] ?? 0, 'alert' => true],
                    'driver_kyc' => ['label' => 'Driver KYC', 'icon' => 'mdi-account-check-outline', 'count' => $counts['driver_kyc'] ?? 0, 'alert' => true],
                    'rides' => ['label' => 'Rides', 'icon' => 'mdi-car', 'count' => $counts['rides'] ?? 0],
                    'services' => ['label' => 'Services', 'icon' => 'mdi-wrench-outline', 'count' => $counts['services'] ?? 0],
                    'medical_claims' => ['label' => 'Medical', 'icon' => 'mdi-hospital-box-outline', 'count' => $counts['medical_claims'] ?? 0],
                    'parcels' => ['label' => 'Parcels', 'icon' => 'mdi-package-variant-closed', 'count' => $counts['parcels'] ?? 0],
                    'broadcast' => ['label' => 'Broadcasts', 'icon' => 'mdi-bullhorn-outline', 'count' => $counts['broadcast'] ?? 0],
                ];
            @endphp

            @foreach($chipTabs as $tabKey => $chip)
                <a href="{{ route('notifications', ['tab' => $tabKey, 'status' => $statusFilter, 'search' => $search]) }}" 
                   class="notif-chip {{ $currentTab === $tabKey ? 'active' : '' }} text-decoration-none">
                    <i class="mdi {{ $chip['icon'] }} mr-1"></i>
                    <span>{{ $chip['label'] }}</span>
                    @if($chip['count'] > 0)
                        <span class="chip-badge {{ !empty($chip['alert']) ? 'chip-badge-alert' : '' }} ml-1">
                            {{ $chip['count'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        <!-- Main Dense Card Container -->
        <div class="card border mb-3 shadow-none" style="border-radius: 8px; border-color: #E2E8F0 !important; overflow: hidden; background: #fff;">
            
            <!-- Compact Search & Sub-bar -->
            <div class="px-3 py-2 border-bottom bg-light d-flex flex-wrap align-items-center justify-content-between" style="border-color: #E2E8F0 !important; gap: 10px;">
                <form action="{{ route('notifications') }}" method="get" class="d-flex align-items-center" style="gap: 6px; max-width: 380px; width: 100%;">
                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <div class="position-relative w-100">
                        <i class="mdi mdi-magnify" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 15px;"></i>
                        <input type="text" class="form-control form-control-sm pl-4" name="search" value="{{ $search }}" 
                               placeholder="Filter by name, phone, title, ID..." 
                               style="height: 30px; font-size: 12px; border-radius: 6px; border: 1px solid #CBD5E1; padding-left: 32px !important; background: #fff;">
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm px-2.5 py-1" style="font-size: 11px; height: 30px; border-radius: 6px;">Filter</button>
                    @if(!empty($search))
                        <a href="{{ route('notifications', ['tab' => $currentTab, 'status' => $statusFilter]) }}" class="btn btn-outline-secondary btn-sm px-2 py-1" style="font-size: 11px; height: 30px; border-radius: 6px;">Clear</a>
                    @endif
                </form>

                <div class="text-muted d-flex align-items-center" style="font-size: 11.5px; gap: 12px;">
                    <span>Showing <strong>{{ count($notifications) }}</strong> of <strong>{{ $pagination['total'] ?? count($notifications) }}</strong> requests</span>
                    @if($currentTab !== 'all' || $statusFilter !== 'all' || !empty($search))
                        <a href="{{ route('notifications', ['tab' => 'all']) }}" class="text-primary font-weight-bold" style="font-size: 11.5px;">Reset All Filters</a>
                    @endif
                </div>
            </div>

            <!-- Dense List Rows -->
            <div class="card-body p-0">
                @if(count($notifications) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notif)
                            <div class="list-group-item px-3 py-2 notif-compact-row {{ $notif['is_pending'] ? 'notif-row-pending' : '' }}" 
                                 onclick="handleRowClick(event, '{{ $notif['url'] }}')"
                                 style="cursor: pointer; transition: background 0.12s ease; border-bottom: 1px solid #F1F5F9;">
                                
                                <div class="d-flex align-items-center justify-content-between" style="gap: 12px;">
                                    
                                    <!-- Left: Mini Icon (28px) -->
                                    <div class="flex-shrink-0">
                                        <div style="width: 28px; height: 28px; border-radius: 6px; background: {{ $notif['icon_bg'] }}; color: {{ $notif['icon_color'] }}; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                                            <i class="{{ $notif['icon'] }}"></i>
                                        </div>
                                    </div>

                                    <!-- Middle: Compact 2-line structure -->
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <!-- Line 1: Category Tag + Title + Status + Time -->
                                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px; line-height: 1.2; margin-bottom: 2px;">
                                            
                                            <!-- Category Pill -->
                                            <span class="badge badge-light border text-uppercase" style="font-size: 9.5px; font-weight: 700; color: {{ $notif['icon_color'] }}; padding: 2px 5px; border-radius: 4px;">
                                                {{ $notif['category_pill'] }}
                                            </span>

                                            <!-- Main Title -->
                                            <span class="font-weight-bold notif-title text-truncate" style="font-size: 13px; color: #0F172A; max-width: 480px;">
                                                {{ $notif['title'] }}
                                            </span>

                                            <!-- Status Badge -->
                                            <span class="badge {{ $notif['status_class'] }}" style="font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 600;">
                                                {{ $notif['status'] }}
                                            </span>

                                            <!-- Time Ago -->
                                            <span class="text-muted" style="font-size: 11px;">
                                                &middot; {{ $notif['time_formatted'] }}
                                            </span>

                                            @if($notif['is_pending'])
                                                <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 9.5px; padding: 1px 5px; border-radius: 4px;">
                                                    Action Required
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Line 2: Truncated excerpt / user info -->
                                        <div class="text-muted text-truncate" style="font-size: 12px; color: #64748B !important; line-height: 1.3;">
                                            {{ $notif['message'] }}
                                        </div>
                                    </div>

                                    <!-- Right: Action Button (Compact) -->
                                    <div class="flex-shrink-0 text-right d-flex align-items-center" style="gap: 8px;">
                                        <a href="{{ $notif['url'] }}" class="btn btn-outline-primary btn-sm notif-btn-action px-2.5 py-1" 
                                           style="font-size: 11.5px; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; height: 28px;">
                                            <span>{{ $notif['action_label'] }}</span>
                                            <i class="mdi mdi-arrow-right" style="font-size: 13px;"></i>
                                        </a>

                                        @if($currentTab === 'broadcast' && $notif['category'] === 'broadcast')
                                            <a href="{{ route('notifications.delete', ['id' => $notif['raw_id']]) }}" 
                                               onclick="event.stopPropagation(); return confirm('Delete this broadcast log?');"
                                               class="btn btn-outline-danger btn-sm px-2 py-1" style="font-size: 11px; height: 28px; border-radius: 6px;" title="Delete Broadcast">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        @endif
                                    </div>

                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Compact Pagination -->
                    @if(($pagination['last_page'] ?? 1) > 1)
                        <div class="px-3 py-2 border-top bg-light d-flex justify-content-between align-items-center" style="border-color: #E2E8F0 !important;">
                            <div class="text-muted" style="font-size: 11.5px;">
                                Page {{ $pagination['current_page'] }} of {{ $pagination['last_page'] }} (Total: {{ $pagination['total'] }})
                            </div>
                            <ul class="pagination pagination-sm mb-0">
                                @if($pagination['current_page'] > 1)
                                    <li class="page-item">
                                        <a class="page-link py-1 px-2" href="{{ route('notifications', ['tab' => $currentTab, 'status' => $statusFilter, 'search' => $search, 'page' => $pagination['current_page'] - 1]) }}" style="font-size: 11px;">Prev</a>
                                    </li>
                                @else
                                    <li class="page-item disabled"><span class="page-link py-1 px-2" style="font-size: 11px;">Prev</span></li>
                                @endif

                                @for($p = max(1, $pagination['current_page'] - 2); $p <= min($pagination['last_page'], $pagination['current_page'] + 2); $p++)
                                    <li class="page-item {{ $p == $pagination['current_page'] ? 'active' : '' }}">
                                        <a class="page-link py-1 px-2" href="{{ route('notifications', ['tab' => $currentTab, 'status' => $statusFilter, 'search' => $search, 'page' => $p]) }}" style="font-size: 11px;">{{ $p }}</a>
                                    </li>
                                @endfor

                                @if($pagination['current_page'] < $pagination['last_page'])
                                    <li class="page-item">
                                        <a class="page-link py-1 px-2" href="{{ route('notifications', ['tab' => $currentTab, 'status' => $statusFilter, 'search' => $search, 'page' => $pagination['current_page'] + 1]) }}" style="font-size: 11px;">Next</a>
                                    </li>
                                @else
                                    <li class="page-item disabled"><span class="page-link py-1 px-2" style="font-size: 11px;">Next</span></li>
                                @endif
                            </ul>
                        </div>
                    @endif

                @else
                    <!-- Compact Empty State -->
                    <div class="text-center py-4 px-3">
                        <i class="mdi mdi-check-circle-outline text-success" style="font-size: 32px;"></i>
                        <h6 class="font-weight-bold text-dark mt-1 mb-1" style="font-size: 14px;">No Requests Found</h6>
                        <p class="text-muted mb-2" style="font-size: 12px;">
                            @if(!empty($search))
                                No requests found matching "{{ $search }}".
                            @elseif($statusFilter === 'pending')
                                Great job! No pending action items requiring attention in this category.
                            @else
                                No notification records available in this section.
                            @endif
                        </p>
                        @if(!empty($search) || $statusFilter === 'pending' || $currentTab !== 'all')
                            <a href="{{ route('notifications', ['tab' => 'all', 'status' => 'all']) }}" class="btn btn-outline-dark btn-sm px-2.5 py-1" style="font-size: 11.5px; border-radius: 6px;">
                                View All Notifications
                            </a>
                        @endif
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>

<style>
    /* Compact Chips */
    .notif-chip {
        display: inline-flex;
        align-items: center;
        background: #F1F5F9;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #E2E8F0;
        white-space: nowrap;
        transition: all 0.12s ease;
    }
    .notif-chip:hover {
        background: #E2E8F0;
        color: #0F172A;
    }
    .notif-chip.active {
        background: #0F172A !important;
        color: #ffffff !important;
        border-color: #0F172A !important;
    }
    .chip-badge {
        background: #CBD5E1;
        color: #1E293B;
        font-size: 9.5px;
        font-weight: 700;
        padding: 1px 5px;
        border-radius: 10px;
    }
    .notif-chip.active .chip-badge {
        background: rgba(255,255,255,0.25);
        color: #ffffff;
    }
    .chip-badge-alert {
        background: #EF4444 !important;
        color: #ffffff !important;
    }
    
    /* Dense Rows */
    .notif-compact-row:hover {
        background: #F8FAFC !important;
    }
    .notif-row-pending {
        border-left: 3px solid #EF4444 !important;
        background: #FFFAFA;
    }
    .notif-btn-action:hover {
        background: #0284C7 !important;
        color: #ffffff !important;
        border-color: #0284C7 !important;
    }
</style>

@endsection

@section('scripts')
<script type="text/javascript">
    function handleRowClick(event, url) {
        if (event.target.closest('a') || event.target.closest('button') || event.target.closest('input')) {
            return;
        }
        if (url) {
            window.location.href = url;
        }
    }
</script>
@endsection
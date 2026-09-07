@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-6 align-self-center">
            <h3 class="text-themecolor">Home Service Bookings (Live Monitor)</h3>
        </div>
        <div class="col-md-6 align-self-center text-right">
            <button type="button" id="btnSoundToggle" class="btn btn-sm btn-outline-secondary mr-2" onclick="toggleAlertSound()" title="Toggle Sound Alerts">
                <i class="fa fa-volume-up mr-1" id="soundIcon"></i> Sound: <span id="soundStatusText">ON</span>
            </button>
            <ol class="breadcrumb d-inline-block p-0 m-0 bg-transparent">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">Service Bookings</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Live Escalation Alert Banner -->
        @if(($escalatedCount ?? 0) > 0)
        <div class="alert alert-danger d-flex align-items-center justify-content-between p-3 mb-3 shadow-sm border-left border-danger" style="border-left-width: 6px !important; border-radius: 6px;">
            <div class="d-flex align-items-center">
                <div class="mr-3 text-danger">
                    <i class="fa fa-bell fa-2x fa-shake"></i>
                </div>
                <div>
                    <h5 class="alert-heading mb-1 font-weight-bold text-danger">
                        🚨 Immediate Admin Action Needed: {{ $escalatedCount }} Escalated Request{{ $escalatedCount > 1 ? 's' : '' }}
                    </h5>
                    <p class="mb-0 text-dark small">
                        Customer requests have been waiting <strong>&ge; 2 minutes</strong> without any service partner accepting. Please review matching business partners (0–30 km) and manually assign a provider.
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ route('service_requests', ['status' => 'escalated']) }}" class="btn btn-sm btn-danger font-weight-bold px-3 shadow-sm">
                    <i class="fa fa-bolt mr-1"></i> Review {{ $escalatedCount }} Escalation{{ $escalatedCount > 1 ? 's' : '' }}
                </a>
            </div>
        </div>
        @endif

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
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Active Searching (&lt;2m)</h6>
                        <h4 class="mb-0 font-weight-bold text-warning">{{ $pendingMatch }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card mb-0 border-left border-danger {{ ($escalatedCount ?? 0) > 0 ? 'bg-light-danger pulse-card' : '' }}" style="border-left-width: 4px !important;">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-danger text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">
                            🚨 Escalated (&ge;2m Unassigned)
                        </h6>
                        <h4 class="mb-0 font-weight-bold text-danger">
                            {{ $escalatedCount ?? 0 }}
                            @if(($escalatedCount ?? 0) > 0)
                                <span class="badge badge-danger font-11 ml-1 pulse-dot">ALERT</span>
                            @endif
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <div class="card mb-0">
                    <div class="card-body p-3 text-center">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 11px; font-weight: 700;">Assigned / Accepted</h6>
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
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2">
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="card-title text-dark mb-0 font-weight-bold">Live Service Orders</h4>
                            <span class="badge badge-secondary" style="font-size: 11px;">2-Min Auto-Escalation Window</span>
                        </div>
                        
                        <!-- Status Filter Tabs -->
                        <div class="btn-group flex-wrap">
                            <a href="{{ route('service_requests', ['status' => 'all']) }}" class="btn btn-sm {{ $status == 'all' || !$status ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                            
                            <a href="{{ route('service_requests', ['status' => 'pending']) }}" class="btn btn-sm {{ $status == 'pending' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-secondary' }}">Searching (&lt; 2m)</a>
                            
                            <!-- New Tab: Escalated (> 2m) -->
                            <a href="{{ route('service_requests', ['status' => 'escalated']) }}" class="btn btn-sm {{ $status == 'escalated' ? 'btn-danger font-weight-bold' : 'btn-outline-danger' }}">
                                🚨 Escalated (&gt; 2m)
                                @if(($escalatedCount ?? 0) > 0)
                                    <span class="badge badge-light text-danger ml-1 font-weight-bold">{{ $escalatedCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('service_requests', ['status' => 'timed_out']) }}" class="btn btn-sm {{ $status == 'timed_out' ? 'btn-secondary font-weight-bold' : 'btn-outline-secondary' }}">Timed Out</a>
                            <a href="{{ route('service_requests', ['status' => 'accepted']) }}" class="btn btn-sm {{ $status == 'accepted' ? 'btn-info' : 'btn-outline-secondary' }}">Accepted</a>
                            <a href="{{ route('service_requests', ['status' => 'in_progress']) }}" class="btn btn-sm {{ $status == 'in_progress' ? 'btn-purple' : 'btn-outline-secondary' }}">In Progress</a>
                            <a href="{{ route('service_requests', ['status' => 'completed']) }}" class="btn btn-sm {{ $status == 'completed' ? 'btn-success' : 'btn-outline-secondary' }}">Completed</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Customer</th>
                                        <th>Service Requested</th>
                                        <th>Scheduled Date/Time</th>
                                        <th>Assigned Provider</th>
                                        <th>Status / Search Threshold</th>
                                        <th style="text-align: right; min-width: 220px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $req)
                                    @php
                                        $createdTime = \Carbon\Carbon::parse($req->created_at);
                                        $minsAgo = $createdTime->diffInMinutes(now());
                                        $isEscalated = !$req->driver_id && (($req->status == 'pending' && $minsAgo >= 2) || $req->status == 'cancelled');
                                    @endphp
                                    <tr class="{{ $isEscalated ? 'table-warning-row' : '' }}">
                                        <td>
                                            <strong>#SR-{{ $req->id }}</strong>
                                            @if($isEscalated)
                                                <div class="small text-danger font-weight-bold"><i class="fa fa-exclamation-triangle"></i> Escalated</div>
                                            @endif
                                        </td>
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
                                                <div class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $req->description }}">{{ $req->description }}</div>
                                            @endif
                                            @if($req->service_address)
                                                <div class="small text-muted text-truncate" style="max-width: 220px;"><i class="fa fa-map-marker text-danger mr-1"></i>{{ $req->service_address }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div><i class="fa fa-calendar-o text-muted mr-1"></i>{{ $req->preferred_date ?? 'Immediate' }}</div>
                                            <small class="text-muted"><i class="fa fa-clock-o mr-1"></i>{{ $req->preferred_time ?? 'NOW' }}</small>
                                        </td>
                                        <td>
                                            @if($req->provider)
                                                <div class="font-weight-bold text-success"><i class="fa fa-check-circle mr-1"></i>{{ $req->provider->prenom }} {{ $req->provider->nom }}</div>
                                                <div class="small font-weight-bold text-dark"><i class="fa fa-phone text-success mr-1"></i>{{ $req->provider->phone }}</div>
                                            @elseif($req->driver_id)
                                                <span class="text-dark">Provider #{{ $req->driver_id }}</span>
                                            @elseif($isEscalated)
                                                <span class="badge badge-danger font-11 px-2 py-1"><i class="fa fa-times-circle mr-1"></i>No Partner Accepted (&gt;2m)</span>
                                                <div class="small text-danger font-weight-bold mt-1">Waiting {{ $minsAgo }} mins</div>
                                            @else
                                                <span class="badge badge-warning text-dark font-11 px-2 py-1"><i class="fa fa-spinner fa-spin mr-1"></i>Searching Nearby</span>
                                                <div class="small text-muted">{{ $minsAgo }}m elapsed (2m window)</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($req->status == 'completed')
                                                <span class="badge badge-success">Completed</span>
                                            @elseif($req->status == 'accepted' || $req->status == 'confirmed')
                                                <span class="badge badge-info font-weight-bold">Confirmed / Accepted</span>
                                            @elseif($req->status == 'in_progress' || $req->status == 'on_the_way' || $req->status == 'reached')
                                                <span class="badge badge-primary">In Progress</span>
                                            @elseif($isEscalated)
                                                <span class="badge badge-danger font-weight-bold px-2 py-1"><i class="fa fa-clock-o mr-1"></i>Needs Admin Assignment</span>
                                            @elseif($req->status == 'cancelled' || $req->status == 'rejected')
                                                <span class="badge badge-secondary">Cancelled</span>
                                            @else
                                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1"><i class="fa fa-hourglass-half mr-1"></i>Searching ({{ $minsAgo }}m)</span>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="d-inline-flex align-items-center flex-wrap gap-1 justify-content-end">
                                                <!-- Action: Assign Partner (0-30km) -->
                                                @if(!$req->driver_id || $req->status == 'pending' || $req->status == 'cancelled')
                                                    <button type="button" class="btn btn-sm btn-warning text-dark font-weight-bold shadow-sm" onclick="openAssignPartnerModal({{ $req->id }}, '{{ addslashes($req->service_name ?? 'Home Service') }}')" title="Check & Assign Related Business Partner within 0-30km">
                                                        <i class="fa fa-user-plus mr-1"></i> Assign Partner (0-30km)
                                                    </button>
                                                @endif

                                                <a href="{{ route('service_requests.show', $req->id) }}" class="btn btn-sm btn-outline-primary" title="View Order Details"><i class="fa fa-eye"></i></a>

                                                @if($req->status == 'pending' || $isEscalated)
                                                <form action="{{ route('service_requests.retry', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restart nearby search with a fresh window?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Restart Provider Search"><i class="fa fa-refresh"></i></button>
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
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="fa fa-inbox fa-3x mb-2 text-muted"></i>
                                            <p class="mb-0">No home service bookings found matching current filter.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end p-3">
                            {{ $requests->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Discover & Assign Related Business Partners (0-30km) -->
<div class="modal fade" id="assignPartnerModal" tabindex="-1" role="dialog" aria-labelledby="assignPartnerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light d-flex align-items-center justify-content-between py-2 px-3">
                <div>
                    <h5 class="modal-title font-weight-bold text-dark mb-0" id="assignPartnerModalLabel">
                        <i class="fa fa-user-plus text-primary mr-1"></i> Assign Business Partner (0–30 km Radius)
                    </h5>
                    <small class="text-muted" id="modalSubtitle">Loading related partners...</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- Booking Summary Card -->
                <div class="card mb-3 border bg-light-primary" id="modalBookingSummary" style="border-radius: 8px;">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <div class="text-muted small font-weight-bold">REQUESTED SERVICE</div>
                                <h5 class="mb-1 font-weight-bold text-primary" id="mServiceTitle">-</h5>
                                <div class="small text-dark"><i class="fa fa-clock-o text-muted mr-1"></i> Waiting: <strong id="mElapsedTime" class="text-danger">-</strong></div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small font-weight-bold">CUSTOMER DETAILS & LOCATION</div>
                                <div class="font-weight-bold text-dark" id="mCustomerName">-</div>
                                <div class="small text-muted"><i class="fa fa-phone mr-1"></i><span id="mCustomerPhone">-</span></div>
                                <div class="small text-dark text-truncate" id="mServiceAddress"><i class="fa fa-map-marker text-danger mr-1"></i>-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Radius Filter Banner -->
                <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded mb-3 border">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-success mr-2 px-2 py-1"><i class="fa fa-compass mr-1"></i> 0–30 KM RADIUS</span>
                        <small class="text-muted">Showing <strong>only business partners related to this service</strong>.</small>
                    </div>
                    <div>
                        <input type="text" id="partnerSearchFilter" class="form-control form-control-sm" placeholder="Search partner by name/phone..." onkeyup="filterPartnerListUI()" style="width: 220px;">
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="modalLoadingSpinner" class="text-center py-5">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary mb-2"></i>
                    <p class="text-muted">Calculating GPS distance & fetching related service partners within 30 km...</p>
                </div>

                <!-- Content Container -->
                <div id="modalPartnersContent" style="display: none;">
                    <!-- Section: Within 0 - 30 km -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-dark mb-2 d-flex align-items-center justify-content-between">
                            <span><i class="fa fa-check-circle text-success mr-1"></i> Matching Partners Within 0–30 km (<span id="countWithin30">0</span>)</span>
                            <span class="badge badge-light border text-muted">Nearest First</span>
                        </h6>
                        <div id="listWithin30" class="partner-card-list">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                    <!-- Section: Other Registered Partners for this Service -->
                    <div class="mb-2" id="sectionOtherPartners" style="display: none;">
                        <h6 class="font-weight-bold text-muted mb-2">
                            <i class="fa fa-map-marker text-muted mr-1"></i> Other Related Partners (GPS Inactive / Further away) (<span id="countOther">0</span>)
                        </h6>
                        <div id="listOtherPartners" class="partner-card-list">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div id="modalNoPartners" class="text-center py-5 text-muted" style="display: none;">
                        <i class="fa fa-user-times fa-3x mb-2 text-warning"></i>
                        <h6 class="font-weight-bold text-dark">No Service Partners Found</h6>
                        <p class="small mb-0">No business partners registered for this service category were found within 30 km.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.table-warning-row {
    background-color: #fffbeb !important;
}
.pulse-card {
    animation: pulseBorder 2s infinite;
}
@keyframes pulseBorder {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
.pulse-dot {
    animation: pulseAnim 1.5s infinite;
}
@keyframes pulseAnim {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.08); }
    100% { opacity: 1; transform: scale(1); }
}
.partner-card-item {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 10px;
    background: #ffffff;
    transition: all 0.2s ease-in-out;
}
.partner-card-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    background: #f8fafc;
}
.partner-phone-display {
    font-size: 14.5px;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: 0.3px;
}
.btn-copy-phone {
    background: none;
    border: none;
    cursor: pointer;
    color: #64748b;
    padding: 2px 5px;
    font-size: 13px;
}
.btn-copy-phone:hover {
    color: #0f172a;
}
</style>

<script>
let currentActiveBookingId = null;
let alertSoundEnabled = localStorage.getItem('fiinway_alert_sound') !== 'false';
let cachedNearbyPartners = [];

document.addEventListener('DOMContentLoaded', function() {
    updateSoundUI();
    
    // If escalated requests exist, trigger initial chime
    @if(($escalatedCount ?? 0) > 0)
        playEscalationAlertSound();
    @endif

    // Background polling every 25 seconds for new 2-minute escalations
    setInterval(pollEscalationStatus, 25000);
});

function updateSoundUI() {
    const icon = document.getElementById('soundIcon');
    const text = document.getElementById('soundStatusText');
    if (alertSoundEnabled) {
        icon.className = 'fa fa-volume-up mr-1 text-success';
        text.innerText = 'ON';
    } else {
        icon.className = 'fa fa-volume-off mr-1 text-muted';
        text.innerText = 'MUTED';
    }
}

function toggleAlertSound() {
    alertSoundEnabled = !alertSoundEnabled;
    localStorage.setItem('fiinway_alert_sound', alertSoundEnabled ? 'true' : 'false');
    updateSoundUI();
    if (alertSoundEnabled) {
        playEscalationAlertSound();
    }
}

// Sound synthesized using Web Audio API (Reliable in all browsers without external asset files)
function playEscalationAlertSound() {
    if (!alertSoundEnabled) return;
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        
        const osc1 = ctx.createOscillator();
        const osc2 = ctx.createOscillator();
        const gain = ctx.createGain();

        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
        osc1.frequency.setValueAtTime(880, ctx.currentTime + 0.15); // A5

        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.45);

        osc1.connect(gain);
        gain.connect(ctx.destination);

        osc1.start();
        osc1.stop(ctx.currentTime + 0.45);
    } catch (e) {
        console.warn('Audio playback restricted by browser policy until interaction:', e);
    }
}

// Background poll
function pollEscalationStatus() {
    fetch('{{ route("service_requests.escalation_check") }}')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.has_escalations) {
                playEscalationAlertSound();
            }
        })
        .catch(err => console.debug('Poll check error:', err));
}

// Open Assign Partner Modal
function openAssignPartnerModal(bookingId, serviceName) {
    currentActiveBookingId = bookingId;
    $('#assignPartnerModal').modal('show');

    document.getElementById('modalLoadingSpinner').style.display = 'block';
    document.getElementById('modalPartnersContent').style.display = 'none';
    document.getElementById('modalSubtitle').innerText = `Loading partners for "${serviceName}"...`;
    document.getElementById('partnerSearchFilter').value = '';

    fetch(`{{ url('service-requests/nearby-providers') }}/${bookingId}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalLoadingSpinner').style.display = 'none';
            if (!data.success) {
                alert(data.message || 'Failed to fetch nearby providers.');
                $('#assignPartnerModal').modal('hide');
                return;
            }

            renderModalData(data);
        })
        .catch(err => {
            document.getElementById('modalLoadingSpinner').style.display = 'none';
            alert('Network error while fetching partners: ' + err.message);
        });
}

function renderModalData(data) {
    const booking = data.booking;
    document.getElementById('modalSubtitle').innerText = `Related business partners for #${booking.id} - ${booking.service_name}`;
    document.getElementById('mServiceTitle').innerText = booking.service_name || 'Home Service';
    document.getElementById('mElapsedTime').innerText = `${booking.elapsed_minutes || 0} mins ago`;
    document.getElementById('mCustomerName').innerText = booking.customer_name || 'Customer';
    document.getElementById('mCustomerPhone').innerText = booking.customer_phone || 'N/A';
    document.getElementById('mServiceAddress').innerText = booking.service_address || 'Customer Location';

    const within30 = data.providers_within_30km || [];
    const otherList = data.other_matching_providers || [];
    cachedNearbyPartners = [...within30, ...otherList];

    document.getElementById('countWithin30').innerText = within30.length;
    document.getElementById('countOther').innerText = otherList.length;

    renderPartnerList(within30, 'listWithin30');
    renderPartnerList(otherList, 'listOtherPartners');

    if (otherList.length > 0) {
        document.getElementById('sectionOtherPartners').style.display = 'block';
    } else {
        document.getElementById('sectionOtherPartners').style.display = 'none';
    }

    if (within30.length === 0 && otherList.length === 0) {
        document.getElementById('modalNoPartners').style.display = 'block';
    } else {
        document.getElementById('modalNoPartners').style.display = 'none';
    }

    document.getElementById('modalPartnersContent').style.display = 'block';
}

function renderPartnerList(partners, containerId) {
    const container = document.getElementById(containerId);
    if (!partners || partners.length === 0) {
        container.innerHTML = '<div class="text-muted small p-2 text-center bg-white rounded border">No partners in this radius.</div>';
        return;
    }

    let html = '';
    partners.forEach(p => {
        const onlineBadge = p.online 
            ? '<span class="badge badge-success px-2 py-0" style="font-size: 11px;">🟢 Online</span>' 
            : '<span class="badge badge-light border text-muted px-2 py-0" style="font-size: 11px;">⚪ Offline</span>';

        const distanceBadge = p.distance_km !== null 
            ? `<span class="badge badge-primary px-2 py-1 font-weight-bold"><i class="fa fa-map-marker mr-1"></i>${p.distance_label}</span>`
            : `<span class="badge badge-secondary px-2 py-1">${p.distance_label}</span>`;

        const businessName = p.business_name ? `<div class="small text-muted font-italic">${escapeHtml(p.business_name)}</div>` : '';

        // Requirement: "dont give button just show number and also the buinsess user see which related to that service not all"
        // Phone is displayed clearly as text with clickable tel: link and quick copy icon, no action button
        const phoneDisplay = `
            <div class="d-flex align-items-center">
                <span class="partner-phone-display">
                    <i class="fa fa-phone text-success mr-1"></i>
                    <a href="tel:${encodeURIComponent(p.phone)}" class="text-dark text-decoration-none" title="Click to call from phone/device">${escapeHtml(p.phone)}</a>
                </span>
                <button type="button" class="btn-copy-phone ml-1" onclick="copyPhoneNumber('${escapeHtml(p.phone)}')" title="Copy Phone Number">
                    <i class="fa fa-copy"></i>
                </button>
            </div>
            ${p.alternate_phone ? `<div class="small text-muted">Alt: <a href="tel:${encodeURIComponent(p.alternate_phone)}" class="text-muted">${escapeHtml(p.alternate_phone)}</a></div>` : ''}
        `;

        html += `
            <div class="partner-card-item partner-item-node" data-name="${escapeHtml(p.name).toLowerCase()}" data-phone="${escapeHtml(p.phone).toLowerCase()}">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center" style="min-width: 220px;">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3 font-weight-bold text-primary border" style="width: 44px; height: 44px; font-size: 16px;">
                            ${escapeHtml(p.name).charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="font-weight-bold text-dark mb-0">${escapeHtml(p.name)}</h6>
                                ${onlineBadge}
                            </div>
                            ${businessName}
                            <div class="small text-muted">
                                <span class="badge badge-light border text-dark mr-1">${escapeHtml(p.profession)}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center px-2">
                        ${distanceBadge}
                        ${p.address ? `<div class="small text-muted text-truncate" style="max-width: 180px;">${escapeHtml(p.address)}</div>` : ''}
                    </div>

                    <!-- Phone Number Display (No call button, plain number display) -->
                    <div class="text-center px-2">
                        <div class="text-muted small" style="font-size: 10.5px; font-weight: 700;">PHONE NUMBER</div>
                        ${phoneDisplay}
                    </div>

                    <!-- Assign Partner Button -->
                    <div>
                        <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm font-weight-bold" onclick="assignPartnerToBooking(${currentActiveBookingId}, ${p.id}, '${escapeJs(p.name)}')">
                            <i class="fa fa-check-circle mr-1"></i> Assign Partner
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function filterPartnerListUI() {
    const q = (document.getElementById('partnerSearchFilter').value || '').toLowerCase().trim();
    const nodes = document.querySelectorAll('.partner-item-node');
    nodes.forEach(node => {
        const name = node.getAttribute('data-name') || '';
        const phone = node.getAttribute('data-phone') || '';
        if (name.includes(q) || phone.includes(q)) {
            node.style.display = 'block';
        } else {
            node.style.display = 'none';
        }
    });
}

function copyPhoneNumber(phone) {
    if (!phone || phone === 'N/A') return;
    navigator.clipboard.writeText(phone).then(() => {
        alert('Phone number copied to clipboard: ' + phone);
    }).catch(() => {
        prompt('Copy phone number:', phone);
    });
}

function assignPartnerToBooking(bookingId, driverId, driverName) {
    if (!confirm(`Are you sure you want to assign ${driverName} to Booking #SR-${bookingId}?\n\nThe customer and partner will receive instant push notifications, and the booking will be confirmed.`)) {
        return;
    }

    const postData = {
        _token: '{{ csrf_token() }}',
        driver_id: driverId
    };

    fetch(`{{ url('service-requests/assign') }}/${bookingId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(postData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(`✅ ${data.message}`);
            $('#assignPartnerModal').modal('hide');
            window.location.reload();
        } else {
            alert(`❌ ${data.message || 'Failed to assign partner.'}`);
        }
    })
    .catch(err => {
        alert(`❌ Network error while assigning partner: ${err.message}`);
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function escapeJs(text) {
    if (!text) return '';
    return String(text).replace(/'/g, "\\'").replace(/"/g, '\\"');
}
</script>
@endsection


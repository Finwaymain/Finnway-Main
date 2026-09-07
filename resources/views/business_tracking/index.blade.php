@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="padding-top: 20px;">
    <div class="container-fluid">
        <!-- Breadcrumb & Top Bar -->
        <div class="row page-titles mb-3">
            <div class="col-md-6 align-self-center">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3" style="width: 44px; height: 44px; border-radius: 12px; font-size: 22px; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);">
                        <i class="mdi mdi-crosshairs-gps"></i>
                    </div>
                    <div>
                        <h3 class="text-themecolor mb-0 font-weight-bold" style="font-size: 20px; color: #0f172a;">Live Business User Tracking</h3>
                        <p class="text-muted mb-0" style="font-size: 12px;">Real-time GPS tracking, availability status, and fleet monitoring</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 align-self-center">
                <div class="d-flex align-items-center justify-content-md-end mt-2 mt-md-0" style="gap: 10px;">
                    <span class="badge badge-pill badge-light border d-inline-flex align-items-center px-3 py-2" style="background: #ffffff; color: #059669; font-size: 12px; font-weight: 600; gap: 6px;">
                        <span class="live-indicator-dot"></span>
                        Live GPS Feed
                    </span>
                    <span id="sync-countdown" class="text-muted font-weight-bold" style="font-size: 12px; min-width: 80px;">Sync in 10s</span>
                    <button id="btn-manual-sync" class="btn btn-sm btn-primary d-inline-flex align-items-center" style="border-radius: 8px; font-weight: 600; gap: 4px;">
                        <i class="mdi mdi-refresh" id="refresh-icon"></i> Refresh Now
                    </button>
                    <button id="btn-fit-all" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center" style="border-radius: 8px; font-weight: 600; gap: 4px; background: #ffffff;">
                        <i class="mdi mdi-fullscreen"></i> Center All
                    </button>
                </div>
            </div>
        </div>

        <!-- ── HEADER LIVE METRIC COUNTERS ── -->
        <div class="row mb-3" style="row-gap: 12px;">
            <!-- 1. Total Registered Business Users -->
            <div class="col-xl col-md-4 col-sm-6">
                <div class="tracking-stat-card card mb-0 border-0 shadow-sm" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #4f46e5 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Total Partners</div>
                                <h3 id="stat-total" class="font-weight-bold mb-0 mt-1" style="font-size: 24px; color: #0f172a;">{{ $counts['total'] }}</h3>
                                <div class="text-muted small mt-1" style="font-size: 11px;">Registered Drivers</div>
                            </div>
                            <div class="stat-icon-wrapper d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 10px; background: #eef2ff; color: #4f46e5; font-size: 22px;">
                                <i class="mdi mdi-account-group"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Online & Available -->
            <div class="col-xl col-md-4 col-sm-6">
                <div class="tracking-stat-card card mb-0 border-0 shadow-sm" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #10b981 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Online & Available</div>
                                <div class="d-flex align-items-center mt-1">
                                    <h3 id="stat-online" class="font-weight-bold mb-0" style="font-size: 24px; color: #10b981;">{{ $counts['online'] }}</h3>
                                    <span class="badge badge-success-light text-success ml-2 px-2 py-0" style="font-size: 10px; background: #ecfdf5; border-radius: 6px; font-weight: 700;">ACTIVE</span>
                                </div>
                                <div class="text-muted small mt-1" style="font-size: 11px;">Ready for Bookings</div>
                            </div>
                            <div class="stat-icon-wrapper d-flex align-items-center justify-content-center position-relative" style="width: 44px; height: 44px; border-radius: 10px; background: #ecfdf5; color: #10b981; font-size: 22px;">
                                <i class="mdi mdi-radar"></i>
                                <span class="stat-pulse-ring"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. On Trip / On Duty -->
            <div class="col-xl col-md-4 col-sm-6">
                <div class="tracking-stat-card card mb-0 border-0 shadow-sm" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #f59e0b !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">On Trip / Engaged</div>
                                <h3 id="stat-on-trip" class="font-weight-bold mb-0 mt-1" style="font-size: 24px; color: #f59e0b;">{{ $counts['on_trip'] }}</h3>
                                <div class="text-muted small mt-1" style="font-size: 11px;">Serving Active Rides</div>
                            </div>
                            <div class="stat-icon-wrapper d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 10px; background: #fffbeb; color: #f59e0b; font-size: 22px;">
                                <i class="mdi mdi-car-connected"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Offline -->
            <div class="col-xl col-md-6 col-sm-6">
                <div class="tracking-stat-card card mb-0 border-0 shadow-sm" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #94a3b8 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Offline / Inactive</div>
                                <h3 id="stat-offline" class="font-weight-bold mb-0 mt-1" style="font-size: 24px; color: #64748b;">{{ $counts['offline'] }}</h3>
                                <div class="text-muted small mt-1" style="font-size: 11px;">Off Duty</div>
                            </div>
                            <div class="stat-icon-wrapper d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 10px; background: #f1f5f9; color: #64748b; font-size: 22px;">
                                <i class="mdi mdi-account-off-outline"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Located on Map (GPS Signal) -->
            <div class="col-xl col-md-6 col-sm-12">
                <div class="tracking-stat-card card mb-0 border-0 shadow-sm" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #0284c7 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">GPS Located</div>
                                <div class="d-flex align-items-center mt-1">
                                    <h3 id="stat-located" class="font-weight-bold mb-0" style="font-size: 24px; color: #0284c7;">{{ $counts['located'] }}</h3>
                                    <span class="badge badge-info-light text-info ml-2 px-2 py-0" style="font-size: 10px; background: #f0f9ff; border-radius: 6px; font-weight: 700;">ON MAP</span>
                                </div>
                                <div class="text-muted small mt-1" style="font-size: 11px;">Live Pin Markers</div>
                            </div>
                            <div class="stat-icon-wrapper d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 10px; background: #f0f9ff; color: #0284c7; font-size: 22px;">
                                <i class="mdi mdi-map-marker-radius"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── MAIN TRACKING PANEL & MAP ── -->
        <div class="row">
            <!-- Left Panel: Filter & Business Users List (4 cols) -->
            <div class="col-xl-4 col-lg-5 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
                    <div class="card-header bg-white border-bottom p-3" style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                        <!-- Search input -->
                        <div class="position-relative mb-2">
                            <i class="mdi mdi-magnify" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #94a3b8;"></i>
                            <input type="text" id="filter-search" class="form-control" placeholder="Search partner name, phone, vehicle..." style="height: 38px; padding-left: 36px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px;">
                            <span id="clear-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; display: none;"><i class="mdi mdi-close-circle"></i></span>
                        </div>

                        <!-- Status Filter Tabs -->
                        <div class="d-flex align-items-center justify-content-between mt-2" style="gap: 4px;">
                            <button class="btn btn-sm filter-tab active flex-fill" data-status="all" style="border-radius: 6px; font-size: 12px; font-weight: 600; padding: 6px 4px;">
                                All (<span class="tab-cnt-all">{{ $counts['total'] }}</span>)
                            </button>
                            <button class="btn btn-sm filter-tab flex-fill" data-status="online" style="border-radius: 6px; font-size: 12px; font-weight: 600; padding: 6px 4px; color: #059669;">
                                Online (<span class="tab-cnt-online">{{ $counts['online'] }}</span>)
                            </button>
                            <button class="btn btn-sm filter-tab flex-fill" data-status="on_trip" style="border-radius: 6px; font-size: 12px; font-weight: 600; padding: 6px 4px; color: #d97706;">
                                On Trip (<span class="tab-cnt-on-trip">{{ $counts['on_trip'] }}</span>)
                            </button>
                            <button class="btn btn-sm filter-tab flex-fill" data-status="offline" style="border-radius: 6px; font-size: 12px; font-weight: 600; padding: 6px 4px; color: #64748b;">
                                Offline (<span class="tab-cnt-offline">{{ $counts['offline'] }}</span>)
                            </button>
                        </div>

                        <!-- Vehicle / Category Dropdown Filter -->
                        <div class="mt-2">
                            <select id="filter-category" class="form-control form-control-sm" style="border-radius: 6px; height: 32px; font-size: 12px; border: 1px solid #cbd5e1; background: #f8fafc;">
                                <option value="all">All Vehicle Types / Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Scrollable Driver Cards Container -->
                    <div class="card-body p-0">
                        <div id="driver-list-container" class="driver-scroll-list" style="height: calc(100vh - 380px); min-height: 480px; overflow-y: auto;">
                            <!-- Dynamic Cards loaded via JS -->
                            <div class="p-4 text-center text-muted">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                <span class="ml-2" style="font-size: 13px;">Loading live business users...</span>
                            </div>
                        </div>
                    </div>

                    <!-- List Footer Info -->
                    <div class="card-footer bg-light px-3 py-2 border-top d-flex align-items-center justify-content-between" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px; font-size: 11px; color: #64748b;">
                        <span>Showing <strong id="visible-driver-count">0</strong> partners</span>
                        <span>Click card to locate on map <i class="mdi mdi-target text-primary"></i></span>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Live Map (8 cols) -->
            <div class="col-xl-8 col-lg-7 mb-3">
                <div class="card border-0 shadow-sm h-100 position-relative" style="border-radius: 14px; background: #ffffff; overflow: hidden;">
                    <!-- Floating Map Overlay Controls -->
                    <div class="map-overlay-controls position-absolute" style="top: 14px; right: 14px; z-index: 1000; display: flex; gap: 8px;">
                        <div class="btn-group bg-white shadow-sm rounded border" style="border-radius: 8px !important; overflow: hidden;">
                            <button id="layer-streets" class="btn btn-sm btn-white layer-btn active px-3 py-1" style="font-size: 12px; font-weight: 600;">Streets</button>
                            <button id="layer-satellite" class="btn btn-sm btn-white layer-btn px-3 py-1" style="font-size: 12px; font-weight: 600;">Satellite</button>
                        </div>
                    </div>

                    <!-- Map Container -->
                    <div id="live-tracking-map" style="height: calc(100vh - 300px); min-height: 560px; width: 100%; z-index: 1;"></div>

                    <!-- Floating Map Legend -->
                    <div class="map-legend-box position-absolute bg-white shadow-sm p-2 px-3 border" style="bottom: 16px; left: 16px; z-index: 1000; border-radius: 8px; font-size: 11px; display: flex; align-items: center; gap: 14px;">
                        <span class="d-flex align-items-center" style="gap: 5px;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                            Online & Ready
                        </span>
                        <span class="d-flex align-items-center" style="gap: 5px;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                            On Trip / Busy
                        </span>
                        <span class="d-flex align-items-center" style="gap: 5px;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #94a3b8; display: inline-block;"></span>
                            Offline
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── STYLES ── -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .tracking-stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .tracking-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06) !important;
    }
    .live-indicator-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: live-pulse 1.8s infinite;
        display: inline-block;
    }
    @keyframes live-pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .stat-pulse-ring {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 10px;
        border: 2px solid #10b981;
        opacity: 0.6;
        animation: stat-pulse 2s cubic-bezier(0.24, 0, 0.38, 1) infinite;
    }
    @keyframes stat-pulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.15); opacity: 0; }
        100% { transform: scale(0.95); opacity: 0; }
    }
    .filter-tab {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid transparent;
        transition: all 0.15s ease;
    }
    .filter-tab:hover {
        background: #e2e8f0;
    }
    .filter-tab.active {
        background: #4f46e5 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
    }
    .layer-btn.active {
        background: #4f46e5 !important;
        color: #ffffff !important;
    }
    .driver-scroll-list::-webkit-scrollbar {
        width: 5px;
    }
    .driver-scroll-list::-webkit-scrollbar-track {
        background: #f8fafc;
    }
    .driver-scroll-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .driver-card-item {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .driver-card-item:hover,
    .driver-card-item.selected {
        background: #f8fafc !important;
        border-left: 3px solid #4f46e5 !important;
    }
    .driver-card-item.selected {
        background: #eef2ff !important;
    }
    /* Custom Leaflet Marker Icons */
    .custom-gps-marker {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .marker-pin {
        width: 38px;
        height: 38px;
        border-radius: 50% 50% 50% 0;
        position: absolute;
        transform: rotate(-45deg);
        left: 50%;
        top: 50%;
        margin: -24px 0 0 -19px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
    }
    .marker-pin i {
        transform: rotate(45deg);
        color: #ffffff;
        font-size: 16px;
    }
    .marker-pin.online {
        background: #10b981;
    }
    .marker-pin.on_trip {
        background: #f59e0b;
    }
    .marker-pin.offline {
        background: #64748b;
    }
    .marker-pulse {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        position: absolute;
        left: 50%;
        top: 50%;
        margin: 6px 0 0 -7px;
        background: rgba(0,0,0,0.2);
        animation: marker-pulse-anim 1.5s infinite;
    }
    @keyframes marker-pulse-anim {
        0% { transform: scale(0.8); opacity: 0.5; }
        100% { transform: scale(2.2); opacity: 0; }
    }
    /* Popup styling */
    .leaflet-popup-content-wrapper {
        border-radius: 12px !important;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2) !important;
        padding: 0 !important;
        overflow: hidden;
    }
    .leaflet-popup-content {
        margin: 0 !important;
        line-height: 1.4 !important;
    }
</style>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
$(document).ready(function() {
    var map;
    var markersLayer = L.layerGroup();
    var markersMap = {}; // driver_id -> Leaflet Marker
    var allDrivers = [];
    var currentStatusFilter = 'all';
    var currentCategoryFilter = 'all';
    var currentSearchKeyword = '';
    var syncIntervalSeconds = 10;
    var countdown = syncIntervalSeconds;
    var syncTimerId = null;
    var isFetching = false;

    // Tile Layers
    var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    });

    var satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
        maxZoom: 18
    });

    // Initialize Map
    var initLat = {{ $center['lat'] ?? 20.5937 }};
    var initLng = {{ $center['lng'] ?? 78.9629 }};

    map = L.map('live-tracking-map', {
        center: [initLat, initLng],
        zoom: 12,
        layers: [osmLayer]
    });

    markersLayer.addTo(map);

    // Layer Toggle Buttons
    $('#layer-streets').click(function() {
        $(this).addClass('active');
        $('#layer-satellite').removeClass('active');
        map.removeLayer(satelliteLayer);
        map.addLayer(osmLayer);
    });

    $('#layer-satellite').click(function() {
        $(this).addClass('active');
        $('#layer-streets').removeClass('active');
        map.removeLayer(osmLayer);
        map.addLayer(satelliteLayer);
    });

    // Icon resolver helper
    function getVehicleIconClass(vehicleType) {
        var v = (vehicleType || '').toLowerCase();
        if (v.indexOf('bike') !== -1 || v.indexOf('motor') !== -1) return 'mdi mdi-motorbike';
        if (v.indexOf('auto') !== -1 || v.indexOf('rickshaw') !== -1) return 'mdi mdi-rickshaw';
        if (v.indexOf('truck') !== -1 || v.indexOf('cargo') !== -1) return 'mdi mdi-truck-fast';
        if (v.indexOf('service') !== -1 || v.indexOf('plumb') !== -1 || v.indexOf('electric') !== -1) return 'mdi mdi-wrench';
        return 'mdi mdi-car';
    }

    // Create Custom HTML Marker for Leaflet
    function createCustomMarker(driver) {
        var statusClass = driver.status; // online | on_trip | offline | inactive
        var iconClass = getVehicleIconClass(driver.vehicle_type);

        var html = '<div class="custom-gps-marker">' +
            '<div class="marker-pulse ' + (driver.status === 'online' ? 'online' : '') + '"></div>' +
            '<div class="marker-pin ' + statusClass + '">' +
                '<i class="' + iconClass + '"></i>' +
            '</div>' +
        '</div>';

        return L.divIcon({
            html: html,
            className: 'custom-driver-marker-wrapper',
            iconSize: [38, 38],
            iconAnchor: [19, 36],
            popupAnchor: [0, -32]
        });
    }

    // Build Popup HTML Card
    function buildPopupHtml(driver) {
        var badgeColor = '#64748b';
        var badgeBg = '#f1f5f9';
        if (driver.status === 'online') {
            badgeColor = '#059669';
            badgeBg = '#ecfdf5';
        } else if (driver.status === 'on_trip') {
            badgeColor = '#d97706';
            badgeBg = '#fffbeb';
        }

        return '<div style="width: 250px; font-family: inherit;">' +
            '<div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 12px 14px; color: #ffffff;">' +
                '<div class="d-flex align-items-center">' +
                    '<img src="' + driver.avatar + '" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #ffffff; margin-right: 10px;" onerror="this.src=\'{{ asset('images/user.png') }}\'">' +
                    '<div style="min-width: 0;">' +
                        '<div style="font-weight: 700; font-size: 13px; color: #ffffff;" class="text-truncate">' + driver.name + '</div>' +
                        '<div style="font-size: 11px; color: #94a3b8;">Partner #' + driver.id + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div style="padding: 12px 14px; background: #ffffff;">' +
                '<div class="d-flex align-items-center justify-content-between mb-2">' +
                    '<span style="font-size: 11px; color: #64748b;">Status:</span>' +
                    '<span style="font-size: 11px; font-weight: 700; color: ' + badgeColor + '; background: ' + badgeBg + '; padding: 2px 8px; border-radius: 6px;">' + driver.status_label + '</span>' +
                '</div>' +
                '<div class="d-flex align-items-center justify-content-between mb-2">' +
                    '<span style="font-size: 11px; color: #64748b;">Category:</span>' +
                    '<span style="font-size: 11px; font-weight: 600; color: #1e293b;">' + driver.vehicle_type + '</span>' +
                '</div>' +
                '<div class="d-flex align-items-center justify-content-between mb-2">' +
                    '<span style="font-size: 11px; color: #64748b;">Phone:</span>' +
                    '<a href="tel:' + driver.phone + '" style="font-size: 11px; font-weight: 600; color: #4f46e5; text-decoration: none;">' + driver.phone + '</a>' +
                '</div>' +
                '<div class="d-flex align-items-center justify-content-between mb-3">' +
                    '<span style="font-size: 11px; color: #64748b;">GPS Updated:</span>' +
                    '<span style="font-size: 11px; color: #475569;">' + driver.last_updated + '</span>' +
                '</div>' +
                '<div class="d-flex align-items-center justify-content-between" style="gap: 6px;">' +
                    '<a href="' + driver.edit_url + '" class="btn btn-sm btn-primary flex-fill text-center" target="_blank" style="font-size: 11px; font-weight: 600; border-radius: 6px; padding: 4px 8px; color: #ffffff !important;">View Profile</a>' +
                    '<a href="https://maps.google.com/?q=' + driver.latitude + ',' + driver.longitude + '" target="_blank" class="btn btn-sm btn-light border flex-fill text-center" style="font-size: 11px; font-weight: 600; border-radius: 6px; padding: 4px 8px;">Google Maps</a>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    // Fetch Live Data
    function fetchLiveData(autoFit) {
        if (isFetching) return;
        isFetching = true;
        $('#refresh-icon').addClass('mdi-spin');

        var url = '{{ route("business.tracking.data") }}';
        var params = {
            status: currentStatusFilter,
            category: currentCategoryFilter,
            search: currentSearchKeyword
        };

        $.ajax({
            url: url,
            method: 'GET',
            data: params,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    allDrivers = response.drivers || [];
                    updateHeaderCounts(response.counts);
                    updateMapMarkers(allDrivers, autoFit);
                    renderDriverList(allDrivers);
                }
            },
            error: function(err) {
                console.error("Live tracking sync error:", err);
            },
            complete: function() {
                isFetching = false;
                $('#refresh-icon').removeClass('mdi-spin');
                countdown = syncIntervalSeconds;
            }
        });
    }

    // Update Header Counts
    function updateHeaderCounts(counts) {
        if (!counts) return;
        $('#stat-total').text(counts.total);
        $('#stat-online').text(counts.online);
        $('#stat-on-trip').text(counts.on_trip);
        $('#stat-offline').text(counts.offline);
        $('#stat-located').text(counts.located);

        $('.tab-cnt-all').text(counts.total);
        $('.tab-cnt-online').text(counts.online);
        $('.tab-cnt-on-trip').text(counts.on_trip);
        $('.tab-cnt-offline').text(counts.offline);
    }

    // Update Leaflet Map Markers
    function updateMapMarkers(drivers, autoFit) {
        var currentDriverIds = {};
        var bounds = [];

        drivers.forEach(function(driver) {
            if (!driver.has_coords) return;

            var lat = driver.latitude;
            var lng = driver.longitude;
            bounds.push([lat, lng]);
            currentDriverIds[driver.id] = true;

            if (markersMap[driver.id]) {
                // Update position & popup
                markersMap[driver.id].setLatLng([lat, lng]);
                markersMap[driver.id].setIcon(createCustomMarker(driver));
                markersMap[driver.id].setPopupContent(buildPopupHtml(driver));
            } else {
                // Create new marker
                var marker = L.marker([lat, lng], {
                    icon: createCustomMarker(driver),
                    title: driver.name
                }).bindPopup(buildPopupHtml(driver));

                marker.on('click', function() {
                    highlightDriverCard(driver.id);
                });

                markersLayer.addLayer(marker);
                markersMap[driver.id] = marker;
            }
        });

        // Remove markers for drivers no longer in the list
        for (var id in markersMap) {
            if (!currentDriverIds[id]) {
                markersLayer.removeLayer(markersMap[id]);
                delete markersMap[id];
            }
        }

        // Fit map bounds if requested or on initial load
        if (autoFit && bounds.length > 0) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
        }
    }

    // Render Driver Cards List in Left Panel
    function renderDriverList(drivers) {
        var container = $('#driver-list-container');
        $('#visible-driver-count').text(drivers.length);

        if (drivers.length === 0) {
            container.html(
                '<div class="p-4 text-center text-muted">' +
                    '<i class="mdi mdi-account-search" style="font-size: 36px; color: #94a3b8; display: block; margin-bottom: 6px;"></i>' +
                    '<div style="font-size: 13px; font-weight: 600; color: #1e293b;">No business users match filters</div>' +
                    '<div style="font-size: 11px; color: #64748b; margin-top: 2px;">Try adjusting status, category, or search keyword</div>' +
                '</div>'
            );
            return;
        }

        var html = '';
        drivers.forEach(function(d) {
            var statusBadge = '';
            if (d.status === 'online') {
                statusBadge = '<span class="badge badge-success" style="font-size: 10px; background: #10b981; font-weight: 600; padding: 2px 6px;">Online</span>';
            } else if (d.status === 'on_trip') {
                statusBadge = '<span class="badge badge-warning text-white" style="font-size: 10px; background: #f59e0b; font-weight: 600; padding: 2px 6px;">On Trip</span>';
            } else {
                statusBadge = '<span class="badge badge-secondary" style="font-size: 10px; background: #94a3b8; font-weight: 600; padding: 2px 6px;">Offline</span>';
            }

            var gpsBadge = d.has_coords
                ? '<span class="text-success small d-flex align-items-center" style="font-size: 11px; gap: 2px;" title="GPS location active"><i class="mdi mdi-map-marker-check"></i> GPS Active</span>'
                : '<span class="text-muted small d-flex align-items-center" style="font-size: 11px; gap: 2px;" title="No GPS signal"><i class="mdi mdi-map-marker-off"></i> No GPS</span>';

            html += 
                '<div class="driver-card-item d-flex align-items-center justify-content-between" data-id="' + d.id + '" data-lat="' + d.latitude + '" data-lng="' + d.longitude + '" data-has-coords="' + d.has_coords + '">' +
                    '<div class="d-flex align-items-center" style="min-width: 0; gap: 10px;">' +
                        '<div class="position-relative">' +
                            '<img src="' + d.avatar + '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #e2e8f0;" onerror="this.src=\'{{ asset('images/user.png') }}\'">' +
                            '<span style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; border-radius: 50%; background: ' + (d.status === 'online' ? '#10b981' : (d.status === 'on_trip' ? '#f59e0b' : '#94a3b8')) + '; border: 2px solid #ffffff;"></span>' +
                        '</div>' +
                        '<div style="min-width: 0;">' +
                            '<div class="d-flex align-items-center" style="gap: 6px;">' +
                                '<span class="font-weight-bold text-truncate" style="font-size: 13px; color: #0f172a;">' + d.name + '</span>' +
                                statusBadge +
                            '</div>' +
                            '<div class="text-muted text-truncate" style="font-size: 11px; margin-top: 1px;">' +
                                '<i class="' + getVehicleIconClass(d.vehicle_type) + ' mr-1 text-primary"></i>' + d.vehicle_type + ' &bull; ' + d.phone +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="text-right flex-shrink-0 ml-2">' +
                        gpsBadge +
                        '<div class="text-muted" style="font-size: 10px; margin-top: 2px;">' + d.last_updated + '</div>' +
                    '</div>' +
                '</div>';
        });

        container.html(html);
    }

    // Locate / Focus Driver on Map when clicking card
    $(document).on('click', '.driver-card-item', function() {
        var id = $(this).data('id');
        var hasCoords = $(this).data('has-coords');
        var lat = parseFloat($(this).data('lat'));
        var lng = parseFloat($(this).data('lng'));

        highlightDriverCard(id);

        if (!hasCoords || isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) {
            Swal.fire({
                title: 'No GPS Signal',
                text: 'This partner has not uploaded live GPS coordinates recently.',
                icon: 'info',
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'OK',
                timer: 2500
            });
            return;
        }

        map.flyTo([lat, lng], 16, { animate: true, duration: 1.2 });

        if (markersMap[id]) {
            setTimeout(function() {
                markersMap[id].openPopup();
            }, 800);
        }
    });

    function highlightDriverCard(id) {
        $('.driver-card-item').removeClass('selected');
        var card = $('.driver-card-item[data-id="' + id + '"]');
        if (card.length) {
            card.addClass('selected');
            card[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // Status Tab Filter Click
    $('.filter-tab').click(function() {
        $('.filter-tab').removeClass('active');
        $(this).addClass('active');
        currentStatusFilter = $(this).data('status');
        fetchLiveData(false);
    });

    // Category Dropdown Filter Change
    $('#filter-category').change(function() {
        currentCategoryFilter = $(this).val();
        fetchLiveData(false);
    });

    // Search Input Typing (Debounced)
    var searchTimer = null;
    $('#filter-search').on('input', function() {
        var val = $(this).val();
        if (val.length > 0) {
            $('#clear-search').show();
        } else {
            $('#clear-search').hide();
        }

        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            currentSearchKeyword = val;
            fetchLiveData(false);
        }, 300);
    });

    $('#clear-search').click(function() {
        $('#filter-search').val('');
        $(this).hide();
        currentSearchKeyword = '';
        fetchLiveData(false);
    });

    // Manual Refresh Button
    $('#btn-manual-sync').click(function() {
        fetchLiveData(false);
    });

    // Center All Markers Button
    $('#btn-fit-all').click(function() {
        var allBounds = [];
        for (var id in markersMap) {
            allBounds.push(markersMap[id].getLatLng());
        }
        if (allBounds.length > 0) {
            map.fitBounds(allBounds, { padding: [40, 40], maxZoom: 15 });
        } else {
            map.setView([initLat, initLng], 12);
        }
    });

    // Countdown Timer & Auto-refresh Poller
    function startCountdown() {
        clearInterval(syncTimerId);
        syncTimerId = setInterval(function() {
            countdown--;
            if (countdown <= 0) {
                $('#sync-countdown').text('Syncing...');
                fetchLiveData(false);
            } else {
                $('#sync-countdown').text('Sync in ' + countdown + 's');
            }
        }, 1000);
    }

    // Initial Load
    fetchLiveData(true);
    startCountdown();
});
</script>
@endsection

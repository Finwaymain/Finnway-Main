@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles mb-3">
        <div class="col-md-6 align-self-center">
            <h3 class="text-themecolor mb-0 font-weight-bold"><i class="mdi mdi-cellphone-arrow-down text-primary mr-2"></i> App Version Control & Upgrade</h3>
            <small class="text-muted">Manage mobile app versioning, enforce compulsory Play Store updates, and toggle maintenance mode</small>
        </div>
        <div class="col-md-6 align-self-center text-right">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">System Settings</li>
                <li class="breadcrumb-item active">Version Control</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle mr-2"></i> {{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Navigation Tabs -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white p-3 border-bottom">
                <ul class="nav nav-pills" id="appVersionTabs">
                    <li class="nav-item mr-2">
                        <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'customer' ? 'active' : '' }}" href="{{ route('app-version-control.index', ['tab' => 'customer']) }}">
                            <i class="mdi mdi-account-circle mr-1"></i> Customer App (User)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'business' ? 'active' : '' }}" href="{{ route('app-version-control.index', ['tab' => 'business']) }}">
                            <i class="mdi mdi-car mr-1"></i> Driver & Partner App (Business)
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        @php
            $activeConfig = $tab === 'business' ? $businessConfig : $customerConfig;
            $otherConfig = $tab === 'business' ? $customerConfig : $businessConfig;
            $themeColor = $tab === 'business' ? '#4f46e5' : '#10b981';
        @endphp

        <div class="row">
            <!-- Left Column: Version Management Form -->
            <div class="col-lg-8 col-md-12">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; border-top: 4px solid {{ $themeColor }} !important;">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center text-white font-weight-bold" style="background-color: {{ $themeColor }}; width: 40px; height: 40px;">
                                <i class="mdi {{ $tab === 'business' ? 'mdi-car' : 'mdi-account-circle' }}" style="font-size: 22px;"></i>
                            </div>
                            <div>
                                <h4 class="card-title m-0 font-weight-bold text-dark">{{ $activeConfig->app_name }}</h4>
                                <small class="text-muted">Configure version thresholds and Play Store upgrade links</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('app-version-control.update', $activeConfig->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="app_name" value="{{ $activeConfig->app_name }}">

                            <!-- Quick Action Switches -->
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="p-3 rounded-lg border bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">
                                                <i class="mdi mdi-alert-decagram text-danger mr-1"></i> Master Force Update
                                            </h6>
                                            <small class="text-muted">Compulsory update for ALL older versions</small>
                                        </div>
                                        <div class="custom-control custom-switch custom-switch-lg">
                                            <input type="checkbox" name="force_update" class="custom-control-input" id="switchForceUpdate" {{ $activeConfig->force_update ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="switchForceUpdate"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 rounded-lg border bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">
                                                <i class="mdi mdi-wrench text-warning mr-1"></i> Maintenance Mode
                                            </h6>
                                            <small class="text-muted">Block app access for scheduled work</small>
                                        </div>
                                        <div class="custom-control custom-switch custom-switch-lg">
                                            <input type="checkbox" name="is_maintenance" class="custom-control-input" id="switchMaintenance" {{ $activeConfig->is_maintenance ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="switchMaintenance"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Version Thresholds -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="font-weight-600 text-dark">
                                            Latest Released Version <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-tag text-primary"></i></span>
                                            </div>
                                            <input type="text" name="latest_version" class="form-control border-left-0 font-weight-bold" value="{{ $activeConfig->latest_version }}" required placeholder="e.g. 1.0.17">
                                        </div>
                                        <small class="form-text text-muted">The latest build available on Google Play Store.</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="font-weight-600 text-dark">
                                            Minimum Supported Version <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-shield-alert text-danger"></i></span>
                                            </div>
                                            <input type="text" name="minimum_version" class="form-control border-left-0 font-weight-bold" value="{{ $activeConfig->minimum_version }}" required placeholder="e.g. 1.0.10">
                                        </div>
                                        <small class="form-text text-danger font-weight-500">Apps below this version will be FORCED to upgrade from Play Store.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Store Links -->
                            <div class="form-group mb-4">
                                <label class="font-weight-600 text-dark">
                                    <i class="mdi mdi-google-play text-success mr-1"></i> Google Play Store URL <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-link text-muted"></i></span>
                                    </div>
                                    <input type="url" name="playstore_url" class="form-control border-left-0" value="{{ $activeConfig->playstore_url }}" required placeholder="https://play.google.com/store/apps/details?id=...">
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-600 text-dark">
                                    <i class="mdi mdi-apple text-dark mr-1"></i> Apple App Store URL <small class="text-muted">(Optional)</small>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-link text-muted"></i></span>
                                    </div>
                                    <input type="url" name="appstore_url" class="form-control border-left-0" value="{{ $activeConfig->appstore_url }}" placeholder="https://apps.apple.com/app/id...">
                                </div>
                            </div>

                            <!-- Custom Alert Text -->
                            <div class="form-group mb-4">
                                <label class="font-weight-600 text-dark">
                                    Update Alert Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="title" class="form-control font-weight-bold" value="{{ $activeConfig->title }}" required placeholder="e.g. New Update Available!">
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-600 text-dark">
                                    Update Release Notes / Message
                                </label>
                                <textarea name="message" class="form-control" rows="3" placeholder="Describe the update reasons, new features, or bug fixes">{{ $activeConfig->message }}</textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-600 text-dark">
                                    Maintenance Announcement Message <small class="text-muted">(Shown when Maintenance Mode is ON)</small>
                                </label>
                                <textarea name="maintenance_message" class="form-control" rows="2" placeholder="Describe maintenance reason and expected uptime">{{ $activeConfig->maintenance_message }}</textarea>
                            </div>

                            <div class="text-right pt-3 border-top">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm font-weight-bold">
                                    <i class="mdi mdi-content-save mr-1"></i> Save Version Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Status Summary & Live Simulator -->
            <div class="col-lg-4 col-md-12">
                <!-- Status Card -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title m-0 font-weight-bold text-dark"><i class="mdi mdi-information-outline text-info mr-1"></i> Live Status Summary</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Target App</span>
                            <span class="badge badge-primary px-3 py-1 font-weight-bold">{{ $activeConfig->app_type === 'business' ? 'Driver Partner App' : 'Customer User App' }}</span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Latest Version</span>
                            <strong class="text-dark font-16">v{{ $activeConfig->latest_version }}</strong>
                        </div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Minimum Required</span>
                            <strong class="text-danger font-16">v{{ $activeConfig->minimum_version }}</strong>
                        </div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Forced Update Status</span>
                            @if ($activeConfig->force_update)
                                <span class="badge badge-danger px-3 py-1">Enforced (Active)</span>
                            @else
                                <span class="badge badge-success px-3 py-1">Standard (Version Based)</span>
                            @endif
                        </div>
                        <div class="mb-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Maintenance Mode</span>
                            @if ($activeConfig->is_maintenance)
                                <span class="badge badge-warning px-3 py-1">ON (Blocked)</span>
                            @else
                                <span class="badge badge-light border text-muted px-3 py-1">OFF (Normal)</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Live Upgrade Simulator -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background-color: #f8fafc;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title m-0 font-weight-bold text-dark"><i class="mdi mdi-play-circle-outline text-success mr-1"></i> Version Check Simulator</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="small text-muted mb-3">Test how the mobile app behaves when a specific version makes a request.</p>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Simulate Installed App Version:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="simVersionInput" class="form-control font-weight-bold" value="1.0.5" placeholder="e.g. 1.0.5">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" onclick="runSimulation()">Test</button>
                                </div>
                            </div>
                        </div>

                        <div id="simResult" class="p-3 rounded-lg border bg-white mt-3" style="display: none;">
                            <h6 class="font-weight-bold mb-2" id="simResultTitle"></h6>
                            <p class="small text-muted mb-2" id="simResultMessage"></p>
                            <div id="simResultBadge"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runSimulation() {
    const v = document.getElementById('simVersionInput').value.trim();
    if (!v) return;

    fetch(`/api/v1/app-version/check?app_type={{ $activeConfig->app_type }}&version=${encodeURIComponent(v)}`)
        .then(res => res.json())
        .then(res => {
            const data = res.data;
            const resDiv = document.getElementById('simResult');
            const titleEl = document.getElementById('simResultTitle');
            const msgEl = document.getElementById('simResultMessage');
            const badgeEl = document.getElementById('simResultBadge');

            resDiv.style.display = 'block';

            if (data.is_maintenance) {
                titleEl.textContent = '⛔ App Under Maintenance';
                titleEl.className = 'font-weight-bold mb-2 text-warning';
                msgEl.textContent = data.maintenance_message;
                badgeEl.innerHTML = '<span class="badge badge-warning">Blocked (Maintenance)</span>';
            } else if (data.force_update) {
                titleEl.textContent = '🚀 Force Update Required (Compulsory)';
                titleEl.className = 'font-weight-bold mb-2 text-danger';
                msgEl.textContent = data.message;
                badgeEl.innerHTML = '<span class="badge badge-danger">Non-Dismissible Play Store Modal</span>';
            } else if (data.optional_update) {
                titleEl.textContent = 'ℹ️ Optional Update Available';
                titleEl.className = 'font-weight-bold mb-2 text-primary';
                msgEl.textContent = data.message;
                badgeEl.innerHTML = '<span class="badge badge-info">Dismissible Update Modal</span>';
            } else {
                titleEl.textContent = '✅ App Up-To-Date';
                titleEl.className = 'font-weight-bold mb-2 text-success';
                msgEl.textContent = 'Installed version meets current requirements. Normal app access.';
                badgeEl.innerHTML = '<span class="badge badge-success">No Action Required</span>';
            }
        });
}
</script>
@endsection

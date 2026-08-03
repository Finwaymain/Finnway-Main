@extends('layouts.app')

@section('content')
<style>
    .referral-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .nav-tabs-custom {
        border-bottom: 2px solid #EFECE4;
        gap: 8px;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #555;
        font-weight: 600;
        font-size: 14px;
        padding: 10px 20px;
        border-radius: 8px 8px 0 0;
        transition: all 0.2s ease;
    }
    .nav-tabs-custom .nav-link.active {
        color: #00A859 !important;
        border-bottom-color: #00A859 !important;
        background-color: rgba(0, 168, 89, 0.05);
    }
    .btn-green-submit {
        background-color: #00A859;
        color: #fff;
        font-weight: 700;
        border-radius: 20px;
        padding: 8px 24px;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 168, 89, 0.25);
    }
    .btn-green-submit:hover {
        background-color: #008D4A;
        color: #fff;
    }
    .pill-switch {
        background: #F0F2F5;
        border-radius: 25px;
        padding: 4px;
        display: inline-flex;
    }
    .pill-switch button {
        border: none;
        padding: 8px 24px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        background: transparent;
        color: #666;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pill-switch button.active {
        background: #00A859;
        color: white;
        box-shadow: 0 2px 6px rgba(0, 168, 89, 0.3);
    }
    .metric-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 16px;
        border: 1px solid #EFECE4;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .custom-switch-green .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #00A859 !important;
        border-color: #00A859 !important;
    }
</style>

<div class="page-wrapper referral-container">
    <div class="container-fluid pt-3">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="font-weight-bold mb-1" style="color: #1A1A1A;">
                    <i class="mdi mdi-share-variant text-success mr-2"></i>Referral & Earn – Admin Panel
                </h3>
                <p class="text-muted small mb-0">Configure ecosystem reward rules, event triggers, service percentages, and track live referral logs.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius: 10px; background-color: #E8F8F0; color: #008D4A;">
                <i class="mdi mdi-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <!-- Single Page Main Tabs Navigation -->
        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="referralTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="reward-settings-tab" data-toggle="tab" href="#reward-settings" role="tab">Reward Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="event-rules-tab" data-toggle="tab" href="#event-rules" role="tab">Event Rules</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="service-rewards-tab" data-toggle="tab" href="#service-rewards" role="tab">Service-wise Rewards</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="reports-tab" data-toggle="tab" href="#reports" role="tab">Reports & Earnings Log</a>
            </li>
        </ul>

        <!-- Single Page Tab Content -->
        <div class="tab-content" id="referralTabsContent">

            <!-- TAB 1: REWARD SETTINGS -->
            <div class="tab-pane fade show active" id="reward-settings" role="tabpanel">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                    <h5 class="font-weight-bold text-dark mb-4">Reward Mode</h5>
                    <form action="{{ route('referral.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="reward_mode" id="reward_mode_input" value="{{ $rewardMode }}">

                        <div class="mb-4">
                            <div class="pill-switch">
                                <button type="button" class="{{ $rewardMode === 'percentage' ? 'active' : '' }}" onclick="selectRewardMode('percentage')">Percentage</button>
                                <button type="button" class="{{ $rewardMode === 'flat' ? 'active' : '' }}" onclick="selectRewardMode('flat')">Flat Amount</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted">Percentage Value / Reward Rate</label>
                                <div class="input-group">
                                    <input type="text" name="reward_value" class="form-control form-control-lg border-right-0" value="{{ $rewardValue }}" style="border-radius: 10px 0 0 10px;">
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-white border-left-0" style="border-radius: 0 10px 10px 0;">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted">Minimum Reward Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px;">₹</span>
                                    </div>
                                    <input type="text" name="reward_min" class="form-control form-control-lg border-left-0" value="{{ $rewardMin }}" style="border-radius: 0 10px 10px 0;">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-green-submit">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 2: EVENT RULES -->
            <div class="tab-pane fade" id="event-rules" role="tabpanel">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                    <h5 class="font-weight-bold text-dark mb-3">Referral Event Rules</h5>
                    <form action="{{ route('referral.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="event_rules_submit" value="1">
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead style="background-color: #F8F9FA;">
                                    <tr>
                                        <th class="font-weight-bold border-0">Event</th>
                                        <th class="font-weight-bold border-0 text-center">Enable</th>
                                        <th class="font-weight-bold border-0">Reward Type</th>
                                        <th class="font-weight-bold border-0">Value</th>
                                        <th class="font-weight-bold border-0 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($eventRules as $key => $rule)
                                    <tr>
                                        <td class="font-weight-bold text-dark">{{ $rule['name'] }}</td>
                                        <td class="text-center">
                                            <div class="custom-control custom-switch custom-switch-green">
                                                <input type="checkbox" class="custom-control-input" id="switch_{{ $key }}" name="event_{{ $key }}_enable" value="1" {{ $rule['enable'] == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="switch_{{ $key }}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light font-weight-bold px-3 py-2 text-dark">{{ ucfirst($rule['type']) }}</span>
                                        </td>
                                        <td>
                                            <input type="text" name="event_{{ $key }}_val" class="form-control form-control-sm font-weight-bold" style="width: 100px; border-radius: 6px;" value="{{ $rule['val'] }}">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-light text-primary font-weight-bold"><i class="mdi mdi-pencil"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-green-submit">Save Rules</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 3: SERVICE-WISE REWARDS -->
            <div class="tab-pane fade" id="service-rewards" role="tabpanel">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="font-weight-bold text-dark mb-0">Service-wise Rewards (Admin)</h5>
                    </div>

                    <!-- Service Sub Tabs -->
                    <ul class="nav nav-pills mb-4 gap-2" id="serviceSubTabs">
                        <li class="nav-item">
                            <a class="nav-link active bg-success text-white font-weight-bold rounded-pill px-3 py-1 mr-2" href="#">Consumer Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-muted font-weight-bold px-3 py-1 mr-2" href="#">Business Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-muted font-weight-bold px-3 py-1 mr-2" href="#">Marketplace</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-muted font-weight-bold px-3 py-1" href="#">Other</a>
                        </li>
                    </ul>

                    <form action="{{ route('referral.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="service_rewards_submit" value="1">

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead style="background-color: #F8F9FA;">
                                    <tr>
                                        <th class="font-weight-bold border-0">Service / Activity</th>
                                        <th class="font-weight-bold border-0">Reward Type</th>
                                        <th class="font-weight-bold border-0">Value</th>
                                        <th class="font-weight-bold border-0 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($consumerServices as $key => $srv)
                                    <tr>
                                        <td class="font-weight-bold text-dark">{{ $srv['name'] }}</td>
                                        <td><span class="badge badge-light font-weight-bold px-3 py-2 text-dark">{{ $srv['type'] }}</span></td>
                                        <td>
                                            <input type="text" name="srv_{{ $key }}_val" class="form-control form-control-sm font-weight-bold" style="width: 100px; border-radius: 6px;" value="{{ $srv['val'] }}">
                                        </td>
                                        <td class="text-center">
                                            <div class="custom-control custom-switch custom-switch-green">
                                                <input type="checkbox" class="custom-control-input" id="srv_switch_{{ $key }}" name="srv_{{ $key }}_status" value="1" {{ $srv['status'] == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="srv_switch_{{ $key }}"></label>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-green-submit">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 4: REPORTS & EARNINGS LOG -->
            <div class="tab-pane fade" id="reports" role="tabpanel">
                <!-- Top Metric Cards Grid (Matching Screenshots 1 & 3) -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="metric-card d-flex align-items-center">
                            <div class="rounded-circle p-3 mr-3" style="background: rgba(0, 168, 89, 0.15); color: #00A859;">
                                <i class="mdi mdi-account-group mdi-24px"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold d-block">Total Referrals</small>
                                <h3 class="font-weight-bold text-dark mb-0">{{ number_format($totalReferrals) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card d-flex align-items-center">
                            <div class="rounded-circle p-3 mr-3" style="background: rgba(40, 167, 69, 0.15); color: #28a745;">
                                <i class="mdi mdi-download mdi-24px"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold d-block">Installed</small>
                                <h3 class="font-weight-bold text-dark mb-0">{{ number_format($totalInstalled) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card d-flex align-items-center">
                            <div class="rounded-circle p-3 mr-3" style="background: rgba(23, 162, 184, 0.15); color: #17a2b8;">
                                <i class="mdi mdi-check-circle mdi-24px"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold d-block">Registered</small>
                                <h3 class="font-weight-bold text-dark mb-0">{{ number_format($totalRegistered) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card d-flex align-items-center">
                            <div class="rounded-circle p-3 mr-3" style="background: rgba(0, 123, 255, 0.15); color: #007bff;">
                                <i class="mdi mdi-shield-check mdi-24px"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold d-block">Verified</small>
                                <h3 class="font-weight-bold text-dark mb-0">{{ number_format($totalVerified) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="metric-card">
                            <small class="text-muted font-weight-bold d-block">Consumer Users</small>
                            <h4 class="font-weight-bold text-dark mb-0 mt-1">{{ number_format($consumerUsers) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="metric-card">
                            <small class="text-muted font-weight-bold d-block">Business Users</small>
                            <h4 class="font-weight-bold text-dark mb-0 mt-1">{{ number_format($businessUsers) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="metric-card">
                            <small class="text-muted font-weight-bold d-block">Total Referral Income</small>
                            <h4 class="font-weight-bold text-success mb-0 mt-1">₹{{ number_format($totalReferralIncome, 2) }}</h4>
                        </div>
                    </div>
                </div>

                <!-- Recent Earnings Log Table -->
                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="font-weight-bold text-dark mb-0">Recent Referral Earnings Log</h5>
                        <button class="btn btn-sm btn-outline-success font-weight-bold rounded-pill">Export Report</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead style="background-color: #F8F9FA;">
                                <tr>
                                    <th class="font-weight-bold border-0">Service / Activity</th>
                                    <th class="font-weight-bold border-0">User Type</th>
                                    <th class="font-weight-bold border-0">Reward Amount</th>
                                    <th class="font-weight-bold border-0">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($earningsLogs as $log)
                                <tr>
                                    <td class="font-weight-bold text-dark">{{ $log['activity'] }}</td>
                                    <td><span class="badge badge-soft-primary px-3 py-1 font-weight-bold">{{ $log['type'] }}</span></td>
                                    <td class="font-weight-bold text-success">{{ $log['amount'] }}</td>
                                    <td class="small text-muted">{{ $log['date'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function selectRewardMode(mode) {
        document.getElementById('reward_mode_input').value = mode;
        const buttons = document.querySelectorAll('.pill-switch button');
        buttons.forEach(btn => btn.classList.remove('active'));
        if (mode === 'percentage') {
            buttons[0].classList.add('active');
        } else {
            buttons[1].classList.add('active');
        }
    }
</script>
@endsection

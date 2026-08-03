@extends('layouts.app')

@section('content')
<style>
    .referral-admin-wrapper {
        padding: 20px;
        background-color: #f8fafc;
        min-height: 100vh;
    }
    .ref-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .ref-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .ref-title {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .ref-tabs-bar {
        display: flex;
        gap: 12px;
        border-bottom: 2px solid #e2e8f0;
        margin-top: 16px;
    }
    .ref-tab-item {
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .ref-tab-item:hover {
        color: #00A859;
    }
    .ref-tab-item.active {
        color: #00A859;
        border-bottom-color: #00A859;
        background-color: rgba(0, 168, 89, 0.04);
        border-radius: 6px 6px 0 0;
    }
    .ref-tab-content {
        padding: 24px;
    }
    .mode-btn-group {
        display: inline-flex;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 30px;
    }
    .mode-btn {
        border: none;
        padding: 8px 24px;
        border-radius: 25px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .mode-btn.active {
        background: #00A859;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(0, 168, 89, 0.3);
    }
    .btn-save-green {
        background-color: #00A859;
        color: #ffffff;
        font-weight: 700;
        font-size: 14px;
        padding: 10px 28px;
        border-radius: 25px;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 168, 89, 0.25);
        transition: all 0.2s;
    }
    .btn-save-green:hover {
        background-color: #008D4A;
        color: #ffffff;
    }
    .stat-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .switch-toggle {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .switch-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #00A859;
    }
    input:checked + .slider:before {
        transform: translateX(20px);
    }
</style>

<div class="page-wrapper referral-admin-wrapper">
    <div class="container-fluid">

        <!-- Notification Alert -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 mb-4" style="border-radius: 10px; background-color: #e6f4ea; color: #137333;">
                <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="ref-card">
            <!-- Header & Main Tabs Navigation -->
            <div class="ref-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="ref-title"><i class="fa fa-share-alt text-success mr-2"></i> Referral & Earn – Admin Panel</h1>
                        <p class="text-muted small mb-0 mt-1">Configure automated referral rewards, event rules, service percentages, and live performance reports.</p>
                    </div>
                </div>

                <div class="ref-tabs-bar">
                    <div class="ref-tab-item active" onclick="openReferralTab('reward-settings', this)">Reward Settings</div>
                    <div class="ref-tab-item" onclick="openReferralTab('event-rules', this)">Event Rules</div>
                    <div class="ref-tab-item" onclick="openReferralTab('service-rewards', this)">Service-wise Rewards</div>
                    <div class="ref-tab-item" onclick="openReferralTab('reports-logs', this)">Reports & Earnings Log</div>
                </div>
            </div>

            <!-- TAB 1: REWARD SETTINGS -->
            <div id="reward-settings" class="ref-tab-content" style="display: block;">
                <h4 class="font-weight-bold text-dark mb-4">Reward Mode Configuration</h4>
                
                <form action="{{ route('referral.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="reward_mode" id="reward_mode_field" value="{{ $rewardMode }}">

                    <div class="form-group mb-4">
                        <label class="d-block font-weight-bold text-muted small uppercase">Select Reward Type</label>
                        <div class="mode-btn-group">
                            <button type="button" id="btn_mode_percentage" class="mode-btn {{ $rewardMode === 'percentage' ? 'active' : '' }}" onclick="setRewardType('percentage')">Percentage</button>
                            <button type="button" id="btn_mode_flat" class="mode-btn {{ $rewardMode === 'flat' ? 'active' : '' }}" onclick="setRewardType('flat')">Flat Amount</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label class="font-weight-bold small text-muted">Percentage Value (%)</label>
                            <div class="input-group">
                                <input type="text" name="reward_value" class="form-control form-control-lg" value="{{ $rewardValue }}" placeholder="1.0">
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label class="font-weight-bold small text-muted">Minimum Reward (₹)</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                <input type="text" name="reward_min" class="form-control form-control-lg" value="{{ $rewardMin }}" placeholder="1">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn-save-green">Save Settings</button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: EVENT RULES -->
            <div id="event-rules" class="ref-tab-content" style="display: none;">
                <h4 class="font-weight-bold text-dark mb-3">Referral Event Triggers & Rules</h4>

                <form action="{{ route('referral.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="event_rules_submit" value="1">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="font-weight-bold">Event</th>
                                    <th class="font-weight-bold text-center">Enable</th>
                                    <th class="font-weight-bold">Reward Type</th>
                                    <th class="font-weight-bold">Value</th>
                                    <th class="font-weight-bold text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eventRules as $key => $rule)
                                <tr>
                                    <td class="font-weight-bold text-dark">{{ $rule['name'] }}</td>
                                    <td class="text-center">
                                        <label class="switch-toggle">
                                            <input type="checkbox" name="event_{{ $key }}_enable" value="1" {{ $rule['enable'] == '1' ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <span class="badge badge-light font-weight-bold px-3 py-2 text-dark">{{ ucfirst($rule['type']) }}</span>
                                    </td>
                                    <td>
                                        <input type="text" name="event_{{ $key }}_val" class="form-control form-control-sm font-weight-bold" style="width: 110px;" value="{{ $rule['val'] }}">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i> Edit</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn-save-green">Save Rules</button>
                    </div>
                </form>
            </div>

            <!-- TAB 3: SERVICE-WISE REWARDS -->
            <div id="service-rewards" class="ref-tab-content" style="display: none;">
                <h4 class="font-weight-bold text-dark mb-3">Service-wise Reward Configuration (Admin)</h4>

                <form action="{{ route('referral.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="service_rewards_submit" value="1">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="font-weight-bold">Service / Activity</th>
                                    <th class="font-weight-bold">Reward Type</th>
                                    <th class="font-weight-bold">Value</th>
                                    <th class="font-weight-bold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($consumerServices as $key => $srv)
                                <tr>
                                    <td class="font-weight-bold text-dark">{{ $srv['name'] }}</td>
                                    <td><span class="badge badge-light font-weight-bold px-3 py-2 text-dark">{{ $srv['type'] }}</span></td>
                                    <td>
                                        <input type="text" name="srv_{{ $key }}_val" class="form-control form-control-sm font-weight-bold" style="width: 110px;" value="{{ $srv['val'] }}">
                                    </td>
                                    <td class="text-center">
                                        <label class="switch-toggle">
                                            <input type="checkbox" name="srv_{{ $key }}_status" value="1" {{ $srv['status'] == '1' ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn-save-green">Save Settings</button>
                    </div>
                </form>
            </div>

            <!-- TAB 4: REPORTS & EARNINGS LOG -->
            <div id="reports-logs" class="ref-tab-content" style="display: none;">
                <!-- Summary Metrics (Matching Screenshots 1 & 3) -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <div class="stat-icon" style="background: rgba(0, 168, 89, 0.15); color: #00A859;"><i class="fa fa-users"></i></div>
                            <div>
                                <small class="text-muted font-weight-bold d-block">Total Referrals</small>
                                <h3 class="font-weight-bold text-dark mb-0">{{ number_format($totalReferrals) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <div class="stat-icon" style="background: rgba(40, 167, 69, 0.15); color: #28a745;"><i class="fa fa-download"></i></div>
                            <div>
                                <small class="text-muted font-weight-bold d-block">Installed</small>
                                <h3 class="font-weight-bold text-dark mb-0">{{ number_format($totalInstalled) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <div class="stat-icon" style="background: rgba(23, 162, 184, 0.15); color: #17a2b8;"><i class="fa fa-user-plus"></i></div>
                            <div>
                                <small class="text-muted font-weight-bold d-block">Registered</small>
                                <h3 class="font-weight-bold text-dark mb-0">{{ number_format($totalRegistered) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <div class="stat-icon" style="background: rgba(0, 123, 255, 0.15); color: #007bff;"><i class="fa fa-check-circle"></i></div>
                            <div>
                                <small class="text-muted font-weight-bold d-block">Verified</small>
                                <h3 class="font-weight-bold text-dark mb-0">{{ number_format($totalVerified) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stat-box">
                            <div>
                                <small class="text-muted font-weight-bold d-block">Consumer Users</small>
                                <h4 class="font-weight-bold text-dark mb-0 mt-1">{{ number_format($consumerUsers) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-box">
                            <div>
                                <small class="text-muted font-weight-bold d-block">Business Users</small>
                                <h4 class="font-weight-bold text-dark mb-0 mt-1">{{ number_format($businessUsers) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-box">
                            <div>
                                <small class="text-muted font-weight-bold d-block">Total Referral Income Paid</small>
                                <h4 class="font-weight-bold text-success mb-0 mt-1">₹{{ number_format($totalReferralIncome, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Earnings Table -->
                <div class="table-responsive mt-3">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="font-weight-bold">Activity / User</th>
                                <th class="font-weight-bold">User Type</th>
                                <th class="font-weight-bold">Reward Paid</th>
                                <th class="font-weight-bold">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($earningsLogs as $log)
                            <tr>
                                <td class="font-weight-bold text-dark">{{ $log['activity'] }}</td>
                                <td><span class="badge badge-info px-3 py-1 font-weight-bold">{{ $log['type'] }}</span></td>
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

<script>
    function openReferralTab(tabId, el) {
        // Hide all tab content divs
        var contents = document.getElementsByClassName('ref-tab-content');
        for (var i = 0; i < contents.length; i++) {
            contents[i].style.display = 'none';
        }

        // Remove active class from all tabs
        var tabItems = document.getElementsByClassName('ref-tab-item');
        for (var j = 0; j < tabItems.length; j++) {
            tabItems[j].classList.remove('active');
        }

        // Show selected tab content and highlight tab
        document.getElementById(tabId).style.display = 'block';
        el.classList.add('active');
    }

    function setRewardType(type) {
        document.getElementById('reward_mode_field').value = type;
        document.getElementById('btn_mode_percentage').classList.remove('active');
        document.getElementById('btn_mode_flat').classList.remove('active');
        if (type === 'percentage') {
            document.getElementById('btn_mode_percentage').classList.add('active');
        } else {
            document.getElementById('btn_mode_flat').classList.add('active');
        }
    }
</script>
@endsection

@extends('layouts.app')

@section('content')
<style>
    .ref-page-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: #F4F6F9;
        min-height: 100vh;
        padding: 24px;
    }
    .ref-card-main {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        padding: 24px;
        margin-bottom: 24px;
    }
    .ref-title-heading {
        font-size: 22px;
        font-weight: 700;
        color: #1E293B;
    }
    
    /* Top Navigation Main Tabs */
    .main-nav-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid #E2E8F0;
        margin-top: 16px;
        margin-bottom: 24px;
    }
    .main-tab-link {
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 600;
        color: #64748B;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .main-tab-link:hover {
        color: #00A859;
    }
    .main-tab-link.active {
        color: #00A859;
        border-bottom-color: #00A859;
        background: rgba(0, 168, 89, 0.05);
        border-radius: 8px 8px 0 0;
    }

    /* Sub Tabs Pill Style (Consumer vs Business) */
    .sub-pill-tabs {
        display: inline-flex;
        background: #F1F5F9;
        border-radius: 30px;
        padding: 4px;
        margin-bottom: 20px;
    }
    .sub-pill-btn {
        border: none;
        padding: 8px 28px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 700;
        color: #64748B;
        background: transparent;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .sub-pill-btn.active {
        background: #00A859;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 168, 89, 0.3);
    }

    /* Metric Boxes */
    .metric-grid-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .metric-icon-wrap {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .hero-stat-card {
        background: linear-gradient(135deg, #00A859 0%, #008D4A 100%);
        border-radius: 16px;
        color: #ffffff;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 15px rgba(0, 168, 89, 0.25);
    }
    
    /* Toggle Switch */
    .switch-ui {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .switch-ui input { opacity: 0; width: 0; height: 0; }
    .slider-round {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #CBD5E1;
        transition: .3s;
        border-radius: 24px;
    }
    .slider-round:before {
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
    input:checked + .slider-round { background-color: #00A859; }
    input:checked + .slider-round:before { transform: translateX(20px); }

    .btn-green-action {
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
    .btn-green-action:hover {
        background-color: #008D4A;
        color: #ffffff;
    }
</style>

<div class="page-wrapper ref-page-container">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 mb-4" style="border-radius: 12px; background-color: #E6F4EA; color: #137333;">
                <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="ref-card-main">
            <!-- Header Title -->
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="ref-title-heading"><i class="fa fa-gift text-success mr-2"></i> Referral & Earn Engine</h1>
                    <p class="text-muted small mb-0 mt-1">Multi-tier ecosystem referral management for Consumer App, Business App & Admin Panel.</p>
                </div>
            </div>

            <!-- Main Single-Page Navigation Tabs -->
            <div class="main-nav-tabs">
                <div class="main-tab-link active" onclick="switchMainTab('dashboard-view', this)">Referral Dashboard</div>
                <div class="main-tab-link" onclick="switchMainTab('reward-settings-view', this)">Reward Settings</div>
                <div class="main-tab-link" onclick="switchMainTab('event-rules-view', this)">Event Rules</div>
                <div class="main-tab-link" onclick="switchMainTab('service-rewards-view', this)">Service-wise Rewards</div>
                <div class="main-tab-link" onclick="switchMainTab('earnings-view', this)">Earnings & Breakdown</div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 1: REFERRAL DASHBOARD (Consumer & Business Sub-Tabs) -->
            <!-- ========================================== -->
            <div id="dashboard-view" class="main-tab-pane" style="display: block;">
                <!-- Consumer vs Business Sub Tab Pills -->
                <div class="sub-pill-tabs">
                    <button type="button" id="sub_consumer_btn" class="sub-pill-btn active" onclick="switchSubDashboard('consumer-section', this)">Consumer</button>
                    <button type="button" id="sub_business_btn" class="sub-pill-btn" onclick="switchSubDashboard('business-section', this)">Business</button>
                </div>

                <!-- SUB TAB CONTENT: CONSUMER SECTION (Screenshot 1) -->
                <div id="consumer-section" class="sub-dashboard-pane" style="display: block;">
                    <!-- Metrics Grid 1 -->
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <div class="metric-grid-card" style="background: rgba(0, 168, 89, 0.08); border-color: rgba(0, 168, 89, 0.2);">
                                <div class="metric-icon-wrap" style="background: #00A859; color: #fff;"><i class="fa fa-users"></i></div>
                                <div>
                                    <small class="text-muted font-weight-bold d-block">Total Referrals</small>
                                    <h2 class="font-weight-bold text-dark mb-0">125</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="metric-grid-card">
                                <div class="metric-icon-wrap" style="background: #E6F4EA; color: #00A859;"><i class="fa fa-arrow-down"></i></div>
                                <div>
                                    <small class="text-muted font-weight-bold d-block">Installed</small>
                                    <h3 class="font-weight-bold text-dark mb-0">110</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="metric-grid-card">
                                <div class="metric-icon-wrap" style="background: #E6F4EA; color: #00A859;"><i class="fa fa-check-circle"></i></div>
                                <div>
                                    <small class="text-muted font-weight-bold d-block">Registered</small>
                                    <h3 class="font-weight-bold text-dark mb-0">105</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="metric-grid-card">
                                <div class="metric-icon-wrap" style="background: #E6F4EA; color: #00A859;"><i class="fa fa-shield"></i></div>
                                <div>
                                    <small class="text-muted font-weight-bold d-block">Verified</small>
                                    <h3 class="font-weight-bold text-dark mb-0">97</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Grid 2 -->
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3">
                            <div class="metric-grid-card">
                                <div class="metric-icon-wrap" style="background: #F1F5F9; color: #475569;"><i class="fa fa-user"></i></div>
                                <div>
                                    <small class="text-muted font-weight-bold d-block">Consumer Users</small>
                                    <h4 class="font-weight-bold text-dark mb-0">75</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="metric-grid-card">
                                <div class="metric-icon-wrap" style="background: #F1F5F9; color: #475569;"><i class="fa fa-briefcase"></i></div>
                                <div>
                                    <small class="text-muted font-weight-bold d-block">Business Users</small>
                                    <h4 class="font-weight-bold text-dark mb-0">30</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="metric-grid-card">
                                <div class="metric-icon-wrap" style="background: #E6F4EA; color: #00A859;"><i class="fa fa-check-square"></i></div>
                                <div>
                                    <small class="text-muted font-weight-bold d-block">Active Users</small>
                                    <h4 class="font-weight-bold text-dark mb-0">80</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Grid 3 -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="metric-grid-card">
                                <div class="metric-icon-wrap" style="background: #EFF6FF; color: #3B82F6;"><i class="fa fa-wallet"></i></div>
                                <div>
                                    <small class="text-muted font-weight-bold d-block">Total Transactions</small>
                                    <h4 class="font-weight-bold text-dark mb-0">4,860</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="metric-grid-card">
                                <div class="metric-icon-wrap" style="background: #FEF3C7; color: #D97706;"><i class="fa fa-inr"></i></div>
                                <div>
                                    <small class="text-muted font-weight-bold d-block">Total Referral Income</small>
                                    <h4 class="font-weight-bold text-success mb-0">₹18,750</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="metric-grid-card">
                                <div class="metric-icon-wrap" style="background: #E6F4EA; color: #00A859;"><i class="fa fa-chart-line"></i></div>
                                <div>
                                    <small class="text-muted font-weight-bold d-block">Avg. Monthly Income</small>
                                    <h4 class="font-weight-bold text-dark mb-0">₹1,560</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Consumer Earnings List -->
                    <div class="card border-0 shadow-sm p-4" style="border-radius: 14px; background: #fafafa;">
                        <h5 class="font-weight-bold text-dark mb-3">Recent Consumer Earnings</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr class="text-muted small uppercase">
                                        <th>Activity</th>
                                        <th>Referred User</th>
                                        <th>Reward Earned</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="font-weight-bold text-dark">🚖 Cab Ride</td>
                                        <td>Rahul Sharma</td>
                                        <td class="font-weight-bold text-success">+₹8</td>
                                        <td class="text-muted small">30 Jul 2026</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-dark">🛒 Marketplace Purchase</td>
                                        <td>Priya Singh</td>
                                        <td class="font-weight-bold text-success">+₹25</td>
                                        <td class="text-muted small">30 Jul 2026</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-dark">💼 Premium Subscription</td>
                                        <td>Amit Kumar</td>
                                        <td class="font-weight-bold text-success">+₹50</td>
                                        <td class="text-muted small">31 Jul 2026</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-dark">🏥 Medicash Health Card</td>
                                        <td>Neha Verma</td>
                                        <td class="font-weight-bold text-success">+₹31</td>
                                        <td class="text-muted small">31 Jul 2026</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SUB TAB CONTENT: BUSINESS SECTION (Screenshots 3 & 4) -->
                <div id="business-section" class="sub-dashboard-pane" style="display: none;">
                    <!-- Business Top Summary Card -->
                    <div class="hero-stat-card d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 bg-white text-success rounded-circle" style="font-size: 24px;"><i class="fa fa-briefcase"></i></div>
                            <div>
                                <h4 class="mb-0 font-weight-bold">Business Referrals: 30</h4>
                                <p class="mb-0 small text-white-50">Active onboarded merchants & driver partners</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <small class="text-white-50 font-weight-bold uppercase d-block">Total Business Earnings</small>
                            <h2 class="font-weight-bold mb-0 text-white">₹12,540</h2>
                        </div>
                    </div>

                    <!-- Business Stats Grid (Screenshot 3) -->
                    <div class="row mb-4">
                        <div class="col-md-2 col-6 mb-3">
                            <div class="metric-grid-card text-center flex-column">
                                <span class="badge badge-soft-success p-2 rounded-circle"><i class="fa fa-download"></i></span>
                                <h3 class="font-weight-bold text-dark mb-0 mt-2">25</h3>
                                <small class="text-muted">App Installed</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="metric-grid-card text-center flex-column">
                                <span class="badge badge-soft-primary p-2 rounded-circle"><i class="fa fa-user-plus"></i></span>
                                <h3 class="font-weight-bold text-dark mb-0 mt-2">22</h3>
                                <small class="text-muted">Registered</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="metric-grid-card text-center flex-column">
                                <span class="badge badge-soft-info p-2 rounded-circle"><i class="fa fa-shield"></i></span>
                                <h3 class="font-weight-bold text-dark mb-0 mt-2">18</h3>
                                <small class="text-muted">Verified</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="metric-grid-card text-center flex-column">
                                <span class="badge badge-soft-success p-2 rounded-circle"><i class="fa fa-check-circle"></i></span>
                                <h3 class="font-weight-bold text-dark mb-0 mt-2">15</h3>
                                <small class="text-muted">Active Business</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="metric-grid-card text-center flex-column">
                                <span class="badge badge-soft-warning p-2 rounded-circle"><i class="fa fa-wrench"></i></span>
                                <h3 class="font-weight-bold text-dark mb-0 mt-2">9</h3>
                                <small class="text-muted">Active Services</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="metric-grid-card text-center flex-column">
                                <span class="badge badge-soft-dark p-2 rounded-circle"><i class="fa fa-shopping-cart"></i></span>
                                <h3 class="font-weight-bold text-dark mb-0 mt-2">12</h3>
                                <small class="text-muted">Total Orders</small>
                            </div>
                        </div>
                    </div>

                    <!-- Business Service-Wise Breakdown (Screenshot 4 - Left) -->
                    <div class="row mb-4">
                        <div class="col-md-7 mb-3">
                            <div class="card border-0 shadow-sm p-4" style="border-radius: 14px;">
                                <h5 class="font-weight-bold text-dark mb-3">Business Service-Wise Earnings</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Service</th>
                                                <th>Count</th>
                                                <th>Earnings</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>🍔 Food Delivery</td><td>8</td><td class="font-weight-bold text-success">₹1,200</td></tr>
                                            <tr><td>🔧 Home Service</td><td>5</td><td class="font-weight-bold text-success">₹1,050</td></tr>
                                            <tr><td>🚖 Cab Ride</td><td>12</td><td class="font-weight-bold text-success">₹1,800</td></tr>
                                            <tr><td>🛍️ Marketplace Sale</td><td>4</td><td class="font-weight-bold text-success">₹900</td></tr>
                                            <tr><td>📦 Parcel Delivery</td><td>6</td><td class="font-weight-bold text-success">₹780</td></tr>
                                            <tr><td>🏨 Hotel Booking</td><td>3</td><td class="font-weight-bold text-success">₹450</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Business Users - Recent (Screenshot 3 - Right) -->
                        <div class="col-md-5 mb-3">
                            <div class="card border-0 shadow-sm p-4" style="border-radius: 14px;">
                                <h5 class="font-weight-bold text-dark mb-3">Business Users – Recent</h5>
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="font-weight-bold mb-0">Amit Sharma</h6>
                                        <small class="text-muted">Food Partner</small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-success px-2 py-1 mb-1">Active</span>
                                        <h6 class="font-weight-bold text-dark mb-0">₹1,200</h6>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="font-weight-bold mb-0">Neha Verma</h6>
                                        <small class="text-muted">Home Service Provider</small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-success px-2 py-1 mb-1">Active</span>
                                        <h6 class="font-weight-bold text-dark mb-0">₹950</h6>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <h6 class="font-weight-bold mb-0">Rohit Singh</h6>
                                        <small class="text-muted">Cab Driver Partner</small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-success px-2 py-1 mb-1">Active</span>
                                        <h6 class="font-weight-bold text-dark mb-0">₹1,450</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: REWARD SETTINGS (Screenshot 2 - Left) -->
            <!-- ========================================== -->
            <div id="reward-settings-view" class="main-tab-pane" style="display: none;">
                <h4 class="font-weight-bold text-dark mb-4">Reward Mode Configuration</h4>
                <form action="{{ route('referral.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="reward_mode" id="reward_mode_input_val" value="{{ $rewardMode }}">

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-muted small uppercase d-block">Reward Mode</label>
                        <div class="sub-pill-tabs">
                            <button type="button" id="mode_perc_btn" class="sub-pill-btn {{ $rewardMode === 'percentage' ? 'active' : '' }}" onclick="selectRewardModeVal('percentage')">Percentage</button>
                            <button type="button" id="mode_flat_btn" class="sub-pill-btn {{ $rewardMode === 'flat' ? 'active' : '' }}" onclick="selectRewardModeVal('flat')">Flat Amount</button>
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
                            <label class="font-weight-bold small text-muted">Minimum Reward Amount (₹)</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                <input type="text" name="reward_min" class="form-control form-control-lg" value="{{ $rewardMin }}" placeholder="1">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-green-action">Save Settings</button>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- TAB 3: EVENT RULES (Screenshot 2 - Center) -->
            <!-- ========================================== -->
            <div id="event-rules-view" class="main-tab-pane" style="display: none;">
                <h4 class="font-weight-bold text-dark mb-3">Referral Event Rules & Triggers</h4>
                <form action="{{ route('referral.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="event_rules_submit" value="1">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Event Trigger</th>
                                    <th class="text-center">Enable</th>
                                    <th>Reward Type</th>
                                    <th>Value</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eventRules as $key => $rule)
                                <tr>
                                    <td class="font-weight-bold text-dark">{{ $rule['name'] }}</td>
                                    <td class="text-center">
                                        <label class="switch-ui">
                                            <input type="checkbox" name="event_{{ $key }}_enable" value="1" {{ $rule['enable'] == '1' ? 'checked' : '' }}>
                                            <span class="slider-round"></span>
                                        </label>
                                    </td>
                                    <td><span class="badge badge-light font-weight-bold px-3 py-2 text-dark">{{ ucfirst($rule['type']) }}</span></td>
                                    <td><input type="text" name="event_{{ $key }}_val" class="form-control form-control-sm font-weight-bold" style="width: 110px;" value="{{ $rule['val'] }}"></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary"><i class="fa fa-pencil"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn-green-action">Save Rules</button>
                    </div>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: SERVICE-WISE REWARDS (Screenshot 2 - Right & Screenshot 4 - Right) -->
            <!-- ========================================== -->
            <div id="service-rewards-view" class="main-tab-pane" style="display: none;">
                <h4 class="font-weight-bold text-dark mb-3">Service-wise Reward Configuration (Admin)</h4>
                
                <form action="{{ route('referral.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="service_rewards_submit" value="1">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Service / Activity</th>
                                    <th>Reward Type</th>
                                    <th>Reward Value</th>
                                    <th>Max Limit</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($consumerServices as $key => $srv)
                                <tr>
                                    <td class="font-weight-bold text-dark">{{ $srv['name'] }}</td>
                                    <td><span class="badge badge-light font-weight-bold px-3 py-2 text-dark">{{ $srv['type'] }}</span></td>
                                    <td><input type="text" name="srv_{{ $key }}_val" class="form-control form-control-sm font-weight-bold" style="width: 100px;" value="{{ $srv['val'] }}"></td>
                                    <td><span class="font-weight-bold text-dark">₹2,000</span></td>
                                    <td class="text-center">
                                        <label class="switch-ui">
                                            <input type="checkbox" name="srv_{{ $key }}_status" value="1" {{ $srv['status'] == '1' ? 'checked' : '' }}>
                                            <span class="slider-round"></span>
                                        </label>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn-green-action">Save Configuration</button>
                    </div>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- TAB 5: EARNINGS & BREAKDOWN (Screenshot 4 - Center) -->
            <!-- ========================================== -->
            <div id="earnings-view" class="main-tab-pane" style="display: none;">
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="metric-grid-card">
                            <div class="metric-icon-wrap" style="background: #E6F4EA; color: #00A859;"><i class="fa fa-bolt"></i></div>
                            <div>
                                <small class="text-muted font-weight-bold d-block">One-Time Earnings</small>
                                <h2 class="font-weight-bold text-dark mb-0">₹8,750</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="metric-grid-card">
                            <div class="metric-icon-wrap" style="background: #EFF6FF; color: #3B82F6;"><i class="fa fa-refresh"></i></div>
                            <div>
                                <small class="text-muted font-weight-bold d-block">Multiple Time Earnings</small>
                                <h2 class="font-weight-bold text-dark mb-0">₹3,420</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4" style="border-radius: 14px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="font-weight-bold text-dark mb-0">Multiple Time Earnings – Service Wise</h5>
                        <select class="form-control form-control-sm font-weight-bold" style="width: 150px; border-radius: 8px;">
                            <option>This Month</option>
                            <option>Today</option>
                            <option>Last Month</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Service / Activity</th>
                                    <th>Times Used</th>
                                    <th>Earnings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>🚖 Cab Ride</td><td class="font-weight-bold">12</td><td class="font-weight-bold text-success">₹96</td></tr>
                                <tr><td>🍔 Food Delivery</td><td class="font-weight-bold">8</td><td class="font-weight-bold text-success">₹64</td></tr>
                                <tr><td>🛒 Marketplace Purchase</td><td class="font-weight-bold">15</td><td class="font-weight-bold text-success">₹120</td></tr>
                                <tr><td>💳 Wallet Transfer</td><td class="font-weight-bold">35</td><td class="font-weight-bold text-success">₹70</td></tr>
                                <tr><td>🏨 Hotel Booking</td><td class="font-weight-bold">2</td><td class="font-weight-bold text-success">₹16</td></tr>
                                <tr><td>🏥 Healthcare Card</td><td class="font-weight-bold">1</td><td class="font-weight-bold text-success">₹10</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function switchMainTab(paneId, el) {
        var panes = document.getElementsByClassName('main-tab-pane');
        for (var i = 0; i < panes.length; i++) {
            panes[i].style.display = 'none';
        }
        var links = document.getElementsByClassName('main-tab-link');
        for (var j = 0; j < links.length; j++) {
            links[j].classList.remove('active');
        }
        document.getElementById(paneId).style.display = 'block';
        el.classList.add('active');
    }

    function switchSubDashboard(subPaneId, el) {
        var subPanes = document.getElementsByClassName('sub-dashboard-pane');
        for (var i = 0; i < subPanes.length; i++) {
            subPanes[i].style.display = 'none';
        }
        document.getElementById('sub_consumer_btn').classList.remove('active');
        document.getElementById('sub_business_btn').classList.remove('active');
        
        document.getElementById(subPaneId).style.display = 'block';
        el.classList.add('active');
    }

    function selectRewardModeVal(type) {
        document.getElementById('reward_mode_input_val').value = type;
        document.getElementById('mode_perc_btn').classList.remove('active');
        document.getElementById('mode_flat_btn').classList.remove('active');
        if (type === 'percentage') {
            document.getElementById('mode_perc_btn').classList.add('active');
        } else {
            document.getElementById('mode_flat_btn').classList.add('active');
        }
    }
</script>
@endsection

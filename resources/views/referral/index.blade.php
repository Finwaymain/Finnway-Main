@extends('layouts.app')

@section('content')
<style>
    .admin-theme-container {
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .admin-card {
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .admin-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .admin-nav-tabs {
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        gap: 6px;
    }
    .admin-tab-item {
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
    }
    .admin-tab-item:hover, .admin-tab-item.active {
        color: #4f46e5;
        border-bottom-color: #4f46e5;
        background: rgba(79, 70, 229, 0.05);
        border-radius: 6px 6px 0 0;
    }
    .admin-pill-group {
        display: inline-flex;
        background: #f1f5f9;
        border-radius: 25px;
        padding: 3px;
    }
    .admin-pill-btn {
        border: none;
        padding: 6px 20px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .admin-pill-btn.active {
        background: #4f46e5;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
    }
    .admin-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .admin-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .srv-cat-scroll-nav {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 6px;
        margin-bottom: 16px;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .srv-cat-scroll-nav::-webkit-scrollbar {
        display: none;
    }
    .srv-cat-item {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        padding: 7px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }
    .srv-cat-item:hover, .srv-cat-item.active {
        border-color: #4f46e5;
        background: #4f46e5;
        color: #ffffff;
    }
    .switch-indigo {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 22px;
    }
    .switch-indigo input { opacity: 0; width: 0; height: 0; }
    .slider-indigo {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 22px;
    }
    .slider-indigo:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    input:checked + .slider-indigo { background-color: #4f46e5; }
    input:checked + .slider-indigo:before { transform: translateX(18px); }

    .badge-high-contrast {
        background-color: #e0e7ff !important;
        color: #3730a3 !important;
        font-weight: 700 !important;
        font-size: 12px !important;
    }
    .input-addon-text {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: 700 !important;
    }

    /* Green Switch Styling matching screenshot */
    .switch-green {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }
    .switch-green input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider-green {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 24px;
    }
    .slider-green:before {
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
    .switch-green input:checked + .slider-green {
        background-color: #10b981;
    }
    .switch-green input:checked + .slider-green:before {
        transform: translateX(22px);
    }
</style>

<div class="page-wrapper admin-theme-container">
    <div class="container-fluid pt-3">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 mb-4" style="border-radius: 8px;">
                <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="admin-card">
            <!-- Header Title -->
            <div class="admin-card-header">
                <h3 class="font-weight-bold text-dark mb-1"><i class="fa fa-share-alt text-primary mr-2"></i> Referral & Earn Engine</h3>
                <p class="text-muted small mb-0">Manage system-wide referral rates, service percentages, and live database logs.</p>

                <!-- Navigation Tabs -->
                <div class="admin-nav-tabs mt-3">
                    <div class="admin-tab-item active" onclick="switchAdminTab('reward-settings-pane', this)">Reward Settings</div>
                    <div class="admin-tab-item" onclick="switchAdminTab('service-rewards-pane', this)">Service-wise Rewards</div>
                    <div class="admin-tab-item" onclick="switchAdminTab('event-rules-pane', this)">Referral Event Rules</div>
                    <div class="admin-tab-item" onclick="switchAdminTab('earnings-pane', this)">Earnings & Breakdown</div>
                </div>
            </div>

            <div class="card-body p-4">

                <!-- ========================================== -->
                <!-- TAB 1: REWARD SETTINGS -->
                <!-- ========================================== -->
                <div id="reward-settings-pane" class="admin-tab-content" style="display: block;">
                    <h4 class="font-weight-bold text-dark mb-4">Reward Engine Settings</h4>
                    <form action="{{ route('referral.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="reward_mode" id="mode_input_field" value="{{ $rewardMode }}">

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-muted small uppercase d-block">Reward Mode</label>
                            <div class="admin-pill-group">
                                <button type="button" id="pill_perc" class="admin-pill-btn {{ $rewardMode === 'percentage' ? 'active' : '' }}" onclick="selectRewardMode('percentage')">Percentage (%)</button>
                                <button type="button" id="pill_flat" class="admin-pill-btn {{ $rewardMode === 'flat' ? 'active' : '' }}" onclick="selectRewardMode('flat')">Flat Amount (₹)</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label id="reward_val_label" class="font-weight-bold small text-muted">
                                    {{ $rewardMode === 'flat' ? 'Flat Value (₹)' : 'Percentage Value (%)' }}
                                </label>
                                <div class="input-group">
                                    <div id="reward_val_prepend" class="input-group-prepend {{ $rewardMode === 'flat' ? '' : 'd-none' }}">
                                        <span class="input-group-text input-addon-text">₹</span>
                                    </div>
                                    <input type="text" name="reward_value" id="reward_value_input" class="form-control font-weight-bold text-dark" value="{{ $rewardValue }}" placeholder="{{ $rewardMode === 'flat' ? '50' : '1.0' }}" style="color: #0f172a !important;">
                                    <div id="reward_val_append" class="input-group-append {{ $rewardMode === 'flat' ? 'd-none' : '' }}">
                                        <span class="input-group-text input-addon-text">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label class="font-weight-bold small text-muted">Minimum Reward Amount (₹)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text input-addon-text">₹</span></div>
                                    <input type="text" name="reward_min" class="form-control font-weight-bold text-dark" value="{{ $rewardMin }}" placeholder="1" style="color: #0f172a !important;">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary font-weight-bold px-4">Save Engine Settings</button>
                    </form>
                </div>

                <!-- ========================================== -->
                <!-- TAB 3: SERVICE-WISE REWARDS -->
                <!-- ========================================== -->
                <div id="service-rewards-pane" class="admin-tab-content" style="display: none;">
                    <h4 class="font-weight-bold text-dark mb-3">Service-wise Reward Configuration</h4>
                    <p class="text-muted small mb-4">Select a parent category to configure reward percentages and limits for its sub-services.</p>

                    <form action="{{ route('referral.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="service_rewards_submit" value="1">

                        @if($categoriesWithSubs->count() > 0)
                            <div class="srv-cat-scroll-nav">
                                @foreach($categoriesWithSubs as $index => $parentCat)
                                    <div class="srv-cat-item {{ $index === 0 ? 'active' : '' }}" onclick="filterCategorySection('srv_sec_{{ $parentCat->id }}', this)">
                                        {{ $parentCat->libelle }}
                                    </div>
                                @endforeach
                            </div>

                            @foreach($categoriesWithSubs as $index => $parentCat)
                                <div class="card border mb-3 srv-group-pane srv_sec_{{ $parentCat->id }}" style="{{ $index === 0 ? 'display: block;' : 'display: none;' }}">
                                    <div class="card-header bg-light font-weight-bold text-dark"><i class="fa fa-folder-open text-primary mr-2"></i> {{ $parentCat->libelle }}</div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-white">
                                                <tr class="small text-muted"><th>Sub-Service</th><th>Reward Mode</th><th>Value</th><th>Max Limit</th><th class="text-center">Status</th></tr>
                                            </thead>
                                            <tbody>
                                                @forelse($parentCat->subcategories as $subCat)
                                                <tr>
                                                    <td class="font-weight-bold text-dark">{{ $subCat->libelle }}</td>
                                                    <td><span class="badge badge-high-contrast px-3 py-1">Percentage</span></td>
                                                    <td><input type="text" name="srv_cat_{{ $subCat->id }}_val" class="form-control form-control-sm font-weight-bold text-dark" style="width: 100px; color: #0f172a !important;" value="2%"></td>
                                                    <td class="font-weight-bold text-dark">₹2,000</td>
                                                    <td class="text-center">
                                                        <label class="switch-indigo"><input type="checkbox" name="srv_cat_{{ $subCat->id }}_status" value="1" checked><span class="slider-indigo"></span></label>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-muted small py-3 text-center">No sub-services registered under {{ $parentCat->libelle }}.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach

                        @else
                            <div class="srv-cat-scroll-nav">
                                @foreach(array_keys($defaultCategories) as $index => $catName)
                                    <div class="srv-cat-item {{ $index === 0 ? 'active' : '' }}" onclick="filterCategorySection('srv_sec_{{ Str::slug($catName) }}', this)">
                                        {{ $catName }}
                                    </div>
                                @endforeach
                            </div>

                            @foreach($defaultCategories as $catName => $content)
                                <div class="card border mb-3 srv-group-pane srv_sec_{{ Str::slug($catName) }}" style="{{ $loop->first ? 'display: block;' : 'display: none;' }}">
                                    <div class="card-header bg-light font-weight-bold text-dark"><i class="fa fa-folder-open text-primary mr-2"></i> {{ $catName }}</div>
                                    
                                    @if($catName === 'Home Services')
                                        @foreach($content as $parentGroup => $subs)
                                        <div class="p-3 border-bottom">
                                            <h6 class="font-weight-bold text-primary mb-2"><i class="fa fa-wrench mr-1"></i> {{ $parentGroup }}</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover align-middle mb-0">
                                                    <thead class="bg-light">
                                                        <tr class="small text-muted"><th>Sub-Service</th><th>Reward Mode</th><th>Value</th><th>Max Limit</th><th class="text-center">Status</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($subs as $subItem)
                                                        <tr>
                                                            <td class="font-weight-bold text-dark pl-3">{{ $subItem }}</td>
                                                            <td><span class="badge badge-high-contrast px-2 py-1">Percentage</span></td>
                                                            <td><input type="text" name="srv_{{ Str::slug($subItem) }}_val" class="form-control form-control-sm font-weight-bold text-dark" style="width: 90px; color: #0f172a !important;" value="2%"></td>
                                                            <td class="font-weight-bold text-dark">₹2,000</td>
                                                            <td class="text-center">
                                                                <label class="switch-indigo"><input type="checkbox" name="srv_{{ Str::slug($subItem) }}_status" value="1" checked><span class="slider-indigo"></span></label>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="bg-white">
                                                    <tr class="small text-muted"><th>Service</th><th>Reward Mode</th><th>Reward Value</th><th>Max Limit</th><th class="text-center">Status</th></tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($content as $subItem)
                                                    <tr>
                                                        <td class="font-weight-bold text-dark">{{ $subItem }}</td>
                                                        <td><span class="badge badge-high-contrast px-3 py-1">Percentage</span></td>
                                                        <td><input type="text" name="srv_{{ Str::slug($subItem) }}_val" class="form-control form-control-sm font-weight-bold text-dark" style="width: 100px; color: #0f172a !important;" value="2%"></td>
                                                        <td class="font-weight-bold text-dark">₹2,000</td>
                                                        <td class="text-center">
                                                            <label class="switch-indigo"><input type="checkbox" name="srv_{{ Str::slug($subItem) }}_status" value="1" checked><span class="slider-indigo"></span></label>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        <button type="submit" class="btn btn-primary font-weight-bold px-4 mt-3"><i class="fa fa-save mr-1"></i> Save Configuration</button>
                    </form>
                </div>

                <!-- ========================================== -->
                <!-- TAB 3: REFERRAL EVENT RULES -->
                <!-- ========================================== -->
                <div id="event-rules-pane" class="admin-tab-content" style="display: none;">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; overflow: hidden; background: #ffffff;">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                            <h4 class="font-weight-bold" style="color: #1e1b4b; font-size: 1.35rem;">Referral Event Rules</h4>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('referral.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="event_rules_submit" value="1">
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle mb-0" style="border-collapse: separate; border-spacing: 0 10px;">
                                        <thead>
                                            <tr style="background-color: #f1f5f9; color: #334155; font-size: 0.88rem; font-weight: 700; border-radius: 10px;">
                                                <th style="padding: 12px 18px; border-top-left-radius: 10px; border-bottom-left-radius: 10px;">Event</th>
                                                <th style="padding: 12px 18px; text-align: center;">Enable</th>
                                                <th style="padding: 12px 18px; text-align: center;">Reward Type</th>
                                                <th style="padding: 12px 18px; text-align: center;">Value</th>
                                                <th style="padding: 12px 18px; text-align: center; border-top-right-radius: 10px; border-bottom-right-radius: 10px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($eventRules as $evtKey => $evtRule)
                                            <tr style="background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-radius: 8px;">
                                                <td style="padding: 14px 18px; font-weight: 600; color: #334155; font-size: 0.95rem;">
                                                    {{ $evtRule['name'] }}
                                                </td>
                                                <td style="padding: 14px 18px; text-align: center;">
                                                    <label class="switch-green mb-0">
                                                        <input type="checkbox" name="event_{{ $evtKey }}_enable" value="1" {{ $evtRule['enable'] ? 'checked' : '' }}>
                                                        <span class="slider-green"></span>
                                                    </label>
                                                </td>
                                                <td style="padding: 14px 18px; text-align: center;">
                                                    <select name="event_{{ $evtKey }}_type" class="form-control form-control-sm text-center font-weight-bold mx-auto" style="width: 140px; border-radius: 20px; border: 1px solid #cbd5e1; color: #4338ca; background: #f8fafc;" onchange="updateEventValueSymbol('{{ $evtKey }}', this.value)">
                                                        <option value="percentage" {{ $evtRule['type'] === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                        <option value="flat" {{ $evtRule['type'] === 'flat' ? 'selected' : '' }}>Flat</option>
                                                    </select>
                                                </td>
                                                <td style="padding: 14px 18px; text-align: center;">
                                                    <div class="d-inline-flex align-items-center justify-content-center" style="width: 110px;">
                                                        <span id="symbol_prefix_{{ $evtKey }}" class="font-weight-bold mr-1" style="color: #1e293b; {{ $evtRule['type'] === 'flat' ? '' : 'display: none;' }}">₹</span>
                                                        <input type="text" name="event_{{ $evtKey }}_value" id="evt_val_input_{{ $evtKey }}" class="form-control form-control-sm text-center font-weight-bold text-dark" style="width: 65px; border-radius: 6px; border: 1px solid #e2e8f0; color: #0f172a !important;" value="{{ $evtRule['value'] }}">
                                                        <span id="symbol_suffix_{{ $evtKey }}" class="font-weight-bold ml-1" style="color: #1e293b; {{ $evtRule['type'] === 'percentage' ? '' : 'display: none;' }}">%</span>
                                                    </div>
                                                </td>
                                                <td style="padding: 14px 18px; text-align: center;">
                                                    <button type="button" class="btn btn-sm btn-light-edit" onclick="document.getElementById('evt_val_input_{{ $evtKey }}').focus()" title="Edit Rule" style="width: 34px; height: 34px; border-radius: 50%; background: #e0e7ff; color: #4338ca; border: none;">
                                                        <i class="fa fa-pencil-alt" style="font-size: 0.82rem;"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-save-rules font-weight-bold px-4 py-2" style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border-radius: 30px; border: none; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                                        Save Rules
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- TAB 4: EARNINGS & BREAKDOWN -->
                <!-- ========================================== -->
                <div id="earnings-pane" class="admin-tab-content" style="display: none;">
                    <h4 class="font-weight-bold text-dark mb-3"><i class="fa fa-chart-line text-primary mr-2"></i> Referral Dashboard – Earnings</h4>

                    <div class="admin-pill-group mb-4">
                        <button type="button" id="sub_earn_onetime" class="admin-pill-btn active" onclick="switchEarningsTab('onetime_earn_pane', this)">One-Time Earnings</button>
                        <button type="button" id="sub_earn_multiple" class="admin-pill-btn" onclick="switchEarningsTab('multiple_earn_pane', this)">Multiple Time Earnings</button>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="admin-stat-card">
                                <div class="admin-stat-icon"><i class="fa fa-bolt"></i></div>
                                <div>
                                    <h3 class="font-weight-bold text-dark mb-0">₹{{ number_format($oneTimeTotal, 2) }}</h3>
                                    <small class="text-muted font-weight-bold">One-Time Earnings</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="admin-stat-card">
                                <div class="admin-stat-icon"><i class="fa fa-refresh"></i></div>
                                <div>
                                    <h3 class="font-weight-bold text-dark mb-0">₹{{ number_format($multipleTimeTotal, 2) }}</h3>
                                    <small class="text-muted font-weight-bold">Multiple Time Earnings</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="onetime_earn_pane" class="earn-pane" style="display: block;">
                        <div class="card border-0 shadow-sm p-3" style="border-radius: 8px;">
                            <h5 class="font-weight-bold text-dark mb-3">One-Time Earnings Breakdown</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr class="small text-muted">
                                            <th>Activity</th>
                                            <th class="text-center">Count / Triggers</th>
                                            <th class="text-right">Earnings</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($oneTimeEarningsTable as $row)
                                        <tr>
                                            <td class="font-weight-bold text-dark">{{ $row['activity'] }}</td>
                                            <td class="text-center font-weight-bold">{{ $row['count'] }}</td>
                                            <td class="text-right font-weight-bold text-primary">{{ $row['earnings'] }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="table-active">
                                            <td class="font-weight-bold">Total One-Time Earnings</td>
                                            <td class="text-center font-weight-bold">{{ $totalRegistered }}</td>
                                            <td class="text-right font-weight-bold text-primary">₹{{ number_format($oneTimeTotal, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="multiple_earn_pane" class="earn-pane" style="display: none;">
                        <div class="card border-0 shadow-sm p-3" style="border-radius: 8px;">
                            <h5 class="font-weight-bold text-dark mb-3">Multiple Time Earnings – Service Wise</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr class="small text-muted">
                                            <th>Service / Activity</th>
                                            <th class="text-center">Times Used</th>
                                            <th class="text-right">Earnings</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($multipleTimeEarningsTable as $row)
                                        <tr>
                                            <td class="font-weight-bold text-dark">{{ $row['activity'] }}</td>
                                            <td class="text-center font-weight-bold">{{ $row['times_used'] }}</td>
                                            <td class="text-right font-weight-bold text-primary">{{ $row['earnings'] }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="table-active">
                                            <td class="font-weight-bold">Total Multiple Time Earnings</td>
                                            <td class="text-center font-weight-bold">{{ $totalTransactions }}</td>
                                            <td class="text-right font-weight-bold text-primary">₹{{ number_format($multipleTimeTotal, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function switchAdminTab(paneId, el) {
        var panes = document.getElementsByClassName('admin-tab-content');
        for (var i = 0; i < panes.length; i++) {
            panes[i].style.display = 'none';
        }
        var links = document.getElementsByClassName('admin-tab-item');
        for (var j = 0; j < links.length; j++) {
            links[j].classList.remove('active');
        }
        document.getElementById(paneId).style.display = 'block';
        el.classList.add('active');
    }

    function switchSubDashboard(paneId, el) {
        var panes = document.getElementsByClassName('sub-pane');
        for (var i = 0; i < panes.length; i++) {
            panes[i].style.display = 'none';
        }
        document.getElementById('sub_cons_btn').classList.remove('active');
        document.getElementById('sub_biz_btn').classList.remove('active');

        document.getElementById(paneId).style.display = 'block';
        el.classList.add('active');
    }

    function switchEarningsTab(paneId, el) {
        var panes = document.getElementsByClassName('earn-pane');
        for (var i = 0; i < panes.length; i++) {
            panes[i].style.display = 'none';
        }
        document.getElementById('sub_earn_onetime').classList.remove('active');
        document.getElementById('sub_earn_multiple').classList.remove('active');

        document.getElementById(paneId).style.display = 'block';
        el.classList.add('active');
    }

    function filterCategorySection(catClass, el) {
        var items = document.getElementsByClassName('srv-cat-item');
        for (var i = 0; i < items.length; i++) {
            items[i].classList.remove('active');
        }
        if (el) el.classList.add('active');

        var groups = document.getElementsByClassName('srv-group-pane');
        for (var j = 0; j < groups.length; j++) {
            if (groups[j].classList.contains(catClass)) {
                groups[j].style.display = 'block';
            } else {
                groups[j].style.display = 'none';
            }
        }
    }

    function selectRewardMode(type) {
        document.getElementById('mode_input_field').value = type;
        document.getElementById('pill_perc').classList.remove('active');
        document.getElementById('pill_flat').classList.remove('active');

        var label = document.getElementById('reward_val_label');
        var appendSymbol = document.getElementById('reward_val_append');
        var prependSymbol = document.getElementById('reward_val_prepend');
        var input = document.getElementById('reward_value_input');

        if (type === 'percentage') {
            document.getElementById('pill_perc').classList.add('active');
            if (label) label.innerText = 'Percentage Value (%)';
            if (appendSymbol) appendSymbol.classList.remove('d-none');
            if (prependSymbol) prependSymbol.classList.add('d-none');
            if (input) input.placeholder = '1.0';
        } else {
            document.getElementById('pill_flat').classList.add('active');
            if (label) label.innerText = 'Flat Value (₹)';
            if (appendSymbol) appendSymbol.classList.add('d-none');
            if (prependSymbol) prependSymbol.classList.remove('d-none');
            if (input) input.placeholder = '50';
        }
    }

    function updateEventValueSymbol(evtKey, type) {
        var prefix = document.getElementById('symbol_prefix_' + evtKey);
        var suffix = document.getElementById('symbol_suffix_' + evtKey);
        if (type === 'flat') {
            if (prefix) prefix.style.display = 'inline';
            if (suffix) suffix.style.display = 'none';
        } else {
            if (prefix) prefix.style.display = 'none';
            if (suffix) suffix.style.display = 'inline';
        }
    }
</script>
@endsection

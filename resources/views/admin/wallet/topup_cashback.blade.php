@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="container-fluid pt-3">
        <!-- Breadcrumbs & Actions -->
        <div class="row page-titles mb-3">
            <div class="col-md-7 align-self-center">
                <h3 class="text-themecolor font-weight-bold mb-1">
                    <i class="mdi mdi-cash-multiple text-success mr-1"></i>Wallet Top-Up Cashback Engine
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('walletstransaction') }}">Wallet & Financials</a></li>
                    <li class="breadcrumb-item active">Top-Up Cashback</li>
                </ol>
            </div>
            <div class="col-md-5 align-self-center text-right">
                <a href="{{ route('admin.wallet.topup-cashback.create') }}" class="btn btn-success shadow-sm font-weight-bold px-4">
                    <i class="mdi mdi-plus-circle mr-1"></i>Create Cashback Rule
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="mdi mdi-check-circle mr-1"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Stat Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card p-3 border-0 shadow-sm" style="border-radius:12px; background:linear-gradient(135deg, #10B981, #059669); color:#fff;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase font-weight-bold" style="font-size:11px; opacity:0.9;">Total Cashback Disbursed</div>
                            <h2 class="font-weight-bold mb-0 mt-1">₹{{ number_format($stats['total_cashback_given'], 2) }}</h2>
                        </div>
                        <i class="mdi mdi-gift-outline" style="font-size:38px; opacity:0.8;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card p-3 border-0 shadow-sm" style="border-radius:12px; background:linear-gradient(135deg, #3B82F6, #1D4ED8); color:#fff;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase font-weight-bold" style="font-size:11px; opacity:0.9;">Cashback Transactions</div>
                            <h2 class="font-weight-bold mb-0 mt-1">{{ number_format($stats['total_transactions']) }}</h2>
                        </div>
                        <i class="mdi mdi-repeat" style="font-size:38px; opacity:0.8;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card p-3 border-0 shadow-sm" style="border-radius:12px; background:linear-gradient(135deg, #8B5CF6, #6D28D9); color:#fff;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase font-weight-bold" style="font-size:11px; opacity:0.9;">Unique Beneficiaries</div>
                            <h2 class="font-weight-bold mb-0 mt-1">{{ number_format($stats['unique_users_count']) }}</h2>
                        </div>
                        <i class="mdi mdi-account-group" style="font-size:38px; opacity:0.8;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-2">
                <div class="card p-3 border-0 shadow-sm" style="border-radius:12px; background:linear-gradient(135deg, #F59E0B, #D97706); color:#fff;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase font-weight-bold" style="font-size:11px; opacity:0.9;">Active Rules</div>
                            <h2 class="font-weight-bold mb-0 mt-1">{{ number_format($stats['active_rules_count']) }}</h2>
                        </div>
                        <i class="mdi mdi-shield-check" style="font-size:38px; opacity:0.8;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cashback Conversion Setting Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; border-left: 4px solid #10B981 !important;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-7 mb-3 mb-lg-0">
                        <h4 class="font-weight-bold text-dark mb-1">
                            <i class="mdi mdi-swap-horizontal-bold text-success mr-1"></i>Cashback-to-Wallet Conversion Rule
                        </h4>
                        <p class="text-muted mb-0" style="font-size:13px;">
                            Top-up cashback accumulates separately in user's cashback balance. Configure the minimum threshold balance required before a customer or driver can convert their collected cashback into their primary wallet balance.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <form action="{{ route('admin.wallet.topup-cashback.update-threshold') }}" method="POST" class="d-flex align-items-center">
                            @csrf
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold bg-light">₹</span>
                                </div>
                                <input type="number" step="1" min="0" name="min_cashback_redeem_threshold" class="form-control font-weight-bold text-dark" value="{{ old('min_cashback_redeem_threshold', $minThreshold ?? 500) }}" placeholder="e.g. 500" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-success font-weight-bold px-3">
                                        <i class="mdi mdi-check mr-1"></i>Save Threshold
                                    </button>
                                </div>
                            </div>
                        </form>
                        <small class="text-muted d-block mt-1">
                            Current threshold: <strong class="text-success">₹{{ number_format($minThreshold ?? 500, 2) }}</strong> (Users need at least this amount to convert).
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rules Table Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title font-weight-bold mb-0">Configured Cashback Rules</h4>
                    <a href="{{ route('admin.wallet.topup-cashback.create') }}" class="btn btn-sm btn-outline-success font-weight-bold">
                        <i class="mdi mdi-plus mr-1"></i>Add New Rule
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>Status</th>
                                <th>Rule Name</th>
                                <th>Type & Value</th>
                                <th>Top-Up Qualifying Range</th>
                                <th>Max Cap (Upto)</th>
                                <th>Audience</th>
                                <th>User Limit</th>
                                <th>Priority</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $r)
                            <tr>
                                <td>
                                    <form action="{{ route('admin.wallet.topup-cashback.toggle', $r->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $r->is_active ? 'btn-success' : 'btn-secondary' }} px-2 py-1 font-weight-bold" style="font-size:11px;" title="Click to toggle status">
                                            {{ $r->is_active ? 'Active' : 'Disabled' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <strong class="text-dark">{{ $r->name }}</strong>
                                    @if($r->start_date || $r->end_date)
                                        <div class="text-muted" style="font-size:11px;">
                                            {{ $r->start_date ? $r->start_date->format('d M Y') : 'Immediate' }} &rarr; {{ $r->end_date ? $r->end_date->format('d M Y') : 'Lifetime' }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($r->cashback_type === 'percentage')
                                        <span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size:12px;">
                                            {{ $r->cashback_value }}%
                                        </span>
                                    @else
                                        <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size:12px;">
                                            Flat ₹{{ number_format($r->cashback_value, 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-weight-bold">Min: ₹{{ number_format($r->min_topup_amount, 2) }}</span>
                                    @if($r->max_topup_amount)
                                        <br><small class="text-muted">Max: ₹{{ number_format($r->max_topup_amount, 2) }}</small>
                                    @else
                                        <br><small class="text-muted">No upper limit</small>
                                    @endif
                                </td>
                                <td>
                                    @if($r->cashback_type === 'percentage' && $r->max_cashback_amount)
                                        <span class="text-danger font-weight-bold">Upto ₹{{ number_format($r->max_cashback_amount, 2) }}</span>
                                    @elseif($r->cashback_type === 'flat')
                                        <span class="text-muted">—</span>
                                    @else
                                        <span class="text-muted">No Cap</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info text-capitalize">{{ $r->target_user_type }}</span>
                                </td>
                                <td>
                                    {{ $r->usage_limit_per_user ? $r->usage_limit_per_user . ' per user' : 'Unlimited' }}
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $r->priority }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.wallet.topup-cashback.edit', $r->id) }}" class="btn btn-sm btn-outline-info mr-1" title="Edit Rule">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.wallet.topup-cashback.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete cashback rule: {{ $r->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Rule">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="mdi mdi-information-outline mr-1"></i>No cashback rules configured yet. Click "Create Cashback Rule" above to add your first rule.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Cashback Logs -->
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-body">
                <h4 class="card-title font-weight-bold mb-3">Recent Cashback Disbursements</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>User / Beneficiary</th>
                                <th>User Type</th>
                                <th>Top-Up Amount</th>
                                <th>Cashback Credited</th>
                                <th>Rule Applied</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div>
                                        <a href="{{ $log->user_url }}" class="font-weight-bold text-dark">{{ $log->user_name }}</a>
                                        <br>
                                        <small class="text-muted">{{ $log->user_phone ?: 'No phone' }}</small>
                                        <span class="badge badge-light">#{{ $log->user_id }}</span>
                                        @if(!empty($log->user_ac_no))
                                            <span class="badge badge-secondary" title="Pocket Account">{{ $log->user_ac_no }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="badge badge-info text-capitalize">{{ $log->user_type }}</span></td>
                                <td>₹{{ number_format($log->topup_amount, 2) }}</td>
                                <td class="text-success font-weight-bold">+₹{{ number_format($log->cashback_amount, 2) }}</td>
                                <td>{{ $log->rule->name ?? 'Custom Rule' }}</td>
                                <td><span class="badge badge-success">{{ $log->status }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-3 text-muted">No cashback disbursements recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

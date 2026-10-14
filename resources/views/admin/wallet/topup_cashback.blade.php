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
                <button type="button" class="btn btn-success shadow-sm font-weight-bold" data-toggle="modal" data-target="#createRuleModal">
                    <i class="mdi mdi-plus-circle mr-1"></i>Create Cashback Rule
                </button>
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

        <!-- Rules Table Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title font-weight-bold mb-0">Active & Configured Cashback Rules</h4>
                    <span class="badge badge-light px-2 py-1 text-muted">{{ count($rules) }} Rules</span>
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
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input rule-toggle" id="switch-{{ $r->id }}" data-id="{{ $r->id }}" {{ $r->is_active ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="switch-{{ $r->id }}"></label>
                                    </div>
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
                                    <button class="btn btn-sm btn-outline-info mr-1" data-toggle="modal" data-target="#editModal-{{ $r->id }}" title="Edit Rule">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.wallet.topup-cashback.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete cashback rule: {{ $r->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Rule">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal for Rule {{ $r->id }} -->
                            <div class="modal fade" id="editModal-{{ $r->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content" style="border-radius:12px;">
                                        <form action="{{ route('admin.wallet.topup-cashback.update', $r->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title font-weight-bold">Edit Cashback Rule: {{ $r->name }}</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="font-weight-bold">Rule Name / Campaign Title <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $r->name }}" required>
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="font-weight-bold">Cashback Type <span class="text-danger">*</span></label>
                                                        <select name="cashback_type" class="form-control" required>
                                                            <option value="percentage" {{ $r->cashback_type === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                            <option value="flat" {{ $r->cashback_type === 'flat' ? 'selected' : '' }}>Flat Amount (₹)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="font-weight-bold">Cashback Value <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" name="cashback_value" class="form-control" value="{{ $r->cashback_value }}" required>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold">Min Top-Up Amount (₹) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" name="min_topup_amount" class="form-control" value="{{ $r->min_topup_amount }}" required>
                                                        <small class="text-muted">Top-up must be &ge; this to qualify</small>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold">Max Top-Up Amount (₹)</label>
                                                        <input type="number" step="0.01" name="max_topup_amount" class="form-control" value="{{ $r->max_topup_amount }}" placeholder="Optional ceiling">
                                                        <small class="text-muted">Leave blank for no upper limit</small>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold">Max Cashback Cap ("Upto ₹")</label>
                                                        <input type="number" step="0.01" name="max_cashback_amount" class="form-control" value="{{ $r->max_cashback_amount }}" placeholder="e.g. 50.00">
                                                        <small class="text-muted">For percentage type (e.g. 10% upto ₹50)</small>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold">Target Audience <span class="text-danger">*</span></label>
                                                        <select name="target_user_type" class="form-control" required>
                                                            <option value="all" {{ $r->target_user_type === 'all' ? 'selected' : '' }}>All (Customers & Drivers)</option>
                                                            <option value="customer" {{ $r->target_user_type === 'customer' ? 'selected' : '' }}>Customers Only</option>
                                                            <option value="driver" {{ $r->target_user_type === 'driver' ? 'selected' : '' }}>Drivers Only</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold">Usage Limit Per User</label>
                                                        <input type="number" name="usage_limit_per_user" class="form-control" value="{{ $r->usage_limit_per_user }}" placeholder="e.g. 1 (Blank = unlimited)">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold">Priority (Evaluation Order)</label>
                                                        <input type="number" name="priority" class="form-control" value="{{ $r->priority }}">
                                                        <small class="text-muted">Higher number evaluated first</small>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="font-weight-bold">Start Date & Time</label>
                                                        <input type="datetime-local" name="start_date" class="form-control" value="{{ $r->start_date ? $r->start_date->format('Y-m-d\TH:i') : '' }}">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="font-weight-bold">End Date & Time</label>
                                                        <input type="datetime-local" name="end_date" class="form-control" value="{{ $r->end_date ? $r->end_date->format('Y-m-d\TH:i') : '' }}">
                                                    </div>
                                                </div>

                                                <div class="custom-control custom-switch mt-2">
                                                    <input type="checkbox" name="is_active" class="custom-control-input" id="editActive-{{ $r->id }}" {{ $r->is_active ? 'checked' : '' }}>
                                                    <label class="custom-control-label font-weight-bold" for="editActive-{{ $r->id }}">Enable Rule (Active immediately)</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary font-weight-bold px-4">Update Rule</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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
                                <th>User ID</th>
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
                                <td><span class="badge badge-light">#{{ $log->user_id }}</span></td>
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

<!-- Create Rule Modal -->
<div class="modal fade" id="createRuleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:12px;">
            <form action="{{ route('admin.wallet.topup-cashback.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold"><i class="mdi mdi-plus-circle text-success mr-1"></i>Create New Top-Up Cashback Rule</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Rule Name / Campaign Title <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Festival 10% Cashback" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold">Cashback Type <span class="text-danger">*</span></label>
                            <select name="cashback_type" id="createType" class="form-control" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="flat">Flat Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold">Cashback Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="cashback_value" class="form-control" placeholder="e.g. 10 or 50" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Min Top-Up Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="min_topup_amount" class="form-control" value="100" required>
                            <small class="text-muted">Minimum top-up required</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Max Top-Up Amount (₹)</label>
                            <input type="number" step="0.01" name="max_topup_amount" class="form-control" placeholder="Optional ceiling">
                            <small class="text-muted">Leave blank for no ceiling</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Max Cashback Cap ("Upto ₹")</label>
                            <input type="number" step="0.01" name="max_cashback_amount" class="form-control" placeholder="e.g. 50">
                            <small class="text-muted">Cap for percentage (e.g. 10% upto ₹50)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Target Audience <span class="text-danger">*</span></label>
                            <select name="target_user_type" class="form-control" required>
                                <option value="all">All (Customers & Drivers)</option>
                                <option value="customer">Customers Only</option>
                                <option value="driver">Drivers Only</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Usage Limit Per User</label>
                            <input type="number" name="usage_limit_per_user" class="form-control" placeholder="e.g. 1 (Blank = unlimited)">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Priority (Evaluation Order)</label>
                            <input type="number" name="priority" class="form-control" value="10">
                            <small class="text-muted">Higher evaluated first</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Start Date & Time (Optional)</label>
                            <input type="datetime-local" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">End Date & Time (Optional)</label>
                            <input type="datetime-local" name="end_date" class="form-control">
                        </div>
                    </div>

                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" name="is_active" class="custom-control-input" id="createActive" checked>
                        <label class="custom-control-label font-weight-bold" for="createActive">Enable Rule (Active immediately)</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success font-weight-bold px-4">Create Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.rule-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function () {
            const ruleId = this.getAttribute('data-id');
            fetch(`/admin/wallet/topup-cashback/toggle/${ruleId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log('Status updated:', data.message);
                }
            })
            .catch(err => console.error(err));
        });
    });
});
</script>
@endsection

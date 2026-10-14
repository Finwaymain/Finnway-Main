@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="container-fluid pt-3">
        <div class="row page-titles mb-3">
            <div class="col-md-7 align-self-center">
                <h3 class="text-themecolor font-weight-bold mb-1">
                    <i class="mdi mdi-pencil text-primary mr-1"></i>Edit Cashback Rule: {{ $rule->name }}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.wallet.topup-cashback.index') }}">Top-Up Cashback Engine</a></li>
                    <li class="breadcrumb-item active">Edit Rule</li>
                </ol>
            </div>
            <div class="col-md-5 align-self-center text-right">
                <a href="{{ route('admin.wallet.topup-cashback.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="mdi mdi-arrow-left mr-1"></i>Back to Rules List
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-body p-4">
                <form action="{{ route('admin.wallet.topup-cashback.update', $rule->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Rule Name / Campaign Title <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name) }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold">Cashback Type <span class="text-danger">*</span></label>
                            <select name="cashback_type" id="cashback_type" class="form-control" required>
                                <option value="percentage" {{ old('cashback_type', $rule->cashback_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="flat" {{ old('cashback_type', $rule->cashback_type) === 'flat' ? 'selected' : '' }}>Flat Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold">Cashback Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="cashback_value" class="form-control" value="{{ old('cashback_value', $rule->cashback_value) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Min Top-Up Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="min_topup_amount" class="form-control" value="{{ old('min_topup_amount', $rule->min_topup_amount) }}" required>
                            <small class="text-muted">Minimum top-up required to qualify</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Max Top-Up Amount (₹)</label>
                            <input type="number" step="0.01" name="max_topup_amount" class="form-control" value="{{ old('max_topup_amount', $rule->max_topup_amount) }}" placeholder="Optional ceiling">
                            <small class="text-muted">Leave blank for no upper limit</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Max Cashback Cap ("Upto ₹")</label>
                            <input type="number" step="0.01" name="max_cashback_amount" class="form-control" value="{{ old('max_cashback_amount', $rule->max_cashback_amount) }}" placeholder="e.g. 50">
                            <small class="text-muted">Max cap for percentage cashback (e.g. 10% upto ₹50)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Target Audience <span class="text-danger">*</span></label>
                            <select name="target_user_type" class="form-control" required>
                                <option value="all" {{ old('target_user_type', $rule->target_user_type) === 'all' ? 'selected' : '' }}>All (Customers & Drivers)</option>
                                <option value="customer" {{ old('target_user_type', $rule->target_user_type) === 'customer' ? 'selected' : '' }}>Customers Only</option>
                                <option value="driver" {{ old('target_user_type', $rule->target_user_type) === 'driver' ? 'selected' : '' }}>Drivers Only</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Usage Limit Per User</label>
                            <input type="number" name="usage_limit_per_user" class="form-control" value="{{ old('usage_limit_per_user', $rule->usage_limit_per_user) }}" placeholder="e.g. 1 (Blank = unlimited)">
                            <small class="text-muted">How many times each user can claim this rule</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Priority (Evaluation Order)</label>
                            <input type="number" name="priority" class="form-control" value="{{ old('priority', $rule->priority) }}">
                            <small class="text-muted">Higher number evaluated first</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Start Date & Time (Optional)</label>
                            <input type="datetime-local" name="start_date" class="form-control" value="{{ $rule->start_date ? $rule->start_date->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">End Date & Time (Optional)</label>
                            <input type="datetime-local" name="end_date" class="form-control" value="{{ $rule->end_date ? $rule->end_date->format('Y-m-d\TH:i') : '' }}">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" {{ old('is_active', $rule->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="is_active">Enable Rule (Active immediately)</label>
                        </div>
                    </div>

                    <div class="pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm">
                            <i class="mdi mdi-check mr-1"></i>Update Cashback Rule
                        </button>
                        <a href="{{ route('admin.wallet.topup-cashback.index') }}" class="btn btn-secondary px-4 ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

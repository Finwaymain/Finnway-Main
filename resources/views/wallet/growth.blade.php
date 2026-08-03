@extends('layouts.app')
@section('content')

<div class="page-wrapper">
    <div class="container-fluid pt-3">
        <div class="card p-4 border-0 shadow-sm" style="border-radius:12px;">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="font-weight-bold"><i class="mdi mdi-trending-up text-primary mr-2"></i>Wallet Growth & Interest Engine</h3>
                    <p class="text-muted mb-0">Configure automated wallet growth interest/bonus credited periodically to active users.</p>
                </div>
                <form action="{{ route('wallet-growth.run') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary font-weight-bold"><i class="mdi mdi-flash mr-1"></i>Run Growth Calculation Now</button>
                </form>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('wallet-growth.update') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Enable Wallet Growth Engine</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="enabled" class="custom-control-input" id="growthSwitch" {{ $growthEnabled === 'true' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="growthSwitch">Active / Allowed</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Growth Calculation Frequency</label>
                        <select name="frequency" class="form-control">
                            <option value="daily" {{ $frequency === 'daily' ? 'selected' : '' }}>Daily (Every 24 hours)</option>
                            <option value="weekly" {{ $frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Calculation Mode</label>
                        <select name="mode" class="form-control">
                            <option value="percentage" {{ $growthMode === 'percentage' ? 'selected' : '' }}>Percentage (%) of Current Wallet Balance</option>
                            <option value="flat" {{ $growthMode === 'flat' ? 'selected' : '' }}>Flat Amount (₹)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Growth Rate / Amount</label>
                        <input type="number" name="rate" step="0.01" min="0" class="form-control" value="{{ $growthRate }}">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

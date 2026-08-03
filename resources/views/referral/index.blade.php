@extends('layouts.app')
@section('content')

<div class="page-wrapper">
    <div class="container-fluid pt-3">
        <div class="card p-4 border-0 shadow-sm mb-4" style="border-radius:12px;">
            <h3 class="font-weight-bold"><i class="mdi mdi-share-variant text-primary mr-2"></i>Refer & Earn Reward Engine</h3>
            <p class="text-muted">Configure automated referral rewards and monitor referrers across the ecosystem.</p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('referral.update') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Reward Type</label>
                        <select name="mode" class="form-control">
                            <option value="percentage" {{ $rewardMode === 'percentage' ? 'selected' : '' }}>Percentage (%) of Transaction / Booking Amount</option>
                            <option value="flat" {{ $rewardMode === 'flat' ? 'selected' : '' }}>Flat Bonus (₹)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Reward Value</label>
                        <input type="number" name="value" step="0.01" min="0" class="form-control" value="{{ $rewardValue }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary px-4 font-weight-bold">Save Reward Engine Settings</button>
            </form>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm text-center" style="border-radius:12px;">
                    <h5 class="text-muted mb-1">Total Linked Referrals</h5>
                    <h2 class="font-weight-bold text-primary mb-0">{{ number_format($totalReferrals) }}</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm text-center" style="border-radius:12px;">
                    <h5 class="text-muted mb-1">Total Referral Paid Out</h5>
                    <h2 class="font-weight-bold text-success mb-0">₹{{ number_format($totalPaid, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

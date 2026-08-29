@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Consumer Premium Plans</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Consumer Plans</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Stats Row --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #5B4FE9 !important;">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3"><i class="mdi mdi-star-circle text-primary" style="font-size:2rem;"></i></div>
                            <div>
                                <h5 class="mb-0 font-weight-bold">{{ $plans->total() }}</h5>
                                <p class="mb-0 text-muted small">Total Plans</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #28a745 !important;">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3"><i class="mdi mdi-check-circle text-success" style="font-size:2rem;"></i></div>
                            <div>
                                <h5 class="mb-0 font-weight-bold">{{ $plans->where('status','active')->count() }}</h5>
                                <p class="mb-0 text-muted small">Active Plans</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3"><i class="mdi mdi-close-circle text-danger" style="font-size:2rem;"></i></div>
                            <div>
                                <h5 class="mb-0 font-weight-bold">{{ $plans->where('status','inactive')->count() }}</h5>
                                <p class="mb-0 text-muted small">Inactive Plans</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="{{ route('consumer-plans.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus mr-1"></i> Create Consumer Plan
                            </a>
                            <form action="{{ route('consumer-plans.index') }}" method="get" class="d-flex">
                                <input type="text" name="search" class="form-control mr-2" placeholder="Search plans..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-secondary mr-1"><i class="fa fa-search"></i></button>
                                <a href="{{ route('consumer-plans.index') }}" class="btn btn-warning">Clear</a>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-striped">
                                <thead style="background: #f8fafc; color: #1e293b; border-bottom: 2px solid #e2e8f0;">
                                    <tr>
                                        <th>Plan Name</th>
                                        <th>Price</th>
                                        <th>Validity</th>
                                        <th>Cashback (Send/Receive)</th>
                                        <th>Service Discounts</th>
                                        <th>Loan Eligible</th>
                                        <th>Virtual Credit</th>
                                        <th>Quotas & Benefits</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($plans as $plan)
                                    <tr>
                                        <td>
                                            <strong>{{ $plan->name }}</strong>
                                            @if($plan->free_shipping)
                                                <span class="badge badge-info ml-1">Free Shipping</span>
                                            @endif
                                        </td>
                                        <td>{{ $currency_symbol ?? \App\Helpers\Helper::getCurrencySymbol() }}{{ number_format($plan->price, 2) }}/yr</td>
                                        <td>{{ $plan->validity_days }} days</td>
                                        <td>
                                            <span class="badge badge-success">S: {{ $plan->sender_cashback_value }}{{ $plan->sender_cashback_type === 'percentage' ? '%' : ($currency_symbol ?? \App\Helpers\Helper::getCurrencySymbol()) }}</span>
                                            <span class="badge badge-primary ml-1">R: {{ $plan->receiver_cashback_value }}{{ $plan->receiver_cashback_type === 'percentage' ? '%' : ($currency_symbol ?? \App\Helpers\Helper::getCurrencySymbol()) }}</span>
                                            <br><small class="text-muted">Purchase: {{ $plan->cashback_on_purchase }}{{ $plan->sender_cashback_type === 'percentage' ? '%' : ($currency_symbol ?? \App\Helpers\Helper::getCurrencySymbol()) }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted d-block">Cab: {{ $plan->discount_cab }}%</small>
                                            <small class="text-muted d-block">Bike: {{ $plan->discount_bike }}%</small>
                                            <small class="text-muted d-block">Food: {{ $plan->discount_food }}%</small>
                                            <small class="text-muted d-block">Hotel: {{ $plan->discount_hotel }}%</small>
                                            <small class="text-muted d-block">Travel: {{ $plan->discount_travel }}%</small>
                                            <small class="text-muted d-block">Health: {{ $plan->discount_healthcare }}%</small>
                                            <small class="text-muted d-block">Market: {{ $plan->discount_marketplace }}%</small>
                                        </td>
                                        <td>
                                            @php $loans = array_filter(['Personal' => $plan->loan_personal, 'Business' => $plan->loan_business, 'Credit Card' => $plan->loan_credit_card, 'Interest-Free' => $plan->loan_interest_free, 'Virtual' => $plan->loan_virtual]); @endphp
                                            @if(count($loans))
                                                @foreach($loans as $label => $v)
                                                    <span class="badge badge-light border">{{ $label }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">None</span>
                                            @endif
                                        </td>
                                        <td>₹{{ number_format($plan->virtual_credit_limit, 0) }}</td>
                                        <td>
                                            <small class="text-muted d-block">Free Rides: {{ $plan->free_ride_limit }}</small>
                                            <small class="text-muted d-block">Hotel Bookings: {{ $plan->quota_hotel_booking }}</small>
                                            <small class="text-muted d-block">Home Service: {{ $plan->quota_home_service }}</small>
                                            <small class="text-muted d-block">Shopping: {{ $plan->quota_shopping }}</small>
                                            <small class="text-muted d-block">Food: {{ $plan->quota_food }}</small>
                                            <small class="text-muted d-block">Medical: {{ $plan->quota_medical }}</small>
                                            <small class="text-muted d-block">Travel: {{ $plan->quota_travel }}</small>
                                        </td>
                                        <td>
                                            <label class="switch">
                                                <input type="checkbox" class="plan-toggle" data-id="{{ $plan->id }}" {{ $plan->status === 'active' ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="d-inline-flex align-items-center" style="gap:6px;">
                                                <a href="{{ route('consumer-plans.edit', $plan->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i> Edit</a>
                                                <a href="{{ route('consumer-plans.delete', $plan->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this plan?')" title="Delete"><i class="fa fa-trash"></i> Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">No consumer plans yet. <a href="{{ route('consumer-plans.create') }}">Create one.</a></td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Showing {{ $plans->firstItem() ?? 0 }} to {{ $plans->lastItem() ?? 0 }} of {{ $plans->total() }} plans
                            </div>
                            {{ $plans->appends(request()->query())->links('pagination.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
$(document).on('change', '.plan-toggle', function () {
    const id = $(this).data('id');
    const ischeck = $(this).is(':checked') ? 'true' : 'false';
    $.ajax({
        url: '{{ route("consumer-plans.toggle") }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: { id, ischeck },
        error: function () { alert('Failed to update status.'); location.reload(); }
    });
});
</script>
@endsection

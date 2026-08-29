@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Header & Breadcrumb -->
        <div class="row page-titles mb-3">
            <div class="col-md-6 align-self-center">
                <h3 class="text-themecolor font-weight-bold"><i class="fa fa-credit-card mr-2 text-primary"></i> Medical Cashback Card Plans</h3>
            </div>
            <div class="col-md-6 align-self-center text-right">
                <a href="{{ route('admin.medical.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold">
                    <i class="fa fa-list mr-1"></i> Claims Queue
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show font-weight-bold shadow-sm" role="alert">
                <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show font-weight-bold shadow-sm" role="alert">
                <i class="fa fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="row">
            <!-- Left Column: Add / Edit Card Plan Form (NO MODALS) -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header {{ $editPlan ? 'bg-info text-white' : 'bg-primary text-white' }} py-3">
                        <h5 class="font-weight-bold mb-0 text-white">
                            @if($editPlan)
                                <i class="fa fa-edit mr-1"></i> Edit Card Plan
                            @else
                                <i class="fa fa-plus-circle mr-1"></i> Add New Card Plan
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ $editPlan ? route('admin.medical.plans.update', $editPlan->id) : route('admin.medical.plans.store') }}" method="POST">
                            @csrf
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark small">Card Title</label>
                                <input type="text" name="title" class="form-control font-weight-bold" value="{{ $editPlan ? $editPlan->title : old('title') }}" placeholder="e.g. VIP HEALTH CREDIT" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark small">Badge Text (Optional)</label>
                                <input type="text" name="badge" class="form-control" value="{{ $editPlan ? $editPlan->badge : old('badge') }}" placeholder="e.g. ★ Most Popular">
                            </div>

                            <div class="row">
                                <div class="col-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark small">Price (₹)</label>
                                    <input type="number" step="0.01" name="price" class="form-control font-weight-bold text-success" value="{{ $editPlan ? $editPlan->price : old('price') }}" placeholder="1200" required>
                                </div>
                                <div class="col-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark small">Claim Limit (₹)</label>
                                    <input type="number" step="0.01" name="claim_limit" class="form-control font-weight-bold text-primary" value="{{ $editPlan ? $editPlan->claim_limit : old('claim_limit') }}" placeholder="5000" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark small">Max Claims</label>
                                    <input type="number" name="max_claims" class="form-control font-weight-bold" value="{{ $editPlan ? $editPlan->max_claims : (old('max_claims') ?: 1) }}" required>
                                </div>
                                <div class="col-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark small">Validity Period</label>
                                    <input type="text" name="period" class="form-control font-weight-bold" value="{{ $editPlan ? $editPlan->period : (old('period') ?: '1 Year') }}" placeholder="e.g. 1 Year" required>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark small">Status</label>
                                <select name="status" class="form-control font-weight-bold">
                                    <option value="active" {{ ($editPlan && $editPlan->status == 'active') ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ ($editPlan && $editPlan->status == 'inactive') ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark small">Benefits (One per line)</label>
                                <textarea name="benefits" class="form-control" rows="4" placeholder="Enter card benefits, one item per line...">{{ $editPlan ? $editPlan->benefits_text : old('benefits') }}</textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn {{ $editPlan ? 'btn-info' : 'btn-primary' }} btn-block font-weight-bold py-2">
                                    @if($editPlan)
                                        <i class="fa fa-save mr-1"></i> Update Card Plan
                                    @else
                                        <i class="fa fa-check mr-1"></i> Create Card Plan
                                    @endif
                                </button>

                                @if($editPlan)
                                    <a href="{{ route('admin.medical.plans.index') }}" class="btn btn-secondary btn-block font-weight-bold py-2 mt-0 ml-2">
                                        Cancel
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Card Plans Table -->
            <div class="col-lg-8 col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="font-weight-bold mb-0 text-dark"><i class="fa fa-cogs mr-2 text-info"></i> All Configured Card Plans</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-uppercase text-secondary font-weight-bold" style="font-size: 11px;">
                                    <tr>
                                        <th>#</th>
                                        <th>Card Plan</th>
                                        <th>Price & Limit</th>
                                        <th>Claims & Period</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($plans as $plan)
                                        <tr class="{{ ($editPlan && $editPlan->id == $plan->id) ? 'table-info' : '' }}">
                                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                                            <td>
                                                <strong class="text-dark font-weight-bold d-block">{{ $plan->title }}</strong>
                                                @if($plan->badge)
                                                    <span class="badge badge-warning text-dark font-weight-bold">{{ $plan->badge }}</span>
                                                @endif
                                                <small class="text-muted d-block mt-1">Benefits: {{ count($plan->benefits_list) }} items</small>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-success d-block">Price: ₹{{ number_format($plan->price, 2) }}</span>
                                                <span class="font-weight-bold text-primary d-block">Limit: ₹{{ number_format($plan->claim_limit, 2) }}</span>
                                            </td>
                                            <td>
                                                <small class="d-block font-weight-bold text-dark"><i class="fa fa-refresh mr-1 text-info"></i> {{ $plan->max_claims }} Claim(s)</small>
                                                <small class="text-muted"><i class="fa fa-calendar mr-1"></i> {{ $plan->period }}</small>
                                            </td>
                                            <td>
                                                @if($plan->status == 'active')
                                                    <span class="badge badge-success px-2.5 py-1 font-weight-bold">ACTIVE</span>
                                                @else
                                                    <span class="badge badge-secondary px-2.5 py-1 font-weight-bold">INACTIVE</span>
                                                @endif
                                            </td>
                                            <td class="text-right" style="white-space: nowrap;">
                                                <!-- Direct standard anchor link for EDIT -->
                                                <a href="{{ route('admin.medical.plans.index') }}?edit_id={{ $plan->id }}" class="btn btn-sm btn-info font-weight-bold mr-1">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>

                                                <!-- Direct standard anchor link for TOGGLE -->
                                                <a href="{{ route('admin.medical.plans.toggle', $plan->id) }}" class="btn btn-sm {{ $plan->status == 'active' ? 'btn-warning text-white' : 'btn-success' }} font-weight-bold mr-1">
                                                    <i class="fa {{ $plan->status == 'active' ? 'fa-ban' : 'fa-check' }}"></i> {{ $plan->status == 'active' ? 'Disable' : 'Enable' }}
                                                </a>

                                                <!-- Direct standard anchor link for DELETE -->
                                                <a href="{{ route('admin.medical.plans.delete', $plan->id) }}" onclick="return confirm('Are you sure you want to delete card plan {{ $plan->title }}?');" class="btn btn-sm btn-danger font-weight-bold">
                                                    <i class="fa fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="fa fa-credit-card text-slate-300 mb-2" style="font-size: 40px;"></i>
                                                <p class="font-weight-bold">No card plans found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

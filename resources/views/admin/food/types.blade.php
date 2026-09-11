@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">🏷️ Restaurant Types & Onboarding Fees</h3>
            <p class="text-muted mb-0">Configure partner business categories, onboarding fees, approval workflows, and ordering rules.</p>
        </div>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTypeModal">
            <i class="fa fa-plus-circle mr-1"></i> Add New Category Type
        </button>
    </div>

    <!-- Active Types Cards -->
    <div class="row">
        @foreach($types as $t)
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100 {{ $t->is_active ? 'border-left border-primary' : 'border-left border-secondary' }}" style="border-left-width: 4px !important;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="mr-2" style="font-size: 24px;">
                                @if($t->code === 'cloud_kitchen') 🍳 @elseif($t->code === 'actual_restaurant') 🍽️ @else 🏪 @endif
                            </span>
                            <div>
                                <h5 class="font-weight-bold text-dark mb-0">{{ $t->name }}</h5>
                                <code class="small text-muted">{{ $t->code }}</code>
                            </div>
                        </div>
                        <div>
                            @if($t->is_active)
                                <span class="badge badge-success px-2 py-1">Active</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1">Disabled</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.food.types.save', $t->id) }}">
                            @csrf
                            <input type="hidden" name="code" value="{{ $t->code }}">

                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Display Title</label>
                                <input type="text" name="name" class="form-control" value="{{ $t->name }}" required>
                            </div>

                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ $t->description }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Onboarding Fee (₹)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text font-weight-bold">₹</span>
                                        </div>
                                        <input type="number" step="1" name="onboarding_fee" class="form-control" value="{{ $t->onboarding_fee }}" required>
                                    </div>
                                    <small class="text-muted">Charged during registration</small>
                                </div>
                                <div class="col-md-6 form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Approval Mode</label>
                                    <select name="approval_mode" class="form-control custom-select">
                                        <option value="manual" @selected($t->approval_mode === 'manual')>Manual (Admin Review)</option>
                                        <option value="auto" @selected($t->approval_mode === 'auto')>Auto (Instant Live)</option>
                                    </select>
                                    <small class="text-muted">After fee payment</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="activeSwitch{{ $t->id }}" name="is_active" value="1" @checked($t->is_active)>
                                    <label class="custom-control-label font-weight-bold small text-muted" for="activeSwitch{{ $t->id }}">
                                        Enable Category
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fa fa-save mr-1"></i> Update Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Add Type Modal -->
<div class="modal fade" id="addTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('admin.food.types.save') }}">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold text-dark">Add New Restaurant Type</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Unique Code Identifier</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. bakery, cafe, food_truck" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Cafe & Dessert Parlour" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief info shown during onboarding..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Onboarding Fee (₹)</label>
                            <input type="number" name="onboarding_fee" class="form-control" value="499" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-muted">Approval Mode</label>
                            <select name="approval_mode" class="form-control custom-select">
                                <option value="manual" selected>Manual Admin Review</option>
                                <option value="auto">Auto Activate</option>
                            </select>
                        </div>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="newActive" name="is_active" value="1" checked>
                        <label class="custom-control-label font-weight-bold small text-muted" for="newActive">
                            Activate immediately
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

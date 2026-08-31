@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background-color: #f8fafc; min-height: 100vh; padding: 20px;">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded mb-3" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded mb-3" role="alert">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Clean Header (No Icons) -->
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">Marketplace Commission</h3>
            <p class="text-muted mb-0 font-13">Set platform commission to deduct from seller payouts upon order settlement.</p>
        </div>
        <div>
            <a href="{{ route('admin.marketplace.orders.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold px-3 py-1 rounded">
                View Marketplace Orders
            </a>
        </div>
    </div>

    <!-- Simple, Clean Configuration Form -->
    <div class="row">
        <div class="col-lg-6 col-md-8">
            <div class="card border shadow-sm rounded bg-white">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                    <h5 class="font-weight-bold mb-0 text-dark">Commission Settings</h5>
                    <span class="badge {{ $setting->is_active ? 'badge-success' : 'badge-danger' }} px-2 py-1 font-11">
                        {{ $setting->is_active ? 'Active' : 'Disabled' }}
                    </span>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('admin.marketplace.commission.update') }}" method="POST">
                        @csrf

                        <!-- Commission Type -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark font-13 mb-1">Commission Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="custom-control custom-radio mr-3">
                                    <input type="radio" id="type_percentage" name="commission_type" value="percentage" {{ $setting->commission_type === 'percentage' ? 'checked' : '' }} class="custom-control-input">
                                    <label class="custom-control-label font-weight-bold text-dark" for="type_percentage">Percentage (%)</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="type_fixed" name="commission_type" value="fixed" {{ $setting->commission_type === 'fixed' ? 'checked' : '' }} class="custom-control-input">
                                    <label class="custom-control-label font-weight-bold text-dark" for="type_fixed">Fixed Amount (₹)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Commission Rate -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark font-13 mb-1" id="rateLabel">
                                Commission Rate ({{ $setting->commission_type === 'percentage' ? '%' : '₹' }}) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light font-weight-bold" id="ratePrefix">
                                        {{ $setting->commission_type === 'percentage' ? '%' : '₹' }}
                                    </span>
                                </div>
                                <input type="number" step="0.01" min="0" max="100" name="commission_value" id="commission_value" value="{{ old('commission_value', $setting->commission_value) }}" class="form-control font-weight-bold" required placeholder="e.g. 5.00">
                            </div>
                        </div>

                        <!-- Min Order Amount -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark font-13 mb-1">Minimum Order Amount (₹)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light font-weight-bold">₹</span>
                                </div>
                                <input type="number" step="0.01" min="0" name="min_order_amount" id="min_order_amount" value="{{ old('min_order_amount', $setting->min_order_amount) }}" class="form-control" placeholder="0.00">
                            </div>
                        </div>

                        <!-- Status Toggle -->
                        <div class="form-group mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold text-dark" for="is_active">
                                    Enable Marketplace Commission Deduction
                                </label>
                            </div>
                        </div>

                        <!-- Description / Note -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark font-13 mb-1">Description / Note</label>
                            <input type="text" name="description" value="{{ old('description', $setting->description) }}" class="form-control form-control-sm" placeholder="e.g. Platform Marketplace Commission">
                        </div>

                        <div class="pt-2 border-top">
                            <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2 rounded">
                                Save Commission Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioPercentage = document.getElementById('type_percentage');
    const radioFixed = document.getElementById('type_fixed');
    const ratePrefix = document.getElementById('ratePrefix');
    const rateLabel = document.getElementById('rateLabel');
    const commInput = document.getElementById('commission_value');

    function updateLabels() {
        if (radioPercentage.checked) {
            ratePrefix.textContent = '%';
            rateLabel.innerHTML = 'Commission Rate (%) <span class="text-danger">*</span>';
            commInput.max = 100;
        } else {
            ratePrefix.textContent = '₹';
            rateLabel.innerHTML = 'Commission Fixed Amount (₹) <span class="text-danger">*</span>';
            commInput.removeAttribute('max');
        }
    }

    radioPercentage.addEventListener('change', updateLabels);
    radioFixed.addEventListener('change', updateLabels);
});
</script>
@endsection

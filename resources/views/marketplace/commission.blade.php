@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background-color: #f8fafc; min-height: 100vh; padding: 25px;">
    
    <!-- Top Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-12 shadow-sm mb-4" role="alert" style="border-left: 5px solid #10b981;">
            <i class="mdi mdi-check-circle mr-2 font-18"></i> <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-12 shadow-sm mb-4" role="alert" style="border-left: 5px solid #ef4444;">
            <i class="mdi mdi-alert-circle mr-2 font-18"></i> <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <h2 class="font-weight-bold text-dark mb-1">
                <i class="mdi mdi-percent text-success mr-2"></i> Marketplace Commission Settings
            </h2>
            <p class="text-muted mb-0 font-14">Configure platform commission deducted from seller earnings upon order settlement confirmation.</p>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('admin.marketplace.orders.index') }}" class="btn btn-outline-primary font-weight-bold px-4 py-2 rounded-8 shadow-sm">
                <i class="mdi mdi-clipboard-list mr-1"></i> View Marketplace Orders
            </a>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-16 h-100 bg-white" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-12 bg-blue-50 p-3 mr-3 text-primary">
                        <i class="mdi mdi-cart-outline font-24"></i>
                    </div>
                    <div>
                        <div class="text-muted font-12 font-weight-bold uppercase">Total Orders</div>
                        <h4 class="font-weight-extrabold text-dark mb-0 mt-1">{{ number_format($totalOrdersCount) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-16 h-100 bg-white" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-12 bg-emerald-50 p-3 mr-3 text-success">
                        <i class="mdi mdi-cash-check font-24"></i>
                    </div>
                    <div>
                        <div class="text-muted font-12 font-weight-bold uppercase">Settled Commission</div>
                        <h4 class="font-weight-extrabold text-success mb-0 mt-1">₹{{ number_format($totalCommissionEarned, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-16 h-100 bg-white" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-12 bg-amber-50 p-3 mr-3 text-warning">
                        <i class="mdi mdi-timer-sand font-24"></i>
                    </div>
                    <div>
                        <div class="text-muted font-12 font-weight-bold uppercase">Escrow Held Commission</div>
                        <h4 class="font-weight-extrabold text-warning mb-0 mt-1">₹{{ number_format($pendingCommissionHold, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-16 h-100 bg-white" style="border-left: 4px solid #8b5cf6 !important;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-12 bg-purple-50 p-3 mr-3 text-purple" style="color: #8b5cf6;">
                        <i class="mdi mdi-wallet font-24"></i>
                    </div>
                    <div>
                        <div class="text-muted font-12 font-weight-bold uppercase">Settled to Sellers</div>
                        <h4 class="font-weight-extrabold text-dark mb-0 mt-1">₹{{ number_format($totalSellerPayoutsSettled, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row">
        <!-- Commission Edit Form -->
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm rounded-16 bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h5 class="font-weight-bold mb-0 text-dark">
                        <i class="mdi mdi-tune-vertical text-primary mr-1"></i> Commission Configuration
                    </h5>
                    <span class="badge {{ $setting->is_active ? 'badge-success' : 'badge-danger' }} px-3 py-2 rounded-pill font-12">
                        {{ $setting->is_active ? 'Active' : 'Disabled' }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.marketplace.commission.update') }}" method="POST">
                        @csrf

                        <!-- Commission Type Switcher -->
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark mb-2">Commission Calculation Type <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-6">
                                    <label class="card border p-3 rounded-12 text-center cursor-pointer mb-0 w-100 commission-type-card {{ $setting->commission_type === 'percentage' ? 'border-primary bg-light-primary' : '' }}" style="cursor: pointer;">
                                        <input type="radio" name="commission_type" value="percentage" {{ $setting->commission_type === 'percentage' ? 'checked' : '' }} class="d-none" id="type_percentage">
                                        <i class="mdi mdi-percent font-24 text-primary d-block mb-1"></i>
                                        <span class="font-weight-bold text-dark d-block">Percentage (%)</span>
                                        <small class="text-muted">Calculates % on product selling price</small>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label class="card border p-3 rounded-12 text-center cursor-pointer mb-0 w-100 commission-type-card {{ $setting->commission_type === 'fixed' ? 'border-primary bg-light-primary' : '' }}" style="cursor: pointer;">
                                        <input type="radio" name="commission_type" value="fixed" {{ $setting->commission_type === 'fixed' ? 'checked' : '' }} class="d-none" id="type_fixed">
                                        <i class="mdi mdi-currency-inr font-24 text-success d-block mb-1"></i>
                                        <span class="font-weight-bold text-dark d-block">Fixed Amount (₹)</span>
                                        <small class="text-muted">Fixed flat deduction per order</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Commission Value -->
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark mb-2" id="valLabel">
                                Commission Rate ({{ $setting->commission_type === 'percentage' ? '%' : '₹' }}) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light font-weight-bold" id="valPrefix">
                                        {{ $setting->commission_type === 'percentage' ? '%' : '₹' }}
                                    </span>
                                </div>
                                <input type="number" step="0.01" min="0" max="100" name="commission_value" id="commission_value" value="{{ old('commission_value', $setting->commission_value) }}" class="form-control form-control-lg font-weight-bold" required placeholder="e.g. 5.00">
                            </div>
                            <small class="text-muted mt-1 d-block">Example: Enter 5 for 5% admin commission on all marketplace orders.</small>
                        </div>

                        <!-- Min Order Amount -->
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark mb-2">Minimum Order Amount for Commission (₹)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light font-weight-bold">₹</span>
                                </div>
                                <input type="number" step="0.01" min="0" name="min_order_amount" id="min_order_amount" value="{{ old('min_order_amount', $setting->min_order_amount) }}" class="form-control" placeholder="0.00">
                            </div>
                            <small class="text-muted">Orders below this amount will not be charged commission (0 for all orders).</small>
                        </div>

                        <!-- Status Toggle -->
                        <div class="form-group mb-4">
                            <div class="custom-control custom-switch custom-switch-lg">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold text-dark pt-1" for="is_active">
                                    Enable Marketplace Admin Commission
                                </label>
                            </div>
                            <small class="text-muted">When enabled, commission will be automatically computed and deducted during payout settlement.</small>
                        </div>

                        <!-- Description / Note -->
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark mb-2">Admin Policy Note / Description</label>
                            <textarea name="description" class="form-control rounded-8" rows="3" placeholder="Internal remarks about this commission tier...">{{ old('description', $setting->description) }}</textarea>
                        </div>

                        <div class="pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-lg font-weight-bold px-5 rounded-8 shadow-sm">
                                <i class="mdi mdi-content-save mr-1"></i> Save Commission Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Live Payout Simulator Card -->
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm rounded-16 bg-white mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="font-weight-bold mb-0 text-dark">
                        <i class="mdi mdi-calculator text-success mr-1"></i> Live Settlement Simulator
                    </h5>
                    <small class="text-muted">Test how payouts & commissions calculate in real-time</small>
                </div>
                <div class="card-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 uppercase">Sample Product Price (₹)</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text bg-light">₹</span></div>
                            <input type="number" id="sim_price" value="1000" class="form-control font-weight-bold" step="10">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 uppercase">Quantity</label>
                        <input type="number" id="sim_qty" value="1" min="1" class="form-control font-weight-bold">
                    </div>

                    <!-- Breakdown Box -->
                    <div class="p-3 rounded-12 bg-light border mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted font-14">Order Subtotal:</span>
                            <span class="font-weight-bold text-dark font-14" id="sim_subtotal">₹1,000.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-danger font-14">Admin Commission (<span id="sim_comm_rate">5%</span>):</span>
                            <span class="font-weight-bold text-danger font-14" id="sim_commission">- ₹50.00</span>
                        </div>
                        <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                            <span class="font-weight-extrabold text-success font-16">Seller Net Wallet Payout:</span>
                            <span class="font-weight-extrabold text-success font-18" id="sim_payout">₹950.00</span>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0 font-12 rounded-8">
                        <i class="mdi mdi-information-outline mr-1 font-14"></i>
                        When a buyer purchases an item, money is held securely in Escrow. When admin confirms order in <strong>Marketplace Orders</strong>, the net payout (₹950.00) is credited to the seller's wallet and transaction entries are logged.
                    </div>
                </div>
            </div>

            <!-- Workflow Guide Card -->
            <div class="card border-0 shadow-sm rounded-16 bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="font-weight-bold mb-0 text-dark">
                        <i class="mdi mdi-shield-check text-primary mr-1"></i> How Marketplace Escrow Works
                    </h6>
                </div>
                <div class="card-body p-4 font-13 text-muted">
                    <ol class="pl-3 mb-0" style="line-height: 1.8;">
                        <li><strong class="text-dark">Buyer Purchases Product:</strong> Total amount (including GST tax) is charged from buyer wallet & order status set to <code>Placed</code>.</li>
                        <li><strong class="text-dark">Seller Ships Product:</strong> Seller uploads Courier Name & Tracking ID.</li>
                        <li><strong class="text-dark">Admin Confirmation:</strong> Admin reviews tracking, clicks <strong>"Confirm & Release Payout"</strong> on the orders page.</li>
                        <li><strong class="text-dark">Wallet Credited:</strong> Net earnings are credited to seller wallet with dual transaction logging (Sale + Commission).</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioPercentage = document.getElementById('type_percentage');
    const radioFixed = document.getElementById('type_fixed');
    const cards = document.querySelectorAll('.commission-type-card');
    const valPrefix = document.getElementById('valPrefix');
    const valLabel = document.getElementById('valLabel');
    const commInput = document.getElementById('commission_value');
    
    // Simulator Elements
    const simPrice = document.getElementById('sim_price');
    const simQty = document.getElementById('sim_qty');
    const simSubtotal = document.getElementById('sim_subtotal');
    const simCommRate = document.getElementById('sim_comm_rate');
    const simCommission = document.getElementById('sim_commission');
    const simPayout = document.getElementById('sim_payout');

    function updateTypeSelection() {
        cards.forEach(c => c.classList.remove('border-primary', 'bg-light-primary'));
        if (radioPercentage.checked) {
            radioPercentage.closest('.commission-type-card').classList.add('border-primary', 'bg-light-primary');
            valPrefix.textContent = '%';
            valLabel.innerHTML = 'Commission Rate (%) <span class="text-danger">*</span>';
            commInput.max = 100;
        } else {
            radioFixed.closest('.commission-type-card').classList.add('border-primary', 'bg-light-primary');
            valPrefix.textContent = '₹';
            valLabel.innerHTML = 'Commission Fixed Amount (₹) <span class="text-danger">*</span>';
            commInput.removeAttribute('max');
        }
        recalcSimulator();
    }

    function recalcSimulator() {
        const price = parseFloat(simPrice.value) || 0;
        const qty = parseInt(simQty.value) || 1;
        const subtotal = price * qty;
        const commVal = parseFloat(commInput.value) || 0;
        let commAmount = 0;

        if (radioPercentage.checked) {
            commAmount = (subtotal * commVal) / 100;
            simCommRate.textContent = commVal + '%';
        } else {
            commAmount = commVal;
            simCommRate.textContent = '₹' + commVal.toFixed(2);
        }

        const netPayout = Math.max(0, subtotal - commAmount);

        simSubtotal.textContent = '₹' + subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        simCommission.textContent = '- ₹' + commAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        simPayout.textContent = '₹' + netPayout.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    radioPercentage.addEventListener('change', updateTypeSelection);
    radioFixed.addEventListener('change', updateTypeSelection);
    commInput.addEventListener('input', recalcSimulator);
    simPrice.addEventListener('input', recalcSimulator);
    simQty.addEventListener('input', recalcSimulator);

    updateTypeSelection();
});
</script>

<style>
.rounded-8 { border-radius: 8px !important; }
.rounded-12 { border-radius: 12px !important; }
.rounded-16 { border-radius: 16px !important; }
.bg-light-primary { background-color: #eff6ff !important; }
.bg-blue-50 { background-color: #eff6ff !important; }
.bg-emerald-50 { background-color: #ecfdf5 !important; }
.bg-amber-50 { background-color: #fffbeb !important; }
.bg-purple-50 { background-color: #f5f3ff !important; }
.font-24 { font-size: 24px; }
.font-18 { font-size: 18px; }
.font-16 { font-size: 16px; }
.font-14 { font-size: 14px; }
.font-12 { font-size: 12px; }
.font-weight-extrabold { font-weight: 800; }
</style>
@endsection

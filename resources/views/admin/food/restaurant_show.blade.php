@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <!-- Breadcrumb & Back -->
    <div class="mb-3">
        <a href="{{ route('admin.food.restaurants') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left mr-1"></i> Back to Restaurants
        </a>
    </div>

    <!-- Header Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-4">
            <div class="row align-items-center">
                <div class="col-md-7 d-flex align-items-center">
                    <div class="mr-4">
                        @if($restaurant->logo)
                            <img src="{{ asset('storage/' . $restaurant->logo) }}" alt="{{ $restaurant->name }}" class="rounded-circle border shadow-sm" style="width: 72px; height: 72px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold shadow-sm" style="width: 72px; height: 72px; font-size: 26px;">
                                {{ strtoupper(substr($restaurant->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <h3 class="font-weight-bold mb-0 mr-3 text-dark">{{ $restaurant->name }}</h3>
                            <span class="badge badge-secondary mr-2">ID: #{{ $restaurant->id }}</span>
                            @if($restaurant->onboarding_status === 'active')
                                <span class="badge badge-success px-2 py-1">Active & Live</span>
                            @elseif($restaurant->onboarding_status === 'pending_approval')
                                <span class="badge badge-warning px-2 py-1">Pending Approval</span>
                            @elseif($restaurant->onboarding_status === 'doc_resubmission_required')
                                <span class="badge badge-info px-2 py-1">Doc Resubmission Required</span>
                            @elseif($restaurant->onboarding_status === 'suspended')
                                <span class="badge badge-danger px-2 py-1">Suspended</span>
                            @else
                                <span class="badge badge-light border px-2 py-1">{{ ucfirst($restaurant->onboarding_status) }}</span>
                            @endif
                        </div>
                        <p class="text-muted mb-0">
                            <i class="fa fa-map-marker-alt text-danger mr-1"></i> {{ $restaurant->city ?: 'No city' }}, {{ $restaurant->state }}
                            <span class="mx-2">•</span>
                            <i class="fa fa-user text-primary mr-1"></i> {{ $restaurant->owner_name ?: ($restaurant->owner ? $restaurant->owner->name : 'N/A') }} ({{ $restaurant->owner_phone ?: ($restaurant->owner ? $restaurant->owner->phone : 'N/A') }})
                            <span class="mx-2">•</span>
                            <span class="badge badge-light border">{{ ucwords(str_replace('_', ' ', $restaurant->business_type ?: 'restaurant')) }}</span>
                            @if($restaurant->pure_veg)
                                <span class="badge badge-success">Pure Veg 🟢</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-md-5 text-md-right mt-3 mt-md-0">
                    <div class="d-flex justify-content-md-end align-items-center flex-wrap">
                        <!-- Operational Status Selector -->
                        <form method="post" action="{{ route('admin.food.restaurants.status', $restaurant->id) }}" class="d-inline-block mr-2 mb-2">
                            @csrf
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold bg-light">Ops Status</span>
                                </div>
                                <select name="operational_status" class="custom-select custom-select-sm" onchange="this.form.submit()">
                                    <option value="open" @selected($restaurant->operational_status=='open')>🟢 Open</option>
                                    <option value="busy" @selected($restaurant->operational_status=='busy')>🟡 Busy</option>
                                    <option value="temporarily_closed" @selected($restaurant->operational_status=='temporarily_closed')>🟠 Paused</option>
                                    <option value="closed" @selected($restaurant->operational_status=='closed')>🔴 Closed</option>
                                </select>
                            </div>
                        </form>

                        @if($restaurant->onboarding_status !== 'active')
                            <form method="post" action="{{ route('admin.food.restaurants.approve', $restaurant->id) }}" class="d-inline mb-2 mr-2" onsubmit="return confirm('Approve and activate this partner?');">
                                @csrf
                                <button class="btn btn-sm btn-success"><i class="fa fa-check mr-1"></i> Approve Partner</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-info mb-2 mr-2" data-toggle="modal" data-target="#docReuploadModal">
                                <i class="fa fa-redo mr-1"></i> Request Doc Re-upload
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger mb-2 mr-2" data-toggle="modal" data-target="#rejectModal">
                                <i class="fa fa-times mr-1"></i> Reject
                            </button>
                        @else
                            <form method="post" action="{{ route('admin.food.restaurants.suspend', $restaurant->id) }}" class="d-inline mb-2 mr-2" onsubmit="return confirm('Suspend this restaurant? It will be removed from customer discovery.');">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger"><i class="fa fa-ban mr-1"></i> Suspend</button>
                            </form>
                        @endif

                        <form method="post" action="{{ route('admin.food.orders.test') }}" class="d-inline mb-2">
                            @csrf
                            <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">
                            <button class="btn btn-sm btn-primary" title="Place a test order for verification"><i class="fa fa-vial mr-1"></i> Test Order</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Metrics Strip -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase font-weight-bold">Delivered Orders</div>
                    <div class="h4 font-weight-bold text-dark mb-0">{{ number_format($totalOrdersDelivered) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase font-weight-bold">Gross Food Sales</div>
                    <div class="h4 font-weight-bold text-success mb-0">₹{{ number_format($totalGrossSales, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase font-weight-bold">Company Due (COD)</div>
                    <div class="h4 font-weight-bold {{ $pendingDueTotal > 0 ? 'text-danger' : 'text-success' }} mb-0">
                        ₹{{ number_format($pendingDueTotal, 2) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase font-weight-bold">Menu Products</div>
                    <div class="h4 font-weight-bold text-primary mb-0">{{ $products->count() }} Items</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Tab Navigation -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <ul class="nav nav-tabs card-header-tabs" id="restaurantTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active font-weight-bold" id="overview-tab" data-toggle="tab" href="#overview" role="tab">
                        <i class="fa fa-info-circle mr-1"></i> Overview & Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="docs-tab" data-toggle="tab" href="#docs" role="tab">
                        <i class="fa fa-file-invoice mr-1"></i> Compliance Documents
                        @php
                            $docCount = ($restaurant->fssai_doc ? 1 : 0) + ($restaurant->gst_doc ? 1 : 0) + ($restaurant->cancelled_cheque ? 1 : 0) + ($restaurant->id_proof ? 1 : 0);
                        @endphp
                        <span class="badge badge-primary ml-1">{{ $docCount }}/4</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="menu-tab" data-toggle="tab" href="#menu" role="tab">
                        <i class="fa fa-utensils mr-1"></i> Menu & Catalog ({{ $products->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="commission-tab" data-toggle="tab" href="#commission" role="tab">
                        <i class="fa fa-percentage mr-1"></i> Commission & Rules
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="dues-tab" data-toggle="tab" href="#dues" role="tab">
                        <i class="fa fa-hand-holding-usd mr-1"></i> COD Dues & Settlements
                        @if($pendingDueTotal > 0)
                            <span class="badge badge-danger ml-1">Due</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="orders-tab" data-toggle="tab" href="#orders" role="tab">
                        <i class="fa fa-receipt mr-1"></i> Orders History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="disputes-tab" data-toggle="tab" href="#disputes" role="tab">
                        <i class="fa fa-exclamation-triangle mr-1"></i> Disputes & Reviews
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="restaurantTabContent">

                <!-- TAB 1: OVERVIEW & EDITABLE PROFILE -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">

                    <!-- Onboarding Fee & Category Verification Card -->
                    <div class="card border-0 bg-light shadow-sm mb-4">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="font-weight-bold text-dark mb-0 mr-3">
                                            @if($restaurant->business_type === 'cloud_kitchen' || optional($restaurant->type)->code === 'cloud_kitchen')
                                                🍳 Cloud Kitchen Partner
                                            @else
                                                🍽️ Actual Restaurant Partner
                                            @endif
                                        </h5>
                                        <span class="badge badge-primary px-2 py-1">
                                            Category: {{ optional($restaurant->type)->name ?: ucwords(str_replace('_', ' ', $restaurant->business_type ?: 'Restaurant')) }}
                                        </span>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <strong>Admin Onboarding Fee:</strong> ₹{{ number_format(optional($restaurant->type)->onboarding_fee ?? 0, 2) }}
                                        <span class="mx-2">•</span>
                                        <strong>Payment Status:</strong>
                                        @if($restaurant->onboarding_fee_paid > 0)
                                            <span class="badge badge-success">Paid (₹{{ number_format($restaurant->onboarding_fee_paid, 2) }})</span>
                                        @elseif($restaurant->onboarding_payment_id)
                                            <span class="badge badge-info">UTR Submitted — Needs Verification</span>
                                        @else
                                            <span class="badge badge-warning">Fee Pending</span>
                                        @endif
                                    </div>
                                    <div class="small text-secondary">
                                        <strong>Transaction / UTR Reference:</strong>
                                        <code>{{ $restaurant->onboarding_payment_id ?: 'None submitted yet' }}</code>
                                    </div>
                                    @php
                                        $latestPayment = $onboardingPayments->first();
                                    @endphp
                                    @if($latestPayment && isset($latestPayment->meta['proof']) && $latestPayment->meta['proof'])
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/' . $latestPayment->meta['proof']) }}" target="_blank" class="btn btn-xs btn-outline-info">
                                                <i class="fa fa-image mr-1"></i> View Payment Receipt Proof
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-5 text-md-right mt-3 mt-md-0">
                                    <div class="d-flex justify-content-md-end align-items-center flex-wrap">
                                        <!-- Approve Onboarding Fee -->
                                        <form method="post" action="{{ route('admin.food.restaurants.verifyOnboardingFee', $restaurant->id) }}" class="d-inline mr-2 mb-1" onsubmit="return confirm('Verify and mark onboarding fee as paid? Partner will be activated.');">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-sm btn-success">
                                                <i class="fa fa-check-circle mr-1"></i> Verify & Approve Fee
                                            </button>
                                        </form>

                                        <!-- Waive Onboarding Fee -->
                                        <form method="post" action="{{ route('admin.food.restaurants.verifyOnboardingFee', $restaurant->id) }}" class="d-inline mr-2 mb-1" onsubmit="return confirm('Waive the onboarding fee for this partner? They will be activated with ₹0 fee.');">
                                            @csrf
                                            <input type="hidden" name="action" value="waive">
                                            <button class="btn btn-sm btn-outline-secondary">
                                                <i class="fa fa-gift mr-1"></i> Waive Fee
                                            </button>
                                        </form>

                                        @if($restaurant->onboarding_payment_id && $restaurant->onboarding_status === 'payment_pending')
                                            <!-- Reject Payment Proof -->
                                            <form method="post" action="{{ route('admin.food.restaurants.verifyOnboardingFee', $restaurant->id) }}" class="d-inline mb-1" onsubmit="return confirm('Reject this payment submission?');">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="fa fa-times-circle mr-1"></i> Reject Proof
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="post" action="{{ route('admin.food.restaurants.profile', $restaurant->id) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="font-weight-bold text-dark border-bottom pb-2 mb-3">Restaurant Information</h5>
                                <div class="form-group">
                                    <label class="font-weight-bold small text-muted">Restaurant Name</label>
                                    <input name="name" value="{{ $restaurant->name }}" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Business Sub-category</label>
                                        <input name="sub_category" value="{{ $restaurant->sub_category }}" class="form-control" placeholder="e.g. Biryani, Bakery, Cafe">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Avg Prep Time (Minutes)</label>
                                        <input type="number" name="avg_prep_minutes" value="{{ $restaurant->avg_prep_minutes }}" class="form-control" required>
                                    </div>
                                </div>
                                @if(!empty($restaurant->cuisines) && is_array($restaurant->cuisines))
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small text-muted">Cuisines Served (Multi-Select)</label>
                                    <div>
                                        @foreach($restaurant->cuisines as $c)
                                            <span class="badge badge-info px-2 py-1 mr-1 mb-1 font-weight-normal" style="font-size: 13px;">🍽️ {{ $c }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Opening Time</label>
                                        <input type="time" name="opening_time" value="{{ $restaurant->opening_time }}" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Closing Time</label>
                                        <input type="time" name="closing_time" value="{{ $restaurant->closing_time }}" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Delivery Radius (KM)</label>
                                        <input type="number" step="0.5" name="delivery_radius_km" value="{{ $restaurant->delivery_radius_km }}" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Min Order Amount (₹)</label>
                                        <input type="number" name="min_order_amount" value="{{ $restaurant->min_order_amount }}" class="form-control">
                                    </div>
                                </div>

                                <div class="card bg-light border-0 p-3 mb-3">
                                    <div class="font-weight-bold small text-muted mb-2">Service Capabilities</div>
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="pure_veg" name="pure_veg" value="1" @checked($restaurant->pure_veg)>
                                        <label class="custom-control-label font-weight-bold text-success" for="pure_veg">Pure Veg Restaurant 🟢</label>
                                    </div>
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="delivery_available" name="delivery_available" value="1" @checked($restaurant->delivery_available)>
                                        <label class="custom-control-label" for="delivery_available">Door Delivery Available</label>
                                    </div>
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="takeaway_available" name="takeaway_available" value="1" @checked($restaurant->takeaway_available)>
                                        <label class="custom-control-label" for="takeaway_available">Takeaway Available</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="font-weight-bold text-dark border-bottom pb-2 mb-3">Location & Coordinates</h5>
                                <div class="form-group">
                                    <label class="font-weight-bold small text-muted">Complete Address</label>
                                    <textarea name="address" rows="2" class="form-control">{{ $restaurant->address }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Landmark</label>
                                        <input name="landmark" value="{{ $restaurant->landmark }}" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">City</label>
                                        <input name="city" value="{{ $restaurant->city }}" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">State</label>
                                        <input name="state" value="{{ $restaurant->state }}" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Pincode</label>
                                        <input name="pincode" value="{{ $restaurant->pincode }}" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Latitude</label>
                                        <input name="latitude" value="{{ $restaurant->latitude }}" class="form-control" placeholder="e.g. 28.6139">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Longitude</label>
                                        <input name="longitude" value="{{ $restaurant->longitude }}" class="form-control" placeholder="e.g. 77.2090">
                                    </div>
                                </div>

                                <h5 class="font-weight-bold text-dark border-bottom pb-2 mt-2 mb-3">Owner Contact Details</h5>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Owner Name</label>
                                        <input name="owner_name" value="{{ $restaurant->owner_name }}" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Owner Phone</label>
                                        <input name="owner_phone" value="{{ $restaurant->owner_phone }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3 text-right">
                            <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                                <i class="fa fa-save mr-1"></i> Save Changes to Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: COMPLIANCE DOCUMENTS VERIFICATION HUB -->
                <div class="tab-pane fade" id="docs" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="font-weight-bold text-dark mb-1">Legal & Compliance Verification</h5>
                            <p class="text-muted small mb-0">Carefully inspect partner food licenses and banking details before approval.</p>
                        </div>
                        <button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#docReuploadModal">
                            <i class="fa fa-redo mr-1"></i> Request Document Resubmission
                        </button>
                    </div>

                    @php
                        $docStatuses = $restaurant->doc_status ?: [];
                    @endphp

                    <div class="row">
                        <!-- FSSAI License Card -->
                        <div class="col-md-6 mb-4">
                            <div class="card border h-100 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold"><i class="fa fa-certificate text-warning mr-1"></i> FSSAI Food License</span>
                                    @if(isset($docStatuses['fssai']['status']) && $docStatuses['fssai']['status'] === 'approved')
                                        <span class="badge badge-success">Verified</span>
                                    @elseif(isset($docStatuses['fssai']['status']) && $docStatuses['fssai']['status'] === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-warning">Needs Verification</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <span class="text-muted small">FSSAI Number:</span>
                                        <strong class="text-dark d-block">{{ $restaurant->fssai_number ?: 'Not Provided' }}</strong>
                                    </div>
                                    @if($restaurant->fssai_doc)
                                        <div class="p-3 bg-light rounded text-center mb-3">
                                            <a href="{{ asset('storage/' . $restaurant->fssai_doc) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fa fa-external-link-alt mr-1"></i> View FSSAI Document
                                            </a>
                                        </div>
                                    @else
                                        <div class="alert alert-warning py-2 small mb-3">No FSSAI file uploaded.</div>
                                    @endif

                                    <div class="border-top pt-2 d-flex justify-content-between align-items-center">
                                        <form method="post" action="{{ route('admin.food.restaurants.verifyDoc', $restaurant->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="doc_type" value="fssai">
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn btn-sm btn-outline-success"><i class="fa fa-check mr-1"></i> Mark Verified</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.food.restaurants.verifyDoc', $restaurant->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="doc_type" value="fssai">
                                            <input type="hidden" name="status" value="rejected">
                                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-times mr-1"></i> Reject Doc</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GST Card -->
                        <div class="col-md-6 mb-4">
                            <div class="card border h-100 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold"><i class="fa fa-file-invoice text-info mr-1"></i> GST Registration</span>
                                    @if(isset($docStatuses['gst']['status']) && $docStatuses['gst']['status'] === 'approved')
                                        <span class="badge badge-success">Verified</span>
                                    @elseif(isset($docStatuses['gst']['status']) && $docStatuses['gst']['status'] === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-light border">Optional / Pending</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <span class="text-muted small">GSTIN:</span>
                                        <strong class="text-dark d-block">{{ $restaurant->gst_number ?: 'Not Provided' }}</strong>
                                    </div>
                                    @if($restaurant->gst_doc)
                                        <div class="p-3 bg-light rounded text-center mb-3">
                                            <a href="{{ asset('storage/' . $restaurant->gst_doc) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fa fa-external-link-alt mr-1"></i> View GST Certificate
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-muted small mb-3">No GST document attached.</div>
                                    @endif

                                    <div class="border-top pt-2 d-flex justify-content-between align-items-center">
                                        <form method="post" action="{{ route('admin.food.restaurants.verifyDoc', $restaurant->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="doc_type" value="gst">
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn btn-sm btn-outline-success"><i class="fa fa-check mr-1"></i> Mark Verified</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.food.restaurants.verifyDoc', $restaurant->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="doc_type" value="gst">
                                            <input type="hidden" name="status" value="rejected">
                                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-times mr-1"></i> Reject Doc</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Account & Cheque Card -->
                        <div class="col-md-6 mb-4">
                            <div class="card border h-100 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold"><i class="fa fa-university text-success mr-1"></i> Bank Account & Payout Details</span>
                                    @if(isset($docStatuses['cheque']['status']) && $docStatuses['cheque']['status'] === 'approved')
                                        <span class="badge badge-success">Verified</span>
                                    @else
                                        <span class="badge badge-warning">Needs Verification</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <span class="text-muted small">Bank Name:</span>
                                            <strong class="text-dark d-block">{{ $restaurant->bank_name ?: 'N/A' }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted small">Account Holder:</span>
                                            <strong class="text-dark d-block">{{ $restaurant->bank_account_name ?: 'N/A' }}</strong>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <span class="text-muted small">Account Number:</span>
                                            <strong class="text-dark d-block">{{ $restaurant->bank_account_number ?: 'N/A' }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted small">IFSC Code:</span>
                                            <strong class="text-dark d-block">{{ $restaurant->bank_ifsc ?: 'N/A' }}</strong>
                                        </div>
                                    </div>

                                    @if($restaurant->cancelled_cheque)
                                        <div class="p-3 bg-light rounded text-center mb-3">
                                            <a href="{{ asset('storage/' . $restaurant->cancelled_cheque) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fa fa-external-link-alt mr-1"></i> View Cancelled Cheque / Passbook
                                            </a>
                                        </div>
                                    @else
                                        <div class="alert alert-warning py-2 small mb-3">No cancelled cheque file attached.</div>
                                    @endif

                                    <div class="border-top pt-2 d-flex justify-content-between align-items-center">
                                        <form method="post" action="{{ route('admin.food.restaurants.verifyDoc', $restaurant->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="doc_type" value="cheque">
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn btn-sm btn-outline-success"><i class="fa fa-check mr-1"></i> Mark Verified</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.food.restaurants.verifyDoc', $restaurant->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="doc_type" value="cheque">
                                            <input type="hidden" name="status" value="rejected">
                                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-times mr-1"></i> Reject Doc</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Owner ID Proof & PAN Card -->
                        <div class="col-md-6 mb-4">
                            <div class="card border h-100 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold"><i class="fa fa-id-card text-primary mr-1"></i> PAN Card & Owner ID Proof</span>
                                    @if(isset($docStatuses['id_proof']['status']) && $docStatuses['id_proof']['status'] === 'approved')
                                        <span class="badge badge-success">Verified</span>
                                    @else
                                        <span class="badge badge-warning">Needs Verification</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <span class="text-muted small">PAN Number:</span>
                                        <strong class="text-dark d-block">{{ $restaurant->pan_number ?: 'Not Provided' }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-around p-3 bg-light rounded mb-3">
                                        @if($restaurant->id_proof)
                                            <a href="{{ asset('storage/' . $restaurant->id_proof) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fa fa-external-link-alt mr-1"></i> View ID Proof
                                            </a>
                                        @endif
                                        @if($restaurant->business_proof)
                                            <a href="{{ asset('storage/' . $restaurant->business_proof) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                                <i class="fa fa-external-link-alt mr-1"></i> View PAN/Business Proof
                                            </a>
                                        @endif
                                    </div>

                                    <div class="border-top pt-2 d-flex justify-content-between align-items-center">
                                        <form method="post" action="{{ route('admin.food.restaurants.verifyDoc', $restaurant->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="doc_type" value="id_proof">
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn btn-sm btn-outline-success"><i class="fa fa-check mr-1"></i> Mark Verified</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.food.restaurants.verifyDoc', $restaurant->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="doc_type" value="id_proof">
                                            <input type="hidden" name="status" value="rejected">
                                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-times mr-1"></i> Reject Doc</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: MENU & CATALOG MANAGEMENT (ADMIN SUPERPOWER) -->
                <div class="tab-pane fade" id="menu" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="font-weight-bold text-dark mb-1">Restaurant Menu & Products Catalog</h5>
                            <p class="text-muted small mb-0">Admin has full control to add items, change prices, or toggle in/out of stock.</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm font-weight-bold" data-toggle="modal" data-target="#addProductModal">
                            <i class="fa fa-plus mr-1"></i> Add Food Item
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small text-muted font-weight-bold">
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Food Type</th>
                                    <th>Base Price</th>
                                    <th>Discount Price</th>
                                    <th>Stock Availability</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $p)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($p->image)
                                                    <img src="{{ asset('storage/' . $p->image) }}" class="rounded mr-3 border" style="width: 44px; height: 44px; object-fit: cover;">
                                                @else
                                                    <div class="rounded bg-light border mr-3 d-flex align-items-center justify-content-center text-muted" style="width: 44px; height: 44px;">
                                                        <i class="fa fa-utensils"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-weight-bold text-dark">{{ $p->name }}</div>
                                                    <small class="text-muted">{{ Str::limit($p->description, 40) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border">{{ $p->category ? $p->category->name : 'Uncategorized' }}</span>
                                        </td>
                                        <td>
                                            @if($p->food_type === 'veg')
                                                <span class="badge badge-success">Veg 🟢</span>
                                            @elseif($p->food_type === 'egg')
                                                <span class="badge badge-warning">Egg 🟡</span>
                                            @else
                                                <span class="badge badge-danger">Non-Veg 🔴</span>
                                            @endif
                                        </td>
                                        <td class="font-weight-bold text-dark">₹{{ number_format($p->price, 2) }}</td>
                                        <td>
                                            @if($p->discount_price)
                                                <span class="text-success font-weight-bold">₹{{ number_format($p->discount_price, 2) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form method="post" action="{{ route('admin.food.restaurants.toggleStock', ['id' => $restaurant->id, 'productId' => $p->id]) }}" class="d-inline">
                                                @csrf
                                                @if($p->is_available)
                                                    <button class="btn btn-sm btn-outline-success py-0 px-2 font-weight-bold" title="Click to mark Out of Stock">
                                                        ● In Stock
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-danger py-0 px-2 font-weight-bold" title="Click to mark Available">
                                                        ○ Out of Stock
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                        <td class="text-right">
                                            <form method="post" action="{{ route('admin.food.restaurants.deleteProduct', ['id' => $restaurant->id, 'productId' => $p->id]) }}" class="d-inline" onsubmit="return confirm('Delete this menu item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Delete Product"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No food items added to this restaurant's menu yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 4: COMMISSION & PRICING RULES -->
                <div class="tab-pane fade" id="commission" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border shadow-sm mb-4">
                                <div class="card-header bg-light font-weight-bold">
                                    <i class="fa fa-sliders-h text-primary mr-1"></i> Custom Partner Commission Override
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">
                                        Set a specific platform commission percentage for this restaurant. This overrides global category or type commission rules.
                                    </p>
                                    <form method="post" action="{{ route('admin.food.restaurants.commission', $restaurant->id) }}">
                                        @csrf
                                        <div class="form-group">
                                            <label class="font-weight-bold small text-muted">Commission Rate (%)</label>
                                            <div class="input-group">
                                                <input type="number" step="0.1" name="commission_rate" value="{{ $restaurant->custom_commission_rate ?: 15 }}" class="form-control font-weight-bold text-primary" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text font-weight-bold">%</span>
                                                </div>
                                            </div>
                                            <small class="text-muted">Example: Enter 12.5 for 12.5% platform commission on gross food items.</small>
                                        </div>

                                        <button type="submit" class="btn btn-primary font-weight-bold">
                                            <i class="fa fa-check mr-1"></i> Update Commission Rule
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border bg-light shadow-sm">
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-dark mb-2">How Commission Works in Fiinway Food</h6>
                                    <ul class="text-muted small pl-3 mb-0">
                                        <li class="mb-1"><strong>Online Prepaid Orders</strong>: Platform fee and commission are retained automatically before bank settlement.</li>
                                        <li class="mb-1"><strong>Cash on Delivery (COD)</strong>: Restaurant/Rider collects cash directly from the customer. The platform commission is credited as <strong>Company Due</strong> that the partner must clear.</li>
                                        <li><strong>Markup Pricing</strong>: If configured in global rules, customer menu price includes platform markup transparently.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: COD DUES & SETTLEMENTS -->
                <div class="tab-pane fade" id="dues" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="font-weight-bold text-dark mb-1">COD Company Dues & Settlement Reconciliation</h5>
                            <p class="text-muted small mb-0">Inspect and approve partner payments for commission accumulated on COD orders.</p>
                        </div>
                        <div class="h4 font-weight-bold {{ $pendingDueTotal > 0 ? 'text-danger' : 'text-success' }} mb-0">
                            Total Due: ₹{{ number_format($pendingDueTotal, 2) }}
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small text-muted font-weight-bold">
                                <tr>
                                    <th>ID</th>
                                    <th>Due Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment Ref</th>
                                    <th>Date</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($duePayments as $dp)
                                    <tr>
                                        <td>#{{ $dp->id }}</td>
                                        <td><span class="badge badge-light border">{{ ucfirst($dp->due_type) }}</span></td>
                                        <td class="font-weight-bold text-dark">₹{{ number_format($dp->amount, 2) }}</td>
                                        <td>
                                            @if($dp->status === 'paid')
                                                <span class="badge badge-success">Settled / Paid</span>
                                            @else
                                                <span class="badge badge-warning">Pending Payment</span>
                                            @endif
                                        </td>
                                        <td>{{ $dp->payment_ref ?: '—' }}</td>
                                        <td>{{ $dp->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="text-right">
                                            @if($dp->status !== 'paid')
                                                <form method="post" action="{{ route('admin.food.restaurants.approveDue', ['id' => $restaurant->id, 'paymentId' => $dp->id]) }}" class="d-inline" onsubmit="return confirm('Mark this due payment as approved and settled?');">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success font-weight-bold">
                                                        <i class="fa fa-check mr-1"></i> Approve Payment
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-success small font-weight-bold"><i class="fa fa-check-circle mr-1"></i> Cleared</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No due payment records found for this restaurant.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 6: ORDERS HISTORY -->
                <div class="tab-pane fade" id="orders" role="tabpanel">
                    <h5 class="font-weight-bold text-dark mb-3">Recent Orders Dispatched</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small text-muted font-weight-bold">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Order Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $o)
                                    <tr>
                                        <td class="font-weight-bold text-primary">{{ $o->order_number }}</td>
                                        <td>
                                            <div>{{ $o->customer_name }}</div>
                                            <small class="text-muted">{{ $o->customer_phone }}</small>
                                        </td>
                                        <td class="font-weight-bold text-dark">₹{{ number_format($o->customer_payable, 2) }}</td>
                                        <td>
                                            <span class="badge badge-light border text-uppercase">{{ $o->payment_method }}</span>
                                        </td>
                                        <td>
                                            @if($o->order_status === 'delivered')
                                                <span class="badge badge-success">Delivered</span>
                                            @elseif(in_array($o->order_status, ['cancelled', 'rejected']))
                                                <span class="badge badge-danger">{{ ucfirst($o->order_status) }}</span>
                                            @else
                                                <span class="badge badge-warning">{{ ucfirst($o->order_status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $o->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No orders found for this restaurant yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 7: DISPUTES & REVIEWS -->
                <div class="tab-pane fade" id="disputes" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark mb-3">Customer Disputes</h5>
                            @forelse($disputes as $d)
                                <div class="card border shadow-sm mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-1">
                                            <strong class="text-danger">Dispute #{{ $d->id }}</strong>
                                            <span class="badge badge-warning">{{ ucfirst($d->status) }}</span>
                                        </div>
                                        <p class="mb-2 text-muted small">{{ $d->reason }}</p>
                                        <div class="small text-dark font-weight-bold">Claimed Amount: ₹{{ number_format($d->claimed_amount, 2) }}</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">No disputes filed against this restaurant.</p>
                            @endforelse
                        </div>

                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-dark mb-3">Customer Ratings & Reviews</h5>
                            @forelse($reviews as $rev)
                                <div class="card border shadow-sm mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-1">
                                            <div>
                                                @for($i=1; $i<=5; $i++)
                                                    <i class="fa fa-star {{ $i <= $rev->rating ? 'text-warning' : 'text-muted' }} small"></i>
                                                @endfor
                                            </div>
                                            <small class="text-muted">{{ $rev->created_at->format('d M Y') }}</small>
                                        </div>
                                        <p class="mb-0 text-dark small">{{ $rev->comment ?: 'No written review' }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">No customer reviews yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- MODAL: Request Document Re-upload -->
<div class="modal fade" id="docReuploadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('admin.food.restaurants.requestReupload', $restaurant->id) }}">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fa fa-redo mr-1"></i> Request Document Resubmission</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Select the documents that failed compliance checks. The restaurant partner will be notified in their PWA app to re-upload them.</p>
                    <div class="card bg-light border-0 p-3 mb-3">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="chk_fssai" name="docs_needed[]" value="FSSAI Food License">
                            <label class="custom-control-label font-weight-bold" for="chk_fssai">FSSAI Food License Certificate</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="chk_gst" name="docs_needed[]" value="GST Registration Certificate">
                            <label class="custom-control-label font-weight-bold" for="chk_gst">GST Certificate</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="chk_pan" name="docs_needed[]" value="PAN Card / Business Proof">
                            <label class="custom-control-label font-weight-bold" for="chk_pan">PAN Card Document</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="chk_cheque" name="docs_needed[]" value="Cancelled Cheque / Bank Passbook">
                            <label class="custom-control-label font-weight-bold" for="chk_cheque">Bank Cancelled Cheque / Passbook</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chk_id" name="docs_needed[]" value="Owner Identity Proof">
                            <label class="custom-control-label font-weight-bold" for="chk_id">Owner ID Proof (Aadhaar/DL)</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small text-muted">Feedback / Correction Instructions for Partner</label>
                        <textarea name="reason" rows="3" class="form-control" placeholder="e.g. Uploaded FSSAI certificate is expired. Please re-upload active registration." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm font-weight-bold">Send Resubmission Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Reject Application -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('admin.food.restaurants.reject', $restaurant->id) }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fa fa-times mr-1"></i> Reject Restaurant Application</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small text-muted">Rejection Reason</label>
                        <textarea name="reason" rows="3" class="form-control" placeholder="Provide specific reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold">Reject Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Add Menu Product -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('admin.food.restaurants.saveProduct', $restaurant->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fa fa-plus mr-1"></i> Add Food Item to Menu</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small text-muted">Item Name</label>
                        <input name="name" class="form-control" placeholder="e.g. Butter Chicken Masala" required>
                    </div>
                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="font-weight-bold small text-muted">Category</label>
                            <select name="category_id" class="form-control custom-select">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 form-group">
                            <label class="font-weight-bold small text-muted">Food Type</label>
                            <select name="food_type" class="form-control custom-select" required>
                                <option value="veg">Veg 🟢</option>
                                <option value="non_veg">Non-Veg 🔴</option>
                                <option value="egg">Contains Egg 🟡</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="font-weight-bold small text-muted">Base Price (₹)</label>
                            <input type="number" step="0.5" name="price" class="form-control" placeholder="199" required>
                        </div>
                        <div class="col-6 form-group">
                            <label class="font-weight-bold small text-muted">Discount Price (₹)</label>
                            <input type="number" step="0.5" name="discount_price" class="form-control" placeholder="Optional">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-muted">Description</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Portion size, ingredients..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-muted">Product Image</label>
                        <input type="file" name="image" class="form-control-file">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold">Save Food Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

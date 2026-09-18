@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <!-- Breadcrumb & Header -->
    <div class="row page-titles mb-3">
        <div class="col-md-6 align-self-center">
            <h3 class="text-themecolor mb-0 font-weight-bold">
                <i class="mdi mdi-plus-box text-success mr-2"></i> Create Driver & Partner Kit
            </h3>
            <small class="text-muted">Configure starter packages, apparel, equipment, and automated partner onboarding kits</small>
        </div>
        <div class="col-md-6 align-self-center text-right">
            <a href="{{ route('driver-kits.index') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm font-weight-bold mr-2">
                <i class="mdi mdi-arrow-left mr-1"></i> Back to Kits
            </a>
            <ol class="breadcrumb d-inline-block p-0 bg-transparent mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('driver-kits.index') }}">Partner Kits</a></li>
                <li class="breadcrumb-item active">Create Kit</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <h5 class="font-weight-bold mb-2"><i class="mdi mdi-alert-circle mr-1"></i> Please fix the following errors:</h5>
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form id="createKitForm" action="{{ route('driver-kits.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="status" id="kitStatusInput" value="published">

            <div class="row">
                <!-- Left Column: Step 1, 2, 3, 4, 6 -->
                <div class="col-xl-8 col-lg-7">
                    <!-- Step 2: Basic Details -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                            <span class="badge badge-primary rounded-circle mr-2 px-2 py-1 font-12">1</span>
                            <h5 class="card-title font-weight-bold text-dark m-0">Basic Details</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark font-13">
                                            Kit Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="title" id="kitTitleInput" class="form-control rounded-lg" value="{{ old('title') }}" placeholder="e.g. Bike Taxi Onboarding Starter Kit" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark font-13">
                                            Select Service / Partner Type <span class="text-danger">*</span>
                                        </label>
                                        <select name="category_code" id="kitCategorySelect" class="form-control rounded-lg" required>
                                            <option value="">-- Choose Category --</option>
                                            @foreach($partnerTypes as $code => $label)
                                                <option value="{{ $code }}" {{ old('category_code') == $code ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark font-13">Description</label>
                                        <textarea name="description" id="kitDescInput" rows="3" class="form-control rounded-lg" placeholder="Explain what is inside the kit and why partner needs it...">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark font-13">Kit Main Image</label>
                                        <div class="custom-file mb-2">
                                            <input type="file" name="image" id="kitImageFile" class="custom-file-input" accept="image/*" onchange="previewKitImage(this)">
                                            <label class="custom-file-label rounded-lg font-12" for="kitImageFile">Choose file</label>
                                        </div>
                                        <small class="text-muted d-block font-11">Recommended: 800x800 PNG or JPG (Max 5MB)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark font-13">SKU (Stock Keeping Unit)</label>
                                        <input type="text" name="sku" id="kitSkuInput" class="form-control rounded-lg" value="{{ old('sku') }}" placeholder="Auto-generated if empty">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark font-13">Stock Quantity</label>
                                        <input type="number" name="stock_quantity" class="form-control rounded-lg" value="{{ old('stock_quantity', 100) }}" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0 pt-md-4">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="is_active" class="custom-control-input" id="isActiveCheck" value="1" checked>
                                            <label class="custom-control-label font-weight-bold text-dark font-13" for="isActiveCheck">
                                                Active / Available to Drivers
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3, 4, 5: Select Products & Manage Options -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-primary rounded-circle mr-2 px-2 py-1 font-12">2</span>
                                <div>
                                    <h5 class="card-title font-weight-bold text-dark m-0">Products Included in Kit</h5>
                                    <small class="text-muted">Add existing products or custom branded items, set Free/Paid and Mandatory/Optional</small>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm font-weight-bold mr-2" data-toggle="modal" data-target="#selectProductModal" data-bs-toggle="modal" data-bs-target="#selectProductModal" onclick="openProductSelectModal()">
                                    <i class="mdi mdi-plus-box mr-1"></i> Select from Products
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 shadow-sm font-weight-bold" onclick="addCustomProductRow()">
                                    <i class="mdi mdi-playlist-plus mr-1"></i> Add Custom Item
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0" id="productsTable">
                                    <thead class="bg-light text-dark font-12 text-uppercase">
                                        <tr>
                                            <th style="width: 50px;">Item</th>
                                            <th>Product Name</th>
                                            <th style="width: 120px;">Size / Variant</th>
                                            <th style="width: 100px;">Qty</th>
                                            <th style="width: 130px;">Free / Paid</th>
                                            <th style="width: 120px;">Price (₹)</th>
                                            <th style="width: 130px;">Mandatory</th>
                                            <th style="width: 60px;" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selectedProductsBody">
                                        <!-- Populated dynamically -->
                                    </tbody>
                                </table>
                            </div>

                            <div id="noProductsNotice" class="text-center py-5">
                                <div class="text-muted mb-3">
                                    <i class="mdi mdi-package-variant-closed" style="font-size: 48px; opacity: 0.4;"></i>
                                </div>
                                <h6 class="font-weight-bold text-secondary">No products added to this kit yet</h6>
                                <p class="text-muted font-12 mb-3">Pick products from your catalog or click "Add Custom Item" to insert t-shirts, bags, helmets, etc.</p>
                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-toggle="modal" data-target="#selectProductModal">
                                    <i class="mdi mdi-plus mr-1"></i> Browse Catalog
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 6: Kit Pricing -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                            <span class="badge badge-primary rounded-circle mr-2 px-2 py-1 font-12">3</span>
                            <h5 class="card-title font-weight-bold text-dark m-0">Kit Pricing & Margin</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark font-13">
                                            Total Product Cost (₹)
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-right-0 font-weight-bold">₹</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" name="cost_price" id="costPriceInput" class="form-control rounded-right" value="{{ old('cost_price', 0) }}" placeholder="e.g. 2000" oninput="calculateMargin()">
                                        </div>
                                        <small class="text-muted font-11">Internal inventory/procurement cost</small>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark font-13">
                                            Total MRP (₹)
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-right-0 font-weight-bold">₹</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" name="mrp" id="mrpInput" class="form-control rounded-right" value="{{ old('mrp', 2999) }}" placeholder="e.g. 2999" oninput="calculateMargin()">
                                        </div>
                                        <small class="text-muted font-11">Printed / Maximum Retail Price</small>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark font-13">
                                            Offer Price (₹) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-right-0 font-weight-bold text-primary">₹</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" name="price" id="offerPriceInput" class="form-control rounded-right font-weight-bold text-primary" value="{{ old('price', 1499) }}" placeholder="e.g. 1499" required oninput="calculateMargin()">
                                        </div>
                                        <small class="text-muted font-11">Amount driver pays at checkout</small>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark font-13">
                                            Cashback (₹)
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-right-0 font-weight-bold text-success">₹</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" name="cashback_amount" id="cashbackInput" class="form-control rounded-right text-success font-weight-bold" value="{{ old('cashback_amount', 100) }}" placeholder="e.g. 100" oninput="calculateMargin()">
                                        </div>
                                        <small class="text-muted font-11">Credited to wallet upon kit delivery</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Real-time margin / discount preview banner -->
                            <div class="p-3 rounded-lg border d-flex flex-wrap align-items-center justify-content-between" style="background-color: #f8fafc; border-color: #e2e8f0;">
                                <div class="mr-3 mb-2 mb-md-0">
                                    <span class="text-muted font-12 d-block">Driver Discount:</span>
                                    <span class="font-weight-bold text-danger font-16" id="discountPercentText">50% OFF</span>
                                </div>
                                <div class="mr-3 mb-2 mb-md-0">
                                    <span class="text-muted font-12 d-block">Net Realization (After Cashback):</span>
                                    <span class="font-weight-bold text-dark font-16" id="netRealizationText">₹1,399.00</span>
                                </div>
                                <div>
                                    <span class="text-muted font-12 d-block">Estimated Gross Margin:</span>
                                    <span class="font-weight-bold text-success font-16" id="grossMarginText">--</span>
                                </div>
                            </div>

                            <!-- Mandatory Popup Rule -->
                            <div class="mt-4 pt-3 border-top">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_compulsory" class="custom-control-input" id="isCompulsorySwitch" value="1" checked onchange="updatePreviewCard()">
                                    <label class="custom-control-label font-weight-bold font-13 text-dark" for="isCompulsorySwitch">
                                        Mandatory Kit Popup on Driver App
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1 pl-4">
                                    If enabled, verified drivers in this category will receive an unclosable or mandatory prompt to purchase their starter kit before accepting rides/jobs.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Step 7 - Kit Preview & Step 8 - Save / Publish -->
                <div class="col-xl-4 col-lg-5">
                    <!-- Live Kit Preview Card -->
                    <div class="card shadow-sm border-0 mb-4 sticky-top" style="border-radius: 16px; top: 80px; z-index: 10;">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="card-title font-weight-bold text-dark m-0">
                                <i class="mdi mdi-eye text-primary mr-1"></i> Live Kit Preview
                            </h5>
                            <span class="badge badge-light border text-muted font-11">App / Checkout View</span>
                        </div>
                        <div class="card-body p-4">
                            <!-- Driver Checkout Simulation Card -->
                            <div class="border rounded-lg overflow-hidden bg-white shadow-sm" style="border-color: #e2e8f0;">
                                <!-- Kit Image Box -->
                                <div style="height: 190px; background-color: #f1f5f9; position: relative; overflow: hidden;" class="d-flex align-items-center justify-content-center">
                                    <img id="previewKitImg" src="{{ asset('assets/images/placeholder_kit.png') }}" alt="Kit Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                    <div id="previewKitPlaceholder" class="text-center p-3 text-muted">
                                        <i class="mdi mdi-tshirt-crew" style="font-size: 54px; opacity: 0.35;"></i>
                                        <div class="font-12 mt-1">Upload Kit Image</div>
                                    </div>
                                    <span class="badge badge-primary px-3 py-1 font-weight-bold position-absolute" style="top: 12px; left: 12px; border-radius: 20px;" id="previewCategoryBadge">
                                        SERVICE PARTNER
                                    </span>
                                    <span class="badge badge-danger px-2 py-1 font-weight-bold position-absolute" style="top: 12px; right: 12px; border-radius: 20px;" id="previewMandatoryBadge">
                                        MANDATORY
                                    </span>
                                </div>

                                <!-- Kit Content Info -->
                                <div class="p-3">
                                    <h5 class="font-weight-bold text-dark mb-1" id="previewTitle">Kit Title Here</h5>
                                    <p class="text-muted font-12 mb-3" id="previewDesc" style="min-height: 36px;">Short description explaining the kit...</p>

                                    <!-- Price Breakdown -->
                                    <div class="d-flex align-items-baseline mb-2">
                                        <h3 class="font-weight-bold text-success mb-0 mr-2" id="previewOfferPrice">₹1,499</h3>
                                        <del class="text-muted font-14 mr-2" id="previewMrp">₹2,999</del>
                                        <span class="badge badge-warning text-dark font-weight-bold font-11" id="previewSaveBadge">50% OFF</span>
                                    </div>

                                    <!-- Cashback Callout -->
                                    <div class="rounded p-2 mb-3 d-flex align-items-center" style="background-color: #ecfdf5; border: 1px dashed #a7f3d0;" id="previewCashbackBox">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-2" style="width: 22px; height: 22px; font-size: 11px; font-weight: bold;">₹</div>
                                        <div class="font-11 text-success font-weight-bold" id="previewCashbackText">
                                            ₹100 Wallet Cashback upon delivery
                                        </div>
                                    </div>

                                    <!-- Inclusions List in Preview -->
                                    <div class="border-top pt-2">
                                        <small class="text-uppercase text-muted font-weight-bold font-11 d-block mb-2">
                                            Included in this kit:
                                        </small>
                                        <div id="previewInclusionsList" style="max-height: 130px; overflow-y: auto;">
                                            <div class="text-muted font-11 italic">No products added yet</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Save & Publish Action Buttons -->
                            <div class="mt-4">
                                <div class="row">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-outline-secondary btn-block rounded-pill font-weight-bold py-2 shadow-sm" onclick="submitForm('draft')">
                                            <i class="mdi mdi-content-save-outline mr-1"></i> Save Draft
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" class="btn btn-success btn-block rounded-pill font-weight-bold py-2 shadow-sm" onclick="submitForm('published')">
                                            <i class="mdi mdi-rocket mr-1"></i> Publish Kit
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted text-center d-block mt-2 font-11">
                                    Published kits appear immediately on Driver App & Checkout
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Select Product from Catalog -->
<div class="modal fade" id="selectProductModal" tabindex="-1" role="dialog" aria-labelledby="selectProductModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title font-weight-bold text-dark" id="selectProductModalTitle">
                    <i class="mdi mdi-package-variant text-primary mr-1"></i> Select Products for Kit
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="closeProductSelectModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- Search bar -->
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-magnify"></i></span>
                    </div>
                    <input type="text" id="productSearchInput" class="form-control border-left-0" placeholder="Search by product name, brand or category..." oninput="filterCatalogProducts(this.value)">
                </div>

                <!-- Products Grid -->
                <div class="row" id="catalogProductsGrid" style="max-height: 420px; overflow-y: auto;">
                    @forelse($availableProducts as $prod)
                        @php
                            $prodImg = $prod->primaryImage ? $prod->primaryImage->image_path : ($prod->images->first() ? $prod->images->first()->image_path : '');
                        @endphp
                        <div class="col-md-6 mb-3 catalog-prod-item" data-title="{{ strtolower($prod->title) }}">
                            <div class="border rounded-lg p-2 d-flex align-items-center justify-content-between h-100 bg-white hover-shadow">
                                <div class="d-flex align-items-center" style="gap: 10px; max-width: 70%;">
                                    @if($prodImg)
                                        <img src="{{ $prodImg }}" alt="{{ $prod->title }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                            <i class="mdi mdi-image-outline font-20"></i>
                                        </div>
                                    @endif
                                    <div style="overflow: hidden;">
                                        <div class="font-weight-bold text-dark font-13 text-truncate" title="{{ $prod->title }}">{{ $prod->title }}</div>
                                        <div class="text-primary font-weight-bold font-12">₹{{ number_format($prod->price, 2) }}</div>
                                        <small class="text-muted font-11">Stock: {{ $prod->stock_quantity ?? 0 }}</small>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 font-weight-bold" onclick="addCatalogProduct({{ $prod->id }}, '{{ addslashes($prod->title) }}', '{{ $prodImg }}', {{ floatval($prod->price) }})">
                                        + Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted">
                            No marketplace products found. You can add custom items directly!
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal" data-bs-dismiss="modal" onclick="closeProductSelectModal()">Done</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Stack Fix: Ensure backdrop doesn't cover modal */
.modal-backdrop {
    z-index: 1050 !important;
}
#selectProductModal {
    z-index: 1060 !important;
}
#selectProductModal .modal-dialog {
    z-index: 1065 !important;
}
.hover-shadow:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-color: #3b82f6 !important;
}
.rounded-lg {
    border-radius: 10px !important;
}
</style>

<script>
let productIndex = 0;
const selectedProducts = [];

function openProductSelectModal() {
    // Append to body if not already to prevent parent stacking-context traps
    const modalEl = document.getElementById('selectProductModal');
    if (modalEl && modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }
    if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
        $('#selectProductModal').modal('show');
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();
    }
}

function closeProductSelectModal() {
    const modalEl = document.getElementById('selectProductModal');
    if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
        $('#selectProductModal').modal('hide');
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const bsModal = bootstrap.Modal.getInstance(modalEl);
        if (bsModal) bsModal.hide();
    }
}

function previewKitImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewKitImg').src = e.target.result;
            document.getElementById('previewKitImg').style.display = 'block';
            document.getElementById('previewKitPlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function filterCatalogProducts(query) {
    const q = query.toLowerCase().trim();
    const items = document.querySelectorAll('.catalog-prod-item');
    items.forEach(it => {
        const title = it.getAttribute('data-title') || '';
        if (title.includes(q) || q === '') {
            it.style.display = 'block';
        } else {
            it.style.display = 'none';
        }
    });
}

function addCatalogProduct(id, title, img, price) {
    appendProductRow({
        id: id,
        name: title,
        image: img,
        variant: 'Standard',
        quantity: 1,
        is_free: 1,
        price: price || 0,
        is_mandatory: 1
    });
    closeProductSelectModal();
}

function addCustomProductRow() {
    appendProductRow({
        id: '',
        name: '',
        image: '',
        variant: 'L',
        quantity: 1,
        is_free: 1,
        price: 0,
        is_mandatory: 1
    });
}

function appendProductRow(item) {
    const idx = productIndex++;
    const row = document.createElement('tr');
    row.id = `prodRow_${idx}`;
    row.innerHTML = `
        <td class="text-center align-middle">
            ${item.image ? `<img src="${item.image}" style="width: 36px; height: 36px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;">` : `<i class="mdi mdi-package text-muted" style="font-size: 24px;"></i>`}
            <input type="hidden" name="products[${idx}][id]" value="${item.id || ''}">
            <input type="hidden" name="products[${idx}][image]" value="${item.image || ''}">
        </td>
        <td>
            <input type="text" name="products[${idx}][name]" class="form-control form-control-sm rounded-lg font-weight-bold" value="${item.name}" placeholder="Product Name (e.g. Fiinway T-Shirt)" required oninput="updatePreviewCard()">
        </td>
        <td>
            <input type="text" name="products[${idx}][variant]" class="form-control form-control-sm rounded-lg" value="${item.variant}" placeholder="e.g. M, L, Standard" oninput="updatePreviewCard()">
        </td>
        <td>
            <input type="number" min="1" name="products[${idx}][quantity]" class="form-control form-control-sm rounded-lg" value="${item.quantity || 1}" oninput="updatePreviewCard()">
        </td>
        <td>
            <select name="products[${idx}][is_free]" class="form-control form-control-sm rounded-lg" onchange="togglePaidPrice(${idx}, this.value)">
                <option value="1" ${item.is_free == 1 ? 'selected' : ''}>Free</option>
                <option value="0" ${item.is_free == 0 ? 'selected' : ''}>Paid</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="products[${idx}][price]" id="prodPrice_${idx}" class="form-control form-control-sm rounded-lg ${item.is_free == 1 ? 'd-none' : ''}" value="${item.price || 0}" placeholder="₹ Price">
        </td>
        <td>
            <select name="products[${idx}][is_mandatory]" class="form-control form-control-sm rounded-lg" onchange="updatePreviewCard()">
                <option value="1" ${item.is_mandatory == 1 ? 'selected' : ''}>Mandatory</option>
                <option value="0" ${item.is_mandatory == 0 ? 'selected' : ''}>Optional</option>
            </select>
        </td>
        <td class="text-center align-middle">
            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-1" onclick="removeProductRow(${idx})" title="Remove item">
                <i class="mdi mdi-trash-can-outline font-16"></i>
            </button>
        </td>
    `;
    document.getElementById('selectedProductsBody').appendChild(row);
    document.getElementById('noProductsNotice').style.display = 'none';
    updatePreviewCard();
}

function togglePaidPrice(idx, val) {
    const input = document.getElementById(`prodPrice_${idx}`);
    if (input) {
        if (val === '0') {
            input.classList.remove('d-none');
            input.required = true;
        } else {
            input.classList.add('d-none');
            input.required = false;
            input.value = '0';
        }
    }
    updatePreviewCard();
}

function removeProductRow(idx) {
    const row = document.getElementById(`prodRow_${idx}`);
    if (row) {
        row.remove();
    }
    const remaining = document.querySelectorAll('#selectedProductsBody tr');
    if (remaining.length === 0) {
        document.getElementById('noProductsNotice').style.display = 'block';
    }
    updatePreviewCard();
}

function calculateMargin() {
    const cost = parseFloat(document.getElementById('costPriceInput').value || 0);
    const mrp = parseFloat(document.getElementById('mrpInput').value || 0);
    const offer = parseFloat(document.getElementById('offerPriceInput').value || 0);
    const cashback = parseFloat(document.getElementById('cashbackInput').value || 0);

    // Discount percentage
    let discount = 0;
    if (mrp > 0 && offer < mrp) {
        discount = Math.round(((mrp - offer) / mrp) * 100);
    }
    document.getElementById('discountPercentText').textContent = discount > 0 ? `${discount}% OFF` : '0% OFF';
    document.getElementById('previewSaveBadge').textContent = discount > 0 ? `${discount}% OFF` : 'Best Value';

    // Net realization
    const net = Math.max(0, offer - cashback);
    document.getElementById('netRealizationText').textContent = '₹' + net.toLocaleString('en-IN', { minimumFractionDigits: 2 });

    // Margin
    if (cost > 0) {
        const margin = net - cost;
        const marginPct = Math.round((margin / cost) * 100);
        document.getElementById('grossMarginText').textContent = `₹${margin.toFixed(2)} (${marginPct}%)`;
        document.getElementById('grossMarginText').className = margin >= 0 ? 'font-weight-bold text-success font-16' : 'font-weight-bold text-danger font-16';
    } else {
        document.getElementById('grossMarginText').textContent = 'Cost not set';
        document.getElementById('grossMarginText').className = 'font-weight-bold text-muted font-16';
    }

    // Live preview pricing
    document.getElementById('previewOfferPrice').textContent = '₹' + Math.round(offer).toLocaleString('en-IN');
    document.getElementById('previewMrp').textContent = mrp > 0 ? '₹' + Math.round(mrp).toLocaleString('en-IN') : '';
    document.getElementById('previewCashbackText').textContent = cashback > 0 ? `₹${cashback} Wallet Cashback upon delivery` : 'No cashback';
    document.getElementById('previewCashbackBox').style.display = cashback > 0 ? 'flex' : 'none';
}

function updatePreviewCard() {
    // Title
    const titleVal = document.getElementById('kitTitleInput').value.trim();
    document.getElementById('previewTitle').textContent = titleVal || 'Driver Onboarding Kit';

    // Desc
    const descVal = document.getElementById('kitDescInput').value.trim();
    document.getElementById('previewDesc').textContent = descVal || 'Essential partner apparel, identity badge and supplies.';

    // Category
    const catSelect = document.getElementById('kitCategorySelect');
    const catText = catSelect.options[catSelect.selectedIndex]?.text || 'PARTNER';
    document.getElementById('previewCategoryBadge').textContent = catText.toUpperCase();

    // Mandatory
    const isCompulsory = document.getElementById('isCompulsorySwitch').checked;
    document.getElementById('previewMandatoryBadge').style.display = isCompulsory ? 'inline' : 'none';

    // Inclusions
    const rows = document.querySelectorAll('#selectedProductsBody tr');
    const inclusionsBox = document.getElementById('previewInclusionsList');
    if (rows.length === 0) {
        inclusionsBox.innerHTML = `<div class="text-muted font-11 italic">No products added yet</div>`;
    } else {
        let html = '';
        rows.forEach(r => {
            const name = r.querySelector('input[name*="[name]"]')?.value.trim() || 'Item';
            const variant = r.querySelector('input[name*="[variant]"]')?.value.trim() || '';
            const qty = r.querySelector('input[name*="[quantity]"]')?.value.trim() || '1';
            const isFree = r.querySelector('select[name*="[is_free]"]')?.value === '1';
            const isMandatory = r.querySelector('select[name*="[is_mandatory]"]')?.value === '1';

            html += `
                <div class="d-flex align-items-center justify-content-between py-1 border-bottom font-11">
                    <div>
                        <span class="text-success mr-1">✓</span>
                        <strong>${name}</strong> ${variant ? `<span class="badge badge-light border">${variant}</span>` : ''}
                        <span class="text-muted">x${qty}</span>
                    </div>
                    <div>
                        <span class="badge ${isFree ? 'badge-success' : 'badge-info'} font-10 mr-1">${isFree ? 'FREE' : 'PAID'}</span>
                        <span class="badge ${isMandatory ? 'badge-danger' : 'badge-secondary'} font-10">${isMandatory ? 'MANDATORY' : 'OPT'}</span>
                    </div>
                </div>
            `;
        });
        inclusionsBox.innerHTML = html;
    }

    calculateMargin();
}

function submitForm(status) {
    document.getElementById('kitStatusInput').value = status;
    const form = document.getElementById('createKitForm');
    if (form.reportValidity()) {
        form.submit();
    }
}

// Initial Listeners
document.getElementById('kitTitleInput').addEventListener('input', updatePreviewCard);
document.getElementById('kitDescInput').addEventListener('input', updatePreviewCard);
document.getElementById('kitCategorySelect').addEventListener('change', updatePreviewCard);

// Seed default items for quick onboarding convenience
window.addEventListener('DOMContentLoaded', () => {
    // Ensure modal element is placed at root of body to prevent backdrop stacking trap
    const modalEl = document.getElementById('selectProductModal');
    if (modalEl && modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }

    appendProductRow({
        id: '',
        name: 'Fiinway Branded Polo T-Shirt',
        image: '',
        variant: 'L',
        quantity: 2,
        is_free: 1,
        price: 0,
        is_mandatory: 1
    });
    appendProductRow({
        id: '',
        name: 'Official Partner ID Card & Lanyard',
        image: '',
        variant: 'Standard',
        quantity: 1,
        is_free: 1,
        price: 0,
        is_mandatory: 1
    });
    appendProductRow({
        id: '',
        name: 'Reflective Safety Helmet / Gear',
        image: '',
        variant: 'ISI Certified',
        quantity: 1,
        is_free: 0,
        price: 499,
        is_mandatory: 1
    });
    updatePreviewCard();
});
</script>
@endsection

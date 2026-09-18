@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles mb-3">
        <div class="col-md-6 align-self-center">
            <h3 class="text-themecolor mb-0 font-weight-bold"><i class="mdi mdi-tshirt-crew text-info mr-2"></i> Driver & Partner Welcome Kits</h3>
            <small class="text-muted">Configure starter kits, apparel, safety gear, and category-level compulsory popup rules</small>
        </div>
        <div class="col-md-6 align-self-center text-right">
            <a href="{{ route('driver-kits.create') }}" class="btn btn-success rounded-pill px-3 shadow-sm font-weight-bold mr-2">
                <i class="mdi mdi-plus-circle mr-1"></i> + Create Kit
            </a>
            <a href="{{ route('driver-kits.orders') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm font-weight-bold mr-2">
                <i class="mdi mdi-truck-delivery mr-1"></i> View Kit Orders
            </a>
            <ol class="breadcrumb d-inline-block p-0 bg-transparent mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Partner Kits</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Quick Stats Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0" style="border-radius: 14px;">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-light-info text-info p-3 mr-3">
                            <i class="mdi mdi-package-variant-closed font-24"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase font-weight-bold">Total Kits</small>
                            <h4 class="font-weight-bold m-0 text-dark">{{ $totalKits }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0" style="border-radius: 14px;">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-light-danger text-danger p-3 mr-3">
                            <i class="mdi mdi-alert-decagram font-24"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase font-weight-bold">Compulsory Categories</small>
                            <h4 class="font-weight-bold m-0 text-danger">{{ $compulsoryCount }} Categories</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0" style="border-radius: 14px;">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-light-success text-success p-3 mr-3">
                            <i class="mdi mdi-cart-check font-24"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase font-weight-bold">Total Orders</small>
                            <h4 class="font-weight-bold m-0 text-success">{{ $totalOrders }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0" style="border-radius: 14px;">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-light-warning text-warning p-3 mr-3">
                            <i class="mdi mdi-cash-multiple font-24"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase font-weight-bold">Kit Revenue</small>
                            <h4 class="font-weight-bold m-0 text-dark">₹{{ number_format($totalRevenue, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Navigation Tabs -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white p-3 border-bottom">
                <ul class="nav nav-pills" id="categoryTabs">
                    <li class="nav-item mr-2">
                        <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'all' ? 'active' : '' }}" href="{{ route('driver-kits.index', ['tab' => 'all']) }}">
                            <i class="mdi mdi-view-grid mr-1"></i> All Categories
                        </a>
                    </li>
                    <li class="nav-item mr-2">
                        <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'bike' ? 'active' : '' }}" href="{{ route('driver-kits.index', ['tab' => 'bike']) }}">
                            <i class="mdi mdi-motorbike mr-1"></i> 🏍️ Bike Taxi / Two-Wheeler
                        </a>
                    </li>
                    <li class="nav-item mr-2">
                        <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'auto' ? 'active' : '' }}" href="{{ route('driver-kits.index', ['tab' => 'auto']) }}">
                            <i class="mdi mdi-rickshaw mr-1"></i> 🛺 Auto Rickshaw
                        </a>
                    </li>
                    <li class="nav-item mr-2">
                        <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'car' ? 'active' : '' }}" href="{{ route('driver-kits.index', ['tab' => 'car']) }}">
                            <i class="mdi mdi-car mr-1"></i> 🚗 Car & Cab
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'home_service' ? 'active' : '' }}" href="{{ route('driver-kits.index', ['tab' => 'home_service']) }}">
                            <i class="mdi mdi-wrench mr-1"></i> 🛠️ Home Service Pro
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Kits Grid -->
        <div class="row">
            @forelse($kits as $kit)
                @php
                    $items = is_array($kit->items_included) ? $kit->items_included : (json_decode($kit->items_included, true) ?? []);
                    $categoryBadges = [
                        'bike' => ['label' => 'Two-Wheeler / Bike Taxi', 'badge' => 'badge-info', 'icon' => 'mdi-motorbike'],
                        'auto' => ['label' => 'Auto Rickshaw', 'badge' => 'badge-warning', 'icon' => 'mdi-rickshaw'],
                        'car' => ['label' => 'Car & Cab', 'badge' => 'badge-primary', 'icon' => 'mdi-car'],
                        'home_service' => ['label' => 'Home Service Pro', 'badge' => 'badge-success', 'icon' => 'mdi-wrench'],
                        'all' => ['label' => 'All Partners', 'badge' => 'badge-secondary', 'icon' => 'mdi-account-group'],
                    ];
                    $catInfo = $categoryBadges[$kit->category_code] ?? $categoryBadges['all'];
                @endphp
                @php
                    $products = is_array($kit->products) ? $kit->products : (json_decode($kit->products ?? '[]', true) ?: []);
                @endphp
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 16px; border-left: 5px solid {{ $kit->is_compulsory ? '#ef4444' : '#10b981' }} !important;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <!-- Top Row: Image Thumbnail, Title, Badges -->
                                <div class="d-flex align-items-start mb-3" style="gap: 14px;">
                                    <div style="width: 72px; height: 72px; border-radius: 12px; overflow: hidden; background: #f1f5f9; border: 1px solid #e2e8f0; flex-shrink: 0;" class="d-flex align-items-center justify-content-center">
                                        @if($kit->image)
                                            <img src="{{ str_starts_with($kit->image, 'http') ? $kit->image : asset($kit->image) }}" alt="{{ $kit->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <i class="mdi mdi-tshirt-crew text-muted font-30" style="opacity: 0.5;"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge {{ $catInfo['badge'] }} px-2 py-1 font-weight-bold font-11">
                                                <i class="mdi {{ $catInfo['icon'] }} mr-1"></i> {{ $catInfo['label'] }}
                                            </span>
                                            <div>
                                                <span class="badge {{ ($kit->status ?? 'published') === 'published' ? 'badge-success' : 'badge-warning' }} px-2 py-1 font-11 font-weight-bold text-uppercase mr-1">
                                                    {{ $kit->status ?? 'published' }}
                                                </span>
                                                <span class="badge {{ $kit->is_compulsory ? 'badge-danger' : 'badge-light border text-muted' }} px-2 py-1 font-11 font-weight-bold">
                                                    <i class="mdi {{ $kit->is_compulsory ? 'mdi-lock' : 'mdi-lock-open-outline' }} mr-1"></i>
                                                    {{ $kit->is_compulsory ? 'COMPULSORY' : 'OPTIONAL' }}
                                                </span>
                                            </div>
                                        </div>
                                        <h4 class="font-weight-bold text-dark mb-1">{{ $kit->title }}</h4>
                                        <div class="d-flex align-items-baseline" style="gap: 8px;">
                                            <h3 class="font-weight-bold text-primary mb-0">₹{{ number_format($kit->price, 2) }}</h3>
                                            @if($kit->mrp && $kit->mrp > $kit->price)
                                                <del class="text-muted font-13">₹{{ number_format($kit->mrp, 0) }}</del>
                                                <span class="badge badge-warning text-dark font-11 font-weight-bold">
                                                    {{ round((($kit->mrp - $kit->price)/$kit->mrp)*100) }}% OFF
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <p class="text-muted font-13 mb-3">{{ $kit->description ?: 'No description provided' }}</p>

                                <!-- Pricing Breakdown Pills -->
                                <div class="row no-gutters p-2 rounded-lg mb-3 border text-center font-12" style="background: #f8fafc; border-color: #e2e8f0;">
                                    <div class="col-4 border-right">
                                        <small class="text-muted d-block">Product Cost</small>
                                        <strong class="text-dark">₹{{ number_format($kit->cost_price ?? 0, 0) }}</strong>
                                    </div>
                                    <div class="col-4 border-right">
                                        <small class="text-muted d-block">Offer Price</small>
                                        <strong class="text-primary">₹{{ number_format($kit->price, 0) }}</strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Cashback</small>
                                        <strong class="text-success">₹{{ number_format($kit->cashback_amount ?? 0, 0) }}</strong>
                                    </div>
                                </div>

                                <!-- Items Included / Products -->
                                <div class="p-3 rounded-lg mb-3" style="background-color: #f8fafc; border: 1px dashed #cbd5e1;">
                                    <small class="text-uppercase font-weight-bold text-muted d-block mb-2">
                                        <i class="mdi mdi-check-all text-success mr-1"></i> Products Included in Kit:
                                    </small>
                                    @if(!empty($products))
                                        <div class="d-flex flex-column" style="gap: 6px;">
                                            @foreach($products as $p)
                                                <div class="d-flex align-items-center justify-content-between p-2 rounded bg-white border font-12">
                                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                                        @if(!empty($p['image']))
                                                            <img src="{{ str_starts_with($p['image'], 'http') ? $p['image'] : asset($p['image']) }}" style="width: 28px; height: 28px; object-fit: cover; border-radius: 4px;">
                                                        @else
                                                            <i class="mdi mdi-package text-muted"></i>
                                                        @endif
                                                        <strong>{{ $p['name'] ?? 'Item' }}</strong>
                                                        @if(!empty($p['variant']))
                                                            <span class="badge badge-light border text-muted">{{ $p['variant'] }}</span>
                                                        @endif
                                                        <span class="text-muted font-11">x{{ $p['quantity'] ?? 1 }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="badge {{ empty($p['is_free']) ? 'badge-info' : 'badge-success' }} font-10 mr-1">
                                                            {{ empty($p['is_free']) ? ('₹' . number_format($p['price'] ?? 0, 0)) : 'FREE' }}
                                                        </span>
                                                        <span class="badge {{ !empty($p['is_mandatory']) ? 'badge-danger' : 'badge-secondary' }} font-10">
                                                            {{ !empty($p['is_mandatory']) ? 'MANDATORY' : 'OPTIONAL' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="d-flex flex-wrap">
                                            @foreach($items as $item)
                                                <span class="badge badge-light border text-dark px-2 py-1 mr-2 mb-2 font-12 font-weight-500 shadow-none">
                                                    ✓ {{ $item }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Controls & Toggle Footer -->
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                <!-- Compulsory Toggle -->
                                <div class="d-flex align-items-center">
                                    <div class="custom-control custom-switch mr-2">
                                        <input type="checkbox" class="custom-control-input switchCompulsory" id="switchComp_{{ $kit->id }}" data-id="{{ $kit->id }}" {{ $kit->is_compulsory ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold font-13 text-dark" for="switchComp_{{ $kit->id }}">
                                            Mandatory Popup
                                        </label>
                                    </div>
                                    <small class="text-muted d-none d-sm-inline">
                                        ({{ $kit->is_compulsory ? 'Unclosable' : 'Closable' }})
                                    </small>
                                </div>

                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <a href="{{ route('driver-kits.edit', $kit->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm font-weight-bold">
                                        <i class="mdi mdi-pencil mr-1"></i> Edit Kit
                                    </a>
                                    <form action="{{ route('driver-kits.destroy', $kit->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this kit?');" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" title="Delete Kit">
                                            <i class="mdi mdi-trash-can-outline font-16"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Edit Kit Modal -->
                <div class="modal fade" id="editKitModal_{{ $kit->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content" style="border-radius: 16px;">
                            <form action="{{ route('driver-kits.update', $kit->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header bg-light py-3">
                                    <h5 class="modal-title font-weight-bold text-dark">
                                        <i class="mdi mdi-pencil text-primary mr-1"></i> Edit {{ $kit->title }}
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark">Kit Title <span class="text-danger">*</span></label>
                                                <input type="text" name="title" class="form-control font-weight-bold" value="{{ $kit->title }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark">Price (₹) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="price" class="form-control font-weight-bold text-primary" value="{{ $kit->price }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ $kit->description }}</textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Select Items Included in this Kit:</label>
                                        <div class="row">
                                            @php
                                                $availableItems = [
                                                    'Fiinway Branded T-Shirt',
                                                    'Certified Safety Helmet',
                                                    'Partner ID Card & Lanyard',
                                                    'Official Auto Decal Sticker',
                                                    'Vehicle Safety Decal',
                                                    'Car Windshield Tag',
                                                    'Tool Bag Organizer',
                                                    'Fiinway Cap',
                                                    'Waterproof Delivery Bag',
                                                ];
                                            @endphp
                                            @foreach($availableItems as $availItem)
                                                <div class="col-md-6 mb-2">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" name="items_included[]" value="{{ $availItem }}" class="custom-control-input" id="item_{{ $kit->id }}_{{ loop_index($availItem) }}" {{ in_array($availItem, $items) ? 'checked' : '' }}>
                                                        <label class="custom-control-label font-13" for="item_{{ $kit->id }}_{{ loop_index($availItem) }}">
                                                            {{ $availItem }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Add Custom Item (Optional):</label>
                                        <input type="text" name="custom_item" class="form-control" placeholder="e.g. Arm Sleeves, Raincoat, First Aid Pouch">
                                    </div>

                                    <div class="p-3 rounded-lg border bg-light mb-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="font-weight-bold mb-1 text-dark">
                                                    <i class="mdi mdi-shield-alert text-danger mr-1"></i> Make Kit Compulsory for this Category
                                                </h6>
                                                <small class="text-muted">When enabled, verified drivers in this category cannot close the popup dialog until they purchase the kit.</small>
                                            </div>
                                            <div class="custom-control custom-switch custom-switch-lg">
                                                <input type="checkbox" name="is_compulsory" class="custom-control-input" id="modalComp_{{ $kit->id }}" {{ $kit->is_compulsory ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="modalComp_{{ $kit->id }}"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light py-2">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                                        <i class="mdi mdi-content-save mr-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="mdi mdi-package-variant-closed font-48 text-muted d-block mb-3"></i>
                    <h5 class="text-muted">No kits found for this category.</h5>
                </div>
            @endforelse
        </div>
    </div>
</div>

@php
function loop_index($str) {
    return preg_replace('/[^a-zA-Z0-9]/', '_', $str);
}
@endphp

<script>
document.querySelectorAll('.switchCompulsory').forEach(sw => {
    sw.addEventListener('change', function() {
        const id = this.dataset.id;
        fetch(`/driver-kits/toggle-compulsory/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });
});
</script>
@endsection

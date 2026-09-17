@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles mb-3">
        <div class="col-md-6 align-self-center">
            <h3 class="text-themecolor mb-0 font-weight-bold"><i class="mdi mdi-truck-delivery text-success mr-2"></i> Partner Kit Orders & Deliveries</h3>
            <small class="text-muted">Track driver kit purchases, apparel sizes, shipping addresses, and update courier tracking</small>
        </div>
        <div class="col-md-6 align-self-center text-right">
            <a href="{{ route('driver-kits.index') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm font-weight-bold mr-2">
                <i class="mdi mdi-arrow-left mr-1"></i> Back to Kits
            </a>
            <ol class="breadcrumb d-inline-block p-0 bg-transparent mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('driver-kits.index') }}">Partner Kits</a></li>
                <li class="breadcrumb-item active">Orders</li>
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

        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap">
                <!-- Status Filter Pills -->
                <ul class="nav nav-pills mb-2 mb-md-0">
                    <li class="nav-item mr-2">
                        <a class="nav-link font-weight-bold rounded-pill {{ $status === 'all' ? 'active' : '' }}" href="{{ route('driver-kits.orders', ['status' => 'all', 'search' => $search]) }}">
                            All Orders
                        </a>
                    </li>
                    <li class="nav-item mr-2">
                        <a class="nav-link font-weight-bold rounded-pill {{ $status === 'processing' ? 'active' : '' }}" href="{{ route('driver-kits.orders', ['status' => 'processing', 'search' => $search]) }}">
                            ⏳ Processing
                        </a>
                    </li>
                    <li class="nav-item mr-2">
                        <a class="nav-link font-weight-bold rounded-pill {{ $status === 'dispatched' ? 'active' : '' }}" href="{{ route('driver-kits.orders', ['status' => 'dispatched', 'search' => $search]) }}">
                            🚚 Dispatched
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold rounded-pill {{ $status === 'delivered' ? 'active' : '' }}" href="{{ route('driver-kits.orders', ['status' => 'delivered', 'search' => $search]) }}">
                            ✅ Delivered
                        </a>
                    </li>
                </ul>

                <!-- Search Form -->
                <form action="{{ route('driver-kits.orders') }}" method="GET" class="form-inline">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control rounded-left" placeholder="Search order, name, phone..." value="{{ $search }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="font-weight-bold text-dark border-0">Order #</th>
                                <th class="font-weight-bold text-dark border-0">Driver Partner</th>
                                <th class="font-weight-bold text-dark border-0">Kit & Category</th>
                                <th class="font-weight-bold text-dark border-0">Size</th>
                                <th class="font-weight-bold text-dark border-0">Amount</th>
                                <th class="font-weight-bold text-dark border-0">Shipping Address</th>
                                <th class="font-weight-bold text-dark border-0">Delivery Status</th>
                                <th class="font-weight-bold text-dark border-0 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $order->order_number }}</strong>
                                        <div class="small text-muted">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $order->receiver_name }}</div>
                                        <div class="small text-muted"><i class="mdi mdi-phone font-12 mr-1"></i>{{ $order->receiver_phone }}</div>
                                    </td>
                                    <td>
                                        <div class="font-weight-600 text-dark">{{ $order->kit_title }}</div>
                                        <span class="badge badge-light border text-uppercase font-11">{{ $order->category_code }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info px-2 py-1 font-12">{{ $order->tshirt_size ?? 'L' }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-dark">₹{{ number_format($order->amount, 2) }}</strong>
                                        <span class="badge badge-success font-11 d-block mt-1">Paid</span>
                                    </td>
                                    <td>
                                        <small class="text-muted" style="max-width: 200px; display: inline-block;">
                                            {{ Str::limit($order->shipping_address, 40) }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($order->delivery_status === 'delivered')
                                            <span class="badge badge-success px-3 py-1 font-12">Delivered</span>
                                        @elseif($order->delivery_status === 'out_for_delivery')
                                            <span class="badge badge-warning px-3 py-1 font-12">Out for Delivery</span>
                                        @elseif($order->delivery_status === 'in_transit' || $order->delivery_status === 'dispatched')
                                            <span class="badge badge-primary px-3 py-1 font-12">In Transit</span>
                                        @elseif($order->delivery_status === 'picked_up')
                                            <span class="badge badge-info px-3 py-1 font-12">Picked Up</span>
                                        @else
                                            <span class="badge badge-secondary px-3 py-1 font-12">Booked</span>
                                        @endif
                                        @if($order->tracking_code || $order->tracking_number)
                                            <div class="small text-muted mt-1 font-11">
                                                <strong>{{ $order->courier_partner ?? 'Courier' }}:</strong> {{ $order->tracking_code ?? $order->tracking_number }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 font-weight-bold" data-toggle="modal" data-target="#updateOrderModal_{{ $order->id }}">
                                                Manage
                                            </button>
                                            <a href="{{ route('driver-kits.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 ml-1" title="Print Invoice">
                                                <i class="mdi mdi-printer font-14"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Status & Courier Modal -->
                                <div class="modal fade" id="updateOrderModal_{{ $order->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content" style="border-radius: 16px;">
                                            <form action="{{ route('driver-kits.orders.updateStatus', $order->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-light py-3">
                                                    <h5 class="modal-title font-weight-bold text-dark">
                                                        Dispatch & Tracking #{{ $order->order_number }}
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-bold text-dark">Delivery Status <span class="text-danger">*</span></label>
                                                        <select name="delivery_status" class="form-control font-weight-bold" required>
                                                            <option value="booked" {{ in_array($order->delivery_status, ['booked', 'processing']) ? 'selected' : '' }}>📦 Booked (Order Placed)</option>
                                                            <option value="picked_up" {{ $order->delivery_status === 'picked_up' ? 'selected' : '' }}>🏭 Picked Up (From Warehouse)</option>
                                                            <option value="in_transit" {{ in_array($order->delivery_status, ['in_transit', 'dispatched']) ? 'selected' : '' }}>🚚 In Transit (Hub to Hub)</option>
                                                            <option value="out_for_delivery" {{ $order->delivery_status === 'out_for_delivery' ? 'selected' : '' }}>🛵 Out for Delivery (Almost there)</option>
                                                            <option value="delivered" {{ $order->delivery_status === 'delivered' ? 'selected' : '' }}>✅ Delivered (Handed to Partner)</option>
                                                            <option value="cancelled" {{ $order->delivery_status === 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-bold text-dark">Courier Partner</label>
                                                        <input type="text" list="courierList" name="courier_partner" class="form-control" value="{{ $order->courier_partner ?? 'Blue Dart Express' }}" placeholder="e.g. Blue Dart, Delhivery, DTDC, Ekart">
                                                        <datalist id="courierList">
                                                            <option value="Blue Dart Express">
                                                            <option value="Delhivery Logistics">
                                                            <option value="DTDC Express">
                                                            <option value="Ekart Logistics">
                                                            <option value="Shadowfax">
                                                            <option value="In-House Delivery">
                                                        </datalist>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-bold text-dark">Tracking Code / AWB Number</label>
                                                        <input type="text" name="tracking_code" class="form-control font-weight-bold" value="{{ $order->tracking_code ?? $order->tracking_number }}" placeholder="e.g. FWP7823456789">
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-bold text-dark">Expected Delivery (Date & Time)</label>
                                                        <input type="text" name="expected_delivery_date" class="form-control" value="{{ $order->expected_delivery_date }}" placeholder="e.g. Today, 18 Sep by 6:00 PM">
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-bold text-dark">Tracking Link / URL</label>
                                                        <input type="url" name="tracking_url" class="form-control" value="{{ $order->tracking_url }}" placeholder="https://www.bluedart.com/tracking?track=...">
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6 form-group mb-3">
                                                            <label class="font-weight-bold text-dark font-12">Delivery Boy Name</label>
                                                            <input type="text" name="delivery_partner_name" class="form-control form-control-sm" value="{{ $order->delivery_partner_name }}" placeholder="e.g. Ravi Kumar">
                                                        </div>
                                                        <div class="col-md-6 form-group mb-3">
                                                            <label class="font-weight-bold text-dark font-12">Delivery Boy Phone</label>
                                                            <input type="text" name="delivery_partner_phone" class="form-control form-control-sm" value="{{ $order->delivery_partner_phone }}" placeholder="e.g. +91 98765 43210">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6 form-group mb-0">
                                                            <label class="font-weight-bold text-dark font-12">Vehicle Number</label>
                                                            <input type="text" name="delivery_partner_vehicle" class="form-control form-control-sm" value="{{ $order->delivery_partner_vehicle }}" placeholder="e.g. DL 1L AB 1234">
                                                        </div>
                                                        <div class="col-md-6 form-group mb-0">
                                                            <label class="font-weight-bold text-dark font-12">Executive ID</label>
                                                            <input type="text" name="delivery_partner_id" class="form-control form-control-sm" value="{{ $order->delivery_partner_id }}" placeholder="e.g. BD567890">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold">Update Order</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="mdi mdi-cart-off font-48 text-muted d-block mb-3"></i>
                                        <h5 class="text-muted">No kit orders found.</h5>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

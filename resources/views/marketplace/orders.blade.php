@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background-color: #f8fafc; min-height: 100vh; padding: 25px;">

    <!-- Alerts -->
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
                <i class="mdi mdi-shopping text-warning mr-2"></i> Marketplace Orders & Settlement
            </h2>
            <p class="text-muted mb-0 font-14">Track orders across all fulfillment stages and confirm payouts to seller wallets after admin review.</p>
        </div>
        <div class="mt-2 mt-md-0 d-flex align-items-center gap-2">
            <a href="{{ route('admin.marketplace.commission.index') }}" class="btn btn-outline-success font-weight-bold px-3 py-2 rounded-8 shadow-sm mr-2">
                <i class="mdi mdi-percent mr-1"></i> Commission Settings
            </a>
        </div>
    </div>

    <!-- Summary KPI Header Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-16 bg-white" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body p-3">
                    <div class="text-muted font-12 font-weight-bold uppercase">Total Order Volume</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h4 class="font-weight-extrabold text-dark mb-0">₹{{ number_format($totalSalesAmount, 2) }}</h4>
                        <span class="badge badge-light-primary text-primary font-12">{{ $counts['all'] }} Orders</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-16 bg-white" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="text-muted font-12 font-weight-bold uppercase">Escrow Pending Payouts</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h4 class="font-weight-extrabold text-warning mb-0">{{ $counts['pending_payout'] }} Orders</h4>
                        <span class="badge badge-light-warning text-warning font-12">Action Required</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-16 bg-white" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="text-muted font-12 font-weight-bold uppercase">Settled Payouts</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h4 class="font-weight-extrabold text-success mb-0">{{ $counts['released_payout'] }} Orders</h4>
                        <span class="badge badge-light-success text-success font-12">Wallet Credited</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm rounded-16 bg-white" style="border-left: 4px solid #8b5cf6 !important;">
                <div class="card-body p-3">
                    <div class="text-muted font-12 font-weight-bold uppercase">Settled Admin Commission</div>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h4 class="font-weight-extrabold text-purple mb-0" style="color: #8b5cf6;">₹{{ number_format($totalCommissionEarned, 2) }}</h4>
                        <span class="badge badge-light-secondary font-12">Platform Revenue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Stage Filter Pills (Top Stage Selector) -->
    <div class="card border-0 shadow-sm rounded-16 bg-white mb-4">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="stage-filter-pills d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'stage'), ['stage' => 'all'])) }}"
                       class="btn btn-sm font-weight-bold rounded-pill px-3 py-2 mr-1 mb-1 {{ $stage === 'all' ? 'btn-primary text-white shadow-sm' : 'btn-light text-dark' }}">
                        All Stages <span class="badge badge-pill {{ $stage === 'all' ? 'badge-light text-primary' : 'badge-secondary' }} ml-1">{{ $counts['all'] }}</span>
                    </a>

                    <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'stage'), ['stage' => 'placed'])) }}"
                       class="btn btn-sm font-weight-bold rounded-pill px-3 py-2 mr-1 mb-1 {{ $stage === 'placed' ? 'btn-info text-white shadow-sm' : 'btn-light text-dark' }}">
                        Placed <span class="badge badge-pill {{ $stage === 'placed' ? 'badge-light text-info' : 'badge-secondary' }} ml-1">{{ $counts['placed'] }}</span>
                    </a>

                    <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'stage'), ['stage' => 'processing'])) }}"
                       class="btn btn-sm font-weight-bold rounded-pill px-3 py-2 mr-1 mb-1 {{ $stage === 'processing' ? 'btn-warning text-white shadow-sm' : 'btn-light text-dark' }}">
                        Processing <span class="badge badge-pill {{ $stage === 'processing' ? 'badge-light text-warning' : 'badge-secondary' }} ml-1">{{ $counts['processing'] }}</span>
                    </a>

                    <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'stage'), ['stage' => 'dispatched'])) }}"
                       class="btn btn-sm font-weight-bold rounded-pill px-3 py-2 mr-1 mb-1 {{ $stage === 'dispatched' ? 'btn-primary text-white shadow-sm' : 'btn-light text-dark' }}">
                        Dispatched <span class="badge badge-pill {{ $stage === 'dispatched' ? 'badge-light text-primary' : 'badge-secondary' }} ml-1">{{ $counts['dispatched'] }}</span>
                    </a>

                    <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'stage'), ['stage' => 'shipped'])) }}"
                       class="btn btn-sm font-weight-bold rounded-pill px-3 py-2 mr-1 mb-1 {{ $stage === 'shipped' ? 'btn-primary text-white shadow-sm' : 'btn-light text-dark' }}" style="{{ $stage === 'shipped' ? 'background-color: #3b82f6;' : '' }}">
                        Shipped <span class="badge badge-pill {{ $stage === 'shipped' ? 'badge-light text-primary' : 'badge-secondary' }} ml-1">{{ $counts['shipped'] }}</span>
                    </a>

                    <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'stage'), ['stage' => 'out_for_delivery'])) }}"
                       class="btn btn-sm font-weight-bold rounded-pill px-3 py-2 mr-1 mb-1 {{ $stage === 'out_for_delivery' ? 'btn-info text-white shadow-sm' : 'btn-light text-dark' }}" style="{{ $stage === 'out_for_delivery' ? 'background-color: #06b6d4;' : '' }}">
                        Out for Delivery <span class="badge badge-pill {{ $stage === 'out_for_delivery' ? 'badge-light text-info' : 'badge-secondary' }} ml-1">{{ $counts['out_for_delivery'] }}</span>
                    </a>

                    <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'stage'), ['stage' => 'delivered'])) }}"
                       class="btn btn-sm font-weight-bold rounded-pill px-3 py-2 mr-1 mb-1 {{ $stage === 'delivered' ? 'btn-success text-white shadow-sm' : 'btn-light text-dark' }}">
                        Delivered <span class="badge badge-pill {{ $stage === 'delivered' ? 'badge-light text-success' : 'badge-secondary' }} ml-1">{{ $counts['delivered'] }}</span>
                    </a>

                    <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'stage'), ['stage' => 'completed'])) }}"
                       class="btn btn-sm font-weight-bold rounded-pill px-3 py-2 mr-1 mb-1 {{ $stage === 'completed' ? 'btn-success text-white shadow-sm' : 'btn-light text-dark' }}" style="{{ $stage === 'completed' ? 'background-color: #059669;' : '' }}">
                        Completed <span class="badge badge-pill {{ $stage === 'completed' ? 'badge-light text-success' : 'badge-secondary' }} ml-1">{{ $counts['completed'] }}</span>
                    </a>

                    <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'stage'), ['stage' => 'cancelled'])) }}"
                       class="btn btn-sm font-weight-bold rounded-pill px-3 py-2 mr-1 mb-1 {{ $stage === 'cancelled' ? 'btn-danger text-white shadow-sm' : 'btn-light text-dark' }}">
                        Cancelled <span class="badge badge-pill {{ $stage === 'cancelled' ? 'badge-light text-danger' : 'badge-secondary' }} ml-1">{{ $counts['cancelled'] }}</span>
                    </a>
                </div>

                <!-- Search Form -->
                <form action="{{ route('admin.marketplace.orders.index') }}" method="GET" class="d-flex align-items-center">
                    <input type="hidden" name="stage" value="{{ $stage }}">
                    <input type="hidden" name="payout_status" value="{{ $payoutFilter }}">
                    <div class="input-group input-group-sm" style="min-width: 260px;">
                        <input type="text" name="search" value="{{ $search }}" class="form-control rounded-left-8" placeholder="Search Order ID, Seller, Buyer, Courier...">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-3 rounded-right-8" type="submit">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                            @if($search)
                                <a href="{{ route('admin.marketplace.orders.index', ['stage' => $stage, 'payout_status' => $payoutFilter]) }}" class="btn btn-light border" title="Clear Search">
                                    <i class="mdi mdi-close"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Orders Data Table -->
    <div class="card border-0 shadow-sm rounded-16 bg-white">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-wrap align-items-center justify-content-between">
            <h5 class="font-weight-bold mb-0 text-dark">
                <i class="mdi mdi-format-list-bulleted text-primary mr-1"></i> Orders List ({{ $orders->total() }} Total)
            </h5>
            <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                <span class="text-muted font-12 mr-2">Filter Payout:</span>
                <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'payout_status'), ['payout_status' => 'all'])) }}"
                   class="badge {{ $payoutFilter === 'all' ? 'badge-primary' : 'badge-light text-dark' }} px-2 py-1 mr-1">All Payouts</a>
                <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'payout_status'), ['payout_status' => 'pending'])) }}"
                   class="badge {{ $payoutFilter === 'pending' ? 'badge-warning text-dark' : 'badge-light text-dark' }} px-2 py-1 mr-1">Pending Escrow</a>
                <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'payout_status'), ['payout_status' => 'released'])) }}"
                   class="badge {{ $payoutFilter === 'released' ? 'badge-success' : 'badge-light text-dark' }} px-2 py-1">Released Payouts</a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="bg-light text-muted uppercase font-11 font-weight-bold">
                        <tr>
                            <th class="pl-4 py-3">Order / Date</th>
                            <th class="py-3">Product Item</th>
                            <th class="py-3 text-center">Qty</th>
                            <th class="py-3">Bill Breakdown & Taxes</th>
                            <th class="py-3">Commission & Net Payout</th>
                            <th class="py-3">Seller Details</th>
                            <th class="py-3">Buyer & Shipping Address</th>
                            <th class="py-3">Shipping Partner</th>
                            <th class="py-3 text-center">Stage</th>
                            <th class="pr-4 py-3 text-center">Payout & Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $firstItem = $order->items->first();
                                $product = $firstItem ? $firstItem->product : null;
                                $firstImg = $product && $product->images->count() > 0 ? $product->images->first()->image_path : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=150&q=80';
                                $seller = $order->resolved_seller;
                                $buyer = $order->buyer;
                            @endphp
                            <tr>
                                <!-- Order ID & Date -->
                                <td class="pl-4 py-3 font-weight-bold">
                                    <span class="text-primary d-block font-14">#{{ $order->id }}</span>
                                    <small class="text-muted d-block">{{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : 'N/A' }}</small>
                                    <span class="badge badge-light-secondary font-11 mt-1">{{ $order->payment_method ?: 'Wallet' }}</span>
                                </td>

                                <!-- Product Info -->
                                <td class="py-3" style="max-width: 220px;">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $firstImg }}" alt="Product" class="rounded-8 mr-2 border shadow-2xs" style="width: 48px; height: 48px; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=150&q=80';">
                                        <div class="text-truncate">
                                            <span class="font-weight-bold text-dark d-block text-truncate" title="{{ $product ? $product->title : 'Product Deleted' }}">
                                                {{ $product ? $product->title : 'Product Deleted' }}
                                            </span>
                                            <small class="text-muted">Unit: ₹{{ number_format($firstItem ? $firstItem->price : ($order->subtotal ?: $order->total_amount), 2) }}</small>
                                            @if($order->items->count() > 1)
                                                <span class="badge badge-light-info font-10 d-block mt-1">+{{ $order->items->count() - 1 }} more item(s)</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Qty -->
                                <td class="py-3 text-center font-weight-bold text-dark">
                                    {{ $firstItem ? $firstItem->quantity : 1 }}
                                </td>

                                <!-- Bill Breakdown & Taxes -->
                                <td class="py-3" style="min-width: 170px;">
                                    <div class="font-12">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Subtotal:</span>
                                            <span class="font-weight-bold text-dark">₹{{ number_format($order->subtotal ?: $order->total_amount, 2) }}</span>
                                        </div>
                                        @if($order->tax_amount > 0)
                                        <div class="d-flex justify-content-between text-info">
                                            <span>{{ $order->tax_name ?: 'GST' }} ({{ $order->tax_rate }}%):</span>
                                            <span class="font-weight-bold">+₹{{ number_format($order->tax_amount, 2) }}</span>
                                        </div>
                                        @endif
                                        @if($order->delivery_charge > 0)
                                        <div class="d-flex justify-content-between text-muted">
                                            <span>Delivery:</span>
                                            <span>+₹{{ number_format($order->delivery_charge, 2) }}</span>
                                        </div>
                                        @endif
                                        <div class="d-flex justify-content-between border-top pt-1 mt-1 font-weight-bold text-dark">
                                            <span>Total Bill:</span>
                                            <span class="text-primary font-13">₹{{ number_format($order->total_amount, 2) }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Commission & Net Payout -->
                                <td class="py-3" style="min-width: 180px;">
                                    <div class="font-12">
                                        <div class="d-flex justify-content-between text-danger mb-1">
                                            <span>Admin Comm ({{ $order->admin_commission_rate ?: 5 }}%):</span>
                                            <span class="font-weight-bold">-₹{{ number_format($order->admin_commission_amount, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between border-top pt-1 font-weight-extrabold text-success">
                                            <span>Seller Payout:</span>
                                            <span class="font-14">₹{{ number_format($order->seller_payout_amount, 2) }}</span>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            @if($order->payout_status === 'released')
                                                <span class="text-success"><i class="mdi mdi-check-circle-outline"></i> Settled {{ $order->payout_released_at ? date('d M', strtotime($order->payout_released_at)) : '' }}</span>
                                            @else
                                                <span class="text-warning"><i class="mdi mdi-clock-outline"></i> Held in Escrow</span>
                                            @endif
                                        </small>
                                    </div>
                                </td>

                                <!-- Seller Details -->
                                <td class="py-3" style="max-width: 180px;">
                                    @if($seller)
                                        <div class="font-weight-bold text-dark text-truncate">
                                            {{ trim(($seller->prenom ?? '') . ' ' . ($seller->nom ?? '')) ?: ($seller->name ?? 'Seller') }}
                                        </div>
                                        <div class="text-muted font-12"><i class="mdi mdi-phone mr-1"></i>{{ $seller->phone ?: ($seller->mobile ?: 'N/A') }}</div>
                                        @if(!empty($seller->email))
                                            <small class="text-muted d-block text-truncate"><i class="mdi mdi-email mr-1"></i>{{ $seller->email }}</small>
                                        @endif
                                        <span class="badge {{ $seller instanceof \App\Models\Driver ? 'badge-light-info' : 'badge-light-primary' }} font-10 mt-1">
                                            {{ $seller instanceof \App\Models\Driver ? 'Driver Seller' : 'User Seller' }}
                                        </span>
                                    @else
                                        <span class="text-muted font-italic">Seller ID: {{ $order->seller_id ?: ($product ? $product->user_id : 'N/A') }}</span>
                                    @endif
                                </td>

                                <!-- Buyer & Shipping Address -->
                                <td class="py-3" style="max-width: 220px;">
                                    <div class="font-weight-bold text-dark text-truncate">
                                        {{ $order->contact_name ?: ($buyer ? trim(($buyer->prenom ?? '') . ' ' . ($buyer->nom ?? '')) : 'Buyer') }}
                                    </div>
                                    <div class="text-muted font-12"><i class="mdi mdi-phone mr-1"></i>{{ $order->phone ?: ($buyer ? $buyer->phone : 'N/A') }}</div>
                                    <div class="text-muted font-11 mt-1 text-truncate" title="{{ $order->delivery_address }}">
                                        <i class="mdi mdi-map-marker mr-1 text-danger"></i>{{ $order->delivery_address ?: 'No Address' }}
                                    </div>
                                    @if($order->city || $order->pincode)
                                        <small class="text-muted d-block">{{ $order->city }} {{ $order->pincode ? '- ' . $order->pincode : '' }}</small>
                                    @endif
                                </td>

                                <!-- Shipping Partner / Courier Details -->
                                <td class="py-3" style="min-width: 160px;">
                                    @if($order->courier_name || $order->tracking_id)
                                        <div class="font-weight-bold text-primary font-12">
                                            <i class="mdi mdi-truck-delivery mr-1"></i>{{ $order->courier_name ?: 'Courier Partner' }}
                                        </div>
                                        @if($order->tracking_id)
                                            <div class="font-11 text-dark mt-1">
                                                <strong>Track:</strong> <code class="text-dark bg-light px-1 py-0 rounded">{{ $order->tracking_id }}</code>
                                            </div>
                                        @endif
                                        @if($order->delivery_days)
                                            <small class="text-muted d-block">ETA: {{ $order->delivery_days }} days</small>
                                        @endif
                                        @if($order->status_notes)
                                            <small class="text-info d-block text-truncate font-italic" title="{{ $order->status_notes }}">{{ $order->status_notes }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-light-warning font-11">
                                            <i class="mdi mdi-timer-sand mr-1"></i> Awaiting Courier Info
                                        </span>
                                    @endif
                                </td>

                                <!-- Stage / Status Badge -->
                                <td class="py-3 text-center">
                                    @php
                                        $s = strtolower($order->status);
                                        $badgeClass = 'badge-secondary';
                                        if ($s === 'placed') $badgeClass = 'badge-info';
                                        elseif ($s === 'processing') $badgeClass = 'badge-warning';
                                        elseif ($s === 'dispatched' || $s === 'shipped') $badgeClass = 'badge-primary';
                                        elseif ($s === 'out_for_delivery') $badgeClass = 'badge-info';
                                        elseif ($s === 'delivered' || $s === 'completed') $badgeClass = 'badge-success';
                                        elseif ($s === 'cancelled') $badgeClass = 'badge-danger';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} font-12 px-3 py-2 rounded-pill font-weight-bold uppercase">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </span>
                                </td>

                                <!-- Payout Status & Confirmation Action -->
                                <td class="pr-4 py-3 text-center" style="min-width: 170px;">
                                    @if($order->payout_status === 'released')
                                        <span class="badge badge-success font-12 px-3 py-2 rounded-pill mb-1 d-inline-block">
                                            <i class="mdi mdi-check-all mr-1"></i> Payout Settled
                                        </span>
                                        <small class="text-muted d-block font-11">Wallet Credited</small>
                                    @else
                                        <span class="badge badge-warning font-11 px-2 py-1 rounded-pill mb-2 d-inline-block text-dark">
                                            <i class="mdi mdi-timer-sand mr-1"></i> Pending Escrow
                                        </span>
                                        <!-- Release Payout Form / Action Button -->
                                        <form action="{{ route('admin.marketplace.orders.releasePayout', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to release ₹{{ number_format($order->seller_payout_amount, 2) }} to seller wallet? Admin commission of ₹{{ number_format($order->admin_commission_amount, 2) }} will be recorded.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success font-weight-bold rounded-8 px-3 py-1 shadow-sm d-inline-flex align-items-center">
                                                <i class="mdi mdi-cash-multiple mr-1 font-14"></i> Confirm & Release
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-cart-off font-48 d-block mb-2 text-muted opacity-50"></i>
                                    <h5 class="font-weight-bold">No Marketplace Orders Found</h5>
                                    <p class="mb-0">There are no orders matching the selected stage filter or search criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($orders->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center">
            <span class="text-muted font-13">Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders</span>
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.rounded-8 { border-radius: 8px !important; }
.rounded-12 { border-radius: 12px !important; }
.rounded-16 { border-radius: 16px !important; }
.rounded-left-8 { border-top-left-radius: 8px !important; border-bottom-left-radius: 8px !important; }
.rounded-right-8 { border-top-right-radius: 8px !important; border-bottom-right-radius: 8px !important; }
.bg-light-primary { background-color: #eff6ff !important; }
.bg-light-success { background-color: #ecfdf5 !important; }
.bg-light-warning { background-color: #fffbeb !important; }
.bg-light-secondary { background-color: #f1f5f9 !important; }
.badge-light-primary { background-color: #eff6ff; color: #2563eb; }
.badge-light-success { background-color: #ecfdf5; color: #059669; }
.badge-light-warning { background-color: #fffbeb; color: #d97706; }
.badge-light-secondary { background-color: #f1f5f9; color: #475569; }
.badge-light-info { background-color: #f0f9ff; color: #0284c7; }
.font-24 { font-size: 24px; }
.font-18 { font-size: 18px; }
.font-14 { font-size: 14px; }
.font-13 { font-size: 13px; }
.font-12 { font-size: 12px; }
.font-11 { font-size: 11px; }
.font-10 { font-size: 10px; }
.font-48 { font-size: 48px; }
.font-weight-extrabold { font-weight: 800; }
</style>
@endsection

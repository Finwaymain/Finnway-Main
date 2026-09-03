@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background-color: #f8fafc; min-height: 100vh; padding: 20px;">

    <!-- Alerts -->
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
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">Marketplace Orders</h3>
            <p class="text-muted mb-0 font-13">Track order fulfillment stages and confirm seller wallet payouts.</p>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('admin.marketplace.commission.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold px-3 py-1 rounded">
                Commission Settings
            </a>
        </div>
    </div>

    <!-- Compact Stage Filter Tabs & Search Bar -->
    <div class="card border shadow-sm rounded bg-white mb-3">
        <div class="card-body p-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                
                <!-- Stage Filter Pills -->
                <div class="d-flex flex-wrap align-items-center gap-1">
                    @php
                        $stages = [
                            'all' => 'All',
                            'placed' => 'Placed',
                            'processing' => 'Processing',
                            'dispatched' => 'Dispatched',
                            'shipped' => 'Shipped',
                            'out_for_delivery' => 'Out for Delivery',
                            'delivered' => 'Delivered',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled'
                        ];
                    @endphp

                    @foreach($stages as $stgKey => $stgLabel)
                        <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'stage'), ['stage' => $stgKey])) }}"
                           class="btn btn-xs font-weight-bold px-2 py-1 mr-1 mb-1 rounded {{ $stage === $stgKey ? 'btn-primary' : 'btn-light border text-dark' }}" style="font-size: 11px;">
                            {{ $stgLabel }} ({{ $counts[$stgKey] ?? 0 }})
                        </a>
                    @endforeach
                </div>

                <!-- Search -->
                <form action="{{ route('admin.marketplace.orders.index') }}" method="GET" class="d-flex align-items-center mb-1">
                    <input type="hidden" name="stage" value="{{ $stage }}">
                    <input type="hidden" name="payout_status" value="{{ $payoutFilter }}">
                    <div class="input-group input-group-sm" style="width: 240px;">
                        <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search orders...">
                        <div class="input-group-append">
                            <button class="btn btn-primary btn-sm px-2" type="submit">Search</button>
                            @if($search)
                                <a href="{{ route('admin.marketplace.orders.index', ['stage' => $stage, 'payout_status' => $payoutFilter]) }}" class="btn btn-light btn-sm border" title="Clear">×</a>
                            @endif
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="card border shadow-sm rounded bg-white">
        <div class="card-header bg-white border-bottom py-2 px-3 d-flex flex-wrap align-items-center justify-content-between">
            <span class="font-weight-bold text-dark font-14">Orders List ({{ $orders->total() }})</span>
            
            <!-- Payout Quick Filter -->
            <div class="d-flex align-items-center font-12">
                <span class="text-muted mr-2">Payout:</span>
                <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'payout_status'), ['payout_status' => 'all'])) }}"
                   class="badge {{ $payoutFilter === 'all' ? 'badge-primary' : 'badge-light border text-dark' }} px-2 py-1 mr-1">All</a>
                <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'payout_status'), ['payout_status' => 'pending'])) }}"
                   class="badge {{ $payoutFilter === 'pending' ? 'badge-warning text-dark' : 'badge-light border text-dark' }} px-2 py-1 mr-1">Pending ({{ $counts['pending_payout'] ?? 0 }})</a>
                <a href="{{ route('admin.marketplace.orders.index', array_merge(request()->except('page', 'payout_status'), ['payout_status' => 'released'])) }}"
                   class="badge {{ $payoutFilter === 'released' ? 'badge-success' : 'badge-light border text-dark' }} px-2 py-1">Settled ({{ $counts['released_payout'] ?? 0 }})</a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm mb-0" style="font-size: 12px;">
                    <thead class="bg-light text-dark font-weight-bold">
                        <tr>
                            <th class="py-2 px-2 text-center" style="width: 70px;">Order</th>
                            <th class="py-2 px-2">Product</th>
                            <th class="py-2 px-2 text-center" style="width: 45px;">Qty</th>
                            <th class="py-2 px-2">Bill & Taxes</th>
                            <th class="py-2 px-2">Commission & Payout</th>
                            <th class="py-2 px-2">Seller</th>
                            <th class="py-2 px-2">Buyer & Delivery Address</th>
                            <th class="py-2 px-2">Shipping Partner</th>
                            <th class="py-2 px-2 text-center" style="width: 90px;">Stage</th>
                            <th class="py-2 px-2 text-center" style="width: 130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $firstItem = $order->items->first();
                                $product = $firstItem ? $firstItem->product : null;
                                $firstImg = $product && $product->images->count() > 0 ? $product->images->first()->image_path : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=100&q=80';
                                $seller = $order->resolved_seller;
                                $buyer = $order->buyer;
                                
                                $subtotal = floatval($order->subtotal ?: $order->total_amount);
                                $commRate = $order->admin_commission_rate > 0 ? $order->admin_commission_rate : 5;
                                $commAmount = $order->admin_commission_amount > 0 ? $order->admin_commission_amount : round(($subtotal * $commRate) / 100, 2);
                                $payoutAmount = $order->seller_payout_amount > 0 ? $order->seller_payout_amount : max(0, round($subtotal - $commAmount, 2));
                            @endphp
                            <tr>
                                <!-- Order ID & Date -->
                                <td class="p-2 text-center align-middle">
                                    <strong class="text-primary d-block font-13">#{{ $order->id }}</strong>
                                    <small class="text-muted d-block font-11">{{ $order->created_at ? $order->created_at->format('d M Y') : '' }}</small>
                                    <span class="badge badge-light border text-muted font-10">{{ $order->payment_method ?: 'Wallet' }}</span>
                                </td>

                                <!-- Product Item -->
                                <td class="p-2 align-middle">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $firstImg }}" alt="Product" class="rounded mr-2 border" style="width: 36px; height: 36px; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=100&q=80';">
                                        <div style="max-width: 150px;">
                                            <span class="font-weight-bold text-dark d-block text-truncate" title="{{ $product ? $product->title : 'Product' }}">
                                                {{ $product ? $product->title : 'Product' }}
                                            </span>
                                            <small class="text-muted">₹{{ number_format($firstItem ? $firstItem->price : $subtotal, 2) }}</small>
                                        </div>
                                    </div>
                                </td>

                                <!-- Qty -->
                                <td class="p-2 text-center align-middle font-weight-bold">
                                    {{ $firstItem ? $firstItem->quantity : 1 }}
                                </td>

                                <!-- Bill & Taxes -->
                                <td class="p-2 align-middle">
                                    <div>Subtotal: <strong>₹{{ number_format($subtotal, 2) }}</strong></div>
                                    @if($order->tax_amount > 0)
                                        <div class="text-info font-11">{{ $order->tax_name ?: 'GST' }} ({{ $order->tax_rate }}%): +₹{{ number_format($order->tax_amount, 2) }}</div>
                                    @endif
                                    @if($order->delivery_charge > 0)
                                        <div class="text-muted font-11">Delivery: +₹{{ number_format($order->delivery_charge, 2) }}</div>
                                    @endif
                                    <div class="text-dark font-weight-bold mt-1 border-top pt-1">Total: <span class="text-primary">₹{{ number_format($order->total_amount, 2) }}</span></div>
                                </td>

                                <!-- Commission & Payout -->
                                <td class="p-2 align-middle">
                                    <div class="text-danger font-11">Commission ({{ $commRate }}%): -₹{{ number_format($commAmount, 2) }}</div>
                                    <div class="text-success font-weight-bold">Payout: ₹{{ number_format($payoutAmount, 2) }}</div>
                                    <small class="d-block mt-1">
                                        @if($order->payout_status === 'released')
                                            <span class="badge badge-success font-10">Settled</span>
                                        @else
                                            <span class="badge badge-warning text-dark font-10">Held in Escrow</span>
                                        @endif
                                    </small>
                                </td>

                                <!-- Seller -->
                                <td class="p-2 align-middle" style="max-width: 130px;">
                                    @if($seller)
                                        <strong class="text-dark d-block text-truncate">{{ trim(($seller->prenom ?? '') . ' ' . ($seller->nom ?? '')) ?: ($seller->name ?? 'Seller') }}</strong>
                                        <small class="text-muted d-block">{{ $seller->phone ?: ($seller->mobile ?: 'N/A') }}</small>
                                    @else
                                        <small class="text-muted">Seller ID: {{ $order->seller_id ?: ($product ? $product->user_id : 'N/A') }}</small>
                                    @endif
                                </td>

                                <!-- Buyer & Address -->
                                <td class="p-2 align-middle" style="max-width: 170px;">
                                    <strong class="text-dark d-block text-truncate">{{ $order->contact_name ?: ($buyer ? trim(($buyer->prenom ?? '') . ' ' . ($buyer->nom ?? '')) : 'Buyer') }}</strong>
                                    <small class="text-muted d-block">{{ $order->phone ?: ($buyer ? $buyer->phone : 'N/A') }}</small>
                                    <small class="text-muted d-block text-truncate" title="{{ $order->delivery_address }}">{{ $order->delivery_address ?: 'No address' }}</small>
                                </td>

                                <!-- Shipping Partner -->
                                <td class="p-2 align-middle" style="max-width: 130px;">
                                    @if($order->courier_name || $order->tracking_id)
                                        <strong class="text-primary d-block text-truncate">{{ $order->courier_name ?: 'Courier' }}</strong>
                                        @if($order->tracking_id)
                                            <small class="text-dark d-block text-truncate">ID: {{ $order->tracking_id }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-light border text-muted font-10">Awaiting Courier</span>
                                    @endif
                                </td>

                                <!-- Stage -->
                                <td class="p-2 text-center align-middle">
                                    @php
                                        $s = strtolower($order->status);
                                        $bClass = 'badge-secondary';
                                        if ($s === 'placed') $bClass = 'badge-info';
                                        elseif ($s === 'processing') $bClass = 'badge-warning text-dark';
                                        elseif ($s === 'dispatched' || $s === 'shipped') $bClass = 'badge-primary';
                                        elseif ($s === 'out_for_delivery') $bClass = 'badge-info';
                                        elseif ($s === 'delivered' || $s === 'completed') $bClass = 'badge-success';
                                        elseif ($s === 'cancelled') $bClass = 'badge-danger';
                                    @endphp
                                    <span class="badge {{ $bClass }} px-2 py-1 text-uppercase font-10 font-weight-bold">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </span>
                                </td>

                                <!-- Action / Payout -->
                                <td class="p-2 text-center align-middle">
                                    @if(in_array(strtolower($order->status), ['rejected', 'cancelled']))
                                        <span class="badge badge-danger px-2 py-1 font-11 d-block mb-1">Cancelled</span>
                                        <small class="text-muted font-10">No payout (Buyer refunded)</small>
                                    @elseif($order->payout_status === 'released')
                                        <span class="badge badge-success px-2 py-1 font-11 d-block mb-1">Paid &amp; Settled</span>
                                        <small class="text-muted font-10">{{ $order->payout_released_at ? date('d M Y', strtotime($order->payout_released_at)) : '' }}</small>
                                    @elseif(in_array(strtolower($order->status), ['delivered', 'completed']))
                                        <form action="{{ route('admin.marketplace.orders.releasePayout', $order->id) }}" method="POST" onsubmit="return confirm('Release ₹{{ number_format($payoutAmount, 2) }} to seller wallet? (Commission: ₹{{ number_format($commAmount, 2) }})');">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success font-weight-bold px-2 py-1 rounded w-100" style="font-size: 11px;">
                                                Release Payout
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge badge-warning text-dark px-2 py-1 font-11 d-block mb-1">Awaiting Delivery</span>
                                        <small class="text-muted font-10">Releases after delivery</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    No Marketplace Orders Found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($orders->hasPages())
        <div class="card-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center font-12">
            <span class="text-muted">Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }}</span>
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

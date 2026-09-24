@extends("layouts.app")
@section("content")
<div class="container-fluid px-4 px-lg-5 py-4">
    <!-- Food Navigation Bar -->
    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-body py-2 px-3">
            <ul class="nav nav-pills card-header-pills flex-wrap">
                <li class="nav-item mr-1 mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.food.dashboard') ? 'active font-weight-bold shadow-sm' : 'text-dark' }}" href="{{ route('admin.food.dashboard') }}">
                        <i class="fa fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item mr-1 mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.food.live') ? 'active font-weight-bold shadow-sm' : 'text-dark' }}" href="{{ route('admin.food.live') }}">
                        <i class="fa fa-satellite-dish mr-1 text-danger"></i> Live Operations Radar
                    </a>
                </li>
                <li class="nav-item mr-1 mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.food.restaurants*') ? 'active font-weight-bold shadow-sm' : 'text-dark' }}" href="{{ route('admin.food.restaurants') }}">
                        <i class="fa fa-utensils mr-1"></i> Restaurants
                    </a>
                </li>
                <li class="nav-item mr-1 mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.food.orders*') ? 'active font-weight-bold shadow-sm' : 'text-dark' }}" href="{{ route('admin.food.orders') }}">
                        <i class="fa fa-receipt mr-1"></i> Orders Hub
                    </a>
                </li>
                <li class="nav-item mr-1 mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.food.charges*') ? 'active font-weight-bold shadow-sm' : 'text-dark' }}" href="{{ route('admin.food.charges') }}">
                        <i class="fa fa-money-bill-wave mr-1"></i> Charges & Delivery
                    </a>
                </li>
                <li class="nav-item mr-1 mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.food.settings*') ? 'active font-weight-bold shadow-sm' : 'text-dark' }}" href="{{ route('admin.food.settings') }}">
                        <i class="fa fa-cogs mr-1"></i> Global Settings
                    </a>
                </li>
                <li class="nav-item mr-1 mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.food.disputes*') ? 'active font-weight-bold shadow-sm' : 'text-dark' }}" href="{{ route('admin.food.disputes') }}">
                        <i class="fa fa-headset mr-1"></i> Support & Disputes
                    </a>
                </li>
            </ul>
        </div>
    </div>

    @if(session("success"))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-check-circle mr-2"></i> {{ session("success") }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session("error"))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-exclamation-triangle mr-2"></i> {{ session("error") }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @yield("food")
</div>
@endsection

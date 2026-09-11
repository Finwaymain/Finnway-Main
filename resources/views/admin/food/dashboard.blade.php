@extends("admin.food.layout")
@section("food")
<h3 class="mb-4">Food Command Dashboard</h3>
<div class="row">
@foreach([
  ["Live Orders",$liveOrders,"danger"],
  ["Open Restaurants",$activeRestaurants,"success"],
  ["Pending Approvals",$pendingApprovals,"warning"],
  ["Open Disputes",$openDisputes,"info"],
  ["Today Orders",$todayOrders,"primary"],
  ["Today Sales","₹".number_format($todaySales,2),"secondary"],
] as $c)
<div class="col-md-4 mb-3"><div class="card border-{{ $c[2] }}"><div class="card-body"><h6>{{ $c[0] }}</h6><h2>{{ $c[1] }}</h2></div></div></div>
@endforeach
</div>
<a href="{{ route("admin.food.live") }}" class="btn btn-danger">Open Live Command</a>
<a href="{{ route("admin.food.restaurants",["status"=>"pending_approval"]) }}" class="btn btn-warning">Pending Approvals</a>
@endsection

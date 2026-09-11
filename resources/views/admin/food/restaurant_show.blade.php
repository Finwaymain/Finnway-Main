@extends("admin.food.layout")
@section("food")
<h3>{{ $restaurant->name }}</h3>
<p>Status: <b>{{ $restaurant->onboarding_status }}</b> / Ops: <b>{{ $restaurant->operational_status }}</b></p>
<p>{{ $restaurant->address }}, {{ $restaurant->city }} {{ $restaurant->pincode }}</p>
<p>Owner: {{ $restaurant->owner_name }} {{ $restaurant->owner_phone }}</p>
<form method="post" action="{{ route("admin.food.restaurants.approve",$restaurant->id) }}" class="d-inline">@csrf<button class="btn btn-success">Approve</button></form>
<form method="post" action="{{ route("admin.food.restaurants.reject",$restaurant->id) }}" class="d-inline">@csrf<input name="reason" placeholder="Reject reason" class="form-control d-inline-block w-auto"><button class="btn btn-danger">Reject</button></form>
<form method="post" action="{{ route("admin.food.restaurants.suspend",$restaurant->id) }}" class="d-inline">@csrf<button class="btn btn-warning">Suspend</button></form>
<form method="post" action="{{ route("admin.food.orders.test") }}" class="mt-3">@csrf<input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}"><button class="btn btn-primary">Create Test Order</button></form>
@endsection

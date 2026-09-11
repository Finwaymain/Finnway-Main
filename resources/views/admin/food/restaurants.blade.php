@extends("admin.food.layout")
@section("food")
<h3>Restaurants</h3>
<form class="form-inline mb-3"><input name="q" value="{{ request("q") }}" class="form-control mr-2" placeholder="Search"><select name="status" class="form-control mr-2"><option value="">All</option>@foreach(["pending_approval","active","rejected","suspended","payment_pending"] as $s)<option value="{{ $s }}" @selected(request("status")==$s)>{{ $s }}</option>@endforeach</select><button class="btn btn-primary">Filter</button></form>
<table class="table table-bordered table-sm bg-white"><thead><tr><th>ID</th><th>Name</th><th>Type</th><th>City</th><th>Status</th><th>Ops</th><th></th></tr></thead><tbody>
@foreach($restaurants as $r)
<tr><td>{{ $r->id }}</td><td>{{ $r->name }}</td><td>{{ $r->business_type }}</td><td>{{ $r->city }}</td><td>{{ $r->onboarding_status }}</td><td>{{ $r->operational_status }}</td>
<td><a href="{{ route("admin.food.restaurants.show",$r->id) }}" class="btn btn-sm btn-info">View</a>
@if($r->onboarding_status==="pending_approval")
<form class="d-inline" method="post" action="{{ route("admin.food.restaurants.approve",$r->id) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
@endif</td></tr>
@endforeach
</tbody></table>
{{ $restaurants->withQueryString()->links() }}
@endsection

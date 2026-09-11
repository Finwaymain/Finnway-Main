@extends("admin.food.layout")
@section("food")
<h3>Food Charges</h3>
<p class="text-muted">Manage food module charges.</p>
@if(isset($rules))<pre>{{ json_encode($rules, JSON_PRETTY_PRINT) }}</pre>@endif
@if(isset($markups))<pre>{{ json_encode($markups, JSON_PRETTY_PRINT) }}</pre>@endif
@if(isset($charges))<pre>{{ json_encode($charges, JSON_PRETTY_PRINT) }}</pre>@endif
@if(isset($delivery))<pre>{{ json_encode($delivery, JSON_PRETTY_PRINT) }}</pre>@endif
@if(isset($settings))
<form method="post" action="{{ route("admin.food.settings.save") }}">@csrf
@foreach($settings as $k => $v)
<div class="form-group row"><label class="col-md-4">{{ $k }}</label><div class="col-md-6"><input class="form-control" name="{{ $k }}" value="{{ $v }}"></div></div>
@endforeach
<button class="btn btn-primary">Save Settings</button></form>
@endif
@if(isset($orders))
<table class="table table-sm table-bordered bg-white"><thead><tr><th>ID</th><th>Number</th><th>Restaurant</th><th>Status</th><th>Payable</th><th>Test</th></tr></thead><tbody>
@foreach($orders as $o)<tr><td>{{ $o->id }}</td><td>{{ $o->order_number }}</td><td>{{ optional($o->restaurant)->name }}</td><td>{{ $o->order_status }}</td><td>{{ $o->customer_payable }}</td><td>{{ $o->is_test ? "Y":"N" }}</td></tr>@endforeach
</tbody></table>
{{ $orders->links() }}
@endif
@if(isset($restaurants))
<div class="row">@foreach($restaurants as $r)<div class="col-md-3 mb-2"><div class="card p-2"><b>{{ $r->name }}</b><br>{{ $r->city }}</div></div>@endforeach</div>
@endif
@if(isset($settlements))
<table class="table table-bordered bg-white">@foreach($settlements as $s)<tr><td>{{ $s->settlement_number }}</td><td>{{ $s->net_amount }}</td><td>{{ $s->status }}</td></tr>@endforeach</table>
{{ $settlements->links() }}
@endif
@if(isset($disputes))
<table class="table table-bordered bg-white">@foreach($disputes as $d)<tr><td>{{ $d->ticket_number }}</td><td>{{ $d->issue_type }}</td><td>{{ $d->status }}</td>
<td><form method="post" action="{{ route("admin.food.disputes.resolve",$d->id) }}">@csrf<input name="resolution" class="form-control form-control-sm" placeholder="Resolution"><button class="btn btn-sm btn-success">Resolve</button></form></td></tr>@endforeach</table>
{{ $disputes->links() }}
@endif
@endsection

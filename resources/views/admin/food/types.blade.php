@extends("admin.food.layout")
@section("food")
<h3>Restaurant Types & Onboarding Fees</h3>
<table class="table table-bordered bg-white"><thead><tr><th>Code</th><th>Name</th><th>Fee</th><th>Approval</th><th>Active</th></tr></thead><tbody>
@foreach($types as $t)
<tr><td colspan="5">
<form method="post" action="{{ route("admin.food.types.save",$t->id) }}" class="form-row align-items-center">@csrf
<input type="hidden" name="code" value="{{ $t->code }}">
<div class="col"><input class="form-control" name="name" value="{{ $t->name }}"></div>
<div class="col"><input class="form-control" name="onboarding_fee" value="{{ $t->onboarding_fee }}"></div>
<div class="col"><select name="approval_mode" class="form-control"><option value="manual" @selected($t->approval_mode=="manual")>manual</option><option value="auto" @selected($t->approval_mode=="auto")>auto</option></select></div>
<div class="col"><label><input type="checkbox" name="is_active" value="1" @checked($t->is_active)> Active</label> <button class="btn btn-sm btn-primary">Save</button></div>
</form></td></tr>
@endforeach
</tbody></table>
@endsection

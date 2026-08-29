@extends('layouts.app')



@section('content')

<div class="page-wrapper">

	<div class="row page-titles">

		<div class="col-md-5 align-self-center">

			<h3 class="text-themecolor">{{trans('lang.business_model_settings')}}</h3>

		</div>



		<div class="col-md-7 align-self-center">

			<ol class="breadcrumb">

				<li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

				<li class="breadcrumb-item"><a href="{!! route('commission') !!}">{{trans('lang.business_model_settings')}}</a></li>

			</ol>

		</div>

	</div>





	<div class="container-fluid">

		<div class="row">

			<div class="col-12">

				<div class="card pb-4">

					<div class="card-body">



						<div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">

							{{trans('lang.processing')}}

						</div>

						<div class="error_top"></div>

						@if($errors->any())

						<div class="alert alert-danger">

							<ul>

								@foreach($errors->all() as $error)

								<li>{{ $error }}</li>

								@endforeach

							</ul>

						</div>

						@endif



						<div class="row restaurant_payout_create">

							<div class="restaurant_payout_create-inner">

								<fieldset>
									<legend><i class="mr-3 mdi mdi-shopping"></i>{{ trans('lang.subscription_based_model_settings') }}</legend>
									<div class="form-group row mt-1 ">
										<div class="form-group row mt-1 ">
											<div class="col-12 switch-box">
												<div class="switch-box-inner">
													<label class=" control-label">{{ trans('lang.subscription_based_model') }}</label>
													<label class="switch"> <input type="checkbox" name="subscription_model" data-id="{{$subscription->id}}" id="subscription_model" {{$subscription->subscription_model=="true" ? 'checked' : ''}}><span
															class=" slider round"></span></label>
													<i class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip"
														title="{{ trans('lang.subscription_tooltip') }}" aria-describedby="tippy-3"></i>
												</div>
											</div>
										</div>
									</div>
								</fieldset>

								<form action="{{ route('commission.update',$commission->id) }}" method="post" enctype="multipart/form-data" id="create_driver">

									@csrf

									@method("PUT")
									<fieldset>

										<legend>{{trans('lang.commission_based_model_settings')}}</legend>

										<div class="form-group row width-100 switch-box">
											<div class="switch-box-inner">
												<label class=" control-label">{{ trans('lang.commission_based_model') }}</label>
												<label class="switch"> <input type="checkbox" name="status" onclick="ShowHideDiv()"
														id="enable_commission" {{$commission->statut=='yes' ? 'checked' : ''}}><span class="slider round"></span></label>
												<i class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip"
													title="{{ trans('lang.commission_tooltip') }}" aria-describedby="tippy-3"></i>
											</div>
										</div>



										<div class="form-group row width-50 admin_commision_detail d-none">

											<label class="col-3 control-label">{{trans('lang.commission_type')}}</label>



											<select name="type" class="form-control">
												<option value="Percentage" {{$commission->type == 'Percentage' ? 'selected' : ''}}>Percentage</option>
												<option value="Fixed" {{$commission->type == 'Fixed' ? 'selected' : ''}}>Fixed</option>
											</select>
										</div>

										<div class="form-group row width-50 admin_commision_detail d-none">

											<label class="col-3 control-label">{{trans('lang.dashboard_total_admin_commission')}}</label>
											<input type="text" class="form-control code" name="value" value="{{ $commission->value }}">
										</div>


										<div class="form-group col-12 text-center btm-btn">
											<button type="submit" class="btn btn-primary  edit-setting-btn"><i class="fa fa-save"></i> {{ trans('lang.save')}}</button>
											<a href="{!! route('dashboard') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel')}}</a>
										</div>
									</fieldset>
								</form>
								<form action="{{ route('bulk.commission.update') }}" method="post" enctype="multipart/form-data">
									@csrf
									@method("PUT")
									<fieldset>
										<legend><i class="mr-3 mdi mdi-shopping"></i>{{ trans('lang.bulk_update')}}</legend>
										<div class="form-group row width-100">
											<label class="col-3 control-label">{{ trans('lang.driver') }} <i
													class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip"
													title="{{ trans('lang.bulk_update_commission_tooltip') }}" aria-describedby="tippy-3"></i>
											</label>
											<div class="col-12">
												<select id="driver_type" name="driver_type" class="form-control mb-2" required>
													<option value="all">{{ trans('lang.all_driver')}}</option>
													<option value="custom">{{ trans('lang.custom_driver')}}</option>
												</select>
												<select id="driver" name="driver[]" style="display:none" multiple class="form-control mt-3">
													@foreach ($drivers as $driver )
													<option value="{{$driver->id}}">{{$driver->prenom." ".$driver->nom }}</option>
													@endforeach
												</select>
												<div class="form-text text-muted">
													{{ trans("lang.select_driver") }}
												</div>
											</div>
										</div>
										<div class="form-group row width-50">
											<label class="col-4 control-label">{{ trans('lang.commission_type')}}</label>
											<div class="col-7">
												<select class="form-control bulk_commission_type" id="bulk_commission_type" name="bulk_commission_type">
													<option value="Percentage">{{trans('lang.coupon_percent')}}</option>
													<option value="Fixed">{{trans('lang.coupon_fixed')}}</option>
												</select>
											</div>
										</div>
										<div class="form-group row width-50">
											<label class="col-4 control-label">{{ trans('lang.admin_commission')}}</label>
											<div class="col-7">
												<input type="number" value="0" class="form-control bulk_admin_commission_value" name="bulk_admin_commission_value">
											</div>
										</div>
										<div class="form-group col-12 text-center">
											<div class="col-12">
												<button type="submit" id="bulk_update_btn" class="btn btn-primary edit-setting-btn"><i
														class="fa fa-save"></i> {{ trans('lang.bulk_update')}}</button>
											</div>
										</div>
									</fieldset>
								</form>
							</div>
						</div>

					</div>

				</div>

			</div>

		</div>

	</div>

</div>



@endsection



@section('scripts')
@if(session('success'))
<script>
	document.addEventListener("DOMContentLoaded", function() {
		Swal.fire('{{trans("lang.update_complete")}}', `commission model updated.`, 'success');
	});
</script>
@endif
@if(session('success_bulk'))
<script>
	document.addEventListener("DOMContentLoaded", function() {
		Swal.fire('{{trans("lang.bulk_update_completed")}}', 'success');
	});
</script>
@endif
<script>
	function ShowHideDiv() {
		var checkboxValue = $("#enable_commission").is(":checked");
		if (checkboxValue) {
			$(".admin_commision_detail").removeClass('d-none');
		} else {
			$(".admin_commision_detail").addClass('d-none');
		}
	}
	var enableCommision = "{{$commission->statut}}";
	if (enableCommision == 'yes') {
		$(".admin_commision_detail").removeClass('d-none');
	}
	/* toggal publish action code start*/

	$(document).on("click", "input[name='subscription_model']", function(e) {

		var subscription_model = $("#subscription_model").is(":checked");
		var id = $(this).attr('data-id');

		var userConfirmed = confirm(subscription_model ? "{{ trans('lang.enable_subscription_plan_confirm_alert')}}" : "{{ trans('lang.disable_subscription_plan_confirm_alert')}}");
		if (!userConfirmed) {
			$(this).prop("checked", !subscription_model);
			return;
		}
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: 'subscription-model-switch',
			method: "POST",
			data: {
				'ischeck': subscription_model,
				'id': id
			},
			success: function(response) {
				Swal.fire('{{trans("lang.update_complete")}}', `Subscription model updated.`, 'success');
			},


		});
	});
	$(document).ready(function() {
		$('#driver_type').on('change', function() {
			if ($('#driver_type').val() === 'custom') {
				$('#driver').show();
				$('#driver').select2({
					placeholder: "{{trans('lang.select_driver')}}",
					allowClear: true,
					width: '100%',
					dropdownAutoWidth: true
				});
			} else {
				$('#driver').hide();
				$('#driver').select2('destroy');
			}
		});
	});
</script>


@endsection
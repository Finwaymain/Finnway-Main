@extends('layouts.app')



@section('content')

<div class="page-wrapper">

	<div class="row page-titles">

		<div class="col-md-5 align-self-center">

			<h3 class="text-themecolor">{{trans('lang.email_template_plural')}}</h3>

		</div>

		<div class="col-md-7 align-self-center">

			<ol class="breadcrumb">

				<li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

				<li class="breadcrumb-item active">{{trans('lang.email_template_plural')}}</li>

				<li class="breadcrumb-item active">{{trans('lang.edit_email_template')}}</li>

			</ol>

		</div>



	</div>

	<div class="container-fluid">

		<div class="row">

			<div class="col-12">

				<div class="card pb-4">

					<div class="card-body">



						<div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{trans('lang.processing')}}</div>

						<div class="error_top"></div>



						<form action="{{ route('email_template.update',$template->id) }}" method="post" enctype="multipart/form-data" id="edit_template">

							@csrf

							@method("PUT")



							<div class="row restaurant_payout_create term-cond">

								<div class="restaurant_payout_create-inner">

									<fieldset>

										<legend>{{trans('lang.email_template')}}</legend>

										<div class="form-group row">

											<div class="col-12 p-0">

												<label for="send_admin">{{trans('lang.template_type')}}</label>

												@if($template->type=="payment_receipt")

												@php $type=trans("lang.payment_receipt") @endphp

												@elseif($template->type=="wallet_topup")

												@php $type=trans("lang.wallet_topup") @endphp

												@elseif($template->type=="payout_approve_disapprove")

												@php $type=trans("lang.payout_approve_disapprove") @endphp

												@elseif($template->type=="payout_request")

												@php $type=trans("lang.payout_request") @endphp

												@elseif($template->type=="new_registration")

												@php $type=trans("lang.new_registration") @endphp

												@elseif($template->type=="reset_password")

												@php $type=trans("lang.reset_password") @endphp

												@endif



												<input type="text" class="form-control col-7" name="type" id="type"

													value="{{$type}}" readonly>

											</div>

										</div>

										<div class="form-group row">

											<div class="col-12 p-0">

												<label for="send_admin">{{trans('lang.subject')}}</label>

												<input type="text" class="form-control col-7" name="subject" id="subject" value="{{$template->subject}}">

											</div>

										</div>





										<div class="form-group row">

											<div class="col-12 p-0">

												<label>{{trans('lang.message')}}</label>

												<textarea class="form-control col-7" name="message" id="message">{{$template->message}}</textarea>

											</div>

										</div>

										<div class="form-group row width-50">

											<div class="form-check">

												<input type="checkbox" class="send_admin" id="send_admin" name="send_admin" {{$template->send_to_admin=="true"?"checked":""}}>

												<label class="col-3 control-label" for="send_admin">{{trans('lang.is_send_to_admin')}}</label>



											</div>



										</div>





									</fieldset>

								</div>

							</div>

							<div class="form-group col-12 text-center btm-btn text-center">

								<button type="submit" class="btn btn-primary  edit-setting-btn"><i class="fa fa-save"></i> {{ trans('lang.save')}}</button>

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

<script type="text/javascript">
	$('#message').summernote({

		height: 400,



		toolbar: [

			['style', ['bold', 'italic', 'underline', 'clear']],

			['font', ['strikethrough', 'superscript', 'subscript']],

			['fontsize', ['fontsize']],

			['color', ['color']],

			['forecolor', ['forecolor']],

			['backcolor', ['backcolor']],

			['para', ['ul', 'ol', 'paragraph']],

			['height', ['height']],

			['view', ['codeview', 'help']],



		]

	});
	$('#edit_template').on('submit', function() {
		var editor = $('#message');
		if (editor.summernote('codeview.isActivated')) {
			editor.summernote('code', editor.summernote('code'));
		}

	});
</script>





@endsection
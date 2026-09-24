@extends('layouts.app')

@section('content')
<div class="page-wrapper">
	<div class="row page-titles">
		<div class="col-md-5 align-self-center">
			<h3 class="text-themecolor">{{trans('lang.edit_banner')}}</h3>
		</div>

		<div class="col-md-7 align-self-center">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
				<li class="breadcrumb-item"><a href="{!! route('banners') !!}">{{trans('lang.banners')}}</a></li>
				<li class="breadcrumb-item active">{{trans('lang.edit_banner')}}</li>
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
						<form action="{{ route('banners.update',$banners->id) }}" method="post" enctype="multipart/form-data">
							@csrf
							@method("PUT")
							<div class="row restaurant_payout_create">
								<div class="restaurant_payout_create-inner">
									<fieldset>
										<legend>{{trans('lang.edit_banner')}}</legend>

										<div class="form-group row width-100">
											<label class="col-3 control-label">Alt Text / Name <span class="text-danger">*</span></label>
											<div class="col-7">
												<input type="text" class="form-control" name="alt" value="{{ old('alt', $banners->alt ?: $banners->title) }}" placeholder="e.g. Summer Special Cashback Offer" required>
												<small class="form-text text-muted">Accessibility alt text and display label for the banner.</small>
											</div>
										</div>

										<div class="form-group row width-100">
											<label class="col-3 control-label">Redirect Link (URL)</label>
											<div class="col-7">
												<input type="url" class="form-control" name="link" value="{{ old('link', $banners->link) }}" placeholder="https://example.com/offer">
												<small class="form-text text-muted">When clicked, user will be redirected to this link with their details (e.g. <code>?phone=...</code>) automatically attached.</small>
											</div>
										</div>

										<div class="form-group row width-100">
											<label class="col-3 control-label">Target App <span class="text-danger">*</span></label>
											<div class="col-7">
												<select name="target_app" class="form-control" required>
													<option value="both" {{ old('target_app', $banners->target_app) == 'both' ? 'selected' : '' }}>Both (User & Driver App)</option>
													<option value="user" {{ old('target_app', $banners->target_app) == 'user' ? 'selected' : '' }}>User App Only</option>
													<option value="driver" {{ old('target_app', $banners->target_app) == 'driver' ? 'selected' : '' }}>Driver App Only</option>
												</select>
												<small class="form-text text-muted">Choose which app should display this sliding banner.</small>
											</div>
										</div>

										<div class="form-group row width-100">
											<label class="col-3 control-label">Banner Image</label>
											<div class="col-7">
												<input type="file" class="form-control" name="image" value="" onchange="readURL(this);">
												<small class="form-text text-muted">Leave empty to keep existing image. Recommended aspect ratio: 16:7 or 2:1 (e.g. 800x350 px, JPG/PNG/WebP).</small>
												<div style="padding-top: 10px;">
													@if (file_exists(public_path('assets/images/banners'.'/'.$banners->image)) && !empty($banners->image))
													<img class="rounded" style="max-height: 120px; max-width: 100%; border: 1px solid #ddd;" id="uploding_image" src="{{asset('assets/images/banners').'/'.$banners->image}}" alt="{{ $banners->alt ?? 'image' }}">
													@else
													<img class="rounded" style="max-height: 120px; max-width: 100%; border: 1px solid #ddd;" id="uploding_image" src="{{asset('assets/images/placeholder_image.jpg')}}" alt="image">
													@endif
												</div>
											</div>
										</div>

										<div class="form-group row width-100">
											<label class="col-3 control-label">{{trans('lang.title')}} (Optional)</label>
											<div class="col-7">
												<input type="text" class="form-control libelle" name="title" value="{{ old('title', $banners->title) }}" placeholder="Internal banner title">
											</div>
										</div>

										<div class="form-group row width-100">
											<label class="col-3 control-label">{{trans('lang.description')}} (Optional)</label>
											<div class="col-7">
												<textarea rows="3" class="form-control description" name="description" placeholder="Optional notes or details">{{ old('description', $banners->description) }}</textarea>
											</div>
										</div>

										<div class="form-group row width-100">
											<div class="form-check">
												@if ($banners->status === "yes")
												<input type="checkbox" class="user_active" id="status" name="status" checked="checked" value="1">
												@else
												<input type="checkbox" class="user_active" id="status" name="status" value="1">
												@endif
												<label class="col-3 control-label" for="status">{{trans('lang.status')}} (Active)</label>
											</div>
										</div>

								</div>



								</fieldset>


							</div>
					</div>


					<div class="form-group col-12 text-center btm-btn">
						<button type="submit" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i> {{ trans('lang.save')}}</button>
						<a href="{!! route('banners') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel')}}</a>
					</div>

					</form>

				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('scripts')
<script type="text/javascript">

$(document).ready(function () {
            $(".shadow-sm").hide();
        })

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#image_preview').show();
                    $('#uploding_image').attr('src', e.target.result);


                }

                reader.readAsDataURL(input.files[0]);
            }
        }
</script>
@endsection
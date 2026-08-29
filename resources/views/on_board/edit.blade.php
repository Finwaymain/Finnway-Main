@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.edit_on_boarding')}}</h3>
        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a
                        href="{!! route('on-boarding') !!}">{{trans('lang.on_boarding')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.edit_on_boarding')}}</li>
            </ol>
        </div>
    </div>


    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card pb-4">
                    <div class="card-body">

                        <div id="data-table_processing" class="dataTables_processing panel panel-default"
                            style="display: none;">
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
                        <form action="{{ route('on-boarding.update', $onboarding->id) }}" method="post"
                            enctype="multipart/form-data">
                            @csrf
                            @method("PUT")
                            <div class="row restaurant_payout_create">
                                <div class="restaurant_payout_create-inner">
                                    <fieldset>
                                        <legend>{{trans('lang.edit_on_boarding')}}</legend>

                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{trans('lang.title')}}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control libelle" name="title"
                                                    value="{{$onboarding->title}}">

                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{trans('lang.description')}}</label>
                                            <div class="col-7">
                                                <textarea type="text" rows="5" class="form-control description" name="description"
                                                    value="{{$onboarding->description}}">{{$onboarding->description}}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">{{trans('lang.onboarding_type')}}</label>
                                            <div class="col-7">
                                                <select  class="form-control type" name="type">
                                                    <option value="Customer" {{$onboarding->type == 'Customer' ? 'selected' : '' }}>{{trans("lang.customer")}}</option>
                                                    <option value="Driver" {{$onboarding->type == 'Driver' ? 'selected' : '' }}>{{trans("lang.driver")}}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">{{trans('lang.photo')}}</label>
                                            <div class="col-7">
                                                <input type="file" class="form-control" name="image" value=""
                                                    onchange="readURL(this);">
                                                @if (file_exists(public_path('assets/images/onboarding' . '/' . $onboarding->image)) && ! empty($onboarding->image))
                                                    <img class="rounded" style="width:50px" id="uploding_image"
                                                        src="{{asset('assets/images/onboarding') . '/' . $onboarding->image}}"
                                                        alt="image">
                                                @else
                                                    <img class="rounded" style="width:50px" id="uploding_image"
                                                        src="{{asset('assets/images/placeholder_image.jpg')}}" alt="image">
                                                @endif
                                            </div>
                                        </div>

                                      

                                </div>



                                </fieldset>


                            </div>
                    </div>


                    <div class="form-group col-12 text-center btm-btn">
                        <button type="submit" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i>
                            {{ trans('lang.save')}}</button>
                        <a href="{!! route('on-boarding') !!}" class="btn btn-default"><i
                                class="fa fa-undo"></i>{{ trans('lang.cancel')}}</a>
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

    $(document).ready(function() {
        $(".shadow-sm").hide();
    })

    function readURL(input) {
        console.log(input.files);
        if(input.files&&input.files[0]) {
            var reader=new FileReader();

            reader.onload=function(e) {
                $('#image_preview').show();
                $('#uploding_image').attr('src',e.target.result);


            }

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
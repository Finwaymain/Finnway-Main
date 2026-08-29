@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.tax_create')}}</h3>
        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('tax') !!}">{{trans('lang.administration_tools_tax')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.tax_create')}}</li>
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
                        <form action="{{route('tax.store')}}" method="post" enctype="multipart/form-data"
                            id="create_driver">
                            @csrf
                            <div class="row restaurant_payout_create">
                                <div class="restaurant_payout_create-inner">
                                    <fieldset>
                                        <legend>{{trans('lang.tax_create')}}</legend>

                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">{{trans('lang.label')}}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control libelle" name="libelle">

                                            </div>
                                        </div>

                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">{{trans('lang.tax_table')}}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control tax" name="tax">

                                            </div>
                                        </div>


                                        <div class="form-group row width-50">
                                            <label class="col-3 control-label">{{trans('lang.type_tax')}}</label>
                                            <div class="col-7">
                                                <select class="form-control commission_type" name="type">
                                                    <option value="Percentage">{{trans("lang.percentage")}}</option>
                                                    <option value="Fixed" selected>{{trans("lang.fix")}}</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row width-50">
                                            <label
                                                class="col-3 control-label">{{trans('lang.country')}}</label>
                                            <div class="col-7">
                                                <select  name="country" class="form-control country" >
                                                    
                                                         @foreach($countries as $country)
                                                    
                                                            <option value="{{$country->libelle}}">{{$country->libelle}}</option>
                                                
                                                         @endforeach
                                                    

                                                </select>

                                            </div>
                                        </div>

                                        <div class="form-group row width-50">
                                            <div class="form-check">
                                                <input type="checkbox" class="user_active" id="status" name="statut"
                                                    value="yes" checked>
                                                <label class="col-3 control-label"
                                                    for="status">{{trans('lang.status')}}</label>

                                            </div>
                                        </div>

                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Applicable Payment Methods</label>
                                            <div class="col-9">
                                                <div class="d-flex flex-wrap" style="gap: 12px;">
                                                    <label class="btn btn-outline-primary btn-sm mb-0" style="cursor: pointer; border-radius: 20px; padding: 6px 14px;">
                                                        <input type="checkbox" name="applicable_on[]" value="upi" checked> 📱 UPI
                                                    </label>
                                                    <label class="btn btn-outline-success btn-sm mb-0" style="cursor: pointer; border-radius: 20px; padding: 6px 14px;">
                                                        <input type="checkbox" name="applicable_on[]" value="wallet" checked> 👛 Wallet
                                                    </label>
                                                    <label class="btn btn-outline-warning btn-sm mb-0" style="cursor: pointer; border-radius: 20px; padding: 6px 14px;">
                                                        <input type="checkbox" name="applicable_on[]" value="cash" checked> 💵 Cash
                                                    </label>
                                                    <label class="btn btn-outline-info btn-sm mb-0" style="cursor: pointer; border-radius: 20px; padding: 6px 14px;">
                                                        <input type="checkbox" name="applicable_on[]" value="online" checked> 💳 Online
                                                    </label>
                                                </div>
                                                <small class="form-text text-muted mt-1">Select the payment methods where this charge/tax will be applied.</small>
                                            </div>
                                        </div>

                                </div>



                                </fieldset>


                            </div>
                    </div>


                    <div class="form-group col-12 text-center btm-btn">
                        <button type="submit" class="btn btn-primary  save-setting-btn"><i class="fa fa-save"></i> {{
                            trans('lang.save')}}</button>
                        <a href="{!! route('tax') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                            trans('lang.cancel')}}</a>
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
$('.country').select2();
</script>

@endsection
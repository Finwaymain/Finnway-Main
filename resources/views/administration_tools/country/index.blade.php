@extends('layouts.app')

@section('content')
        <div class="page-wrapper">

            <div class="row page-titles">

                <div class="col-md-5 align-self-center">

                    <h3 class="text-themecolor">{{trans('lang.administration_tools_country')}}</h3>

                </div>

                <div class="col-md-7 align-self-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{trans('lang.administration_tools')}}</li>
                        <li class="breadcrumb-item active">{{trans('lang.administration_tools_country')}}</li>
                    </ol>
                </div>

            </div>



            <div class="container-fluid">

                <div class="row">

                    <div class="col-12">

                        <div class="card">

                        <div class="card-body">
                        <div class="userlist-topsearch d-flex mb-3">
                                 
                            <div id="users-table_filter" class="ml-auto">
                              <label>{{ trans('lang.search_by')}}
                                <div class="form-group mb-0">

                                    <form action="{{ route('country') }}" method="get">
                                        @if(isset($_GET['selected_search']) &&  $_GET['selected_search'] != '')
                                        <select name="selected_search" id="selected_search" class="form-control input-sm">
                                        <option value="libelle" @if ($_GET['selected_search']=='libelle')
                                    selected="selected" @endif>{{ trans('lang.Name')}}</option>
                                        <option value="code" @if ($_GET['selected_search']=='code')
                                    selected="selected" @endif>{{ trans('lang.code')}}</option>
                                        </select>
                                        @else
                                        <select name="selected_search" id="selected_search" class="form-control input-sm">
                                        <option value="libelle">{{ trans('lang.Name')}}</option>
                                        <option value="code">{{ trans('lang.code')}}</option>
                                    </select>
                                    @endif
                                    <div class="search-box position-relative">
                                        @if(isset($_GET['search']) &&  $_GET['search'] != '')
                                        <input type = "text" class="search form-control" name="search" id = "search" value="{{$_GET['search']}}">
                                        @else
                                        <input type = "text" class="search form-control" name="search" id = "search">
                                        @endif
                                        <button type="submit" class="btn-flat position-absolute"><i class="fa fa-search"></i></button>
                                        <a class="btn btn-warning btn-flat" href="{{url('administration_tools/country')}}">Clear</a>
                                    </div>
                                    </form>
                                </div>
                               </label>
                            </div>
                        </div>

                                <div class="table-responsive m-t-10">

                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">

                                        <thead>

                                            <tr>

                                                <th>{{trans('lang.country_name')}}</th>
                                                <th>{{trans('lang.country_code')}}</th>
                                                <th>{{trans('lang.country_status')}}</th>
                                            </tr>

                                        </thead>

                                        <tbody id="append_list1">
                                         @if(count($countries) > 0)
                                            @foreach($countries as $country)
                                            <tr>
                                                <td>{{ $country->libelle}}</td>
                                                <td>{{ $country->code}}</td>
                                                <td>
                                                    @if ($country->statut=="yes")
                                                        <label class="switch"><input type="checkbox" checked id="{{$country->id}}" name="isSwitch"><span class="slider round"></span></label>
                                                    @else
                                                        <label class="switch"><input type="checkbox"  id="{{$country->id}}" name="isSwitch"><span class="slider round"></span></label>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                              @else
                                	<tr><td colspan="11" align="center">{{trans("lang.no_result")}}</td></tr>
                                @endif

                                        </tbody>

                                    </table>

                                    <nav aria-label="Page navigation example" class="custom-pagination">
                                    {{$countries->appends(request()->query())->links()}}
                                    </nav>
                                {{ $countries->Links('pagination.pagination') }}
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

$(document).ready(function() {
 $(".shadow-sm").hide();
})

$(document).on("click", "input[name='isSwitch']", function (e) {
var ischeck = $(this).is(':checked');
var id = this.id;
console.log(id);
$.ajax({
  headers: {
 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
},
   url : '../country/switch',
   method:"POST",
   data:{'ischeck':ischeck,'id':id},
   success: function(data){

   },
});

});

</script>

@endsection

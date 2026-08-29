@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor restaurantTitle">{{ trans('lang.subscription_plans') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.subscription_plans') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        @if(count($overviewPlans) > 0)
        <div class="overview-sec">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{trans("lang.overview")}}</h3>
                                <p class="mb-0 text-dark-2">{{trans("lang.see_overview_of_package_earning")}}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row subscription-list">

                                @foreach($overviewPlans as $data)
                                <div class="col-md-4">
                                    <div class="card card-box-with-icon">
                                        <div class="card-body">
                                            <span class="box-icon"><img src="{{asset('assets/images/subscription').'/'.$data->image}}"></span>
                                            <div class="card-box-with-content mt-3">
                                                <h4 class="text-dark-2 mb-1 h4 ">
                                                    @if(!empty($data->total_earning))
                                                    @php $totalEarning=$data->total_earning;@endphp
                                                    @else
                                                    @php $totalEarning=0;@endphp
                                                    @endif
                                                    @if($currency->symbol_at_right=="true")
                                                    {{number_format($totalEarning,$currency->decimal_digit)."".$currency->symbole}}
                                                    @else
                                                    {{$currency->symbole."".number_format($totalEarning,$currency->decimal_digit)}}
                                                    @endif
                                                </h4>
                                                <p class="mb-0 text-dark-2">{{$data->name}}</p>
                                            </div>
                                            <span class="background-img"><img src="{{asset('assets/images/subscription').'/'.$data->image}}"></span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">
                        <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                            {{trans('lang.processing')}}
                        </div>
                        <div class="userlist-topsearch d-flex mb-3">

                            <div class="userlist-top-left">

                                <a class="nav-link" href="{!! route('subscription-plans.create') !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.create_subscription_plan')}}</a>

                            </div>



                            <div id="users-table_filter" class="ml-auto">

                                <label>{{ trans('lang.search_by')}}

                                    <div class="form-group mb-0">

                                        <form action="{{ route('subscription-plans.index') }}" method="get">

                                            @if(isset($_GET['selected_search']) && $_GET['selected_search'] != '')

                                            <select name="selected_search" id="selected_search" class="form-control input-sm">

                                                <option value="name" @if ($_GET['selected_search']=='name' )
                                                    selected="selected" @endif>{{trans('lang.plan_name')}}</option>
                                                <option value="price" @if ($_GET['selected_search']=='price' )
                                                    selected="selected" @endif>{{trans('lang.price')}}</option>
                                              
                                            </select>
                                            @else
                                            <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                <option value="name">{{trans('lang.plan_name')}}</option>
                                                <option value="price">{{trans('lang.price')}}</option>
                                               
                                            </select>
                                            @endif

                                            <div class="search-box position-relative">
                                                @if(isset($_GET['search']) && $_GET['search'] != '')
                                                <input type="text" class="search form-control" name="search" id="search" value="{{$_GET['search']}}">
                                                @else
                                                <input type="text" class="search form-control" name="search" id="search">
                                                @endif
                                                <button type="submit" class="btn-flat position-absolute">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                                <a class="btn btn-warning btn-flat" href="{{route('subscription-plans.index')}}">Clear</a>
                                            </div>
                                        </form>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="table-responsive m-t-10">
                            <table id="subscriptionPlansTable"
                                class="display nowrap table table-hover table-striped table-bordered table table-striped dataTable no-footer dtr-inline collapsed"
                                cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                class="col-3 control-label" for="is_active">
                                                <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                        class="fa fa-trash"></i>
                                                    {{ trans('lang.all') }}</a></label>
                                        </th>
                                        <th>{{ trans('lang.image') }}</th>
                                        <th>{{ trans('lang.plan_name') }}</th>
                                        <th>{{ trans('lang.plan_price') }}</th>
                                        <th>{{ trans('lang.duration') }}</th>
                                        <th>{{ trans('lang.current_subscriber') }}</th>
                                        <th>{{ trans('lang.status') }}</th>
                                        <th>{{ trans('lang.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="append_list1">

                                    @if(count($subscriptionPlans) > 0)

                                    @foreach($subscriptionPlans as $value)

                                    <tr>

                                        <td class="delete-all">
                                            @if(intval($value->id)!=1)
                                            <input type="checkbox"

                                                id="is_open_{{$value->id}}"

                                                class="is_open"

                                                dataid="{{$value->id}}"><label

                                                class="col-3 control-label"

                                                for="is_open_{{$value->id}}"></label>
                                            @endif
                                        </td>

                                        @if (file_exists(public_path('assets/images/subscription'.'/'.$value->image))

                                        &&

                                        !empty($value->image))

                                        <td><img class="rounded" width="50px" 

                                                src="{{asset('assets/images/subscription').'/'.$value->image}}"

                                                alt="image">
                                        </td>

                                        @else

                                        <td><img class="rounded" width="50px"
                                                src="{{ asset('assets/images/placeholder_image.jpg')}}"
                                                alt="image">
                                        </td>

                                        @endif
                                        <td><a href="{{route('subscription-plans.edit', ['id' => $value->id])}}">{{

                                                $value->name}}</a></td>


                                        <td>
                                            @if(intVal($value->price!=0))
                                            @if($currency->symbol_at_right=="true")
                                            {{number_format($value->price,$currency->decimal_digit)."".$currency->symbole}}
                                            @else
                                            {{$currency->symbole."".number_format($value->price,$currency->decimal_digit)}}
                                            @endif
                                            @else
                                            {{trans("lang.free")}}
                                            @endif
                                        </td>

                                        <td>@if($value->expiryDay=='-1')
                                            {{trans('lang.unlimited')}}
                                            @else
                                            {{$value->expiryDay}} {{ trans("lang.days") }}
                                            @endif
                                        </td>

                                        <td><a href="{{route('current-subscriber.list', ['id' => $value->id])}}">{{$value->subscribers_count}}</a></td>


                                        <td>
                                            @if(intval($value->id)!=1)
                                            @if ($value->isEnable=="true")

                                            <label class="switch"><input type="checkbox" id="{{$value->id}}"

                                                    name="isActive" checked><span

                                                    class="slider round"></span></label>

                                            @else

                                            <label class="switch"><input type="checkbox" id="{{$value->id}}"

                                                    name="isActive"><span

                                                    class="slider round"></span></label>

                                            @endif
                                            @endif

                                        </td>

                                        <td class="action-btn">
                                            <div class="d-inline-flex align-items-center" style="gap:6px;">
                                                <a href="{{route('subscription-plans.edit', ['id' => $value->id])}}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i> Edit</a>
                                                @if(intval($value->id)!=1)
                                                <a href="{{route('subscription-plans.delete', ['id' => $value->id])}}" class="btn btn-sm btn-outline-danger delete-btn" title="Delete"><i class="fa fa-trash"></i> Delete</a>
                                                @endif
                                            </div>
                                        </td>

                                    </tr>

                                    @endforeach

                                    @else

                                    <tr>

                                        <td colspan="11" align="center">{{trans("lang.no_result")}}</td>

                                    </tr>

                                    @endif

                                </tbody>
                            </table>
                            <nav aria-label="Page navigation example" class="custom-pagination">

                                {{ $subscriptionPlans->appends(request()->query())->links() }}

                            </nav>

                            {{ $subscriptionPlans->Links('pagination.pagination') }}

                        </div>
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
<script>
    $("#is_active").click(function() {
        $("#subscriptionPlansTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function() {
        if ($('#subscriptionPlansTable .is_open:checked').length) {
            if (confirm('{{trans("lang.selected_delete_alert")}}')) {
                var arrayUsers = [];
                $('#subscriptionPlansTable .is_open:checked').each(function() {
                    var dataId = $(this).attr('dataId');
                    arrayUsers.push(dataId);
                });

                arrayUsers = JSON.stringify(arrayUsers);
                var url = "{{url('subscription-plans/delete', 'id')}}";
                url = url.replace('id', arrayUsers);
                $(this).attr('href', url);
            }
        } else {
            alert('Please Select Any One Record .');
        }
    });

    /* toggal publish action code start*/

    $(document).on("click", "input[name='isActive']", function(e) {

        var ischeck = $(this).is(':checked');
        var id = this.id;
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: 'subscription-plans-switch',
            method: "POST",
            data: {
                'ischeck': ischeck,
                'id': id
            },
            success: function(response) {

            },
            error: function(xhr) {
                alert(xhr.responseJSON.message);
                location.reload();
            }

        });
    });
</script>
@endsection
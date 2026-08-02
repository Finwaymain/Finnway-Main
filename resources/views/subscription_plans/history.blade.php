@extends('layouts.app')


@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.subscription_history')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.subscription_history_table')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>

    <div class="container-fluid">
        
            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                                {{trans('lang.processing')}}
                            </div>
                            <div class="userlist-topsearch d-flex mb-3">

                                <div id="users-table_filter" class="ml-auto">

                                    <label>{{ trans('lang.search_by')}}

                                        <div class="form-group mb-0">

                                            <form action="{{ route('driver.subscriptionHistory') }}" method="get">

                                                @if(isset($_GET['selected_search']) && $_GET['selected_search'] != '')

                                                <select name="selected_search" id="selected_search" class="form-control input-sm">

                                                    <option value="name" @if ($_GET['selected_search']=='name' )
                                                        selected="selected" @endif>{{trans('lang.plan_name')}}</option>
                                                    <option value="driver" @if ($_GET['selected_search']=='driver' )
                                                        selected="selected" @endif>{{trans('lang.driver')}}</option>
                                                  

                                                </select>
                                                @else
                                                <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                    <option value="name">{{trans('lang.plan_name')}}</option>
                                                    <option value="driver">{{trans('lang.driver')}}</option>

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
                                                    <a class="btn btn-warning btn-flat" href="{{route('driver.subscriptionHistory')}}">Clear</a>
                                                </div>
                                            </form>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="table-responsive m-t-10">
                                <table id="subscriptionHistoryTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active"><a id="deleteAll"
                                                        class="do_not_delete" href="javascript:void(0)"><i
                                                            class="fa fa-trash"></i> {{trans('lang.all')}}</a></label>
                                            </th>
                                            <th>{{trans('lang.driver')}}</th>
                                            <th>{{trans('lang.plan_name')}}</th>
                                            <th>{{trans('lang.plan_type')}}</th>
                                            <th>{{trans('lang.plan_expires_at')}}</th>
                                            <th>{{trans('lang.purchase_date')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list1">
                                        @foreach ($history as $value )
                                        <tr>
                                            <td class="delete-all">
                                                <input type="checkbox"

                                                    id="is_open_{{$value->id}}"

                                                    class="is_open"

                                                    dataid="{{$value->id}}"><label

                                                    class="col-3 control-label"

                                                    for="is_open_{{$value->id}}"></label>
                                            </td>
                                            <td><a href="{{route('driver.show', ['id' => $value->user_id])}}">{{$value->prenom.' '.$value->nom}}</a></td>
                                            <td>{{$value->subscription_plan['name']}}</td>
                                            <td>
                                                @if($value->subscription_plan['type']=='free')
                                                <span class="badge badge-success">{{$value->subscription_plan['type']}}</span>
                                                @else
                                                <span class="badge badge-danger">{{$value->subscription_plan['type']}}</span>
                                                @endif

                                            </td>
                                            <td>
                                                @if($value->expiry_date==null)
                                                {{trans("lang.unlimited")}}
                                                @else
                                                <span class="date">{{ date('d F Y',strtotime($value->expiry_date))}}</span>

                                                <span class="time">{{ date('h:i A',strtotime($value->expiry_date))}}</span>

                                                @endif
                                            </td>
                                            <td>
                                                <span class="date">{{ date('d F Y',strtotime($value->created_at))}}</span>

                                                <span class="time">{{ date('h:i A',strtotime($value->created_at))}}</span>

                                            </td>

                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <nav aria-label="Page navigation example" class="custom-pagination">

                                    {{ $history->appends(request()->query())->links() }}

                                </nav>

                                {{ $history->Links('pagination.pagination') }}
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
        $("#subscriptionHistoryTable .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(function() {
        if ($('#subscriptionHistoryTable .is_open:checked').length) {
            if (confirm('{{trans("lang.selected_delete_alert")}}')) {
                var arrayUsers = [];
                $('#subscriptionHistoryTable .is_open:checked').each(function() {
                    var dataId = $(this).attr('dataId');
                    arrayUsers.push(dataId);

                });
                arrayUsers = JSON.stringify(arrayUsers);
                var url = "{{url('subscription-history/delete', 'id')}}";
                url = url.replace('id', arrayUsers);
                $(this).attr('href', url);
            }
        } else {
            alert('Please Select Any One Record .');
        }
    });
</script>

@endsection
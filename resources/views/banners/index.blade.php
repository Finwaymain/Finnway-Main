@extends('layouts.app')

@section('content')
    <div class="page-wrapper">


        <div class="row page-titles">

            <div class="col-md-5 align-self-center">

                <h3 class="text-themecolor">{{trans('lang.banners')}}</h3>

            </div>

            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.banners')}}</li>

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

                            <div class="userlist-topsearch d-flex mb-3">
                                <div class="userlist-top-left">
                                    <a class="nav-link " href="{{ route('banners.create')}}"><i
                                                class="fa fa-plus mr-2"></i>{{trans('lang.create_banner')}}</a>
                                </div>
                                <div id="users-table_filter" class="ml-auto">
                                    <label>{{ trans('lang.search_by')}}
                                        <div class="form-group mb-0">

                                            <form action="{{ route('banners') }}" method="get">
                                                @if(isset($_GET['selected_search']) && $_GET['selected_search'] != '')
                                                    <select name="selected_search" id="selected_search"
                                                            class="form-control input-sm">
                                                        <option value="title" @if ($_GET['selected_search']=='title' )
                                                        selected="selected" @endif>{{ trans('lang.title')}}</option>

                                                    </select>
                                                @else
                                                    <select name="selected_search" id="selected_search"
                                                            class="form-control input-sm">
                                                        <option value="title">{{ trans('lang.title')}}</option>

                                                    </select>
                                                @endif
                                                <div class="search-box position-relative">
                                                    @if(isset($_GET['search']) && $_GET['search'] != '')
                                                        <input type="text" class="search form-control" name="search"
                                                               id="search"
                                                               value="{{$_GET['search']}}">
                                                    @else
                                                        <input type="text" class="search form-control" name="search"
                                                               id="search">
                                                    @endif

                                                    <button type="submit" class="btn-flat position-absolute"><i
                                                                class="fa fa-search"></i></button>
                                                    <a class="btn btn-warning btn-flat"
                                                       href="{{url('banners')}}">Clear</a>
                                                </div>
                                            </form>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="table-responsive m-t-10">


                                <table id="example24"
                                       class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                       cellspacing="0" width="100%">

                                    <thead>

                                    <tr>
                                        <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active"><a id="deleteAll"
                                                                                                   class="do_not_delete"
                                                                                                   href="javascript:void(0)"><i
                                                            class="fa fa-trash"></i> All</a></label></th>
                                        <th>{{trans('lang.photo')}}</th>
                                        <th>{{trans('lang.title')}}</th>
                                        <th>{{trans('lang.description')}}</th>
                                        <th>{{trans('lang.status')}}</th>
                                        <th>{{trans('lang.actions')}}</th>

                                    </tr>

                                    </thead>

                                    <tbody id="append_list1">
                                    @if(count($banners) > 0)
                                        @foreach($banners as $value)
                                            <tr>
                                                <td class="delete-all"><input type="checkbox"
                                                                              id="is_open_{{$value->id}}"
                                                                              class="is_open"
                                                                              dataid="{{$value->id}}"><label
                                                            class="col-3 control-label"
                                                            for="is_open_{{$value->id}}"></label></td>
                                                @if (file_exists(public_path('assets/images/banners'.'/'.$value->image))
                                                &&
                                                !empty($value->image))
                                                    <td><img class="rounded" style="width:50px"
                                                             src="{{asset('assets/images/banners').'/'.$value->image}}"
                                                             alt="image"></td>
                                                @else
                                                    <td><img class="rounded" style="width:50px"
                                                             src="{{ asset('assets/images/placeholder_image.jpg')}}"
                                                             alt="image">
                                                    </td>
                                                @endif

                                                <td><a href="{{route('banners.edit', ['id' => $value->id])}}">{{
                                                $value->title}}</a></td>
                                                
                                                <td>{{$value->description}}</td>

                                                <td>
                                                    @if ($value->status=="yes")
                                                        <label class="switch"><input type="checkbox" id="{{$value->id}}"
                                                                                     name="isActive" checked><span
                                                                    class="slider round"></span></label>
                                                    @else
                                                        <label class="switch"><input type="checkbox" id="{{$value->id}}"
                                                                                     name="isActive"><span
                                                                    class="slider round"></span></label>
                                                    @endif
                                                </td>
                                                <td class="action-btn">
                                                    <a href="{{route('banners.edit', ['id' => $value->id])}}"><i class="fa fa-edit"></i></a>
                                                    <a href="{{route('banners.delete', ['id' => $value->id])}}" class="delete-btn"><i
                                                                class="fa fa-trash"></i></a>
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
                                    {{ $banners->appends(request()->query())->links() }}
                                </nav>
                                {{ $banners->Links('pagination.pagination') }}
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
    <script type="text/javascript">


        $(document).ready(function () {
            $(".shadow-sm").hide();
        })

        $("#is_active").click(function () {
            $("#example24 .is_open").prop('checked', $(this).prop('checked'));

        });

        $("#deleteAll").click(function () {
            if ($('#example24 .is_open:checked').length) {
                if (confirm('{{trans("lang.selected_delete_alert")}}')) {
                    var arrayUsers = [];
                    $('#example24 .is_open:checked').each(function () {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);

                    });

                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{url('banners/delete', 'id')}}";
                    url = url.replace('id', arrayUsers);

                    $(this).attr('href', url);
                }
            } else {
                alert('{{trans("lang.select_delete_alert")}}');
            }
        });
        /* toggal publish action code start*/
        $(document).on("click", "input[name='isActive']", function (e) {

            var ischeck = $(this).is(':checked');
            var id = this.id;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: 'banners-switch',
                method: "POST",
                data: {'ischeck': ischeck, 'id': id},
                success: function (data) {

                },
            });

        });

    </script>

@endsection
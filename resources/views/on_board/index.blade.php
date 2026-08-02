@extends('layouts.app')

@section('content')
<div class="page-wrapper">


    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.on_boarding')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.on_boarding')}}</li>

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

                            <div id="users-table_filter" class="ml-auto">
                                <label>{{ trans('lang.search_by')}}
                                    <div class="form-group mb-0">

                                        <form action="{{ route('on-boarding') }}" method="get">
                                            @if(isset($_GET['selected_search']) && $_GET['selected_search'] != '')
                                                <select name="selected_search" id="selected_search"
                                                    class="form-control input-sm">
                                                    <option value="title" @if ($_GET['selected_search'] == 'title')
                                                    selected="selected" @endif>{{ trans('lang.title')}}</option>
                                                    <option value="type" @if ($_GET['selected_search'] == 'type')
                                                    selected="selected" @endif>{{ trans('lang.onboarding_type')}}</option>
                                                </select>
                                            @else
                                                <select name="selected_search" id="selected_search"
                                                    class="form-control input-sm">
                                                    <option value="title">{{ trans('lang.title')}}</option>
                                                    <option value="type">{{ trans('lang.onboarding_type')}}</option>

                                                </select>
                                            @endif
                                            <div class="search-box position-relative">
                                                @if(isset($_GET['search']) && $_GET['search'] != '')
                                                    <input type="text" class="search form-control" name="search" id="search"
                                                        value="{{$_GET['search']}}">
                                                @else
                                                    <input type="text" class="search form-control" name="search"
                                                        id="search">
                                                @endif

                                                <button type="submit" class="btn-flat position-absolute"><i
                                                        class="fa fa-search"></i></button>
                                                <a class="btn btn-warning btn-flat"
                                                    href="{{url('on-boarding')}}">Clear</a>
                                            </div>
                                        </form>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="table-responsive m-t-10">


                            <table id="example24"
                                class="display  table table-hover table-striped table-bordered table table-striped"
                                cellspacing="0" width="100%">

                                <thead>

                                    <tr>
                                        <th>{{trans('lang.photo')}}</th>
                                        <th>{{trans('lang.title')}}</th>
                                        <th>{{trans('lang.description')}}</th>
                                        <th>{{trans('lang.onboarding_type')}}</th>
                                        <th>{{trans('lang.actions')}}</th>

                                    </tr>

                                </thead>

                                <tbody id="append_list1">
                                    @if(count($onboarding) > 0)
                                                                @foreach($onboarding as $value)
                                                                                            <tr>
                                                                                                @if (file_exists(public_path('assets/images/onboarding' . '/' . $value->image))
                                                                                                    &&
                                                                                                    ! empty($value->image))
                                                                                                                                <td><img class="rounded" style="width:50px"
                                                                                                                                        src="{{asset('assets/images/onboarding') . '/' . $value->image}}"
                                                                                                                                        alt="image"></td>
                                                                                                @else
                                                                                                    <td><img class="rounded" style="width:50px"
                                                                                                            src="{{ asset('assets/images/placeholder_image.jpg')}}" alt="image">
                                                                                                    </td>
                                                                                                @endif

                                                                                                <td><a href="{{route('on-boarding.edit', ['id' => $value->id])}}">{{
                                                                    $value->title}}</a></td>

                                                                                                <td>
                                                                                                    {{$value->description}}
                                                                                                </td>
                                                                                                <td>
                                                                                                    {{$value->type}}
                                                                                                </td>
                                                                                                <td class="action-btn">
                                                                                                    <a href="{{route('on-boarding.edit', ['id' => $value->id])}}"><i
                                                                                                            class="fa fa-edit"></i></a>
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
                                {{ $onboarding->appends(request()->query())->links() }}
                            </nav>
                            {{ $onboarding->Links('pagination.pagination') }}
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


    $(document).ready(function() {
        $(".shadow-sm").hide();
    })

    
</script>

@endsection
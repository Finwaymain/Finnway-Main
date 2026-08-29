@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Bread crumb and right sidebar toggle -->
    <!-- ============================================================== -->
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.vehicle_type')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.vehicle_type')}}</li>
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

                        <div id="data-table_processing" class="dataTables_processing panel panel-default"
                             style="display: none;">{{trans('lang.processing')}}
                        </div>

                        <div class="userlist-topsearch d-flex mb-3">

                            <div class="userlist-top-left">
                                <a class="nav-link" href="{!! route('vehicle.creates') !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.create_vehicle')}}</a>
                            </div>

                            <div id="users-table_filter" class="ml-auto">
                                <label>{{ trans('lang.search_by')}}
                                <div class="form-group">

                                    <form action="{{ route('vehicle-type') }}" method="get">


                                        @if(isset($_GET['selected_search']) && $_GET['selected_search'] != '')
                                        <select name="selected_search" id="selected_search"
                                                class="form-control input-sm">
                                            <option value="libelle" @if ($_GET[
                                            'selected_search']=='libelle')
                                            selected="selected" @endif>{{trans('lang.vehicle_type')}}</option>

                                        </select>
                                        @else
                                        <select name="selected_search" id="selected_search"
                                                class="form-control input-sm">
                                            <option value="libelle">{{trans('lang.vehicle_type')}}</option>

                                        </select>
                                        @endif
                                        <div class="search-box position-relative">
                                            @if(isset($_GET['search']) && $_GET['search'] != '')
                                            <input type="text" class="search form-control" name="search" id="search"
                                                   value="{{$_GET['search']}}">
                                            @else
                                            <input type="text" class="search form-control" name="search" id="search">
                                            @endif
                                            <button type="submit" class="btn-flat position-absolute"><i
                                                        class="fa fa-search"></i></button>
                                            <a class="btn btn-warning btn-flat"
                                               href="{{url('vehicle/index')}}">Clear</a>
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
                                    <th>{{trans('lang.extra_image')}}</th>
                                    <th>{{trans('lang.vehicle_type')}}</th>
                                    <th>Base Fare</th>
                                    <th>Per KM Rate</th>
                                    <th>Delivery / KM</th>
                                    <th>Min Delivery Charge</th>
                                    <th>{{trans('lang.status')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
                                </tr>
                                </thead>
                                <tbody id="append_list12">
                                 @if(count($types) > 0)
                                @foreach($types as $type)

                                <tr>
                                    <td class="delete-all"><input type="checkbox"
                                                                  id="is_open_{{$type->id}}"
                                                                  class="is_open"
                                                                  dataid="{{$type->id}}"><label
                                                class="col-3 control-label"
                                                for="is_open_{{$type->id}}"></label></td>
                                    @if (file_exists(public_path('assets/images/type_vehicle'.'/'.$type->image)) &&
                                    !empty($type->image))
                                    <td><img class="rounded" style="width:50px"
                                             src="{{asset('assets/images/type_vehicle').'/'.$type->image}}" alt="image">
                                    </td>
                                    @else
                                    <td><img class="rounded" style="width:50px"
                                             src="{{asset('assets/images/placeholder_image.jpg')}}" alt="image"></td>
                                    @endif
                                    <td>
                                        <a href="{{route('vehicle.edits', ['id' => $type->id])}}" class="font-weight-bold text-dark">
                                            {{ $type->libelle}}
                                        </a>
                                    </td>

                                    <td>
                                        <span class="badge badge-primary font-weight-bold" style="font-size: 13px; padding: 6px 10px;">
                                            ₹{{ number_format((float)($type->base_price ?? $type->prix ?? 0), 2) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge badge-info font-weight-bold" style="font-size: 13px; padding: 6px 10px;">
                                            ₹{{ number_format((float)($type->per_km_price ?? 0), 2) }} / km
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge badge-success font-weight-bold" style="font-size: 13px; padding: 6px 10px;">
                                            ₹{{ number_format((float)($type->delivery_charges_per_km ?? 0), 2) }} / km
                                        </span>
                                    </td>

                                    <td>
                                        <span class="text-muted">
                                            ₹{{ number_format((float)($type->minimum_delivery_charges ?? 0), 2) }}
                                            @if(!empty($type->minimum_delivery_charges_within_km))
                                                <small>({{ $type->minimum_delivery_charges_within_km }} km)</small>
                                            @endif
                                        </span>
                                    </td>

                                    <td>@if ($type->status=="Yes")
                                      <label class="switch"><input type="checkbox" checked id="{{$type->id}}" name="isActive"><span class="slider round"></span></label>
                                      @else <label class="switch"><input type="checkbox"  id="{{$type->id}}" name="isActive"><span class="slider round"></span></label>
                                       @endif
                                    </td>

                                    <td class="action-btn">
                                        <a href="{{route('vehicle.edits', ['id' => $type->id])}}" class="btn btn-sm btn-info text-white mr-1" title="Edit Rates & Vehicle">
                                            <i class="fa fa-edit"></i> Edit Rates
                                        </a>
                                        <a id="{{$type->id}}" class="delete-btn btn btn-sm btn-danger text-white"
                                           href="{{route('vehicle-type.delete', ['id' => $type->id])}}" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>

                                </tr>
                                @endforeach
                                 @else
                                		<tr><td colspan="11" align="center">{{trans("lang.no_result")}}</td></tr>
                                	@endif
                                </tbody>
                            </table>


                            <nav aria-label="Page navigation example" class="custom-pagination">
                                {{ $types->appends(request()->query())->links() }}
                            </nav>
{{ $types->links('pagination.pagination') }}
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
            if (confirm('Are You Sure want to Delete Selected Data ?')) {
                var arrayUsers = [];
                $('#example24 .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');
                    arrayUsers.push(dataId);

                });

                arrayUsers = JSON.stringify(arrayUsers);
                var url = "{{url('vehicle-type/delete', 'id')}}";
                url = url.replace('id', arrayUsers);

                $(this).attr('href', url);
            }
        } else {
            alert('Please Select Any One Record .');
        }
    });
       $(document).on("click", "input[name='isActive']", function (e) {
           var ischeck = $(this).is(':checked');
           var id = this.id;

           $.ajax({
             headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
              url : '../vehicle-type/switch',
              method:"POST",
              data:{'ischeck':ischeck,'id':id},
              success: function(data){

              },
           });

       });

</script>


@endsection

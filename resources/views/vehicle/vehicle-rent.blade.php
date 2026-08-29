@extends('layouts.app')



@section('content')

<div class="page-wrapper">

    <!-- ============================================================== -->

    <!-- Bread crumb and right sidebar toggle -->

    <!-- ============================================================== -->

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.vehicle_renting')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item">

                    <a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a>

                </li>

                <li class="breadcrumb-item active">

                    {{trans('lang.vehicle_rent')}}

                </li>

            </ol>

        </div>

        <div></div>

    </div>



    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">

                        </div>
                        <form action="{{ route('vehicle-rent') }}" method="get" id="filterForm">
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control status_selector filteredRecords" name="status_selector">
                                        <option value="">{{trans("lang.status")}}</option>
                                        <option value="in progress" {{isset($_GET['status_selector']) && $_GET['status_selector'] =='in progress' ? 'selected ':'' }}>{{ trans('lang.in_progress')}}</option>
                                        <option value="accepted" {{isset($_GET['status_selector']) && $_GET['status_selector'] =='accepted' ? 'selected ':'' }}>{{ trans('lang.accepted')}}</option>
                                        <option value="completed" {{isset($_GET['status_selector']) && $_GET['status_selector'] =='completed' ? 'selected ':'' }}>{{ trans('lang.completed')}}</option>
                                        <option value="rejected" {{isset($_GET['status_selector']) && $_GET['status_selector'] =='rejected' ? 'selected ':'' }}>{{ trans('lang.rejected')}}</option>

                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <input type="text" placeholder="dd-mm-yyyy" class="form-control filteredRecords" id="daterange" name="daterange" value="{{ request('daterange') }}" readonly />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--15">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4 total_ride">{{ $totalRides }}</h4>
                                <p class="mb-0 small text-dark-2">{{trans('lang.total_orders')}}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/total_rides.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--5">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4 placed_ride">{{ $totalNewRides }}</h4>
                                <p class="mb-0 small text-dark-2">{{trans('lang.new_orders')}}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/placed_rides.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--1">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4 active_ride">{{ $totalOnRides }}</h4>
                                <p class="mb-0 small text-dark-2">{{trans('lang.active_orders')}}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/active_rides.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--24">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4 completed_ride">{{ $totalCompletedRides }}</h4>
                                <p class="mb-0 small text-dark-2">{{trans('lang.complete_orders')}}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/complete_rides.png') }}"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-12">

                <div class="card">



                    <div class="card-body">



                        <div id="data-table_processing" class="dataTables_processing panel panel-default"

                            style="display: none;">

                            {{trans('lang.processing')}}

                        </div>



                        <div class="userlist-topsearch d-flex mb-3">



                            <div id="users-table_filter" class="ml-auto">

                                <label>{{ trans('lang.search_by')}}

                                    <div class="form-group mb-0">

                                        <form action="{{ route('vehicle-rent') }}" method="get">



                                            @if(isset($_GET['selected_search']) && $_GET['selected_search'] != '')

                                            <select name="selected_search" id="selected_search"

                                                class="form-control input-sm">

                                                <option value="vehicle_type" @if ($_GET[ 'selected_search' ]=='vehicle_type' )

                                                    selected="selected" @endif>{{trans('lang.vehicle_type')}}</option>

                                                <option value="customer" @if ($_GET[ 'selected_search' ]=='customer' )

                                                    selected="selected" @endif>{{trans('lang.customer')}}</option>

                                            </select>

                                            @else

                                            <select name="selected_search" id="selected_search"

                                                class="form-control input-sm">

                                                <option value="vehicle_type	">{{trans('lang.vehicle_type')}}</option>

                                                <option value="customer">{{trans('lang.customer')}}</option>

                                            </select>

                                            @endif

                                            <div class="search-box position-relative">

                                                @if(isset($_GET['search']) && $_GET['search'] != '')

                                                <input type="text" class="search form-control" name="search"

                                                    id="search" value="{{$_GET['search']}}">

                                                @else

                                                <input type="text" class="search form-control" name="search"

                                                    id="search">

                                                @endif

                                                <button type="submit" class="btn-flat position-absolute"><i

                                                        class="fa fa-search"></i></button>

                                                <a class="btn btn-warning btn-flat"

                                                    href="{{url('vehicle/vehicle-rent')}}">Clear</a>

                                            </div>

                                        </form>



                                    </div>

                                </label>

                            </div>

                        </div>
                        <div class="dropdown text-right">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> {{trans("lang.export_as")}}
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'excel', 'model' => 'VehicleLocation']) }}">{{ trans("lang.export_excel") }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'pdf', 'model' => 'VehicleLocation']) }}">{{ trans("lang.export_pdf") }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'csv', 'model' => 'VehicleLocation']) }}">{{ trans("lang.export_csv") }}</a></li>
                            </ul>
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

                                        <th>{{trans('lang.vehicle_type')}}</th>

                                        <th>{{trans('lang.customer')}}</th>

                                        <th>{{trans('lang.number_of_days')}}</th>

                                        <th>{{trans('lang.start_date')}}</th>

                                        <th>{{trans('lang.end_date')}}</th>

                                        <th>{{trans('lang.contact')}}</th>

                                        <th>{{trans('lang.status')}}</th>

                                        <th>{{trans('lang.created_at')}}</th>

                                        <th>{{trans('lang.modified_at')}}</th>

                                        <th>{{trans('lang.actions')}}</th>

                                    </tr>

                                </thead>

                                <tbody id="append_list12">

                                    @if(count($rentals) > 0)

                                    @foreach($rentals as $rental)

                                    <tr>

                                        <td class="delete-all" id="deleteAll"><input type="checkbox"

                                                id="is_open_{{$rental->id}}"

                                                class="is_open"

                                                dataid="{{$rental->id}}"><label

                                                class="col-3 control-label"

                                                for="is_open_{{$rental->id}}"></label></td>

                                        <td>{{ $rental->libelle}}</td>

                                        <td>{{ $rental->prenom}}</td>

                                        <td>{{ $rental->nb_jour}}</td>

                                        <td><span class="date">{{ date('d F Y',strtotime($rental->date_debut))}}</span></td>

                                        <td><span class="date">{{ date('d F Y',strtotime($rental->date_fin))}}</span></td>

                                        <td>{{ $rental->contact}}</td>

                                        <td>@if ($rental->statut=="in progress")

                                            <span class="badge badge-success">{{ $rental->statut}}<span> @else

                                                    {{-- <span class="badge badge-warning">{{ $vehicle->statut}}<span> --}}

                                                        <span class="badge badge-warning">{{ $rental->statut}}<span> @endif

                                        </td>

                                        <td class="dt-time"><span class="date">{{ date('d F Y',strtotime($rental->creer))}}</span>

                                            <span class="time">{{ date('h:i A',strtotime($rental->creer))}}</span>

                                        </td>

                                        <td class="dt-time"><span class="date">{{ date('d F Y',strtotime($rental->modifier))}}</span>

                                            <span class="time">{{ date('h:i A',strtotime($rental->modifier))}}</span>

                                        </td>





                                        <td class="action-btn">

                                            <a href="{{route('vehicle-rent.show', ['id' => $rental->id])}}" class=""

                                                data-toggle="tooltip" data-original-title="Details"><i

                                                    class="fa fa-eye"></i></a>

                                            <a id="'+val.id+'" class="delete-btn"

                                                href="{{route('vehicle-rent.delete', ['id' => $rental->id]) }}"><i

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

                                {{ $rentals->appends(request()->query())->links() }}

                            </nav>

                            {{ $rentals->links('pagination.pagination') }}

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



    $("#is_active").click(function() {

        $("#example24 .is_open").prop('checked', $(this).prop('checked'));



    });

    $('.status_selector').select2({
        placeholder: '{{trans("lang.status")}}',
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('select').on("select2:unselecting", function(e) {
        var self = $(this);
        setTimeout(function() {
            self.select2('close');
        }, 0);
    });

    function setDate() {
        let initialDateRange = $('#daterange').val(); // Get the initial value from input

        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear'
            }
        });

        if (initialDateRange) {
            let dates = initialDateRange.split(' - ');
            $('#daterange').data('daterangepicker').setStartDate(dates[0]);
            $('#daterange').data('daterangepicker').setEndDate(dates[1]);
            $('#daterange').val(initialDateRange);
        }

        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
            $('.filteredRecords').trigger('change');
        });

        $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('.filteredRecords').trigger('change');
        });
    }

    setDate();
    $('.filteredRecords').change(async function() {
        $('#filterForm').submit();
    })


    $("#deleteAll").click(function() {

        if ($('#example24 .is_open:checked').length) {

            if (confirm('Are You Sure want to Delete Selected Data ?')) {

                var arrayUsers = [];

                $('#example24 .is_open:checked').each(function() {

                    var dataId = $(this).attr('dataId');

                    arrayUsers.push(dataId);



                });



                arrayUsers = JSON.stringify(arrayUsers);

                var url = "{{url('vehicle/vehicle-rent/delete', 'id')}}";

                url = url.replace('id', arrayUsers);



                $(this).attr('href', url);

            }

        } else {

            alert('Please Select Any One Record .');

        }

    });
</script>



@endsection
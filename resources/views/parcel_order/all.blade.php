@extends('layouts.app')



@section('content')

<div class="page-wrapper">



  <div class="row page-titles">



    <div class="col-md-5 align-self-center">



      <h3 class="text-themecolor">{{trans('lang.parcel_orders')}}</h3>



    </div>



    <div class="col-md-7 align-self-center">



      <ol class="breadcrumb">



        <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>



        <li class="breadcrumb-item active">{{trans('lang.parcel_orders')}}</li>



      </ol>



    </div>



    <div>



    </div>



  </div>





  <div class="container-fluid">
    <div class="admin-top-section">
      <div class="row">
        <div class="col-12">
          <div class="d-flex top-title-section pb-4 justify-content-between">
            <div class="d-flex top-title-left align-self-center">

            </div>
            <form action="{{ route('parcel.all') }}" method="get" id="filterForm">
              <div class="d-flex top-title-right align-self-center">
                <div class="select-box pl-3">
                  <select class="form-control status_selector filteredRecords" name="status_selector">
                    <option value="">{{trans("lang.status")}}</option>
                    <option value="confirmed" {{isset($_GET['status_selector']) && $_GET['status_selector'] =='confirmed' ? 'selected ':'' }}>{{ trans('lang.confirmed')}}</option>
                    <option value="new" {{isset($_GET['status_selector']) && $_GET['status_selector'] =='new' ? 'selected ':'' }}>{{ trans('lang.new')}}</option>
                    <option value="on ride" {{isset($_GET['status_selector']) && $_GET['status_selector'] =='on ride' ? 'selected ':'' }}>{{ trans('lang.on_ride')}}</option>
                    <option value="completed" {{isset($_GET['status_selector']) && $_GET['status_selector'] =='completed' ? 'selected ':'' }}>{{ trans('lang.completed')}}</option>
                    <option value="rejected" {{isset($_GET['status_selector']) && $_GET['status_selector'] =='rejected' ? 'selected ':'' }}>{{ trans('lang.rejected')}}</option>
                    <option value="driver rejected" {{isset($_GET['status_selector']) && $_GET['status_selector'] =='driver rejected' ? 'selected ':'' }}>{{ trans('lang.driver_rejected')}}</option>
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



            <h4 class="card-title"></h4>



            <div class="userlist-topsearch d-flex mb-3">

              <!-- <div class="userlist-top-left">

                                  <a class="nav-link" href="javascript:void(0);" data-toggle="modal" data-target="#typeVehicleModal"><i class="fa fa-plus mr-2"></i>{{trans('lang.create_new_ride')}}</a>

                                </div>  -->



              <div id="users-table_filter" class="ml-auto">

                <label>{{ trans('lang.search_by')}}

                  <div class="form-group  mb-0">

                    @if($id!='')

                    <form action="{{ url('parcel/all',['id'=>$id]) }}" method="get">

                      @else

                      <form action="{{ route('parcel.all') }}" method="get">

                        @endif

                        @if(isset($_GET['selected_search']) && $_GET['selected_search'] != '')

                        <select name="selected_search" id="selected_search"

                          class="form-control input-sm">

                          <option value="userName"

                            @if($_GET['selected_search']=='userName' )

                            selected="selected" @endif>{{trans('lang.user_name')}}</option>

                          <option value="driverName"

                            @if($_GET['selected_search']=='driverName' )

                            selected="selected" @endif>{{trans('lang.driver_name')}}</option>

                          <option value="status" @if($_GET['selected_search']=='status' )

                            selected="selected" @endif>{{trans('lang.status')}}</option>

                        </select>

                        @else

                        <select name="selected_search" id="selected_search"

                          class="form-control input-sm">

                          <option value="userName">{{trans('lang.user_name')}}</option>

                          <option value="driverName">{{trans('lang.driver_name')}}</option>

                          <option value="status">{{trans('lang.status')}}</option>



                        </select>

                        @endif



                        <div class="search-box position-relative">

                          @if(isset($_GET['search']) && $_GET['search'] != '')

                          <input type="text" class="search form-control" name="search"

                            id="search" value="{{$_GET['search']}}">

                          <select id="ride_status" class="form-control"

                            name="ride_status" style="display: none">

                            <option value="confirmed">{{ trans('lang.confirmed')}}</option>

                            <option value="new">{{ trans('lang.new')}}</option>

                            <option value="on ride">{{ trans('lang.on_ride')}}</option>

                            <option value="completed">{{ trans('lang.completed')}}</option>

                            <option value="rejected">{{ trans('lang.rejected')}}</option>

                            <option value="driver_rejected">{{ trans('lang.driver_rejected')}}</option>

                          </select>



                          @elseif(isset($_GET['ride_status']) && $_GET['ride_status']!='')

                          <input type="text" class="search form-control" name="search"

                            id="search" style="display:none">

                          <select id="ride_status" class="search form-control" name="ride_status">

                            <option @if($_GET['ride_status']=='confirmed' )selected="selected"

                              @endif value="confirmed">{{ trans('lang.confirmed')}}</option>



                            <option @if($_GET['ride_status']=='new' )selected="selected"

                              @endif value="new">{{ trans('lang.new')}}</option>



                            <option @if($_GET['ride_status']=='onride' )selected="selected"

                              @endif value="onride">{{ trans('lang.on_ride')}}</option>



                            <option @if($_GET['ride_status']=='completed' )selected="selected"

                              @endif value="completed">{{ trans('lang.completed')}}</option>



                            <option @if($_GET['ride_status']=='rejected' )selected="selected"

                              @endif value="rejected">{{ trans('lang.rejected')}}</option>



                            <option @if($_GET['ride_status']=='driver_rejected' )selected="selected"

                              @endif value="driver_rejected">{{ trans('lang.driver_rejected')}}</option>



                          </select>



                          @else

                          <input type="text" class="search form-control" name="search"

                            id="search">

                          <select id="ride_status" class="search form-control"

                            name="ride_status" style="display: none">

                            <option value="confirmed">{{ trans('lang.confirmed')}}</option>

                            <option value="new">{{ trans('lang.new')}}</option>



                            <option value="onride">{{ trans('lang.on_ride')}}</option>

                            <option value="completed">{{ trans('lang.completed')}}</option>

                            <option value="rejected">{{ trans('lang.rejected')}}</option>

                            <option value="driver_rejected">{{ trans('lang.driver_rejected')}}</option>



                          </select>

                          @endif









                          <button type="submit" class="btn-flat position-absolute"><i

                              class="fa fa-search"></i></button>

                        </div>

                        <button onclick="searchtext();" class="btn btn-warning btn-flat">{{trans('lang.search')}}</button>

                        <a class="btn btn-warning btn-flat" href="{{url('parcel/all')}}">Clear</a>



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
                <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'excel', 'model' => 'ParcelOrder']) }}">{{ trans("lang.export_excel") }}</a></li>
                <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'pdf', 'model' => 'ParcelOrder']) }}">{{ trans("lang.export_pdf") }}</a></li>
                <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'csv', 'model' => 'ParcelOrder']) }}">{{ trans("lang.export_csv") }}</a></li>
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

                    <th>{{trans('lang.order_id')}}</th>

                    <th>{{trans('lang.userName')}}</th>

                    <th>{{trans('lang.driver_name')}}</th>

                    <th>{{trans('lang.cost_amount')}}</th>

                    <th>{{trans('lang.status')}}</th>

                    <th>{{trans('lang.created')}}</th>

                    <th>{{trans('lang.actions')}}</th>



                  </tr>

                </thead>

                <tbody id="append_list12">

                  @if(count($rides) > 0)

                  @foreach($rides as $ride)

                  <tr>

                    <td class="delete-all"><input type="checkbox"

                        id="is_open_{{$ride->id}}"

                        class="is_open"

                        dataid="{{$ride->id}}"><label

                        class="col-3 control-label"

                        for="is_open_{{$ride->id}}"></label></td>



                    <td><a href="{{route('parcel.show', ['id' => $ride->id])}}">{{ $ride->id}}</a>

                    </td>

                    <td>

                      <a href="{{route('users.show', ['id' => $ride->user_id])}}">{{ $ride->userPrenom}} {{ $ride->userNom}}

                      </a>

                    </td>



                    <td>

                      @if(isset($ride->driver_id))

                      <a href="{{route('driver.show', ['id' => $ride->driver_id])}}">{{ $ride->driverPrenom}} {{ $ride->driverNom}}</a>

                      @else

                      @endif

                    </td>

                    <?php $montant = floatval($ride->amount);

                    $total_price = $montant;



                    $discount = $ride->discount;

                    if ($discount) {

                      $total_price = $montant - $discount;
                    }

                    $tax = json_decode($ride->tax, true);

                    $totalTaxAmount = 0;

                    if (!empty($tax)) {

                      for ($i = 0; $i < sizeof($tax); $i++) {

                        $data = $tax[$i];

                        if ($data['type'] == "Percentage") {

                          $taxValue = (floatval($data['value']) * $total_price) / 100;
                        } else {

                          $taxValue = floatval($data['value']);
                        }

                        $totalTaxAmount += floatval(number_format($taxValue, $currency->decimal_digit));
                      }

                      $total_price = floatval($total_price) + $totalTaxAmount;



                      if ($ride->tip != null && $ride->tip > 0) {

                        $total_price = floatval($total_price) + $ride->tip;
                      }
                    }



                    ?>

                    <td>

                      @if($currency->symbol_at_right=="true")

                      {{number_format(floatval($total_price),$currency->decimal_digit)."".$currency->symbole}}

                      @else

                      {{$currency->symbole."".number_format(floatval($total_price),$currency->decimal_digit)}}

                      @endif

                      </span>

                    </td>

                    <td>

                      @if($ride->status=="completed")

                      <span class="badge badge-success">{{ $ride->status }}<span>

                          @elseif($ride->status == "confirmed")

                          <span class="badge badge-success">{{ $ride->status }}<span>

                              @elseif($ride->status == "new")

                              <span class="badge badge-primary">{{ $ride->status }}<span>

                                  @elseif($ride->status=="rejected")

                                  <span class="badge badge-danger">{{ $ride->status }}<span>

                                      @elseif($ride->status=="driver_rejected")

                                      <span class="badge badge-danger">{{trans("lang.driver_rejected")}}<span>

                                          @else

                                          <span class="badge badge-warning">{{ $ride->status }}<span>

                                              @endif

                    </td>

                    <td class="dt-time"><span class="date">{{ date('d F Y',strtotime($ride->created_at))}}</span>

                      <span class="time">{{ date('h:i A',strtotime($ride->created_at))}}</span>



                    </td>

                    <td class="action-btn">

                      <a href="{{route('parcel.show', ['id' => $ride->id])}}" class=""

                        data-toggle="tooltip" data-original-title="Details"><i

                          class="fa fa-eye"></i></a>



                      <a id="'+val.id+'"

                        class="delete-btn"

                        name="user-delete"

                        href="{{route('parcel.delete', ['rideid' => $ride->id])}}"><i

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

                <!-- {{ $rides->withQueryString()->links() }} -->

                {{$rides->appends(request()->query())->links()}}

              </nav>

              {{ $rides->withQueryString()->links('pagination.pagination') }}

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
      $('#daterange').attr('readonly', true);
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

        var url = "{{url('parcel/delete', 'rideid')}}";

        url = url.replace('rideid', arrayUsers);



        $(this).attr('href', url);

      }

    } else {

      alert('Please Select Any One Record .');

    }

  });

  $(document).ready(function() {



    if ($('#selected_search').val() == "status") {

      jQuery('#search_one').hide();

      jQuery('#search').val('');

    } else {

      jQuery('#search_one').hide();

      jQuery('#ride_status').val('');



    }

    if ($('#selected_search').val() == "type") {

      jQuery('#search').hide();

      jQuery('#search_one').val('');

    } else {



    }



  })

  $(document.body).on('change', '#selected_search', function() {



    if (jQuery(this).val() == 'status') {

      jQuery('#search_one').hide();

      jQuery('#ride_status').show();

      jQuery('#ride_status').val('new');

      jQuery('#search').val('');

      jQuery('#search').hide();

    } else {

      jQuery('#search_one').hide();

      jQuery('#ride_status').hide();

      jQuery('#ride_status').val('');

      jQuery('#search').show();



    }



    if (jQuery(this).val() == 'type') {

      jQuery('#search').hide();

      jQuery('#search_one').val('');

      jQuery('#search_one').hide();

    } else {



    }



  });
</script>

@endsection
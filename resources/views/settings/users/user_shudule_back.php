@extends('layouts.app')



@section('content')

    <div class="page-wrapper">



        <div class="row page-titles">

            <div class="col-md-5 align-self-center">

                <h3 class="text-themecolor">{{ trans('lang.user_plural') }} Schedule Show</h3>

            </div>

            <div class="col-md-7 align-self-center">

                <ol class="breadcrumb">

                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>

                    <li class="breadcrumb-item active">Users Schedule Show</li>

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
                            <form action="{{ route('users') }}" method="get" id="filterForm">
                                <div class="d-flex top-title-right align-self-center">
                                    <div class="select-box pl-3">
                                        <select class="form-control status_selector filteredRecords" name="status_selector">
                                            <option value="">{{ trans('lang.status') }}</option>
                                            <option value="active"
                                                {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'active' ? 'selected ' : '' }}>
                                                {{ trans('lang.active') }}</option>
                                            <option value="inactive"
                                                {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'inactive' ? 'selected' : '' }}>
                                                {{ trans('lang.in_active') }}</option>
                                        </select>
                                    </div>
                                    <div class="select-box pl-3">
                                        <input type="text" placeholder="dd-mm-yyyy" class="form-control filteredRecords"
                                            id="daterange" name="daterange" value="{{ request('daterange') }}" readonly />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-body">

                            <div id="data-table_processing" class="dataTables_processing panel panel-default"
                                style="display: none;">{{ trans('lang.processing') }}

                            </div>

                            <div class="userlist-topsearch d-flex mb-3">

                                <div class="userlist-top-left">

                                    <a class="nav-link" href="{!! route('users.create') !!}"><i
                                            class="fa fa-plus mr-2"></i>{{ trans('lang.user_create') }}</a>

                                </div>

                                <div id="users-table_filter" class="ml-auto">

                                    <label>{{ trans('lang.search_by') }}

                                        <div class="form-group mb-0">

                                            <form action="{{ route('users_shudule') }}" method="get">

                                                @if (isset($_GET['selected_search']) && $_GET['selected_search'] != '')
                                                    <select name="selected_search" id="selected_search"
                                                        class="form-control input-sm">
                                                        <option value="prenom"
                                                            @if ($_GET['selected_search'] == 'prenom') selected="selected" @endif>
                                                            {{ trans('lang.user_name') }}</option>
                                                        <option value="email"
                                                            @if ($_GET['selected_search'] == 'email') selected="selected" @endif>
                                                            {{ trans('lang.email') }}</option>
                                                        <option value="phone"
                                                            @if ($_GET['selected_search'] == 'phone') selected="selected" @endif>
                                                            {{ trans('lang.user_phone') }}</option>
                                                    </select>
                                                @else
                                                    <select name="selected_search" id="selected_search"
                                                        class="form-control input-sm">
                                                        <option value="prenom">{{ trans('lang.user_name') }}</option>
                                                        <option value="email">{{ trans('lang.email') }}</option>
                                                        <option value="phone">{{ trans('lang.user_phone') }}</option>
                                                    </select>
                                                @endif
                                                <div class="search-box position-relative">
                                                    @if (isset($_GET['search']) && $_GET['search'] != '')
                                                        <input type="text" class="search form-control" name="search"
                                                            id="search" value="{{ $_GET['search'] }}">
                                                    @else
                                                        <input type="text" class="search form-control" name="search"
                                                            id="search">
                                                    @endif
                                                    <button type="submit" class="btn-flat position-absolute"><i
                                                            class="fa fa-search"></i></button>
                                                    <a class="btn btn-warning btn-flat"
                                                        href="{{ url('users_shudule') }}">Clear</a>
                                                </div>
                                            </form>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="dropdown text-right">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download"></i> {{ trans('lang.export_as') }}
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li><a class="dropdown-item"
                                            href="{{ route('export.data', ['type' => 'excel', 'model' => 'UserApp']) }}">{{ trans('lang.export_excel') }}</a>
                                    </li>
                                    <li><a class="dropdown-item"
                                            href="{{ route('export.data', ['type' => 'pdf', 'model' => 'UserApp']) }}">{{ trans('lang.export_pdf') }}</a>
                                    </li>
                                    <li><a class="dropdown-item"
                                            href="{{ route('export.data', ['type' => 'csv', 'model' => 'UserApp']) }}">{{ trans('lang.export_csv') }}</a>
                                    </li>
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
                                                        class="do_not_delete" href="javascript:void(0)"><i
                                                            class="fa fa-trash"></i> All</a></label></th>
                                            <th>{{ trans('lang.status') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
                                            <th>Kyc Status</th>
                                            <th>Wallet</th>
                                            <th>{{ trans('lang.user_name') }}</th>
                                            <th>{{ trans('lang.user_phone') }}</th>
                                            <th>Mpin</th>
                                            <th>Pocket No</th>
                                            <th>{{ trans('lang.extra_image') }}</th>
                                            <th>{{ trans('lang.wallet_history') }}</th>
                                            <th>{{ trans('lang.email') }}</th>


                                        </tr>
                                    </thead>
                                    <tbody id="append_list12">
                                        @if (count($users) > 0)
                                            @foreach ($users as $customer)
                                                <tr>
                                                    <td class="delete-all"><input type="checkbox"
                                                            id="is_open_{{ $customer->id }}" class="is_open"
                                                            dataid="{{ $customer->id }}"><label
                                                            class="col-3 control-label"
                                                            for="is_open_{{ $customer->id }}"></label></td>
                                                    @if ($customer->statut == 'yes')
                                                        <td> <label class="switch"><input type="checkbox" checked
                                                                    id="{{ $customer->id }}" name="isActive"><span
                                                                    class="slider round"></span></label></td>
                                                    @else
                                                        <td><label class="switch"><input type="checkbox"
                                                                    id="{{ $customer->id }}" name="isActive"><span
                                                                    class="slider round"></span></label></td>
                                                    @endif
                                                    <td class="action-btn">
                                                        <a href="{{ route('users.show', ['id' => $customer->id]) }}"
                                                            class="" data-toggle="tooltip"
                                                            data-original-title="Details"><i class="fa fa-eye"></i></a>
                                                        <a href="{{ route('users.edit', ['id' => $customer->id]) }}"><i
                                                                class="fa fa-edit"></i></a><a id="'+val.id+'"
                                                            class="delete-btn" name="user-delete"
                                                            href="{{ route('user.delete', ['id' => $customer->id]) }}"><i
                                                                class="fa fa-trash"></i></a>
                                                    </td>


                                                    <!-- Kyc Status Start Here -->
                                                    <td>
                                                        @if ($customer->kyc_status == 1)
                                                            <label class="switch">
                                                                <input type="checkbox" class="KycStatusSwitch"
                                                                    value="{{ $customer->id }}" checked>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        @else
                                                            <label class="switch">
                                                                <input type="checkbox" class="KycStatusSwitch"
                                                                    value="{{ $customer->id }}">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        @endif
                                                        <br>

                                                        @if (!empty($customer->start_date) && !empty($customer->end_date))
                                                            <button type="button"
                                                                class="btn btn-success py-0 px-1 btn-sm rounded-circle"
                                                                data-toggle="tooltip" data-placement="top"
                                                                title="Sender Receiver" style="height:20px;width:20px">
                                                            </button>
                                                        @endif

                                                        @if (!empty($customer->start_date2) && !empty($customer->end_date2))
                                                            <button type="button"
                                                                class="btn btn-primary py-0 px-1 btn-sm rounded-circle"
                                                                data-toggle="tooltip" data-placement="top"
                                                                title="Daily Increment" style="height:20px;width:20px">
                                                            </button>
                                                        @endif

                                                        @if (!empty($customer->start_date3) && !empty($customer->end_date3))
                                                            <button type="button"
                                                                class="btn btn-danger py-0 px-1 btn-sm rounded-circle"
                                                                data-toggle="tooltip" data-placement="top"
                                                                title="Deduction" style="height:20px;width:20px">
                                                            </button>
                                                        @endif

                                                        @if (empty($customer->start_date) && empty($customer->start_date2) && empty($customer->start_date3))
                                                            <span>Sorry No Schedule</span>
                                                        @endif
                                                    </td>
                                                    <!-- Kyc Status End Here -->

                                                    <!-- Wallet is = amount_earn_amount Start Here -->
                                                    <td>
                                                        {{ $currency_symbol ?? \App\Helpers\Helper::getCurrencySymbol() }}{{ $customer->amount + $customer->earn_amount }}<br>
                                                        <a href="javascript:void(0);"
                                                            class="btn py-0 px-1 btn-sm btn-info mdi mdi-6px mdi mdi-wallet me-3 editwallet"
                                                            data-amount="{{ $customer->amount }}"
                                                            data-earn_amount="{{ $customer->earn_amount }}"
                                                            data-ac_no="{{ $customer->ac_no }}"></a>
                                                    </td>
                                                    <!-- Wallet is = amount_earn_amount End Here -->



                                                    <td><a href="{{ route('users.show', ['id' => $customer->id]) }}">{{ $customer->prenom }}
                                                            {{ $customer->nom }}</a>
                                                    </td>
                                                    <td>{{ $customer->phone }}</td>
                                                    <td>{{ $customer->m_pin }}</td>
                                                    <td>{{ $customer->ac_no }}</td>
                                                    @if (file_exists(public_path('assets/images/users' . '/' . $customer->photo_path)) && !empty($customer->photo_path))
                                                        <td><img class="rounded" style="width:50px"
                                                                src="{{ asset('assets/images/users') . '/' . $customer->photo_path }}"
                                                                alt="image"></td>
                                                    @else
                                                        <td><img class="rounded" style="width:50px"
                                                                src="{{ asset('assets/images/placeholder_image.jpg') }}"
                                                                alt="image"></td>
                                                    @endif
                                                    <td><a
                                                            href="{{ route('users.walletstransaction', ['id' => $customer->id]) }}">{{ trans('lang.wallet_history') }}</a>
                                                    </td>
                                                    <td>{{ $customer->email }}</td>



                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="11" align="center">{{ trans('lang.no_result') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>

                                <!-- {{ $users->onEachSide(5)->links() }} -->

                                <nav aria-label="Page navigation example" class="custom-pagination">

                                    {{ $users->appends(request()->query())->links() }}

                                </nav>

                                {{ $users->links('pagination.pagination') }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- Edit Wallet/eanr_wallet (amount/earn_amount) Start Here -->
    <div class="modal fade" id="editmodalwallet" tabindex="-1" aria-labelledby="editModalWalletLabel"
        aria-hidden="true" data-backdrop="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalWalletLabel">Update Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="edit_formwallet" action="javascript:void(0);" enctype="multipart/form-data"
                        method="post">
                        @csrf
                        <input type="hidden" name="ac_no" id="wallet_ac_no" value="" class="form-control">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_wallet">Wallet</label>
                                    <input type="text" id="edit_wallet" style="border:none;font-weight:bold">
                                    <!--<input type="text" name="wallet" value="0" id="edit_wallet1" class="form-control">-->
                                    <input type="text" name="amount" id="edit_wallet1" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="earn_wallet">Earn Wallet</label>
                                    <input type="text" id="edit_earn_wallet" style="border:none;font-weight:bold">
                                    <!--<input type="text" name="earn_wallet" value="0" id="edit_earn_wallet1" class="form-control">-->
                                    <input type="text" name="earn_amount" id="edit_earn_wallet1"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="earn_wallet">Description</label>
                                    <textarea name="description" placeholder="Description" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <button class="float-right btn btn-primary btn_wallet">Update</button>
                    </form>

                </div>

            </div>
        </div>
    </div>
    <!-- End amount/ear_amount model here  -->





@endsection





@section('scripts')
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <!-- Kyc Status Start Here -->
    <script>
        $('.KycStatusSwitch').change(function() {
            var isChecked = $(this).is(':checked');
            var switchLabel = this.value;
            var checkedVal = isChecked ? 1 : 0;
            var tableName = "tj_user_app";

            alert(tableName);

            swal({
                title: "Are you sure?",
                text: "You are about to update the KYC status.",
                icon: "warning",
                buttons: ["Cancel", "Yes, update it"],
                dangerMode: true,
            }).then((willUpdate) => {
                if (willUpdate) {
                    var formData = new FormData();
                    formData.append('id', switchLabel);
                    formData.append('kyc_status', checkedVal);

                    $.ajax({
                        type: "POST",
                        url: "{{ url('switch_kyc_status_update') }}/" + tableName,
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        encode: true,
                        success: function(data) {
                            swal("KYC Status Updated Successfully", "", "success");
                        },
                        error: function(errResponse) {
                            // Handle error if needed
                        }
                    });
                } else {
                    $(this).prop('checked', !isChecked);
                    swal("KYC Status Update Cancelled", "", "info");
                }
            });
        });
    </script>
    <!-- Kyc Status End Here -->

    <script>
        $('#editmodalwallet .close').on('click', function() {
            $("#editmodalwallet").modal("hide");
        });

        // edit wallet / earn_wallet like (amount/earn_amount) here 
        $(document).on('click', '.editwallet', function() {
            var ac_no = $(this).data('ac_no');
            var amount = $(this).data('amount');
            var earn_amount = $(this).data('earn_amount');
            $("#wallet_ac_no").val(ac_no);
            $("#edit_wallet").val(amount);
            $("#edit_earn_wallet").val(earn_amount);
            $("#editmodalwallet").modal("show");
        });

        // Close modal on button click
        $('#editmodalwallet .close').on('click', function() {
            $("#editmodalwallet").modal("hide");
        });

        // update wallet amount here 
        $("#edit_formwallet").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                type: "post",
                url: "{{ url('/update_user_wallet') }}",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                encode: true,
                success: function(data) {
                    if (data.success == 'success') {
                        $(".btn_wallet").prop("disabled", false);
                        $("#edit_formwallet")[0].reset();
                        $("#editmodalwallet").modal("hide");
                        swal("Wallet Updated Successfull", "", "success");
                        // show data function call here 
                        window.location.reload();
                    } else {
                        swal("Wallet Not Update!", "", "error");
                        $(".btn_wallet").prop('disabled', false);
                    }
                },
                error: function(errResponse) {
                    swal("Somthing Went Wrong!", "", "error");
                    $(".btn_wallet").prop('disabled', false);
                }
            });
        });
    </script>




    <script type="text/javascript">
        $(document).ready(function() {

            $(".shadow-sm").hide();

        })
        $('.status_selector').select2({
            placeholder: '{{ trans('lang.status') }}',
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

        $("#is_active").click(function() {

            $("#example24 .is_open").prop('checked', $(this).prop('checked'));



        });



        $("#deleteAll").click(function() {

            if ($('#example24 .is_open:checked').length) {

                if (confirm('{{ trans('lang.selected_delete_alert') }}')) {

                    var arrayUsers = [];

                    $('#example24 .is_open:checked').each(function() {

                        var dataId = $(this).attr('dataId');

                        arrayUsers.push(dataId);



                    });



                    arrayUsers = JSON.stringify(arrayUsers);

                    var url = "{{ url('user/delete', 'id') }}";

                    url = url.replace('id', arrayUsers);



                    $(this).attr('href', url);

                }

            } else {

                alert('{{ trans('lang.select_delete_alert') }}');

            }

        });

        /* toggal publish action code start*/

        $(document).on("click", "input[name='isActive']", function(e) {

            var ischeck = $(this).is(':checked');

            var id = this.id;

            console.log(id);

            $.ajax({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                },

                url: '{{ url('switch') }}',

                method: "POST",

                data: {
                    'ischeck': ischeck,
                    'id': id
                },

                success: function(data) {



                },

            });



        });



        /*toggal publish action code end*/
    </script>
@endsection

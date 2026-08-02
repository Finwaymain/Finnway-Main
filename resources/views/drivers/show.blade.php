@extends('layouts.app')



@section('content')



<div class="page-wrapper userdetail-page">



    <div class="row page-titles">



        <div class="col-md-5 align-self-center">



            <h3 class="text-themecolor">Driver Detail</h3>



        </div>



        <div class="col-md-7 align-self-center">



            <ol class="breadcrumb">



                <li class="breadcrumb-item"><a href="{!! url('/dashboard') !!}">Dashboard</a></li>



                <li class="breadcrumb-item"><a href="{!! url('drivers') !!}">Drivers</a></li>



                <li class="breadcrumb-item active">Driver Detail</li>



            </ol>



        </div>



    </div>



    <div class="container-fluid">



        <div class="row">



            <div class="col-12">



                <div class="card">



                    <div class="card-body p-0 pb-5">



                        <div class="user-top">



                            <div class="row align-items-center">



                                <div class="user-profile col-md-2">



                                    <div class="profile-img">



                                        @if (file_exists(public_path('assets/images/driver' . '/' . $driver->photo_path)) && !empty($driver->photo_path))
                                        <td><img class="profile-pic"
                                                src="{{ asset('assets/images/driver') . '/' . $driver->photo_path }}"
                                                alt="image"></td>
                                        @else
                                        <td><img class="profile-pic"
                                                src="{{ asset('assets/images/placeholder_image.jpg') }}"
                                                alt="image"></td>
                                        @endif

                                    </div>



                                </div>

                                <div class="user-title col-md-7">

                                    <h4 class="card-title"> Details of {{ $driver->prenom }} {{ $driver->nom }} <span class="badge badge-info" style="font-size: 13px; margin-left: 10px;">{{ $driver->profession }}</span></h4>

                                </div>

                                <div class="col-md-3">

                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#addWalletModal"
                                        class="text-white add-wallate btn btn-success"><i class="fa fa-plus"></i> Add

                                        Wallet Amount</a>


                                </div>




                            </div>

                        </div>





                        <div class="user-detail" role="tabpanel">



                            <!-- Nav tabs -->

                            <ul class="nav nav-tabs">



                                <li role="presentation" class="">

                                    <a href="#information" aria-controls="information" role="tab" data-toggle="tab"
                                        class="{{ Request::get('tab') == 'information' || Request::get('tab') == '' ? 'active show' : '' }}">Information</a>

                                </li>



                                <li role="presentation" class="">

                                    <a href="#rides" aria-controls="rides" role="tab" data-toggle="tab"
                                        class="{{ Request::get('tab') == 'rides' ? 'active show' : '' }}">Rides</a>

                                </li>

                                <li role="presentation" class="">

                                    <a href="#parcels" aria-controls="parcels" role="tab" data-toggle="tab"
                                        class="{{ Request::get('tab') == 'parcels' ? 'active show' : '' }}">{{ trans('lang.parcel') }}</a>

                                </li>



                                <li role="presentation" class="">

                                    <a href="#vehicle" aria-controls="vehicle" role="tab" data-toggle="tab"
                                        class="{{ Request::get('tab') == 'vehicle' ? 'active show' : '' }}">Vehicle</a>

                                </li>



                                <li role="presentation" class="">

                                    <a href="#transaction" aria-controls="transaction" role="tab" data-toggle="tab"
                                        class="{{ Request::get('tab') == 'transaction' ? 'active show' : '' }}">Wallet

                                        Transaction</a>

                                </li>
                                <li role="presentation" class="">

                                    <a href="#subscription_history" aria-controls="subscription_history" role="tab"
                                        data-toggle="tab"
                                        class="{{ Request::get('tab') == 'subscription_history' ? 'active show' : '' }}">{{ trans('lang.subscription_history') }}

                                    </a>

                                </li>



                            </ul>



                            <!-- Tab panes -->

                            <div class="tab-content">



                                <div role="tabpanel"
                                    class="tab-pane {{ Request::get('tab') == 'information' || Request::get('tab') == '' ? 'active' : '' }}"
                                    id="information">
                                    <div class="mb-3">

                                        @if($subscriptionModel || $commissionModel)
                                        <a href="javascript:void(0)" data-toggle="modal"
                                            data-target="#changeSubscriptionModal"
                                            class="text-white change-plan btn btn-success"><i
                                                class="fa fa-plus pr-2"></i>{{ trans('lang.change_subscription_plan') }}</a>
                                        @endif
                                    </div>

                                    <div class="row">



                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.user_phone') }}:</label>

                                                <span>{{ $driver->phone }}</span>

                                            </div>

                                        </div>



                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.email') }}:</label>

                                                <span>{{ $driver->email }}</span>

                                            </div>

                                        </div>

                                        <div class="col-md-6">
                                            <div class="col-group">
                                                <label class="font-weight-bold">Vehicle Brand:</label>
                                                <span>{{ $driver->brand ?? 'N/A' }}</span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="col-group">
                                                <label class="font-weight-bold">Vehicle Model:</label>
                                                <span>{{ $driver->model ?? 'N/A' }}</span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="col-group">
                                                <label class="font-weight-bold">Vehicle Number Plate:</label>
                                                <span>{{ $driver->numberplate ?? 'N/A' }}</span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.bank_name') }}:</label>

                                                <span>{{ $driver->bank_name }}</span>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.branch_name') }}:</label>

                                                <span>{{ $driver->branch_name }}</span>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.status') }}

                                                    :</label>

                                                @if ($driver->statut == 'yes' && $driver->is_verified == 1)
                                                <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i> Verified & Active</span>
                                                @else
                                                <span class="badge badge-warning px-2 py-1">Pending Verification</span>
                                                @endif

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.holder_name') }}:</label>

                                                <span>{{ $driver->holder_name }}</span>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.account_no') }}

                                                    :</label>

                                                <span>{{ $driver->account_no }}</span>

                                            </div>

                                        </div>



                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.ifsc_code') }}

                                                    :</label>

                                                <span>{{ $driver->ifsc_code }}</span>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.Other_info') }}

                                                    :</label>

                                                <span>{{ $driver->other_info }}</span>

                                            </div>

                                        </div>



                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.created_at') }}

                                                    :</label>

                                                <span
                                                    class="date">{{ date('d F Y', strtotime($driver->creer)) }}</span>

                                                <span
                                                    class="time">{{ date('h:i A', strtotime($driver->creer)) }}</span>

                                            </div>

                                        </div>



                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.edited') }}

                                                    :</label>

                                                @if ($driver->modifier != '0000-00-00 00:00:00')
                                                <span
                                                    class="date">{{ date(
                                                                'd F
                                                            
                                                            													Y',
                                                                strtotime($driver->modifier),
                                                            ) }}</span>

                                                <span
                                                    class="time">{{ date(
                                                                'h:i
                                                            
                                                            													A',
                                                                strtotime($driver->modifier),
                                                            ) }}</span>
                                                @endif

                                            </div>

                                        </div>



                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.wallet_balance') }}

                                                    :</label>

                                                <span>

                                                    @if ($currency->symbol_at_right == 'true')
                                                    @if (substr($driver->amount, 0, 1) == '-')
                                                    <span
                                                        style="color:red">-{{ number_format(floatval(substr($driver->amount, 1)), $currency->decimal_digit) . '' . $currency->symbole }}</span>
                                                    @else
                                                    <span
                                                        style="color:green">{{ number_format(floatval($driver->amount), $currency->decimal_digit) . '' . $currency->symbole }}</span>
                                                    @endif
                                                    @else
                                                    @if (substr($driver->amount, 0, 1) == '-')
                                                    <span
                                                        style="color:red">-{{ $currency->symbole . '' . number_format(floatval(substr($driver->amount, 1)), $currency->decimal_digit) }}</span>
                                                    @else
                                                    <span
                                                        style="color:green">{{ $currency->symbole . '' . number_format(floatval($driver->amount), $currency->decimal_digit) }}</span>
                                                    @endif
                                                    @endif

                                                </span>

                                            </div>

                                        </div>



                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.zone') }}

                                                    :</label>

                                                <span>{{ $zone_name }}</span>

                                            </div>

                                        </div>



                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.rating') }}

                                                    :</label>

                                                <span><i class="fa fa-star" style="color:yellow"></i>
                                                    {{ $driverRating }}

                                                </span>

                                            </div>

                                        </div>
                                        <div class="col-md-12">

                                            <div class="col-group">
                                                <div class="d-flex align-items-center pb-3">
                                                    <label for=""
                                                        class="font-weight-bold">{{ trans('lang.active_subscription_plan') }}</label>
                                                    @if (!empty($driver->subscription_plan))
                                                    <a href="javascript:void(0)" data-toggle="modal"
                                                        data-target="#updateLimitModal"
                                                        class="btn-primary btn rounded-full update-limit text-white btn btn-sm btn-success mb-2 ml-auto">{{ trans('lang.update_plan_limit') }}</a>
                                                    @endif
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-12 col-group d-flex">
                                                            <label
                                                                class="font-weight-bold">{{ trans('lang.plan_name') }}</label>
                                                            <span class="ml-auto">
                                                                {{ !empty($driver->subscription_plan) ? $driver->subscription_plan['name'] : 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="col-12 col-group d-flex">
                                                            <label
                                                                class="font-weight-bold">{{ trans('lang.plan_type') }}</label>
                                                            <span class="ml-auto">
                                                                {{ !empty($driver->subscription_plan) ? $driver->subscription_plan['type'] : 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="col-12 col-group d-flex">
                                                            <label
                                                                class="font-weight-bold">{{ trans('lang.plan_expires_at') }}</label>
                                                            <span class="ml-auto">
                                                                {{ !empty($driver->subscription_plan) ? ($driver->subscriptionExpiryDate == null ? trans('lang.unlimited') : date('d F Y h:i A', strtotime($driver->subscriptionExpiryDate))) : 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="col-12 col-group d-flex">
                                                            <label
                                                                class="font-weight-bold">{{ trans('lang.total_booking_limit') }}</label>
                                                            <span class="ml-auto">
                                                                {{ !empty($driver->subscription_plan) ? $driver->subscription_plan['bookingLimit']=='-1' ? trans('lang.unlimited') : $driver->subscription_plan['bookingLimit'] : 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="col-12 col-group d-flex">
                                                            <label
                                                                class="font-weight-bold">{{ trans('lang.available_booking_limit') }}</label>
                                                            <span class="ml-auto">
                                                                {{ !empty($driver->subscription_plan) ? $driver->subscriptionTotalOrders=='-1' ? trans('lang.unlimited') : $driver->subscriptionTotalOrders : 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="col-12 col-group">
                                                            <label
                                                                class="font-weight-bold">{{ trans('lang.available_features') }}</label>
                                                            @if (!empty($driver->subscription_plan) && !empty($driver->subscription_plan['plan_points']))
                                                            <ul>
                                                                @foreach ($driver->subscription_plan['plan_points'] as $point)
                                                                <li>{{ $point }}</li>
                                                                @endforeach
                                                            </ul>
                                                            @else
                                                            N/A
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                        </div>





                                        <div class="col-md-6">

                                            <div class="col-group-btn">

                                                @if ($driver->statut == 'no' || $driver->is_verified == 0)
                                                <a href="{{ route('driver.verifyAndEnable', ['id' => $driver->id]) }}"
                                                    class="btn btn-success" data-toggle="tooltip"
                                                    data-original-title="Verify & Enable">
                                                    <i class="fa fa-check mr-2"></i> Verify & Enable Driver
                                                </a>
                                                @else
                                                <a href="{{ route('driver.changeStatus', ['id' => $driver->id]) }}"
                                                    class="btn btn-warning" data-toggle="tooltip"
                                                    data-original-title="Disable Account"> Disable account <i
                                                        class="fa fa-times ml-1"></i> </a>
                                                @endif

                                            </div>

                                        </div>







                                    </div>



                                </div>



                                <div role="tabpanel"
                                    class="tab-pane {{ Request::get('tab') == 'rides' ? 'active' : '' }}"
                                    id="rides">

                                    @if (count($rides) > 0)
                                    <div class="table-responsive">

                                        <table
                                            class="display nowrap table table-hover table-striped table-bordered table table-striped">

                                            <thead>

                                                <tr>

                                                    <th>{{ trans('lang.ride_id') }}</th>

                                                    <th>{{ trans('lang.driver_name') }}</th>

                                                    <th>{{ trans('lang.ride_type') }}</th>

                                                    <!-- <th >{{ trans('lang.depart') }}</th>

       <th >{{ trans('lang.destination') }}</th> -->

                                                    <th>{{ trans('lang.status') }}</th>

                                                    <th>{{ trans('lang.created') }}</th>

                                                    <th>{{ trans('lang.actions') }}</th>

                                                </tr>

                                            </thead>

                                            <tbody id="append_list12">

                                                @foreach ($rides as $ride)
                                                <tr>

                                                    <td><a
                                                            href="{{ route('ride.show', ['id' => $ride->id]) }}">{{ $ride->id }}</a>
                                                    </td>

                                                    <td><a
                                                            href="{{ route('driver.show', ['id' => $ride->driver_id]) }}">{{ $ride->driverPrenom }}
                                                            {{ $ride->driverNom }}</a></td>

                                                    <td>
                                                        @if ($ride->ride_type == 'dispatcher')
                                                        {{ trans('lang.dispatcher') }}
                                                        @else
                                                        {{ trans('lang.normal') }}
                                                        @endif
                                                    </td>

                                                    <!-- <td>{{ $ride->depart_name }}</td>

       <td>{{ $ride->destination_name }}</td> -->

                                                    <td>

                                                        @if ($ride->statut == 'completed')
                                                        <span
                                                            class="badge badge-success">{{ $ride->statut }}<span>
                                                                @elseif($ride->statut == 'rejected')
                                                                <span
                                                                    class="badge badge-danger">{{ $ride->statut }}<span>
                                                                        @else
                                                                        <span
                                                                            class="badge badge-warning">{{ $ride->statut }}<span>
                                                                                @endif

                                                    </td>

                                                    <td>{{ date('d F Y h:i A', strtotime($ride->creer)) }}</td>

                                                    <td class="action-btn">

                                                        <a href="{{ route('ride.show', ['id' => $ride->id]) }}"
                                                            class="" data-toggle="tooltip"
                                                            data-original-title="Details"><i
                                                                class="fa fa-ellipsis-h"></i></a>

                                                        <a id="'+val.id+'" class="do_not_delete"
                                                            name="user-delete"
                                                            href="{{ route('ride.delete', ['rideid' => $ride->id]) }}"><i
                                                                class="fa fa-trash"></i></a>

                                                    </td>

                                                </tr>
                                                @endforeach

                                            </tbody>

                                        </table>

                                        <nav aria-label="Page navigation example" class="custom-pagination">

                                            {{ $rides->appends(['tab' => 'rides'])->links() }}



                                        </nav>

                                        {{ $rides->appends(['tab' => 'rides'])->links('pagination.pagination') }}



                                    </div>
                                    @else
                                    <p>

                                        <center>No results found.</center>

                                    </p>
                                    @endif

                                </div>

                                <div role="tabpanel"
                                    class="tab-pane {{ Request::get('tab') == 'parcels' ? 'active' : '' }}"
                                    id="parcels">

                                    @if (count($parcelOrders) > 0)
                                    <div class="table-responsive">

                                        <table
                                            class="display nowrap table table-hover table-striped table-bordered table table-striped">

                                            <thead>

                                                <tr>

                                                    <th>{{ trans('lang.parcel_id') }}</th>

                                                    <th>{{ trans('lang.userName') }}</th>

                                                    <th>{{ trans('lang.status') }}</th>

                                                    <th>{{ trans('lang.created') }}</th>

                                                    <th>{{ trans('lang.actions') }}</th>

                                                </tr>

                                            </thead>

                                            <tbody id="append_list12">

                                                @foreach ($parcelOrders as $parcel)
                                                <tr>

                                                    <td><a
                                                            href="{{ route('parcel.show', ['id' => $parcel->id]) }}">{{ $parcel->id }}</a>
                                                    </td>

                                                    <td><a
                                                            href="{{ route('users.show', ['id' => $parcel->user_id]) }}">{{ $parcel->userPrenom }}
                                                            {{ $parcel->userNom }}</a></td>

                                                    <td>

                                                        @if ($parcel->status == 'completed')
                                                        <span
                                                            class="badge badge-success">{{ $parcel->status }}<span>
                                                                @elseif($parcel->status == 'confirmed')
                                                                <span
                                                                    class="badge badge-success">{{ $parcel->status }}<span>
                                                                        @elseif($parcel->status == 'new')
                                                                        <span
                                                                            class="badge badge-primary">{{ $parcel->status }}<span>
                                                                                @elseif($parcel->status == 'rejected')
                                                                                <span
                                                                                    class="badge badge-danger">{{ $parcel->status }}<span>
                                                                                        @elseif($parcel->status == 'driver_rejected')
                                                                                        <span
                                                                                            class="badge badge-danger">{{ trans('lang.driver_rejected') }}<span>
                                                                                                @else
                                                                                                <span
                                                                                                    class="badge badge-warning">{{ $parcel->status }}<span>
                                                                                                        @endif

                                                    </td>

                                                    <td>{{ date('d F Y h:i A', strtotime($parcel->created_at)) }}
                                                    </td>

                                                    <td class="action-btn">

                                                        <a href="{{ route('parcel.show', ['id' => $parcel->id]) }}"
                                                            class="" data-toggle="tooltip"
                                                            data-original-title="Details"><i
                                                                class="fa fa-ellipsis-h"></i></a>

                                                        <a id="'+val.id+'" class="do_not_delete"
                                                            name="user-delete"
                                                            href="{{ route('parcel.delete', ['rideid' => $parcel->id]) }}"><i
                                                                class="fa fa-trash"></i></a>

                                                    </td>

                                                </tr>
                                                @endforeach

                                            </tbody>

                                        </table>

                                        <nav aria-label="Page navigation example" class="custom-pagination">

                                            {{ $parcelOrders->appends(['tab' => 'parcels'])->links() }}



                                        </nav>

                                        {{ $parcelOrders->appends(['tab' => 'parcels'])->links('pagination.pagination') }}



                                    </div>
                                    @else
                                    <p>

                                        <center>No results found.</center>

                                    </p>
                                    @endif

                                </div>



                                <div role="tabpanel"
                                    class="tab-pane {{ Request::get('tab') == 'vehicle' ? 'active' : '' }}"
                                    id="vehicle">



                                    <div class="row">

                                        @if ($vehicle)
                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.brand') }}:</label>



                                                <span>{{ $vehicle->brand }}</span>





                                            </div>

                                        </div>



                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.vehicle_model') }}:</label>



                                                <span>{{ $vehicle->model }}</span>



                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.car_number') }}:</label>

                                                <span>{{ $vehicle->numberplate }}</span>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.number_of_pessanger') }}:</label>

                                                <span>{{ $vehicle->passenger }}</span>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.vehicle_color') }}:</label>

                                                <span>{{ $vehicle->color }}</span>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.vehicle_milage') }}:</label>

                                                <span>{{ $vehicle->milage }}</span>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="col-group">

                                                <label for=""
                                                    class="font-weight-bold">{{ trans('lang.vehicle_km') }}:</label>

                                                <span>{{ $vehicle->km }}</span>

                                            </div>

                                        </div>
                                        @endif

                                        {{-- <div class="col-md-6">

											<div class="col-group">

												<label for="" class="font-weight-bold">{{trans('lang.status')}}

                                        :</label>

                                        @if ($vehicle->statut == 'yes')

                                        <span class="badge badge-success">Enabled</span>

                                        @else

                                        <span class="badge badge-warning">Disabled</span>

                                        @endif

                                    </div>

                                </div> --}}



                                <div class="col-md-6">

                                    <div class="col-group">

                                        <label for=""
                                            class="font-weight-bold">{{ trans('lang.created_at') }}

                                            :</label>

                                        <span
                                            class="date">{{ date('d F Y', strtotime($driver->creer)) }}</span>

                                        <span
                                            class="time">{{ date('h:i A', strtotime($driver->creer)) }}</span>

                                    </div>

                                </div>



                                <div class="col-md-6">

                                    <div class="col-group">

                                        <label for=""
                                            class="font-weight-bold">{{ trans('lang.edited') }}

                                            :</label>

                                        @if ($driver->modifier != '0000-00-00 00:00:00')
                                        <span
                                            class="date">{{ date(
                                                                'd F
                                                            
                                                            													Y',
                                                                strtotime($driver->modifier),
                                                            ) }}</span>

                                        <span
                                            class="time">{{ date(
                                                                'h:i
                                                            
                                                            													A',
                                                                strtotime($driver->modifier),
                                                            ) }}</span>
                                        @endif

                                    </div>

                                </div>



                            </div>



                        </div>



                        <div role="tabpanel"
                            class="tab-pane {{ Request::get('tab') == 'transaction' ? 'active' : '' }}"
                            id="transaction">

                            @if (count($transactions) > 0)
                            <div class="table-responsive">

                                <table
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped exclude-css">

                                    <thead>

                                        <tr>

                                            <th>{{ trans('lang.transaction_id') }}</th>

                                            <th>{{ trans('lang.amount') }}</th>

                                            <th>{{ trans('lang.date') }}</th>

                                            <th>{{ trans('lang.payment_method') }}</th>

                                            <th>{{ trans('lang.status') }}</th>

                                        </tr>

                                    </thead>

                                    <tbody id="append_list12">

                                        @foreach ($transactions as $transaction)
                                        <tr>

                                            <td>{{ $transaction->id }}</td>

                                            <td>

                                                @if ($currency->symbol_at_right == 'true')
                                                @if (substr($transaction->amount, 0, 1) == '-')
                                                <span
                                                    style="color:red">(-{{ number_format(floatval(substr($transaction->amount, 1)), $currency->decimal_digit) . '' . $currency->symbole }})</span>
                                                @else
                                                <span
                                                    style="color:green">{{ number_format(floatval($transaction->amount), $currency->decimal_digit) . '' . $currency->symbole }}</span>
                                                @endif
                                                @else
                                                @if (substr($transaction->amount, 0, 1) == '-')
                                                <span
                                                    style="color:red">(-{{ $currency->symbole . '' . number_format(floatval(substr($transaction->amount, 1)), $currency->decimal_digit) }})</span>
                                                @else
                                                <span
                                                    style="color:green">{{ $currency->symbole . '' . number_format(floatval($transaction->amount), $currency->decimal_digit) }}</span>
                                                @endif
                                                @endif

                                            </td>

                                            <td>{{ date('d F Y h:i A', strtotime($transaction->creer)) }}
                                            </td>



                                            @if ($transaction->image)
                                            <td><img class="rounded"
                                                    style="{{ $transaction->image == 'paystack.png' ? 'width: 90px;' : 'width: 50px;' }}"
                                                    src="{{ asset('/assets/images/payment_method/' . $transaction->image) }}"
                                                    alt="image"></td>
                                            @else
                                            <td>{{ $transaction->payment_method }}"</td>
                                            @endif



                                            <td>

                                                <span
                                                    class="badge badge-success">{{ trans('lang.success') }}<span>

                                            </td>

                                        </tr>
                                        @endforeach

                                    </tbody>

                                </table>

                                <nav aria-label="Page navigation example" class="custom-pagination">

                                    {{ $transactions->appends(['tab' => 'transaction'])->links() }}

                                </nav>

                            </div>
                            @else
                            <p>

                                <center>No results found.</center>

                            </p>
                            @endif

                        </div>

                        <div role="tabpanel"
                            class="tab-pane {{ Request::get('tab') == 'subscription_history' ? 'active' : '' }}"
                            id="subscription_history">

                            @if (count($history) > 0)
                            <div class="table-responsive">

                                <table
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped">

                                    <thead>

                                        <tr>

                                            <th>{{ trans('lang.plan_name') }}</th>
                                            <th>{{ trans('lang.plan_type') }}</th>
                                            <th>{{ trans('lang.plan_expires_at') }}</th>
                                            <th>{{ trans('lang.purchase_date') }}</th>

                                        </tr>

                                    </thead>

                                    <tbody id="append_list12">

                                        @foreach ($history as $value)
                                        <tr>

                                            <td>{{ $value->subscription_plan['name'] }}</td>

                                            <td>

                                                @if ($value->subscription_plan['type'] == 'free')
                                                <span
                                                    class="badge badge-success">{{ trans('lang.free') }}</span>
                                                @else
                                                <span
                                                    class="badge badge-danger">{{ trans('lang.paid') }}</span>
                                                @endif

                                            </td>


                                            @if (!empty($value->expiry_date))
                                            <td>{{ date('d F Y h:i A', strtotime($value->expiry_date)) }}
                                            </td>
                                            @else
                                            <td>{{ trans('lang.unlimited') }}</td>
                                            @endif

                                            <td>{{ date('d F Y h:i A', strtotime($value->created_at)) }}
                                            </td>

                                        </tr>
                                        @endforeach

                                    </tbody>

                                </table>

                                <nav aria-label="Page navigation example" class="custom-pagination">

                                    {{ $history->appends(['tab' => 'subscription_history'])->links() }}

                                </nav>

                            </div>
                            @else
                            <p>

                                <center>No results found.</center>

                            </p>
                            @endif

                        </div>





                    </div>



                </div>



            </div>

        </div>

    </div>

</div>

</div>

</div>

<div class="modal fade" id="addWalletModal" tabindex="-1" role="dialog" aria-hidden="true">



    <div class="modal-dialog modal-dialog-centered location_modal">



        <div class="modal-content">



            <div class="modal-header">



                <h5 class="modal-title locationModalTitle">{{ trans('lang.add_wallet_amount') }}</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>



            </div>



            <div class="modal-body">



                <form action="{{ route('driver.wallet', $driver->id) }}" method="post" class="">

                    @csrf



                    <div class="form-row">



                        <div class="form-group row">



                            <div class="form-group row width-100">

                                <label class="col-12 control-label">{{ trans('lang.amount') }}</label>

                                <div class="col-12">

                                    <input type="number" name="amount" class="form-control" id="amount"
                                        placeholder="Enter Amount">

                                    <div id="wallet_error" style="color:red"></div>

                                </div>

                            </div>



                        </div>



                    </div>





                    <div class="modal-footer">

                        <button type="submit" class="btn btn-primary"
                            id="add-wallet-btn">{{ trans('submit') }}</a>

                        </button>

                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">

                            {{ trans('close') }}</a>

                        </button>



                    </div>

                </form>





            </div>

        </div>



    </div>



</div>
<div class="modal fade" id="changeSubscriptionModal" tabindex="-1" role="dialog" aria-hidden="true"
    style="width: 100%">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.business_plans') }}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-lg-12 ml-lg-auto mr-lg-auto">
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex top-title-section pb-4 mb-2 justify-content-between">
                                    <div class="d-flex top-title-left align-start-center">
                                        <div class="top-title">
                                            <h3 class="mb-0">{{ trans('lang.choose_your_business_plan') }}</h3>
                                            <p class="mb-0 text-dark-2">
                                                {{ trans('lang.choose_your_business_plan_description') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="row" id="default-plan">
                                    @foreach ($plans as $plan)
                                    @php
                                    $activeClass =
                                    $plan->id == $activeSubscriptionId
                                    ? '<span class="badge badge-success">' .
                                        trans('lang.active') .
                                        '</span>'
                                    : '';
                                    $buttonText =
                                    $plan->id == $activeSubscriptionId
                                    ? trans('lang.renew_plan')
                                    : trans('lang.select_plan');
                                    @endphp

                                    @if ($plan->id == '1')
                                    @if ($commissionModel)
                                    <div class="col-md-3 mb-3 pricing-card pricing-card-commission">
                                        <div class="pricing-card-inner">
                                            <div class="pricing-card-top">
                                                <div class="d-flex align-items-center pb-4">
                                                    <span class="pricing-card-icon mr-4"><img
                                                            src="{{ asset('assets/images/subscription') . '/' . $plan->image }}"
                                                            alt="{{ $plan->name }}"></span>
                                                </div>
                                                <div class="pricing-card-price">
                                                    <h3 class="text-dark-2">{!! $plan->name !!}
                                                        {!! $activeClass !!}</h3>
                                                    <span class="price-day">{{ $adminCommission ?? '' }}
                                                        {{ trans('lang.commision_per_order') }}</span>
                                                </div>
                                            </div>
                                            <div class="pricing-card-content pt-3 mt-3 border-top">
                                                <ul class="pricing-card-list text-dark-2">
                                                    <li><span
                                                            class="mdi mdi-check"></span>{{ trans('lang.pay_commission_of') }}
                                                        {{ $adminCommission ?? '' }}
                                                        {{ trans('lang.on_each_order') }}
                                                    </li>
                                                    @foreach ($plan->plan_points as $point)
                                                    <li><span
                                                            class="mdi mdi-check"></span>{{ $point }}
                                                    </li>
                                                    @endforeach
                                                    <li><span
                                                            class="mdi mdi-check"></span>{{ trans('lang.unlimited') }}
                                                        {{ trans('lang.bookings') }}
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="pricing-card-btm">
                                                <a href="javascript:void(0)"
                                                    onClick="chooseSubscriptionPlan('{{ $plan->id }}')"
                                                    class="btn rounded-full active-btn btn-primary">{{ $buttonText }}</a>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @else
                                    @if ($subscriptionModel)
                                    <div
                                        class="col-md-3 mt-2 pricing-card pricing-card-subscription {{ $plan->name }}">
                                        <div class="pricing-card-inner">
                                            <div class="pricing-card-top">
                                                <div class="d-flex align-items-center pb-4">
                                                    <span class="pricing-card-icon mr-4"><img
                                                            src="{{ asset('assets/images/subscription') . '/' . $plan->image }}"
                                                            alt="{{ $plan->name }}"></span>
                                                    <h2 class="text-dark-2">{!! $plan->name !!}
                                                        {!! $activeClass !!}</h2>
                                                </div>
                                                <p class="text-muted">{{ $plan->description }}</p>
                                                <div class="pricing-card-price">
                                                    <h3 class="text-dark-2">

                                                        {{intval($plan->price==0) ?  trans("lang.free") : ($currency->symbol_at_right == 'true' ? number_format($plan->price, $currency->decimal_degit ?? 2) . $currency->symbole : $currency->symbole . number_format($plan->price, $currency->decimal_degit ?? 2)) }}

                                                    </h3>
                                                    <span
                                                        class="price-day">{{ $plan->expiryDay == -1 ? trans('lang.unlimited') : $plan->expiryDay }}
                                                        {{ trans('lang.days') }}</span>
                                                </div>
                                            </div>
                                            <div class="pricing-card-content pt-3 mt-3 border-top">
                                                <ul class="pricing-card-list text-dark-2">
                                                    @foreach ($plan->plan_points as $point)
                                                    <li><span
                                                            class="mdi mdi-check"></span>{{ $point }}
                                                    </li>
                                                    @endforeach
                                                    <li><span
                                                            class="mdi mdi-check"></span>{{ $plan->bookingLimit == -1 ? trans('lang.unlimited') : $plan->bookingLimit }}
                                                        {{ trans('lang.bookings') }}
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="pricing-card-btm">
                                                <a href="javascript:void(0)"
                                                    onClick="chooseSubscriptionPlan('{{ $plan->id }}')"
                                                    class="btn rounded-full">{{ $buttonText }}</a>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="checkoutSubscriptionModal" tabindex="-1" role="dialog" aria-hidden="true"
    style="width: 100%">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.shift_to_plan') }}</h6>

            </div>
            <div class="modal-body p-4">
                <form class="">
                    <div class="subscription-section">
                        <div class="subscription-section-inner">
                            <div class="card-body">
                                <div class="row" id="plan-details"></div>
                                <div class="pay-method-section pt-4 manual_pay_div">
                                    <h6 class="text-dark-2 h6 mb-3 pb-3">{{ trans('lang.pay_via_online') }}</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="pay-method-box d-flex align-items-center">
                                                <div class="pay-method-icon">
                                                    <img src="{{ asset('images/wallet_icon_ic.png') }}">
                                                </div>
                                                <div class="form-check ">
                                                    <h6 class="text-dark-2 h6 mb-0">{{ trans('lang.manual_pay') }}
                                                    </h6>
                                                    <input type="radio" id="manual_pay" name="payment_method"
                                                        value="manual_pay" checked="">
                                                    <label class="control-label mb-0" for="manual_pay"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer border-top">
                                <div class="align-items-center justify-content-between">
                                    <div class="edit-form-group btm-btn text-right">
                                        <div class="card-block-active-plan">
                                            <a href="" class="btn btn-default rounded-full mr-2"
                                                data-dismiss="modal">{{ trans('lang.cancel_plan') }}</a>
                                            <input type="hidden" id="plan_id" name="plan_id" value="">
                                            <button type="button" class="btn-primary btn rounded-full"
                                                onclick="finalCheckout()">{{ trans('lang.change_plan') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="updateLimitModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered location_modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title locationModalTitle">{{ trans('lang.update_plan_limit') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('subscription-limit.update', $driver->id) }}" method="post"
                    enctype="multipart/form-data" id="update-limit-form">
                    @csrf
                    @method('PUT')

                    @if (!empty($driver->subscription_plan))
                    <div class="form-row">
                        <div class="form-group row">
                            <div class="form-group row width-100">
                                <label class="control-label">{{ trans('lang.maximum_booking_limit') }}</label>
                                <div class="form-check width-100">
                                    <input type="radio" id="unlimited_booking" name="set_booking_limit"
                                        value="unlimited"
                                        {{ !empty($driver->subscription_plan) ? ($driver->subscription_plan['bookingLimit'] == '-1' ? 'checked' : '') : '' }}>
                                    <label class="control-label"
                                        for="unlimited_booking">{{ trans('lang.unlimited') }}</label>
                                </div>
                                <div class="d-flex">
                                    <div class="form-check width-50 limited_booking_div">
                                        <input type="radio" id="limited_booking" name="set_booking_limit"
                                            value="limited"
                                            {{ !empty($driver->subscription_plan) ? ($driver->subscription_plan['bookingLimit'] != '-1' ? 'checked' : '') : '' }}>

                                        <label class="control-label"
                                            for="limited_booking">{{ trans('lang.limited') }}</label>
                                    </div>
                                    @if ($driver->subscription_plan['bookingLimit'] == '-1')
                                    @php $limitClass='d-none'@endphp
                                    @else
                                    @php $limitClass=''@endphp
                                    @endif
                                    <div class="form-check width-50 {{ $limitClass }} booking-limit-div">
                                        <input type="number" id="booking_limit" name="booking_limit"
                                            class="form-control"
                                            value="{{ $driver->subscription_plan['bookingLimit'] != '-1' ? $driver->subscription_plan['bookingLimit'] : '' }}"
                                            placeholder="{{ trans('lang.ex_1000') }}">
                                    </div>

                                </div>
                                <span class="booking_limit_err text-danger font-weight-bold"></span>


                            </div>

                        </div>
                    </div>
                    @endif
                    <div class="modal-footer">
                        <button type="submit"
                            class="btn btn-primary update-plan-limit">{{ trans('submit') }}</a></button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{ trans('close') }}</a>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection



@section('scripts')
<script>
    $("#checkoutSubscriptionModal").on('hide.bs.modal', function() {
        $("#plan-details").html('');
    });

    function chooseSubscriptionPlan(planId) {
        $("#changeSubscriptionModal").modal('hide');
        $("#checkoutSubscriptionModal").modal('show');
        showPlanDetail(planId);
    }
    async function showPlanDetail(planId) {
        $("#plan_id").val(planId);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('get-plan-detail') }}",
            method: "POST",
            data: {
                'planId': planId,
                'driverId': "{{ $driver->id }}"
            },
            success: function(response) {
                var choosedPlan = response.planData;
                var activePlan = '';
                if (response.activePlan) {
                    activePlan = response.activePlan.subscription_plan;
                }

                const basePath = "{{ asset('assets/images/subscription') }}/";
                let html = '';
                let choosedPlan_price;
                if (parseInt(choosedPlan.price) != 0) {
                    choosedPlan_price = "{{ $currency->symbol_at_right == 'true' }}" ? parseFloat(
                            choosedPlan.price).toFixed("{{ $currency->decimal_digit }}") +
                        "{{ $currency->symbole }}" :
                        "{{ $currency->symbole }}" + parseFloat(choosedPlan.price).toFixed(
                            "{{ $currency->decimal_digit }}");
                    $('.manual_pay_div').removeClass('d-none');
                } else {
                    choosedPlan_price = 'Free';
                    $('.manual_pay_div').addClass('d-none');
                }

                if (activePlan) {
                    let activePlan_price;

                    if (parseInt(activePlan.price) != 0) {

                        activePlan_price = "{{ $currency->symbol_at_right == 'true' }}" ?
                            parseFloat(activePlan.price).toFixed("{{ $currency->decimal_digit }}") +
                            "{{ $currency->symbole }}" :
                            "{{ $currency->symbole }}" + parseFloat(activePlan.price).toFixed(
                                "{{ $currency->decimal_digit }}");

                    } else {

                        activePlan_price = 'Free';

                    }
                    html += ` 
            <div class="col-md-8">
                <div class="subscription-card-left"> 
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${basePath}${activePlan.image}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${activePlan.id=="1"? "{{ trans('lang.commission') }}":activePlan.name}</h2>
                                </div>
                                <h3 class="text-dark-2">${activePlan.id=="1"? "{{ $adminCommission }}"+" {{ trans('lang.base_plan') }}":activePlan_price}</h3>
                                <p class="text-center">${activePlan.id=="1"? "Free":activePlan.expiryDay==-1? "{{ trans('lang.unlimited') }}":activePlan.expiryDay+" {{ trans('lang.days') }}"}</p>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <img src="{{ asset('images/left-right-arrow.png') }}">
                        </div>
                        <div class="col-md-5">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${basePath}${choosedPlan.image}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${choosedPlan.name}
                                    </h2>
                                </div>
                                <h3 class="text-dark-2">${choosedPlan_price}</h3>
                                <p class="text-center">${choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="subscription-card-right">
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.validity') }}</span>
                        <span class="font-weight-semibold">${choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.price') }}</span>
                        <span class="font-weight-semibold">${choosedPlan_price}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.bill_status') }}</span>
                        <span class="font-weight-semibold">{{ trans('lang.migrate_to_new_plan') }}</span>
                    </div>
                </div>
            </div>`;
                } else {

                    html += ` 
            <div class="col-md-6">
                <div class="subscription-card-left"> 
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${basePath}${choosedPlan.image}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${choosedPlan.name}
                                    </h2>
                                </div>
                                <h3 class="text-dark-2">${choosedPlan_price}</h3>
                                <p class="text-center">${choosedPlan.id=="1"? "Free":choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="subscription-card-right">
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.validity') }}</span>
                        <span class="font-weight-semibold">${choosedPlan.id=="1"? "Unlimited":choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.price') }}</span>
                        <span class="font-weight-semibold">${choosedPlan_price}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.bill_status') }}</span>
                        <span class="font-weight-semibold">{{ trans('lang.migrate_to_new_plan') }}</span>
                    </div>
                </div>
            </div>`;
                }
                $("#plan-details").html(html);
            },
            error: function(xhr) {

            }
        });
    }
    async function finalCheckout() {
        let planId = $("#plan_id").val();
        if (planId != undefined && planId != '' && planId != null) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('
                subscription - checkout ') }}',
                method: "POST",
                data: {
                    'planId': planId,
                    'driverId': "{{ $driver->id }}"
                },
                success: function(response) {
                    window.location.reload();
                }

            });

        }
    }

    $("#add-wallet-btn").click(function() {

        var amount = $('#amount').val();

        if (amount == '') {

            $('#wallet_error').text('{{ trans('
                lang.add_wallet_amount_error ') }}');

            return false;

        }
    });
    $('input[name="set_booking_limit"]').on('change', function() {
        if ($('#limited_booking').is(':checked')) {
            $('.booking-limit-div').removeClass('d-none');
        } else {
            $('.booking-limit-div').addClass('d-none');
        }
    });
    $(document).on('submit', '#update-limit-form', function(event) {
        let bookingLimit = $('#booking_limit').val().trim();
        var set_booking_limit = $('input[name="set_booking_limit"]:checked').val();
        if (set_booking_limit == 'limited' && bookingLimit === '') {
            event.preventDefault();
            $('.booking_limit_err').html('{{ trans('
                lang.enter_booking_limit ') }}');
        }
    });
</script>
@endsection
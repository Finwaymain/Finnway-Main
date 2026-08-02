@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor plan_title">{{ trans('lang.current_subscriber_list_of') }}
                {{ $subscriptionPlan->name }}
            </h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ url('/subscription-plans') }}">{{ trans('lang.subscription_plans') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.current_subscriber_list') }} </li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! url()->current() !!}"><i
                                        class="fa fa-list mr-2"></i>{{ trans('lang.current_subscriber_list') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="userlist-topsearch d-flex mb-3">

                            <div id="users-table_filter" class="ml-auto">

                                <label>{{ trans('lang.search_by') }}

                                    <div class="form-group mb-0">

                                        <form action="{{ route('current-subscriber.list',$subscriptionPlan->id) }}" method="get">

                                            @if (isset($_GET['selected_search']) && $_GET['selected_search'] != '')
                                            <select name="selected_search" id="selected_search"
                                                class="form-control input-sm">

                                                <option value="driver"
                                                    @if ($_GET['selected_search']=='driver' ) selected="selected" @endif>
                                                    {{ trans('lang.driver') }}
                                                </option>
                                                <option value="planName"
                                                    @if ($_GET['selected_search']=='planName' ) selected="selected" @endif>
                                                    {{ trans('lang.plan_name') }}
                                                </option>
                                                <option value="planType"
                                                    @if ($_GET['selected_search']=='planType' ) selected="selected" @endif>
                                                    {{ trans('lang.plan_type') }}
                                                </option>


                                            </select>
                                            @else
                                            <select name="selected_search" id="selected_search"
                                                class="form-control input-sm">
                                                <option value="driver">{{ trans('lang.driver') }}</option>
                                                <option value="planName">{{ trans('lang.plan_name') }}</option>
                                                <option value="planType">{{ trans('lang.plan_type') }}</option>
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
                                                <button type="submit" class="btn-flat position-absolute">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                                <a class="btn btn-warning btn-flat"
                                                    href="{{ route('current-subscriber.list',$subscriptionPlan->id) }}">Clear</a>
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
                                        <th>{{ trans('lang.driver') }}</th>
                                        <th>{{ trans('lang.plan_name') }}</th>
                                        <th>{{ trans('lang.plan_type') }}</th>
                                        <th>{{ trans('lang.booking_limit') }}</th>
                                        <th>{{ trans('lang.plan_expires_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($currentSubscribers) > 0)
                                    @foreach ($currentSubscribers as $value)
                                    <tr>
                                        <td>{{ $value->prenom }} {{ $value->nome }}</td>
                                        <td>{{ $value->subscription_plan['name'] }}</td>
                                        <td>{{ $value->subscription_plan['type'] }}</td>
                                        <td>
                                            <span>{{ trans('lang.total') }} :
                                                {{ $value->subscription_plan['bookingLimit'] == '-1' ? trans('lang.unlimited') : $value->subscription_plan['bookingLimit'] }}</span><br>
                                            <span>{{ trans('lang.available') }} :
                                                {{ $value->subscriptionTotalOrders == '-1' ? trans('lang.unlimited') : $value->subscriptionTotalOrders }}</span
                                                </td>
                                        <td>
                                            @if ($value->subscriptionExpiryDate == null)
                                            {{ trans('lang.unlimited') }}
                                            @else
                                            <span
                                                class="date">{{ date('d F Y', strtotime($value->subscriptionExpiryDate)) }}</span>

                                            <span
                                                class="time">{{ date('h:i A', strtotime($value->subscriptionExpiryDate)) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <nav aria-label="Page navigation example" class="custom-pagination">

                                {{ $currentSubscribers->appends(request()->query())->links() }}

                            </nav>

                            {{ $currentSubscribers->Links('pagination.pagination') }}
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
<script></script>
@endsection
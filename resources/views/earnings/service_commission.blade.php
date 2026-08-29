@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor font-weight-bold">💼 Service Commission</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Earning Management</li>
                <li class="breadcrumb-item active">Service Commission</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body">
                <h4 class="card-title font-weight-bold border-bottom pb-2">Service-wise Commission Earnings</h4>
                <p class="text-muted small">Commission breakdown across Cab, Home Services, Food, Parcel/Delivery, Travel, and Other Services.</p>

                <div class="table-responsive mt-3">
                    <table class="table table-hover table-striped border">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Service Category</th>
                                <th>Commission Type</th>
                                <th>Commission Rate</th>
                                <th>Total Bookings</th>
                                <th>Gross Booking Volume</th>
                                <th>Admin Commission Earned</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicesBreakdown as $key => $svc)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><strong class="text-dark">{{ $svc['service'] }}</strong></td>
                                    <td><span class="badge badge-secondary">{{ $svc['type'] }}</span></td>
                                    <td><span class="badge badge-info font-weight-bold">{{ $svc['rate'] }}</span></td>
                                    <td>{{ number_format($svc['total_bookings']) }}</td>
                                    <td>₹{{ number_format($svc['gross_amount']) }}</td>
                                    <td class="text-success font-weight-bold" style="font-size: 1.1rem;">
                                        ₹{{ number_format($svc['commission_earned']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

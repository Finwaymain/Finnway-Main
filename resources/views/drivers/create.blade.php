@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.driver_create')}}</h3>
        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('drivers') !!}">{{trans('lang.driver_plural')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.driver_create')}}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-8 col-lg-6 mx-auto">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-primary text-white py-3" style="border-radius: 12px 12px 0 0;">
                        <h4 class="card-title text-white mb-0 font-weight-bold">
                            <i class="fa fa-user-plus mr-2"></i> Register New Driver / Partner
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                        </div>
                        @endif

                        <form action="{{route('drivers.store')}}" method="post" id="create_driver">
                            @csrf

                            <div class="form-group mb-3">
                                <label class="control-label font-weight-bold text-dark">
                                    Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="nom" value="{{ old('nom') }}" required placeholder="Enter name" style="border-radius: 8px; height: 44px;">
                                <small class="form-text text-muted">Driver / Partner first name</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="control-label font-weight-bold text-dark">
                                    Last Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="prenom" value="{{ old('prenom') }}" required placeholder="Enter last name" style="border-radius: 8px; height: 44px;">
                                <small class="form-text text-muted">Driver / Partner last name</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="control-label font-weight-bold text-dark">
                                    Phone Number <span class="text-danger">*</span>
                                </label>
                                <input type="tel" class="form-control" name="phone" value="{{ old('phone') }}" required placeholder="e.g. 9876543210" maxlength="15" style="border-radius: 8px; height: 44px;">
                                <small class="form-text text-muted">10-digit mobile number</small>
                            </div>

                            <div class="form-group mb-4">
                                <label class="control-label font-weight-bold text-dark">
                                    MPIN <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" name="m_pin" value="{{ old('m_pin') }}" required minlength="4" maxlength="6" pattern="[0-9]*" inputmode="numeric" placeholder="Enter 4-digit MPIN" autocomplete="new-password" style="border-radius: 8px; height: 44px; letter-spacing: 2px;">
                                <small class="form-text text-muted">4-digit numeric login PIN used for mobile app access</small>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2">
                                <a href="{!! route('drivers') !!}" class="btn btn-secondary px-4" style="border-radius: 8px;">
                                    <i class="fa fa-arrow-left mr-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4 font-weight-bold" style="border-radius: 8px;">
                                    <i class="fa fa-save mr-1"></i> Save Driver
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
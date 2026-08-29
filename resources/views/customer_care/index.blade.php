@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Customer Care Contact Management</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Support & CMS</li>
                <li class="breadcrumb-item active">Customer Care</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle mr-2"></i> Please fix the validation errors below.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('customer-care.update') }}" method="POST">
            @csrf
            <div class="row">
                <!-- Section 1: Business App (Driver / Partner) -->
                <div class="col-lg-6 col-md-12">
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; border-top: 4px solid #4f46e5 !important;">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                            <div class="btn btn-sm btn-indigo text-white rounded-circle mr-3" style="background-color: #4f46e5; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="mdi mdi-briefcase text-white" style="font-size: 18px;"></i>
                            </div>
                            <div>
                                <h4 class="card-title m-0 font-weight-bold" style="color: #1e293b;">Business App (Driver / Partner)</h4>
                                <small class="text-muted">Customer care contact details shown to drivers & business partners</small>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-group mb-4">
                                <label for="business_whatsapp_number" class="font-weight-600 text-dark">
                                    <i class="mdi mdi-whatsapp text-success mr-1"></i> WhatsApp Contact Number <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-whatsapp text-success"></i></span>
                                    </div>
                                    <input type="text" 
                                           name="business_whatsapp_number" 
                                           id="business_whatsapp_number" 
                                           class="form-control border-left-0 @error('business_whatsapp_number') is-invalid @enderror" 
                                           value="{{ old('business_whatsapp_number', $settings->business_whatsapp_number ?? '9429693669') }}" 
                                           placeholder="e.g. 9429693669" 
                                           required>
                                </div>
                                @error('business_whatsapp_number')
                                    <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Driver app WhatsApp chat will initiate with this phone number.</small>
                            </div>

                            <div class="form-group mb-2">
                                <label for="business_call_number" class="font-weight-600 text-dark">
                                    <i class="mdi mdi-phone-in-talk text-info mr-1"></i> Calling Contact Number <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-phone text-info"></i></span>
                                    </div>
                                    <input type="text" 
                                           name="business_call_number" 
                                           id="business_call_number" 
                                           class="form-control border-left-0 @error('business_call_number') is-invalid @enderror" 
                                           value="{{ old('business_call_number', $settings->business_call_number ?? '9429693669') }}" 
                                           placeholder="e.g. 9429693669" 
                                           required>
                                </div>
                                @error('business_call_number')
                                    <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Driver app phone dialer call button will connect to this phone number.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Customer App (User / Consumer) -->
                <div class="col-lg-6 col-md-12">
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; border-top: 4px solid #10b981 !important;">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                            <div class="btn btn-sm text-white rounded-circle mr-3" style="background-color: #10b981; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="mdi mdi-account-group text-white" style="font-size: 18px;"></i>
                            </div>
                            <div>
                                <h4 class="card-title m-0 font-weight-bold" style="color: #1e293b;">Customer App (User / Consumer)</h4>
                                <small class="text-muted">Customer care contact details shown to end users & customers</small>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-group mb-4">
                                <label for="customer_whatsapp_number" class="font-weight-600 text-dark">
                                    <i class="mdi mdi-whatsapp text-success mr-1"></i> WhatsApp Contact Number <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-whatsapp text-success"></i></span>
                                    </div>
                                    <input type="text" 
                                           name="customer_whatsapp_number" 
                                           id="customer_whatsapp_number" 
                                           class="form-control border-left-0 @error('customer_whatsapp_number') is-invalid @enderror" 
                                           value="{{ old('customer_whatsapp_number', $settings->customer_whatsapp_number ?? '9429693669') }}" 
                                           placeholder="e.g. 9429693669" 
                                           required>
                                </div>
                                @error('customer_whatsapp_number')
                                    <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Customer app WhatsApp chat will initiate with this phone number.</small>
                            </div>

                            <div class="form-group mb-2">
                                <label for="customer_call_number" class="font-weight-600 text-dark">
                                    <i class="mdi mdi-phone-in-talk text-info mr-1"></i> Calling Contact Number <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-phone text-info"></i></span>
                                    </div>
                                    <input type="text" 
                                           name="customer_call_number" 
                                           id="customer_call_number" 
                                           class="form-control border-left-0 @error('customer_call_number') is-invalid @enderror" 
                                           value="{{ old('customer_call_number', $settings->customer_call_number ?? '9429693669') }}" 
                                           placeholder="e.g. 9429693669" 
                                           required>
                                </div>
                                @error('customer_call_number')
                                    <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Customer app phone dialer call button will connect to this phone number.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <span class="text-muted small"><i class="mdi mdi-information-outline mr-1"></i> Changes will immediately take effect for both mobile apps and backend endpoints.</span>
                            <button type="submit" class="btn text-white px-4 font-weight-bold" style="background-color: #4f46e5; border-radius: 8px;">
                                <i class="mdi mdi-content-save mr-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

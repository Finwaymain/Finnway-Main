@extends('layouts.app')
@section('content')

<style>
.page-header-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.key-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    overflow: hidden;
}
.key-card .key-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.key-card .key-body {
    padding: 20px;
}
.switch-label {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    margin: 0;
}
.switch-label input { opacity: 0; width: 0; height: 0; }
.switch-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .2s ease;
    border-radius: 24px;
}
.switch-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .2s ease;
    border-radius: 50%;
}
.switch-label input:checked + .switch-slider { background-color: #2563eb; }
.switch-label input:checked + .switch-slider:before { transform: translateX(20px); }
</style>

<div class="page-wrapper">
    <div class="container-fluid pt-3">
        <div class="page-header-card d-flex align-items-center justify-content-between">
            <div>
                <h3><i class="mdi mdi-key-variant text-primary mr-2"></i>Dynamic API Keys & Integrations</h3>
                <p class="mb-0 text-muted">Manage Google Maps, Payment Gateways, WhatsApp API, SMS Gateways & Firebase Keys dynamically.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <div class="row">
            <!-- 🗺️ Google Maps & Location -->
            <div class="col-md-6">
                <div class="key-card">
                    <div class="key-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="mdi mdi-map-marker-radius text-danger font-20 mr-2"></i>
                            <h5 class="mb-0 font-weight-bold">Google Maps & Location API</h5>
                        </div>
                        <span class="badge badge-primary">Active</span>
                    </div>
                    <div class="key-body">
                        <form action="{{ route('api-keys.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="group" value="maps">
                            <input type="hidden" name="provider" value="google_maps">
                            <input type="hidden" name="key_name" value="google_maps_api_key">
                            <div class="form-group">
                                <label class="font-weight-bold small text-uppercase">Google Maps API Key</label>
                                <input type="text" name="key_value" class="form-control" value="{{ optional($apiKeys->get('maps')?->firstWhere('provider', 'google_maps'))->key_value ?? '' }}" placeholder="AIzaSy...">
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-2">
                                <label class="switch-label">
                                    <input type="checkbox" name="is_active" {{ optional($apiKeys->get('maps')?->firstWhere('provider', 'google_maps'))->is_active ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                </label>
                                <button type="submit" class="btn btn-sm btn-primary px-4">Save Key</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 💳 Razorpay Payment Gateway -->
            <div class="col-md-6">
                <div class="key-card">
                    <div class="key-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="mdi mdi-credit-card-outline text-info font-20 mr-2"></i>
                            <h5 class="mb-0 font-weight-bold">Razorpay Payment Gateway</h5>
                        </div>
                    </div>
                    <div class="key-body">
                        <form action="{{ route('api-keys.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="group" value="payment">
                            <input type="hidden" name="provider" value="razorpay">
                            <input type="hidden" name="key_name" value="razorpay_key_id">
                            <div class="form-group">
                                <label class="font-weight-bold small text-uppercase">Razorpay Key ID</label>
                                <input type="text" name="key_value" class="form-control" value="{{ optional($apiKeys->get('payment')?->firstWhere('provider', 'razorpay'))->key_value ?? '' }}" placeholder="rzp_live_...">
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small text-uppercase">Razorpay Key Secret</label>
                                <input type="password" name="secret_value" class="form-control" value="{{ optional($apiKeys->get('payment')?->firstWhere('provider', 'razorpay'))->secret_value ?? '' }}" placeholder="••••••••">
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-2">
                                <label class="switch-label">
                                    <input type="checkbox" name="is_active" {{ optional($apiKeys->get('payment')?->firstWhere('provider', 'razorpay'))->is_active ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                </label>
                                <button type="submit" class="btn btn-sm btn-primary px-4">Save Key</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 💬 WhatsApp Business API -->
            <div class="col-md-6">
                <div class="key-card">
                    <div class="key-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="mdi mdi-whatsapp text-success font-20 mr-2"></i>
                            <h5 class="mb-0 font-weight-bold">WhatsApp Business API</h5>
                        </div>
                    </div>
                    <div class="key-body">
                        <form action="{{ route('api-keys.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="group" value="whatsapp">
                            <input type="hidden" name="provider" value="whatsapp_biz">
                            <input type="hidden" name="key_name" value="whatsapp_access_token">
                            <div class="form-group">
                                <label class="font-weight-bold small text-uppercase">WhatsApp Permanent Access Token</label>
                                <input type="text" name="key_value" class="form-control" value="{{ optional($apiKeys->get('whatsapp')?->firstWhere('provider', 'whatsapp_biz'))->key_value ?? '' }}" placeholder="EAAG...">
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small text-uppercase">Phone Number ID</label>
                                <input type="text" name="secret_value" class="form-control" value="{{ optional($apiKeys->get('whatsapp')?->firstWhere('provider', 'whatsapp_biz'))->secret_value ?? '' }}" placeholder="105492348572...">
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-2">
                                <label class="switch-label">
                                    <input type="checkbox" name="is_active" {{ optional($apiKeys->get('whatsapp')?->firstWhere('provider', 'whatsapp_biz'))->is_active ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                </label>
                                <button type="submit" class="btn btn-sm btn-primary px-4">Save Key</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 🔔 Firebase FCM Push Notifications -->
            <div class="col-md-6">
                <div class="key-card">
                    <div class="key-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="mdi mdi-bell-outline text-warning font-20 mr-2"></i>
                            <h5 class="mb-0 font-weight-bold">Firebase FCM Push Notifications</h5>
                        </div>
                    </div>
                    <div class="key-body">
                        <form action="{{ route('api-keys.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="group" value="push">
                            <input type="hidden" name="provider" value="fcm">
                            <input type="hidden" name="key_name" value="fcm_server_key">
                            <div class="form-group">
                                <label class="font-weight-bold small text-uppercase">FCM Server Key</label>
                                <textarea name="key_value" class="form-control" rows="3" placeholder="AAAA...">{{ optional($apiKeys->get('push')?->firstWhere('provider', 'fcm'))->key_value ?? '' }}</textarea>
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-2">
                                <label class="switch-label">
                                    <input type="checkbox" name="is_active" {{ optional($apiKeys->get('push')?->firstWhere('provider', 'fcm'))->is_active ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                </label>
                                <button type="submit" class="btn btn-sm btn-primary px-4">Save Key</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

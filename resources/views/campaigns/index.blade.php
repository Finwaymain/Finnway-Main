@extends('layouts.app')
@section('content')

<div class="page-wrapper">
    <div class="container-fluid pt-3">
        <div class="card p-4 border-0 shadow-sm" style="border-radius:12px;">
            <h3 class="font-weight-bold"><i class="mdi mdi-send text-primary mr-2"></i>Multi-Channel Targeted Campaigns</h3>
            <p class="text-muted">Broadcast Push Notifications, WhatsApp Messages, Email, and SMS with targeted user & profession filters.</p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('campaigns.send') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Communication Channel</label>
                        <select name="channel" class="form-control" required>
                            <option value="push">🔔 Push Notification</option>
                            <option value="whatsapp">💬 WhatsApp Business API</option>
                            <option value="email">✉️ Email Campaign</option>
                            <option value="sms">📱 SMS</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Target User Type</label>
                        <select name="user_type" class="form-control">
                            <option value="">All Users (Consumer & Business)</option>
                            <option value="customer">Consumer Users Only</option>
                            <option value="driver">Business Partners / Drivers Only</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Campaign Title / Subject</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Special 20% Cashback Today!" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Message Content</label>
                    <textarea name="message" class="form-control" rows="4" placeholder="Enter campaign message text..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary px-4 font-weight-bold"><i class="fa fa-paper-plane mr-1"></i>Launch Targeted Campaign</button>
            </form>
        </div>
    </div>
</div>
@endsection

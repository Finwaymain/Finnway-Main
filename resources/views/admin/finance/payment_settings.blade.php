@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1200px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('admin.finance.dashboard') }}" style="color: #64748b; font-size: 13px; text-decoration: none;">
                        ← Finance Overview
                    </a>
                    <span style="color: #cbd5e1;">/</span>
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        PAYMENT GATEWAY CONFIGURATION
                    </span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Loan Flow Razorpay Gateway
                </h3>
                <div style="font-size: 13px; color: #64748b; margin-top: 3px;">
                    Isolated credentials specifically for Loan Processing Fees and Credit Repayments
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.finance.products') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    ⚙️ Products &amp; Fees
                </a>
                <a href="{{ route('admin.finance.applications') }}" class="btn btn-sm" style="background: #0f172a; color: #ffffff; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px;">
                    Loan Applications
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 py-3 px-4 mb-4" style="border-radius: 8px; background: #ecfdf5; color: #065f46; font-size: 13px; font-weight: 600; border-left: 4px solid #059669 !important;" role="alert">
            <strong>✓ Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 10px 14px; color: #065f46;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 py-3 px-4 mb-4" style="border-radius: 8px; background: #fef2f2; color: #991b1b; font-size: 13px; font-weight: 600; border-left: 4px solid #ef4444 !important;" role="alert">
            <strong>⚠ Error:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 10px 14px; color: #991b1b;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <!-- Architecture Isolation Notice -->
        <div class="card border-0 mb-4" style="background: #eff6ff; border: 1px solid #bfdbfe !important; border-radius: 8px;">
            <div class="p-3 d-flex align-items-start gap-3">
                <div style="font-size: 24px; line-height: 1;">🔒</div>
                <div>
                    <div style="font-size: 14px; font-weight: 700; color: #1e3a8a;">
                        Independent Dual-Gateway Architecture Active
                    </div>
                    <div style="font-size: 13px; color: #1e40af; margin-top: 2px; line-height: 1.5;">
                        The Razorpay credentials configured below are <strong>strictly scoped to the Loan &amp; Credit Ecosystem</strong> (Processing Fee payments, Daily Credit recoveries, Zero-CIBIL, and Virtual Loans).
                        The main platform's payment settings (Food delivery, driver rides, user wallets, Payment Key 13) are untouched and continue running independently.
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Settings Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="p-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #e2e8f0;">
                        <div>
                            <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">
                                Razorpay Credentials (Loan Flow)
                            </h5>
                            <div style="font-size: 12px; color: #64748b;">
                                Obtain these from your Razorpay Dashboard → Settings → API Keys
                            </div>
                        </div>
                        <div>
                            @if(!empty($config['key']))
                                <span class="badge" style="background: {{ $config['is_enabled'] ? '#ecfdf5' : '#fef2f2' }}; color: {{ $config['is_enabled'] ? '#065f46' : '#991b1b' }}; font-size: 12px; font-weight: 700; padding: 5px 10px; border-radius: 4px;">
                                    {{ $config['is_enabled'] ? '● GATEWAY ACTIVE' : '○ DISABLED' }}
                                </span>
                            @else
                                <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 12px; font-weight: 700; padding: 5px 10px; border-radius: 4px;">
                                    NOT CONFIGURED
                                </span>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.finance.settings.payment.save') }}" class="p-4">
                        @csrf

                        <!-- Environment Toggle -->
                        <div class="mb-4">
                            <label class="form-label d-block" style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
                                Environment / Mode
                            </label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="d-flex align-items-center gap-3 p-3 border rounded" style="cursor: pointer; background: {{ !$config['is_sandbox'] ? '#f8fafc' : '#ffffff' }}; border-color: {{ !$config['is_sandbox'] ? '#0f172a' : '#e2e8f0' }} !important;">
                                        <input type="radio" name="is_sandbox" value="0" {{ !$config['is_sandbox'] ? 'checked' : '' }} style="transform: scale(1.2);">
                                        <div>
                                            <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Live / Production Mode</div>
                                            <div style="font-size: 11px; color: #64748b;">Real money transactions (rzp_live_...)</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="d-flex align-items-center gap-3 p-3 border rounded" style="cursor: pointer; background: {{ $config['is_sandbox'] ? '#fef3c7' : '#ffffff' }}; border-color: {{ $config['is_sandbox'] ? '#d97706' : '#e2e8f0' }} !important;">
                                        <input type="radio" name="is_sandbox" value="1" {{ $config['is_sandbox'] ? 'checked' : '' }} style="transform: scale(1.2);">
                                        <div>
                                            <div style="font-size: 13px; font-weight: 700; color: #92400e;">Test / Sandbox Mode</div>
                                            <div style="font-size: 11px; color: #b45309;">Simulated payments (rzp_test_...)</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Razorpay Key ID -->
                        <div class="mb-3">
                            <label class="form-label" style="font-size: 13px; font-weight: 700; color: #0f172a;">
                                Razorpay Key ID <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="background: #f1f5f9; border-color: #cbd5e1; font-size: 13px;">🔑</span>
                                </div>
                                <input type="text" name="key" class="form-control" value="{{ old('key', $config['key']) }}" placeholder="rzp_live_xxxxxxxxxxxxxx or rzp_test_xxxxxxxxxxxxxx" required style="font-family: monospace; font-size: 13px; border-color: #cbd5e1;">
                            </div>
                            <small class="form-text text-muted" style="font-size: 11px;">
                                Public Key provided by Razorpay. Used in the loan checkout window.
                            </small>
                        </div>

                        <!-- Razorpay Key Secret -->
                        <div class="mb-3">
                            <label class="form-label" style="font-size: 13px; font-weight: 700; color: #0f172a;">
                                Razorpay Key Secret <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="background: #f1f5f9; border-color: #cbd5e1; font-size: 13px;">🔒</span>
                                </div>
                                <input type="password" id="rzpSecretInput" name="secret" class="form-control" value="{{ old('secret', $config['secret']) }}" placeholder="Enter Razorpay Secret Key" required style="font-family: monospace; font-size: 13px; border-color: #cbd5e1;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="toggleSecretVisibility()" style="border-color: #cbd5e1; font-size: 12px;">
                                        👁 Show
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted" style="font-size: 11px;">
                                Private Secret Key provided by Razorpay. Kept securely on server for signature verification and refund operations.
                            </small>
                        </div>

                        <!-- Merchant Display Name -->
                        <div class="mb-3">
                            <label class="form-label" style="font-size: 13px; font-weight: 700; color: #0f172a;">
                                Checkout Display / Brand Name
                            </label>
                            <input type="text" name="merchant_name" class="form-control" value="{{ old('merchant_name', $config['merchant_name']) }}" placeholder="Fiinway Loan & Credit" style="font-size: 13px; border-color: #cbd5e1;">
                            <small class="form-text text-muted" style="font-size: 11px;">
                                Shown at the top of the Razorpay checkout popup to the borrower.
                            </small>
                        </div>

                        <!-- Webhook Secret (Optional) -->
                        <div class="mb-4">
                            <label class="form-label" style="font-size: 13px; font-weight: 700; color: #0f172a;">
                                Razorpay Webhook Secret <span class="text-muted" style="font-weight: 400;">(Optional)</span>
                            </label>
                            <input type="text" name="webhook_secret" class="form-control" value="{{ old('webhook_secret', $config['webhook_secret']) }}" placeholder="Optional webhook secret for signature verification" style="font-family: monospace; font-size: 13px; border-color: #cbd5e1;">
                            <small class="form-text text-muted" style="font-size: 11px;">
                                Configured in Razorpay Webhooks tab if you wish to verify automated background capture events.
                            </small>
                        </div>

                        <!-- Enable Switch -->
                        <div class="p-3 mb-4 rounded d-flex align-items-center justify-content-between" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div>
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Enable Gateway for Loan Flow</div>
                                <div style="font-size: 11px; color: #64748b;">When turned on, borrowers pay processing fees and EMIs through this Razorpay account.</div>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="isEnabledSwitch" name="is_enabled" value="1" {{ $config['is_enabled'] ? 'checked' : '' }}>
                                <label class="custom-control-label" for="isEnabledSwitch" style="cursor: pointer;"></label>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2">
                            <button type="submit" class="btn" style="background: #0f172a; color: #ffffff; font-size: 13px; font-weight: 600; border-radius: 6px; padding: 10px 24px;">
                                💾 Save Loan Gateway Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Status & Testing Panel -->
            <div class="col-lg-4">
                <!-- Test Connectivity Card -->
                <div class="card border-0 shadow-sm mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                        <h6 class="mb-0" style="font-size: 14px; font-weight: 700; color: #0f172a;">
                            ⚡ Verify Razorpay Connection
                        </h6>
                    </div>
                    <div class="p-3">
                        <p style="font-size: 12px; color: #64748b; line-height: 1.5; margin-bottom: 14px;">
                            Test whether the current loan credentials can authenticate with Razorpay API servers.
                        </p>

                        <form method="POST" action="{{ route('admin.finance.settings.payment.test') }}">
                            @csrf
                            <input type="hidden" name="key" value="{{ $config['key'] }}">
                            <input type="hidden" name="secret" value="{{ $config['secret'] }}">

                            <button type="submit" class="btn btn-block" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 14px;" {{ empty($config['key']) || empty($config['secret']) ? 'disabled' : '' }}>
                                🔄 Test Connection to Razorpay
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Snapshot Summary Card -->
                <div class="card border-0 shadow-sm mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                        <h6 class="mb-0" style="font-size: 14px; font-weight: 700; color: #0f172a;">
                            📊 Gateway Summary
                        </h6>
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #f1f5f9; font-size: 12px;">
                            <span style="color: #64748b;">Target Scope</span>
                            <span style="font-weight: 700; color: #0f172a;">Loan Flow Only</span>
                        </div>
                        <div class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #f1f5f9; font-size: 12px;">
                            <span style="color: #64748b;">Mode</span>
                            <span class="badge" style="background: {{ $config['is_sandbox'] ? '#fef3c7' : '#ecfdf5' }}; color: {{ $config['is_sandbox'] ? '#92400e' : '#065f46' }};">
                                {{ $config['is_sandbox'] ? 'Test / Sandbox' : 'Live Production' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #f1f5f9; font-size: 12px;">
                            <span style="color: #64748b;">Key ID Configured</span>
                            <span style="font-family: monospace; font-size: 11px; font-weight: 600;">
                                @if(!empty($config['key']))
                                    {{ substr($config['key'], 0, 8) }}...{{ substr($config['key'], -4) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #f1f5f9; font-size: 12px;">
                            <span style="color: #64748b;">Secret Configured</span>
                            <span style="font-weight: 600; color: {{ !empty($config['secret']) ? '#059669' : '#94a3b8' }};">
                                {{ !empty($config['secret']) ? '✓ Saved' : '— Missing' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2" style="font-size: 12px;">
                            <span style="color: #64748b;">Whole Platform (Key 13)</span>
                            <span class="badge" style="background: #f1f5f9; color: #334155;">Protected &amp; Untouched</span>
                        </div>
                    </div>
                </div>

                <!-- Webhook URL Card -->
                <div class="card border-0 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                        <h6 class="mb-0" style="font-size: 14px; font-weight: 700; color: #0f172a;">
                            🔗 Loan Webhook Endpoint
                        </h6>
                    </div>
                    <div class="p-3">
                        <p style="font-size: 11px; color: #64748b; margin-bottom: 8px;">
                            Set this URL in your Razorpay Dashboard under Webhooks if you wish to receive instant server-side notifications:
                        </p>
                        <div class="p-2 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-family: monospace; font-size: 11px; word-break: break-all; color: #0f172a;">
                            {{ url('/api/v1/finance/webhooks/razorpay') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function toggleSecretVisibility() {
    var input = document.getElementById('rzpSecretInput');
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
</script>
@endsection

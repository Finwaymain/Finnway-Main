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
.smtp-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    overflow: hidden;
}
.smtp-card .card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.smtp-card .card-body {
    padding: 22px;
}
.preset-btn {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.preset-btn:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}
.preset-btn.active-preset {
    background: #e0f2fe;
    color: #0369a1;
    border-color: #7dd3fc;
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
.switch-label input:checked + .switch-slider { background-color: #16a34a; }
.switch-label input:checked + .switch-slider:before { transform: translateX(20px); }

.test-result-box {
    display: none;
    border-radius: 8px;
    padding: 14px;
    margin-top: 14px;
    font-size: 13px;
    line-height: 1.4;
}
.test-result-box.success {
    display: block;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}
.test-result-box.error {
    display: block;
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.status-pill-verified {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}
.status-pill-failed {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}
.status-pill-untested {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #cbd5e1;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}
</style>

<div class="page-wrapper">
    <div class="container-fluid pt-3">
        <!-- Page Header -->
        <div class="page-header-card d-flex align-items-center justify-content-between">
            <div>
                <h3 class="mb-1"><i class="mdi mdi-email-check text-primary mr-2"></i>Email & SMTP Configuration</h3>
                <p class="mb-0 text-muted">Configure outbound SMTP mail delivery for Email OTP verification, plan tax invoices, and transactional alerts.</p>
            </div>
            <div>
                @if($setting->last_test_status === 'success')
                    <span class="status-pill-verified">
                        <i class="fa fa-check-circle"></i> Connection Verified
                    </span>
                @elseif($setting->last_test_status === 'failed')
                    <span class="status-pill-failed">
                        <i class="fa fa-times-circle"></i> Last Test Failed
                    </span>
                @else
                    <span class="status-pill-untested">
                        <i class="fa fa-question-circle"></i> Untested
                    </span>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <!-- Left Column: Configuration Form -->
            <div class="col-lg-8">
                <div class="smtp-card">
                    <div class="card-header">
                        <h5 class="mb-0 font-weight-bold"><i class="mdi mdi-server mr-2 text-primary"></i>SMTP Server Credentials</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small mr-2">Quick Presets:</span>
                            <button type="button" class="preset-btn" onclick="applyPreset('hostinger')">Hostinger</button>
                            <button type="button" class="preset-btn" onclick="applyPreset('gmail')">Gmail</button>
                            <button type="button" class="preset-btn" onclick="applyPreset('office365')">Office 365</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('smtp-settings.save') }}" method="POST">
                            @csrf
                            <input type="hidden" name="mail_mailer" value="smtp">

                            <div class="row">
                                <div class="col-md-8 form-group">
                                    <label class="font-weight-bold">SMTP Host <span class="text-danger">*</span></label>
                                    <input type="text" name="mail_host" id="mail_host" class="form-control" value="{{ old('mail_host', $setting->mail_host) }}" required placeholder="e.g. smtp.hostinger.com">
                                    <small class="form-text text-muted">Mail server hostname provided by your email service provider.</small>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="font-weight-bold">SMTP Port <span class="text-danger">*</span></label>
                                    <input type="number" name="mail_port" id="mail_port" class="form-control" value="{{ old('mail_port', $setting->mail_port) }}" required placeholder="465 or 587">
                                    <small class="form-text text-muted">Standard ports: 465 (SSL) or 587 (TLS).</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Encryption Protocol</label>
                                    <select name="mail_encryption" id="mail_encryption" class="form-control">
                                        <option value="ssl" {{ old('mail_encryption', $setting->mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL (Recommended for Port 465)</option>
                                        <option value="tls" {{ old('mail_encryption', $setting->mail_encryption) == 'tls' ? 'selected' : '' }}>TLS (Recommended for Port 587)</option>
                                        <option value="none" {{ in_array(old('mail_encryption', $setting->mail_encryption), ['none', null, '']) ? 'selected' : '' }}>None (Unencrypted)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group d-flex align-items-center justify-content-between pt-4">
                                    <div>
                                        <label class="font-weight-bold mb-0 d-block">Enable Outbound SMTP</label>
                                        <small class="text-muted">Toggle off to pause sending transactional emails.</small>
                                    </div>
                                    <label class="switch-label">
                                        <input type="checkbox" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }}>
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">SMTP Username / Email <span class="text-danger">*</span></label>
                                    <input type="text" name="mail_username" id="mail_username" class="form-control" value="{{ old('mail_username', $setting->mail_username) }}" required placeholder="e.g. git@openscore.msmeloan.sbs">
                                    <small class="form-text text-muted">The complete email address used to authenticate with SMTP.</small>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">SMTP Password</label>
                                    <div class="input-group">
                                        <input type="password" name="mail_password" id="mail_password" class="form-control" placeholder="Enter new password or leave blank to keep existing" value="{{ $setting->mail_password }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                                <i class="fa fa-eye" id="togglePasswordIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">For Gmail, generate and use an <strong>App Password</strong>.</small>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">From Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="mail_from_address" id="mail_from_address" class="form-control" value="{{ old('mail_from_address', $setting->mail_from_address) }}" required placeholder="e.g. noreply@fiinway.com">
                                    <small class="form-text text-muted">The address users will see in the 'From' header.</small>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">From Name <span class="text-danger">*</span></label>
                                    <input type="text" name="mail_from_name" id="mail_from_name" class="form-control" value="{{ old('mail_from_name', $setting->mail_from_name) }}" required placeholder="e.g. Fiinway Desk">
                                    <small class="form-text text-muted">Sender name displayed in the recipient inbox.</small>
                                </div>
                            </div>

                            <div class="pt-3">
                                <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                                    <i class="fa fa-save mr-1"></i> Save & Activate Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Verification & Testing Tool -->
            <div class="col-lg-4">
                <!-- Test / Verify Tool -->
                <div class="smtp-card">
                    <div class="card-header">
                        <h5 class="mb-0 font-weight-bold"><i class="mdi mdi-send-check text-success mr-2"></i>Verify SMTP Connection</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Send a live test verification email to confirm that your SMTP host, port, credentials, and encryption are working correctly.
                        </p>

                        <div class="form-group">
                            <label class="font-weight-bold small">Recipient Test Email</label>
                            <input type="email" id="test_email" class="form-control" placeholder="Enter recipient email" value="{{ auth()->user()->email ?? 'admin@fiinway.com' }}">
                        </div>

                        <button type="button" id="btnSendTest" class="btn btn-success btn-block font-weight-bold" onclick="sendTestEmail()">
                            <i class="fa fa-paper-plane mr-1" id="testBtnIcon"></i> Send Test Email & Verify
                        </button>

                        <div id="testResultBox" class="test-result-box">
                            <div id="testResultMessage"></div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <h6 class="font-weight-bold small text-uppercase text-muted">Last Verification Status</h6>
                            <div class="small mt-2">
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Status:</span>
                                    <span id="lastTestStatusBadge" class="font-weight-bold text-{{ $setting->last_test_status === 'success' ? 'success' : ($setting->last_test_status === 'failed' ? 'danger' : 'muted') }}">
                                        {{ ucfirst($setting->last_test_status ?? 'Not Tested') }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Verified At:</span>
                                    <span id="lastTestedAtText">{{ $setting->last_tested_at ? $setting->last_tested_at->format('d M Y, h:i A') : 'Never' }}</span>
                                </div>
                                @if($setting->last_test_message)
                                <div class="mt-2 text-muted small p-2 bg-light rounded" id="lastTestMessageText" style="word-break: break-all;">
                                    {{ $setting->last_test_message }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Provider Guidelines Card -->
                <div class="smtp-card">
                    <div class="card-header">
                        <h5 class="mb-0 font-weight-bold"><i class="mdi mdi-information-outline text-info mr-2"></i>Provider Guidelines</h5>
                    </div>
                    <div class="card-body small text-muted">
                        <div class="mb-3">
                            <strong class="text-dark d-block mb-1">Hostinger (Recommended):</strong>
                            Host: <code>smtp.hostinger.com</code><br>
                            Port: <code>465</code> (SSL) or <code>587</code> (TLS)<br>
                            Username: Your full Hostinger Webmail email address.
                        </div>
                        <div class="mb-3">
                            <strong class="text-dark d-block mb-1">Gmail / Google Workspace:</strong>
                            Host: <code>smtp.gmail.com</code> | Port: <code>587</code> (TLS)<br>
                            Must enable 2-Step Verification & create an <strong>App Password</strong> under your Google Account security.
                        </div>
                        <div>
                            <strong class="text-dark d-block mb-1">Transactional Use Cases:</strong>
                            ✓ Email OTP verification for Driver & Consumer plans.<br>
                            ✓ Branded PDF Tax Invoices & payment confirmations.<br>
                            ✓ Courier dispatch & welcome kit updates.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('mail_password');
    const icon = document.getElementById('togglePasswordIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function applyPreset(provider) {
    if (provider === 'hostinger') {
        document.getElementById('mail_host').value = 'smtp.hostinger.com';
        document.getElementById('mail_port').value = '465';
        document.getElementById('mail_encryption').value = 'ssl';
    } else if (provider === 'gmail') {
        document.getElementById('mail_host').value = 'smtp.gmail.com';
        document.getElementById('mail_port').value = '587';
        document.getElementById('mail_encryption').value = 'tls';
    } else if (provider === 'office365') {
        document.getElementById('mail_host').value = 'smtp.office365.com';
        document.getElementById('mail_port').value = '587';
        document.getElementById('mail_encryption').value = 'tls';
    }
}

function sendTestEmail() {
    const emailInput = document.getElementById('test_email');
    const testEmail = emailInput.value.trim();
    if (!testEmail) {
        alert('Please enter a recipient email address for testing.');
        emailInput.focus();
        return;
    }

    const btn = document.getElementById('btnSendTest');
    const icon = document.getElementById('testBtnIcon');
    const box = document.getElementById('testResultBox');
    const msg = document.getElementById('testResultMessage');

    btn.disabled = true;
    icon.className = 'fa fa-spinner fa-spin mr-1';
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Connecting & Sending...';

    box.className = 'test-result-box';
    box.style.display = 'none';

    fetch("{{ route('smtp-settings.test') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ test_email: testEmail })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-paper-plane mr-1"></i> Send Test Email & Verify';

        box.style.display = 'block';
        if (data.success) {
            box.className = 'test-result-box success';
            msg.innerHTML = '<strong><i class="fa fa-check-circle"></i> Verification Successful!</strong><br>' + data.message;
            document.getElementById('lastTestStatusBadge').className = 'font-weight-bold text-success';
            document.getElementById('lastTestStatusBadge').innerText = 'Verified';
            if (data.tested_at) {
                document.getElementById('lastTestedAtText').innerText = data.tested_at;
            }
        } else {
            box.className = 'test-result-box error';
            msg.innerHTML = '<strong><i class="fa fa-times-circle"></i> Connection Failed!</strong><br>' + data.message;
            document.getElementById('lastTestStatusBadge').className = 'font-weight-bold text-danger';
            document.getElementById('lastTestStatusBadge').innerText = 'Failed';
            if (data.tested_at) {
                document.getElementById('lastTestedAtText').innerText = data.tested_at;
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-paper-plane mr-1"></i> Send Test Email & Verify';
        box.style.display = 'block';
        box.className = 'test-result-box error';
        msg.innerHTML = '<strong><i class="fa fa-times-circle"></i> Request Error:</strong><br>' + err.message;
    });
}
</script>

@endsection

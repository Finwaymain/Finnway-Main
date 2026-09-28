@extends('finance.layouts.base')
@section('title', 'Documents Submitted — Fiinway')
@section('header-sub', 'Cash Loan')
@section('back')
@endsection

@section('content')
<div class="fw-card" style="padding: 24px 16px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #ecfdf5; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 10px;">
            ✓
        </div>
        <h2 class="fw-heading" style="font-size: 20px; font-weight: 700; color: #059669; margin-bottom: 4px;">
            Documents Submitted Successfully
        </h2>
        <div style="display: inline-block; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 20px;">
            🟡 Underwriting Review In Progress
        </div>
    </div>

    <!-- Application Summary -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
            <span style="color: #64748b;">Application No.:</span>
            <span style="font-family: monospace; font-weight: 700; color: #0f172a;">{{ $appNumber }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 13px;">
            <span style="color: #64748b;">Application Status:</span>
            <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700;">Documents Resubmitted</span>
        </div>
    </div>

    <!-- Submitted Documents List -->
    <div style="margin-bottom: 20px;">
        <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 10px;">
            Uploaded Documents for Verification:
        </div>
        <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; font-weight: 600; text-align: left;">
                        <th style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0;">Document</th>
                        <th style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: right;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerDocuments as $doc)
                    <tr>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-weight: 600;">
                            {{ ucwords(str_replace('_', ' ', $doc->document_type)) }}
                        </td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; text-align: right;">
                            <span class="badge" style="background: {{ $doc->status === 'verified' ? '#ecfdf5' : '#fef3c7' }}; color: {{ $doc->status === 'verified' ? '#065f46' : '#92400e' }}; font-size: 11px; padding: 3px 8px; border-radius: 4px;">
                                {{ strtoupper($doc->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" style="padding: 14px; text-align: center; color: #64748b;">
                            Documents recorded and sent to underwriting.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Live Status Notice Box -->
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px 16px; text-align: center;">
        <div style="font-size: 13px; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
            ⏳ Admin Verification in Progress
        </div>
        <p style="font-size: 12px; color: #1e3a8a; line-height: 1.5; margin: 0;">
            Our credit underwriting team has received your documents and is reviewing them right now.<br>
            <strong>This screen will automatically advance once approved by the admin.</strong>
        </p>
    </div>
</div>

{{-- No sticky bottom continue button per specification --}}
@section('sticky-bottom')
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    var pollUrl = "{{ route('finance.cash_loan.application_status_poll', ['phone' => $phone, 'current_step' => 's25', 'application_id' => $application->id ?? '']) }}";
    
    // Real-Time Poller for Admin Approval (every 2.5s)
    var pollInterval = setInterval(function () {
        fetch(pollUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data && data.action === 'redirect' && data.redirect_url) {
                clearInterval(pollInterval);
                window.location.href = data.redirect_url;
            }
        })
        .catch(function (err) {
            console.log('Polling waiting for approval...', err);
        });
    }, 2500);
});
</script>
@endsection

@extends('finance.layouts.base')
@section('title', 'Repayments & EMI Schedule — Fiinway')
@section('header-sub', 'Loan Repayments')

@section('back')
<a href="{{ route('finance.hub', ['phone' => $phone]) }}" class="fw-back">← Back to Dashboard</a>
@endsection

@section('content')
<div style="padding-bottom: 24px;">

    {{-- Header Title --}}
    <div style="margin-bottom: 18px;">
        <h1 class="fw-section-title" style="font-size: 20px;">Repayments &amp; Schedule</h1>
        <p class="fw-section-sub" style="font-size: 12.5px; margin-bottom: 0;">Manage your loan EMIs, view upcoming due dates, and make repayments.</p>
    </div>

    @if(empty($loan))
    <div class="fw-card fw-text-center" style="padding: 30px 16px;">
        <div style="font-size: 40px; margin-bottom: 10px;">💳</div>
        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">No Active Loan Found</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">You currently do not have any active disbursed loans requiring repayment.</p>
        <a href="{{ route('finance.hub', ['phone' => $phone]) }}" class="fw-btn fw-btn-primary" style="display:inline-block; width:auto; padding:10px 20px;">
            Explore Loan Products
        </a>
    </div>
    @else

    {{-- Loan Summary & Progress Card --}}
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 14px; padding: 18px; color: white; margin-bottom: 18px; border: 1px solid #334155; box-shadow: 0 4px 16px rgba(15,23,42,0.15);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px;">
            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 3px 8px; border-radius: 4px; font-weight: 700;">
                ● {{ $loan->application_status === 'CLOSED' ? 'Closed' : 'Active Facility' }}
            </span>
            <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">{{ $loan->application_number }}</span>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom: 12px;">
            <div>
                <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Total Loan Amount</div>
                <div style="font-size: 24px; font-weight: 800; color: #ffffff;">₹{{ number_format($loan->approved_amount ?: $loan->requested_amount) }}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Outstanding Due</div>
                <div style="font-size: 20px; font-weight: 800; color: #f5a623;">₹{{ number_format($totalOutstanding) }}</div>
            </div>
        </div>

        @php
            $totalPrincipal = floatval($loan->approved_amount ?: $loan->requested_amount);
            $totalScheduleDue = $schedules->sum('total_due');
            $progressPercent = $totalScheduleDue > 0 ? min(100, round(($totalPaid / $totalScheduleDue) * 100)) : 0;
        @endphp

        {{-- Progress Bar --}}
        <div style="margin-bottom: 8px;">
            <div style="display:flex; justify-content:space-between; font-size: 11px; color: #cbd5e1; margin-bottom: 4px;">
                <span>Repaid: ₹{{ number_format($totalPaid) }} ({{ $progressPercent }}%)</span>
                <span>{{ $schedules->where('status', 'paid')->count() }}/{{ $schedules->count() }} EMIs</span>
            </div>
            <div style="height: 6px; background: #334155; border-radius: 6px; overflow: hidden;">
                <div style="width: {{ $progressPercent }}%; height: 100%; background: #10b981; border-radius: 6px; transition: width 0.4s ease;"></div>
            </div>
        </div>
    </div>

    {{-- Upcoming EMI Due Card --}}
    @if($nextDue)
    <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 16px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(245, 166, 35, 0.08);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 12px;">
            <div>
                <span style="font-size: 10.5px; text-transform: uppercase; font-weight: 800; background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 4px; display:inline-block; margin-bottom: 4px;">
                    Next Installment #{{ $nextDue->day_number }}
                </span>
                <div style="font-size: 22px; font-weight: 800; color: #0f172a;">
                    ₹{{ number_format($nextDue->total_due) }}
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                    Due on <strong>{{ \Carbon\Carbon::parse($nextDue->schedule_date)->format('d F Y') }}</strong>
                </div>
            </div>
            <button type="button" class="fw-btn fw-btn-primary" style="width:auto; padding: 10px 18px; font-size: 13px;" onclick="triggerEmiPayment({{ $nextDue->id }}, {{ $nextDue->total_due }}, {{ $nextDue->day_number }});">
                💳 Pay Now
            </button>
        </div>
        <div style="font-size: 11px; color: #92400e; background: #fefce8; padding: 8px 10px; border-radius: 6px;">
            💡 Paying your EMI on or before the due date boosts your CIBIL credit score and increases your future pre-approved limit.
        </div>
    </div>
    @else
    <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; color: #065f46; display:flex; align-items:center; gap:10px;">
        <span style="font-size: 24px;">🎉</span>
        <div>
            <div style="font-weight: 700; font-size: 13.5px;">All EMIs are currently up to date!</div>
            <div style="font-size: 12px; color: #047857; margin-top: 2px;">You have no pending dues at this moment.</div>
        </div>
    </div>
    @endif

    {{-- Repayment Schedule List --}}
    <div style="margin-bottom: 12px; display:flex; justify-content:space-between; align-items:center;">
        <h2 style="font-size: 15px; font-weight: 700; color: var(--navy);">Installment Schedule</h2>
        <span style="font-size: 11px; color: #64748b;">{{ $schedules->count() }} Total Installments</span>
    </div>

    <div class="fw-card" style="padding: 0; overflow: hidden;">
        @foreach($schedules as $sched)
        <div style="display:flex; align-items:center; justify-content:space-between; padding: 14px 16px; border-bottom: 1px solid #f1f5f9; background: {{ $sched->status === 'paid' ? '#fafafa' : '#ffffff' }};">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size: 12px; font-weight: 700; background: {{ $sched->status === 'paid' ? '#ecfdf5' : '#f8fafc' }}; color: {{ $sched->status === 'paid' ? '#059669' : '#475569' }}; border: 1px solid {{ $sched->status === 'paid' ? '#a7f3d0' : '#e2e8f0' }};">
                    @if($sched->status === 'paid') ✓ @else {{ $sched->day_number }} @endif
                </div>
                <div>
                    <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;">
                        Installment #{{ $sched->day_number }}
                    </div>
                    <div style="font-size: 11.5px; color: #64748b; margin-top: 1px;">
                        Due: {{ \Carbon\Carbon::parse($sched->schedule_date)->format('d M Y') }}
                        @if($sched->status === 'paid' && $sched->paid_at)
                        · <span style="color:#059669;">Paid on {{ \Carbon\Carbon::parse($sched->paid_at)->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 14px; font-weight: 700; color: #0f172a;">
                    ₹{{ number_format($sched->total_due) }}
                </div>
                <div style="margin-top: 2px;">
                    @if($sched->status === 'paid')
                        <span style="font-size: 10px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 6px; border-radius: 4px; border: 1px solid #a7f3d0;">
                            PAID ✓
                        </span>
                    @elseif($sched->status === 'overdue')
                        <button type="button" onclick="triggerEmiPayment({{ $sched->id }}, {{ $sched->total_due }}, {{ $sched->day_number }});" style="font-size: 10px; font-weight: 700; color: #dc2626; background: #fef2f2; border: 1px solid #f87171; padding: 3px 8px; border-radius: 4px; cursor: pointer;">
                            PAY OVERDUE
                        </button>
                    @else
                        <button type="button" onclick="triggerEmiPayment({{ $sched->id }}, {{ $sched->total_due }}, {{ $sched->day_number }});" style="font-size: 10px; font-weight: 700; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; padding: 3px 8px; border-radius: 4px; cursor: pointer;">
                            PAY NOW
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @endif

</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function triggerEmiPayment(scheduleId, amount, dayNumber) {
    const razorpayKey = "{{ $razorpayKey ?? 'rzp_test_fiinway' }}";
    const phone = "{{ $phone ?? '' }}";
    const customerName = "{{ $applicantName ?? 'Customer' }}";

    if (!confirm('Proceed to pay Installment #' + dayNumber + ' of ₹' + amount + '?')) {
        return;
    }

    const options = {
        "key": razorpayKey,
        "amount": Math.round(amount * 100),
        "currency": "INR",
        "name": "Fiinway Finance",
        "description": "EMI Repayment Installment #" + dayNumber,
        "image": "https://api.fiinway.com/assets/images/logo.png",
        "prefill": {
            "name": customerName,
            "contact": phone
        },
        "theme": {
            "color": "#1a5fa8"
        },
        "handler": function (response) {
            submitPaymentConfirmation(scheduleId, amount, response.razorpay_payment_id);
        }
    };

    try {
        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function (resp) {
            alert('Payment failed: ' + resp.error.description);
        });
        rzp.open();
    } catch (e) {
        // Fallback for simulation / mock testing
        const mockPayId = 'PAY_SIM_' + Date.now();
        submitPaymentConfirmation(scheduleId, amount, mockPayId);
    }
}

function submitPaymentConfirmation(scheduleId, amount, paymentId) {
    fetch("{{ route('finance.repayments.pay') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            schedule_id: scheduleId,
            amount: amount,
            payment_id: paymentId,
            phone: "{{ $phone ?? '' }}"
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Payment successful!');
            window.location.reload();
        } else {
            alert(data.message || 'Payment processing failed.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error while recording repayment.');
    });
}
</script>
@endpush

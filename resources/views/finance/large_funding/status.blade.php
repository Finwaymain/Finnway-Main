@extends('finance.layouts.base')
@section('title', 'Application Status — Private Funding (₹2Cr to ₹100Cr)')
@section('header-sub', 'Live Syndicate Tracker')

@section('content')
<div style="padding-bottom: 30px;">

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
    <div style="background: #ecfdf5; border: 1.5px solid #6ee7b7; border-radius: 10px; padding: 14px; margin-bottom: 16px; color: #065f46; font-size: 13px; font-weight: 600; display:flex; align-items:center; gap:8px;">
        <span>✓</span>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    {{-- =========================================================================
         STAGE 7 & 8: FUNDING APPROVED CELEBRATION SCREEN (Matches media_1791783271989.png)
    ========================================================================= --}}
    @if($activeApp->current_stage >= 7 || $activeApp->application_status === 'FUND_APPROVED' || $activeApp->application_status === 'DISBURSED')
    <div class="fw-card" style="background: linear-gradient(135deg, #022c22 0%, #064e3b 100%); color: #ffffff; border: 1.5px solid #10b981; border-radius: 20px; padding: 26px 20px; text-align: center; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.25);">
        
        <!-- Big Green Success Icon -->
        <div style="width: 72px; height: 72px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 0 0 8px rgba(16, 185, 129, 0.25);">
            <svg style="width: 40px; height: 40px; fill: white;" viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>
        </div>

        <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #6ee7b7; margin-bottom: 4px;">
            Institutional Sanction Milestone
        </div>
        <h2 style="font-size: 24px; font-weight: 800; color: #ffffff; margin-bottom: 6px;">
            Funding Approved!
        </h2>
        <p style="font-size: 13px; color: #a7f3d0; margin-bottom: 20px;">
            Congratulations! Private syndicate has formally approved your capital facility.
        </p>

        <!-- Approved Amount Card -->
        <div style="background: rgba(255, 255, 255, 0.08); border-radius: 14px; padding: 18px; margin-bottom: 20px; border: 1px solid rgba(255, 255, 255, 0.15);">
            <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.5px;">Approved Sanction Amount</div>
            <div style="font-size: 34px; font-weight: 800; color: #34d399; letter-spacing: -0.5px; margin: 6px 0;">
                ₹{{ number_format($activeApp->approved_amount ?: 100000000) }}
            </div>
            <div style="font-size: 12px; color: #cbd5e1;">
                Sanctioned by: <strong>{{ $activeApp->approved_investor_name ?: 'Fiinway Capital Partners & Syndicate' }}</strong>
            </div>
        </div>

        <!-- Transfer Status Tracker -->
        <div style="background: rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 14px; margin-bottom: 20px; text-align: left; border: 1px solid rgba(255, 255, 255, 0.1);">
            <div style="font-size: 11px; text-transform: uppercase; color: #6ee7b7; font-weight: 800; margin-bottom: 8px;">
                {{ $activeApp->current_stage == 8 ? '✓ Fund Transfer Completed' : '⚡ Fund Transfer In Progress' }}
            </div>
            
            <div style="display:flex; justify-content:space-between; font-size: 12.5px; color:#cbd5e1; margin-bottom:6px;">
                <span>Destination Bank:</span>
                <strong style="color:#ffffff;">{{ $activeApp->bank_name ?: 'Verified Corporate Account' }}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; font-size: 12.5px; color:#cbd5e1; margin-bottom:6px;">
                <span>Account Number:</span>
                <strong style="color:#ffffff;">••••{{ substr($activeApp->bank_account_number, -4) }}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; font-size: 12.5px; color:#cbd5e1;">
                <span>IFSC Code:</span>
                <strong style="color:#ffffff;">{{ $activeApp->bank_ifsc }}</strong>
            </div>

            @if($activeApp->disbursement_utr)
            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255, 255, 255, 0.1); display:flex; justify-content:space-between; font-size: 12px;">
                <span style="color:#94a3b8;">Banking UTR:</span>
                <strong style="color:#38bdf8; font-family:monospace;">{{ $activeApp->disbursement_utr }}</strong>
            </div>
            @endif
        </div>

        <div style="display:flex; gap:10px;">
            <a href="tel:18001234567" style="flex:1; background: #ffffff; color: #022c22; font-weight: 700; font-size: 13px; padding: 12px; border-radius: 8px; text-decoration: none;">
                📞 Syndicate Manager
            </a>
            <a href="javascript:alert('Institutional Sanction Letter is available for download.');" style="flex:1; background: #10b981; color: #ffffff; font-weight: 700; font-size: 13px; padding: 12px; border-radius: 8px; text-decoration: none;">
                📄 Sanction Letter
            </a>
        </div>
    </div>
    @endif

    {{-- =========================================================================
         FEE PAYMENT ACTION CARD (STAGE 2/3)
    ========================================================================= --}}
    @if($activeApp->fee_payment_status !== 'paid' && $activeApp->current_stage < 7)
    <div class="fw-card" style="border: 2px solid #6366f1; background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%); margin-bottom: 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
            <span style="font-size: 11px; font-weight: 800; background: #e0e7ff; color: #4338ca; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">
                Step 3: Service Fee Settlement
            </span>
            <span style="font-size: 11.5px; color: #dc2626; font-weight: 700;">
                @if($activeApp->fee_deadline)
                    Deadline: {{ $activeApp->fee_deadline->format('d M, h:i A') }}
                @else
                    Pending Action
                @endif
            </span>
        </div>

        <h3 style="font-size: 18px; font-weight: 800; color: #1e1b4b; margin-bottom: 6px;">
            Syndicate Underwriting Service Fee
        </h3>
        <p style="font-size: 12.5px; color: #475569; line-height: 1.45; margin-bottom: 16px;">
            Your project documentation and business model have been reviewed by our underwriting committee. Settle the verified institutional processing fee to forward your pitch directly to accredited investor syndicate boards.
        </p>

        <!-- Fee Breakdown Grid -->
        <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 14px; margin-bottom: 18px;">
            <div style="display:flex; justify-content:space-between; font-size: 13px; color: #475569; margin-bottom: 6px;">
                <span>Base Underwriting Fee:</span>
                <span>₹{{ number_format($activeApp->service_fee_base, 2) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size: 13px; color: #475569; margin-bottom: 8px;">
                <span>GST (18%):</span>
                <span>₹{{ number_format($activeApp->service_fee_gst, 2) }}</span>
            </div>
            <div style="border-top: 1px dashed #cbd5e1; padding-top: 8px; display:flex; justify-content:space-between; font-size: 15px; font-weight: 800; color: #1e1b4b;">
                <span>Total Payable:</span>
                <span style="color: #4f46e5;">₹{{ number_format($activeApp->service_fee_total, 2) }}</span>
            </div>
        </div>

        <!-- Direct Pay Form (Seamless razorpay or instant deduction) -->
        <form method="POST" action="{{ route('finance.large_funding.pay_fee') }}">
            @csrf
            <input type="hidden" name="phone" value="{{ $phone }}">
            <input type="hidden" name="payment_method" value="razorpay_upi">
            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #ffffff; font-weight: 800; font-size: 15px; padding: 14px; border-radius: 10px; border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);">
                Pay Fee ₹{{ number_format($activeApp->service_fee_total) }} &amp; Forward to Investors →
            </button>
        </form>
    </div>
    @endif

    {{-- =========================================================================
         REALTIME APPLICATION TIMELINE TRACKER (Matches media_1791783270067.png)
    ========================================================================= --}}
    <div class="fw-card" style="padding: 22px 18px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
            <div style="font-size: 16px; font-weight: 800; color: #0f172a;">Application Status</div>
            <span style="font-size: 11px; font-family: monospace; background: #f1f5f9; padding: 3px 8px; border-radius: 4px; font-weight: 700; color: #475569;">
                {{ $activeApp->application_number }}
            </span>
        </div>
        <p style="font-size: 12.5px; color: #64748b; margin-bottom: 20px;">
            Real-time multi-stage verification tracked directly by Institutional Underwriting.
        </p>

        <!-- Vertical Milestone Timeline -->
        @php
            $stages = [
                1 => ['title' => 'Application Submitted', 'sub' => 'Dossier & KYC uploaded successfully', 'icon' => '1'],
                2 => ['title' => 'Documents & Project Review', 'sub' => 'Underwriting Committee validation', 'icon' => '2'],
                3 => ['title' => 'Service Fee Payment', 'sub' => 'Nominal syndicate onboarding fee', 'icon' => '3'],
                4 => ['title' => 'Forwarded to Investors / Lenders', 'sub' => 'Pitch deck circulated to accredited syndicate funds', 'icon' => '4'],
                5 => ['title' => 'Funding Discussion', 'sub' => 'Investor evaluation & term discussion', 'icon' => '5'],
                6 => ['title' => 'Final Offer & Approval', 'sub' => 'Term sheet signing & legal due diligence', 'icon' => '6'],
                7 => ['title' => 'Funding Approved', 'sub' => 'Sanction letter issued & disbursement initialized', 'icon' => '7'],
                8 => ['title' => 'Amount Credited', 'sub' => 'Funds transferred to corporate bank account', 'icon' => '8'],
            ];
            $current = (int)$activeApp->current_stage;
        @endphp

        <div style="display:flex; flex-direction:column; gap: 0; position:relative;">
            @foreach($stages as $num => $info)
                @php
                    $isCompleted = ($current > $num) || ($current == $num && $num == 8);
                    $isInProgress = ($current == $num && $num < 8);
                    $isPending = ($current < $num);
                @endphp
                <div style="display:flex; gap: 14px; position:relative; padding-bottom: 24px;">
                    
                    {{-- Vertical connecting line --}}
                    @if($num < 8)
                    <div style="position: absolute; left: 15px; top: 32px; bottom: 0; width: 2px; background: {{ $isCompleted ? '#10b981' : '#e2e8f0' }};"></div>
                    @endif

                    {{-- Milestone Badge Icon --}}
                    <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; z-index: 2; flex-shrink: 0; 
                        background: {{ $isCompleted ? '#10b981' : ($isInProgress ? '#4f46e5' : '#f1f5f9') }}; 
                        color: {{ ($isCompleted || $isInProgress) ? '#ffffff' : '#94a3b8' }};
                        box-shadow: {{ $isInProgress ? '0 0 0 4px rgba(79, 70, 229, 0.2)' : 'none' }};">
                        @if($isCompleted)
                            ✓
                        @else
                            {{ $num }}
                        @endif
                    </div>

                    {{-- Milestone Content --}}
                    <div style="flex: 1;">
                        <div style="display:flex; justify-content:space-between; align-items:baseline;">
                            <div style="font-size: 14px; font-weight: 700; color: {{ $isPending ? '#94a3b8' : '#0f172a' }};">
                                {{ $info['title'] }}
                            </div>
                            @if($isCompleted)
                                <span style="font-size: 11px; font-weight: 700; color: #10b981; background: #ecfdf5; padding: 2px 6px; border-radius: 4px;">Completed</span>
                            @elseif($isInProgress)
                                <span style="font-size: 11px; font-weight: 700; color: #4f46e5; background: #e0e7ff; padding: 2px 6px; border-radius: 4px;">In Progress</span>
                            @else
                                <span style="font-size: 11px; font-weight: 600; color: #94a3b8;">Pending</span>
                            @endif
                        </div>
                        <div style="font-size: 12px; color: {{ $isPending ? '#cbd5e1' : '#64748b' }}; margin-top: 3px;">
                            {{ $info['sub'] }}
                        </div>

                        @if($num === 3 && $activeApp->fee_payment_status === 'paid')
                        <div style="font-size: 11px; color: #10b981; font-weight: 600; margin-top: 4px;">
                            ✓ Fee of ₹{{ number_format($activeApp->service_fee_total) }} paid (Ref: {{ $activeApp->fee_payment_ref }})
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div style="border-top: 1px solid #f1f5f9; padding-top: 16px; margin-top: 8px; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size: 12px; color: #64748b;">
                Requested: <strong>{{ $activeApp->required_funding_amount }}</strong>
            </div>
            <a href="{{ route('finance.hub', ['phone' => $phone]) }}" style="font-size: 12.5px; font-weight: 700; color: #4f46e5; text-decoration: none;">
                Finance Home →
            </a>
        </div>
    </div>

</div>
@endsection

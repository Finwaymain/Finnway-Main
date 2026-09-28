@extends('finance.layouts.base')
@section('title', 'Help & Support — Fiinway Finance')
@section('header-sub', 'Support')

@section('back')
<a href="{{ route('finance.hub', ['phone' => $phone]) }}" class="fw-back">← Back to Dashboard</a>
@endsection

@section('content')
<div style="padding-bottom: 24px;">

    {{-- Header Title --}}
    <div style="margin-bottom: 18px;">
        <h1 class="fw-section-title" style="font-size: 20px;">Customer Support</h1>
        <p class="fw-section-sub" style="font-size: 12.5px; margin-bottom: 0;">We are here 24x7 to assist you with loan disbursement, EMI repayments, or technical queries.</p>
    </div>

    {{-- Quick Contact Cards --}}
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
        <a href="tel:+919876543210" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:14px; text-decoration:none; color:inherit; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.04);">
            <div style="font-size:26px; margin-bottom:4px;">📞</div>
            <div style="font-size:13px; font-weight:700; color:#0f172a;">Call Helpline</div>
            <div style="font-size:11px; color:#64748b; margin-top:2px;">+91 98765 43210</div>
        </a>

        <a href="https://wa.me/919876543210?text=Hi%2C%20I%20have%20a%20query%20regarding%20my%20Fiinway%20Loan" target="_blank" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:14px; text-decoration:none; color:inherit; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.04);">
            <div style="font-size:26px; margin-bottom:4px;">💬</div>
            <div style="font-size:13px; font-weight:700; color:#059669;">WhatsApp</div>
            <div style="font-size:11px; color:#64748b; margin-top:2px;">Chat with an Agent</div>
        </a>
    </div>

    {{-- FAQ Section --}}
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 15px; font-weight: 700; color: var(--navy); margin-bottom: 12px;">Frequently Asked Questions</h2>

        <div class="fw-card" style="padding: 16px; margin-bottom: 10px;">
            <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; margin-bottom: 4px;">
                When will the disbursed amount reach my bank account?
            </div>
            <div style="font-size: 12.5px; color: #64748b; line-height: 1.5;">
                Disbursements via IMPS / NEFT are typically credited within 1–4 business hours once approved by underwriting. You will receive an SMS confirmation with your bank UTR number.
            </div>
        </div>

        <div class="fw-card" style="padding: 16px; margin-bottom: 10px;">
            <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; margin-bottom: 4px;">
                How can I pay my monthly EMI?
            </div>
            <div style="font-size: 12.5px; color: #64748b; line-height: 1.5;">
                You can pay your EMI anytime directly from the <strong>Repayments</strong> tab using UPI, NetBanking, Debit Card, or Razorpay. Auto-debit (eNACH) is also processed automatically on your due date.
            </div>
        </div>

        <div class="fw-card" style="padding: 16px; margin-bottom: 10px;">
            <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; margin-bottom: 4px;">
                Can I prepay or close my loan early?
            </div>
            <div style="font-size: 12.5px; color: #64748b; line-height: 1.5;">
                Yes! You can clear your total outstanding balance early with zero foreclosure charges. Once fully repaid, your loan will be closed and an NOC letter will be issued.
            </div>
        </div>

        <div class="fw-card" style="padding: 16px; margin-bottom: 10px;">
            <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; margin-bottom: 4px;">
                What should I do if my bank account details were incorrect?
            </div>
            <div style="font-size: 12.5px; color: #64748b; line-height: 1.5;">
                Please contact our customer support team immediately with your Application Number (#{{ $appNumber ?? 'FIIN-APP' }}) and a cancelled cheque or bank statement of your correct account.
            </div>
        </div>
    </div>

    {{-- Request Callback Card --}}
    <div class="fw-card" style="padding: 18px;">
        <h3 style="font-size: 14.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Request a Priority Callback</h3>
        <p style="font-size: 12px; color: #64748b; margin-bottom: 14px;">Leave your query and our loan executive will call you back within 30 minutes.</p>

        <form onsubmit="handleSupportSubmit(event);">
            <div class="fw-input-group">
                <label class="fw-label">Your Query / Issue</label>
                <textarea class="fw-input" id="supportQuery" rows="3" placeholder="Describe your query or request..." required style="resize:vertical;"></textarea>
            </div>
            <button type="submit" class="fw-btn fw-btn-primary" style="padding: 11px;">
                Submit Callback Request
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function handleSupportSubmit(e) {
    e.preventDefault();
    const query = document.getElementById('supportQuery').value;
    alert('Thank you! Your request has been logged. A loan support representative will call you shortly.');
    document.getElementById('supportQuery').value = '';
}
</script>
@endpush

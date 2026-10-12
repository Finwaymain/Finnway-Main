@extends('finance.layouts.base')
@section('title', 'Business Loan & Private Funding (₹2Cr to ₹100Cr) — Fiinway')
@section('header-sub', 'Private Funding Syndicate')

@section('content')
<div class="fw-card" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #ffffff; border: 1px solid #312e81; padding: 24px 20px; border-radius: 16px; margin-bottom: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
        <span style="background: rgba(99, 102, 241, 0.25); color: #a5b4fc; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid rgba(165, 180, 252, 0.3);">
            🚀 High-Ticket Funding
        </span>
        <span style="font-size: 12px; color: #cbd5e1; font-weight: 600;">₹2Cr – ₹100Cr</span>
    </div>
    
    <h2 style="font-size: 22px; font-weight: 800; line-height: 1.3; margin-bottom: 8px; color: #ffffff;">
        Business Loan &amp; Private Investor Funding
    </h2>
    <p style="font-size: 13.5px; color: #cbd5e1; line-height: 1.5; margin-bottom: 18px;">
        Empowering ambitious founders and enterprise ventures with institutional capital, equity funding, syndicated private debt, and large-scale expansion capital.
    </p>

    <div style="background: rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; border: 1px solid rgba(255, 255, 255, 0.12);">
        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; color: #94a3b8; font-weight: 700; margin-bottom: 8px;">Facility Highlights</div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12.5px;">
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="color:#38bdf8;">✓</span> Ticket: <strong>₹2 Cr to ₹100 Cr</strong>
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="color:#38bdf8;">✓</span> Stage: <strong>Idea to Established</strong>
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="color:#38bdf8;">✓</span> Type: <strong>Equity / Debt / Loan</strong>
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="color:#38bdf8;">✓</span> Syndicate: <strong>Verified HNIs &amp; Funds</strong>
            </div>
        </div>
    </div>

    <a href="{{ route('finance.large_funding.apply', ['phone' => request('phone') ?? ($phone ?? '')]) }}" 
       style="display: block; text-align: center; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #ffffff; font-weight: 800; font-size: 15px; padding: 14px; border-radius: 10px; text-decoration: none; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);">
        Apply For Funding Now →
    </a>
</div>

{{-- 7-Step Institutional Process Preview --}}
<div class="fw-card" style="padding: 20px;">
    <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">How It Works</div>
    <div style="font-size: 12.5px; color: #64748b; margin-bottom: 16px;">Transparent, real-time verified underwriting pipeline</div>

    <div style="display:flex; flex-direction:column; gap:14px;">
        <div style="display:flex; gap:12px; align-items:flex-start;">
            <div style="width:26px; height:26px; border-radius:50%; background:#e0e7ff; color:#4338ca; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; flex-shrink:0;">1</div>
            <div>
                <div style="font-size:13.5px; font-weight:700; color:#1e293b;">Fill Institutional Dossier</div>
                <div style="font-size:12px; color:#64748b;">Applicant profile, Business model, Founders, Requirement &amp; Pitch Deck</div>
            </div>
        </div>

        <div style="display:flex; gap:12px; align-items:flex-start;">
            <div style="width:26px; height:26px; border-radius:50%; background:#e0e7ff; color:#4338ca; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; flex-shrink:0;">2</div>
            <div>
                <div style="font-size:13.5px; font-weight:700; color:#1e293b;">Project &amp; Document Review</div>
                <div style="font-size:12px; color:#64748b;">Fiinway Underwriting Board validates legal, KYC, and financial projections</div>
            </div>
        </div>

        <div style="display:flex; gap:12px; align-items:flex-start;">
            <div style="width:26px; height:26px; border-radius:50%; background:#e0e7ff; color:#4338ca; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; flex-shrink:0;">3</div>
            <div>
                <div style="font-size:13.5px; font-weight:700; color:#1e293b;">Service Fee &amp; Syndicate Forwarding</div>
                <div style="font-size:12px; color:#64748b;">Nominal underwriting fee settled, pitch dossier forwarded directly to verified investors</div>
            </div>
        </div>

        <div style="display:flex; gap:12px; align-items:flex-start;">
            <div style="width:26px; height:26px; border-radius:50%; background:#dcfce7; color:#15803d; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; flex-shrink:0;">4</div>
            <div>
                <div style="font-size:13.5px; font-weight:700; color:#1e293b;">Discussion, Final Offer &amp; Sanction</div>
                <div style="font-size:12px; color:#64748b;">Term sheet finalization and direct disbursement into corporate bank account</div>
            </div>
        </div>
    </div>
</div>

<div style="text-align:center; padding:10px 0 20px;">
    <a href="{{ route('finance.hub', ['phone' => request('phone') ?? ($phone ?? '')]) }}" style="color: #64748b; font-size: 13px; font-weight: 600; text-decoration: none;">
        ← Back to Finance Hub
    </a>
</div>
@endsection

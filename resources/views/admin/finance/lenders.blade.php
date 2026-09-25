@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        BANKING &amp; NBFC NETWORK
                    </span>
                    <span style="color: #64748b; font-size: 13px; font-weight: 600;">Lending Institutions</span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Lender Partners &amp; Web Portals
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 py-2 px-3 mb-4" style="border-radius: 6px; background: #ecfdf5; color: #065f46; font-size: 13px; font-weight: 600; border-left: 4px solid #059669 !important;" role="alert">
            <strong>✓ Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 6px 12px; color: #065f46;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="row g-4">
            <!-- Partners Table -->
            <div class="col-lg-8">
                <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <div class="p-3" style="border-bottom: 1px solid #e2e8f0;">
                        <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">Configured Lending Partners</h5>
                        <div style="font-size: 12px; color: #64748b;">Partners presented to applicants in the single-partner lock stage</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 13px;">
                            <thead>
                                <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                                    <th style="padding: 12px 16px;">Institution</th>
                                    <th style="padding: 12px 16px;">Amount Range</th>
                                    <th style="padding: 12px 16px;">Interest Rate</th>
                                    <th style="padding: 12px 16px;">Tenure</th>
                                    <th style="padding: 12px 16px;">Status</th>
                                    <th style="padding: 12px 16px; text-align: right;">Portal Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($partners as $partner)
                                <tr>
                                    <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">
                                        {{ $partner->name }}
                                    </td>
                                    <td style="padding: 12px 16px; color: #334155;">
                                        ₹{{ number_format($partner->min_loan_amount) }} – ₹{{ number_format($partner->max_loan_amount) }}
                                    </td>
                                    <td style="padding: 12px 16px; font-weight: 600; color: #059669;">
                                        {{ $partner->interest_rate_display }}
                                    </td>
                                    <td style="padding: 12px 16px; color: #64748b;">
                                        {{ $partner->tenure_display }}
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <span class="badge" style="background: {{ $partner->status === 'active' ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $partner->status === 'active' ? '#065f46' : '#94a3b8' }}; font-weight: 700;">
                                            {{ strtoupper($partner->status) }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 16px; text-align: right;">
                                        <a href="{{ $partner->application_url }}" target="_blank" class="btn btn-sm" style="background: #f1f5f9; color: #0f172a; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 10px; border: 1px solid #cbd5e1;">
                                            Open URL ↗
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No banking partners configured.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add / Edit Partner Form -->
            <div class="col-lg-4">
                <div class="card border-0 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                    <h5 class="mb-3" style="font-size: 16px; font-weight: 700; color: #0f172a;">Add Partner Institution</h5>
                    
                    <form method="POST" action="{{ route('admin.finance.lenders.save') }}">
                        @csrf
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Bank / Institution Name</label>
                            <input type="text" name="name" required placeholder="e.g. HDFC Bank, Bajaj Finserv" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label style="font-size: 12px; font-weight: 600; color: #475569;">Min Loan (₹)</label>
                                <input type="number" name="min_loan_amount" value="50000" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                            </div>
                            <div class="col-6">
                                <label style="font-size: 12px; font-weight: 600; color: #475569;">Max Loan (₹)</label>
                                <input type="number" name="max_loan_amount" value="5000000" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Interest Rate Display</label>
                            <input type="text" name="interest_rate_display" value="10.5% - 14% p.a." class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Tenure Display</label>
                            <input type="text" name="tenure_display" value="12 - 60 Months" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Application / Portal URL</label>
                            <input type="url" name="application_url" required placeholder="https://partner-portal.com/apply" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label style="font-size: 12px; font-weight: 600; color: #475569;">Status</label>
                                <select name="status" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label style="font-size: 12px; font-weight: 600; color: #475569;">Sort Order</label>
                                <input type="number" name="sort_order" value="0" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm w-100" style="background: #0f172a; color: #ffffff; font-size: 13px; font-weight: 600; border-radius: 6px; padding: 10px;">
                            Save Partner Institution
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        CUSTOMER 360 MASTER
                    </span>
                    <span style="color: #64748b; font-size: 13px; font-weight: 600;">Unified Borrower Database</span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Borrowers &amp; KYC Vault Profiles
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; border: 1px solid #cbd5e1;">
                    Back to Overview
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 mb-4 p-3" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <form method="GET" action="{{ route('admin.finance.customers') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search by borrower name, phone, or PAN..." style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>
                <div class="col-md-3">
                    <select name="user_type" class="form-control form-control-sm" style="border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <option value="">All Account Types</option>
                        <option value="driver" {{ request('user_type') === 'driver' ? 'selected' : '' }}>Drivers (tj_conducteur)</option>
                        <option value="customer" {{ request('user_type') === 'customer' ? 'selected' : '' }}>Customers (tj_user_app)</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm" style="background: #0f172a; color: #ffffff; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 6px 16px;">
                        Filter
                    </button>
                    <a href="{{ route('admin.finance.customers') }}" class="btn btn-sm" style="background: #f1f5f9; color: #475569; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 6px 16px;">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Customers Table -->
        <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                            <th style="padding: 12px 16px;">Borrower</th>
                            <th style="padding: 12px 16px;">Type</th>
                            <th style="padding: 12px 16px;">PAN / Aadhaar Ref</th>
                            <th style="padding: 12px 16px;">KYC Status</th>
                            <th style="padding: 12px 16px;">CIBIL Bracket</th>
                            <th style="padding: 12px 16px;">Virtual Limit</th>
                            <th style="padding: 12px 16px;">Registered</th>
                            <th style="padding: 12px 16px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $c)
                        <tr>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $c->name }}</div>
                                <div style="font-size: 12px; color: #64748b;">{{ $c->phone }}</div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: {{ $c->user_type === 'driver' ? '#e0f2fe' : '#f1f5f9' }}; color: {{ $c->user_type === 'driver' ? '#0369a1' : '#334155' }}; font-weight: 600; font-size: 11px;">
                                    {{ ucfirst($c->user_type) }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; color: #334155; font-family: monospace;">
                                {{ $c->pan ?: 'Not linked' }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: {{ $c->kyc_status === 'VERIFIED' ? '#ecfdf5' : '#fef3c7' }}; color: {{ $c->kyc_status === 'VERIFIED' ? '#065f46' : '#92400e' }}; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 4px;">
                                    {{ $c->kyc_status }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; color: #334155; font-weight: 600;">
                                {{ $c->cibil_score ? $c->cibil_score . ' (' . ucfirst($c->cibil_bracket) . ')' : 'Unrated' }}
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                                ₹{{ number_format($c->virtual_credit_limit) }}
                            </td>
                            <td style="padding: 12px 16px; color: #64748b; font-size: 12px;">
                                {{ $c->created_at->format('d M Y') }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <a href="{{ route('admin.finance.customer-details', $c->id) }}" class="btn btn-sm" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 5px 12px;">
                                    Customer 360°
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No borrowers matching your search criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
            <div class="p-3" style="border-top: 1px solid #e2e8f0;">
                {{ $customers->withQueryString()->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px;">

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e2e8f0;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 4px;">
                        DAILY RECOVERY OPERATIONS
                    </span>
                    <span style="color: #64748b; font-size: 13px; font-weight: 600;">Daily Usage Lock Engine</span>
                </div>
                <h3 class="font-weight-bold mb-0" style="font-size: 24px; color: #0f172a; letter-spacing: -0.3px;">
                    Daily Recovery &amp; Overdue Control
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

        <!-- Card: Today's Scheduled Collections -->
        <div class="card border-0 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="p-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #e2e8f0;">
                <div>
                    <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #0f172a;">Today's Scheduled Collections ({{ date('d M Y') }})</h5>
                    <div style="font-size: 12px; color: #64748b;">Daily EMI dues scheduled for recovery today</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                            <th style="padding: 12px 16px;">Borrower</th>
                            <th style="padding: 12px 16px;">Application No</th>
                            <th style="padding: 12px 16px;">Day Number</th>
                            <th style="padding: 12px 16px;">Due Amount</th>
                            <th style="padding: 12px 16px;">Collected</th>
                            <th style="padding: 12px 16px;">Status</th>
                            <th style="padding: 12px 16px; text-align: right;">Borrower Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todaySchedules as $sch)
                        <tr>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $sch->customer->name ?? 'Borrower' }}</div>
                                <div style="font-size: 12px; color: #64748b;">{{ $sch->customer->phone ?? '—' }}</div>
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                                {{ $sch->application->application_number ?? 'APP-' . $sch->application_id }}
                            </td>
                            <td style="padding: 12px 16px; color: #334155;">
                                Day {{ $sch->day_number }}
                            </td>
                            <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">
                                ₹{{ number_format($sch->total_due, 2) }}
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #059669;">
                                ₹{{ number_format($sch->paid_amount, 2) }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: {{ $sch->status === 'paid' ? '#ecfdf5' : '#fef3c7' }}; color: {{ $sch->status === 'paid' ? '#065f46' : '#92400e' }}; font-weight: 700; font-size: 11px;">
                                    {{ strtoupper($sch->status) }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                @if($sch->status !== 'paid')
                                <form method="POST" action="{{ route('admin.finance.recovery.mark-paid', $sch->id) }}" style="display: inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success mr-1" onclick="return confirm('Record cash/offline payment of ₹{{ number_format($sch->total_due, 2) }}?');" style="font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 8px;">
                                        ✓ Record Paid
                                    </button>
                                </form>
                                @endif
                                @if($sch->customer)
                                <a href="{{ route('admin.finance.customer-details', $sch->customer->id) }}" class="btn btn-sm" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 10px;">
                                    Inspect &amp; Lock
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No recovery installments scheduled for today.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($todaySchedules->hasPages())
            <div class="p-3" style="border-top: 1px solid #e2e8f0;">
                {{ $todaySchedules->links() }}
            </div>
            @endif
        </div>

        <!-- Card: Overdue Accounts (Locked Borrowers) -->
        <div class="card border-0 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="p-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #e2e8f0;">
                <div>
                    <h5 class="mb-0" style="font-size: 16px; font-weight: 700; color: #dc2626;">Overdue Accounts (Active Usage Locks)</h5>
                    <div style="font-size: 12px; color: #64748b;">Borrowers whose daily credit limit is currently locked due to missed EMIs</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px;">
                            <th style="padding: 12px 16px;">Borrower</th>
                            <th style="padding: 12px 16px;">Application No</th>
                            <th style="padding: 12px 16px;">Schedule Date</th>
                            <th style="padding: 12px 16px;">Overdue Amount</th>
                            <th style="padding: 12px 16px;">Credit Status</th>
                            <th style="padding: 12px 16px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overdueSchedules as $overdue)
                        <tr>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $overdue->customer->name ?? 'Borrower' }}</div>
                                <div style="font-size: 12px; color: #64748b;">{{ $overdue->customer->phone ?? '—' }}</div>
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                                {{ $overdue->application->application_number ?? 'APP-' . $overdue->application_id }}
                            </td>
                            <td style="padding: 12px 16px; color: #dc2626; font-weight: 600;">
                                {{ date('d M Y', strtotime($overdue->schedule_date)) }}
                            </td>
                            <td style="padding: 12px 16px; font-weight: 700; color: #dc2626;">
                                ₹{{ number_format($overdue->total_due - $overdue->paid_amount, 2) }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: #fef2f2; color: #991b1b; font-weight: 700; font-size: 11px;">
                                    🔒 USAGE LOCKED
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <form method="POST" action="{{ route('admin.finance.recovery.mark-paid', $overdue->id) }}" style="display: inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success mr-1" onclick="return confirm('Record recovery payment of ₹{{ number_format($overdue->total_due - $overdue->paid_amount, 2) }}?');" style="font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 8px;">
                                        ✓ Record Paid
                                    </button>
                                </form>
                                @if($overdue->customer)
                                <a href="{{ route('admin.finance.customer-details', $overdue->customer->id) }}" class="btn btn-sm" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 10px;">
                                    Recover &amp; Review
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No overdue loan accounts. Excellent repayment health!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($overdueSchedules->hasPages())
            <div class="p-3" style="border-top: 1px solid #e2e8f0;">
                {{ $overdueSchedules->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

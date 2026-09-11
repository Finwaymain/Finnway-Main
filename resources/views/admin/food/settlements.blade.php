@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">🏦 Restaurant Settlements & Payouts</h3>
            <p class="text-muted mb-0">Review automated daily bank settlement batches, commission withholdings, and payout proofs.</p>
        </div>
    </div>

    <!-- Settlements Table -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Settlement Ref</th>
                        <th>Restaurant</th>
                        <th>Period</th>
                        <th>Gross Food Sales</th>
                        <th>Commission Deducted</th>
                        <th>Net Payout</th>
                        <th>Status</th>
                        <th>Settled At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settlements as $s)
                        <tr>
                            <td>
                                <code>{{ $s->settlement_number ?: ('SETTLE-' . $s->id) }}</code>
                            </td>
                            <td class="font-weight-bold text-dark">
                                {{ optional($s->restaurant)->name ?: ('Restaurant #' . $s->restaurant_id) }}
                            </td>
                            <td class="small text-muted">
                                {{ $s->start_date ? $s->start_date->format('d M') : 'N/A' }} - {{ $s->end_date ? $s->end_date->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="font-weight-bold">₹{{ number_format($s->gross_amount, 2) }}</td>
                            <td class="text-danger">-₹{{ number_format($s->commission_deducted, 2) }}</td>
                            <td class="font-weight-bold text-success">₹{{ number_format($s->net_amount, 2) }}</td>
                            <td>
                                @if($s->status === 'paid' || $s->status === 'completed')
                                    <span class="badge badge-success px-2 py-1">Paid / Settled</span>
                                @elseif($s->status === 'pending')
                                    <span class="badge badge-warning px-2 py-1">Pending Processing</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1">{{ ucfirst($s->status) }}</span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ $s->paid_at ? $s->paid_at->format('d M Y, h:i A') : 'Pending' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa fa-university fa-3x mb-3 text-light"></i>
                                <p class="mb-0">No restaurant payouts processed yet. Settlements run automatically each night.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($settlements->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="text-muted small">
                    Showing {{ $settlements->firstItem() }} to {{ $settlements->lastItem() }} of {{ $settlements->total() }} settlements
                </div>
                <div>
                    {{ $settlements->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

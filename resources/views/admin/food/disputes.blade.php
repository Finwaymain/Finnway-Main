@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">⚖️ Disputes & Support Tickets</h3>
            <p class="text-muted mb-0">Handle customer meal complaints, missing items, spilled food, and rider transit issues.</p>
        </div>
    </div>

    <!-- Disputes Table -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Ticket</th>
                        <th>Order #</th>
                        <th>Restaurant</th>
                        <th>Issue Type</th>
                        <th>Status</th>
                        <th>Refund (₹)</th>
                        <th class="text-right" style="min-width: 160px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disputes as $d)
                        <tr>
                            <td><code>{{ $d->ticket_number ?: ('TKT-' . $d->id) }}</code></td>
                            <td>
                                @if($d->order)
                                    <span class="font-weight-bold text-dark">#{{ $d->order->order_number }}</span>
                                @else
                                    <span class="text-muted">#{{ $d->order_id }}</span>
                                @endif
                            </td>
                            <td>
                                {{ optional($d->restaurant)->name ?: ('Restaurant #' . $d->restaurant_id) }}
                            </td>
                            <td>
                                <span class="badge badge-light border">{{ ucwords(str_replace('_', ' ', $d->issue_type ?: 'Order Problem')) }}</span>
                            </td>
                            <td>
                                @if($d->status === 'resolved')
                                    <span class="badge badge-success">Resolved</span>
                                @elseif($d->status === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @else
                                    <span class="badge badge-warning">Needs Review</span>
                                @endif
                            </td>
                            <td class="font-weight-bold text-primary">
                                {{ $d->refund_amount ? '₹' . number_format($d->refund_amount, 2) : '—' }}
                            </td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#resolveModal{{ $d->id }}">
                                    <i class="fa fa-gavel mr-1"></i> Resolve
                                </button>

                                <!-- Resolve Modal -->
                                <div class="modal fade text-left" id="resolveModal{{ $d->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content border-0 shadow">
                                            <form method="post" action="{{ route('admin.food.disputes.resolve', $d->id) }}">
                                                @csrf
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title font-weight-bold">Resolve Ticket #{{ $d->ticket_number }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold text-muted">Action Status</label>
                                                        <select name="status" class="form-control custom-select">
                                                            <option value="resolved" @selected($d->status === 'resolved')>Mark Resolved</option>
                                                            <option value="under_review" @selected($d->status === 'under_review')>Under Investigation</option>
                                                            <option value="rejected" @selected($d->status === 'rejected')>Reject Claim</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold text-muted">Customer Refund Amount (₹)</label>
                                                        <input type="number" step="1" name="refund_amount" value="{{ $d->refund_amount }}" class="form-control" placeholder="0 if no refund">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold text-muted">Resolution Notes</label>
                                                        <textarea name="resolution" class="form-control" rows="3" placeholder="Explain how the dispute was settled...">{{ $d->resolution }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Submit Resolution</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa fa-shield-alt fa-3x mb-3 text-light"></i>
                                <p class="mb-0">No active customer disputes or quality issues.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($disputes->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="text-muted small">
                    Showing {{ $disputes->firstItem() }} to {{ $disputes->lastItem() }} of {{ $disputes->total() }} disputes
                </div>
                <div>
                    {{ $disputes->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

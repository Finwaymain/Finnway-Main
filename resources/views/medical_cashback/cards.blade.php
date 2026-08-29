@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor font-weight-bold">Purchased Medical Cards</h3>
            </div>
            <div class="col-md-7 align-self-center text-right">
                <a href="{{ route('admin.medical.index') }}" class="btn btn-outline-secondary font-weight-bold">
                    <i class="mdi mdi-arrow-left mr-1"></i> Back to Claims Queue
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>User Info</th>
                                <th>Card Type</th>
                                <th>Aadhaar Number</th>
                                <th>Limits & Usage</th>
                                <th>Claims</th>
                                <th>Status</th>
                                <th>Purchased Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cards as $card)
                                <tr>
                                    <td>#{{ $card->id }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $card->user_name }}</div>
                                        <small class="text-muted"><i class="fa fa-phone"></i> {{ $card->user_phone }} ({{ strtoupper($card->user_type) }})</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-pill badge-primary px-3 py-1 font-weight-bold">{{ $card->card_type }}</span>
                                    </td>
                                    <td>
                                        <span class="font-mono text-dark font-weight-bold">{{ $card->aadhaar_number ? substr($card->aadhaar_number, 0, 4) . ' **** ' . substr($card->aadhaar_number, -4) : 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">Limit: ₹{{ number_format($card->claim_limit, 2) }}</div>
                                        <div class="text-success font-weight-bold">Remaining: ₹{{ number_format($card->remaining_amount, 2) }}</div>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold">{{ $card->claims_count }} / {{ $card->max_claims }}</span>
                                    </td>
                                    <td>
                                        @if($card->status == 'active')
                                            <span class="badge badge-success px-3 py-1">Active</span>
                                        @elseif($card->status == 'exhausted')
                                            <span class="badge badge-secondary px-3 py-1">Exhausted</span>
                                        @else
                                            <span class="badge badge-danger px-3 py-1">Expired</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-dark">{{ date('d M Y', strtotime($card->creer)) }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <p class="font-weight-bold mb-0">No active purchased cards found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($cards->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $cards->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

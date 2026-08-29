@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Header & Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor font-weight-bold">Medical Cashback Claims</h3>
            </div>
            <div class="col-md-7 align-self-center text-right">
                <a href="{{ route('admin.medical.plans.index') }}" class="btn btn-outline-success font-weight-bold mr-2">
                    <i class="fa fa-cogs mr-1"></i> Manage Card Plans ({{ \Illuminate\Support\Facades\Schema::hasTable('tj_medical_card_plans') ? \DB::table('tj_medical_card_plans')->count() : 0 }})
                </a>
                <a href="{{ route('admin.medical.cards') }}" class="btn btn-outline-primary font-weight-bold">
                    <i class="mdi mdi-credit-card mr-1"></i> Active User Cards ({{ \Illuminate\Support\Facades\Schema::hasTable('tj_medical_cards') ? \DB::table('tj_medical_cards')->count() : 0 }})
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><i class="fa fa-check-circle"></i> Success!</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fa fa-exclamation-triangle"></i> Error!</strong> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Summary Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-2 font-weight-bold">
                <div class="card shadow-sm border-left border-primary py-2">
                    <div class="card-body p-2 text-center">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Claims</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 font-weight-bold">
                <div class="card shadow-sm border-left border-warning py-2">
                    <div class="card-body p-2 text-center">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Review</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 font-weight-bold">
                <div class="card shadow-sm border-left border-success py-2">
                    <div class="card-body p-2 text-center">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved & Credited</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['approved'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 font-weight-bold">
                <div class="card shadow-sm border-left border-danger py-2">
                    <div class="card-body p-2 text-center">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected Claims</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['rejected'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 font-weight-bold">
                <div class="card shadow-sm border-left border-info py-2">
                    <div class="card-body p-2 text-center">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Approved Payouts</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">₹{{ number_format($stats['total_approved_amount'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Nav Tabs -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0">
                <ul class="nav nav-pills card-header-pills">
                    <li class="nav-item">
                        <a class="nav-link {{ $status == 'all' ? 'active font-weight-bold bg-primary' : 'text-dark' }}" href="{{ route('admin.medical.index', ['status' => 'all']) }}">All Claims ({{ $stats['total'] }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status == 'under_review' || $status == 'pending' ? 'active font-weight-bold bg-warning text-white' : 'text-dark' }}" href="{{ route('admin.medical.index', ['status' => 'under_review']) }}">Pending Review ({{ $stats['pending'] }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status == 'approved' ? 'active font-weight-bold bg-success' : 'text-dark' }}" href="{{ route('admin.medical.index', ['status' => 'approved']) }}">Approved ({{ $stats['approved'] }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status == 'need_reupload' ? 'active font-weight-bold bg-info text-white' : 'text-dark' }}" href="{{ route('admin.medical.index', ['status' => 'need_reupload']) }}">Need Reupload ({{ $stats['reupload'] }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status == 'rejected' ? 'active font-weight-bold bg-danger' : 'text-dark' }}" href="{{ route('admin.medical.index', ['status' => 'rejected']) }}">Rejected ({{ $stats['rejected'] }})</a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Claim ID</th>
                                <th>User Info</th>
                                <th>Card Type</th>
                                <th>Claim Amount</th>
                                <th>Status</th>
                                <th>Uploaded Documents</th>
                                <th>Submitted Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($claims as $claim)
                                <tr>
                                    <td>
                                        <span class="font-weight-bold text-dark">{{ $claim->claim_id }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $claim->user_name }}</div>
                                        <small class="text-muted"><i class="fa fa-phone"></i> {{ $claim->user_phone }} ({{ strtoupper($claim->user_type) }})</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-pill badge-primary px-3 py-1 font-weight-bold">{{ $claim->card_type ?? 'Medical Card' }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-success" style="font-size: 15px;">₹{{ number_format($claim->requested_amount, 2) }}</div>
                                        @if($claim->approved_amount > 0)
                                            <small class="text-primary">Approved: ₹{{ number_format($claim->approved_amount, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($claim->status == 'approved')
                                            <span class="badge badge-success px-3 py-1">Approved & Credited</span>
                                        @elseif($claim->status == 'need_reupload')
                                            <span class="badge badge-info px-3 py-1">Need Reupload</span>
                                        @elseif($claim->status == 'rejected')
                                            <span class="badge badge-danger px-3 py-1">Rejected</span>
                                        @else
                                            <span class="badge badge-warning px-3 py-1 text-white">Under Review</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info font-weight-bold text-white shadow-sm" data-toggle="modal" data-target="#docsModal{{ $claim->id }}">
                                            <i class="fa fa-file-image-o mr-1"></i> View Docs
                                        </button>
                                    </td>
                                    <td>
                                        <small class="text-dark">{{ date('d M Y, h:i A', strtotime($claim->creer)) }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            @if($claim->status != 'approved')
                                                <button type="button" class="btn btn-sm btn-success font-weight-bold" data-toggle="modal" data-target="#approveModal{{ $claim->id }}">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning font-weight-bold text-white" data-toggle="modal" data-target="#reuploadModal{{ $claim->id }}">
                                                    <i class="fa fa-refresh"></i> Reupload
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger font-weight-bold" data-toggle="modal" data-target="#rejectModal{{ $claim->id }}">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                            @else
                                                <span class="text-success font-weight-bold"><i class="fa fa-check-circle"></i> Wallet Credited</span>
                                            @endif
                                        </div>

                                        <!-- View Documents Modal -->
                                        <div class="modal fade" id="docsModal{{ $claim->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg">
                                                    <div class="modal-header bg-info text-white">
                                                        <h5 class="modal-title font-weight-bold text-white">
                                                            <i class="fa fa-folder-open mr-1"></i> Claim Documents — Claim #{{ $claim->claim_id }}
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                    </div>
                                                    <div class="modal-body bg-light">
                                                        <div class="row">
                                                            <!-- Prescription Doc -->
                                                            <div class="col-md-4 mb-3">
                                                                <div class="card h-100 shadow-sm border-0">
                                                                    <div class="card-header bg-white font-weight-bold text-primary">
                                                                        <i class="fa fa-file-text-o mr-1"></i> Prescription
                                                                    </div>
                                                                    <div class="card-body text-center p-2">
                                                                        @if(!empty($claim->prescription_doc) && str_contains($claim->prescription_doc, 'uploads/'))
                                                                            @if(preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $claim->prescription_doc))
                                                                                <a href="{{ asset($claim->prescription_doc) }}" target="_blank">
                                                                                    <img src="{{ asset($claim->prescription_doc) }}" class="img-fluid rounded border mb-2" style="max-height: 200px; object-fit: contain;">
                                                                                </a>
                                                                            @else
                                                                                <div class="py-4">
                                                                                    <i class="fa fa-file-pdf-o text-danger" style="font-size: 45px;"></i>
                                                                                    <p class="small text-muted mt-2">PDF Document</p>
                                                                                </div>
                                                                            @endif
                                                                            <a href="{{ asset($claim->prescription_doc) }}" target="_blank" class="btn btn-sm btn-outline-primary btn-block font-weight-bold">
                                                                                <i class="fa fa-external-link"></i> Open Full Document
                                                                            </a>
                                                                        @else
                                                                            <div class="py-4 text-muted">
                                                                                <i class="fa fa-exclamation-circle text-warning" style="font-size: 30px;"></i>
                                                                                <p class="small font-weight-bold mt-2">No File / Default</p>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Diagnostic Doc -->
                                                            <div class="col-md-4 mb-3">
                                                                <div class="card h-100 shadow-sm border-0">
                                                                    <div class="card-header bg-white font-weight-bold text-info">
                                                                        <i class="fa fa-flask mr-1"></i> Diagnostic Report
                                                                    </div>
                                                                    <div class="card-body text-center p-2">
                                                                        @if(!empty($claim->diagnostic_doc) && str_contains($claim->diagnostic_doc, 'uploads/'))
                                                                            @if(preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $claim->diagnostic_doc))
                                                                                <a href="{{ asset($claim->diagnostic_doc) }}" target="_blank">
                                                                                    <img src="{{ asset($claim->diagnostic_doc) }}" class="img-fluid rounded border mb-2" style="max-height: 200px; object-fit: contain;">
                                                                                </a>
                                                                            @else
                                                                                <div class="py-4">
                                                                                    <i class="fa fa-file-pdf-o text-danger" style="font-size: 45px;"></i>
                                                                                    <p class="small text-muted mt-2">PDF Document</p>
                                                                                </div>
                                                                            @endif
                                                                            <a href="{{ asset($claim->diagnostic_doc) }}" target="_blank" class="btn btn-sm btn-outline-info btn-block font-weight-bold">
                                                                                <i class="fa fa-external-link"></i> Open Full Document
                                                                            </a>
                                                                        @else
                                                                            <div class="py-4 text-muted">
                                                                                <i class="fa fa-minus-circle text-secondary" style="font-size: 30px;"></i>
                                                                                <p class="small font-weight-bold mt-2">Not Uploaded</p>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Cash Memo Doc -->
                                                            <div class="col-md-4 mb-3">
                                                                <div class="card h-100 shadow-sm border-0">
                                                                    <div class="card-header bg-white font-weight-bold text-success">
                                                                        <i class="fa fa-file-image-o mr-1"></i> Cash Memo / Bill
                                                                    </div>
                                                                    <div class="card-body text-center p-2">
                                                                        @if(!empty($claim->cash_memo_doc) && str_contains($claim->cash_memo_doc, 'uploads/'))
                                                                            @if(preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $claim->cash_memo_doc))
                                                                                <a href="{{ asset($claim->cash_memo_doc) }}" target="_blank">
                                                                                    <img src="{{ asset($claim->cash_memo_doc) }}" class="img-fluid rounded border mb-2" style="max-height: 200px; object-fit: contain;">
                                                                                </a>
                                                                            @else
                                                                                <div class="py-4">
                                                                                    <i class="fa fa-file-pdf-o text-danger" style="font-size: 45px;"></i>
                                                                                    <p class="small text-muted mt-2">PDF Document</p>
                                                                                </div>
                                                                            @endif
                                                                            <a href="{{ asset($claim->cash_memo_doc) }}" target="_blank" class="btn btn-sm btn-outline-success btn-block font-weight-bold">
                                                                                <i class="fa fa-external-link"></i> Open Full Document
                                                                            </a>
                                                                        @else
                                                                            <div class="py-4 text-muted">
                                                                                <i class="fa fa-exclamation-circle text-warning" style="font-size: 30px;"></i>
                                                                                <p class="small font-weight-bold mt-2">No File / Default</p>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    </div>
                                                    <div class="modal-footer bg-white flex justify-content-between">
                                                        <div>
                                                            @if($claim->status != 'approved')
                                                                <button type="button" class="btn btn-sm btn-success font-weight-bold mr-1" data-dismiss="modal" data-toggle="modal" data-target="#approveModal{{ $claim->id }}">
                                                                    <i class="fa fa-check"></i> Approve Claim
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-warning font-weight-bold text-white mr-1" data-dismiss="modal" data-toggle="modal" data-target="#reuploadModal{{ $claim->id }}">
                                                                    <i class="fa fa-refresh"></i> Request Reupload
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-danger font-weight-bold" data-dismiss="modal" data-toggle="modal" data-target="#rejectModal{{ $claim->id }}">
                                                                    <i class="fa fa-times"></i> Reject Claim
                                                                </button>
                                                            @endif
                                                        </div>
                                                        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal{{ $claim->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.medical.approve', $claim->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title font-weight-bold text-white"><i class="fa fa-check-circle mr-1"></i> Approve Medical Claim</h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                        </div>
                                                        <div class="modal-body text-left">
                                                            <p class="mb-2">Claim ID: <strong>{{ $claim->claim_id }}</strong></p>
                                                            <p class="mb-2">User: <strong>{{ $claim->user_name }} ({{ $claim->user_phone }})</strong></p>
                                                            <p class="mb-3">Requested Amount: <strong class="text-success">₹{{ number_format($claim->requested_amount, 2) }}</strong></p>
                                                            
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">Approved Cashback Amount (₹)</label>
                                                                <input type="number" step="0.01" name="approved_amount" class="form-control font-weight-bold text-success" value="{{ $claim->requested_amount }}" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">Approval Remarks</label>
                                                                <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Verified prescription & medical store cash memo."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success font-weight-bold">Confirm & Credit Wallet</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reupload Modal -->
                                        <div class="modal fade" id="reuploadModal{{ $claim->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.medical.reupload', $claim->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header bg-warning text-white">
                                                            <h5 class="modal-title font-weight-bold text-white"><i class="fa fa-refresh mr-1"></i> Request Document Reupload</h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                        </div>
                                                        <div class="modal-body text-left">
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">Reason for Reupload Request</label>
                                                                <select name="reason" class="form-control mb-2" required>
                                                                    <option value="Prescription photo is blurry/unreadable">Prescription photo is blurry / unreadable</option>
                                                                    <option value="Medical bill or Cash memo missing">Medical bill / Cash memo missing</option>
                                                                    <option value="Diagnostic report missing">Diagnostic report missing</option>
                                                                    <option value="Bill date expired or outside validity">Bill date expired / outside card validity</option>
                                                                    <option value="Other verification requirement">Other verification requirement</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-warning font-weight-bold text-white">Send Reupload Request</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $claim->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg">
                                                    <form action="{{ route('admin.medical.reject', $claim->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title font-weight-bold text-white"><i class="fa fa-times-circle mr-1"></i> Reject Claim #{{ $claim->claim_id }}</h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                        </div>
                                                        <div class="modal-body text-left">
                                                            <p class="mb-1">User: <strong>{{ $claim->user_name }} ({{ $claim->user_phone }})</strong></p>
                                                            <p class="mb-3">Claim Amount: <strong class="text-danger">₹{{ number_format($claim->requested_amount, 2) }}</strong></p>
                                                            <div class="form-group mb-2">
                                                                <label class="font-weight-bold">Select Preset Rejection Reason</label>
                                                                <select class="form-control mb-2 font-weight-bold" onchange="document.getElementById('reject_reason_text_{{ $claim->id }}').value = this.value;">
                                                                    <option value="Invalid or unreadable medical bills/documents">Invalid or unreadable medical bills / documents</option>
                                                                    <option value="Cash memo / bill amount does not match requested amount">Cash memo / bill amount does not match requested amount</option>
                                                                    <option value="Prescription date expired or outside card validity">Prescription date expired or outside card validity</option>
                                                                    <option value="Duplicate medical claim submission">Duplicate medical claim submission</option>
                                                                    <option value="Non-eligible medical expense under this card plan">Non-eligible medical expense under this card plan</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">Custom Rejection Remarks</label>
                                                                <textarea id="reject_reason_text_{{ $claim->id }}" name="reason" class="form-control" rows="2" placeholder="Enter reason for rejection..." required>Invalid or unreadable medical bills/documents</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger font-weight-bold"><i class="fa fa-times-circle"></i> Confirm Rejection</button>
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
                                        <i class="mdi mdi-inbox text-slate-300" style="font-size: 40px;"></i>
                                        <p class="font-weight-bold mt-2">No medical claims found in this queue.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($claims->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $claims->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

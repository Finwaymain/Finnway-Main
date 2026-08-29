@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles mb-3">
        <div class="col-md-6 align-self-center">
            <h3 class="text-themecolor mb-0 font-weight-bold"><i class="mdi mdi-help-circle-outline text-primary mr-2"></i> Support Quick Questions</h3>
            <small class="text-muted">Manage preset questions and automated instant replies for mobile app support</small>
        </div>
        <div class="col-md-6 align-self-center text-right">
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm mr-2" data-toggle="modal" data-target="#addQuestionModal">
                <i class="mdi mdi-plus-circle mr-1"></i> Add New Question
            </button>
            <a href="{{ route('support.chat.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="mdi mdi-forum mr-1"></i> Live Chat Dashboard
            </a>
        </div>
    </div>

    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle mr-2"></i> {{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Card Container with Customer and Business Tabs -->
        <div class="card shadow-sm border-0" style="border-radius: 16px;">
            <div class="card-header bg-white p-3 border-bottom">
                <ul class="nav nav-pills" id="questionTabs">
                    <li class="nav-item mr-2">
                        <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'customer' ? 'active' : '' }}" href="{{ route('support.questions.index', ['tab' => 'customer']) }}">
                            <i class="mdi mdi-account-circle mr-1"></i> Customer App Questions ({{ $customerQuestions->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'business' ? 'active' : '' }}" href="{{ route('support.questions.index', ['tab' => 'business']) }}">
                            <i class="mdi mdi-car mr-1"></i> Driver / Partner Questions ({{ $businessQuestions->count() }})
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                @php
                    $activeQuestions = $tab === 'business' ? $businessQuestions : $customerQuestions;
                @endphp

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th style="width: 70px;">Sort</th>
                                <th style="width: 140px;">Category</th>
                                <th>Question Prompt</th>
                                <th>Automated Instant Reply</th>
                                <th style="width: 110px;">Status</th>
                                <th style="width: 130px;" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activeQuestions as $q)
                                <tr>
                                    <td>
                                        <span class="badge badge-light border font-weight-bold">{{ $q->sort_order }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary px-2 py-1">{{ $q->category ?? 'General' }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $q->question }}</strong>
                                    </td>
                                    <td>
                                        @if (!empty($q->auto_reply))
                                            <span class="text-muted small"><i class="mdi mdi-robot mr-1 text-info"></i> {{ Str::limit($q->auto_reply, 75) }}</span>
                                        @else
                                            <span class="text-muted font-italic small">No auto-reply (routes directly to admin)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-xs {{ $q->status === 'active' ? 'btn-success' : 'btn-secondary' }} rounded-pill px-3" onclick="toggleStatus({{ $q->id }}, this)">
                                            {{ ucfirst($q->status) }}
                                        </button>
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-circle mr-1" data-toggle="modal" data-target="#editQuestionModal{{ $q->id }}" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <a href="{{ route('support.questions.delete', $q->id) }}" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Are you sure you want to delete this question?');" title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editQuestionModal{{ $q->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content" style="border-radius: 16px;">
                                            <form action="{{ route('support.questions.update', $q->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title font-weight-bold"><i class="mdi mdi-pencil text-primary mr-1"></i> Edit Quick Question</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-600">Target App <span class="text-danger">*</span></label>
                                                        <select name="user_type" class="form-control" required>
                                                            <option value="customer" {{ $q->user_type === 'customer' ? 'selected' : '' }}>Customer App</option>
                                                            <option value="business" {{ $q->user_type === 'business' ? 'selected' : '' }}>Driver / Partner App</option>
                                                            <option value="all" {{ $q->user_type === 'all' ? 'selected' : '' }}>Both Apps</option>
                                                        </select>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-600">Category / Topic</label>
                                                                <input type="text" name="category" class="form-control" value="{{ $q->category }}" placeholder="e.g. Ride & Taxi, Payouts, Wallet">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-600">Sort Order</label>
                                                                <input type="number" name="sort_order" class="form-control" value="{{ $q->sort_order }}" min="0">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-600">Question Text <span class="text-danger">*</span></label>
                                                        <textarea name="question" class="form-control" rows="2" required placeholder="Enter the preset prompt text">{{ $q->question }}</textarea>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-600">Automated Instant Reply <small class="text-muted">(Optional)</small></label>
                                                        <textarea name="auto_reply" class="form-control" rows="3" placeholder="If provided, the system sends this instant response immediately upon selection">{{ $q->auto_reply }}</textarea>
                                                    </div>

                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-600">Status</label>
                                                        <select name="status" class="form-control">
                                                            <option value="active" {{ $q->status === 'active' ? 'selected' : '' }}>Active (Visible in App)</option>
                                                            <option value="inactive" {{ $q->status === 'inactive' ? 'selected' : '' }}>Inactive (Hidden)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-help-circle-outline" style="font-size: 36px;"></i>
                                        <p class="mt-2 mb-0">No quick questions added for this app yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px;">
            <form action="{{ route('support.questions.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title font-weight-bold"><i class="mdi mdi-plus-circle text-primary mr-1"></i> Add New Quick Question</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-600">Target App <span class="text-danger">*</span></label>
                        <select name="user_type" class="form-control" required>
                            <option value="customer" {{ $tab === 'customer' ? 'selected' : '' }}>Customer App</option>
                            <option value="business" {{ $tab === 'business' ? 'selected' : '' }}>Driver / Partner App</option>
                            <option value="all">Both Apps</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label class="font-weight-600">Category / Topic</label>
                                <input type="text" name="category" class="form-control" placeholder="e.g. Ride & Taxi, Payouts, Wallet">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-600">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" value="1" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-600">Question Text <span class="text-danger">*</span></label>
                        <textarea name="question" class="form-control" rows="2" required placeholder="Enter the preset prompt text displayed to users"></textarea>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-600">Automated Instant Reply <small class="text-muted">(Optional)</small></label>
                        <textarea name="auto_reply" class="form-control" rows="3" placeholder="Optional instant reply automatically sent when user taps this question"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Add Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleStatus(id, btn) {
    fetch(`/support-questions/toggle-status/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.status === 'active') {
                btn.className = 'btn btn-xs btn-success rounded-pill px-3';
                btn.textContent = 'Active';
            } else {
                btn.className = 'btn btn-xs btn-secondary rounded-pill px-3';
                btn.textContent = 'Inactive';
            }
        }
    });
}
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Manage Home Services</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">Manage Home Services</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Add Sub-Service Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="card-title text-primary"><i class="mdi mdi-plus-circle-outline mr-1"></i> Add Home Service / Category</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('home_services.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Parent Category</label>
                                        <select name="parent_id" class="form-control" required>
                                            <option value="">-- Select Category --</option>
                                            <option value="{{ $parent->id }}" class="font-weight-bold text-primary">📁 Create New Main Category (e.g. Electrician)</option>
                                            <optgroup label="Add Sub-Service To Existing Main Category:">
                                                @foreach($services as $category)
                                                    <option value="{{ $category->id }}">↳ {{ $category->libelle }}</option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Service Name</label>
                                        <input type="text" name="libelle" class="form-control" placeholder="e.g. Fan Installation & Repair" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Icon Image (Optional)</label>
                                        <input type="file" name="image" class="form-control-file" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fa fa-plus"></i> Add Service</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Existing Subcategories & Sub-services Grid -->
                <div class="row">
                    @foreach($services as $category)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light d-flex align-items-center justify-content-between">
                                    <h5 class="card-title font-weight-bold text-dark mb-0">
                                        {{ $category->libelle }}
                                        <span class="badge badge-info ml-1">{{ count($category->children) }}</span>
                                    </h5>
                                    <div class="d-flex align-items-center" style="gap:4px;">
                                        <form action="{{ route('home_services.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete main category {{ $category->libelle }} and all its sub-services?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete Category"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                                    <ul class="list-group list-group-flush">
                                        @forelse($category->children as $sub)
                                            <li class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                                                <div class="d-flex align-items-center">
                                                    @if($sub->image)
                                                        <img src="{{ $sub->image }}" alt="icon" style="width: 24px; height: 24px; border-radius: 4px; margin-right: 8px; object-fit: cover;">
                                                    @else
                                                        <i class="mdi mdi-check-circle-outline text-success mr-2"></i>
                                                    @endif
                                                    <span style="font-size: 13px; font-weight: 500; color: #334155;">{{ $sub->libelle }}</span>
                                                </div>
                                                <div class="d-flex align-items-center" style="gap: 4px;">
                                                    <form action="{{ route('home_services.toggleStatus', $sub->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs {{ $sub->statut ? 'btn-outline-success' : 'btn-outline-secondary' }}" title="Toggle Status">
                                                            {{ $sub->statut ? 'Active' : 'Hidden' }}
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('home_services.destroy', $sub->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete sub-service {{ $sub->libelle }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-center text-muted py-3">No sub-services yet.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

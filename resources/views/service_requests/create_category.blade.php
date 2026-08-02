@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Create Home Service Category</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{route('home_services.index')}}">Home Services</a></li>
                <li class="breadcrumb-item active">Create Category</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="card-title text-primary"><i class="mdi mdi-plus-circle-outline mr-1"></i> Add Home Service Category / Sub-Service</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('home_services.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label">Parent Category</label>
                                <select name="parent_id" class="form-control" required>
                                    <option value="">-- Select Parent --</option>
                                    <option value="{{ $parent->id }}" class="font-weight-bold text-primary">📁 Create New Main Category (e.g. Electrician)</option>
                                    <optgroup label="Add Sub-Service To Existing Category:">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">↳ {{ $cat->libelle }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Service / Category Name</label>
                                <input type="text" name="libelle" class="form-control" placeholder="e.g. AC Installation & Repair" required>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label">Category Icon Image (Optional)</label>
                                <input type="file" name="image" class="form-control-file" accept="image/*">
                            </div>

                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Category</button>
                                <a href="{{ route('home_services.index') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

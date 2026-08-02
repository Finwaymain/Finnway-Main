@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Edit Home Service Category</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{route('home_services.index')}}">Home Services</a></li>
                <li class="breadcrumb-item active">Edit Category</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="card-title text-primary"><i class="mdi mdi-pencil-box-outline mr-1"></i> Edit Category / Sub-Service</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('home_services.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label">Parent Category</label>
                                <select name="parent_id" class="form-control" required>
                                    <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }} class="font-weight-bold text-primary">📁 Main Category Level</option>
                                    <optgroup label="Or Move Sub-Service To:">
                                        @foreach($categories as $cat)
                                            @if($cat->id != $category->id)
                                                <option value="{{ $cat->id }}" {{ $category->parent_id == $cat->id ? 'selected' : '' }}>↳ {{ $cat->libelle }}</option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Service / Category Name</label>
                                <input type="text" name="libelle" class="form-control" value="{{ $category->libelle }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Icon Image</label>
                                @if($category->image)
                                    <div class="mb-2">
                                        <img src="{{ $category->image }}" alt="Current Icon" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid #cbd5e1;">
                                    </div>
                                @endif
                                <input type="file" name="image" class="form-control-file" accept="image/*">
                            </div>

                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Category</button>
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

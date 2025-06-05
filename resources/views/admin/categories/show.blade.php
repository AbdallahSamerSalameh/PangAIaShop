@extends('admin.layouts.app')

@section('title', 'Category Details')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Category Details</h1>
    <div>
        <a href="{{ route('admin.categories.edit', $category->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Category
        </a>
        <a href="{{ route('admin.categories.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Categories
        </a>
    </div>
</div>

<!-- Category Details Card -->
<div class="row">
    <div class="col-xl-8 col-lg-7">
        <!-- Category Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Category Information</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="categoryActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="categoryActions">
                        <a class="dropdown-item" href="{{ route('admin.categories.edit', $category->id) }}">
                            <i class="fas fa-edit fa-sm mr-2 text-gray-400"></i> Edit
                        </a>
                        <div class="dropdown-divider"></div>
                        <form id="delete-form-{{ $category->id }}" action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                        </form>                        <button type="button" class="dropdown-item text-danger" onclick="showDeleteModal({{ $category->id }}, '{{ addslashes($category->name) }}', 'category')">
                            <i class="fas fa-trash fa-sm mr-2 text-danger"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($category->image_url)
                    <div class="col-md-12 mb-4">
                        @php
                            $imageUrl = str_starts_with($category->image_url, 'http') 
                                ? $category->image_url 
                                : asset('storage/' . $category->image_url);
                        @endphp
                        <div class="text-center">
                            <img src="{{ $imageUrl }}" alt="{{ $category->name }}" class="img-fluid rounded shadow" style="max-height: 300px;">
                        </div>
                    </div>                    @endif
                    
                    <!-- Basic Information Row -->
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold text-gray-600">Category Name:</label>
                                    <p class="mb-0">{{ $category->name }}</p>
                                </div>
                            </div>
                            
                            <div class="col-md-4 text-center">
                                <div class="form-group">
                                    <label class="font-weight-bold text-gray-600">Status:</label>
                                    <p class="mb-0">
                                        @if($category->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div class="col-md-4 text-center">
                                <div class="form-group">
                                    <label class="font-weight-bold text-gray-600">Parent Category:</label>
                                    <p class="mb-0">
                                        @if($category->parent)
                                            <a href="{{ route('admin.categories.show', $category->parent->id) }}" class="text-primary">
                                                {{ $category->parent->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">Root Category</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($category->category_description)
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="font-weight-bold text-gray-600">Description:</label>
                            <p class="mb-0">{{ $category->category_description }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-gray-600">Created At:</label>
                            <p class="mb-0">{{ $category->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-gray-600">Last Updated:</label>
                            <p class="mb-0">{{ $category->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Side Information -->
    <div class="col-xl-4 col-lg-5">
        <!-- Subcategories Card -->
        @if($category->children->count() > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Subcategories ({{ $category->children->count() }})</h6>
            </div>
            <div class="card-body">
                @foreach($category->children as $child)
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <a href="{{ route('admin.categories.show', $child->id) }}" class="text-primary font-weight-bold">
                            {{ $child->name }}
                        </a>
                        <div class="text-xs text-gray-600">
                            @if($child->is_active)
                                <span class="badge badge-success badge-sm">Active</span>
                            @else
                                <span class="badge badge-danger badge-sm">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="{{ route('admin.categories.show', $child->id) }}">
                                <i class="fas fa-eye fa-sm mr-2 text-gray-400"></i> View
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.categories.edit', $child->id) }}">
                                <i class="fas fa-edit fa-sm mr-2 text-gray-400"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Products Count Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Products in Category</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="h2 mb-0 font-weight-bold text-primary">{{ $category->products->count() }}</div>
                    <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Products</div>
                </div>
                @if($category->products->count() > 0)
                <div class="mt-3">
                    <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-eye fa-sm mr-1"></i> View Products
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="row no-gutters align-items-center mb-3">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Subcategories</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $category->children->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-layer-group fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Active Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $category->products->where('status', 'active')->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

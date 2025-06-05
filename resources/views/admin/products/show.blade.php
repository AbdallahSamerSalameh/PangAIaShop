@extends('admin.layouts.app')

@section('title', 'Product Details')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Product Details</h1>
    <div>
        <a href="{{ route('admin.products.edit', $product->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Product
        </a>
        <a href="{{ route('admin.products.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Products
        </a>
    </div>
</div>

<!-- Product Details Card -->
<div class="row">
    <div class="col-xl-8 col-lg-7">
        <!-- Product Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="productActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="productActions">
                        <a class="dropdown-item" href="{{ route('admin.products.edit', $product->id) }}">
                            <i class="fas fa-edit fa-sm mr-2 text-gray-400"></i> Edit
                        </a>                        <div class="dropdown-divider"></div>
                        <form id="delete-form-{{ $product->id }}" action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                        </form>                        <button type="button" class="dropdown-item text-danger" onclick="showDeleteModal({{ $product->id }}, '{{ addslashes($product->name) }}', 'product')">
                            <i class="fas fa-trash fa-sm mr-2 text-danger"></i> Delete
                        </button>
                    </div>
                </div>
            </div>            <div class="card-body">                <div class="row">
                    <div class="col-md-12 mb-4">
                        @php
                            // Get product image
                            $productImage = null;
                            $categoryFallback = null;
                            
                            // Get primary or first product image
                            if($product->images && $product->images->count() > 0) {
                                $primaryImage = $product->images->where('is_primary', true)->first();
                                $productImage = $primaryImage ? $primaryImage->image_url : $product->images->first()->image_url;
                            }
                            
                            // Get category fallback
                            if($product->directCategories && $product->directCategories->count() > 0 && $product->directCategories->first()->image_url) {
                                $categoryFallback = $product->directCategories->first()->image_url;
                            } elseif($product->categories && $product->categories->count() > 0 && $product->categories->first()->image_url) {
                                $categoryFallback = $product->categories->first()->image_url;
                            }
                        @endphp
                        
                        <div class="product-image-container rounded overflow-hidden bg-light d-flex align-items-center justify-content-center mx-auto" style="max-width: 400px; height: 280px;">
                            @include('admin.components.image-with-fallback', [
                                'src' => $productImage,
                                'alt' => $product->name,
                                'type' => 'product',
                                'fallbacks' => [$categoryFallback],
                                'class' => 'img-fluid',
                                'style' => 'max-width: 100%; max-height: 100%; object-fit: cover; object-position: center;'
                            ])
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="row">                            <div class="col-md-6 mb-3">
                                <div class="card bg-light h-100">
                                    <div class="card-body">
                                        <p class="lead font-weight-bold mb-1">{{ $product->name }}</p>
                                        <p class="mb-2 text-muted">SKU: {{ $product->sku }}</p>
                                        
                                        @if($product->sale_price)
                                            <p class="mb-0">
                                                <span class="text-danger h4">${{ number_format($product->sale_price, 2) }}</span>
                                                <span class="text-muted"><s>${{ number_format($product->price, 2) }}</s></span>
                                            </p>
                                        @else
                                            <p class="mb-0">
                                                <span class="h4">${{ number_format($product->price, 2) }}</span>
                                            </p>
                                        @endif                                        <div class="mt-3">
                                            @if($product->inventory)
                                                @php
                                                    $stockQuantity = (int)$product->inventory->quantity;
                                                @endphp
                                                @if($stockQuantity > 10)
                                                    <span class="badge badge-success">In Stock</span>
                                                @elseif($stockQuantity > 0)
                                                    <span class="badge badge-warning">Low Stock</span>
                                                @else
                                                    <span class="badge badge-danger">Out of Stock</span>
                                                @endif
                                            @else
                                                <span class="badge badge-danger">Out of Stock</span>
                                            @endif
                                            
                                            @if($product->is_featured)
                                                <span class="badge badge-primary ml-1">Featured</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="font-weight-bold">Inventory</h6>                                        <p class="mb-1">
                                            <strong>Quantity:</strong> 
                                            @if($product->inventory)
                                                @php
                                                    $quantity = (int)$product->inventory->quantity;
                                                @endphp
                                                @if($quantity > 10)
                                                    <span class="text-success">{{ $quantity }}</span>
                                                @elseif($quantity > 0)
                                                    <span class="text-warning">{{ $quantity }} (Low Stock)</span>
                                                @else
                                                    <span class="text-danger">0 (Out of Stock)</span>
                                                @endif
                                            @else
                                                <span class="text-danger">Not Available</span>
                                            @endif
                                        </p>
                                        <p class="mb-1"><strong>Brand:</strong> {{ $product->brand ?: 'N/A' }}</p>
                                        <p class="mb-0"><strong>Created:</strong> {{ $product->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mt-4">
                        <h6 class="font-weight-bold">Description</h6>
                        <div class="p-3 bg-white rounded">
                            {{ $product->description }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <!-- Categories Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Categories</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    @if($product->categories->count() > 0)
                        @foreach($product->categories as $category)
                            <span class="badge badge-info p-2 mb-1">{{ $category->name }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">No categories assigned</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Gallery Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Product Gallery</h6>
            </div>
            <div class="card-body">                <div class="row">
                    @if($product->images->count() > 0)
                        @foreach($product->images as $image)
                            <div class="col-md-6 mb-3">
                                @php
                                    // Get category fallback for gallery images
                                    $galleryCategoryFallback = null;
                                    if($product->directCategories && $product->directCategories->count() > 0 && $product->directCategories->first()->image_url) {
                                        $galleryCategoryFallback = $product->directCategories->first()->image_url;
                                    } elseif($product->categories && $product->categories->count() > 0 && $product->categories->first()->image_url) {
                                        $galleryCategoryFallback = $product->categories->first()->image_url;
                                    }
                                @endphp
                                <div class="position-relative">
                                    @include('admin.components.image-with-fallback', [
                                        'src' => $image->image_url,
                                        'alt' => $product->name,
                                        'type' => 'product',
                                        'fallbacks' => [$galleryCategoryFallback],
                                        'class' => 'img-thumbnail w-100',
                                        'style' => 'height: 120px; object-fit: cover;'
                                    ])
                                    @if($image->is_primary)
                                        <span class="badge badge-primary position-absolute" style="top: 5px; right: 5px;">Main</span>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1">{{ ucfirst($image->image_type) }}</small>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="text-center p-4 bg-light rounded">
                                <i class="fas fa-images fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No images available</p>
                            </div>
                        </div>
                    @endif
                </div>            </div>        </div>
    </div>
</div>

@endsection

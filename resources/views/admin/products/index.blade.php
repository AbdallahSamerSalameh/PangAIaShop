@extends('admin.layouts.app')

@section('title', 'Products Management')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Products Management</h1>
    <a href="{{ route('admin.products.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add New Product
    </a>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Products List</h6>
    </div>    <div class="card-body">        <!-- Custom filter row -->
        <div class="row mb-3 align-items-center">
            <div class="col-sm-4">
                <form id="perPageForm" action="{{ route('admin.products.index') }}" method="GET" class="form-inline">
                    <label class="mr-2 text-nowrap">Show</label>
                    <select name="per_page" id="per_page" class="form-control form-control-sm"
                        onchange="document.getElementById('perPageForm').submit()">
                        <option value="10" {{ request('per_page')==10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 25)==25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page')==50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page')==100 ? 'selected' : '' }}>100</option>
                    </select>
                    <label class="ml-2 text-nowrap">entries</label>
                </form>
            </div>
            <div class="col-sm-4 offset-sm-4">
                <div id="searchContainer"></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="productsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($products) && $products->count() > 0)
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>                        <td>
                            @if($product->images && $product->images->count() > 0 &&
                            $product->images->where('is_primary', true)->first())
                            @php
                                $primaryImage = $product->images->where('is_primary', true)->first();
                                $imageUrl = str_starts_with($primaryImage->image_url, 'http') 
                                    ? $primaryImage->image_url 
                                    : asset('storage/' . $primaryImage->image_url);
                                
                                // Get category fallback image
                                $categoryImageUrl = '';
                                if($product->directCategories && $product->directCategories->count() > 0 && $product->directCategories->first()->image_url) {
                                    $categoryImage = $product->directCategories->first()->image_url;
                                    $categoryImageUrl = str_starts_with($categoryImage, 'http') 
                                        ? $categoryImage 
                                        : asset('storage/' . $categoryImage);
                                } elseif($product->categories && $product->categories->count() > 0 && $product->categories->first()->image_url) {
                                    $categoryImage = $product->categories->first()->image_url;
                                    $categoryImageUrl = str_starts_with($categoryImage, 'http') 
                                        ? $categoryImage 
                                        : asset('storage/' . $categoryImage);
                                } else {
                                    $categoryImageUrl = asset('admin-assets/img/undraw_posting_photo.svg');
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" 
                                alt="{{ $product->name }}" class="img-thumbnail"
                                style="width: 50px; height: 50px; object-fit: cover;"
                                onerror="this.src='{{ $categoryImageUrl }}'; this.onerror=function(){this.src='{{ asset('admin-assets/img/undraw_posting_photo.svg') }}'};"
                                loading="lazy">
                            @elseif($product->images && $product->images->count() > 0)
                            @php
                                $firstImage = $product->images->first();
                                $imageUrl = str_starts_with($firstImage->image_url, 'http') 
                                    ? $firstImage->image_url 
                                    : asset('storage/' . $firstImage->image_url);
                                
                                // Get category fallback image
                                $categoryImageUrl = '';
                                if($product->directCategories && $product->directCategories->count() > 0 && $product->directCategories->first()->image_url) {
                                    $categoryImage = $product->directCategories->first()->image_url;
                                    $categoryImageUrl = str_starts_with($categoryImage, 'http') 
                                        ? $categoryImage 
                                        : asset('storage/' . $categoryImage);
                                } elseif($product->categories && $product->categories->count() > 0 && $product->categories->first()->image_url) {
                                    $categoryImage = $product->categories->first()->image_url;
                                    $categoryImageUrl = str_starts_with($categoryImage, 'http') 
                                        ? $categoryImage 
                                        : asset('storage/' . $categoryImage);
                                } else {
                                    $categoryImageUrl = asset('admin-assets/img/undraw_posting_photo.svg');
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" 
                                alt="{{ $product->name }}" class="img-thumbnail"
                                style="width: 50px; height: 50px; object-fit: cover;"
                                onerror="this.src='{{ $categoryImageUrl }}'; this.onerror=function(){this.src='{{ asset('admin-assets/img/undraw_posting_photo.svg') }}'};"
                                loading="lazy">
                            @else
                            @php
                                // No product image, use category image directly
                                $categoryImageUrl = '';
                                if($product->directCategories && $product->directCategories->count() > 0 && $product->directCategories->first()->image_url) {
                                    $categoryImage = $product->directCategories->first()->image_url;
                                    $categoryImageUrl = str_starts_with($categoryImage, 'http') 
                                        ? $categoryImage 
                                        : asset('storage/' . $categoryImage);
                                } elseif($product->categories && $product->categories->count() > 0 && $product->categories->first()->image_url) {
                                    $categoryImage = $product->categories->first()->image_url;
                                    $categoryImageUrl = str_starts_with($categoryImage, 'http') 
                                        ? $categoryImage 
                                        : asset('storage/' . $categoryImage);
                                }
                            @endphp
                            @if($categoryImageUrl)
                            <img src="{{ $categoryImageUrl }}" 
                                alt="{{ $product->name }}" class="img-thumbnail"
                                style="width: 50px; height: 50px; object-fit: cover;"
                                onerror="this.src='{{ asset('admin-assets/img/undraw_posting_photo.svg') }}'; this.onerror=null;"
                                loading="lazy">
                            @else
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                            @endif
                            @endif
                        </td>
                        <td>{{ $product->name }}</td>                        <td>
                            @if($product->directCategories && $product->directCategories->count() > 0)
                                {{ $product->directCategories->first()->name }}
                                @if($product->directCategories->count() > 1)
                                {{-- <span class="badge badge-info">+{{ $product->directCategories->count() - 1 }}</span> --}}
                                @endif
                            @elseif($product->categories && $product->categories->count() > 0)
                                {{ $product->categories->first()->name }}
                            @else
                                <span class="text-muted">Uncategorized</span>
                            @endif
                        </td>
                        <td>${{ number_format($product->price, 2) }}</td>
                        <td>
                            @if($product->inventory)
                            @if($product->inventory->quantity > 10)
                            <span class="badge badge-success">{{ $product->inventory->quantity }}</span>
                            @elseif($product->inventory->quantity > 0)
                            <span class="badge badge-warning">{{ $product->inventory->quantity }}</span>
                            @else
                            <span class="badge badge-danger">Out of Stock</span>
                            @endif
                            @else
                            <span class="badge badge-secondary">No Data</span>
                            @endif
                        </td>
                        <td>{{ $product->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                    data-toggle="dropdown">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.products.show', $product->id) }}">
                                        <i class="fas fa-eye fa-sm mr-2"></i> View
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.products.edit', $product->id) }}">
                                        <i class="fas fa-edit fa-sm mr-2"></i> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger"
                                            onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash fa-sm mr-2"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="8" class="text-center">No products found.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination section -->
        @if(isset($products) && method_exists($products, 'links'))
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total()
                ?? 0 }} entries
            </div>
            <div>
                {{ $products->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<!-- Custom styles for this page -->
<link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<style>
    /* Custom pagination styling */
    .pagination {
        margin-bottom: 0;
    }

    .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    .page-link {
        color: #4e73df;
    }

    .page-link:hover {
        color: #2e59d9;
    }    /* Hide DataTables elements we don't need */
    .dataTables_paginate,
    .dataTables_info {
        display: none !important;
    }    /* Custom styling for search filter */
    #searchContainer .dataTables_filter {
        margin-bottom: 0;
    }
    
    #searchContainer .dataTables_filter label {
        margin-bottom: 0;
        display: flex;
        align-items: center;
        font-weight: normal;
        justify-content: flex-end;
        width: 100%;
        white-space: nowrap;
    }
    
    #searchContainer .dataTables_filter input {
        margin-left: 0.75rem;
        width: 200px;
        flex-shrink: 0;
    }    /* Prevent text wrapping on form elements */
    .text-nowrap {
        white-space: nowrap;
    }    /* Actions button hover styling */
    .btn-outline-primary:hover {
        color: #fff !important;
    }

    /* Actions button active/pressed styling */
    .btn-outline-primary:active,
    .btn-outline-primary.active,
    .btn-outline-primary:focus {
        color: #fff !important;
    }

</style>
@endpush

@push('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<!-- Page level custom scripts -->
<script>
$(document).ready(function() {
    // Initialize DataTables with custom DOM structure
    $('#productsTable').DataTable({
        "ordering": true,
        "searching": true,
        "responsive": true,
        "paging": false,
        "info": false,
        "lengthChange": false,        "dom": 'frt', // Custom DOM layout - filter (f), table (r) and table content (t)
        "language": {
            "search": "Search products:",
            "searchPlaceholder": "Enter search terms..."
        },
        "initComplete": function() {
            // Move the search box to our custom container
            var searchBox = $('.dataTables_filter');
            searchBox.detach().appendTo('#searchContainer');
            searchBox.addClass('text-right');
        }
    });
});
</script>
@endpush
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
                    
                    <!-- Hidden inputs to preserve other parameters -->
                    @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('category_id'))
                    <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                    @endif
                    @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                </form>
            </div>
            <div class="col-sm-4 offset-sm-4">
                <form action="{{ route('admin.products.index') }}" method="GET" class="form-inline justify-content-end">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search products..." 
                               value="{{ request('search') }}" style="min-width: 200px;">
                        <div class="input-group-append">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                            @if(request('search'))
                            <a href="{{ route('admin.products.index', request()->except(['search', 'page'])) }}" 
                               class="btn btn-secondary btn-sm">
                                <i class="fas fa-times fa-sm"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Hidden inputs to preserve other parameters -->
                    @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    @endif
                    @if(request('category_id'))
                    <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                    @endif
                    @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                </form>
            </div>
        </div>

        <!-- Active Search Filter Display -->
        @if(request('search'))
        <div class="mb-3">
            <div class="d-flex flex-wrap align-items-center">
                <span class="mr-2 text-muted">Active search:</span>
                <span class="badge badge-info mr-2 mb-1">
                    Search: {{ request('search') }}
                    <a href="{{ route('admin.products.index', request()->except(['search', 'page'])) }}" 
                       class="text-white ml-1">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </span>
            </div>
        </div>
        @endif

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
                            
                            @include('admin.components.image-with-fallback', [
                                'src' => $productImage,
                                'alt' => $product->name,
                                'type' => 'product',
                                'fallbacks' => [$categoryFallback],
                                'class' => 'img-thumbnail',
                                'style' => 'width: 50px; height: 50px; object-fit: cover;'
                            ])
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
                                    <div class="dropdown-divider"></div>                                    <form id="delete-form-{{ $product->id }}" action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                    </form>                                    <button type="button" class="dropdown-item text-danger"
                                        onclick="showDeleteModal({{ $product->id }}, '{{ addslashes($product->name) }}', 'product')">
                                        <i class="fas fa-trash fa-sm mr-2"></i> Delete
                                    </button>
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
        </div>        @endif
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
    }

    /* Search form styling */
    .input-group-sm .form-control {
        border-radius: 0.25rem 0 0 0.25rem;
    }
    
    .input-group-sm .btn {
        border-radius: 0 0.25rem 0.25rem 0;
    }

    /* Active filter badge styling */
    .badge-info {
        background-color: #17a2b8;
    }
    
    .badge-info .fas {
        font-size: 10px;
    }/* Prevent text wrapping on form elements */
    .text-nowrap {
        white-space: nowrap;
    }    /* Actions button hover styling */
    .btn-outline-primary:hover {
        color: #fff !important;
    }    /* Actions button active/pressed styling */
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
    // Initialize DataTables with minimal configuration for styling only
    $('#productsTable').DataTable({
        "ordering": true,
        "searching": false, // Disable client-side search
        "responsive": true,
        "paging": false,
        "info": false,
        "lengthChange": false,
        "dom": 'rt', // Only table (r) and table content (t) - removed filter (f)
        "columnDefs": [
            { "orderable": false, "targets": [1, 7] } // Disable ordering for Image and Actions columns
        ]
    });
});
</script>
@endpush
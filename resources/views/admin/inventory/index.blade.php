@extends('admin.layouts.app')

@section('title', 'Inventory Management')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Inventory Management</h1>
    <a href="{{ route('admin.products.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add New Product
    </a>
</div>

<!-- Inventory Filters Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Inventory Filters</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.inventory.index') }}" method="GET">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="search">Search Product</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name or SKU">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="stock_status">Stock Status</label>
                        <select class="form-control" id="stock_status" name="stock_status">
                            <option value="">All Products</option>
                            <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                            <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-group mb-0 w-100">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter fa-sm"></i> Filter
                        </button>
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt fa-sm"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Inventory Status Cards -->
<div class="row">
    <!-- Total Products Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProducts }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- In Stock Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            In Stock Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inStockProducts }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Low Stock Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStockProducts }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Out of Stock Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Out of Stock Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $outOfStockProducts }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Table Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Inventory Status</h6>
    </div>    <div class="card-body">
        <!-- Custom filter row -->
        <div class="row mb-3 align-items-center">
            <div class="col-sm-4">
                <form id="perPageForm" action="{{ route('admin.inventory.index') }}" method="GET" class="form-inline">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('stock_status'))
                        <input type="hidden" name="stock_status" value="{{ request('stock_status') }}">
                    @endif
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
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="inventoryTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($inventory->count() > 0)
                        @foreach($inventory as $product)
                            <tr>
                                <td>{{ $product->id }}</td>                                <td>
                                    @php
                                        $productImage = null;
                                        $categoryFallback = null;
                                        
                                        // Get primary or first product image
                                        if($product->images && $product->images->count() > 0) {
                                            $primaryImage = $product->images->where('is_primary', true)->first();
                                            $productImage = $primaryImage ? $primaryImage->image_url : $product->images->first()->image_url;
                                        }
                                        
                                        // Get category fallback
                                        if($product->directCategories && $product->directCategories->count() > 0) {
                                            $categoryFallback = $product->directCategories->first()->image_url;
                                        } elseif($product->categories && $product->categories->count() > 0) {
                                            $categoryFallback = $product->categories->first()->image_url;
                                        }
                                    @endphp
                                    @include('admin.components.image-with-fallback', [
                                        'src' => $productImage,
                                        'alt' => $product->name,
                                        'type' => 'product',
                                        'fallbacks' => [$categoryFallback],
                                        'class' => 'img-thumbnail',
                                        'style' => 'width: 50px; height: 50px; object-fit: cover;',
                                        'loading' => 'lazy'
                                    ])
                                </td>
                                <td>{{ $product->name }}</td>                                <td>{{ $product->sku }}</td>                                <td>
                                    @php
                                        $quantity = $product->inventory ? (int)$product->inventory->quantity : 0;
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <form action="{{ route('admin.inventory.update', $product->id) }}" method="POST" class="inventory-update-form d-flex align-items-center" data-product-id="{{ $product->id }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="input-group input-group-sm" style="width: 100px;">
                                                <input type="number" class="form-control form-control-sm stock-input-{{ $product->id }}" name="quantity" value="{{ $quantity }}" min="0" style="font-size: 0.875rem; text-align: center;">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-primary btn-sm update-stock-btn" type="submit" title="Update Stock">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge-{{ $product->id }}">
                                        @if($quantity > 10)
                                            <span class="badge badge-success">In Stock</span>
                                        @elseif($quantity > 0)
                                            <span class="badge badge-warning">Low Stock</span>
                                        @else
                                            <span class="badge badge-danger">Out of Stock</span>
                                        @endif
                                    </span>
                                </td>
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
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No inventory data found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination section -->
        @if($inventory->count() > 0 && method_exists($inventory, 'links'))
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $inventory->firstItem() ?? 0 }} to {{ $inventory->lastItem() ?? 0 }} of {{ $inventory->total() ?? 0 }} entries
            </div>
            <div>
                {{ $inventory->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
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
    }

    /* Prevent text wrapping on form elements */
    .text-nowrap {
        white-space: nowrap;
    }

    /* Actions button hover styling */
    .btn-outline-primary:hover {
        color: #fff !important;
    }

    /* Actions button active/pressed styling */
    .btn-outline-primary:active,
    .btn-outline-primary.active,
    .btn-outline-primary:focus {
        color: #fff !important;
    }

    /* Custom styling for update stock button */
    .update-stock-btn {
        transition: all 0.3s ease;
    }

    .update-stock-btn:hover {
        background-color: #4e73df;
        border-color: #4e73df;
        color: white;
    }

    /* Loading state for update button */
    .update-stock-btn.loading {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
        pointer-events: none;
    }

    /* Success animation for updated rows */
    .inventory-updated {
        background-color: #d4edda !important;
        transition: background-color 0.3s ease;
    }    /* Input group sizing */
    .input-group-sm .form-control {
        height: calc(1.5em + 0.5rem + 2px);
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    
    /* Dropdown form styling */
    .dropdown-item-text {
        width: 200px;
        padding: 0.5rem 1rem;
    }
    
    .dropdown-item-text .input-group {
        width: 100%;
    }
    
    .dropdown-item-text .form-control {
        border-radius: 0.25rem 0 0 0.25rem;
    }
    
    .dropdown-item-text .btn {
        border-radius: 0 0.25rem 0.25rem 0;
    }
</style>
@endpush

@push('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<script>
$(document).ready(function() {
    $('#inventoryTable').DataTable({
        "paging": false,
        "ordering": false, // Disable client-side ordering completely
        "info": false,
        "searching": false,
        "responsive": true
        // We removed columnDefs since ordering is disabled
    });
    
    // Handle inventory update with AJAX
    $('.inventory-update-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const productId = form.data('product-id');
        const url = form.attr('action');
        const button = form.find('.update-stock-btn');
        const input = form.find('input[name="quantity"]');
        const newQuantity = parseInt(input.val());
        const originalQuantity = input.data('original') || input.val();
          // Store original value if not already stored
        if (!input.data('original')) {
            input.data('original', input.val());
        }
        
        // Add loading state
        button.addClass('loading');
        button.html('<i class="fas fa-spinner fa-spin"></i>');
        button.prop('disabled', true);
        
        $.ajax({
            type: 'POST',
            url: url,
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },            success: function(response) {
                // Update the status badge
                updateStatusBadge(productId, newQuantity);
                
                // Update the current stock display
                updateCurrentStockDisplay(productId, newQuantity);
                
                // Show success notification
                showNotification('success', 'Inventory updated successfully!');
                
                // Add success highlight to row
                const row = form.closest('tr');
                row.addClass('inventory-updated');
                setTimeout(function() {
                    row.removeClass('inventory-updated');
                }, 2000);
                
                // Update the original value
                input.data('original', newQuantity);
            },
            error: function(xhr, status, error) {
                console.error('Error updating inventory:', error);
                
                // Revert to original value
                input.val(originalQuantity);
                
                // Show error notification
                let errorMessage = 'Error updating inventory.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('error', errorMessage);
            },
            complete: function() {
                // Remove loading state
                button.removeClass('loading');
                button.html('<i class="fas fa-save"></i>');
                button.prop('disabled', false);
            }
        });
    });    // Function to update status badge based on quantity
    function updateStatusBadge(productId, quantity) {
        const badgeContainer = $('.status-badge-' + productId);
        let badgeHtml = '';
        
        if (quantity > 10) {
            badgeHtml = '<span class="badge badge-success">In Stock</span>';
        } else if (quantity > 0) {
            badgeHtml = '<span class="badge badge-warning">Low Stock</span>';
        } else {
            badgeHtml = '<span class="badge badge-danger">Out of Stock</span>';
        }
        
        badgeContainer.html(badgeHtml);
    }
      // Function to update current stock display
    function updateCurrentStockDisplay(productId, quantity) {
        const stockInput = $('.stock-input-' + productId);
        stockInput.val(quantity);
        stockInput.data('original', quantity);
    }
    
    // Function to show notifications
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="${iconClass} mr-2"></i>${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);
        
        $('body').append(notification);
        
        // Auto-hide after 4 seconds
        setTimeout(function() {
            notification.alert('close');
        }, 4000);
    }
    
    // Validate quantity input
    $('input[name="quantity"]').on('input', function() {
        const value = parseInt($(this).val());
        if (value < 0) {
            $(this).val(0);
        }
    });
    
    // Handle enter key in quantity input
    $('input[name="quantity"]').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $(this).closest('form').submit();
        }
    });
});
</script>
@endpush

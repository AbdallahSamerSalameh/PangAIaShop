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
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inventory->total() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- In Stock Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            In Stock Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inventory->where('in_stock', true)->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Out of Stock Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Out of Stock Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inventory->where('in_stock', false)->count() }}</div>
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
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="inventoryTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
                <tbody>
                    @if($inventory->count() > 0)
                        @foreach($inventory as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->images && $product->images->count() > 0)
                                            <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" alt="{{ $product->name }}" class="img-thumbnail mr-3" style="width: 50px; height: 50px;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center mr-3" style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <span>{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>
                                    <form action="{{ route('admin.inventory.update', $product->id) }}" method="POST" class="inventory-update-form">
                                        @csrf
                                        @method('PUT')
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control" name="quantity" value="{{ $product->inventory ? $product->inventory->quantity : 0 }}" min="0">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-primary" type="submit">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    @if($product->inventory && $product->inventory->quantity > 10)
                                        <span class="badge badge-success">In Stock</span>
                                    @elseif($product->inventory && $product->inventory->quantity > 0)
                                        <span class="badge badge-warning">Low Stock</span>
                                    @else
                                        <span class="badge badge-danger">Out of Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center">No inventory data found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        @if($inventory->count() > 0)
            <div class="d-flex justify-content-center mt-4">
                {{ $inventory->appends(['search' => request('search'), 'stock_status' => request('stock_status')])->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<!-- Custom styles for this page -->
<link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<script>
$(document).ready(function() {
    $('#inventoryTable').DataTable({
        "paging": false,
        "ordering": true,
        "info": false,
        "searching": false,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [4] }
        ]
    });
    
    // Show success message when quantity is updated
    $('.inventory-update-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        
        $.ajax({
            type: 'POST',
            url: url,
            data: form.serialize(),
            success: function(data) {
                // Show success notification
                const successAlert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                    'Inventory updated successfully!' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>');
                
                $('#content').prepend(successAlert);
                
                // Auto-hide after 3 seconds
                setTimeout(function() {
                    successAlert.alert('close');
                }, 3000);
            },
            error: function(error) {
                // Show error notification
                const errorAlert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    'Error updating inventory.' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>');
                
                $('#content').prepend(errorAlert);
                
                // Auto-hide after 3 seconds
                setTimeout(function() {
                    errorAlert.alert('close');
                }, 3000);
            }
        });
    });
});
</script>
@endpush

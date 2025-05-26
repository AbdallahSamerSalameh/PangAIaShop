@extends('admin.layouts.app')

@section('title', 'Edit Inventory')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Inventory</h1>
    <a href="{{ route('admin.inventory.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Inventory
    </a>
</div>

<!-- Inventory Edit Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ $product->name }} (SKU: {{ $product->sku }})</h6>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.inventory.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <!-- Current Stock -->
                    <div class="form-group">
                        <label for="quantity">Current Stock <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="{{ old('quantity', $product->inventory ? $product->inventory->quantity : 0) }}" required>
                    </div>
                    
                    <!-- SKU -->
                    <div class="form-group">
                        <label for="sku">SKU</label>
                        <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                    </div>
                    
                    <!-- Location -->
                    <div class="form-group">
                        <label for="location">Storage Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $product->inventory ? $product->inventory->location : '') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Low Stock Threshold -->
                    <div class="form-group">
                        <label for="low_stock_threshold">Low Stock Threshold</label>
                        <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $product->inventory ? $product->inventory->low_stock_threshold : 5) }}">
                    </div>

                    <!-- Restock Status -->
                    <div class="form-group">
                        <label for="restock_status">Restock Status</label>
                        <select class="form-control" id="restock_status" name="restock_status">
                            <option value="">Select Status</option>
                            <option value="Not Required" {{ (old('restock_status', $product->inventory ? $product->inventory->restock_status : '') == 'Not Required') ? 'selected' : '' }}>Not Required</option>
                            <option value="Pending" {{ (old('restock_status', $product->inventory ? $product->inventory->restock_status : '') == 'Pending') ? 'selected' : '' }}>Pending</option>
                            <option value="Ordered" {{ (old('restock_status', $product->inventory ? $product->inventory->restock_status : '') == 'Ordered') ? 'selected' : '' }}>Ordered</option>
                        </select>
                    </div>
                    
                    <!-- Restock ETA -->
                    <div class="form-group">
                        <label for="restock_eta">Restock ETA</label>
                        <input type="date" class="form-control" id="restock_eta" name="restock_eta" value="{{ old('restock_eta', $product->inventory && $product->inventory->restock_eta ? $product->inventory->restock_eta->format('Y-m-d') : '') }}">
                    </div>
                </div>
            </div>
            
            <!-- Safety Stock -->
            <div class="form-group">
                <label for="safety_stock">Safety Stock</label>
                <input type="number" class="form-control" id="safety_stock" name="safety_stock" min="0" value="{{ old('safety_stock', $product->inventory ? $product->inventory->safety_stock : 0) }}">
                <small class="form-text text-muted">The minimum inventory level to maintain for emergencies or unexpected demand.</small>
            </div>

            <!-- Notes -->
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $product->inventory ? $product->inventory->notes : '') }}</textarea>
            </div>

            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="fas fa-save mr-1"></i> Update Inventory
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Product Information -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                @if($product->images && $product->images->count() > 0)
                    <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" alt="{{ $product->name }}" class="img-fluid mb-3">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center p-5 mb-3">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-9">
                <h5>{{ $product->name }}</h5>
                <p class="mb-1"><strong>SKU:</strong> {{ $product->sku }}</p>
                <p class="mb-1"><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                <p class="mb-1"><strong>Status:</strong> 
                    @if($product->in_stock)
                        <span class="badge badge-success">In Stock</span>
                    @else
                        <span class="badge badge-danger">Out of Stock</span>
                    @endif
                </p>
                <p class="mb-0"><strong>Categories:</strong> 
                    @if($product->categories)
                        @foreach($product->categories as $category)
                            <span class="badge badge-info">{{ $category->name }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">No categories</span>
                    @endif
                </p>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between">
                    <h6 class="font-weight-bold">Recent Activity</h6>
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit fa-sm"></i> Edit Product
                    </a>
                </div>
                
                <div class="mt-3">
                    <p class="text-muted">No recent inventory activity found for this product.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

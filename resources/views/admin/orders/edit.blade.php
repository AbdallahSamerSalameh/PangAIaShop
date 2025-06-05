@extends('admin.layouts.app')

@section('title', 'Update Order Status')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Update Order Status</h1>
    <div>
        <a href="{{ route('admin.orders.show', $order->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm mr-2">
            <i class="fas fa-eye fa-sm text-white-50"></i> View Details
        </a>
        <a href="{{ route('admin.orders.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Orders
        </a>
    </div>
</div>

<!-- Order Summary -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order #{{ $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h6>
            </div>            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                @php
                                    $userImage = $order->user && $order->user->profile_image ? asset('storage/' . $order->user->profile_image) : ($order->user->avatar_url ?? null);
                                @endphp
                                @include('admin.components.image-with-fallback', [
                                    'src' => $userImage,
                                    'alt' => $order->user->username ?? $order->user->name ?? 'Guest User',
                                    'type' => 'profile',
                                    'class' => 'img-profile rounded-circle',
                                    'style' => 'width: 40px; height: 40px; object-fit: cover;'
                                ])
                            </div>
                            <div>
                                <div class="font-weight-bold">{{ $order->user->username ?? $order->user->name ?? 'Guest User' }}</div>
                                <div class="text-muted small">{{ $order->user->email ?? 'No email' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Order Date:</strong> {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y g:i A') : 'N/A' }}</p>
                        <p><strong>Total Amount:</strong> <span class="text-success font-weight-bold">${{ number_format($order->total_amount, 2) }}</span></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Current Status:</strong> 
                            @php
                                $statusClass = match(strtolower($order->status)) {
                                    'pending' => 'badge-warning',
                                    'processing' => 'badge-info',
                                    'shipped' => 'badge-primary',
                                    'delivered' => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                    // 'refunded' => 'badge-secondary',
                                    default => 'badge-light'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                        </p>
                        <p><strong>Items:</strong> {{ $order->items->count() }} items</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Update Order Status</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="form-group">
                        <label for="status" class="form-label"><strong>Order Status</strong></label>
                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="">Select Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ strtolower($status) }}" 
                                    {{ strtolower($order->status) === strtolower($status) ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Current status: <strong>{{ ucfirst($order->status) }}</strong>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="admin_notes" class="form-label"><strong>Admin Notes (Optional)</strong></label>
                        <textarea class="form-control @error('admin_notes') is-invalid @enderror" 
                                  id="admin_notes" 
                                  name="admin_notes" 
                                  rows="4" 
                                  placeholder="Add any internal notes about this status change...">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                        @error('admin_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            These notes are internal and will not be visible to customers.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="expected_delivery_date" class="form-label"><strong>Expected Delivery Date (Optional)</strong></label>
                        <input type="date" 
                               class="form-control @error('expected_delivery_date') is-invalid @enderror" 
                               id="expected_delivery_date" 
                               name="expected_delivery_date" 
                               value="{{ old('expected_delivery_date', $order->expected_delivery_date ? \Carbon\Carbon::parse($order->expected_delivery_date)->format('Y-m-d') : '') }}">
                        @error('expected_delivery_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Set or update the expected delivery date for this order.
                        </small>
                    </div>

                    <!-- Status Change Actions -->
                    <div class="alert alert-info" id="status-info" style="display: none;">
                        <h6 class="alert-heading">Status Change Effects:</h6>
                        <p class="mb-0" id="status-description"></p>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Update Order Status
                        </button>
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Items Summary -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
            </div>
            <div class="card-body">                @foreach($order->items as $item)                <div class="d-flex align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    @if($item->product)
                        @include('admin.components.image-with-fallback', [
                            'src' => $item->product->images->first()->image_url ?? null,
                            'alt' => $item->product->name,
                            'type' => 'product',
                            'fallbacks' => [$item->product->categories->first()->image_url ?? null],
                            'class' => 'img-thumbnail mr-3',
                            'style' => 'width: 40px; height: 40px; object-fit: cover;'
                        ])
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px;">
                            <i class="fas fa-image text-muted"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <div class="font-weight-bold small">{{ $item->product->name ?? 'Product Deleted' }}</div>
                        <div class="text-muted small">Qty: {{ $item->quantity }} × ${{ number_format($item->price, 2) }}</div>
                    </div>
                </div>
                @endforeach
                
                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong class="text-success">${{ number_format($order->total_amount, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Status change descriptions
    const statusDescriptions = {
        'pending': 'Order is waiting to be processed. Inventory items are reserved.',
        'processing': 'Order is being prepared for shipment. Items are being picked and packed.',
        'shipped': 'Order has been shipped and is on its way to the customer.',
        'delivered': 'Order has been delivered to the customer.',
        // 'completed': 'Order is complete and payment has been confirmed.',
        'cancelled': 'Order has been cancelled. Inventory items will be returned to stock.',
        // 'refunded': 'Order has been refunded. Payment has been returned to customer.'
    };

    // Show status description when status changes
    $('#status').on('change', function() {
        const selectedStatus = $(this).val();
        const currentStatus = '{{ strtolower($order->status) }}';
        
        if (selectedStatus && selectedStatus !== currentStatus) {
            $('#status-description').text(statusDescriptions[selectedStatus] || 'Status will be updated.');
            $('#status-info').show();
        } else {
            $('#status-info').hide();
        }
    });

    // Form validation
    $('form').on('submit', function(e) {
        const selectedStatus = $('#status').val();
        const currentStatus = '{{ strtolower($order->status) }}';
        
        if (!selectedStatus) {
            e.preventDefault();
            alert('Please select a status for the order.');
            return false;
        }

        // // Confirm status change
        // if (selectedStatus !== currentStatus) {
        //     const statusName = $('#status option:selected').text();
        //     if (!confirm(`Are you sure you want to change the order status to "${statusName}"?`)) {
        //         e.preventDefault();
        //         return false;
        //     }
        // }
    });
});
</script>
@endpush

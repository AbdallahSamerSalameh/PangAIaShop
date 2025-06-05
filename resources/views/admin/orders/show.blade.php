@extends('admin.layouts.app')

@section('title', 'Order Details')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Order Details</h1>
    <div>
        <a href="{{ route('admin.orders.invoice', $order->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm mr-2">
            <i class="fas fa-print fa-sm text-white-50"></i> Print Invoice
        </a>
        <a href="{{ route('admin.orders.edit', $order->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm mr-2">
            <i class="fas fa-edit fa-sm text-white-50"></i> Update Status
        </a>
        <a href="{{ route('admin.orders.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Orders
        </a>
    </div>
</div>

<!-- Order Header -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Order #{{ $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h6>
                @php
                    $statusClass = match(strtolower($order->status)) {
                        'pending' => 'badge-warning',
                        'processing' => 'badge-info',
                        'shipped' => 'badge-primary',
                        'delivered', 'completed' => 'badge-success',
                        'cancelled' => 'badge-danger',
                        'refunded' => 'badge-secondary',
                        default => 'badge-light'
                    };
                @endphp
                <span class="badge {{ $statusClass }} p-2">{{ ucfirst($order->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Order Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="40%"><strong>Order Date:</strong></td>
                                <td>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y g:i A') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount:</strong></td>
                                <td><strong class="text-success">${{ number_format($order->total_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Payment Status:</strong></td>
                                <td>
                                    @if($order->payment)
                                        <span class="badge badge-success">Paid</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>                    <div class="col-md-6">
                        <h6 class="text-primary">Customer Information</h6>                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                @php
                                    $userImage = $order->user && $order->user->profile_image ? asset('storage/' . $order->user->profile_image) : ($order->user->avatar_url ?? null);
                                @endphp
                                @include('admin.components.image-with-fallback', [
                                    'src' => $userImage,
                                    'alt' => $order->user->username ?? $order->user->name ?? 'Guest User',
                                    'type' => 'profile',
                                    'class' => 'img-profile rounded-circle',
                                    'style' => 'width: 50px; height: 50px; object-fit: cover;'
                                ])
                            </div>
                            <div>
                                <div class="font-weight-bold h6 mb-1">{{ $order->user->username ?? $order->user->name ?? 'Guest User' }}</div>
                                <div class="text-muted">{{ $order->user->email ?? 'No email' }}</div>
                            </div>
                        </div>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="40%"><strong>Total Items:</strong></td>
                                <td>{{ $order->items->count() }} items</td>
                            </tr>
                            @if($order->expected_delivery_date)
                            <tr>
                                <td><strong>Expected Delivery:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($order->expected_delivery_date)->format('M d, Y') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Address Information -->
<div class="row">
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Billing Address</h6>
            </div>
            <div class="card-body">
                @if($order->billing_street)
                    <address>
                        {{ $order->billing_street }}<br>
                        {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postal_code }}<br>
                        {{ $order->billing_country }}
                    </address>
                @else
                    <p class="text-muted">No billing address provided</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Shipping Address</h6>
            </div>
            <div class="card-body">
                @if($order->shipping_street)
                    <address>
                        {{ $order->shipping_street }}<br>
                        {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}<br>
                        {{ $order->shipping_country }}
                    </address>
                @else
                    <p class="text-muted">Same as billing address</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Order Items -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th width="15%" class="text-center">Quantity</th>
                                <th width="15%" class="text-right">Unit Price</th>
                                <th width="15%" class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->product)
                                            @include('admin.components.image-with-fallback', [
                                                'src' => $item->product->images->first()->image_url ?? null,
                                                'alt' => $item->product->name,
                                                'type' => 'product',
                                                'fallbacks' => [$item->product->categories->first()->image_url ?? null],
                                                'class' => 'img-thumbnail mr-3',
                                                'style' => 'width: 50px; height: 50px; object-fit: cover;'
                                            ])
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center mr-3" style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $item->product->name ?? 'Product Deleted' }}</strong>
                                            @if($item->product && $item->product->sku)
                                                <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-right">${{ number_format($item->price, 2) }}</td>
                                <td class="text-right"><strong>${{ number_format($item->quantity * $item->price, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                <td class="text-right"><strong>${{ number_format($order->subtotal ?? $order->total_amount, 2) }}</strong></td>
                            </tr>
                            @if($order->shipping && $order->shipping > 0)
                            <tr class="table-light">
                                <td colspan="3" class="text-right"><strong>Shipping:</strong></td>
                                <td class="text-right"><strong>${{ number_format($order->shipping, 2) }}</strong></td>
                            </tr>
                            @endif
                            @if($order->discount && $order->discount > 0)
                            <tr class="table-light">
                                <td colspan="3" class="text-right text-danger"><strong>Discount:</strong></td>
                                <td class="text-right text-danger"><strong>-${{ number_format($order->discount, 2) }}</strong></td>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <td colspan="3" class="text-right"><h5 class="mb-0">Total Amount:</h5></td>
                                <td class="text-right"><h5 class="mb-0 text-primary">${{ number_format($order->total_amount, 2) }}</h5></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Notes -->
@if($order->notes || $order->admin_notes)
<div class="row">
    @if($order->notes)
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Customer Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $order->notes }}</p>
            </div>
        </div>
    </div>
    @endif
    @if($order->admin_notes)
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Admin Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $order->admin_notes }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

@endsection

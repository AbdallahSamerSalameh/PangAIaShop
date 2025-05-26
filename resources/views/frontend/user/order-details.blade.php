@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - Order Details')

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Order Information</p>
                    <h1>Order Details</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- order details section -->
<div class="checkout-section mt-150 mb-150">
    <div class="container">
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="order-details-wrap">
                    <div class="section-title">
                        <h3>Order #{{ $order->order_number }}</h3>
                        <p>Placed on {{ $order->order_date->format('F d, Y') }}</p>
                    </div>

                    <div class="order-status mb-4">
                        <h4>Order Status: 
                            <span class="badge 
                                @if($order->status == 'delivered') badge-success 
                                @elseif($order->status == 'shipped') badge-info 
                                @elseif($order->status == 'processing') badge-primary 
                                @elseif($order->status == 'pending') badge-warning 
                                @elseif($order->status == 'cancelled') badge-danger 
                                @else badge-secondary 
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </h4>
                          @if($order->status == 'shipped' && $order->shipments->isNotEmpty())
                        <p>
                            <strong>Tracking Number:</strong> {{ $order->shipments->first()->tracking_number }}<br>
                            <strong>Shipped On:</strong> {{ $order->shipments->first()->shipped_at->format('M d, Y') }}<br>
                            <strong>Expected Delivery:</strong> {{ $order->expected_delivery_date->format('M d, Y') }}
                        </p>
                        <a href="{{ route('orders.track', $order->id) }}" class="boxed-btn">Track Shipment</a>
                        @endif                        @if(in_array($order->status, ['pending', 'processing']))
                        <button type="button" class="boxed-btn red red-btn" data-toggle="modal" data-target="#cancelOrderModal">
                            Cancel Order
                        </button>
                        
                        <!-- Cancel Order Modal -->
                        <div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content" style="border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                                    <div class="modal-header" style="background-color: #dc3545; color: white; border-bottom: none;">
                                        <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order #{{ $order->order_number }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body" style="padding: 25px;">
                                            <div class="text-center mb-4">
                                                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
                                                <h5>Are you sure you want to cancel this order?</h5>
                                                <p class="text-muted">This action cannot be undone.</p>
                                            </div>
                                            <div class="form-group">
                                                <label for="reason" style="font-weight: 600; color: #333;">Reason for cancellation (optional):</label>
                                                <textarea name="reason" id="reason" class="form-control" rows="3" style="border-radius: 5px; border: 1px solid #ddd;"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer" style="border-top: none; padding: 15px 25px 25px; justify-content: center;">
                                            <button type="button" class="btn" style="background-color: #e0e0e0; color: #333; border-radius: 5px; padding: 8px 18px; font-weight: 600; margin: 0 5px;" data-dismiss="modal">Keep Order</button>
                                            <button type="submit" class="btn" style="background-color: #dc3545; color: white; border-radius: 5px; padding: 8px 18px; font-weight: 600; margin: 0 5px;">Yes, Cancel Order</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Shipping Address</h5>
                                </div>
                                <div class="card-body">
                                    <address>
                                        {{ $order->shipping_street }}<br>
                                        {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}<br>
                                        {{ $order->shipping_country }}
                                    </address>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Billing Address</h5>
                                </div>
                                <div class="card-body">
                                    <address>
                                        {{ $order->billing_street }}<br>
                                        {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postal_code }}<br>
                                        {{ $order->billing_country }}
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Payment Information</h5>
                        </div>
                        <div class="card-body">
                            @if($order->payment)
                            <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment->payment_method)) }}</p>
                            <p><strong>Payment Status:</strong> {{ ucfirst($order->payment->status) }}</p>
                            <p><strong>Transaction ID:</strong> {{ $order->payment->transaction_id }}</p>
                            @else
                            <p>No payment information available.</p>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5>Order Items</h5>
                        </div>
                        <div class="card-body p-0">                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="product-name">Product</th>
                                        <th class="product-price text-right">Price</th>
                                        <th class="product-quantity text-center">Quantity</th>
                                        <th class="product-total text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>                                    @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td class="text-right">${{ number_format($item->price, 2) }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-right">${{ number_format($item->quantity * $item->price, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                        <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Shipping:</strong></td>
                                        <td class="text-right">${{ number_format($order->shipping, 2) }}</td>
                                    </tr>
                                    @if($order->discount > 0)
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Discount:</strong></td>
                                        <td class="text-right">-${{ number_format($order->discount, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr class="order-total-row">
                                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                        <td class="text-right"><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="order-summary-wrap">
                    <h4 class="mb-3">Order Summary</h4>
                    <table class="order-details">
                        <tbody class="order-details-body">
                            <tr>
                                <td>Subtotal</td>
                                <td>${{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Shipping</td>
                                <td>${{ number_format($order->shipping, 2) }}</td>
                            </tr>
                            @if($order->discount > 0)
                            <tr>
                                <td>Discount</td>
                                <td>-${{ number_format($order->discount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="mt-4">
                        <a href="{{ route('orders') }}" class="boxed-btn">Back to Orders</a>
                        @if($order->status == 'delivered')
                        <a href="#" class="boxed-btn mt-3">Write a Review</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end order details section -->
@endsection

@section('styles')
<style>
    .badge {
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
    }
    .badge-success { background-color: #28a745; color: white; }
    .badge-info { background-color: #17a2b8; color: white; }
    .badge-primary { background-color: #007bff; color: white; }
    .badge-warning { background-color: #ffc107; color: #212529; }
    .badge-danger, .red-btn { background-color: #dc3545 !important ; color: white !important ; }
    .badge-secondary { background-color: #6c757d; color: white; }
    
    .order-details {
        width: 100%;
    }
    
    .order-details td {
        padding: 10px;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .order-details tr:last-child td {
        border-bottom: none;
    }
    
    /* Table styling */
    .table {
        width: 100%;
        margin-bottom: 1rem;
        border-collapse: collapse;
        background-color: #fff;
    }
    
    .table td, .table th {
        padding: 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }
    
    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    /* Text alignment helpers */
    .text-right {
        text-align: right !important;
    }
    
    .text-center {
        text-align: center !important;
    }
    
    /* Product column styles */
    .product-name {
        font-weight: 500;
        width: 40%;
    }
    
    .product-quantity {
        width: 15%;
    }
    
    .product-price {
        width: 20%;
    }
    
    .product-total {
        width: 25%;
        font-weight: 600;
    }
      /* Table hover effect */
    .table-hover tbody tr:hover {
        background-color: rgba(242, 129, 35, 0.05);
    }
    
    /* Footer styling */
    .table tfoot td {
        padding: 10px 12px;
        border-top: 1px solid #dee2e6;
    }
    
    .table tfoot tr:last-child {
        border-top: 2px solid #dee2e6;
        background-color: #f8f9fa;
    }
    
    .table tfoot tr:last-child td {
        padding-top: 14px;
        padding-bottom: 14px;
        font-size: 1.1em;
    }
    
    .order-total-row {
        font-weight: bold;
    }
</style>
@endsection

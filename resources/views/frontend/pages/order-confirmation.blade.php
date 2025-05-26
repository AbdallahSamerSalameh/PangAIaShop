@extends('frontend.layouts.master')

@section('title', 'Order Confirmation')

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Thank you for your order</p>
                    <h1>Order Confirmation</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<div class="order-confirmation-section mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="order-confirmation-box">
                    <div class="confirmation-header text-center mb-5">
                        <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                        <h2>Your Order Has Been Received</h2>
                        <p class="lead">Thank you for your purchase!</p>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="order-details mb-4">
                                <h4>Order Details</h4>
                                <table class="table">                                    <tr>
                                        <td><strong>Order Number:</strong></td>
                                        <td>{{ $order->order_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date:</strong></td>
                                        <td>{{ $order->order_date->format('F d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total:</strong></td>
                                        <td>${{ number_format($order->total_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Method:</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $order->payment->payment_method ?? 'not available')) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="shipping-details mb-4">                                <h4>Shipping Details</h4>
                                <table class="table">
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td>{{ $order->shipments->first()->recipient_name ?? $order->user->username ?? $order->billing_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Address:</strong></td>
                                        <td>{{ $order->shipping_street }}, {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}, {{ $order->shipping_country }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ $order->shipments->first()->recipient_email ?? $order->user->email ?? $order->billing_email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ $order->shipments->first()->recipient_phone ?? $order->user->phone_number ?? $order->billing_phone }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>                    <div class="order-items mt-4">
                        <h4>Order Items</h4>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="product-name">Product</th>
                                    <th class="product-quantity text-center">Quantity</th>
                                    <th class="product-price text-right">Price</th>
                                    <th class="product-total text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td class="product-name">{{ $item->product->name }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">${{ number_format($item->price, 2) }}</td>
                                    <td class="text-right">${{ number_format($item->quantity * $item->price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>                            <tfoot>
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
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                    <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>                    <div class="order-actions mt-5 text-center">
                        <a href="{{ route('shop') }}" class="boxed-btn">Continue Shopping</a>
                        <a href="{{ route('orders') }}" class="boxed-btn black">View Your Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .order-confirmation-box {
        background-color: #f5f5f5;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .text-success {
        color: #4CAF50;
    }
    
    .confirmation-header {
        margin-bottom: 30px;
    }
    
    .order-details, .shipping-details {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        height: 100%;
    }
    
    .order-items {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
    }
    
    .order-actions {
        margin-top: 30px;
    }
    
    /* Improved table styles */
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
    
    .table tfoot td {
        font-weight: 500;
        padding-top: 12px;
        padding-bottom: 12px;
    }
    
    .table tfoot tr:last-child {
        font-weight: 700;
        border-top: 2px solid #dee2e6;
        background-color: #f8f9fa;
    }
    
    .table tfoot tr:last-child td {
        font-size: 1.1em;
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
    
    /* Footer row spacing */
    tfoot tr td {
        padding-top: 12px;
        padding-bottom: 12px;
    }
</style>
@endsection

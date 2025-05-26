@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - Track Order')

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Shipping Information</p>
                    <h1>Track Order</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- tracking section -->
<div class="tracking-section mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="tracking-wrap">
                    <div class="section-title text-center">
                        <h3>Order #{{ $order->order_number }}</h3>
                        <p>Tracking Number: {{ $shipment->tracking_number }}</p>
                    </div>

                    <div class="tracking-details mt-5">
                        <div class="tracking-timeline">
                            <div class="timeline-container">
                                <div class="timeline">
                                    <div class="timeline-item {{ $order->status == 'processing' || $order->status == 'shipped' || $order->status == 'delivered' ? 'active' : '' }}">
                                        <div class="timeline-icon">
                                            <i class="fa fa-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h4>Order Processed</h4>
                                            <p>{{ $order->order_date->format('F d, Y - h:i A') }}</p>
                                            <p>Your order has been confirmed and is being processed</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item {{ $order->status == 'shipped' || $order->status == 'delivered' ? 'active' : '' }}">
                                        <div class="timeline-icon">
                                            <i class="fa fa-truck"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h4>Order Shipped</h4>
                                            <p>{{ $shipment->shipped_at ? $shipment->shipped_at->format('F d, Y - h:i A') : 'Pending' }}</p>
                                            <p>Your order has been shipped and is on its way</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item {{ $order->status == 'delivered' ? 'active' : '' }}">
                                        <div class="timeline-icon">
                                            <i class="fa fa-home"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h4>Order Delivered</h4>
                                            <p>{{ $order->status == 'delivered' ? $shipment->delivered_at->format('F d, Y - h:i A') : 'Expected: ' . $order->expected_delivery_date->format('F d, Y') }}</p>
                                            <p>{{ $order->status == 'delivered' ? 'Your order has been delivered' : 'Your order will be delivered soon' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="shipping-details mt-5">
                            <div class="row">
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
                                            <h5>Shipping Method</h5>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Carrier:</strong> {{ $shipment->carrier ?? 'Standard Shipping' }}</p>
                                            <p><strong>Method:</strong> {{ ucfirst($shipment->shipping_method) }}</p>
                                            <p><strong>Estimated Delivery:</strong> {{ $order->expected_delivery_date->format('F d, Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <a href="{{ route('orders.show', $order->id) }}" class="boxed-btn">View Order Details</a>
                            <a href="{{ route('orders') }}" class="boxed-btn">Back to Orders</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end tracking section -->
@endsection

@section('styles')
<style>
    .timeline-container {
        width: 100%;
        position: relative;
    }
    
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }
    
    .timeline:before {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #ddd;
        left: 50px;
        margin-left: -2px;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 50px;
        display: flex;
        align-items: flex-start;
    }
    
    .timeline-icon {
        background: #f5f5f5;
        border: 4px solid #ddd;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        text-align: center;
        line-height: 44px;
        font-size: 20px;
        color: #999;
        position: relative;
        z-index: 1;
    }
    
    .timeline-item.active .timeline-icon {
        background: #F28123;
        border-color: #F28123;
        color: #fff;
    }
    
    .timeline-content {
        margin-left: 30px;
        background: #f8f8f8;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        flex-grow: 1;
    }
    
    .timeline-content h4 {
        margin-top: 0;
        color: #333;
    }
    
    .timeline-content p {
        margin-bottom: 0;
        color: #666;
    }
    
    .timeline-content p:first-of-type {
        font-weight: bold;
        color: #F28123;
    }
</style>
@endsection

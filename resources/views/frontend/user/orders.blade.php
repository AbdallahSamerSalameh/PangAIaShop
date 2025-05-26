@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - My Orders')

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>My Account</p>
                    <h1>My Orders</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- orders section -->
<div class="cart-section mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="cart-table-wrap">
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

                    @if(count($orders) > 0)
                    <table class="cart-table">
                        <thead class="cart-table-head">
                            <tr class="table-head-row">
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr class="table-body-row">
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->order_date->format('M d, Y') }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>
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
                                </td>
                                <td>
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-primary">View</a>
                                    @if(in_array($order->status, ['pending', 'processing']))                                    <button type="button" class="btn btn-sm" style="background-color: #dc3545; color: white;" data-toggle="modal" data-target="#cancelOrderModal{{ $order->id }}">Cancel</button>
                                    
                                    <!-- Cancel Order Modal -->
                                    <div class="modal fade" id="cancelOrderModal{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="cancelOrderModalLabel{{ $order->id }}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content" style="border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                                                <div class="modal-header" style="background-color: #dc3545; color: white; border-bottom: none;">
                                                    <h5 class="modal-title" id="cancelOrderModalLabel{{ $order->id }}">Cancel Order #{{ $order->order_number }}</h5>
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
                                                            <label for="reason{{ $order->id }}" style="font-weight: 600; color: #333;">Reason for cancellation (optional):</label>
                                                            <textarea name="reason" id="reason{{ $order->id }}" class="form-control" rows="3" style="border-radius: 5px; border: 1px solid #ddd;"></textarea>
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
                                    @if(in_array($order->status, ['processing', 'shipped']))
                                    <a href="{{ route('orders.track', $order->id) }}" class="btn btn-sm btn-info">Track</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div class="pagination-wrap mt-5">
                        {{ $orders->links() }}
                    </div>
                    @else
                    <div class="text-center">
                        <h3>You haven't placed any orders yet</h3>
                        <p>Once you place an order, you'll be able to track its status here.</p>
                        <a href="{{ route('shop') }}" class="boxed-btn mt-4">Shop Now</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end orders section -->
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
    .badge-danger { background-color: #dc3545; color: white; }
    .badge-secondary { background-color: #6c757d; color: white; }
</style>
@endsection

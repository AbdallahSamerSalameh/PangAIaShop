@extends('layouts.app')

@section('title', 'Guest Shopping Cart')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Your Shopping Cart</h1>
            
            @if(count($products) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">                                            @if(!empty($product['images']) && count($product['images']) > 0)
                                                <img src="{{ $product['images'][0]['image_url'] }}" 
                                                     alt="{{ $product['name'] }}" 
                                                     class="img-thumbnail mr-3" 
                                                     style="width: 80px; height: 80px; object-fit: cover;"
                                                     onerror="this.onerror=null; this.src='{{ !empty($product['category_image']) ? asset($product['category_image']) : asset('assets/img/categories/default-category.jpg') }}'">
                                            @else
                                                <img src="{{ asset('assets/img/categories/default-category.jpg') }}" 
                                                     alt="{{ $product['name'] }}" 
                                                     class="img-thumbnail mr-3" 
                                                     style="width: 80px; height: 80px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <h5 class="mb-0">{{ $product['name'] }}</h5>
                                                <small class="text-muted">{{ $product['sku'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${{ number_format($product['price'], 2) }}</td>
                                    <td>
                                        <form action="{{ route('guest.cart.update') }}" method="POST" class="quantity-form">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="item_id" value="{{ $product['cart_item_id'] }}">                                            <div class="input-group" style="width: 120px;">
                                                <button type="button" style="background-color: #f1f1f1; color: #333; border: 1px solid #ddd; cursor: pointer;" class="quantity-decrease">-</button>
                                                <input type="number" name="quantity" style="border-radius: 0; text-align: center; border: 1px solid #ddd;" class="form-control text-center quantity-input" value="{{ $product['cart_quantity'] }}" min="1" max="{{ $product['stock'] }}">
                                                <button type="button" style="background-color: #f1f1f1; color: #333; border: 1px solid #ddd; cursor: pointer;" class="quantity-increase">+</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td>${{ number_format($product['price'] * $product['cart_quantity'], 2) }}</td>
                                    <td>
                                        <form action="{{ route('guest.cart.remove', $product['cart_item_id']) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background-color: #dc3545; color: white; border: none; border-radius: 3px; padding: 5px 10px; cursor: pointer; transition: all 0.3s ease;">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                <td colspan="2">${{ number_format($cartTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between mt-4">                    <a href="{{ route('shop') }}" style="background-color: white; color: #F28123; border: 1px solid #F28123; border-radius: 3px; padding: 8px 15px; text-decoration: none; font-weight: 600;">Continue Shopping</a>
                    <div>
                        <a href="{{ route('login') }}" style="background-color: #F28123; color: white; border: none; border-radius: 3px; padding: 8px 15px; text-decoration: none; font-weight: 600; margin-right: 10px;">Login to Checkout</a>
                        <a href="{{ route('register') }}" class="btn btn-success">Register to Checkout</a>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <i class="fa fa-info-circle me-2"></i>
                    You need to <a href="{{ route('login') }}">login</a> or <a href="{{ route('register') }}">register</a> to complete your purchase.
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fa fa-shopping-cart fa-4x mb-3 text-muted"></i>
                    <h3>Your cart is empty</h3>
                    <p class="lead text-muted">Looks like you haven't added any products to your cart yet.</p>
                    <a href="{{ route('shop') }}" class="btn btn-primary mt-3">Start Shopping</a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle quantity buttons
        document.querySelectorAll('.quantity-decrease').forEach(function(button) {
            button.addEventListener('click', function() {
                var input = this.parentNode.querySelector('.quantity-input');
                var value = parseInt(input.value);
                if (value > 1) {
                    input.value = value - 1;
                    this.closest('form').submit();
                }
            });
        });
        
        document.querySelectorAll('.quantity-increase').forEach(function(button) {
            button.addEventListener('click', function() {
                var input = this.parentNode.querySelector('.quantity-input');
                var value = parseInt(input.value);
                var max = parseInt(input.getAttribute('max'));
                if (value < max) {
                    input.value = value + 1;
                    this.closest('form').submit();
                }
            });
        });
        
        // Submit form when quantity changes
        document.querySelectorAll('.quantity-input').forEach(function(input) {
            input.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    });
</script>
@endpush
@endsection

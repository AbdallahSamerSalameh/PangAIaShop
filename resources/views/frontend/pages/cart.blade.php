@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - Shopping Cart')

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Fresh and Organic</p>
                    <h1>Cart</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- cart -->
<div class="cart-section mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-12">
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

                    @if(count($cartItems) > 0)
                    <table class="cart-table">
                        <thead class="cart-table-head">
                            <tr class="table-head-row">
                                <th class="product-remove"></th>
                                <th class="product-image">Product Image</th>
                                <th class="product-name">Name</th>
                                <th class="product-price">Price</th>
                                <th class="product-quantity">Quantity</th>
                                <th class="product-total">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cartItems as $item)
                            <tr class="table-body-row">                                <td class="product-remove">
                                    <form action="{{ route('cart.remove') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                        <button type="submit" class="btn-remove" style="background: none; border: none; color: #F28123; cursor: pointer; font-size: 20px;"><i
                                                class="far fa-window-close"></i></button>
                                    </form>
                                </td><td class="product-image">
                                    <img src="{{ asset($item->product->featured_image) }}" 
                                         alt="{{ $item->product->name }}" 
                                         style="width: 80px; height: 80px; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='{{ $item->product->categories->isNotEmpty() ? asset($item->product->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">
                                </td>                                <td class="product-name">{{ $item->product->name }}</td>
                                <td class="product-price">
                                    @if($item->product->sale_price && $item->product->sale_price > 0)
                                        <span style="text-decoration: line-through; color: #999;">${{ number_format($item->product->price, 2) }}</span>
                                        <span style="color: #F28123; font-weight: bold;">${{ number_format($item->product->sale_price, 2) }}</span>
                                    @else
                                        ${{ number_format($item->product->price, 2) }}
                                    @endif
                                </td><td class="product-quantity">
                                    <form action="{{ route('cart.update') }}" method="POST" style="display: flex; align-items: center;">
                                        @csrf
                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                        <input type="number" name="quantity" min="1" value="{{ $item->quantity }}" style="width: 70px; margin-right: 10px; border-radius: 3px; border: 1px solid #ddd; padding: 5px 10px;">
                                        <button type="submit" style="background-color: #F28123; color: white; border: none; border-radius: 3px; padding: 5px 10px; cursor: pointer; transition: all 0.3s ease;">Update</button>
                                    </form>
                                </td>
                                <td class="product-total">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center">
                        <h3>Your cart is empty</h3>
                        <p>Looks like you haven't added any products to your cart yet.</p>
                        <a href="{{ route('shop') }}" class="boxed-btn mt-4">Continue Shopping</a>
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="total-section">
                    <table class="total-table">
                        <thead class="total-table-head">
                            <tr class="table-total-row">
                                <th>Total</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="total-data">
                                <td><strong>Subtotal: </strong></td>
                                <td>${{ number_format($subtotal, 2) }}</td>
                            </tr>
                            <tr class="total-data">
                                <td><strong>Shipping: </strong></td>
                                <td>${{ number_format($shipping, 2) }}</td>
                            </tr>
                            @if($discount > 0)
                            <tr class="total-data">
                                <td><strong>Discount: </strong></td>
                                <td>-${{ number_format($discount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="total-data">
                                <td><strong>Total: </strong></td>
                                <td>${{ number_format($total, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="cart-buttons">
                        <a href="{{ route('shop') }}" class="boxed-btn">Continue Shopping</a>
                        @if(count($cartItems) > 0)
                        <a href="{{ route('checkout') }}" class="boxed-btn black">Checkout</a>
                        @endif
                    </div>
                </div>                <div class="coupon-section">
                    <h3>Apply Coupon</h3>
                    <div class="coupon-form-wrap">
                        <form action="{{ route('cart.apply-promo') }}" method="POST">
                            @csrf
                            <p><input type="text" name="code" placeholder="Coupon Code" style="width: 100%; padding: 10px; border-radius: 3px; border: 1px solid #ddd; margin-bottom: 10px;"></p>
                            <p><button type="submit" style="background-color: #F28123; color: white; border: none; border-radius: 3px; padding: 10px 20px; font-weight: 600; width: 100%; cursor: pointer; transition: all 0.3s ease;">Apply Coupon</button></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end cart -->
@endsection
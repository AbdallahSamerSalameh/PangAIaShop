<?php
// filepath: c:\Users\Abdal\OneDrive\Desktop\PangAIaShop-BackEnd\resources\views\frontend\user\wishlist.blade.php
?>
@extends('frontend.layouts.master')

@section('title', 'My Wishlist - PangAIaShop')

@section('styles')
<style>
    .wishlist-page {
        padding: 40px 0;
    }
    .wishlist-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .wishlist-title {
        margin: 0;
        font-size: 28px;
        color: #051922;
    }
    .wishlist-actions {
        display: flex;
        gap: 15px;
    }
    .wishlist-actions .btn {
        padding: 10px 20px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .wishlist-actions .btn-orange {
        background-color: #F28123;
        color: white;
        border: none;
    }
    .wishlist-actions .btn-orange:hover {
        background-color: #e67612;
    }
    .wishlist-actions .btn-outline {
        background-color: transparent;
        color: #F28123;
        border: 1px solid #F28123;
    }
    .wishlist-actions .btn-outline:hover {
        background-color: #F28123;
        color: white;
    }
    .wishlist-empty {
        text-align: center;
        padding: 50px 20px;
        background-color: #f8f9fa;
        border-radius: 10px;
    }
    .wishlist-empty p {
        margin-bottom: 20px;
        color: #666;
        font-size: 16px;
    }
    
    /* Product card styles */
    .single-product-item {
        margin-bottom: 30px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        transition: all 0.3s;
        height: 100%;
        display: flex;
        flex-direction: column;
        border-radius: 8px;
        overflow: hidden;
        background-color: white;
        min-height: 450px;
    }
    
    .single-product-item:hover {
        box-shadow: 0 0 30px rgba(0,0,0,0.2);
        transform: translateY(-5px);
    }
    
    .product-image {
        position: relative;
        overflow: hidden;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f9f9f9;
    }
    
    .product-image img {
        transition: transform 0.5s ease;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .single-product-item:hover .product-image img {
        transform: scale(1.05);
    }
    
    .product-info-section {
        display: flex;
        flex-direction: column;
        min-height: 150px;
        padding: 15px;
    }
    
    .single-product-item h3 {
        padding: 0;
        margin-top: 5px;
        margin-bottom: 10px;
        height: 66px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.5;
    }
    
    .single-product-item h3 a {
        color: #333;
        text-decoration: none;
        transition: all 0.3s;
        font-weight: 600;
        font-size: 17px;
    }
    
    .single-product-item h3 a:hover {
        color: #f28123;
    }
      .product-price {
        color: #f28123;
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 8px;
        min-height: 30px;
    }
    
    .original-price {
        text-decoration: line-through;
        color: #999;
        margin-right: 10px;
        font-size: 14px;
    }
    
    .product-category {
        color: #666;
        margin-top: 0;
        margin-bottom: 5px;
        padding: 0;
        height: 20px;
    }
    
    .product-category small {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    
    /* Action Buttons Styles */
    .product-action-buttons {
        padding: 15px;
        margin-top: auto;
        border-top: 1px solid #f1f1f1;
    }
    
    .cart-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #f28123;
        color: white;
        border: none;
        border-radius: 30px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        width: 100%;
        margin-bottom: 10px;
        box-shadow: 0 2px 5px rgba(242, 129, 35, 0.3);
    }
    
    .cart-btn i {
        margin-right: 8px;
    }
    
    .cart-btn:hover {
        background-color: #e67211;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(242, 129, 35, 0.4);
    }
    
    .cart-btn.disabled {
        background-color: #999;
        cursor: not-allowed;
        opacity: 0.7;
        box-shadow: none;
    }
    
    .cart-btn.disabled:hover {
        transform: none;
    }
    
    .wishlist-btn {
        background-color: #f5f5f5;
        color: #555;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .wishlist-btn:hover {
        background-color: #f28123;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(242, 129, 35, 0.3);
    }
    
    .wishlist-btn.active {
        background-color: #f28123;
        color: white;
    }
    
    .out-of-stock {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc3545;
        color: white;
        padding: 5px 15px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
    }
    
    /* Mobile responsive styles */
    @media only screen and (max-width: 768px) {
        body .single-product-item {
            min-height: 400px;
        }
        
        body .product-image {
            height: 180px;
        }
        
        body .out-of-stock {
            padding: 3px 10px;
            font-size: 10px;
        }
        
        body .cart-btn {
            padding: 8px 15px;
            font-size: 12px;
        }
        
        body .cart-btn i {
            font-size: 12px;
        }
        
        body .wishlist-btn {
            width: 35px;
            height: 35px;
        }
    }
</style>
@endsection

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>PangAIaShop</p>
                    <h1>My Wishlist</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- wishlist section -->
<div class="wishlist-page mt-150 mb-150">
    <div class="container">
        @if(session('success'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif
        
        @if(session('error'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif
        
        <div class="row">
            <div class="col-12">
                <div class="wishlist-header">
                    <h2 class="wishlist-title">My Wishlist</h2>
                    <div class="wishlist-actions">
                        <form action="{{ route('wishlist.move_to_cart') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-orange">Add All to Cart</button>
                        </form>
                        <form action="{{ route('wishlist.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear your wishlist?');" id="clear-wishlist-form">
                            @csrf
                            <button type="submit" class="btn btn-outline">Clear Wishlist</button>
                        </form>
                    </div>
                </div>
                
                @if(count($wishlistItems) > 0)
                    <div id="wishlist-items" class="row">
                        @foreach($wishlistItems as $item)
                            <div class="col-lg-4 col-md-6 text-center mb-4">
                                <div class="single-product-item">
                                    <div class="product-image">
                                        <a href="{{ route('product.show', $item->product->id) }}">
                                            <img src="{{ $item->product->featured_image }}" alt="{{ $item->product->name }}" 
                                                 onerror="this.onerror=null; this.src='{{ $item->product->categories->isNotEmpty() ? asset($item->product->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">                                            @php
                                                $inStock = true;
                                                if ($item->product->inventory) {
                                                    $productInventory = $item->product->inventory;
                                                    $inStock = $productInventory->quantity > 0;
                                                } else {
                                                    $inStock = false;
                                                }
                                            @endphp
                                            
                                            @if(!$inStock)
                                                <span class="out-of-stock">Out of Stock</span>
                                            @endif
                                        </a>
                                    </div>
                                    <div class="product-info-section">
                                        <h3>
                                            <a href="{{ route('product.show', $item->product->id) }}">{{ $item->product->name }}</a>
                                        </h3>
                                        <p class="product-price">
                                            @if($item->product->sale_price && $item->product->sale_price < $item->product->price)
                                                <span class="original-price">${{ number_format($item->product->price, 2) }}</span>
                                                ${{ number_format($item->product->sale_price, 2) }}
                                            @else
                                                ${{ number_format($item->product->price, 2) }}
                                            @endif
                                        </p>
                                        <p class="product-category">
                                            <small>{{ isset($item->product->category_names) ? implode(', ', $item->product->category_names->toArray()) : 'Uncategorized' }}</small>
                                        </p>
                                    </div>
                                    <div class="product-action-buttons">
                                        <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="variant_id" value="">
                                            <button type="submit" class="cart-btn {{ !$inStock ? 'disabled' : '' }}" {{ !$inStock ? 'disabled' : '' }}>
                                                <i class="fas fa-shopping-cart"></i> {{ $inStock ? 'Add to Cart' : 'Out of Stock' }}
                                            </button>
                                        </form>
                                        <div class="d-flex justify-content-center mt-2">
                                            <form action="{{ route('wishlist.remove') }}" method="POST" class="d-inline wishlist-form">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                                <button type="submit" class="wishlist-btn active" data-product-id="{{ $item->product_id }}">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="wishlist-empty">
                        <p>Your wishlist is empty.</p>
                        <a href="{{ route('shop') }}" class="btn-orange">Continue Shopping</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- end wishlist section -->
@endsection

@section('scripts')
<script src="{{ asset('assets/js/wishlist-common.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize wishlist functionality
        initializeWishlistFunctionality();

        // Auto hide success messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    });
</script>
@endsection

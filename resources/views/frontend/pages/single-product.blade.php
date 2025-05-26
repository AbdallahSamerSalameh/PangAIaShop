@extends('frontend.layouts.master')

@section('title', 'MegaStore - ' . $product->name)

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Product Details</p>
                    <h1>{{ $product->name }}</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- single product -->
<div class="single-product mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="single-product-images">
                    <!-- Product gallery thumbnails -->
                    <div class="product-gallery">
                        <div class="row">
                            @if(isset($product->images) && $product->images->count() > 0)
                                @foreach($product->images as $image)
                                <div class="col-4 col-md-3 mb-3">
                                    <div class="product-image-thumb{{ $image->is_primary ? ' active' : '' }}">
                                        <img src="{{ asset($image->image_url) }}" alt="Product Image" class="img-fluid thumbnail-img" onclick="changeMainImage(this)" 
                                             onerror="this.onerror=null; this.src='{{ $product->categories->isNotEmpty() ? asset($product->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="col-4 col-md-3 mb-3">
                                    <div class="product-image-thumb active">
                                        <img src="{{ asset($product->featured_image) }}" alt="Product Image" class="img-fluid thumbnail-img" onclick="changeMainImage(this)"
                                             onerror="this.onerror=null; this.src='{{ $product->categories->isNotEmpty() ? asset($product->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="single-product-content">
                    <div class="product-header">
                        <h2 class="product-title">{{ $product->name }}</h2>
                        
                        <!-- Product rating -->
                        <div class="product-rating mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($product->avg_rating))
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                            <span class="rating-count">({{ $product->review_count }} reviews)</span>
                        </div>
                          <!-- Product category -->
                        <div class="product-categories mb-3">
                            <span class="category-label">Categories: </span>
                            @foreach($product->categories as $category)
                                <span class="category-badge">{{ $category->name }}</span>
                            @endforeach
                        </div>

                        <!-- Product price -->
                        <div class="product-price-section mb-4">
                            @if($product->sale_price && $product->sale_price < $product->price)
                                <span class="original-price">${{ number_format($product->price, 2) }}</span>
                                <span class="current-price">${{ number_format($product->sale_price, 2) }}</span>
                                <span class="discount-percentage">{{ round((1 - $product->sale_price / $product->price) * 100) }}% OFF</span>
                            @else
                                <span class="current-price">${{ number_format($product->price, 2) }}</span>
                            @endif
                        </div>
                        
                        <!-- Stock availability -->
                        <div class="product-availability mb-4">                            @if($product->in_stock)
                                <span class="in-stock"><i class="fas fa-check-circle"></i> In Stock ({{ $product->stock_qty }} available)</span>
                            @else
                                <span class="out-of-stock product-details-out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Product description -->
                    <div class="product-description mb-4">
                        <h4 class="description-title">Description</h4>
                        <p class="description-text">{{ $product->description }}</p>
                    </div>                    <!-- Product variants if available -->
                    @if(isset($product->variants) && $product->variants->count() > 0)
                    <div class="product-variants mb-4">
                        @foreach($product->variants as $variant)
                        <div class="variant-group mb-3">
                            <h5 class="variant-title">{{ $variant->name }}:</h5>
                            <div class="variant-options">
                                @if(is_array($variant->values))
                                    @foreach($variant->values as $key => $value)
                                    <div class="variant-option">
                                        <input type="radio" name="variant_{{ $variant->name }}" id="variant_{{ $variant->name }}_{{ $key }}" value="{{ $key }}">
                                        <label for="variant_{{ $variant->name }}_{{ $key }}">{{ $value }}</label>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    <!-- Add to cart form -->
                    <div class="single-product-form mb-4">
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="variant_id" value="">
                            
                            <div class="quantity-section mb-3">
                                <label for="quantity">Quantity:</label>
                                <div class="quantity-input">
                                    <button type="button" class="quantity-btn minus" onclick="decrementQuantity()">-</button>
                                    <input type="number" class="m-0" id="quantity" name="quantity" min="1" max="{{ $product->stock_qty }}" value="1" {{ !$product->in_stock ? 'disabled' : '' }}>
                                    <button type="button" class="quantity-btn plus" onclick="incrementQuantity()">+</button>
                                </div>
                            </div>                            <div class="action-buttons">
                                <button type="submit" class="cart-btn {{ !$product->in_stock ? 'disabled' : '' }}" {{ !$product->in_stock ? 'disabled' : '' }}>
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        </form>
                        
                        @auth
                        <form action="{{ route('wishlist.add') }}" method="POST" class="d-inline wishlist-form mt-2">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="wishlist-btn" data-product-id="{{ $product->id }}">
                                <i class="fas fa-heart"></i>
                            </button>
                        </form>
                        @endauth
                    </div>
                    
                    <!-- Shipping info -->
                    <div class="shipping-info">
                        <div class="shipping-item">
                            <i class="fas fa-truck"></i>
                            <span>Free Shipping on orders over $75</span>
                        </div>
                        <div class="shipping-item">
                            <i class="fas fa-exchange-alt"></i>
                            <span>30-Day Returns</span>
                        </div>
                        <div class="shipping-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Secure Checkout</span>
                        </div>
                    </div>
                    
                    <!-- Social sharing -->
                    <div class="social-sharing mt-4">
                        <h5>Share This Product:</h5>
                        <ul class="product-share">
                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                            <li><a href="#"><i class="fab fa-pinterest"></i></a></li>
                            <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product tabs (details, reviews, etc) -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="product-tabs">
                    <ul class="nav nav-tabs" id="productTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="specifications-tab" data-toggle="tab" href="#specifications" role="tab">Product Specifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="reviews-tab" data-toggle="tab" href="#reviews" role="tab">Reviews ({{ $product->review_count }})</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="shipping-tab" data-toggle="tab" href="#shipping" role="tab">Shipping & Returns</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="productTabContent">
                        <!-- Specifications tab -->
                        <div class="tab-pane fade show active" id="specifications" role="tabpanel">
                            <div class="product-specifications">
                                <table class="specifications-table">
                                    <tbody>
                                        @if(isset($product->specifications))
                                            @foreach($product->specifications as $key => $value)
                                            <tr>
                                                <th>{{ $key }}</th>
                                                <td>{{ $value }}</td>
                                            </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <th>Brand</th>
                                                <td>{{ $product->brand ?? 'MegaStore' }}</td>
                                            </tr>
                                            <tr>
                                                <th>SKU</th>
                                                <td>{{ $product->sku ?? 'MS-' . $product->id }}</td>
                                            </tr>
                                            <tr>
                                                <th>Weight</th>
                                                <td>{{ $product->weight ?? '0.5 kg' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dimensions</th>
                                                <td>{{ $product->dimensions ?? '10 x 5 x 2 cm' }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Reviews tab -->
                        <div class="tab-pane fade" id="reviews" role="tabpanel">
                            <div class="product-reviews">
                                <!-- Reviews summary -->
                                <div class="reviews-summary mb-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-4 text-center">
                                            <div class="average-rating">
                                                <h2>{{ number_format($product->avg_rating, 1) }}</h2>
                                                <div class="stars">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= round($product->avg_rating))
                                                            <i class="fas fa-star"></i>
                                                        @else
                                                            <i class="far fa-star"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <p>Based on {{ $product->review_count }} reviews</p>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="rating-breakdown">                                                @for($i = 5; $i >= 1; $i--)
                                                    <div class="rating-row">
                                                        <span class="rating-stars">{{ $i }} <i class="fas fa-star"></i></span>
                                                        <div class="progress">
                                                            <div class="progress-bar" role="progressbar" style="width: {{ $ratingPercentages[$i] }}%" aria-valuenow="{{ $ratingPercentages[$i] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <span class="rating-count">{{ $ratingDistribution[$i] }}</span>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Individual reviews -->
                                <div class="customer-reviews">
                                    <h4>Customer Reviews</h4>
                                    
                                    @if(isset($product->reviews) && $product->reviews->count() > 0)
                                        @foreach($product->reviews as $review)
                                        <div class="review-item">
                                            <div class="reviewer-info">
                                                <h5 class="reviewer-name">{{ $review->user->name }}</h5>
                                                <div class="review-date">{{ $review->created_at->format('M d, Y') }}</div>
                                                <div class="reviewer-rating">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $review->rating)
                                                            <i class="fas fa-star"></i>
                                                        @else
                                                            <i class="far fa-star"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                            </div>                                            <div class="review-content">
                                                <p>{{ $review->comment ?? 'No comment provided.' }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                        @endforeach
                                    @else
                                        <div class="no-reviews">
                                            <p>There are no reviews yet. Be the first to review this product!</p>
                                        </div>
                                    @endif
                                      <!-- Write a review section -->
                                    @auth
                                    <div class="write-review mt-4">
                                        <h4>Write a Review</h4>
                                        <form action="{{ route('product.review', $product->id) }}" method="POST">
                                            @csrf                                            <div class="form-group mb-4">
                                                <label class="d-block mb-2">Your Rating:</label>                                                <div class="rating-input">
                                                    <input type="radio" id="star5" name="rating" value="5">
                                                    <label for="star5"><i class="far fa-star rating-star"></i></label>
                                                    <input type="radio" id="star4" name="rating" value="4">
                                                    <label for="star4"><i class="far fa-star rating-star"></i></label>
                                                    <input type="radio" id="star3" name="rating" value="3">
                                                    <label for="star3"><i class="far fa-star rating-star"></i></label>
                                                    <input type="radio" id="star2" name="rating" value="2">
                                                    <label for="star2"><i class="far fa-star rating-star"></i></label>
                                                    <input type="radio" id="star1" name="rating" value="1" required>
                                                    <label for="star1"><i class="far fa-star rating-star"></i></label>
                                                </div>
                                            </div>
                                            <div class="form-group mb-4">
                                                <label for="reviewText" class="mb-2">Your Review:</label>
                                                <textarea class="form-control" id="reviewText" name="comment" rows="4" required placeholder="Share your experience with this product..."></textarea>
                                                <small class="text-muted">Your review will help other shoppers make informed decisions.</small>
                                            </div>
                                            <button type="submit" class="boxed-btn">Submit Review</button>
                                        </form>
                                    </div>
                                    @else
                                    <div class="review-login-prompt mt-4 p-4 text-center bg-light">
                                        <p class="mb-0">Please <a href="{{ route('login') }}" class="text-primary">sign in</a> to write a review.</p>
                                    </div>
                                    @endauth
                                </div>
                            </div>
                        </div>
                        
                        <!-- Shipping & Returns tab -->
                        <div class="tab-pane fade" id="shipping" role="tabpanel">
                            <div class="shipping-returns-info">
                                <h4>Shipping Information</h4>
                                <p>We offer free standard shipping on all orders over $75. For orders under $75, standard shipping costs $5.99.</p>
                                <p>Estimated delivery time: 3-7 business days.</p>
                                
                                <h4>Returns Policy</h4>
                                <p>We accept returns within 30 days of delivery. Items must be in original condition with all tags attached and packaging intact.</p>
                                <p>To initiate a return, please contact our customer service team or visit your account page.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end single product -->

<!-- Related Products section -->
<div class="related-products pb-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title">  
                    <h3 class="text-center"><span class="orange-text">Related</span> Products</h3>
                    <p class="text-center">You might also be interested in these products</p>
                </div>
            </div>
        </div>
        
        <div class="row">            
            @foreach($relatedProducts as $related)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="single-product-item">
                    <div class="product-image">
                        <a href="{{ route('product.show', $related->id) }}">
                            <img src="{{ asset($related->featured_image) }}" alt="{{ $related->name }}"
                                 onerror="this.onerror=null; this.src='{{ $related->categories->isNotEmpty() ? asset($related->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">
                            @if(!$related->in_stock)
                                <span class="out-of-stock">Out of Stock</span>
                            @endif
                        </a>
                    </div>
                    <div class="product-info-section">
                        <h3><a href="{{ route('product.show', $related->id) }}">{{ $related->name }}</a></h3>
                        <p class="product-price">
                            @if(isset($related->sale_price) && $related->sale_price < $related->price)
                            <span class="original-price">${{ number_format($related->price, 2) }}</span>
                            ${{ number_format($related->sale_price, 2) }}
                            @else
                            ${{ number_format($related->price, 2) }}
                            @endif
                        </p>
                        <p class="product-category">
                            <small>{{ $related->categories->isNotEmpty() ? $related->categories->first()->name : 'Uncategorized' }}</small>
                        </p>
                    </div>
                    <div class="product-action-buttons">
                        <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $related->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="variant_id" value="">
                            <button type="submit" class="cart-btn {{ !$related->in_stock ? 'disabled' : '' }}" {{ !$related->in_stock ? 'disabled' : '' }}>
                                <i class="fas fa-shopping-cart self-center"></i> {{ $related->in_stock ? 'Add to Cart' : 'Out of Stock' }}
                            </button>
                        </form>                        @auth
                        <form action="{{ route('wishlist.add') }}" method="POST" class="d-inline mt-2 wishlist-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $related->id }}">
                            <button type="submit" class="wishlist-btn" data-product-id="{{ $related->id }}">
                                <i class="fas fa-heart"></i>
                            </button>
                        </form>
                        @endauth
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
<!-- end related products -->

<!-- Recently Viewed Products -->
@if(isset($recentlyViewed) && $recentlyViewed->count() > 0)
<div class="recently-viewed-products pb-100 bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title">  
                    <h3 class="text-center"><span class="orange-text">Recently</span> Viewed</h3>
                    <p class="text-center">Products you've viewed recently</p>
                </div>
            </div>
        </div>
        
        <div class="row">            @foreach($recentlyViewed as $viewed)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="single-product-item">
                    <div class="product-image">
                        <a href="{{ route('product.show', $viewed->id) }}">
                            <img src="{{ asset($viewed->featured_image) }}" alt="{{ $viewed->name }}"
                                 onerror="this.onerror=null; this.src='{{ $viewed->categories->isNotEmpty() ? asset($viewed->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">
                            @if(!$viewed->in_stock)
                                <span class="out-of-stock">Out of Stock</span>
                            @endif
                        </a>
                    </div>
                    <div class="product-info-section">
                        <h3><a href="{{ route('product.show', $viewed->id) }}">{{ $viewed->name }}</a></h3>
                        <p class="product-price">
                            @if(isset($viewed->sale_price) && $viewed->sale_price < $viewed->price)
                            <span class="original-price">${{ number_format($viewed->price, 2) }}</span>
                            ${{ number_format($viewed->sale_price, 2) }}
                            @else
                            ${{ number_format($viewed->price, 2) }}
                            @endif
                        </p>
                        <p class="product-category">
                            <small>{{ $viewed->categories->isNotEmpty() ? $viewed->categories->first()->name : 'Uncategorized' }}</small>
                        </p>
                    </div>
                    <div class="product-action-buttons">
                        <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $viewed->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="variant_id" value="">
                            <button type="submit" class="cart-btn {{ !$viewed->in_stock ? 'disabled' : '' }}" {{ !$viewed->in_stock ? 'disabled' : '' }}>
                                <i class="fas fa-shopping-cart"></i> {{ $viewed->in_stock ? 'Add to Cart' : 'Out of Stock' }}
                            </button>
                        </form>                        @auth
                        <form action="{{ route('wishlist.add') }}" method="POST" class="d-inline mt-2 wishlist-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $viewed->id }}">
                            <button type="submit" class="wishlist-btn" data-product-id="{{ $viewed->id }}">
                                <i class="fas fa-heart"></i>
                            </button>
                        </form>
                        @endauth
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
<!-- end recently viewed products -->
@endsection

@section('styles')
<style>
    /* Product gallery */
    .product-image-thumb {
        cursor: pointer;
        border: 1px solid #ddd;
        padding: 5px;
        transition: all 0.3s;
    }
    
    .product-image-thumb.active {
        border-color: #f28123;
    }
    
    .product-image-thumb:hover {
        border-color: #f28123;
    }
    
    .product-main-img {
        position: relative;
    }
    
    .out-of-stock-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc3545;
        color: white;
        padding: 5px 15px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
    }
    
    /* Product card styles for related and recently viewed products */
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
        min-height: 450px; /* Ensure minimum height for consistency */
    }
    
    .single-product-item:hover {
        box-shadow: 0 0 30px rgba(0,0,0,0.2);
        transform: translateY(-5px);
    }
    
    .product-image {
        position: relative;
        overflow: hidden;
        height: 200px; /* Fixed height for all product images */
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f9f9f9;
    }
    
    .product-image img {
        transition: transform 0.5s ease;
        width: 100%;
        height: 100%;
        object-fit: cover; /* Maintain aspect ratio while covering the container */
    }
    
    .single-product-item:hover .product-image img {
        transform: scale(1.05);
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
    
    /* Product header */
    .product-title {
        font-size: 28px;
        margin-bottom: 15px;
    }
    
    .product-rating i {
        color: #f28123;
    }
    
    .rating-count {
        color: #777;
        margin-left: 5px;
    }
    
    .category-badge {
        background-color: #f5f5f5;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        margin-right: 5px;
    }
    
    /* Product price */
    .product-price-section {
        font-size: 24px;
    }
    
    .original-price {
        text-decoration: line-through;
        color: #999;
        font-size: 18px;
        margin-right: 10px;
    }
    
    .current-price {
        color: #f28123;
        font-weight: bold;
        font-size: 28px;
    }
    
    .discount-percentage {
        background-color: #28a745;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 14px;
        margin-left: 10px;
    }
      /* Stock status */
    .in-stock {
        color: #28a745;
        font-weight: bold;
    }
      /* Product stock status in the details section (not the badge) */
    .product-details-out-of-stock {
        color: #dc3545;
        font-weight: bold;
        position: static;
        background: none;
        padding: 0;
        border-radius: 0;
        box-shadow: none;
    }
    
    /* Product description */
    .description-title {
        font-size: 18px;
        margin-bottom: 10px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }
    
    /* Quantity input */
    .quantity-section {
        display: flex;
        align-items: center;
    }
    
    .quantity-section label {
        margin-right: 15px;
        font-weight: bold;
    }
    
    .quantity-input {
        display: flex;
        align-items: center;
        border: 1px solid #ddd;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .quantity-btn {
        width: 40px;
        height: 40px;
        background: #f8f9fa;
        border: none;
        cursor: pointer;
        font-size: 18px;
    }
    
    .quantity-input input {
        width: 60px;
        height: 40px;
        border: none;
        text-align: center;
        font-size: 16px;
    }
      /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
    }
    
    /* Main product cart button (single product view) */
    .single-product-form .cart-btn {
        background-color: #f28123;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
        flex-grow: 1;
    }
    
    .single-product-form .cart-btn:hover {
        background-color: #e67211;
    }
    
    .single-product-form .cart-btn.disabled {
        background-color: #999;
        cursor: not-allowed;
    }

    .product-action-buttons{
        text-align: center;
    }
    
    /* Product cards cart button (related products) */
    .product-action-buttons .cart-btn {
        justify-content: center;
        align-items: center;
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
    
    .product-action-buttons .cart-btn i {
        margin: 0%;
    }
    
    .product-action-buttons .cart-btn:hover {
        background-color: #e67211;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(242, 129, 35, 0.4);
    }
    
    .product-action-buttons .cart-btn.disabled {
        background-color: #999;
        cursor: not-allowed;
        opacity: 0.7;
        box-shadow: none;
    }
    
    .product-action-buttons .cart-btn.disabled:hover {
        transform: none;
    }    .wishlist-btn {
        background-color: #f5f5f5;
        color: #555;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .wishlist-btn:hover {
        background-color: #f28123;
        color: white;
    }
    
    .wishlist-btn.active {
        background-color: #F28123;
        color: white;
    }
    
    .wishlist-btn.active i {
        color: white;
    }
    
    /* Shipping info */
    .shipping-info {
        margin-top: 30px;
        padding: 15px;
        border: 1px solid #eee;
        border-radius: 5px;
    }
    
    .shipping-item {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    
    .shipping-item i {
        color: #f28123;
        margin-right: 10px;
        font-size: 20px;
    }
    
    /* Product tabs */
    .product-tabs {
        margin-top: 50px;
    }
    
    .nav-tabs {
        border-bottom: 2px solid #f28123;
    }
    
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        color: #555;
        font-weight: bold;
        padding: 15px 20px;
    }
    
    .nav-tabs .nav-link.active {
        color: #f28123;
        background-color: transparent;
        border-bottom: 2px solid #f28123;
    }
    
    .tab-content {
        padding: 30px 0;
    }
    
    /* Specifications tab */
    .specifications-table {
        width: 100%;
    }
    
    .specifications-table th, 
    .specifications-table td {
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
    }
    
    .specifications-table th {
        font-weight: bold;
        width: 30%;
        background-color: #f9f9f9;
    }
    
    /* Reviews tab */
    .average-rating {
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 5px;
    }
    
    .average-rating h2 {
        font-size: 42px;
        margin-bottom: 0;
        color: #f28123;
    }
    
    .average-rating .stars {
        margin: 10px 0;
    }
    
    .rating-row {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }
    
    .rating-stars {
        width: 70px;
        text-align: left;
    }
    
    .rating-count {
        width: 30px;
        text-align: right;
        margin-left: 10px;
    }
    
    .progress {
        flex-grow: 1;
        height: 10px;
        border-radius: 5px;
        background-color: #eee;
    }
    
    .progress-bar {
        background-color: #f28123;
    }
      .review-item {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #eee;
        border-radius: 5px;
        background-color: white;
    }
    
    .reviewer-name {
        margin-bottom: 0;
        font-size: 16px;
    }
    
    .review-date {
        color: #777;
        font-size: 13px;
        margin-bottom: 5px;
    }
    
    .review-content {
        padding: 10px 0;
        margin-top: 10px;
        border-top: 1px solid #eee;
    }
    
    .review-content p {
        color: #555;
        font-size: 14px;
        line-height: 1.6;
    }
        margin-bottom: 5px;
    }
    
    .reviewer-rating {
        color: #f28123;
        margin-bottom: 10px;
    }
      .rating-input {
        display: flex;
        flex-direction: row-reverse;  /* Reversed to match the star order with values */
        justify-content: flex-end;
        margin: 10px 0;
    }
    
    .rating-input input {
        display: none;
    }
    
    .rating-input label {
        cursor: pointer;
        font-size: 28px;
        padding: 0 5px;
        transition: all 0.2s ease;
    }
    
    /* Default star state - empty stars */
    .rating-input label i {
        color: #ddd;
    }
    
    /* Hover effect */
    .rating-input label:hover i,
    .rating-input label:hover ~ label i {
        color: #f28123 !important;
    }
    
    /* Selected state */
    .rating-input input:checked + label i,
    .rating-input input:checked ~ label i {
        color: #f28123 !important;
    }
    
    /* Make sure filled stars are colored correctly */
    .rating-input label i.fas {
        color: #f28123 !important;
        font-weight: 900 !important;
    }
    
    /* Additional styling for better visibility */
    .rating-input label i.fas.fa-star {
        text-shadow: 0 0 1px rgba(0, 0, 0, 0.2);
    }
    
    /* Specific styling for our rating stars */
    .rating-input label i.rating-star.fas {
        color: #f28123 !important;
    }
    
    /* Animation for star rating */
    .rating-input label:hover {
        transform: scale(1.2);
        transition: transform 0.2s ease;
    }
    
    /* Recently viewed section */
    .bg-light {
        background-color: #f8f9fa!important;
    }
    
    /* More product card styles */
    .product-info-section {
        display: flex;
        flex-direction: column;
        min-height: 160px; /* Further increased min-height for product info area */
        padding: 15px;
    }
    
    .single-product-item h3 {
        padding: 0;
        margin-top: 5px;
        margin-bottom: 10px;
        height: 66px; /* Increased height for product title */
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2; /* Limit to 2 lines with more line-height */
        -webkit-box-orient: vertical;
        line-height: 1.5; /* Improved line spacing */
    }
    
    .single-product-item h3 a {
        color: #333;
        text-decoration: none;
        transition: all 0.3s;
        font-weight: 600;
        font-size: 17px; /* Slightly larger font size for better readability */
    }
    
    .single-product-item h3 a:hover {
        color: #f28123;
    }
    
    .product-category {
        color: #666;
        margin-top: 0;
        margin-bottom: 5px;
        padding: 0;
        height: 20px; /* Fixed height for category area */
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    
    /* Product action buttons */
    .product-action-buttons {
        justify-content: center;
        padding: 15px;
        margin-top: auto; /* Push buttons to bottom */
        border-top: 1px solid #f1f1f1;
    }
      .wishlist-btn {
        margin: 0%;
        background-color: #f5f5f5;
        color: #555;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
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
    
    /* Active state for wishlist button */
    .wishlist-btn.active {
        background-color: #f28123;
        color: white;
    }
    
    /* Additional reviewer rating and input styles */
    .reviewer-rating {
        margin-top: 5px;
    }
    
    .reviewer-rating i {
        color: #f28123;
        margin-right: 2px;
    }
      /* Rating input for writing reviews */    .rating-input {
        display: flex;
        flex-direction: row-reverse;  /* Reversed to match the star order with values */
        justify-content: flex-end;
        margin: 10px 0;
    }
    
    .rating-input input {
        display: none;
    }
    
    .rating-input label {
        cursor: pointer;
        font-size: 28px;
        color: #ddd;
        padding: 0 5px;
        transition: all 0.2s ease;
    }      
    
    /* Hover effect */
    .rating-input label:hover i,
    .rating-input label:hover ~ label i {
        color: #f28123;
    }
    
    /* Selected state */
    .rating-input input:checked + label i,
    .rating-input input:checked ~ label i {
        color: #f28123;
    }
    
    /* Animation for star rating */
    .rating-input label:hover {
        transform: scale(1.2);
        transition: transform 0.2s ease;
    }
    
    /* Review content styling enhancements */
    .review-content {
        padding: 10px 0;
        margin-top: 10px;
        border-top: 1px solid #eee;
    }
    
    .review-content p {
        color: #555;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 0;
    }
    
    /* No reviews message styling */
    .no-reviews {
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 5px;
        text-align: center;
    }

    /* Fix for filled stars - ensure proper coloring */
    .fas.fa-star, 
    .rating-input label i.fas.fa-star,
    .rating-input input:checked + label i,
    .rating-input input:checked ~ label i,
    .rating-input label:hover i,
    .rating-input label:hover ~ label i {
        color: #f28123 !important;
    }
</style>
@endsection

@section('scripts')
<script>
    function changeMainImage(element) {
        document.getElementById('main-product-img').src = element.src;
        
        // Update active thumbnail
        const thumbnails = document.querySelectorAll('.product-image-thumb');
        thumbnails.forEach(thumb => thumb.classList.remove('active'));
        element.parentElement.classList.add('active');
    }
    
    function incrementQuantity() {
        const quantity = document.getElementById('quantity');
        const max = parseInt(quantity.getAttribute('max'));
        let value = parseInt(quantity.value);
        
        if (value < max) {
            quantity.value = value + 1;
        }
    }
    
    function decrementQuantity() {
        const quantity = document.getElementById('quantity');
        let value = parseInt(quantity.value);
        
        if (value > 1) {
            quantity.value = value - 1;
        }
    }
    
    // Function to update star appearance to ensure they are filled
    function updateStarAppearance() {
        // For each filled star (fas), explicitly set styles
        $('.rating-input label i.fas').each(function() {
            $(this).css({
                'color': '#f28123',
                'font-weight': '900'
            });
        });
    }
      $(document).ready(function() {
        // Handle star rating selection
        $('.rating-input input').on('change', function() {
            const value = $(this).val();
            console.log('Selected rating:', value);
            
            // Reset all stars to outline version
            $('.rating-input label i').removeClass('fas').addClass('far');
            
            // Fill the selected star and all stars to the right of it (in the flex-direction-row-reverse context)
            const selectedStar = $(this);
            selectedStar.next('label').find('i').removeClass('far').addClass('fas');
            selectedStar.next('label').nextAll('label').find('i').removeClass('far').addClass('fas');
            
            // Call our helper function to ensure stars are properly filled
            updateStarAppearance();
            
            // Add a small delay and update again (helps with some browser rendering issues)
            setTimeout(updateStarAppearance, 50);
        });
          // For accessibility, add keyboard navigation to stars
        $('.rating-input input').on('keydown', function(e) {
            const currentValue = parseInt($(this).val());
            
            // Left arrow increases rating (because our stars are reversed)
            if (e.keyCode === 37 && currentValue < 5) {
                $(`#star${currentValue + 1}`).prop('checked', true).trigger('change');
                updateStarAppearance();
            }
            
            // Right arrow decreases rating (because our stars are reversed)
            if (e.keyCode === 39 && currentValue > 1) {
                $(`#star${currentValue - 1}`).prop('checked', true).trigger('change');
                updateStarAppearance();
            }
        });
        
        // Product tabs
        $('#productTabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');        });    // Wishlist functionality
    $(document).ready(function() {
        // Check if user is logged in
        const isLoggedIn = $('.wishlist-form').length > 0;
        
        if (isLoggedIn) {
            // Function to update wishlist localStorage
            function updateWishlistLocalStorage(productId, isInWishlist) {
                let wishlist = JSON.parse(localStorage.getItem('userWishlist')) || {};
                if (isInWishlist) {
                    wishlist[productId] = true;
                } else {
                    delete wishlist[productId];
                }
                localStorage.setItem('userWishlist', JSON.stringify(wishlist));
            }
            
            // Get all wishlist buttons
            const wishlistButtons = $('.wishlist-btn');
            
            // Apply localStorage state first for immediate feedback
            const savedWishlist = JSON.parse(localStorage.getItem('userWishlist')) || {};
            
            wishlistButtons.each(function() {
                const button = $(this);
                const productId = button.data('product-id');
                if (savedWishlist[productId]) {
                    button.addClass('active');
                }
            });
            
            // Then verify with the server
            wishlistButtons.each(function() {
                const button = $(this);
                const productId = button.data('product-id');
                
                $.get(`/wishlist/check?product_id=${productId}`, function(data) {
                    if (data.inWishlist) {
                        button.addClass('active');
                        // Update localStorage if needed
                        updateWishlistLocalStorage(productId, true);
                    } else {
                        button.removeClass('active');
                        // Update localStorage if needed
                        updateWishlistLocalStorage(productId, false);
                    }
                });
            });
            
            // Add event listeners to wishlist forms
            $('.wishlist-form').on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const button = form.find('.wishlist-btn');
                const productId = button.data('product-id');
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                const isActive = button.hasClass('active');
                
                // Immediately toggle the button state for responsive UI
                if (isActive) {
                    button.removeClass('active');
                } else {
                    button.addClass('active');
                }
                
                // Immediately update localStorage for instant cross-page feedback
                updateWishlistLocalStorage(productId, !isActive);
                
                if (isActive) {
                    // Remove from wishlist
                    $.ajax({
                        url: '/wishlist/remove',
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: JSON.stringify({
                            product_id: productId
                        }),
                        contentType: 'application/json',
                        success: function(data) {
                            if (data.success) {
                                showToast('Product removed from wishlist!');
                                
                                // Update all instances of this product's wishlist button
                                updateAllWishlistButtons(productId, false);
                            } else {
                                // If there was an error, revert the button state
                                button.addClass('active');
                                // Revert localStorage
                                updateWishlistLocalStorage(productId, true);
                                showToast('Error removing product from wishlist');
                            }
                        },
                        error: function() {
                            // Revert button state on error
                            button.addClass('active');
                            // Revert localStorage
                            updateWishlistLocalStorage(productId, true);
                            showToast('Error removing product from wishlist');
                        }
                    });
                } else {
                    // Add to wishlist
                    const formData = new FormData(form[0]);
                    
                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.success) {
                                showToast('Product added to wishlist!');
                                
                                // Update all instances of this product's wishlist button
                                updateAllWishlistButtons(productId, true);
                            } else {
                                // If there was an error, revert the button state
                                button.removeClass('active');
                                // Revert localStorage
                                updateWishlistLocalStorage(productId, false);
                                showToast('Error adding product to wishlist');
                            }
                        },
                        error: function() {
                            // Revert button state on error
                            button.removeClass('active');
                            // Revert localStorage
                            updateWishlistLocalStorage(productId, false);
                            showToast('Error adding product to wishlist');
                        }
                    });
                }
            });
            
            // Function to update all wishlist buttons for the same product
            function updateAllWishlistButtons(productId, isInWishlist) {
                const allButtons = $(`.wishlist-btn[data-product-id="${productId}"]`);
                
                allButtons.each(function() {
                    const btn = $(this);
                    if (isInWishlist) {
                        btn.addClass('active');
                    } else {
                        btn.removeClass('active');
                    }
                });
                
                // Update localStorage
                updateWishlistLocalStorage(productId, isInWishlist);
            }
                
                if (isActive) {
                    // Remove from wishlist
                    $.ajax({
                        url: '/wishlist/remove',
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: JSON.stringify({
                            product_id: productId
                        }),
                        contentType: 'application/json',
                        success: function(data) {
                            if (data.success) {
                                showToast('Product removed from wishlist!');
                                
                                // Update all instances of this product's wishlist button
                                updateAllWishlistButtons(productId, false);
                            } else {
                                // If there was an error, revert the button state
                                button.addClass('active');
                                // Revert localStorage
                                updateWishlistLocalStorage(productId, true);
                                showToast('Error removing product from wishlist');
                            }
                        },
                        error: function() {
                            // Revert button state on error
                            button.addClass('active');
                            // Revert localStorage
                            updateWishlistLocalStorage(productId, true);
                            showToast('Error removing product from wishlist');
                        }
                    });
                } else {
                    // Add to wishlist
                    const formData = new FormData(form[0]);
                    
                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.success) {
                                showToast('Product added to wishlist!');
                                
                                // Update all instances of this product's wishlist button
                                updateAllWishlistButtons(productId, true);
                            } else {
                                // If there was an error, revert the button state
                                button.removeClass('active');
                                // Revert localStorage
                                updateWishlistLocalStorage(productId, false);
                                showToast('Error adding product to wishlist');
                            }
                        },
                        error: function() {
                            // Revert button state on error
                            button.removeClass('active');
                            // Revert localStorage
                            updateWishlistLocalStorage(productId, false);
                            showToast('Error adding product to wishlist');
                        }
                    });
                }
            });
            
            // Function to update all wishlist buttons for the same product
            function updateAllWishlistButtons(productId, isInWishlist) {
                const allButtons = $(`.wishlist-btn[data-product-id="${productId}"]`);
                
                allButtons.each(function() {
                    const btn = $(this);
                    if (isInWishlist) {
                        btn.addClass('active');
                    } else {
                        btn.removeClass('active');
                    }
                });
                
                // Update localStorage
                updateWishlistLocalStorage(productId, isInWishlist);
            }
              // Helper function to show toast message
            function showToast(message) {
                // Create toast container if it doesn't exist
                let toastContainer = $('.toast-container');
                
                if (toastContainer.length === 0) {
                    // Create toast container
                    toastContainer = $('<div class="toast-container"></div>');
                    $('body').append(toastContainer);
                    
                    // Add toast container styles if they don't exist
                    if ($('#toast-styles').length === 0) {
                        const styles = `
                            <style id="toast-styles">
                                .toast-container {
                                    position: fixed;
                                    top: 20px;
                                    right: 20px;
                                    z-index: 9999;
                                }
                                .toast {
                                    background-color: #333;
                                    color: white;
                                    padding: 15px 25px;
                                    border-radius: 5px;
                                    margin-bottom: 10px;
                                    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                    animation: toast-in-right 0.5s;
                                }
                                @keyframes toast-in-right {
                                    from { transform: translateX(100%); }
                                    to { transform: translateX(0); }
                                }
                            </style>
                        `;
                        $('head').append(styles);
                    }
                }
                
                // Create toast message
                const toast = $('<div class="toast"></div>').text(message);
                toastContainer.append(toast);
                
                // Remove toast after 3 seconds
                setTimeout(function() {
                    toast.css({
                        'opacity': '0',
                        'transition': 'opacity 0.5s'
                    });
                    
                    setTimeout(function() {
                        toast.remove();
                    }, 500);
                }, 3000);
            }
        }
    });
</script>
<script src="{{ asset('assets/js/enhanced-star-rating.js') }}"></script>
@endsection
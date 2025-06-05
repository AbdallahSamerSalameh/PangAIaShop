@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - Your One-Stop Shop')

@section('content')
    <!-- home page slider -->
    <div class="homepage-slider">
        <!-- single home slider -->
        <div class="single-homepage-slider homepage-bg-1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 text-left">
                        <div class="hero-text">
                            <div class="hero-text-tablecell">
                                <p class="subtitle">Shop Anything & Everything</p>
                                <h1>Your One-Stop Shop</h1>
                                <div class="hero-btns">
                                    <a href="{{ route('shop') }}" class="boxed-btn">Shop Now</a>
                                    <a href="{{ route('contact') }}" class="bordered-btn">Contact Us</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- single home slider -->
        <div class="single-homepage-slider homepage-bg-2">
            <div class="container">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 text-center">
                        <div class="hero-text">
                            <div class="hero-text-tablecell">
                                <p class="subtitle">Premium Quality</p>
                                <h1>Electronics, Fashion, Home & More</h1>
                                <div class="hero-btns">
                                    <a href="{{ route('shop') }}" class="boxed-btn">Visit Shop</a>
                                    <a href="{{ route('contact') }}" class="bordered-btn">Contact Us</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- single home slider -->
        <div class="single-homepage-slider homepage-bg-3">
            <div class="container">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 text-right">
                        <div class="hero-text">
                            <div class="hero-text-tablecell">
                                <p class="subtitle">Mega Sale Going On!</p>
                                <h1>Get Huge Discounts</h1>
                                <div class="hero-btns">
                                    <a href="{{ route('shop') }}" class="boxed-btn">Visit Shop</a>
                                    <a href="{{ route('contact') }}" class="bordered-btn">Contact Us</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end home page slider -->

    <!-- features list section -->
    <div class="list-section pt-80 pb-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="list-box d-flex align-items-center">
                        <div class="list-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="content">
                            <h3>Fast Shipping</h3>
                            <p>When order over $75</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="list-box d-flex align-items-center">
                        <div class="list-icon">
                            <i class="fas fa-phone-volume"></i>
                        </div>
                        <div class="content">
                            <h3>24/7 Support</h3>
                            <p>Get support anytime</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <div class="list-box d-flex align-items-center">
                        <div class="list-icon">
                            <i class="fas fa-sync"></i>
                        </div>
                        <div class="content">
                            <h3>Easy Returns</h3>
                            <p>Within 30 days</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="list-box d-flex justify-content-start align-items-center">
                        <div class="list-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="content">
                            <h3>Secure Checkout</h3>
                            <p>100% Protected</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end features list section -->

    <!-- Category section -->
    <div class="product-section mt-80 mb-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="section-title">
                        <h3><span class="orange-text">Shop by</span> Categories</h3>
                        <p>Discover our extensive range of products across various categories</p>
                    </div>
                </div>
            </div>

            <div class="row">
                @forelse($featuredCategories as $category)
                    <div class="col-lg-4 col-md-6 mb-4 text-center">
                        <div class="single-product-item category-item">
                            <div class="product-image">
                                <a href="{{ route('shop') }}?category={{ $category->id }}">
                                    <img src="{{ asset($category->image_url ?? 'assets/img/categories/default-category.jpg') }}"
                                        alt="{{ $category->name }}">
                                </a>
                            </div>
                            <div class="product-info-section">
                                <h3><a href="{{ route('shop') }}?category={{ $category->id }}">{{ $category->name }}</a>
                                </h3>
                                <p>{{ $category->products_count ?? 0 }} Products</p>
                            </div>
                            <div class="product-action-buttons">
                                <a href="{{ route('shop') }}?category={{ $category->id }}" class="cart-btn">Browse
                                    Products</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p>Categories coming soon!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    <!-- end category section -->

    <!-- Featured products section -->
    <div class="product-section mt-80 mb-80 bg-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="section-title">
                        <h3><span class="orange-text">Featured</span> Products</h3>
                        <p>Discover our carefully curated selection of top-quality products</p>
                    </div>
                </div>
            </div>

            <div class="row">
                @foreach ($featuredProducts as $product)
                    <div class="col-lg-3 col-md-6 text-center mb-4">
                        <div class="single-product-item">
                            <div class="product-image">
                                <a href="{{ route('product.show', $product->id) }}">
                                    <img src="{{ asset($product->featured_image) }}" alt="{{ $product->name }}"
                                         onerror="this.onerror=null; this.src='{{ $product->categories->isNotEmpty() ? asset($product->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">
                                </a>
                            </div>
                            <div class="product-info-section">
                                <h3><a href="{{ route('product.show', $product->id) }}">{{ $product->name }}</a></h3>
                                <p class="product-price">
                                    @if ($product->sale_price && $product->sale_price < $product->price)
                                        <span class="original-price">${{ number_format($product->price, 2) }}</span>
                                        ${{ number_format($product->sale_price, 2) }}
                                    @else
                                        ${{ number_format($product->price, 2) }}
                                    @endif
                                </p>
                                <p class="product-category"><small>{{ $product->category_name }}</small></p>
                            </div>
                            <div class="product-action-buttons">
                                @if ($product->inventory && $product->inventory->quantity > 0)
                                    <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="variant_id" value="">
                                        <button type="submit" class="cart-btn">
                                            <i class="fas fa-shopping-cart"></i>
                                            Add to Cart
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="cart-btn" disabled>
                                        <i class="fas fa-ban"></i>
                                        Out of Stock
                                    </button>
                                @endif
                                @auth
                                    <form action="{{ route('wishlist.add') }}" method="POST"
                                        class="d-inline mt-2 wishlist-form">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="wishlist-btn" data-product-id="{{ $product->id }}">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </form>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a href="{{ route('shop') }}" class="boxed-btn">View All Products</a>
                </div>
            </div>
        </div>
    </div>
    <!-- end featured products section -->

    <!-- New Arrivals section -->
    <div class="product-section mt-80 mb-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="section-title">
                        <h3><span class="orange-text">New</span> Arrivals</h3>
                        <p>Check out the latest additions to our inventory</p>
                    </div>
                </div>
            </div>            <div class="row">
                @foreach ($newArrivals as $product)
                    <div class="col-lg-3 col-md-6 text-center mb-4">
                        <div class="single-product-item">
                            <div class="product-image">
                                <a href="{{ route('product.show', $product->id) }}">
                                    <img src="{{ asset($product->featured_image) }}" alt="{{ $product->name }}"
                                         onerror="this.onerror=null; this.src='{{ $product->categories->isNotEmpty() ? asset($product->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">
                                    <span class="new-product">New</span>
                                </a>
                            </div>
                            <div class="product-info-section">
                                <h3><a href="{{ route('product.show', $product->id) }}">{{ $product->name }}</a></h3>
                                <p class="product-price">
                                    @if ($product->sale_price && $product->sale_price < $product->price)
                                        <span class="original-price">${{ number_format($product->price, 2) }}</span>
                                        ${{ number_format($product->sale_price, 2) }}
                                    @else
                                        ${{ number_format($product->price, 2) }}
                                    @endif
                                </p>
                                <p class="product-category"><small>{{ $product->category_name }}</small></p>
                            </div>
                            <div class="product-action-buttons">
                                @if ($product->inventory && $product->inventory->quantity > 0)
                                    <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="variant_id" value="">
                                        <button type="submit" class="cart-btn">
                                            <i class="fas fa-shopping-cart"></i>
                                            Add to Cart
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="cart-btn" disabled>
                                        <i class="fas fa-ban"></i>
                                        Out of Stock
                                    </button>
                                @endif
                                @auth
                                    <form action="{{ route('wishlist.add') }}" method="POST"
                                        class="d-inline mt-2 wishlist-form">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="wishlist-btn" data-product-id="{{ $product->id }}">
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
    <!-- end new arrivals section -->

    <!-- cart banner section -->
    <section class="cart-banner pt-100 pb-100">
        <div class="container">
            <div class="row clearfix">
                <!--Image Column-->
                <div class="image-column col-lg-6">
                    <div class="image">
                        <div class="price-box">
                            <div class="inner-price">                                <span class="price">
                                    <strong>Up to 25%</strong> <br> off
                                </span>
                            </div>
                        </div>
                        <img src="{{ asset('assets/img/a.jpg') }}" alt="">
                    </div>
                </div>
                <!--Content Column-->
                <div class="content-column col-lg-6">
                    <h3><span class="orange-text">Deal</span> of the month</h3>
                    <h4>Limited Time Offer</h4>
                    <div class="text">Don't miss out on this incredible deal! Shop now and save big on premium products.
                    </div>
                    <!--Countdown Timer-->
                    <div class="time-counter">
                        <div class="time-countdown clearfix" data-countdown="2025/05/31">
                            <div class="counter-column">
                                <div class="inner"><span class="count">00</span>Days</div>
                            </div>
                            <div class="counter-column">
                                <div class="inner"><span class="count">00</span>Hours</div>
                            </div>
                            <div class="counter-column">
                                <div class="inner"><span class="count">00</span>Mins</div>
                            </div>
                            <div class="counter-column">
                                <div class="inner"><span class="count">00</span>Secs</div>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('shop') }}" class="cart-btn mt-3 text-center"><i class="fas fa-shopping-cart"></i>
                        Shop Now</a>
                </div>
            </div>
        </div>
    </section>
    <!-- end cart banner section -->

    <!-- Best Sellers section -->
    <div class="product-section mt-80 mb-80 bg-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="section-title">
                        <h3><span class="orange-text">Best</span> Sellers</h3>
                        <p>Our most popular products that customers love</p>
                    </div>
                </div>
            </div>            <div class="row">
                @foreach ($bestSellers as $product)
                    <div class="col-lg-3 col-md-6 text-center mb-4">
                        <div class="single-product-item">
                            <div class="product-image">
                                <a href="{{ route('product.show', $product->id) }}">
                                    <img src="{{ asset($product->featured_image) }}" alt="{{ $product->name }}"
                                         onerror="this.onerror=null; this.src='{{ $product->categories->isNotEmpty() ? asset($product->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">
                                    <span class="best-seller">Best Seller</span>
                                </a>
                            </div>
                            <div class="product-info-section">
                                <h3><a href="{{ route('product.show', $product->id) }}">{{ $product->name }}</a></h3>
                                <p class="product-price">
                                    @if ($product->sale_price && $product->sale_price < $product->price)
                                        <span class="original-price">${{ number_format($product->price, 2) }}</span>
                                        ${{ number_format($product->sale_price, 2) }}
                                    @else
                                        ${{ number_format($product->price, 2) }}
                                    @endif
                                </p>
                                <p class="product-category"><small>{{ $product->category_name }}</small></p>
                            </div>
                            <div class="product-action-buttons">
                                @if ($product->inventory && $product->inventory->quantity > 0)
                                    <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="variant_id" value="">
                                        <button type="submit" class="cart-btn">
                                            <i class="fas fa-shopping-cart"></i>
                                            Add to Cart
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="cart-btn" disabled>
                                        <i class="fas fa-ban"></i>
                                        Out of Stock
                                    </button>
                                @endif
                                @auth
                                    <form action="{{ route('wishlist.add') }}" method="POST"
                                        class="d-inline mt-2 wishlist-form">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="wishlist-btn" data-product-id="{{ $product->id }}">
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
    <!-- end best sellers section -->
    {{-- 
<!-- testimonial section -->
<div class="testimonail-section mt-80 mb-80">
	<div class="container">
		<div class="row">
			<div class="col-lg-10 offset-lg-1 text-center">
				<div class="testimonial-sliders">
					<div class="single-testimonial-slider">
						<div class="client-avater">
							<img src="{{ asset('assets/img/avaters/avatar1.png') }}" alt="">
						</div>
						<div class="client-meta">
							<h3>Customer One <span>Regular Customer</span></h3>
							<p class="testimonial-body">
								"I've been shopping with MegaStore for months now and I'm consistently impressed with the quality of products and service across multiple categories. Highly recommend!"
							</p>
							<div class="last-icon">
								<i class="fas fa-quote-right"></i>
							</div>
						</div>
					</div>
					<div class="single-testimonial-slider">
						<div class="client-avater">
							<img src="{{ asset('assets/img/avaters/avatar2.png') }}" alt="">
						</div>
						<div class="client-meta">
							<h3>Customer Two <span>Happy Shopper</span></h3>
							<p class="testimonial-body">
								"The customer service is outstanding, and the delivery is always on time. MegaStore has become my first choice for all my shopping needs, from electronics to fashion."
							</p>
							<div class="last-icon">
								<i class="fas fa-quote-right"></i>
							</div>
						</div>
					</div>
					<div class="single-testimonial-slider">
						<div class="client-avater">
							<img src="{{ asset('assets/img/avaters/avatar3.png') }}" alt="">
						</div>
						<div class="client-meta">
							<h3>Customer Three <span>Satisfied Buyer</span></h3>
							<p class="testimonial-body">
								"The wide selection of products and competitive prices keep me coming back. MegaStore truly understands what customers want in a one-stop shopping destination."
							</p>
							<div class="last-icon">
								<i class="fas fa-quote-right"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- end testimonial section -->

<!-- latest news -->
<div class="latest-news pt-80 pb-80 bg-light">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 text-center">
				<div class="section-title">	
					<h3><span class="orange-text">Latest</span> News</h3>
					<p>Stay updated with the latest trends, offers, and announcements.</p>
				</div>
			</div>
		</div>

		<div class="row">
			@foreach ($latestNews as $news)
			<div class="col-lg-4 col-md-6 mb-4">
				<div class="single-latest-news">
					<a href="{{ route('news.show', $news->id) }}">
						<div class="latest-news-bg" style="background-image: url({{ asset($news->image) }})"></div>
					</a>
					<div class="news-text-box">
						<h3><a href="{{ route('news.show', $news->id) }}">{{ $news->title }}</a></h3>
						<p class="blog-meta">
							<span class="author"><i class="fas fa-user"></i> {{ $news->author }}</span>
							<span class="date"><i class="fas fa-calendar"></i> {{ $news->created_at->format('d F, Y') }}</span>
						</p>
						<p class="excerpt">{{ Str::limit($news->excerpt, 100) }}</p>
						<a href="{{ route('news.show', $news->id) }}" class="read-more-btn">read more <i class="fas fa-angle-right"></i></a>
					</div>
				</div>
			</div>
			@endforeach
		</div>
		<div class="row">
			<div class="col-lg-12 text-center">
				<a href="{{ route('news') }}" class="boxed-btn">More News</a>
			</div>
		</div>
	</div>
</div>
<!-- end latest news --> --}}

    <!-- logo carousel -->
    <div class="logo-carousel-section">
        <div class="container">
			<div class="row">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="section-title">
                        <h3><span class="orange-text">Our</span> Partners</h3>
                        <p>Meet our trusted Partners</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="logo-carousel-inner">
                        <div class="single-logo-item">
                            <img src="{{ asset('assets/img/company-logos/1.png') }}" alt="">
                        </div>
                        <div class="single-logo-item">
                            <img src="{{ asset('assets/img/company-logos/2.png') }}" alt="">
                        </div>
                        <div class="single-logo-item">
                            <img src="{{ asset('assets/img/company-logos/3.png') }}" alt="">
                        </div>
                        <div class="single-logo-item">
                            <img src="{{ asset('assets/img/company-logos/4.png') }}" alt="">
                        </div>
                        <div class="single-logo-item">
                            <img src="{{ asset('assets/img/company-logos/5.png') }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end logo carousel -->

    {{-- <!-- Newsletter subscription section -->
    <div class="subscribe-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 text-center">
                    <div class="subscribe-form">
                        <h3>Subscribe to our newsletter</h3>
                        <p>Get the latest updates on new products and upcoming sales</p>
                        <form action="{{ route('subscribe') }}" method="POST">
                            @csrf
                            <input type="email" name="email" placeholder="Your Email Address" required>
                            <button type="submit" class="main-btn">Subscribe Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end newsletter section --> --}}
@endsection

@section('styles')
    <style>
        .single-product-item {
            margin-bottom: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            border-radius: 8px;
            overflow: hidden;
            background-color: white;
            min-height: 450px;
            /* Ensure minimum height for consistency */
        }

        .single-product-item:hover {
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .product-image {
            position: relative;
            overflow: hidden;
            height: 200px;
            /* Fixed height for all product images */
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
            /* Maintain aspect ratio while covering the container */
        }

        .single-product-item:hover .product-image img {
            transform: scale(1.05);
        }

        .out-of-stock,
        .new-product,
        .best-seller {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #f28123;
            color: white;
            padding: 5px 15px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .out-of-stock {
            background: #dc3545;
        }

        .new-product {
            background: #28a745;
        }

        .best-seller {
            background: #007bff;
        }

        .product-info-section {
            display: flex;
            flex-direction: column;
            min-height: 150px;
            /* Increased min-height for product info area */
            padding: 15px;
        }

        .single-product-item h3 {
            padding: 0;
            margin-top: 5px;
            margin-bottom: 10px;
            height: 66px;
            /* Increased height for product title */
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* Still limit to 2 lines but with more line-height */
            -webkit-box-orient: vertical;
            line-height: 1.5;
            /* Improved line spacing */
        }

        .single-product-item h3 a {
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 17px;
            /* Slightly larger font size for better readability */
        }

        .original-price {
            text-decoration: line-through;
            color: #999;
            margin-right: 10px;
            font-size: 14px;
        }        .product-price {
            color: #f28123;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 8px;
            min-height: 30px;
            /* Increased min-height for price area to accommodate discounted prices */
        }

        .product-action-buttons {
            padding: 15px;
            margin-top: auto;
            /* Push buttons to bottom */
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

        /* Mobile responsive styles for cart buttons */
        @media only screen and (max-width: 768px) {
            body .cart-btn {
                padding: 8px 15px;
                font-size: 12px;
            }

            body .cart-btn i {
                font-size: 12px;
            }
        }

        .category-item {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
        }

        .subscribe-section {
            background-color: #051922;
            color: white;
            padding: 60px 0;
            margin-top: 80px;
        }

        .subscribe-form h3 {
            color: white;
            margin-bottom: 10px;
        }

        .subscribe-form p {
            color: #ccc;
            margin-bottom: 20px;
        }

        .subscribe-form input[type="email"] {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 3px;
            margin-bottom: 10px;
        }

        .main-btn {
            background-color: #f28123;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .main-btn:hover {
            background-color: #e67211;
        }

        .bg-light {
            background-color: #f8f9fa !important;
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
            margin-right: 10px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

        .wishlist-btn.active {
            background-color: #f28123;
            color: white;
        }
    </style>
@endsection

@section('scripts')
    <script>        $(document).ready(function() {
            // Update the countdown date to end of this month
            $('.time-countdown').attr('data-countdown', '2025/05/31');
        });
    </script>
    {{-- Remove per-page wishlist script; using wishlist-common.js for functionality --}}
    @yield('scripts')

@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - Shop')

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Browse our collection</p>
                    <h1>Shop</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- products -->
<div class="product-section mt-150 mb-150">
    <div class="container">
        <!-- Error message display -->
        @if(session('error'))
            <div class="row mb-5">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif
        
        <div class="row">            <!-- Sidebar with filters -->
            <div class="col-lg-3">
                <div class="shop-sidebar" id="sticky-sidebar">
                    <div class="sidebar-scrollable">
                        <!-- Search products -->
                        <div class="sidebar-section mb-5">
                            <h4>Search Products</h4>
                            <form action="{{ route('shop') }}" method="GET">
                                <div class="search-input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                                    <button class="search-btn" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    
                    <!-- Categories filter -->
                    <div class="sidebar-section mb-5">
                        <h4>Categories</h4>
                        <ul class="category-list">
                            <li>
                                <a href="{{ route('shop') }}" class="{{ !request('category') ? 'active' : '' }}">
                                    All Categories
                                </a>
                            </li>                            @foreach($categories as $category)
                            <li>
                                <a href="{{ route('shop', ['category' => is_object($category) ? $category->id : $category['id']]) }}" class="{{ request('category') == (is_object($category) ? $category->id : $category['id']) ? 'active' : '' }}">
                                    {{ is_object($category) ? $category->name : $category['name'] }} ({{ is_object($category) ? $category->products_count : $category['products_count'] }})
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                      <!-- Price range filter -->
                    <div class="sidebar-section mb-5">
                        <h4>Price Range</h4>
                        <form action="{{ route('shop') }}" method="GET" id="price-range-form">
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            
                            @if(request('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif
                            <div class="price-range-inputs">
                                <div class="mb-3">
                                    <div class="price-range-label">
                                        <label for="min-price">Minimum Price:</label>
                                        <span class="price-range-info">$<span id="min-price-display">{{ $minPrice }}</span></span>
                                    </div>
                                    <div class="price-input-group">
                                        <span class="price-symbol">$</span>
                                        <input type="number" name="min_price" id="min-price" class="form-control" min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" value="{{ $minPrice }}" step="1">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="price-range-label">
                                        <label for="max-price">Maximum Price:</label>
                                        <span class="price-range-info">$<span id="max-price-display">{{ $maxPrice }}</span></span>
                                    </div>
                                    <div class="price-input-group">
                                        <span class="price-symbol">$</span>
                                        <input type="number" name="max_price" id="max-price" class="form-control" min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" value="{{ $maxPrice }}" step="1">
                                    </div>
                                </div>                                <button type="submit" class="filter-btn"><i class="fas fa-filter mr-2"></i>Apply Filter</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Availability filter -->
                    <div class="sidebar-section">
                        <h4>Availability</h4>
                        <form action="{{ route('shop') }}" method="GET" id="availability-form">
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            
                            @if(request('min_price'))
                                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                            @endif
                            
                            @if(request('max_price'))
                                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                            @endif
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="in_stock" id="in-stock" value="1" {{ request('in_stock') ? 'checked' : '' }} onchange="document.getElementById('availability-form').submit();">
                                <label class="form-check-label" for="in-stock">
                                    In Stock Only
                                </label>                            </div>
                        </form>
                    </div>
                    </div><!-- end of sidebar-scrollable -->
                </div>
            </div>              <!-- Products list -->
            <div class="col-lg-9">
                <!-- Sort options and display count --> 
                <div id="sticky-top-bar">
                <div class="shop-top-bar mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="showing-results">
                                Showing <span class="badge">{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</span> of <span class="badge">{{ $products->total() ?? 0 }}</span> products
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sort-by-wrapper">
                                <form action="{{ route('shop') }}" method="GET">
                                    @if(request('category'))
                                        <input type="hidden" name="category" value="{{ request('category') }}">
                                    @endif
                                    
                                    @if(request('search'))
                                        <input type="hidden" name="search" value="{{ request('search') }}">
                                    @endif
                                    
                                    @if(request('min_price'))
                                        <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                                    @endif
                                    
                                    @if(request('max_price'))
                                        <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                                    @endif
                                    
                                    @if(request('in_stock'))
                                        <input type="hidden" name="in_stock" value="{{ request('in_stock') }}">
                                    @endif
                                    
                                    <div class="sort-select-wrapper">
                                        <label for="sort-select">Sort by:</label>
                                        <div class="select-container">
                                            <select name="sort" id="sort-select" onchange="this.form.submit()">
                                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Popularity</option>
                                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name: Z to A</option>
                                            </select>
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>                    </div>
                </div>

                <!-- Active filters -->
                @if(request('search') || request('category') || request('min_price') || request('max_price') || request('in_stock'))
                <div class="active-filters mb-4">
                    <span class="filter-label">Active Filters:</span>
                    <div class="filter-tags">
                        @if(request('search'))
                        <span class="filter-tag">
                            Search: {{ request('search') }}
                            <a href="{{ route('shop', array_merge(request()->except('search'), ['page' => 1])) }}" class="remove-filter">×</a>
                        </span>
                        @endif
                          @if(request('category'))
                        <span class="filter-tag">
                            @php
                                $selectedCategory = $categories->first(function($cat) {
                                    return is_object($cat) ? $cat->id == request('category') : $cat['id'] == request('category');
                                });
                                $categoryName = $selectedCategory ? (is_object($selectedCategory) ? $selectedCategory->name : $selectedCategory['name']) : 'Unknown';
                            @endphp
                            Category: {{ $categoryName }}
                            <a href="{{ route('shop', array_merge(request()->except('category'), ['page' => 1])) }}" class="remove-filter">×</a>
                        </span>
                        @endif
                        
                        @if(request('min_price') || request('max_price'))
                        <span class="filter-tag">
                            Price: ${{ request('min_price', $priceRange['min']) }} - ${{ request('max_price', $priceRange['max']) }}
                            <a href="{{ route('shop', array_merge(request()->except(['min_price', 'max_price']), ['page' => 1])) }}" class="remove-filter">×</a>
                        </span>
                        @endif
                        
                        @if(request('in_stock'))
                        <span class="filter-tag">
                            In Stock Only
                            <a href="{{ route('shop', array_merge(request()->except('in_stock'), ['page' => 1])) }}" class="remove-filter">×</a>
                        </span>
                        @endif
                          <a href="{{ route('shop') }}" class="clear-all-filters">Clear All</a>
                    </div>
                </div>
                @endif
                </div>
                <!-- end of sticky-top-bar -->                <div class="row product-lists">
                    @if(session('error') && $products->count() == 0)
                    <div class="col-12 text-center py-5">
                        <div class="no-products-found">
                            <i class="fas fa-database fa-3x mb-3"></i>
                            <h3>Data Error</h3>
                            <p>There was a problem loading the product data. Please try again later.</p>
                            <a href="{{ route('shop') }}" class="boxed-btn mt-3">Refresh Page</a>
                        </div>
                    </div>
                    @else
                    @forelse($products as $product)
                    <div class="col-lg-4 col-md-6 text-center mb-4">
                        <div class="single-product-item">
                            <div class="product-image">
                                <a href="{{ route('product.show', $product->id) }}">
                                    <img src="{{ asset($product->featured_image) }}" alt="{{ $product->name }}" 
                                         onerror="this.onerror=null; this.src='{{ $product->categories->isNotEmpty() ? asset($product->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">
                                    @if(!$product->in_stock)
                                        <span class="out-of-stock">Out of Stock</span>
                                    @endif
                                </a>
                            </div>
                            <div class="product-info-section">
                                <h3><a href="{{ route('product.show', $product->id) }}">{{ $product->name }}</a></h3>
                                <p class="product-price">
                                    @if($product->sale_price && $product->sale_price < $product->price)
                                    <span class="original-price">${{ number_format($product->price, 2) }}</span>
                                    ${{ number_format($product->sale_price, 2) }}
                                    @else
                                    ${{ number_format($product->price, 2) }}
                                    @endif
                                </p>
                                <p class="product-category">
                                    <small>{{ implode(', ', $product->category_names->toArray() ?? ['Uncategorized']) }}</small>
                                </p>
                            </div>
                            <div class="product-action-buttons">                                <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="variant_id" value="">
                                    <button type="submit" class="cart-btn {{ !$product->in_stock ? 'disabled' : '' }}" {{ !$product->in_stock ? 'disabled' : '' }}>
                                        <i class="fas fa-shopping-cart"></i> {{ $product->in_stock ? 'Add to Cart' : 'Out of Stock' }}
                                    </button>
                                </form>@auth
                                <form action="{{ route('wishlist.add') }}" method="POST" class="d-inline mt-2 wishlist-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="wishlist-btn" data-product-id="{{ $product->id }}">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </form>
                                @endauth
                            </div>
                        </div>
                    </div>                    @empty
                    <div class="col-12 text-center py-5">
                        <div class="no-products-found">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <h3>No products found</h3>
                            <p>Try adjusting your search or filter criteria</p>
                            <a href="{{ route('shop') }}" class="boxed-btn mt-3">Clear All Filters</a>
                        </div>
                    </div>
                    @endforelse
                    @endif
                </div>                <!-- Pagination -->
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <div class="pagination-wrap">
                            {{ $products->appends(request()->except('page'))->links('vendor.pagination.simple-tailwind') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end products -->
@endsection

@section('styles')
<style>    
/* Shop Sidebar Styles */    
    .shop-sidebar {
        position: sticky;
        top: 85px;
        padding: 0;
        background-color: #f8f9fa;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        max-height: calc(100vh - 40px);
        overflow: hidden;
        z-index: 90;
        display: flex;
        flex-direction: column;
    }
    
    .sidebar-scrollable {
        position: relative;
        max-height: calc(100vh - 90px);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 25px 25px 25px 25px; /* Restored top padding */
        padding-right: 5px; /* Add space for scrollbar */
        direction: rtl; /* Move scrollbar to left side */
        scrollbar-width: thin;
        scrollbar-color: #f28123 #f1f1f1;
    }
    
    .sidebar-scrollable > * {
        direction: ltr; /* Reset text direction to normal */
    }
      /* Responsive adjustments for mobile */
    @media (max-width: 991px) {
        .shop-sidebar {
            position: relative;
            top: 0;
            margin-bottom: 30px;
            max-height: none;
        }
        
        .sidebar-scrollable {
            max-height: none;
            overflow: visible;
        }
        
        #sticky-top-bar {
            position: sticky;
            top: 85px;
        }
    }
    
    @media only screen and (max-width: 768px) {
        body .shop-sidebar {
            margin-bottom: 20px !important;
            border-radius: 6px !important;
        }
        
        body .sidebar-scrollable {
            padding: 15px 15px 15px 15px !important;
            padding-right: 5px !important;
        }
        
        body .sidebar-section {
            margin-bottom: 20px !important;
        }
        
        body .sidebar-section h4 {
            padding-bottom: 8px !important;
            margin-bottom: 10px !important;
            font-size: 15px !important;
        }
        
        body .category-list li {
            margin-bottom: 5px !important;
        }
        
        body .category-list li a {
            padding: 3px 0 !important;
            font-size: 13px !important;
        }
    }
    /* New responsive styles for active filters and top bar on smaller screens */
    @media only screen and (max-width: 768px) {
        body #sticky-top-bar {
            position: sticky !important;
            top: 0px !important;
            padding: 0px !important;
            margin: 0 !important;
            z-index: 101 !important;
            width: 100% !important;
            max-width: 100% !important;
            text-align: center !important;
        }
        
        body .shop-top-bar {
            padding: 0px 0px !important;
            border-radius: 6px !important;
            margin-bottom: 0px !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        
        body .sticky-active {
            padding: 0px !important;
        }
        
        body .showing-results {
            font-size: 12px !important;
        }
        
        body .showing-results .badge {
            padding: 2px 6px !important;
            font-size: 11px !important;
        }

        body .sort-select-wrapper {
            justify-content: space-between !important;
            font-size: 12px !important;
        }
        
        body .sort-select-wrapper label {
            justify-content: center !important;
            margin: 5px !important;
            font-size: 10px !important;
        }
        
        body .select-container {
            min-width: 100px !important;
        }
        
        body .select-container select {
            padding: 5px 10px !important;
            padding-right: 25px !important;
            font-size: 10px !important;
        }
        
        /* Active filters compact styling */
        body .active-filters {
            text-align: center !important;
            padding: 0px 0px !important;
            border-radius: 6px !important;
            margin-bottom: 0px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            justify-content: center !important;
        }
        
        body .filter-label {
            text-align: center !important;
            align-items: center !important;
            align-self: center !important;
            margin: 0% !important;
            font-size: 12px !important;
        }
        
        body .filter-tag {
            padding: 4px 8px !important;
            font-size: 11px !important;
            margin-bottom: 5px !important;
        }
        
        body .remove-filter {
            margin-left: 5px !important;
        }
        
        body .clear-all-filters {
            padding: 4px 8px !important;
            font-size: 11px !important;
            margin-left: 8px !important;
        }
        
        body .filter-tags {
            gap: 6px !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            width: 100% !important;
        }
    }
    
    /* Style the scrollbar */
    .sidebar-scrollable::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar-scrollable::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 8px;
    }
      
    .sidebar-scrollable::-webkit-scrollbar-thumb {
        background: #f28123;
        border-radius: 8px;
    }
    
    /* Enhanced scrollbar hover effect */
    .sidebar-scrollable::-webkit-scrollbar-thumb:hover {
        background: #e67211;
    }
    
    /* Add scroll hint indicator */
    .sidebar-scrollable::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 30px;
        background: linear-gradient(to top, rgba(248, 249, 250, 0.9), transparent);
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 2;
    }
    
    .sidebar-scrollable.can-scroll::after {
        opacity: 1;
    }
      .sidebar-section {
        margin-bottom: 30px;
    }
      .sidebar-focus {
        box-shadow: 0 0 20px rgba(242, 129, 35, 0.15);
        border-left: 3px solid #f28123;
        margin-left: -3px; /* Compensate for the border to avoid layout shift */
    }
    
    .sidebar-section h4 {
        border-bottom: 2px solid #f28123;
        padding-bottom: 10px;
        margin-bottom: 15px;
        font-size: 18px;
        color: #333;
        font-weight: 600;
    }
    
    /* Category List Styles */
    .category-list {
        list-style-type: none;
        padding-left: 0;
    }
    
    .category-list li {
        margin-bottom: 10px;
        position: relative;
    }
    
    .category-list li a {
        color: #555;
        text-decoration: none;
        transition: all 0.3s;
        display: block;
        padding: 5px 0;
        position: relative;
    }
    
    .category-list li a:hover, 
    .category-list li a.active {
        color: #f28123;
        padding-left: 10px;
    }
    
    .category-list li a:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 1px;
        background: #f28123;
        transition: all 0.3s ease;
    }
    
    .category-list li a:hover:after,
    .category-list li a.active:after {
        width: 100%;
    }
    
    /* Search Box Styles */
    .search-input-group {
        position: relative;
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .search-input-group input {
        border-radius: 30px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        padding-right: 40px;
        width: 100%;
        transition: all 0.3s;
    }
    
    .search-input-group input:focus {
        outline: none;
        border-color: #f28123;
        box-shadow: 0 0 5px rgba(242, 129, 35, 0.3);
    }
    
    .search-btn {
        position: absolute;
        right: 5px;
        background: #f28123;
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    
    .search-btn:hover {
        background: #e67211;
        transform: scale(1.05);
    }
      /* Price Range Styles */
    .price-range-inputs {
        width: 100%;
        margin-top: 15px;
    }    .price-range-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    
    .price-range-label label {
        color: #555;
        font-weight: 500;
        margin: 0;
    }
    
    .price-range-info {
        color: #f28123;
        font-size: 15px;
        font-weight: 600;
        background: rgba(242, 129, 35, 0.1);
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }
      .price-input-group {
        position: relative;
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    /* Price input group margins */    
    .price-symbol {
        position: absolute;
        left: 12px;
        top: 36%;
        transform: translateY(-50%);
        color: #777;
        font-weight: 500;
        line-height: normal;
        font-size: 15px; /* Match the font size with input */
        display: flex;
        align-items: center;
        height: 42px; /* Match the height with input */
    }
    
    @media only screen and (max-width: 768px) {
        body .price-input-group {
            margin-bottom: 10px !important;
        }
        
        body .price-symbol {
            left: 8px !important;
            font-size: 13px !important;
            height: 34px !important;
        }
        
        body .price-range-label {
            margin-bottom: 5px !important;
        }
        
        body .price-range-label label {
            font-size: 12px !important;
        }
        
        body .price-range-info {
            font-size: 12px !important;
            padding: 2px 6px !important;
        }
    }
      .price-input-group input {
        padding: 8px 12px;
        padding-left: 28px; /* Increased padding for $ symbol */
        border-radius: 6px;
        border: 1px solid #ddd;
        width: 100%;
        height: 42px;
        transition: all 0.3s;
        font-size: 15px;
        line-height: normal; /* Ensure consistent line height */
    }
    
    @media only screen and (max-width: 768px) {
        body .price-input-group input {
            padding: 5px 8px !important;
            padding-left: 22px !important;
            height: 34px !important;
            font-size: 13px !important;
        }
    }
    
    .price-input-group input:focus {
        outline: none;
        border-color: #f28123;
        box-shadow: 0 0 5px rgba(242, 129, 35, 0.3);
    }    /* Input focus effects */
    .price-input-group input:hover {
        border-color: #f28123;
    }    
    /* Active Filters Styles */
    .active-filters {
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    
    .filter-label {
        font-weight: bold;
        margin-right: 12px;
        color: #333;
    }
    
    .filter-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }
    
    .filter-tag {
        background-color: #fff;
        border: 1px solid #ddd;
        padding: 8px 12px;
        border-radius: 30px;
        font-size: 13px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .filter-tag:hover {
        border-color: #f28123;
    }
    
    .remove-filter {
        margin-left: 8px;
        color: #999;
        font-weight: bold;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .remove-filter:hover {
        color: #f28123;
    }
    
    .clear-all-filters {
        background-color: #f28123;
        color: white;
        padding: 8px 15px;
        border-radius: 30px;
        font-size: 13px;
        text-decoration: none;
        margin-left: 15px;
        box-shadow: 0 2px 5px rgba(242, 129, 35, 0.3);
        transition: all 0.3s;
    }
    
    .clear-all-filters:hover {
        background-color: #e67211;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(242, 129, 35, 0.4);
    }    /* Shop Top Bar Styles */    
    #sticky-top-bar {
        position: sticky;
        top: 85px;
        z-index: 100;
        background-color: #fff;
        padding: 10px 0;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .shop-top-bar {
        background-color: #f8f9fa;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 15px;
        transition: all 0.3s ease;
    }
    
    .sticky-active {
        background-color: rgba(248, 249, 250, 0.95);
        backdrop-filter: blur(8px);
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-top: 2px solid #f28123;
        padding: 10px 15px;
    }
    
    .has-sticky-bar {
        padding-top: 10px;
    }
    
    /* Enhanced showing results style */
    .showing-results {
        font-size: 15px;
        color: #555;
    }
    
    .showing-results .badge {
        background-color: #f28123;
        color: white;
        padding: 3px 8px;
        border-radius: 20px;
        font-weight: 500;
    }
    
    /* Enhanced sort select style */
    .sort-select-wrapper {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    
    .sort-select-wrapper label {
        margin-right: 10px;
        font-weight: 500;
        color: #333;
    }
    
    .select-container {
        position: relative;
        min-width: 180px;
    }
    
    .select-container select {
        appearance: none;
        -webkit-appearance: none;
        width: 100%;
        padding: 8px 15px;
        padding-right: 30px;
        border: 1px solid #ddd;
        border-radius: 20px;
        background-color: white;
        color: #444;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .select-container select:focus {
        border-color: #f28123;
        outline: none;
        box-shadow: 0 0 0 2px rgba(242, 129, 35, 0.2);
    }
    
    .select-container i {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        color: #f28123;
        pointer-events: none;
    }
      /* Product Item Styles */
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
      .product-info-section {
        display: flex;
        flex-direction: column;
        min-height: 150px; /* Increased min-height for product info area */
        padding: 15px;
    }
    
    .single-product-item h3 {
        padding: 0;
        margin-top: 5px;
        margin-bottom: 10px;
        height: 66px; /* Increased height for product title */
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2; /* Still limit to 2 lines but with more line-height */
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
    
    .original-price {
        text-decoration: line-through;
        color: #999;
        margin-right: 10px;
        font-size: 14px;
    }
      .product-price {
        color: #f28123;
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 8px;
        min-height: 30px; /* Increased min-height for price area to accommodate discounted prices */
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
    
    /* Action Buttons Styles */
    .product-action-buttons {
        padding: 15px;
        margin-top: auto; /* Push buttons to bottom */
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
    }    .wishlist-btn {
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
    
    .wishlist-btn.active {
        background-color: #f28123;
        color: white;
    }
    
    /* Mobile responsive styles for product cards */
    @media only screen and (max-width: 768px) {        body .single-product-item {
            min-height: 420px; /* Increased min-height for mobile to account for taller titles */
        }
        
        body .product-image {
            height: 180px; /* Slightly smaller image height for mobile */
        }
        
        body .product-image img {
            height: 100%;
        }
        
        body .out-of-stock {
            padding: 3px 10px;
            font-size: 10px;
        }
          body .single-product-item h3 {
            height: 56px; /* Increased height for mobile */
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.4; /* Slightly tighter line-height for mobile */
        }
        
        body .product-category {
            height: 18px;
            font-size: 12px;
        }
        
        body .original-price {
            font-size: 12px;
        }
        
        body .product-price {
            height: 22px;
            font-size: 16px;
        }
        
        body .product-action-buttons {
            padding: 10px;
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
        
        /* Adjust grid columns for mobile */
        .row-cols-2 > * {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }
    
    /* Filter Button Styles */
    .filter-btn {
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
        margin-top: 10px;
        box-shadow: 0 2px 5px rgba(242, 129, 35, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .filter-btn i {
        margin-right: 8px;
    }
    
    .filter-btn:hover {
        background-color: #e67211;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(242, 129, 35, 0.4);
    }
    
    /* Pagination Styles */
    .pagination-wrap {
        margin-top: 40px;
        text-align: center;
    }
    
    .custom-pagination {
        display: inline-flex;
        align-items: center;
        background-color: #f8f9fa;
        padding: 10px 15px;
        border-radius: 50px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .page-btn {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 5px;
        background-color: white;
        color: #666;
        border-radius: 50%;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        cursor: pointer;
    }
    
    .page-btn:hover {
        background-color: #f5f5f5;
        color: #f28123;
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .page-btn.active {
        background-color: #f28123;
        color: white;
    }
    
    .page-btn.disabled {
        background-color: #f5f5f5;
        color: #aaa;
        cursor: not-allowed;
        box-shadow: none;
    }
    
    .page-btn.disabled:hover {
        transform: none;
    }
    
    /* Make pagination responsive */
    @media only screen and (max-width: 768px) {
        .pagination-wrap {
            margin-top: 20px;
        }
        
        .custom-pagination {
            padding: 6px 10px;
        }
        
        .page-btn {
            width: 32px;
            height: 32px;
            margin: 0 3px;
            font-size: 12px;
        }
    }
    
    /* Mobile responsive styles for filter button */
    @media only screen and (max-width: 768px) {
        body .filter-btn {
            padding: 8px 15px;
            font-size: 13px;
            margin-top: 8px;
        }
    }
</style>
@endsection
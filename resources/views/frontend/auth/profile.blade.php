@extends('frontend.layouts.master')

@section('title', 'My Account - PangAIaShop')

@section('styles')
    <style>
        .user-profile {
            padding: 40px 0;
        }

        .profile-box {
            background-color: #f5f5f5;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 20px;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info h3 {
            margin: 0 0 10px;
            color: #051922;
        }

        .profile-info p {
            margin: 0;
            color: #666;
        }

        .profile-section {
            margin-bottom: 40px;
        }

        .profile-section h4 {
            color: #F28123;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .profile-form .form-group {
            margin-bottom: 20px;
        }

        .profile-form label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .profile-form input,
        .profile-form select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .profile-form .btn-orange {
            background-color: #F28123;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .profile-form .btn-orange:hover {
            background-color: #e67612;
        }

        .order-item {
            background-color: #fff;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .order-id {
            font-weight: 600;
        }

        .order-date {
            color: #666;
        }

        .order-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .order-status.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .order-status.processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .order-status.completed {
            background-color: #d4edda;
            color: #155724;
        }

        .order-status.cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .order-total {
            font-weight: 600;
        }

        .order-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .order-actions a {
            color: #F28123;
            text-decoration: none;
        }

        .order-actions a:hover {
            text-decoration: underline;
        }

        .profile-tabs {
            display: flex;
            flex-direction: column;
            margin-bottom: 30px;
            border-left: 3px solid #ddd;
        }

        .profile-tabs a {
            padding: 15px;
            color: #666;
            text-decoration: none;
            border-left: 3px solid transparent;
            margin-left: -3px;
            transition: all 0.3s;
        }

        .profile-tabs a:hover {
            color: #F28123;
            background-color: rgba(242, 129, 35, 0.05);
        }

        .profile-tabs a.active {
            color: #F28123;
            border-left-color: #F28123;
            background-color: rgba(242, 129, 35, 0.1);
            font-weight: 600;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .text-orange {
            color: #F28123;
        }

        .btn-orange {
            background-color: #F28123;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-orange:hover {
            background-color: #e67612;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Country select dropdown styles */
        .country-select-container {
            position: relative;
        }

        .country-search-input {
            display: block;
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .country-dropdown {
            position: absolute;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
            z-index: 10;
            display: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .country-dropdown.show {
            display: block;
        }

        .country-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .country-item:hover {
            background: #f5f5f5;
        }

        .country-code {
            display: inline-block;
            min-width: 30px;
            font-weight: bold;
            color: #666;
        }

        .alert-success {
            transition: opacity 0.5s ease-in-out;
        }

        /* Fix for dropdown height */
        select.form-control {
            height: auto !important;
            min-height: 45px;
        }

        /* Remove browser default styling */
        select.country-select {
            line-height: 1.5;
        }

        /* Fix option height */
        select.country-select option {
            padding: 10px 15px;
            min-height: 30px;
            line-height: 1.5;
        }

        /* Additional styles for vertical tabs layout */
        .profile-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        .profile-box {
            height: 100%;
        }

        .tab-content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        /* Product card styles from shop page - 100% identical to shop.blade.php */
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

        .single-product-item h3 a:hover {
            color: #f28123;
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

        .product-category {
            color: #666;
            margin-top: 0;
            margin-bottom: 5px;
            padding: 0;
            height: 20px;
            /* Fixed height for category area */
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        /* Action Buttons Styles */
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .wishlist-btn:hover {
            background-color: #f28123;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(242, 129, 35, 0.4);
        }

        .wishlist-btn.active {
            background-color: #f28123;
            color: white;
        }

        .wishlist-btn.active:hover {
            background-color: #e67211;
        }

        /* Toast notification styles */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 14px;
            opacity: 0;
            transform: translateY(100%);
            transition: all 0.3s ease;
            z-index: 10000;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
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
                        <h1>My Account</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end breadcrumb section -->

    <!-- user profile section -->
    <div class="user-profile mt-150 mb-150">
        <div class="container">
            @if (session('success'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-4">
                    <div class="profile-box">
                        <div class="profile-header">                            <div class="profile-avatar">
                                <img src="{{ $user->avatar_url ? (str_starts_with($user->avatar_url, 'http') ? $user->avatar_url : asset('storage/' . $user->avatar_url)) : 'https://via.placeholder.com/200x200?text=User' }}"
                                    alt="Profile Picture">
                            </div>
                            <div class="profile-info">
                                <h3>{{ $user->username }}</h3>
                                <p>{{ $user->email }}</p>
                            </div>
                        </div>                        <div class="profile-tabs">
                            <a href="#profile-info" class="tab-link active">Profile</a>
                            <a href="#orders" class="tab-link">Orders</a>
                            <a href="#wishlist" class="tab-link">Wishlist</a>
                            {{-- <a href="#cart" class="tab-link">Cart</a> --}}
                            <a href="#settings" class="tab-link">Settings</a>
                        </div>
                        <div class="mt-4 text-center">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-orange">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="profile-box tab-content active" id="profile-info">
                        <div class="profile-section">
                            <h4>Personal Information</h4>                            <form action="{{ route('profile.update') }}" method="POST" class="profile-form" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="form_type" value="personal_info">                                @if ($errors->any() && 
                                    ($errors->has('username') || 
                                     $errors->has('email') || 
                                     $errors->has('phone_number') || 
                                     $errors->has('avatar')))
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @if ($errors->has('username'))
                                                <li>{{ $errors->first('username') }}</li>
                                            @endif
                                            @if ($errors->has('email'))
                                                <li>{{ $errors->first('email') }}</li>
                                            @endif
                                            @if ($errors->has('phone_number'))
                                                <li>{{ $errors->first('phone_number') }}</li>
                                            @endif
                                            @if ($errors->has('avatar'))
                                                <li>{{ $errors->first('avatar') }}</li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif<div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" id="username" name="username"
                                                value="{{ old('username', $user->username) }}" required>
                                            @error('username')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                                required>
                                            @error('email')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="text" id="phone" name="phone_number"
                                                value="{{ old('phone_number', $user->phone_number) }}">
                                            @error('phone_number')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="avatar_url">Avatar URL</label>
                                            <input type="url" id="avatar_url" name="avatar_url"
                                                value="{{ old('avatar_url', $user->avatar_url) }}" 
                                                placeholder="Enter image URL (optional)">
                                            @error('avatar_url')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="avatar">Or Upload Profile Picture</label>
                                            <input type="file" id="avatar" name="avatar" accept="image/*">
                                            @error('avatar')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                            @if($user->avatar_url)
                                                <small class="form-text text-muted">Current: <a href="{{ str_starts_with($user->avatar_url, 'http') ? $user->avatar_url : asset('storage/' . $user->avatar_url) }}" target="_blank">View current image</a></small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn-orange">Update Profile</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="profile-section">
                            <h4>Shipping Address</h4>
                            <form action="{{ route('profile.shipping.update') }}" method="POST" class="profile-form">
                                @csrf
                                @method('PATCH')

                                @if (
                                    $errors->any() &&
                                        ($errors->has('street') ||
                                            $errors->has('city') ||
                                            $errors->has('state') ||
                                            $errors->has('postal_code') ||
                                            $errors->has('country')))
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="street">Street Address</label>
                                            <input type="text" id="street" name="street"
                                                value="{{ old('street', $user->street) }}">
                                            @error('street')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <input type="text" id="city" name="city"
                                                value="{{ old('city', $user->city) }}">
                                            @error('city')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="state">State/Province</label>
                                            <input type="text" id="state" name="state"
                                                value="{{ old('state', $user->state) }}">
                                            @error('state')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="postal_code">Postal Code</label>
                                            <input type="text" id="postal_code" name="postal_code"
                                                value="{{ old('postal_code', $user->postal_code) }}">
                                            @error('postal_code')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="country">Country</label>
                                            <select id="country" name="country" class="form-control country-select"
                                                data-current="{{ old('country', $user->country) }}">
                                                <option value="">Select a country</option>
                                                <!-- Country options will be populated by JavaScript -->
                                            </select>
                                            {{-- <small class="form-text text-muted">Start typing to search for a country</small> --}}
                                            @error('country')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-orange"
                                            style="padding: 12px 25px; font-weight: bold;">
                                            <i class="fa fa-save"></i> Save Shipping Address
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="profile-box tab-content" id="orders">
                        <div class="profile-section">
                            <h4>Recent Orders</h4>

                            @if ($orders->count() > 0)
                                @foreach ($orders as $order)
                                    <div class="order-item">
                                        <div class="order-header">
                                            <span class="order-id">Order #{{ $order->id }}</span>
                                            <span class="order-date">{{ $order->order_date->format('M d, Y') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="order-status {{ strtolower($order->status) }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                            <span class="order-total">${{ number_format($order->total_amount, 2) }}</span>
                                        </div>
                                        <div class="order-actions">
                                            <a href="{{ route('orders.show', $order->id) }}">View Details</a>                                            @if ($order->status == 'pending')
                                                <button type="button" class="btn btn-link text-danger p-0" data-toggle="modal" data-target="#cancelOrderModalProfile{{ $order->id }}">
                                                    Cancel Order
                                                </button>
                                                
                                                <!-- Cancel Order Modal for Profile Page -->
                                                <div class="modal fade" id="cancelOrderModalProfile{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="cancelOrderModalProfileLabel{{ $order->id }}" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content" style="border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                                                            <div class="modal-header" style="background-color: #dc3545; color: white; border-bottom: none;">
                                                                <h5 class="modal-title" id="cancelOrderModalProfileLabel{{ $order->id }}">Cancel Order #{{ $order->order_number ?? $order->id }}</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="source" value="profile">
                                                                <div class="modal-body" style="padding: 25px;">
                                                                    <div class="text-center mb-4">
                                                                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
                                                                        <h5>Are you sure you want to cancel this order?</h5>
                                                                        <p class="text-muted">This action cannot be undone.</p>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="reasonProfile{{ $order->id }}" style="font-weight: 600; color: #333;">Reason for cancellation (optional):</label>
                                                                        <textarea name="reason" id="reasonProfile{{ $order->id }}" class="form-control" rows="3" style="border-radius: 5px; border: 1px solid #ddd;"></textarea>
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
                                    </div>
                                @endforeach

                                <div class="text-center mt-4">
                                    <a href="{{ route('orders') }}" class="btn-orange">View All Orders</a>
                                </div>
                            @else
                                <p>You haven't placed any orders yet.</p>
                            @endif
                        </div>
                    </div>
                    <div class="profile-box tab-content" id="wishlist">
                        <div class="profile-section">
                            <h4>My Wishlist</h4>

                            <div id="wishlist-items">
                                @if (isset($wishlistItems) && $wishlistItems->count() > 0)
                                    <div class="row">
                                        @foreach ($wishlistItems as $item)
                                            <div class="col-md-6 mb-4">
                                                <div class="single-product-item">
                                                    <div class="product-image">
                                                        <a href="{{ route('product.show', $item->product->id) }}">                                                            <img src="{{ $item->product->featured_image }}"
                                                                alt="{{ $item->product->name }}"
                                                                onerror="this.onerror=null; this.src='{{ $item->product->categories->isNotEmpty() ? asset($item->product->categories->first()->image_url ?? 'assets/img/categories/default-category.jpg') : asset('assets/img/categories/default-category.jpg') }}'">                                                            @php
                                                                $inStock = true;
                                                                if ($item->product->inventory) {
                                                                    $productInventory = $item->product->inventory;
                                                                    $inStock = $productInventory->quantity > 0;
                                                                } else {
                                                                    $inStock = false;
                                                                }
                                                            @endphp

                                                            @if (!$inStock)
                                                                <span class="out-of-stock">Out of Stock</span>
                                                            @endif
                                                        </a>
                                                    </div>
                                                    <div class="product-info-section text-center">
                                                        <h3><a
                                                                href="{{ route('product.show', $item->product->id) }}">{{ $item->product->name }}</a>
                                                        </h3>
                                                        <p class="product-price text-center">
                                                            @if ($item->product->sale_price && $item->product->sale_price < $item->product->price)
                                                                <span
                                                                    class="original-price">${{ number_format($item->product->price, 2) }}</span>
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
                                                        <form action="{{ route('cart.add') }}" method="POST"
                                                            class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="product_id"
                                                                value="{{ $item->product_id }}">
                                                            <input type="hidden" name="quantity" value="1">
                                                            <input type="hidden" name="variant_id" value="">
                                                            <button type="submit"
                                                                class="cart-btn {{ !$inStock ? 'disabled' : '' }}"
                                                                {{ !$inStock ? 'disabled' : '' }}>
                                                                <i class="fas fa-shopping-cart"></i>
                                                                {{ $inStock ? 'Add to Cart' : 'Out of Stock' }}
                                                            </button>
                                                        </form>
                                                        <div class="d-flex justify-content-center mt-2">
                                                            <form action="{{ route('wishlist.remove') }}" method="POST"
                                                                class="d-inline wishlist-form">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="product_id"
                                                                    value="{{ $item->product_id }}">
                                                                <button type="submit" class="wishlist-btn active"
                                                                    data-product-id="{{ $item->product_id }}">
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
                                    <div class="text-center py-4">
                                        <p>Your wishlist is empty.</p>
                                        <a href="{{ route('shop') }}" class="btn-orange">Shop Now</a>
                                    </div>
                                @endif
                            </div>

                            <div class="text-center mt-4">
                                <a href="{{ route('wishlist') }}" class="btn-orange">View Full Wishlist</a>
                            </div>
                        </div>
                    </div>

                    <div class="profile-box tab-content" id="cart">
                        <div class="profile-section">
                            <h4>My Cart</h4>
                            <div class="mt-3">
                                <a href="{{ route('cart') }}" class="btn-orange">Go to Cart Page</a>
                            </div>
                        </div>
                    </div>                    <div class="profile-box tab-content" id="settings">
                        <div class="profile-section">
                            <h4>Change Password</h4>
                            <form action="{{ route('password.change.update') }}" method="POST" class="profile-form">
                                @csrf
                                @method('PATCH')

                                @if ($errors->any() && 
                                    ($errors->has('current_password') || 
                                     $errors->has('password') || 
                                     $errors->has('password_confirmation')))
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="current_password">Current Password</label>
                                            <input type="password" id="current_password" name="current_password"
                                                required>
                                            @error('current_password')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">New Password</label>
                                            <input type="password" id="password" name="password" required>
                                            @error('password')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_confirmation">Confirm New Password</label>
                                            <input type="password" id="password_confirmation"
                                                name="password_confirmation" required>
                                            @error('password_confirmation')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn-orange">Change Password</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if (config('app.debug'))
                    <div class="profile-section">
                        <h4>Debug Information</h4>
                        <div class="card">
                            <div class="card-body">
                                <h5>User Model Data:</h5>
                                <ul>
                                    <li><strong>ID:</strong> {{ $user->id }}</li>
                                    <li><strong>Username:</strong> {{ $user->username }}</li>
                                    <li><strong>Email:</strong> {{ $user->email }}</li>
                                    <li><strong>Street:</strong> {{ $user->street ?: 'Not set' }}</li>
                                    <li><strong>City:</strong> {{ $user->city ?: 'Not set' }}</li>
                                    <li><strong>State:</strong> {{ $user->state ?: 'Not set' }}</li>
                                    <li><strong>Postal Code:</strong> {{ $user->postal_code ?: 'Not set' }}</li>
                                    <li><strong>Country:</strong> {{ $user->country ?: 'Not set' }}</li>
                                    <li><strong>Last Updated:</strong> {{ $user->updated_at }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
    <!-- end user profile section -->

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/countries.js') }}"></script>
    <script src="{{ asset('assets/js/country-dropdown.js') }}"></script>
    <script src="{{ asset('assets/js/dropdown-scroll-fix.js') }}"></script>
    <script src="{{ asset('assets/js/wishlist-common.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize wishlist functionality
            initializeWishlistFunctionality();

            // Tab functionality
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            function activateTab(tabId) {
                // Remove active class from all links and contents
                tabLinks.forEach(link => link.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Add active class to matching link and content
                const targetTab = document.getElementById(tabId);
                const targetLink = document.querySelector(`a[href="#${tabId}"]`);

                if (targetTab && targetLink) {
                    targetTab.classList.add('active');
                    targetLink.classList.add('active');
                }
            } // Handle tab clicks
            tabLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tabId = link.getAttribute('href').substring(1);
                    // Instead of changing window.location.hash, use history.pushState
                    history.pushState(null, '', `#${tabId}`);
                    activateTab(tabId);
                });
            }); // Check for URL hash on page load without scrolling
            if (window.location.hash) {
                const tabId = window.location.hash.substring(1);
                activateTab(tabId);
                // Prevent scroll
                window.scrollTo(window.scrollX, window.scrollY);
            } else {
                // Show first tab by default
                const firstTabId = tabLinks[0].getAttribute('href').substring(1);
                activateTab(firstTabId);
            }

            // Also prevent scroll on popstate (browser back/forward)
            window.addEventListener('popstate', () => {
                if (window.location.hash) {
                    const tabId = window.location.hash.substring(1);
                    activateTab(tabId);
                    // Prevent scroll
                    window.scrollTo(window.scrollX, window.scrollY);
                }
            });

            // Auto hide alerts after 5 seconds
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

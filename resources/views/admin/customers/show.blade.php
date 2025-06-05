@extends('admin.layouts.app')

@section('title', 'Customer Details - ' . ($customer->username ?? 'Customer'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Customer Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">{{ $customer->username ?? 'Customer #' . $customer->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Back to Customers
            </a>
            <a href="{{ route('admin.reviews.index', ['search' => $customer->email]) }}" class="btn btn-outline-info mr-2">
                <i class="fas fa-star"></i> View All Reviews
            </a>
            {{-- <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Customer
            </a> --}}
        </div>
    </div>

    <div class="row">
        <!-- Customer Profile Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-primary">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-user"></i> Customer Profile</h6>
                </div>
                <div class="card-body">                    <div class="text-center mb-4">
                        @include('admin.components.image-with-fallback', [
                            'src' => $customer->avatar_url,
                            'alt' => $customer->username ?? 'Customer',
                            'type' => 'profile',
                            'class' => 'rounded-circle img-fluid mb-3 border shadow',
                            'style' => 'width: 120px; height: 120px; object-fit: cover;'
                        ])
                        <h4 class="font-weight-bold text-gray-800 mb-1">{{ $customer->username ?? 'No Username' }}</h4>
                        <p class="text-muted mb-2">Customer ID: #{{ $customer->id }}</p>
                        <div class="mb-3">
                            @if(($customer->account_status ?? 'active') === 'active')
                                <span class="badge badge-success badge-pill px-3 py-2">
                                    <i class="fas fa-check-circle"></i> Active Account
                                </span>
                            @elseif($customer->account_status === 'suspended')
                                <span class="badge badge-warning badge-pill px-3 py-2">
                                    <i class="fas fa-pause-circle"></i> Suspended
                                </span>
                            @else
                                <span class="badge badge-danger badge-pill px-3 py-2">
                                    <i class="fas fa-times-circle"></i> Inactive
                                </span>
                            @endif
                            
                            {{-- @if($customer->is_verified ?? true)
                                <span class="badge badge-info badge-pill px-3 py-2 ml-1">
                                    <i class="fas fa-shield-alt"></i> Verified
                                </span>
                            @else
                                <span class="badge badge-warning badge-pill px-3 py-2 ml-1">
                                    <i class="fas fa-exclamation-triangle"></i> Unverified
                                </span>
                            @endif --}}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="font-weight-bold text-gray-600" style="width: 40%;"><i class="fas fa-envelope fa-fw text-primary"></i> Email:</td>
                                    <td>{{ $customer->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-gray-600"><i class="fas fa-phone fa-fw text-success"></i> Phone:</td>
                                    <td>{{ $customer->phone_number ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-gray-600"><i class="fas fa-calendar-alt fa-fw text-info"></i> Joined:</td>
                                    <td>{{ $customer->created_at ? $customer->created_at->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-gray-600"><i class="fas fa-clock fa-fw text-warning"></i> Last Login:</td>
                                    <td>{{ $customer->last_login ? $customer->last_login->format('M d, Y h:i A') : 'Never' }}</td>
                                </tr>
                                @if($customer->street || $customer->city || $customer->state)
                                <tr>
                                    <td class="font-weight-bold text-gray-600"><i class="fas fa-map-marker-alt fa-fw text-danger"></i> Address:</td>
                                    <td>
                                        @if($customer->street){{ $customer->street }}<br>@endif
                                        @if($customer->city || $customer->state)
                                            {{ $customer->city }}@if($customer->city && $customer->state), @endif{{ $customer->state }}
                                            @if($customer->postal_code) {{ $customer->postal_code }}@endif
                                        @endif
                                        @if($customer->country)<br>{{ $customer->country }}@endif
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>            <!-- Customer Statistics Overview -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-info">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-chart-bar"></i> Customer Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-right">
                            <div class="p-2">
                                <h3 class="text-primary mb-1">{{ $orderStats['total'] ?? 0 }}</h3>
                                <small class="text-muted font-weight-bold">Total Orders</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2">
                                <h3 class="text-success mb-1">${{ number_format($orderStats['total_spent'] ?? 0, 2) }}</h3>
                                <small class="text-muted font-weight-bold">Total Spent</small>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row text-center">
                        <div class="col-6 border-right">
                            <div class="p-2">
                                <h3 class="text-warning mb-1">{{ $reviewStats['total'] ?? 0 }}</h3>
                                <small class="text-muted font-weight-bold">Reviews Written</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2">
                                <h3 class="text-info mb-1">
                                    @if(($reviewStats['average_rating'] ?? 0) > 0)
                                        {{ number_format($reviewStats['average_rating'], 1) }}/5
                                        <div class="mt-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= round($reviewStats['average_rating']))
                                                    <i class="fas fa-star text-warning" style="font-size: 12px;"></i>
                                                @else
                                                    <i class="far fa-star text-muted" style="font-size: 12px;"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    @else
                                        -
                                    @endif
                                </h3>
                                <small class="text-muted font-weight-bold">Avg Rating</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-xl-8 col-lg-7">
            <!-- Order Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orderStats['total'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orderStats['completed'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orderStats['pending'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Cancelled</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orderStats['cancelled'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Reviews</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reviewStats['total'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-star fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reviewStats['approved'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reviewStats['pending'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reviewStats['rejected'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-times fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            <!-- Recent Orders -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-gradient-primary">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-shopping-cart"></i> Recent Orders</h6>
                    <a href="{{ route('admin.orders.index', ['search' => $customer->email]) }}" class="btn btn-sm btn-light">
                        <i class="fas fa-external-link-alt"></i> View All Orders
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentOrders) && $recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold text-primary">#{{ $order->id }}</span>
                                            </td>                            <td>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $order->items->count() ?? 0 }} items</span>
                            </td>
                            <td>
                                <span class="font-weight-bold text-success">${{ number_format($order->total_amount ?? 0, 2) }}</span>
                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match($order->order_status ?? 'pending') {
                                                        'delivered', 'completed' => 'badge-success',
                                                        'cancelled', 'refunded' => 'badge-danger',
                                                        'processing', 'shipped' => 'badge-info',
                                                        'pending' => 'badge-warning',
                                                        default => 'badge-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    {{ ucfirst($order->order_status ?? 'pending') }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Orders Found</h5>
                            <p class="text-muted">This customer hasn't placed any orders yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-gradient-warning">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-star"></i> Recent Reviews</h6>
                    <a href="{{ route('admin.reviews.index', ['search' => $customer->email]) }}" class="btn btn-sm btn-light">
                        <i class="fas fa-star"></i> View All Reviews
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentReviews) && $recentReviews->count() > 0)
                        @foreach($recentReviews as $review)
                            <div class="border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        @if($review->product)
                                            <h6 class="mb-1">
                                                <a href="{{ route('admin.products.show', $review->product->id) }}" class="text-decoration-none text-primary">
                                                    <i class="fas fa-box"></i> {{ $review->product->name }}
                                                </a>
                                            </h6>
                                        @else
                                            <h6 class="mb-1 text-muted">
                                                <i class="fas fa-trash"></i> Product Deleted
                                            </h6>
                                        @endif
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="mr-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= ($review->rating ?? 0))
                                                        <i class="fas fa-star text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-muted"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span class="badge badge-primary">{{ $review->rating ?? 0 }}/5</span>
                                        </div>
                                    </div>
                                    <div class="text-right ml-3">
                                        @php
                                            $moderationClass = match($review->moderation_status ?? 'pending') {
                                                'approved' => 'badge-success',
                                                'rejected' => 'badge-danger',
                                                'pending' => 'badge-warning',
                                                default => 'badge-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $moderationClass }} mb-1">
                                            {{ ucfirst($review->moderation_status ?? 'pending') }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> 
                                            {{ $review->created_at ? $review->created_at->format('M d, Y') : 'N/A' }}
                                        </small>
                                    </div>
                                </div>
                                @if($review->comment)
                                    <p class="mb-2 text-gray-700">
                                        <i class="fas fa-quote-left text-muted"></i>
                                        {{ Str::limit($review->comment, 200) }}
                                        <i class="fas fa-quote-right text-muted"></i>
                                    </p>
                                @endif
                                <div class="text-right">
                                    <a href="{{ route('admin.reviews.show', $review->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View Full Review
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Reviews Found</h5>
                            <p class="text-muted">This customer hasn't written any reviews yet.</p>
                            <a href="{{ route('admin.reviews.index', ['search' => $customer->email]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-star"></i> Check All Reviews
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    }
    
    .card {
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0,123,255,.075);
    }
    
    .badge-pill {
        font-size: 0.75rem;
    }
    
    .breadcrumb {
        background-color: transparent;
        padding: 0;
        margin-bottom: 0;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        color: #6c757d;
    }
    
    .breadcrumb-item a {
        color: #007bff;
        text-decoration: none;
    }
    
    .breadcrumb-item a:hover {
        text-decoration: underline;
    }
    
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    
    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }
    
    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
    
    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    
    .border-left-danger {
        border-left: 4px solid #e74a3b !important;
    }
</style>
@endpush

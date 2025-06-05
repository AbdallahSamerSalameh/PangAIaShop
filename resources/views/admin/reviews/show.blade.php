@extends('admin.layouts.app')

@section('title', 'Review Details')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Review Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">Reviews</a></li>
                    <li class="breadcrumb-item active">Review #{{ $review->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex">
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Back to Reviews
            </a>
            @if($review->product)
                <a href="{{ route('admin.products.show', $review->product->id) }}" class="btn btn-outline-info mr-2">
                    <i class="fas fa-box"></i> View Product
                </a>
            @endif
            @if($review->user)
                <a href="{{ route('admin.customers.show', $review->user->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-user"></i> View Customer
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Review Details Card -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-star"></i> Review Information
                    </h6>
                    <div class="d-flex align-items-center">
                        @if($review->moderation_status === 'pending')
                            <span class="badge badge-warning mr-2">Pending Approval</span>
                        @elseif($review->moderation_status === 'approved')
                            <span class="badge badge-success mr-2">Approved</span>
                        @elseif($review->moderation_status === 'rejected')
                            <span class="badge badge-danger mr-2">Rejected</span>
                        @endif
                        
                        <!-- Quick Status Actions -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="fas fa-cog"></i> Actions
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                @if($review->moderation_status !== 'approved')
                                    <form method="POST" action="{{ route('admin.reviews.update-status', $review->id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="moderation_status" value="approved">
                                        <button type="submit" class="dropdown-item text-success">
                                            <i class="fas fa-check fa-sm mr-2"></i> Approve
                                        </button>
                                    </form>
                                @endif
                                @if($review->moderation_status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.reviews.update-status', $review->id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="moderation_status" value="rejected">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-times fa-sm mr-2"></i> Reject
                                        </button>
                                    </form>
                                @endif
                                @if($review->moderation_status !== 'pending')
                                    <form method="POST" action="{{ route('admin.reviews.update-status', $review->id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="moderation_status" value="pending">
                                        <button type="submit" class="dropdown-item text-warning">
                                            <i class="fas fa-clock fa-sm mr-2"></i> Set Pending
                                        </button>
                                    </form>
                                @endif
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('admin.reviews.destroy', $review->id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger"
                                            onclick="return confirm('Are you sure you want to delete this review? This action cannot be undone.')">
                                        <i class="fas fa-trash fa-sm mr-2"></i> Delete Review
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Rating Display -->
                    <div class="mb-4">
                        <h5 class="mb-2">Rating</h5>
                        <div class="d-flex align-items-center">
                            <div class="rating-stars mr-3">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i class="fas fa-star text-warning"></i>
                                    @else
                                        <i class="far fa-star text-muted"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="badge badge-primary">{{ $review->rating }}/5</span>
                        </div>
                    </div>                    <!-- Review Comment -->
                    <div class="mb-4">
                        <h5 class="mb-2">Review Comment</h5>
                        <div class="p-3 bg-light rounded">
                            <p class="mb-0 text-gray-800">{{ $review->comment }}</p>
                        </div>
                    </div>

                    <!-- Customer Review Info -->
                    @if($review->user)
                        <div class="mb-4">
                            <h5 class="mb-3">Reviewed By</h5>
                            <div class="d-flex align-items-center p-3 bg-white border rounded">
                                <div class="mr-3">
                                    @php
                                        $userImage = $review->user->profile_image ? asset('storage/' . $review->user->profile_image) : ($review->user->avatar_url ?? null);
                                    @endphp
                                    @include('admin.components.image-with-fallback', [
                                        'src' => $userImage,
                                        'alt' => $review->user->username ?? $review->user->name ?? 'Customer',
                                        'type' => 'profile',
                                        'class' => 'rounded-circle',
                                        'style' => 'width: 50px; height: 50px; object-fit: cover;'
                                    ])
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 font-weight-bold text-gray-800">
                                        {{ $review->user->username ?? $review->user->name ?? 'Unknown User' }}
                                    </h6>
                                    <p class="mb-0 text-muted small">{{ $review->user->email }}</p>
                                    <small class="text-muted">Customer ID: #{{ $review->user->id }}</small>
                                </div>
                                <div class="text-right">
                                    <a href="{{ route('admin.customers.show', $review->user->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Review Metadata -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Submitted:</strong>
                                <br>
                                <span class="text-muted">{{ $review->created_at->format('M d, Y g:i A') }}</span>
                                <br>
                                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($review->moderated_at)
                                <div class="mb-3">
                                    <strong>Moderated:</strong>
                                    <br>
                                    <span class="text-muted">{{ $review->moderated_at->format('M d, Y g:i A') }}</span>
                                    <br>
                                    <small class="text-muted">{{ $review->moderated_at->diffForHumans() }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product & Customer Info Sidebar -->
        <div class="col-xl-4 col-lg-5">
            <!-- Product Information -->
            @if($review->product)
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-gradient-primary">
                        <h6 class="m-0 font-weight-bold text-white">
                            <i class="fas fa-box"></i> Product Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @php
                                $productImage = null;
                                $categoryFallback = null;
                                
                                // Get product image
                                if($review->product->images && $review->product->images->count() > 0) {
                                    $primaryImage = $review->product->images->where('is_primary', true)->first();
                                    $productImage = $primaryImage ? $primaryImage->image_url : $review->product->images->first()->image_url;
                                }
                                
                                // Get category fallback
                                if($review->product->categories && $review->product->categories->count() > 0 && $review->product->categories->first()->image_url) {
                                    $categoryFallback = $review->product->categories->first()->image_url;
                                }
                            @endphp
                            
                            @include('admin.components.image-with-fallback', [
                                'src' => $productImage,
                                'alt' => $review->product->name,
                                'type' => 'product',
                                'fallbacks' => [$categoryFallback],
                                'class' => 'img-fluid rounded mb-3',
                                'style' => 'max-height: 200px; object-fit: cover;'
                            ])
                        </div>
                        
                        <h5 class="font-weight-bold text-gray-800 mb-2">{{ $review->product->name }}</h5>
                        <p class="text-muted mb-2">Product ID: #{{ $review->product->id }}</p>
                        <p class="text-primary mb-3">${{ number_format($review->product->price, 2) }}</p>
                        
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('admin.products.show', $review->product->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Product
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="card shadow mb-4">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                        <p class="text-muted">Product information not available</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .rating-stars {
        font-size: 1.2rem;
    }
    
    .card-header.bg-gradient-primary {
        background: linear-gradient(45deg, #4e73df 0%, #224abe 100%);
    }
    
    .card-header.bg-gradient-info {
        background: linear-gradient(45deg, #36b9cc 0%, #258391 100%);
    }
</style>
@endpush

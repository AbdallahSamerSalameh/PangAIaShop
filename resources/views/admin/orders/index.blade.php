@extends('admin.layouts.app')

@section('title', 'Orders Management')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Orders Management</h1>
    {{-- <div>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm mr-2">
            <i class="fas fa-download fa-sm text-white-50"></i> Export Orders
        </a>
    </div> --}}
</div>

<!-- Order Statistics Slider -->
<div class="position-relative mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="text-gray-800 font-weight-bold mb-0">Order Statistics</h6>
        <div class="stats-slider-controls">
            <button class="btn btn-sm btn-outline-secondary rounded-circle mr-2" id="statsPrev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-circle" id="statsNext">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
    
    <div class="stats-slider-container">
        <div class="stats-slider" id="statsSlider">
            <div class="stats-card">
                <div class="card border-left-primary shadow h-100 py-2 card-hover">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Orders</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalOrders }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="card border-left-success shadow h-100 py-2 card-hover">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Delivered Orders</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $deliveredOrders }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="card border-left-warning shadow h-100 py-2 card-hover">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Processing Orders</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $processingOrders }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cog fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="card border-left-info shadow h-100 py-2 card-hover">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Shipped Orders</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $shippedOrders }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="card border-left-danger shadow h-100 py-2 card-hover">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Cancelled Orders</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $cancelledOrders }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Slider Indicators -->
    <div class="d-flex justify-content-center mt-3">
        <div class="stats-indicators" id="statsIndicators">
            <span class="indicator active" data-slide="0"></span>
            <span class="indicator" data-slide="1"></span>
            <span class="indicator" data-slide="2"></span>
            <span class="indicator" data-slide="3"></span>
            <span class="indicator" data-slide="4"></span>
        </div>
    </div>
</div>

<!-- Status Filter -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-body py-3">
                <form action="{{ route('admin.orders.index') }}" method="GET" class="form-inline">
                    <label class="mr-2 mb-0 font-weight-bold">Filter by Status:</label>
                    <select name="status" class="form-control form-control-sm mr-3" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    
                    @if(request('status'))
                    <a href="{{ route('admin.orders.index', array_merge(request()->except(['status', 'page']), [])) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear Filter
                    </a>
                    @endif

                    <!-- Preserve other parameters -->
                    @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    @endif
                    @if(request('quick_search'))
                    <input type="hidden" name="quick_search" value="{{ request('quick_search') }}">
                    @endif
                    @foreach(request()->except(['status', 'page', 'per_page', 'quick_search']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Orders List</h6>
    </div>
    <div class="card-body">
        <!-- Show Entries Control and Search -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <form id="perPageForm" action="{{ route('admin.orders.index') }}" method="GET" class="form-inline">
                    <label class="mr-2 mb-0">Show</label>
                    <select name="per_page" class="form-control form-control-sm" style="width: auto;"
                        onchange="document.getElementById('perPageForm').submit()">
                        <option value="10" {{ request('per_page', 10)==10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 10)==25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 10)==50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 10)==100 ? 'selected' : '' }}>100</option>
                    </select>
                    <label class="ml-2 mb-0">entries</label>                    @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('quick_search'))
                    <input type="hidden" name="quick_search" value="{{ request('quick_search') }}">
                    @endif
                    @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @foreach(request()->except(['per_page', 'page', 'search', 'quick_search', 'status']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>
            </div>            <div class="col-md-6">
                <form id="quickSearchForm" action="{{ route('admin.orders.index') }}" method="GET" class="form-inline justify-content-end">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="quick_search" class="form-control" placeholder="Quick search..."
                            value="{{ request('quick_search') }}"
                            onchange="document.getElementById('quickSearchForm').submit()">
                    </div>                    @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    @endif
                    @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @foreach(request()->except(['per_page', 'page', 'quick_search', 'status']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>            </div>
        </div>        <!-- Active Filters Display -->
        @if(request('search') || request('quick_search') || request('status'))
        <div class="mb-3">
            <div class="d-flex flex-wrap align-items-center">
                <span class="mr-2">Active filters:</span>
                @if(request('quick_search'))
                <span class="badge badge-info mr-2 mb-1">
                    Quick search: {{ request('quick_search') }}
                    <a href="{{ route('admin.orders.index', array_merge(request()->except(['quick_search', 'page']), [])) }}"
                        class="text-white ml-1">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </span>
                @endif
                @if(request('status'))
                <span class="badge badge-primary mr-2 mb-1">
                    Status: {{ ucfirst(request('status')) }}
                    <a href="{{ route('admin.orders.index', array_merge(request()->except(['status', 'page']), [])) }}"
                        class="text-white ml-1">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </span>
                @endif
                @if(request('search'))
                <span class="badge badge-info mr-2 mb-1">
                    Search: {{ request('search') }}
                    <a href="{{ route('admin.orders.index', array_merge(request()->except(['search', 'page']), [])) }}"
                        class="text-white ml-1">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </span>
                @endif
            </div>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_number ?? '#ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    @include('admin.components.image-with-fallback', [
                                        'src' => $order->user->avatar_url ?? null,
                                        'alt' => $order->user->username ?? $order->user->name ?? 'Guest User',
                                        'type' => 'profile',
                                        'class' => 'img-profile rounded-circle',
                                        'style' => 'width: 30px; height: 30px; object-fit: cover;'
                                    ])
                                </div>
                                <div>
                                    <div class="font-weight-bold">{{ $order->user->username ?? $order->user->name ??
                                        'Guest User' }}</div>
                                    <div class="text-muted small">{{ $order->user->email ?? 'No email' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') : 'N/A'
                            }}</td>
                        <td>
                            @php
                            $statusClass = match(strtolower($order->status)) {
                            'pending' => 'badge-warning',
                            'processing' => 'badge-info',
                            'shipped' => 'badge-primary',
                            'delivered', 'completed' => 'badge-success',
                            'cancelled' => 'badge-danger',
                            'refunded' => 'badge-secondary',
                            default => 'badge-light'
                            };
                            @endphp <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td>{{ $order->items->count() }}</td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            @if($order->payment)
                            <span class="badge badge-success">Paid</span>
                            @else
                            <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                    data-toggle="dropdown">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.orders.show', $order->id) }}">
                                        <i class="fas fa-eye fa-sm mr-2"></i> View Details
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.orders.edit', $order->id) }}">
                                        <i class="fas fa-edit fa-sm mr-2"></i> Update Status
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.orders.invoice', $order->id) }}">
                                        <i class="fas fa-print fa-sm mr-2"></i> Print Invoice
                                    </a>
                                    {{-- @if(in_array(strtolower($order->status), ['pending', 'processing']))
                                    <div class="dropdown-divider"></div>
                                    <button type="button" class="dropdown-item text-danger"
                                        onclick="if(confirm('Are you sure you want to cancel this order?')) { document.getElementById('cancel-form-{{ $order->id }}').submit(); }">
                                        <i class="fas fa-times fa-sm mr-2"></i> Cancel Order
                                    </button>
                                    <form id="cancel-form-{{ $order->id }}"
                                        action="{{ route('admin.orders.update', $order->id) }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                    </form>
                                    @endif --}}
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No orders found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div> <!-- Pagination section -->
        @if(isset($orders) && method_exists($orders, 'links'))
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() ?? 0 }}
                entries
            </div>
            <div>
                {{ $orders->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<!-- Custom styles for this page -->
<link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<style>
    /* Custom pagination styling */
    .pagination {
        margin-bottom: 0;
    }

    .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    .page-link {
        color: #4e73df;
    }

    .page-link:hover {
        color: #2e59d9;
    }

    /* Actions button styling */
    .btn-outline-primary:hover {
        color: #fff !important;
    }

    .btn-outline-primary:active,
    .btn-outline-primary.active,
    .btn-outline-primary:focus {
        color: #fff !important;
    }    /* Custom card hover effect */
    .card-hover:hover {
        transform: translateY(-5px);
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }    /* Stats Slider Styles */
    .stats-slider-container {
        overflow: hidden;
        border-radius: 10px;
        position: relative;
        width: 100%;
    }

    .stats-slider {
        display: flex;
        transition: transform 0.5s ease;
        gap: 20px;
        padding: 5px;
        will-change: transform;
    }

    .stats-card {
        min-width: 280px;
        max-width: 280px;
        flex-shrink: 0;
        flex-grow: 0;
    }

    .stats-slider-controls button {
        width: 40px;
        height: 40px;
        border: 2px solid #e3e6f0;
        background: white;
        transition: all 0.3s ease;
    }

    .stats-slider-controls button:hover {
        background: #4e73df;
        border-color: #4e73df;
        color: white;
        transform: scale(1.1);
    }

    .stats-indicators {
        display: flex;
        gap: 8px;
    }

    .indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #d1d3e2;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .indicator.active {
        background: #4e73df;
        transform: scale(1.2);
    }

    .indicator:hover {
        background: #5a5c69;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .stats-card {
            min-width: 250px;
            max-width: 250px;
        }
        
        .stats-slider-controls {
            display: none;
        }
    }

    @media (max-width: 576px) {
        .stats-card {
            min-width: 220px;
            max-width: 220px;
        }
    }
</style>
@endpush

@push('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<!-- Page level custom scripts -->
<script>    $(document).ready(function() {
    // Seamless Infinite Stats Slider    let currentIndex = 0;
    const totalCards = 5; // Total original cards (Total, Delivered, Processing, Shipped, Cancelled)
    const cardWidth = 300; // Card width + gap
    let isTransitioning = false;
    let autoSlideInterval;
      // Create seamless infinite slider
    function setupSeamlessSlider() {
        const slider = $('#statsSlider');
        const originalCards = slider.children().clone();
        
        // Create multiple copies for seamless effect
        // Add 2 sets before and 2 sets after for smooth infinite scrolling
        for (let i = 0; i < 2; i++) {
            slider.prepend(originalCards.clone());
            slider.append(originalCards.clone());
        }
        
        // Start at the first "real" set (after 2 prepended sets)
        // This ensures we start at "Total Orders" (index 0 of the original cards)
        currentIndex = totalCards * 2; // This is index 8, which corresponds to "Total Orders"
        updateSliderPosition(false);
    }
    
    function updateSliderPosition(animate = true) {
        const slider = $('#statsSlider');
        const translateX = -currentIndex * cardWidth;
        
        slider.css({
            'transition': animate ? 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)' : 'none',
            'transform': `translateX(${translateX}px)`
        });
        
        updateIndicators();
    }
    
    function updateIndicators() {
        $('.indicator').removeClass('active');
        const indicatorIndex = currentIndex % totalCards;
        $(`.indicator[data-slide="${indicatorIndex}"]`).addClass('active');
    }
    
    function slideNext() {
        if (isTransitioning) return;
        isTransitioning = true;
        
        currentIndex++;
        updateSliderPosition(true);
        
        // Check for seamless loop
        setTimeout(() => {
            checkInfiniteLoop();
            isTransitioning = false;
        }, 600);
    }
    
    function slidePrev() {
        if (isTransitioning) return;
        isTransitioning = true;
        
        currentIndex--;
        updateSliderPosition(true);
        
        // Check for seamless loop
        setTimeout(() => {
            checkInfiniteLoop();
            isTransitioning = false;
        }, 600);
    }
    
    function checkInfiniteLoop() {
        const slider = $('#statsSlider');
        const totalClonedCards = totalCards * 5; // 2 before + 1 original + 2 after
        
        // If we've gone too far right, jump back to equivalent position on the left
        if (currentIndex >= totalCards * 4) {
            currentIndex = totalCards * 2;
            updateSliderPosition(false);
        }
        
        // If we've gone too far left, jump forward to equivalent position on the right
        if (currentIndex < totalCards) {
            currentIndex = totalCards * 3;
            updateSliderPosition(false);
        }
    }
    
    function goToSlide(slideIndex) {
        if (isTransitioning) return;
        isTransitioning = true;
        
        // Find the closest instance of the target slide
        const targetIndex = (totalCards * 2) + slideIndex;
        currentIndex = targetIndex;
        updateSliderPosition(true);
        
        setTimeout(() => {
            isTransitioning = false;
        }, 600);
    }
    
    // Event listeners
    $('#statsNext').on('click', slideNext);
    $('#statsPrev').on('click', slidePrev);
    
    $('.indicator').on('click', function() {
        const slideIndex = parseInt($(this).data('slide'));
        goToSlide(slideIndex);
    });
    
    // Auto-slide functionality
    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            slideNext();
        }, 4000);
    }
    
    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }
    
    // Hover to pause auto-slide
    $('.stats-slider-container').hover(
        () => stopAutoSlide(),
        () => startAutoSlide()
    );
    
    // Enhanced touch/swipe support
    let touchStartX = 0;
    let touchStartY = 0;
    let isDragging = false;
    let isSwiping = false;
    
    $('.stats-slider-container').on('touchstart', function(e) {
        if (isTransitioning) return;
        touchStartX = e.originalEvent.touches[0].clientX;
        touchStartY = e.originalEvent.touches[0].clientY;
        isDragging = true;
        stopAutoSlide();
    });
    
    $('.stats-slider-container').on('touchmove', function(e) {
        if (!isDragging || isTransitioning) return;
        
        const touchCurrentX = e.originalEvent.touches[0].clientX;
        const touchCurrentY = e.originalEvent.touches[0].clientY;
        const diffX = touchStartX - touchCurrentX;
        const diffY = touchStartY - touchCurrentY;
        
        // Determine if this is a horizontal swipe
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 30) {
            e.preventDefault(); // Prevent vertical scrolling
            isSwiping = true;
        }
    });
    
    $('.stats-slider-container').on('touchend', function(e) {
        if (!isDragging) return;
        
        if (isSwiping) {
            const touchEndX = e.originalEvent.changedTouches[0].clientX;
            const diffX = touchStartX - touchEndX;
            
            if (Math.abs(diffX) > 80) { // Minimum swipe distance
                if (diffX > 0) {
                    slideNext();
                } else {
                    slidePrev();
                }
            }
        }
        
        isDragging = false;
        isSwiping = false;
        startAutoSlide();
    });
    
    // Mouse drag support for desktop
    let mouseStartX = 0;
    let isMouseDragging = false;
    
    $('.stats-slider-container').on('mousedown', function(e) {
        if (isTransitioning) return;
        mouseStartX = e.clientX;
        isMouseDragging = true;
        stopAutoSlide();
        e.preventDefault();
    });
    
    $(document).on('mousemove', function(e) {
        if (!isMouseDragging || isTransitioning) return;
        
        const diffX = mouseStartX - e.clientX;
        if (Math.abs(diffX) > 100) {
            if (diffX > 0) {
                slideNext();
            } else {
                slidePrev();
            }
            isMouseDragging = false;
        }
    });
    
    $(document).on('mouseup', function() {
        if (isMouseDragging) {
            isMouseDragging = false;
            startAutoSlide();
        }
    });
    
    // Initialize seamless slider
    setupSeamlessSlider();
    startAutoSlide();

    // Initialize DataTable
    $('#dataTable').DataTable({
        "order": [[ 0, "desc" ]], // Order by Order ID descending (newest first)
        "pageLength": 25,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [7] } // Disable ordering for Actions column
        ],
        "paging": false,
        "info": false,
        "lengthChange": false,
        "searching": false, // Disable DataTables built-in search since we use custom search
        "dom": 'rt', // Custom DOM layout - just table (r) and table content (t)
        "language": {
            "emptyTable": "No orders found",
            "zeroRecords": "No matching orders found"
        }
    });
    
    // Add confirmation for cancel actions
    $('.cancel-order').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to cancel this order?')) {
            $(this).closest('form').submit();
        }
    });
});
</script>
@endpush
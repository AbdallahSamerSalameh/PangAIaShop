@extends('admin.layouts.app')

@section('title', 'Reviews Management')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Reviews Management</h1>
        <div class="d-flex">
            @if(request('status') === 'pending')
                <span class="badge badge-warning badge-lg mr-2">{{ $reviews->total() }} Pending Reviews</span>
            @endif
            {{-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i> Export Reviews
            </a> --}}
        </div>
    </div>

    <!-- Reviews DataTable -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Reviews</h6>
                </div>
                <div class="col-md-6 text-right">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.reviews.index') }}" 
                           class="btn btn-sm text-white{{ !request('status') ? 'btn-primary text-white' : 'btn-outline-primary text-white' }}">
                            All Reviews
                        </a>
                        <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}" 
                           class="btn btn-sm text-white  {{ request('status') === 'pending' ? 'btn-warning text-white' : 'btn-outline-warning' }}">
                            Pending
                            @if($pendingReviewsCount > 0)
                                <span class="badge badge-light ml-1">{{ $pendingReviewsCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.reviews.index', ['status' => 'approved']) }}" 
                           class="btn btn-sm text-white {{ request('status') === 'approved' ? 'btn-success text-white' : 'btn-outline-success' }}">
                            Approved
                        </a>
                        <a href="{{ route('admin.reviews.index', ['status' => 'rejected']) }}" 
                           class="btn btn-sm text-white {{ request('status') === 'rejected' ? 'btn-danger text-white' : 'btn-outline-danger' }}">
                            Rejected
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Custom filter row -->
            <div class="row mb-3 align-items-center">
                <div class="col-sm-4">
                    <form id="perPageForm" action="{{ route('admin.reviews.index') }}" method="GET" class="form-inline">
                        <label class="mr-2 text-nowrap">Show</label>
                        <select name="per_page" id="per_page" class="form-control form-control-sm"
                            onchange="document.getElementById('perPageForm').submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                        <label class="ml-2 text-nowrap">entries</label>
                        
                        <!-- Hidden inputs to preserve other parameters -->
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                    </form>
                </div>
                <div class="col-sm-4 offset-sm-4">
                    <form method="GET" action="{{ route('admin.reviews.index') }}" class="form-inline justify-content-end">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search reviews, products, customers..." 
                                   value="{{ $searchQuery }}" style="min-width: 250px;">
                            <div class="input-group-append">
                                <button class="btn btn-primary btn-sm" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                                @if($searchQuery)
                                <a href="{{ route('admin.reviews.index', request()->except(['search', 'page'])) }}" 
                                   class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times fa-sm"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Hidden inputs to preserve other parameters -->
                        @if(request('per_page'))
                        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                        @endif
                        @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                    </form>
                </div>
            </div>

            <!-- Active Search Filter Display -->
            @if($searchQuery || request('status'))
            <div class="mb-3">
                <div class="d-flex flex-wrap align-items-center">
                    <span class="mr-2 text-muted">Active filters:</span>
                    @if($searchQuery)
                    <span class="badge badge-info mr-2 mb-1">
                        Search: {{ $searchQuery }}
                        <a href="{{ route('admin.reviews.index', request()->except(['search', 'page'])) }}" 
                           class="text-white ml-1">
                            <i class="fas fa-times-circle"></i>
                        </a>
                    </span>
                    @endif
                    @if(request('status'))
                    <span class="badge badge-info mr-2 mb-1">
                        Status: {{ ucfirst(request('status')) }}
                        <a href="{{ route('admin.reviews.index', request()->except(['status', 'page'])) }}" 
                           class="text-white ml-1">
                            <i class="fas fa-times-circle"></i>
                        </a>
                    </span>
                    @endif
                </div>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" id="reviewsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                            <tr>
                                <td>
                                    @if($review->product)
                                        <a href="{{ route('admin.products.show', $review->product->id) }}" 
                                           class="text-decoration-none">
                                            {{ Str::limit($review->product->name, 30) }}
                                        </a>
                                    @else
                                        <span class="text-muted">Product Deleted</span>
                                    @endif
                                </td>
                                <td>
                                    @if($review->user)
                                        <a href="{{ route('admin.customers.show', $review->user->id) }}" 
                                           class="text-decoration-none">
                                            {{ $review->user->username ?? 'N/A' }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $review->user->email }}</small>
                                    @else
                                        <span class="text-muted">User Deleted</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-muted"></i>
                                            @endif
                                        @endfor
                                        <span class="ml-2">{{ $review->rating }}/5</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="review-content">
                                        {{ Str::limit($review->comment, 80) }}
                                    </div>
                                </td>
                                <td>
                                    @if($review->moderation_status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($review->moderation_status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($review->moderation_status === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $review->created_at->format('M d, Y') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $review->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                            data-toggle="dropdown">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.reviews.show', $review->id) }}">
                                                <i class="fas fa-eye fa-sm mr-2"></i> View Details
                                            </a>
                                            
                                            @if($review->moderation_status === 'pending')
                                                <div class="dropdown-divider"></div>                                                <form action="{{ route('admin.reviews.approve', $review->id) }}" 
                                                      method="POST" class="d-inline approve-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="button" class="dropdown-item text-success approve-btn" 
                                                            data-review-id="{{ $review->id }}" data-product-name="{{ $review->product->name ?? 'Unknown Product' }}">
                                                        <i class="fas fa-check fa-sm mr-2"></i> Approve
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.reviews.reject', $review->id) }}" 
                                                      method="POST" class="d-inline reject-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="button" class="dropdown-item text-danger reject-btn" 
                                                            data-review-id="{{ $review->id }}" data-product-name="{{ $review->product->name ?? 'Unknown Product' }}">
                                                        <i class="fas fa-times fa-sm mr-2"></i> Reject
                                                    </button>
                                                </form>
                                            @elseif($review->moderation_status === 'approved')
                                                <div class="dropdown-divider"></div>                                                <form action="{{ route('admin.reviews.reject', $review->id) }}" 
                                                      method="POST" class="d-inline reject-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="button" class="dropdown-item text-danger reject-btn" 
                                                            data-review-id="{{ $review->id }}" data-product-name="{{ $review->product->name ?? 'Unknown Product' }}">
                                                        <i class="fas fa-times fa-sm mr-2"></i> Reject
                                                    </button>
                                                </form>
                                            @elseif($review->moderation_status === 'rejected')
                                                <div class="dropdown-divider"></div>                                                <form action="{{ route('admin.reviews.approve', $review->id) }}" 
                                                      method="POST" class="d-inline approve-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="button" class="dropdown-item text-success approve-btn" 
                                                            data-review-id="{{ $review->id }}" data-product-name="{{ $review->product->name ?? 'Unknown Product' }}">
                                                        <i class="fas fa-check fa-sm mr-2"></i> Approve
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="py-4">
                                    <i class="fas fa-star fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-muted">No reviews found.</p>
                                    @if($searchQuery || request('status'))
                                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-list"></i> View All Reviews
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination section -->
            @if(isset($reviews) && method_exists($reviews, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing {{ $reviews->firstItem() ?? 0 }} to {{ $reviews->lastItem() ?? 0 }} of {{ $reviews->total() ?? 0 }} entries
                </div>
                <div>
                    {{ $reviews->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        toastr.success('{{ session('success') }}');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        toastr.error('{{ session('error') }}');
    });
</script>
@endif
@endsection

@push('styles')
<!-- Custom styles for this page -->
<link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Custom pagination styling */
    .pagination {
        margin-bottom: 0;
    }
    
    .pagination .page-link {
        color: #5a5c69;
        border-color: #dddfeb;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #858796;
        border-color: #858796;
    }
    
    .pagination .page-link:hover {
        color: #3a3b45;
        background-color: #eaecf4;
        border-color: #dddfeb;
    }

    /* Active filter badge styling */
    .badge-info {
        background-color: #17a2b8;
    }
    
    .badge-info .fas {
        font-size: 10px;
    }

    /* Prevent text wrapping on form elements */
    .text-nowrap {
        white-space: nowrap;
    }

    /* Actions button hover styling */
    .btn-outline-primary:hover {
        color: #fff !important;
    }

    /* Actions button active/pressed styling */
    .btn-outline-primary:active,
    .btn-outline-primary.active,
    .btn-outline-primary:focus {
        color: #fff !important;
    }

    /* Filter button styling */
    .btn-group .btn-outline-primary:hover,
    .btn-group .btn-outline-success:hover,
    .btn-group .btn-outline-warning:hover,
    .btn-group .btn-outline-danger:hover {
        color: #fff !important;
    }

    /* Table responsive styling */
    .table-responsive {
        border: none;
    }

    .review-content {
        max-width: 200px;
        word-wrap: break-word;
    }    /* Card header background for better contrast */
    .card-header.bg-light {
        background-color: #f8f9fc !important;
        border-bottom: 1px solid #e3e6f0;
    }

    /* SweetAlert2 Custom Styling */
    .swal2-popup {
        border-radius: 10px;
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .swal2-title {
        color: #5a5c69;
        font-weight: 700;
    }

    .swal2-content {
        color: #6e707e;
    }

    .swal2-confirm.btn-success {
        background-color: #1cc88a !important;
        border-color: #1cc88a !important;
        color: white !important;
        padding: 0.375rem 0.75rem;
        margin: 0.25rem;
        border-radius: 0.35rem;
        font-weight: 400;
        line-height: 1.5;
        text-align: center;
        text-decoration: none;
        vertical-align: middle;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .swal2-confirm.btn-success:hover {
        background-color: #17a673 !important;
        border-color: #169b6b !important;
    }

    .swal2-confirm.btn-danger {
        background-color: #e74a3b !important;
        border-color: #e74a3b !important;
        color: white !important;
        padding: 0.375rem 0.75rem;
        margin: 0.25rem;
        border-radius: 0.35rem;
        font-weight: 400;
        line-height: 1.5;
        text-align: center;
        text-decoration: none;
        vertical-align: middle;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .swal2-confirm.btn-danger:hover {
        background-color: #d33534 !important;
        border-color: #bd2d2c !important;
    }

    .swal2-cancel.btn-secondary {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: white !important;
        padding: 0.375rem 0.75rem;
        margin: 0.25rem;
        border-radius: 0.35rem;
        font-weight: 400;
        line-height: 1.5;
        text-align: center;
        text-decoration: none;
        vertical-align: middle;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .swal2-cancel.btn-secondary:hover {
        background-color: #5a6268 !important;
        border-color: #545b62 !important;
    }
</style>
@endpush

@push('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Page level custom scripts -->
<script>
$(document).ready(function() {
    // Initialize DataTables with minimal configuration for styling only
    $('#reviewsTable').DataTable({
        "ordering": true,
        "searching": false, // Disable client-side search
        "responsive": true,
        "paging": false,
        "info": false,
        "lengthChange": false,
        "dom": 'rt', // Only table (r) and table content (t) - removed filter (f)
        "columnDefs": [
            { "orderable": false, "targets": [6] } // Disable ordering for Actions column
        ]
    });

    // Handle approve button clicks
    $('.approve-btn').on('click', function(e) {
        e.preventDefault();
        const reviewId = $(this).data('review-id');
        const productName = $(this).data('product-name');
        const form = $(this).closest('form');
        
        // Show confirmation using SweetAlert2 or custom modal
        Swal.fire({
            title: 'Approve Review',
            text: `Are you sure you want to approve this review for "${productName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Yes, Approve',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Approving review...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit the form
                form[0].submit();
            }
        });
    });

    // Handle reject button clicks
    $('.reject-btn').on('click', function(e) {
        e.preventDefault();
        const reviewId = $(this).data('review-id');
        const productName = $(this).data('product-name');
        const form = $(this).closest('form');
        
        // Show confirmation using SweetAlert2
        Swal.fire({
            title: 'Reject Review',
            text: `Are you sure you want to reject this review for "${productName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-times"></i> Yes, Reject',
            cancelButtonText: '<i class="fas fa-arrow-left"></i> Cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Rejecting review...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit the form
                form[0].submit();
            }
        });
    });
});
</script>
@endpush

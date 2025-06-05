@extends('admin.layouts.app')

@section('title', 'Promotions Management')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        {{-- <i class="fas fa-tags mr-2"></i> --}}
        Promotions Management
    </h1>
    <div class="d-sm-flex">
        <a href="{{ route('admin.promotions.promo_codes.create') }}"
            class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Create New Promotion
        </a>
    </div>
</div>

<!-- Promotion Statistics Slider -->
<div class="position-relative mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="text-gray-800 font-weight-bold mb-0">Promotion Statistics</h6>
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
                                    Total Promotions</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tags fa-2x text-gray-300"></i>
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
                                    Active Promotions</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] }}</div>
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
                                    Inactive Promotions</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactive'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
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
                                    Expired Promotions</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expired'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                    Used This Month</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['used_this_month'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
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
{{--
<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Quick Actions</div>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.promotions.promo_codes.create') }}"
                                    class="btn btn-info btn-sm btn-block text-white">
                                    <i class="fas fa-plus mr-1 text-white"></i> Create Promotion
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-success btn-sm btn-block" onclick="bulkActivate()">
                                    <i class="fas fa-play mr-1"></i> Bulk Activate
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-danger btn-sm btn-block" onclick="bulkDeactivate()">
                                    <i class="fas fa-pause mr-1"></i> Bulk Deactivate
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-secondary btn-sm btn-block" onclick="exportPromotions()">
                                    <i class="fas fa-download mr-1"></i> Export Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}

<!-- Filter and Search -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter & Search</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.promotions.index') }}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="expired" {{ request('status')=='expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="type" class="form-label">Discount Type</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="percentage" {{ request('type')=='percentage' ? 'selected' : '' }}>Percentage
                        </option>
                        <option value="fixed" {{ request('type')=='fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="free_shipping" {{ request('type')=='free_shipping' ? 'selected' : '' }}>Free
                            Shipping</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">Search Code</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Enter promo code..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary btn-sm mr-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Promotions Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Promotions & Discount Codes</h6>
            {{-- <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                    aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Export Options:</div>
                    <a class="dropdown-item" href="#" onclick="exportPromotions('csv')">
                        <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i> Export as CSV
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportPromotions('pdf')">
                        <i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-gray-400"></i> Export as PDF
                    </a>
                </div>
            </div> --}}
        </div>
    </div>
    <div class="card-body">
        <!-- Entries per page selector -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <label for="per_page" class="mr-2 mb-0">Show</label> <select name="per_page" id="per_page"
                    class="form-control form-control-sm d-inline-block" style="width: auto;"
                    onchange="changePerPage(this.value)">
                    <option value="5" {{ request('per_page', 10)==5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('per_page', 10)==10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('per_page', 10)==15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page', 10)==25 ? 'selected' : '' }}>25</option>
                </select>
                <span class="ml-2 mb-0">entries</span>
            </div>
            @if($promoCodes->total() > 0)
            <div class="text-muted">
                Showing {{ $promoCodes->firstItem() }} to {{ $promoCodes->lastItem() }} of {{ $promoCodes->total() }}
                entries
            </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="25%">Promotion Details</th>
                        <th width="15%">Discount Value</th>
                        <th width="15%">Usage Statistics</th>
                        <th width="15%">Validity Period</th>
                        <th width="10%">Status</th>
                        <th width="10%">Performance</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promoCodes as $code)
                    <tr>                        <td class="align-middle">
                            <div class="d-flex align-items-center">
                                <div class="promotion-icon mr-3">
                                    @if($code->discount_type == 'percentage')
                                    <div class="badge badge-primary p-2" title="Percentage Discount">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    @elseif($code->discount_type == 'fixed')
                                    <div class="badge badge-success p-2" title="Fixed Amount Discount">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    @else
                                    <div class="badge badge-info p-2" title="Free Shipping">
                                        <i class="fas fa-shipping-fast"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="mb-1">
                                        <span class="font-weight-bold text-primary h6 mb-0">{{ $code->code }}</span>
                                    </div>

                                    {{-- @if($code->target_audience)
                                    <div class="mb-1">
                                        <small class="badge badge-light mr-1">
                                            @if(isset($code->target_audience['new_users']))
                                            <i class="fas fa-user-plus mr-1"></i>New Users
                                            @elseif(isset($code->target_audience['repeat_customers']))
                                            <i class="fas fa-redo mr-1"></i>Repeat Customers
                                            @elseif(isset($code->target_audience['vip_customers']))
                                            <i class="fas fa-crown mr-1"></i>VIP Customers
                                            @else
                                            <i class="fas fa-users mr-1"></i>All Customers
                                            @endif
                                        </small>
                                    </div>
                                    @endif --}}
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="text-center">
                                @if($code->discount_type == 'percentage')
                                <div class="h5 mb-1 text-primary">{{ $code->discount_value }}%</div>
                                <small class="text-muted">Percentage Off</small>
                                @elseif($code->discount_type == 'fixed')
                                <div class="h5 mb-1 text-success">${{ number_format($code->discount_value, 2) }}</div>
                                <small class="text-muted">Fixed Amount</small>
                                @else
                                <div class="h5 mb-1 text-info">FREE</div>
                                <small class="text-muted">Shipping</small>
                                @endif
                                @if($code->min_order_amount)
                                <br><small class="text-warning">Min: ${{ number_format($code->min_order_amount, 2)
                                    }}</small>
                                @endif
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="text-center">
                                <div class="h6 mb-1 text-dark">{{ $code->usages_count }}</div>
                                @if($code->max_uses)
                                <div class="progress mt-1 mb-1" style="height: 6px;">
                                    @php
                                    $usagePercentage = min(100, ($code->usages_count / $code->max_uses) * 100);
                                    @endphp
                                    <div class="progress-bar bg-{{ $usagePercentage >= 80 ? 'danger' : ($usagePercentage >= 50 ? 'warning' : 'success') }}"
                                        role="progressbar" style="width: {{ $usagePercentage }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($usagePercentage, 1) }}% of {{
                                    number_format($code->max_uses) }}</small>
                                @else
                                <small class="text-muted">Unlimited Usage</small>
                                @endif
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="text-sm">
                                <div class="mb-1">
                                    <i class="fas fa-calendar-plus text-success"></i>
                                    <strong>Start:</strong> {{ $code->valid_from->format('M j, Y') }}
                                </div>
                                <div class="mb-1">
                                    <i class="fas fa-calendar-times text-danger"></i>
                                    <strong>End:</strong> {{ $code->valid_until->format('M j, Y') }}
                                </div>
                                @if($code->valid_until < now()) <small class="badge badge-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Expired
                                    </small>
                                    @else
                                    <small class="badge badge-success">
                                        <i class="fas fa-clock"></i> {{ $code->valid_until->diffForHumans() }}
                                    </small>
                                    @endif
                            </div>
                        </td>
                        <td class="align-middle text-center">
                            @if($code->is_active && $code->valid_until > now())
                            <span class="badge badge-success badge-lg">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                            @elseif(!$code->is_active)
                            <span class="badge badge-secondary badge-lg">
                                <i class="fas fa-pause-circle"></i> Inactive
                            </span>
                            @else
                            <span class="badge badge-danger badge-lg">
                                <i class="fas fa-times-circle"></i> Expired
                            </span>
                            @endif
                        </td>
                        <td class="align-middle text-center">
                            @if($code->max_uses && $code->usages_count > 0)
                            @php
                            $usageRate = ($code->usages_count / $code->max_uses) * 100;
                            @endphp
                            @if($usageRate >= 90)
                            <span class="badge badge-danger">
                                <i class="fas fa-fire"></i> Nearly Full
                            </span>
                            @elseif($usageRate >= 70)
                            <span class="badge badge-warning">
                                <i class="fas fa-exclamation"></i> High Usage
                            </span>
                            @elseif($usageRate >= 30)
                            <span class="badge badge-info">
                                <i class="fas fa-chart-line"></i> Moderate
                            </span>
                            @else
                            <span class="badge badge-success">
                                <i class="fas fa-seedling"></i> Low Usage
                            </span>
                            @endif
                            @elseif($code->usages_count > 0)
                            <span class="badge badge-primary">
                                <i class="fas fa-thumbs-up"></i> {{ $code->usages_count }} Uses
                            </span>
                            @else
                            <span class="badge badge-light">
                                <i class="fas fa-clock"></i> Unused
                            </span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                    data-toggle="dropdown">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item"
                                        href="{{ route('admin.promotions.promo_codes.show', $code->id) }}">
                                        <i class="fas fa-eye fa-sm mr-2"></i> View
                                    </a>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.promotions.promo_codes.edit', $code->id) }}">
                                        <i class="fas fa-edit fa-sm mr-2"></i> Edit
                                    </a>
                                    <a class="dropdown-item text-info" href="#" onclick="copyCode('{{ $code->code }}')">
                                        <i class="fas fa-copy fa-sm mr-2"></i> Copy Code
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    {{-- <form method="POST"
                                        action="{{ route('admin.promotions.promo_codes.toggle', $code->id) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="dropdown-item {{ $code->is_active ? 'text-warning' : 'text-success' }}">
                                            <i class="fas fa-{{ $code->is_active ? 'pause' : 'play' }} fa-sm mr-2"></i>
                                            {{ $code->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <div class="dropdown-divider"></div> --}}                                    <form method="POST" id="delete-form-{{ $code->id }}"
                                        action="{{ route('admin.promotions.promo_codes.destroy', $code->id) }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <a href="#" class="dropdown-item text-danger" 
                                       onclick="showDeleteModal('{{ $code->id }}', '{{ $code->code }}', 'promo code')">
                                        <i class="fas fa-trash fa-sm mr-2"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-tags fa-3x mb-3"></i>
                                <h5>No Promotions Found</h5>
                                <p>Get started by creating your first promotional campaign.</p>
                                <a href="{{ route('admin.promotions.promo_codes.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-2"></i> Create Promotion
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination Info and Links -->
        @if($promoCodes->total() > 0)
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                Showing {{ $promoCodes->firstItem() }} to {{ $promoCodes->lastItem() }} of {{ $promoCodes->total() }}
                entries
            </div>
            @if($promoCodes->hasPages())
            <nav aria-label="Promotions pagination">
                <div class="pagination-wrapper">
                    {{ $promoCodes->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </nav>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Select an action to perform on the selected promotions:</p>
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action" onclick="bulkActivate()">
                        <i class="fas fa-play text-success mr-2"></i> Activate Selected
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="bulkDeactivate()">
                        <i class="fas fa-pause text-warning mr-2"></i> Deactivate Selected
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="bulkDelete()">
                        <i class="fas fa-trash text-danger mr-2"></i> Delete Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .pagination-wrapper .pagination {
        margin-bottom: 0;
    }

    .pagination-wrapper .page-link {
        color: #5a5c69;
        border: 1px solid #d1d3e2;
        padding: 0.5rem 0.75rem;
    }

    .pagination-wrapper .page-link:hover {
        color: #224abe;
        background-color: #eaecf4;
        border-color: #d1d3e2;
    }

    .pagination-wrapper .page-item.active .page-link {
        background-color: #5a5c69;
        border-color: #5a5c69;
        color: white;
    }

    .pagination-wrapper .page-item.disabled .page-link {
        color: #858796;
        background-color: #f8f9fc;
        border-color: #d1d3e2;
    }
</style>
@endsection

@section('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<!-- Page level custom scripts -->
<script>
    $(document).ready(function() {
    // Remove DataTables initialization to avoid conflicts with server-side filtering
    // $('#dataTable').DataTable();
    
    // Select all checkbox functionality
    $('#selectAll').on('change', function() {
        $('.code-checkbox').prop('checked', this.checked);
    });
    
    // Individual checkbox change
    $('.code-checkbox').on('change', function() {
        if (!this.checked) {
            $('#selectAll').prop('checked', false);
        }
    });
});

// Copy promo code to clipboard
function copyCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        showToast('Code copied to clipboard!', 'success');
    }).catch(function(err) {
        showToast('Failed to copy code', 'error');
    });
}

// Bulk actions
function bulkActions() {
    var selected = $('.code-checkbox:checked').length;
    if (selected === 0) {
        showToast('Please select at least one promotion', 'warning');
        return;
    }
    $('#bulkActionsModal').modal('show');
}

function bulkActivate() {
    var selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        showToast('Please select at least one promotion', 'warning');
        return;
    }
    
    if (confirm('Activate ' + selectedIds.length + ' selected promotions?')) {
        // Implement bulk activate functionality
        showToast('Promotions activated successfully!', 'success');
    }
}

function bulkDeactivate() {
    var selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        showToast('Please select at least one promotion', 'warning');
        return;
    }
    
    if (confirm('Deactivate ' + selectedIds.length + ' selected promotions?')) {
        // Implement bulk deactivate functionality
        showToast('Promotions deactivated successfully!', 'success');
    }
}

function bulkDelete() {
    var selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        showToast('Please select at least one promotion', 'warning');
        return;
    }
    
    if (confirm('Are you sure you want to delete ' + selectedIds.length + ' selected promotions? This action cannot be undone.')) {
        // Implement bulk delete functionality
        showToast('Promotions deleted successfully!', 'success');
    }
}

function getSelectedIds() {
    var ids = [];
    $('.code-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function exportPromotions(format) {
    var url = '{{ route("admin.promotions.export") }}?format=' + (format || 'csv');
    window.open(url, '_blank');
}

// Change entries per page
function changePerPage(perPage) {
    var url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}

function showToast(message, type) {
    // Simple toast notification
    var alertClass = type === 'success' ? 'alert-success' : 
                    type === 'warning' ? 'alert-warning' : 'alert-danger';
    
    var toast = $('<div class="alert ' + alertClass + ' alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">' +
                  '<strong>' + message + '</strong>' +
                  '<button type="button" class="close" data-dismiss="alert">' +
                  '<span>&times;</span>' +
                  '</button>' +
                  '</div>');
    
    $('body').append(toast);
    
    setTimeout(function() {
        toast.remove();
    }, 5000);
}

// Seamless Infinite Stats Slider
let currentIndex = 0;
const totalCards = 5; // Total original cards (Total, Active, Inactive, Expired, Used This Month)
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
    // This ensures we start at "Total Promotions" (index 0 of the original cards)
    currentIndex = totalCards * 2; // This is index 10, which corresponds to "Total Promotions"
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

// Event listeners for carousel
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
</script>

@push('styles')
<style>
    /* Custom card hover effect */
    .card-hover:hover {
        transform: translateY(-5px);
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    /* Fix Actions dropdown button text color on hover/active */
    .btn-outline-primary:hover,
    .btn-outline-primary:focus,
    .btn-outline-primary:active,
    .btn-outline-primary.active {
        color: #fff !important;
        /* background-color: #4e73df !important;
        border-color: #4e73df !important; */
    }    .btn-outline-primary.dropdown-toggle:hover,
    .btn-outline-primary.dropdown-toggle:focus,
    .btn-outline-primary.dropdown-toggle:active,
    .btn-outline-primary.dropdown-toggle.active,
    .btn-outline-primary.dropdown-toggle.show {
        color: #fff !important;
        background-color: #fd7e14 !important;
        border-color: #fd7e14 !important;
    }

    /* Stats Slider Styles */
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
@endsection
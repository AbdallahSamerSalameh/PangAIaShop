@extends('admin.layouts.app')

@section('title', 'Customers Management')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Customers Management</h1>
    <div>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm mr-2">
            <i class="fas fa-download fa-sm text-white-50"></i> Export Customers
        </a>
    </div>
</div>

<!-- Customer Statistics -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Customers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($customerStats['total'] ?? 0) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Active Customers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($customerStats['active'] ?? 0) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">New This Month</div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{
                                    number_format($customerStats['new_this_month'] ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Verification</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{
                            number_format($customerStats['pending_verification'] ?? 0) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customers DataTable -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Customers List</h6>
    </div>
    <div class="card-body">
        <!-- Custom filter row -->
        <div class="row mb-3 align-items-center">
            <div class="col-sm-4">
                <form id="perPageForm" action="{{ route('admin.customers.index') }}" method="GET" class="form-inline">
                    <label class="mr-2 text-nowrap">Show</label>
                    <select name="per_page" id="per_page" class="form-control form-control-sm"
                        onchange="document.getElementById('perPageForm').submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
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
                <form action="{{ route('admin.customers.index') }}" method="GET" class="form-inline justify-content-end">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search customers..." 
                               value="{{ request('search') }}" style="min-width: 200px;">
                        <div class="input-group-append">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                            @if(request('search'))
                            <a href="{{ route('admin.customers.index', request()->except(['search', 'page'])) }}" 
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
        @if(request('search') || request('status'))
        <div class="mb-3">
            <div class="d-flex flex-wrap align-items-center">
                <span class="mr-2 text-muted">Active filters:</span>
                @if(request('search'))
                <span class="badge badge-info mr-2 mb-1">
                    Search: {{ request('search') }}
                    <a href="{{ route('admin.customers.index', request()->except(['search', 'page'])) }}" 
                       class="text-white ml-1">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </span>
                @endif
                @if(request('status'))
                <span class="badge badge-info mr-2 mb-1">
                    Status: {{ ucfirst(request('status')) }}
                    <a href="{{ route('admin.customers.index', request()->except(['status', 'page'])) }}" 
                       class="text-white ml-1">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </span>
                @endif
            </div>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered" id="customersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        {{-- <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th> --}}
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->id }}</td>                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    @include('admin.components.image-with-fallback', [
                                        'src' => $customer->avatar_url,
                                        'alt' => $customer->username ?? 'Customer',
                                        'type' => 'profile',
                                        'class' => 'img-profile rounded-circle',
                                        'style' => 'width: 40px; height: 40px; object-fit: cover;'
                                    ])
                                </div>
                                <div>
                                    <div class="font-weight-bold">{{ $customer->username ?? 'N/A' }}</div>
                                    <div class="text-muted small">
                                        @if(($customer->orders_count ?? 0) >= 10)
                                        Premium Customer
                                        @elseif(($customer->orders_count ?? 0) >= 3)
                                        Regular Customer
                                        @else
                                        New Customer
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $customer->email ?? 'N/A' }}</td>
                        <td>{{ $customer->phone_number ?? 'N/A' }}</td>
                        <td>
                            @if(($customer->account_status ?? 'active') === 'active')
                            <span class="badge badge-success">Active</span>
                            @elseif($customer->account_status === 'suspended')
                            <span class="badge badge-warning">Suspended</span>
                            @else
                            <span class="badge badge-danger">Deactivated</span>
                            @endif
                            {{-- @if(!($customer->is_verified ?? true))
                            <span class="badge badge-warning ml-1">Unverified</span>
                            @endif --}}
                        </td>
                        {{-- <td>{{ $customer->orders_count ?? 0 }}</td>
                        <td>${{ number_format($customer->total_spent ?? 0, 2) }}</td>
                        <td>{{ $customer->created_at ? $customer->created_at->format('M d, Y') : 'N/A' }}</td> --}}                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                    data-toggle="dropdown">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.customers.show', $customer->id) }}">
                                        <i class="fas fa-eye fa-sm mr-2"></i> View Profile
                                    </a>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.orders.index', ['search' => $customer->email]) }}">
                                        <i class="fas fa-shopping-cart fa-sm mr-2"></i> View Orders
                                    </a>                                    {{-- <a class="dropdown-item" href="{{ route('admin.customers.edit', $customer->id) }}">
                                        <i class="fas fa-edit fa-sm mr-2"></i> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    @if(($customer->account_status ?? 'active') === 'active')
                                    <a class="dropdown-item text-warning" href="#"
                                        onclick="toggleCustomerStatus({{ $customer->id }}, 'suspended')">
                                        <i class="fas fa-ban fa-sm mr-2"></i> Suspend
                                    </a>
                                    @else
                                    <a class="dropdown-item text-success" href="#"
                                        onclick="toggleCustomerStatus({{ $customer->id }}, 'active')">
                                        <i class="fas fa-check fa-sm mr-2"></i> Activate
                                    </a>
                                    @endif --}}
                                    {{-- <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#"
                                        onclick="if(confirm('Are you sure you want to delete this customer?')) { document.getElementById('delete-form-{{ $customer->id }}').submit(); }">
                                        <i class="fas fa-trash fa-sm mr-2"></i> Delete
                                    </a> --}}
                                    <form id="delete-form-{{ $customer->id }}"
                                        action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="py-4">
                                <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">No customers found.</p>
                                @if(request('search') || request('status'))
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-list"></i> View All Customers
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
        @if(isset($customers) && method_exists($customers, 'links'))
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() ?? 0 }} entries
            </div>
            <div>
                {{ $customers->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
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
    }    /* Actions button hover styling */
    .btn-outline-primary:hover {
        color: #fff !important;
    }

    /* Actions button active/pressed styling */
    .btn-outline-primary:active,
    .btn-outline-primary.active,
    .btn-outline-primary:focus {
        color: #fff !important;
    }

    /* Table responsive styling */
    .table-responsive {
        border: none;
    }
</style>
@endpush

@push('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<!-- Page level custom scripts -->
<script>
$(document).ready(function() {
    // Initialize DataTables with minimal configuration for styling only
    $('#customersTable').DataTable({
        "ordering": true,
        "searching": false, // Disable client-side search
        "responsive": true,
        "paging": false,
        "info": false,
        "lengthChange": false,
        "dom": 'rt', // Only table (r) and table content (t) - removed filter (f)
        "columnDefs": [
            { "orderable": false, "targets": [1, 8] } // Disable ordering for Customer avatar and Actions columns
        ]
    });
});

function toggleCustomerStatus(customerId, newStatus) {
    if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'suspend'} this customer?`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/customers/${customerId}/toggle-active`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add method field for PATCH
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
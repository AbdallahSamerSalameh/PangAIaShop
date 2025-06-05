@extends('admin.layouts.app')

@section('title', 'Manage Admin Users')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Manage Admin Users</h1>
        <p class="mb-0 text-muted small">Manage regular admin accounts (Super Admins manage their profile separately)</p>
    </div>
    <div>
        <a href="{{ route('admin.admins.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Admin
        </a>
    </div>
</div>

<!-- Search Controls -->
<div class="card shadow mb-4">
    <div class="card-body">
        @if($searchQuery)
            <div class="row align-items-center">
                <div class="col-md-9">
                    <form method="GET" action="{{ route('admin.admins.index') }}" class="d-flex w-100">
                        <input type="text" class="form-control mr-2" name="search" 
                               value="{{ $searchQuery }}" placeholder="Search by username or email..."
                               style="flex: 1;">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <div class="text-right">
                        <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times mr-1"></i> Clear Search
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    <form method="GET" action="{{ route('admin.admins.index') }}" class="d-flex w-100">
                        <input type="text" class="form-control mr-2" name="search" 
                               value="{{ $searchQuery }}" placeholder="Search by username or email..."
                               style="flex: 1;">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Admin List -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Regular Admin Users</h6>
        <div class="text-muted small">
            <i class="fas fa-info-circle mr-1"></i>
            Super Admins are not listed here
        </div>
    </div>
    <div class="card-body">
        @if($admins->count() > 0)            <!-- Entries per page control -->
            <div class="row mb-3 align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <label class="mr-2 mb-0 text-muted">Show</label>
                        <select class="form-control form-control-sm" style="width: auto; border-color: #d1d3e2;" onchange="changePerPage(this.value)">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <label class="ml-2 mb-0 text-muted">entries</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-right">
                        <small class="pagination-info">
                            Showing {{ $admins->firstItem() }} to {{ $admins->lastItem() }} of {{ $admins->total() }} entries
                        </small>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                            <tr>                                <td class="text-center">
                                    @php
                                        $adminProfileImage = $admin->profile_image ? asset('storage/' . $admin->profile_image) : ($admin->avatar_url ?? null);
                                    @endphp
                                    @include('admin.components.image-with-fallback', [
                                        'src' => $adminProfileImage,
                                        'alt' => $admin->username,
                                        'type' => 'profile',
                                        'class' => 'rounded-circle',
                                        'style' => 'width: 40px; height: 40px; object-fit: cover;'
                                    ])
                                </td>
                                <td>
                                    <strong>{{ $admin->username }}</strong>
                                    @if($admin->id === auth('admin')->id())
                                        <span class="badge badge-info badge-sm ml-1">You</span>
                                    @endif
                                </td>
                                <td>{{ $admin->email }}</td>
                                <td>
                                    @if($admin->role === 'Super Admin')
                                        <span class="badge badge-danger">{{ $admin->role }}</span>
                                    @else
                                        <span class="badge badge-primary">{{ $admin->role }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($admin->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($admin->last_login)
                                        {{ $admin->last_login->format('M j, Y g:i A') }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>{{ $admin->created_at->format('M j, Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                                data-toggle="dropdown">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.admins.show', $admin->id) }}">
                                                <i class="fas fa-eye text-info"></i> View Details
                                            </a>
                                            <a class="dropdown-item" href="{{ route('admin.admins.edit', $admin->id) }}">
                                                <i class="fas fa-edit text-warning"></i> Edit
                                            </a>                                            @if($admin->id !== auth('admin')->id())
                                                <div class="dropdown-divider"></div>
                                                <button class="dropdown-item text-danger" 
                                                        onclick="showDeleteModal('{{ $admin->id }}', '{{ addslashes($admin->username) }}', 'admin')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                                <!-- Hidden form for universal delete modal -->
                                                <form id="delete-form-{{ $admin->id }}" action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>                        @endforeach
                    </tbody>
                </table>
            </div>            <!-- Pagination Info and Controls -->
            <div class="row mt-4 align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="pagination-info">
                            Showing {{ $admins->firstItem() ?? 0 }} to {{ $admins->lastItem() ?? 0 }} 
                            of {{ $admins->total() }} entries
                            @if($searchQuery)
                                <span class="text-info">(filtered from total entries)</span>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <nav aria-label="Admin pagination">
                            {{ $admins->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </nav>
                    </div>
                </div>
            </div>@else
            <div class="text-center py-4">
                <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No regular admins found</h5>
                <p class="text-muted">
                    @if($searchQuery)
                        No regular admin users match your search criteria.
                    @else
                        There are no regular admin users in the system yet.
                    @endif
                </p>
                <p class="text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Super Admin accounts are managed separately through the profile section.
                </p>
                @if($searchQuery)
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-sm btn-outline-primary">
                        Clear Search
                    </a>
                @else
                    <a href="{{ route('admin.admins.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus mr-1"></i> Add First Admin
                    </a>
                @endif
            </div>
        @endif    </div>
</div>
@endsection

@push('styles')
<!-- Additional styles -->
<style>
    /* Fix for action button hover styling */
    .btn-outline-primary:hover,
    .btn-outline-primary:active,
    .btn-outline-primary.active,
    .btn-outline-primary:focus {
        color: #fff !important;
    }
    
    /* Pagination styling improvements */
    .pagination {
        margin: 0;
    }
    
    .pagination .page-link {
        color: #5a5c69;
        border: 1px solid #d1d3e2;
        background-color: #fff;
        padding: 0.375rem 0.75rem;
        margin: 0 0.125rem;
        border-radius: 0.35rem;
    }
    
    .pagination .page-link:hover {
        color: #224abe;
        background-color: #eaecf4;
        border-color: #d1d3e2;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
        color: #fff;
    }
    
    .pagination .page-item.disabled .page-link {
        color: #858796;
        background-color: #f8f9fc;
        border-color: #d1d3e2;
    }
    
    /* Improved spacing for pagination info */
    .pagination-info {
        font-size: 0.875rem;
        color: #6c757d;
    }
</style>
@endpush

@section('scripts')
<script>
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', '1'); // Reset to first page when changing per_page
    window.location.href = url.toString();
}
</script>
@endsection

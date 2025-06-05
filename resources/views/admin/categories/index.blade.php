@extends('admin.layouts.app')

@section('title', 'Categories Management')

@push('styles')
<!-- Custom styles for this page -->
<link href="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<style>
    .category-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }

    .category-name {
        font-weight: 500;
        color: #4e73df;
    }

    .badge {
        font-weight: 500;
        padding: 6px 10px;
    }

    .badge-success {
        background-color: #1cc88a;
    }

    .badge-warning {
        background-color: #f6c23e;
        color: #fff;
    }

    .badge-danger {
        background-color: #e74a3b;
    }

    .badge-secondary {
        background-color: #858796;
    }

    .badge-info {
        background-color: #36b9cc;
    }

    .badge-primary {
        background-color: #4e73df;
    }

    .dropdown-menu {
        min-width: 180px;
    }

    .badge-info {
        background-color: #36b9cc;
    }

    .badge-info a:hover {
        text-decoration: none;
    }

    #filterCollapse {
        transition: all 0.3s ease;
        position: relative;
        z-index: 1030;
    }

    .card-header .btn-outline-primary:hover {
        background-color: #4e73df;
        color: white;
    }
</style>
@endpush

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Categories Management</h1>
    <a href="{{ route('admin.categories.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add New Category
    </a>
</div>

<!-- DataTables Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Categories List</h6>
        <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse"
            data-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
            <i class="fas fa-filter fa-sm"></i> Filters
        </button>
    </div>
    <div class="card-body">
        <!-- Custom filter row -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <form id="perPageForm" action="{{ route('admin.categories.index') }}" method="GET" class="form-inline">
                    <label class="mr-2 text-nowrap">Show</label>
                    <select name="per_page" id="per_page" class="form-control form-control-sm"
                        onchange="document.getElementById('perPageForm').submit()">
                        <option value="5" {{ request('per_page')==5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page')==10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page')==15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page', 25)==25 ? 'selected' : '' }}>25</option>
                    </select>
                    <label class="ml-2 text-nowrap">entries</label>

                    @foreach(request()->except(['per_page', 'page', 'quick_search']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>
            </div>
            <div class="col-md-6">
                <form id="quickSearchForm" action="{{ route('admin.categories.index') }}" method="GET" class="form-inline justify-content-end">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="quick_search" class="form-control" placeholder="Quick search..."
                            value="{{ request('quick_search') }}"
                            onchange="document.getElementById('quickSearchForm').submit()">
                    </div>

                    @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    @endif
                    @foreach(request()->except(['per_page', 'page', 'quick_search']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12">
                <div id="searchContainer"></div>
            </div>
        </div>
        <!-- Active Filters Display -->
        @if(request('search') || request('parent_filter') || request('status_filter') || request('quick_search'))
        <div class="mb-3">
            <div class="d-flex flex-wrap align-items-center">
                <span class="mr-2">Active filters:</span>
                @if(request('quick_search'))
                <span class="badge badge-info mr-2 mb-1">
                    Quick search: {{ request('quick_search') }}
                    <a href="{{ route('admin.categories.index', array_merge(request()->except(['quick_search', 'page']), [])) }}"
                        class="text-white ml-1">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </span>
                @endif
                @if(request('search'))
                <span class="badge badge-info mr-2 mb-1">
                    Search: {{ request('search') }}
                    <a href="{{ route('admin.categories.index', array_merge(request()->except(['search', 'page']), [])) }}"
                        class="text-white ml-1">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </span>
                @endif

                @if(request('parent_filter'))
                <span class="badge badge-info mr-2 mb-1">
                    @if(request('parent_filter') == 'root')
                    Parent: Root Categories
                    @elseif(request('parent_filter') == 'children')
                    Parent: Subcategories
                    @else
                    @foreach($allCategories as $cat)
                    @if($cat->id == request('parent_filter'))
                    Parent: Children of {{ $cat->name }}
                    @endif
                    @endforeach
                    @endif
                    <a href="{{ route('admin.categories.index', array_merge(request()->except(['parent_filter', 'page']), [])) }}"
                        class="text-white ml-1">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </span>
                @endif

                @if(request('status_filter'))
                <span class="badge badge-info mr-2 mb-1">
                    Status: {{ ucfirst(request('status_filter')) }}
                    <a href="{{ route('admin.categories.index', array_merge(request()->except(['status_filter', 'page']), [])) }}"
                        class="text-white ml-1">
                        <i class="fas fa-times-circle"></i>
                    </a>
                </span>
                @endif

                <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary mb-1">
                    <i class="fas fa-times"></i> Clear All
                </a>
            </div>
        </div>
        @endif

        <!-- Filter Collapse -->
        <div class="collapse mb-4" id="filterCollapse">
            <div class="card card-body bg-light border-0">
                <form action="{{ route('admin.categories.index') }}" method="GET" id="categoryFilterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search">Search</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Search by name">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="parent_filter">Parent Category</label>
                                <select class="form-control" id="parent_filter" name="parent_filter">
                                    <option value="">All Categories</option>
                                    <option value="root" {{ request('parent_filter')=='root' ? 'selected' : '' }}>Root
                                        Categories Only</option>
                                    <option value="children" {{ request('parent_filter')=='children' ? 'selected' : ''
                                        }}>Subcategories Only</option>
                                    @foreach($allCategories as $parentCategory)
                                    <option value="{{ $parentCategory->id }}" {{
                                        request('parent_filter')==$parentCategory->id ? 'selected' : '' }}>
                                        Children of: {{ $parentCategory->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status_filter">Status</label>
                                <select class="form-control" id="status_filter" name="status_filter">
                                    <option value="">All Statuses</option>
                                    <option value="active" {{ request('status_filter')=='active' ? 'selected' : '' }}>
                                        Active</option>
                                    <option value="inactive" {{ request('status_filter')=='inactive' ? 'selected' : ''
                                        }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            @if(request('per_page'))
                            <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                            @endif
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Parent Category</th>
                        <th>Products Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>                        <td>
                            @include('admin.components.image-with-fallback', [
                                'src' => $category->image_url,
                                'alt' => $category->name,
                                'type' => 'category',
                                'class' => 'category-image',
                                'style' => 'width: 50px; height: 50px; object-fit: cover; border-radius: 4px;'
                            ])
                        </td>
                        <td class="category-name">{{ $category->name }}</td>
                        <td>
                            @if($category->parent)
                            <span class="badge badge-info">{{ $category->parent->name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $category->products_count }}</span>
                        </td>
                        <td>                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                    data-toggle="dropdown">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.categories.show', $category->id) }}">
                                        <i class="fas fa-eye fa-sm mr-2"></i> View
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.categories.edit', $category->id) }}">
                                        <i class="fas fa-edit fa-sm mr-2"></i> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form id="delete-form-{{ $category->id }}" action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                    </form>                                    <button type="button" class="dropdown-item text-danger"
                                        onclick="showDeleteModal({{ $category->id }}, '{{ addslashes($category->name) }}', 'category')">
                                        <i class="fas fa-trash fa-sm mr-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No categories found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination section -->
        @if(isset($categories) && method_exists($categories, 'links'))
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{
                $categories->total()
                ?? 0 }} entries
            </div>
            <div>
                {{ $categories->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif    </div>
</div>
@endsection

@push('styles')
<!-- Additional styles -->
<style>
    .dataTables_filter input {
        width: 300px;
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
        border: 1px solid #d1d3e2;
    }

    .pagination {
        margin-bottom: 0;
    }

    .dropdown-item {
        padding: 0.5rem 1.25rem;
    }

    .dropdown-toggle::after {
        margin-left: 0.5em;
    }

    /* Fix for action button hover styling */
    .btn-outline-primary:hover,
    .btn-outline-primary:active,
    .btn-outline-primary.active,
    .btn-outline-primary:focus {
        color: #fff !important;
    }
</style>
@endpush

@section('scripts')
<!-- Page level plugins -->
{{-- <script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script> --}}
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Page level custom scripts -->
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#dataTable').DataTable({
        "order": [[ 0, "asc" ]], // Order by ID from smallest to largest
        "pageLength": 25,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [1, 5] } // Disable ordering for Image and Actions columns
        ],
        "paging": false,
        "info": false,
        "lengthChange": false,
        "dom": 'frt', // Custom DOM layout - filter (f), table (r) and table content (t)
        "language": {
            "search": "Search categories:",
            "searchPlaceholder": "Enter search terms...",
            "emptyTable": "No categories found",
            "zeroRecords": "No matching categories found"
        },
        "initComplete": function() {
            // Move the search box to our custom container
            var searchBox = $('.dataTables_filter');
            searchBox.detach().appendTo('#searchContainer');
            searchBox.addClass('text-right');
        }
    });
    
    // Auto-expand filter if any filters are active
    if (
        "{{ request('search') }}".length > 0 || 
        "{{ request('parent_filter') }}".length > 0 || 
        "{{ request('status_filter') }}".length > 0
    ) {
        $('#filterCollapse').collapse('show');
    }
    
    // Handle filter toggle button styling
    $('#filterCollapse').on('show.bs.collapse', function () {
        $('[data-target="#filterCollapse"]').addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
    });
      $('#filterCollapse').on('hide.bs.collapse', function () {
        $('[data-target="#filterCollapse"]').removeClass('active').removeClass('btn-primary').addClass('btn-outline-primary');
    });
});
</script>
@endsection
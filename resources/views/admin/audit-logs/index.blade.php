@extends('admin.layouts.app')

@section('title', 'Audit Logs')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Admin Activity Audit Logs</h1>
        <p class="mb-0 text-muted small">Comprehensive audit trail of all administrative activities</p>
    </div>
    <div>
        <button type="button" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#filterModal">
            <i class="fas fa-filter fa-sm text-white-50"></i> Filters
        </button>
        @if(request()->anyFilled(['admin_id', 'action', 'date_from', 'date_to', 'resource']))
            <a href="{{ route('admin.audit-logs.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-outline-secondary shadow-sm ml-2">
                <i class="fas fa-times fa-sm"></i> Clear Filters
            </a>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Logs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_logs']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today's Activity</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['today_logs']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">This Week</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['this_week_logs']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Admins</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['unique_admins']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- Active Filters Display -->
@if(request()->anyFilled(['admin_id', 'action', 'date_from', 'date_to', 'resource']))
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <strong class="mr-3 text-gray-800">Active Filters:</strong>
                <div class="d-flex flex-wrap">
                    @if($adminFilter)
                        <span class="badge badge-primary mr-2 mb-1">Admin: {{ $admins->find($adminFilter)->username ?? 'Unknown' }}</span>
                    @endif
                    @if($actionFilter)
                        <span class="badge badge-primary mr-2 mb-1">Action: {{ $actionFilter }}</span>
                    @endif
                    @if($resourceFilter)
                        <span class="badge badge-primary mr-2 mb-1">Resource: {{ $resourceFilter }}</span>
                    @endif
                    @if($dateFrom)
                        <span class="badge badge-primary mr-2 mb-1">From: {{ $dateFrom }}</span>
                    @endif
                    @if($dateTo)
                        <span class="badge badge-primary mr-2 mb-1">To: {{ $dateTo }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Audit Logs List -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Activity Logs</h6>
        <div class="text-muted small">
            <i class="fas fa-info-circle mr-1"></i>
            Comprehensive audit trail
        </div>
    </div>
    <div class="card-body">
        @if($auditLogs->count() > 0)
            <!-- Entries per page and pagination info -->
            <div class="row mb-3 align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="pagination-info text-muted">
                            Showing {{ $auditLogs->firstItem() ?? 0 }} to {{ $auditLogs->lastItem() ?? 0 }} 
                            of {{ $auditLogs->total() }} entries
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-right">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#filterModal">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                        @if(request()->anyFilled(['admin_id', 'action', 'date_from', 'date_to', 'resource']))
                            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-outline-secondary ml-2">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Action</th>
                            <th>Resource</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                        <tr>
                            <td>
                                <div>
                                    <span class="font-weight-bold">{{ $log->created_at->format('M j, Y') }}</span><br>
                                    <small class="text-muted">{{ $log->created_at->format('g:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                @php
                                    $actionColors = [
                                        'create' => 'success',
                                        'update' => 'warning', 
                                        'delete' => 'danger',
                                        'view' => 'info',
                                        'login' => 'primary',
                                        'logout' => 'secondary'
                                    ];
                                    $color = $actionColors[$log->action] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $color }}">{{ ucfirst($log->action) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-outline-primary">{{ ucfirst(str_replace('_', ' ', $log->resource)) }}</span>
                            </td>
                            <td>
                                <div class="text-wrap" style="max-width: 300px; word-wrap: break-word;">
                                    {{ $log->description ?: 'No description available' }}
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">{{ $log->ip_address ?: 'N/A' }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.audit-logs.show', $log->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info and Controls -->
            <div class="row mt-4 align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="pagination-info text-muted">
                            Showing {{ $auditLogs->firstItem() ?? 0 }} to {{ $auditLogs->lastItem() ?? 0 }} 
                            of {{ $auditLogs->total() }} entries
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <nav aria-label="Audit logs pagination">
                            {{ $auditLogs->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </nav>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-clipboard-text fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No audit logs found</h5>
                <p class="text-muted">
                    @if(request()->anyFilled(['admin_id', 'action', 'date_from', 'date_to', 'resource']))
                        No administrative activities match your current filters.
                    @else
                        No administrative activities have been logged yet.
                    @endif
                </p>
                @if(request()->anyFilled(['admin_id', 'action', 'date_from', 'date_to', 'resource']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-outline-primary">
                        Clear Filters
                    </a>
                @endif
            </div>
        @endif

    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Audit Logs</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="GET" action="{{ route('admin.audit-logs.index') }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="admin_id">Admin</label>
                                <select class="form-control" id="admin_id" name="admin_id">
                                    <option value="">All Admins</option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}" {{ $adminFilter == $admin->id ? 'selected' : '' }}>
                                            {{ $admin->username }} ({{ $admin->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="action">Action</label>
                                <input type="text" class="form-control" id="action" name="action" 
                                       value="{{ $actionFilter }}" placeholder="e.g., create, update, delete">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="resource">Resource</label>
                                <select class="form-control" id="resource" name="resource">
                                    <option value="">All Resources</option>
                                    @foreach($resources as $resource)
                                        <option value="{{ $resource }}" {{ $resourceFilter == $resource ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $resource)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Empty for spacing -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_from">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_to">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Additional styles for consistent appearance -->
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
    
    /* Badge styling improvements */
    .badge-outline-primary {
        color: #4e73df;
        border: 1px solid #4e73df;
        background-color: transparent;
    }
    
    /* Table styling improvements */
    .table th {
        background-color: #f8f9fc;
        border-color: #e3e6f0;
        font-weight: 600;
        font-size: 0.875rem;
        color: #5a5c69;
    }
    
    .table td {
        vertical-align: middle;
        border-color: #e3e6f0;
    }
    
    /* Card border colors to match dashboard */
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
</style>
@endpush

@section('scripts')
<script>
$(document).ready(function() {
    // Remove auto-refresh to avoid disrupting user interaction
    // Auto-refresh can be manually triggered if needed
});
</script>

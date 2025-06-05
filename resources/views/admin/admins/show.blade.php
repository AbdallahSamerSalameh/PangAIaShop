@extends('admin.layouts.app')

@section('title', 'Admin Details - ' . $admin->username)

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Admin Details: {{ $admin->username }}</h1>
    <div>
        <a href="{{ route('admin.admins.edit', $admin->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm mr-2 text-white">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Admin
        </a>
        <a href="{{ route('admin.admins.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <!-- Admin Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="adminActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="adminActions">
                        <a class="dropdown-item" href="{{ route('admin.admins.edit', $admin->id) }}">
                            <i class="fas fa-edit fa-sm mr-2 text-gray-400"></i> Edit Admin
                        </a>                        @if($admin->id !== auth('admin')->id())
                            <div class="dropdown-divider"></div>
                            <button type="button" class="dropdown-item text-danger" onclick="showDeleteModal('{{ $admin->id }}', '{{ addslashes($admin->username) }}', 'admin')">
                                <i class="fas fa-trash fa-sm mr-2 text-danger"></i> Delete Admin
                            </button>
                            <!-- Hidden form for universal delete modal -->
                            <form id="delete-form-{{ $admin->id }}" action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">                <div class="row">                    <div class="col-md-2 text-center">
                        @php
                            $adminProfileImage = $admin->profile_image ? asset('storage/' . $admin->profile_image) : ($admin->avatar_url ?? null);
                        @endphp
                        @include('admin.components.image-with-fallback', [
                            'src' => $adminProfileImage,
                            'alt' => $admin->username,
                            'type' => 'profile',
                            'class' => 'rounded-circle mb-3',
                            'style' => 'width: 80px; height: 80px; object-fit: cover;'
                        ])
                    </div>
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Username:</label>
                                    <p class="mb-1">{{ $admin->username }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Email:</label>
                                    <p class="mb-1">{{ $admin->email }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Role:</label>
                                    <p class="mb-1">
                                        @if($admin->role === 'Super Admin')
                                            <span class="badge badge-danger">{{ $admin->role }}</span>
                                        @else
                                            <span class="badge badge-primary">{{ $admin->role }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Status:</label>
                                    <p class="mb-1">
                                        @if($admin->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Phone Number:</label>
                                    <p class="mb-1">{{ $admin->phone_number ?: 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Two Factor Auth:</label>
                                    <p class="mb-1">
                                        @if($admin->two_factor_verified)
                                            <span class="badge badge-success">Enabled</span>
                                            <small class="text-muted">({{ ucfirst($admin->two_factor_method) }})</small>
                                        @else
                                            <span class="badge badge-warning">Disabled</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>        </div>

        <!-- Activity Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">Activity Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Last Login:</label>                            <p class="mb-1">
                                @if($admin->last_login)
                                    {{ $admin->last_login->format('F j, Y g:i A') }}
                                    <small class="text-muted">({{ $admin->last_login->diffForHumans() }})</small>
                                @else
                                    <span class="text-muted">Never logged in</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Failed Login Attempts:</label>
                            <p class="mb-1">
                                @if($admin->failed_login_count > 0)
                                    <span class="text-warning">{{ $admin->failed_login_count }}</span>
                                @else
                                    <span class="text-success">0</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Account Created:</label>                            <p class="mb-1">
                                {{ $admin->created_at->format('F j, Y g:i A') }}
                                <small class="text-muted">({{ $admin->created_at->diffForHumans() }})</small>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Last Updated:</label>                            <p class="mb-1">                                {{ $admin->updated_at->format('F j, Y g:i A') }}
                                <small class="text-muted">({{ $admin->updated_at->diffForHumans() }})</small>
                            </p>
                        </div>
                    </div>
                    @if($admin->last_password_change)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Last Password Change:</label>                                <p class="mb-1">
                                    {{ $admin->last_password_change->format('F j, Y g:i A') }}
                                    <small class="text-muted">({{ $admin->last_password_change->diffForHumans() }})</small>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>        <!-- Recent Activity Logs -->
        @if($auditLogs->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Recent Activity (Last 50 actions)</h6>
                    <a href="{{ route('admin.audit-logs.index', ['admin_id' => $admin->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>View All Activities
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-black" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th>Action</th>
                                    <th>Resource</th>
                                    <th>Description</th>
                                    <th>Date/Time</th>
                                    <th>IP Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($auditLogs as $log)
                                    <tr>
                                        <td>
                                            <span class="badge text-white bg-{{ $log->getActionColor() }}">
                                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">{{ ucfirst(str_replace('_', ' ', $log->resource)) }}</span>
                                            @if($log->resource_id)
                                                <small class="text-muted d-block">#{{ $log->resource_id }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->description)
                                                <span class="text-dark">{{ Str::limit($log->description, 60) }}</span>
                                                @if(strlen($log->description) > 60)
                                                    <span class="text-muted">...</span>
                                                @endif
                                            @else
                                                <span class="text-muted">{{ ucfirst($log->action) }} {{ $log->resource }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">{{ $log->created_at->format('M j, Y') }}</span>
                                            <small class="text-muted d-block">{{ $log->created_at->format('g:i A') }}</small>
                                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <code class="bg-light px-2 py-1 rounded">{{ $log->ip_address }}</code>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.audit-logs.show', $log->id) }}" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($auditLogs->count() >= 50)
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Showing the most recent 50 activities. 
                            <a href="{{ route('admin.audit-logs.index', ['admin_id' => $admin->id]) }}" class="font-weight-bold">
                                View complete activity history
                            </a> to see all activities for this admin.
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Activity Logs</h6>
                </div>
                <div class="card-body text-center py-4">
                    <i class="fas fa-history fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">No activity logs found for this admin.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

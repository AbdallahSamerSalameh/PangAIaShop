@extends('admin.layouts.app')

@section('title', 'Audit Log Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Audit Log Details</h1>
                    <p class="text-muted">Detailed information about this audit log entry</p>
                </div>
                <div>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Audit Logs
                    </a>
                </div>
            </div>

            <!-- Audit Log Details Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Audit Log Entry #{{ $auditLog->id }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-clipboard-list me-2"></i>Basic Information
                            </h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted" style="width: 30%;">ID:</td>
                                    <td>{{ $auditLog->id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Admin:</td>
                                    <td>
                                        @if($auditLog->admin)
                                            <a href="{{ route('admin.admins.show', $auditLog->admin) }}" class="text-decoration-none">
                                                {{ $auditLog->admin->name }}
                                            </a>
                                            <small class="text-muted d-block">{{ $auditLog->admin->email }}</small>
                                        @else
                                            <span class="text-danger">Admin Deleted</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Action:</td>
                                    <td>
                                        <span class="badge bg-{{ $auditLog->getActionColor() }} fs-6">
                                            {{ ucfirst($auditLog->action) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Model:</td>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded">{{ $auditLog->model }}</code>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Model ID:</td>
                                    <td>
                                        @if($auditLog->model_id)
                                            <code class="bg-light px-2 py-1 rounded">{{ $auditLog->model_id }}</code>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Timestamp:</td>
                                    <td>
                                        <span class="fw-bold">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</span>
                                        <small class="text-muted d-block">{{ $auditLog->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Description and Details -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-file-alt me-2"></i>Description
                            </h6>
                            @if($auditLog->description)
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ $auditLog->description }}
                                </div>
                            @else
                                <div class="alert alert-secondary">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No description available for this action.
                                </div>
                            @endif

                            <!-- IP Address and User Agent -->
                            <h6 class="text-primary mb-3 mt-4">
                                <i class="fas fa-network-wired me-2"></i>Technical Details
                            </h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted" style="width: 30%;">IP Address:</td>
                                    <td>
                                        @if($auditLog->ip_address)
                                            <code class="bg-light px-2 py-1 rounded">{{ $auditLog->ip_address }}</code>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">User Agent:</td>
                                    <td>
                                        @if($auditLog->user_agent)
                                            <small class="text-muted">{{ Str::limit($auditLog->user_agent, 50) }}</small>
                                            @if(strlen($auditLog->user_agent) > 50)
                                                <button class="btn btn-sm btn-outline-secondary ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#fullUserAgent">
                                                    Show Full
                                                </button>
                                                <div class="collapse mt-2" id="fullUserAgent">
                                                    <div class="card card-body bg-light">
                                                        <small>{{ $auditLog->user_agent }}</small>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Changes Data -->
                    @if($auditLog->changes)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-exchange-alt me-2"></i>Changes Made
                                </h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <pre class="bg-white p-3 rounded border"><code>{{ json_encode(json_decode($auditLog->changes), JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Related Actions -->
                    @if($relatedLogs && $relatedLogs->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-link me-2"></i>Related Actions
                                    <small class="text-muted">(Same model and ID)</small>
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Admin</th>
                                                <th>Action</th>
                                                <th>Description</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($relatedLogs as $log)
                                                <tr class="{{ $log->id == $auditLog->id ? 'table-warning' : '' }}">
                                                    <td>
                                                        <small>{{ $log->created_at->format('M d, H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        @if($log->admin)
                                                            {{ $log->admin->name }}
                                                        @else
                                                            <span class="text-danger">Deleted</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $log->getActionColor() }}">
                                                            {{ ucfirst($log->action) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>{{ Str::limit($log->description ?? 'No description', 50) }}</small>
                                                    </td>
                                                    <td>
                                                        @if($log->id != $auditLog->id)
                                                            <a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                                                View
                                                            </a>
                                                        @else
                                                            <span class="badge bg-warning">Current</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Timeline View -->
                    @if($timelineLogs && $timelineLogs->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-clock me-2"></i>Recent Activities by {{ $auditLog->admin ? $auditLog->admin->name : 'This Admin' }}
                                </h6>
                                <div class="timeline">
                                    @foreach($timelineLogs as $log)
                                        <div class="timeline-item {{ $log->id == $auditLog->id ? 'timeline-item-current' : '' }}">
                                            <div class="timeline-marker">
                                                <span class="badge bg-{{ $log->getActionColor() }}">
                                                    {{ substr($log->action, 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            {{ ucfirst($log->action) }} {{ class_basename($log->model) }}
                                                            @if($log->id == $auditLog->id)
                                                                <span class="badge bg-warning ms-2">Current</span>
                                                            @endif
                                                        </h6>
                                                        <p class="text-muted mb-1">{{ $log->description ?? 'No description' }}</p>
                                                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    @if($log->id != $auditLog->id)
                                                        <a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                                            View
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item-current {
    background: rgba(255, 193, 7, 0.1);
    border-radius: 8px;
    padding: 15px;
    margin-left: -15px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: white;
    border: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-content {
    padding-left: 15px;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.2) !important;
}
</style>
@endsection

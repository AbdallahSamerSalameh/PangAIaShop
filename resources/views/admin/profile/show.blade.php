@extends('admin.layouts.app')

@section('title', 'My Profile')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-circle mr-2"></i>My Profile</h1>
    <div>
        <span class="badge badge-{{ $admin->role === 'Super Admin' ? 'danger' : 'primary' }} badge-pill px-3 py-2">
            <i class="fas fa-crown mr-1"></i>{{ $admin->role }}
        </span>
    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row">
    <!-- Profile Information Card -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Profile Picture</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#profileImageModal">
                            <i class="fas fa-camera fa-sm fa-fw mr-2 text-gray-400"></i>
                            Change Picture
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body text-center">                <div class="position-relative d-inline-block mb-3">
                    @php
                        $profileImage = $admin->profile_image ?? $admin->avatar_url ?? null;
                    @endphp
                    
                    @if($profileImage)
                        @include('admin.components.image-with-fallback', [
                            'src' => $profileImage,
                            'alt' => 'Profile Picture',
                            'type' => 'profile',
                            'class' => 'img-profile rounded-circle border border-primary',
                            'style' => 'width: 120px; height: 120px; object-fit: cover;'
                        ])
                    @else
                        <div class="img-profile rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center text-white" style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;">
                            {{ strtoupper(substr($admin->username, 0, 1)) }}
                        </div>
                    @endif
                    <span class="position-absolute" style="bottom: 0; right: 10px;">
                        <span class="badge badge-{{ $admin->is_active ? 'success' : 'danger' }} badge-pill">
                            <i class="fas fa-circle fa-xs mr-1"></i>{{ $admin->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </span>
                </div>
                <h5 class="font-weight-bold mb-1">{{ $admin->username }}</h5>
                <p class="text-muted mb-2">{{ $admin->email }}</p>
                <p class="text-muted small mb-3">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Joined {{ $admin->created_at->format('M d, Y') }}
                </p>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editProfileModal">
                    <i class="fas fa-edit mr-1"></i> Edit Profile
                </button>
            </div>
        </div>        <!-- Account Statistics -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-right">
                            <h4 class="mb-1 text-primary">{{ $activityStats['total_activities'] }}</h4>
                            <small class="text-muted">Total Actions</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-1 text-success">
                            {{ $admin->last_login ? $admin->last_login->diffForHumans() : 'Never' }}
                        </h4>
                        <small class="text-muted">Last Login</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <h5 class="mb-1 text-info">{{ $activityStats['today_activities'] }}</h5>
                        <small class="text-muted">Today</small>
                    </div>
                    <div class="col-4">
                        <h5 class="mb-1 text-warning">{{ $activityStats['this_week_activities'] }}</h5>
                        <small class="text-muted">This Week</small>
                    </div>
                    <div class="col-4">
                        <h5 class="mb-1 text-secondary">{{ $activityStats['last_30_days_activities'] }}</h5>
                        <small class="text-muted">Last 30 Days</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="fas fa-key mr-1"></i>
                            Password last changed: 
                            {{ $admin->last_password_change ? $admin->last_password_change->format('M d, Y') : 'Never' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Breakdown -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Activity Breakdown</h6>
            </div>
            <div class="card-body">
                @if($activityBreakdown->count() > 0)
                    @foreach($activityBreakdown as $activity)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-black-600">
                                <span class="badge bg-{{ (new \App\Models\AdminAuditLog(['action' => $activity->action]))->getActionColor() }} me-2">
                                    {{ ucfirst($activity->action) }}
                                </span>
                            </span>
                            <span class="font-weight-bold">{{ $activity->count }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">No activity data available.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Profile Details and Settings -->
    <div class="col-lg-8">
        <!-- Profile Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-gray-600">Username</label>
                        <div class="form-control-plaintext">{{ $admin->username }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-gray-600">Email</label>
                        <div class="form-control-plaintext">{{ $admin->email }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-gray-600">Phone Number</label>
                        <div class="form-control-plaintext">{{ $admin->phone_number ?: 'Not provided' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-gray-600">Role</label>
                        <div class="form-control-plaintext">
                            <span class="badge badge-{{ $admin->role === 'Super Admin' ? 'danger' : 'primary' }}">
                                {{ $admin->role }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-gray-600">Account Status</label>
                        <div class="form-control-plaintext">
                            <span class="badge badge-{{ $admin->is_active ? 'success' : 'danger' }}">
                                {{ $admin->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-gray-600">Member Since</label>
                        <div class="form-control-plaintext">{{ $admin->created_at->format('F d, Y') }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#editProfileModal">
                        <i class="fas fa-edit mr-1"></i> Edit Profile
                    </button>
                    <button class="btn btn-warning ml-2" data-toggle="modal" data-target="#changePasswordModal">
                        <i class="fas fa-key mr-1"></i> Change Password
                    </button>
                </div>
            </div>
        </div>        <!-- Recent Activity -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                @if(Auth::guard('admin')->user()->role === 'Super Admin')
                    <a href="{{ route('admin.audit-logs.index', ['admin_id' => $admin->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>View All
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if($recentActivities->count() > 0)
                    <div class="timeline">
                        @foreach($recentActivities as $activity)
                            <div class="d-flex mb-4">
                                <div class="mr-3">
                                    <div class="bg-{{ $activity->getActionColor() }} rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                        <i class="fas fa-{{ 
                                            str_contains(strtolower($activity->action), 'create') ? 'plus' : 
                                            (str_contains(strtolower($activity->action), 'update') || str_contains(strtolower($activity->action), 'edit') ? 'edit' : 
                                            (str_contains(strtolower($activity->action), 'delete') ? 'trash' : 
                                            (str_contains(strtolower($activity->action), 'view') || str_contains(strtolower($activity->action), 'access') ? 'eye' : 
                                            (str_contains(strtolower($activity->action), 'toggle') ? 'toggle-on' : 
                                            (str_contains(strtolower($activity->action), 'export') ? 'download' : 
                                            (str_contains(strtolower($activity->action), 'import') ? 'upload' : 'cog')))))) 
                                        }} fa-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="font-weight-bold text-gray-800">
                                                <span class="badge bg-{{ $activity->getActionColor() }} me-2">
                                                    {{ ucfirst($activity->action) }}
                                                </span>
                                                {{ ucfirst($activity->resource) }}
                                                @if($activity->resource_id)
                                                    <small class="text-muted">#{{ $activity->resource_id }}</small>
                                                @endif
                                            </div>
                                            @if($activity->description)
                                                <div class="text-muted small mb-1">
                                                    {{ $activity->description }}
                                                </div>
                                            @endif
                                            <div class="small text-muted">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ $activity->created_at->format('M d, Y \a\t H:i') }}
                                                <span class="mx-2">•</span>
                                                {{ $activity->created_at->diffForHumans() }}
                                                @if($activity->ip_address)
                                                    <span class="mx-2">•</span>
                                                    <i class="fas fa-globe mr-1"></i>
                                                    {{ $activity->ip_address }}
                                                @endif
                                            </div>
                                        </div>
                                        @if(Auth::guard('admin')->user()->role === 'Super Admin')
                                            <a href="{{ route('admin.audit-logs.show', $activity->id) }}" 
                                               class="btn btn-sm btn-outline-secondary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($recentActivities->count() >= 10)
                        <div class="text-center mt-3">
                            @if(Auth::guard('admin')->user()->role === 'Super Admin')
                                <a href="{{ route('admin.audit-logs.index', ['admin_id' => $admin->id]) }}" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-history mr-1"></i>View Complete Activity History
                                </a>
                            @else
                                <p class="text-muted">Showing last 20 activities</p>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-gray-300 mb-3"></i>
                        <p class="text-muted">No recent activity to display.</p>
                    </div>                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">
                    <i class="fas fa-edit mr-2"></i>Edit Profile
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username" class="font-weight-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                       id="username" name="username" value="{{ old('username', $admin->username) }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="font-weight-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone_number" class="font-weight-bold">Phone Number</label>
                                <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" value="{{ old('phone_number', $admin->phone_number) }}">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="profile_image" class="font-weight-bold">Profile Image</label>
                                <input type="file" class="form-control-file @error('profile_image') is-invalid @enderror" 
                                       id="profile_image" name="profile_image" accept="image/*">
                                @error('profile_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Max file size: 2MB. Accepted formats: JPG, PNG, GIF</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="fas fa-key mr-2"></i>Change Password
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="current_password" class="font-weight-bold">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password" class="font-weight-bold">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required minlength="8">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" class="font-weight-bold">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key mr-1"></i>Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Profile Image Modal -->
<div class="modal fade" id="profileImageModal" tabindex="-1" role="dialog" aria-labelledby="profileImageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileImageModalLabel">
                    <i class="fas fa-camera mr-2"></i>Change Profile Picture
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="username" value="{{ $admin->username }}">
                <input type="hidden" name="email" value="{{ $admin->email }}">
                <input type="hidden" name="phone_number" value="{{ $admin->phone_number }}">
                <div class="modal-body text-center">                    <div class="mb-3">
                        @php
                            $profileImage = $admin->profile_image ?? $admin->avatar_url ?? null;
                        @endphp
                        
                        @if($profileImage)
                            @include('admin.components.image-with-fallback', [
                                'src' => $profileImage,
                                'alt' => 'Current Profile Picture',
                                'type' => 'profile',
                                'class' => 'img-profile rounded-circle border',
                                'style' => 'width: 150px; height: 150px; object-fit: cover;'
                            ])
                        @else
                            <div class="img-profile rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center text-white mx-auto" style="width: 150px; height: 150px; font-size: 4rem; font-weight: bold;">
                                {{ strtoupper(substr($admin->username, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="profile_image_upload" class="font-weight-bold">Choose New Picture</label>
                        <input type="file" class="form-control-file @error('profile_image') is-invalid @enderror" 
                               id="profile_image_upload" name="profile_image" accept="image/*" required>
                        @error('profile_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Max file size: 2MB. Accepted formats: JPG, PNG, GIF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload mr-1"></i>Upload Picture
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Show modal if there are validation errors
    @if($errors->any())
        @if($errors->has('current_password') || $errors->has('password'))
            $('#changePasswordModal').modal('show');
        @elseif($errors->has('profile_image') && !$errors->has('username') && !$errors->has('email'))
            $('#profileImageModal').modal('show');
        @else
            $('#editProfileModal').modal('show');
        @endif
    @endif

    // Preview image before upload
    $('#profile_image, #profile_image_upload').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Create preview logic here if needed
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection

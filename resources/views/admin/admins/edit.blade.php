@extends('admin.layouts.app')

@section('title', 'Edit Admin - ' . $admin->username)

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Admin: {{ $admin->username }}</h1>
    <div>
        <a href="{{ route('admin.admins.show', $admin->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm mr-2 text-white">
            <i class="fas fa-eye fa-sm text-white-50"></i> View Details
        </a>
        <a href="{{ route('admin.admins.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Edit Admin Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Admin Details</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.admins.update', $admin->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                       id="username" name="username" value="{{ old('username', $admin->username) }}" 
                                       placeholder="e.g., john_admin" maxlength="100" required>
                                <small class="form-text text-muted">Max 100 characters. Use letters, numbers, and underscores only.</small>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $admin->email) }}" 
                                       placeholder="admin@example.com" maxlength="150" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="Leave blank to keep current password">
                                <small class="form-text text-muted">Minimum 8 characters. Leave blank to keep current password.</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Confirm New Password</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" 
                                       placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @if(count($roles) > 0)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Role <span class="text-danger">*</span></label>
                                    <select class="form-control @error('role') is-invalid @enderror" 
                                            id="role" name="role" required>
                                        @foreach($roles as $role)
                                            <option value="{{ $role }}" {{ old('role', $admin->role) == $role ? 'selected' : '' }}>
                                                {{ $role }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        Super Admin has full access. Admin has limited access.
                                    </small>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @else
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Role:</label>
                                    <p class="form-control-plaintext">{{ $admin->role }}</p>
                                    <small class="form-text text-muted">You can only edit your own profile details, not your role.</small>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" value="{{ old('phone_number', $admin->phone_number) }}" 
                                       placeholder="+1-555-123-4567" maxlength="20">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    @if(count($roles) > 0 && $admin->id !== auth('admin')->id())
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="is_active">Status</label>
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                               {{ old('is_active', $admin->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                    <small class="form-text text-muted">Inactive admins cannot log in to the system.</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    <hr>
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Update Admin
                        </button>
                        <a href="{{ route('admin.admins.show', $admin->id) }}" class="btn btn-info ml-2 text-white">
                            <i class="fas fa-eye mr-2 text-white"></i> View Details
                        </a>
                        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary ml-2">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Current Info -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-user mr-2"></i> Current Information
                </h6>
            </div>
            <div class="card-body">                <div class="text-center mb-3">
                    @if($admin->profile_image)
                        <img src="{{ asset('storage/' . $admin->profile_image) }}" alt="{{ $admin->username }}" 
                             class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                    @elseif($admin->avatar_url)
                        <img src="{{ $admin->avatar_url }}" alt="{{ $admin->username }}" 
                             class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                    @else
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white"
                             style="width: 80px; height: 80px; font-size: 24px;">
                            {{ strtoupper(substr($admin->username, 0, 2)) }}
                        </div>
                    @endif
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-right">
                            <h6 class="font-weight-bold text-primary">{{ $admin->role }}</h6>
                            <small class="text-muted">Current Role</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="font-weight-bold {{ $admin->is_active ? 'text-success' : 'text-secondary' }}">
                            {{ $admin->is_active ? 'Active' : 'Inactive' }}
                        </h6>
                        <small class="text-muted">Current Status</small>
                    </div>
                </div>
                
                <hr>
                <small class="text-muted">
                    <strong>Created:</strong> {{ $admin->created_at->format('M j, Y g:i A') }}<br>
                    <strong>Last Login:</strong> 
                    @if($admin->last_login)
                        {{ $admin->last_login->format('M j, Y g:i A') }}
                    @else
                        Never
                    @endif
                </small>
            </div>
        </div>

        <!-- Warning Messages -->
        @if($admin->id === auth('admin')->id())
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Editing Your Profile</strong><br>
                You are editing your own admin profile. Some restrictions may apply.
            </div>
        @endif

        @if(count($roles) > 0 && $admin->role === 'Super Admin')
            @php
                $superAdminCount = \App\Models\Admin::where('role', 'Super Admin')->where('is_active', true)->count();
            @endphp
            @if($superAdminCount <= 1)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Last Super Admin!</strong><br>
                    This is the last active Super Admin. Role cannot be changed.
                </div>
            @endif
        @endif

        @if(!$admin->is_active)
            <div class="alert alert-warning">
                <i class="fas fa-pause-circle mr-2"></i>
                <strong>Account Inactive</strong><br>
                This admin account is currently disabled.
            </div>
        @endif

        @if($admin->failed_login_count >= 3)
            <div class="alert alert-danger">
                <i class="fas fa-shield-alt mr-2"></i>
                <strong>Security Alert</strong><br>
                This account has {{ $admin->failed_login_count }} failed login attempts.
            </div>
        @endif

        <!-- Help -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-lightbulb mr-2"></i> Tips
                </h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Leave password fields blank to keep current password</li>
                    <li>Username should be unique and memorable</li>
                    <li>Use a valid email for password recovery</li>
                    @if(count($roles) > 0)
                        <li>Super Admins can manage other admins</li>
                        <li>Regular Admins have limited access</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Password strength indicator (same as create page)
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        var strengthText = '';
        var strengthClass = '';
        
        switch(strength) {
            case 0:
            case 1:
                strengthText = 'Very Weak';
                strengthClass = 'text-danger';
                break;
            case 2:
                strengthText = 'Weak';
                strengthClass = 'text-warning';
                break;
            case 3:
                strengthText = 'Fair';
                strengthClass = 'text-info';
                break;
            case 4:
                strengthText = 'Good';
                strengthClass = 'text-primary';
                break;
            case 5:
                strengthText = 'Strong';
                strengthClass = 'text-success';
                break;
        }
        
        var helpText = $('#password').siblings('.form-text');
        if (password.length > 0) {
            helpText.html('Minimum 8 characters. Strength: <span class="' + strengthClass + '">' + strengthText + '</span>');
        } else {
            helpText.html('Minimum 8 characters. Leave blank to keep current password.');
        }
    });
    
    // Warn about role changes for Super Admin
    $('#role').change(function() {
        var newRole = $(this).val();
        var currentRole = '{{ $admin->role }}';
        
        if (currentRole === 'Super Admin' && newRole !== 'Super Admin') {
            if (!confirm('Are you sure you want to remove Super Admin privileges? This action will reduce access levels.')) {
                $(this).val(currentRole);
            }
        }
    });
});
</script>
@endsection

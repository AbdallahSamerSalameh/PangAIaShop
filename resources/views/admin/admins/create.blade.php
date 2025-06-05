@extends('admin.layouts.app')

@section('title', 'Create New Admin')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Admin</h1>
    <div>
        <a href="{{ route('admin.admins.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Create Admin Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Admin Details</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.admins.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                       id="username" name="username" value="{{ old('username') }}" 
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
                                       id="email" name="email" value="{{ old('email') }}" 
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
                                <label for="password">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="Enter secure password" required>
                                <small class="form-text text-muted">Minimum 8 characters.</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" 
                                       placeholder="Confirm password" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role">Role <span class="text-danger">*</span></label>
                                <select class="form-control @error('role') is-invalid @enderror" 
                                        id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" name="phone_number" value="{{ old('phone_number') }}" 
                                       placeholder="+1-555-123-4567" maxlength="20">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                                <small class="form-text text-muted">Inactive admins cannot log in to the system.</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Create Admin
                        </button>
                        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary ml-2">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Help Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle mr-2"></i> Admin Roles
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-danger">Super Admin</h6>
                    <ul class="small text-muted mb-0">
                        <li>Full system access</li>
                        <li>Manage other admins</li>
                        <li>Access all features</li>
                        <li>View audit logs</li>
                    </ul>
                </div>
                <div>
                    <h6 class="text-primary">Admin</h6>
                    <ul class="small text-muted mb-0">
                        <li>Limited system access</li>
                        <li>Cannot manage other admins</li>
                        <li>Access most features</li>
                        <li>View limited audit logs</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Security Tips -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-shield-alt mr-2"></i> Security Tips
                </h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Use strong, unique passwords</li>
                    <li>Limit Super Admin accounts</li>
                    <li>Regularly review admin access</li>
                    <li>Enable 2FA when available</li>
                    <li>Monitor admin activity logs</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Password strength indicator
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
            helpText.html('Minimum 8 characters.');
        }
    });
});
</script>
@endsection

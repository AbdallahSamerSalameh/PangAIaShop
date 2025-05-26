@extends('admin.layouts.app')

@section('title', 'Admin Profile')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Admin Profile</h1>
</div>

<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profile Picture</h6>
            </div>
            <div class="card-body text-center">
                <img class="img-profile rounded-circle mb-3" style="width: 120px; height: 120px;"
                    src="{{ asset('admin-assets/img/undraw_profile.svg') }}" alt="Profile Picture">
                <h5 class="font-weight-bold">Admin User</h5>
                <p class="text-muted">Super Administrator</p>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-camera"></i> Change Picture
                </button>
            </div>
        </div>

        <!-- Account Status -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge badge-success">Active</span>
                    <span class="badge badge-info ml-2">Verified</span>
                </div>
                <div class="mb-2">
                    <strong>Last Login:</strong><br>
                    <small class="text-muted">January 20, 2024 at 2:30 PM</small>
                </div>
                <div class="mb-2">
                    <strong>Member Since:</strong><br>
                    <small class="text-muted">June 15, 2023</small>
                </div>
                <div class="mb-2">
                    <strong>Login Count:</strong><br>
                    <small class="text-muted">1,247 times</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Personal Information</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" class="form-control" id="first_name" value="Admin" placeholder="Enter first name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" class="form-control" id="last_name" value="User" placeholder="Enter last name">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" value="admin@pangaia.com" placeholder="Enter email address">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" value="+1 (555) 123-4567" placeholder="Enter phone number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select class="form-control" id="role" disabled>
                                    <option value="super_admin" selected>Super Administrator</option>
                                    <option value="admin">Administrator</option>
                                    <option value="moderator">Moderator</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea class="form-control" id="bio" rows="3" placeholder="Tell us about yourself">Experienced ecommerce administrator with expertise in managing online stores and digital marketing.</textarea>
                    </div>
                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select class="form-control" id="timezone">
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">America/New_York</option>
                            <option value="Europe/London">Europe/London</option>
                            <option value="Asia/Tokyo" selected>Asia/Tokyo</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" class="form-control" id="current_password" placeholder="Enter current password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" placeholder="Confirm new password">
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            Password must be at least 8 characters long and contain uppercase, lowercase, numbers, and special characters.
                        </small>
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>

        <!-- Two-Factor Authentication -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Two-Factor Authentication</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Two-Factor Authentication</h6>
                        <p class="text-muted mb-0">Add an extra layer of security to your account</p>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="enable_2fa" checked>
                        <label class="custom-control-label" for="enable_2fa"></label>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <span class="badge badge-success">Enabled</span></p>
                        <p><strong>Method:</strong> Authenticator App</p>
                        <p><strong>Backup Codes:</strong> 5 remaining</p>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-secondary btn-sm mb-2">View Backup Codes</button><br>
                        <button class="btn btn-warning btn-sm mb-2">Regenerate Codes</button><br>
                        <button class="btn btn-danger btn-sm">Disable 2FA</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Password strength indicator
    $('#new_password').on('input', function() {
        const password = $(this).val();
        // Add password strength logic here
    });

    // 2FA toggle
    $('#enable_2fa').change(function() {
        if ($(this).is(':checked')) {
            // Show 2FA setup modal or redirect
            console.log('Enable 2FA');
        } else {
            // Show disable confirmation
            console.log('Disable 2FA');
        }
    });
});
</script>
@endsection

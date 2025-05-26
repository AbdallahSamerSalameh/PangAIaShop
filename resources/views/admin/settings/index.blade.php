@extends('admin.layouts.app')

@section('title', 'System Settings')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">System Settings</h1>
</div>

<div class="row">
    <!-- General Settings -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" class="form-control" id="site_name" value="PangAIa Shop" placeholder="Enter site name">
                    </div>
                    <div class="form-group">
                        <label for="site_email">Site Email</label>
                        <input type="email" class="form-control" id="site_email" value="admin@pangaia.com" placeholder="Enter site email">
                    </div>
                    <div class="form-group">
                        <label for="site_description">Site Description</label>
                        <textarea class="form-control" id="site_description" rows="3" placeholder="Enter site description">Your premier ecommerce destination for quality products</textarea>
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
                    <button type="submit" class="btn btn-primary">Save General Settings</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Store Settings -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Store Settings</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label for="default_currency">Default Currency</label>
                        <select class="form-control" id="default_currency">
                            <option value="USD" selected>USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="JPY">JPY - Japanese Yen</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tax_rate">Default Tax Rate (%)</label>
                        <input type="number" class="form-control" id="tax_rate" value="8.5" step="0.1" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label for="shipping_cost">Default Shipping Cost</label>
                        <input type="number" class="form-control" id="shipping_cost" value="9.99" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label for="free_shipping_threshold">Free Shipping Threshold</label>
                        <input type="number" class="form-control" id="free_shipping_threshold" value="75.00" step="0.01" min="0">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Store Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Email Settings -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Email Settings</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label for="smtp_host">SMTP Host</label>
                        <input type="text" class="form-control" id="smtp_host" value="smtp.gmail.com" placeholder="Enter SMTP host">
                    </div>
                    <div class="form-group">
                        <label for="smtp_port">SMTP Port</label>
                        <input type="number" class="form-control" id="smtp_port" value="587" placeholder="Enter SMTP port">
                    </div>
                    <div class="form-group">
                        <label for="smtp_username">SMTP Username</label>
                        <input type="text" class="form-control" id="smtp_username" placeholder="Enter SMTP username">
                    </div>
                    <div class="form-group">
                        <label for="smtp_password">SMTP Password</label>
                        <input type="password" class="form-control" id="smtp_password" placeholder="Enter SMTP password">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="smtp_encryption" checked>
                            <label class="custom-control-label" for="smtp_encryption">Enable TLS Encryption</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Email Settings</button>
                    <button type="button" class="btn btn-secondary ml-2">Test Email</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Security Settings</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label for="session_timeout">Session Timeout (minutes)</label>
                        <input type="number" class="form-control" id="session_timeout" value="120" min="5" max="1440">
                    </div>
                    <div class="form-group">
                        <label for="max_login_attempts">Max Login Attempts</label>
                        <input type="number" class="form-control" id="max_login_attempts" value="5" min="1" max="20">
                    </div>
                    <div class="form-group">
                        <label for="lockout_duration">Lockout Duration (minutes)</label>
                        <input type="number" class="form-control" id="lockout_duration" value="15" min="1" max="1440">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="require_2fa" checked>
                            <label class="custom-control-label" for="require_2fa">Require Two-Factor Authentication</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="audit_logging" checked>
                            <label class="custom-control-label" for="audit_logging">Enable Audit Logging</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Security Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Mode -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Maintenance Mode</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="maintenance_mode">
                                <label class="custom-control-label" for="maintenance_mode">Enable Maintenance Mode</label>
                            </div>
                            <small class="form-text text-muted">When enabled, only administrators can access the site.</small>
                        </div>
                        <div class="form-group">
                            <label for="maintenance_message">Maintenance Message</label>
                            <textarea class="form-control" id="maintenance_message" rows="3" placeholder="Enter maintenance message">We are currently performing scheduled maintenance. Please check back soon!</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Warning</h6>
                            <p class="mb-0">Enabling maintenance mode will prevent customers from accessing your store. Use this feature only when necessary for updates or repairs.</p>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning">Update Maintenance Settings</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Add any custom JavaScript for settings page
    console.log('Settings page loaded');
});
</script>
@endsection

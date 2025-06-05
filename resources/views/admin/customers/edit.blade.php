@extends('admin.layouts.app')

@section('title', 'Edit Customer - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Customer</h1>
        <div class="d-flex">
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Back to Customer Details
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> All Customers
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                           id="username" name="username" value="{{ old('username', $customer->username) }}" required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $customer->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $customer->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="profile_image">Profile Image</label>
                            <input type="file" class="form-control-file @error('profile_image') is-invalid @enderror" 
                                   id="profile_image" name="profile_image" accept="image/*">
                            @error('profile_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($customer->profile_image)
                                <small class="form-text text-muted">Current image will be replaced if you upload a new one.</small>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Leave blank to keep current password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Only fill this field if you want to change the customer's password.</small>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" 
                                   placeholder="Confirm new password">
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Account Active
                                </label>
                            </div>
                            <small class="form-text text-muted">Unchecking this will disable the customer's account.</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Customer
                            </button>
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">            <!-- Current Profile Image -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Profile Image</h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $profileImage = $customer->profile_image ? asset('storage/' . $customer->profile_image) : ($customer->avatar_url ?? null);
                    @endphp
                    @include('admin.components.image-with-fallback', [
                        'src' => $profileImage,
                        'alt' => $customer->name ?? 'Customer Profile',
                        'type' => 'profile',
                        'class' => 'img-fluid rounded',
                        'style' => 'max-width: 200px;'
                    ])
                </div>
            </div>

            <!-- Customer Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Member Since:</small>
                        <p class="mb-1 font-weight-bold">{{ $customer->created_at->format('F d, Y') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Last Login:</small>
                        <p class="mb-1 font-weight-bold">
                            {{ $customer->last_login_at ? $customer->last_login_at->format('F d, Y h:i A') : 'Never' }}
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Total Orders:</small>
                        <p class="mb-1 font-weight-bold">{{ $customer->orders_count ?? $customer->orders->count() }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Account Status:</small>
                        <p class="mb-1">
                            <span class="badge {{ $customer->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.orders.index', ['customer' => $customer->id]) }}" 
                       class="btn btn-info btn-sm btn-block mb-2">
                        <i class="fas fa-shopping-cart"></i> View Orders
                    </a>
                    <a href="{{ route('admin.reviews.index', ['search' => $customer->email]) }}" 
                       class="btn btn-warning btn-sm btn-block mb-2">
                        <i class="fas fa-star"></i> View Reviews
                    </a>
                    <button type="button" class="btn btn-danger btn-sm btn-block" 
                            onclick="toggleCustomerStatus()">
                        @if($customer->is_active)
                            <i class="fas fa-ban"></i> Deactivate Account
                        @else
                            <i class="fas fa-check"></i> Activate Account
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    toastr.success('{{ session('success') }}');
</script>
@endif

@if(session('error'))
<script>
    toastr.error('{{ session('error') }}');
</script>
@endif

<script>
function toggleCustomerStatus() {
    const isActive = {{ $customer->is_active ? 'true' : 'false' }};
    const action = isActive ? 'deactivate' : 'activate';
    const message = `Are you sure you want to ${action} this customer account?`;
    
    if (confirm(message)) {
        // Toggle the checkbox
        document.getElementById('is_active').checked = !isActive;
        
        // Submit the form
        document.querySelector('form').submit();
    }
}
</script>
@endsection

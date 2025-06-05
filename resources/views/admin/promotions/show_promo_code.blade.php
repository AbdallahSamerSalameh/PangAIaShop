@extends('admin.layouts.app')

@section('title', 'Promo Code Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tag mr-2"></i>Promo Code Details
        </h1>        <div class="d-sm-flex">
            <a href="{{ route('admin.promotions.promo_codes.edit', $promoCode->id) }}" class="btn btn-primary btn-sm mr-2">
                <i class="fas fa-edit"></i> Edit Promo Code
            </a>
            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Promo Code Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Promo Code Information</h6>
                    <span class="badge badge-{{ $promoCode->is_active ? 'success' : 'danger' }} badge-lg">
                        {{ $promoCode->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light" value="{{ $promoCode->code }}" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $promoCode->code }}')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Type</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-info">{{ ucfirst($promoCode->discount_type) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Discount Value</label>
                                <p class="form-control-plaintext">
                                    @if($promoCode->discount_type === 'percentage')
                                        {{ $promoCode->discount_value }}%
                                    @else
                                        ${{ number_format($promoCode->discount_value, 2) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Minimum Order Amount</label>
                                <p class="form-control-plaintext">
                                    @if($promoCode->min_order_amount)
                                        ${{ number_format($promoCode->min_order_amount, 2) }}
                                    @else
                                        <span class="text-muted">No minimum</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Maximum Discount Amount</label>
                                <p class="form-control-plaintext">
                                    @if($promoCode->max_discount_amount)
                                        ${{ number_format($promoCode->max_discount_amount, 2) }}
                                    @else
                                        <span class="text-muted">No maximum cap</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><div class="form-group">
                                <label class="font-weight-bold text-dark">Usage Limit</label>
                                <p class="form-control-plaintext">
                                    @if($promoCode->max_uses)
                                        {{ number_format($promoCode->max_uses) }} uses
                                    @else
                                        <span class="text-muted">Unlimited</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Times Used</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-secondary">{{ $promoCode->usages->count() }} times</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Valid From</label>
                                <p class="form-control-plaintext">
                                    @if($promoCode->valid_from)
                                        {{ $promoCode->valid_from->format('M d, Y H:i') }}
                                    @else
                                        <span class="text-muted">No start date</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Valid Until</label>
                                <p class="form-control-plaintext">
                                    @if($promoCode->valid_until)
                                        {{ $promoCode->valid_until->format('M d, Y H:i') }}
                                        @if($promoCode->valid_until->isPast())
                                            <span class="badge badge-warning ml-2">Expired</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No expiry date</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($promoCode->description)
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Description</label>
                        <p class="form-control-plaintext">{{ $promoCode->description }}</p>
                    </div>
                    @endif                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Created</label>
                                <p class="form-control-plaintext text-muted">
                                    @if($promoCode->created_at)
                                        {{ $promoCode->created_at->format('M d, Y H:i') }}
                                    @else
                                        <span class="text-muted">No creation date</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Last Updated</label>
                                <p class="form-control-plaintext text-muted">
                                    @if($promoCode->updated_at)
                                        {{ $promoCode->updated_at->format('M d, Y H:i') }}
                                    @else
                                        <span class="text-muted">Never updated</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics & Quick Actions -->
        <div class="col-lg-4">
            <!-- Statistics Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Usage Statistics</h6>
                </div>
                <div class="card-body">                    <div class="text-center mb-3">
                        <div class="progress mb-2" style="height: 20px;">
                            @php
                                $usagePercentage = $promoCode->max_uses ? 
                                    min(100, ($promoCode->usages->count() / $promoCode->max_uses) * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-{{ $usagePercentage >= 80 ? 'danger' : ($usagePercentage >= 50 ? 'warning' : 'success') }}" 
                                 role="progressbar" style="width: {{ $usagePercentage }}%">
                                {{ number_format($usagePercentage, 1) }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $promoCode->usages->count() }} / 
                            {{ $promoCode->max_uses ? number_format($promoCode->max_uses) : '∞' }} uses
                        </small>
                    </div>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h5 class="text-primary">${{ number_format($totalSavings, 2) }}</h5>
                                <small class="text-muted">Total Savings</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-success">${{ number_format($totalRevenue, 2) }}</h5>
                            <small class="text-muted">Revenue Generated</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="{{ route('admin.promotions.promo_codes.toggle', $promoCode->id) }}" method="POST" class="mb-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $promoCode->is_active ? 'warning' : 'success' }} btn-sm btn-block">
                                <i class="fas fa-{{ $promoCode->is_active ? 'pause' : 'play' }}"></i>
                                {{ $promoCode->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <a href="{{ route('admin.promotions.promo_codes.edit', $promoCode->id) }}" class="btn btn-primary btn-sm btn-block mb-2">
                            <i class="fas fa-edit"></i> Edit Details
                        </a>

                        <button type="button" class="btn btn-info btn-sm btn-block mb-2" onclick="copyToClipboard('{{ $promoCode->code }}')">
                            <i class="fas fa-copy"></i> Copy Code
                        </button>                        <form id="delete-form-{{ $promoCode->id }}" action="{{ route('admin.promotions.promo_codes.destroy', $promoCode->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                        
                        <button type="button" class="btn btn-danger btn-sm btn-block" 
                                onclick="showDeleteModal('{{ $promoCode->id }}', '{{ addslashes($promoCode->code) }}', 'promo code', 'This will permanently delete the promo code and all its usage history.')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Usage History</h6>
        </div>
        <div class="card-body">
            @if($promoCode->usages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="usageTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Order Total</th>
                                <th>Discount Applied</th>
                                <th>Used At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promoCode->usages->load(['order', 'order.user']) as $usage)
                            <tr>
                                <td>
                                    <a href="#" class="text-primary font-weight-bold">
                                        #{{ $usage->order->id ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    @if($usage->order && $usage->order->user)
                                        {{ $usage->order->user->name }}
                                        <br>
                                        <small class="text-muted">{{ $usage->order->user->email }}</small>
                                    @else
                                        <span class="text-muted">Guest User</span>
                                    @endif
                                </td>
                                <td>${{ number_format($usage->order->total ?? 0, 2) }}</td>
                                <td>
                                    <span class="text-success font-weight-bold">
                                        -${{ number_format($usage->discount_amount, 2) }}
                                    </span>
                                </td>
                                <td>{{ $usage->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($usage->order)
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View Order
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Usage History</h5>
                    <p class="text-muted">This promo code hasn't been used yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <strong>Copied!</strong> Promo code copied to clipboard.
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Failed to copy to clipboard');
    });
}

$(document).ready(function() {
    // Initialize DataTable for usage history
    if ($('#usageTable tbody tr').length > 0) {
        $('#usageTable').DataTable({
            "order": [[ 4, "desc" ]], // Sort by 'Used At' column descending
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": 5 } // Disable sorting on Actions column
            ]
        });
    }
});
</script>
@endsection

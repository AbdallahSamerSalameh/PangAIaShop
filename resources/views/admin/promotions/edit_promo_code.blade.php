@extends('admin.layouts.app')

@section('title', 'Edit Promo Code')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Promo Code: {{ $promoCode->code }}</h1>    <div>
        <a href="{{ route('admin.promotions.promo_codes.show', $promoCode->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm mr-2 text-white">
            <i class="fas fa-eye fa-sm text-white-50"></i> View Details
        </a>
        <a href="{{ route('admin.promotions.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Edit Promo Code Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Promo Code Details</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.promotions.promo_codes.update', $promoCode->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">Promo Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code', $promoCode->code) }}" 
                                       placeholder="e.g., SUMMER2025" maxlength="50" required>
                                <small class="form-text text-muted">Use uppercase letters and numbers. Max 50 characters.</small>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="discount_type">Discount Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('discount_type') is-invalid @enderror" 
                                        id="discount_type" name="discount_type" required>
                                    <option value="">Select Discount Type</option>
                                    <option value="percentage" {{ old('discount_type', $promoCode->discount_type) == 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)
                                    </option>
                                    <option value="fixed" {{ old('discount_type', $promoCode->discount_type) == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount ($)
                                    </option>
                                    <option value="free_shipping" {{ old('discount_type', $promoCode->discount_type) == 'free_shipping' ? 'selected' : '' }}>
                                        Free Shipping
                                    </option>
                                </select>
                                @error('discount_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="discount_value">Discount Value <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend" id="discount_symbol">
                                        <span class="input-group-text">
                                            @if($promoCode->discount_type == 'percentage')%@elseif($promoCode->discount_type == 'fixed')$@endif
                                        </span>
                                    </div>
                                    <input type="number" class="form-control @error('discount_value') is-invalid @enderror" 
                                           id="discount_value" name="discount_value" 
                                           value="{{ old('discount_value', $promoCode->discount_value) }}" 
                                           step="0.01" min="0" required 
                                           @if($promoCode->discount_type == 'free_shipping') disabled @endif>
                                </div>
                                <small class="form-text text-muted" id="discount_help">
                                    @if($promoCode->discount_type == 'percentage')
                                        Enter the discount percentage (0-100).
                                    @elseif($promoCode->discount_type == 'fixed')
                                        Enter the fixed discount amount in dollars.
                                    @else
                                        Free shipping discount (value will be ignored).
                                    @endif
                                </small>
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="min_order_amount">Minimum Order Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control @error('min_order_amount') is-invalid @enderror" 
                                           id="min_order_amount" name="min_order_amount" 
                                           value="{{ old('min_order_amount', $promoCode->min_order_amount) }}" 
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                                <small class="form-text text-muted">Leave empty for no minimum requirement.</small>
                                @error('min_order_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_discount_amount">Maximum Discount Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control @error('max_discount_amount') is-invalid @enderror" 
                                           id="max_discount_amount" name="max_discount_amount" 
                                           value="{{ old('max_discount_amount', $promoCode->max_discount_amount) }}" 
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                                <small class="form-text text-muted">Leave empty for no maximum cap (not recommended for percentage discounts).</small>
                                @error('max_discount_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_uses">Maximum Uses</label>
                                <input type="number" class="form-control @error('max_uses') is-invalid @enderror" 
                                       id="max_uses" name="max_uses" value="{{ old('max_uses', $promoCode->max_uses) }}" 
                                       min="1" placeholder="Unlimited">
                                <small class="form-text text-muted">Leave empty for unlimited uses.</small>
                                @error('max_uses')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="valid_from">Valid From <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('valid_from') is-invalid @enderror" 
                                       id="valid_from" name="valid_from" 
                                       value="{{ old('valid_from', $promoCode->valid_from->format('Y-m-d\TH:i')) }}" required>
                                @error('valid_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="valid_until">Valid Until <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('valid_until') is-invalid @enderror" 
                                       id="valid_until" name="valid_until" 
                                       value="{{ old('valid_until', $promoCode->valid_until->format('Y-m-d\TH:i')) }}" required>
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_audience">Target Audience</label>
                                @php
                                    $currentAudience = 'all';
                                    if ($promoCode->target_audience) {
                                        $audienceKeys = array_keys($promoCode->target_audience);
                                        $currentAudience = $audienceKeys[0] ?? 'all';
                                    }
                                @endphp                                <select class="form-control @error('target_audience') is-invalid @enderror" 
                                        id="target_audience_type" name="target_audience_type">
                                    <option value="all" {{ $currentAudience == 'all' ? 'selected' : '' }}>All Customers</option>
                                    <option value="new_users" {{ $currentAudience == 'new_users' ? 'selected' : '' }}>
                                        New Customers Only
                                    </option>
                                    <option value="repeat_customers" {{ $currentAudience == 'repeat_customers' ? 'selected' : '' }}>
                                        Repeat Customers
                                    </option>
                                    <option value="vip_customers" {{ $currentAudience == 'vip_customers' ? 'selected' : '' }}>
                                        VIP Customers
                                    </option>
                                </select>
                                <input type="hidden" id="target_audience_hidden" name="target_audience" 
                                       value="{{ old('target_audience', json_encode($promoCode->target_audience)) }}">
                                @error('target_audience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $promoCode->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                                <small class="form-text text-muted">Inactive promo codes cannot be used by customers.</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Update Promo Code
                        </button>                        <a href="{{ route('admin.promotions.promo_codes.show', $promoCode->id) }}" class="btn btn-info ml-2 text-white">
                            <i class="fas fa-eye mr-2 text-white"></i> View Details
                        </a>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary ml-2">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Usage Statistics -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-chart-bar mr-2"></i> Usage Statistics
                </h6>
            </div>
            <div class="card-body">
                @php
                    $usageCount = $promoCode->usages()->count();
                @endphp
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-right">
                            <h4 class="font-weight-bold text-primary">{{ $usageCount }}</h4>
                            <small class="text-muted">Total Uses</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="font-weight-bold text-success">
                            @if($promoCode->max_uses)
                                {{ $promoCode->max_uses - $usageCount }}
                            @else
                                ∞
                            @endif
                        </h4>
                        <small class="text-muted">Remaining</small>
                    </div>
                </div>
                
                @if($promoCode->max_uses)
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: {{ min(100, ($usageCount / $promoCode->max_uses) * 100) }}%"></div>
                    </div>
                @endif
                
                <hr>
                <small class="text-muted">
                    <strong>Created:</strong> {{ $promoCode->created_at->format('M j, Y g:i A') }}<br>
                    @if($promoCode->createdBy)
                        <strong>By:</strong> {{ $promoCode->createdBy->name }}
                    @endif
                </small>
            </div>
        </div>

        <!-- Warning Messages -->
        @if($promoCode->valid_until < now())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Expired!</strong> This promo code has expired and cannot be used by customers.
            </div>
        @endif

        @if(!$promoCode->is_active)
            <div class="alert alert-warning">
                <i class="fas fa-pause-circle mr-2"></i>
                <strong>Inactive!</strong> This promo code is currently disabled.
            </div>
        @endif

        @if($promoCode->max_uses && $usageCount >= $promoCode->max_uses)
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Usage Limit Reached!</strong> This promo code has reached its maximum usage limit.
            </div>
        @endif

        <!-- Preview -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-eye mr-2"></i> Customer Preview
                </h6>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 bg-light">
                    <div class="text-center">
                        <h5 class="text-primary mb-1" id="preview_code">{{ $promoCode->code }}</h5>
                        <h6 class="text-success mb-2" id="preview_discount">
                            @if($promoCode->discount_type == 'percentage')
                                {{ $promoCode->discount_value }}% OFF
                            @elseif($promoCode->discount_type == 'fixed')
                                ${{ number_format($promoCode->discount_value, 2) }} OFF
                            @else
                                FREE SHIPPING
                            @endif
                        </h6>
                        <small class="text-muted" id="preview_conditions">
                            @php
                                $conditions = [];
                                if ($promoCode->min_order_amount) {
                                    $conditions[] = 'Min order: $' . number_format($promoCode->min_order_amount, 2);
                                }
                                if ($promoCode->target_audience && !isset($promoCode->target_audience['all'])) {
                                    $audienceType = array_keys($promoCode->target_audience)[0];
                                    $conditions[] = str_replace('_', ' ', $audienceType);
                                }
                            @endphp
                            {{ count($conditions) ? implode(' • ', $conditions) : 'No special conditions' }}
                        </small>
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
    // Same JavaScript as create form for dynamic updates
    $('#discount_type').change(function() {
        const type = $(this).val();
        const symbol = $('#discount_symbol span');
        const help = $('#discount_help');
        const valueInput = $('#discount_value');
        
        if (type === 'percentage') {
            symbol.text('%');
            help.text('Enter the discount percentage (0-100).');
            valueInput.attr('max', '100');
        } else if (type === 'fixed') {
            symbol.text('$');
            help.text('Enter the fixed discount amount in dollars.');
            valueInput.removeAttr('max');
        } else if (type === 'free_shipping') {
            symbol.text('');
            help.text('Free shipping discount (value will be ignored).');
            valueInput.val('0');
            valueInput.prop('disabled', true);
        }
        
        if (type !== 'free_shipping') {
            valueInput.prop('disabled', false);
        }
        
        updatePreview();
    });

    // Update target audience JSON
    $('select[name="target_audience_type"]').change(function() {
        const type = $(this).val();
        let audienceData = {};
        
        if (type !== 'all') {
            audienceData[type] = true;
        } else {
            audienceData = { "all": true };
        }
        
        $('input[name="target_audience"]').val(JSON.stringify(audienceData));
        updatePreview();
    });

    // Update preview
    function updatePreview() {
        const code = $('#code').val();
        const type = $('#discount_type').val();
        const value = $('#discount_value').val();
        const minOrder = $('#min_order_amount').val();
        const audience = $('select[name="target_audience_type"]').val();
        
        $('#preview_code').text(code);
        
        let discountText = 'Discount Value';
        if (type === 'percentage' && value) {
            discountText = value + '% OFF';
        } else if (type === 'fixed' && value) {
            discountText = '$' + value + ' OFF';
        } else if (type === 'free_shipping') {
            discountText = 'FREE SHIPPING';
        }
        $('#preview_discount').text(discountText);
        
        let conditions = [];
        if (minOrder) {
            conditions.push('Min order: $' + minOrder);
        }
        if (audience !== 'all') {
            conditions.push(audience.replace('_', ' '));
        }
        
        $('#preview_conditions').text(conditions.length ? conditions.join(' • ') : 'No special conditions');
    }

    // Update preview on input changes
    $('#code, #discount_value, #min_order_amount').on('input', updatePreview);
    
    // Initialize
    $('select[name="target_audience_type"]').trigger('change');
});
</script>
@endsection

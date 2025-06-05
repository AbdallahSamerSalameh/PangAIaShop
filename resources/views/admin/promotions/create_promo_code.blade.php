@extends('admin.layouts.app')

@section('title', 'Create Promo Code')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Promo Code</h1>    <div>
        <a href="{{ route('admin.promotions.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Promo Codes
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Create Promo Code Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Promo Code Details</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.promotions.promo_codes.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">Promo Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code') }}" 
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
                                    <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)
                                    </option>
                                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount ($)
                                    </option>
                                    <option value="free_shipping" {{ old('discount_type') == 'free_shipping' ? 'selected' : '' }}>
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
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <input type="number" class="form-control @error('discount_value') is-invalid @enderror" 
                                           id="discount_value" name="discount_value" value="{{ old('discount_value') }}" 
                                           step="0.01" min="0" required>
                                </div>
                                <small class="form-text text-muted" id="discount_help">
                                    Enter the discount percentage (0-100).
                                </small>
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="min_order_amount">Minimum Order Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control @error('min_order_amount') is-invalid @enderror" 
                                           id="min_order_amount" name="min_order_amount" value="{{ old('min_order_amount') }}" 
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                                <small class="form-text text-muted">Leave empty for no minimum requirement.</small>
                                @error('min_order_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_discount_amount">Maximum Discount Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control @error('max_discount_amount') is-invalid @enderror" 
                                           id="max_discount_amount" name="max_discount_amount" value="{{ old('max_discount_amount') }}" 
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
                                       id="max_uses" name="max_uses" value="{{ old('max_uses') }}" 
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
                                       id="valid_from" name="valid_from" value="{{ old('valid_from', now()->format('Y-m-d\TH:i')) }}" required>
                                @error('valid_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="valid_until">Valid Until <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('valid_until') is-invalid @enderror" 
                                       id="valid_until" name="valid_until" value="{{ old('valid_until', now()->addMonth()->format('Y-m-d\TH:i')) }}" required>
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_audience_type">Target Audience</label>
                                <select class="form-control @error('target_audience') is-invalid @enderror" 
                                        id="target_audience_type" name="target_audience_type">
                                    <option value="all">All Customers</option>
                                    <option value="new_users" {{ old('target_audience_type') == 'new_users' ? 'selected' : '' }}>
                                        New Customers Only
                                    </option>
                                    <option value="repeat_customers" {{ old('target_audience_type') == 'repeat_customers' ? 'selected' : '' }}>
                                        Repeat Customers
                                    </option>
                                    <option value="vip_customers" {{ old('target_audience_type') == 'vip_customers' ? 'selected' : '' }}>
                                        VIP Customers
                                    </option>
                                </select>
                                <input type="hidden" id="target_audience_hidden" name="target_audience" value="{{ old('target_audience') }}">
                                @error('target_audience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                                <small class="form-text text-muted">Inactive promo codes cannot be used by customers.</small>
                            </div>
                        </div>
                    </div>

                    <hr><div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i> Create Promo Code
                        </button>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary ml-2">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Help & Guidelines -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-lightbulb mr-2"></i> Guidelines
                </h6>
            </div>
            <div class="card-body">
                <h6 class="font-weight-bold">Code Naming:</h6>
                <ul class="small mb-3">
                    <li>Use clear, memorable codes</li>
                    <li>Include purpose (SUMMER2025, WELCOME10)</li>
                    <li>Avoid confusing characters (0, O, I, l)</li>
                </ul>

                <h6 class="font-weight-bold">Discount Types:</h6>
                <ul class="small mb-3">
                    <li><strong>Percentage:</strong> % off total order</li>
                    <li><strong>Fixed:</strong> $ amount off</li>
                    <li><strong>Free Shipping:</strong> No shipping cost</li>
                </ul>

                <h6 class="font-weight-bold">Best Practices:</h6>
                <ul class="small mb-0">
                    <li>Set reasonable validity periods</li>
                    <li>Use minimum order amounts wisely</li>
                    <li>Monitor usage regularly</li>
                    <li>Target specific customer segments</li>
                </ul>
            </div>
        </div>

        <!-- Preview -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-eye mr-2"></i> Preview
                </h6>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 bg-light">
                    <div class="text-center">
                        <h5 class="text-primary mb-1" id="preview_code">PROMO CODE</h5>
                        <h6 class="text-success mb-2" id="preview_discount">Discount Value</h6>
                        <small class="text-muted" id="preview_conditions">Conditions will appear here</small>
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
    // Update discount symbol and help text based on type
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
        } else {
            symbol.text('%');
            help.text('Select a discount type first.');
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
        
        $('#target_audience_hidden').val(JSON.stringify(audienceData));
        updatePreview();
    });

    // Update preview
    function updatePreview() {
        const code = $('#code').val() || 'PROMO CODE';
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

    // Initialize target audience
    $('select[name="target_audience_type"]').trigger('change');
    
    // Update preview on input changes
    $('#code, #discount_value, #min_order_amount').on('input', updatePreview);
    $('#discount_type').trigger('change');
    
    // Generate random code
    $('#code').on('focus', function() {
        if (!$(this).val()) {
            const randomCode = 'PROMO' + Math.random().toString(36).substr(2, 5).toUpperCase();
            $(this).val(randomCode);
            updatePreview();
        }
    });
});
</script>
@endsection

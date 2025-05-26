@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - Checkout')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/checkout-form-enhancements.css') }}">
@endsection

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Fresh and Organic</p>
                    <h1>Check Out</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- check out section -->
<div class="checkout-section mt-150 mb-150">
    <div class="container">
        @if(session('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
        </div>
        @endif
        @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="list-unstyled mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <div class="row">
            <div class="col-lg-8">
                <div class="checkout-accordion-wrap">
                    <div class="accordion" id="accordionExample">
                        <div class="card single-accordion">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Billing Address
                                    </button>
                                </h5>
                            </div>

                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">                                <div class="card-body">                                    <div class="billing-address-form">
                                        <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
                                            @csrf
                                            <p><input type="text" name="billing_name" placeholder="Name *" required></p>
                                            <p><input type="email" name="billing_email" placeholder="Email *" required></p>
                                            <p><input type="text" name="billing_street" placeholder="Street Address *" required></p>
                                            <p><input type="text" name="billing_city" placeholder="City *" required></p>
                                            <p><input type="text" name="billing_state" placeholder="State/Province *" required></p>
                                            <p><input type="text" name="billing_postal_code" placeholder="Postal/Zip Code *" required></p><p>
                                                <select name="billing_country" id="billing_country" class="country-select" required>
                                                    <option value="" disabled selected>Select Country</option>
                                                </select>
                                            </p>
                                            <p><input type="tel" name="billing_phone" placeholder="Phone" required></p>
                                            <p><textarea name="notes" id="bill" cols="30" rows="5" placeholder="Additional Notes"></textarea></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card single-accordion">
                            <div class="card-header" id="headingTwo">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Shipping Address
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">                                <div class="card-body">
                                    <div class="shipping-address-form">
                                        <p>
                                            <label for="same_address">
                                                <input type="checkbox" id="same_address" name="same_address" value="1" checked> 
                                                Same as billing address
                                            </label>
                                        </p>
                                        <div id="shipping_fields" style="display: none;">
                                            <p><input type="text" name="shipping_name" placeholder="Name"></p>
                                            <p><input type="text" name="shipping_street" placeholder="Street Address"></p>
                                            <p><input type="text" name="shipping_city" placeholder="City"></p>
                                            <p><input type="text" name="shipping_state" placeholder="State/Province"></p>
                                            <p><input type="text" name="shipping_postal_code" placeholder="Postal/Zip Code"></p>                                            <p>
                                                <select name="shipping_country" id="shipping_country" class="country-select">
                                                    <option value="" disabled selected>Select Country</option>
                                                </select>
                                            </p>
                                            <p><input type="tel" name="shipping_phone" placeholder="Phone"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card single-accordion">
                            <div class="card-header" id="headingThree">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        Payment Information
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">                                <div class="card-body">
                                    <div class="payment-method">
                                        <div class="payment-options">
                                            <p>
                                                <label>
                                                    <input type="radio" name="payment_method" value="credit_card" checked> 
                                                    Credit Card
                                                </label>
                                            </p>
                                            <div id="credit_card_fields">
                                                <p><input type="text" name="payment_details[card_number]" placeholder="Card Number"></p>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><input type="text" name="payment_details[expiry_date]" placeholder="MM/YY"></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><input type="text" name="payment_details[cvv]" placeholder="CVV"></p>
                                                    </div>
                                                </div>
                                                <p><input type="text" name="payment_details[card_name]" placeholder="Name on Card"></p>
                                            </div>
                                            <p>
                                                <label>
                                                    <input type="radio" name="payment_method" value="paypal"> 
                                                    PayPal
                                                </label>
                                            </p>
                                            <p>
                                                <label>
                                                    <input type="radio" name="payment_method" value="bank_transfer"> 
                                                    Bank Transfer
                                                </label>
                                            </p>
                                        </div>                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="order-details-wrap">
                    <table class="order-details">
                        <thead>
                            <tr>
                                <th>Your Order Details</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody class="order-details-body">
                            @foreach($cartItems as $item)
                            <tr>
                                <td>{{ $item->product->name }} ({{ $item->quantity }})</td>
                                <td>${{ number_format($item->product->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tbody class="checkout-details">
                            <tr>
                                <td>Subtotal</td>
                                <td>${{ number_format($subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Shipping</td>
                                <td>${{ number_format($shipping, 2) }}</td>
                            </tr>
                            @if($discount > 0)
                            <tr>
                                <td>Discount</td>
                                <td>-${{ number_format($discount, 2) }}</td>
                            </tr>
                            @endif                            <tr>
                                <td>Total</td>
                                <td>${{ number_format($total, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="checkout-buttons">
                        <a href="{{ route('cart') }}" class="boxed-btn-outline">Back to Cart</a>
                        <button type="submit" class="boxed-btn">Place Order</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end check out section -->
@endsection

@section('scripts')
<script src="{{ asset('assets/js/countries.js') }}"></script>
<script>
    $(document).ready(function() {
        // Toggle shipping address fields
        $('#same_address').change(function() {
            if(this.checked) {
                $('#shipping_fields').hide();
            } else {
                $('#shipping_fields').show();
            }
        });

        // Toggle payment method fields
        $('input[name="payment_method"]').change(function() {
            if($(this).val() === 'credit_card') {
                $('#credit_card_fields').show();
            } else {
                $('#credit_card_fields').hide();
            }
        });

        // Populate country dropdowns
        if (typeof countries !== 'undefined' && Array.isArray(countries)) {
            const billingCountrySelect = document.getElementById('billing_country');
            const shippingCountrySelect = document.getElementById('shipping_country');
            
            // Sort countries alphabetically
            const sortedCountries = [...countries].sort((a, b) => 
                a.name.localeCompare(b.name)
            );
            
            // Add sorted countries to billing dropdown
            sortedCountries.forEach(function(country) {
                const option = document.createElement('option');
                option.value = country.code;
                option.textContent = country.name;
                billingCountrySelect.appendChild(option);
            });
            
            // Add sorted countries to shipping dropdown
            sortedCountries.forEach(function(country) {
                const option = document.createElement('option');
                option.value = country.code;
                option.textContent = country.name;
                shippingCountrySelect.appendChild(option);
            });
        }

        // Form validation
        $('#checkout-form').on('submit', function(e) {
            let isValid = true;

            // Check if all required fields are filled
            $(this).find('input[required], select[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Check shipping fields if same_address is not checked
            if (!$('#same_address').is(':checked')) {
                $('#shipping_fields input, #shipping_fields select').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
            }

            // Check payment method fields
            if ($('input[name="payment_method"]:checked').val() === 'credit_card') {
                $('#credit_card_fields input').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
            }

            if (!isValid) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $('.is-invalid:first').offset().top - 100
                }, 500);
            }
        });

        // Remove validation error when field is edited
        $('input, select').on('input change', function() {
            $(this).removeClass('is-invalid');
        });
    });
</script>
@endsection
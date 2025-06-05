@extends('frontend.layouts.master')

@section('title', 'Register - PangAIaShop')

@section('styles')
<style>
    .form-container {
        max-width: 600px;
        margin: 0 auto;
        padding: 30px;
        background-color: #f5f5f5;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .auth-title {
        text-align: center;
        margin-bottom: 30px;
        color: #F28123;
    }
    .auth-form input {
        width: 100%;
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .auth-form button {
        background-color: #F28123;
        color: #fff;
        border: none;
        padding: 15px 25px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        width: 100%;
        transition: all 0.3s;
    }
    .auth-form button:hover {
        background-color: #e67612;
    }
    .auth-links {
        margin-top: 20px;
        text-align: center;
    }
    .auth-links a {
        color: #F28123;
        text-decoration: none;
    }
    .auth-links a:hover {
        text-decoration: underline;
    }
    .form-check {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    .form-check input {
        width: auto;
        margin-right: 10px;
        margin-top: 4px;
        margin-bottom: 0;
    }
    .error-message {
        color: #d9534f;
        margin-bottom: 20px;
        padding: 10px;
        background-color: rgba(217, 83, 79, 0.1);
        border-radius: 5px;
    }
    .password-requirements {
        font-size: 0.85rem;
        color: #666;
        margin-top: -15px;
        margin-bottom: 20px;
    }
    .required-indicator {
        color: #F28123;
    }
</style>
@endsection

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Join Our Community</p>
                    <h1>Register</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- register form -->
<div class="contact-from-section mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                <div class="form-container">
                    <h2 class="auth-title">Create Account</h2>
                    
                    @if ($errors->any())
                        <div class="error-message">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('register') }}" method="POST" class="auth-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" name="name" placeholder="Full Name *" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" placeholder="Email *" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6">
                                <input type="tel" name="phone" placeholder="Phone Number (Optional)" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6">
                                <input type="password" name="password" placeholder="Password *" required>
                            </div>
                            <div class="col-md-6">
                                <input type="password" name="password_confirmation" placeholder="Confirm Password *" required>
                            </div>
                        </div>
                        
                        <div class="password-requirements">
                            Password must be at least 8 characters and include uppercase, lowercase, numbers, and special characters.
                        </div>
                          <div class="form-check">
                            <input type="checkbox" name="terms" id="terms" required>
                            <label for="terms">I agree to the <a href="{{ route('terms-of-service') }}" target="_blank">Terms of Service</a> and <a href="{{ route('privacy-policy') }}" target="_blank">Privacy Policy</a> <span class="required-indicator">*</span></label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" name="marketing" id="marketing" {{ old('marketing') ? 'checked' : '' }}>
                            <label for="marketing">I would like to receive promotional emails about product updates and offers</label>
                        </div>
                        
                        <button type="submit">Register</button>
                    </form>
                    
                    <div class="auth-links">
                        <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end register form -->
@endsection

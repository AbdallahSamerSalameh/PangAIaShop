@extends('frontend.layouts.master')

@section('title', 'Forgot Password - PangAIaShop')

@section('styles')
<style>
    .form-container {
        max-width: 500px;
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
    .error-message {
        color: #d9534f;
        margin-bottom: 20px;
        padding: 10px;
        background-color: rgba(217, 83, 79, 0.1);
        border-radius: 5px;
    }
    .success-message {
        color: #5cb85c;
        margin-bottom: 20px;
        padding: 10px;
        background-color: rgba(92, 184, 92, 0.1);
        border-radius: 5px;
    }
    .form-description {
        margin-bottom: 25px;
        text-align: center;
        color: #666;
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
                    <p>Account Recovery</p>
                    <h1>Forgot Password</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- forgot password form -->
<div class="contact-from-section mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="form-container">
                    <h2 class="auth-title">Reset Your Password</h2>
                    
                    <p class="form-description">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>
                    
                    @if ($errors->any())
                        <div class="error-message">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @if (session('status'))
                        <div class="success-message">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    <form action="{{ route('password.email') }}" method="POST" class="auth-form">
                        @csrf
                        <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required autofocus>
                        <button type="submit">Send Password Reset Link</button>
                    </form>
                    
                    <div class="auth-links">
                        <p>Remember your password? <a href="{{ route('login') }}">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end forgot password form -->
@endsection

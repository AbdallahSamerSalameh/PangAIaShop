@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - Page Not Found')

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Fresh and Organic</p>
                    <h1>404 - Not Found</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- error section -->
<div class="full-height-section error-section">
    <div class="full-height-tablecell">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="error-text">
                        <i class="far fa-sad-cry"></i>
                        <h1>Oops! Page Not Found.</h1>
                        <p>The page you requested does not exist or has been moved.</p>
                        <a href="{{ route('home') }}" class="boxed-btn">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end error section -->
@endsection
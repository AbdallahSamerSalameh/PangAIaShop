@extends('admin.layouts.auth')

@section('title', 'Admin Login')

@section('content')
<!-- Outer Row -->
<div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-xl-8 col-lg-10 col-md-9">
        <div class="card o-hidden border-0 shadow-lg">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-6 d-none d-lg-block bg-login-image"></div><div class="col-lg-6 d-flex align-items-center">
                        <div class="w-100 px-4 py-3">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Welcome Back to Admin Panel!</h1>
                            </div>

                            @if(session('error'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if(session('success'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif                            <form class="user" method="POST" action="{{ route('admin.login.post') }}">
                                @csrf
                                <div class="form-group">
                                    <input type="email" 
                                           class="form-control form-control-user @error('email') is-invalid @enderror" 
                                           id="exampleInputEmail" 
                                           name="email"
                                           value="{{ old('email') }}"
                                           placeholder="Enter Email Address..." 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <input type="password" 
                                           class="form-control form-control-user @error('password') is-invalid @enderror" 
                                           id="exampleInputPassword" 
                                           name="password"
                                           placeholder="Password" 
                                           required>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                {{-- <div class="form-group">
                                    <div class="custom-control custom-checkbox small">
                                        <input type="checkbox" class="custom-control-input" id="customCheck" name="remember">
                                        <label class="custom-control-label" for="customCheck">Remember Me</label>
                                    </div>
                                </div> --}}
                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Login
                                </button>
                            </form>
                            {{-- <hr> --}}                            {{-- <div class="text-center">
                                <a class="small" href="{{ route('admin.password.request') }}">Forgot Password?</a>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

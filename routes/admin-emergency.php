<?php
// Direct routes for emergency admin access
// These routes should bypass any middleware or prefix issues

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminTestController;

// Admin login options page - use this as the main entry point
Route::get('/admin-access', function() {
    return view('admin.login_options');
});

// Test route to check if routing is working
Route::get('/admin-test-route', function() {
    return "Admin test route is working!";
});

// Direct controller test route
Route::get('/admin-controller-test', [AdminTestController::class, 'test']);

// Emergency direct login routes
Route::get('/admin-direct-login', function() {
    return view('admin.emergency_login');
});

Route::post('/admin-direct-login-process', [AdminTestController::class, 'processLogin']);

// Access the original login page directly
Route::get('/admin-original-login', [AdminTestController::class, 'directLogin']);

<?php

use Illuminate\Support\Facades\Route;

// This file contains emergency admin access routes
// Add this to your main routes file or require it from there

// Direct admin login route that bypasses prefix groups and middleware
Route::get('/direct-admin-login', function() {
    return view('admin.auth.login');
});

// Direct test route
Route::get('/direct-admin-test', function() {
    return "Direct admin access is working!";
});

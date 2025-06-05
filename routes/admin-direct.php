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

// Test route to debug admin profile issues
Route::get('/debug-admin-profile', function() {
    $admin = \App\Models\Admin::first();
    if (!$admin) {
        return 'No admin found in database';
    }
    
    return [
        'admin_id' => $admin->id,
        'username' => $admin->username,
        'email' => $admin->email,
        'last_login' => $admin->last_login,
        'last_login_formatted' => $admin->last_login ? $admin->last_login->format('Y-m-d H:i:s') : 'Never',
        'profile_image' => $admin->profile_image,
        'avatar_url' => $admin->avatar_url,
        'created_at' => $admin->created_at->format('Y-m-d H:i:s'),
        'timezone' => config('app.timezone'),
        'current_time' => now()->format('Y-m-d H:i:s'),
        'recent_audit_logs' => \App\Models\AdminAuditLog::where('admin_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function($log) {
                return [
                    'action' => $log->action,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'created_at_raw' => $log->created_at,
                ];
            })
    ];
});

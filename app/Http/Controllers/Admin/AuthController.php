<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\AdminAuditLog;

class AuthController extends Controller
{
    /**
     * Test method to check if the controller is accessible
     */
    public function testAccess()
    {
        return "Admin Auth Controller is working correctly!";
    }
    
    /**
     * Show the admin login form
     */
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle admin login
     */    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        // Attempt to log in with the correct credentials
        if (Auth::guard('admin')->attempt([
            'email' => $credentials['email'], 
            'password' => $credentials['password'],
            'is_active' => true
        ], $request->boolean('remember'))) {
            
            $admin = Auth::guard('admin')->user();              // Log the successful login
            AdminAuditLog::create([
                'admin_id' => $admin->id,
                'action' => 'login',
                'resource' => 'auth',
                'resource_id' => $admin->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);// Update last login timestamp
            Admin::where('id', $admin->id)->update([
                'last_login' => now(),
                'failed_login_count' => 0
            ]);            $request->session()->regenerate();
            
            // Get the intended URL or default to admin dashboard
            $redirectTo = $request->session()->has('url.intended') 
                ? $request->session()->pull('url.intended') 
                : route('admin.dashboard');
                
            return redirect($redirectTo);
        }

        // Login failed, check if admin exists
        $admin = Admin::where('email', $credentials['email'])->first();
        
        if ($admin) {
            // Increment failed login count
            $admin->increment('failed_login_count');              // Log the failed login attempt
            AdminAuditLog::create([
                'admin_id' => $admin->id,
                'action' => 'failed_login',
                'resource' => 'auth',
                'resource_id' => $admin->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);// If too many failed attempts, lock the account
            if ($admin->failed_login_count >= 5 && $admin->is_active) {
                Admin::where('id', $admin->id)->update([
                    'is_active' => false
                ]);
                  AdminAuditLog::create([
                    'admin_id' => $admin->id,
                    'action' => 'account_locked',
                    'resource' => 'auth',
                    'resource_id' => $admin->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return back()->withErrors([
                    'email' => 'This account has been locked due to too many failed login attempts. Please contact a super admin.',
                ]);
            }
        }        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email')->with('error', 'Login failed. Please check your credentials.');
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();
          // Log the logout action
        if ($admin) {            AdminAuditLog::create([
                'admin_id' => $admin->id,
                'action' => 'logout',
                'resource' => 'auth',
                'resource_id' => $admin->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }
        
        Auth::guard('admin')->logout();
          $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/admin/login');
    }

    /**
     * Show the forgot password form
     */
    public function showForgotPassword()
    {
        return view('admin.auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);        $admin = Admin::where('email', $request->email)->first();
        
        if (!$admin) {
            return back()->withErrors([
                'email' => 'We could not find an admin account with that email address.',
            ]);        }
        
        // Generate a random token
        $token = hash('sha256', Str::random(60));
        
        // Store the token in the password resets table
        \Illuminate\Support\Facades\DB::table('admin_password_reset_tokens')->updateOrInsert(
            ['email' => $admin->email],
            [
                'email' => $admin->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );
        
        // Send the password reset link
        // Note: You would typically send an email here
        // For now, we'll just redirect to the reset password page with the token
        
        return redirect()->route('admin.password.reset', ['token' => $token, 'email' => $request->email])
                ->with('status', 'Password reset link has been sent to your email.');
    }

    /**
     * Show the password reset form
     */
    public function showResetPassword(Request $request, $token)
    {
        return view('admin.auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $admin = Admin::where('email', $request->email)->first();
        
        if (!$admin) {
            return back()->withErrors(['email' => 'We could not find an admin with that email address.']);        }
        
        // Check if the token exists and is valid
        $tokenRecord = \Illuminate\Support\Facades\DB::table('admin_password_reset_tokens')
            ->where('email', $request->email)
            ->first();
            
        // If no token found or token is expired (60 minutes)
        if (!$tokenRecord || now()->diffInMinutes($tokenRecord->created_at) > 60) {
            return back()->withErrors(['token' => 'This password reset token is invalid or expired.']);
        }
        
        // Verify the token matches
        if (!Hash::check($request->token, $tokenRecord->token)) {
            return back()->withErrors(['token' => 'This password reset token is invalid.']);
        }        // Reset the password
        Admin::where('id', $admin->id)->update([
            'password_hash' => Hash::make($request->password),
            'last_password_change' => now()
        ]);        // Log the password reset
        AdminAuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'password_reset',
            'resource' => 'auth',
            'resource_id' => $admin->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Delete the token
        \Illuminate\Support\Facades\DB::table('admin_password_reset_tokens')
            ->where('email', $request->email)
            ->delete();
        
        // Login the admin
        Auth::guard('admin')->login($admin);
        
        return redirect()->route('admin.dashboard')->with('success', 'Your password has been reset successfully.');
    }
}

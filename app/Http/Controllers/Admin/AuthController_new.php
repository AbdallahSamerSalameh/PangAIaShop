<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\AdminAuditLog;

class AuthController extends Controller
{
    use AuditLoggable;
    
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
        // Log login page access (for security monitoring)
        try {
            $this->logCustomAction(
                'access_login_page',
                null,
                'Admin login page was accessed from ' . request()->ip()
            );
        } catch (\Exception $e) {
            // Silently handle if audit logging fails on login page
        }
        
        return view('admin.auth.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
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
            
            $admin = Admin::find(Auth::guard('admin')->id());
            
            // Log the successful login using AuditLoggable trait
            $this->logCustomAction(
                'successful_login',
                $admin,
                'Admin logged in successfully from ' . $request->ip()
            );
            
            // Update last login timestamp
            Admin::where('id', $admin->id)->update([
                'last_login' => now(),
                'failed_login_count' => 0
            ]);
            
            $request->session()->regenerate();
            
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
            $admin->increment('failed_login_count');
            
            // Log the failed login attempt using AuditLoggable trait
            $this->logCustomAction(
                'failed_login_attempt',
                $admin,
                'Failed login attempt for ' . $admin->email . ' from ' . $request->ip()
            );
            
            // If too many failed attempts, lock the account
            if ($admin->failed_login_count >= 5 && $admin->is_active) {
                Admin::where('id', $admin->id)->update([
                    'is_active' => false
                ]);
                
                // Log account lock using AuditLoggable trait
                $this->logCustomAction(
                    'account_locked',
                    $admin,
                    'Admin account locked due to excessive failed login attempts from ' . $request->ip()
                );
                
                return back()->withErrors([
                    'email' => 'This account has been locked due to too many failed login attempts. Please contact a super admin.',
                ]);
            }
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email')->with('error', 'Login failed. Please check your credentials.');
    }
    
    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        $admin = $adminId ? Admin::find($adminId) : null;
        
        // Log the logout action using AuditLoggable trait
        if ($admin) {
            $this->logCustomAction(
                'logout',
                $admin,
                'Admin logged out from ' . $request->ip()
            );
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
        // Log forgot password page access
        try {
            $this->logCustomAction(
                'access_forgot_password_page',
                null,
                'Forgot password page accessed from ' . request()->ip()
            );
        } catch (\Exception $e) {
            // Silently handle if audit logging fails
        }
        
        return view('admin.auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);
        
        $admin = Admin::where('email', $request->email)->first();
        
        if (!$admin) {
            // Log invalid email attempt
            try {
                $this->logCustomAction(
                    'forgot_password_invalid_email',
                    null,
                    'Forgot password attempted with invalid email: ' . $request->email . ' from ' . $request->ip()
                );
            } catch (\Exception $e) {
                // Silently handle audit logging error
            }
            
            return back()->withErrors([
                'email' => 'We could not find an admin account with that email address.',
            ]);
        }
        
        // Log forgot password request
        $this->logCustomAction(
            'forgot_password_request',
            $admin,
            'Password reset requested for ' . $admin->email . ' from ' . $request->ip()
        );
        
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
            return back()->withErrors(['email' => 'We could not find an admin with that email address.']);
        }
        
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
        }
        
        // Reset the password
        Admin::where('id', $admin->id)->update([
            'password_hash' => Hash::make($request->password),
            'last_password_change' => now()
        ]);
        
        // Log the password reset using AuditLoggable trait
        $this->logCustomAction(
            'password_reset_completed',
            $admin,
            'Admin password was reset successfully from ' . $request->ip()
        );
        
        // Delete the token
        \Illuminate\Support\Facades\DB::table('admin_password_reset_tokens')
            ->where('email', $request->email)
            ->delete();
        
        // Login the admin
        Auth::guard('admin')->login($admin);
        
        return redirect()->route('admin.dashboard')->with('success', 'Your password has been reset successfully.');
    }
}

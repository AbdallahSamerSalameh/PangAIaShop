<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;

class AdminAuthenticate
{    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        // Default to admin guard if none specified
        if (empty($guards)) {
            $guards = ['admin'];
        }

        // Check if user is authenticated with the admin guard
        if (!Auth::guard('admin')->check()) {
            // Handle JSON requests
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // Store intended URL for after login (only for admin routes)
            if ($request->is('admin/*') && !$request->is('admin/login')) {
                session(['url.intended' => $request->url()]);
            }
            
            // Redirect to admin login page
            return redirect()->route('admin.login');
        }

        // Check if admin is active
        $admin = Auth::guard('admin')->user();
        if (!$admin || !$admin->is_active) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Your account is inactive. Please contact administrator.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionValidator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as admin
        if (Auth::guard('admin')->check()) {
            // Define admin paths - all paths that should keep admin logged in
            $adminPaths = [
                'admin',
                'admin/*',
            ];
            
            // Check if current request is for an admin path
            $isAdminPath = false;
            foreach ($adminPaths as $path) {
                if ($request->is($path)) {
                    $isAdminPath = true;
                    break;
                }
            }
            
            // If the request is NOT for an admin route, log them out
            if (!$isAdminPath) {
                // Log the admin out completely
                Auth::guard('admin')->logout();
                
                // Clear all admin-related session data
                $request->session()->forget([
                    'login_admin_' . sha1('admin'), // Laravel's admin guard session key
                    'password_hash_admin',
                    'url.intended',
                    'admin_logged_in',
                    'admin_user',                ]);
                
                // Regenerate session ID to ensure complete logout
                $request->session()->regenerate();
                
                // Clear any remember me tokens for admin by queuing cookie for deletion
                $cookieName = 'remember_admin_' . sha1('admin');
                if ($request->cookies->has($cookieName)) {
                    Cookie::queue(Cookie::forget($cookieName));
                }
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */    public function handle(Request $request, Closure $next)
    {        
        // If not authenticated with admin guard, redirect to our emergency admin login
        if (!Auth::guard('admin')->check()) {
            // Store the current URL as the intended URL for post-login redirect
            if (!$request->session()->has('url.intended')) {
                $request->session()->put('url.intended', $request->url());
            }
            
            // Force a redirect to the emergency admin login URL which should work without route issues
            return redirect('/admin-direct-login')->with('message', 'Please login to access the admin dashboard');
        }

        return $next($request);
    }
}

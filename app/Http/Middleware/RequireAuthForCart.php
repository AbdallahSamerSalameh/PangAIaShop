<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireAuthForCart
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // If this is an AJAX request, return JSON with redirect URL
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to add items to cart',
                    'redirect' => route('login'),
                    'requires_auth' => true
                ], 401);
            }
            
            // For regular requests, redirect to login with intended URL
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminAccess
{
    /**
     * Handle an incoming request - ensure user is a Super Admin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as admin
        if (!auth('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Authentication required.'
                ], 401);
            }
            return redirect()->route('admin.login')->with('error', 'Please log in first.');
        }

        // Check if user has Super Admin role
        if (auth('admin')->user()->role !== 'Super Admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Super Admin access required.'
                ], 403);
            }
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
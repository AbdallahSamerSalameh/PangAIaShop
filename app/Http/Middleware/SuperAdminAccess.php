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
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated, is an admin, and has Super Admin role
        if (!$request->user() || 
            !$request->user() instanceof \App\Models\Admin || 
            $request->user()->role !== 'Super Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super Admin access required.'
            ], 403);
        }

        return $next($request);
    }
}
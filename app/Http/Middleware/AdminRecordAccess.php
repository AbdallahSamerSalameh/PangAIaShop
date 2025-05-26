<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRecordAccess
{
    /**
     * Handle an incoming request - restrict admin record access based on role
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow Super Admins full access
        if ($request->user()->role === 'Super Admin') {
            return $next($request);
        }
        
        // For regular admins, get the requested admin ID (if applicable)
        $adminId = $request->route('admin');
        
        // If there's no admin ID in the route or it matches the current user, allow access
        if (!$adminId || $adminId == $request->user()->id) {
            // For route parameters that aren't objects yet
            return $next($request);
        }
        
        // If it's a GET request (viewing) or PUT/PATCH (editing) to their own record, allow
        if (($request->isMethod('get') || $request->isMethod('put') || $request->isMethod('patch')) && 
            $adminId == $request->user()->id) {
            return $next($request);
        }
        
        // Block DELETE methods for all regular admins, even on their own account
        if ($request->isMethod('delete')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You cannot delete admin accounts.'
            ], 403);
        }
        
        // In all other cases, block access
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. You can only view and edit your own admin record.'
        ], 403);
    }
}
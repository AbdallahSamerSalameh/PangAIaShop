<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if account status is active
            if ($user->account_status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account is ' . $user->account_status . '. Please contact support.'
                ]);
            }
              // Update last activity timestamp
            if (!$request->is('logout') && !$request->expectsJson()) {
                // Just touch the model to update the updated_at timestamp instead of last_activity
                $user->touch();
            }
        }
        
        return $next($request);
    }
}

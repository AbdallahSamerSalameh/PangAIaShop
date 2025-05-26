<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class HandleGuestUser
{
    /**
     * Protected routes that require authentication
     */
    protected $protectedRoutes = [
        'checkout',
        'profile',
        'orders',
        'wishlist',
        'wishlist/*',
        'add-to-wishlist',
        'remove-from-wishlist',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is not authenticated, we'll set up a guest user in the session
        if (!Auth::check()) {
            // Create or retrieve the guest session identifier
            if (!Session::has('guest_id')) {
                // Generate a unique guest identifier
                Session::put('guest_id', 'guest_' . uniqid());
                Session::put('guest_created_at', now()->toDateTimeString());
            }
            
            // Make the guest user data available to all views
            $guestUser = (object)[
                'id' => Session::get('guest_id'),
                'username' => 'Guest',
                'is_guest' => true,
                'created_at' => Session::get('guest_created_at'),
                'cart' => Session::get('guest_cart', []),
            ];
            
            // Share this with all views
            view()->share('guestUser', $guestUser);
            
            // Check if the current route is protected
            foreach ($this->protectedRoutes as $route) {
                if ($request->is($route)) {
                    return redirect()->route('login')
                        ->with('message', 'Please login or register to access this feature.');
                }
            }
            
            // If it's an AJAX request to a protected action
            if ($request->ajax() && 
                ($request->is('api/wishlist*') || 
                 $request->is('api/checkout*') || 
                 $request->is('api/profile*') ||
                 $request->is('api/submit-review'))) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Please login or register to use this feature.',
                    'require_auth' => true
                ], 401);
            }
        }
        
        return $next($request);
    }
}

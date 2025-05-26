<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\UserPreference;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\GuestCartController;

class AuthController extends Controller
{
    /**
     * Display the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLogin()
    {
        return view('frontend.auth.login');
    }
      /**
     * Handle a login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        // Check if the user account exists
        $user = User::where('email', $request->email)->first();
        
        // If user exists, check account status
        if ($user && $user->account_status !== 'active') {
            return back()->withErrors([
                'email' => 'Your account is ' . $user->account_status . '. Please contact support.'
            ])->withInput($request->except('password'));
        }
          // With Laravel's Auth facade, we need to use the getAuthPassword method
        // which is configured to use password_hash field
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->has('remember'))) {
            $request->session()->regenerate();
            
            // Update last login timestamp
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->last_login = now();
            // Use save() as an alternative to update()
            $user->save();
            
            // Transfer guest cart to user cart if there's a guest cart
            if ($request->session()->has('guest_cart')) {
                app(GuestCartController::class)->transferCart($user->id);
            }
            
            return redirect()->intended(route('home'))
                             ->with('success', 'You have successfully logged in.');
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('password'));
    }
    
    /**
     * Display the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegister()
    {
        return view('frontend.auth.register');
    }
      /**
     * Handle a registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required', 
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'phone' => 'nullable|string|max:20',
            'terms' => 'required|accepted',
        ]);
        
        $user = User::create([
            'username' => $request->name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'phone_number' => $request->phone,
            'account_status' => 'active',
            'is_verified' => false,
            'last_login' => now(),
        ]);
          // Create user preferences with default values
        UserPreference::create([
            'user_id' => $user->id,
            'language' => 'en',
            'currency' => 'USD',
            'theme_preference' => 'light',
            'notification_preferences' => json_encode([
                'email_notifications' => true,
                'marketing_emails' => $request->has('marketing'),
                'order_updates' => true,
                'product_recommendations' => true
            ]),
            'ai_interaction_enabled' => true,
            'chat_history_enabled' => true,
        ]);
        
        // Fire registered event (sends verification email if configured)
        event(new Registered($user));
          // Login the user
        Auth::login($user);
        
        // Transfer guest cart to user cart if there's a guest cart
        if ($request->session()->has('guest_cart')) {
            app(GuestCartController::class)->transferCart($user->id);
        }
        
        return redirect()->route('home')
                         ->with('success', 'Account created successfully! Welcome to PangAIaShop.');
    }
    
    /**
     * Display the forgot password form.
     *
     * @return \Illuminate\View\View
     */
    public function showForgotPassword()
    {
        return view('frontend.auth.forgot-password');
    }
    
    /**
     * Handle a forgot password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        
        // Generate a password reset token
        $token = \Illuminate\Support\Str::random(64);
        
        // Store the token in the password_reset_tokens table
        \App\Models\PasswordResetToken::create([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);
        
        // Send reset link email (implementation depends on your mailing setup)
        // Mail::to($request->email)->send(new PasswordReset($token));
        
        return back()->with('status', 'Password reset link has been sent to your email.');
    }
    
    /**
     * Log the user out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')
                         ->with('success', 'You have been successfully logged out.');
    }
      /**
     * Display the user profile page.
     *
     * @return \Illuminate\View\View
     */    public function profile()
    {        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Explicitly get orders to help IDE understand the relationship
        // Use orderBy('order_date') instead of latest() which uses created_at
        $orders = Order::where('user_id', $user->id)
                      ->orderBy('order_date', 'desc')
                      ->take(5)
                      ->get();
        
        // Get wishlist items for display in profile
        $wishlist = Wishlist::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'My Wishlist']
        );
          // Get wishlist items with product details
        $wishlistItems = $wishlist->items()
            ->with(['product.images' => function($query) {
                $query->where('is_primary', true);
            }, 'product.categories', 'product.inventory'])
            ->take(4)
            ->get();
            
        // Transform the collection to add featured image and category names
        $wishlistItems = $wishlistItems->map(function($item) {
            $product = $item->product;
            
            $product->featured_image = $product->images->first() 
                ? $product->images->first()->image_url 
                : 'assets/img/products/product-img-1.jpg';
                
            // Set category names for display
            if ($product->categories->count() > 0) {
                $product->category_names = $product->categories->pluck('name');
            } else {
                $product->category_names = collect(['Uncategorized']);
            }
                
            return $item;
        });
        
        return view('frontend.auth.profile', [
            'user' => $user,
            'orders' => $orders,
            'wishlistItems' => $wishlistItems
        ]);
    }
      /**
     * Update the user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */    public function updateProfile(Request $request)
    {
        try {
            // Start a database transaction
            \DB::beginTransaction();
            
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Check which form is being submitted
            $formType = $request->input('form_type', 'personal_info');
            
            if ($formType === 'personal_info') {
                // Validate personal info form
                $request->validate([
                    'username' => 'required|string|max:255',
                    'email' => 'required|email|max:150|unique:users,email,' . $user->id,
                    'phone_number' => 'nullable|string|max:20',
                    'avatar_url' => 'nullable|url|max:255',
                ]);
                
                // Update personal info
                $user->username = $request->username;
                $user->email = $request->email;
                $user->phone_number = $request->phone_number;
                $user->avatar_url = $request->avatar_url;
            } 
            elseif ($formType === 'shipping_address') {
                // Validate shipping address form
                $request->validate([
                    'street' => 'nullable|string|max:255',
                    'city' => 'nullable|string|max:100',
                    'state' => 'nullable|string|max:100',
                    'postal_code' => 'nullable|string|max:20',
                    'country' => 'nullable|string|max:2',
                ]);
                
                // Log shipping address update request
                \Log::info('Shipping address update request', [
                    'user_id' => $user->id,
                    'form_data' => $request->only(['street', 'city', 'state', 'postal_code', 'country']),
                ]);
                
                // Update shipping address
                $user->street = $request->street;
                $user->city = $request->city;
                $user->state = $request->state;
                $user->postal_code = $request->postal_code;
                $user->country = $request->country;
            }
            
            // Save user changes
            $saved = $user->save();
            
            if (!$saved) {
                throw new \Exception('Failed to save user data');
            }
            
            // Commit transaction
            \DB::commit();
            
            // Update user preferences if they exist
            $userPreference = UserPreference::where('user_id', $user->id)->first();
            if ($userPreference) {
                // Get existing preferences or default to an empty array
                $notificationPrefs = $userPreference->notification_preferences ?? [];
                if (is_string($notificationPrefs)) {
                    $notificationPrefs = json_decode($notificationPrefs, true) ?? [];
                }
                
                // Update specified notification preferences
                $notificationPrefs['email_notifications'] = $request->has('email_notifications') ?? ($notificationPrefs['email_notifications'] ?? true);
                $notificationPrefs['marketing_emails'] = $request->has('marketing_emails') ?? ($notificationPrefs['marketing_emails'] ?? false);
                
                $userPreference->update([
                    'notification_preferences' => $notificationPrefs,
                    'theme_preference' => $request->theme_preference ?? $userPreference->theme_preference,
                    'language' => $request->language ?? $userPreference->language,
                    'currency' => $request->currency ?? $userPreference->currency,
                ]);
            }
            
            return redirect()->route('profile')
                ->with('success', $formType === 'shipping_address' ? 
                    'Shipping address updated successfully!' : 
                    'Profile updated successfully!');
                    
        } catch (\Exception $e) {
            // Rollback transaction on error
            \DB::rollback();
            
            \Log::error('Profile update error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'form_type' => $request->input('form_type', 'personal_info'),
                'form_data' => $request->all()
            ]);
            
            return redirect()->route('profile')
                ->withErrors(['error' => 'Failed to update profile. Please try again.']);
        }
    }
    
    /**
     * Display the change password form.
     *
     * @return \Illuminate\View\View
     */
    public function showChangePassword()
    {
        return view('frontend.auth.change-password');
    }
      /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password_hash)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => [
                'required', 
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'different:current_password'
            ],
        ]);
        
        // Update user password
        $user->password_hash = Hash::make($request->password);
        $user->last_password_change = now();
        $user->failed_login_count = 0; // Reset failed login count
        $user->save();
        
        return redirect()->route('profile')
                         ->with('success', 'Password changed successfully!');
    }
    
    /**
     * Display the password reset page.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetPassword(Request $request, $token)
    {
        return view('frontend.auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }
    
    /**
     * Reset the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required', 
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        // Find the password reset token
        $passwordReset = \App\Models\PasswordResetToken::where('token', $request->token)
            ->where('email', $request->email)
            ->where('created_at', '>', now()->subHours(1))
            ->first();

        if (!$passwordReset || $passwordReset->isUsed() || $passwordReset->isExpired()) {
            return back()->withErrors(['email' => 'This password reset token is invalid or has expired.']);
        }

        // Find the user
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        // Update the user's password
        $user->password_hash = Hash::make($request->password);
        $user->last_password_change = now();
        $user->failed_login_count = 0;
        $user->save();
          // Mark the token as used
        $passwordReset->markAsUsed();
        
        return redirect()->route('login')->with('status', 'Your password has been reset successfully!');
    }
    
    /**
     * Update the user's shipping address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateShippingAddress(Request $request)
    {
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Validate shipping address
            $validated = $request->validate([
                'street' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:2',
            ]);
            
            // Update shipping address
            $user->fill($validated);
            $saved = $user->save();
            
            if (!$saved) {
                throw new \Exception('Failed to save shipping address');
            }
            
            // Log successful update
            Log::info('Shipping address updated', [
                'user_id' => $user->id,
                'shipping_address' => $validated,
            ]);
            
            // Commit transaction
            DB::commit();
            
            return redirect()->route('profile')
                ->with('success', 'Shipping address updated successfully!');
                
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollback();
            
            Log::error('Shipping address update error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('profile')
                ->withInput()
                ->withErrors(['error' => 'Failed to update shipping address. Please try again.']);
        }
    }
}
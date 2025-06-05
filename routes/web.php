<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShopController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\NewsController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\GuestCartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GeminiController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// Frontend Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/terms-of-service', [PageController::class, 'termsOfService'])->name('terms-of-service');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');

// Debug and diagnostic routes
Route::get('/debug-inventory', function() {
    $products = \App\Models\Product::with('inventory')->take(10)->get();
    
    echo "<h1>Inventory Debug</h1>";
    
    foreach ($products as $product) {
        echo "<hr>";
        echo "<h3>Product ID: {$product->id} - {$product->name}</h3>";
        echo "<ul>";
        foreach ($product->inventory as $inv) {
            echo "<li>Inventory ID: {$inv->id}</li>";
            echo "<li>Quantity (raw): {$inv->quantity}</li>";
            echo "<li>Quantity (int): " . (int)$inv->quantity . "</li>";
            echo "<li>Quantity Type: " . gettype($inv->quantity) . "</li>";
            echo "<li>Comparison (qty > 0): " . ($inv->quantity > 0 ? 'true' : 'false') . "</li>";
            echo "<li>Comparison ((int)qty > 0): " . ((int)$inv->quantity > 0 ? 'true' : 'false') . "</li>";
        }
        echo "</ul>";
        
        // Check what our current fix should be setting
        $productInventory = $product->inventory->first();
        $quantity = $productInventory ? (int)$productInventory->quantity : 0;
        $in_stock = $quantity > 0;
        
        echo "<p>Our fix would set: in_stock = " . ($in_stock ? 'true' : 'false') . "</p>";
    }
    
    return "Debug complete";
});

// Maintenance routes for cart fixes
Route::get('/maintenance/fix-zero-quantity-cart-items', 'App\Http\Controllers\Frontend\CartFixController@fixZeroQuantityItems');
Route::get('/maintenance/fix-cart', 'App\Http\Controllers\Frontend\CartFixController@fixZeroQuantityItems');
Route::get('/maintenance/fix-cart-structure', 'App\Http\Controllers\Frontend\CartFixController@fixCartStructure');

// Better diagnostic route with UI
Route::get('/inventory-diagnostic', [App\Http\Controllers\Frontend\DiagnosticController::class, 'checkInventory'])->name('inventory.diagnostic');

// Shop Routes
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
Route::post('/product/{id}/review', [ProductController::class, 'submitReview'])->name('product.review');

// Cart Routes
Route::middleware(['auth'])->group(function() {
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::match(['post', 'patch'], '/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
    Route::post('/cart/apply-promo', [CartController::class, 'applyCoupon'])->name('cart.apply-promo');
    Route::delete('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.remove-coupon');
    
    // Checkout Routes
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/process', [CartController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/order/confirmation/{order}', [CartController::class, 'orderConfirmation'])->name('order.confirmation');
});

// Cart Add Route - uses special middleware to redirect to login if not authenticated
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add')->middleware('cart.auth');

// Guest Cart Routes
Route::middleware(['web'])->group(function() {
    Route::get('/guest/cart', [GuestCartController::class, 'index'])->name('guest.cart');
    Route::post('/guest/cart/add', [GuestCartController::class, 'addToCart'])->name('guest.cart.add');
    Route::post('/guest/cart/update', [GuestCartController::class, 'updateQuantity'])->name('guest.cart.update');
    Route::post('/guest/cart/remove', [GuestCartController::class, 'removeFromCart'])->name('guest.cart.remove');
});

// News Routes
Route::get('/news', [NewsController::class, 'index'])->name('news');
Route::get('/news/{id}', [NewsController::class, 'show'])->name('news.show');
Route::post('/news/{id}/comment', [NewsController::class, 'addComment'])->name('news.comment');

// Newsletter Subscription
Route::post('/subscribe', [PageController::class, 'subscribe'])->name('subscribe');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email')->middleware('guest');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.update')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// User Profile Routes (Protected by auth and account status middleware)
Route::middleware(['auth', 'account.status'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::patch('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profile/shipping-address', [AuthController::class, 'updateShippingAddress'])->name('profile.shipping.update');
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::patch('/change-password', [AuthController::class, 'changePassword'])->name('password.change.update');
    
    // User Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{id}/track', [OrderController::class, 'track'])->name('orders.track');
    
    // User Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::delete('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::post('/wishlist/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.move_to_cart');
    Route::post('/wishlist/clear', [WishlistController::class, 'clear'])->name('wishlist.clear');
    Route::get('/wishlist/check', [WishlistController::class, 'check'])->name('wishlist.check');
    Route::get('/wishlist/debug', [App\Http\Controllers\WishlistDebugController::class, 'debug'])->name('wishlist.debug');
});

// Debug route for testing shipping address update
Route::get('/debug-profile', function() {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    
    $user = Auth::user();
    
    return [
        'id' => $user->id,
        'username' => $user->username,
        'email' => $user->email,
        'shipping_address' => [
            'street' => $user->street,
            'city' => $user->city,
            'state' => $user->state,
            'postal_code' => $user->postal_code,
            'country' => $user->country,
        ],
        'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
    ];
})->middleware('auth');

// Fallback for 404
Route::fallback(function () {
    return view('frontend.pages.404');
});

// Direct admin test route outside of group
Route::get('/admin-test', function() {
    return "Direct admin test route working!";
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Root admin route - redirect to dashboard if authenticated, otherwise to login
    Route::get('/', function() {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.login');
    });
    
    // Test route to verify routing functionality
    Route::get('test', [App\Http\Controllers\Admin\TestController::class, 'test'])->name('test');
    
    // CSS test route to verify admin styling
    Route::get('css-test', function() {
        return view('admin.css-test');
    })->name('css.test');
    
    // Auth Routes (Guest admin middleware - only accessible when not logged in)
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.post');
        Route::get('forgot-password', [App\Http\Controllers\Admin\AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('forgot-password', [App\Http\Controllers\Admin\AuthController::class, 'forgotPassword'])->name('password.email');
        Route::get('reset-password/{token}', [App\Http\Controllers\Admin\AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('reset-password', [App\Http\Controllers\Admin\AuthController::class, 'resetPassword'])->name('password.reset.update');
    });

    // Protected Routes (Admin authentication required)
    Route::middleware('admin.auth')->group(function () {
        
        // Dashboard route
        Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        // Notifications
        Route::post('notifications/dismiss', [App\Http\Controllers\Admin\NotificationController::class, 'dismiss'])->name('notifications.dismiss');
        Route::post('notifications/dismiss-all', [App\Http\Controllers\Admin\NotificationController::class, 'dismissAll'])->name('notifications.dismiss-all');
        
        // Profile
        Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');
        
        // Products
        Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
        
        // Categories
        Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
        
        // Orders
        Route::resource('orders', App\Http\Controllers\Admin\OrderController::class);
        Route::get('orders/filter/pending', [App\Http\Controllers\Admin\OrderController::class, 'pending'])->name('orders.pending');
        Route::get('orders/{order}/invoice', [App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('orders/test-route', function() {
            return "Available routes: " . route('orders.pending') . " and " . route('orders.index');
        });
        
        // Customers
        Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);
        
        // Inventory
        Route::resource('inventory', App\Http\Controllers\Admin\InventoryController::class);
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales', [App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('sales');
            Route::get('/inventory', [App\Http\Controllers\Admin\ReportController::class, 'inventory'])->name('inventory');
            Route::get('/customers', [App\Http\Controllers\Admin\ReportController::class, 'customers'])->name('customers');
        });
        
        // Settings
        Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        
        // Vendors (Commented out for future implementation)
        // Route::resource('vendors', App\Http\Controllers\Admin\VendorController::class);
        
        // Promotions
        Route::prefix('promotions')->name('promotions.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\PromotionController::class, 'index'])->name('index');
            Route::get('/export', [App\Http\Controllers\Admin\PromotionController::class, 'export'])->name('export');
            Route::get('/create', [App\Http\Controllers\Admin\PromotionController::class, 'createPromoCode'])->name('promo_codes.create');
            Route::post('/', [App\Http\Controllers\Admin\PromotionController::class, 'storePromoCode'])->name('promo_codes.store');
            Route::get('/{id}', [App\Http\Controllers\Admin\PromotionController::class, 'showPromoCode'])->name('promo_codes.show');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\PromotionController::class, 'editPromoCode'])->name('promo_codes.edit');
            Route::put('/{id}', [App\Http\Controllers\Admin\PromotionController::class, 'updatePromoCode'])->name('promo_codes.update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\PromotionController::class, 'destroyPromoCode'])->name('promo_codes.destroy');
            Route::patch('/{id}/toggle', [App\Http\Controllers\Admin\PromotionController::class, 'togglePromoCode'])->name('promo_codes.toggle');
        });
        
        // Reviews & Ratings
        Route::resource('reviews', App\Http\Controllers\Admin\ReviewController::class)->except(['create', 'store', 'edit', 'update']);
        Route::patch('reviews/{review}/status', [App\Http\Controllers\Admin\ReviewController::class, 'updateStatus'])->name('reviews.update-status');
        Route::patch('reviews/{review}/approve', [App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
        Route::patch('reviews/{review}/reject', [App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reviews.reject');
        
        // Support Tickets
        Route::resource('support-tickets', App\Http\Controllers\Admin\SupportTicketController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
        Route::post('support-tickets/{ticket}/reply', [App\Http\Controllers\Admin\SupportTicketController::class, 'reply'])->name('support-tickets.reply');
        Route::patch('support-tickets/{ticket}/status', [App\Http\Controllers\Admin\SupportTicketController::class, 'updateStatus'])->name('support-tickets.update-status');
        Route::patch('support-tickets/{ticket}/close', [App\Http\Controllers\Admin\SupportTicketController::class, 'close'])->name('support-tickets.close');
        
        // Admin Management (Super Admin only)
        Route::middleware('admin.super')->group(function () {
            Route::resource('admins', App\Http\Controllers\Admin\AdminController::class);
            Route::resource('audit-logs', App\Http\Controllers\Admin\AuditLogController::class)->only(['index', 'show']);
        });
        
        // Logout
        Route::post('/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
    });
});

// Chat routes
Route::get('/chat', [GeminiController::class, 'index'])->name('chat');
Route::post('/chat/send', [GeminiController::class, 'sendMessage'])->name('chat.send');
Route::post('/chat/clear', [GeminiController::class, 'clearConversation'])->name('chat.clear');

// Test route for Gemini chat
Route::get('/test-chat', function () {
    try {
        $result = \Gemini\Laravel\Facades\Gemini::generativeModel(model: 'gemini-1.5-flash-latest')
                    ->generateContent('Hello! This is a test message for PangAIaShop chatbot.');
        
        return response()->json([
            'success' => true,
            'message' => $result->text(),
            'status' => 'Gemini API is working correctly!'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'status' => 'Gemini API error'
        ]);
    }
});

// Test route for settings helper
Route::get('/test-setting', function () {
    // Try to set a setting
    setting('test_key', 'This is a test value');
    
    // Try to get the setting
    $value = setting('test_key');
    
    return "Setting value: " . $value;
});

// Temporary test route to debug admin reviews page
Route::get('/test-admin-reviews', function() {
    // Test the same logic as the admin controller
    $searchQuery = request()->input('search');
    $statusFilter = request()->input('status');
    
    $reviews = App\Models\Review::with(['product', 'user'])
        ->when($searchQuery, function ($query, $search) {
            return $query->where('comment', 'like', "%{$search}%")
                ->orWhereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
        })
        ->when($statusFilter, function ($query, $status) {
            if (in_array($status, ['pending', 'approved', 'rejected'])) {
                return $query->where('moderation_status', $status);
            }
            return $query;
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    
    // Get pending reviews count for badge display
    $pendingReviewsCount = App\Models\Review::where('moderation_status', 'pending')->count();
        
    return view('admin.reviews.index-test', compact('reviews', 'searchQuery', 'statusFilter', 'pendingReviewsCount'));
});

// Test route for Gemini API debugging
Route::get('/test-gemini-debug', function() {
    try {
        echo "<h1>Gemini API Debug Test</h1>";
        
        // Check if API key is configured
        $apiKey = config('gemini.api_key');
        echo "<p><strong>API Key:</strong> " . (empty($apiKey) ? 'NOT CONFIGURED' : substr($apiKey, 0, 10) . '...') . "</p>";
        
        if (empty($apiKey)) {
            echo "<p style='color: red;'>❌ API key is not configured!</p>";
            return;
        }
        
        echo "<p>✅ API key is configured</p>";
        
        // Test API call
        echo "<h2>Testing API Call...</h2>";
        
        $result = \Gemini\Laravel\Facades\Gemini::generativeModel(model: 'gemini-1.5-flash-latest')
                                               ->generateContent('Hello! Please respond with just "API working correctly" to confirm the connection.');
        
        $response = $result->text();
        
        echo "<p><strong>Response:</strong> " . htmlspecialchars($response) . "</p>";
        echo "<p style='color: green;'>✅ Gemini API is working correctly!</p>";
        
    } catch (\Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Error Class:</strong> " . get_class($e) . "</p>";
        echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
        echo "<pre><strong>Stack Trace:</strong>\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
});

// Gemini AI Routes
Route::post('/ask-gemini', [GeminiController::class, 'generateText']);
// Or for GET requests with a query parameter:
Route::get('/ask-gemini', [GeminiController::class, 'generateText']);
Route::post('/chat.send', [GeminiController::class, 'sendMessage'])->name('chat.send');
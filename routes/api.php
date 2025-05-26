<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Commented out non-existent API controllers to fix class loading
// use App\Http\Controllers\API\AuthController;
// use App\Http\Controllers\API\ProductController;
// use App\Http\Controllers\API\CategoryController;
// use App\Http\Controllers\API\ReviewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// All API routes have been temporarily commented out until the corresponding controllers are created
// The following routes were causing class loading errors because the controllers don't exist

/* 
// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Public auth routes for admin panel
Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login');

// Products
Route::get('products', [ProductController::class, 'index']);
Route::get('products/featured', [ProductController::class, 'getFeaturedProducts']);
Route::get('products/new-arrivals', [ProductController::class, 'getNewArrivals']);
Route::get('products/best-sellers', [ProductController::class, 'getBestSellers']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/{product}/related', [ProductController::class, 'getRelatedProducts']);
Route::get('products/{product}/reviews', [ReviewController::class, 'index']);

// Categories
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('categories/{category}/products', [CategoryController::class, 'products']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user-profile', [AuthController::class, 'user']);
    
    // User profile
    Route::get('user', 'App\Http\Controllers\API\UserController@show');
    Route::put('user', 'App\Http\Controllers\API\UserController@update');
    Route::delete('user', 'App\Http\Controllers\API\UserController@destroy');
    
    // Cart
    Route::get('cart', 'App\Http\Controllers\API\CartController@index');
    Route::post('cart/add', 'App\Http\Controllers\API\CartController@addToCart');
    Route::put('cart/{cartItem}', 'App\Http\Controllers\API\CartController@updateCartItem');
    Route::delete('cart/{cartItem}', 'App\Http\Controllers\API\CartController@removeFromCart');
    Route::delete('cart', 'App\Http\Controllers\API\CartController@clearCart');
    Route::post('cart/apply-promo', 'App\Http\Controllers\API\CartController@applyPromoCode');
    Route::delete('cart/remove-promo', 'App\Http\Controllers\API\CartController@removePromoCode');
    
    // Cart cleanup for problematic items
    Route::post('cart/cleanup', 'App\Http\Controllers\API\CartCleanupController@cleanupProblematicItems');
    Route::post('cart/fix-hoody', 'App\Http\Controllers\API\CartCleanupController@fixHoodyIssue');
    
    // Wishlist
    Route::get('wishlist', 'App\Http\Controllers\API\WishlistController@index');
    Route::post('wishlist/add', 'App\Http\Controllers\API\WishlistController@addToWishlist');
    Route::delete('wishlist/{wishlistItem}', 'App\Http\Controllers\API\WishlistController@removeFromWishlist');
    Route::delete('wishlist', 'App\Http\Controllers\API\WishlistController@clearWishlist');
    Route::post('wishlist/{wishlistItem}/move-to-cart', 'App\Http\Controllers\API\WishlistController@moveToCart');
    
    // Orders
    Route::get('orders', 'App\Http\Controllers\API\OrderController@index');
    Route::post('orders', 'App\Http\Controllers\API\OrderController@store');
    Route::get('orders/{order}', 'App\Http\Controllers\API\OrderController@show');
    Route::put('orders/{order}/cancel', 'App\Http\Controllers\API\OrderController@cancel');
    
    // Reviews
    Route::get('my-reviews', 'App\Http\Controllers\API\ReviewController@myReviews');
    Route::post('products/{product}/reviews', 'App\Http\Controllers\API\ReviewController@store');
    Route::put('reviews/{review}', 'App\Http\Controllers\API\ReviewController@update');
    Route::delete('reviews/{review}', 'App\Http\Controllers\API\ReviewController@destroy');
    
    // User preferences
    Route::get('preferences', 'App\Http\Controllers\API\UserPreferenceController@show');
    Route::put('preferences', 'App\Http\Controllers\API\UserPreferenceController@update');
    
    // Support tickets
    Route::get('support-tickets', 'App\Http\Controllers\API\SupportTicketController@index');
    Route::post('support-tickets', 'App\Http\Controllers\API\SupportTicketController@store');
    Route::get('support-tickets/{ticket}', 'App\Http\Controllers\API\SupportTicketController@show');
    Route::put('support-tickets/{ticket}', 'App\Http\Controllers\API\SupportTicketController@update');
    Route::put('support-tickets/{ticket}/close', 'App\Http\Controllers\API\SupportTicketController@close');
});

// Admin protected routes
Route::middleware(['auth:sanctum', 'admin.access'])->prefix('admin')->group(function () {
    // Admin dashboard data
    Route::get('dashboard', 'App\Http\Controllers\API\Admin\DashboardController@index');
    Route::get('sales-report', 'App\Http\Controllers\API\Admin\DashboardController@salesReport');
    Route::get('low-stock-products', 'App\Http\Controllers\API\Admin\DashboardController@getLowStockProducts');
    
    // Cart cleanup admin tools
    Route::post('cart/cleanup-all', 'App\Http\Controllers\API\CartCleanupController@cleanupAllZeroQuantityItems');
    
    // Product management
    Route::apiResource('products', 'App\Http\Controllers\API\Admin\ProductController');
    Route::post('products/{product}/images', 'App\Http\Controllers\API\Admin\ProductImageController@store');
    Route::delete('products/{product}/images/{image}', 'App\Http\Controllers\API\Admin\ProductImageController@destroy');
    
    // Categories management
    Route::apiResource('categories', 'App\Http\Controllers\API\Admin\CategoryController');
    
    // Orders management
    Route::apiResource('orders', 'App\Http\Controllers\API\Admin\OrderController');
    Route::put('orders/{order}/status', 'App\Http\Controllers\API\Admin\OrderController@updateStatus');
    
    // Inventory management
    Route::apiResource('inventory', 'App\Http\Controllers\API\Admin\InventoryController');
    
    // Users management
    Route::apiResource('users', 'App\Http\Controllers\API\Admin\UserController');
    
    // Promo codes
    Route::apiResource('promo-codes', 'App\Http\Controllers\API\Admin\PromoCodeController');
    
    // Support tickets management
    Route::apiResource('support-tickets', 'App\Http\Controllers\API\Admin\SupportTicketController');
    Route::put('support-tickets/{ticket}/assign', 'App\Http\Controllers\API\Admin\SupportTicketController@assignTicket');
    
    // Reviews moderation
    Route::get('reviews', 'App\Http\Controllers\API\Admin\ReviewController@index');
    Route::put('reviews/{review}/moderate', 'App\Http\Controllers\API\Admin\ReviewController@moderate');
    
    // Admin profile (all admins can access their own profile)
    Route::get('profile', 'App\Http\Controllers\API\Admin\AdminController@profile');
    Route::put('profile', 'App\Http\Controllers\API\Admin\AdminController@updateProfile');
    
    // Admins management - apply specialized middleware
    Route::middleware('admin.record.access')->group(function () {
        Route::get('admins', 'App\Http\Controllers\API\Admin\AdminController@index');
        Route::get('admins/{admin}', 'App\Http\Controllers\API\Admin\AdminController@show');
        Route::put('admins/{admin}', 'App\Http\Controllers\API\Admin\AdminController@update');
    });
    
    // Super Admin only routes
    Route::middleware('superadmin.access')->group(function () {
        // Create and delete admins (Super Admin only)
        Route::post('admins', 'App\Http\Controllers\API\Admin\AdminController@store');
        Route::delete('admins/{admin}', 'App\Http\Controllers\API\Admin\AdminController@destroy');
        
        // Vendors management (Super Admin only)
        Route::apiResource('vendors', 'App\Http\Controllers\API\Admin\VendorController');
        
        // Audit logs (Super Admin only)
        Route::get('audit-logs', 'App\Http\Controllers\API\Admin\AuditLogController@index');
    });
});
*/

// Debug route to check orders statuses
Route::get('debug/orders-status', function() {
    $statuses = \App\Models\Order::select('status')
        ->distinct()
        ->get()
        ->pluck('status');
    
    $orderCounts = \App\Models\Order::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get()
        ->pluck('count', 'status')
        ->toArray();
    
    return response()->json([
        'available_statuses' => $statuses,
        'order_counts_by_status' => $orderCounts
    ]);
});

// Debug route to check low stock products
Route::get('debug/low-stock', function() {
    $products = \App\Models\Product::join('inventories', 'products.id', '=', 'inventories.product_id')
        ->where('inventories.quantity', '<', 5)
        ->where('products.status', 'active')
        ->orderBy('inventories.quantity', 'asc')
        ->limit(10)
        ->select(
            'products.id',
            'products.name',
            'products.price',
            'inventories.quantity as stock_quantity'
        )
        ->get();
    
    return response()->json([
        'count' => $products->count(),
        'products' => $products
    ]);
});
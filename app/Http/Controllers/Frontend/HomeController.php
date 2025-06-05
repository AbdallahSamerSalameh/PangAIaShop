<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Article;
use App\Helpers\InventoryHelper;

class HomeController extends Controller
{
    /**
     * Display the home page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {        // Get main product categories for homepage display
        $featuredCategories = Category::whereNull('parent_category_id')
                                    ->where('is_active', 1)
                                    ->withCount('products')  // This will add products_count attribute
                                    ->orderBy('display_order')
                                    ->take(6)
                                    ->get();
        
        // Get featured products across different categories
        $featuredProducts = Product::where('status', 'active')
                                   ->inRandomOrder()
                                   ->take(8)
                                   ->with(['images' => function($query) {
                                       $query->where('is_primary', true);
                                   }, 'categories', 'inventory'])
                                   ->get()
                                   ->map(function($product) {
                                       // Set a default featured image if none exists
                                       $product->featured_image = $product->images->first() 
                                           ? $product->images->first()->image_url 
                                           : 'assets/img/products/product-img-1.jpg';
                                       
                                       // Get category name
                                       $product->category_name = $product->categories->first() 
                                           ? $product->categories->first()->name 
                                           : 'Uncategorized';                                         
                                       // Check stock status using hasOne relationship
                                       $productInventory = $product->inventory;
                                        
                                       // Add enhanced debug logging
                                       \Log::debug('Featured product inventory check', [
                                           'product_id' => $product->id,
                                           'inventory_id' => $productInventory ? $productInventory->id : 'none',
                                           'quantity' => $productInventory ? $productInventory->quantity : 'none',
                                           'quantity_type' => $productInventory ? gettype($productInventory->quantity) : 'none',
                                           'is_numeric' => $productInventory ? is_numeric($productInventory->quantity) : false
                                       ]);
                                       
                                       // Ensure we're working with an actual number for quantity
                                       $rawQuantity = $productInventory ? $productInventory->quantity : 0;
                                       $quantity = is_numeric($rawQuantity) ? intval($rawQuantity) : 0;
                                       
                                       // Force in_stock to a boolean and ensure stock_qty is an integer
                                       $product->in_stock = $quantity > 0;
                                       $product->stock_qty = $quantity;
                                       
                                       return $product;
                                   });        // Get new arrivals (recently added products)
        $newArrivals = Product::where('status', 'active')
                              ->orderBy('created_at', 'desc')
                              ->take(4)
                              ->with(['images' => function($query) {
                                  $query->where('is_primary', true);
                              }, 'categories', 'inventory'])
                              ->get()
                              ->map(function($product) {
                                  $product->featured_image = $product->images->first() 
                                      ? $product->images->first()->image_url 
                                      : 'assets/img/products/product-img-1.jpg';
                                  
                                  $product->category_name = $product->categories->first() 
                                      ? $product->categories->first()->name 
                                      : 'Uncategorized';
                                      
                                  // Check stock status using hasOne relationship
                                  $productInventory = $product->inventory;
                                  // Explicitly cast quantity to int for comparison
                                  $quantity = $productInventory ? (int)$productInventory->quantity : 0;
                                  $product->in_stock = $quantity > 0;
                                  $product->stock_qty = $quantity;
                                  
                                  return $product;
                              });          // Get best selling products
        $bestSellers = Product::where('status', 'active')
                              ->withCount('orderItems')
                              ->orderBy('order_items_count', 'desc')
                              ->take(4)
                              ->with(['images' => function($query) {
                                  $query->where('is_primary', true);
                              }, 'categories', 'inventory'])
                              ->get()
                              ->map(function($product) {
                                  $product->featured_image = $product->images->first() 
                                      ? $product->images->first()->image_url 
                                      : 'assets/img/products/product-img-1.jpg';
                                  
                                  $product->category_name = $product->categories->first() 
                                      ? $product->categories->first()->name 
                                      : 'Uncategorized';
                                      
                                  // Check stock status using hasOne relationship
                                  $productInventory = $product->inventory;
                                  // Explicitly cast quantity to int for comparison
                                  $quantity = $productInventory ? (int)$productInventory->quantity : 0;
                                  $product->in_stock = $quantity > 0;
                                  $product->stock_qty = $quantity;
                                      
                                  return $product;
                              });
                              
        // Try to get articles/news from database if table exists, otherwise use placeholder
        try {
            $latestNews = Article::orderBy('created_at', 'desc')
                                ->take(3)
                                ->get();
        } catch (\Exception $e) {
            // Fallback to placeholder data
            $latestNews = collect([
                (object)[
                    'id' => 1,
                    'title' => 'The Latest Tech Trends for 2025',
                    'excerpt' => 'Discover the cutting-edge technology trends shaping our future.',
                    'image' => 'assets/img/latest-news/news-bg-1.jpg',
                    'author' => 'Admin',
                    'created_at' => now()->subDays(5),
                    'category' => 'Technology'
                ],
                (object)[
                    'id' => 2,
                    'title' => 'Top Fashion Items This Season',
                    'excerpt' => 'Check out the most popular fashion items trending this season.',
                    'image' => 'assets/img/latest-news/news-bg-2.jpg',
                    'author' => 'Admin',
                    'created_at' => now()->subDays(10),
                    'category' => 'Fashion'
                ],
                (object)[
                    'id' => 3,
                    'title' => 'Home Appliances Buying Guide',
                    'excerpt' => 'Learn how to select the best appliances for your home needs.',
                    'image' => 'assets/img/latest-news/news-bg-3.jpg',
                    'author' => 'Admin',
                    'created_at' => now()->subDays(15),
                    'category' => 'Home'
                ]
            ]);
        }

        return view('frontend.pages.home', [
            'featuredCategories' => $featuredCategories,
            'featuredProducts' => $featuredProducts,
            'newArrivals' => $newArrivals,
            'bestSellers' => $bestSellers,
            'latestNews' => $latestNews,
        ]);
    }
}
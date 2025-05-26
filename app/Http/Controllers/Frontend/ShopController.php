<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Helpers\InventoryHelper;

class ShopController extends Controller
{
    /**
     * Display the shop page with products and filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */    public function index(Request $request)
    {
        try {
            // Check database connection first
            \DB::connection()->getPdo();
            \Log::info('Database connection successful');
            
            // Get all categories for the filter sidebar with better error handling
            try {
                $categories = Category::withCount('products')
                                    ->where('is_active', 1)
                                    ->orderBy('name')
                                    ->get();
                \Log::debug('Categories loaded successfully', ['count' => $categories->count()]);
            } catch (\Exception $e) {
                \Log::error('Error loading categories: ' . $e->getMessage());
                $categories = collect([]);
            }
            
            // Build query based on filters with better error handling
            \Log::debug('Building product query with relationships');
            $query = Product::with([
                'images' => function($query) {
                    $query->where('is_primary', true);
                }, 
                'categories', 
                'inventory'
            ]);
            
            // Category filter
            if ($request->has('category') && $request->category) {
                $categoryId = $request->category;
                $query->whereHas('categories', function($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                });
            }
              // Price range filter - with better error handling
            if ($request->has('min_price') && $request->has('max_price')) {
                $minPrice = $request->min_price;
                $maxPrice = $request->max_price;
                
                \Log::debug('Applying price filter', ['min' => $minPrice, 'max' => $maxPrice]);
                
                try {
                    // Using a safer approach that handles null sale_price values
                    $query->where(function($q) use ($minPrice, $maxPrice) {
                        $q->where(function($innerQ) use ($minPrice, $maxPrice) {
                            // When sale_price is not null and is lower than regular price
                            $innerQ->whereNotNull('sale_price')
                                   ->where('sale_price', '>=', $minPrice)
                                   ->where('sale_price', '<=', $maxPrice);
                        })->orWhere(function($innerQ) use ($minPrice, $maxPrice) {
                            // When sale_price is null or not lower than regular price, use regular price
                            $innerQ->where(function($deepQ) {
                                $deepQ->whereNull('sale_price')
                                      ->orWhereColumn('sale_price', '>=', 'price');
                            })->where('price', '>=', $minPrice)
                              ->where('price', '<=', $maxPrice);
                        });
                    });
                } catch (\Exception $e) {
                    // Fallback to using only the regular price if there's an error
                    \Log::warning('Error in price filtering, falling back to basic filter: ' . $e->getMessage());
                    $query->whereBetween('price', [$minPrice, $maxPrice]);
                }
            }
            
            // Search query
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhereHas('categories', function($catQuery) use ($search) {
                          $catQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }
            
            // In stock filter
            if ($request->has('in_stock') && $request->in_stock) {
                $query->whereHas('inventory', function($q) {
                    $q->where('quantity', '>', 0);
                });
            }
              // Sorting with improved error handling
            $sort = $request->sort ?? 'newest';
            try {
                switch ($sort) {
                    case 'price_low':
                        // Calculate effective price (sale price if available, otherwise regular price)
                        $query->orderByRaw('CASE WHEN sale_price IS NOT NULL AND sale_price < price THEN sale_price ELSE price END ASC');
                        break;
                    case 'price_high':
                        // Calculate effective price (sale price if available, otherwise regular price)
                        $query->orderByRaw('CASE WHEN sale_price IS NOT NULL AND sale_price < price THEN sale_price ELSE price END DESC');
                        break;
                    case 'name_asc':
                        $query->orderBy('name', 'asc');
                        break;
                    case 'name_desc':
                        $query->orderBy('name', 'desc');
                        break;
                    case 'popular':
                        $query->withCount('orderItems')->orderBy('order_items_count', 'desc');
                        break;
                    case 'newest':
                    default:
                        $query->orderBy('created_at', 'desc');
                }
            } catch (\Exception $e) {
                \Log::warning('Error in product sorting, falling back to newest: ' . $e->getMessage());
                // Fallback to a safe default sorting if there's an error
                $query->orderBy('created_at', 'desc');
            }
              // Get products with pagination - with added error handling and debugging
            \Log::debug('About to execute product query', ['query' => $query->toSql()]);
            
            // Get the base products with minimal relationships to reduce complexity
            $products = $query->where('status', 'active')
                              ->paginate(12)
                              ->appends($request->except('page'));
            
            // Format products for display with better error handling
            $products->getCollection()->transform(function($product) {
                try {
                    // Set featured image - with better error handling
                    if ($product->images && $product->images->isNotEmpty() && $product->images->first() && $product->images->first()->image_url) {
                        $product->featured_image = $product->images->first()->image_url;
                    } else {
                        $product->featured_image = 'assets/img/products/product-img-1.jpg';
                    }                    // Check stock status using our new helper and macros
                    if ($product->inventory && $product->inventory->isNotEmpty()) {
                        // Use the collection macros we defined
                        $isInStock = $product->inventory->isInStock();
                        $stockQty = $product->inventory->safeQuantity();
                        
                        // Add detailed debug logging
                        \Log::debug('Product inventory check using macros', [
                            'product_id' => $product->id,
                            'is_in_stock' => $isInStock,
                            'stock_qty' => $stockQty,
                            'first_inventory_id' => $product->inventory->first() ? $product->inventory->first()->id : 'none',
                        ]);
                        
                        // Set the values on the product
                        $product->in_stock = $isInStock;
                        $product->stock_qty = $stockQty;
                        
                    } else {
                        $product->in_stock = false;
                        $product->stock_qty = 0;
                    }
                    
                    // Alternative approach using helper (uncommment if macros don't work)
                    // \App\Helpers\InventoryHelper::updateProductStockInfo($product);
                    
                    // Get category names - with better error handling
                    if ($product->categories && $product->categories->isNotEmpty()) {
                        $product->category_names = $product->categories->pluck('name');
                    } else {
                        $product->category_names = collect(['Uncategorized']);
                    }                } catch (\Exception $e) {
                    \Log::warning('Error transforming product ID: ' . $product->id, ['error' => $e->getMessage()]);
                    // Set default values if transformation fails
                    $product->featured_image = 'assets/img/products/product-img-1.jpg';
                    $product->in_stock = false;
                    $product->stock_qty = 0;
                    $product->category_names = collect(['Uncategorized']);
                }
                
                return $product;
            });
            
            // Price range filter with better error handling
            try {
                $minPrice = Product::where('status', 'active')->min('price') ?? 0;
                $maxPrice = Product::where('status', 'active')->max('price') ?? 100;
                
                $priceRange = [
                    'min' => floor($minPrice),
                    'max' => ceil($maxPrice)
                ];
            } catch (\Exception $e) {
                \Log::warning('Error getting price range', ['error' => $e->getMessage()]);
                $priceRange = ['min' => 0, 'max' => 100];
            }        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database-related errors more specifically
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            \Log::error('Database error in shop page: ' . $errorMessage, [
                'exception' => $e,
                'code' => $errorCode,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Create empty collections for required data
            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                12,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            
            $categories = collect([]);
            $priceRange = ['min' => 0, 'max' => 100];
            
            // Provide more specific error messages based on error type
            if (strpos($errorMessage, 'no such table') !== false) {
                session()->flash('error', 'Database table not found. Please run database migrations.');
            } else if (strpos($errorMessage, 'Access denied') !== false) {
                session()->flash('error', 'Database access error. Please check your database credentials.');
            } else {
                session()->flash('error', 'Database error occurred. Please try again later or contact support.');
            }
            
        } catch (\PDOException $e) {
            // Handle connection-related errors
            \Log::error('PDO error in shop page: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Create empty collections for required data
            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                12,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            
            $categories = collect([]);
            $priceRange = ['min' => 0, 'max' => 100];
            
            session()->flash('error', 'Database connection error. Please check your database configuration.');
            
        } catch (\Exception $e) {
            // Log the actual exception for debugging
            \Log::error('Shop page error: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Create empty collections for required data
            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                12,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            
            $categories = collect([]);
            $priceRange = ['min' => 0, 'max' => 100];
            
            // Set a flash message to display on the page
            session()->flash('error', 'There was a problem loading product data: ' . $e->getMessage());
        }
        
        return view('frontend.pages.shop', [
            'products' => $products,
            'categories' => $categories,
            'priceRange' => $priceRange,
            'currentCategory' => $request->category,
            'minPrice' => $request->min_price ?? $priceRange['min'],
            'maxPrice' => $request->max_price ?? $priceRange['max'],
            'search' => $request->search ?? '',
            'sort' => $sort ?? 'newest',
        ]);
    }
    
    /**
     * Display a specific product.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            // Get the product with relationships
            $product = Product::with([
                'images',
                'categories',
                'inventory',
                'reviews' => function($query) {
                    $query->with('user')->orderBy('created_at', 'desc');
                },
                'variants',
                'relatedProducts' => function($query) {
                    $query->with(['images' => function($q) {
                        $q->where('is_primary', true);
                    }])->limit(4);
                }
            ])->findOrFail($id);
            
            // Format product data
            $product->featured_image = $product->images->where('is_primary', true)->first() 
                ? $product->images->where('is_primary', true)->first()->image_url 
                : 'assets/img/products/product-img-1.jpg';            // Check stock status - inventory is a collection, so we need to use first()
            $productInventory = $product->inventory->first();
            
            // Add enhanced debug logging for single product view
            \Log::debug('Single product inventory check', [
                'product_id' => $product->id,
                'inventory_id' => $productInventory ? $productInventory->id : 'none',
                'quantity' => $productInventory ? $productInventory->quantity : 'none',
                'quantity_type' => $productInventory ? gettype($productInventory->quantity) : 'none',
                'quantity_value' => $productInventory ? var_export($productInventory->quantity, true) : 'none',
                'is_numeric' => $productInventory ? is_numeric($productInventory->quantity) : false,
                'casting_to_int' => $productInventory ? (int)$productInventory->quantity : 0,
                'raw_comparison' => $productInventory ? ($productInventory->quantity > 0 ? 'true' : 'false') : 'n/a',
                'cast_comparison' => $productInventory ? ((int)$productInventory->quantity > 0 ? 'true' : 'false') : 'n/a'
            ]);
            
            // Ensure we're working with an actual number for quantity
            $rawQuantity = $productInventory ? $productInventory->quantity : 0;
            $quantity = is_numeric($rawQuantity) ? intval($rawQuantity) : 0;
            
            // Force in_stock to a boolean and ensure stock_qty is an integer
            $product->in_stock = $quantity > 0;
            $product->stock_qty = $quantity;
            
            \Log::debug('Final product stock status', [
                'product_id' => $product->id,
                'final_in_stock' => $product->in_stock ? 'true' : 'false',
                'final_stock_qty' => $product->stock_qty
            ]);
            $product->category_names = $product->categories->pluck('name');
            
            // Calculate average rating
            $avgRating = $product->reviews->avg('rating') ?: 0;
            $product->avg_rating = round($avgRating, 1);
            $product->review_count = $product->reviews->count();
            
            // Format related products
            $relatedProducts = $product->relatedProducts->map(function($related) {                
                    $related->featured_image = $related->images->first() 
                    ? $related->images->first()->image_url 
                    : 'assets/img/products/product-img-1.jpg';                // Check stock status - inventory is a collection, so we need to use first()
                $relatedInventory = $related->inventory->first();
                
                // Add enhanced debug logging for related products
                \Log::debug('Related product inventory check', [
                    'product_id' => $related->id,
                    'inventory_id' => $relatedInventory ? $relatedInventory->id : 'none',
                    'quantity' => $relatedInventory ? $relatedInventory->quantity : 'none',
                    'quantity_type' => $relatedInventory ? gettype($relatedInventory->quantity) : 'none',
                    'quantity_value' => $relatedInventory ? var_export($relatedInventory->quantity, true) : 'none',
                    'is_numeric' => $relatedInventory ? is_numeric($relatedInventory->quantity) : false,
                    'casting_to_int' => $relatedInventory ? (int)$relatedInventory->quantity : 0
                ]);
                
                // Ensure we're working with an actual number for quantity
                $rawQuantity = $relatedInventory ? $relatedInventory->quantity : 0;
                $quantity = is_numeric($rawQuantity) ? intval($rawQuantity) : 0;
                
                // Force in_stock to a boolean and ensure stock_qty is an integer
                $related->in_stock = $quantity > 0;
                $related->stock_qty = $quantity;
                $related->category_names = $related->categories->pluck('name');
                return $related;
            });
            
            // Get recently viewed products (exclude current)
            $recentlyViewed = Product::where('id', '!=', $id)
                                    ->where('status', 'active')
                                    ->with(['images' => function($query) {
                                        $query->where('is_primary', true);
                                    }, 'categories', 'inventory'])
                                    ->inRandomOrder()
                                    ->limit(4)
                                    ->get()
                                    ->map(function($product) {                                        $product->featured_image = $product->images->first() 
                                            ? $product->images->first()->image_url 
                                            : 'assets/img/products/product-img-1.jpg';                                        // Check stock status - inventory is a collection, so we need to use first()
                                        $productInventory = $product->inventory->first();
                                        
                                        // Add enhanced debug logging for recently viewed products
                                        \Log::debug('Recently viewed product inventory check', [
                                            'product_id' => $product->id,
                                            'inventory_id' => $productInventory ? $productInventory->id : 'none',
                                            'quantity' => $productInventory ? $productInventory->quantity : 'none',
                                            'quantity_type' => $productInventory ? gettype($productInventory->quantity) : 'none',
                                            'quantity_value' => $productInventory ? var_export($productInventory->quantity, true) : 'none',
                                            'is_numeric' => $productInventory ? is_numeric($productInventory->quantity) : false,
                                            'casting_to_int' => $productInventory ? (int)$productInventory->quantity : 0
                                        ]);
                                        
                                        // Ensure we're working with an actual number for quantity
                                        $rawQuantity = $productInventory ? $productInventory->quantity : 0;
                                        $quantity = is_numeric($rawQuantity) ? intval($rawQuantity) : 0;
                                        
                                        // Force in_stock to a boolean and ensure stock_qty is an integer
                                        $product->in_stock = $quantity > 0;
                                        $product->stock_qty = $quantity;
                                        $product->category_names = $product->categories->pluck('name');
                                        return $product;
                                    });
                                    
            // Store recently viewed products in session
            if (!session()->has('recently_viewed')) {
                session(['recently_viewed' => []]);
            }
            
            $recentlyViewedIds = session('recently_viewed');
            
            // Add current product to recently viewed
            if (!in_array($id, $recentlyViewedIds)) {
                $recentlyViewedIds[] = $id;
                
                // Keep only the most recent 10 products
                if (count($recentlyViewedIds) > 10) {
                    array_shift($recentlyViewedIds);
                }
                
                session(['recently_viewed' => $recentlyViewedIds]);
            }
              } catch (\Exception $e) {
            // Log the actual exception for debugging
            \Log::error('Product detail page error: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'product_id' => $id
            ]);
            
            // Instead of showing fake product data, redirect to shop page with error message
            return redirect()->route('shop')->with('error', 'The requested product could not be found or is currently unavailable.');
        }
        
        return view('frontend.pages.single-product', [
            'product' => $product,
            'relatedProducts' => $relatedProducts ?? collect([]),
            'recentlyViewed' => $recentlyViewed ?? collect([]),
        ]);
    }
}
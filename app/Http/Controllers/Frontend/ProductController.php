<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Review;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use App\Helpers\InventoryHelper;

class ProductController extends Controller
{
    /**
     * Display the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */    public function show($id)
    {
        // Find product and load basic relationships
        $product = Product::with([
            'images', 
            'categories', 
            'variants',
            'inventory'
        ])->findOrFail($id);
        
        // Separately load explicitly approved reviews to ensure consistency        // Get all approved reviews for this product
        $approvedReviews = Review::where('product_id', $id)
            ->where('moderation_status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Load the users for each review to avoid N+1 queries
        $userIds = $approvedReviews->pluck('user_id')->unique()->toArray();
        $users = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');
              // Attach user data to each review to avoid N+1 query issues
        $approvedReviews->each(function($review) use ($users) {
            if (isset($users[$review->user_id])) {
                $review->setRelation('user', $users[$review->user_id]);
            }
        });

        // Explicitly set the reviews relation
        $product->setRelation('reviews', $approvedReviews);
        
        // Increment view count
        $product->increment('view_count');
        
        // Get related products from the same categories
        $relatedProducts = collect();
        if ($product->categories->isNotEmpty()) {
            $categoryIds = $product->categories->pluck('id');              $relatedProducts = Product::whereHas('categories', function($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->with(['images' => function($query) {
                $query->where('is_primary', true);
            }, 'categories', 'inventory'])
            ->take(4)
            ->get();
            
            // Transform the collection to add featured image and stock status
            $relatedProducts = $relatedProducts->map(function($related) {
                $related->featured_image = $related->images->first() 
                    ? $related->images->first()->image_url 
                    : 'assets/img/products/product-img-1.jpg';
                
                // Set stock status attributes
                $inventory = $related->inventory;
                $quantity = $inventory ? intval($inventory->quantity) : 0;
                $related->stock_qty = $quantity;
                $related->in_stock = $quantity > 0;
                    
                return $related;
            });
        }
        
        // Check if product is in user's wishlist
        $inWishlist = false;
        if (Auth::check()) {
            $user = Auth::user();
            $wishlist = $user->wishlist;
            
            if ($wishlist) {
                $inWishlist = $wishlist->items()
                    ->where('product_id', $product->id)
                    ->exists();
            }
        }        // Calculate and explicitly set average rating and review count on the product
        // These are based on the approved reviews only
        $approvedReviews = $product->reviews; // We've already set this to only approved reviews
        $product->avg_rating = $approvedReviews->avg('rating') ?: 0;
        $product->review_count = $approvedReviews->count();        // Get rating distribution from approved reviews
        $ratingDistribution = [
            5 => $approvedReviews->where('rating', 5)->count(),
            4 => $approvedReviews->where('rating', 4)->count(),
            3 => $approvedReviews->where('rating', 3)->count(),
            2 => $approvedReviews->where('rating', 2)->count(),
            1 => $approvedReviews->where('rating', 1)->count(),
        ];
        
        // Calculate percentages for the progress bars
        $ratingPercentages = [];
        if ($product->review_count > 0) {
            foreach ($ratingDistribution as $rating => $count) {
                $ratingPercentages[$rating] = round(($count / $product->review_count) * 100);
            }
        } else {
            $ratingPercentages = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        }
        
        // Store current product in recently viewed products session
        $recentlyViewedIds = session()->get('recently_viewed', []);
        
        // Don't add the current product to recently viewed
        if (!in_array($id, $recentlyViewedIds)) {
            // Push current product to the beginning of the array
            array_unshift($recentlyViewedIds, $id);
            // Limit to 4 items
            $recentlyViewedIds = array_slice($recentlyViewedIds, 0, 4);
            session()->put('recently_viewed', $recentlyViewedIds);
        }
        
        // Get recently viewed products
        $recentlyViewed = collect();
        
        if (!empty($recentlyViewedIds)) {
            // Filter out the current product
            $viewedIds = array_diff($recentlyViewedIds, [$id]);
            
            if (!empty($viewedIds)) {
                $recentlyViewed = Product::whereIn('id', $viewedIds)
                    ->with(['images' => function($query) {
                        $query->where('is_primary', true);
                    }, 'categories'])
                    ->take(3) // Only show 3 at most
                    ->get()
                    ->map(function($viewed) {
                        $viewed->featured_image = $viewed->images->first() 
                            ? $viewed->images->first()->image_url 
                            : 'assets/img/products/product-img-1.jpg';
                        
                        return $viewed;                    });
            }
        }
        
        // Set inventory status attributes for the template
        $inventory = $product->inventory;
        $quantity = $inventory ? intval($inventory->quantity) : 0;
        
        // Set stock status attributes that the template expects
        $product->stock_qty = $quantity;
        $product->in_stock = $quantity > 0;
          return view('frontend.pages.single-product', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'inWishlist' => $inWishlist,
            'ratingDistribution' => $ratingDistribution,
            'ratingPercentages' => $ratingPercentages,
            'recentlyViewed' => $recentlyViewed
        ]);
    }
    
    /**
     * Submit a product review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */    public function submitReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|min:10|max:500',
        ]);
        
        // Find the product
        $product = Product::findOrFail($id);
        
        // Only authenticated users can submit reviews
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to submit a review.');
        }

        $user = Auth::user();
        
        // Check if the user has already reviewed this product
        $existingReview = Review::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();
              if ($existingReview) {
            // Update existing review
            $existingReview->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
                'moderation_status' => 'pending', // Submit for admin approval
                'created_at' => now(),
            ]);
            
            $message = 'Your review has been updated and is pending approval.';
        } else {
            // Create a new review
            Review::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'moderation_status' => 'pending', // Submit for admin approval
                'created_at' => now(),
            ]);
            
            $message = 'Thank you for your review! It is pending approval and will be visible once approved.';
        }
        
        return redirect()->back()->with('success', $message);
    }
    
    /**
     * Get product variant details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVariantDetails(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,id',
        ]);
        
        $variant = ProductVariant::with('images')
            ->where('product_id', $request->product_id)
            ->where('id', $request->variant_id)
            ->first();
            
        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [                'id' => $variant->id,
                'name' => $variant->name,
                'price' => $variant->price,
                'formatted_price' => '$' . number_format($variant->price, 2),
                'stock_quantity' => $variant->inventory ? $variant->inventory->quantity : 0,
                'in_stock' => $variant->inventory && $variant->inventory->quantity > 0,
                'sku' => $variant->sku,
                'images' => $variant->images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->image_url,
                        'is_primary' => $image->is_primary,
                    ];
                }),
            ]
        ]);
    }
}